# 📋 Changelog — KWL Coupon WP

All notable changes to this project are documented here.

---

## [1.0.0]

### 🚀 New

**Post Types & Taxonomies**
- Custom post type: Stores
- Custom post type: Coupons
- Custom taxonomy: Categories
- Custom taxonomy: Tags

**Coupon Features**
- Three coupon types — Code (click to reveal), Deal (no code), Free Shipping
- Click-to-reveal coupon codes with automatic clipboard copy
- Expiry dates — auto-hide or badge expired coupons
- Verified and Exclusive badges
- Works / Doesn't Work voting with success rate display
- Affiliate link cloaking via `/go/{store-slug}/` redirects
- Coupon-specific affiliate URLs — overrides store default

**SEO**
- Auto-generated meta titles and descriptions per page type
- Open Graph and Twitter Card tags
- JSON-LD structured data — WebSite, Store, Offer, BreadcrumbList, FAQPage
- Custom XML sitemap with 4 sub-sitemaps (stores, coupons, categories, pages)
- Sitemap pings Google and Bing on publish
- Smart canonical URLs and robots directives (noindex expired coupons)
- Breadcrumbs on all pages
- Dots preserved in slugs — `shopee.ph`, `shopee.my`, `shopee.sg` all distinct
- Auto-generated slugs from store name with duplicate detection

**Design & Customization**
- 3 built-in style presets — Clean (blue/white), Dark (green/dark), Warm (orange/cream)
- Full WordPress Customizer integration — colors, fonts, header, footer
- CSS custom properties throughout — easy to override
- Sidebar-right or full-width layout
- Toggleable Google Fonts (6 choices + system fonts)
- Custom CSS field in theme settings

**Admin**
- Visual preset selector with color swatches
- 40+ theme options across 8 settings sections
- Custom admin columns for stores and coupons (color-coded expiry, type emoji, click counts)
- Filter coupons by store, type, and status in admin
- CSV bulk import for stores and coupons
- Coupon stats (clicks, votes, success rate) in admin

**Performance**
- Zero jQuery dependency on frontend
- Lazy-loaded images
- Preconnect hints for Google Fonts
- Clean `<head>` — removes WordPress bloat
- Preset system uses CSS variables only — no layout re-rendering

**Other**
- Live search autocomplete
- 5 custom sidebar widgets
- Hooks and filters for extensibility
- Child theme support
