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
        '2.0.10'
    );
    wp_enqueue_script(
        'wichtelwerken-main',
        get_stylesheet_directory_uri() . '/js/main.js',
        ['jquery'],
        '2.0.0',
        true
    );
    wp_localize_script('wichtelwerken-main', 'ww_vars', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('ww_nonce'),
        'shop_url' => ww_get_shop_url(),
    ]);
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
  .storefront-handheld-footer-bar { background: var(--ww-green-dark); }
  .site-header { background-color: var(--ww-cream) !important; }
  /* Scrolled State wird per JS gesetzt */
  .site-header.ww-scrolled { box-shadow: 0 2px 12px rgba(45,74,36,0.08); }
</style>
<?php }

// =========================================================
// 4. WOOCOMMERCE ANPASSUNGEN
// =========================================================
add_filter('loop_shop_per_page',  fn() => 12, 20);
add_filter('loop_shop_columns',   fn() => 3);

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
add_filter('option_woocommerce_price_display_suffix', fn() => 'inkl. MwSt.');

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
        'Are you sure?' => 'Bist du sicher?',
        'Attribute Name' => 'Attributname',
        'Available' => 'Verfügbar',
        'Built with WooCommerce.' => 'Erstellt mit WooCommerce.',
        'Calculating' => 'Wird berechnet',
        'Cancel' => 'Abbrechen',
        'Cart' => 'Warenkorb',
        'Checkout' => 'Kasse',
        'Choose a file' => 'Datei auswählen',
        'Choose Image' => 'Bild auswählen',
        'Dashboard' => 'Übersicht',
        'Default sorting' => 'Standardsortierung',
        'Delete Permanently' => 'Endgültig löschen',
        'Direct bank transfer' => 'Banküberweisung',
        'Edit Account' => 'Konto bearbeiten',
        'Edit' => 'Bearbeiten',
        'Email address' => 'E-Mail-Adresse',
        'First Name' => 'Vorname',
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
        'No products in the cart.' => 'Es befinden sich keine Produkte im Warenkorb.',
        'No Result Found' => 'Keine Ergebnisse gefunden',
        'Not Available' => 'Nicht verfügbar',
        'Phone Number' => 'Telefonnummer',
        'Phone Number*' => 'Telefonnummer*',
        'Place Order' => 'Bestellung aufgeben',
        'Proceed to Checkout' => 'Weiter zur Kasse',
        'Proceed to Kasse' => 'Weiter zur Kasse',
        'Product category is required' => 'Eine Produktkategorie ist erforderlich',
        'Product created successfully' => 'Produkt erfolgreich erstellt',
        'Product title is required' => 'Ein Produkttitel ist erforderlich',
        'Products' => 'Produkte',
        'Register' => 'Registrieren',
        'Registration' => 'Registrierung',
        'Remember me' => 'Angemeldet bleiben',
        'Required' => 'Pflichtfeld',
        'Return to Cart' => 'Zurück zum Warenkorb',
        'Search' => 'Suchen',
        'Search category' => 'Kategorie suchen',
        'Search for:' => 'Suche nach:',
        'Search Products' => 'Produkte suchen',
        'Search products' => 'Produkte suchen',
        'Search products&hellip;' => 'Suche',
        'Searching…' => 'Suche läuft…',
        'Select and Crop' => 'Auswählen und zuschneiden',
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
        'Balance:' => 'Kontostand:',
        'Orders' => 'Bestellungen',
        'Withdraw' => 'Auszahlungen',
        'Settings' => 'Einstellungen',
        'Payment' => 'Zahlung',
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
        'Save Changes' => 'Änderungen speichern',
        'Add Product' => 'Produkt hinzufügen',
        'Product' => 'Produkt',
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
        'Send Message' => 'Nachricht senden',
        'Type your message...' => 'Nachricht eingeben...',
        'Uncategorized' => 'Allgemein',
        'Your Name' => 'Dein Name',
        'Your cart is currently empty!' => 'Dein Warenkorb ist aktuell leer.',
        'You are already logged in' => 'Du bist bereits angemeldet.',
        'Something went wrong. Please try again.' => 'Etwas ist schiefgelaufen. Bitte versuche es erneut.',
        'Upload featured image' => 'Produktbild hochladen',
        'View' => 'Ansehen',
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
        'Balance:',
        'Orders',
        'Withdraw',
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
        'Your personal data will be used to support your experience throughout this website, to manage access to your account, and for other purposes described in our privacy policy.',
    ]) + [
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
        'Orders',
        'Withdraw',
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
    ]) + [
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
