/**
 * Admin Dashboard JavaScript for Client Onboarding Form Plugin
 */

(function($) {
    'use strict';

    class COBAdmin {
        constructor() {
            this.init();
        }

        init() {
            this.bindEvents();
            this.initTooltips();
        }

        bindEvents() {
            // Confirmation dialogs for delete actions
            $('.button-link-delete').on('click', function(e) {
                if (!confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
                    e.preventDefault();
                    return false;
                }
            });

            // Form submission loading states
            $('form').on('submit', function() {
                $(this).find('input[type="submit"], button[type="submit"]').addClass('cob-loading').prop('disabled', true);
            });

            // Auto-save settings as user types (debounced)
            let settingsTimeout;
            $('.form-table input, .form-table textarea, .form-table select').on('input change', function() {
                clearTimeout(settingsTimeout);
                settingsTimeout = setTimeout(() => {
                    // Could implement auto-save here if needed
                }, 2000);
            });

            // Copy shortcode to clipboard
            $(document).on('click', 'code', function() {
                const text = $(this).text();
                navigator.clipboard.writeText(text).then(() => {
                    const $this = $(this);
                    const originalText = $this.text();
                    $this.text('Copied!').css('background-color', '#d4edda');
                    setTimeout(() => {
                        $this.text(originalText).css('background-color', '');
                    }, 2000);
                }).catch(() => {
                    // Fallback for older browsers
                    this.select();
                    document.execCommand('copy');
                });
            });

            // Test webhook functionality
            $('#test-webhook').on('click', function(e) {
                e.preventDefault();
                const webhookUrl = $('#webhook_url').val();
                
                if (!webhookUrl) {
                    alert('Please enter a webhook URL first.');
                    return;
                }

                $(this).addClass('cob-loading').prop('disabled', true);

                // Send test webhook
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'cob_test_webhook',
                        webhook_url: webhookUrl,
                        nonce: $('#_wpnonce').val()
                    }
                }).done((response) => {
                    if (response.success) {
                        alert('Webhook test successful!');
                    } else {
                        alert('Webhook test failed: ' + (response.data || 'Unknown error'));
                    }
                }).fail(() => {
                    alert('Webhook test failed: Network error');
                }).always(() => {
                    $(this).removeClass('cob-loading').prop('disabled', false);
                });
            });

            // Bulk actions for submissions
            $('#doaction, #doaction2').on('click', function(e) {
                const action = $(this).siblings('select').val();
                const checked = $('input[name="submission[]"]:checked');

                if (action === 'delete' && checked.length > 0) {
                    if (!confirm(`Are you sure you want to delete ${checked.length} submission(s)? This action cannot be undone.`)) {
                        e.preventDefault();
                        return false;
                    }
                }
            });

            // Select all submissions checkbox
            $('#cb-select-all-1, #cb-select-all-2').on('change', function() {
                const isChecked = $(this).is(':checked');
                $('input[name="submission[]"]').prop('checked', isChecked);
            });

            // Update master checkbox when individual items change
            $('input[name="submission[]"]').on('change', function() {
                const total = $('input[name="submission[]"]').length;
                const checked = $('input[name="submission[]"]:checked').length;
                
                $('#cb-select-all-1, #cb-select-all-2').prop('checked', total === checked && total > 0);
            });
        }

        initTooltips() {
            // Add tooltips to help icons
            $('.help-icon').tooltip({
                position: { my: "left+15 center", at: "right center" }
            });
        }

        // Utility function to show admin notices
        showNotice(message, type = 'success') {
            const noticeClass = type === 'error' ? 'notice-error' : 'notice-success';
            const notice = $(`
                <div class="notice ${noticeClass} is-dismissible">
                    <p>${message}</p>
                    <button type="button" class="notice-dismiss">
                        <span class="screen-reader-text">Dismiss this notice.</span>
                    </button>
                </div>
            `);

            $('.wrap h1').after(notice);

            // Auto-dismiss after 5 seconds
            setTimeout(() => {
                notice.fadeOut(() => notice.remove());
            }, 5000);

            // Manual dismiss
            notice.find('.notice-dismiss').on('click', () => {
                notice.fadeOut(() => notice.remove());
            });
        }

        // Export submissions to CSV
        exportSubmissions() {
            const selectedIds = $('input[name="submission[]"]:checked').map(function() {
                return $(this).val();
            }).get();

            if (selectedIds.length === 0) {
                alert('Please select submissions to export.');
                return;
            }

            // Create form and submit
            const form = $('<form>', {
                method: 'POST',
                action: ajaxurl
            }).append(
                $('<input>', { type: 'hidden', name: 'action', value: 'cob_export_submissions' }),
                $('<input>', { type: 'hidden', name: 'submission_ids', value: selectedIds.join(',') }),
                $('<input>', { type: 'hidden', name: 'nonce', value: $('#_wpnonce').val() })
            );

            $('body').append(form);
            form.submit();
            form.remove();
        }
    }

    // Initialize when document is ready
    $(document).ready(() => {
        window.COBAdmin = new COBAdmin();
    });

    // Global functions for template use
    window.cobExportSubmissions = () => {
        if (window.COBAdmin) {
            window.COBAdmin.exportSubmissions();
        }
    };

})(jQuery);