<?php
/**
 * Production-oriented performance and monitoring helpers for Wichtelwerken.
 */

defined('ABSPATH') || exit;

add_action('init', 'ww_configure_private_error_log', 0);
function ww_configure_private_error_log() {
    $log_dir = defined('WW_PRIVATE_LOG_DIR')
        ? WW_PRIVATE_LOG_DIR
        : dirname(WP_CONTENT_DIR) . '/private-logs';

    if (!is_dir($log_dir)) {
        wp_mkdir_p($log_dir);
    }

    if (is_dir($log_dir) && is_writable($log_dir)) {
        ini_set('log_errors', '1');
        ini_set('error_log', trailingslashit($log_dir) . 'php-errors.log');
    }
}

add_action('init', 'ww_disable_unneeded_wordpress_assets');
function ww_disable_unneeded_wordpress_assets() {
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('wp_print_styles', 'print_emoji_styles');
    remove_action('admin_print_scripts', 'print_emoji_detection_script');
    remove_action('admin_print_styles', 'print_emoji_styles');
    remove_filter('the_content_feed', 'wp_staticize_emoji');
    remove_filter('comment_text_rss', 'wp_staticize_emoji');
    remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
    remove_action('wp_head', 'wp_generator');
    remove_action('wp_head', 'wp_oembed_add_discovery_links');
    remove_action('wp_head', 'wp_oembed_add_host_js');
}

add_filter('emoji_svg_url', '__return_false');

add_action('wp_enqueue_scripts', 'ww_optimize_frontend_scripts', 100);
function ww_optimize_frontend_scripts() {
    if (!is_admin()) {
        wp_deregister_script('wp-embed');
    }
}

function ww_should_keep_cart_fragments() {
    return function_exists('is_woocommerce') && (
        is_front_page()
        || is_woocommerce()
        || is_cart()
        || is_checkout()
        || is_account_page()
    );
}

add_action('wp_print_scripts', 'ww_dequeue_static_page_cart_fragments', 100);
add_action('wp_print_footer_scripts', 'ww_dequeue_static_page_cart_fragments', 1);
function ww_dequeue_static_page_cart_fragments() {
    if (!ww_should_keep_cart_fragments()) {
        wp_dequeue_script('wc-cart-fragments');
        wp_deregister_script('wc-cart-fragments');
    }
}

add_filter('wp_get_attachment_image_attributes', 'ww_tune_image_loading_attributes', 10, 3);
function ww_tune_image_loading_attributes($attr, $attachment, $size) {
    if (!is_array($attr)) {
        return $attr;
    }

    if (empty($attr['loading'])) {
        $attr['loading'] = 'lazy';
    }

    if (is_front_page() && $size === 'woocommerce_thumbnail') {
        static $first_frontpage_product_image = true;
        if ($first_frontpage_product_image) {
            $attr['loading'] = 'eager';
            $attr['fetchpriority'] = 'high';
            $first_frontpage_product_image = false;
        }
    }

    return $attr;
}

add_action('rest_api_init', 'ww_register_health_endpoint');
function ww_register_health_endpoint() {
    register_rest_route('wichtelwerken/v1', '/health', [
        'methods'             => 'GET',
        'callback'            => 'ww_health_endpoint_response',
        'permission_callback' => '__return_true',
    ]);
}

function ww_health_endpoint_response(WP_REST_Request $request) {
    global $wpdb;

    $started = microtime(true);
    $db_ok = $wpdb->get_var('SELECT 1') === '1';
    $status_code = $db_ok ? 200 : 503;

    $payload = [
        'status' => $db_ok ? 'ok' : 'degraded',
        'time_utc' => gmdate('c'),
        'site' => parse_url(home_url('/'), PHP_URL_HOST),
        'checks' => [
            'database' => $db_ok ? 'ok' : 'fail',
        ],
        'duration_ms' => round((microtime(true) - $started) * 1000, 2),
    ];

    $token = defined('WW_MONITORING_TOKEN') ? (string) WW_MONITORING_TOKEN : '';
    $request_token = (string) ($request->get_header('x-wichtelwerken-monitoring-token') ?: $request->get_param('token'));

    if ($token !== '' && hash_equals($token, $request_token)) {
        $payload['debug'] = [
            'wp_debug' => defined('WP_DEBUG') && WP_DEBUG,
            'wp_debug_log' => defined('WP_DEBUG_LOG') && WP_DEBUG_LOG,
            'private_log_dir_configured' => ini_get('error_log') !== '',
        ];
    }

    $response = new WP_REST_Response($payload, $status_code);
    $response->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');

    return $response;
}
