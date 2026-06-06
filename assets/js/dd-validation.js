(function ($) {
    'use strict';

    // Exposed globally so dd-search can re-bind after AJAX table update
    window.DD_Validation = {

        init: function () {
            const form = $('#dd-doctor-form');
            if (!form.length) return;

            $('#full_name, #email, #address').on('blur input', function () {
                DD_Validation.validateField($(this));
            });

            form.on('submit', function (e) {
                let valid = true;

                $('#full_name, #email, #address').each(function () {
                    if (!DD_Validation.validateField($(this))) {
                        valid = false;
                    }
                });

                if (!valid) {
                    e.preventDefault();
                    $('.button-primary').addClass('dd-shake');
                    setTimeout(() => $('.button-primary').removeClass('dd-shake'), 500);

                    const firstError = form.find('.dd-field--error').first();
                    if (firstError.length) {
                        $('html, body').animate({ scrollTop: firstError.offset().top - 40 }, 300);
                    }
                } else {
                    setTimeout(function () {
                        $('button[name="dd_submit"]').prop('disabled', true).text('Saving...');
                    }, 50);
                }
            });
        },

        validateField: function ($field) {
            const id = $field.attr('id');
            const value = $field.val().trim();
            const $wrap = $field.closest('.dd-field');
            const $errMsg = $('#err-' + id);
            let error = '';

            if (id === 'full_name' && !value) {
                error = 'Full name is required.';
            }

            if (id === 'email') {
                if (!value) {
                    error = 'Email address is required.';
                } else if (!DD_Validation.isValidEmail(value)) {
                    error = 'Please enter a valid email address.';
                }
            }

            if (id === 'address' && !value) {
                error = 'Physical address is required.';
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
        },

        isValidEmail: function (email) {
            return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
        }
    };

    $(document).ready(function () {
        DD_Validation.init();
    });

})(jQuery);