/**
 * Detailed Comment: Admin Theme Settings Page Controller.
 * Manages color picker synchronization, real-time live preview rendering,
 * one-click preset application (including the Teal & Lime 2-color branding),
 * logo image upload preview, and AJAX persistence to the backend.
 */
$(function () {
    // Detailed Comment: Mapping of color picker IDs to text inputs and preview targets
    const colorBindings = [
        { picker: '#header_bg_picker', text: '#header_bg', update: (val) => $('#preview_navbar').css('background-color', val) },
        { picker: '#header_text_color_picker', text: '#header_text_color', update: (val) => $('#preview_navbar').css('color', val) },
        { picker: '#header_accent_color_picker', text: '#header_accent_color', update: (val) => $('#preview_accent_strip').css('background-color', val) },
        { picker: '#app_background_picker', text: '#app_background', update: (val) => $('#preview_app_body').css('background-color', val) },
        { picker: '#text_color_picker', text: '#text_color', update: (val) => $('#preview_app_body').css('color', val) },
        { picker: '#sidebar_bg_picker', text: '#sidebar_bg', update: (val) => $('#preview_sidebar').css('background-color', val) },
        { picker: '#sidebar_text_color_picker', text: '#sidebar_text_color', update: (val) => $('#preview_sidebar').css('color', val) },
        { picker: '#primary_button_bg_picker', text: '#primary_button_bg', update: (val) => $('#preview_btn_primary').css('background-color', val) },
        { picker: '#primary_button_text_picker', text: '#primary_button_text', update: (val) => $('#preview_btn_primary').css('color', val) },
        { picker: '#secondary_button_bg_picker', text: '#secondary_button_bg', update: (val) => $('#preview_btn_secondary').css('background-color', val) },
        { picker: '#secondary_button_text_picker', text: '#secondary_button_text', update: (val) => $('#preview_btn_secondary').css('color', val) },
        { picker: '#footer_bg_picker', text: '#footer_bg', update: (val) => $('#preview_footer').css('background-color', val) },
        { picker: '#footer_text_color_picker', text: '#footer_text_color', update: (val) => $('#preview_footer').css('color', val) }
    ];

    // Detailed Comment: Bind two-way synchronization between HTML5 color pickers and hex text inputs
    colorBindings.forEach(binding => {
        $(binding.picker).on('input change', function () {
            const val = $(this).val();
            $(binding.text).val(val.toUpperCase());
            binding.update(val);
        });

        $(binding.text).on('input change', function () {
            const val = $(this).val().trim();
            if (/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/.test(val)) {
                $(binding.picker).val(val);
                binding.update(val);
            }
        });
    });

    // Detailed Comment: Handle logo file change and update both thumbnails in real-time
    $('#logo_file').on('change', function () {
        const file = this.files[0];
        if (file) {
            const objectUrl = URL.createObjectURL(file);
            $('#theme_logo_preview').attr('src', objectUrl);
            $('#preview_nav_logo').attr('src', objectUrl);
        }
    });

    // Detailed Comment: Apply presets on click
    $('.preset-btn').on('click', function () {
        const btn = $(this);
        const headerBg       = btn.data('header-bg');
        const headerAccent   = btn.data('header-accent');
        const headerText     = btn.data('header-text');
        const appBg          = btn.data('app-bg');
        const footerBg       = btn.data('footer-bg');
        const footerText     = btn.data('footer-text');
        const sidebarBg      = btn.data('sidebar-bg');
        const sidebarText    = btn.data('sidebar-text');
        const primaryBtnBg   = btn.data('primary-btn-bg');
        const primaryBtnText = btn.data('primary-btn-text');
        const secondaryBtnBg = btn.data('secondary-btn-bg');
        const secondaryBtnText = btn.data('secondary-btn-text');
        const textColor      = btn.data('text-color');

        function applyColor(pickerId, textId, value) {
            if (value) {
                $(pickerId).val(value);
                $(textId).val(value.toUpperCase()).trigger('change');
            }
        }

        applyColor('#header_bg_picker', '#header_bg', headerBg);
        applyColor('#header_accent_color_picker', '#header_accent_color', headerAccent);
        applyColor('#header_text_color_picker', '#header_text_color', headerText);
        applyColor('#app_background_picker', '#app_background', appBg);
        applyColor('#footer_bg_picker', '#footer_bg', footerBg);
        applyColor('#footer_text_color_picker', '#footer_text_color', footerText);
        applyColor('#sidebar_bg_picker', '#sidebar_bg', sidebarBg);
        applyColor('#sidebar_text_color_picker', '#sidebar_text_color', sidebarText);
        applyColor('#primary_button_bg_picker', '#primary_button_bg', primaryBtnBg);
        applyColor('#primary_button_text_picker', '#primary_button_text', primaryBtnText);
        applyColor('#secondary_button_bg_picker', '#secondary_button_bg', secondaryBtnBg);
        applyColor('#secondary_button_text_picker', '#secondary_button_text', secondaryBtnText);
        applyColor('#text_color_picker', '#text_color', textColor);

        if (btn.attr('id') === 'preset_teal_lime') {
            $('#theme_name').val('Teal & Lime Brand');
        } else {
            $('#theme_name').val('Warm Orange Theme');
        }

        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'info',
            title: 'Preset applied to preview',
            showConfirmButton: false,
            timer: 1500
        });
    });

    // Detailed Comment: AJAX Save Theme Changes
    $('#save_theme_btn').on('click', function () {
        const form = document.getElementById('theme_settings_form');
        if (!form.checkValidity()) {
            return form.reportValidity();
        }

        const formData = new FormData(form);
        const btn = $(this);
        btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-1"></i> Saving...');

        $.ajax({
            url: '/api/admin/theme/update',
            type: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                btn.prop('disabled', false).html('<i class="fa-solid fa-floppy-disk me-1"></i> Save Theme Changes');
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Theme Updated!',
                        text: 'Your clinic branding and color scheme have been updated successfully.',
                        confirmButtonText: 'Great!'
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Update Failed',
                        text: response.message || 'An error occurred while saving theme settings.'
                    });
                }
            },
            error: function (xhr) {
                btn.prop('disabled', false).html('<i class="fa-solid fa-floppy-disk me-1"></i> Save Theme Changes');
                let message = 'An error occurred while saving theme settings.';
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    const firstError = Object.values(xhr.responseJSON.errors)[0];
                    message = Array.isArray(firstError) ? firstError[0] : firstError;
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: message
                });
            }
        });
    });

    // Detailed Comment: AJAX Reset to Defaults
    $('#reset_theme_btn').on('click', function () {
        Swal.fire({
            title: 'Reset Theme to Defaults?',
            text: 'This will restore the original clinic branding colors and default logo.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, reset theme'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/api/admin/theme/reset',
                    type: 'POST',
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    success: function (response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Reset Successful',
                                text: 'Theme has been reset to defaults.',
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.reload();
                            });
                        }
                    },
                    error: function () {
                        Swal.fire({
                            icon: 'error',
                            title: 'Reset Failed',
                            text: 'Could not reset theme settings at this time.'
                        });
                    }
                });
            }
        });
    });
});
