<?
add_filter('manage_edit-shop_order_columns', 'MY_COLUMNS_FUNCTION');
function MY_COLUMNS_FUNCTION($columns)
{
    $new_columns = (is_array($columns)) ? $columns : array();
    unset($new_columns['order_actions']);
    $new_columns['MY_COLUMN_ID_1'] = 'Имя клиента';
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

add_filter('manage_edit-product_columns', 'show_product_order', 15);
function show_product_order($columns)
{
    $columns['stock_goods'] = 'Запасы';
    $columns['params'] = 'Параметры';
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
    if ($column == 'params')
    {
        echo get_the_excerpt($postid);
    }
}

function price_array($price){
    $del = array('<span class="woocommerce-Price-amount amount">', '<span class="woocommerce-Price-currencySymbol">' ,'</span>','<del>','<ins>', '&#8381;', '&nbsp;');
    $price = str_replace($del, '', $price);
    $price = str_replace('</del>', '|', $price);
    $price = str_replace('</ins>', '|', $price);
    $price_arr = explode('|', $price);
    $price_arr = array_filter($price_arr);
    return $price_arr;
}

function product_render($post)
{
    setup_postdata($post);
    $product = wc_get_product($post->ID);

    $context['id'] = $product->get_id();
    $context['title'] = $product->get_title();;
    $context['link'] = $product->get_permalink();
    $context['thumbnail'] = get_the_post_thumbnail_url($product->get_id() , 'medium');
    $context['prices'] = price_array($product->get_price_html());

    Timber::render('partials/product-item.twig', $context);
}