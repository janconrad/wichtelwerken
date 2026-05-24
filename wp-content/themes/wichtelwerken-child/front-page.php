<?php
/**
 * Wichtelwerken – Startseite v2
 * Design basiert auf Lovable-Entwurf (April 2026)
 */
get_header(); ?>

<main id="main" class="site-main">

  <!-- =====================================================
       HERO – Vollbild mit Foto-Hintergrund
       Foto: themes/wichtelwerken-child/images/hero-bg.png
  ===================================================== -->
  <section class="ww-hero">
    <div class="ww-hero__bg" style="
      background-image: url('<?php echo get_stylesheet_directory_uri(); ?>/images/hero-bg.png'),
        linear-gradient(135deg, #e8f0e4 0%, #f0e8e0 40%, #e8ddd4 70%, #f5ede6 100%);
    "></div>
    <div class="ww-hero__overlay"></div>

    <div class="ww-hero__content">
      <span class="ww-hero__badge">Öko, Fair &amp; Selbstgemacht</span>

      <h1 class="ww-hero__title">
        Handgemacht<br><em class="ww-accent">mit Herz</em>
      </h1>

      <p class="ww-hero__subtitle">
        Dein Marktplatz für Manufakturprodukte, Handgemachtes und liebevolle
        Unikate. Von Menschen mit Herz — nachhaltig und fair.
      </p>

      <div class="ww-hero__buttons">
        <a href="<?php echo esc_url(ww_get_shop_url()); ?>"
           class="ww-btn ww-btn--primary">
          Jetzt entdecken &rarr;
        </a>
        <a href="<?php echo esc_url(ww_get_vendor_signup_url()); ?>"
           class="ww-btn ww-btn--outline">
          Verkäufer werden
        </a>
      </div>
    </div>
  </section>

  <!-- =====================================================
       KATEGORIEN
  ===================================================== -->
  <section class="ww-categories">
    <p class="ww-section-label">Kategorien</p>
    <div class="ww-categories__grid">
      <?php
      $icons = ['🧸','👕','🎨','🌿','🕯️','🧶','🪴'];
      $kategorien = get_terms([
        'taxonomy'   => 'product_cat',
        'hide_empty' => true,
        'parent'     => 0,
        'number'     => 8,
      ]);
      if (!empty($kategorien) && !is_wp_error($kategorien)) :
        foreach ($kategorien as $i => $kat) : ?>
          <a href="<?php echo esc_url(get_term_link($kat)); ?>" class="ww-category-tile">
            <span class="ww-category-tile__icon"><?php echo esc_html(ww_get_product_category_icon($kat, $icons[$i % count($icons)])); ?></span>
            <span class="ww-category-tile__label"><?php echo esc_html($kat->name); ?></span>
          </a>
        <?php endforeach;
      else : ?>
        <?php foreach (['Spielzeug','Kleidung','Gebastelt','Bio & Natur','Kerzen','Bücher','Wolle & Garn','Pflanzen'] as $i => $name) : ?>
          <a href="<?php echo esc_url(ww_get_shop_url()); ?>" class="ww-category-tile">
            <span class="ww-category-tile__icon"><?php echo $icons[$i]; ?></span>
            <span class="ww-category-tile__label"><?php echo esc_html($name); ?></span>
          </a>
        <?php endforeach;
      endif; ?>
    </div>
  </section>

  <!-- =====================================================
       NEUE PRODUKTE
  ===================================================== -->
  <section class="ww-products-preview">
    <div class="ww-section-header">
      <p class="ww-section-label ww-section-label--inline">Neu eingestellt</p>
      <a href="<?php echo esc_url(ww_get_shop_url()); ?>"
         class="ww-section-link">
        Alle anzeigen &rarr;
      </a>
    </div>
    <?php
    if (function_exists('WC')) {
        echo do_shortcode('[recent_products limit="6" columns="3" orderby="date" order="DESC"]');
    } else {
        echo '<p class="ww-placeholder">Produkte erscheinen hier, sobald WooCommerce eingerichtet ist.</p>';
    }
    ?>
  </section>

  <!-- =====================================================
       USP STREIFEN
  ===================================================== -->
  <section class="ww-usp">
    <div class="ww-usp__grid">
      <div>
        <div class="ww-usp__icon">🌿</div>
        <h4 class="ww-usp__title">Nachhaltig</h4>
        <p class="ww-usp__text">Bewusst auswählen, sorgfältig herstellen — weniger Wegwerfkultur, mehr Wertschätzung.</p>
      </div>
      <div>
        <div class="ww-usp__icon">🤝</div>
        <h4 class="ww-usp__title">Fair</h4>
        <p class="ww-usp__text">Einrichtungen und Familien verdienen direkt. Kleine Provision, große Wirkung.</p>
      </div>
      <div>
        <div class="ww-usp__icon">💚</div>
        <h4 class="ww-usp__title">Selbstgemacht</h4>
        <p class="ww-usp__text">Jedes Stück ist ein Unikat mit Geschichte — von echten Menschen für echte Menschen.</p>
      </div>
    </div>
  </section>

  <!-- =====================================================
       ANBIETER CTA
  ===================================================== -->
  <section class="ww-cta">
    <div class="ww-cta__inner">
      <span class="ww-cta__badge">Für Vereine &amp; Einrichtungen</span>
      <h2 class="ww-cta__title">Euren Basar<br><em>online bringen</em></h2>
      <p class="ww-cta__text">
        Richtet euren eigenen Shop ein und verkauft gebastelte Artikel,
        Handgemachtes und besondere Manufakturprodukte — ganz ohne technische Vorkenntnisse.
      </p>
      <a href="<?php echo esc_url(ww_get_vendor_signup_url()); ?>"
         class="ww-btn ww-btn--primary ww-cta__button">
        Jetzt Shop eröffnen
      </a>
    </div>
  </section>

</main>

<?php get_footer(); ?>
