<?php
/**
 * Noriks Call Center - PHP API v3
 * + Create orders from abandoned carts
 * + Meta tags for call center orders
 */

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit(0);

// Store configs
$stores = [
    'hr' => ['name' => 'Croatia', 'flag' => '🇭🇷', 'url' => 'https://noriks.com/hr', 'ck' => 'ck_d73881b20fd65125fb071414b8d54af7681549e3', 'cs' => 'cs_e024298df41e4352d90e006d2ec42a5b341c1ce5'],
    'cz' => ['name' => 'Czech', 'flag' => '🇨🇿', 'url' => 'https://noriks.com/cz', 'ck' => 'ck_396d624acec5f7a46dfcfa7d2a74b95c82b38962', 'cs' => 'cs_2a69c7ad4a4d118a2b8abdf44abdd058c9be9115'],
    'pl' => ['name' => 'Poland', 'flag' => '🇵🇱', 'url' => 'https://noriks.com/pl', 'ck' => 'ck_8fd83582ada887d0e586a04bf870d43634ca8f2c', 'cs' => 'cs_f1bf98e46a3ae0623c5f2f9fcf7c2478240c5115'],
    'gr' => ['name' => 'Greece', 'flag' => '🇬🇷', 'url' => 'https://noriks.com/gr', 'ck' => 'ck_2595568b83966151e08031e42388dd1c34307107', 'cs' => 'cs_dbd091b4fc11091638f8ec4c838483be32cfb15b'],
    'sk' => ['name' => 'Slovakia', 'flag' => '🇸🇰', 'url' => 'https://noriks.com/sk', 'ck' => 'ck_1abaeb006bb9039da0ad40f00ab674067ff1d978', 'cs' => 'cs_32b33bc2716b07a738fb18eb377a767ef60edfe7'],
    'it' => ['name' => 'Italy', 'flag' => '🇮🇹', 'url' => 'https://noriks.com/it', 'ck' => 'ck_84a1e1425710ff9eeed69b100ed9ac445efc39e2', 'cs' => 'cs_81d25dcb0371773387da4d30482afc7ce83d1b3e'],
    'hu' => ['name' => 'Hungary', 'flag' => '🇭🇺', 'url' => 'https://noriks.com/hu', 'ck' => 'ck_e591c2a0bf8c7a59ec5893e03adde3c760fbdaae', 'cs' => 'cs_d84113ee7a446322d191be0725c0c92883c984c3']
];

$storeCurrencies = ['hr' => 'EUR', 'cz' => 'CZK', 'pl' => 'PLN', 'sk' => 'EUR', 'hu' => 'HUF', 'gr' => 'EUR', 'it' => 'EUR'];

$dataFile = __DIR__ . '/data/call_data.json';
$cacheDir = __DIR__ . '/data/cache/';

function loadCallData() {
    global $dataFile;
    if (file_exists($dataFile)) {
        return json_decode(file_get_contents($dataFile), true) ?: [];
    }
    return [];
}

function saveCallData($data) {
    global $dataFile;
    $dir = dirname($dataFile);
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    file_put_contents($dataFile, json_encode($data, JSON_PRETTY_PRINT));
}

function getCache($key, $maxAge = 300) {
    global $cacheDir;
    if (!is_dir($cacheDir)) @mkdir($cacheDir, 0755, true);
    $file = $cacheDir . md5($key) . '.json';
    if (file_exists($file) && (time() - filemtime($file)) < $maxAge) {
        return json_decode(file_get_contents($file), true);
    }
    return null;
}

function setCache($key, $data) {
    global $cacheDir;
    if (!is_dir($cacheDir)) @mkdir($cacheDir, 0755, true);
    file_put_contents($cacheDir . md5($key) . '.json', json_encode($data));
}

function curlMultiGet($urls) {
    $mh = curl_multi_init();
    $handles = [];
    foreach ($urls as $key => $url) {
        $ch = curl_init();
        curl_setopt_array($ch, [CURLOPT_URL => $url, CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 20, CURLOPT_SSL_VERIFYPEER => true]);
        curl_multi_add_handle($mh, $ch);
        $handles[$key] = $ch;
    }
    $running = null;
    do { curl_multi_exec($mh, $running); curl_multi_select($mh); } while ($running > 0);
    $results = [];
    foreach ($handles as $key => $ch) {
        $results[$key] = curl_multi_getcontent($ch);
        curl_multi_remove_handle($mh, $ch);
        curl_close($ch);
    }
    curl_multi_close($mh);
    return $results;
}

function wcApiRequest($storeCode, $endpoint, $params = [], $method = 'GET', $body = null) {
    global $stores;
    $config = $stores[$storeCode] ?? null;
    if (!$config) return ['error' => 'Invalid store'];
    
    $url = $config['url'] . '/wp-json/wc/v3/' . $endpoint;
    if ($params && $method === 'GET') $url .= '?' . http_build_query($params);
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_USERPWD => $config['ck'] . ':' . $config['cs'],
        CURLOPT_SSL_VERIFYPEER => true
    ]);
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) return ['error' => $error];
    
    $data = json_decode($response, true);
    if ($httpCode >= 400) {
        return ['error' => $data['message'] ?? 'API Error', 'code' => $httpCode];
    }
    
    return $data ?: [];
}

function getCompletedOrderEmails($storeCode) {
    $cacheKey = "completed_emails_{$storeCode}";
    $cached = getCache($cacheKey, 600);
    if ($cached !== null) return $cached;
    
    $emails = [];
    $orders = wcApiRequest($storeCode, 'orders', [
        'status' => 'processing,completed',
        'per_page' => 100,
        'after' => date('Y-m-d\TH:i:s', strtotime('-30 days'))
    ]);
    
    if (is_array($orders)) {
        foreach ($orders as $order) {
            if (!empty($order['billing']['email'])) {
                $emails[] = strtolower($order['billing']['email']);
            }
        }
    }
    
    setCache($cacheKey, $emails);
    return $emails;
}

function fetchAbandonedCarts() {
    global $stores, $storeCurrencies;
    
    $cached = getCache('abandoned_carts_filtered', 300);
    if ($cached !== null) return $cached;
    
    $endpoints = [];
    foreach ($stores as $code => $config) {
        $endpoints[$code] = "https://noriks.com/{$code}/wp-json/noriks/v1/abandoned-carts?key=n0r1k5-c4rt-4cc355";
    }
    
    $callData = loadCallData();
    $allCarts = [];
    $responses = curlMultiGet($endpoints);
    
    $completedEmails = [];
    foreach ($stores as $code => $config) {
        $completedEmails[$code] = getCompletedOrderEmails($code);
    }
    
    foreach ($responses as $storeCode => $response) {
        $config = $stores[$storeCode] ?? null;
        if (!$config) continue;
        
        $carts = json_decode($response, true);
        if (!is_array($carts)) continue;
        
        $storeCompletedEmails = $completedEmails[$storeCode] ?? [];
        
        foreach ($carts as $cart) {
            if (!is_array($cart)) continue;
            
            $cartEmail = strtolower($cart['email'] ?? '');
            if ($cartEmail && in_array($cartEmail, $storeCompletedEmails)) {
                continue;
            }
            
            $cartId = $storeCode . '_' . ($cart['id'] ?? 'unknown');
            $savedData = $callData[$cartId] ?? [];
            
            // Skip if already converted to order
            if (($savedData['callStatus'] ?? '') === 'converted' && !empty($savedData['orderId'])) {
                continue;
            }
            
            $cartContents = [];
            $cartData = $cart['cart_contents'] ?? [];
            if (is_array($cartData)) {
                foreach ($cartData as $item) {
                    if (!is_array($item)) continue;
                    $lines = $item['_orto_lines'] ?? [];
                    $name = is_array($lines) && count($lines) > 0 ? implode(', ', $lines) : 'Product #' . ($item['product_id'] ?? '');
                    $cartContents[] = [
                        'name' => $name,
                        'quantity' => intval($item['quantity'] ?? 1),
                        'price' => floatval($item['line_total'] ?? 0),
                        'productId' => $item['product_id'] ?? null,
                        'variationId' => $item['variation_id'] ?? null
                    ];
                }
            }
            
            $fields = $cart['other_fields'] ?? [];
            
            $allCarts[] = [
                'id' => $cartId,
                'storeCode' => $storeCode,
                'storeName' => $config['name'],
                'storeFlag' => $config['flag'],
                'cartDbId' => $cart['id'] ?? null,
                'customerName' => trim(($fields['wcf_first_name'] ?? '') . ' ' . ($fields['wcf_last_name'] ?? '')) ?: 'Unknown',
                'firstName' => $fields['wcf_first_name'] ?? '',
                'lastName' => $fields['wcf_last_name'] ?? '',
                'email' => $cart['email'] ?? '',
                'phone' => $fields['wcf_phone_number'] ?? '',
                'address' => $fields['wcf_billing_address_1'] ?? '',
                'city' => trim(str_replace(',', '', $fields['wcf_location'] ?? '')),
                'postcode' => $fields['wcf_billing_postcode'] ?? '',
                'location' => ltrim($fields['wcf_location'] ?? '', ', '),
                'cartContents' => $cartContents,
                'cartValue' => floatval($cart['cart_total'] ?? 0),
                'currency' => $storeCurrencies[$storeCode] ?? 'EUR',
                'abandonedAt' => $cart['time'] ?? '',
                'status' => $cart['order_status'] ?? '',
                'callStatus' => $savedData['callStatus'] ?? 'not_called',
                'notes' => $savedData['notes'] ?? '',
                'lastUpdated' => $savedData['lastUpdated'] ?? null,
                'orderId' => $savedData['orderId'] ?? null
            ];
        }
    }
    
    usort($allCarts, function($a, $b) {
        return strtotime($b['abandonedAt'] ?: '1970-01-01') - strtotime($a['abandonedAt'] ?: '1970-01-01');
    });
    
    setCache('abandoned_carts_filtered', $allCarts);
    return $allCarts;
}

function fetchOneTimeBuyers($storeFilter = null) {
    global $stores, $storeCurrencies;
    
    $cacheKey = 'one_time_buyers_' . ($storeFilter ?: 'all');
    $cached = getCache($cacheKey, 600);
    if ($cached !== null) return $cached;
    
    $callData = loadCallData();
    $allBuyers = [];
    
    $storesToFetch = $storeFilter ? [$storeFilter => $stores[$storeFilter]] : $stores;
    
    foreach ($storesToFetch as $storeCode => $config) {
        if (!$config) continue;
        
        $customers = wcApiRequest($storeCode, 'customers', [
            'per_page' => 100,
            'orderby' => 'registered_date',
            'order' => 'desc',
            'role' => 'customer'
        ]);
        
        if (!is_array($customers)) continue;
        
        foreach ($customers as $customer) {
            $orderCount = intval($customer['orders_count'] ?? 0);
            if ($orderCount !== 1) continue;
            
            $customerId = $storeCode . '_customer_' . ($customer['id'] ?? 'unknown');
            $savedData = $callData[$customerId] ?? [];
            
            $allBuyers[] = [
                'id' => $customerId,
                'storeCode' => $storeCode,
                'storeName' => $config['name'],
                'storeFlag' => $config['flag'],
                'customerId' => $customer['id'] ?? null,
                'customerName' => trim(($customer['first_name'] ?? '') . ' ' . ($customer['last_name'] ?? '')) ?: 'Unknown',
                'email' => $customer['email'] ?? '',
                'phone' => $customer['billing']['phone'] ?? '',
                'location' => trim(($customer['billing']['city'] ?? '') . ', ' . ($customer['billing']['country'] ?? ''), ', '),
                'totalSpent' => floatval($customer['total_spent'] ?? 0),
                'currency' => $storeCurrencies[$storeCode] ?? 'EUR',
                'registeredAt' => $customer['date_created'] ?? '',
                'callStatus' => $savedData['callStatus'] ?? 'not_called',
                'notes' => $savedData['notes'] ?? ''
            ];
        }
    }
    
    usort($allBuyers, function($a, $b) {
        return strtotime($b['registeredAt'] ?: '1970-01-01') - strtotime($a['registeredAt'] ?: '1970-01-01');
    });
    
    setCache($cacheKey, $allBuyers);
    return $allBuyers;
}

function fetchPendingOrders() {
    global $stores;
    
    $cached = getCache('pending_orders', 300);
    if ($cached !== null) return $cached;
    
    $callData = loadCallData();
    $allOrders = [];
    
    $mh = curl_multi_init();
    $handles = [];
    
    foreach ($stores as $storeCode => $config) {
        $params = http_build_query(['status' => 'pending,cancelled,failed,on-hold', 'per_page' => 50, 'orderby' => 'date', 'order' => 'desc']);
        $url = $config['url'] . '/wp-json/wc/v3/orders?' . $params;
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_USERPWD => $config['ck'] . ':' . $config['cs'],
            CURLOPT_SSL_VERIFYPEER => true
        ]);
        curl_multi_add_handle($mh, $ch);
        $handles[$storeCode] = $ch;
    }
    
    $running = null;
    do { curl_multi_exec($mh, $running); curl_multi_select($mh); } while ($running > 0);
    
    foreach ($handles as $storeCode => $ch) {
        $response = curl_multi_getcontent($ch);
        $orders = json_decode($response, true);
        curl_multi_remove_handle($mh, $ch);
        curl_close($ch);
        
        if (!is_array($orders)) continue;
        
        $config = $stores[$storeCode];
        foreach ($orders as $order) {
            if (!is_array($order)) continue;
            
            $orderId = $storeCode . '_order_' . ($order['id'] ?? 'unknown');
            $savedData = $callData[$orderId] ?? [];
            $billing = $order['billing'] ?? [];
            
            $items = [];
            foreach (($order['line_items'] ?? []) as $item) {
                $items[] = ['name' => $item['name'] ?? '', 'quantity' => $item['quantity'] ?? 1, 'price' => $item['total'] ?? '0'];
            }
            
            $allOrders[] = [
                'id' => $orderId,
                'storeCode' => $storeCode,
                'storeName' => $config['name'],
                'storeFlag' => $config['flag'],
                'orderId' => $order['id'] ?? null,
                'customerName' => trim(($billing['first_name'] ?? '') . ' ' . ($billing['last_name'] ?? '')) ?: 'Unknown',
                'email' => $billing['email'] ?? '',
                'phone' => $billing['phone'] ?? '',
                'location' => trim(($billing['city'] ?? '') . ', ' . ($billing['country'] ?? ''), ', '),
                'orderStatus' => $order['status'] ?? '',
                'orderTotal' => floatval($order['total'] ?? 0),
                'currency' => $order['currency'] ?? 'EUR',
                'createdAt' => $order['date_created'] ?? '',
                'items' => $items,
                'callStatus' => $savedData['callStatus'] ?? 'not_called',
                'notes' => $savedData['notes'] ?? ''
            ];
        }
    }
    curl_multi_close($mh);
    
    usort($allOrders, function($a, $b) {
        return strtotime($b['createdAt'] ?: '1970-01-01') - strtotime($a['createdAt'] ?: '1970-01-01');
    });
    
    setCache('pending_orders', $allOrders);
    return $allOrders;
}

// CREATE ORDER FROM ABANDONED CART
function createOrderFromCart($cartId, $agentName = 'Call Center') {
    global $stores, $storeCurrencies;
    
    // Find the cart
    $carts = fetchAbandonedCarts();
    $cart = null;
    foreach ($carts as $c) {
        if ($c['id'] === $cartId) {
            $cart = $c;
            break;
        }
    }
    
    if (!$cart) {
        return ['error' => 'Cart not found'];
    }
    
    $storeCode = $cart['storeCode'];
    $config = $stores[$storeCode] ?? null;
    if (!$config) {
        return ['error' => 'Invalid store'];
    }
    
    // Build order data
    $lineItems = [];
    foreach ($cart['cartContents'] as $item) {
        $lineItem = [
            'product_id' => $item['productId'],
            'quantity' => $item['quantity']
        ];
        if (!empty($item['variationId'])) {
            $lineItem['variation_id'] = $item['variationId'];
        }
        $lineItems[] = $lineItem;
    }
    
    // Parse name
    $nameParts = explode(' ', $cart['customerName'], 2);
    $firstName = $cart['firstName'] ?: ($nameParts[0] ?? '');
    $lastName = $cart['lastName'] ?: ($nameParts[1] ?? '');
    
    $orderData = [
        'payment_method' => 'cod',
        'payment_method_title' => 'Cash on Delivery',
        'set_paid' => false,
        'status' => 'processing',
        'billing' => [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $cart['email'],
            'phone' => $cart['phone'],
            'address_1' => $cart['address'],
            'city' => $cart['city'],
            'postcode' => $cart['postcode'],
            'country' => strtoupper(substr($storeCode, 0, 2))
        ],
        'shipping' => [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'address_1' => $cart['address'],
            'city' => $cart['city'],
            'postcode' => $cart['postcode'],
            'country' => strtoupper(substr($storeCode, 0, 2))
        ],
        'line_items' => $lineItems,
        'meta_data' => [
            ['key' => '_call_center', 'value' => 'yes'],
            ['key' => '_call_center_agent', 'value' => $agentName],
            ['key' => '_call_center_date', 'value' => date('Y-m-d H:i:s')],
            ['key' => '_abandoned_cart_id', 'value' => $cart['cartDbId']]
        ],
        'customer_note' => 'Order created via Call Center by ' . $agentName
    ];
    
    // Create order via WooCommerce API
    $result = wcApiRequest($storeCode, 'orders', [], 'POST', $orderData);
    
    if (isset($result['error'])) {
        return $result;
    }
    
    if (!isset($result['id'])) {
        return ['error' => 'Failed to create order'];
    }
    
    // Update call data to mark as converted
    $callData = loadCallData();
    $callData[$cartId] = [
        'callStatus' => 'converted',
        'notes' => ($callData[$cartId]['notes'] ?? '') . "\nOrder #{$result['id']} created by {$agentName}",
        'lastUpdated' => date('c'),
        'orderId' => $result['id']
    ];
    saveCallData($callData);
    
    // Clear cache
    global $cacheDir;
    if (is_dir($cacheDir)) array_map('unlink', glob($cacheDir . '*.json'));
    
    return [
        'success' => true,
        'orderId' => $result['id'],
        'orderNumber' => $result['number'] ?? $result['id'],
        'orderTotal' => $result['total'],
        'orderStatus' => $result['status'],
        'storeUrl' => $config['url'] . '/wp-admin/post.php?post=' . $result['id'] . '&action=edit'
    ];
}

// Router
$action = $_GET['action'] ?? '';
$store = $_GET['store'] ?? null;

try {
    switch ($action) {
        case 'abandoned-carts':
            $carts = fetchAbandonedCarts();
            if ($store) $carts = array_values(array_filter($carts, fn($c) => $c['storeCode'] === $store));
            echo json_encode($carts);
            break;
            
        case 'one-time-buyers':
            echo json_encode(fetchOneTimeBuyers($store));
            break;
            
        case 'pending-orders':
            $orders = fetchPendingOrders();
            if ($store) $orders = array_values(array_filter($orders, fn($o) => $o['storeCode'] === $store));
            echo json_encode($orders);
            break;
            
        case 'stores':
            $storeList = [];
            foreach ($stores as $code => $config) {
                $storeList[] = ['code' => $code, 'name' => $config['name'], 'flag' => $config['flag']];
            }
            echo json_encode($storeList);
            break;
            
        case 'create-order':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(['error' => 'POST required']);
                break;
            }
            $input = json_decode(file_get_contents('php://input'), true);
            $cartId = $input['cartId'] ?? '';
            $agent = $input['agent'] ?? 'Call Center';
            
            if (!$cartId) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing cartId']);
                break;
            }
            
            $result = createOrderFromCart($cartId, $agent);
            if (isset($result['error'])) {
                http_response_code(400);
            }
            echo json_encode($result);
            break;
            
        case 'update-status':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(['error' => 'POST required']);
                break;
            }
            $input = json_decode(file_get_contents('php://input'), true);
            $id = $input['id'] ?? '';
            if (!$id) { http_response_code(400); echo json_encode(['error' => 'Missing ID']); break; }
            
            $callData = loadCallData();
            $callData[$id] = [
                'callStatus' => $input['callStatus'] ?? 'not_called',
                'notes' => $input['notes'] ?? '',
                'lastUpdated' => date('c'),
                'orderId' => $callData[$id]['orderId'] ?? null
            ];
            saveCallData($callData);
            echo json_encode(['success' => true]);
            break;
            
        case 'clear-cache':
            global $cacheDir;
            if (is_dir($cacheDir)) array_map('unlink', glob($cacheDir . '*.json'));
            echo json_encode(['success' => true]);
            break;
            
        case 'login':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['error' => 'POST required']); break; }
            $input = json_decode(file_get_contents('php://input'), true);
            $users = [
                'noriks' => ['password' => 'noriks', 'role' => 'admin', 'countries' => ['all']],
                'hr' => ['password' => 'hr', 'role' => 'agent', 'countries' => ['hr']]
            ];
            $user = $users[$input['username'] ?? ''] ?? null;
            if ($user && $user['password'] === ($input['password'] ?? '')) {
                echo json_encode(['success' => true, 'user' => ['username' => $input['username'], 'role' => $user['role'], 'countries' => $user['countries']]]);
            } else {
                http_response_code(401);
                echo json_encode(['success' => false, 'error' => 'Invalid credentials']);
            }
            break;
            
        case 'health':
            echo json_encode(['status' => 'ok', 'version' => '3.0', 'timestamp' => date('c')]);
            break;
            
        default:
            echo json_encode(['error' => 'Unknown action', 'available' => ['abandoned-carts', 'one-time-buyers', 'pending-orders', 'stores', 'create-order', 'update-status', 'clear-cache', 'login', 'health']]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
