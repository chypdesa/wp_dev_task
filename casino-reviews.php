<?php 
/**
* Plugin Name: WP Casino Reviews
* Plugin URI: http://paulosa.pt
* Description: Probably the best casino reviews plugin in the market, according to my mother.
* Version: 1.0.0
* Author: Paulo Sa
* Author URI: http://paulosa.pt
* License: GPL2
*/

function casino_reviews_widget() {
    register_widget( 'casino_reviews' );
    
    /* Custom CSS */
    wp_register_style('casino_reviews', plugins_url('/includes/css/style.css',__FILE__ ));
    wp_enqueue_style('casino_reviews');

    /* Bootstrap 5.0.2 */
    wp_register_style('cr_bootstrap', 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.0.2/css/bootstrap.min.css');
    wp_enqueue_style('cr_bootstrap');

    /* Font Awesome 6.0.0 - Icon Library */
    wp_register_style('cr_fontawesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css');
    wp_enqueue_style('cr_fontawesome');
}
add_action( 'widgets_init', 'casino_reviews_widget' );

class casino_reviews extends WP_Widget {

    function __construct() {
      parent::__construct(
        'casino_reviews',
        __('WP Casino Reviews', 'casino_reviews_domain'),
        array( 'description' => __( 'Probably the best casino reviews plugin in the market, according to my mother.', 'casino_reviews_domain' ), )
      );
    }
  
    public function widget( $args, $settings ) {
      echo $args['before_widget'];
  
      // Start API Request
      $curl = curl_init();
      curl_setopt($curl, CURLOPT_URL, 'https://api.mocki.io/v2/060d39fa');  /* API URL */
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      $response = curl_exec($curl);                                         
      $err = curl_error($curl);                                             /* Error feedback */
      curl_close($curl); /* Close curl session */
      // End API Request

      if ($err) {                                                            /* If request fails */
        echo "Sorry! Machine went boom! This happened:" . $err;
      } 
      else {                                                                 /* If request succeeds */
        $responseObj = json_decode($response);
        var_dump($responseObj);
      }
      echo $args['after_widget'];
    } 
  
  } 
?>