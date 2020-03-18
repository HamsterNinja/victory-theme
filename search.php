<?php
$templates = array( 'search.twig', 'archive-product.twig', 'index.twig' );
$context          = Timber::get_context();
$context['title'] = get_search_query();
$context['category_count'] = $wp_query->found_posts;
$context['posts'] = new Timber\PostQuery();
Timber::render( $templates, $context );