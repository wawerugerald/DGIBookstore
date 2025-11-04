<?php
/**
 * WooCommerce Integration for Book CPT
 */

if (!defined('ABSPATH')) exit;

/**
 * Automatically link a WooCommerce Product by ISBN (SKU)
 * Runs when a Book is saved or updated
 * Waweru Gerald Anthony
 */
add_action('acf/init', function() {
    add_action('save_post_book', 'bookshop_link_product_to_book', 20, 3);
});

function bookshop_link_product_to_book($post_id, $post, $update) {
    // Skip autosaves and revisions
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (wp_is_post_revision($post_id)) return;
    if ($post->post_type !== 'book') return;
    if (!class_exists('WooCommerce')) return;
    if (!function_exists('get_field')) return;

    // Get book details
    $book_title = get_field('book_title', $post_id);
    $author     = get_field('author', $post_id);
    $isbn       = get_field('isbn', $post_id);
    $price      = get_field('price', $post_id);

    if (!$isbn || !$price) return;

    // Clean up ISBN to remove strange characters
    $isbn = trim(preg_replace('/[^\w-]/', '', $isbn));

    // Find product by SKU (ISBN)
    $product_id = wc_get_product_id_by_sku($isbn);

    if (!$product_id) {
        // Create new WooCommerce product
        $product = new WC_Product_Simple();
        $product->set_name($book_title . ' by ' . $author);
        $product->set_regular_price($price);
        $product->set_sku($isbn);
        $product->set_manage_stock(false);
        $product->set_stock_status('instock');
        $product->set_catalog_visibility('visible');
        $product->save();

        $product_id = $product->get_id();
    }

    // Link both ways
    update_post_meta($post_id, '_linked_product_id', $product_id);
    update_post_meta($product_id, '_linked_book_id', $post_id);
    update_post_meta($product_id, '_book_isbn', $isbn);
}

/**
 * Get linked WooCommerce Product ID for a Book
 */
function bookshop_get_product_id_for_book($book_id) {
    $product_id = get_post_meta($book_id, '_linked_product_id', true);

    if (!$product_id && function_exists('get_field')) {
        $isbn = get_field('isbn', $book_id);
        if ($isbn) {
            $isbn = trim(preg_replace('/[^\w-]/', '', $isbn));
            $product_id = wc_get_product_id_by_sku($isbn);
        }
    }

    return $product_id;
}

/**
 * Load custom template for single books
 */
function bookshop_single_book_template($template) {
    if (is_singular('book')) {
        $custom_template = locate_template('single-book.php');
        if ($custom_template) return $custom_template;
    }
    return $template;
}
add_filter('template_include', 'bookshop_single_book_template');
