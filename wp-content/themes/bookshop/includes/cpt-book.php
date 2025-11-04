<?php
/**
 * Register Custom Post Type: Book
 */

function bookstore_register_book_cpt() {
    $labels = array(
        'name'                  => 'Books',
        'singular_name'         => 'Book',
        'menu_name'             => 'Books',
        'add_new'               => 'Add New',
        'add_new_item'          => 'Add New Book',
        'edit_item'             => 'Edit Book',
        'new_item'              => 'New Book',
        'view_item'             => 'View Book',
        'search_items'          => 'Search Books',
        'not_found'             => 'No books found',
        'not_found_in_trash'    => 'No books found in trash',
    );

    $args = array(
        'labels'              => $labels,
        'public'              => true,
        'has_archive'         => true,
        'publicly_queryable'  => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'show_in_rest'        => true, // Enable Gutenberg & REST
        'menu_icon'           => 'dashicons-book',
        'supports'            => array('title', 'editor', 'thumbnail'),
        'rewrite'             => array('slug' => 'books'),
        'capability_type'     => 'post',
    );

    register_post_type('book', $args);
}
add_action('init', 'bookstore_register_book_cpt');
