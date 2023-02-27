<?php

if( !defined( "WP_UNINSTALL_PLUGIN" ) ) {
    die;
}

//get all posts from unit cpt from db

$args = array(
    "post_type" => "Units",
    "posts_per_page" => -1
);
$unit_posts = get_post( $args );

// delete all posts from unit cpt from db

foreach( $unit_posts as $unit_post ) {
    wp_delete_post( $unit_post->ID, true );
}