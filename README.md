# WhatsApp Light

Lightweight and elegant WordPress plugin to add a floating WhatsApp chat button to your website.

## Features

- **Zero frontend overhead**: No extra HTTP requests or unnecessary scripts.
- **Pre-minified CSS**: The floating button styles are generated inline and minified on-the-fly.
- **Customizable**: Choose between Left and Right positions, and set a custom text label.
- **Direct to WhatsApp**: Uses the official `wa.me` API for a seamless transition to the app or web version.

## Installation

1. Upload the plugin files to the `/wp-content/plugins/whatsapp-light` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the **Plugins** screen in WordPress.
3. Navigate to **Settings > WhatsApp** to configure your phone number and options.

## Frequently Asked Questions

**Does it affect my page speed?**

No. WhatsApp Light was built specifically to have zero frontend impact, with no additional HTTP requests and all CSS carefully minified inline.

## Changelog

### 1.0.4
- Performance and security improvements
- Added Portuguese (Brazil) translation (`pt_BR`)
- Added `load_plugin_textdomain` hooks for standardized i18n support
