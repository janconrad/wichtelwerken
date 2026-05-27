/**
 * Wichtelwerken - main.js v2
 */
(function () {
  'use strict';

  // =========================================================
  // 1. HEADER: Scroll-Schatten
  // =========================================================
  const header = document.querySelector('.site-header');
  if (header) {
    window.addEventListener('scroll', () => {
      header.classList.toggle('ww-scrolled', window.scrollY > 60);
    }, { passive: true });
  }

  // =========================================================
  // 2. HERO: Parallax-Effekt (subtil)
  // =========================================================
  const heroBg = document.querySelector('.ww-hero__bg');
  if (heroBg) {
    window.addEventListener('scroll', () => {
      const offset = window.scrollY * 0.3;
      heroBg.style.transform = `translateY(${offset}px)`;
    }, { passive: true });
  }

  // =========================================================
  // 3. PRODUKT-BILDER: Fade-in beim Scrollen
  // =========================================================
  if ('IntersectionObserver' in window) {
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(e => {
        if (e.isIntersecting) {
          e.target.style.opacity = '1';
          e.target.style.transform = 'translateY(0)';
          observer.unobserve(e.target);
        }
      });
    }, { threshold: 0.1 });

    document.querySelectorAll('ul.products li.product').forEach(el => {
      el.style.opacity = '0';
      el.style.transform = 'translateY(16px)';
      el.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
      observer.observe(el);
    });
  }

  // =========================================================
  // 4. SCROLL-TO-TOP
  // =========================================================
  const btn = Object.assign(document.createElement('button'), {
    innerHTML: '<span aria-hidden="true">↑</span>',
    className: 'ww-scroll-top',
    ariaLabel: 'Nach oben',
  });
  Object.assign(btn.style, {
    position:'fixed', bottom:'28px', right:'28px',
    width:'44px', height:'44px',
    display:'flex', alignItems:'center', justifyContent:'center',
    background:'var(--ww-green-btn)', color:'#fff',
    border:'none', borderRadius:'50%',
    fontSize:'18px', lineHeight:'1', cursor:'pointer',
    opacity:'0', transition:'opacity 0.3s, transform 0.3s',
    zIndex:'9999', boxShadow:'0 4px 16px rgba(0,0,0,0.15)',
  });
  document.body.appendChild(btn);
  window.addEventListener('scroll', () => {
    btn.style.opacity   = window.scrollY > 400 ? '1' : '0';
    btn.style.transform = window.scrollY > 400 ? 'scale(1)' : 'scale(0.8)';
  }, { passive: true });
  btn.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));

})();
