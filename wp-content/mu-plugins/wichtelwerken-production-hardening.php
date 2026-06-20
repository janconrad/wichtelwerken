<?php
/**
 * Production hardening hooks for Wichtelwerken.
 */

defined('ABSPATH') || exit;

add_filter('xmlrpc_enabled', '__return_false');
add_filter('xmlrpc_methods', 'ww_disable_xmlrpc_methods');
function ww_disable_xmlrpc_methods($methods) {
    return [];
}

add_filter('wp_headers', 'ww_remove_pingback_header');
function ww_remove_pingback_header($headers) {
    unset($headers['X-Pingback']);

    return $headers;
}

add_action('send_headers', 'ww_send_production_security_headers');
function ww_send_production_security_headers() {
    if (headers_sent()) {
        return;
    }

    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: accelerometer=(), camera=(), geolocation=(), gyroscope=(), magnetometer=(), microphone=(), payment=(self), usb=()');

    if (ww_truthy_env('WW_ENABLE_HSTS') && is_ssl()) {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
    }
}

add_action('phpmailer_init', 'ww_configure_smtp_from_environment');
function ww_configure_smtp_from_environment($phpmailer) {
    $host = ww_env('WW_SMTP_HOST');

    if ($host === '') {
        return;
    }

    $phpmailer->isSMTP();
    $phpmailer->Host = $host;
    $phpmailer->Port = (int) (ww_env('WW_SMTP_PORT') ?: 587);
    $phpmailer->SMTPAuth = ww_env('WW_SMTP_USER') !== '' || ww_truthy_env('WW_SMTP_AUTH');

    $secure = strtolower(ww_env('WW_SMTP_SECURE') ?: 'tls');
    if (in_array($secure, ['tls', 'ssl'], true)) {
        $phpmailer->SMTPSecure = $secure;
    }

    $username = ww_env('WW_SMTP_USER');
    if ($username !== '') {
        $phpmailer->Username = $username;
    }

    $password = ww_env('WW_SMTP_PASS');
    if ($password !== '') {
        $phpmailer->Password = $password;
    }
}

add_filter('wp_mail_from', 'ww_mail_from_address_from_environment');
function ww_mail_from_address_from_environment($from) {
    $configured_from = ww_env('WW_MAIL_FROM');

    return $configured_from !== '' ? $configured_from : $from;
}

add_filter('wp_mail_from_name', 'ww_mail_from_name_from_environment');
function ww_mail_from_name_from_environment($name) {
    $configured_name = ww_env('WW_MAIL_FROM_NAME');

    return $configured_name !== '' ? $configured_name : $name;
}

function ww_env($key) {
    $value = getenv($key);

    return is_string($value) ? trim($value) : '';
}

function ww_truthy_env($key) {
    return in_array(strtolower(ww_env($key)), ['1', 'true', 'yes', 'on'], true);
}
