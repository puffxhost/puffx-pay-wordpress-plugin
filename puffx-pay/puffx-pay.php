<?php
/*
Plugin Name: Puffx Pay
Plugin URI: https://puffxhost.com
Description: Puffx Pay WooCommerce gateway. Uses pay.x-server.in PayIN API. Configure from WooCommerce -> Settings -> Payments -> Puffx Pay.
Version: 1.0
Author: Nitin Mehta
Text Domain: puffx-pay
*/

if (!defined('ABSPATH')) exit;

// Admin notice if WooCommerce not active
add_action('admin_init', function() {
    if (is_admin() && !in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
        add_action('admin_notices', function() {
            echo '<div class="notice notice-error"><p><strong>Puffx Pay:</strong> WooCommerce is not active. Please install & activate WooCommerce to use Puffx Pay gateway.</p></div>';
        });
    }
});

// Load gateway when plugins_loaded and WooCommerce is active
add_action('plugins_loaded', 'puffx_pay_init', 11);
function puffx_pay_init() {
    if (!class_exists('WC_Payment_Gateway')) return;

    // Include gateway class
    require_once plugin_dir_path(__FILE__) . 'includes/class-puffx-pay-gateway.php';

    // Add to available gateways
    add_filter('woocommerce_payment_gateways', function($methods) {
        $methods[] = 'WC_Gateway_Puffx_Pay';
        return $methods;
    });

    // Add settings link on plugin page
    add_filter('plugin_action_links_' . plugin_basename(__FILE__), function($links) {
        $settings_link = '<a href="' . admin_url('admin.php?page=wc-settings&tab=checkout&section=puffx_pay') . '">Settings</a>';
        array_unshift($links, $settings_link);
        return $links;
    });
}

// Activation/Deactivation hooks (optional placeholders)
register_activation_hook(__FILE__, function(){
    // nothing for now
});
register_deactivation_hook(__FILE__, function(){
    // nothing for now
});
