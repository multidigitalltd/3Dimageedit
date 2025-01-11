<?php
// Shortcode to display the camera and AR viewer
function holocaust_ar_camera_shortcode() {
    // Enqueue model-viewer script
    if (!wp_script_is('model-viewer', 'enqueued')) {
        wp_enqueue_script(
            'model-viewer',
            'https://unpkg.com/@google/model-viewer/dist/model-viewer-legacy.min.js',
            array(),
            null,
            false
        );
    }

    // Enqueue custom script and style
    wp_enqueue_script(
        'holocaust-script',
        plugin_dir_url(__FILE__) . '../assets/script.js',
        array(),
        null,
        true
    );

    wp_enqueue_style(
        'holocaust-style',
        plugin_dir_url(__FILE__) . '../assets/style.css'
    );

    // Pass AJAX URL to script
    wp_localize_script(
        'holocaust-script',
        'ajax_object',
        array('ajaxurl' => admin_url('admin-ajax.php'))
    );

    // Return the HTML for the camera and AR viewer
    ob_start();
    ?>
    <div id="camera-ar-container">
        <video id="camera-stream" autoplay muted></video>
        <model-viewer 
            id="ar-element"
            src="https://modelviewer.dev/shared-assets/models/Astronaut.glb" 
            auto-rotate 
            camera-controls 
            environment-image="neutral"
            shadow-intensity="1">
        </model-viewer>
        <button id="capture-photo">Capture</button>
    </div>
    <canvas id="photo-canvas" style="display:none;"></canvas>
    <?php
    return ob_get_clean();
}
add_shortcode('holocaust_ar_camera', 'holocaust_ar_camera_shortcode');

// Shortcode to display the gallery of artifacts
function holocaust_gallery_shortcode() {
    $args = array(
        'post_type' => 'artifact',
        'posts_per_page' => -1,
    );

    $query = new WP_Query($args);
    ob_start();

    if ($query->have_posts()) {
        echo '<div class="holocaust-gallery">';
        while ($query->have_posts()) {
            $query->the_post();
            echo '<div class="artifact">';
            echo '<h3>' . get_the_title() . '</h3>';
            echo get_the_post_thumbnail(get_the_ID(), 'medium');
            $ar_file = get_post_meta(get_the_ID(), 'ar_file', true);
            if ($ar_file) {
                echo '<model-viewer src="' . esc_url($ar_file) . '" auto-rotate camera-controls></model-viewer>';
            }
            echo '</div>';
        }
        echo '</div>';
    }

    wp_reset_postdata();
    return ob_get_clean();
}
add_shortcode('holocaust_gallery', 'holocaust_gallery_shortcode');

// Shortcode to display a single AR element
function holocaust_ar_shortcode($atts) {
    $atts = shortcode_atts(array('file' => ''), $atts, 'holocaust_ar');

    if (empty($atts['file'])) {
        return 'No AR file provided.';
    }

    return '<model-viewer src="' . esc_url($atts['file']) . '" alt="3D model" auto-rotate camera-controls></model-viewer>';
}
add_shortcode('holocaust_ar', 'holocaust_ar_shortcode');
