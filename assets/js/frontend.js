jQuery(function($) {

    "use strict";

    //===== Shopping Cart

    $('.shopping-cart-open').on('click', function(){
        $('.shopping-cart-canvas').addClass('open');
        $('.whpdf-overlay').addClass('open');
    });

    $('.shopping-cart-close').on('click', function(){
        $('.shopping-cart-canvas').removeClass('open');
        $('.whpdf-overlay').removeClass('open');
    });

    $('.whpdf-overlay').on('click', function(){
        $('.shopping-cart-canvas').removeClass('open');
        $('.whpdf-overlay').removeClass('open');
    });


    if (options.enable_message == 1) {
        $(document).on('added_to_cart', function(event, data) {
            display_custom_notifications();
        });
    }

    function display_custom_notifications() {
        var message_text = 'Product was added to cart successfully';
        if (options.message_text.length != 0) {
            message_text = options.message_text;
        }
        var html = '<div class="message-box woo-message-box success">'+message_text+'</div>';
        $(html).appendTo(".wc-notification-wrapper").fadeIn('slow').animate({opacity: 1.0}, 2500).fadeOut('slow');
    }

});
