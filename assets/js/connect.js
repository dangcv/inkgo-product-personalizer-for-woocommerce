var InkGo_Connect;

(function () {
    'use strict';

    InkGo_Connect = {
        interval: 0,
        ajax_url: '',
        init: function (ajax_url) {
            this.ajax_url = ajax_url;
            this.loader();
        },
        loader: function () {
            jQuery('.inkgo-connect-button').click(function () {
                jQuery(this).hide();
                jQuery(this).siblings('.loader').removeClass('hidden');
                jQuery(this).listen_status();

                setTimeout(function() {
                    Printful_Connect.hide_loader();
                }, 60000); //hide the loader after a minute, assume failure
            });
        },
        hide_loader: function() {
            var button = jQuery('.inkgo-connect-button');
            button.show();
            button.siblings('.loader').addClass('hidden');
        },
        listen_status: function () {
            this.interval = setInterval(this.get_status.bind(this), 5000);
        },
        get_status: function () {
            var interval = this.interval;
            jQuery.ajax( {
                type: "GET",
                url: this.ajax_url,
                success: function( response ) {
                    if (response === 'OK') {
                        clearInterval(interval);
                        // InkGo_Connect.send_return_message();
                        location.reload();
                    }
                }
            });
        }
    };
})();