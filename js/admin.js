jQuery(document).ready(function($) {
    $('.wcp-progress').hide();

    $(".add-field").click(function(event) {
        event.preventDefault();
        var clone_this = $(this).closest('tr').clone(true);
        clone_this.removeClass('first-element');
        $(clone_this).appendTo('.table-woo-packages').hide().fadeIn('slow');
    });

    $(".delete-field").click(function(event) {
        event.preventDefault();
        if ($(this).closest('tr').hasClass('first-element')) {
            swal('Sorry!', 'You can not delete first package.', 'warning');
        } else {
            $(this).closest('tr').fadeOut(500, function() { $(this).remove(); });
        }
    });

    $('#rem-woo-form').submit(function(event) {
	    event.preventDefault();
        $('.wcp-progress').show();
        var packages = [];
        $('.table-woo-packages').find('tr').each(function(index, el) {
            if (!$(this).hasClass('rem-table-header')) {
                var pkg = {
                    pkg_name: $(this).find('.pkg_name').val(),
                    count: $(this).find('.count').val(),
                    price: $(this).find('.price').val(),
                }
                packages.push(pkg);
            }
        });
        var data = {
            action: 'wcp_rem_save_woo_estato',
            subscription_type: $('#subscription_type').val(),
            product_id: $('#woo_product').val(),
            field_title: $('#field_title').val(),
            packages: packages
        }
        
        $.post(ajaxurl, data, function(resp) {
            $('.wcp-progress').hide();
            swal(resp.title, resp.message, resp.status);
        }, 'json');

    });

});