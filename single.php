<?php
$context = Timber::get_context();
$post = Timber::query_post();
$context['post'] = $post;

$args = array(
    'post_type' => 'post',
    'posts_per_page' => 2,
    'tax_query' => array(
        'relation' => 'OR',
        array(
            'taxonomy' => 'category',
            'field' => 'slug',
            'terms' => ['novosti']
        ) ,
    )
);
$extra_news = new Timber\PostQuery($args);
$context['extra_news'] = $extra_news;

$args = array(
    'post_type' => 'post',
    'posts_per_page' => 3,
    'tax_query' => array(
        'relation' => 'OR',
        array(
            'taxonomy' => 'category',
            'field' => 'slug',
            'terms' => ['novosti']
        ) ,
    )
);
$read_news = new Timber\PostQuery($args);
$context['read_news'] = $read_news;

if (post_password_required($post->ID))
{
    Timber::render('single-password.twig', $context);
}
else
{
    Timber::render(array(
        'single-' . $post->ID . '.twig',
        'single-' . $post->post_type . '.twig',
        'single.twig'
    ) , $context);
}

