<?php
if ( class_exists( 'Timber' ) ){
    Timber::$cache = false;
}
//Скрытие версии wp
add_filter('the_generator', '__return_empty_string');

//TODO: Отключение авторизации rest. Удалить на production
function wc_authenticate_alter(){   
    return new WP_User( 1 );
}
add_filter( 'woocommerce_api_check_authentication', 'wc_authenticate_alter', 1 );
add_filter( 'woocommerce_rest_check_permissions', 'my_woocommerce_rest_check_permissions', 90, 4 );
function my_woocommerce_rest_check_permissions( $permission, $context, $object_id, $post_type  ){
  return true;
}

include_once(get_template_directory() .'/include/Timber/Integrations/WooCommerce/WooCommerce.php');
include_once(get_template_directory() .'/include/Timber/Integrations/WooCommerce/ProductsIterator.php');
include_once(get_template_directory() .'/include/Timber/Integrations/WooCommerce/Product.php');

add_action( 'after_setup_theme', function() {
    add_theme_support( 'woocommerce' );
} );

if ( class_exists( 'WooCommerce' ) ) {
    Timber\Integrations\WooCommerce\WooCommerce::init();
}

add_image_size( 'blog_image', 438, 257, true ); 
add_image_size( 'catalog_image', 360, 370, true ); 
add_image_size( 'catalog_image_x2', 720, 405, true ); 

remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
remove_action( 'admin_print_styles', 'print_emoji_styles' ); 
remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
remove_filter( 'comment_text_rss', 'wp_staticize_emoji' ); 
remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
add_filter( 'tiny_mce_plugins', 'disable_wp_emojis_in_tinymce' );
function disable_wp_emojis_in_tinymce( $plugins ) {
    if ( is_array( $plugins ) ) {
        return array_diff( $plugins, array( 'wpemoji' ) );
    } else {
        return array();
    }
}
function true_remove_default_widget() {
	unregister_widget('WP_Widget_Archives'); 
	unregister_widget('WP_Widget_Calendar');
	unregister_widget('WP_Widget_Categories'); 
	unregister_widget('WP_Widget_Meta'); 
	unregister_widget('WP_Widget_Pages');
	unregister_widget('WP_Widget_Recent_Comments'); 
	unregister_widget('WP_Widget_Recent_Posts'); 
	unregister_widget('WP_Widget_RSS'); 
	unregister_widget('WP_Widget_Search'); 
	unregister_widget('WP_Widget_Tag_Cloud'); 
	unregister_widget('WP_Widget_Text'); 
	unregister_widget('WP_Nav_Menu_Widget'); 
}
 
add_filter( 'woocommerce_enqueue_styles', '__return_empty_array' );

add_action( 'widgets_init', 'true_remove_default_widget', 20 );
add_theme_support('post-thumbnails');

register_nav_menus(array(
    'menu_header' => 'Верхнее меню',
    'menu_about_company' => 'О КОМПАНИИ',
    'menu_buyers' => 'Покупателям',
    'menu_account' => 'МОЙ КАБИНЕТs',
));

function add_async_forscript($url)
{
    if (strpos($url, '#asyncload')===false)
        return $url;
    else if (is_admin())
        return str_replace('#asyncload', '', $url);
    else
        return str_replace('#asyncload', '', $url)."' defer='defer"; 
}

add_filter('clean_url', 'add_async_forscript', 11, 1);
function time_enqueuer($my_handle, $relpath, $type='script', $async='false', $media="all",  $my_deps=array()) {
    if($async == 'true'){
        $uri = get_theme_file_uri($relpath.'#asyncload');
    }
    else{
        $uri = get_theme_file_uri($relpath);
    }
    $vsn = filemtime(get_theme_file_path($relpath));
    if($type == 'script') wp_enqueue_script($my_handle, $uri, $my_deps, $vsn);
    else if($type == 'style') wp_enqueue_style($my_handle, $uri, $my_deps, $vsn, $media);      
}

//Самая низкая цена в категории
function get_min_price_per_product_cat( $term_id ) {
    global $wpdb;
    $sql = "
    SELECT MIN( meta_value+0 ) as minprice
    FROM {$wpdb->posts} 
    INNER JOIN {$wpdb->term_relationships} ON ({$wpdb->posts}.ID = {$wpdb->term_relationships}.object_id)
    INNER JOIN {$wpdb->postmeta} ON ({$wpdb->posts}.ID = {$wpdb->postmeta}.post_id) 
    WHERE 
    ( {$wpdb->term_relationships}.term_taxonomy_id IN (%d) ) 
    AND {$wpdb->posts}.post_type = 'product' 
    AND {$wpdb->posts}.post_status = 'publish' 
    AND {$wpdb->postmeta}.meta_key = '_price'
    "; 
    return $wpdb->get_var( $wpdb->prepare( $sql, $term_id ) );
};

//Самая высокая цена в категории
function get_max_price_per_product_cat( $term_id ) {
    global $wpdb;
    $sql = "
    SELECT MAX( meta_value+0 ) as maxprice
    FROM {$wpdb->posts} 
    INNER JOIN {$wpdb->term_relationships} ON ({$wpdb->posts}.ID = {$wpdb->term_relationships}.object_id)
    INNER JOIN {$wpdb->postmeta} ON ({$wpdb->posts}.ID = {$wpdb->postmeta}.post_id) 
    WHERE 
    ( {$wpdb->term_relationships}.term_taxonomy_id IN (%d) ) 
    AND {$wpdb->posts}.post_type = 'product' 
    AND {$wpdb->posts}.post_status = 'publish' 
    AND {$wpdb->postmeta}.meta_key = '_price'
    "; 
    return $wpdb->get_var( $wpdb->prepare( $sql, $term_id ) );
};

// Получение размеров всех товаров
function getAllSizes() {
    $args_product_variation = array(
        'post_type'     => 'product_variation',
        'post_status'   => array( 'private', 'publish' ),
        'numberposts'   => -1,
        'orderby'       => 'menu_order',
        'order'         => 'ASC',
    );
    $product_variations_query = get_posts( $args_product_variation );    
    $sizes = [];

    foreach ( $product_variations_query as $variation ) {
        $product_variation = new WC_Product_Variation( $variation->ID );
        $product_variation_object_value = $product_variation->get_variation_attributes();
        $sizes[] = $product_variation_object_value['attribute_razmer'];
    }

    $sizes = array_unique($sizes);
    $sizes = array_filter($sizes);
    $sizes = array_values($sizes);
    return $sizes;
}

add_action('wp_footer', 'add_scripts');
function add_scripts() {
    time_enqueuer('jquerylatest', '/assets/js/vendors/jquery-3.2.0.min.js', 'script', true);
    time_enqueuer('slick', '/assets/js/vendors/slick.js', 'script', true);
    time_enqueuer('onepagescrolljs', '/assets/js/vendors/onepagescroll.js', 'script', true);
    time_enqueuer('app-main', '/assets/js/main.bundle.js', 'script', true);
    
    $queried_object = get_queried_object();
    if ($queried_object) {
        $term_id = $queried_object->term_id;
        $term = get_term( $term_id, 'product_cat' );
        $category_slug = $term->slug;
        $current_brand_term = get_term( $term_id, 'brand_product' );
        $current_brand = $current_brand_term->slug;
    }
    if($_GET && $category_slug == null){
        if ($_GET['product-cat']) {
            $category_slug = $_GET['product-cat'];
        }
    }

    if (is_product()) {
        $post_params = Timber::get_post();
        $product_params = wc_get_product( $post_params->ID );
        $regular_price = $product_params->get_regular_price();
        $sale_price = $product_params->get_sale_price();
    }
    else{
        $regular_price = 0;
        $sale_price = 0;
    }

    $user = get_userdata(get_current_user_id());
    if ($user) {
        $user_url = $user->get('user_url');
    }

    $min_price_per_product_cat = get_min_price_per_product_cat($term_id);
    $max_price_per_product_cat = round(get_max_price_per_product_cat($term_id), -3);

    //TODO: починить блядоразмеры
    $sizes_v1 = TimberHelper::transient( 'sizes_v1', function(){
        return getAllSizes();
    }, 2600 );

    wp_localize_script( 'app-main', 'SITEDATA', array(
        'url' => get_site_url(),
        'themepath' => get_template_directory_uri(),
        'ajax_url' => admin_url('admin-ajax.php'),
        'product_id' => get_the_ID(),
        'is_home' => is_home() ? 'true' : 'false',
        'is_product' => is_product() ? 'true' : 'false',
        'is_filter' => is_page('filter') ? 'true' : 'false',
        'is_cart' => is_cart() ? 'true' : 'false',
        'is_search' => is_search() ? 'true' : 'false',
        'search_query' => get_search_query() ? get_search_query() : '',
        'category_slug' => $category_slug,
        'is_shop' => is_shop() ? 'true' : 'false',
        'current_user_id' => get_current_user_id(),
        'user_url' => $user_url,
        'paged' => $paged ,
        'nonce_like' => $nonce_like ,
        'ajax_noncy_nonce' =>  wp_create_nonce( 'noncy_nonce' ),
        'min_price_per_product_cat' => $min_price_per_product_cat ? $min_price_per_product_cat : 0,
        'max_price_per_product_cat' => $max_price_per_product_cat ? $max_price_per_product_cat : 50000,
        'sizes' => $sizes_v1,
    ));
}

//wp-embed.min.js remove
remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
remove_action( 'wp_head', 'wp_oembed_add_host_js' );

//remove jquery-migrate
function dequeue_jquery_migrate( $scripts ) {
	if ( ! is_admin() && ! empty( $scripts->registered['jquery'] ) ) {
		$jquery_dependencies = $scripts->registered['jquery']->deps;
		$scripts->registered['jquery']->deps = array_diff( $jquery_dependencies, array( 'jquery-migrate' ) );
	}
}
add_action( 'wp_default_scripts', 'dequeue_jquery_migrate' );

function add_styles() {
        if(is_admin()) return false;
        time_enqueuer('onepagescrollcss', '/assets/css/onepagescroll.css', 'style', false, 'all'); 
        time_enqueuer('main', '/assets/css/main.css', 'style', false, 'all');    
        time_enqueuer('mediacss', '/assets/css/media-requests.css', 'style', false, 'all');   
}
add_action('wp_print_styles', 'add_styles');

if( function_exists('acf_add_options_page') ) {
    acf_add_options_page(array(
        'page_title'    => 'Основные настройки',
        'menu_title'    => 'Основные настройки',
        'menu_slug'     => 'options',
        'capability'    => 'edit_posts',
        'redirect'      => false
    ));
}

Timber::$dirname = array('templates', 'views');
class StarterSite extends TimberSite {
	function __construct() {
		add_theme_support( 'post-formats' );
        add_theme_support( 'post-thumbnails' );
        add_theme_support( 'woocommerce' );
        add_theme_support( 'menus' );
        add_filter( 'timber_context', array( $this, 'add_to_context' ) );
		add_theme_support( 'html5', array( 'comment-list', 'comment-form', 'search-form', 'gallery', 'caption' ) );
        parent::__construct();
    }
    
    function add_to_context( $context ) {
        $context['menu_header'] = new TimberMenu('menu_header');      
        $context['menu_about_company'] = new TimberMenu('menu_about_company');      
        $context['menu_buyers'] = new TimberMenu('menu_buyers');      
        $context['menu_account'] = new TimberMenu('menu_account');      
        
		return $context;
	}
}
new StarterSite();

function timber_set_product( $post ) {
    global $product;
    
    if ( is_woocommerce() || is_home() || is_page('filter') ) {
        $product = wc_get_product( $post->ID );
    }
}

function woocommerce_script_cleaner() {
	// Remove the generator tag
	remove_action( 'wp_head', array( $GLOBALS['woocommerce'], 'generator' ) );
	wp_dequeue_style( 'woocommerce_frontend_styles' );
	wp_dequeue_style( 'woocommerce-general');
	wp_dequeue_style( 'woocommerce-layout' );
	wp_dequeue_style( 'woocommerce-smallscreen' );
	wp_dequeue_style( 'woocommerce_fancybox_styles' );
	wp_dequeue_style( 'woocommerce_chosen_styles' );
	wp_dequeue_style( 'woocommerce_prettyPhoto_css' );
	wp_dequeue_script( 'selectWoo' );
	wp_deregister_script( 'selectWoo' );
	wp_dequeue_script( 'wc-add-payment-method' );
	wp_dequeue_script( 'wc-lost-password' );
	wp_dequeue_script( 'wc_price_slider' );
	wp_dequeue_script( 'wc-single-product' );
	// wp_dequeue_script( 'wc-cart-fragments' );
	wp_dequeue_script( 'wc-credit-card-form' );
	wp_dequeue_script( 'wc-checkout' );
	wp_dequeue_script( 'wc-add-to-cart-variation' );
	wp_dequeue_script( 'wc-single-product' );
	wp_dequeue_script( 'wc-cart' );
	wp_dequeue_script( 'wc-chosen' );
	wp_dequeue_script( 'woocommerce' );
	wp_dequeue_script( 'prettyPhoto' );
	wp_dequeue_script( 'prettyPhoto-init' );
	wp_dequeue_script( 'jquery-blockui' );
	wp_dequeue_script( 'jquery-placeholder' );
	wp_dequeue_script( 'jquery-payment' );
	wp_dequeue_script( 'fancybox' );
	wp_dequeue_script( 'jqueryui' );
	if ( ! is_woocommerce() && ! is_cart() && ! is_checkout() ) {
        // wp_dequeue_script( 'wc-add-to-cart' );
    }
}
add_action( 'wp_enqueue_scripts', 'woocommerce_script_cleaner', 99 );

//Disable gutenberg style in Front
function wps_deregister_styles() {
    wp_dequeue_style( 'wp-block-library' );
}
add_action( 'wp_print_styles', 'wps_deregister_styles', 100 );

//remove type js and css for validator
add_action('wp_loaded', 'prefix_output_buffer_start');
function prefix_output_buffer_start() { 
    ob_start("prefix_output_callback"); 
}
add_action('shutdown', 'prefix_output_buffer_end');
function prefix_output_buffer_end() { 
    ob_end_flush(); 
}
function prefix_output_callback($buffer) {
    return preg_replace( "%[ ]type=[\'\"]text\/(javascript|css)[\'\"]%", '', $buffer );
}

include_once(get_template_directory() .'/include/acf-fields.php');
include_once(get_template_directory() .'/include/woocommerce-theme-settings.php');
include_once(get_template_directory() .'/include/rest-api.php');