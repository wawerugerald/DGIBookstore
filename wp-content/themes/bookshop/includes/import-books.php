<?php
/**
 * Import Books from CSV
 * 
 * Usage: Access via Books → Import Books menu 
 */

function bookshop_import_books_from_csv() {
    // Security check that only the admin can run this
    if (!current_user_can('manage_options')) {
        return;
    }

    // Check if import is being requested
    if (isset($_GET['import_books']) && $_GET['import_books'] === 'true') {
        
        // Check if already imported to prevent duplicates
        if (get_option('bookshop_books_imported')) {
            wp_die('Books have already been imported. Delete the books and remove the option to re-import.');
        }

        $csv_file = get_stylesheet_directory() . '/books.csv';
        
        if (!file_exists($csv_file)) {
            wp_die('CSV file not found. Please ensure books.csv exists in the theme directory.');
        }

        $imported = 0;
        $errors = array();

        // Opening and reading CSV
        if (($handle = fopen($csv_file, 'r')) !== FALSE) {
            $headers = fgetcsv($handle, 1000, ','); // Getting the headers

            while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
                $book_data = array_combine($headers, $data);
                
                // Creating a post
                $post_id = wp_insert_post(array(
                    'post_title'   => sanitize_text_field($book_data['book_title']),
                    'post_type'    => 'book',
                    'post_status'  => 'publish',
                    'post_content' => 'Book description goes here.',
                ));

                if (is_wp_error($post_id)) {
                    $errors[] = $post_id->get_error_message();
                    continue;
                }

                // Add ACF fields
                update_field('book_title', sanitize_text_field($book_data['book_title']), $post_id);
                update_field('author', sanitize_text_field($book_data['author']), $post_id);
                update_field('isbn', sanitize_text_field($book_data['isbn']), $post_id);
                update_field('price', floatval($book_data['price']), $post_id);

                $imported++;
            }
            
            fclose($handle);
        }

        // Mark as imported
        update_option('bookshop_books_imported', true);

        // Show success message
        $message = "Successfully imported {$imported} books.";
        if (!empty($errors)) {
            $message .= " Errors: " . implode(', ', $errors);
        }
        
        wp_die($message . ' <br><a href="' . admin_url('edit.php?post_type=book') . '">View Books</a>');
    }
}
add_action('init', 'bookshop_import_books_from_csv');

// Add import button in admin menu
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

function bookshop_import_page() {
    ?>
    <div class="wrap">
        <h1>Import Books from CSV</h1>
        <?php if (get_option('bookshop_books_imported')): ?>
            <div class="notice notice-success">
                <p>Books have already been imported.</p>
                <p><a href="<?php echo admin_url('edit.php?post_type=book'); ?>" class="button">View Books</a></p>
            </div>
            <hr>
            <h2>Re-import Books</h2>
            <p>To re-import, you need to:</p>
            <ol>
                <li>Delete all existing books</li>
                <li>Run this SQL command in phpMyAdmin: <code>DELETE FROM wp_options WHERE option_name = 'bookshop_books_imported';</code></li>
                <li>Then click the import button again</li>
            </ol>
        <?php else: ?>
            <p>Click the button below to import books from books.csv</p>
            <p><strong>File location:</strong> <?php echo get_stylesheet_directory(); ?>/books.csv</p>
            <a href="<?php echo admin_url('?import_books=true'); ?>" class="button button-primary">Import Books Now</a>
        <?php endif; ?>
    </div>
    <?php
}