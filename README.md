# Custom Elements for Elementor

A WordPress plugin that provides custom Elementor widgets designed for magazine and news-style websites.

**Author:** Jonathan  
**Version:** 1.0.0  
**Requires:** WordPress 6.0+, Elementor 3.0+, PHP 7.4+

---

## Folder & File Structure

```
custom-elements/
├── custom-elements.php
├── includes/
│   ├── Plugin.php
│   └── Widgets_Manager.php
├── widgets/
│   └── (widget class files go here)
├── assets/
│   ├── css/
│   │   └── widgets.css
│   └── js/
│       └── widgets.js
└── languages/
    └── (translation files go here)
```

---

## File & Folder Descriptions

### `custom-elements.php`
The main plugin entry point. WordPress reads this file to identify the plugin (name, version, author, etc.).

Responsibilities:
- Defines global constants (`CUSTOM_ELEMENTS_PATH`, `CUSTOM_ELEMENTS_URL`, version strings)
- Checks that Elementor is installed, activated, and meets the minimum version
- Checks that the server meets the minimum PHP version
- Displays admin notices if any of the above checks fail
- Loads `includes/Plugin.php` and boots the plugin singleton

---

### `includes/`
Core PHP classes that power the plugin. Nothing here is a widget — it's the engine.

| File | Description |
|---|---|
| `Plugin.php` | Singleton class. Registers hooks with WordPress and Elementor: widget category, widget registration, frontend styles and scripts. |
| `Widgets_Manager.php` | Scans the `widgets/` folder for `class-*.php` files, includes them, and registers each widget with Elementor automatically. |

> Adding a new widget is as simple as dropping a properly named class file into `widgets/` — no manual registration needed.

---

### `widgets/`
Home for all custom Elementor widget classes.

**Naming convention:** `class-{widget-slug}.php`  
**Namespace convention:** `CustomElements\Widgets\{Widget_Class_Name}`

| Example filename | Example class |
|---|---|
| `class-news-ticker.php` | `CustomElements\Widgets\News_Ticker` |
| `class-article-card.php` | `CustomElements\Widgets\Article_Card` |

Each widget class must extend `\Elementor\Widget_Base` and implement at minimum:
- `get_name()` — unique widget slug
- `get_title()` — label shown in the Elementor panel
- `get_icon()` — Elementor icon class
- `get_categories()` — return `['custom-elements']` to appear under the plugin's panel section
- `_register_controls()` — define widget settings/controls
- `render()` — output the frontend HTML

---

### `assets/`
Static frontend resources split by type.

#### `assets/css/widgets.css`
Shared stylesheet loaded on every page that uses Elementor. Use this for global/shared styles.  
Scope each widget's CSS under its own class to avoid conflicts, e.g. `.ce-news-ticker { ... }`.

#### `assets/js/widgets.js`
Shared JavaScript file registered (not enqueued globally) on the frontend. Widgets that need JS should enqueue it on demand.  
Use Elementor's frontend hook system to initialise per-widget behaviour:

```js
elementorFrontend.hooks.addAction(
    'frontend/element_ready/ce-widget-name.default',
    function ( $scope ) {
        // widget init code
    }
);
```

---

### `languages/`
Stores translation files (`.po` and `.mo`) for internationalisation (i18n).

The text domain used throughout the plugin is `custom-elements`.  
To generate a translation template, run WP-CLI:

```bash
wp i18n make-pot . languages/custom-elements.pot
```
