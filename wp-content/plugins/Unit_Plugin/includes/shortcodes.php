<?php
//shortcode to display units on the frontend
function render_unit_list() {
    
    // HTML For the First List Starts Here
    
    $html = 
    '<div class="container">
        <div class="row">
            <div class="col-6 col-md-6 col-lg-4">
                <h6 class="text-muted">Units With Area > 1</h6>
                <div class="list-group">';

    $greaterArgs = array(
        'post_type' => 'units',
        'posts_per_page'   => -1,
        'orderby'          => 'post_date',
        'order'            => 'DESC',
        'post_status'      => 'publish',
        'meta_query' => array(
            array(
                'key' => 'area',
                'value' => '1',
                'compare' => '>',
            )
        ),
    );
    
    // $greater_unit_posts = get_posts( $greaterArgs );
    $query = new WP_Query( $greaterArgs );
    $greater_unit_posts = $query->posts;

    if($query->have_posts()){
        while ( $query->have_posts() ) {
            $query->the_post();
            $html.='<li class="list-group-item list-group-item-action">'.get_the_title().'</li>';
        }
    }
    $html.="</div></div>";
    wp_reset_postdata();
    // HTML For the First List Ends Here

    // HTML For the Second List Starts Here
    
    $html .= 
    '<div class="col-6 col-md-6 col-lg-4">
        <h6 class="text-muted">Units With Area = 1</h6>
        <div class="list-group">';

    $equalArgs = array(
        'post_type' => 'units',
        'posts_per_page'   => -1,
        'orderby'          => 'post_date',
        'order'            => 'DESC',
        'post_status'      => 'publish',
        'meta_query' => array(
            array(
                'key' => 'area',
                'value' => '1',
                'compare' => '=',
            )
        ),
    );

    $query = new WP_Query( $equalArgs );
    //$greater_unit_posts = $query->posts;

    if($query->have_posts()){
        while ( $query->have_posts() ) {
            $query->the_post();
            $html.='<li class="list-group-item list-group-item-action">'.get_the_title().'</li>';
        }
    }
    $html.="</div></div>";

    $html .= '</div></div>';
    wp_reset_postdata();

    // HTML For the Second List Ends Here

    return $html;
}
add_shortcode( "get_unit_list", "render_unit_list" );