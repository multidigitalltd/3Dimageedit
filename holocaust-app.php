<?php
/*
Plugin Name: Holocaust App
Description: A custom app plugin for AR-based Holocaust artifacts.
Version: 1.0
Author: Your Name
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// Include core files
if ( file_exists( plugin_dir_path( __FILE__ ) . 'includes/shortcodes.php' ) ) {
    require_once plugin_dir_path( __FILE__ ) . 'includes/shortcodes.php';
} else {
    error_log('Error: shortcodes.php file not found.');
}

if ( file_exists( plugin_dir_path( __FILE__ ) . 'includes/custom-post-types.php' ) ) {
    require_once plugin_dir_path( __FILE__ ) . 'includes/custom-post-types.php';
} else {
    error_log('Error: custom-post-types.php file not found.');
}

// Enqueue styles
function holocaust_enqueue_styles() {
    wp_enqueue_style( 'holocaust-style', plugin_dir_url( __FILE__ ) . 'assets/style.css' );
}
add_action( 'wp_enqueue_scripts', 'holocaust_enqueue_styles' );

// Enqueue custom scripts
function holocaust_enqueue_scripts() {
    wp_enqueue_script( 'holocaust-script', plugin_dir_url( __FILE__ ) . 'assets/script.js', array(), false, true );
}
add_action( 'wp_enqueue_scripts', 'holocaust_enqueue_scripts' );

// Add model-viewer to the <head>
function holocaust_add_model_viewer_to_head() {
    echo '
    <script type="module" src="https://unpkg.com/@google/model-viewer/dist/model-viewer.min.js"></script>
    ';
}
add_action('wp_head', 'holocaust_add_model_viewer_to_head');

// AJAX handler to save photo and create post
add_action('wp_ajax_save_photo', 'holocaust_save_photo');
add_action('wp_ajax_nopriv_save_photo', 'holocaust_save_photo');

function holocaust_save_photo() {
    // Check if photo data is provided
    if (!isset($_POST['photo']) || empty($_POST['photo'])) {
        wp_send_json_error('No photo data received');
        return;
    }

    $photo_data = $_POST['photo'];

    // Remove "data:image/png;base64," from the data
    $photo_data = str_replace('data:image/png;base64,', '', $photo_data);
    $photo_data = base64_decode($photo_data);

    // Save the file in the uploads directory
    $upload_dir = wp_upload_dir();
    $file_name = uniqid() . '.png';
    $file_path = $upload_dir['path'] . '/' . $file_name;
    $file_url = $upload_dir['url'] . '/' . $file_name;

    // Save the file
    if (!file_put_contents($file_path, $photo_data)) {
        wp_send_json_error('Failed to save the photo');
        return;
    }

    // Create a new post of type 'artifact'
    $post_id = wp_insert_post(array(
        'post_title' => 'New Artifact ' . date('Y-m-d H:i:s'),
        'post_type' => 'artifact',
        'post_status' => 'publish',
    ));

    if (is_wp_error($post_id)) {
        wp_send_json_error('Failed to create artifact post');
        return;
    }

    // Attach the image to the post
    $attachment_id = wp_insert_attachment(
        array(
            'guid'           => $file_url,
            'post_mime_type' => 'image/png',
            'post_title'     => 'Artifact Image',
            'post_content'   => '',
            'post_status'    => 'inherit',
        ),
        $file_path,
        $post_id
    );

    require_once(ABSPATH . 'wp-admin/includes/image.php');
    $attachment_data = wp_generate_attachment_metadata($attachment_id, $file_path);
    wp_update_attachment_metadata($attachment_id, $attachment_data);

    // Set the featured image for the post
    set_post_thumbnail($post_id, $attachment_id);

    wp_send_json_success(array(
        'message' => 'Photo saved and artifact created successfully',
        'post_id' => $post_id,
        'file_url' => $file_url,
    ));
}

