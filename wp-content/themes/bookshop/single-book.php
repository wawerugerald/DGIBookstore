<?php
/**
 * Template: Single Book Page
 * Waweru Gerald Anthony
 */

get_header();

while (have_posts()) : the_post();

    $book_title = get_field('book_title');
    $author     = get_field('author');
    $isbn       = get_field('isbn');
    $price      = get_field('price');
    $product_id = bookshop_get_product_id_for_book(get_the_ID());
    $product    = $product_id ? wc_get_product($product_id) : null;
?>

<div class="book-single-container" style="max-width:800px;margin:50px auto;padding:30px;border:1px solid #ddd;border-radius:10px;background:#fafafa;">
    <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

        <header class="book-header" style="text-align:center;">
            <h1 style="font-size:36px;margin-bottom:10px;">
                <?php echo esc_html($book_title ?: get_the_title()); ?>
            </h1>
        </header>

        <?php if (has_post_thumbnail()): ?>
            <div style="text-align:center;margin-bottom:25px;">
                <?php the_post_thumbnail('medium', ['style' => 'border-radius:6px;']); ?>
            </div>
        <?php endif; ?>

        <div style="text-align:center;font-size:18px;">
            <p><strong>Author:</strong> <?php echo esc_html($author ?: 'Unknown'); ?></p>
            <p><strong>ISBN:</strong> <?php echo esc_html($isbn ?: 'N/A'); ?></p>
            <p style="font-size:24px;margin-top:10px;color:#0073aa;font-weight:bold;">
                <strong>Price:</strong> <?php echo $price ? wc_price($price) : 'N/A'; ?>
            </p>
        </div>

        <?php if ($product_id): ?>
            <div style="text-align:center;margin-top:30px;">
                <form action="<?php echo esc_url(wc_get_checkout_url()); ?>" method="GET">
                    <input type="hidden" name="add-to-cart" value="<?php echo esc_attr($product_id); ?>">
                    <button type="submit" 
                            class="button alt" 
                            style="background:#6772E5;color:#fff;padding:14px 28px;font-size:18px;border:none;border-radius:6px;cursor:pointer;">
                        💳 Buy with Stripe
                    </button>
                </form>
            </div>
        <?php else: ?>
            <p style="text-align:center;color:red;font-weight:bold;margin-top:20px;">
                ⚠️ No linked WooCommerce Product found for this Book.
            </p>
        <?php endif; ?>

        <?php if (get_the_content()): ?>
            <div class="book-description" style="margin-top:40px;background:#fff;padding:20px;border-radius:8px;">
                <h2 style="margin-bottom:15px;">Description</h2>
                <?php the_content(); ?>
            </div>
        <?php endif; ?>

    </article>
</div>

<?php endwhile; 
get_footer(); ?>
