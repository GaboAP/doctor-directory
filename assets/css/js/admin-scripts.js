(function ($) {
    'use strict';

    // ─── FORM VALIDATION ────────────────────────────────────────────────────────

    const form = $('#dd-doctor-form');

    if (form.length) {

        // Real-time inline validation on blur
        $('#full_name').on('blur input', function () {
            validateField($(this));
        });

        $('#email').on('blur input', function () {
            validateField($(this));
        });

        $('#address').on('blur input', function () {
            validateField($(this));
        });

        // Intercept submit
        form.on('submit', function (e) {
            let valid = true;

            // Validate all fields on submit
            form.find('input[type="text"], input[type="email"], textarea').each(function () {
                if (!validateField($(this))) {
                    valid = false;
                }
            });

            if (!valid) {
                e.preventDefault();

                // Shake the submit button
                $('.button-primary').addClass('dd-shake');
                setTimeout(() => $('.button-primary').removeClass('dd-shake'), 500);

                // Scroll to first error
                const firstError = form.find('.dd-field--error').first();
                if (firstError.length) {
                    $('html, body').animate({
                        scrollTop: firstError.offset().top - 40
                    }, 300);
                }
            } else {
                // Show loading state
                const btn = $('button[name="dd_submit"]');
                btn.prop('disabled', true).text('Saving...');
            }
        });
    }

    /**
     * Validates a single field.
     * Returns true if valid, false if not.
     */
    function validateField($field) {
        const id      = $field.attr('id');
        const value   = $field.val().trim();
        const $wrap   = $field.closest('.dd-field');
        const $errMsg = $('#err-' + id);
        let error     = '';

        if (id === 'full_name') {
            if (!value) error = 'Full name is required.';
        }

        if (id === 'email') {
            if (!value) {
                error = 'Email address is required.';
            } else if (!isValidEmail(value)) {
                error = 'Please enter a valid email address.';
            }
        }

        if (id === 'address') {
            if (!value) error = 'Physical address is required.';
        }

        if (error) {
            $wrap.addClass('dd-field--error');
            $errMsg.text(error).addClass('dd-error-visible');
            return false;
        } else {
            $wrap.removeClass('dd-field--error');
            $errMsg.text('').removeClass('dd-error-visible');
            return true;
        }
    }

    function isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    // ─── DELETE CONFIRMATION MODAL ───────────────────────────────────────────────

    $(document).on('click', '.dd-delete-btn', function (e) {
        e.preventDefault();

        const name = $(this).data('name');
        const href = $(this).attr('href');

        // Inject modal if not present
        if (!$('#dd-modal').length) {
            $('body').append(`
                <div id="dd-modal-overlay" class="dd-modal-overlay">
                    <div id="dd-modal" class="dd-modal">
                        <div class="dd-modal-icon">⚕</div>
                        <h3 class="dd-modal-title">Delete Doctor</h3>
                        <p class="dd-modal-body">You are about to remove <strong id="dd-modal-name"></strong> from the directory. This action cannot be undone.</p>
                        <div class="dd-modal-actions">
                            <a id="dd-modal-confirm" href="#" class="button button-link-delete">Yes, Delete</a>
                            <button id="dd-modal-cancel" class="button">Cancel</button>
                        </div>
                    </div>
                </div>
            `);
        }

        $('#dd-modal-name').text(name);
        $('#dd-modal-confirm').attr('href', href);

        // Show modal with animation
        $('#dd-modal-overlay').addClass('dd-modal-open');
    });

    $(document).on('click', '#dd-modal-cancel, #dd-modal-overlay', function (e) {
        if (e.target === this) {
            $('#dd-modal-overlay').removeClass('dd-modal-open');
        }
    });

    // ─── LIVE SEARCH (with debounce) ─────────────────────────────────────────────

    const $searchInput = $('#dd-search-input');

    if ($searchInput.length) {
        let debounceTimer;

        $searchInput.on('input', function () {
            clearTimeout(debounceTimer);
            const query = $(this).val();

            debounceTimer = setTimeout(function () {
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
                            $('#dd-table-wrap').html(response.data).css('opacity', '1');
                        }
                    },
                    error: function () {
                        $('#dd-table-wrap').css('opacity', '1');
                    }
                });
            }, 350); // 350ms debounce
        });
    }

})(jQuery);