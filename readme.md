# WooCommerce integration for Sage 10 & Stage

This package enables WooCommerce integration with Stage & Sage 10 themes and Blade templates.

## Please use an Alternative

[Some other solutions](https://discourse.roots.io/t/woocommerce-support-for-sage-10/17999) have came up lastely to add WooCommerce support for Sage 10.
I tested a couple and decided to not maintain this package in the future. A better alternative is: https://github.com/generoi/sage-woocommerce

## Installation

Install the package in your theme folder:

```bash
composer require stealth-media/stage-woocommerce
```

## Usage

Create `woocommerce` folder in `/resources/views` folder of your theme and place there any template used by WooCommerce with `.blade.php` extension. This template will be loaded instead of a template from the WooCommerce plugin. If you want to replace particular template, please have a look into plugin folder `woocommerce/templates` and use same folder structure and file name (and change the extension to `.blade.php`) as the original template.

## Forked

This is a fork from `ouun/stage-woocommerce` to replace 'shop' with 'woocommerce' in the path to the templates.
