<?php
if (class_exists('Timber'))
{
    Timber::$cache = false;
}
//Скрытие версии wp
add_filter('the_generator', '__return_empty_string');

//TODO: Отключение авторизации rest. Удалить на production
function wc_authenticate_alter()
{
    return new WP_User(1);
}
add_filter('woocommerce_api_check_authentication', 'wc_authenticate_alter', 1);
add_filter('woocommerce_rest_check_permissions', 'my_woocommerce_rest_check_permissions', 90, 4);
function my_woocommerce_rest_check_permissions($permission, $context, $object_id, $post_type)
{
    return true;
}

include_once (get_template_directory() . '/include/Timber/Integrations/WooCommerce/WooCommerce.php');
include_once (get_template_directory() . '/include/Timber/Integrations/WooCommerce/ProductsIterator.php');
include_once (get_template_directory() . '/include/Timber/Integrations/WooCommerce/Product.php');

add_action('after_setup_theme', function ()
{
    add_theme_support('woocommerce');
});

if (class_exists('WooCommerce'))
{
    Timber\Integrations\WooCommerce\WooCommerce::init();
}

function remove_unused_image_sizes()
{

    $allowed_sizes = array(
        'full'
    );
    $registered_sizes = get_intermediate_image_sizes();

    foreach ($registered_sizes as $size)
    {
        if (!in_array($size, $allowed_sizes))
        {
            remove_image_size($size);
        }
    }
}

add_action('init', 'remove_unused_image_sizes');

remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('admin_print_scripts', 'print_emoji_detection_script');
remove_action('wp_print_styles', 'print_emoji_styles');
remove_action('admin_print_styles', 'print_emoji_styles');
remove_filter('the_content_feed', 'wp_staticize_emoji');
remove_filter('comment_text_rss', 'wp_staticize_emoji');
remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
add_filter('tiny_mce_plugins', 'disable_wp_emojis_in_tinymce');
function disable_wp_emojis_in_tinymce($plugins)
{
    if (is_array($plugins))
    {
        return array_diff($plugins, array(
            'wpemoji'
        ));
    }
    else
    {
        return array();
    }
}
function true_remove_default_widget()
{
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

add_action('widgets_init', 'true_remove_default_widget', 20);
add_theme_support('post-thumbnails');

register_nav_menus(array(
    'spring_menu' => 'Весна-Лето',
    'winter_menu' => 'Осень-Зима',
    'sale_menu' => 'SALE',
    'company_menu' => 'О компании',
    'buyer_menu' => 'Покупателям',
    'new_collection_menu' => 'Каталог NEW COLLECTION',
    'specially_menu' => 'Каталог Specially',
    'parole_menu' => 'Каталог PAROLE',
));

function add_async_forscript($url)
{
    if (strpos($url, '#asyncload') === false) return $url;
    else if (is_admin()) return str_replace('#asyncload', '', $url);
    else return str_replace('#asyncload', '', $url) . "' defer='defer";
}

add_filter('clean_url', 'add_async_forscript', 11, 1);
function time_enqueuer($my_handle, $relpath, $type = 'script', $async = 'false', $media = "all", $my_deps = array())
{
    if ($async == 'true')
    {
        $uri = get_theme_file_uri($relpath . '#asyncload');
    }
    else
    {
        $uri = get_theme_file_uri($relpath);
    }
    $vsn = filemtime(get_theme_file_path($relpath));
    if ($type == 'script') wp_enqueue_script($my_handle, $uri, $my_deps, $vsn);
    else if ($type == 'style') wp_enqueue_style($my_handle, $uri, $my_deps, $vsn, $media);
}

//Самая низкая цена в категории
function get_min_price_per_product_cat($term_id)
{
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
    return $wpdb->get_var($wpdb->prepare($sql, $term_id));
};

//Самая высокая цена в категории
function get_max_price_per_product_cat($term_id)
{
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
    return $wpdb->get_var($wpdb->prepare($sql, $term_id));
};

// Получение размеров всех товаров
function getAllSizes()
{
    $args_product_variation = array(
        'post_type' => 'product_variation',
        'post_status' => array(
            'private',
            'publish'
        ) ,
        'numberposts' => - 1,
        'orderby' => 'menu_order',
        'order' => 'ASC',
    );
    $product_variations_query = get_posts($args_product_variation);
    $sizes = [];

    foreach ($product_variations_query as $variation)
    {
        $product_variation = new WC_Product_Variation($variation->ID);
        $product_variation_object_value = $product_variation->get_variation_attributes();
        $sizes[] = $product_variation_object_value['attribute_razmer'];
    }

    $sizes = array_unique($sizes);
    $sizes = array_filter($sizes);
    $sizes = array_values($sizes);
    return $sizes;
}

add_action('wp_footer', 'add_scripts');
function add_scripts()
{
    time_enqueuer('jquerylatest', '/assets/js/vendors/jquery-3.2.0.min.js', 'script', true);
    time_enqueuer('slick', '/assets/js/vendors/slick.js', 'script', true);
    time_enqueuer('lmenujs', '/assets/js/src/left-menu.js', 'script', true);
    time_enqueuer('onepagescrolljs', '/assets/js/vendors/onepagescroll.js', 'script', true);
    time_enqueuer('sliders', '/assets/js/sliders.js', 'script', true);
    time_enqueuer('app-main', '/assets/js/main.bundle.js', 'script', true);

    $queried_object = get_queried_object();
    if ($queried_object)
    {
        $term_id = $queried_object->term_id;
        $term = get_term($term_id, 'product_cat');
        $category_slug = $term->slug;
        $current_brand_term = get_term($term_id, 'brand_product');
        $current_brand = $current_brand_term->slug;
    }
    if ($_GET && $category_slug == null)
    {
        if ($_GET['product-cat'])
        {
            $category_slug = $_GET['product-cat'];
        }
    }

    if (is_product())
    {
        $post_params = Timber::get_post();
        $product_params = wc_get_product($post_params->ID);
        $regular_price = $product_params->get_regular_price();
        $sale_price = $product_params->get_sale_price();
    }
    else
    {
        $regular_price = 0;
        $sale_price = 0;
    }

    $user = get_userdata(get_current_user_id());
    if ($user)
    {
        $user_url = $user->get('user_url');
    }

    $min_price_per_product_cat = get_min_price_per_product_cat($term_id);
    $max_price_per_product_cat = round(get_max_price_per_product_cat($term_id) , -3);

    //TODO: починить блядоразмеры
    $sizes_v1 = TimberHelper::transient('sizes_v1', function ()
    {
        // return getAllSizes();
        return [];
    }
    , 2600);

    wp_localize_script('app-main', 'SITEDATA', array(
        'url' => get_site_url() ,
        'themepath' => get_template_directory_uri() ,
        'ajax_url' => admin_url('admin-ajax.php') ,
        'product_id' => get_the_ID() ,
        'is_home' => is_home() ? 'true' : 'false',
        'is_product' => is_product() ? 'true' : 'false',
        'is_filter' => is_page('filter') ? 'true' : 'false',
        'is_cart' => is_cart() ? 'true' : 'false',
        'is_search' => is_search() ? 'true' : 'false',
        'search_query' => get_search_query() ? get_search_query() : '',
        'category_slug' => $category_slug,
        'is_shop' => is_shop() ? 'true' : 'false',
        'current_user_id' => get_current_user_id() ,
        'user_url' => $user_url,
        'paged' => $paged,
        'nonce_like' => $nonce_like,
        'ajax_noncy_nonce' => wp_create_nonce('noncy_nonce') ,
        'min_price_per_product_cat' => $min_price_per_product_cat ? $min_price_per_product_cat : 0,
        'max_price_per_product_cat' => $max_price_per_product_cat ? $max_price_per_product_cat : 50000,
        'sizes' => $sizes_v1,
    ));
}

//wp-embed.min.js remove
remove_action('wp_head', 'wp_oembed_add_discovery_links');
remove_action('wp_head', 'wp_oembed_add_host_js');

//remove jquery-migrate
function dequeue_jquery_migrate($scripts)
{
    if (!is_admin() && !empty($scripts->registered['jquery']))
    {
        $jquery_dependencies = $scripts->registered['jquery']->deps;
        $scripts->registered['jquery']->deps = array_diff($jquery_dependencies, array(
            'jquery-migrate'
        ));
    }
}
add_action('wp_default_scripts', 'dequeue_jquery_migrate');

function add_styles()
{
    if (is_admin()) return false;
    wp_enqueue_style('materialdesignicons', 'https://cdn.jsdelivr.net/npm/@mdi/font@4.x/css/materialdesignicons.min.css');
    time_enqueuer('onepagescrollcss', '/assets/css/onepagescroll.css', 'style', false, 'all');
    time_enqueuer('vuitify', '/assets/css/vuitify.css', 'style', false, 'all');
    time_enqueuer('main', '/assets/css/main.css', 'style', false, 'all');
    time_enqueuer('mediacss', '/assets/css/media-requests.css', 'style', false, 'all');
    time_enqueuer('customcss', '/assets/css/custom.css', 'style', false, 'all');
}
add_action('wp_print_styles', 'add_styles');

if (function_exists('acf_add_options_page'))
{
    acf_add_options_page(array(
        'page_title' => 'Основные настройки',
        'menu_title' => 'Основные настройки',
        'menu_slug' => 'options',
        'capability' => 'edit_posts',
        'redirect' => false
    ));
}

Timber::$dirname = array(
    'templates',
    'views'
);
class StarterSite extends TimberSite
{
    function __construct()
    {
        add_theme_support('post-formats');
        add_theme_support('post-thumbnails');
        add_theme_support('woocommerce');
        add_theme_support('menus');
        add_filter('timber_context', array(
            $this,
            'add_to_context'
        ));
        add_theme_support('html5', array(
            'comment-list',
            'comment-form',
            'search-form',
            'gallery',
            'caption'
        ));
        parent::__construct();
    }

    function add_to_context($context)
    {
        $context['spring_menu'] = new TimberMenu('spring_menu');
        $context['winter_menu'] = new TimberMenu('winter_menu');
        $context['sale_menu'] = new TimberMenu('sale_menu');

        $context['new_collection_menu'] = new TimberMenu('new_collection_menu');
        $context['specially_menu'] = new TimberMenu('specially_menu');
        $context['parole_menu'] = new TimberMenu('parole_menu');

        $context['company_menu'] = new TimberMenu('company_menu');
        $context['buyer_menu'] = new TimberMenu('buyer_menu');
        $context['gallery'] = get_field('галерея');
        $context['after_text'] = get_field('текст_под_галереей');
        $context['banners'] = get_field('баннеры', 'options');
        $context['new'] = get_field('new');
        $context['home_cats'] = get_field('категории', 'options');
        $context['address1'] = get_field('адрес_шоу-рум', 'options');
        $context['address2'] = get_field('адрес_форменная_одежда_ржд', 'options');

        $context['facebook'] = 'https://www.facebook.com/victoriaandreyanovaofficial';
        $context['instagram'] = 'https://www.instagram.com/victoria_andreyanova_official';

        global $product; //Если не объявлен ранее. Не уверен в необходимости.
        $categories = get_the_terms($post->ID, 'product_cat');
        $args = array(
            'post_type' => 'product',
            'posts_per_page' => 10,
            'post_parent' => 0,
            'orderby' => 'rand',
            'tax_query' => array(
                'relation' => 'AND',
                array(
                    'taxonomy' => 'product_cat',
                    'field' => 'term_id',
                    'terms' => $categories[0]->term_id,
                )
            )
        );
        $context['other_prod'] = Timber::get_posts($args);

        $args_variation = array(
            'post_status' => 'publish',
            'post_type' => 'product_variation',
            'fields' => 'id=>parent',
            'posts_per_page' => - 1,
        );

        $args_variation['tax_query'] = array(
            'relation' => 'AND'
        );
        $args_variation['meta_query'] = array(
            'relation' => 'AND'
        );

        $request_params = array(
            'key' => '_stock_status',
            'value' => 'instock',
        );
        array_push($args_variation['meta_query'], $request_params);

        $q = new WP_Query($args_variation);
        $parent_ids = wp_list_pluck($q->posts, 'post_parent');

        $args = array(
            'post_status' => 'publish',
            'post_type' => 'product',
            'posts_per_page' => 8,
            'orderby' => 'rand'
        );

        if ($parent_ids)
        {
            $args['post__in'] = $parent_ids;
        }

        if ($include)
        {
            $args['post__in'] = explode(',', $include);
        }
        $context['random_prod'] = Timber::get_posts($args);

        $args = array(
            'post_type' => 'product',
            'posts_per_page' => 10,
            'post_parent' => 0,
            'tax_query' => array(
                'relation' => 'AND',
                array(
                    'taxonomy' => 'product_cat',
                    'field' => 'slug',
                    'terms' => 'v-l-20',
                )
            )
        );
        $context['sprsum'] = Timber::get_posts($args);

        $args = array(
            'post_type' => 'product',
            'posts_per_page' => 10,
            'post_parent' => null,
            'tax_query' => array(
                'relation' => 'AND',
                array(
                    'taxonomy' => 'product_cat',
                    'field' => 'slug',
                    'terms' => 'o-z-19-20',
                )
            )
        );
        $context['wint'] = Timber::get_posts($args);

        return $context;
    }
}
new StarterSite();

function timber_set_product($post)
{
    global $product;

    if (is_woocommerce() || is_home() || is_page('filter'))
    {
        $product = wc_get_product($post->ID);
    }
}

function woocommerce_script_cleaner()
{
    // Remove the generator tag
    remove_action('wp_head', array(
        $GLOBALS['woocommerce'],
        'generator'
    ));
    wp_dequeue_style('woocommerce_frontend_styles');
    wp_dequeue_style('woocommerce-general');
    wp_dequeue_style('woocommerce-layout');
    wp_dequeue_style('woocommerce-smallscreen');
    wp_dequeue_style('woocommerce_fancybox_styles');
    wp_dequeue_style('woocommerce_chosen_styles');
    wp_dequeue_style('woocommerce_prettyPhoto_css');
    wp_dequeue_script('selectWoo');
    wp_deregister_script('selectWoo');
    wp_dequeue_script('wc-add-payment-method');
    wp_dequeue_script('wc-lost-password');
    wp_dequeue_script('wc_price_slider');
    wp_dequeue_script('wc-single-product');
    // wp_dequeue_script( 'wc-cart-fragments' );
    wp_dequeue_script('wc-credit-card-form');
    wp_dequeue_script('wc-checkout');
    wp_dequeue_script('wc-add-to-cart-variation');
    wp_dequeue_script('wc-single-product');
    wp_dequeue_script('wc-cart');
    wp_dequeue_script('wc-chosen');
    wp_dequeue_script('woocommerce');
    wp_dequeue_script('prettyPhoto');
    wp_dequeue_script('prettyPhoto-init');
    wp_dequeue_script('jquery-blockui');
    wp_dequeue_script('jquery-placeholder');
    wp_dequeue_script('jquery-payment');
    wp_dequeue_script('fancybox');
    wp_dequeue_script('jqueryui');
    if (!is_woocommerce() && !is_cart() && !is_checkout())
    {
        // wp_dequeue_script( 'wc-add-to-cart' );
        
    }
}
// add_action( 'wp_enqueue_scripts', 'woocommerce_script_cleaner', 99 );
//Disable gutenberg style in Front
function wps_deregister_styles()
{
    wp_dequeue_style('wp-block-library');
}
add_action('wp_print_styles', 'wps_deregister_styles', 100);

//remove type js and css for validator
//add_action('wp_loaded', 'prefix_output_buffer_start');
//function prefix_output_buffer_start() {
//    ob_start("prefix_output_callback");
//}
//add_action('shutdown', 'prefix_output_buffer_end');
//function prefix_output_buffer_end() {
//    ob_end_flush();
//}
//function prefix_output_callback($buffer) {
//    return preg_replace( "%[ ]type=[\'\"]text\/(javascript|css)[\'\"]%", '', $buffer );
//}


// Check and validate the mobile billing_phone
add_action('woocommerce_save_account_details_errors', 'billing_phone_field_validation', 20, 1);
function billing_phone_field_validation($args)
{
    if (isset($_POST['billing_phone']) && empty($_POST['billing_phone'])) $args->add('error', __('Please fill in your Mobile billing_phone', 'woocommerce') , '');
}

// Save the mobile billing_phone value to user data
add_action('woocommerce_save_account_details', 'my_account_saving_billing_phone', 20, 1);
function my_account_saving_billing_phone($user_id)
{
    if (isset($_POST['billing_phone']) && !empty($_POST['billing_phone'])) update_user_meta($user_id, 'billing_phone', sanitize_text_field($_POST['billing_phone']));
}

// Check and validate the mobile billing_phone
add_action('woocommerce_save_account_details_errors', 'user_birthday_field_validation', 20, 1);
function user_birthday_field_validation($args)
{
    if (isset($_POST['user_birthday']) && empty($_POST['user_birthday'])) $args->add('error', __('Please fill in your Mobile billing_phone', 'woocommerce') , '');
}

// Save the mobile billing_phone value to user data
add_action('woocommerce_save_account_details', 'my_account_saving_user_birthday', 20, 1);
function my_account_saving_user_birthday($user_id)
{
    if (isset($_POST['user_birthday']) && !empty($_POST['user_birthday'])) update_user_meta($user_id, 'user_birthday', sanitize_text_field($_POST['user_birthday']));
}

function wooc_extra_register_fields()
{ ?>      
       <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
          <label for="reg_first_name"><?php _e('First name', 'woocommerce'); ?><span class="required">*</span></label>
          <input type="text" class="input-text" name="first_name" id="reg_first_name" value="<?php if (!empty($_POST['first_name'])) esc_attr_e($_POST['first_name']); ?>" />
       </p>
       <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
          <label for="reg_last_name"><?php _e('Last name', 'woocommerce'); ?><span class="required">*</span></label>
          <input type="text" class="input-text" name="last_name" id="reg_last_name" value="<?php if (!empty($_POST['last_name'])) esc_attr_e($_POST['last_name']); ?>" />
       </p>
       <div class="clear"></div>


       <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
          <label for="reg_billing_phone"><?php _e('Phone', 'woocommerce'); ?></label>
          <input type="text" class="input-text" name="billing_phone" id="reg_billing_phone" value="<?php esc_attr_e($_POST['billing_phone']); ?>" />
       </p>
       <?php
}
add_action('woocommerce_register_form_start', 'wooc_extra_register_fields');

function wooc_validate_extra_register_fields($username, $email, $validation_errors)
{
    if (isset($_POST['first_name']) && empty($_POST['first_name']))
    {
        $validation_errors->add('first_name_error', __('<strong>Error</strong>: First name is required!', 'woocommerce'));
    }
    if (isset($_POST['last_name']) && empty($_POST['last_name']))
    {
        $validation_errors->add('last_name_error', __('<strong>Error</strong>: Last name is required!.', 'woocommerce'));
    }
    return $validation_errors;
}

function wooc_save_extra_register_fields($customer_id)
{
    if (isset($_POST['billing_phone']))
    {
        // billing_phone input filed which is used in WooCommerce
        update_user_meta($customer_id, 'billing_phone', sanitize_text_field($_POST['billing_phone']));
    }
    if (isset($_POST['first_name']))
    {
        //First name field which is by default
        update_user_meta($customer_id, 'first_name', sanitize_text_field($_POST['first_name']));
        // First name field which is used in WooCommerce
        update_user_meta($customer_id, 'first_name', sanitize_text_field($_POST['first_name']));
    }
    if (isset($_POST['last_name']))
    {
        // Last name field which is by default
        update_user_meta($customer_id, 'last_name', sanitize_text_field($_POST['last_name']));
        // Last name field which is used in WooCommerce
        update_user_meta($customer_id, 'last_name', sanitize_text_field($_POST['last_name']));
    }

}
add_action('woocommerce_created_customer', 'wooc_save_extra_register_fields');

function your_prefix_wc_remove_uncategorized_from_breadcrumb($crumbs)
{
    $caregory_links = [];

    $caregory_link = get_category_link(17);
    $caregory_links[] = $caregory_link;

    $caregory_link = get_category_link(36);
    $caregory_links[] = $caregory_link;

    $caregory_link = get_category_link(45);
    $caregory_links[] = $caregory_link;

    foreach ($caregory_links as $caregory_link)
    {

        foreach ($crumbs as $key => $crumb)
        {
            if (in_array($caregory_link, $crumb))
            {
                unset($crumbs[$key]);
            }
        }
    }

    return array_values($crumbs);
}

add_filter('woocommerce_get_breadcrumb', 'your_prefix_wc_remove_uncategorized_from_breadcrumb');

function product_render($post)
{
    setup_postdata($post);
    $product = wc_get_product($post->ID);

    $context['id'] = $product->get_id();
    $context['title'] = $product->get_title();;
    $context['link'] = $product->get_permalink();
    $context['thumbnail'] = get_the_post_thumbnail_url($product->get_id() , 'medium');
    $context['price'] = $product->get_price_html();

    Timber::render('partials/product-item.twig', $context);
}

add_filter('woocommerce_currency_symbol', 'change_existing_currency_symbol', 10, 2);

include_once (get_template_directory() . '/include/acf-fields.php');
include_once (get_template_directory() . '/include/woocommerce-theme-settings.php');
include_once (get_template_directory() . '/include/rest-api.php');

//Запрет обновления плагина
add_filter('site_transient_update_plugins', 'filter_plugin_updates');
function filter_plugin_updates($value)
{
    unset($value->response['woocommerce-and-1centerprise-data-exchange/woocommerce-1c.php']);
    return $value;
}

add_filter('manage_edit-product_columns', 'show_product_order', 15);
function show_product_order($columns)
{
    $columns['stock_goods'] = 'Запасы';
    return $columns;
}

function get_stock_variations_from_product($product_id)
{
    $product = wc_get_product($product_id);
    $variations = $product->get_available_variations();
    foreach ($variations as $variation)
    {
        $variation_id = $variation['variation_id'];
        $variation_obj = new WC_Product_variation($variation_id);
        $stock += $variation_obj->get_stock_quantity();
    }
    return $stock;
}

add_action('manage_product_posts_custom_column', 'wpso23858236_product_column_stock_goods', 10, 2);
function wpso23858236_product_column_stock_goods($column, $postid)
{
    if ($column == 'stock_goods')
    {
        echo get_stock_variations_from_product($postid);
    }
}

/**
 * Change the breadcrumb separator
 */
add_filter('woocommerce_breadcrumb_defaults', 'wcc_change_breadcrumb_delimiter');
function wcc_change_breadcrumb_delimiter($defaults)
{
    $defaults['delimiter'] = ' <svg xmlns="http://www.w3.org/2000/svg" width="8px" viewBox="0 0 512.002 512.002"><path d="M388.425 241.951L151.609 5.79c-7.759-7.733-20.321-7.72-28.067.04-7.74 7.759-7.72 20.328.04 28.067l222.72 222.105-222.728 222.104c-7.759 7.74-7.779 20.301-.04 28.061a19.8 19.8 0 0014.057 5.835 19.79 19.79 0 0014.017-5.795l236.817-236.155c3.737-3.718 5.834-8.778 5.834-14.05s-2.103-10.326-5.834-14.051z"/></svg> ';
    return $defaults;
}

add_filter('manage_edit-shop_order_columns', 'MY_COLUMNS_FUNCTION');
function MY_COLUMNS_FUNCTION($columns)
{
    $new_columns = (is_array($columns)) ? $columns : array();
    unset($new_columns['order_actions']);

    //edit this for your column(s)
    //all of your columns will be added before the actions column
    $new_columns['MY_COLUMN_ID_1'] = 'Имя клиента';

    //stop editing
    $new_columns['order_actions'] = $columns['order_actions'];
    return $new_columns;
}

add_filter("manage_edit-shop_order_sortable_columns", 'MY_COLUMNS_SORT_FUNCTION');
function MY_COLUMNS_SORT_FUNCTION($columns)
{
    $custom = array(
        'MY_COLUMN_ID_1' => 'MY_COLUMN_1_POST_META_ID',
    );
    return wp_parse_args($custom, $columns);
}

add_action('manage_shop_order_posts_custom_column', 'MY_COLUMNS_VALUES_FUNCTION', 2);
function MY_COLUMNS_VALUES_FUNCTION($column)
{
    global $post;
    $data = get_post_meta($post->ID);

    if ($column == 'MY_COLUMN_ID_1')
    {
        $name = $data['_billing_first_name'][0] . ' ' . $data['_billing_last_name'][0];
        echo (isset($name) ? $name : '');
    }
}

