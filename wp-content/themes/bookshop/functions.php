<?php
/**
 * Bookshop Child Theme Functions
 * Waweru Gerald Anthony
 */

function bookshop_enqueue_styles() {
    wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css');
    wp_enqueue_style('child-style', get_stylesheet_uri(), array('parent-style'));
}
add_action('wp_enqueue_scripts', 'bookshop_enqueue_styles');

//  Custom Post Type
require_once get_stylesheet_directory() . '/includes/cpt-book.php';

// Book Import functionality
require_once get_stylesheet_directory() . '/includes/import-books.php';

// WooCommerce Integration
require_once get_stylesheet_directory() . '/includes/woocommerce-integration.php';

// Register ACF Fields 
function bookshop_register_acf_fields() {
    if (function_exists('acf_add_local_field_group')) {
        acf_add_local_field_group(array(
            'key' => 'group_book_details',
            'title' => 'Book Details',
            'fields' => array(
                array(
                    'key' => 'field_book_title',
                    'label' => 'Book Title',
                    'name' => 'book_title',
                    'type' => 'text',
                    'required' => 1,
                ),
                array(
                    'key' => 'field_author',
                    'label' => 'Author',
                    'name' => 'author',
                    'type' => 'text',
                    'required' => 1,
                ),
                array(
                    'key' => 'field_isbn',
                    'label' => 'ISBN',
                    'name' => 'isbn',
                    'type' => 'text',
                    'required' => 1,
                ),
                array(
                    'key' => 'field_price',
                    'label' => 'Price',
                    'name' => 'price',
                    'type' => 'number',
                    'required' => 1,
                    'min' => 0,
                    'step' => 0.01,
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'book',
                    ),
                ),
            ),
        ));
    }
}
add_action('acf/init', 'bookshop_register_acf_fields');

//testing if product created linked to the book's ISBN number..This works
add_action('admin_notices', function() {
    if (get_post_type() === 'book') {
        $product_id = get_post_meta(get_the_ID(), '_linked_product_id', true);
        if ($product_id) {
            echo '<div class="notice notice-success"><p>✅ Linked WooCommerce Product ID: ' . esc_html($product_id) . '</p></div>';
        } else {
            echo '<div class="notice notice-error"><p>⚠️ No linked WooCommerce Product found for this book.</p></div>';
        }
    }
});
