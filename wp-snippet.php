/**
 * REST API endpoint for abandoned carts
 * Add this to your theme's functions.php or as mu-plugin
 */
add_action('rest_api_init', function() {
    register_rest_route('noriks/v1', '/abandoned-carts', array(
        'methods' => 'GET',
        'callback' => 'noriks_get_abandoned_carts',
        'permission_callback' => function() {
            // Simple API key check
            return isset($_GET['key']) && $_GET['key'] === 'n0r1k5-c4rt-4cc355';
        }
    ));
});

function noriks_get_abandoned_carts($request) {
    global $wpdb;
    
    $table = $wpdb->prefix . 'cartflows_ca_cart_abandonment';
    
    // Check if table exists
    if($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {
        return new WP_REST_Response(['error' => 'Table not found'], 404);
    }
    
    $results = $wpdb->get_results("
        SELECT 
            id,
            email,
            cart_contents,
            cart_total,
            session_id,
            other_fields,
            checkout_id,
            order_status,
            unsubscribed,
            time
        FROM $table 
        WHERE order_status = 'abandoned'
        ORDER BY time DESC 
        LIMIT 500
    ", ARRAY_A);
    
    // Parse cart_contents JSON
    foreach($results as &$row) {
        if(!empty($row['cart_contents'])) {
            $row['cart_contents'] = maybe_unserialize($row['cart_contents']);
        }
        if(!empty($row['other_fields'])) {
            $row['other_fields'] = maybe_unserialize($row['other_fields']);
        }
    }
    
    return new WP_REST_Response($results, 200);
}
