# Contributing to KWL Coupon WP

First off — thank you for considering contributing. This project exists to give the coupon website community a genuinely good free option, and every contribution makes it better for everyone.

---

## Table of Contents

- [Code of Conduct](#code-of-conduct)
- [Ways to Contribute](#ways-to-contribute)
- [Submitting a New Style Preset](#submitting-a-new-style-preset)
- [Reporting Bugs](#reporting-bugs)
- [Suggesting Features](#suggesting-features)
- [Submitting Code](#submitting-code)
- [Coding Standards](#coding-standards)
- [Commit Message Format](#commit-message-format)

---

## Code of Conduct

Be respectful. Be constructive. This is a small community project — treat everyone the way you'd want to be treated.

---

## Ways to Contribute

| Contribution type | Skill required | Time |
|---|---|---|
| Submit a style preset | CSS only | ~30 minutes |
| Report a bug | None | ~5 minutes |
| Improve documentation | Writing | ~1 hour |
| Translate the theme | None / translation tools | Varies |
| Fix a bug | PHP / JS / CSS | Varies |
| Add a feature | PHP / WordPress | Varies |

---

## Submitting a New Style Preset

This is the easiest way to contribute — **no PHP knowledge required.**

A preset is just two files: `preset.json` (metadata) and `preset.css` (CSS variables).

### Step 1 — Fork and clone

```bash
git clone https://github.com/YOUR-USERNAME/kwl-coupon-wp.git
cd kwl-coupon-wp
```

### Step 2 — Create your preset folder

```
presets/
└── your-preset-name/
    ├── preset.json
    └── preset.css
```

Use a short, descriptive, lowercase slug: `ocean`, `midnight`, `forest`, `candy`, etc.

### Step 3 — Write preset.json

```json
{
    "name": "Ocean",
    "description": "Cool blue and teal tones. Great for travel and lifestyle sites.",
    "version": "1.0.0",
    "author": "Your Name",
    "author_uri": "https://github.com/yourusername",
    "colors": ["#0ea5e9", "#06b6d4", "#f0f9ff", "#0c4a6e"]
}
```

| Field | Required | Notes |
|---|---|---|
| `name` | ✓ | Display name shown in Theme Settings |
| `description` | ✓ | Short description of the preset's style |
| `version` | ✓ | Start at `1.0.0` |
| `author` | ✓ | Your name |
| `author_uri` | | Your GitHub or website URL |
| `colors` | ✓ | 3–5 hex colors used as swatches in the preset selector UI |

### Step 4 — Write preset.css

Your preset CSS must **only override CSS custom properties**. No layout rules, no component styles — just variables.

```css
/*
 * Ocean Preset for KWL Coupon WP
 * Author: Your Name
 * License: GPL v2
 */

:root {
    --cwp-primary:        #0ea5e9;
    --cwp-primary-hover:  #0284c7;
    --cwp-primary-light:  #f0f9ff;
    --cwp-accent:         #06b6d4;
    --cwp-accent-hover:   #0891b2;
    --cwp-bg:             #f8fafc;
    --cwp-surface:        #ffffff;
    --cwp-surface-alt:    #f0f9ff;
    --cwp-surface-hover:  #e0f2fe;
    --cwp-border:         #bae6fd;
    --cwp-text:           #0c4a6e;
    --cwp-text-muted:     #0369a1;
    --cwp-radius:         10px;
}
```

**Available CSS variables** (copy from `style.css` `:root` block):

```
--cwp-primary          Main brand color
--cwp-primary-hover    Hover state
--cwp-primary-light    Light tint (backgrounds, badges)
--cwp-accent           Secondary accent
--cwp-accent-hover
--cwp-success          Verified badge color
--cwp-danger           Expired, error states
--cwp-warning          Expiring soon
--cwp-bg               Page background
--cwp-surface          Card/panel background
--cwp-surface-alt      Alternate surface
--cwp-surface-hover    Hover background
--cwp-border           Border color
--cwp-text             Primary text
--cwp-text-muted       Secondary text
--cwp-text-light       Placeholder, disabled
--cwp-radius           Base border radius
--cwp-radius-sm        Small radius
--cwp-radius-md        Medium radius
--cwp-radius-lg        Large radius
```

You can also add additional rules for theme-specific overrides (e.g. nav background color):

```css
/* Optional: override nav bar */
.cwp-nav {
    background: #0ea5e9;
}

/* Optional: override footer */
.cwp-footer {
    background: #0c4a6e;
}
```

### Step 5 — Validate your preset

Your preset CSS must not contain:
- ❌ `<?php` or `<?=` (PHP tags)
- ❌ `<script>` tags or `javascript:` URLs
- ❌ `@import` statements
- ❌ Files larger than 50KB

### Step 6 — Submit a pull request

```bash
git checkout -b preset/ocean
git add presets/ocean/
git commit -m "Add Ocean preset"
git push origin preset/ocean
```

Then open a pull request on GitHub. In the PR description, include:
- A screenshot or colour palette preview
- Which niche/site type it's designed for
- Any special overrides beyond CSS variables

---

## Reporting Bugs

Use the [GitHub Issues](https://github.com/kenweill/kwl-coupon-wp/issues) tracker.

**Before reporting:**
- Check if the issue already exists
- Test with default theme settings (no customizations)
- Disable other plugins to rule out conflicts

**A good bug report includes:**
- WordPress version
- PHP version
- What you expected to happen
- What actually happened
- Steps to reproduce
- Screenshots if relevant

---

## Suggesting Features

Open a [GitHub Issue](https://github.com/kenweill/kwl-coupon-wp/issues) with the label `enhancement`.

Please include:
- **The problem** you're trying to solve (not just the solution)
- **Who benefits** from this feature
- **Whether it fits the project philosophy** — minimal, fast, no paid dependencies

Features that add significant weight, require paid services, or are very niche are unlikely to be merged into core. They may be better as a child theme or custom plugin.

---

## Submitting Code

### Setup

```bash
git clone https://github.com/kenweill/kwl-coupon-wp.git
```

No build tools required — this is a plain WordPress theme. No npm, no webpack, no compilation step.

### Branch naming

```
fix/slug-duplicate-detection
feature/user-submission-form
preset/forest
docs/installation-guide
```

### Pull Request Checklist

Before submitting a PR, please confirm:

- [ ] Code follows WordPress coding standards (see below)
- [ ] No new external dependencies introduced
- [ ] Existing functionality is not broken
- [ ] New functions have docblock comments
- [ ] Strings are wrapped in translation functions (`__()`, `esc_html__()`, etc.)
- [ ] No hardcoded text — everything should be translatable
- [ ] Security: inputs are sanitized, outputs are escaped
- [ ] The PR description explains what changed and why

---

## Coding Standards

### PHP

Follow [WordPress PHP Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/php/).

Key conventions used in this project:

```php
// Function prefix: kwl_
function kwl_my_function(): void {}

// CSS class prefix: cwp-
// Post meta keys: _kwl_
// Option key: kwl_theme_options (single array)

// Always escape output
echo esc_html( $value );
echo esc_url( $url );
echo esc_attr( $attr );
echo wp_kses_post( $html );

// Always sanitize input
$value = sanitize_text_field( $_POST['field'] ?? '' );
$id    = absint( $_POST['id'] ?? 0 );
$url   = esc_url_raw( $_POST['url'] ?? '' );

// Always verify nonces
check_ajax_referer( 'kwl_nonce', 'nonce' );
check_ajax_referer( 'kwl_admin_nonce', 'nonce' );

// Type declarations on all functions (PHP 8+)
function kwl_get_store( int $id ): ?WP_Post {}
```

### JavaScript

- Vanilla JS only on the frontend (no jQuery)
- jQuery is acceptable in admin JS (it's already loaded in WP admin)
- Use IIFE wrappers: `( function() { 'use strict'; ... } )()`
- `const` and `let` only — no `var`
- Async/await for AJAX — no raw callbacks
- All user-visible strings come from `kwlData` or `kwlAdmin` (localized via PHP)

### CSS

- Use CSS custom properties (variables) — never hardcode colors
- Follow the `cwp-` class prefix
- BEM-inspired naming: `.cwp-block__element--modifier`
- No `!important` unless absolutely necessary
- Mobile-first breakpoints: 1024px (tablet), 768px (mobile), 480px (small mobile)

---

## Commit Message Format

```
type: short description (max 72 chars)

Optional longer explanation if needed.
```

Types:
- `fix:` — bug fix
- `feat:` — new feature
- `preset:` — new style preset
- `docs:` — documentation only
- `style:` — CSS/formatting changes, no logic change
- `refactor:` — code change that neither fixes a bug nor adds a feature
- `chore:` — build process, dependencies, housekeeping

Examples:
```
fix: slug duplicate detection for TLD-style slugs (shopee.ph)
feat: add user-facing coupon submission form
preset: add Ocean blue/teal preset
docs: update CSV import column reference
```

---

## Translation

KWL Coupon WP is fully translation-ready. All strings use the `kwl-coupon-wp` text domain.

To generate the `.pot` template file:

```bash
wp i18n make-pot . languages/kwl-coupon-wp.pot --domain=kwl-coupon-wp
```

To contribute a translation:
1. Copy `languages/kwl-coupon-wp.pot`
2. Translate using [Poedit](https://poedit.net/) or similar
3. Save as `languages/kwl-coupon-wp-{locale}.po` (e.g. `kwl-coupon-wp-es_ES.po`)
4. Submit via pull request

---

## Questions?

Open an issue on GitHub — happy to help. 🙌
