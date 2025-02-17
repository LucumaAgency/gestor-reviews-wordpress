<?php
/**
 * Plugin Name: Reviews Slider
 * Plugin URI: https://tusitio.com
 * Description: Plugin para gestionar y mostrar un slider de reviews usando Slick.js.
 * Version: 1.0.0
 * Author: Tu Nombre
 * Author URI: https://tusitio.com
 */

// Evitar acceso directo
if (!defined('ABSPATH')) exit;

// Registrar el Custom Post Type de Reviews
function rs_register_reviews_cpt() {
    $args = array(
        'labels' => array(
            'name' => 'Reviews',
            'singular_name' => 'Review',
        ),
        'public' => true,
        'menu_icon' => 'dashicons-star-filled',
        'supports' => array('title', 'editor'),
        'publicly_queryable' => false,
        'rewrite' => false,
    );
    register_post_type('reviews', $args);
}
add_action('init', 'rs_register_reviews_cpt');

// Agregar campos personalizados
function rs_add_meta_boxes() {
    add_meta_box('rs_review_details', 'Detalles de la Review', 'rs_render_review_meta_box', 'reviews', 'normal', 'high');
}
add_action('add_meta_boxes', 'rs_add_meta_boxes');

function rs_render_review_meta_box($post) {
    $stars = get_post_meta($post->ID, '_rs_stars', true);
    ?>
    <label for="rs_stars">Calificación (1-5):</label>
    <input type="number" id="rs_stars" name="rs_stars" value="<?php echo esc_attr($stars); ?>" min="1" max="5" />
    <?php
}

// Guardar meta
function rs_save_review_meta($post_id) {
    if (array_key_exists('rs_stars', $_POST)) {
        update_post_meta($post_id, '_rs_stars', $_POST['rs_stars']);
    }
}
add_action('save_post', 'rs_save_review_meta');

// Shortcode para mostrar el slider
function rs_reviews_slider_shortcode() {
    ob_start();
    ?>
    <div class="reviews-slider">
        <?php
        $query = new WP_Query(array('post_type' => 'reviews', 'posts_per_page' => 5));
        while ($query->have_posts()): $query->the_post();
            $stars = get_post_meta(get_the_ID(), '_rs_stars', true);
        ?>
            <div class="review-card">
                <h3 class="review-name"><?php the_title(); ?></h3>
                <div class="review-stars">
                    <?php for ($i = 0; $i < $stars; $i++): ?>⭐<?php endfor; ?>
                </div>
                <p class="review-text"><?php the_content(); ?></p>
            </div>
        <?php endwhile; wp_reset_postdata(); ?>
    </div>
    <script>
        jQuery(document).ready(function($) {
            $('.reviews-slider').slick({
                slidesToShow: $(window).width() > 768 ? 3 : 1,
                slidesToScroll: 1,
                autoplay: true,
                autoplaySpeed: 3000,
                dots: false,
                arrows: true,
                prevArrow: '<button class="slick-prev">&#10094;</button>',
                nextArrow: '<button class="slick-next">&#10095;</button>',
                responsive: [
                    {
                        breakpoint: 768,
                        settings: {
                            slidesToShow: 1
                        }
                    }
                ]
            });
        });
    </script>
    <style>
        .reviews-slider {
            max-width: 100%;
            margin: auto;
            display: flex;
            gap: 5px;
            justify-content: center;
            align-items: center;
        }
        .review-card {
            background: #f8f8f8;
            border-radius: 10px;
            padding: 10px;
            box-shadow: 0px 2px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            width: 280px;
            height: 210px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .review-name {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .review-stars {
            color: #FFD700;
            font-size: 18px;
            margin-bottom: 5px;
        }
        .review-text {
            font-size: 14px;
            color: #555;
        }
        .slick-prev, .slick-next {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            color: #333;
            z-index: 10;
        }
        .slick-prev {
            left: -30px;
        }
        .slick-next {
            right: -30px;
        }
    </style>
    <?php
    return ob_get_clean();
}
add_shortcode('reviews_slider', 'rs_reviews_slider_shortcode');

// Encolar Scripts y Estilos
function rs_enqueue_scripts() {
    wp_enqueue_style('slick-css', 'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css');
    wp_enqueue_script('jquery');
    wp_enqueue_script('slick-js', 'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js', array('jquery'), null, true);
}
add_action('wp_enqueue_scripts', 'rs_enqueue_scripts');
