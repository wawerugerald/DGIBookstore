<?php
/**
 * Import and Manage Books from CSV
 * 
 * Adds:
 * - Import Books button (from books.csv)
 * - Delete All Books button (removes all imported books and resets the import flag)
 */

if (!defined('ABSPATH')) exit;

/**
 * Handle book import from CSV
 */
function bookshop_import_books_from_csv() {
    if (!current_user_can('manage_options')) return;

    // === Handle Import ===
    if (isset($_GET['import_books']) && $_GET['import_books'] === 'true') {

        // Prevent duplicate imports
        if (get_option('bookshop_books_imported')) {
            wp_die('Books have already been imported. Delete them first to re-import.');
        }

        $csv_file = get_stylesheet_directory() . '/books.csv';

        if (!file_exists($csv_file)) {
            wp_die('CSV file not found. Please ensure books.csv exists in the theme directory.');
        }

        $imported = 0;
        $errors = [];

        if (($handle = fopen($csv_file, 'r')) !== false) {
            $headers = fgetcsv($handle, 1000, ','); // First row = headers

            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                $book_data = array_combine($headers, $data);

                $post_id = wp_insert_post([
                    'post_title'   => sanitize_text_field($book_data['book_title']),
                    'post_type'    => 'book',
                    'post_status'  => 'publish',
                    'post_content' => 'Book description goes here.',
                ]);

                if (is_wp_error($post_id)) {
                    $errors[] = $post_id->get_error_message();
                    continue;
                }

                // Add ACF fields
                if (function_exists('update_field')) {
                    update_field('book_title', sanitize_text_field($book_data['book_title']), $post_id);
                    update_field('author', sanitize_text_field($book_data['author']), $post_id);
                    update_field('isbn', sanitize_text_field($book_data['isbn']), $post_id);
                    update_field('price', floatval($book_data['price']), $post_id);
                } else {
                    // Fallback if ACF not available
                    update_post_meta($post_id, 'book_title', sanitize_text_field($book_data['book_title']));
                    update_post_meta($post_id, 'author', sanitize_text_field($book_data['author']));
                    update_post_meta($post_id, 'isbn', sanitize_text_field($book_data['isbn']));
                    update_post_meta($post_id, 'price', floatval($book_data['price']));
                }

                $imported++;
            }
            fclose($handle);
        }

        update_option('bookshop_books_imported', true);

        $message = "✅ Successfully imported {$imported} books.";
        if (!empty($errors)) $message .= "<br>Errors: " . implode(', ', $errors);

        wp_die($message . '<br><a href="' . admin_url('edit.php?post_type=book') . '" class="button">View Books</a>');
    }

    // === Handle Delete ===
    if (isset($_GET['delete_books']) && $_GET['delete_books'] === 'true') {
        $books = get_posts([
            'post_type' => 'book',
            'posts_per_page' => -1,
            'fields' => 'ids',
        ]);

        $deleted = 0;
        foreach ($books as $book_id) {
            wp_delete_post($book_id, true);
            $deleted++;
        }

        delete_option('bookshop_books_imported');

        wp_die("🗑️ Successfully deleted {$deleted} books.<br><a href='" . admin_url('edit.php?post_type=book&page=import-books') . "' class='button'>Back</a>");
    }
}
add_action('init', 'bookshop_import_books_from_csv');

/**
 * Add Import/Delete page to the Books menu
 */
function bookshop_add_import_menu() {
    add_submenu_page(
        'edit.php?post_type=book',
        'Import Books',
        'Import Books',
        'manage_options',
        'import-books',
        'bookshop_import_page'
    );
}
add_action('admin_menu', 'bookshop_add_import_menu');

/**
 * Render admin page
 */
function bookshop_import_page() {
    ?>
    <div class="wrap">
        <h1>📚 Book Import Manager</h1>

        <?php if (get_option('bookshop_books_imported')): ?>
            <div class="notice notice-success is-dismissible">
                <p><strong>Books have already been imported.</strong></p>
                <p>
                    <a href="<?php echo admin_url('edit.php?post_type=book'); ?>" class="button">View Books</a>
                    <a href="<?php echo admin_url('edit.php?post_type=book&page=import-books&delete_books=true'); ?>" 
                       class="button button-secondary" 
                       onclick="return confirm('Are you sure you want to delete all books?');">
                        🗑️ Delete All Books
                    </a>
                </p>
            </div>
        <?php else: ?>
            <p>Click below to import books from your <code>books.csv</code> file.</p>
            <p><strong>File location:</strong> <?php echo get_stylesheet_directory(); ?>/books.csv</p>
            <a href="<?php echo admin_url('edit.php?post_type=book&page=import-books&import_books=true'); ?>" 
               class="button button-primary">📥 Import Books Now</a>
        <?php endif; ?>
    </div>
    <?php
}
