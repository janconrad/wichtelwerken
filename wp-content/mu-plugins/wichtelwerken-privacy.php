<?php
/**
 * Privacy bootstrap for Wichtelwerken.
 *
 * Complianz decides whether to enqueue the banner before the child theme is
 * loaded, so this filter has to live in a must-use plugin.
 */

defined('ABSPATH') || exit;

add_filter('cmplz_site_needs_cookiewarning', '__return_true');
