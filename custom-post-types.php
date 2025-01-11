<?php
function holocaust_register_artifacts_cpt() {
    $labels = array(
        'name' => 'Artifacts',
        'singular_name' => 'Artifact',
        'add_new' => 'Add New Artifact',
        'edit_item' => 'Edit Artifact',
    );

    $args = array(
        'labels' => $labels,
        'public' => true,
        'menu_icon' => 'dashicons-archive',
        'supports' => array('title', 'editor', 'thumbnail'),
        'has_archive' => true,
    );

    register_post_type('artifact', $args);
}
add_action('init', 'holocaust_register_artifacts_cpt');
?>
