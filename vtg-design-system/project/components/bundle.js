/* @ds-bundle: {"format":4,"namespace":"VTG","components":[{"name":"GroupBar"},{"name":"SiteMark"},{"name":"NavBar"},{"name":"Hero"},{"name":"SectionHeading"},{"name":"ServiceCard"},{"name":"ProjectCard"},{"name":"FloorIndicator"},{"name":"Button"},{"name":"Badge"},{"name":"Icon"},{"name":"Field"},{"name":"Footer"}]} */
(function () {
  var R = window.React, h = R.createElement;
  function cx() { return Array.prototype.filter.call(arguments, Boolean).join(' '); }
  function omit(p, keys) { var o = {}; for (var k in p) if (keys.indexOf(k) < 0) o[k] = p[k]; return o; }

  var PATHS = {
    up: 'M12 20V5M5.5 11.5 12 5l6.5 6.5',
    down: 'M12 4v15M5.5 12.5 12 19l6.5-6.5',
    right: 'M4 12h15M12.5 5.5 19 12l-6.5 6.5',
    phone: 'M5 3.5h3.5l1.8 4.6-2.3 1.4a11 11 0 0 0 6.5 6.5l1.4-2.3 4.6 1.8V19c0 .8-.7 1.5-1.6 1.5C10.4 20 4 13.6 3.5 5.1c0-.9.7-1.6 1.5-1.6z',
    mail: 'M3.5 5.5h17v13h-17zM3.5 6.5 12 13l8.5-6.5',
    wrench: 'M14.5 3.5a4.5 4.5 0 0 0-4.3 5.9L3.5 16.1l4.4 4.4 6.7-6.7a4.5 4.5 0 0 0 5.9-4.3l-2.9 2.9-3.2-.8-.8-3.2z',
    shield: 'M12 3.5 19 6v5.5c0 4.4-3 7.7-7 9-4-1.3-7-4.6-7-9V6z',
    clock: 'M12 3.5a8.5 8.5 0 1 0 0 17 8.5 8.5 0 0 0 0-17zM12 7.5V12l3 2',
    check: 'M5 12.5l5 5 9-10',
    menu: 'M4 7h16M4 12h16M4 17h16'
  };

  function Icon(p) {
    var s = p.size || 20;
    return h('svg', {
      className: cx('vtg-icon', p.className), width: s, height: s, viewBox: '0 0 24 24', fill: 'none',
      stroke: 'currentColor', strokeWidth: 1.75, strokeLinecap: 'square', strokeLinejoin: 'miter',
      role: p.label ? 'img' : undefined, 'aria-label': p.label, 'aria-hidden': p.label ? undefined : true
    }, h('path', { d: PATHS[p.name] || PATHS.right }));
  }

  function Button(p) {
    var v = p.variant || 'primary', sz = p.size || 'md';
    var cls = cx('vtg-btn', 'vtg-btn--' + v, 'vtg-btn--' + sz, p.className);
    var kids = [h('span', { key: 'l' }, p.children)];
    if (p.icon) kids.push(h(Icon, { key: 'i', name: p.icon, size: sz === 'lg' ? 20 : 18 }));
    var rest = omit(p, ['variant', 'size', 'icon', 'className', 'children', 'href']);
    return p.href
      ? h('a', Object.assign({ href: p.href, className: cls }, rest), kids)
      : h('button', Object.assign({ type: 'button', className: cls }, rest), kids);
  }

  function Badge(p) {
    var tone = p.tone || 'neutral';
    var icon = p.icon || (tone === 'success' ? 'check' : null);
    return h('span', { className: cx('vtg-badge', 'vtg-badge--' + tone, p.className) },
      icon ? h(Icon, { name: icon, size: 14 }) : null, p.children);
  }

  function SectionHeading(p) {
    var Tag = 'h' + (p.level || 2);
    return h('div', { className: cx('vtg-heading', p.align === 'center' && 'vtg-heading--center', p.className) },
      h(Tag, { className: cx(p.size || 'h2', p.steel && 'vtg-steel') }, p.title),
      p.lead ? h('p', { className: 'lead vtg-lead' }, p.lead) : null);
  }

  function FloorIndicator(p) {
    var d = p.direction;
    return h('div', { className: cx('vtg-floor', p.className), role: 'group', 'aria-label': p.ariaLabel || ((p.label || '') + ' ' + p.value) },
      h('div', { className: 'vtg-floor__arrows' },
        h('span', { className: cx('vtg-floor__arrow vtg-floor__arrow--up', d === 'up' && 'is-on') }, h(Icon, { name: 'up', size: 18 })),
        h('span', { className: cx('vtg-floor__arrow vtg-floor__arrow--down', d === 'down' && 'is-on') }, h(Icon, { name: 'down', size: 18 }))),
      h('div', { className: 'vtg-floor__body' },
        h('span', { className: 'floor vtg-floor__value' }, p.value),
        p.label ? h('span', { className: 'label vtg-floor__label' }, p.label) : null));
  }

  function GroupBar(p) {
    var sites = p.sites || [];
    return h('div', { className: cx('vtg-groupbar', p.className), 'data-theme': 'dark' },
      h('div', { className: 'vtg-container vtg-groupbar__in' },
        h('span', { className: 'label vtg-groupbar__name' }, p.groupName || 'The Vertical Transportation Group'),
        h('nav', { 'aria-label': 'Group websites' },
          h('ul', { className: 'vtg-groupbar__list' }, sites.map(function (s) {
            return h('li', { key: s.num },
              h('a', { href: s.href || '#', className: cx('vtg-groupbar__link', s.current && 'is-current'), 'aria-current': s.current ? 'page' : undefined },
                h('span', { className: 'data vtg-groupbar__num' }, s.num), s.label));
          })))));
  }

  function SiteMark(p) {
    return h('a', { href: p.href || '/', className: cx('vtg-sitemark', p.className), 'aria-label': p.ariaLabel || ('The Vertical Transportation Group' + (p.name ? ' — ' + p.name : '')) },
      h('img', { src: p.logoSrc, alt: '', className: 'vtg-sitemark__logo', height: p.logoHeight || 44 }),
      p.name ? h('span', { className: 'vtg-sitemark__site' },
        h('span', { className: 'data vtg-sitemark__num' }, p.num),
        h('span', { className: 'vtg-sitemark__text' },
          h('span', { className: 'vtg-sitemark__name' }, p.name),
          p.sub ? h('span', { className: 'vtg-sitemark__sub' }, p.sub) : null)) : null);
  }

  function NavBar(p) {
    var links = p.links || [];
    return h('header', { className: cx('vtg-nav', p.className), 'data-theme': 'dark' },
      h('div', { className: 'vtg-container vtg-nav__in' },
        p.brand,
        h('nav', { className: 'vtg-nav__links', 'aria-label': 'Main' },
          h('ul', null, links.map(function (l) {
            return h('li', { key: l.label }, h('a', { href: l.href || '#', className: cx('vtg-nav__link', l.active && 'is-active'), 'aria-current': l.active ? 'page' : undefined }, l.label));
          }))),
        h('div', { className: 'vtg-nav__actions' },
          p.phone ? h('a', { href: 'tel:' + p.phone.replace(/\s/g, ''), className: 'vtg-nav__phone' }, h(Icon, { name: 'phone', size: 18 }), h('span', { className: 'data' }, p.phone)) : null,
          p.cta ? h(Button, { variant: p.cta.variant || 'amber', href: p.cta.href, size: 'md' }, p.cta.label) : null,
          h('button', { type: 'button', className: 'vtg-nav__menu', 'aria-label': 'Open menu' }, h(Icon, { name: 'menu', size: 22 })))));
  }

  function Hero(p) {
    return h('section', { className: cx('vtg-hero', p.className), 'data-theme': p.theme || 'dark' },
      h('div', { className: 'vtg-container vtg-hero__in' },
        h('div', { className: 'vtg-hero__copy' },
          h('h1', { className: cx(p.size || 'display-xl', 'vtg-steel') }, p.title),
          p.lead ? h('p', { className: 'lead vtg-lead' }, p.lead) : null,
          p.actions ? h('div', { className: 'vtg-hero__actions' }, p.actions) : null),
        p.image ? h('figure', { className: 'vtg-hero__media' },
          h('img', { src: p.image, alt: p.imageAlt || '' }),
          p.aside ? h('div', { className: 'vtg-hero__aside' }, p.aside) : null) : null));
  }

  function ServiceCard(p) {
    return h('article', { className: cx('vtg-service', p.className) },
      h('div', { className: 'vtg-service__top' },
        h('span', { className: 'data vtg-service__num' }, p.num),
        p.icon ? h(Icon, { name: p.icon, size: 28, className: 'vtg-service__icon' }) : null),
      h('h3', { className: 'h3' }, p.title),
      p.subtitle ? h('p', { className: 'small vtg-service__sub' }, p.subtitle) : null,
      p.items ? h('ul', { className: 'small vtg-service__items' }, p.items.map(function (i) { return h('li', { key: i }, i); })) : null,
      p.href ? h('a', { href: p.href, className: 'vtg-link' }, p.linkLabel || 'Learn more', h(Icon, { name: 'right', size: 18 })) : null);
  }

  function ProjectCard(p) {
    return h('a', { href: p.href || '#', className: cx('vtg-project', p.tall && 'vtg-project--tall', p.className) },
      h('div', { className: 'vtg-project__media' }, h('img', { src: p.image, alt: p.imageAlt || '' })),
      h('div', { className: 'vtg-project__meta' },
        p.category ? h(Badge, { tone: 'up' }, p.category) : null,
        h('h3', { className: 'h3' }, p.title),
        p.location ? h('p', { className: 'small vtg-project__loc' }, p.location) : null));
  }

  function Field(p) {
    var id = p.id || ('f' + R.useId().replace(/:/g, ''));
    var desc = p.error ? id + '-err' : (p.hint ? id + '-hint' : undefined);
    var Ctrl = p.multiline ? 'textarea' : 'input';
    var rest = omit(p, ['id', 'label', 'hint', 'error', 'multiline', 'className', 'optional']);
    return h('div', { className: cx('vtg-field', p.error && 'is-invalid', p.className) },
      h('label', { htmlFor: id, className: 'body-strong vtg-field__label' }, p.label, p.optional ? h('span', { className: 'vtg-field__opt' }, ' (optional)') : null),
      h(Ctrl, Object.assign({ id: id, className: 'vtg-field__ctrl', 'aria-invalid': p.error ? true : undefined, 'aria-describedby': desc, rows: p.multiline ? 4 : undefined }, rest)),
      p.error ? h('p', { id: id + '-err', className: 'small vtg-field__error' }, 'Error: ' + p.error)
        : p.hint ? h('p', { id: id + '-hint', className: 'small vtg-field__hint' }, p.hint) : null);
  }

  function Footer(p) {
    var cols = p.columns || [];
    return h('footer', { className: cx('vtg-footer', p.className), 'data-theme': 'dark' },
      h('div', { className: 'vtg-container' },
        h('div', { className: 'vtg-footer__top' },
          h('div', { className: 'vtg-footer__brand' },
            p.logoSrc ? h('img', { src: p.logoSrc, alt: 'The Vertical Transportation Group', className: 'vtg-footer__logo' }) : null,
            p.tagline ? h('p', { className: 'small vtg-footer__tag' }, p.tagline) : null),
          cols.map(function (c) {
            return h('div', { key: c.title, className: 'vtg-footer__col' },
              h('p', { className: 'label vtg-footer__title' }, c.title),
              h('ul', null, c.links.map(function (l) { return h('li', { key: l.label }, h('a', { href: l.href || '#' }, l.label)); })));
          })),
        h('div', { className: 'small vtg-footer__legal' }, p.legal)));
  }

  window.VTG = Object.assign(window.VTG || {}, {
    Icon: Icon, Button: Button, Badge: Badge, SectionHeading: SectionHeading, FloorIndicator: FloorIndicator,
    GroupBar: GroupBar, SiteMark: SiteMark, NavBar: NavBar, Hero: Hero, ServiceCard: ServiceCard,
    ProjectCard: ProjectCard, Field: Field, Footer: Footer
  });
})();
