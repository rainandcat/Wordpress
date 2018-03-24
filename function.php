<?php

/*Wordpress的Child theme功能*/
function my_theme_enqueue_styles() {
    $parent_style = 'parent-style'; 
    wp_enqueue_style( $parent_style, get_template_directory_uri() . '/style.css' );
    wp_enqueue_style( 'child-style',
        get_stylesheet_directory_uri() . '/style.css',
        array( $parent_style ),
        wp_get_theme()->get('Version')
    );
}
add_action( 'wp_enqueue_scripts', 'my_theme_enqueue_styles' );

/*修正Wordpress滿額免運，還會出現其他選項情況*/
/*add_filter-->Hook a function or method to a specific filter action.*/
function hide_shipping_free( $rates ) {
	$free = array();
	foreach ( $rates as $rate_id => $rate ) {
		if ( 'free_shipping' === $rate->method_id ) {
			$free[ $rate_id ] = $rate;
			break;
		}
	}
/* PHP if else 縮寫-->如果$free不為空，回傳$free，要不然回傳$rates*/
	return ! empty( $free ) ? $free : $rates;
}
add_filter( 'woocommerce_package_rates', 'hide_shipping_free', 100 );

/*WooCommerce 購買指定商品數量免運費或減少運費，只會扣除指定商品運費，其他商品不受影響*/
function add_custom_shipping_fee($rates) {
   global $woocommerce;
   if( WC()->cart->shipping_total>0){ //If have not free shipping,and then executing.
		$minimum_number_of_item = 2; //Give here minumum number of items to allow subtract shipping
		$shop_country = 'US'; //Give here the country code where the store located

    //If have not free shipping,and then executing.
		$item_shpping_fee=0;

		//輸入指定商品ID、商品數量與要扣除運費
		$cart_product_free = array(
			"3320" => array(
						"quantity"=>2,
						"shpping_fee"=>100),
			"3321" => array(
						"quantity"=>3,
						"shpping_fee"=>100),
			"3322" => array(
						"quantity"=>1,
						"shpping_fee"=>50),					
		);	
				
		$customer_country = $woocommerce->customer->get_shipping_country();
		
		foreach( WC()->cart->get_cart() as $cart_item ){ //run cart product
			$product_id = $cart_item['product_id'];
			foreach( $cart_product_free as $product_id_free => $product_num_free){ //check $cart_product_free data
				
				if($product_id == $product_id_free){
					
						if($cart_item['quantity'] >= $product_num_free["quantity"])//Check quantity.
						{
							$item_shpping_fee += $product_num_free["shpping_fee"]*$cart_item['quantity'];
						}
					
				}
			}
		}
	
	
		if( $customer_country ==  $shop_country && $item_shpping_fee>0){
      //可在下面輸入清單顯示文字
			$woocommerce->cart->add_fee(__('指定商品扣除運費','woocommerce'), -$item_shpping_fee);
		}
	}
	
}
add_action('woocommerce_cart_calculate_fees','add_custom_shipping_fee',100);

/*新增或刪除script*/
/*Change script wp_enqueue_script-->add script  wp_deregister_script-->remove script*/
wp_enqueue_script( 'edit_script', get_stylesheet_directory_uri(). '/edit_script.js', array ( 'jquery' ), 1.1, true);

function remove_scripts_styles_footer() {
	 wp_deregister_script('script');
}
add_action('wp_footer', 'remove_scripts_styles_footer');

/*Wordpress 的 Post 顯示排列,依照dates由最新到舊(DESC)，最舊到新使用ASC*/
function posts_orderby($query){
	if( !empty($query) && $query->is_category ){
		$query->set( 'order', 'DESC');
		//Set the orderby
    $query->set( 'orderby', 'dates' );
	}
}
add_action('pre_get_posts', 'posts_orderby');

/*Wordpress-對於特定文字做取代*/
function bbloomer_translate _string( $translated ) {
  $translated = str_ireplace( '填入網站想更改文字1', '填入更改後的文字1', $translated );
  $translated = str_ireplace( '填入網站想更改文字2', '填入更改後的文字2', $translated );
  return $translated;
}
add_filter( 'gettext', 'bbloomer_translate _string', 999 );


?>
