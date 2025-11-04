jQuery(document).ready(function($) {
    $('#buy-with-stripe').on('click', function(e) {
        e.preventDefault();
        
        var button = $(this);
        var bookId = button.data('book-id');
        var messageDiv = $('#cart-message');
        
        // Disable button and show loading
        button.prop('disabled', true).text('Adding to cart...');
        messageDiv.html('');
        
        // AJAX request
        $.ajax({
            url: bookshop_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'add_book_to_cart',
                book_id: bookId,
                nonce: bookshop_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    messageDiv.html('<span style="color: #46b450; font-size: 18px;">✓ ' + response.data.message + '</span>');
                    button.text('✓ Added to Cart!').css('background', '#46b450');
                    
                    // Redirect to cart after 1.5 seconds
                    setTimeout(function() {
                        window.location.href = response.data.cart_url;
                    }, 1500);
                } else {
                    messageDiv.html('<span style="color: #dc3232;">✗ ' + response.data.message + '</span>');
                    button.prop('disabled', false).text('🛒 Buy with Stripe');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', status, error);
                messageDiv.html('<span style="color: #dc3232;">✗ An error occurred. Please try again.</span>');
                button.prop('disabled', false).text('🛒 Buy with Stripe');
            }
        });
    });
});