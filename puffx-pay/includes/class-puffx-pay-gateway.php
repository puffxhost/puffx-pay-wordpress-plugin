<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WC_Gateway_Puffx_Pay extends WC_Payment_Gateway {

    public function __construct() {
        $this->id                 = 'puffx_pay';
        $this->method_title       = 'Puffx Pay';
        $this->method_description = 'Accept payments using Puffx Pay API.';
        $this->has_fields         = false;

        // Load settings
        $this->init_form_fields();
        $this->init_settings();

        $this->title        = $this->get_option('title');
        $this->description  = $this->get_option('description');
        $this->api_key      = $this->get_option('api_key');
        $this->route        = $this->get_option('route');
        $this->redirect_url = $this->get_option('redirect_url');
        $this->webhook_url  = plugins_url('webhook-handler.php', dirname(__FILE__, 2));

        // Save admin settings
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, [$this, 'process_admin_options']);

        // Admin copy script
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
    }

    public function enqueue_admin_scripts() {
        wp_add_inline_script('jquery-core', "
            jQuery(document).ready(function($) {
                $('#puffx-copy-webhook').on('click', function(e) {
                    e.preventDefault();
                    var copyText = $('#woocommerce_puffx_pay_webhook_url').val();
                    navigator.clipboard.writeText(copyText).then(function() {
                        alert('Webhook URL copied to clipboard!');
                    });
                });
            });
        ");
    }

    public function init_form_fields() {
        $default_webhook = plugins_url('webhook-handler.php', dirname(__FILE__, 2));

        $this->form_fields = [
            'enabled' => [
                'title'   => 'Enable/Disable',
                'type'    => 'checkbox',
                'label'   => 'Enable Puffx Pay',
                'default' => 'yes'
            ],
            'title' => [
                'title'       => 'Title',
                'type'        => 'text',
                'default'     => 'Puffx Pay',
                'desc_tip'    => true,
                'description' => 'Title shown during checkout.'
            ],
            'description' => [
                'title'       => 'Description',
                'type'        => 'textarea',
                'default'     => 'Pay securely via Puffx Pay.',
                'description' => 'Description shown during checkout.'
            ],
            'api_key' => [
                'title'       => 'API Key (user_token)',
                'type'        => 'text',
                'default'     => '',
                'description' => 'Enter your Puffx Pay API key.'
            ],
            'route' => [
                'title'       => 'Route',
                'type'        => 'select',
                'options'     => [
                    '1' => 'Route 1 (Normal)',
                    '2' => 'Route 2 (VIP)',
                    '3' => 'Route 3'
                ],
                'default'     => '1',
                'description' => 'Select route for transactions.'
            ],
            'redirect_url' => [
                'title'       => 'Redirect URL',
                'type'        => 'text',
                'default'     => wc_get_checkout_url(),
                'description' => 'URL where customer will be redirected after payment.'
            ],
            'webhook_url' => [
                'title'       => 'Webhook URL',
                'type'        => 'text',
                'default'     => $default_webhook,
                'custom_attributes' => [
                    'readonly' => 'readonly'
                ],
                'description' => '<button id="puffx-copy-webhook" type="button" class="button">Copy Webhook URL</button>'
            ],
        ];
    }

    public function process_payment($order_id) {
        $order = wc_get_order($order_id);

        $api_url = 'https://pay.x-server.in/api/create-order';
        $current_url = preg_replace('/^www\./', '', $_SERVER['HTTP_HOST']);

        $post_data = [
            'customer_mobile' => $order->get_billing_phone(),
            'user_token'      => $this->api_key,
            'amount'          => $order->get_total(),
            'order_id'        => $order_id,
            'redirect_url'    => $this->redirect_url,
            'current_url'     => $current_url,
            'remark1'         => 'WooCommerce Order',
            'remark2'         => 'Puffx Pay Plugin',
            'route'           => $this->route
        ];

        $ch = curl_init($api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
        $response = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($response, true);

        if (!empty($result['status']) && !empty($result['result']['payment_url'])) {
            return [
                'result'   => 'success',
                'redirect' => $result['result']['payment_url']
            ];
        } else {
            wc_add_notice('Payment error: ' . ($result['message'] ?? 'Unknown error'), 'error');
            return;
        }
    }
}
