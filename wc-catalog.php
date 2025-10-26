<?php

/**
 * Plugin Name: WooCommerce Catalog
 * Description: Transforma a loja em catálogo e adiciona botão com link externo por produto. Possui opção para ocultar preços.
 * Version: 1.0.0
 * Author: Microframeworks
 * License: MIT
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('init', function() {
	// remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 999);
	// remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 999);
	remove_action('woocommerce_simple_add_to_cart', 'woocommerce_simple_add_to_cart', 999);
	remove_action('woocommerce_grouped_add_to_cart', 'woocommerce_grouped_add_to_cart', 999);
	remove_action('woocommerce_variable_add_to_cart', 'woocommerce_variable_add_to_cart', 999);
	remove_action('woocommerce_single_variation', 'woocommerce_single_variation', 999);
	remove_action('woocommerce_single_variation_add_to_cart_button', 'woocommerce_single_variation_add_to_cart_button', 999);
	add_filter('woocommerce_is_purchasable', '__return_false');
	add_filter('woocommerce_is_sold_individually', '__return_true');
	add_filter('woocommerce_product_add_to_cart_url', '__return_empty_string');
	// add_filter('woocommerce_get_price_html', '__return_empty_string');
	// add_filter('woocommerce_cart_item_price', '__return_empty_string');
});

add_action('wp', function() {
	remove_action('astra_header', 'astra_woocommerce_header_cart', 999);
});

add_filter('astra_enable_header_cart', '__return_false');

add_action('wp', function() {
	remove_action('astra_header', 'astra_woocommerce_header_cart', 10);
	add_filter('astra_enable_header_cart', '__return_false');
});

add_action('wp_head', function() {
echo '<style>
.add_to_cart_button,
.single_add_to_cart_button,
.woocommerce-cart,
.woocommerce-checkout,
#site-header-cart,
.ast-site-header-cart,
.ast-header-woo-cart,
.widget_shopping_cart,
.woocommerce-mini-cart {
	display: none !important;
}
#header_cart,
.top_bar_right_wrapper a[href*="carrinho"],
.top_bar_right_wrapper a[href*="cart"],
.icon-bag-fine,
#header-cart-count {
	display: none !important;
}
</style>';
});

include __DIR__ . '/WCCatalog.php';

new WCCatalog();
