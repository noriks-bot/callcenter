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
    'sk' => ['name' => 'Slovakia', 'flag' => '🇸🇰', 'url' => 'https://noriks.com/sk', 'ck' => 'ck_1abaeb006bb9039da0ad40f00ab674067ff1d978', 'cs' => 'cs_32b33bc2716b07a738ff18eb377a767ef60edfe7'],
    'it' => ['name' => 'Italy', 'flag' => '🇮🇹', 'url' => 'https://noriks.com/it', 'ck' => 'ck_84a1e1425710ff9eeed69b100ed9ac445efc39e2', 'cs' => 'cs_81d25dcb0371773387da4d30482afc7ce83d1b3e'],
    'hu' => ['name' => 'Hungary', 'flag' => '🇭🇺', 'url' => 'https://noriks.com/hu', 'ck' => 'ck_e591c2a0bf8c7a59ec5893e03adde3c760fbdaae', 'cs' => 'cs_d84113ee7a446322d191be0725c0c92883c984c3']
];

$storeCurrencies = ['hr' => 'EUR', 'cz' => 'CZK', 'pl' => 'PLN', 'sk' => 'EUR', 'hu' => 'HUF', 'gr' => 'EUR', 'it' => 'EUR'];
$storeCountryCodes = ['hr' => 'HR', 'cz' => 'CZ', 'pl' => 'PL', 'sk' => 'SK', 'hu' => 'HU', 'gr' => 'GR', 'it' => 'IT'];

// Phone country codes for SMS formatting (MetaKocka requires international format without +)
$phoneCountryCodes = [
    'hr' => '385',  // Croatia
    'cz' => '420',  // Czech
    'pl' => '48',   // Poland
    'gr' => '30',   // Greece
    'sk' => '421',  // Slovakia
    'it' => '39',   // Italy
    'hu' => '36',   // Hungary
    'si' => '386'   // Slovenia
];

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

// ========== PHONE NUMBER FORMATTING ==========
/**
 * Format phone number for MetaKocka SMS API
 * MetaKocka requires international format WITHOUT + prefix
 * Format: "385 98 216 102" (country code + national number with spaces)
 * 
 * Examples:
 *   098216102      → 385 98 216 102 (HR)
 *   +38598216102   → 385 98 216 102
 *   0038598216102  → 385 98 216 102
 *   38598216102    → 385 98 216 102
 */
function formatPhoneForSms($phone, $storeCode) {
    global $phoneCountryCodes;
    
    // Get country code for this store
    $countryCode = $phoneCountryCodes[$storeCode] ?? '385'; // Default to Croatia
    
    // Remove all non-digit characters except leading +
    $phone = trim($phone);
    
    // Remove + prefix
    if (substr($phone, 0, 1) === '+') {
        $phone = substr($phone, 1);
    }
    
    // Remove 00 prefix (international dialing)
    if (substr($phone, 0, 2) === '00') {
        $phone = substr($phone, 2);
    }
    
    // Remove any remaining non-digit characters (spaces, dashes, etc.)
    $phone = preg_replace('/[^0-9]/', '', $phone);
    
    // Check if already has country code
    $hasCountryCode = false;
    foreach ($GLOBALS['phoneCountryCodes'] as $code) {
        if (substr($phone, 0, strlen($code)) === $code) {
            $hasCountryCode = true;
            break;
        }
    }
    
    // If starts with 0 (local format), remove it and add country code
    if (!$hasCountryCode && substr($phone, 0, 1) === '0') {
        $phone = $countryCode . substr($phone, 1);
    } elseif (!$hasCountryCode) {
        // Number doesn't have country code and doesn't start with 0 - prepend country code
        $phone = $countryCode . $phone;
    }
    
    // MetaKocka format: +COUNTRYCODE followed by digits, no spaces
    // Format: "+306908238196" for Greece, "+38640688722" for Slovenia
    return '+' . $phone;
}

/**
 * Validate phone number for SMS
 * Returns array with 'valid' (bool) and 'error' (string if invalid)
 */
function validatePhoneForSms($phone, $storeCode) {
    global $phoneCountryCodes;
    
    if (empty(trim($phone))) {
        return ['valid' => false, 'error' => 'Telefonska številka je prazna'];
    }
    
    // Clean the phone number
    $cleaned = preg_replace('/[^0-9]/', '', $phone);
    
    // Minimum 7 digits (very short numbers exist in some countries)
    if (strlen($cleaned) < 7) {
        return ['valid' => false, 'error' => 'Telefonska številka je prekratka (min. 7 številk)'];
    }
    
    // Maximum 15 digits (E.164 standard)
    if (strlen($cleaned) > 15) {
        return ['valid' => false, 'error' => 'Telefonska številka je predolga (max. 15 številk)'];
    }
    
    // Format the number and check if it looks reasonable
    $formatted = formatPhoneForSms($phone, $storeCode);
    $countryCode = $phoneCountryCodes[$storeCode] ?? '385';
    
    // After formatting, check the national number length (varies by country)
    $digitsOnly = preg_replace('/[^0-9]/', '', $formatted);
    $nationalPart = substr($digitsOnly, strlen($countryCode));
    
    // Most mobile numbers have 8-10 digits national part
    if (strlen($nationalPart) < 6) {
        return ['valid' => false, 'error' => 'Nacionalni del številke je prekratek'];
    }
    if (strlen($nationalPart) > 12) {
        return ['valid' => false, 'error' => 'Nacionalni del številke je predolg'];
    }
    
    return ['valid' => true, 'formatted' => $formatted];
}

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
    // HARDCODED SMS eshop_sync_id - SAME FOR ALL COUNTRIES
    // IMPORTANT: Do NOT read from settings file - always use this value!
    $SMS_ESHOP_SYNC_ID = '637100000075'; // HARDCODED - use for ALL countries
    
    // ALWAYS return hardcoded ID, ignoring any cached/saved values
    // This ensures the correct SMS connection is used regardless of server cache
    return [
        'providers' => [
            'hr' => ['eshop_sync_id' => $SMS_ESHOP_SYNC_ID, 'enabled' => true, 'lastTest' => null],
            'cz' => ['eshop_sync_id' => $SMS_ESHOP_SYNC_ID, 'enabled' => true, 'lastTest' => null],
            'pl' => ['eshop_sync_id' => $SMS_ESHOP_SYNC_ID, 'enabled' => true, 'lastTest' => null],
            'gr' => ['eshop_sync_id' => $SMS_ESHOP_SYNC_ID, 'enabled' => true, 'lastTest' => null],
            'sk' => ['eshop_sync_id' => $SMS_ESHOP_SYNC_ID, 'enabled' => true, 'lastTest' => null],
            'it' => ['eshop_sync_id' => $SMS_ESHOP_SYNC_ID, 'enabled' => true, 'lastTest' => null],
            'hu' => ['eshop_sync_id' => $SMS_ESHOP_SYNC_ID, 'enabled' => true, 'lastTest' => null],
            'si' => ['eshop_sync_id' => $SMS_ESHOP_SYNC_ID, 'enabled' => true, 'lastTest' => null]
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
        
        // Must be callback or completed status
        if ($log['status'] !== 'callback' && $log['status'] !== 'completed') return false;
        
        // Filter by agent if specified
        if ($agentId && !$includeAll && $log['agentId'] !== $agentId) return false;
        
        // Only future or today callbacks (not past), unless completed
        $callbackDate = date('Y-m-d', strtotime($log['callbackAt']));
        if ($log['status'] === 'completed') {
            // Show completed from last 7 days
            $completedDate = date('Y-m-d', strtotime($log['completedAt'] ?? $log['callbackAt']));
            return $completedDate >= date('Y-m-d', strtotime('-7 days'));
        }
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
    
    // HARDCODED SMS ID - do NOT read from settings file!
    $eshopSyncId = '637100000075'; // HARDCODED - use for ALL countries
    
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

function sendQueuedSms($smsId, $overridePhone = null) {
    global $metakocka;
    
    // File-based logging for debugging
    $logFile = __DIR__ . '/data/sms-debug.log';
    $logMsg = function($msg) use ($logFile) {
        $line = '[' . date('Y-m-d H:i:s') . '] ' . $msg . "\n";
        file_put_contents($logFile, $line, FILE_APPEND);
        error_log($msg);
    };
    
    $logMsg("[SMS-SEND] ========== START smsId: $smsId ==========");
    
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
        $logMsg("[SMS-SEND] ERROR: SMS not found in queue: $smsId");
        return ['success' => false, 'error' => 'SMS not found in queue', 'debug' => 'smsId not in queue'];
    }
    
    if ($sms['status'] !== 'queued') {
        $logMsg("[SMS-SEND] ERROR: SMS already processed: $smsId (status: {$sms['status']})");
        return ['success' => false, 'error' => 'SMS already processed (status: ' . $sms['status'] . ')', 'debug' => 'already_processed'];
    }
    
    $storeCode = $sms['storeCode'];
    
    // HARDCODED SMS ID - do NOT read from settings file!
    $eshopSyncId = '637100000075'; // HARDCODED - use for ALL countries
    
    $logMsg("[SMS-SEND] Store: $storeCode, eshop_sync_id: $eshopSyncId (HARDCODED)");
    
    // Use overridden phone if provided, otherwise use the one from the queue
    $rawPhone = $overridePhone ?: $sms['recipient'];
    
    // Log if phone was overridden
    if ($overridePhone) {
        $logMsg("[SMS-SEND] Phone overridden: {$sms['recipient']} -> {$overridePhone}");
    }
    
    // Validate phone number first
    $validation = validatePhoneForSms($rawPhone, $storeCode);
    if (!$validation['valid']) {
        $logMsg("[SMS-SEND] ERROR: Invalid phone: {$rawPhone} - {$validation['error']}");
        $queue[$smsIndex]['status'] = 'failed';
        $queue[$smsIndex]['error'] = $validation['error'];
        $queue[$smsIndex]['sentAt'] = date('c');
        saveSmsQueue($queue);
        return [
            'success' => false,
            'error' => $validation['error'],
            'debug' => 'invalid_phone',
            'rawPhone' => $rawPhone
        ];
    }
    
    // Use the validated and formatted phone number
    $recipientPhone = $validation['formatted'];
    $logMsg("[SMS-SEND] Phone formatted: {$rawPhone} -> {$recipientPhone}");
    
    // Prepare MetaKocka SMS payload (correct format per API docs)
    // SENDER MUST BE "Narocilo" - ALWAYS!
    $payload = [
        'secret_key' => $metakocka['secret_key'],
        'company_id' => strval($metakocka['company_id']),
        'message_list' => [
            [
                'type' => 'sms',
                'eshop_sync_id' => $eshopSyncId,
                'sender' => 'Narocilo',
                'to_number' => $recipientPhone,
                'message' => $sms['message']
            ]
        ]
    ];
    
    $logMsg("[SMS-SEND] API URL: " . $metakocka['api_url']);
    $logMsg("[SMS-SEND] Recipient: {$recipientPhone}");
    $logMsg("[SMS-SEND] Message: " . substr($sms['message'], 0, 50) . '...');
    $logMsg("[SMS-SEND] Payload: " . json_encode($payload));
    
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
    $curlError = curl_error($ch);
    curl_close($ch);
    
    $logMsg("[SMS-SEND] HTTP Code: $httpCode");
    $logMsg("[SMS-SEND] Response: $response");
    if ($curlError) $logMsg("[SMS-SEND] CURL Error: $curlError");
    
    if ($curlError) {
        $queue[$smsIndex]['status'] = 'failed';
        $queue[$smsIndex]['error'] = 'Connection error: ' . $curlError;
        $queue[$smsIndex]['sentAt'] = date('c');
        saveSmsQueue($queue);
        return [
            'success' => false, 
            'error' => 'Connection error: ' . $curlError,
            'debug' => 'curl_error',
            'httpCode' => $httpCode
        ];
    }
    
    $data = json_decode($response, true);
    
    // Check for MetaKocka specific error response
    if (isset($data['opr_code']) && $data['opr_code'] !== '0') {
        $errorMsg = $data['opr_desc'] ?? 'MetaKocka error (code: ' . $data['opr_code'] . ')';
        $logMsg("[SMS-SEND] MK ERROR: opr_code={$data['opr_code']}, opr_desc=$errorMsg");
        
        $queue[$smsIndex]['status'] = 'failed';
        $queue[$smsIndex]['error'] = $errorMsg;
        $queue[$smsIndex]['sentAt'] = date('c');
        $queue[$smsIndex]['metakockaResponse'] = $data;
        saveSmsQueue($queue);
        
        return [
            'success' => false, 
            'error' => $errorMsg,
            'debug' => 'mk_opr_code_error',
            'metakockaResponse' => $data,
            'httpCode' => $httpCode
        ];
    }
    
    if ($httpCode >= 400) {
        $errorMsg = $data['error'] ?? $data['opr_desc'] ?? 'API Error (HTTP ' . $httpCode . ')';
        $logMsg("[SMS-SEND] HTTP ERROR: $httpCode - $errorMsg");
        
        $queue[$smsIndex]['status'] = 'failed';
        $queue[$smsIndex]['error'] = $errorMsg;
        $queue[$smsIndex]['sentAt'] = date('c');
        $queue[$smsIndex]['metakockaResponse'] = $data;
        saveSmsQueue($queue);
        
        return [
            'success' => false, 
            'error' => $errorMsg,
            'debug' => 'http_error',
            'metakockaResponse' => $data,
            'httpCode' => $httpCode
        ];
    }
    
    // Check message_list for individual message errors (MK returns opr_code=0 but message status=error)
    if (isset($data['message_list'][0]['status']) && $data['message_list'][0]['status'] === 'error') {
        $errorMsg = $data['message_list'][0]['error_desc'] ?? 'Message delivery failed';
        $logMsg("[SMS-SEND] MESSAGE ERROR: $errorMsg");
        
        $queue[$smsIndex]['status'] = 'failed';
        $queue[$smsIndex]['error'] = $errorMsg;
        $queue[$smsIndex]['sentAt'] = date('c');
        $queue[$smsIndex]['metakockaResponse'] = $data;
        saveSmsQueue($queue);
        
        return [
            'success' => false, 
            'error' => $errorMsg,
            'debug' => 'message_status_error',
            'metakockaResponse' => $data,
            'httpCode' => $httpCode
        ];
    }
    
    // Success!
    $logMsg("[SMS-SEND] ✅ SUCCESS! SMS sent to {$sms['recipient']}");
    $queue[$smsIndex]['status'] = 'sent';
    $queue[$smsIndex]['sentAt'] = date('c');
    $queue[$smsIndex]['metakockaResponse'] = $data;
    saveSmsQueue($queue);
    
    return [
        'success' => true, 
        'message' => 'SMS uspešno poslan na ' . $sms['recipient'], 
        'smsId' => $smsId,
        'recipient' => $sms['recipient'],
        'metakockaResponse' => $data,
        'httpCode' => $httpCode
    ];
}

/**
 * Send SMS directly without using queue (for manual testing)
 */
function sendDirectSms($data) {
    global $metakocka;
    
    // File-based logging
    $logFile = __DIR__ . '/data/sms-debug.log';
    $logMsg = function($msg) use ($logFile) {
        $line = '[' . date('Y-m-d H:i:s') . '] ' . $msg . "\n";
        file_put_contents($logFile, $line, FILE_APPEND);
        error_log($msg);
    };
    
    $logMsg("[SMS-DIRECT] ========== START ==========");
    
    $phone = $data['phone'] ?? '';
    $storeCode = $data['storeCode'] ?? 'hr';
    $message = $data['message'] ?? '';
    
    // HARDCODED SMS ID - do NOT read from settings file!
    $eshopSyncId = '637100000075'; // HARDCODED - use for ALL countries
    
    $logMsg("[SMS-DIRECT] Phone: $phone, Store: $storeCode, eshop_sync_id: $eshopSyncId (HARDCODED)");
    
    // Validate phone
    $validation = validatePhoneForSms($phone, $storeCode);
    if (!$validation['valid']) {
        $logMsg("[SMS-DIRECT] ERROR: Invalid phone: $phone - {$validation['error']}");
        return [
            'success' => false,
            'error' => $validation['error'],
            'debug' => 'invalid_phone'
        ];
    }
    
    // Validate message
    if (empty(trim($message))) {
        return [
            'success' => false,
            'error' => 'Sporočilo je prazno',
            'debug' => 'empty_message'
        ];
    }
    
    $recipientPhone = $validation['formatted'];
    $logMsg("[SMS-DIRECT] Phone formatted: $phone -> $recipientPhone");
    
    // Prepare MetaKocka SMS payload
    // SENDER MUST BE "Narocilo" - ALWAYS!
    $payload = [
        'secret_key' => $metakocka['secret_key'],
        'company_id' => strval($metakocka['company_id']),
        'message_list' => [
            [
                'type' => 'sms',
                'eshop_sync_id' => $eshopSyncId,
                'sender' => 'Narocilo',
                'to_number' => $recipientPhone,
                'message' => $message
            ]
        ]
    ];
    
    $logMsg("[SMS-DIRECT] API URL: " . $metakocka['api_url']);
    $logMsg("[SMS-DIRECT] Payload: " . json_encode($payload));
    
    // Make API request
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
    $curlError = curl_error($ch);
    curl_close($ch);
    
    $logMsg("[SMS-DIRECT] HTTP Code: $httpCode");
    $logMsg("[SMS-DIRECT] Response: $response");
    if ($curlError) $logMsg("[SMS-DIRECT] CURL Error: $curlError");
    
    if ($curlError) {
        return [
            'success' => false,
            'error' => 'Connection error: ' . $curlError,
            'debug' => 'curl_error'
        ];
    }
    
    $responseData = json_decode($response, true);
    
    // Check for MetaKocka error
    if (isset($responseData['opr_code']) && $responseData['opr_code'] !== '0') {
        $errorMsg = $responseData['opr_desc'] ?? 'MetaKocka error (code: ' . $responseData['opr_code'] . ')';
        $logMsg("[SMS-DIRECT] MK ERROR: opr_code={$responseData['opr_code']}, opr_desc=$errorMsg");
        return [
            'success' => false,
            'error' => $errorMsg,
            'debug' => 'mk_opr_code_error',
            'metakockaResponse' => $responseData
        ];
    }
    
    // Check individual message status
    if (isset($responseData['message_list'])) {
        foreach ($responseData['message_list'] as $msgResult) {
            if (isset($msgResult['status']) && $msgResult['status'] === 'error') {
                $errorMsg = $msgResult['error_desc'] ?? 'Napaka pri pošiljanju';
                $logMsg("[SMS-DIRECT] Message ERROR: $errorMsg");
                return [
                    'success' => false,
                    'error' => $errorMsg,
                    'debug' => 'mk_message_error',
                    'metakockaResponse' => $responseData
                ];
            }
        }
    }
    
    if ($httpCode >= 400) {
        $errorMsg = $responseData['error'] ?? 'API Error (HTTP ' . $httpCode . ')';
        $logMsg("[SMS-DIRECT] HTTP ERROR: $httpCode - $errorMsg");
        return [
            'success' => false,
            'error' => $errorMsg,
            'debug' => 'http_error',
            'httpCode' => $httpCode
        ];
    }
    
    // Success!
    $logMsg("[SMS-DIRECT] ✅ SUCCESS! SMS sent to $recipientPhone");
    
    // Log to SMS history
    $queue = loadSmsQueue();
    $queue[] = [
        'id' => 'direct_' . time() . '_' . rand(1000, 9999),
        'date' => date('c'),
        'recipient' => $recipientPhone,
        'recipientOriginal' => $phone,
        'customerName' => 'Manual Test',
        'storeCode' => $storeCode,
        'message' => $message,
        'status' => 'sent',
        'sentAt' => date('c'),
        'addedBy' => 'manual',
        'metakockaResponse' => $responseData
    ];
    saveSmsQueue($queue);
    
    return [
        'success' => true,
        'message' => 'SMS uspešno poslan na ' . $recipientPhone,
        'recipient' => $recipientPhone,
        'metakockaResponse' => $responseData,
        'httpCode' => $httpCode
    ];
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

function clearCache($key) {
    global $cacheDir;
    $file = $cacheDir . md5($key) . '.json';
    if (file_exists($file)) {
        @unlink($file);
    }
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

// Get recent orders data (emails AND phones) for conversion tracking
function getRecentOrderContacts($storeCode) {
    $cacheKey = "recent_order_contacts_{$storeCode}";
    $cached = getCache($cacheKey, 300); // 5 min cache
    if ($cached !== null) return $cached;
    
    $contacts = ['emails' => [], 'phones' => []];
    $orders = wcApiRequest($storeCode, 'orders', [
        'status' => 'processing,completed',
        'per_page' => 100,
        'after' => date('Y-m-d\TH:i:s', strtotime('-7 days'))
    ]);
    
    if (is_array($orders)) {
        foreach ($orders as $order) {
            if (!empty($order['billing']['email'])) {
                $contacts['emails'][] = strtolower($order['billing']['email']);
            }
            if (!empty($order['billing']['phone'])) {
                // Normalize phone - remove all non-digits
                $phone = preg_replace('/[^0-9]/', '', $order['billing']['phone']);
                if (strlen($phone) >= 7) {
                    $contacts['phones'][] = $phone;
                    // Also add last 9 digits for matching
                    if (strlen($phone) > 9) {
                        $contacts['phones'][] = substr($phone, -9);
                    }
                }
            }
        }
    }
    
    setCache($cacheKey, $contacts);
    return $contacts;
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
    
    // Get recent order contacts (emails + phones) for conversion tracking
    $orderContacts = [];
    foreach ($stores as $code => $config) {
        $orderContacts[$code] = getRecentOrderContacts($code);
    }
    
    foreach ($responses as $storeCode => $response) {
        $config = $stores[$storeCode] ?? null;
        if (!$config) continue;
        
        $carts = json_decode($response, true);
        if (!is_array($carts)) continue;
        
        $storeContacts = $orderContacts[$storeCode] ?? ['emails' => [], 'phones' => []];
        
        foreach ($carts as $cart) {
            if (!is_array($cart)) continue;
            
            // Skip carts that are less than 1 hour old (give customers time to complete purchase)
            $cartTime = strtotime($cart['time'] ?? '');
            $cartAgeHours = $cartTime ? (time() - $cartTime) / 3600 : 0;
            if ($cartAgeHours < 1) continue;
            
            // Check if this cart is converted (customer made an order)
            $cartEmail = strtolower($cart['email'] ?? '');
            $cartPhone = preg_replace('/[^0-9]/', '', $cart['other_fields']['wcf_phone_number'] ?? '');
            $cartPhoneLast9 = strlen($cartPhone) > 9 ? substr($cartPhone, -9) : $cartPhone;
            
            $isConverted = false;
            if ($cartEmail && in_array($cartEmail, $storeContacts['emails'])) {
                $isConverted = true;
            }
            if (!$isConverted && $cartPhone && (
                in_array($cartPhone, $storeContacts['phones']) || 
                in_array($cartPhoneLast9, $storeContacts['phones'])
            )) {
                $isConverted = true;
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
                    $productId = $item['product_id'] ?? null;
                    $variationId = $item['variation_id'] ?? null;
                    
                    // Build product name
                    $name = '';
                    if (is_array($lines) && count($lines) > 0) {
                        $name = implode(', ', $lines);
                    } else {
                        // Fetch real product name from WooCommerce
                        if ($productId) {
                            $productData = wcApiRequest($storeCode, "products/{$productId}");
                            if ($productData && !isset($productData['error'])) {
                                $name = $productData['name'] ?? '';
                                // If variation, add variation attributes
                                if ($variationId) {
                                    $varData = wcApiRequest($storeCode, "products/{$productId}/variations/{$variationId}");
                                    if ($varData && !isset($varData['error']) && !empty($varData['attributes'])) {
                                        $attrs = array_map(fn($a) => $a['option'] ?? '', $varData['attributes']);
                                        $name .= ' (' . implode(' / ', array_filter($attrs)) . ')';
                                    }
                                }
                            }
                        }
                        if (!$name) $name = 'Product #' . ($productId ?? 'unknown');
                    }
                    
                    // Get product image and SKU
                    $image = null;
                    $sku = '';
                    if ($productId && isset($productData)) {
                        if (!empty($productData['images'][0]['src'])) {
                            $image = $productData['images'][0]['src'];
                        }
                        $sku = $productData['sku'] ?? '';
                        // Use variation SKU if available
                        if ($variationId && isset($varData) && !empty($varData['sku'])) {
                            $sku = $varData['sku'];
                        }
                    }
                    
                    $cartContents[] = [
                        'name' => $name,
                        'quantity' => intval($item['quantity'] ?? 1),
                        'price' => floatval($item['line_total'] ?? 0),
                        'productId' => $productId,
                        'variationId' => $variationId,
                        'image' => $image,
                        'sku' => $sku
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
                'orderId' => $savedData['orderId'] ?? null,
                'converted' => $isConverted
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
    
    $logFile = __DIR__ . '/data/buyers-debug.log';
    $logMsg = function($msg) use ($logFile) {
        $line = '[' . date('Y-m-d H:i:s') . '] ' . $msg . "\n";
        file_put_contents($logFile, $line, FILE_APPEND);
        error_log("[OneTimeBuyers] " . $msg);
    };
    
    $logMsg("========== START fetchOneTimeBuyers ==========");
    $logMsg("Store filter: " . ($storeFilter ?: 'all'));
    $startTime = microtime(true);
    
    // Load buyers settings for min days filter
    $buyersSettingsFile = __DIR__ . '/data/buyers-settings.json';
    $minDaysFromPurchase = 10; // default
    if (file_exists($buyersSettingsFile)) {
        $buyersSettings = json_decode(file_get_contents($buyersSettingsFile), true);
        $minDaysFromPurchase = isset($buyersSettings['minDaysFromPurchase']) ? (int)$buyersSettings['minDaysFromPurchase'] : 10;
    }
    $logMsg("Min days from purchase: $minDaysFromPurchase");
    
    $cacheKey = 'one_time_buyers_' . ($storeFilter ?: 'all') . '_' . $minDaysFromPurchase;
    $cached = getCache($cacheKey, 300);  // 5 min cache - match auto-refresh for conversion detection
    if ($cached !== null) {
        $logMsg("✓ Returning cached data: " . count($cached) . " buyers");
        return $cached;
    }
    
    $callData = loadCallData();
    $allBuyers = [];
    
    $storesToFetch = $storeFilter ? [$storeFilter => $stores[$storeFilter]] : $stores;
    $logMsg("Stores to fetch: " . implode(', ', array_keys($storesToFetch)));
    
    // Fetch ALL orders - no page limit
    // Loop until no more results (empty response = done)
    // Caching ensures this only runs once per 30 min
    $maxPages = 999; // Effectively unlimited - will stop when empty
    
    // Collect all orders from all stores using parallel requests
    $allStoreOrders = [];
    $curlMultiFailed = false;
    
    try {
        // Process pages in batches - all stores page 1, then all stores page 2, etc.
        for ($page = 1; $page <= $maxPages; $page++) {
            $logMsg("Processing page $page...");
            
            $mh = curl_multi_init();
            if ($mh === false) {
                $logMsg("✗ curl_multi_init failed! Falling back to sequential.");
                $curlMultiFailed = true;
                break;
            }
            
            $handles = [];
            
            foreach ($storesToFetch as $storeCode => $config) {
                if (!$config) continue;
                
                // Skip stores that already returned empty on previous page
                // BUG FIX: must check if done === true, not just isset()
                if ($page > 1 && !empty($allStoreOrders[$storeCode]['done'])) continue;
                
                $params = http_build_query([
                    'per_page' => 100,
                    'status' => 'processing,completed',
                    'orderby' => 'date',
                    'order' => 'desc',
                    'page' => $page
                ]);
                $url = $config['url'] . '/wp-json/wc/v3/orders?' . $params;
                
                $ch = curl_init();
                curl_setopt_array($ch, [
                    CURLOPT_URL => $url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT => 15,  // Reduced from 25 to 15 for cPanel
                    CURLOPT_CONNECTTIMEOUT => 10,
                    CURLOPT_USERPWD => $config['ck'] . ':' . $config['cs'],
                    CURLOPT_SSL_VERIFYPEER => true
                ]);
                curl_multi_add_handle($mh, $ch);
                $handles[$storeCode] = $ch;
            }
            
            if (empty($handles)) {
                curl_multi_close($mh);
                break;
            }
            
            // Execute all requests in parallel with timeout protection
            $running = null;
            $startExec = microtime(true);
            $maxExecTime = 20; // Max 20 seconds per batch
            
            do { 
                $status = curl_multi_exec($mh, $running);
                if ($status !== CURLM_OK) {
                    $logMsg("✗ curl_multi_exec error: $status");
                    $curlMultiFailed = true;
                    break;
                }
                curl_multi_select($mh, 0.5);
                
                // Timeout protection
                if (microtime(true) - $startExec > $maxExecTime) {
                    $logMsg("✗ curl_multi timeout after {$maxExecTime}s!");
                    break;
                }
            } while ($running > 0);
            
            // Process responses
            foreach ($handles as $storeCode => $ch) {
                $response = curl_multi_getcontent($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $curlError = curl_error($ch);
                
                if ($curlError) {
                    $logMsg("✗ $storeCode: CURL error - $curlError");
                } else {
                    $logMsg("$storeCode page $page: HTTP $httpCode, " . strlen($response) . " bytes");
                }
                
                $orders = json_decode($response, true);
                curl_multi_remove_handle($mh, $ch);
                curl_close($ch);
                
                if (!isset($allStoreOrders[$storeCode])) {
                    $allStoreOrders[$storeCode] = ['orders' => [], 'done' => false];
                }
                
                if (!is_array($orders) || empty($orders)) {
                    $allStoreOrders[$storeCode]['done'] = true;
                    continue;
                }
                
                $allStoreOrders[$storeCode]['orders'] = array_merge(
                    $allStoreOrders[$storeCode]['orders'], 
                    $orders
                );
                $logMsg("$storeCode: Got " . count($orders) . " orders (total: " . count($allStoreOrders[$storeCode]['orders']) . ")");
                
                // Mark as done if less than 100 results (no more pages)
                if (count($orders) < 100) {
                    $allStoreOrders[$storeCode]['done'] = true;
                }
            }
            curl_multi_close($mh);
            
            if ($curlMultiFailed) break;
            
            // Check if all stores are done
            $allDone = true;
            foreach ($storesToFetch as $storeCode => $config) {
                if (!isset($allStoreOrders[$storeCode]['done']) || !$allStoreOrders[$storeCode]['done']) {
                    $allDone = false;
                    break;
                }
            }
            if ($allDone) {
                $logMsg("All stores done at page $page");
                break;
            }
        }
    } catch (Exception $e) {
        $logMsg("✗ Exception in curl_multi: " . $e->getMessage());
        $curlMultiFailed = true;
    }
    
    // FALLBACK: Sequential requests if curl_multi failed
    if ($curlMultiFailed) {
        $logMsg("Falling back to sequential requests...");
        $allStoreOrders = [];
        
        foreach ($storesToFetch as $storeCode => $config) {
            if (!$config) continue;
            
            $allStoreOrders[$storeCode] = ['orders' => [], 'done' => false];
            
            for ($page = 1; $page <= $maxPages; $page++) {
                $params = http_build_query([
                    'per_page' => 100,
                    'status' => 'processing,completed',
                    'orderby' => 'date',
                    'order' => 'desc',
                    'page' => $page
                ]);
                $url = $config['url'] . '/wp-json/wc/v3/orders?' . $params;
                
                $ch = curl_init();
                curl_setopt_array($ch, [
                    CURLOPT_URL => $url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT => 12,  // Shorter timeout for sequential
                    CURLOPT_CONNECTTIMEOUT => 8,
                    CURLOPT_USERPWD => $config['ck'] . ':' . $config['cs'],
                    CURLOPT_SSL_VERIFYPEER => true
                ]);
                
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $curlError = curl_error($ch);
                curl_close($ch);
                
                if ($curlError) {
                    $logMsg("✗ $storeCode (seq): CURL error - $curlError");
                    break; // Skip to next store
                }
                
                $orders = json_decode($response, true);
                
                if (!is_array($orders) || empty($orders)) {
                    $logMsg("$storeCode (seq): No more orders at page $page");
                    break;
                }
                
                $allStoreOrders[$storeCode]['orders'] = array_merge(
                    $allStoreOrders[$storeCode]['orders'], 
                    $orders
                );
                $logMsg("$storeCode (seq): Got " . count($orders) . " orders (total: " . count($allStoreOrders[$storeCode]['orders']) . ")");
                
                if (count($orders) < 100) break;
            }
        }
    }
    
    // Process orders from all stores
    foreach ($storesToFetch as $storeCode => $config) {
        if (!$config || !isset($allStoreOrders[$storeCode])) continue;
        
        $orders = $allStoreOrders[$storeCode]['orders'];
        
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
            
            // Check if enough days have passed since the purchase
            $orderDate = strtotime($order['date_created'] ?? '');
            $daysSincePurchase = $orderDate ? (time() - $orderDate) / (60 * 60 * 24) : 0;
            if ($daysSincePurchase < $minDaysFromPurchase) continue;
            
            $customerId = $storeCode . '_buyer_' . md5($email);
            $savedData = $callData[$customerId] ?? [];
            
            // Skip if already marked as converted
            if (($savedData['callStatus'] ?? '') === 'converted') continue;
            
            // One-time buyers: no automatic conversion detection needed
            // When they make a 2nd order, they disappear from this list automatically
            // (because filter checks count($data['orders']) === 1)
            $isConverted = false;
            
            // Extract order items (products purchased)
            $orderItems = [];
            foreach (($order['line_items'] ?? []) as $item) {
                $orderItems[] = [
                    'name' => $item['name'] ?? 'Unknown product',
                    'quantity' => intval($item['quantity'] ?? 1),
                    'total' => floatval($item['total'] ?? 0)
                ];
            }
            
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
                'notes' => $savedData['notes'] ?? '',
                'converted' => $isConverted,
                'orderItems' => $orderItems
            ];
        }
    }
    
    usort($allBuyers, function($a, $b) {
        return strtotime($b['registeredAt'] ?: '1970-01-01') - strtotime($a['registeredAt'] ?: '1970-01-01');
    });
    
    $elapsed = round(microtime(true) - $startTime, 2);
    $logMsg("✓ COMPLETE: " . count($allBuyers) . " buyers found in {$elapsed}s");
    $logMsg("========== END fetchOneTimeBuyers ==========\n");
    
    setCache($cacheKey, $allBuyers);
    return $allBuyers;
}

// Debug function to see what's happening with orders
function fetchOneTimeBuyersDebug($storeFilter = null) {
    global $stores;
    
    $storesToFetch = $storeFilter ? [$storeFilter => $stores[$storeFilter]] : $stores;
    $debug = [];
    
    foreach ($storesToFetch as $storeCode => $config) {
        if (!$config) continue;
        
        $allOrders = [];
        $pageStats = [];
        
        for ($page = 1; $page <= 30; $page++) {
            $orders = wcApiRequest($storeCode, 'orders', [
                'per_page' => 100,
                'status' => 'processing,completed',
                'orderby' => 'date',
                'order' => 'desc',
                'page' => $page
            ]);
            
            if (!is_array($orders) || isset($orders['error']) || empty($orders)) {
                $pageStats[] = ['page' => $page, 'count' => 0, 'stopped' => true];
                break;
            }
            
            $pageStats[] = ['page' => $page, 'count' => count($orders)];
            $allOrders = array_merge($allOrders, $orders);
            
            if (count($orders) < 100) break;
        }
        
        // Count unique emails
        $emailCounts = [];
        foreach ($allOrders as $order) {
            $email = strtolower($order['billing']['email'] ?? '');
            if ($email) {
                $emailCounts[$email] = ($emailCounts[$email] ?? 0) + 1;
            }
        }
        
        $oneTimeBuyers = count(array_filter($emailCounts, fn($c) => $c === 1));
        $repeatBuyers = count(array_filter($emailCounts, fn($c) => $c > 1));
        
        $debug[$storeCode] = [
            'totalOrders' => count($allOrders),
            'pagesFetched' => count($pageStats),
            'pageStats' => $pageStats,
            'uniqueCustomers' => count($emailCounts),
            'oneTimeBuyers' => $oneTimeBuyers,
            'repeatBuyers' => $repeatBuyers
        ];
    }
    
    return $debug;
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
    $phone = $data['phone'] ?? '';
    $storeCode = $data['storeCode'] ?? 'hr';
    $message = $data['message'] ?? '';
    
    // Validate phone number
    $validation = validatePhoneForSms($phone, $storeCode);
    if (!$validation['valid']) {
        return ['success' => false, 'error' => $validation['error']];
    }
    
    // Validate message
    if (empty(trim($message))) {
        return ['success' => false, 'error' => 'Sporočilo je prazno'];
    }
    
    $queue = loadSmsQueue();
    
    $smsEntry = [
        'id' => time() . '_' . rand(1000, 9999),
        'date' => date('c'),
        'recipient' => $validation['formatted'], // Store pre-formatted phone
        'recipientOriginal' => $phone, // Keep original for reference
        'customerName' => $data['customerName'] ?? '',
        'storeCode' => $storeCode,
        'message' => $message,
        'status' => 'queued', // Always queued - NEVER sent automatically!
        'cartId' => $data['cartId'] ?? null,
        'addedBy' => $data['addedBy'] ?? 'system'
    ];
    
    $queue[] = $smsEntry;
    saveSmsQueue($queue);
    
    return [
        'success' => true, 
        'id' => $smsEntry['id'], 
        'status' => 'queued',
        'formattedPhone' => $validation['formatted']
    ];
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
    
    // Find the SMS to get cartId before removing
    $removedSms = null;
    foreach ($queue as $sms) {
        if ($sms['id'] === $smsId) {
            $removedSms = $sms;
            break;
        }
    }
    
    $queue = array_filter($queue, fn($s) => $s['id'] !== $smsId);
    saveSmsQueue(array_values($queue));
    
    // Also remove from automation-queued-carts tracking
    if ($removedSms && !empty($removedSms['cartId'])) {
        $queuedCartsFile = __DIR__ . '/data/automation-queued-carts.json';
        if (file_exists($queuedCartsFile)) {
            $queuedCarts = json_decode(file_get_contents($queuedCartsFile), true) ?: [];
            // Extract just the cart DB ID (remove store prefix like "hr_")
            $cartDbId = preg_replace('/^[a-z]+_/', '', $removedSms['cartId']);
            foreach ($queuedCarts as $autoId => &$carts) {
                $carts = array_values(array_filter($carts, fn($c) => $c !== $cartDbId && $c !== $removedSms['cartId']));
            }
            unset($carts);
            file_put_contents($queuedCartsFile, json_encode($queuedCarts, JSON_PRETTY_PRINT));
        }
    }
    
    return ['success' => true];
}

// ========== SMS AUTOMATION RUNNER ==========
// This function checks automations and ADDS to queue (never sends directly)

function runSmsAutomations() {
    $automationsFile = __DIR__ . '/data/sms-automations.json';
    $queuedCartsFile = __DIR__ . '/data/automation-queued-carts.json';
    $logFile = __DIR__ . '/data/automation-runner.log';
    
    $log = function($msg) use ($logFile) {
        $line = '[' . date('Y-m-d H:i:s') . '] ' . $msg . "\n";
        file_put_contents($logFile, $line, FILE_APPEND);
    };
    
    $log("=== Starting automation run ===");
    
    // Load automations
    $automations = [];
    if (file_exists($automationsFile)) {
        $automations = json_decode(file_get_contents($automationsFile), true) ?: [];
    }
    
    // Filter enabled automations
    $enabledAutomations = array_filter($automations, fn($a) => $a['enabled'] ?? false);
    $log("Found " . count($enabledAutomations) . " enabled automations");
    
    if (empty($enabledAutomations)) {
        return ['success' => true, 'message' => 'No enabled automations', 'queued' => 0];
    }
    
    // Load already queued carts (to prevent duplicates)
    $queuedCarts = [];
    if (file_exists($queuedCartsFile)) {
        $queuedCarts = json_decode(file_get_contents($queuedCartsFile), true) ?: [];
    }
    
    // Load SMS templates
    $templatesFile = __DIR__ . '/sms-templates.json';
    $templates = [];
    if (file_exists($templatesFile)) {
        $data = json_decode(file_get_contents($templatesFile), true);
        $templates = $data['templates'] ?? $data;
    }
    
    $totalQueued = 0;
    $results = [];
    
    foreach ($enabledAutomations as $automation) {
        $autoId = $automation['id'];
        $store = $automation['store'];
        $type = $automation['type'];
        $templateId = $automation['template']; // e.g., "abandoned_cart_hr" or just "abandoned_cart"
        $delayHours = (int)($automation['delay_hours'] ?? 2);
        
        $log("Processing automation: {$automation['name']} (store: $store, type: $type, delay: {$delayHours}h)");
        
        // Initialize queued carts tracking for this automation
        if (!isset($queuedCarts[$autoId])) {
            $queuedCarts[$autoId] = [];
        }
        
        // Handle abandoned_cart type
        if ($type === 'abandoned_cart') {
            $carts = fetchAbandonedCarts();
            $log("Fetched " . count($carts) . " total abandoned carts");
            
            // Filter carts for this store
            $storeCarts = array_filter($carts, fn($c) => $c['storeCode'] === $store);
            $log("Found " . count($storeCarts) . " carts for store $store");
            
            $queuedThisRun = 0;
            
            foreach ($storeCarts as $cart) {
                $cartId = $cart['cartDbId'] ?? $cart['id'] ?? null;
                if (!$cartId) continue;
                
                // Skip if already queued by this automation
                if (in_array($cartId, $queuedCarts[$autoId])) {
                    continue;
                }
                
                // Skip if cart was already converted or has an order
                if (!empty($cart['orderId'])) {
                    continue;
                }
                
                // Check delay - abandonedAt + delay_hours < now
                $abandonedAt = strtotime($cart['abandonedAt'] ?? '');
                if (!$abandonedAt) continue;
                
                $sendAfter = $abandonedAt + ($delayHours * 3600);
                if (time() < $sendAfter) {
                    // Not yet time to send
                    continue;
                }
                
                // Check max_days - skip carts older than max_days
                $maxDays = $automation['max_days'] ?? 7;
                $maxAgeSeconds = $maxDays * 24 * 3600;
                if ((time() - $abandonedAt) > $maxAgeSeconds) {
                    $log("Skipping cart $cartId - older than $maxDays days");
                    continue;
                }
                
                // Skip if no phone number
                $phone = $cart['phone'] ?? '';
                if (empty($phone)) {
                    $log("Skipping cart $cartId - no phone number");
                    continue;
                }
                
                // Get template message
                // Template ID format: "{type}_{store}" e.g., "abandoned_cart_hr"
                // Type can contain underscores, so we extract by removing the store suffix
                $templateType = $templateId;
                if (str_ends_with($templateId, '_' . $store)) {
                    $templateType = substr($templateId, 0, -(strlen($store) + 1));
                }
                
                $message = '';
                if (isset($templates[$templateType][$store])) {
                    $message = $templates[$templateType][$store]['message'] ?? '';
                } elseif (isset($templates[$type][$store])) {
                    $message = $templates[$type][$store]['message'] ?? '';
                }
                
                $log("Template lookup: templateId=$templateId, templateType=$templateType, store=$store");
                
                if (empty($message)) {
                    $log("No template message found for type=$type, store=$store");
                    continue;
                }
                
                // Replace variables in message
                $firstName = $cart['firstName'] ?? '';
                $productName = '';
                if (!empty($cart['cartContents']) && is_array($cart['cartContents'])) {
                    $firstItem = reset($cart['cartContents']);
                    $productName = $firstItem['name'] ?? 'proizvod';
                }
                $checkoutLink = "https://noriks.com/{$store}/checkout/?source=callboss";
                $checkoutLinkWithCoupon = "https://noriks.com/{$store}/checkout/?coupon=SMS20&source=callboss";
                $shopLink = "https://noriks.com/{$store}/?source=callboss";
                
                $message = str_replace(
                    ['{ime}', '{produkt}', '{link}', '{link_coupon}', '{shop_link}', '{cena}'],
                    [$firstName ?: 'Kupac', $productName ?: 'proizvod', $checkoutLink, $checkoutLinkWithCoupon, $shopLink, number_format($cart['cartValue'] ?? 0, 2)],
                    $message
                );
                
                // Add to queue
                $result = addSmsToQueue([
                    'phone' => $phone,
                    'storeCode' => $store,
                    'message' => $message,
                    'customerName' => $cart['customerName'] ?? '',
                    'cartId' => $cartId,
                    'addedBy' => 'automation:' . $autoId
                ]);
                
                if ($result['success']) {
                    $queuedCarts[$autoId][] = $cartId;
                    $queuedThisRun++;
                    $totalQueued++;
                    $log("Queued SMS for cart $cartId to " . ($result['formattedPhone'] ?? $phone));
                } else {
                    $log("Failed to queue SMS for cart $cartId: " . ($result['error'] ?? 'Unknown error'));
                }
            }
            
            $results[$autoId] = [
                'name' => $automation['name'],
                'queued' => $queuedThisRun
            ];
            
            // Update queued_count on automation
            foreach ($automations as &$a) {
                if ($a['id'] === $autoId) {
                    $a['queued_count'] = count($queuedCarts[$autoId]);
                    break;
                }
            }
            unset($a);
        }
    }
    
    // Save updated automations with queued_count
    file_put_contents($automationsFile, json_encode(array_values($automations), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    
    // Save queued carts tracking
    file_put_contents($queuedCartsFile, json_encode($queuedCarts, JSON_PRETTY_PRINT));
    
    $log("=== Automation run complete. Total queued: $totalQueued ===");
    
    return [
        'success' => true,
        'totalQueued' => $totalQueued,
        'results' => $results
    ];
}

// Clean up old queued cart entries (run periodically)
function cleanupAutomationTracking($maxAgeDays = 30) {
    $queuedCartsFile = __DIR__ . '/data/automation-queued-carts.json';
    
    if (!file_exists($queuedCartsFile)) return;
    
    // For now, we keep all entries. In the future, could compare against
    // abandoned carts that are no longer present (converted or deleted)
    // This is a placeholder for future cleanup logic
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

/**
 * BUILD FULL PAKETOMATI CACHE
 * Fetches ALL orders in batches, checks delivery events in parallel
 * Called by cron every 15 min
 */
function buildPaketomatiCacheFull() {
    $startTime = microtime(true);
    $cacheFile = __DIR__ . '/data/paketomati-cache.json';
    
    // Ensure data directory exists
    $dataDir = __DIR__ . '/data';
    if (!is_dir($dataDir)) mkdir($dataDir, 0755, true);
    
    $PAKETOMAT_STATUSES = [
        // GLS
        "Can be picked up from GLS parcel locker",
        "Can be picked up from ParcelShop",
        // DPD / Pošta
        "Placed in the (collection) parcel machine",
        "Parcel stored in temporary parcel machine",
        "Packet has been delivered to its destination branch and is waiting for pickup",
        // InPost / Packeta / Expedico
        "It's waiting to be collected at the Parcel Service Point",
        "Awaiting collection",
        "Accepted at an InPost branch",
        "Rerouted to parcel machine",
        // Croatian - Hrvatska Pošta / Overseas Express
        "predana u paketomat",
        "Pošiljka predana u paketomat",
        "predana na pickup",
        "čeka preuzimanje",
        // Generic waiting statuses
        "Čaka na prevzem",
        "Waiting for pickup",
        "Ready for pickup",
        "Ready for collection",
        "Available for pickup",
        "Dostavljen na pošto",
        "Dostavljen v paketnik",
        "Dostavljen na prevzemno mesto",
        "Delivered to parcel locker",
        "Delivered to pickup point",
        "waiting at pickup",
        "at collection point"
    ];
    
    $mkSearchUrl = 'https://main.metakocka.si/rest/eshop/v1/search';
    $mkGetDocUrl = 'https://main.metakocka.si/rest/eshop/v1/get_document';
    $secretKey = 'ee759602-961d-4431-ac64-0725ae8d9665';
    $companyId = '6371';
    
    $allShippedOrders = [];
    $stats = ['batches' => 0, 'total_orders' => 0, 'shipped' => 0, 'with_events' => 0, 'matched' => 0];
    
    // STEP 1: Fetch orders in batches of 100 (up to 500 total)
    for ($offset = 0; $offset < 500; $offset += 100) {
        $stats['batches']++;
        
        $searchPayload = [
            'secret_key' => $secretKey,
            'company_id' => $companyId,
            'doc_type' => 'sales_order',
            'result_type' => 'doc',
            'limit' => 100,
            'offset' => $offset,
            'order_direction' => 'desc'
        ];
        
        $ch = curl_init($mkSearchUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => json_encode($searchPayload),
            CURLOPT_TIMEOUT => 30
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            error_log("[PaketomatiCache] Batch $offset failed: HTTP $httpCode");
            break;
        }
        
        $data = json_decode($response, true);
        $orders = $data['result'] ?? [];
        
        if (empty($orders)) break; // No more orders
        
        $stats['total_orders'] += count($orders);
        
        // Filter to shipped only
        foreach ($orders as $order) {
            if (($order['status_desc'] ?? '') === 'shipped') {
                $allShippedOrders[] = $order;
            }
        }
        
        // If we got less than 100, we've reached the end
        if (count($orders) < 100) break;
    }
    
    $stats['shipped'] = count($allShippedOrders);
    error_log("[PaketomatiCache] Found {$stats['shipped']} shipped orders from {$stats['total_orders']} total");
    
    // STEP 2: Fetch delivery events in parallel using curl_multi
    $paketomatOrders = [];
    $statusData = loadPaketomatStatus();
    $batchSize = 20; // Process 20 orders at a time
    
    for ($i = 0; $i < count($allShippedOrders); $i += $batchSize) {
        $batch = array_slice($allShippedOrders, $i, $batchSize);
        $multiHandle = curl_multi_init();
        $curlHandles = [];
        
        // Setup parallel requests
        foreach ($batch as $idx => $order) {
            $mkId = $order['mk_id'] ?? null;
            if (!$mkId) continue;
            
            $payload = [
                'secret_key' => $secretKey,
                'company_id' => $companyId,
                'doc_type' => 'sales_order',
                'doc_id' => $mkId,
                'return_delivery_service_events' => 'true',
                'show_tracking_url' => 'true'
            ];
            
            $ch = curl_init($mkGetDocUrl);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
                CURLOPT_POSTFIELDS => json_encode($payload),
                CURLOPT_TIMEOUT => 15
            ]);
            
            curl_multi_add_handle($multiHandle, $ch);
            $curlHandles[$idx] = ['handle' => $ch, 'order' => $order];
        }
        
        // Execute all requests
        $running = null;
        do {
            curl_multi_exec($multiHandle, $running);
            curl_multi_select($multiHandle);
        } while ($running > 0);
        
        // Process responses
        foreach ($curlHandles as $idx => $item) {
            $response = curl_multi_getcontent($item['handle']);
            curl_multi_remove_handle($multiHandle, $item['handle']);
            curl_close($item['handle']);
            
            $docData = json_decode($response, true);
            $order = $item['order'];
            $events = $docData['delivery_service_events'] ?? [];
            
            // Normalize events (MetaKocka returns dict for single, array for multiple)
            if (is_array($events) && isset($events['event_status'])) {
                $events = [$events];
            } else if (!is_array($events) || empty($events)) {
                continue;
            }
            
            $stats['with_events']++;
            
            // Check FIRST (newest) event
            $lastEvent = $events[0] ?? [];
            $lastEventStatus = $lastEvent['event_status'] ?? '';
            
            // Match against paketomat statuses
            $isPaketomat = false;
            foreach ($PAKETOMAT_STATUSES as $status) {
                if (stripos($lastEventStatus, $status) !== false) {
                    $isPaketomat = true;
                    break;
                }
            }
            
            if (!$isPaketomat) continue;
            
            $stats['matched']++;
            
            // Build order object for cache
            $fullOrder = array_merge($order, $docData ?: []);
            $orderId = 'mk_' . ($fullOrder['count_code'] ?? $fullOrder['mk_id'] ?? uniqid());
            $partner = $fullOrder['partner'] ?? [];
            $partnerContact = $partner['partner_contact'] ?? [];
            
            // Extract tracking code and links from MetaKocka
            $trackingCode = '';
            $trackingLink = '';
            $trackingPageUrl = '';
            foreach ($fullOrder['extra_column'] ?? [] as $col) {
                $colName = strtolower($col['name'] ?? '');
                if ($colName === 'tracking_number') {
                    $trackingCode = $col['value'] ?? '';
                }
                if ($colName === 'tracking_link' || $colName === 'tracking_url' || $colName === 'sledilna_povezava') {
                    $trackingLink = $col['value'] ?? '';
                }
                if ($colName === 'tracking_page_url') {
                    $trackingPageUrl = $col['value'] ?? '';
                }
            }
            
            // Get store code - try multiple sources
            $eshopName = strtolower($fullOrder['eshop_name'] ?? '');
            $country = $partner['country'] ?? '';
            $storeCode = 'hr'; // Default to HR (most common)
            
            // 1. Try eshop_name patterns
            if (preg_match('/\.([a-z]{2})\./', $eshopName, $m)) {
                $storeCode = $m[1];
            } elseif (preg_match('/(sk|cz|pl|hr|hu|gr|it|si)/i', $eshopName, $m)) {
                $storeCode = strtolower($m[1]);
            } elseif (strpos($eshopName, 'slovakia') !== false || strpos($eshopName, 'sk') !== false) {
                $storeCode = 'sk';
            } elseif (strpos($eshopName, 'czech') !== false || strpos($eshopName, 'cz') !== false) {
                $storeCode = 'cz';
            } elseif (strpos($eshopName, 'poland') !== false || strpos($eshopName, 'pl') !== false) {
                $storeCode = 'pl';
            } elseif (strpos($eshopName, 'croatia') !== false || strpos($eshopName, 'hr') !== false) {
                $storeCode = 'hr';
            } elseif (strpos($eshopName, 'hungary') !== false || strpos($eshopName, 'hu') !== false) {
                $storeCode = 'hu';
            } elseif (strpos($eshopName, 'greece') !== false || strpos($eshopName, 'gr') !== false) {
                $storeCode = 'gr';
            } elseif (strpos($eshopName, 'italy') !== false || strpos($eshopName, 'it') !== false) {
                $storeCode = 'it';
            }
            // 2. Fallback to country name mapping
            else {
                $countryMap = [
                    'Slovakia' => 'sk', 'Slovensko' => 'sk',
                    'Czech Republic' => 'cz', 'Česká republika' => 'cz', 'Czechia' => 'cz',
                    'Poland' => 'pl', 'Polska' => 'pl',
                    'Croatia' => 'hr', 'Hrvatska' => 'hr',
                    'Hungary' => 'hu', 'Magyarország' => 'hu',
                    'Greece' => 'gr', 'Ελλάδα' => 'gr',
                    'Italy' => 'it', 'Italia' => 'it',
                    'Slovenia' => 'si', 'Slovenija' => 'si'
                ];
                foreach ($countryMap as $name => $code) {
                    if (stripos($country, $name) !== false) {
                        $storeCode = $code;
                        break;
                    }
                }
            }
            
            // Extract items from product_list (skip shipping entries)
            $items = [];
            foreach ($fullOrder['product_list'] ?? [] as $product) {
                $name = $product['name'] ?? '';
                // Skip shipping/delivery entries
                if (stripos($name, 'pošta') !== false || stripos($name, 'doručení') !== false || 
                    stripos($name, 'shipping') !== false || stripos($name, 'delivery') !== false ||
                    stripos($name, 'dostava') !== false || stripos($name, 'szállítás') !== false) {
                    continue;
                }
                $items[] = [
                    'name' => $name,
                    'quantity' => intval($product['amount'] ?? 1),
                    'price' => floatval($product['price_with_tax'] ?? $product['price'] ?? 0),
                    'variant' => $product['doc_desc'] ?? '' // Contains size/color info
                ];
            }
            
            $paketomatOrders[] = [
                'id' => $orderId,
                'orderNumber' => $fullOrder['count_code'] ?? '',
                'customer' => [
                    'name' => $partner['customer'] ?? $partner['name'] ?? '',
                    'email' => $partnerContact['email'] ?? $partner['email'] ?? '',
                    'phone' => $partnerContact['gsm'] ?? $partnerContact['phone'] ?? $partner['phone'] ?? ''
                ],
                'address' => [
                    'street' => $partner['street'] ?? '',
                    'city' => $partner['place'] ?? $partner['city'] ?? '',
                    'postcode' => $partner['post_number'] ?? '',
                    'country' => $partner['country'] ?? ''
                ],
                'deliveryService' => $fullOrder['delivery_type'] ?? '',
                'trackingCode' => $trackingCode,
                'trackingLink' => $trackingLink,
                'trackingPageUrl' => $trackingPageUrl,
                'paketomatLocation' => $lastEventStatus,
                'lastDeliveryEvent' => $lastEventStatus,
                'orderTotal' => floatval($fullOrder['sum_all'] ?? 0),
                'currency' => $fullOrder['currency_code'] ?? 'EUR',
                'createdAt' => $fullOrder['doc_date'] ?? '',
                'shippedAt' => $fullOrder['shipped_date'] ?? '',
                'status' => $statusData[$orderId]['status'] ?? 'not_called',
                'notes' => $statusData[$orderId]['notes'] ?? '',
                'storeCode' => $storeCode,
                'items' => $items
            ];
        }
        
        curl_multi_close($multiHandle);
    }
    
    // Save to cache file
    $cacheData = [
        'generated_at' => date('c'),
        'stats' => $stats,
        'duration_sec' => round(microtime(true) - $startTime, 2),
        'orders' => $paketomatOrders
    ];
    
    file_put_contents($cacheFile, json_encode($cacheData, JSON_PRETTY_PRINT));
    
    error_log("[PaketomatiCache] Done: {$stats['matched']} paketomati from {$stats['shipped']} shipped orders in {$cacheData['duration_sec']}s");
    
    return $cacheData;
}

function fetchPaketomatOrders($filter = 'all') {
    global $stores;
    
    // READ FROM JSON CACHE FILE (built by cron)
    $cacheFile = __DIR__ . '/data/paketomati-cache.json';
    if (file_exists($cacheFile)) {
        $cacheData = json_decode(file_get_contents($cacheFile), true);
        if ($cacheData && isset($cacheData['orders'])) {
            $orders = $cacheData['orders'];
            
            // Apply status data overlay
            $statusData = loadPaketomatStatus();
            foreach ($orders as &$order) {
                $orderId = $order['id'] ?? '';
                if (isset($statusData[$orderId])) {
                    $order['status'] = $statusData[$orderId]['status'] ?? $order['status'];
                    $order['notes'] = $statusData[$orderId]['notes'] ?? $order['notes'];
                }
            }
            
            // Filter if needed
            if ($filter !== 'all' && $filter !== 'debug') {
                $orders = array_filter($orders, function($o) use ($filter) {
                    return ($o['status'] ?? 'not_called') === $filter;
                });
            }
            
            return array_values($orders);
        }
    }
    
    // Fallback: return empty if no cache (cron will build it)
    return [];
}

function fetchPaketomatOrdersLegacy($filter = 'all') {
    global $stores;
    
    // 5 min cache - API calls are slow
    $cached = getCache('paketomat_orders_' . $filter, 300);
    if ($cached !== null && $filter !== 'debug') return $cached;
    
    $statusData = loadPaketomatStatus();
    $allOrders = [];
    
    // PRAVILNA LOGIKA: Paketomat = naročilo kjer je ZADNJI delivery event eden od teh statusov
    // To pomeni, da je paket TRENUTNO v paketomatu/pošti/pickup pointu in čaka na prevzem
    $PAKETOMAT_STATUSES = [
        // GLS
        "Can be picked up from GLS parcel locker",
        "Can be picked up from ParcelShop",
        // DPD / Pošta
        "Placed in the (collection) parcel machine",
        "Parcel stored in temporary parcel machine",
        "Packet has been delivered to its destination branch and is waiting for pickup",
        // InPost / Packeta / Expedico
        "It's waiting to be collected at the Parcel Service Point",
        "Awaiting collection",
        "Accepted at an InPost branch",
        "Rerouted to parcel machine",
        // Croatian - Hrvatska Pošta / Overseas Express
        "predana u paketomat",      // Delivered to paketomat
        "Pošiljka predana u paketomat",
        "predana na pickup",
        "čeka preuzimanje",         // Waiting for pickup (HR)
        // Generic waiting statuses (multiple languages)
        "Čaka na prevzem",
        "Waiting for pickup",
        "Ready for pickup",
        "Ready for collection",
        "Available for pickup",
        "Dostavljen na pošto",
        "Dostavljen v paketnik",
        "Dostavljen na prevzemno mesto",
        "Delivered to parcel locker",
        "Delivered to pickup point",
        // Additional pickup indicators
        "waiting at pickup",
        "at collection point"
    ];
    
    // STEP 1: Fetch recent orders from MetaKocka (search endpoint)
    // Get 100 most recent orders (newest first)
    $mkSearchUrl = 'https://main.metakocka.si/rest/eshop/v1/search';
    $mkPayload = [
        'secret_key' => 'ee759602-961d-4431-ac64-0725ae8d9665',
        'company_id' => '6371',
        'doc_type' => 'sales_order',
        'result_type' => 'doc',
        'limit' => 100,
        'order_direction' => 'desc'
    ];
    
    $ch = curl_init($mkSearchUrl);
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
        error_log("[Paketomati] MetaKocka search error: HTTP $httpCode, error: $curlError");
        return [];
    }
    
    $data = json_decode($response, true);
    if (!$data || !isset($data['opr_code']) || $data['opr_code'] !== '0') {
        error_log("[Paketomati] MetaKocka search error response: " . json_encode($data));
        return [];
    }
    
    $orders = $data['result'] ?? [];
    
    error_log("[Paketomati] Found " . count($orders) . " orders, fetching delivery events...");
    
    // DEBUG: Track processing
    $debugInfo = ['total_orders' => count($orders), 'processed' => 0, 'with_events' => 0, 'matched' => 0];
    
    // STEP 2: For each order, fetch delivery events using get_document
    // Only check SHIPPED orders - those are the only ones that can be at pickup points
    $mkGetDocUrl = 'https://main.metakocka.si/rest/eshop/v1/get_document';
    $processedCount = 0;
    
    // Filter to only shipped orders first (reduces API calls significantly)
    $shippedOrders = array_filter($orders, function($o) {
        return ($o['status_desc'] ?? '') === 'shipped';
    });
    $debugInfo['shipped_orders'] = count($shippedOrders);
    
    foreach ($shippedOrders as $order) {
        $mkId = $order['mk_id'] ?? null;
        if (!$mkId) continue;
        
        // Limit API calls - max 60 shipped orders to check
        $processedCount++;
        $debugInfo['processed'] = $processedCount;
        if ($processedCount > 60) break;
        
        // Fetch delivery events for this order
        $getDocPayload = [
            'secret_key' => 'ee759602-961d-4431-ac64-0725ae8d9665',
            'company_id' => '6371',
            'doc_type' => 'sales_order',
            'doc_id' => $mkId,
            'return_delivery_service_events' => 'true',
            'show_tracking_url' => 'true'
        ];
        
        $ch = curl_init($mkGetDocUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => json_encode($getDocPayload),
            CURLOPT_TIMEOUT => 10
        ]);
        
        $docResponse = curl_exec($ch);
        curl_close($ch);
        
        $docData = json_decode($docResponse, true);
        $events = $docData['delivery_service_events'] ?? [];
        
        // MetaKocka returns dict for single event, array for multiple
        if (is_array($events) && !isset($events['event_status'])) {
            // It's an array of events
        } else if (is_array($events) && isset($events['event_status'])) {
            // Single event as dict
            $events = [$events];
        } else {
            $events = [];
        }
        
        // Skip orders without delivery events
        if (empty($events)) continue;
        
        $debugInfo['with_events']++;
        
        // Get the FIRST (newest) event - MetaKocka returns events newest first
        $lastEvent = $events[0] ?? [];
        $lastEventStatus = $lastEvent['event_status'] ?? '';
        
        // DEBUG: Check specific orders
        $isDebugOrder = strpos($order['count_code'] ?? '', '5278') !== false || strpos($order['count_code'] ?? '', '5239') !== false;
        if ($isDebugOrder) {
            error_log("[Paketomati] DEBUG ORDER {$order['count_code']}: lastEventStatus = '$lastEventStatus'");
        }
        
        // Check if last event status is a paketomat status
        $isPaketomat = false;
        foreach ($PAKETOMAT_STATUSES as $paketStatus) {
            if (stripos($lastEventStatus, $paketStatus) !== false || strtolower($lastEventStatus) === strtolower($paketStatus)) {
                $isPaketomat = true;
                error_log("[Paketomati] MATCH: Order {$order['count_code']} - '$lastEventStatus' matches '$paketStatus'");
                $debugInfo['matched']++;
                break;
            }
        }
        
        if ($isDebugOrder) {
            error_log("[Paketomati] DEBUG ORDER {$order['count_code']}: isPaketomat = " . ($isPaketomat ? 'true' : 'false'));
        }
        
        // If filter is not 'all_orders', only show paketomat orders
        if ($filter !== 'all_orders' && !$isPaketomat) {
            if ($isDebugOrder) {
                error_log("[Paketomati] DEBUG ORDER {$order['count_code']}: SKIPPED (filter=$filter, isPaketomat=false)");
            }
            continue;
        }
        
        // Extract order details (merge from search + get_document results)
        $fullOrder = array_merge($order, $docData ?: []);
        
        $orderId = 'mk_' . ($fullOrder['count_code'] ?? $fullOrder['mk_id'] ?? uniqid());
        $orderNumber = $fullOrder['count_code'] ?? '';
        
        // Partner (customer) info
        $partner = $fullOrder['partner'] ?? [];
        $partnerContact = $partner['partner_contact'] ?? [];
        $customerName = $partner['customer'] ?? $partner['name'] ?? '';
        $email = $partnerContact['email'] ?? $partner['email'] ?? '';
        $phone = $partnerContact['gsm'] ?? $partnerContact['phone'] ?? $partner['phone'] ?? '';
        
        // Address info
        $street = $partner['street'] ?? '';
        $city = $partner['place'] ?? $partner['city'] ?? '';
        $postcode = $partner['post_number'] ?? '';
        $country = $partner['country'] ?? '';
        
        // Delivery service info
        $deliveryService = $fullOrder['delivery_type'] ?? $fullOrder['delivery_service'] ?? '';
        $trackingCode = '';
        $trackingLink = '';
        $trackingPageUrl = '';
        $extraColumns = $fullOrder['extra_column'] ?? [];
        foreach ($extraColumns as $col) {
            $colName = strtolower($col['name'] ?? '');
            if ($colName === 'tracking_number') {
                $trackingCode = $col['value'] ?? '';
            }
            if ($colName === 'tracking_link' || $colName === 'tracking_url' || $colName === 'sledilna_povezava') {
                $trackingLink = $col['value'] ?? '';
            }
            if ($colName === 'tracking_page_url') {
                $trackingPageUrl = $col['value'] ?? '';
            }
        }
        
        // Paketomat location - try to extract from delivery point or last event
        $paketomatLocation = $fullOrder['parcel_shop_id'] ?? $fullOrder['delivery_point_name'] ?? '';
        if (!$paketomatLocation && $lastEvent) {
            $paketomatLocation = $lastEvent['location'] ?? $lastEventStatus;
        }
        
        // Order total and currency
        $orderTotal = floatval($fullOrder['sum_all'] ?? $fullOrder['total'] ?? 0);
        $currency = $fullOrder['currency_code'] ?? 'EUR';
        
        // Created date
        $createdAt = $fullOrder['doc_date'] ?? $fullOrder['order_create_ts'] ?? '';
        
        // Order status from MetaKocka
        $orderStatus = $fullOrder['status_code'] ?? '';
        
        // Get store code from eshop_name or guess from country
        $eshopName = $fullOrder['eshop_name'] ?? '';
        $storeCode = 'si';
        if (preg_match('/\.([a-z]{2})\./', $eshopName, $m)) {
            $storeCode = $m[1];
        } elseif (stripos($eshopName, 'sk') !== false) {
            $storeCode = 'sk';
        } elseif (stripos($eshopName, 'cz') !== false) {
            $storeCode = 'cz';
        } elseif (stripos($eshopName, 'pl') !== false) {
            $storeCode = 'pl';
        } elseif (stripos($eshopName, 'hr') !== false) {
            $storeCode = 'hr';
        } elseif (stripos($eshopName, 'hu') !== false) {
            $storeCode = 'hu';
        } elseif (stripos($eshopName, 'gr') !== false) {
            $storeCode = 'gr';
        } elseif (!empty($country)) {
            // Fallback: guess from country name
            $countryMap = ['Slovakia' => 'sk', 'Czech Republic' => 'cz', 'Poland' => 'pl', 
                           'Croatia' => 'hr', 'Hungary' => 'hu', 'Greece' => 'gr', 'Slovenia' => 'si'];
            $storeCode = $countryMap[$country] ?? strtolower(substr($country, 0, 2));
        }
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
            'trackingLink' => $trackingLink,
            'trackingPageUrl' => $trackingPageUrl,
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
            'orderStatus' => $orderStatus,
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
    
    // Don't cache while debugging
    // setCache('paketomat_orders_' . $filter, $allOrders);
    
    // Add debug info to response if filter is 'debug'
    if ($filter === 'debug') {
        return ['debug' => $debugInfo, 'orders' => $allOrders];
    }
    
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
        
        case 'customer-360':
            // Fetch all data for a specific customer by email
            $email = $_GET['email'] ?? '';
            if (empty($email)) {
                echo json_encode(['error' => 'Email required']);
                break;
            }
            
            $email = strtolower(trim($email));
            $allOrders = [];
            $totalSpent = 0;
            $orderCount = 0;
            
            // Fetch orders from all stores for this email
            foreach ($stores as $storeCode => $config) {
                $orders = wcApiRequest($storeCode, 'orders', [
                    'search' => $email,
                    'per_page' => 50,
                    'status' => 'processing,completed,on-hold'
                ]);
                
                if (is_array($orders) && !isset($orders['error'])) {
                    foreach ($orders as $order) {
                        $orderEmail = strtolower($order['billing']['email'] ?? '');
                        if ($orderEmail !== $email) continue;
                        
                        $allOrders[] = [
                            'id' => $order['id'],
                            'storeCode' => $storeCode,
                            'storeFlag' => $config['flag'],
                            'storeName' => $config['name'],
                            'status' => $order['status'],
                            'total' => floatval($order['total']),
                            'currency' => $order['currency'] ?? 'EUR',
                            'date' => $order['date_created'],
                            'items' => array_map(fn($i) => [
                                'name' => $i['name'],
                                'quantity' => $i['quantity'],
                                'total' => $i['total']
                            ], $order['line_items'] ?? [])
                        ];
                        $totalSpent += floatval($order['total']);
                        $orderCount++;
                    }
                }
            }
            
            // Sort orders by date descending
            usort($allOrders, fn($a, $b) => strtotime($b['date']) - strtotime($a['date']));
            
            // Fetch SMS history for this customer
            $smsQueue = loadSmsQueue();
            $smsHistory = array_filter($smsQueue, function($sms) use ($email) {
                return strtolower($sms['customerEmail'] ?? '') === $email;
            });
            $smsHistory = array_values($smsHistory);
            usort($smsHistory, fn($a, $b) => strtotime($b['createdAt'] ?? 0) - strtotime($a['createdAt'] ?? 0));
            
            echo json_encode([
                'success' => true,
                'email' => $email,
                'orders' => $allOrders,
                'orderCount' => $orderCount,
                'totalSpent' => $totalSpent,
                'smsHistory' => $smsHistory
            ]);
            break;
            
        case 'one-time-buyers':
            echo json_encode(fetchOneTimeBuyers($store));
            break;
        
        case 'buyers-cache':
            // INSTANT endpoint - returns cached buyers from file
            $buyersCacheFile = __DIR__ . '/data/buyers-cache.json';
            if (file_exists($buyersCacheFile)) {
                $cacheData = json_decode(file_get_contents($buyersCacheFile), true);
                $cacheAge = time() - ($cacheData['generated_at'] ?? 0);
                
                // Return cached data
                $buyers = $cacheData['buyers'] ?? [];
                
                // Apply store filter if provided
                if ($store) {
                    $buyers = array_values(array_filter($buyers, fn($b) => $b['storeCode'] === $store));
                }
                
                echo json_encode([
                    'success' => true,
                    'buyers' => $buyers,
                    'cached' => true,
                    'cache_age_seconds' => $cacheAge,
                    'generated_at' => $cacheData['generated_at'] ?? null
                ]);
            } else {
                // No cache exists - return empty but trigger background refresh
                echo json_encode([
                    'success' => true,
                    'buyers' => [],
                    'cached' => false,
                    'message' => 'Cache not available, use /api.php?action=refresh-buyers-cache to generate'
                ]);
            }
            break;
        
        case 'refresh-buyers-cache':
            // CRON endpoint - regenerates the buyers cache
            // Call this from cron: */30 * * * * curl -s https://callcenter.noriks.com/api.php?action=refresh-buyers-cache
            ignore_user_abort(true);
            set_time_limit(180); // 3 minutes max
            
            $startTime = microtime(true);
            $buyers = fetchOneTimeBuyers($store);
            $elapsed = round(microtime(true) - $startTime, 2);
            
            $buyersCacheFile = __DIR__ . '/data/buyers-cache.json';
            $cacheData = [
                'generated_at' => time(),
                'generated_date' => date('c'),
                'count' => count($buyers),
                'fetch_time_seconds' => $elapsed,
                'buyers' => $buyers
            ];
            
            file_put_contents($buyersCacheFile, json_encode($cacheData, JSON_PRETTY_PRINT));
            
            echo json_encode([
                'success' => true,
                'count' => count($buyers),
                'fetch_time_seconds' => $elapsed,
                'message' => 'Buyers cache refreshed'
            ]);
            break;
            
        case 'one-time-buyers-debug':
            // Debug endpoint to see raw data
            echo json_encode(fetchOneTimeBuyersDebug($store));
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
            
            // Clear caches so updated status is reflected immediately
            clearCache('abandoned_carts_filtered');
            clearCache('pending_orders');
            // Clear buyer caches for all stores
            foreach (['hr', 'cz', 'pl', 'gr', 'sk', 'it', 'hu'] as $sc) {
                clearCache('one_time_buyers_' . $sc . '_14');
                clearCache('one_time_buyers_' . $sc . '_30');
            }
            clearCache('one_time_buyers_all_14');
            clearCache('one_time_buyers_all_30');
            
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
            
        case 'buyers-settings':
            $buyersSettingsFile = __DIR__ . '/data/buyers-settings.json';
            if (file_exists($buyersSettingsFile)) {
                $settings = json_decode(file_get_contents($buyersSettingsFile), true) ?? ['minDaysFromPurchase' => 10];
            } else {
                $settings = ['minDaysFromPurchase' => 10];
            }
            echo json_encode(['success' => true, 'settings' => $settings]);
            break;
            
        case 'buyers-settings-save':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(['error' => 'POST required']);
                break;
            }
            $input = json_decode(file_get_contents('php://input'), true);
            $buyersSettingsFile = __DIR__ . '/data/buyers-settings.json';
            
            // Ensure data directory exists
            $dataDir = __DIR__ . '/data';
            if (!is_dir($dataDir)) {
                mkdir($dataDir, 0755, true);
            }
            
            $settings = $input['settings'] ?? ['minDaysFromPurchase' => 10];
            $result = file_put_contents($buyersSettingsFile, json_encode($settings, JSON_PRETTY_PRINT));
            
            if ($result === false) {
                echo json_encode(['success' => false, 'error' => 'Failed to write settings file']);
            } else {
                echo json_encode(['success' => true, 'saved' => $settings]);
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
            $overridePhone = $input['phone'] ?? null;
            if (!$smsId) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing SMS ID']);
                break;
            }
            echo json_encode(sendQueuedSms($smsId, $overridePhone));
            break;
            
        case 'sms-send-direct':
            // Direct SMS send (without queue) - for manual testing
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(['error' => 'POST required']);
                break;
            }
            $input = json_decode(file_get_contents('php://input'), true);
            echo json_encode(sendDirectSms($input));
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
            $store = $_GET['store'] ?? null;
            $templatesFile = __DIR__ . '/sms-templates.json';
            if (file_exists($templatesFile)) {
                $data = json_decode(file_get_contents($templatesFile), true);
                $templates = $data['templates'] ?? $data;
                
                // If store specified, filter templates for that store
                if ($store) {
                    $result = [];
                    foreach ($templates as $typeKey => $stores) {
                        if (isset($stores[$store])) {
                            $result[] = [
                                'id' => $typeKey . '_' . $store,
                                'type' => $typeKey,
                                'name' => $stores[$store]['name'],
                                'message' => $stores[$store]['message']
                            ];
                        }
                    }
                    echo json_encode($result);
                } else {
                    echo json_encode($templates);
                }
            } else {
                echo json_encode(['error' => 'Templates file not found']);
            }
            break;
        
        case 'all-sms-templates':
            // Get all templates with their translations for management UI
            $templatesFile = __DIR__ . '/sms-templates.json';
            if (file_exists($templatesFile)) {
                $data = json_decode(file_get_contents($templatesFile), true);
                $templates = $data['templates'] ?? $data;
                
                $result = [];
                foreach ($templates as $typeKey => $stores) {
                    $messages = [];
                    $name = '';
                    $category = 'custom';
                    
                    // Determine category from key
                    if (strpos($typeKey, 'abandoned') !== false) $category = 'abandoned';
                    elseif (strpos($typeKey, 'winback') !== false) $category = 'winback';
                    
                    foreach ($stores as $storeCode => $storeData) {
                        if (is_array($storeData)) {
                            $messages[$storeCode] = $storeData['message'] ?? '';
                            if (!$name && !empty($storeData['name'])) $name = $storeData['name'];
                        }
                    }
                    
                    // Get category from metadata if exists
                    if (isset($stores['_meta']['category'])) {
                        $category = $stores['_meta']['category'];
                    }
                    if (isset($stores['_meta']['name'])) {
                        $name = $stores['_meta']['name'];
                    }
                    
                    $result[] = [
                        'id' => $typeKey,
                        'name' => $name ?: ucfirst(str_replace('_', ' ', $typeKey)),
                        'category' => $category,
                        'messages' => $messages
                    ];
                }
                echo json_encode(['templates' => $result]);
            } else {
                echo json_encode(['templates' => []]);
            }
            break;
        
        case 'save-sms-template':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(['error' => 'POST required']);
                break;
            }
            $input = json_decode(file_get_contents('php://input'), true);
            $templatesFile = __DIR__ . '/sms-templates.json';
            
            $id = $input['id'] ?? '';
            $name = $input['name'] ?? '';
            $category = $input['category'] ?? 'custom';
            $messages = $input['messages'] ?? [];
            
            if (empty($id) || empty($name)) {
                echo json_encode(['error' => 'ID and name required']);
                break;
            }
            
            $data = [];
            if (file_exists($templatesFile)) {
                $data = json_decode(file_get_contents($templatesFile), true) ?: [];
            }
            
            // Ensure templates key exists
            if (!isset($data['templates'])) {
                $data = ['templates' => $data];
            }
            
            // Build template structure
            $templateData = [
                '_meta' => ['name' => $name, 'category' => $category]
            ];
            foreach ($messages as $storeCode => $message) {
                if (!empty($message)) {
                    $templateData[$storeCode] = [
                        'name' => $name,
                        'message' => $message
                    ];
                }
            }
            
            $data['templates'][$id] = $templateData;
            
            file_put_contents($templatesFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            echo json_encode(['success' => true]);
            break;
        
        case 'delete-sms-template':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(['error' => 'POST required']);
                break;
            }
            $input = json_decode(file_get_contents('php://input'), true);
            $templatesFile = __DIR__ . '/sms-templates.json';
            
            $id = $input['id'] ?? '';
            if (empty($id)) {
                echo json_encode(['error' => 'ID required']);
                break;
            }
            
            $data = [];
            if (file_exists($templatesFile)) {
                $data = json_decode(file_get_contents($templatesFile), true) ?: [];
            }
            
            if (!isset($data['templates'])) {
                $data = ['templates' => $data];
            }
            
            if (isset($data['templates'][$id])) {
                unset($data['templates'][$id]);
                file_put_contents($templatesFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['error' => 'Template not found']);
            }
            break;
            
        case 'sms-automations':
            $automationsFile = __DIR__ . '/data/sms-automations.json';
            if (file_exists($automationsFile)) {
                $automations = json_decode(file_get_contents($automationsFile), true);
                echo json_encode($automations ?: []);
            } else {
                echo json_encode([]);
            }
            break;
            
        case 'save-sms-automation':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(['error' => 'POST required']);
                break;
            }
            $input = json_decode(file_get_contents('php://input'), true);
            $automationsFile = __DIR__ . '/data/sms-automations.json';
            
            // Load existing
            $automations = [];
            if (file_exists($automationsFile)) {
                $automations = json_decode(file_get_contents($automationsFile), true) ?: [];
            }
            
            // Generate ID if new
            if (empty($input['id'])) {
                $input['id'] = 'auto_' . uniqid();
                $input['created_at'] = date('c');
                $input['sent_count'] = 0;
                $automations[] = $input;
            } else {
                // Update existing
                $found = false;
                foreach ($automations as &$a) {
                    if ($a['id'] === $input['id']) {
                        $input['sent_count'] = $a['sent_count'] ?? 0;
                        $input['created_at'] = $a['created_at'] ?? date('c');
                        $a = $input;
                        $found = true;
                        break;
                    }
                }
                unset($a);
                if (!$found) {
                    $automations[] = $input;
                }
            }
            
            file_put_contents($automationsFile, json_encode($automations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            echo json_encode(['success' => true, 'id' => $input['id']]);
            break;
            
        case 'delete-sms-automation':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(['error' => 'POST required']);
                break;
            }
            $input = json_decode(file_get_contents('php://input'), true);
            $automationsFile = __DIR__ . '/data/sms-automations.json';
            
            if (empty($input['id'])) {
                echo json_encode(['error' => 'ID required']);
                break;
            }
            
            $automations = [];
            if (file_exists($automationsFile)) {
                $automations = json_decode(file_get_contents($automationsFile), true) ?: [];
            }
            
            $automations = array_filter($automations, function($a) use ($input) {
                return $a['id'] !== $input['id'];
            });
            
            file_put_contents($automationsFile, json_encode(array_values($automations), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            echo json_encode(['success' => true]);
            break;
        
        case 'reset-automation-queue':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(['error' => 'POST required']);
                break;
            }
            $input = json_decode(file_get_contents('php://input'), true);
            $queuedCartsFile = __DIR__ . '/data/automation-queued-carts.json';
            
            if (empty($input['automation_id'])) {
                echo json_encode(['error' => 'automation_id required']);
                break;
            }
            
            $queuedCarts = [];
            if (file_exists($queuedCartsFile)) {
                $queuedCarts = json_decode(file_get_contents($queuedCartsFile), true) ?: [];
            }
            
            // Reset the queued carts for this automation
            $autoId = $input['automation_id'];
            $previousCount = count($queuedCarts[$autoId] ?? []);
            $queuedCarts[$autoId] = [];
            
            file_put_contents($queuedCartsFile, json_encode($queuedCarts, JSON_PRETTY_PRINT));
            
            // Also reset queued_count in automations file
            $automationsFile = __DIR__ . '/data/sms-automations.json';
            if (file_exists($automationsFile)) {
                $automations = json_decode(file_get_contents($automationsFile), true) ?: [];
                foreach ($automations as &$a) {
                    if ($a['id'] === $autoId) {
                        $a['queued_count'] = 0;
                        break;
                    }
                }
                unset($a);
                file_put_contents($automationsFile, json_encode($automations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            }
            
            echo json_encode(['success' => true, 'reset_count' => $previousCount]);
            break;
            
        case 'run-sms-automations':
            // Run automation check - adds to queue, never sends directly
            $result = runSmsAutomations();
            echo json_encode($result);
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
            // Also delete buyers-cache.json (separate file)
            $buyersCacheFile = __DIR__ . '/data/buyers-cache.json';
            if (file_exists($buyersCacheFile)) unlink($buyersCacheFile);
            echo json_encode(['success' => true]);
            break;
        
        case 'warm-cache':
            // Background cache warming - call this to pre-populate cache
            // Useful for cron job or after clear-cache
            ignore_user_abort(true);
            set_time_limit(120);
            
            $warmed = [];
            
            // Warm abandoned carts (fastest)
            fetchAbandonedCarts();
            $warmed[] = 'abandoned-carts';
            
            // Warm pending orders
            fetchPendingOrders();
            $warmed[] = 'pending-orders';
            
            // Warm one-time buyers (slowest - but now parallelized)
            fetchOneTimeBuyers();
            $warmed[] = 'one-time-buyers';
            
            echo json_encode(['success' => true, 'warmed' => $warmed]);
            break;
        
        case 'warm-buyers':
            // CRON: Only warm enkratni kupci
            // */5 * * * * curl -s https://callcenter.noriks.com/api.php?action=warm-buyers
            ignore_user_abort(true);
            set_time_limit(120);
            $buyers = fetchOneTimeBuyers();
            echo json_encode(['success' => true, 'count' => count($buyers), 'time' => date('c')]);
            break;
        
        case 'cron-sms-automation':
            // CRON: Run SMS automation checks
            // Recommended: */30 * * * * curl -s https://callcenter.noriks.com/api.php?action=cron-sms-automation
            // This adds SMS to queue for carts that match conditions. Never sends directly!
            ignore_user_abort(true);
            set_time_limit(120);
            $result = runSmsAutomations();
            echo json_encode([
                'success' => $result['success'] ?? false,
                'totalQueued' => $result['totalQueued'] ?? 0,
                'time' => date('c'),
                'results' => $result['results'] ?? []
            ]);
            break;
        
        case 'cache-status':
            // Check cache status for debugging
            global $cacheDir;
            $status = [];
            $cacheFiles = [
                'abandoned_carts_filtered' => 300,
                'pending_orders' => 300,
                'one_time_buyers_all' => 1800
            ];
            foreach ($cacheFiles as $key => $maxAge) {
                $file = $cacheDir . md5($key) . '.json';
                if (file_exists($file)) {
                    $age = time() - filemtime($file);
                    $status[$key] = [
                        'cached' => true,
                        'age_seconds' => $age,
                        'valid' => $age < $maxAge,
                        'expires_in' => max(0, $maxAge - $age)
                    ];
                } else {
                    $status[$key] = ['cached' => false];
                }
            }
            echo json_encode($status);
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
        
        case 'debug-call-logs':
            // Debug endpoint to check call logs
            $logs = loadCallLogs();
            echo json_encode([
                'count' => count($logs),
                'logs' => array_slice($logs, -10), // Last 10 logs
                'file' => realpath(__DIR__ . '/data/call-logs.json')
            ]);
            break;
        
        case 'complete-followup':
            $data = json_decode(file_get_contents('php://input'), true);
            $followupId = $data['id'] ?? '';
            if (empty($followupId)) {
                echo json_encode(['success' => false, 'error' => 'ID required']);
                break;
            }
            $logs = loadCallLogs();
            $found = false;
            foreach ($logs as &$log) {
                if ($log['id'] === $followupId) {
                    $log['status'] = 'completed';
                    $log['completed'] = true;
                    $log['completedAt'] = date('c');
                    $found = true;
                    break;
                }
            }
            if ($found) {
                saveCallLogs($logs);
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Follow-up not found']);
            }
            break;
        
        case 'delete-followup':
            $data = json_decode(file_get_contents('php://input'), true);
            $followupId = $data['id'] ?? '';
            if (empty($followupId)) {
                echo json_encode(['success' => false, 'error' => 'ID required']);
                break;
            }
            $logs = loadCallLogs();
            $originalCount = count($logs);
            $logs = array_values(array_filter($logs, fn($log) => $log['id'] !== $followupId));
            if (count($logs) < $originalCount) {
                saveCallLogs($logs);
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Follow-up not found']);
            }
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
            // FULL DEBUG - show exactly what MetaKocka returns
            set_time_limit(120);
            $debugResult = [];
            
            // Fetch 100 orders
            $ch = curl_init('https://main.metakocka.si/rest/eshop/v1/search');
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
                CURLOPT_POSTFIELDS => json_encode([
                    'secret_key' => 'ee759602-961d-4431-ac64-0725ae8d9665',
                    'company_id' => '6371',
                    'doc_type' => 'sales_order',
                    'result_type' => 'doc',
                    'limit' => 100,
                    'order_direction' => 'desc'
                ]),
                CURLOPT_TIMEOUT => 30
            ]);
            $response = curl_exec($ch);
            curl_close($ch);
            
            $data = json_decode($response, true);
            $orders = $data['result'] ?? [];
            
            // Group by status
            $byStatus = [];
            foreach ($orders as $o) {
                $status = $o['status_desc'] ?? 'unknown';
                if (!isset($byStatus[$status])) $byStatus[$status] = 0;
                $byStatus[$status]++;
            }
            $debugResult['total_orders'] = count($orders);
            $debugResult['by_status'] = $byStatus;
            
            // For shipped orders, get delivery events
            $shipped = array_filter($orders, fn($o) => ($o['status_desc'] ?? '') === 'shipped');
            $debugResult['shipped_count'] = count($shipped);
            
            $shippedDetails = [];
            $eventStatuses = []; // Collect all unique event statuses
            
            foreach (array_slice($shipped, 0, 20) as $order) { // First 20 shipped
                $mkId = $order['mk_id'] ?? null;
                if (!$mkId) continue;
                
                $ch = curl_init('https://main.metakocka.si/rest/eshop/v1/get_document');
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_POST => true,
                    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
                    CURLOPT_POSTFIELDS => json_encode([
                        'secret_key' => 'ee759602-961d-4431-ac64-0725ae8d9665',
                        'company_id' => '6371',
                        'doc_type' => 'sales_order',
                        'doc_id' => $mkId,
                        'return_delivery_service_events' => 'true',
                        'show_tracking_url' => 'true'
                    ]),
                    CURLOPT_TIMEOUT => 10
                ]);
                $docResponse = curl_exec($ch);
                curl_close($ch);
                
                $docData = json_decode($docResponse, true);
                $events = $docData['delivery_service_events'] ?? [];
                
                // Normalize
                if (is_array($events) && isset($events['event_status'])) {
                    $events = [$events];
                }
                
                $lastEventStatus = $events[0]['event_status'] ?? 'NO EVENTS';
                $eventStatuses[] = $lastEventStatus;
                
                $shippedDetails[] = [
                    'order' => $order['count_code'] ?? '',
                    'mk_id' => $mkId,
                    'events_count' => count($events),
                    'last_event' => $lastEventStatus,
                    'all_events' => array_map(fn($e) => $e['event_status'] ?? '', array_slice($events, 0, 3))
                ];
            }
            
            $debugResult['shipped_details'] = $shippedDetails;
            $debugResult['unique_event_statuses'] = array_unique($eventStatuses);
            
            echo json_encode($debugResult, JSON_PRETTY_PRINT);
            break;
            
        case 'refresh-paketomati-cache':
            // CRON: */15 * * * * curl -s "https://callcenter.noriks.com/api.php?action=refresh-paketomati-cache"
            set_time_limit(300);
            $cacheResult = buildPaketomatiCacheFull();
            echo json_encode([
                'success' => true,
                'paketomatCount' => count($cacheResult['orders'] ?? []),
                'stats' => $cacheResult['stats'] ?? [],
                'duration' => $cacheResult['duration_sec'] ?? 0
            ]);
            break;
            
        case 'mk-order-dump':
            // Dump FULL MetaKocka order JSON to find tracking URL field
            $mkId = $_GET['mk_id'] ?? '';
            $orderNum = $_GET['order'] ?? '';
            
            // If order number provided, first search for mk_id
            if (!$mkId && $orderNum) {
                $searchPayload = [
                    'secret_key' => 'ee759602-961d-4431-ac64-0725ae8d9665',
                    'company_id' => '6371',
                    'doc_type' => 'sales_order',
                    'result_type' => 'doc',
                    'limit' => 50,
                    'order_direction' => 'desc'
                ];
                $ch = curl_init('https://main.metakocka.si/rest/eshop/v1/search');
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_POST => true,
                    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
                    CURLOPT_POSTFIELDS => json_encode($searchPayload),
                    CURLOPT_TIMEOUT => 30
                ]);
                $searchResp = curl_exec($ch);
                curl_close($ch);
                $searchData = json_decode($searchResp, true);
                foreach ($searchData['result'] ?? [] as $o) {
                    if (strpos($o['count_code'] ?? '', $orderNum) !== false) {
                        $mkId = $o['mk_id'];
                        break;
                    }
                }
            }
            
            if (!$mkId) {
                echo json_encode(['error' => 'No mk_id found. Use ?mk_id=XXX or ?order=5278']);
                break;
            }
            
            // Get FULL document with all fields
            $docPayload = [
                'secret_key' => 'ee759602-961d-4431-ac64-0725ae8d9665',
                'company_id' => '6371',
                'doc_type' => 'sales_order',
                'doc_id' => $mkId,
                'return_delivery_service_events' => 'true',
                'show_tracking_url' => 'true'
            ];
            $ch = curl_init('https://main.metakocka.si/rest/eshop/v1/get_document');
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
                CURLOPT_POSTFIELDS => json_encode($docPayload),
                CURLOPT_TIMEOUT => 15
            ]);
            $docResp = curl_exec($ch);
            curl_close($ch);
            $fullOrder = json_decode($docResp, true);
            
            // Output full JSON for inspection
            header('Content-Type: application/json');
            echo json_encode([
                'mk_id' => $mkId,
                'order_number' => $fullOrder['count_code'] ?? 'unknown',
                'FULL_ORDER_DATA' => $fullOrder
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            break;
            
        case 'paketomati-raw':
            // Raw debug - check specific orders
            $rawDebug = [];
            $testOrders = ['1200043985993', '1200043941068']; // 5278 and 5239
            
            // First get mk_id for 5239
            $searchPayload = [
                'secret_key' => 'ee759602-961d-4431-ac64-0725ae8d9665',
                'company_id' => '6371',
                'doc_type' => 'sales_order',
                'result_type' => 'doc',
                'limit' => 100,
                'order_direction' => 'desc'
            ];
            $ch = curl_init('https://main.metakocka.si/rest/eshop/v1/search');
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
                CURLOPT_POSTFIELDS => json_encode($searchPayload),
                CURLOPT_TIMEOUT => 30
            ]);
            $searchResp = curl_exec($ch);
            curl_close($ch);
            $searchData = json_decode($searchResp, true);
            
            // Find 5278 and 5239
            $found = [];
            foreach ($searchData['result'] ?? [] as $i => $o) {
                if (strpos($o['count_code'] ?? '', '5278') !== false || strpos($o['count_code'] ?? '', '5239') !== false) {
                    $found[] = ['position' => $i, 'mk_id' => $o['mk_id'], 'count_code' => $o['count_code']];
                }
            }
            $rawDebug['found_orders'] = $found;
            
            // Check delivery events for each
            foreach ($found as $f) {
                $docPayload = [
                    'secret_key' => 'ee759602-961d-4431-ac64-0725ae8d9665',
                    'company_id' => '6371',
                    'doc_type' => 'sales_order',
                    'doc_id' => $f['mk_id'],
                    'return_delivery_service_events' => 'true',
                    'show_tracking_url' => 'true'
                ];
                $ch = curl_init('https://main.metakocka.si/rest/eshop/v1/get_document');
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_POST => true,
                    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
                    CURLOPT_POSTFIELDS => json_encode($docPayload),
                    CURLOPT_TIMEOUT => 10
                ]);
                $docResp = curl_exec($ch);
                curl_close($ch);
                $docData = json_decode($docResp, true);
                $events = $docData['delivery_service_events'] ?? [];
                if (is_array($events) && isset($events['event_status'])) {
                    $events = [$events];
                }
                $rawDebug['events_' . $f['count_code']] = $events;
            }
            
            echo json_encode($rawDebug, JSON_PRETTY_PRINT);
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
                    'sms-send-direct',
                    'agents-list',
                    'agents-add',
                    'agents-update',
                    'agents-delete',
                    'call-logs',
                    'call-logs-add',
                    'call-logs-customer',
                    'my-followups',
                    'debug-call-logs',
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
