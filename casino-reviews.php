<?php 
/**
* Plugin Name: WP Casino Reviews
* Plugin URI: https://paulosa.pt
* Description: Probably the best casino reviews plugin in the market, according to my mother.
* Version: 1.1.0
* Author: Paulo Sa
* Author URI: https://paulosa.pt
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
      curl_setopt($curl, CURLOPT_URL, $settings['apiurl']);                 /* API URL - Set on plugin settings */
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
        $casinoArr = $responseObj->toplists->{$settings['listId']};          /* Select array by List ID - Set on plugin settings */
    
        /* Order ASC $casinoArr by 'position' key */
        usort($casinoArr, function($a, $b){
            if ($a->position == $b->position)
                return (0);
            return (($a->position < $b->position) ? -1 : 1);
        });

        /* Build dynamic list row for each casino in array */
        $rowsPrnt = "";

        foreach ($casinoArr as $casino) {   

            /* Calculate rating stars */                
            $rating = $casino->info->rating;
            $ratingPrnt = "";
            for ($i=1; $i <= 5 ; $i++) { 
                if($i <= $casino->info->rating){ $ratingPrnt.= '<i class="fa-solid fa-star review-stars__star"></i>'; }
                else{ $ratingPrnt.= '<i class="fa-regular fa-star review-stars__star"></i>'; }
            }

            /* Populate feature list */ 
            $featuresPrnt = "";
            foreach ($casino->info->features as $feat) {
                $featuresPrnt .= '<li>'.$feat.'</li>';
            }

            $rowsPrnt .= '<div class="row list-cell align-items-center py-3">'.
                '<div class="col-12 col-md-6 col-lg-3 list-cell__casino text-center">'.
                    '<img class="img-fluid mb-3" src="'.$casino->logo.'">'.
                    '<div class="review-btn">'.
                        '<a href="/'.$casino->brand_id.'">Review</a>'.
                    '</div>'.
                '</div>'.
                '<div class="col-12 col-md-6 col-lg-3 list-cell__bonus text-center px-md-3 px-xl-5">'.
                    '<div class="review-stars">'.
                        $ratingPrnt.       
                    '</div>'.
                    '<p class="bonus-text mb-0">'.$casino->info->bonus.'</p>'.
                '</div>'.
                '<div class="col-12 col-md-6 col-lg-3 list-cell__features text-center ">'.
                    '<ul class="my-3 my-lg-0 pl-0 pl-sm-4 pl-md-0">'.
                        $featuresPrnt.
                    '</ul>'.
                '</div>'.
                '<div class="col-12 col-md-6 col-lg-3 list-cell__play text-center">'.
                    '<a href="'.$casino->play_url.'">'.
                        '<button>PLAY NOW</button>'.
                    '</a>'.
                '<p class="fs-12 mb-4 mb-md-0 mt-3 notice">'.$casino->terms_and_conditions.'</p>'.
                '</div>'.
            '</div>';
        } 

        /* Render */
        echo '<div class="casino-reviews">'.
            '<div class="casino-reviews-container px-4 py-2">'.
                '<div class="row list-header py-3">'.
                    '<div class="col-12 col-lg-3 text-center">'.
                        '<h3>Casino</h3>'.
                    '</div>'.
                    '<div class="col-12 col-lg-3 text-center d-none d-lg-block">'.
                        '<h3>Bonus</h3>'.
                    '</div>'.
                    '<div class="col-12 col-lg-3 text-center d-none d-lg-block">'.
                        '<h3>Features</h3>'.
                    '</div>'.
                    '<div class="col-12 col-lg-3 text-center d-none d-lg-block">'.
                        '<h3>Play</h3>'.
                    '</div>'.
                '</div>'.
                $rowsPrnt.    /* Inject list rows HTML */ 
            '</div>'.
        '</div>';
    
    }
      echo $args['after_widget'];
    } 
  
    /* Backoffice settings */
    public function form( $settings ) {
        if ( isset( $settings['apiurl'] ) ) {
            $api_url = $settings['apiurl'];
        } 
        else {
            $api_url = __( 'https://api.mocki.io/v2/060d39fa', 'wpb_widget_domain' );
        }
        if( isset( $settings[ 'listId' ] ) ) { 
            $toplist_id = $settings[ 'listId' ]; 
        } 
        else { 
            $toplist_id = __( '575', 'wpb_widget_domain' ); 
        }
        ?>
        <p>
            <label for="<?php echo $this->get_field_id( 'apiurl' ); ?>"><?php _e( 'API:' ); ?></label>
            <input class="widefat" id=" <?php echo $this->get_field_id( 'apiurl' ); ?>" name="<?php echo $this->get_field_name( 'apiurl' ); ?>" type="text" value="<?php echo esc_attr( $api_url ); ?>" />
        </p>
        <p>
        <label for="<?php echo $this->get_field_id( 'listId' ); ?>"><?php _e( 'Toplist ID:' ); ?></label>
            <input class="widefat" id=" <?php echo $this->get_field_id( 'listId' ); ?>" name="<?php echo $this->get_field_name( 'listId' ); ?>" type="text" value="<?php echo esc_attr( $toplist_id ); ?>" />
        </p>
        <?php
    }

    /* Update settings */
    public function update( $new_settings, $old_settings ) {
        $settings = array();
        $settings['apiurl'] = (!empty($new_settings['apiurl'])) ? strip_tags($new_settings['apiurl']) : '';
        $settings['listId'] = (!empty($new_settings['listId'])) ? strip_tags($new_settings['listId']) : '';
        return $settings;
    }

  } 
?>