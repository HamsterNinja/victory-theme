<?
//Модификация woo rest api для attribute color
function custom_api_attribute_response( $response, $item, $request ) {
	if ( empty( $response->data ) ) {
		return $response;
    }
    $id = $response->data['id'];
    $image = get_field('color', 'pa_colors_'.$id);
	$response->data['image'] = $image;
	return $response;
}
add_filter( 'woocommerce_rest_prepare_pa_colors', 'custom_api_attribute_response', 10, 3 );
function custom_api_attribute_response_lenses( $response, $item, $request ) {
	if ( empty( $response->data ) ) {
		return $response;
    }
    $id = $response->data['id'];
    $image = get_field('color', 'pa_colors_lenses_'.$id);
	$response->data['image'] = $image;
	return $response;
}
add_filter( 'woocommerce_rest_prepare_pa_colors_lenses', 'custom_api_attribute_response_lenses', 10, 3 );

//Получение продуктов
function getProducts(WP_REST_Request $request) {
    function roundArray($n){
        return round($n, 1);
    };
    if(isset ($_GET)){
        $current_search = $_GET['search'];
        $current_product_cat = $_GET['product-cat'];
        $current_items_order_by = $_GET['order_by'];
        $current_paged = $_GET['paged'];
        $include = $_GET['include'];

        $current_range_price = $_GET['range_price'];

        $current_items_order_by = $_GET['sort'];
        
        $current_colors = $_GET['colors'] ? explode( ',', $_GET['colors']) : [];   
        $current_sizes = $_GET['sizes'] ? explode( ',', $_GET['sizes']) : [];   
        $current_materials = $_GET['materials'] ? explode( ',', $_GET['materials']) : [];   
        $current_widths = $_GET['widths'] ? explode( ',', $_GET['widths']) : [];   


        $args_variation = array(
            'post_status' => 'publish',
            'post_type'     => 'product_variation',
            'fields'         => 'id=>parent',
            'posts_per_page' => -1,
        );

        $args_variation['tax_query'] =  array('relation' => 'AND');
        $args_variation['meta_query'] =  array('relation' => 'AND');

        $request_params = array(
            'key' => '_stock_status',
            'value' => 'instock',
        );
        array_push($args_variation['meta_query'], $request_params); 

        if (isset($current_sizes)  && !(empty($current_sizes))) {
            $request_params = array(
                'key' => 'attribute_razmer',
                'value' => $current_sizes,
                'compare' => 'IN',
            );
            array_push($args_variation['meta_query'], $request_params); 
        }

        if ( isset($current_range_price)  && !(empty($current_range_price)) ) {
            $args_variation['orderby'] = 'meta_value_num';
            $args_variation['order'] = $_GET['price'];
            $args_variation['meta_key'] = '_regular_price';   
            $prices = explode(",", $current_range_price);
            $price_min = intval($prices[0]);
            $price_max = intval($prices[1]);
            $prices= array(
                'key' => '_price',
                'value' => array($price_min, $price_max),
                'compare' => 'BETWEEN',
                'type' => 'NUMERIC'
            );
            array_push($args_variation['meta_query'], $prices); 
        }

        $q = new WP_Query($args_variation);
        $parent_ids = wp_list_pluck( $q->posts, 'post_parent' );

        $args = array(
            'post_status' => 'publish',
            'post_type' => 'product',
            'posts_per_page' => 21,  
            'paged' => ( $current_paged ? $current_paged : 1 ),
        );

        if (isset($current_items_order_by)  && !(empty($current_items_order_by))) {
            if( $current_items_order_by == 'ASC' || $current_items_order_by == 'DESC' ){
                $args['orderby'] = 'title'; 
                $args['order'] = $current_items_order_by;
            }
            if( $current_items_order_by == 'price_desc'){
                $args['orderby'] = 'meta_value_num';
                $args['order'] = 'DESC';
                $args['meta_key'] = '_price';
            }
            if( $current_items_order_by == 'price_asc'){
                $args['orderby'] = 'meta_value_num';
                $args['order'] = 'ASC';
                $args['meta_key'] = '_price';
            }
            if( $current_items_order_by == 'new'){
                $args['ignore_sticky_posts'] = 1;
                $args['orderby'] = array('meta_value' => 'ASC', 'date' => 'DESC');
            }
        }

        if($current_search){
            $args['s'] = $current_search;
        }

        if($parent_ids){
            $args['post__in'] = $parent_ids;
        }

        if($include){
            $args['post__in'] = explode( ',', $include);
        }

        $args['tax_query'] =  array('relation' => 'AND');
        $args['meta_query'] =  array('relation' => 'AND');

        if (isset($current_product_cat)  && !(empty($current_product_cat)) && !is_null($current_product_cat) && !($current_product_cat=="null")) {
            $request_params = array(
                'taxonomy' => 'product_cat',
                'field' => 'slug',
                'terms' => $current_product_cat
            );
            array_push($args['tax_query'], $request_params); 
        }

        $request_params = array(
            'key' => '_stock_status',
            'value' => 'instock',
        );
        array_push($args['meta_query'], $request_params); 
       
        // wp_send_json_success( $args , 200 );

        $result = new WP_Query($args);


        $products = [];

        foreach ($result->posts as $post) {
            $productInstance = new WC_Product($post->ID);
            $product = (object)[];
            $product->id = $post->ID;
            $product->slug = $post->post_name;
            $product->name = $post->post_title;
            $product->stock_status = $productInstance->get_stock_status();
            $product->permalink = get_permalink($post->ID);
            $product->regular_price = $productInstance->get_regular_price();
            $product->sale_price = $productInstance->get_sale_price();
            $product->images = [];   
            $terms_product_tag = get_the_terms( $post->ID, 'product_tag' );
            $term_array_product_tag = array();
            if ( ! empty( $terms_product_tag ) && ! is_wp_error( $terms_product_tag ) ){
                foreach ( $terms_product_tag as $term ) {
                    $term_array_product_tag[] = $term->name;
                }
            }
            $product->product_tags = $term_array_product_tag;

            $sizes_attributes = $productInstance->get_attribute( 'sizes' );
            $product->sizes_attributes = $sizes_attributes;

            $price_html = $productInstance->get_price_html();
            $product->price_html = price_array($price_html);

            global $_wp_additional_image_sizes;
            foreach ($_wp_additional_image_sizes as $size => $value) {
                $image_info = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), $size);
                $product->images[0][$size] = $image_info[0];
            }
            $product->acf = get_fields($post->ID);
            array_push($products, $product);
        }
        $response = (object)[];
        $response->posts = $products;
        $response->max_num_pages = $result->max_num_pages;
        $response->found_posts = $result->found_posts;
        $response->post_count = $result->post_count;
        $response->current_post = $result->current_post;
        
        wp_send_json_success( $response , 200 );
    }
    wp_send_json_error('Ошибка при получение значений продуктов');
}
add_action( 'rest_api_init', function () {
    register_rest_route( 'amadreh/v1/', '/get-products', array(
          'methods' => WP_REST_Server::READABLE,
          'callback' => 'getProducts',
      ) );
});

//добавление товара в избранное 
function addProductInFavorites(WP_REST_Request $request) {
    if(isset ($_POST)){
        $productID = $request['product_id'];
        $userID = $request['user_id'];
        if ($productID && $userID) {
            $meta_favorites = get_post_meta($userID, "favorites", true);
            if($meta_favorites){
                if(!in_array($productID, $meta_favorites, true)){
                    array_push($meta_favorites, $productID);
                    update_post_meta($userID, "favorites", $meta_favorites);
                }
            }
            else{
                $meta_favorites = [];
                array_push($meta_favorites, $productID);
                update_post_meta($userID, "favorites", $meta_favorites);
            }
            
            $result = $meta_favorites;
            wp_send_json_success( $result, 200 );
        }
        else{
            wp_send_json_error('Пустой запрос', 204);
        }
    }
    wp_send_json_error('Ошибка при добавление в избранное');
}

add_action( 'rest_api_init', function () {
    register_rest_route( 'amadreh/v1/', '/add-favorite', array(
          'methods' => WP_REST_Server::CREATABLE,
          'callback' => 'addProductInFavorites',
      ) );
});

//удаление товара в избранное 
function removeProductInFavorites(WP_REST_Request $request) {
    if(isset ($_POST)){
        $productID = $request['product_id'];
        $userID = $request['user_id'];
        if ($productID && $userID) {
            $meta_favorites = get_post_meta($userID, "favorites", true);
            if($meta_favorites){
                if(in_array($productID, $meta_favorites, true)){
                    if (($key = array_search($productID, $meta_favorites)) !== false) {
                        unset($meta_favorites[$key]);
                        update_post_meta($userID, "favorites", $meta_favorites);
                    }
                }
            }            
            $result = $meta_favorites;
            wp_send_json_success( $result, 200 );
        }
        else{
            wp_send_json_error('Пустой запрос', 204);
        }
    }
    wp_send_json_error('Ошибка при добавление в избранное');
}
add_action( 'rest_api_init', function () {
    register_rest_route( 'amadreh/v1/', '/remove-favorite', array(
          'methods' => WP_REST_Server::CREATABLE,
          'callback' => 'removeProductInFavorites',
      ) );
});

//Получение избранного 
function getFavorite(WP_REST_Request $request) {
    if(isset ($_GET)){
        $userID = $request['user_id'];
        if ($userID) {
            $meta_favorites = get_post_meta($userID, "favorites", true);            
            $result = $meta_favorites;
            wp_send_json_success( $result, 200 );
        }
        else{
            wp_send_json_error('Пустой запрос', 204);
        }
    }
    wp_send_json_error('Ошибка при получение избранного');
}
add_action( 'rest_api_init', function () {
    register_rest_route( 'amadreh/v1/', '/get-favorite', array(
          'methods' => WP_REST_Server::READABLE,
          'callback' => 'getFavorite',
      ) );
});


if ( ! function_exists( 'attribute_slug_to_title' ) ) {
    function attribute_slug_to_title( $attribute ,$slug ) {
        global $woocommerce;
        if ( taxonomy_exists( esc_attr( str_replace( 'attribute_', '', $attribute ) ) ) ) {
            $term = get_term_by( 'slug', $slug, esc_attr( str_replace( 'attribute_', '', $attribute ) ) );
            if ( ! is_wp_error( $term ) && $term->name )
                $value = $term->name;
        } else {
            $value = apply_filters( 'woocommerce_variation_option_name', $value );
        }
        return $value;
    }
}

//Получение вариаций 
function getVariations(WP_REST_Request $request) {
    if(isset ($_GET)){
        $post_parent = $request['post_parent'];
        if ($post_parent) {
            $args_product_variation = array(
                'post_type'     => 'product_variation',
                'post_status'   => array( 'private', 'publish' ),
                'numberposts'   => -1,
                'orderby'       => 'menu_order',
                'order'         => 'ASC',
                'post_parent'   => $post_parent
            );
            $product_variations_query = get_posts( $args_product_variation );     
            $product_variations = [];
            foreach ( $product_variations_query as $variation ) {
                $variation_ID = $variation->ID;
                $product_variation = new WC_Product_Variation( $variation_ID );
                $items = new WC_Product_Variation( $variation_ID );
                $product_variation_object = new stdClass();
                $product_variation_object->id = $variation->ID;
                $product_variation_object->image = $product_variation->get_image(); 

                $product_variation_object->regular_price = $product_variation->get_regular_price();
                $product_variation_object->sale_price = $product_variation->get_sale_price();

                $product_variation_object_value = $product_variation->get_variation_attributes();
                $product_variation_object->value = $product_variation_object_value['attribute_razmer'];
                $product_variation_object->label = $product_variation_object_value['attribute_cvet']; 
                $product_variation_object->stock_quantity = $product_variation->get_stock_quantity();

                array_push($product_variations, $product_variation_object);
                
            }

            wp_send_json_success( $product_variations, 200 );
        }
        else{
            wp_send_json_error('Пустой запрос', 204);
        }
    }
    wp_send_json_error('Ошибка при получение избранного');
}

add_action( 'rest_api_init', function () {
    register_rest_route( 'amadreh/v1/', '/get-variations', array(
          'methods' => WP_REST_Server::READABLE,
          'callback' => 'getVariations',
      ) );
});

// AJAX add to cart.    
function add_one_product() {        
    ob_start();
    $product_id = apply_filters('woocommerce_add_to_cart_product_id', absint($_POST['product_id']));
    $quantity          = empty($_POST['quantity']) ? 1 : wc_stock_amount($_POST['quantity']);
    $passed_validation = apply_filters( 'woocommerce_add_to_cart_validation', true, $product_id, $quantity );
    $product_status    = get_post_status( $product_id );
    
    if ( $passed_validation && WC()->cart->add_to_cart( $product_id, $quantity ) && 'publish' === $product_status ) {
        do_action( 'woocommerce_ajax_added_to_cart', $product_id );
        wc_add_to_cart_message( $product_id );
        // $fragments = WC_AJAX::get_refreshed_fragments();
        wp_send_json_success($product_id);
    } else {
        $data = array(
            'error'       => true,
            'product_url' => apply_filters( 'woocommerce_cart_redirect_after_error', get_permalink( $product_id ), $product_id )
        );
        wp_send_json_error( $data );
    }
    die();
}
add_action('wp_ajax_add_one_product', 'add_one_product');
add_action('wp_ajax_nopriv_add_one_product', 'add_one_product');

//установка количества продукта в корзине
function remove_item_from_cart() {
    $cart = WC()->instance()->cart;
    $id = $_POST['product_id'];
    $product_quantity = $_POST['product_quantity'];
    $cart_id = $cart->generate_cart_id($id);
    $cart_item_id = $cart->find_product_in_cart($cart_id);
    if($cart_item_id){
       $cart->set_quantity($cart_item_id, $product_quantity);
       wp_send_json_success();
    } 
    wp_send_json_error();
}
add_action('wp_ajax_remove_item_from_cart', 'remove_item_from_cart');
add_action('wp_ajax_nopriv_remove_item_from_cart', 'remove_item_from_cart');

//установка количества продукта в корзине по cart id
function set_item_from_cart_by_cart_id() {
    $cart = WC()->instance()->cart;
    $cart_id = $_POST['cart_id'];
    $product_quantity = $_POST['product_quantity'];
    $cart_item_id = $cart->find_product_in_cart($cart_id);
    if($cart_item_id){
       $cart->set_quantity($cart_item_id, $product_quantity);
       $data = array(
            'subtotal'   => $cart->subtotal,
        );
       wp_send_json_success($data);
    } 
    wp_send_json_error();
}
add_action('wp_ajax_set_item_from_cart_by_cart_id', 'set_item_from_cart_by_cart_id');
add_action('wp_ajax_nopriv_set_item_from_cart_by_cart_id', 'set_item_from_cart_by_cart_id');

//Модификация woo rest api для product
function custom_api_product_response($data, $object){
    global $_wp_additional_image_sizes;
    unset($data->data['date_created']);
    // unset($data->data['meta_data']);
    unset($data->data['description']);
    // unset($data->data['price_html']);
    // unset($data->data['attributes']);
    unset($data->data['date_created_gmt']);
    unset($data->data['date_modified']);
    unset($data->data['date_modified_gmt']);
    unset($data->data['type']);
    unset($data->data['status']);
    unset($data->data['featured']);
    unset($data->data['catalog_visibility']);
    unset($data->data['short_description']);
    unset($data->data['sku']);
    unset($data->data['date_on_sale_from']);
    unset($data->data['date_on_sale_from_gmt']);
    unset($data->data['date_on_sale_to']);
    unset($data->data['date_on_sale_to_gmt']);
    unset($data->data['on_sale']);
    unset($data->data['purchasable']);
    unset($data->data['total_sales']);
    unset($data->data['virtual']);
    unset($data->data['downloadable']);
    unset($data->data['downloads']);
    unset($data->data['download_limit']);
    unset($data->data['download_expiry']);
    unset($data->data['external_url']);
    unset($data->data['button_text']);
    unset($data->data['tax_status']);
    unset($data->data['tax_class']);
    unset($data->data['manage_stock']);
    unset($data->data['stock_quantity']);
    unset($data->data['stock_status']);
    unset($data->data['backorders']);
    unset($data->data['backorders_allowed']);
    unset($data->data['backordered']);
    unset($data->data['sold_individually']);
    unset($data->data['weight']);
    unset($data->data['dimensions']);
    unset($data->data['shipping_required']);
    unset($data->data['shipping_taxable']);
    unset($data->data['shipping_class']);
    unset($data->data['shipping_class_id']);
    unset($data->data['reviews_allowed']);
    unset($data->data['average_rating']);
    unset($data->data['rating_count']);
    unset($data->data['related_ids']);
    unset($data->data['upsell_ids']);
    unset($data->data['cross_sell_ids']);
    unset($data->data['parent_id']);
    unset($data->data['purchase_note']);
    // unset($data->data['categories']);
    foreach ($data->data['images'] as $key => $image) {
        $image_urls = [];
        foreach ($_wp_additional_image_sizes as $size => $value) {
            $image_info = wp_get_attachment_image_src($image['id'], $size);
            $data->data['images'][$key][$size] = $image_info[0];
        }
    }
    return $data;
}
add_filter( 'woocommerce_rest_prepare_product_object', 'custom_api_product_response', 10, 2 );


/**
 * Ajax newsletter
 * 
 * @url http://www.thenewsletterplugin.com/forums/topic/ajax-subscription
 */
function realhero_ajax_subscribe() {
    check_ajax_referer( 'noncy_nonce', 'nonce' );
    $data = urldecode( $_POST['data'] );
    if ( !empty( $data ) ) :
        $data_array = explode( "&", $data );
        $fields = [];
        foreach ( $data_array as $array ) :
            $array = explode( "=", $array );
            $fields[ $array[0] ] = $array[1];
        endforeach;
    endif;
    if ( !empty( $fields ) ) :
        global $wpdb;
		
		// check if already exists
		
		/** @var int $count **/
		$count = $wpdb->get_var( $wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}newsletter WHERE email = %s", $fields['ne'] ) );
		
		if( $count > 0 ) {
	        $output = array(
	            'status'    => 'error',
	            'msg'       => __( 'Already in a database.', THEME_NAME )
	        );
        } elseif( !defined( 'NEWSLETTER_VERSION' ) ) {
            $output = array(
	            'status'    => 'error',
	            'msg'       => __( 'Please install & activate newsletter plugin.', THEME_NAME )
	        );           
        } else {
            /**
             * Generate token
             */
            
            /** @var string $token */
            $token =  wp_generate_password( rand( 10, 50 ), false );
	        $wpdb->insert( $wpdb->prefix . 'newsletter', array(
	                'email'         => $fields['ne'],
	                'status'        => $fields['na'],
                    'http_referer'  => $fields['nhr'],
                    'token'         => $token,
	            )
            );
            $opts = get_option('newsletter');
            $opt_in = (int) $opts['noconfirmation'];
            // This means that double opt in is enabled
            // so we need to send activation e-mail
            if ($opt_in == 0) {
                $newsletter = Newsletter::instance();
                $user = NewsletterUsers::instance()->get_user( $wpdb->insert_id );
                NewsletterSubscription::instance()->mail($user->email, $newsletter->replace($opts['confirmation_subject'], $user), $newsletter->replace($opts['confirmation_message'], $user));
            }
	        $output = array(
	            'status'    => 'success',
	            'msg'       => __( 'Thank you!', THEME_NAME )
	        );	
		}
		
    else :
        $output = array(
            'status'    => 'error',
            'msg'       => __( 'An Error occurred. Please try again later.', THEME_NAME  )
        );
    endif;
	
    wp_send_json( $output );
}
add_action( 'wp_ajax_realhero_subscribe', 'realhero_ajax_subscribe' );
add_action( 'wp_ajax_nopriv_realhero_subscribe', 'realhero_ajax_subscribe' );