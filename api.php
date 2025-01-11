<?php
add_action('rest_api_init', function () {
    register_rest_route('holocaust/v1', '/artifacts', array(
        'methods' => 'GET',
        'callback' => 'holocaust_get_artifacts',
    ));
});

function holocaust_get_artifacts() {
    $args = array('post_type' => 'artifact', 'posts_per_page' => -1);
    $query = new WP_Query($args);
    $data = array();

    while ($query->have_posts()) {
        $query->the_post();
        $data[] = array(
            'id' => get_the_ID(),
            'title' => get_the_title(),
            'content' => get_the_content(),
            'image' => get_the_post_thumbnail_url(),
        );
    }
    wp_reset_postdata();

    return $data;
}
?>
