<?php
$context = Timber::get_context();
$templates = array( 'index.twig' );
if ( is_home() ) {    
	array_unshift( $templates, 'home.twig' );	

	$args = array(
		'post_type' => 'product',
		'posts_per_page' => 12,
		'tax_query' => array(
			'relation' => 'OR', 
			array(
				'taxonomy' => 'product_cat',
				'field' => 'slug',
				'terms' => ['sale']
			), 
		)
	);
	$sale_products = new Timber\PostQuery($args);
	$sale_products_ids = wp_list_pluck( $sale_products, 'ID' );                    
	$context['sale_products'] = $sale_products;
	$context['sale_products_ids'] = $sale_products_ids;

	$args = array(
		'post_type' => 'product',
		'posts_per_page' => 12,
		'tax_query' => array(
			'relation' => 'OR', 
			array(
				'taxonomy' => 'product_cat',
				'field' => 'slug',
				'terms' => ['new']
			), 
		)
	);
	$new_products = new Timber\PostQuery($args);
	$new_products_ids = wp_list_pluck( $new_products, 'ID' );                    
	$context['new_products'] = $new_products;
	$context['new_products_ids'] = $new_products_ids;

	Timber::render( $templates, $context );
}
else{
	Timber::render( $templates, $context );
}
?>