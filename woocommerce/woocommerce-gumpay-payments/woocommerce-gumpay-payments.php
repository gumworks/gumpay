<?php
/*
 * Plugin Name: WooCommerce GumPay Payment Gateway
 * Plugin URI: 
 * Description: Accept mobile payment on your website, support all major credit cards, Apple Pay, or even your in-store voucher on GumPay. Contact GumPay customer support team to obtain API key.
 * Author: Gumwork LLC.
 * Author URI: http://gumpay.app
 * Version: 1.0.0
 *
 */

 /*
 * This action hook registers our PHP class as a WooCommerce payment gateway
 */

 // Make sure WooCommerce is active
if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	return;
}

/**
 * Add the gateway to WC Available Gateways
 * 
 * @since 1.0.0
 * @param array $gateways all available WC gateways
 * @return array $gateways all WC gateways + offline gateway
 */
function gumpay_add_gateway_class( $gateways ) {
	$gateways[] = 'WC_Gumpay_Gateway'; // your class name is here
	return $gateways;
}
add_filter( 'woocommerce_payment_gateways', 'gumpay_add_gateway_class' );
 
/**
 * Adds plugin page links
 * 
 * @since 1.0.0
 * @param array $links all plugin links
 * @return array $links all plugin links + our custom links (i.e., "Settings")
 */
function gumpay_gateway_plugin_links( $links ) {

	$plugin_links = array(
		'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=woocommerce-gumpay-payments' ) . '">' . __( 'Configure', 'wc_gumpay' ) . '</a>'
	);

	return array_merge( $plugin_links, $links );
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'gumpay_gateway_plugin_links' );

/*
 * The class itself, please note that it is inside plugins_loaded action hook
 */
add_action( 'plugins_loaded', 'gumpay_init_gateway_class' );
function gumpay_init_gateway_class() {
 
	class WC_Gumpay_Gateway extends \WC_Payment_Gateway {

        const GUMPAY_TRANSACTION_ID_META_KEY = '_gpcp_gumpay_transaction_id';
        const GUMPAY_ORDER_LINK = '_gpcp_gumpay_order_link';

        const GUMPAY_ENVIRONMENT_URL = 'https://api.gumpay.app/';
        
 		/**
 		 * Class constructor, more about it in Step 3
 		 */
 		public function __construct() {
            $plugin_dir = plugin_dir_url(__FILE__);
            $this->id = 'woocommerce-gumpay-payments'; // payment gateway plugin ID
            $this->has_fields = false; // in case you need a custom credit card form
            $this->method_title = 'GumPay Payment Gateway';
            $this->method_description = 'GumPay allows small shops to easily get paid by support all major credit cards and Apple Pay. User simple scan a QR code to pay you.'; // will be displayed on the options page
         
            // gateways can support subscriptions, refunds, saved payment methods,
            // but in this tutorial we begin with simple payments
            $this->supports = array(
                'products'
            );
         
            // Method with all the options fields
            $this->init_form_fields();
         
            // Load the settings.
            $this->init_settings();
            $this->title = $this->get_option( 'title' );
            $this->description = '<img src="' . apply_filters( 'woocommerce_gateway_icon', $plugin_dir.'assets/images/GP-web-button1-2.png' ) . '">';
            $this->enabled = $this->get_option( 'enabled' );
            $this->private_key = $this->get_option( 'private_key' );
            $this->order_status    = $this->get_option( 'order_status' );
	
            // This action hook saves the settings
            add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
         
            add_filter( 'woocommerce_thankyou_order_received_text', array( $this, 'order_received_text' ), 10, 2 );

            // We need custom JavaScript to obtain a token
            //add_action( 'wp_enqueue_scripts', array( $this, 'payment_scripts' ) );
         
            // You can also register a webhook here
            // add_action( 'woocommerce_api_{webhook name}', array( $this, 'webhook' ) );
            add_action( 'woocommerce_api_' . strtolower( get_class( $this ) ), array(
                $this,
                'process_returned_response'
            ) );
 		}
 
		/**
 		 * Plugin options, we deal with it in Step 3 too
 		 */
 		public function init_form_fields(){
            $this->form_fields = array(
                'enabled' => array(
                    'title'       => 'Enable/Disable',
                    'label'       => 'Enable GumPay Gateway',
                    'type'        => 'checkbox',
                    'description' => '',
                    'default'     => 'no'
                ),
                'title' => array(
                    'title'       => 'Title',
                    'type'        => 'text',
                    'description' => 'This controls the title which the user sees during checkout.',
                    'default'     => 'Pay with GumPay',
                    'desc_tip'    => true,
                ),
                'order_status'        => array(
                    'title'       => __( 'Order Status After Payment', 'woocommerce-custom-payment-gateway' ),
                    'type'        => 'select',
                    'options'     => wc_get_order_statuses(),
                    'default'     => 'wc-processing',
                    'description' => __( 'The default order status if this gateway used in payment.', 'woocommerce-custom-payment-gateway' ),
                ),
                'private_key' => array(
                    'title'       => 'Live Private Key',
                    'type'        => 'password'
                )
            );
 
	 	}

         public function admin_options() {
            ?>
                       <table class="form-table">
                       <?php
                           // Generate the HTML For the settings form.
                           $this->generate_settings_html();
                       ?>
                       </table>
                       <?php
           } 
 
	
           
		/*
		 * We're processing the payments here, everything about it is in Step 5
		 */
		public function process_payment( $order_id ) {
            $this->init_settings();
            $this->private_key = $this->get_option( 'private_key' );
            $this->endpoint_url = WC_Gumpay_Gateway::GUMPAY_ENVIRONMENT_URL;
            $order = wc_get_order( $order_id );
           
            $payload = array(
                "uniqueKey"     => $this->private_key, 
                "externalOrderId" => $order->get_order_number(),
                "amount"        => $order->order_total,
                "returnUrl" =>  $this->get_return_url( $order )
            );
            $response = wp_remote_post( $this->endpoint_url . 'api/order/getorderlink', 
                array(
                    'method'    => 'POST',
                    'body'      => http_build_query( $payload ),
                    'timeout'   => 90,
                    'sslverify' => false,
                ) 
            );
            
            $gumpayResponse = trim(wp_remote_retrieve_body( $response ));

            if (is_wp_error( $response ) ) 
            {
                // Return failure redirect
                return array(
                    'result'    => 'failure',
                    'redirect'  => 'failed.php'
                );
            }
            else{
                $gumpayResponse = json_decode($gumpayResponse, false);
                if($gumpayResponse->Success)
                {
                    $order->update_meta_data( WC_Gumpay_Gateway::GUMPAY_ORDER_LINK, $gumpayResponse->Data );
                    $order->save();
              
                    // Reduce stock levels
                    $order->reduce_order_stock();
                   // Remove cart
                    WC()->cart->empty_cart();

                    return array(
                        'result'   => 'success',
                        'redirect' => $gumpayResponse->Data
                    );
                   /* // Return thankyou redirect
                    return array(
                        'result'    => 'success',
                        'redirect'	=> $this->get_return_url( $order )
                    );
                    */
                }
                return array(
                    'result'    => 'failure',
                    'redirect'  => 'failed.php'
                );
            }
 
	 	}
        public function process_returned_response() {
            do_action( 'custom_payment_process_returned_result' );
            exit;
        }

         /**
         * Custom GumPay order received text.
         *
         * @since 3.9.0
         * @param string   $text Default text.
         * @param WC_Order $order Order data.
         * @return string
         */
        public function order_received_text( $text, $order ) {
            $this->init_settings();
            $this->private_key = $this->get_option( 'private_key' );
            $this->endpoint_url = WC_Gumpay_Gateway::GUMPAY_ENVIRONMENT_URL;
            
            if ( $order && $this->id === $order->get_payment_method() ) {
                if($order->get_status() == 'pending')
                {
                    $payload = array(
                        "uniqueKey"     => $this->private_key, 
                        "externalOrderId" => $order->get_order_number()
                    );
                    $response = wp_remote_post( $this->endpoint_url . 'api/order/checkordercomplete', 
                        array(
                            'method'    => 'POST',
                            'body'      => http_build_query( $payload ),
                            'timeout'   => 90,
                            'sslverify' => false,
                        ) 
                    );
                
                    $gumpayResponse = trim(wp_remote_retrieve_body( $response ));
                    if (is_wp_error( $response ) ) 
                    {
                        // Return failure redirect
                        return array(
                            'result'    => 'failure',
                            'redirect'  => 'failed.php'
                        );
                    }
                    else{
                        $gumpayResponse = json_decode($gumpayResponse, false);
                        if($gumpayResponse->Success && !empty($gumpayResponse->Data))
                        {
                            $order->update_meta_data( WC_Gumpay_Gateway::GUMPAY_TRANSACTION_ID_META_KEY, $gumpayResponse->Data );
                            $order->update_status( $this->order_status );
                            $order->save();
                            return 'Thanks for pay with GumPay. Your order will be served soon';
                        }
                        return 'You can pay with GumPay following this link. <a href="' . $order->get_meta(WC_Gumpay_Gateway::GUMPAY_ORDER_LINK) .'"><img src="' . apply_filters( 'woocommerce_gateway_icon', plugin_dir_url(__FILE__).'assets/images/GP-web-button1-2.png' ) . '"></a>';
                    }
                }
            }

            return $text;
        }
 	}
}
