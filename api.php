<?php
/**
 * Noriks Call Center - PHP API
 * For cPanel hosting - OPTIMIZED with parallel loading + caching
 */

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// WooCommerce API credentials for all stores
$stores = [
    'hr' => [
        'name' => 'Croatia',
        'flag' => '🇭🇷',
        'url' => 'https://noriks.com/hr',
        'ck' => 'ck_d73881b20fd65125fb071414b8d54af7681549e3',
        'cs' => 'cs_e024298df41e4352d90e006d2ec42a5b341c1ce5'
    ],
    'cz' => [
        'name' => 'Czech',
        'flag' => '🇨🇿',
        'url' => 'https://noriks.com/cz',
        'ck' => 'ck_396d624acec5f7a46dfcfa7d2a74b95c82b38962',
        'cs' => 'cs_2a69c7ad4a4d118a2b8abdf44abdd058c9be9115'
    ],
    'pl' => [
        'name' => 'Poland',
        'flag' => '🇵🇱',
        'url' => 'https://noriks.com/pl',
        'ck' => 'ck_8fd83582ada887d0e586a04bf870d43634ca8f2c',
        'cs' => 'cs_f1bf98e46a3ae0623c5f2f9fcf7c2478240c5115'
    ],
    'gr' => [
        'name' => 'Greece',
        'flag' => '🇬🇷',
        'url' => 'https://noriks.com/gr',
        'ck' => 'ck_2595568b83966151e08031e42388dd1c34307107',
        'cs' => 'cs_dbd091b4fc11091638f8ec4c838483be32cfb15b'
    ],
    'sk' => [
        'name' => 'Slovakia',
        'flag' => '🇸🇰',
        'url' => 'https://noriks.com/sk',
        'ck' => 'ck_1abaeb006bb9039da0ad40f00ab674067ff1d978',
        'cs' => 'cs_32b33bc2716b07a738fb18eb377a767ef60edfe7'
    ],
    'it' => [
        'name' => 'Italy',
        'flag' => '🇮🇹',
        'url' => 'https://noriks.com/it',
        'ck' => 'ck_84a1e1425710ff9eeed69b100ed9ac445efc39e2',
        'cs' => 'cs_81d25dcb0371773387da4d30482afc7ce83d1b3e'
    ],
    'hu' => [
        'name' => 'Hungary',
        'flag' => '🇭🇺',
        'url' => 'https://noriks.com/hu',
        'ck' => 'ck_e591c2a0bf8c7a59ec5893e03adde3c760fbdaae',
        'cs' => 'cs_d84113ee7a446322d191be0725c0c92883c984c3'
    ]
];

// Currency per store
$storeCurrencies = [
    'hr' => 'EUR', 'cz' => 'CZK', 'pl' => 'PLN',
    'sk' => 'EUR', 'hu' => 'HUF', 'gr' => 'EUR', 'it' => 'EUR'
];

// Klaviyo API Key
$KLAVIYO_API_KEY = 'pk_961349939ac712880db8078dd802f74082';

// Data storage
$dataFile = __DIR__ . '/data/call_data.json';
$cacheDir = __DIR__ . '/data/cache/';

function loadCallData() {
    global $dataFile;
    if (file_exists($dataFile)) {
        $content = file_get_contents($dataFile);
        return $content ? (json_decode($content, true) ?: []) : [];
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
    if (!is_dir($cacheDir)) mkdir($cacheDir, 0755, true);
    $file = $cacheDir . md5($key) . '.json';
    if (file_exists($file) && (time() - filemtime($file)) < $maxAge) {
        return json_decode(file_get_contents($file), true);
    }
    return null;
}

function setCache($key, $data) {
    global $cacheDir;
    if (!is_dir($cacheDir)) mkdir($cacheDir, 0755, true);
    $file = $cacheDir . md5($key) . '.json';
    file_put_contents($file, json_encode($data));
}

// Parallel curl for multiple URLs
function curlMultiGet($urls) {
    $mh = curl_multi_init();
    $handles = [];
    
    foreach ($urls as $key => $url) {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_SSL_VERIFYPEER => true
        ]);
        curl_multi_add_handle($mh, $ch);
        $handles[$key] = $ch;
    }
    
    $running = null;
    do {
        curl_multi_exec($mh, $running);
        curl_multi_select($mh);
    } while ($running > 0);
    
    $results = [];
    foreach ($handles as $key => $ch) {
        $results[$key] = curl_multi_getcontent($ch);
        curl_multi_remove_handle($mh, $ch);
        curl_close($ch);
    }
    
    curl_multi_close($mh);
    return $results;
}

function fetchAbandonedCarts($storeFilter = null) {
    global $stores, $storeCurrencies;
    
    // Check cache first (5 min)
    $cacheKey = 'abandoned_carts_' . ($storeFilter ?: 'all');
    $cached = getCache($cacheKey, 300);
    if ($cached !== null) {
        return $cached;
    }
    
    $abandonedCartEndpoints = [];
    foreach ($stores as $code => $config) {
        if ($storeFilter && $storeFilter !== $code) continue;
        $abandonedCartEndpoints[$code] = "https://noriks.com/{$code}/wp-json/noriks/v1/abandoned-carts?key=n0r1k5-c4rt-4cc355";
    }
    
    $callData = loadCallData();
    $allCarts = [];
    
    // Parallel fetch all stores
    $responses = curlMultiGet($abandonedCartEndpoints);
    
    foreach ($responses as $storeCode => $response) {
        $config = $stores[$storeCode] ?? null;
        if (!$config) continue;
        
        $carts = json_decode($response, true);
        if (!is_array($carts)) continue;
        
        foreach ($carts as $cart) {
            if (!is_array($cart)) continue;
            
            $cartId = $storeCode . '_' . ($cart['id'] ?? 'unknown');
            $savedData = $callData[$cartId] ?? [];
            
            // Parse cart contents
            $cartContents = [];
            $cartData = $cart['cart_contents'] ?? [];
            if (is_array($cartData)) {
                foreach ($cartData as $key => $item) {
                    if (!is_array($item)) continue;
                    $lines = $item['_orto_lines'] ?? [];
                    $name = is_array($lines) && count($lines) > 0 
                        ? implode(', ', $lines) 
                        : 'Product #' . ($item['product_id'] ?? 'unknown');
                    
                    $cartContents[] = [
                        'name' => $name,
                        'quantity' => intval($item['quantity'] ?? 1),
                        'price' => floatval($item['line_total'] ?? 0),
                        'productId' => $item['product_id'] ?? null
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
                'email' => $cart['email'] ?? '',
                'phone' => $fields['wcf_phone_number'] ?? '',
                'location' => ltrim($fields['wcf_location'] ?? '', ', '),
                'cartContents' => $cartContents,
                'cartValue' => floatval($cart['cart_total'] ?? 0),
                'currency' => $storeCurrencies[$storeCode] ?? 'EUR',
                'abandonedAt' => $cart['time'] ?? '',
                'status' => $cart['order_status'] ?? '',
                'callStatus' => $savedData['callStatus'] ?? 'not_called',
                'notes' => $savedData['notes'] ?? '',
                'lastUpdated' => $savedData['lastUpdated'] ?? null
            ];
        }
    }
    
    // Sort by date (newest first)
    usort($allCarts, function($a, $b) {
        return strtotime($b['abandonedAt'] ?: '1970-01-01') - strtotime($a['abandonedAt'] ?: '1970-01-01');
    });
    
    // Cache results
    setCache($cacheKey, $allCarts);
    
    return $allCarts;
}

function fetchSuppressedProfiles() {
    global $KLAVIYO_API_KEY;
    
    // Check cache (5 min)
    $cached = getCache('suppressed_profiles', 300);
    if ($cached !== null) return $cached;
    
    $callData = loadCallData();
    $allProfiles = [];
    $cursor = null;
    $pageCount = 0;
    
    do {
        $url = 'https://a.klaviyo.com/api/profiles/?filter=not(equals(subscriptions.email.marketing.consent,"SUBSCRIBED"))&page[size]=100';
        if ($cursor) $url .= '&page[cursor]=' . urlencode($cursor);
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTPHEADER => [
                'Authorization: Klaviyo-API-Key ' . $KLAVIYO_API_KEY,
                'revision: 2024-02-15'
            ]
        ]);
        $response = curl_exec($ch);
        curl_close($ch);
        
        $data = json_decode($response, true);
        if (!$data || empty($data['data'])) break;
        
        foreach ($data['data'] as $profile) {
            $allProfiles[] = $profile;
        }
        
        $cursor = null;
        if (!empty($data['links']['next'])) {
            parse_str(parse_url($data['links']['next'], PHP_URL_QUERY), $params);
            $cursor = $params['page']['cursor'] ?? ($params['page[cursor]'] ?? null);
        }
        $pageCount++;
    } while ($cursor && $pageCount < 100);
    
    $profiles = array_map(function($profile) use ($callData) {
        $profileId = 'klaviyo_' . $profile['id'];
        $savedData = $callData[$profileId] ?? [];
        $attrs = $profile['attributes'] ?? [];
        $suppression = $attrs['subscriptions']['email']['marketing']['suppression'] ?? [];
        
        return [
            'id' => $profileId,
            'email' => $attrs['email'] ?? '',
            'firstName' => $attrs['first_name'] ?? '',
            'lastName' => $attrs['last_name'] ?? '',
            'phone' => $attrs['phone_number'] ?? '',
            'suppressionReason' => $suppression['reason'] ?? 'unsubscribed',
            'suppressedAt' => $suppression['timestamp'] ?? ($attrs['updated'] ?? ''),
            'callStatus' => $savedData['callStatus'] ?? 'not_called',
            'notes' => $savedData['notes'] ?? ''
        ];
    }, $allProfiles);
    
    usort($profiles, function($a, $b) {
        return strtotime($b['suppressedAt'] ?: '1970-01-01') - strtotime($a['suppressedAt'] ?: '1970-01-01');
    });
    
    setCache('suppressed_profiles', $profiles);
    return $profiles;
}

function fetchPendingOrders($storeFilter = null) {
    global $stores;
    
    $cacheKey = 'pending_orders_' . ($storeFilter ?: 'all');
    $cached = getCache($cacheKey, 300);
    if ($cached !== null) return $cached;
    
    $callData = loadCallData();
    $allOrders = [];
    
    // Build URLs for parallel fetch
    $urls = [];
    foreach ($stores as $storeCode => $config) {
        if ($storeFilter && $storeFilter !== $storeCode) continue;
        $params = http_build_query([
            'status' => 'pending,cancelled,failed,on-hold',
            'per_page' => 50,
            'orderby' => 'date',
            'order' => 'desc'
        ]);
        $urls[$storeCode] = $config['url'] . '/wp-json/wc/v3/orders?' . $params;
    }
    
    // Parallel fetch with auth
    $mh = curl_multi_init();
    $handles = [];
    
    foreach ($urls as $storeCode => $url) {
        $config = $stores[$storeCode];
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_USERPWD => $config['ck'] . ':' . $config['cs'],
            CURLOPT_SSL_VERIFYPEER => true
        ]);
        curl_multi_add_handle($mh, $ch);
        $handles[$storeCode] = $ch;
    }
    
    $running = null;
    do {
        curl_multi_exec($mh, $running);
        curl_multi_select($mh);
    } while ($running > 0);
    
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
                if (!is_array($item)) continue;
                $items[] = [
                    'name' => $item['name'] ?? '',
                    'quantity' => $item['quantity'] ?? 1,
                    'price' => $item['total'] ?? '0'
                ];
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
                'notes' => $savedData['notes'] ?? '',
                'lastUpdated' => $savedData['lastUpdated'] ?? null
            ];
        }
    }
    curl_multi_close($mh);
    
    usort($allOrders, function($a, $b) {
        return strtotime($b['createdAt'] ?: '1970-01-01') - strtotime($a['createdAt'] ?: '1970-01-01');
    });
    
    setCache($cacheKey, $allOrders);
    return $allOrders;
}

// Router
$action = $_GET['action'] ?? '';
$store = $_GET['store'] ?? null; // Store filter

try {
    switch ($action) {
        case 'abandoned-carts':
            echo json_encode(fetchAbandonedCarts($store));
            break;
            
        case 'suppressed-profiles':
            echo json_encode(fetchSuppressedProfiles());
            break;
            
        case 'pending-orders':
            echo json_encode(fetchPendingOrders($store));
            break;
            
        case 'stores':
            $storeList = [];
            foreach ($stores as $code => $config) {
                $storeList[] = [
                    'code' => $code,
                    'name' => $config['name'],
                    'flag' => $config['flag']
                ];
            }
            echo json_encode($storeList);
            break;
            
        case 'update-status':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(['error' => 'POST required']);
                break;
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            $id = $input['id'] ?? '';
            
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing ID']);
                break;
            }
            
            $callData = loadCallData();
            $callData[$id] = [
                'callStatus' => $input['callStatus'] ?? 'not_called',
                'notes' => $input['notes'] ?? '',
                'lastUpdated' => date('c')
            ];
            saveCallData($callData);
            
            echo json_encode(['success' => true, 'data' => $callData[$id]]);
            break;
            
        case 'clear-cache':
            global $cacheDir;
            if (is_dir($cacheDir)) {
                array_map('unlink', glob($cacheDir . '*.json'));
            }
            echo json_encode(['success' => true, 'message' => 'Cache cleared']);
            break;
            
        case 'login':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(['error' => 'POST required']);
                break;
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            $username = $input['username'] ?? '';
            $password = $input['password'] ?? '';
            
            $users = [
                'noriks' => ['password' => 'noriks', 'role' => 'admin', 'countries' => ['all']],
                'hr' => ['password' => 'hr', 'role' => 'agent', 'countries' => ['hr']]
            ];
            
            $user = $users[$username] ?? null;
            if ($user && $user['password'] === $password) {
                echo json_encode([
                    'success' => true,
                    'user' => [
                        'username' => $username,
                        'role' => $user['role'],
                        'countries' => $user['countries']
                    ]
                ]);
            } else {
                http_response_code(401);
                echo json_encode(['success' => false, 'error' => 'Invalid credentials']);
            }
            break;
            
        case 'health':
            echo json_encode([
                'status' => 'ok',
                'timestamp' => date('c'),
                'php_version' => PHP_VERSION,
                'cache_enabled' => true
            ]);
            break;
            
        default:
            echo json_encode([
                'error' => 'Unknown action',
                'available' => ['abandoned-carts', 'suppressed-profiles', 'pending-orders', 'stores', 'update-status', 'clear-cache', 'login', 'health'],
                'params' => ['store' => 'Filter by store code (hr, cz, pl, etc.)']
            ]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error', 'message' => $e->getMessage()]);
}
