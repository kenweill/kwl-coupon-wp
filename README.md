# KWL Coupon WP

> A free, open source WordPress theme for coupon and deals websites. Beautiful by default. No paid plugins required.

[![License: GPL v2](https://img.shields.io/badge/License-GPL%20v2-blue.svg)](https://www.gnu.org/licenses/gpl-2.0)
[![WordPress: 6.0+](https://img.shields.io/badge/WordPress-6.0%2B-blue.svg)](https://wordpress.org)
[![PHP: 8.0+](https://img.shields.io/badge/PHP-8.0%2B-purple.svg)](https://php.net)

---

## Why KWL Coupon WP?

Every popular coupon WordPress theme is either:
- 💰 Expensive with annual renewal fees
- 🔒 Closed source — can't see or modify the core logic
- 🧱 Bloated with features most people don't need
- 😰 Built for PHP 7 / WP 5 and poorly maintained

**KWL Coupon WP is the alternative:**

- ✅ **100% free and open source** — GPL v2, forever
- ✅ **Beautiful by default** — looks great without configuration
- ✅ **Zero paid plugin dependencies** — everything baked in
- ✅ **SEO-first** — meta tags, schema markup, XML sitemaps — no Yoast needed
- ✅ **Modern codebase** — PHP 8.0+, WordPress 6.0+, vanilla JS (no jQuery frontend)
- ✅ **Community presets** — contribute new themes via pull request

---

## Features

### Coupon Management
- **Custom post types** for Stores and Coupons
- **Three coupon types**: Code (click to reveal), Deal (no code), Free Shipping
- **Click-to-reveal** with automatic clipboard copy
- **Expiry dates** — auto-hide or badge expired coupons
- **Verified / Exclusive badges**
- **Works / Doesn't Work voting** with success rate display
- **Affiliate link cloaking** via `/go/{store-slug}/` redirects
- **Coupon-specific affiliate URLs** (overrides store default)

### SEO (No Plugin Required)
- Auto-generated meta titles and descriptions per page type
- Open Graph + Twitter Card tags
- JSON-LD structured data (WebSite, Store, Offer, BreadcrumbList, FAQPage)
- Custom XML sitemap with 4 sub-sitemaps (stores, coupons, categories, pages)
- Sitemap pings Google and Bing on publish
- Smart canonical URLs and robots directives (noindex expired coupons)
- Breadcrumbs on all pages

### SEO-Friendly Slugs
- Dots preserved in slugs — `shopee.ph`, `shopee.my`, `shopee.sg` all distinct
- Auto-generated from store name
- Duplicate detection with suggested alternatives

### Design & Customization
- **3 built-in presets**: Clean (blue/white), Dark (green/dark), Warm (orange/cream)
- **Full WordPress Customizer** integration — colors, fonts, header, footer
- **CSS custom properties** throughout — easy to override
- Sidebar-right or full-width layout
- Toggleable Google Fonts (6 choices + system fonts)
- Custom CSS field in theme settings

### Admin
- Visual preset selector with color swatches
- 40+ theme options across 8 settings sections
- Custom admin columns for stores and coupons (color-coded expiry, type emoji, click counts)
- Filter coupons by store, type, and status in admin
- CSV bulk import for stores and coupons
- Coupon stats (clicks, votes, success rate) in admin

### Performance
- Zero jQuery dependency on frontend
- Lazy-loaded images
- Preconnect hints for Google Fonts
- Clean `<head>` (removes WordPress bloat)
- Preset system uses CSS variables only — no layout re-rendering

---

## Requirements

| Requirement | Version |
|-------------|---------|
| WordPress   | 6.0+    |
| PHP         | 8.0+    |
| MySQL       | 5.7+ or MariaDB 10.3+ |

---

## Installation

### Method 1 — Upload ZIP (Recommended)

1. Download the latest release ZIP from [GitHub Releases](https://github.com/kenweill/kwl-coupon-wp/releases)
2. Go to **WordPress Admin → Appearance → Themes → Add New → Upload Theme**
3. Upload the ZIP file and activate

### Method 2 — Clone from GitHub

```bash
cd /path/to/wordpress/wp-content/themes/
git clone https://github.com/kenweill/kwl-coupon-wp.git
```

Then activate via **Appearance → Themes**.

### Post-Installation

1. **Set permalink structure**: Go to **Settings → Permalinks** → select **Post name** → Save
   *(The theme will show a notice if this isn't set)*

2. **Add stores**: Go to **Stores → Add Store**

3. **Add coupons**: Go to **Coupons → Add Coupon**, link to a store

4. **Set homepage**: Go to **Settings → Reading** → set a static front page, or leave as-is for the latest coupons homepage

5. **Configure theme**: Go to **Appearance → Theme Settings**

---

## URL Structure

| URL | Content |
|-----|---------|
| `/store/{slug}/` | Single store page with all its coupons |
| `/stores/` | All stores archive |
| `/coupon/{slug}/` | Single coupon page |
| `/coupons/` | All coupons archive |
| `/category/{slug}/` | Category archive |
| `/coupon-tag/{slug}/` | Tag archive |
| `/go/{store-slug}/` | Affiliate redirect (cloaked) |
| `/sitemap.xml` | Sitemap index |

---

## CSV Import

Bulk-import stores and coupons via **Tools → Import Coupons**.

**Store CSV columns:**

| Column | Required | Notes |
|--------|----------|-------|
| `name` | ✓ | Store display name |
| `slug` | | Auto-generated if blank. Dots allowed (`shopee.ph`) |
| `website` | | Store homepage URL |
| `affiliate_url` | | Your affiliate link |
| `network` | | shareasale, cj, impact, awin, rakuten, amazon, direct, other |
| `description` | | Store description |
| `featured` | | 1 = featured on homepage |

**Coupon CSV columns:**

| Column | Required | Notes |
|--------|----------|-------|
| `title` | ✓ | Coupon title |
| `store_name` | ✓ | Must match an existing store name exactly |
| `type` | | code, deal, freeshipping. Default: code |
| `code` | | Coupon code (auto-uppercased) |
| `discount` | | E.g. `50% Off`, `$10 Off` |
| `expiry` | | YYYY-MM-DD format |
| `verified` | | 1 = verified |
| `exclusive` | | 1 = exclusive |
| `affiliate_url` | | Overrides store affiliate URL |
| `categories` | | Pipe-separated: `Web Hosting\|VPN` |
| `tags` | | Pipe-separated: `Free Shipping\|Student` |
| `description` | | Long description |

Download templates from the import page.

---

## Style Presets

Three presets are included. Switch via **Appearance → Theme Settings → Active Style Preset**.

| Preset | Colors | Best For |
|--------|--------|----------|
| **Clean** | Blue / White | General coupon sites |
| **Dark** | Green / Dark | SaaS, tech, software |
| **Warm** | Orange / Cream | Fashion, food, lifestyle |

### Adding Community Presets

Anyone can contribute a new preset — no PHP knowledge required:

1. Fork the repository
2. Create `/presets/your-preset-name/`
3. Add `preset.json` and `preset.css` (see [CONTRIBUTING.md](CONTRIBUTING.md))
4. Submit a pull request

---

## Theme Settings

Go to **Appearance → Theme Settings** to configure:

- **General**: Google Fonts toggle, affiliate link cloaking, currency code
- **Appearance**: Active preset, layout (sidebar/full-width), custom CSS
- **Homepage**: Hero title/subtitle, featured stores count, categories count, latest coupons count
- **Coupons**: Coupons per page, expired coupon visibility, expiring soon warning days, voting, reveal behavior
- **SEO**: Custom sitemap, search engine pinging, default OG image, title separator
- **Ads**: Enable ad slots, header/sidebar/in-feed ad code
- **Social**: Twitter, Facebook, Instagram, Pinterest, YouTube URLs
- **Advanced**: Footer text, header/footer scripts

---

## WordPress Customizer

Go to **Appearance → Customize → KWL Coupon WP** for live preview of:

- **Colors**: Primary, accent, background, card surface, text, navigation background
- **Typography**: Font family, base size, border radius
- **Header**: Sticky toggle, search visibility, category nav visibility
- **Footer**: Brand description, copyright text, background color

---

## Hooks & Filters

KWL Coupon WP is built to be extensible via standard WordPress hooks.

```php
// Change coupons per page on store pages
add_filter( 'kwl_store_coupons_per_page', function( $count ) {
    return 30;
} );

// Modify the auto-generated store meta description
add_filter( 'kwl_store_meta_description', function( $desc, $store_id ) {
    return "Custom description for " . get_the_title( $store_id );
}, 10, 2 );
```

---

## Child Theme Support

KWL Coupon WP supports child themes. All template files use `get_stylesheet_directory()` for overridable assets.

```css
/* child-theme/style.css */
/*
 Theme Name: My Coupon Site
 Template:   kwl-coupon-wp
*/

:root {
    --cwp-primary: #e11d48;  /* Override any CSS variable */
}
```

---

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md) for guidelines on:
- Submitting bug reports
- Proposing new features
- Adding new style presets
- Contributing code via pull requests

---

## License

KWL Coupon WP is licensed under the [GNU General Public License v2](LICENSE).

You are free to use, modify, and distribute this theme as long as you maintain the GPL v2 license. This means any derivative works must also be released under GPL v2.

---

## Credits

- Built by [Ken Weill](https://github.com/kenweill)
- Inspired by FameTheme Coupon WP's minimal approach
- Icons: inline SVG from [Heroicons](https://heroicons.com) (MIT license)
- Font: [Figtree](https://fonts.google.com/specimen/Figtree) via Google Fonts (OFL license)

---

## Changelog

### 1.0.0 (Initial Release)
- Custom post types: Stores, Coupons
- Custom taxonomies: Categories, Tags
- SEO system: meta tags, Open Graph, schema markup, XML sitemap
- Style preset system: Clean, Dark, Warm
- Full WordPress Customizer integration
- CSV bulk import for stores and coupons
- Live search autocomplete
- Click-to-reveal coupon codes
- Works/Doesn't Work voting
- Affiliate link cloaking
- Admin list table enhancements
- 5 custom sidebar widgets
