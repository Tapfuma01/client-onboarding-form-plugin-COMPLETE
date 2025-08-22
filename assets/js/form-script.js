/**
 * Client Onboarding Form JavaScript - Step navigation and form handling
 */

(function($) {
    'use strict';

    class ClientOnboardingForm {
        constructor() {
            this.currentStep = 1;
            this.totalSteps = 4;
            this.sessionId = this.generateSessionId();
            this.autoSaveInterval = null;
            this.isSubmitting = false;

            this.init();
        }

        init() {
            this.bindEvents();
            this.loadDraft();
            this.startAutoSave();
            this.updateStepDisplay();
        }

        bindEvents() {
            // Step navigation
            $('.cob-step-item').on('click', (e) => {
                const step = parseInt($(e.currentTarget).data('step'));
                this.goToStep(step);
            });

            // Form navigation buttons
            $('#cob-continue-btn').on('click', () => this.nextStep());
            $('#cob-previous-btn').on('click', () => this.previousStep());
            $('#cob-submit-btn').on('click', (e) => {
                e.preventDefault();
                this.submitForm();
            });

            // Header buttons
            $('#cob-save-draft').on('click', () => this.saveDraft(true));
            $('#cob-exit-form').on('click', () => this.exitForm());

            // Form input changes
            $('#cob-onboarding-form').on('input change', 'input, textarea, select', () => {
                this.clearFieldError($(event.target));
            });

            // Prevent form submission on enter
            $('#cob-onboarding-form').on('keypress', (e) => {
                if (e.which === 13 && e.target.type !== 'textarea') {
                    e.preventDefault();
                    if (this.currentStep < this.totalSteps) {
                        this.nextStep();
                    } else {
                        this.submitForm();
                    }
                }
            });
        }

        generateSessionId() {
            return 'session_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
        }

        goToStep(step) {
            if (step < 1 || step > this.totalSteps) return;

            // Validate current step before moving forward
            if (step > this.currentStep && !this.validateCurrentStep()) {
                return;
            }

            this.currentStep = step;
            this.updateStepDisplay();
        }

        nextStep() {
            if (this.currentStep < this.totalSteps && this.validateCurrentStep()) {
                this.currentStep++;
                this.updateStepDisplay();
            }
        }

        previousStep() {
            if (this.currentStep > 1) {
                this.currentStep--;
                this.updateStepDisplay();
            }
        }

        updateStepDisplay() {
            // Update step navigation
            $('.cob-step-item').each((index, item) => {
                const $item = $(item);
                const stepNum = $item.data('step');
                
                $item.removeClass('cob-step-active cob-step-completed');
                
                if (stepNum === this.currentStep) {
                    $item.addClass('cob-step-active');
                } else if (stepNum < this.currentStep) {
                    $item.addClass('cob-step-completed');
                }
            });

            // Update step content
            $('.cob-step-content').removeClass('cob-step-active');
            $(`.cob-step-content[data-step="${this.currentStep}"]`).addClass('cob-step-active');

            // Update step title
            const stepTitles = [
                'CLIENT INFORMATION',
                'TECHNICAL INFORMATION', 
                'REPORTING INFORMATION',
                'MARKETING INFORMATION'
            ];
            $('#cob-current-step-title').text(stepTitles[this.currentStep - 1]);

            // Update navigation buttons
            $('#cob-previous-btn').toggle(this.currentStep > 1);
            if (this.currentStep === this.totalSteps) {
                $('#cob-continue-btn').hide();
                $('#cob-submit-btn').show();
            } else {
                $('#cob-continue-btn').show();
                $('#cob-submit-btn').hide();
            }

            // Scroll to top
            $('.cob-form-content').scrollTop(0);
        }

        validateCurrentStep() {
            const currentStepElement = $(`.cob-step-content[data-step="${this.currentStep}"]`);
            const requiredFields = currentStepElement.find('input[required], textarea[required], select[required]');
            let isValid = true;

            requiredFields.each((index, field) => {
                const $field = $(field);
                const value = $field.val().trim();

                if (!value) {
                    this.showFieldError($field, 'This field is required');
                    isValid = false;
                } else if ($field.attr('type') === 'email' && !this.isValidEmail(value)) {
                    this.showFieldError($field, 'Please enter a valid email address');
                    isValid = false;
                } else if ($field.attr('type') === 'url' && !this.isValidUrl(value)) {
                    this.showFieldError($field, 'Please enter a valid URL');
                    isValid = false;
                }
            });

            return isValid;
        }

        isValidEmail(email) {
            return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
        }

        isValidUrl(url) {
            try {
                new URL(url);
                return true;
            } catch {
                return false;
            }
        }

        showFieldError($field, message) {
            $field.addClass('cob-error');
            
            // Remove existing error message
            $field.siblings('.cob-error-message').remove();
            
            // Add new error message
            $field.after(`<div class="cob-error-message">${message}</div>`);
        }

        clearFieldError($field) {
            $field.removeClass('cob-error');
            $field.siblings('.cob-error-message').remove();
        }

        getFormData() {
            const formData = {};
            
            $('#cob-onboarding-form').find('input, textarea, select').each((index, field) => {
                const $field = $(field);
                const name = $field.attr('name');
                
                if (!name) return;

                if ($field.attr('type') === 'checkbox') {
                    if (!formData[name]) formData[name] = [];
                    if ($field.is(':checked')) {
                        formData[name].push($field.val());
                    }
                } else if ($field.attr('type') === 'radio') {
                    if ($field.is(':checked')) {
                        formData[name] = $field.val();
                    }
                } else {
                    formData[name] = $field.val();
                }
            });

            return formData;
        }

        setFormData(data) {
            Object.keys(data).forEach(name => {
                const value = data[name];
                const $fields = $(`[name="${name}"]`);

                $fields.each((index, field) => {
                    const $field = $(field);
                    
                    if ($field.attr('type') === 'checkbox') {
                        $field.prop('checked', Array.isArray(value) && value.includes($field.val()));
                    } else if ($field.attr('type') === 'radio') {
                        $field.prop('checked', $field.val() === value);
                    } else {
                        $field.val(value);
                    }
                });
            });
        }

        saveDraft(showMessage = false) {
            const formData = this.getFormData();
            const clientEmail = formData['primary_contact_email'] || '';
            
            if (showMessage) {
                $('#cob-save-draft').addClass('cob-loading').prop('disabled', true);
            }

            return $.ajax({
                url: cob_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'cob_save_draft',
                    nonce: cob_ajax.nonce,
                    session_id: this.sessionId,
                    form_data: formData,
                    current_step: this.currentStep,
                    client_email: clientEmail
                }
            }).done((response) => {
                try {
                    const data = typeof response === 'string' ? JSON.parse(response) : response;
                    
                    if (data.success) {
                        if (showMessage) {
                            this.showSaveStatus(cob_ajax.messages.draft_saved);
                        }
                        this.updateSaveStatus(data.last_saved);
                    }
                } catch (e) {
                    console.error('Error parsing save response:', e);
                }
            }).fail(() => {
                if (showMessage) {
                    alert('Failed to save draft. Please try again.');
                }
            }).always(() => {
                if (showMessage) {
                    $('#cob-save-draft').removeClass('cob-loading').prop('disabled', false);
                }
            });
        }

        loadDraft() {
            // Check for shared draft token in URL
            const urlParams = new URLSearchParams(window.location.search);
            const shareToken = urlParams.get('cob_share');
            
            if (shareToken) {
                this.loadSharedDraft(shareToken);
                return;
            }
            
            $.ajax({
                url: cob_ajax.ajax_url,
                type: 'GET',
                data: {
                    action: 'cob_get_draft',
                    nonce: cob_ajax.nonce,
                    session_id: this.sessionId
                }
            }).done((response) => {
                try {
                    const data = typeof response === 'string' ? JSON.parse(response) : response;
                    
                    if (data.success && data.form_data && Object.keys(data.form_data).length > 0) {
                        this.setFormData(data.form_data);
                        if (data.current_step) {
                            this.currentStep = data.current_step;
                            this.updateStepDisplay();
                        }
                    }
                } catch (e) {
                    console.error('Error loading draft:', e);
                }
            });
        }

        startAutoSave() {
            // Auto-save every 30 seconds
            this.autoSaveInterval = setInterval(() => {
                this.saveDraft();
            }, 30000);
        }

        stopAutoSave() {
            if (this.autoSaveInterval) {
                clearInterval(this.autoSaveInterval);
                this.autoSaveInterval = null;
            }
        }

        submitForm() {
            if (this.isSubmitting) return;

            if (!this.validateCurrentStep()) {
                return;
            }

            this.isSubmitting = true;
            $('#cob-submit-btn').addClass('cob-loading').prop('disabled', true).text('SUBMITTING...');

            const formData = this.getFormData();

            $.ajax({
                url: cob_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'cob_submit_form',
                    nonce: cob_ajax.nonce,
                    session_id: this.sessionId,
                    form_data: formData
                }
            }).done((response) => {
                try {
                    const data = typeof response === 'string' ? JSON.parse(response) : response;
                    
                    if (data.success) {
                        this.stopAutoSave();
                        this.showSuccessMessage(data.submission_id);
                    } else {
                        alert(data.message || cob_ajax.messages.submit_error);
                    }
                } catch (e) {
                    console.error('Error parsing submit response:', e);
                    alert(cob_ajax.messages.submit_error);
                }
            }).fail(() => {
                alert(cob_ajax.messages.submit_error);
            }).always(() => {
                this.isSubmitting = false;
                $('#cob-submit-btn').removeClass('cob-loading').prop('disabled', false).text('SUBMIT FORM');
            });
        }

        showSuccessMessage(submissionId) {
            const successHtml = `
                <div class="cob-success-message">
                    <h2>Thank You!</h2>
                    <p>Your client onboarding form has been submitted successfully.</p>
                    <p><strong>Submission ID:</strong> ${submissionId}</p>
                    <p>We'll be in touch with you soon to discuss your project.</p>
                </div>
            `;
            
            $('.cob-form-content').html(successHtml);
        }

        showSaveStatus(message) {
            $('#cob-save-text').text(message);
            $('#cob-save-status').show().delay(3000).fadeOut();
        }

        updateSaveStatus(timestamp) {
            if (timestamp) {
                const date = new Date(timestamp);
                $('#cob-save-text').text(`Last saved: ${date.toLocaleTimeString()}`);
                $('#cob-save-status').show();
            }
        }

        loadSharedDraft(token) {
            $.ajax({
                url: cob_ajax.ajax_url,
                type: 'GET',
                data: {
                    action: 'cob_get_shared_draft',
                    nonce: cob_ajax.nonce,
                    token: token
                }
            }).done((response) => {
                try {
                    const data = typeof response === 'string' ? JSON.parse(response) : response;
                    
                    if (data.success) {
                        // Use the shared session ID
                        this.sessionId = data.session_id;
                        
                        // Load form data
                        if (data.form_data && Object.keys(data.form_data).length > 0) {
                            this.setFormData(data.form_data);
                        }
                        
                        // Set current step
                        if (data.current_step) {
                            this.currentStep = data.current_step;
                            this.updateStepDisplay();
                        }
                        
                        // Show success message
                        this.showSaveStatus('Draft loaded successfully from shared link');
                    } else {
                        alert('Failed to load shared draft: ' + (data.message || 'Unknown error'));
                    }
                } catch (e) {
                    console.error('Error loading shared draft:', e);
                    alert('Failed to load shared draft. Please check the link and try again.');
                }
            }).fail(() => {
                alert('Failed to load shared draft. Please check your connection and try again.');
            });
        }

        exitForm() {
            if (confirm(cob_ajax.messages.exit_confirm)) {
                this.saveDraft().always(() => {
                    window.close();
                });
            }
        }
    }

    // Initialize when document is ready
    $(document).ready(() => {
        new ClientOnboardingForm();
    });

})(jQuery);