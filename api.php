<?php
/**
 * Noriks Call Center - PHP API v4
 * + Enhanced order creation with editable items, prices, free shipping
 * + SMS queue support (MetaKocka ready - NOT sending!)
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
$storeCountryCodes = ['hr' => 'HR', 'cz' => 'CZ', 'pl' => 'PL', 'sk' => 'SK', 'hu' => 'HU', 'gr' => 'GR', 'it' => 'IT'];

$dataFile = __DIR__ . '/data/call_data.json';
$smsQueueFile = __DIR__ . '/data/sms_queue.json';
$cacheDir = __DIR__ . '/data/cache/';

// MetaKocka config (for SMS)
$metakocka = [
    'company_id' => 6371,
    'secret_key' => 'ee759602-961d-4431-ac64-0725ae8d9665',
    'api_url' => 'https://main.metakocka.si/rest/eshop/send_message'
];

$smsSettingsFile = __DIR__ . '/data/sms-settings.json';
$agentsFile = __DIR__ . '/data/agents.json';
$callLogsFile = __DIR__ . '/data/call-logs.json';

// ========== AGENT MANAGEMENT FUNCTIONS ==========
function loadAgents() {
    global $agentsFile;
    if (file_exists($agentsFile)) {
        return json_decode(file_get_contents($agentsFile), true) ?: ['users' => []];
    }
    // Default admin user if file doesn't exist
    return [
        'users' => [
            [
                'id' => 'admin_1',
                'username' => 'noriks',
                'password' => 'noriks2024',
                'role' => 'admin',
                'countries' => ['all'],
                'createdAt' => date('c'),
                'active' => true
            ]
        ]
    ];
}

function saveAgents($data) {
    global $agentsFile;
    $dir = dirname($agentsFile);
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    file_put_contents($agentsFile, json_encode($data, JSON_PRETTY_PRINT));
}

function loadSmsSettings() {
    global $smsSettingsFile;
    if (file_exists($smsSettingsFile)) {
        return json_decode(file_get_contents($smsSettingsFile), true) ?: [];
    }
    return [
        'providers' => [
            'hr' => ['eshop_sync_id' => '', 'enabled' => false, 'lastTest' => null],
            'cz' => ['eshop_sync_id' => '', 'enabled' => false, 'lastTest' => null],
            'pl' => ['eshop_sync_id' => '', 'enabled' => false, 'lastTest' => null],
            'gr' => ['eshop_sync_id' => '', 'enabled' => false, 'lastTest' => null],
            'sk' => ['eshop_sync_id' => '', 'enabled' => false, 'lastTest' => null],
            'it' => ['eshop_sync_id' => '', 'enabled' => false, 'lastTest' => null],
            'hu' => ['eshop_sync_id' => '', 'enabled' => false, 'lastTest' => null]
        ]
    ];
}

function saveSmsSettings($data) {
    global $smsSettingsFile;
    $dir = dirname($smsSettingsFile);
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    file_put_contents($smsSettingsFile, json_encode($data, JSON_PRETTY_PRINT));
}

// ========== CALL LOGS FUNCTIONS ==========
function loadCallLogs() {
    global $callLogsFile;
    if (file_exists($callLogsFile)) {
        $data = json_decode(file_get_contents($callLogsFile), true);
        return $data['logs'] ?? [];
    }
    return [];
}

function saveCallLogs($logs) {
    global $callLogsFile;
    $dir = dirname($callLogsFile);
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    file_put_contents($callLogsFile, json_encode(['logs' => $logs], JSON_PRETTY_PRINT));
}

function addCallLog($data) {
    $logs = loadCallLogs();
    
    $logEntry = [
        'id' => 'call_' . time() . '_' . rand(1000, 9999),
        'customerId' => $data['customerId'] ?? '',
        'storeCode' => $data['storeCode'] ?? '',
        'status' => $data['status'] ?? 'not_called',
        'notes' => $data['notes'] ?? '',
        'duration' => $data['duration'] ?? null,
        'callbackAt' => $data['callbackAt'] ?? null,
        'agentId' => $data['agentId'] ?? 'unknown',
        'createdAt' => date('c')
    ];
    
    $logs[] = $logEntry;
    saveCallLogs($logs);
    
    // Update the customer's current call status based on latest log
    updateCustomerCallStatus($data['customerId'], $data['status'], $data['notes'] ?? '');
    
    return ['success' => true, 'id' => $logEntry['id'], 'log' => $logEntry];
}

function updateCustomerCallStatus($customerId, $status, $notes = '') {
    $callData = loadCallData();
    $callData[$customerId] = [
        'callStatus' => $status,
        'notes' => $notes,
        'lastUpdated' => date('c'),
        'orderId' => $callData[$customerId]['orderId'] ?? null
    ];
    saveCallData($callData);
}

function getCallLogsForCustomer($customerId) {
    $logs = loadCallLogs();
    return array_values(array_filter($logs, fn($log) => $log['customerId'] === $customerId));
}

function getFollowUps($agentId = null, $includeAll = false) {
    $logs = loadCallLogs();
    $today = date('Y-m-d');
    $tomorrow = date('Y-m-d', strtotime('+1 day'));
    
    $followups = array_filter($logs, function($log) use ($agentId, $includeAll, $today) {
        // Must have callback scheduled
        if (empty($log['callbackAt'])) return false;
        
        // Must be callback status
        if ($log['status'] !== 'callback') return false;
        
        // Filter by agent if specified
        if ($agentId && !$includeAll && $log['agentId'] !== $agentId) return false;
        
        // Only future or today callbacks (not past)
        $callbackDate = date('Y-m-d', strtotime($log['callbackAt']));
        return $callbackDate >= $today;
    });
    
    // Sort by callback time
    usort($followups, fn($a, $b) => strtotime($a['callbackAt']) - strtotime($b['callbackAt']));
    
    // Add customer info to each followup
    $carts = fetchAbandonedCarts();
    $enrichedFollowups = [];
    
    foreach ($followups as $followup) {
        $customer = null;
        foreach ($carts as $cart) {
            if ($cart['id'] === $followup['customerId']) {
                $customer = $cart;
                break;
            }
        }
        
        $callbackTime = strtotime($followup['callbackAt']);
        $isDue = $callbackTime <= time();
        $isToday = date('Y-m-d', $callbackTime) === $today;
        $isTomorrow = date('Y-m-d', $callbackTime) === $tomorrow;
        
        $enrichedFollowups[] = array_merge($followup, [
            'customer' => $customer ? [
                'name' => $customer['customerName'],
                'phone' => $customer['phone'],
                'email' => $customer['email'],
                'storeFlag' => $customer['storeFlag'],
                'cartValue' => $customer['cartValue'],
                'currency' => $customer['currency']
            ] : null,
            'isDue' => $isDue,
            'isToday' => $isToday,
            'isTomorrow' => $isTomorrow
        ]);
    }
    
    return $enrichedFollowups;
}

function getCallStats($filters = []) {
    $logs = loadCallLogs();
    
    // Apply date filters
    if (!empty($filters['dateFrom'])) {
        $logs = array_filter($logs, fn($l) => $l['createdAt'] >= $filters['dateFrom']);
    }
    if (!empty($filters['dateTo'])) {
        $logs = array_filter($logs, fn($l) => $l['createdAt'] <= $filters['dateTo'] . 'T23:59:59');
    }
    if (!empty($filters['storeCode'])) {
        $logs = array_filter($logs, fn($l) => $l['storeCode'] === $filters['storeCode']);
    }
    if (!empty($filters['agentId'])) {
        $logs = array_filter($logs, fn($l) => $l['agentId'] === $filters['agentId']);
    }
    
    $logs = array_values($logs);
    
    // Status counts
    $statusCounts = [];
    foreach ($logs as $log) {
        $status = $log['status'];
        $statusCounts[$status] = ($statusCounts[$status] ?? 0) + 1;
    }
    
    // Agent stats
    $agentStats = [];
    foreach ($logs as $log) {
        $agentId = $log['agentId'];
        if (!isset($agentStats[$agentId])) {
            $agentStats[$agentId] = ['calls' => 0, 'converted' => 0];
        }
        $agentStats[$agentId]['calls']++;
        if ($log['status'] === 'converted') {
            $agentStats[$agentId]['converted']++;
        }
    }
    
    // Calls by hour
    $hourlyStats = array_fill(0, 24, 0);
    foreach ($logs as $log) {
        $hour = (int)date('G', strtotime($log['createdAt']));
        $hourlyStats[$hour]++;
    }
    
    // Calls by day (last 30 days)
    $dailyStats = [];
    for ($i = 29; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-{$i} days"));
        $dailyStats[$date] = 0;
    }
    foreach ($logs as $log) {
        $date = date('Y-m-d', strtotime($log['createdAt']));
        if (isset($dailyStats[$date])) {
            $dailyStats[$date]++;
        }
    }
    
    return [
        'totalCalls' => count($logs),
        'statusCounts' => $statusCounts,
        'agentStats' => $agentStats,
        'hourlyStats' => $hourlyStats,
        'dailyStats' => $dailyStats,
        'conversionRate' => count($logs) > 0 ? round(($statusCounts['converted'] ?? 0) / count($logs) * 100, 1) : 0
    ];
}

// MetaKocka SMS Functions
function testSmsConnection($storeCode) {
    global $metakocka;
    
    $settings = loadSmsSettings();
    $eshopSyncId = $settings['providers'][$storeCode]['eshop_sync_id'] ?? '';
    
    if (empty($eshopSyncId)) {
        return ['success' => false, 'error' => 'Eshop Sync ID ni nastavljen za to državo'];
    }
    
    // Test connection by sending a test request (without actually sending SMS)
    $payload = [
        'secret_key' => $metakocka['secret_key'],
        'company_id' => $metakocka['company_id'],
        'eshop_sync_id' => $eshopSyncId,
        'test_connection' => true
    ];
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $metakocka['api_url'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_TIMEOUT => 15,
        CURLOPT_SSL_VERIFYPEER => true
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        return ['success' => false, 'error' => 'Connection error: ' . $error];
    }
    
    // Update last test timestamp
    $settings['providers'][$storeCode]['lastTest'] = date('c');
    $settings['providers'][$storeCode]['lastTestResult'] = $httpCode < 400;
    saveSmsSettings($settings);
    
    if ($httpCode >= 400) {
        $data = json_decode($response, true);
        return ['success' => false, 'error' => $data['error'] ?? 'API Error (HTTP ' . $httpCode . ')'];
    }
    
    return ['success' => true, 'message' => 'Connection OK'];
}

function sendQueuedSms($smsId) {
    global $metakocka;
    
    $queue = loadSmsQueue();
    $sms = null;
    $smsIndex = -1;
    
    foreach ($queue as $i => $item) {
        if ($item['id'] === $smsId) {
            $sms = $item;
            $smsIndex = $i;
            break;
        }
    }
    
    if (!$sms) {
        return ['success' => false, 'error' => 'SMS not found in queue'];
    }
    
    if ($sms['status'] !== 'queued') {
        return ['success' => false, 'error' => 'SMS already processed'];
    }
    
    $settings = loadSmsSettings();
    $storeCode = $sms['storeCode'];
    $eshopSyncId = $settings['providers'][$storeCode]['eshop_sync_id'] ?? '';
    
    if (empty($eshopSyncId)) {
        return ['success' => false, 'error' => 'Eshop Sync ID ni nastavljen za ' . strtoupper($storeCode)];
    }
    
    // Prepare MetaKocka SMS payload
    $payload = [
        'secret_key' => $metakocka['secret_key'],
        'company_id' => $metakocka['company_id'],
        'eshop_sync_id' => $eshopSyncId,
        'message_type' => 'SMS',
        'recipient' => $sms['recipient'],
        'message' => $sms['message']
    ];
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $metakocka['api_url'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => true
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        $queue[$smsIndex]['status'] = 'failed';
        $queue[$smsIndex]['error'] = 'Connection error: ' . $error;
        $queue[$smsIndex]['sentAt'] = date('c');
        saveSmsQueue($queue);
        return ['success' => false, 'error' => 'Connection error: ' . $error];
    }
    
    $data = json_decode($response, true);
    
    if ($httpCode >= 400) {
        $queue[$smsIndex]['status'] = 'failed';
        $queue[$smsIndex]['error'] = $data['error'] ?? 'API Error (HTTP ' . $httpCode . ')';
        $queue[$smsIndex]['sentAt'] = date('c');
        saveSmsQueue($queue);
        return ['success' => false, 'error' => $queue[$smsIndex]['error']];
    }
    
    // Success
    $queue[$smsIndex]['status'] = 'sent';
    $queue[$smsIndex]['sentAt'] = date('c');
    $queue[$smsIndex]['metakockaResponse'] = $data;
    saveSmsQueue($queue);
    
    return ['success' => true, 'message' => 'SMS sent successfully', 'smsId' => $smsId];
}

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

function loadSmsQueue() {
    global $smsQueueFile;
    if (file_exists($smsQueueFile)) {
        return json_decode(file_get_contents($smsQueueFile), true) ?: [];
    }
    return [];
}

function saveSmsQueue($data) {
    global $smsQueueFile;
    $dir = dirname($smsQueueFile);
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    file_put_contents($smsQueueFile, json_encode($data, JSON_PRETTY_PRINT));
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
    
    // Use orders API and group by email to find one-time buyers
    // (WooCommerce customers API doesn't work reliably)
    foreach ($storesToFetch as $storeCode => $config) {
        if (!$config) continue;
        
        // Fetch completed/processing orders from last 90 days
        $orders = wcApiRequest($storeCode, 'orders', [
            'per_page' => 100,
            'status' => 'processing,completed',
            'orderby' => 'date',
            'order' => 'desc',
            'after' => date('Y-m-d\TH:i:s', strtotime('-90 days'))
        ]);
        
        // Skip if not array or if it's an error response
        if (!is_array($orders) || isset($orders['error'])) continue;
        
        // Group orders by email
        $customerOrders = [];
        foreach ($orders as $order) {
            if (!is_array($order)) continue;
            
            $email = strtolower($order['billing']['email'] ?? '');
            if (empty($email)) continue;
            
            if (!isset($customerOrders[$email])) {
                $customerOrders[$email] = [
                    'orders' => [],
                    'billing' => $order['billing'] ?? [],
                    'firstOrder' => $order
                ];
            }
            $customerOrders[$email]['orders'][] = $order;
        }
        
        // Filter to customers with exactly 1 order
        foreach ($customerOrders as $email => $data) {
            if (count($data['orders']) !== 1) continue;
            
            $order = $data['firstOrder'];
            $billing = $data['billing'];
            
            $customerId = $storeCode . '_buyer_' . md5($email);
            $savedData = $callData[$customerId] ?? [];
            
            // Skip if already marked as converted
            if (($savedData['callStatus'] ?? '') === 'converted') continue;
            
            $allBuyers[] = [
                'id' => $customerId,
                'storeCode' => $storeCode,
                'storeName' => $config['name'],
                'storeFlag' => $config['flag'],
                'orderId' => $order['id'] ?? null,
                'customerName' => trim(($billing['first_name'] ?? '') . ' ' . ($billing['last_name'] ?? '')) ?: 'Unknown',
                'email' => $email,
                'phone' => $billing['phone'] ?? '',
                'location' => trim(($billing['city'] ?? '') . ', ' . ($billing['country'] ?? ''), ', '),
                'totalSpent' => floatval($order['total'] ?? 0),
                'currency' => $order['currency'] ?? $storeCurrencies[$storeCode] ?? 'EUR',
                'registeredAt' => $order['date_created'] ?? '',
                'orderStatus' => $order['status'] ?? '',
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

// ENHANCED: CREATE ORDER FROM ABANDONED CART WITH EDITABLE ITEMS
function createOrderFromCart($input) {
    global $stores, $storeCurrencies, $storeCountryCodes;
    
    $cartId = $input['cartId'] ?? '';
    $agentName = $input['agent'] ?? 'Call Center';
    $customerData = $input['customer'] ?? [];
    $items = $input['items'] ?? [];
    $freeShipping = $input['freeShipping'] ?? false;
    
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
    
    // Use edited items if provided, otherwise use original cart contents
    $lineItems = [];
    if (!empty($items)) {
        foreach ($items as $item) {
            $lineItem = [
                'product_id' => intval($item['productId']),
                'quantity' => intval($item['quantity'] ?? 1)
            ];
            if (!empty($item['variationId'])) {
                $lineItem['variation_id'] = intval($item['variationId']);
            }
            // Set custom price if different from product default
            if (isset($item['price'])) {
                $lineItem['subtotal'] = strval($item['price'] * $item['quantity']);
                $lineItem['total'] = strval($item['price'] * $item['quantity']);
            }
            $lineItems[] = $lineItem;
        }
    } else {
        // Fallback to original cart contents
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
    }
    
    if (empty($lineItems)) {
        return ['error' => 'No items in order'];
    }
    
    // Use customer data from input or fallback to cart data
    $firstName = $customerData['firstName'] ?? $cart['firstName'] ?? '';
    $lastName = $customerData['lastName'] ?? $cart['lastName'] ?? '';
    $email = $customerData['email'] ?? $cart['email'] ?? '';
    $phone = $customerData['phone'] ?? $cart['phone'] ?? '';
    $address = $customerData['address'] ?? $cart['address'] ?? '';
    $city = $customerData['city'] ?? $cart['city'] ?? '';
    $postcode = $customerData['postcode'] ?? $cart['postcode'] ?? '';
    $countryCode = $storeCountryCodes[$storeCode] ?? strtoupper(substr($storeCode, 0, 2));
    
    $orderData = [
        'payment_method' => 'cod',
        'payment_method_title' => 'Cash on Delivery',
        'set_paid' => false,
        'status' => 'processing',
        'billing' => [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'phone' => $phone,
            'address_1' => $address,
            'city' => $city,
            'postcode' => $postcode,
            'country' => $countryCode
        ],
        'shipping' => [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'address_1' => $address,
            'city' => $city,
            'postcode' => $postcode,
            'country' => $countryCode
        ],
        'line_items' => $lineItems,
        'meta_data' => [
            ['key' => '_call_center', 'value' => 'yes'],
            ['key' => '_call_center_agent', 'value' => $agentName],
            ['key' => '_call_center_date', 'value' => date('Y-m-d H:i:s')],
            ['key' => '_abandoned_cart_id', 'value' => $cart['cartDbId']],
            ['key' => '_free_shipping', 'value' => $freeShipping ? 'yes' : 'no']
        ],
        'customer_note' => 'Order created via Call Center by ' . $agentName
    ];
    
    // Add shipping line if free shipping
    if ($freeShipping) {
        $orderData['shipping_lines'] = [
            [
                'method_id' => 'free_shipping',
                'method_title' => 'Free Shipping (Call Center)',
                'total' => '0.00'
            ]
        ];
    }
    
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

// SMS QUEUE FUNCTIONS (NOT SENDING - JUST QUEUEING!)
function addSmsToQueue($data) {
    $queue = loadSmsQueue();
    
    $smsEntry = [
        'id' => time() . '_' . rand(1000, 9999),
        'date' => date('c'),
        'recipient' => $data['phone'] ?? '',
        'customerName' => $data['customerName'] ?? '',
        'storeCode' => $data['storeCode'] ?? '',
        'message' => $data['message'] ?? '',
        'status' => 'queued', // Always queued - NEVER sent automatically!
        'cartId' => $data['cartId'] ?? null,
        'addedBy' => $data['addedBy'] ?? 'system'
    ];
    
    $queue[] = $smsEntry;
    saveSmsQueue($queue);
    
    return ['success' => true, 'id' => $smsEntry['id'], 'status' => 'queued'];
}

function getSmsQueue($filters = []) {
    $queue = loadSmsQueue();
    
    // Apply filters
    if (!empty($filters['status'])) {
        $queue = array_filter($queue, fn($s) => $s['status'] === $filters['status']);
    }
    if (!empty($filters['storeCode'])) {
        $queue = array_filter($queue, fn($s) => $s['storeCode'] === $filters['storeCode']);
    }
    if (!empty($filters['dateFrom'])) {
        $queue = array_filter($queue, fn($s) => $s['date'] >= $filters['dateFrom']);
    }
    if (!empty($filters['dateTo'])) {
        $queue = array_filter($queue, fn($s) => $s['date'] <= $filters['dateTo'] . 'T23:59:59');
    }
    
    // Sort by date desc
    usort($queue, fn($a, $b) => strtotime($b['date']) - strtotime($a['date']));
    
    return array_values($queue);
}

function removeSmsFromQueue($smsId) {
    $queue = loadSmsQueue();
    $queue = array_filter($queue, fn($s) => $s['id'] !== $smsId);
    saveSmsQueue(array_values($queue));
    return ['success' => true];
}

// ========== PAKETOMATI FUNCTIONS ==========
$paketomatStatusFile = __DIR__ . '/data/paketomat-status.json';
$notificationSettingsFile = __DIR__ . '/data/notification-settings.json';
$lastSeenFile = __DIR__ . '/data/last-seen.json';

function loadPaketomatStatus() {
    global $paketomatStatusFile;
    if (file_exists($paketomatStatusFile)) {
        return json_decode(file_get_contents($paketomatStatusFile), true) ?: [];
    }
    return [];
}

function savePaketomatStatus($data) {
    global $paketomatStatusFile;
    $dir = dirname($paketomatStatusFile);
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    file_put_contents($paketomatStatusFile, json_encode($data, JSON_PRETTY_PRINT));
}

function loadNotificationSettings() {
    global $notificationSettingsFile;
    if (file_exists($notificationSettingsFile)) {
        return json_decode(file_get_contents($notificationSettingsFile), true) ?: [];
    }
    return [
        'desktopEnabled' => true,
        'soundEnabled' => true,
        'pollingInterval' => 30000
    ];
}

function saveNotificationSettings($data) {
    global $notificationSettingsFile;
    $dir = dirname($notificationSettingsFile);
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    file_put_contents($notificationSettingsFile, json_encode($data, JSON_PRETTY_PRINT));
}

function loadLastSeen() {
    global $lastSeenFile;
    if (file_exists($lastSeenFile)) {
        return json_decode(file_get_contents($lastSeenFile), true) ?: [];
    }
    return ['carts' => [], 'paketomati' => []];
}

function saveLastSeen($data) {
    global $lastSeenFile;
    $dir = dirname($lastSeenFile);
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    file_put_contents($lastSeenFile, json_encode($data, JSON_PRETTY_PRINT));
}

function fetchPaketomatOrders($filter = 'all') {
    global $stores;
    
    $cached = getCache('paketomat_orders_' . $filter, 120); // 2 min cache
    if ($cached !== null) return $cached;
    
    $statusData = loadPaketomatStatus();
    $allOrders = [];
    
    // PRAVILNA LOGIKA: Paketomat = naročilo kjer je ZADNJI delivery event eden od teh statusov
    // To pomeni, da je paket TRENUTNO v paketomatu in čaka na prevzem
    $PAKETOMAT_STATUSES = [
        "Can be picked up from GLS parcel locker",
        "Can be picked up from ParcelShop",
        "Placed in the (collection) parcel machine",
        "Parcel stored in temporary parcel machine",
        "Packet has been delivered to its destination branch and is waiting for pickup",
        "It's waiting to be collected at the Parcel Service Point",
        "Awaiting collection",
        "Accepted at an InPost branch",
        "Rerouted to parcel machine"
    ];
    
    // MetaKocka API - fetch sales orders with delivery events (max 100 per request)
    $mkUrl = 'https://main.metakocka.si/rest/eshop/v1/search';
    $mkPayload = [
        'secret_key' => 'ee759602-961d-4431-ac64-0725ae8d9665',
        'company_id' => '6371',
        'doc_type' => 'sales_order',
        'result_type' => 'doc',
        'limit' => 100,
        'return_delivery_service_events' => true
    ];
    
    $ch = curl_init($mkUrl);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS => json_encode($mkPayload),
        CURLOPT_TIMEOUT => 30
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    if ($curlError || $httpCode !== 200) {
        error_log("[Paketomati] MetaKocka API error: HTTP $httpCode, error: $curlError");
        return [];
    }
    
    $data = json_decode($response, true);
    if (!$data || !isset($data['opr_code']) || $data['opr_code'] !== '0') {
        error_log("[Paketomati] MetaKocka API error response: " . json_encode($data));
        return [];
    }
    
    $orders = $data['result'] ?? [];
    
    foreach ($orders as $order) {
        $events = $order['delivery_service_events'] ?? [];
        
        // Skip orders without delivery events
        if (empty($events)) continue;
        
        // Get the LAST (newest) event - array is chronological, last = newest
        $lastEvent = end($events);
        $lastEventStatus = $lastEvent['event_status'] ?? '';
        
        // Check if last event status is a paketomat status
        $isPaketomat = false;
        foreach ($PAKETOMAT_STATUSES as $paketStatus) {
            if (stripos($lastEventStatus, $paketStatus) !== false || $lastEventStatus === $paketStatus) {
                $isPaketomat = true;
                break;
            }
        }
        
        // If filter is not 'all_orders', only show paketomat orders
        if ($filter !== 'all_orders' && !$isPaketomat) continue;
        
        // Extract order details
        $orderId = 'mk_' . ($order['count_code'] ?? $order['mk_id'] ?? uniqid());
        $orderNumber = $order['count_code'] ?? '';
        
        // Partner (customer) info
        $partner = $order['partner'] ?? [];
        $customerName = $partner['name'] ?? '';
        $email = $partner['email'] ?? '';
        $phone = $partner['phone'] ?? $partner['phone2'] ?? '';
        
        // Address info
        $street = $partner['street'] ?? '';
        $city = $partner['city'] ?? '';
        $postcode = $partner['post_number'] ?? '';
        $country = $partner['country'] ?? '';
        
        // Delivery service info
        $deliveryService = $order['delivery_service'] ?? '';
        $trackingCode = $order['delivery_service_tracking_code'] ?? '';
        
        // Paketomat location - try to extract from delivery point or last event
        $paketomatLocation = $order['delivery_point_name'] ?? $order['delivery_service_point'] ?? '';
        if (!$paketomatLocation && $lastEvent) {
            $paketomatLocation = $lastEvent['location'] ?? $lastEventStatus;
        }
        
        // Order total and currency
        $orderTotal = floatval($order['sum_all'] ?? $order['total'] ?? 0);
        $currency = $order['currency_code'] ?? 'EUR';
        
        // Created date
        $createdAt = $order['doc_date'] ?? $order['date_created'] ?? '';
        
        // Get store code from webshop or guess from country
        $storeCode = strtolower($order['webshop'] ?? $country ?? 'si');
        $storeFlag = $stores[$storeCode]['flag'] ?? '🏳️';
        
        // Saved status
        $savedStatus = $statusData[$orderId] ?? [];
        
        $allOrders[] = [
            'id' => $orderId,
            'mkId' => $order['mk_id'] ?? null,
            'orderNumber' => $orderNumber,
            'title' => '#' . $orderNumber,
            'customerName' => $customerName,
            'email' => $email,
            'phone' => $phone,
            'deliveryService' => $deliveryService,
            'trackingCode' => $trackingCode,
            'paketomatLocation' => $paketomatLocation,
            'lastDeliveryEvent' => $lastEventStatus,
            'lastEventDate' => $lastEvent['event_date'] ?? '',
            'orderTotal' => $orderTotal,
            'currency' => $currency,
            'createdAt' => $createdAt,
            'status' => $savedStatus['status'] ?? 'not_called',
            'notes' => $savedStatus['notes'] ?? '',
            'lastUpdated' => $savedStatus['lastUpdated'] ?? null,
            'address' => $street,
            'city' => $city,
            'postcode' => $postcode,
            'country' => strtoupper($country),
            'orderStatus' => $order['status_code'] ?? '',
            'storeCode' => $storeCode,
            'storeFlag' => $storeFlag,
            'isPaketomat' => $isPaketomat
        ];
    }
    
    // Sort by last event date (newest first)
    usort($allOrders, function($a, $b) {
        $dateA = $a['lastEventDate'] ?: $a['createdAt'] ?: '1970-01-01';
        $dateB = $b['lastEventDate'] ?: $b['createdAt'] ?: '1970-01-01';
        return strtotime($dateB) - strtotime($dateA);
    });
    
    setCache('paketomat_orders_' . $filter, $allOrders);
    return $allOrders;
}

function updatePaketomatStatus($orderId, $status, $notes = '') {
    $statusData = loadPaketomatStatus();
    $statusData[$orderId] = [
        'status' => $status,
        'notes' => $notes,
        'lastUpdated' => date('c')
    ];
    savePaketomatStatus($statusData);
    
    // Clear cache
    global $cacheDir;
    if (is_dir($cacheDir)) {
        foreach (glob($cacheDir . '*paketomat*.json') as $file) {
            @unlink($file);
        }
    }
    
    return ['success' => true];
}

function pollForNewItems($userId) {
    $lastSeen = loadLastSeen();
    $userLastSeen = $lastSeen[$userId] ?? ['carts' => [], 'paketomati' => [], 'lastCheck' => null];
    
    // Fetch current data
    $carts = fetchAbandonedCarts();
    $paketomati = fetchPaketomatOrders('all');
    
    $newCarts = [];
    $newPaketomati = [];
    
    // Find new carts
    foreach ($carts as $cart) {
        if (!in_array($cart['id'], $userLastSeen['carts'])) {
            $newCarts[] = [
                'id' => $cart['id'],
                'customerName' => $cart['customerName'],
                'cartValue' => $cart['cartValue'],
                'currency' => $cart['currency'],
                'storeFlag' => $cart['storeFlag']
            ];
        }
    }
    
    // Find new paketomati
    foreach ($paketomati as $order) {
        if (!in_array($order['id'], $userLastSeen['paketomati'])) {
            $newPaketomati[] = [
                'id' => $order['id'],
                'customerName' => $order['customerName'],
                'orderTotal' => $order['orderTotal'],
                'currency' => $order['currency'],
                'paketomatLocation' => $order['paketomatLocation']
            ];
        }
    }
    
    return [
        'newCarts' => $newCarts,
        'newPaketomati' => $newPaketomati,
        'totalCarts' => count($carts),
        'totalPaketomati' => count($paketomati)
    ];
}

function markItemsSeen($userId, $cartIds, $paketomatIds) {
    $lastSeen = loadLastSeen();
    
    if (!isset($lastSeen[$userId])) {
        $lastSeen[$userId] = ['carts' => [], 'paketomati' => [], 'lastCheck' => null];
    }
    
    if (!empty($cartIds)) {
        $lastSeen[$userId]['carts'] = array_unique(array_merge($lastSeen[$userId]['carts'], $cartIds));
    }
    if (!empty($paketomatIds)) {
        $lastSeen[$userId]['paketomati'] = array_unique(array_merge($lastSeen[$userId]['paketomati'], $paketomatIds));
    }
    $lastSeen[$userId]['lastCheck'] = date('c');
    
    // Keep only last 500 IDs to prevent file bloat
    $lastSeen[$userId]['carts'] = array_slice($lastSeen[$userId]['carts'], -500);
    $lastSeen[$userId]['paketomati'] = array_slice($lastSeen[$userId]['paketomati'], -500);
    
    saveLastSeen($lastSeen);
    return ['success' => true];
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
            
            if (empty($input['cartId'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing cartId']);
                break;
            }
            
            $result = createOrderFromCart($input);
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
            
        // SMS Queue endpoints
        case 'sms-queue':
            $filters = [
                'status' => $_GET['status'] ?? null,
                'storeCode' => $_GET['storeCode'] ?? null,
                'dateFrom' => $_GET['dateFrom'] ?? null,
                'dateTo' => $_GET['dateTo'] ?? null
            ];
            echo json_encode(getSmsQueue($filters));
            break;
            
        case 'sms-add':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(['error' => 'POST required']);
                break;
            }
            $input = json_decode(file_get_contents('php://input'), true);
            echo json_encode(addSmsToQueue($input));
            break;
            
        case 'sms-remove':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(['error' => 'POST required']);
                break;
            }
            $input = json_decode(file_get_contents('php://input'), true);
            echo json_encode(removeSmsFromQueue($input['id'] ?? ''));
            break;
            
        case 'sms-settings':
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                echo json_encode(loadSmsSettings());
            } else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $input = json_decode(file_get_contents('php://input'), true);
                saveSmsSettings($input);
                echo json_encode(['success' => true]);
            }
            break;
            
        case 'sms-test-connection':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(['error' => 'POST required']);
                break;
            }
            $input = json_decode(file_get_contents('php://input'), true);
            $storeCode = $input['storeCode'] ?? '';
            if (!$storeCode || !isset($stores[$storeCode])) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid store code']);
                break;
            }
            echo json_encode(testSmsConnection($storeCode));
            break;
            
        case 'sms-send':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(['error' => 'POST required']);
                break;
            }
            $input = json_decode(file_get_contents('php://input'), true);
            $smsId = $input['id'] ?? '';
            if (!$smsId) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing SMS ID']);
                break;
            }
            echo json_encode(sendQueuedSms($smsId));
            break;
            
        case 'search-products':
            $storeCode = $_GET['store'] ?? null;
            $query = $_GET['q'] ?? '';
            
            if (!$storeCode || !isset($stores[$storeCode])) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid or missing store']);
                break;
            }
            
            if (strlen($query) < 2) {
                echo json_encode([]);
                break;
            }
            
            $cacheKey = "products_search_{$storeCode}_" . md5($query);
            $cached = getCache($cacheKey, 120);
            if ($cached !== null) {
                echo json_encode($cached);
                break;
            }
            
            // Search products via WooCommerce API - by name, SKU, and variations
            
            // 1. Search by product name
            $productsByName = wcApiRequest($storeCode, 'products', [
                'search' => $query,
                'per_page' => 20,
                'status' => 'publish'
            ]);
            
            // 2. Exact SKU match (for simple products)
            $productsBySku = wcApiRequest($storeCode, 'products', [
                'sku' => $query,
                'per_page' => 10,
                'status' => 'publish'
            ]);
            
            // 3. Search variations by SKU (variations often have unique SKUs)
            // Try fetching all products and check their variations for SKU match
            $variationParents = [];
            if (strlen($query) >= 3) {
                // Get recent variable products to check their variation SKUs
                $variableProducts = wcApiRequest($storeCode, 'products', [
                    'type' => 'variable',
                    'per_page' => 30,
                    'status' => 'publish',
                    'orderby' => 'date',
                    'order' => 'desc'
                ]);
                
                if (is_array($variableProducts) && !isset($variableProducts['error'])) {
                    foreach ($variableProducts as $vp) {
                        // Check if any variation SKU contains the query
                        $variations = wcApiRequest($storeCode, "products/{$vp['id']}/variations", ['per_page' => 50]);
                        if (is_array($variations) && !isset($variations['error'])) {
                            foreach ($variations as $var) {
                                $varSku = strtolower($var['sku'] ?? '');
                                if ($varSku && strpos($varSku, strtolower($query)) !== false) {
                                    $variationParents[$vp['id']] = $vp;
                                    break;
                                }
                            }
                        }
                    }
                }
            }
            
            // Merge results, avoiding duplicates
            $seenIds = [];
            $products = [];
            
            // Add products from name search
            if (is_array($productsByName) && !isset($productsByName['error'])) {
                foreach ($productsByName as $p) {
                    if (!isset($seenIds[$p['id']])) {
                        $seenIds[$p['id']] = true;
                        $products[] = $p;
                    }
                }
            }
            
            // Add products from SKU search (prioritize - put at beginning)
            if (is_array($productsBySku) && !isset($productsBySku['error'])) {
                $skuProducts = [];
                foreach ($productsBySku as $p) {
                    if (!isset($seenIds[$p['id']])) {
                        $seenIds[$p['id']] = true;
                        $skuProducts[] = $p;
                    }
                }
                // Put SKU matches first
                $products = array_merge($skuProducts, $products);
            }
            
            // Add variation parent products (from SKU search in variations)
            foreach ($variationParents as $vp) {
                if (!isset($seenIds[$vp['id']])) {
                    $seenIds[$vp['id']] = true;
                    $products[] = $vp;
                }
            }
            
            if (empty($products)) {
                echo json_encode([]);
                break;
            }
            
            $results = [];
            foreach ($products as $product) {
                $productData = [
                    'id' => $product['id'],
                    'name' => $product['name'],
                    'sku' => $product['sku'] ?? '',
                    'price' => floatval($product['price'] ?? 0),
                    'regularPrice' => floatval($product['regular_price'] ?? 0),
                    'salePrice' => floatval($product['sale_price'] ?? 0),
                    'image' => $product['images'][0]['src'] ?? null,
                    'type' => $product['type'] ?? 'simple',
                    'variations' => []
                ];
                
                // Fetch variations for variable products
                if ($product['type'] === 'variable' && !empty($product['variations'])) {
                    $variations = wcApiRequest($storeCode, "products/{$product['id']}/variations", ['per_page' => 50]);
                    if (is_array($variations) && !isset($variations['error'])) {
                        foreach ($variations as $var) {
                            $attrNames = [];
                            foreach (($var['attributes'] ?? []) as $attr) {
                                $attrNames[] = $attr['option'] ?? '';
                            }
                            $productData['variations'][] = [
                                'id' => $var['id'],
                                'name' => implode(' / ', array_filter($attrNames)) ?: "Variation #{$var['id']}",
                                'price' => floatval($var['price'] ?? $productData['price']),
                                'sku' => $var['sku'] ?? '',
                                'inStock' => ($var['stock_status'] ?? 'instock') === 'instock'
                            ];
                        }
                    }
                }
                
                $results[] = $productData;
            }
            
            setCache($cacheKey, $results);
            echo json_encode($results);
            break;
            
        case 'sms-templates':
            $templatesFile = __DIR__ . '/sms-templates.json';
            if (file_exists($templatesFile)) {
                $templates = json_decode(file_get_contents($templatesFile), true);
                echo json_encode($templates);
            } else {
                echo json_encode(['error' => 'Templates file not found']);
            }
            break;
            
        case 'email-templates':
            $templatesFile = __DIR__ . '/email-templates.json';
            if (file_exists($templatesFile)) {
                $templates = json_decode(file_get_contents($templatesFile), true);
                echo json_encode($templates);
            } else {
                echo json_encode(['error' => 'Email templates file not found']);
            }
            break;
            
        case 'email-templates-save':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(['error' => 'POST required']);
                break;
            }
            $input = json_decode(file_get_contents('php://input'), true);
            $templatesFile = __DIR__ . '/email-templates.json';
            file_put_contents($templatesFile, json_encode($input, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
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
            $agents = loadAgents();
            $user = null;
            foreach ($agents['users'] as $u) {
                if ($u['username'] === ($input['username'] ?? '') && $u['active'] !== false) {
                    $user = $u;
                    break;
                }
            }
            if ($user && $user['password'] === ($input['password'] ?? '')) {
                echo json_encode([
                    'success' => true, 
                    'user' => [
                        'id' => $user['id'],
                        'username' => $user['username'], 
                        'role' => $user['role'], 
                        'countries' => $user['countries']
                    ]
                ]);
            } else {
                http_response_code(401);
                echo json_encode(['success' => false, 'error' => 'Invalid credentials']);
            }
            break;
            
        // ========== AGENT MANAGEMENT ==========
        case 'agents-list':
            $agents = loadAgents();
            // Return without passwords
            $safeUsers = array_map(function($u) {
                return [
                    'id' => $u['id'],
                    'username' => $u['username'],
                    'role' => $u['role'],
                    'countries' => $u['countries'],
                    'createdAt' => $u['createdAt'] ?? null,
                    'active' => $u['active'] ?? true
                ];
            }, $agents['users']);
            echo json_encode(['users' => $safeUsers]);
            break;
            
        case 'agents-add':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['error' => 'POST required']); break; }
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (empty($input['username']) || empty($input['password'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Username and password required']);
                break;
            }
            
            $agents = loadAgents();
            
            // Check if username exists
            foreach ($agents['users'] as $u) {
                if ($u['username'] === $input['username']) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Username already exists']);
                    break 2;
                }
            }
            
            $newUser = [
                'id' => 'agent_' . time(),
                'username' => $input['username'],
                'password' => $input['password'],
                'role' => $input['role'] ?? 'agent',
                'countries' => $input['countries'] ?? ['hr'],
                'createdAt' => date('c'),
                'active' => true
            ];
            
            $agents['users'][] = $newUser;
            saveAgents($agents);
            
            echo json_encode(['success' => true, 'id' => $newUser['id']]);
            break;
            
        case 'agents-update':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['error' => 'POST required']); break; }
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (empty($input['id'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Agent ID required']);
                break;
            }
            
            $agents = loadAgents();
            $found = false;
            
            foreach ($agents['users'] as &$u) {
                if ($u['id'] === $input['id']) {
                    if (!empty($input['username'])) $u['username'] = $input['username'];
                    if (!empty($input['password'])) $u['password'] = $input['password'];
                    if (isset($input['role'])) $u['role'] = $input['role'];
                    if (isset($input['countries'])) $u['countries'] = $input['countries'];
                    if (isset($input['active'])) $u['active'] = $input['active'];
                    $found = true;
                    break;
                }
            }
            
            if (!$found) {
                http_response_code(404);
                echo json_encode(['error' => 'Agent not found']);
                break;
            }
            
            saveAgents($agents);
            echo json_encode(['success' => true]);
            break;
            
        case 'agents-delete':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['error' => 'POST required']); break; }
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (empty($input['id'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Agent ID required']);
                break;
            }
            
            // Prevent deleting last admin
            $agents = loadAgents();
            $adminCount = count(array_filter($agents['users'], fn($u) => $u['role'] === 'admin'));
            $targetUser = null;
            foreach ($agents['users'] as $u) {
                if ($u['id'] === $input['id']) {
                    $targetUser = $u;
                    break;
                }
            }
            
            if ($targetUser && $targetUser['role'] === 'admin' && $adminCount <= 1) {
                http_response_code(400);
                echo json_encode(['error' => 'Cannot delete last admin']);
                break;
            }
            
            $agents['users'] = array_values(array_filter($agents['users'], fn($u) => $u['id'] !== $input['id']));
            saveAgents($agents);
            echo json_encode(['success' => true]);
            break;
            
        // ========== CALL LOGS ENDPOINTS ==========
        case 'call-logs':
            $filters = [
                'storeCode' => $_GET['storeCode'] ?? null,
                'agentId' => $_GET['agentId'] ?? null,
                'dateFrom' => $_GET['dateFrom'] ?? null,
                'dateTo' => $_GET['dateTo'] ?? null
            ];
            $logs = loadCallLogs();
            
            // Apply filters
            if ($filters['storeCode']) {
                $logs = array_filter($logs, fn($l) => $l['storeCode'] === $filters['storeCode']);
            }
            if ($filters['agentId']) {
                $logs = array_filter($logs, fn($l) => $l['agentId'] === $filters['agentId']);
            }
            if ($filters['dateFrom']) {
                $logs = array_filter($logs, fn($l) => $l['createdAt'] >= $filters['dateFrom']);
            }
            if ($filters['dateTo']) {
                $logs = array_filter($logs, fn($l) => $l['createdAt'] <= $filters['dateTo'] . 'T23:59:59');
            }
            
            // Sort by date desc
            usort($logs, fn($a, $b) => strtotime($b['createdAt']) - strtotime($a['createdAt']));
            echo json_encode(array_values($logs));
            break;
            
        case 'call-logs-add':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(['error' => 'POST required']);
                break;
            }
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (empty($input['customerId'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Customer ID required']);
                break;
            }
            
            echo json_encode(addCallLog($input));
            break;
            
        case 'call-logs-customer':
            $customerId = $_GET['customerId'] ?? '';
            if (empty($customerId)) {
                http_response_code(400);
                echo json_encode(['error' => 'Customer ID required']);
                break;
            }
            
            $logs = getCallLogsForCustomer($customerId);
            usort($logs, fn($a, $b) => strtotime($b['createdAt']) - strtotime($a['createdAt']));
            echo json_encode($logs);
            break;
            
        case 'my-followups':
            $agentId = $_GET['agentId'] ?? null;
            $includeAll = ($_GET['all'] ?? '') === 'true';
            echo json_encode(getFollowUps($agentId, $includeAll));
            break;
            
        case 'call-stats':
            $filters = [
                'storeCode' => $_GET['storeCode'] ?? null,
                'agentId' => $_GET['agentId'] ?? null,
                'dateFrom' => $_GET['dateFrom'] ?? null,
                'dateTo' => $_GET['dateTo'] ?? null
            ];
            echo json_encode(getCallStats($filters));
            break;
            
        // ========== PAKETOMATI ENDPOINTS ==========
        case 'paketomati':
            $filter = $_GET['filter'] ?? 'all';
            echo json_encode(fetchPaketomatOrders($filter));
            break;
        
        case 'paketomati-debug':
            // Show ALL orders for debugging
            echo json_encode([
                'all_orders' => fetchPaketomatOrders('all_orders'),
                'paketomat_only' => fetchPaketomatOrders('all'),
                'cache_info' => 'Cache TTL: 60 seconds'
            ]);
            break;
            
        case 'paketomati-update':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(['error' => 'POST required']);
                break;
            }
            $input = json_decode(file_get_contents('php://input'), true);
            $orderId = $input['id'] ?? '';
            $status = $input['status'] ?? 'not_called';
            $notes = $input['notes'] ?? '';
            
            if (!$orderId) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing order ID']);
                break;
            }
            
            echo json_encode(updatePaketomatStatus($orderId, $status, $notes));
            break;
            
        // ========== NOTIFICATION ENDPOINTS ==========
        case 'notification-settings':
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                echo json_encode(loadNotificationSettings());
            } else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $input = json_decode(file_get_contents('php://input'), true);
                saveNotificationSettings($input);
                echo json_encode(['success' => true]);
            }
            break;
            
        case 'poll-new':
            $userId = $_GET['userId'] ?? 'default';
            echo json_encode(pollForNewItems($userId));
            break;
            
        case 'mark-seen':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(['error' => 'POST required']);
                break;
            }
            $input = json_decode(file_get_contents('php://input'), true);
            $userId = $input['userId'] ?? 'default';
            $cartIds = $input['cartIds'] ?? [];
            $paketomatIds = $input['paketomatIds'] ?? [];
            echo json_encode(markItemsSeen($userId, $cartIds, $paketomatIds));
            break;
            
        case 'health':
            echo json_encode([
                'status' => 'ok', 
                'version' => '7.0', 
                'timestamp' => date('c'),
                'features' => [
                    'enhanced_order_creation' => true,
                    'editable_items' => true,
                    'free_shipping' => true,
                    'sms_queue' => true,
                    'sms_sending' => true,
                    'metakocka_integration' => true,
                    'sms_settings' => true,
                    'call_logging' => true,
                    'follow_ups' => true,
                    'analytics' => true,
                    'paketomati' => true,
                    'realtime_notifications' => true
                ]
            ]);
            break;
            
        default:
            echo json_encode([
                'error' => 'Unknown action', 
                'available' => [
                    'abandoned-carts', 
                    'one-time-buyers', 
                    'pending-orders', 
                    'stores', 
                    'search-products',
                    'sms-templates',
                    'create-order', 
                    'update-status', 
                    'sms-queue',
                    'sms-add',
                    'sms-remove',
                    'sms-settings',
                    'sms-test-connection',
                    'sms-send',
                    'agents-list',
                    'agents-add',
                    'agents-update',
                    'agents-delete',
                    'call-logs',
                    'call-logs-add',
                    'call-logs-customer',
                    'my-followups',
                    'call-stats',
                    'paketomati',
                    'paketomati-update',
                    'notification-settings',
                    'poll-new',
                    'mark-seen',
                    'clear-cache', 
                    'login', 
                    'health'
                ]
            ]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
