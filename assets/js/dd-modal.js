(function ($) {
    'use strict';

    window.DD_Modal = {

        init: function () {
            // Inject modal markup once into the DOM
            if (!$('#dd-modal').length) {
                $('body').append(`
                    <div id="dd-modal-overlay" class="dd-modal-overlay">
                        <div id="dd-modal" class="dd-modal">
                            <div class="dd-modal-icon">⚕</div>
                            <h3 class="dd-modal-title">Delete Doctor</h3>
                            <p class="dd-modal-body">
                                You are about to remove
                                <strong id="dd-modal-name"></strong>
                                from the directory. This action cannot be undone.
                            </p>
                            <div class="dd-modal-actions">
                                <a id="dd-modal-confirm" href="#" class="button button-link-delete">Yes, Delete</a>
                                <button id="dd-modal-cancel" class="button">Cancel</button>
                            </div>
                        </div>
                    </div>
                `);
            }

            // Open modal on delete button click
            $(document).on('click', '.dd-delete-btn', function (e) {
                e.preventDefault();
                $('#dd-modal-name').text($(this).data('name'));
                $('#dd-modal-confirm').attr('href', $(this).attr('href'));
                $('#dd-modal-overlay').addClass('dd-modal-open');
            });

            // Close on cancel button or overlay click
            $(document).on('click', '#dd-modal-cancel, #dd-modal-overlay', function (e) {
                if (e.target === this) {
                    $('#dd-modal-overlay').removeClass('dd-modal-open');
                }
            });

            // Close on ESC key
            $(document).on('keydown', function (e) {
                if (e.key === 'Escape') {
                    $('#dd-modal-overlay').removeClass('dd-modal-open');
                }
            });
        }
    };

    $(document).ready(function () {
        DD_Modal.init();
    });

})(jQuery);