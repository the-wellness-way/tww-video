<?php
/**
 * Plugin Name: TWW Video
 * Description: Custom videos for TWW 
 * Version: 1.0.0
 * Author: The Wellness Way
 * Author URI: https://www.thewellnessway.com
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: tww-video
 * Domain Path: /languages
 */

if(!defined('ABSPATH')) {
    exit;
}

if(!defined('TWWV_PLUGIN_FILE')) {
    define('TWWV_PLUGIN_FILE', __FILE__);
}

if(!defined('TWWV_PLUGIN_PATH')) {
    define('TWWV_PLUGIN_PATH', plugin_dir_path(__FILE__));
}  

if(!defined('TWWV_PLUGIN_URL')) {
    define('TWWV_PLUGIN_URL', plugin_dir_url(__FILE__));
}

if(!defined('TWWV_ASSETS_VERSION')) {
    define('TWWV_ASSETS_VERSION', '1.3.12');
}

require_once 'vendor/autoload.php';


use Carbon_Fields\Carbon_Fields;
use Carbon_Fields\Container;
use Carbon_Fields\Field;

add_action( 'after_setup_theme', 'twwv_crb_load' );
function twwv_crb_load() {
    if (class_exists('Carbon_Fields\Carbon_Fields')) {
        Carbon_Fields::boot();
    } else {
        error_log('Carbon Fields is NOT available.');
    }
}
class TwwvVideo {
    public function __construct() {
        add_shortcode('tww_video', [$this, 'render_shortcode']);
        add_action('init', [$this, 'register_post_types']);
        add_action( 'carbon_fields_register_fields', [$this, 'crb_attach_post_type_selection'] );
    }

    public function render_shortcode($atts, $content = null) {
        $html = '
        <video id="tww_video_1" class="video-js vjs-16-9 tww-video-player" controls preload="auto">
            <source src="https://the-wellness-way-videos.s3.us-west-2.amazonaws.com/adp/2024/June/why-hair-loss-happens-and-what-you-can-do-about-it.mp4" type="video/mp4">
            Your browser does not support the video tag.
        </video>
        ';

        return $html;
    }

    public function register_post_types() {
        register_post_type('twwv_video', [
            'labels' => [
                'name' => __('TWW Videos'),
                'singular_name' => __('TWW Video')
            ],
            'public' => false,
            'has_archive' => false,
            'menu_icon' => 'dashicons-playlist-video',
            'supports' => ['title', 'post_tag'],
            'show_in_rest' => true,
            'show_in_nav_menus' => true,
            'rewrite' => ['slug' => 'twwv_video']
        ]); 
    }

    public function crb_attach_post_type_selection() {
        // Logging Carbon Fields initialization
    
        try {
            // For twwv_video post type
            Container::make('post_meta', 'Video URL or Gallery')
                ->where('post_type', '=', 'twwv_video')
                ->add_fields([
                    Field::make('media_gallery', 'twwv_video_from_gallery', 'Select Video')
                        ->set_type(['video'])
                        ->set_required(false)
                        ->set_help_text('Select a video from the media gallery or leave empty if using a URL'),
                    Field::make('oembed', 'twwv_video_url', 'Video URL')
                        ->set_help_text('Enter the URL of the video you want to display, or leave empty if selecting from the gallery')
                        ->set_required(false),
                    Field::make('image', 'twwv_video_poster_image', 'Video Poster Image')
                        ->set_help_text('Attach the poster image to display before the video plays')
                        ->set_required(false)
                ]);
    
    
            // For LearnDash lessons
            Container::make('post_meta', 'Video Selection')
                ->where('post_type', '=', 'sfwd-lessons')
                ->add_fields([
                    Field::make('association', 'twwv_video_lesson', 'Select Video')
                        ->set_types([
                            [
                                'type' => 'post',
                                'post_type' => 'twwv_video',
                            ]
                        ])
                        ->set_required(false)
                        ->set_max(1)
                ]);
    
        } catch (Exception $e) {
            // Catching potential errors and logging them
            error_log('Error in attaching Carbon Fields: ' . $e->getMessage());
        }
    }
}
$twwvVideo = new TwwvVideo();

function add_video_to_lesson_content($content) {
    if (is_singular('sfwd-lessons')) {
        global $post;
        $lesson_id = $post->ID;

        // Associative array mapping lesson IDs to video sources
        $videos = [
            123 => 'https://example.com/videos/video-123.mp4',
            456 => 'https://example.com/videos/video-456.mp4',
            // Add more lesson ID => video source pairs
        ];

        // Check if the lesson ID exists in the array
        if (array_key_exists($lesson_id, $videos)) {
            $video_src = $videos[$lesson_id];
            $video_html = '<video controls>
                               <source src="' . esc_url($video_src) . '" type="video/mp4">
                               Your browser does not support the video tag.
                           </video>';
            // Insert the video before the content
            $content = $video_html . $content;
        }
    }

    return $content;
}
add_filter('the_content', 'add_video_to_lesson_content');

function twwv_register_post_types() {
    register_post_type('twwv_video', [
        'labels' => [
            'name' => __('TWW Videos'),
            'singular_name' => __('TWW Video')
        ],
        'public' => true,
        'has_archive' => false,
        'menu_icon' => 'dashicons-playlist-video',
        'supports' => ['title'],
        'taxonomies' => ['post_tag'],
        'show_in_rest' => true,
        'show_in_nav_menus' => true,
        'rewrite' => ['slug' => 'twwv_video']
    ]);
}

add_action('init', 'twwv_register_post_types');

class TwwvPrestoTemplate {
    public function __construct() {
        add_filter('presto_player_template', [$this, 'tww_video_template'], 10, 2);
    }

    public function tww_video_template($template, $data) {
        if('self-hosted' === $data['type']) {
            $template = '
            <div class="tww-video-container">
                <video x-webkit-airplay="allow" id="tww_video_'.$data['id'].'" class="video-js vjs-16-9 tww-video-player" controls preload="auto" playsinline poster="'. $data['poster'].'">
                        <source src="'.$data['src'].'" type="video/mp4">
                        Your browser does not support the video tag.
                </video>
            </div>';
        }

        return $template;
    }
}
$presto_template = new TwwvPrestoTemplate();

function enqueue_tww_video_webpack() {  
    $file = false !== strpos($_SERVER['HTTP_HOST'],'localhost:8084') ? 'main' : 'index';
    $version = false !== strpos($_SERVER['HTTP_HOST'],'localhost:8084') ? null : TWWV_ASSETS_VERSION;
    $url = trailingslashit(site_url()) . 'wp-content/plugins/tww-video/resources/dist/'.$file.'.bundle.js';
    wp_register_script('tww-video-webpack', $url, array(), $version, true);
    // wp_enqueue_script('tww-video-webpack');
}
add_action('wp_enqueue_scripts', 'enqueue_tww_video_webpack');

function enqueue_videojs_assets() {    
    wp_enqueue_style('nuevo-css-overrides', TWWV_PLUGIN_URL . 'resources/videojs/skins/nuevo/videojs.min.css', null, true);

    //wp_enqueue_style('videojs-css-overrides-ten', TWWV_PLUGIN_URL . 'resources/assets/css/tww-video-player.css', '1.3.27', true);

    // // Enqueue Video.js JavaScript in the footer
    // wp_enqueue_script('videojs-js', 'https://vjs.zencdn.net/8.10.0/video.min.js', array(), null, true);
}
add_action('wp_enqueue_scripts', 'enqueue_videojs_assets');

function enqueue_nuevo_player() {
    wp_register_script('video-js', TWWV_PLUGIN_URL . 'resources/videojs/video.min.js', [], null, true);
    wp_enqueue_script('video-js');
    wp_register_script('video-js-airplay', TWWV_PLUGIN_URL . 'resources/videojs/plugins/videojs.airplay.js', [], null, true);
    wp_enqueue_script('video-js-airplay');
    wp_register_script('nuevo-js', TWWV_PLUGIN_URL . 'resources/videojs/nuevo.min.js', [], null, true);
    wp_enqueue_script('nuevo-js');

    if($post_id = get_the_ID()) {
        $airplaysettings = [
            'title' => get_the_title( $post_id ),
            'albumName' => 'A Different Perspective',
            'artistName' => 'Dr. Patrick Flynn'
        ];

        wp_localize_script('nuevo-js', 'twwVideo', ['airplaySettings' => $airplaysettings]);
    }  
}
add_action('wp_enqueue_scripts', 'enqueue_nuevo_player');

function enqueue_tww_nuevo_player() {
    wp_register_script('tww-nueveo-video-player', TWWV_PLUGIN_URL . 'resources/assets/js/video-player.js', [], TWWV_ASSETS_VERSION, true);
    wp_enqueue_script('tww-nueveo-video-player');

    if (false !== strpos($_SERVER['SERVER_NAME'], 'localhost') ) {
        wp_register_script('localhost-video', TWWV_PLUGIN_URL . 'resources/dist/main.bundle.js', [], null, true);
        wp_enqueue_script('localhost-video');
    }

    wp_dequeue_style('bp-media-videojs-css');
}

add_action('wp_enqueue_scripts', 'enqueue_tww_nuevo_player');

class TWW_BP_Styles {
    public function disable_bp_nouveau_styles($styles) {
        unset($styles['bp-nouveau']);
        
        return $styles;
    }
}

$twwBPStyles = new TWW_BP_Styles();

function disable_bp_nouveau_styles() {
    add_filter('bp_nouveau_enqueue_styles', 'remove_bp_nouveau_styles', 20);
}

function remove_bp_nouveau_styles($styles) {
    unset($styles['bp-nouveau']);
    
    return $styles;
}

add_action('init', 'disable_bp_nouveau_styles', 20);

add_action('learndash-content-tab-listing-before', 'custom_echo_string', 10, 4);

function custom_echo_string($post_id, $context, $course_id, $user_id) {
    $src = null;
    $twwv_video_lesson = carbon_get_post_meta($post_id, 'twwv_video_lesson');
    if($twwv_video_lesson) {
        $twwv_video_from_gallery = carbon_get_post_meta($twwv_video_lesson[0]['id'], 'twwv_video_from_gallery');
        $twwv_video_url = carbon_get_post_meta($twwv_video_lesson[0]['id'], 'twwv_video_url');
        $twwv_video_poster_image_id = carbon_get_post_meta($twwv_video_lesson[0]['id'], 'twwv_video_poster_image');
    
        if($twwv_video_from_gallery) {
            $src =  wp_get_attachment_url( $twwv_video_from_gallery[0] );
        } elseif($twwv_video_url) {
            $src = $twwv_video_url;
        }
    
        if($twwv_video_poster_image_id) {
            $twwv_video_poster_image = wp_get_attachment_image_url($twwv_video_poster_image_id, 'full');
        }
    
        if('lesson' === $context && $src) {
            echo '
            <video class="video-js vjs-16-9" width="300" height="150" poster="' . $twwv_video_poster_image . '">>
                <source src="'.$src.'" type="video/mp4" />
            </video>
            ';
        }
    }
}

function customize_offload_media_path($data, $post_id) {
    // Get the post slug from the attachment's parent post
    $parent_post = get_post($data['post_parent']);
    $post_slug = $parent_post->post_name;

    // Modify the offload path for S3 based on the slug
    add_filter('as3cf_object_meta', function ($object_meta) use ($post_slug) {
        // Set the custom S3 path based on the slug
        $object_meta['path'] = 'custom-directory/' . $post_slug . '/' . basename($object_meta['path']);
        return $object_meta;
    });

    return $data;
}

