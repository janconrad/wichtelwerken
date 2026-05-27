<?php
/**
 * Wichtelwerken Child Theme – functions.php v2
 * Design: Lovable-Stil (heller Header, Foto-Hero)
 */

defined('ABSPATH') || exit;

// =========================================================
// 1. ASSETS EINBINDEN
// =========================================================
add_action('wp_enqueue_scripts', 'ww_enqueue_assets');
function ww_enqueue_assets() {
    wp_enqueue_style(
        'storefront-parent',
        get_template_directory_uri() . '/style.css',
        [],
        wp_get_theme('storefront')->get('Version')
    );
    wp_enqueue_style(
        'wichtelwerken-child',
        get_stylesheet_uri(),
        ['storefront-parent'],
        '2.0.24'
    );
    wp_enqueue_script(
        'wichtelwerken-main',
        get_stylesheet_directory_uri() . '/js/main.js',
        [],
        '2.0.2',
        true
    );
}

add_action('wp_head', 'ww_preload_critical_theme_assets', 2);
function ww_preload_critical_theme_assets() {
    if (!is_front_page()) {
        return;
    }

    printf(
        '<link rel="preload" as="image" href="%s" type="image/webp" fetchpriority="high">' . "\n",
        esc_url(get_stylesheet_directory_uri() . '/images/hero-bg.webp')
    );
}

function ww_is_ip_host_request() {
    return !empty($_SERVER['HTTP_HOST'])
        && preg_match('/^\d{1,3}(?:\.\d{1,3}){3}(?::\d+)?$/', $_SERVER['HTTP_HOST']);
}

function ww_get_current_request_url_base() {
    $scheme = is_ssl() ? 'https' : 'http';

    return $scheme . '://' . $_SERVER['HTTP_HOST'];
}

add_action('template_redirect', 'ww_keep_internal_links_on_ip_host', 0);
function ww_keep_internal_links_on_ip_host() {
    if (is_admin() || wp_doing_ajax() || !ww_is_ip_host_request()) {
        return;
    }

    ob_start(function($html) {
        $current_base = ww_get_current_request_url_base();

        return str_replace(
            [
                'https://wichtelwerken.ddev.site',
                'http://wichtelwerken.ddev.site',
                'https:\/\/wichtelwerken.ddev.site',
                'http:\/\/wichtelwerken.ddev.site',
            ],
            [
                $current_base,
                $current_base,
                str_replace('/', '\/', $current_base),
                str_replace('/', '\/', $current_base),
            ],
            $html
        );
    });
}

add_action('wp_enqueue_scripts', 'ww_disable_external_parent_fonts', 100);
function ww_disable_external_parent_fonts() {
    wp_dequeue_style('storefront-fonts');
    wp_deregister_style('storefront-fonts');
}

add_filter('wp_resource_hints', 'ww_remove_external_font_resource_hints', 10, 2);
function ww_remove_external_font_resource_hints($urls, $relation_type) {
    if (!in_array($relation_type, ['dns-prefetch', 'preconnect'], true)) {
        return $urls;
    }

    return array_values(array_filter($urls, function($url) {
        $url = is_array($url) && isset($url['href']) ? $url['href'] : $url;
        return !str_contains((string) $url, 'fonts.googleapis.com')
            && !str_contains((string) $url, 'fonts.gstatic.com');
    }));
}

function ww_get_shop_url() {
    if (function_exists('wc_get_page_id')) {
        $shop_page_id = wc_get_page_id('shop');
        if ($shop_page_id > 0) {
            return get_permalink($shop_page_id);
        }
    }

    return home_url('/shop/');
}

function ww_get_vendor_signup_url() {
    if (function_exists('dokan_get_navigation_url')) {
        return dokan_get_navigation_url('new-product');
    }

    return home_url('/anbieter-werden/');
}

add_filter('storefront_credit_links_output', 'ww_footer_legal_links');
function ww_footer_legal_links($links_output) {
    $links = [
        ['Impressum', home_url('/impressum/')],
        ['AGB Käufer', home_url('/agb-nutzer/')],
        ['AGB Anbieter', home_url('/agb-anbieter/')],
        ['Datenschutz', get_privacy_policy_url() ?: home_url('/datenschutzerklaerung/')],
        ['Cookie-Richtlinie', home_url('/cookie-richtlinie/')],
        ['Widerruf & Rückgabe', home_url('/widerruf-rueckgabe/')],
        ['Meldestelle', home_url('/meldestelle/')],
        ['Barrierefreiheit', home_url('/barrierefreiheit/')],
    ];

    $legal_links = array_map(
        fn($link) => sprintf('<a href="%s">%s</a>', esc_url($link[1]), esc_html($link[0])),
        $links
    );

    return implode('<span role="separator" aria-hidden="true"></span>', $legal_links);
}

// =========================================================
// 2. THEME SETUP
// =========================================================
add_action('after_setup_theme', 'ww_theme_setup');
function ww_theme_setup() {
    add_theme_support('woocommerce');
    add_theme_support('wc-product-gallery-zoom');
    add_theme_support('wc-product-gallery-lightbox');
    add_theme_support('wc-product-gallery-slider');
    add_theme_support('post-thumbnails');
    add_theme_support('title-tag');
    add_theme_support('custom-logo', [
        'height'      => 311,
        'width'       => 640,
        'flex-height' => true,
        'flex-width'  => true,
    ]);
    add_theme_support('html5', ['search-form','comment-form','gallery','caption']);
    register_nav_menus([
        'primary'     => 'Hauptnavigation',
        'footer_menu' => 'Footer Navigation',
    ]);
}

// =========================================================
// 3. STOREFRONT HEADER ANPASSEN
//    Storefront baut den Header über Actions – wir hängen uns ein
// =========================================================

// Storefront Standard-Header-Hintergrund entfernen (war dunkel in v1)
add_action('wp_head', 'ww_header_inline_styles');
function ww_header_inline_styles() { ?>
<style>
  /* Storefront Override – heller Header */
  .site-header { background-color: var(--ww-cream) !important; }
  /* Scrolled State wird per JS gesetzt */
  .site-header.ww-scrolled { box-shadow: 0 2px 12px rgba(45,74,36,0.08); }
</style>
<?php }

add_action('wp_head', 'ww_restore_complianz_consent_before_banner_loads', 1);
function ww_restore_complianz_consent_before_banner_loads() {
    $policy_id = '1';
    if (class_exists('COMPLIANZ') && isset(COMPLIANZ::$banner_loader) && method_exists(COMPLIANZ::$banner_loader, 'get_active_policy_id')) {
        $policy_id = (string) COMPLIANZ::$banner_loader->get_active_policy_id();
    }
    ?>
<script>
  (function () {
    const consentNames = [
      'cmplz_consented_services',
      'cmplz_policy_id',
      'cmplz_marketing',
      'cmplz_statistics',
      'cmplz_preferences',
      'cmplz_functional',
      'cmplz_banner-status'
    ];
    const storageKey = 'ww_cmplz_consent_v1';
    const maxAge = 31536000;
    const policyId = '<?php echo esc_js($policy_id); ?>';

    const readCookies = function () {
      return document.cookie.split(';').reduce(function (cookies, cookie) {
        const trimmed = cookie.trim();
        const separator = trimmed.indexOf('=');
        if (separator > -1) {
          cookies[trimmed.slice(0, separator)] = trimmed.slice(separator + 1);
        }

        return cookies;
      }, {});
    };

    const writeRootCookie = function (name, value) {
      document.cookie = name + '=' + value + '; path=/; max-age=' + maxAge + '; SameSite=Lax';
    };

    const persistConsentValues = function (values) {
      values['cmplz_banner-status'] = 'dismissed';
      values['cmplz_policy_id'] = values['cmplz_policy_id'] || policyId || '1';
      values['cmplz_functional'] = values['cmplz_functional'] || 'allow';

      consentNames.forEach(function (name) {
        if (Object.prototype.hasOwnProperty.call(values, name)) {
          writeRootCookie(name, values[name]);
        }
      });

      try {
        window.localStorage.setItem(storageKey, JSON.stringify(values));
      } catch (error) {
        // Consent cookies are still written even when localStorage is blocked.
      }
    };

    window.wwRestoreComplianzRootConsent = function () {
      try {
        const stored = window.localStorage.getItem(storageKey);
        if (!stored) {
          return;
        }

        const values = JSON.parse(stored);
        if (!values || values['cmplz_banner-status'] !== 'dismissed') {
          return;
        }

        persistConsentValues(values);
      } catch (error) {
        // localStorage may be unavailable in private browsing modes.
      }
    };

    window.wwStoreComplianzRootConsent = function () {
      const cookies = readCookies();
      const values = {};

      consentNames.forEach(function (name) {
        if (Object.prototype.hasOwnProperty.call(cookies, name)) {
          values[name] = cookies[name];
          writeRootCookie(name, cookies[name]);
        }
      });

      if (values['cmplz_banner-status'] === 'dismissed') {
        persistConsentValues(values);
      }
    };

    window.wwRestoreComplianzRootConsent();

    document.addEventListener('click', function (event) {
      if (event.target.closest('.cmplz-accept')) {
        persistConsentValues({
          'cmplz_consented_services': '',
          'cmplz_marketing': 'allow',
          'cmplz_statistics': 'allow',
          'cmplz_preferences': 'allow',
          'cmplz_functional': 'allow'
        });
      }

      if (event.target.closest('.cmplz-deny')) {
        persistConsentValues({
          'cmplz_consented_services': '',
          'cmplz_marketing': 'deny',
          'cmplz_statistics': 'deny',
          'cmplz_preferences': 'deny',
          'cmplz_functional': 'allow'
        });
      }

      if (event.target.closest('.cmplz-accept, .cmplz-deny, .cmplz-save-preferences, .cmplz-close')) {
        window.setTimeout(window.wwStoreComplianzRootConsent, 50);
        window.setTimeout(window.wwStoreComplianzRootConsent, 250);
        window.setTimeout(window.wwStoreComplianzRootConsent, 1000);
        window.setTimeout(window.wwStoreComplianzRootConsent, 2000);
      }
    }, true);
  })();
</script>
<?php }

add_action('after_setup_theme', 'ww_disable_storefront_mobile_footer', 20);
function ww_disable_storefront_mobile_footer() {
    remove_action('storefront_footer', 'storefront_handheld_footer_bar', 999);
}

// =========================================================
// 4. WOOCOMMERCE ANPASSUNGEN
// =========================================================
add_filter('loop_shop_per_page',  fn() => 12, 20);
add_filter('loop_shop_columns',   fn() => 3);

add_action('wp', 'ww_tune_woocommerce_loop_controls');
function ww_tune_woocommerce_loop_controls() {
    remove_action('woocommerce_before_shop_loop', 'storefront_woocommerce_pagination', 30);
    remove_action('woocommerce_after_shop_loop', 'storefront_sorting_wrapper', 9);
    remove_action('woocommerce_after_shop_loop', 'woocommerce_catalog_ordering', 10);
    remove_action('woocommerce_after_shop_loop', 'woocommerce_result_count', 20);
    remove_action('woocommerce_after_shop_loop', 'storefront_sorting_wrapper_close', 31);
}

add_action('wp', 'ww_hide_global_sidebar');
function ww_hide_global_sidebar() {
    remove_action('storefront_sidebar', 'storefront_get_sidebar', 10);
}

add_filter('body_class', 'ww_full_width_body_class');
function ww_full_width_body_class($classes) {
    $classes[] = 'storefront-full-width-content';
    $classes[] = 'ww-full-width-content';

    return $classes;
}

add_filter('woocommerce_breadcrumb_defaults', function($d) {
    $d['home'] = 'Marktplatz'; return $d;
});

add_action('woocommerce_before_shop_loop', 'ww_shop_category_filter', 5);
function ww_shop_category_filter() {
    if (!is_shop() && !is_product_category()) {
        return;
    }

    $categories = get_terms([
        'taxonomy'   => 'product_cat',
        'hide_empty' => true,
        'parent'     => 0,
        'orderby'    => 'name',
        'order'      => 'ASC',
    ]);

    if (empty($categories) || is_wp_error($categories)) {
        return;
    }

    $current = is_product_category() ? get_queried_object() : null;

    echo '<nav class="ww-shop-categories" aria-label="Produktkategorien">';
    printf(
        '<a class="ww-shop-category %s" href="%s"><span class="ww-shop-category__icon">%s</span><span>Alle Produkte</span></a>',
        is_shop() ? 'is-active' : '',
        esc_url(ww_get_shop_url()),
        '✨'
    );

    foreach ($categories as $category) {
        $is_active = $current && isset($current->term_id) && (int) $current->term_id === (int) $category->term_id;
        printf(
            '<a class="ww-shop-category %s" href="%s"><span class="ww-shop-category__icon">%s</span><span>%s</span></a>',
            $is_active ? 'is-active' : '',
            esc_url(get_term_link($category)),
            esc_html(ww_get_product_category_icon($category, '🌿')),
            esc_html($category->name)
        );
    }

    echo '</nav>';
}

function ww_get_product_category_icon($term, $fallback = '🌿') {
    $name = strtolower(html_entity_decode($term->name, ENT_QUOTES, 'UTF-8'));
    $slug = strtolower($term->slug);

    if (str_contains($name, 'buch') || str_contains($slug, 'buch') || str_contains($slug, 'buech')) {
        return '📖';
    }

    if (str_contains($name, 'tee') || str_contains($slug, 'tee')) {
        return '🫖';
    }

    return $fallback;
}

// "In den Warenkorb" Text
add_filter('woocommerce_product_add_to_cart_text', function($text, $product) {
    if ($product instanceof WC_Product && !$product->is_in_stock()) {
        return 'Nicht vorrätig';
    }

    return 'In den Warenkorb';
}, 10, 2);
add_filter('woocommerce_product_single_add_to_cart_text', fn() => 'In den Warenkorb legen');

add_filter('option_woocommerce_tax_display_cart', fn() => 'incl');
add_filter('option_woocommerce_tax_display_shop', fn() => 'incl');

add_filter('woocommerce_get_price_suffix', 'ww_product_specific_price_suffix', 20, 4);
function ww_product_specific_price_suffix($suffix, $product, $price = '', $qty = 1) {
    if (!$product instanceof WC_Product) {
        return $suffix;
    }

    if ($product->get_tax_status() !== 'taxable' || $product->get_tax_class() === 'zero-rate') {
        return ' <small class="woocommerce-price-suffix">keine MwSt. ausgewiesen</small>';
    }

    return ' <small class="woocommerce-price-suffix">inkl. MwSt.</small>';
}

add_filter('woocommerce_get_privacy_policy_text', 'ww_translate_woocommerce_privacy_policy_text', 10, 2);
function ww_translate_woocommerce_privacy_policy_text($text, $type) {
    if ($type === 'checkout' && str_contains($text, 'Your personal data will be used to process your order')) {
        return 'Deine personenbezogenen Daten werden verwendet, um deine Bestellung zu bearbeiten, dein Nutzungserlebnis auf dieser Website zu unterstützen und für weitere Zwecke, die in unserer [privacy_policy] beschrieben sind.';
    }

    if ($type === 'registration' && str_contains($text, 'Your personal data will be used to support your experience')) {
        return 'Deine personenbezogenen Daten werden verwendet, um dein Nutzungserlebnis auf dieser Website zu unterstützen, den Zugriff auf dein Konto zu verwalten und für weitere Zwecke, die in unserer [privacy_policy] beschrieben sind.';
    }

    return $text;
}

// Vendor Badge in der Produktliste
add_action('woocommerce_after_shop_loop_item_title', 'ww_vendor_badge_loop', 5);
function ww_vendor_badge_loop() {
    if (!function_exists('dokan_get_vendor_by_product')) return;
    global $product;
    $vendor = dokan_get_vendor_by_product($product->get_id());
    if ($vendor) {
        printf(
            '<a href="%s" class="ww-vendor-badge">%s</a>',
            esc_url($vendor->get_shop_url()),
            esc_html($vendor->get_shop_name())
        );
    }
}

// =========================================================
// 5. DOKAN
// =========================================================
add_filter('dokan_get_seller_percentage', fn() => 95);

add_action('dokan_new_seller_created', 'ww_new_vendor_setup', 10, 2);
function ww_new_vendor_setup($seller_id, $dokan_settings) {
    $user = get_userdata($seller_id);
    ww_set_vendor_review_status($seller_id, 'incomplete', false);

    wp_mail(
        $user->user_email,
        'Willkommen bei Wichtelwerken! 🌿',
        ww_vendor_welcome_email($user->display_name),
        ['Content-Type: text/html; charset=UTF-8']
    );
}

function ww_vendor_welcome_email($name) {
    $dashboard_url = function_exists('dokan_get_navigation_url')
        ? dokan_get_navigation_url('settings') : home_url('/dashboard');
    return '
    <div style="font-family:\'Lato\',Arial,sans-serif;max-width:600px;margin:0 auto;background:#f9f5f0;">
      <div style="background:#2d4a24;padding:28px 32px;text-align:center;">
        <h1 style="color:#f9f5f0;font-family:Georgia,serif;font-size:28px;margin:0;font-weight:400;">
          Wichtelwerken
        </h1>
        <p style="color:#d4789a;font-size:11px;letter-spacing:1px;margin:6px 0 0;text-transform:uppercase;">
          öko, fair & selbstgemacht
        </p>
      </div>
      <div style="padding:40px 32px;">
        <h2 style="color:#2d4a24;font-family:Georgia,serif;font-weight:400;font-size:26px;">
          Herzlich Willkommen, ' . esc_html($name) . '!
        </h2>
        <p style="color:#6b7c6b;line-height:1.8;margin:16px 0;">
          Schön, dass du Teil unserer Gemeinschaft bist. Richte jetzt deinen Shop ein
          und stell deine ersten Artikel ein.
        </p>
        <ul style="color:#6b7c6b;line-height:2.2;padding-left:20px;">
          <li>✅ Shop-Profil vervollständigen</li>
          <li>✅ Erste Produkte einstellen</li>
          <li>✅ Versandoptionen konfigurieren</li>
          <li>✅ Auszahlungskonto hinterlegen</li>
        </ul>
        <a href="' . esc_url($dashboard_url) . '"
           style="display:inline-block;background:#4a7a3a;color:#fff;
                  padding:13px 28px;border-radius:999px;text-decoration:none;
                  font-weight:500;margin-top:24px;font-size:15px;">
          Zum Dashboard →
        </a>
      </div>
      <div style="background:#1c1c1a;padding:16px 32px;text-align:center;">
        <p style="color:rgba(255,255,255,0.4);font-size:12px;margin:0;">
          Wichtelwerken UG &middot;
          <a href="' . home_url('/impressum') . '" style="color:#d4789a;">Impressum</a> &middot;
          <a href="' . home_url('/datenschutz') . '" style="color:#d4789a;">Datenschutz</a>
        </p>
      </div>
    </div>';
}

function ww_product_vat_options() {
    return [
        '' => [
            'label' => 'Regulärer MwSt.-Satz (19 %)',
            'hint' => 'Für die meisten Produkte und Leistungen.',
        ],
        'reduced-rate' => [
            'label' => 'Ermäßigter MwSt.-Satz (7 %)',
            'hint' => 'Zum Beispiel für Bücher und viele Lebensmittel/Tees.',
        ],
        'zero-rate' => [
            'label' => '0 % / keine MwSt. ausweisen',
            'hint' => 'Nur verwenden, wenn dafür eine steuerliche Grundlage besteht.',
        ],
    ];
}

function ww_allowed_product_vat_classes() {
    return array_keys(ww_product_vat_options());
}

function ww_sanitize_product_vat_class($tax_class) {
    $tax_class = is_string($tax_class) ? sanitize_title($tax_class) : '';

    return in_array($tax_class, ww_allowed_product_vat_classes(), true) ? $tax_class : '';
}

function ww_render_product_vat_select($current_class = '') {
    $current_class = ww_sanitize_product_vat_class($current_class);
    ?>
    <div class="dokan-form-group ww-product-vat-field">
      <label for="ww-product-tax-class">Mehrwertsteuer</label>
      <input type="hidden" name="_tax_status" value="taxable">
      <select id="ww-product-tax-class" name="_tax_class" class="dokan-form-control" required>
        <?php foreach (ww_product_vat_options() as $class => $option) : ?>
          <option value="<?php echo esc_attr($class); ?>" <?php selected($current_class, $class); ?>>
            <?php echo esc_html($option['label']); ?>
          </option>
        <?php endforeach; ?>
      </select>
      <p class="help-block">Bitte für jedes Produkt prüfen. <?php echo esc_html(ww_product_vat_options()[$current_class]['hint']); ?></p>
    </div>
    <?php
}

add_action('dokan_product_edit_after_pricing_fields', 'ww_render_dokan_product_vat_field_edit', 20, 2);
function ww_render_dokan_product_vat_field_edit($post, $post_id) {
    $product = wc_get_product($post_id);
    $current_class = $product instanceof WC_Product ? $product->get_tax_class() : '';

    ww_render_product_vat_select($current_class);
}

add_action('dokan_new_product_after_product_category', 'ww_render_dokan_product_vat_field_new', 20);
function ww_render_dokan_product_vat_field_new() {
    ww_render_product_vat_select('');
}

add_filter('dokan_new_product_popup_args', 'ww_validate_dokan_product_vat_field', 20, 2);
function ww_validate_dokan_product_vat_field($errors, $data) {
    if (is_wp_error($errors)) {
        return $errors;
    }

    if (!isset($data['_tax_class'])) {
        return $errors;
    }

    $tax_class = ww_sanitize_product_vat_class(wc_clean(wp_unslash($data['_tax_class'])));
    if ($tax_class !== wc_clean(wp_unslash($data['_tax_class']))) {
        return new WP_Error('ww_invalid_tax_class', __('Bitte wähle einen gültigen Mehrwertsteuersatz.', 'wichtelwerken'));
    }

    return $errors;
}

add_action('dokan_new_product_added', 'ww_save_dokan_product_vat_field', 20, 2);
add_action('dokan_product_updated', 'ww_save_dokan_product_vat_field', 20, 2);
function ww_save_dokan_product_vat_field($product_id, $data = []) {
    if (!isset($data['_tax_class'])) {
        return;
    }

    $product = wc_get_product($product_id);
    if (!$product instanceof WC_Product) {
        return;
    }

    $product->set_tax_status('taxable');
    $product->set_tax_class(ww_sanitize_product_vat_class(wc_clean(wp_unslash($data['_tax_class']))));
    $product->save();
}

function ww_vendor_legal_field_definitions() {
    return [
        'business_name' => [
            'label' => 'Rechtlicher Name / Firma',
            'type' => 'text',
            'public' => true,
            'required' => true,
            'help' => 'Name des Unternehmens oder der verantwortlichen Anbieterperson.',
        ],
        'representative' => [
            'label' => 'Vertretungsberechtigte Person',
            'type' => 'text',
            'public' => true,
            'required' => false,
            'help' => 'Bei Einzelunternehmen optional, bei Gesellschaften erforderlich.',
        ],
        'legal_address' => [
            'label' => 'Ladungsfähige Anschrift',
            'type' => 'textarea',
            'public' => true,
            'required' => true,
            'help' => 'Straße, Hausnummer, PLZ, Ort und Land. Postfach reicht nicht.',
        ],
        'legal_email' => [
            'label' => 'E-Mail für Kunden und rechtliche Anfragen',
            'type' => 'email',
            'public' => true,
            'required' => true,
            'help' => 'Diese Adresse kann im Shop-Impressum angezeigt werden.',
        ],
        'legal_phone' => [
            'label' => 'Telefon',
            'type' => 'text',
            'public' => true,
            'required' => false,
            'help' => 'Falls geschäftlich vorhanden.',
        ],
        'register_details' => [
            'label' => 'Registerangaben',
            'type' => 'text',
            'public' => true,
            'required' => false,
            'help' => 'Registergericht und Registernummer, falls vorhanden.',
        ],
        'vat_id' => [
            'label' => 'Umsatzsteuer-ID',
            'type' => 'text',
            'public' => true,
            'required' => false,
            'help' => 'Falls vorhanden. Keine private Steuer-ID eintragen.',
        ],
        'lucid_number' => [
            'label' => 'LUCID-Registrierungsnummer',
            'type' => 'text',
            'public' => false,
            'required' => false,
            'help' => 'Für Anbieter, die Verpackungen in Deutschland in Verkehr bringen.',
        ],
        'return_address' => [
            'label' => 'Rücksendeadresse',
            'type' => 'textarea',
            'public' => true,
            'required' => true,
            'help' => 'Adresse für Widerruf, Rückgabe und Reklamationen.',
        ],
        'product_safety_contact' => [
            'label' => 'Produktsicherheitskontakt',
            'type' => 'textarea',
            'public' => true,
            'required' => false,
            'help' => 'Hersteller, verantwortliche Person oder Sicherheitskontakt nach Produktgruppe.',
        ],
    ];
}

function ww_get_vendor_legal_data($vendor_id, $profile_info = null) {
    if (!is_array($profile_info)) {
        $profile_info = get_user_meta($vendor_id, 'dokan_profile_settings', true);
    }

    return is_array($profile_info) && isset($profile_info['ww_legal']) && is_array($profile_info['ww_legal'])
        ? $profile_info['ww_legal']
        : [];
}

add_action('dokan_settings_form_bottom', 'ww_render_vendor_legal_settings_fields', 15, 2);
function ww_render_vendor_legal_settings_fields($current_user, $profile_info) {
    $vendor_id = $current_user instanceof WP_User ? $current_user->ID : get_current_user_id();
    $legal = ww_get_vendor_legal_data($vendor_id, $profile_info);
    ?>
    <section class="ww-vendor-legal-fields" aria-labelledby="ww-vendor-legal-fields-title">
      <h3 id="ww-vendor-legal-fields-title">Rechtliche Anbieterangaben</h3>
      <p>Diese Angaben werden für Impressum, DSA/KYBC-Prüfung, Widerruf, Produktsicherheit und Marktplatzpflichten benötigt. Öffentlich sichtbare Felder erscheinen im Anbieter-Shop.</p>

      <?php foreach (ww_vendor_legal_field_definitions() as $key => $field) : ?>
        <?php
        $value = isset($legal[$key]) ? (string) $legal[$key] : '';
        $field_id = 'ww_legal_' . $key;
        ?>
        <div class="dokan-form-group">
          <label class="dokan-w3 dokan-control-label" for="<?php echo esc_attr($field_id); ?>">
            <?php echo esc_html($field['label']); ?>
            <?php if (!empty($field['required'])) : ?><span class="required">*</span><?php endif; ?>
          </label>
          <div class="dokan-w7 dokan-text-left">
            <?php if ($field['type'] === 'textarea') : ?>
              <textarea id="<?php echo esc_attr($field_id); ?>" name="ww_legal[<?php echo esc_attr($key); ?>]" class="dokan-form-control" rows="3" <?php echo !empty($field['required']) ? 'required' : ''; ?>><?php echo esc_textarea($value); ?></textarea>
            <?php else : ?>
              <input id="<?php echo esc_attr($field_id); ?>" name="ww_legal[<?php echo esc_attr($key); ?>]" class="dokan-form-control" type="<?php echo esc_attr($field['type']); ?>" value="<?php echo esc_attr($value); ?>" <?php echo !empty($field['required']) ? 'required' : ''; ?>>
            <?php endif; ?>
            <p class="help-block">
              <?php echo esc_html($field['help']); ?>
              <?php echo !empty($field['public']) ? ' Öffentlich sichtbar.' : ' Nur intern sichtbar.'; ?>
            </p>
          </div>
        </div>
      <?php endforeach; ?>

      <div class="dokan-form-group">
        <label class="dokan-w3 dokan-control-label">Bestätigungen</label>
        <div class="dokan-w7 dokan-text-left ww-vendor-legal-checks">
          <?php
          $checks = [
              'dsa_self_certification' => 'Ich bestätige, dass ich nur rechtmäßige Produkte anbiete und alle Verbraucher-, Produkt- und Steuerpflichten einhalte.',
              'packaging_compliance' => 'Ich prüfe und erfülle, soweit einschlägig, meine Pflichten nach VerpackG/LUCID und Systembeteiligung.',
              'gpsr_compliance' => 'Ich stelle erforderliche Hersteller-, Sicherheits- und Warnhinweise nach GPSR bzw. Spezialrecht bereit.',
              'book_price_compliance' => 'Beim Verkauf preisgebundener Bücher halte ich die Buchpreisbindung ein.',
          ];
          foreach ($checks as $key => $label) :
              $checked = !empty($legal[$key]) && $legal[$key] === 'yes';
              ?>
              <label class="ww-vendor-legal-check">
                <input type="hidden" name="ww_legal[<?php echo esc_attr($key); ?>]" value="no">
                <input type="checkbox" name="ww_legal[<?php echo esc_attr($key); ?>]" value="yes" <?php checked($checked); ?> required>
                <span><?php echo esc_html($label); ?></span>
              </label>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
    <?php
}

add_filter('dokan_store_profile_settings_args', 'ww_save_vendor_legal_settings', 20, 2);
function ww_save_vendor_legal_settings($settings, $store_id) {
    if (empty($_POST['ww_legal']) || !is_array($_POST['ww_legal'])) {
        return $settings;
    }

    $raw = wc_clean(wp_unslash($_POST['ww_legal']));
    $legal = [];

    foreach (ww_vendor_legal_field_definitions() as $key => $field) {
        $value = isset($raw[$key]) ? (string) $raw[$key] : '';
        $legal[$key] = $field['type'] === 'email' ? sanitize_email($value) : sanitize_textarea_field($value);
    }

    foreach (['dsa_self_certification', 'packaging_compliance', 'gpsr_compliance', 'book_price_compliance'] as $key) {
        $legal[$key] = isset($raw[$key]) && $raw[$key] === 'yes' ? 'yes' : 'no';
    }

    $settings['ww_legal'] = $legal;

    return $settings;
}

function ww_vendor_has_complete_legal_profile($vendor_id) {
    $legal = ww_get_vendor_legal_data($vendor_id);
    foreach (ww_vendor_legal_field_definitions() as $key => $field) {
        if (!empty($field['required']) && empty($legal[$key])) {
            return false;
        }
    }

    foreach (['dsa_self_certification', 'packaging_compliance', 'gpsr_compliance', 'book_price_compliance'] as $key) {
        if (empty($legal[$key]) || $legal[$key] !== 'yes') {
            return false;
        }
    }

    return true;
}

function ww_vendor_review_statuses() {
    return [
        'incomplete' => [
            'label' => 'Angaben unvollständig',
            'description' => 'Der Anbieter muss die rechtlichen Pflichtangaben vervollständigen.',
        ],
        'pending_review' => [
            'label' => 'In Prüfung',
            'description' => 'Die Angaben liegen vor und werden vom Marktplatz geprüft.',
        ],
        'approved' => [
            'label' => 'Freigegeben',
            'description' => 'Der Anbieter darf verkaufen. Neue und geänderte Produkte gehen trotzdem in die Produktprüfung.',
        ],
        'suspended' => [
            'label' => 'Gesperrt',
            'description' => 'Der Anbieter darf keine Produkte einstellen oder bearbeiten.',
        ],
    ];
}

function ww_get_vendor_review_status($vendor_id) {
    $status = get_user_meta($vendor_id, 'ww_vendor_review_status', true);
    $statuses = ww_vendor_review_statuses();

    if (isset($statuses[$status])) {
        return $status;
    }

    if ('yes' === get_user_meta($vendor_id, 'dokan_enable_selling', true)) {
        return 'approved';
    }

    return ww_vendor_has_complete_legal_profile($vendor_id) ? 'pending_review' : 'incomplete';
}

function ww_get_vendor_review_label($status) {
    $statuses = ww_vendor_review_statuses();

    return isset($statuses[$status]) ? $statuses[$status]['label'] : $statuses['incomplete']['label'];
}

function ww_vendor_is_marketplace_approved($vendor_id) {
    return ww_get_vendor_review_status($vendor_id) === 'approved';
}

function ww_set_vendor_review_status($vendor_id, $status, $notify_vendor = true) {
    $statuses = ww_vendor_review_statuses();
    if (!isset($statuses[$status])) {
        $status = 'incomplete';
    }

    $old_status = get_user_meta($vendor_id, 'ww_vendor_review_status', true);
    update_user_meta($vendor_id, 'ww_vendor_review_status', $status);
    update_user_meta($vendor_id, 'ww_vendor_review_updated_at', current_time('mysql'));

    if (is_user_logged_in()) {
        update_user_meta($vendor_id, 'ww_vendor_review_updated_by', get_current_user_id());
    }

    if ($status === 'approved') {
        update_user_meta($vendor_id, 'dokan_enable_selling', 'yes');
        update_user_meta($vendor_id, 'dokan_publishing', 'no');
    } else {
        update_user_meta($vendor_id, 'dokan_enable_selling', 'no');
        update_user_meta($vendor_id, 'dokan_publishing', 'no');
    }

    if ($notify_vendor && $old_status !== $status) {
        ww_notify_vendor_review_status_change($vendor_id, $status);
    }
}

function ww_notify_vendor_review_status_change($vendor_id, $status) {
    $user = get_userdata($vendor_id);
    if (!$user instanceof WP_User) {
        return;
    }

    $subject = 'Wichtelwerken: Anbieterstatus aktualisiert';
    $message = sprintf(
        "Hallo %s,\n\n dein Anbieterstatus wurde aktualisiert: %s.\n\n%s\n\nViele Grüße\nWichtelwerken",
        $user->display_name ?: $user->user_login,
        ww_get_vendor_review_label($status),
        ww_vendor_review_statuses()[$status]['description']
    );

    wp_mail($user->user_email, $subject, $message);
}

add_filter('dokan_can_enable_selling', 'ww_gate_dokan_vendor_activation', 10, 2);
function ww_gate_dokan_vendor_activation($can_enable, $vendor_id) {
    return $can_enable && ww_vendor_is_marketplace_approved((int) $vendor_id);
}

add_action('dokan_store_profile_saved', 'ww_update_vendor_review_status_after_profile_save', 20, 3);
function ww_update_vendor_review_status_after_profile_save($store_id, $settings, $previous_settings) {
    $status = ww_get_vendor_review_status($store_id);
    if (in_array($status, ['approved', 'suspended'], true)) {
        return;
    }

    $new_status = ww_vendor_has_complete_legal_profile($store_id) ? 'pending_review' : 'incomplete';
    if ($new_status !== $status) {
        ww_set_vendor_review_status($store_id, $new_status, false);

        if ($new_status === 'pending_review') {
            ww_notify_admin_vendor_pending_review($store_id);
        }
    }
}

function ww_notify_admin_vendor_pending_review($vendor_id) {
    $user = get_userdata($vendor_id);
    if (!$user instanceof WP_User) {
        return;
    }

    wp_mail(
        get_option('admin_email'),
        'Wichtelwerken: Anbieter wartet auf Freigabe',
        sprintf(
            "Ein Anbieter hat seine Pflichtangaben eingereicht und wartet auf Prüfung:\n\n%s\n%s\n\n%s",
            $user->display_name ?: $user->user_login,
            $user->user_email,
            admin_url('user-edit.php?user_id=' . absint($vendor_id))
        )
    );
}

add_filter('dokan_get_default_product_status', 'ww_force_vendor_product_review_status', 20, 3);
function ww_force_vendor_product_review_status($status, $seller_id, $is_trusted) {
    if ($seller_id && !current_user_can('manage_woocommerce')) {
        return 'pending';
    }

    return $status;
}

add_filter('dokan_insert_product_post_data', 'ww_prepare_vendor_product_for_review', 20, 2);
add_filter('dokan_update_product_post_data', 'ww_prepare_vendor_product_for_review', 20, 2);
function ww_prepare_vendor_product_for_review($post_data, $raw_data = []) {
    $vendor_id = get_current_user_id();
    if (!$vendor_id || current_user_can('manage_woocommerce') || !function_exists('dokan_is_user_seller') || !dokan_is_user_seller($vendor_id)) {
        return $post_data;
    }

    $post_data['post_status'] = 'pending';

    return $post_data;
}

add_filter('dokan_can_add_product', 'ww_gate_vendor_product_changes');
add_filter('dokan_can_edit_product', 'ww_gate_vendor_product_changes');
function ww_gate_vendor_product_changes($errors) {
    $vendor_id = get_current_user_id();
    if (!$vendor_id || ww_vendor_is_marketplace_approved($vendor_id)) {
        return $errors;
    }

    $errors[] = 'Dein Anbieterkonto ist noch nicht freigegeben. Bitte vervollständige deine Anbieterangaben und warte auf die Prüfung.';

    return $errors;
}

add_filter('dokan_new_product_popup_args', 'ww_gate_vendor_product_popup_changes', 15, 2);
function ww_gate_vendor_product_popup_changes($errors, $data) {
    $vendor_id = get_current_user_id();
    if (!$vendor_id || ww_vendor_is_marketplace_approved($vendor_id)) {
        return $errors;
    }

    return new WP_Error(
        'ww_vendor_not_approved',
        'Dein Anbieterkonto ist noch nicht freigegeben. Produkte können erst nach der Anbieterprüfung eingestellt werden.'
    );
}

add_action('dokan_new_product_added', 'ww_mark_vendor_product_pending_review', 30, 2);
add_action('dokan_product_updated', 'ww_mark_vendor_product_pending_review', 30, 2);
function ww_mark_vendor_product_pending_review($product_id, $data = []) {
    $product = wc_get_product($product_id);
    if (!$product instanceof WC_Product || $product->get_status() !== 'pending') {
        return;
    }

    update_post_meta($product_id, '_ww_product_review_status', 'pending');
    update_post_meta($product_id, '_ww_product_review_submitted_at', current_time('mysql'));
    update_post_meta($product_id, '_ww_product_review_submitted_by', get_current_user_id());

    ww_notify_admin_product_pending_review($product_id);
}

function ww_notify_admin_product_pending_review($product_id) {
    $last_notice = (int) get_post_meta($product_id, '_ww_product_review_last_notice', true);
    if ($last_notice && (time() - $last_notice) < HOUR_IN_SECONDS) {
        return;
    }

    update_post_meta($product_id, '_ww_product_review_last_notice', time());

    $edit_url = admin_url('post.php?post=' . absint($product_id) . '&action=edit');
    wp_mail(
        get_option('admin_email'),
        'Wichtelwerken: Produkt wartet auf Prüfung',
        sprintf("Ein Produkt wartet auf Prüfung:\n\n%s\n\n%s", get_the_title($product_id), $edit_url)
    );
}

add_action('transition_post_status', 'ww_mark_product_review_decision', 10, 3);
function ww_mark_product_review_decision($new_status, $old_status, $post) {
    if (!$post instanceof WP_Post || $post->post_type !== 'product' || $new_status === $old_status) {
        return;
    }

    if ($new_status === 'publish' && current_user_can('manage_woocommerce')) {
        update_post_meta($post->ID, '_ww_product_review_status', 'approved');
        update_post_meta($post->ID, '_ww_product_review_decided_at', current_time('mysql'));
        update_post_meta($post->ID, '_ww_product_review_decided_by', get_current_user_id());
    }
}

add_action('dokan_dashboard_content_before', 'ww_vendor_review_dashboard_notice', 8);
function ww_vendor_review_dashboard_notice() {
    $vendor_id = get_current_user_id();
    if (!$vendor_id || !function_exists('dokan_is_user_seller') || !dokan_is_user_seller($vendor_id)) {
        return;
    }

    $status = ww_get_vendor_review_status($vendor_id);
    $settings_url = function_exists('dokan_get_navigation_url') ? dokan_get_navigation_url('settings') : home_url('/mein-konto/');

    if ($status === 'approved') {
        if (!ww_vendor_has_complete_legal_profile($vendor_id)) {
            echo '<div class="dokan-alert dokan-alert-warning ww-vendor-review-notice">';
            echo 'Dein Anbieterkonto ist freigegeben, aber rechtliche Angaben oder Bestätigungen sind noch unvollständig. Bitte vor dem Go-live vervollständigen.';
            echo ' <a href="' . esc_url($settings_url) . '">Angaben prüfen</a>';
            echo '</div>';
        } else {
            echo '<div class="dokan-alert dokan-alert-info ww-vendor-review-notice">';
            echo 'Dein Anbieterkonto ist freigegeben. Neue oder geänderte Produkte werden vor Veröffentlichung vom Marktplatz geprüft.';
            echo '</div>';
        }
        return;
    }

    echo '<div class="dokan-alert dokan-alert-warning ww-vendor-review-notice">';
    if ($status === 'suspended') {
        echo 'Dein Anbieterkonto ist derzeit gesperrt. Neue Produktänderungen sind nicht möglich. Bitte kontaktiere Wichtelwerken.';
    } elseif ($status === 'pending_review') {
        echo 'Dein Anbieterkonto ist in Prüfung. Produkte können erst nach Freigabe eingestellt oder bearbeitet werden.';
    } else {
        echo 'Bitte vervollständige deine rechtlichen Anbieterangaben. Danach kann Wichtelwerken dein Anbieterkonto prüfen.';
        echo ' <a href="' . esc_url($settings_url) . '">Angaben vervollständigen</a>';
    }
    echo '</div>';
}

add_action('dokan_settings_before_form', 'ww_vendor_legal_profile_notice', 5, 2);
function ww_vendor_legal_profile_notice($current_user, $profile_info) {
    $vendor_id = $current_user instanceof WP_User ? $current_user->ID : get_current_user_id();
    if (ww_vendor_has_complete_legal_profile($vendor_id)) {
        return;
    }

    echo '<div class="dokan-alert dokan-alert-warning ww-vendor-legal-notice">';
    echo 'Bitte vervollständige die rechtlichen Anbieterangaben. Ohne vollständige Angaben sollte dein Shop vor dem Go-live nicht freigeschaltet werden.';
    echo '</div>';
}

add_action('dokan_seller_meta_fields', 'ww_render_vendor_review_admin_fields', 20);
function ww_render_vendor_review_admin_fields($user) {
    if (!current_user_can('manage_woocommerce') || !$user instanceof WP_User) {
        return;
    }

    $status = ww_get_vendor_review_status($user->ID);
    $legal_complete = ww_vendor_has_complete_legal_profile($user->ID);
    $updated_at = get_user_meta($user->ID, 'ww_vendor_review_updated_at', true);
    ?>
    <tr class="ww-admin-vendor-review-row">
      <th><label for="ww_vendor_review_status">Wichtelwerken-Freigabe</label></th>
      <td>
        <select name="ww_vendor_review_status" id="ww_vendor_review_status">
          <?php foreach (ww_vendor_review_statuses() as $key => $data) : ?>
            <option value="<?php echo esc_attr($key); ?>" <?php selected($status, $key); ?>>
              <?php echo esc_html($data['label']); ?>
            </option>
          <?php endforeach; ?>
        </select>
        <p class="description">
          <?php echo esc_html(ww_vendor_review_statuses()[$status]['description']); ?>
          <?php echo $legal_complete ? ' Pflichtangaben wirken vollständig.' : ' Pflichtangaben sind noch unvollständig.'; ?>
          <?php if ($updated_at) : ?>
            Zuletzt geändert: <?php echo esc_html($updated_at); ?>.
          <?php endif; ?>
        </p>
        <p class="description">
          Hinweis: Auch freigegebene Anbieter veröffentlichen neue oder geänderte Produkte nicht direkt. Produktfreigabe erfolgt über den WooCommerce-Produktstatus.
        </p>
      </td>
    </tr>
    <?php
}

add_action('dokan_process_seller_meta_fields', 'ww_save_vendor_review_admin_fields', 20);
function ww_save_vendor_review_admin_fields($user_id) {
    if (!current_user_can('manage_woocommerce') || !isset($_POST['ww_vendor_review_status'])) {
        return;
    }

    ww_set_vendor_review_status($user_id, sanitize_key(wp_unslash($_POST['ww_vendor_review_status'])));
}

add_filter('manage_users_columns', 'ww_add_vendor_review_user_column');
function ww_add_vendor_review_user_column($columns) {
    $columns['ww_vendor_review_status'] = 'Anbieterfreigabe';

    return $columns;
}

add_filter('manage_users_custom_column', 'ww_render_vendor_review_user_column', 10, 3);
function ww_render_vendor_review_user_column($value, $column_name, $user_id) {
    if ($column_name !== 'ww_vendor_review_status' || !user_can($user_id, 'dokandar')) {
        return $value;
    }

    $status = ww_get_vendor_review_status($user_id);
    return sprintf(
        '<span class="ww-admin-status ww-admin-status--%s">%s</span>',
        esc_attr($status),
        esc_html(ww_get_vendor_review_label($status))
    );
}

add_action('admin_notices', 'ww_vendor_review_admin_notice');
function ww_vendor_review_admin_notice() {
    if (!current_user_can('manage_woocommerce')) {
        return;
    }

    $pending_vendors = get_users([
        'role__in' => ['seller'],
        'meta_key' => 'ww_vendor_review_status',
        'meta_value' => 'pending_review',
        'fields' => 'ID',
    ]);
    $pending_products = get_posts([
        'post_type' => 'product',
        'post_status' => 'pending',
        'meta_key' => '_ww_product_review_status',
        'meta_value' => 'pending',
        'fields' => 'ids',
        'numberposts' => -1,
    ]);

    if (!$pending_vendors && !$pending_products) {
        return;
    }

    echo '<div class="notice notice-warning"><p>';
    echo '<strong>Wichtelwerken Freigabe:</strong> ';
    if ($pending_vendors) {
        echo count($pending_vendors) . ' Anbieter in Prüfung. ';
    }
    if ($pending_products) {
        echo count($pending_products) . ' Produkte warten auf Freigabe. ';
    }
    echo '<a href="' . esc_url(admin_url('users.php?role=seller')) . '">Anbieter prüfen</a> · ';
    echo '<a href="' . esc_url(admin_url('edit.php?post_type=product&post_status=pending')) . '">Produkte prüfen</a>';
    echo '</p></div>';
}

add_action('admin_head-users.php', 'ww_vendor_review_admin_styles');
add_action('admin_head-user-edit.php', 'ww_vendor_review_admin_styles');
add_action('admin_head-profile.php', 'ww_vendor_review_admin_styles');
function ww_vendor_review_admin_styles() {
    ?>
    <style>
      .ww-admin-status {
        display: inline-block;
        padding: 3px 8px;
        border-radius: 999px;
        background: #f0f0f1;
        color: #1d2327;
        font-size: 12px;
        font-weight: 600;
      }
      .ww-admin-status--approved { background: #e5f5e8; color: #1f6f33; }
      .ww-admin-status--pending_review { background: #fff4d8; color: #7a4f00; }
      .ww-admin-status--incomplete { background: #fcebea; color: #8a2424; }
      .ww-admin-status--suspended { background: #ece7f6; color: #4b2e83; }
      .ww-admin-vendor-review-row select { min-width: 240px; }
    </style>
    <?php
}

add_action('dokan_store_profile_frame_after', 'ww_render_public_vendor_legal_box', 20, 2);
function ww_render_public_vendor_legal_box($store_user, $store_info) {
    if (empty($store_user->ID)) {
        return;
    }

    $legal = ww_get_vendor_legal_data((int) $store_user->ID, $store_info);
    $fields = ww_vendor_legal_field_definitions();
    $public_rows = [];

    foreach ($fields as $key => $field) {
        if (empty($field['public']) || empty($legal[$key])) {
            continue;
        }

        $public_rows[] = [
            'label' => $field['label'],
            'value' => $legal[$key],
        ];
    }

    if (!$public_rows) {
        return;
    }
    ?>
    <section class="ww-store-legal-box" aria-labelledby="ww-store-legal-box-title">
      <h2 id="ww-store-legal-box-title">Anbieter-Impressum</h2>
      <dl>
        <?php foreach ($public_rows as $row) : ?>
          <dt><?php echo esc_html($row['label']); ?></dt>
          <dd><?php echo nl2br(esc_html($row['value'])); ?></dd>
        <?php endforeach; ?>
      </dl>
    </section>
    <?php
}

// =========================================================
// 6. SHORTCODES
// =========================================================
add_shortcode('ww_hero', 'ww_hero_shortcode');
function ww_hero_shortcode($atts) {
    $a = shortcode_atts([
        'title'    => 'Handgemacht <em class="ww-accent">mit Herz</em>',
        'subtitle' => 'Dein Marktplatz für Manufakturprodukte, Handgemachtes und liebevolle Unikate. Von Menschen mit Herz — nachhaltig und fair.',
        'btn1'     => 'Jetzt entdecken →',
        'btn1_url' => '/shop',
        'btn2'     => 'Verkäufer werden',
        'btn2_url' => '/anbieter-werden',
    ], $atts);
    ob_start(); ?>
    <section class="ww-hero">
      <div class="ww-hero__bg"></div>
      <div class="ww-hero__overlay"></div>
      <div class="ww-hero__content">
        <span class="ww-hero__badge">Öko, Fair &amp; Selbstgemacht</span>
        <h1 class="ww-hero__title"><?php echo wp_kses_post($a['title']); ?></h1>
        <p class="ww-hero__subtitle"><?php echo esc_html($a['subtitle']); ?></p>
        <div class="ww-hero__buttons">
          <a href="<?php echo esc_url($a['btn1_url']); ?>" class="ww-btn ww-btn--primary"><?php echo esc_html($a['btn1']); ?></a>
          <a href="<?php echo esc_url($a['btn2_url']); ?>" class="ww-btn ww-btn--outline"><?php echo esc_html($a['btn2']); ?></a>
        </div>
      </div>
    </section>
    <?php return ob_get_clean();
}

// =========================================================
// 7. WIDGETS
// =========================================================
add_action('widgets_init', 'ww_register_sidebars');
function ww_register_sidebars() {
    $shared = ['before_widget'=>'<div class="ww-footer-widget">','after_widget'=>'</div>',
               'before_title'=>'<h4 class="widget-title">','after_title'=>'</h4>'];
    register_sidebar(array_merge($shared, ['name'=>'Footer 1','id'=>'ww-footer-1']));
    register_sidebar(array_merge($shared, ['name'=>'Footer 2','id'=>'ww-footer-2']));
    register_sidebar(array_merge($shared, ['name'=>'Footer 3','id'=>'ww-footer-3']));
}

// =========================================================
// 8. DEUTSCHE FALLBACK-UEBERSETZUNGEN
// =========================================================
add_filter('gettext', 'ww_translate_remaining_strings', 20, 3);
function ww_translate_remaining_strings($translation, $text, $domain) {
    return ww_translation_map()[$text] ?? $translation;
}

function ww_translation_map() {
    return [
        'Add to cart' => 'In den Warenkorb',
        'Add Images to Product Gallery' => 'Bilder zur Produktgalerie hinzufügen',
        'Add new category' => 'Neue Kategorie hinzufügen',
        'Add new product' => 'Neues Produkt hinzufügen',
        'Add Payment Method' => 'Zahlungsmethode hinzufügen',
        'Add to gallery' => 'Zur Galerie hinzufügen',
        'Address' => 'Adresse',
        'A link to set a new password will be sent to your email address.' => 'Ein Link zum Erstellen eines neuen Passworts wird an deine E-Mail-Adresse gesendet.',
        'All' => 'Alle',
        'All dates' => 'Alle Zeiträume',
        'Are you sure?' => 'Bist du sicher?',
        'Attribute Name' => 'Attributname',
        'Available' => 'Verfügbar',
        'Brand' => 'Marke',
        'Bulk Actions' => 'Mehrfachaktion',
        'Built with WooCommerce.' => 'Erstellt mit WooCommerce.',
        'Calculating' => 'Wird berechnet',
        'Cancel' => 'Abbrechen',
        'Cart' => 'Warenkorb',
        'Category' => 'Kategorie',
        'Checkout' => 'Kasse',
        'Choose a file' => 'Datei auswählen',
        'Choose Image' => 'Bild auswählen',
        'Dashboard' => 'Übersicht',
        'Default sorting' => 'Standardsortierung',
        'Delete Permanently' => 'Endgültig löschen',
        'Description' => 'Beschreibung',
        'Direct bank transfer' => 'Banküberweisung',
        'Discounted Price' => 'Angebotspreis',
        'Done' => 'Fertig',
        'Edit Account' => 'Konto bearbeiten',
        'Edit' => 'Bearbeiten',
        'Email address' => 'E-Mail-Adresse',
        'Earning' => 'Erlös',
        'First Name' => 'Vorname',
        'Image' => 'Bild',
        'In stock' => 'Vorrätig',
        'Insert file URL' => 'Datei-URL einfügen',
        'I am a customer' => 'Ich bin Kunde',
        'I am a vendor' => 'Ich bin Anbieter',
        'Last Name' => 'Nachname',
        'Loading failed' => 'Laden fehlgeschlagen',
        'Loading more results…' => 'Weitere Ergebnisse werden geladen…',
        'Login' => 'Anmelden',
        'Log in' => 'Anmelden',
        'Lost your password?' => 'Passwort vergessen?',
        'My account' => 'Mein Konto',
        'My Orders' => 'Meine Bestellungen',
        'New in store' => 'Neu im Shop',
        'No category' => 'Keine Kategorie',
        'No orders found!' => 'Keine Bestellungen gefunden.',
        'No products in the cart.' => 'Es befinden sich keine Produkte im Warenkorb.',
        'No Result Found' => 'Keine Ergebnisse gefunden',
        'Not Available' => 'Nicht verfügbar',
        'Phone Number' => 'Telefonnummer',
        'Phone Number*' => 'Telefonnummer*',
        'Place Order' => 'Bestellung aufgeben',
        'Go to Vendor Dashboard' => 'Zur Anbieterübersicht',
        'Go to vendor Dashboard' => 'Zur Anbieterübersicht',
        'Go to vendor Übersicht' => 'Zur Anbieterübersicht',
        'go to vendor Dashboard' => 'Zur Anbieterübersicht',
        'go to vendor Übersicht' => 'Zur Anbieterübersicht',
        'Proceed to Checkout' => 'Weiter zur Kasse',
        'Proceed to Kasse' => 'Weiter zur Kasse',
        'Product category is required' => 'Eine Produktkategorie ist erforderlich',
        'Product created successfully' => 'Produkt erfolgreich erstellt',
        'Product name..' => 'Produktname',
        'Product name...' => 'Produktname',
        'Product title is required' => 'Ein Produkttitel ist erforderlich',
        'Products' => 'Produkte',
        'Published' => 'Veröffentlicht',
        'Publisheded' => 'Veröffentlicht',
        'Veröffentlichted' => 'Veröffentlicht',
        'Publish' => 'Veröffentlicht',
        'Register' => 'Registrieren',
        'Registration' => 'Registrierung',
        'Remember me' => 'Angemeldet bleiben',
        'Required' => 'Pflichtfeld',
        'Reset' => 'Zurücksetzen',
        'Return to Cart' => 'Zurück zum Warenkorb',
        'Schedule' => 'Zeitplan',
        'Search' => 'Suchen',
        'Search category' => 'Kategorie suchen',
        'Search for:' => 'Suche nach:',
        'Search Products' => 'Produkte suchen',
        'Search products' => 'Produkte suchen',
        'Search products&hellip;' => 'Suche',
        'Searching…' => 'Suche läuft…',
        'Select and Crop' => 'Auswählen und zuschneiden',
        'Select a brand' => 'Marke auswählen',
        '- Select a brand -' => '- Marke auswählen -',
        'Select brand' => 'Marke auswählen',
        'Select a category' => 'Kategorie auswählen',
        'Select product tags' => 'Schlagwörter auswählen',
        'Selected: No category' => 'Ausgewählt: keine Kategorie',
        'Select bulk action' => 'Mehrfachaktion auswählen',
        'Set featured image' => 'Produktbild festlegen',
        'Shop' => 'Shop',
        'Shop Name' => 'Shop-Name',
        'Shop Name *' => 'Shop-Name *',
        'Shop URL' => 'Shop-Adresse',
        'Shop URL *' => 'Shop-Adresse *',
        'Store List' => 'Anbieter',
        'Store Product Category' => 'Produktkategorien',
        'Subtotal:' => 'Zwischensumme:',
        'Vendor Onboarding' => 'Anbieter werden',
        'Vendor:' => 'Anbieter:',
        'View cart' => 'Warenkorb ansehen',
        'Contact Vendor' => 'Anbieter kontaktieren',
        'Enter product name' => 'Suche',
        'Overview' => 'Übersicht',
        'Reports' => 'Berichte',
        'Balance' => 'Kontostand',
        'Balance:' => 'Kontostand:',
        'Orders' => 'Bestellungen',
        'Withdraw' => 'Auszahlungen',
        'Settings' => 'Einstellungen',
        'Payment' => 'Zahlung',
        'Payment Details' => 'Zahlungsdetails',
        'Zahlung Details' => 'Zahlungsdetails',
        'Payment Method' => 'Zahlungsmethode',
        'Payment Methods' => 'Zahlungsmethoden',
        'Performance' => 'Kennzahlen',
        'Marketplace Commission' => 'Marktplatz-Provision',
        'Total Earning' => 'Gesamterlös',
        'Marketplace Discount' => 'Marktplatz-Rabatt',
        'Store Discount' => 'Shop-Rabatt',
        'Charts' => 'Diagramme',
        'By day' => 'Nach Tag',
        'By week' => 'Nach Woche',
        'Net sales Report' => 'Bericht zum Nettoumsatz',
        'Net sales' => 'Nettoumsatz',
        'Orders Report' => 'Bestellbericht',
        'No data for the selected date range' => 'Keine Daten für den ausgewählten Zeitraum',
        'Previous year' => 'Vorjahr',
        'Visit Store' => 'Shop ansehen',
        'Add New Product' => 'Neues Produkt hinzufügen',
        'Coupons' => 'Gutscheine',
        'Reviews' => 'Bewertungen',
        'Tools' => 'Werkzeuge',
        'Status' => 'Status',
        'Followers' => 'Follower',
        'Announcements' => 'Ankündigungen',
        'Delivery Time' => 'Lieferzeit',
        'Return Request' => 'Rückgabeanfragen',
        'Store Settings' => 'Shop-Einstellungen',
        'Payment Settings' => 'Zahlungseinstellungen',
        'Update Settings' => 'Einstellungen speichern',
        'Store Name' => 'Shop-Name',
        'Store Address' => 'Shop-Adresse',
        'Country' => 'Land',
        'City' => 'Ort',
        'Postcode / ZIP' => 'Postleitzahl',
        'Postcode / Zip' => 'Postleitzahl',
        'Post/ZIP Code' => 'Postleitzahl',
        'Save Changes' => 'Änderungen speichern',
        'Add Product' => 'Produkt hinzufügen',
        'Product' => 'Produkt',
        'Price' => 'Preis',
        'Filter' => 'Filtern',
        'Sort by:' => 'Sortieren nach:',
        'Most Recent' => 'Neueste zuerst',
        'Most Popular' => 'Beliebteste',
        'Random' => 'Zufällig',
        'Search Vendors' => 'Anbieter suchen',
        'Apply' => 'Anwenden',
        'Export' => 'Exportieren',
        'Customer' => 'Kunde',
        'Date' => 'Datum',
        'Total stores showing:' => 'Summe Anbieter:',
        'Total' => 'Summe',
        'Town / City' => 'Ort',
        'Action' => 'Aktion',
        'Processing' => 'In Bearbeitung',
        'Completed' => 'Abgeschlossen',
        'On-hold' => 'In Wartestellung',
        'Pending' => 'Ausstehend',
        'Cancelled' => 'Storniert',
        'Refunded' => 'Erstattet',
        'Failed' => 'Fehlgeschlagen',
        'Default' => 'Standard',
        'Setup' => 'Einrichten',
        'Make default' => 'Als Standard festlegen',
        'More Products' => 'Weitere Produkte',
        'No ratings found yet!' => 'Noch keine Bewertungen vorhanden.',
        'Name' => 'Name',
        'Previous' => 'Zurück',
        'Next' => 'Weiter',
        'Sort by popularity' => 'Nach Beliebtheit sortieren',
        'Sort by average rating' => 'Nach Durchschnittsbewertung sortieren',
        'Sort by latest' => 'Nach Aktualität sortieren',
        'Sort by price: low to high' => 'Nach Preis sortieren: aufsteigend',
        'Sort by price: high to low' => 'Nach Preis sortieren: absteigend',
        'Store' => 'Anbieter',
        'Store Close Notice' => 'Hinweis bei geschlossenem Shop',
        'Store has open close time' => 'Shop hat Öffnungs- und Schließzeiten',
        'Store is closed' => 'Shop ist geschlossen',
        'Store is open' => 'Shop ist geöffnet',
        'Store Open Notice' => 'Hinweis bei geöffnetem Shop',
        'Store Schedule' => 'Öffnungszeiten',
        'Stock' => 'Lagerbestand',
        'Send Message' => 'Nachricht senden',
        'Short description of the product...' => 'Kurzbeschreibung des Produkts...',
        'Short Beschreibung' => 'Kurzbeschreibung',
        'Show email address in store' => 'E-Mail-Adresse im Shop anzeigen',
        'Tags' => 'Schlagwörter',
        'There is no payment method to add.' => 'Es ist keine weitere Zahlungsmethode verfügbar.',
        'There is no payment method to show.' => 'Es ist noch keine Zahlungsmethode hinterlegt.',
        'These are the withdraw methods available for you. Please update your payment information below to submit withdraw requests and get your store payments seamlessly.' => 'Hier findest du die verfügbaren Auszahlungsmethoden. Hinterlege deine Zahlungsinformationen, damit Auszahlungen aus deinem Shop abgewickelt werden können.',
        'Your Balance' => 'Dein Kontostand',
        'Your Kontostand' => 'Dein Kontostand',
        'Minimum Withdraw Amount' => 'Mindestauszahlungsbetrag',
        'Minimum Auszahlungen Amount' => 'Mindestauszahlungsbetrag',
        'Last Payment' => 'Letzte Zahlung',
        'Last Zahlung' => 'Letzte Zahlung',
        'You do not have any approved withdraw yet' => 'Du hast noch keine freigegebene Auszahlung',
        'You do not have any approved withdraw yet.' => 'Du hast noch keine freigegebene Auszahlung.',
        'Request Withdraw' => 'Auszahlung anfordern',
        'Request Auszahlungen' => 'Auszahlung anfordern',
        'View Payment' => 'Zahlung ansehen',
        'View Payments' => 'Zahlungen ansehen',
        'View Zahlung' => 'Zahlung ansehen',
        'Ansehen Zahlungs' => 'Zahlungen ansehen',
        'No information found.' => 'Keine Informationen gefunden.',
        'Title' => 'Titel',
        'Type your message...' => 'Nachricht eingeben...',
        'Type' => 'Typ',
        'Uncategorized' => 'Allgemein',
        'Upload a banner for your store. Banner size is (625x300) pixels.' => 'Lade ein Banner für deinen Shop hoch. Empfohlene Größe: 625 x 300 Pixel.',
        'Upload banner' => 'Banner hochladen',
        'Your Name' => 'Dein Name',
        'Your cart is currently empty!' => 'Dein Warenkorb ist aktuell leer.',
        'You are already logged in' => 'Du bist bereits angemeldet.',
        'Something went wrong. Please try again.' => 'Etwas ist schiefgelaufen. Bitte versuche es erneut.',
        'Sorry, this attribute option already exists, Try a different one.' => 'Diese Attributoption existiert bereits. Bitte wähle eine andere.',
        'Warning! This product will not have any variations if this option is not checked.' => 'Hinweis: Dieses Produkt hat keine Varianten, wenn diese Option nicht aktiviert ist.',
        'Enter a name for the new attribute term:' => 'Gib einen Namen für den neuen Attributwert ein:',
        'Remove this attribute?' => 'Dieses Attribut entfernen?',
        'Are you sure you want to link all variations? This will create a new variation for each and every possible combination of variation attributes (max 50 per run).' => 'Möchtest du wirklich alle Varianten verknüpfen? Für jede mögliche Attributkombination wird eine neue Variante erstellt (max. 50 pro Durchlauf).',
        'Enter a value' => 'Wert eingeben',
        'Variation menu order (determines position in the list of variations)' => 'Reihenfolge der Variante (bestimmt die Position in der Variantenliste)',
        'Enter a value (fixed or %)' => 'Wert eingeben (fest oder %)',
        'Are you sure you want to delete all variations? This cannot be undone.' => 'Möchtest du wirklich alle Varianten löschen? Das kann nicht rückgängig gemacht werden.',
        'Last warning, are you sure?' => 'Letzte Warnung: Bist du sicher?',
        'Choose an image' => 'Bild auswählen',
        'Set variation image' => 'Variantenbild festlegen',
        'variation added' => 'Variante hinzugefügt',
        'variations added' => 'Varianten hinzugefügt',
        'No variations added' => 'Keine Varianten hinzugefügt',
        'Are you sure you want to remove this variation?' => 'Möchtest du diese Variante wirklich entfernen?',
        'Sale start date (YYYY-MM-DD format or leave blank)' => 'Startdatum des Angebots (Format JJJJ-MM-TT oder leer lassen)',
        'Sale end date (YYYY-MM-DD format or leave blank)' => 'Enddatum des Angebots (Format JJJJ-MM-TT oder leer lassen)',
        'Save changes before changing page?' => 'Änderungen speichern, bevor du die Seite wechselst?',
        'Please insert value less than the regular price!' => 'Bitte gib einen Wert unterhalb des regulären Preises ein.',
        'Please enter with one decimal point (.) without thousand separators.' => 'Bitte mit einem Dezimalpunkt (.) und ohne Tausendertrennzeichen eingeben.',
        'Please enter with one monetary decimal point (.) without thousand separators and currency symbols.' => 'Bitte mit einem Dezimalpunkt (.) ohne Tausendertrennzeichen und ohne Währungssymbol eingeben.',
        'Please enter in country code with two capital letters.' => 'Bitte den Ländercode mit zwei Großbuchstaben eingeben.',
        'Please enter in a value less than the regular price.' => 'Bitte einen Wert unterhalb des regulären Preises eingeben.',
        'This product has produced sales and may be linked to existing orders. Are you sure you want to delete it?' => 'Dieses Produkt wurde bereits verkauft und kann mit bestehenden Bestellungen verknüpft sein. Möchtest du es wirklich löschen?',
        'This action cannot be reversed. Are you sure you wish to erase personal data from the selected orders?' => 'Diese Aktion kann nicht rückgängig gemacht werden. Möchtest du die personenbezogenen Daten der ausgewählten Bestellungen wirklich löschen?',
        'One result is available, press enter to select it.' => 'Ein Ergebnis verfügbar. Drücke Enter, um es auszuwählen.',
        '%qty% results are available, use up and down arrow keys to navigate.' => '%qty% Ergebnisse verfügbar. Nutze die Pfeiltasten zur Auswahl.',
        'No matches found' => 'Keine Treffer gefunden',
        'Please enter 1 or more characters' => 'Bitte mindestens 1 Zeichen eingeben',
        'Please enter %qty% or more characters' => 'Bitte mindestens %qty% Zeichen eingeben',
        'Please delete 1 character' => 'Bitte 1 Zeichen löschen',
        'Please delete %qty% characters' => 'Bitte %qty% Zeichen löschen',
        'You can only select 1 item' => 'Du kannst nur 1 Eintrag auswählen',
        'You can only select %qty% items' => 'Du kannst nur %qty% Einträge auswählen',
        'Upload featured image' => 'Produktbild hochladen',
        'Upload Product Image' => 'Produktbild hochladen',
        'Upload Photo' => 'Foto hochladen',
        'View' => 'Ansehen',
        'Views' => 'Aufrufe',
        'Become a Vendor' => 'Anbieter werden',
        'Vendors can sell products and manage a store with a vendor dashboard.' => 'Anbieter können Produkte verkaufen und ihren Shop im Verkäuferbereich verwalten.',
        'View Product' => 'Produktvorschau',
        'Create & Add New' => 'Erstellen und weiteres hinzufügen',
        'Create Product' => 'Produkt erstellen',
        'Opening Hours' => 'Öffnungszeiten',
        'Phone No' => 'Telefonnummer',
        'Profile Picture' => 'Profilbild',
        'Street' => 'Straße',
        'Street 2' => 'Adresszusatz',
        'You are currently checking out as a guest.' => 'Du bestellst aktuell als Gast.',
        'Your personal data will be used to support your experience throughout this website, to manage access to your account, and for other purposes described in our privacy policy.' => 'Deine personenbezogenen Daten werden verwendet, um dein Nutzungserlebnis auf dieser Website zu unterstützen, den Zugriff auf dein Konto zu verwalten und für weitere Zwecke, die in unserer Datenschutzerklärung beschrieben sind.',
    ];
}

function ww_translation_subset(array $keys) {
    $translations = ww_translation_map();
    $subset = [];

    foreach ($keys as $key) {
        if (isset($translations[$key])) {
            $subset[$key] = $translations[$key];
        }
    }

    return $subset;
}

function ww_rendered_html_translation_map() {
    return ww_translation_subset([
        'View cart',
        'Checkout',
        'Proceed to Checkout',
        'Proceed to Kasse',
        'Vendor:',
        'Subtotal:',
        'Phone Number*',
        'Phone Number',
        'Go to Vendor Dashboard',
        'Go to vendor Dashboard',
        'Go to vendor Übersicht',
        'go to vendor Dashboard',
        'go to vendor Übersicht',
        'Store Product Category',
        'Contact Vendor',
        'Enter product name',
        'Your cart is currently empty!',
        'New in store',
        'Marketplace Commission',
        'Marketplace Discount',
        'Store Discount',
        'Total Earning',
        'Net sales Report',
        'Orders Report',
        'No data for the selected date range',
        'Previous year',
        'Visit Store',
        'Add New Product',
        'Add new product',
        'Add new category',
        'Store Settings',
        'Payment Settings',
        'Payment Methods',
        'Payment Method',
        'Add Payment Method',
        'Update Settings',
        'Overview',
        'Reports',
        'Balance',
        'Balance:',
        'Orders',
        'Withdraw',
        'Settings',
        'Payment',
        'Payment Details',
        'Zahlung Details',
        'Performance',
        'Charts',
        'By day',
        'By week',
        'Net sales',
        'Return Request',
        'Delivery Time',
        'Save Changes',
        'Add Product',
        'Address',
        'Town / City',
        'Country',
        'City',
        'Postcode / ZIP',
        'Postcode / Zip',
        'Store Schedule',
        'Store has open close time',
        'Store Open Notice',
        'Store is open',
        'Store Close Notice',
        'Store is closed',
        'Total stores showing:',
        'Sort by:',
        'Most Recent',
        'Most Popular',
        'Random',
        'Search Vendors',
        'Search Products',
        'Search products',
        'Search category',
        'Apply',
        'Search products&hellip;',
        'No ratings found yet!',
        'Products',
        'More Products',
        'Previous',
        'Next',
        'Sort by popularity',
        'Sort by average rating',
        'Sort by latest',
        'Sort by price: low to high',
        'Sort by price: high to low',
        'Send Message',
        'Delete Permanently',
        'Edit Account',
        'Type your message...',
        'Your Name',
        'You are already logged in',
        'Shop Name *',
        'Shop URL *',
        'I am a customer',
        'I am a vendor',
        'Uncategorized',
        'All dates',
        'Brand',
        'Bulk Actions',
        'Category',
        'Description',
        'Discounted Price',
        'Done',
        'Earning',
        'Image',
        'In stock',
        'No category',
        'No orders found!',
        'Product name..',
        'Product name...',
        'Published',
        'Publisheded',
        'Veröffentlichted',
        'Publish',
        'Reset',
        'Schedule',
        'Select a brand',
        '- Select a brand -',
        'Select brand',
        'Select a category',
        'Select product tags',
        'Selected: No category',
        'Select bulk action',
        'Post/ZIP Code',
        'Price',
        'Name',
        'Stock',
        'Short description of the product...',
        'Short Beschreibung',
        'Show email address in store',
        'Tags',
        'There is no payment method to add.',
        'There is no payment method to show.',
        'These are the withdraw methods available for you. Please update your payment information below to submit withdraw requests and get your store payments seamlessly.',
        'Your Balance',
        'Your Kontostand',
        'Minimum Withdraw Amount',
        'Minimum Auszahlungen Amount',
        'Last Payment',
        'Last Zahlung',
        'You do not have any approved withdraw yet',
        'Request Withdraw',
        'Request Auszahlungen',
        'Title',
        'Type',
        'Upload a banner for your store. Banner size is (625x300) pixels.',
        'Upload banner',
        'Upload Product Image',
        'Upload Photo',
        'Views',
        'Become a Vendor',
        'Vendors can sell products and manage a store with a vendor dashboard.',
        'View Product',
        'Create & Add New',
        'Create Product',
        'Opening Hours',
        'Phone No',
        'Profile Picture',
        'Street 2',
        'Street',
        'Your personal data will be used to support your experience throughout this website, to manage access to your account, and for other purposes described in our privacy policy.',
    ]) + [
        'All (' => 'Alle (',
        'Produkte suchen&nbsp;…' => 'Suche',
        'Produkte suchen …' => 'Suche',
        'Produkte suchen …' => 'Suche',
        'Your personal data will be used to support your experience throughout this website, to manage access to your account, and for other purposes described in our Datenschutzerklärung.' => 'Deine personenbezogenen Daten werden verwendet, um dein Nutzungserlebnis auf dieser Website zu unterstützen, den Zugriff auf dein Konto zu verwalten und für weitere Zwecke, die in unserer Datenschutzerklärung beschrieben sind.',
    ];
}

function ww_dynamic_frontend_translation_pairs() {
    $translations = ww_translation_subset([
        'View cart',
        'Proceed to Checkout',
        'Proceed to Kasse',
        'Checkout',
        'Vendor:',
        'Subtotal:',
        'Phone Number*',
        'Phone Number',
        'Go to Vendor Dashboard',
        'Go to vendor Dashboard',
        'Go to vendor Übersicht',
        'go to vendor Dashboard',
        'go to vendor Übersicht',
        'Store Product Category',
        'Contact Vendor',
        'Enter product name',
        'Your cart is currently empty!',
        'New in store',
        'Marketplace Commission',
        'Marketplace Discount',
        'Store Discount',
        'Total Earning',
        'Net sales Report',
        'Orders Report',
        'No data for the selected date range',
        'Previous year',
        'Visit Store',
        'Add New Product',
        'Add new product',
        'Add new category',
        'Store Settings',
        'Payment Settings',
        'Payment Methods',
        'Payment Method',
        'Add Payment Method',
        'Update Settings',
        'Dashboard',
        'Overview',
        'Reports',
        'Balance:',
        'Your Balance',
        'Your Kontostand',
        'Orders',
        'Withdraw',
        'Minimum Withdraw Amount',
        'Minimum Auszahlungen Amount',
        'Last Payment',
        'Last Zahlung',
        'You do not have any approved withdraw yet',
        'You do not have any approved withdraw yet.',
        'Request Withdraw',
        'Request Auszahlungen',
        'View Payment',
        'View Payments',
        'View Zahlung',
        'Ansehen Zahlungs',
        'No information found.',
        'Settings',
        'Payment',
        'Performance',
        'Charts',
        'By day',
        'By week',
        'Net sales',
        'Return Request',
        'Delivery Time',
        'Save Changes',
        'Add Product',
        'Coupons',
        'Reviews',
        'Tools',
        'Followers',
        'Announcements',
        'Store Name',
        'Store Address',
        'Address',
        'Town / City',
        'Country',
        'City',
        'Postcode / ZIP',
        'Postcode / Zip',
        'Store Schedule',
        'Store has open close time',
        'Store Open Notice',
        'Store is open',
        'Store Close Notice',
        'Store is closed',
        'Product',
        'Sort by:',
        'Most Recent',
        'Most Popular',
        'Random',
        'Search Vendors',
        'Search Products',
        'Search products',
        'Search category',
        'Apply',
        'Export',
        'Customer',
        'Total stores showing:',
        'Total',
        'Title',
        'Action',
        'Processing',
        'Completed',
        'On-hold',
        'Pending',
        'Cancelled',
        'Refunded',
        'Failed',
        'Delete Permanently',
        'Edit Account',
        'Edit',
        'View',
        'Default',
        'View Product',
        'Setup',
        'Make default',
        'No ratings found yet!',
        'Products',
        'More Products',
        'Previous',
        'Next',
        'Sort by popularity',
        'Sort by average rating',
        'Sort by latest',
        'Sort by price: low to high',
        'Sort by price: high to low',
        'Send Message',
        'Type your message...',
        'Your Name',
        'You are already logged in',
        'Shop Name *',
        'Shop URL *',
        'I am a customer',
        'I am a vendor',
        'A link to set a new password will be sent to your email address.',
        'Your personal data will be used to support your experience throughout this website, to manage access to your account, and for other purposes described in our privacy policy.',
        'Uncategorized',
        'All dates',
        'Brand',
        'Bulk Actions',
        'Category',
        'Description',
        'Discounted Price',
        'Done',
        'Earning',
        'Image',
        'In stock',
        'No category',
        'No orders found!',
        'Product name..',
        'Product name...',
        'Publish',
        'Reset',
        'Schedule',
        'Select a brand',
        '- Select a brand -',
        'Select brand',
        'Select a category',
        'Select product tags',
        'Selected: No category',
        'Post/ZIP Code',
        'Price',
        'Name',
        'Stock',
        'Short description of the product...',
        'Short Beschreibung',
        'Show email address in store',
        'Tags',
        'Title',
        'There is no payment method to add.',
        'There is no payment method to show.',
        'These are the withdraw methods available for you. Please update your payment information below to submit withdraw requests and get your store payments seamlessly.',
        'Sorry, this attribute option already exists, Try a different one.',
        'Warning! This product will not have any variations if this option is not checked.',
        'Enter a name for the new attribute term:',
        'Remove this attribute?',
        'Are you sure you want to link all variations? This will create a new variation for each and every possible combination of variation attributes (max 50 per run).',
        'Enter a value',
        'Variation menu order (determines position in the list of variations)',
        'Enter a value (fixed or %)',
        'Are you sure you want to delete all variations? This cannot be undone.',
        'Last warning, are you sure?',
        'Choose an image',
        'Set variation image',
        'variation added',
        'variations added',
        'No variations added',
        'Are you sure you want to remove this variation?',
        'Sale start date (YYYY-MM-DD format or leave blank)',
        'Sale end date (YYYY-MM-DD format or leave blank)',
        'Save changes before changing page?',
        'Please insert value less than the regular price!',
        'Please enter with one decimal point (.) without thousand separators.',
        'Please enter with one monetary decimal point (.) without thousand separators and currency symbols.',
        'Please enter in country code with two capital letters.',
        'Please enter in a value less than the regular price.',
        'This product has produced sales and may be linked to existing orders. Are you sure you want to delete it?',
        'This action cannot be reversed. Are you sure you wish to erase personal data from the selected orders?',
        'One result is available, press enter to select it.',
        '%qty% results are available, use up and down arrow keys to navigate.',
        'No matches found',
        'Please enter 1 or more characters',
        'Please enter %qty% or more characters',
        'Please delete 1 character',
        'Please delete %qty% characters',
        'You can only select 1 item',
        'You can only select %qty% items',
        'Type',
        'Upload a banner for your store. Banner size is (625x300) pixels.',
        'Upload banner',
        'Upload Product Image',
        'Upload Photo',
        'Views',
        'Become a Vendor',
        'Vendors can sell products and manage a store with a vendor dashboard.',
        'Create & Add New',
        'Create Product',
        'Opening Hours',
        'Phone No',
        'Go to Vendor Dashboard',
        'Go to vendor Dashboard',
        'Go to vendor Übersicht',
        'go to vendor Dashboard',
        'go to vendor Übersicht',
        'Profile Picture',
        'Street 2',
        'Street',
    ]) + [
        'All (' => 'Alle (',
        ' items' => ' Artikel',
        ' item' => ' Artikel',
        'Produkte suchen …' => 'Suche',
        'Produkte suchen …' => 'Suche',
        'Search products…' => 'Suche',
        'Your personal data will be used to support your experience throughout this website, to manage access to your account, and for other purposes described in our Datenschutzerklärung.' => 'Deine personenbezogenen Daten werden verwendet, um dein Nutzungserlebnis auf dieser Website zu unterstützen, den Zugriff auf dein Konto zu verwalten und für weitere Zwecke, die in unserer Datenschutzerklärung beschrieben sind.',
    ];

    uksort($translations, fn($a, $b) => strlen($b) <=> strlen($a));

    return array_map(
        fn($source, $translation) => [$source, $translation],
        array_keys($translations),
        array_values($translations)
    );
}

add_filter('ngettext', 'ww_translate_remaining_plural_strings', 20, 5);
function ww_translate_remaining_plural_strings($translation, $single, $plural, $number, $domain) {
    if ($single === '%d item' || $plural === '%d items') {
        return sprintf(_n('%d Artikel', '%d Artikel', $number, 'wichtelwerken-child'), $number);
    }

    return $translation;
}

add_action('template_redirect', 'ww_start_german_output_buffer');
function ww_start_german_output_buffer() {
    if (!is_admin()) {
        ob_start('ww_translate_rendered_html');
    }
}

function ww_translate_rendered_html($html) {
    $protected_blocks = [];
    $html = preg_replace_callback('/<(script|style|noscript)\b[^>]*>.*?<\/\1>/is', function($matches) use (&$protected_blocks) {
        $key = '%%WW_PROTECTED_BLOCK_' . count($protected_blocks) . '%%';
        $protected_blocks[$key] = $matches[0];

        return $key;
    }, $html);

    $html = strtr($html, ww_rendered_html_translation_map());

    $html = str_replace('%2Fanalytics%2FÜbersicht', '%2Fanalytics%2FOverview', $html);

    $html = preg_replace('/(\d+)\s+items?/', '$1 Artikel', $html);

    return strtr($html, $protected_blocks);
}

add_action('wp_footer', 'ww_translate_dynamic_frontend_strings', 100);
function ww_translate_dynamic_frontend_strings() {
    ?>
    <script>
      window.wwTranslateDynamicFrontendStrings = function () {
        const replacements = <?php echo wp_json_encode(ww_dynamic_frontend_translation_pairs(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;

        const walker = document.createTreeWalker(document.body, 4);
        const nodes = [];
        while (walker.nextNode()) {
          if (!['SCRIPT', 'STYLE', 'NOSCRIPT'].includes(walker.currentNode.parentElement.tagName)) {
            nodes.push(walker.currentNode);
          }
        }

        nodes.forEach(function (node) {
          let value = node.nodeValue;
          replacements.forEach(function (entry) {
            value = value.split(entry[0]).join(entry[1]);
          });
          if (value.indexOf('Your personal data will be used to support your experience throughout this website') !== -1) {
            value = 'Deine personenbezogenen Daten werden verwendet, um dein Nutzungserlebnis auf dieser Website zu unterstützen, den Zugriff auf dein Konto zu verwalten und für weitere Zwecke, die in unserer Datenschutzerklärung beschrieben sind.';
          }
          value = value.split('Datumnschutz').join('Datenschutz');
          value = value.split('Datumnschutzerklärung').join('Datenschutzerklärung');
          value = value.replace(/Filtern+/g, 'Filtern');
          node.nodeValue = value;
        });

        document.querySelectorAll('a, button').forEach(function (element) {
          const text = element.textContent.trim();
          if (text === 'Proceed to Checkout' || text === 'Proceed to Kasse') {
            element.textContent = 'Weiter zur Kasse';
          }
          if (element.textContent.trim() === 'Weiter zur Kasse' && element.tagName === 'A') {
            element.setAttribute('href', '<?php echo esc_js(wc_get_checkout_url()); ?>');
          }
        });

        replacements.forEach(function (entry) {
          document.title = document.title.split(entry[0]).join(entry[1]);
        });

        document.querySelectorAll('input, textarea').forEach(function (field) {
          replacements.forEach(function (entry) {
            if (field.placeholder) {
              field.placeholder = field.placeholder.split(entry[0]).join(entry[1]);
            }
            if (field.type === 'submit' && field.value) {
              field.value = field.value.split(entry[0]).join(entry[1]);
            }
          });
          if (field.placeholder === 'you@example.com') {
            field.placeholder = 'du@example.com';
          }
          if (field.matches('.woocommerce-product-search .search-field')) {
            field.placeholder = 'Suche';
          }
        });
      };

      if (document.readyState === 'loading') {
        window.addEventListener('DOMContentLoaded', window.wwTranslateDynamicFrontendStrings);
      } else {
        window.wwTranslateDynamicFrontendStrings();
      }
      window.setTimeout(window.wwTranslateDynamicFrontendStrings, 500);
      window.setTimeout(window.wwTranslateDynamicFrontendStrings, 1500);
      window.setTimeout(window.wwTranslateDynamicFrontendStrings, 3000);
      new MutationObserver(window.wwTranslateDynamicFrontendStrings).observe(document.body, {
        childList: true,
        subtree: true
      });
    </script>
    <?php
}
