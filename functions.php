<?php


// after theme setup
function organi_sb_setup(){
    load_theme_textdomain('organi_sb', get_template_part('/languages'));
    
    // common support
    add_theme_support('title-tag');
    add_theme_support( 'woocommerce' );
    // navigations
    register_nav_menus(array(
        'main-menu' => __('Main Menu', 'organi_sb'),
        'cetagory-menu' => __('Cetagory Menu', 'organi_sb)')
    ));
}

add_action( 'after_setup_theme', 'organi_sb_setup');

// Load assests
function organi_sb_assets(){
    // style
    wp_enqueue_style('bootstrap',  get_template_directory_uri() . '/css/bootstrap.min.css', array(), '1.0.0', 'all');
    wp_enqueue_style('font-awesome.min',  get_template_directory_uri() . '/css/font-awesome.min.css', array(), '1.0.0', 'all');
    wp_enqueue_style('elegant-icons',  get_template_directory_uri() . '/css/elegant-icons.css', array(), '1.0.0', 'all');
    wp_enqueue_style('nice-select',  get_template_directory_uri() . '/css/nice-select.css', array(), '1.0.0', 'all');
    wp_enqueue_style('jquery-ui.min',  get_template_directory_uri() . '/css/jquery-ui.min.css', array(), '1.0.0', 'all');
    wp_enqueue_style('owl-carousel',  get_template_directory_uri() . '/css/owl.carousel.min.css', array(), '1.0.0', 'all');
    wp_enqueue_style('slicknav',  get_template_directory_uri() . '/css/slicknav.min.css', array(), '1.0.0', 'all');
   // wp_enqueue_style('style',  get_template_directory_uri() . '/css', array(), '1.0.0', 'all'); // demo
    wp_enqueue_style('primary-style',  get_template_directory_uri() . '/css/style.css', array(), '1.0.0', 'all');
    wp_enqueue_style( 'wp-style', get_stylesheet_uri() );


    // javaScripts files
    wp_enqueue_script( 'bootstrap-js', get_template_directory_uri() . '/js/bootstrap.min.js', array('jquery'), '1.0.0', true );
    wp_enqueue_script( 'nice-select', get_template_directory_uri() . '/js/jquery.nice-select.min.js', array('jquery'), '1.0.0', true );
    wp_enqueue_script( 'jquery-ui', get_template_directory_uri() . '/js/jquery-ui.min.js', array('jquery'), '1.0.0', true );
    wp_enqueue_script( 'slicknav', get_template_directory_uri() . '/js/jquery.slicknav.js', array('jquery'), '1.0.0', true );
    wp_enqueue_script( 'mixitup', get_template_directory_uri() . '/js/mixitup.min.js', array('jquery'), '1.0.0', true );
    wp_enqueue_script( 'owl', get_template_directory_uri() . '/js/owl.carousel.min.js', array('jquery'), '1.0.0', true );
    wp_enqueue_script( 'scrip', get_template_directory_uri() . '/js/main.js', array('jquery'), '1.0.0', true );
    //wp_enqueue_script( 'scrip', get_template_directory_uri() . '/js', array('jquery'), '1.0.0', true ); // demo


}
add_action( 'wp_enqueue_scripts', 'organi_sb_assets' );


/**
 * 
 *  Woo commerce custom functions
 * 
 */

 if(!function_exists('loop_columns')){
     function loop_columns(){
         return 3;
     }
 }
add_filter('loop_shop_columns','loop_columns',999);

// Display the Woocommerce Discount Percentage on the Sale Badge for variable products and single products
add_filter( 'woocommerce_sale_flash', 'display_percentage_on_sale_badge', 20, 3 );
function display_percentage_on_sale_badge( $html, $post, $product ) {

  if( $product->is_type('variable')){
      $percentages = array();

      // This will get all the variation prices and loop throughout them
      $prices = $product->get_variation_prices();

      foreach( $prices['price'] as $key => $price ){
          // Only on sale variations
          if( $prices['regular_price'][$key] !== $price ){
              // Calculate and set in the array the percentage for each variation on sale
              $percentages[] = round( 100 - ( floatval($prices['sale_price'][$key]) / floatval($prices['regular_price'][$key]) * 100 ) );
          }
      }
      // Displays maximum discount value
      $percentage = max($percentages) . '%';

  } elseif( $product->is_type('grouped') ){
      $percentages = array();

       // This will get all the variation prices and loop throughout them
      $children_ids = $product->get_children();

      foreach( $children_ids as $child_id ){
          $child_product = wc_get_product($child_id);

          $regular_price = (float) $child_product->get_regular_price();
          $sale_price    = (float) $child_product->get_sale_price();

          if ( $sale_price != 0 || ! empty($sale_price) ) {
              // Calculate and set in the array the percentage for each child on sale
              $percentages[] = round(100 - ($sale_price / $regular_price * 100));
          }
      }
     // Displays maximum discount value
      $percentage = max($percentages) . '%';

  } else {
      $regular_price = (float) $product->get_regular_price();
      $sale_price    = (float) $product->get_sale_price();

      if ( $sale_price != 0 || ! empty($sale_price) ) {
          $percentage    = round(100 - ($sale_price / $regular_price * 100)) . '%';
      } else {
          return $html;
      }
  }
  return '<div class="product__discount__percent">' . esc_html__( '-', 'woocommerce' ) . ' '. $percentage . '</div>'; // If needed then change or remove "up to -" text
}

//  add to card plus minus 
add_action( 'woocommerce_after_add_to_cart_quantity', 'ts_quantity_plus_sign' );
 
function ts_quantity_plus_sign() {
   echo '<button type="button" class="plus" >+</button>';
}
 
add_action( 'woocommerce_before_add_to_cart_quantity', 'ts_quantity_minus_sign' );

function ts_quantity_minus_sign() {
   echo '<button type="button" class="minus" >-</button>';
}
 
add_action( 'wp_footer', 'ts_quantity_plus_minus' );
 
function ts_quantity_plus_minus() {
   // To run this on the single product page
   if ( ! is_product() ) return;
   ?>
   <script type="text/javascript">
          
      jQuery(document).ready(function($){   
          
            $('form.cart').on( 'click', 'button.plus, button.minus', function() {
 
            // Get current quantity values
            var qty = $( this ).closest( 'form.cart' ).find( '.qty' );
            var val   = parseFloat(qty.val());
            var max = parseFloat(qty.attr( 'max' ));
            var min = parseFloat(qty.attr( 'min' ));
            var step = parseFloat(qty.attr( 'step' ));
 
            // Change the value if plus or minus
            if ( $( this ).is( '.plus' ) ) {
               if ( max && ( max <= val ) ) {
                  qty.val( max );
               } 
            else {
               qty.val( val + step );
                 }
            } 
            else {
               if ( min && ( min >= val ) ) {
                  qty.val( min );
               } 
               else if ( val > 1 ) {
                  qty.val( val - step );
               }
            }
             
         });
          
      });
          
   </script>
   <?php
}
add_action('template_redirect', 'remove_shop_breadcrumbs' );
function remove_shop_breadcrumbs(){
    remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20, 0);
}



  
add_action( 'woocommerce_before_add_to_cart_quantity', 'silva_display_quantity_plus' );
  
function silva_display_quantity_plus() {
   echo '<button type="button" class="plus" >+</button>';
}
  
add_action( 'woocommerce_after_add_to_cart_quantity', 'silva_display_quantity_minus' );
  
function silva_display_quantity_minus() {
   echo '<button type="button" class="minus" >-</button>';
}
?>




