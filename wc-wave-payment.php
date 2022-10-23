<?php
/*
 * Plugin Name: WooCommerce Wave Payment Gateway
 * Plugin URI: https://softtechiit.com
 * Description: Take wave payments on your store.
 * Author: Jh Fahim
 * Author URI: http://jhfahim.com
 * Version: 1.0.1
 */


 /*
 * This action hook registers our PHP class as a WooCommerce payment gateway
 */
add_filter( 'woocommerce_payment_gateways', 'wave_add_gateway_class' );
function wave_add_gateway_class( $gateways ) {
	$gateways[] = 'WC_Wave_Gateway'; // your class name is here
	return $gateways;
}



/*
 * The class itself, please note that it is inside plugins_loaded action hook
 */
add_action( 'plugins_loaded', 'wave_init_gateway_class' );
function wave_init_gateway_class() {

	class WC_Wave_Gateway extends WC_Payment_Gateway {

 		/**
 		 * Class constructor, more about it in Step 3
 		 */
 		public function __construct() {

         $this->id = 'wave'; // payment gateway plugin ID
         $this->icon =  plugin_dir_url( ( __FILE__ ) ) . 'assets/img/wave-logo.png';; // URL of the icon that will be displayed on checkout page near your gateway name
         $this->has_fields = true; // in case you need a custom credit card form
         $this->method_title = 'Wave Gateway';
         $this->method_description = 'Wave payment gateway'; // will be displayed on the options page

         // gateways can support subscriptions, refunds, saved payment methods,
         // but in this tutorial we begin with simple payments
         $this->supports = array(
            'products'
         );

         // Method with all the options fields
         $this->init_form_fields();

         // Load the settings.
         $this->init_settings();
         $this->title = $this->get_option( 'wave_title' );
         $this->description = $this->get_option( 'wave_description' );
         $this->enabled = $this->get_option( 'enabled' );
         $this->get_option( 'wave_api_key' );
         // This action hook saves the settings
         add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );

         add_action('wp_head', function(){

            $api_key = get_option('woocommerce_wave_settings')['wave_api_key'] ;
            
            // $amount = 1200;
            // $currency = "XOF";
            // $url = "http://google.com";
            
            // $options = array();
            
            // $options['amount'] = $amount;
            // $options['currency'] = $currency;
            // $options['error_url'] = "https://example.com/error";
            // $options['success_url'] = "https://example.com/success";
            
            // $options_obj = json_encode( $options );
            
            
            
            // $curl = curl_init();

            // curl_setopt_array($curl, array(
            //     CURLOPT_URL => 'https://api.wave.com/v1/checkout/sessions',
            //     CURLOPT_RETURNTRANSFER => true,
            //     CURLOPT_ENCODING => '',
            //     CURLOPT_MAXREDIRS => 10,
            //     CURLOPT_TIMEOUT => 0,
            //     CURLOPT_FOLLOWLOCATION => true,
            //     CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            //     CURLOPT_CUSTOMREQUEST => 'POST',
            //     CURLOPT_POSTFIELDS => $options_obj,
            //     CURLOPT_HTTPHEADER => array(
            //       'Content-Type: application/json',
            //       'Authorization: Bearer ' . $api_key
            //     ),
            // ));

            // $response = curl_exec($curl);

            // curl_close($curl);
            
            
            // echo json_decode( $response, true )['wave_launch_url'];
            
            // echo "<br>";
            
            // echo $api_key;
            
            // echo get_option('woocommerce_currency') , ' second';
            

            
         });
         // We need custom JavaScript to obtain a token
         //add_action( 'wp_enqueue_scripts', array( $this, 'payment_scripts' ) );
         
         // You can also register a webhook here
         // add_action( 'woocommerce_api_{webhook name}', array( $this, 'webhook' ) );

	

 		}

		/**
 		 * Plugin options, we deal with it in Step 3 too
 		 */
 		public function init_form_fields(){

         $this->form_fields = array(
            'enabled' => array(
               'title'       => 'Enable/Disable',
               'label'       => 'Enable Wave Gateway',
               'type'        => 'checkbox',
               'description' => '',
               'default'     => 'no'
            ),
            'wave_title' => array(
               'title'       => 'Title',
               'type'        => 'text',
               'description' => 'This controls the title which the user sees during checkout.',
               'default'     => 'Wave Payment',
               'desc_tip'    => true,
            ),
            'wave_description' => array(
               'title'       => 'Description',
               'type'        => 'textarea',
               'description' => 'This controls the description which the user sees during checkout.',
               'default'     => 'Pay with wave payment gateway.',
            ),
            
            'wave_api_key' => array(
               'title'       => 'API Key',
               'type'        => 'text'
            ),
           
         );
      
	 	}

		

		/*
 		 * Fields validation, more in Step 5
		 */
		// public function validate_fields() {

      //    if( empty( $_POST[ 'billing_first_name' ]) ) {
      //       wc_add_notice(  'First name is required!', 'error' );
      //       return false;
      //    }

      //    if( empty( $_POST[ 'billing_email' ]) ) {
      //       wc_add_notice(  'Email name is required!', 'error' );
      //       return false;
      //    }

      //    if( empty( $_POST[ 'billing_phone' ]) ) {
      //       wc_add_notice(  'Phone name is required!', 'error' );
      //       return false;
      //    }

      //    if( empty( $_POST[ 'billing_address_1' ]) ) {
      //       wc_add_notice(  'Address 1 is required!', 'error' );
      //       return false;
      //    } 
         
      //    if( empty( $_POST[ 'billing_city' ]) ) {
      //       wc_add_notice(  'City is required!', 'error' );
      //       return false;
      //    }

      //    return true;

		// }

		/*
		 * We're processing the payments here, everything about it is in Step 5
		 */
		public function process_payment( $order_id ) {

         global $woocommerce;
 
         // we need it to get any order detailes
         $order = wc_get_order( $order_id );
       
         $api_key = get_option('woocommerce_wave_settings')['wave_api_key'] ;
         $total_amount = $order->get_total();
         $currency = get_option('woocommerce_currency');
         
            // wc_add_notice(  $this->get_return_url( $order ), 'error' );
         
        //  return;
         
       
         /*
          * Your API interaction could be built with wp_remote_post()
           */
          $api_key = get_option('woocommerce_wave_settings')['wave_api_key'] ;
            
            
            $amount = 1200;
            $url = "http://google.com";
                        
            $options = array();
            
            $options['amount'] = $total_amount;
            $options['currency'] = $currency;
            $options['error_url'] = home_url();
            $options['success_url'] = $this->get_return_url( $order );
            
            $options_obj = json_encode( $options );
            
            
            
            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://api.wave.com/v1/checkout/sessions',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => $options_obj,
                CURLOPT_HTTPHEADER => array(
                   'Content-Type: application/json',
                   'Authorization: Bearer ' . $api_key
                ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);
            
            $launch_url = json_decode( $response, true )['wave_launch_url'];


        //   wc_add_notice(  $launch_url, 'error' );

         return array(
            'result' => 'success',
            'redirect' => $launch_url
         );


        //  $order->payment_complete();
        //  $order->reduce_order_stock();
   
         // some notes to customer (replace true with false to make it private)
        //  $order->add_order_note( 'Hey, your order is paid! Thank you!', true );
   
         // Empty cart
        //  $woocommerce->cart->empty_cart();

         //wave api
         // $this-> wave_payment_api();
   
         // Redirect to the thank you page
        //  return array(
        //     'result' => 'success',
        //     'redirect' => $this->get_return_url( $order )
        //  );
         

         //  if( !is_wp_error( $response ) ) {
       
         //     $body = json_decode( $response['body'], true );
       
         //     // it could be different depending on your payment processor
         //     if ( $body['response']['responseCode'] == 'APPROVED' ) {
       
         //       // we received the payment
         //       $order->payment_complete();
         //       $order->reduce_order_stock();
       
         //       // some notes to customer (replace true with false to make it private)
         //       $order->add_order_note( 'Hey, your order is paid! Thank you!', true );
       
         //       // Empty cart
         //       $woocommerce->cart->empty_cart();

         //       //wave api
         //       $this-> wave_payment_api();
       
         //       // Redirect to the thank you page
         //       return array(
         //          'result' => 'success',
         //          'redirect' => $this->get_return_url( $order )
         //       );
       
         //     } else {
         //       wc_add_notice(  'Please try again.', 'error' );
         //       return;
         //    }
       
         // } else {
         //    wc_add_notice(  'Connection error.', 'error' );
         //    return;
         // }
					
	 	}

		/*
		 * In case you need a webhook, like PayPal IPN etc
		 */
		public function wave_payment_api( $order ) {

         $api_key = $this->wave_api_key ;
         $total_amount = $order->get_total();
         $order_id =$order->get_id();;
       

         $url = "";
					
	 	}
 	}
}