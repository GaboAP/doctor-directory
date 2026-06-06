(function ($) {
    'use strict';

    window.DD_Search = {

        init: function () {
            const $input = $('#dd-search-input');
            if (!$input.length) return;

            let debounceTimer;

            $input.on('input', function () {
                clearTimeout(debounceTimer);
                const query = $(this).val();

                debounceTimer = setTimeout(function () {
                    DD_Search.fetch(query);
                }, 350);
            });
        },

        fetch: function (query) {
            $.ajax({
                url:  DD_Ajax.ajax_url,
                type: 'GET',
                data: {
                    action: 'dd_live_search',
                    nonce:  DD_Ajax.nonce,
                    s:      query,
                },
                beforeSend: function () {
                    $('#dd-table-wrap').css('opacity', '0.5');
                },
                success: function (response) {
                    if (response.success) {
                        $('#dd-table-wrap')
                            .html(response.data)
                            .css('opacity', '1');

                        // Re-bind modal after table is replaced by AJAX
                        DD_Modal.init();
                    }
                },
                error: function () {
                    $('#dd-table-wrap').css('opacity', '1');
                }
            });
        }
    };

    $(document).ready(function () {
        DD_Search.init();
    });

})(jQuery);