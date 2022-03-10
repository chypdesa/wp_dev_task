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
      curl_close($curl);                                                    /* Close curl session */
      // End API Request

      if ($err) {                                                            /* If request fails */
        echo "Sorry! Machine went boom! This happened:" . $err;
      } 
      else {                                                                 /* If request succeeds */
        $responseObj = json_decode($response);
        $casinoArr = $responseObj->toplists->{'575'};                        /* Define array to display - 575 TODO: Make this changeable through backoffice */
    
        /* Render List Start Wrapper*/
        echo ''.
        '<div class="body-wrapper">'.
            '<div class="container px-4 py-2">'.
                '<div class="row list-header py-3">'.
                    '<div class="col-3 text-center">'.
                        '<h3>Casino</h3>'.
                    '</div>'.
                    '<div class="col-3 text-center">'.
                        '<h3>Bonus</h3>'.
                    '</div>'.
                    '<div class="col-3 text-center">'.
                        '<h3>Features</h3>'.
                    '</div>'.
                    '<div class="col-3 text-center">'.
                        '<h3>Play</h3>'.
                    '</div>'.
                '</div>';
            
                /* Render dynamic list row for each casino in array */
                foreach ($casinoArr as $casino) {
                    echo ''.
                    '<div class="row list-cell align-items-center">'.
                        '<div class="col-3 list-cell__casino text-center py-4">'.
                            '<img class="img-fluid mb-3" src="'.$casino->logo.'">'.
                            '<div class="review-btn">'.
                                '<a href="/'.$casino->brand_id.'">Review</a>'.
                            '</div>'.
                        '</div>'.
                        '<div class="col-3 list-cell__bonus text-center py-4 px-5">'.
                            '<div class="review-stars">'; 

                        /* Fill stars according to rating */
                        $rating = $casino->info->rating;
                        for ($i=1; $i <= 5 ; $i++) { 
                            if($i <= $casino->info->rating){
                                echo '<i class="fa-solid fa-star"></i>';
                            }
                            else{
                                echo '<i class="fa-regular fa-star"></i>';
                            }
                        }

                        echo '</div>'.
                            '<p>'.$casino->info->bonus.'</p>'.
                        '</div>'.
                        '<div class="col-3 list-cell__features text-center py-4">'.
                            '<ul>';

                        /* Render features from array*/        
                        foreach ($casino->info->features as $feat) {
                            echo '<li>'.$feat.'</li>';
                        }

                        echo '</ul>'.
                        '</div>'.
                        '<div class="col-3 list-cell__play text-center py-4">'.
                            '<a href="'.$casino->play_url.'">'.
                                '<button>PLAY NOW</button>'.
                            '</a>'.
                        '<p class="fs-12 mt-3 notice">'.$casino->terms_and_conditions.'</p>'.
                        '</div>'.
                    '</div>';
                } 

        /* Render List End Wrapper */     
        echo '</div>'.
        '</div>';
    
    }
      echo $args['after_widget'];
    } 
  
  } 
?>