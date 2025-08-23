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
            
            // Ensure button visibility is correct on initialization
            setTimeout(() => {
                this.updateButtonVisibility();
                console.log('COB: Initial button visibility update completed');
            }, 100);
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

            // Handle conditional fields
            this.bindConditionalFields();

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

        bindConditionalFields() {
            // Handle radio button changes for conditional fields
            $('input[type="radio"]').on('change', (e) => {
                const name = $(e.target).attr('name');
                const value = $(e.target).val();
                this.toggleConditionalFields(name, value);
            });

            // Handle checkbox changes for conditional fields
            $('input[type="checkbox"]').on('change', (e) => {
                const name = $(e.target).attr('name');
                const isChecked = $(e.target).is(':checked');
                const value = $(e.target).val();
                
                // Check if this checkbox affects conditional fields
                if (value === 'other') {
                    this.toggleConditionalFields(name, isChecked ? 'other' : '');
                }
            });

            // Initialize conditional fields on page load
            this.initializeConditionalFields();
        }

        toggleConditionalFields(fieldName, fieldValue) {
            $(`.cob-conditional-fields[data-show-when="${fieldName}"]`).each((index, element) => {
                const $element = $(element);
                const showValue = $element.attr('data-show-value');
                
                if (fieldValue === showValue) {
                    $element.show();
                    // Make required fields actually required when shown
                    $element.find('[required]').prop('required', true);
                } else {
                    $element.hide();
                    // Remove required attribute when hidden to prevent validation errors
                    $element.find('[required]').prop('required', false);
                }
            });
        }

        initializeConditionalFields() {
            // Set initial state of conditional fields based on current form values
            $('input[type="radio"]:checked').each((index, element) => {
                const name = $(element).attr('name');
                const value = $(element).val();
                this.toggleConditionalFields(name, value);
            });

            // Handle checkbox arrays for "other" options
            $('input[type="checkbox"]:checked').each((index, element) => {
                const name = $(element).attr('name');
                const value = $(element).val();
                
                if (value === 'other') {
                    this.toggleConditionalFields(name, 'other');
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
            $('.cob-step-item').removeClass('cob-step-active cob-step-completed');
            
            for (let i = 1; i <= this.currentStep; i++) {
                if (i === this.currentStep) {
                    $(`.cob-step-item[data-step="${i}"]`).addClass('cob-step-active');
                } else {
                    $(`.cob-step-item[data-step="${i}"]`).addClass('cob-step-completed');
                }
            }
            
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
            
            // Update button visibility based on step
            this.updateButtonVisibility();
        }

        updateButtonVisibility() {
            const isFirstStep = this.currentStep === 1;
            const isLastStep = this.currentStep === 4;
            
            console.log('COB: Updating button visibility - Step:', this.currentStep, 'First:', isFirstStep, 'Last:', isLastStep);
            
            // Remove all visibility classes first
            $('#cob-previous-btn').removeClass('cob-visible');
            $('#cob-continue-btn').removeClass('cob-hidden');
            $('#cob-submit-btn').removeClass('cob-visible');
            
            // Previous button - show on all steps except first
            if (isFirstStep) {
                $('#cob-previous-btn').removeClass('cob-visible');
                console.log('COB: Previous button hidden (first step)');
            } else {
                $('#cob-previous-btn').addClass('cob-visible');
                console.log('COB: Previous button shown (not first step)');
            }
            
            // Continue button - show on all steps except last
            if (isLastStep) {
                $('#cob-continue-btn').addClass('cob-hidden');
                console.log('COB: Continue button hidden (last step)');
            } else {
                $('#cob-continue-btn').removeClass('cob-hidden');
                console.log('COB: Continue button shown (not last step)');
            }
            
            // Submit button - show only on last step
            if (isLastStep) {
                $('#cob-submit-btn').addClass('cob-visible');
                console.log('COB: Submit button shown (last step)');
            } else {
                $('#cob-submit-btn').removeClass('cob-visible');
                console.log('COB: Submit button hidden (not last step)');
            }
            
            // Also force with CSS to override any inline styles
            if (isFirstStep) {
                $('#cob-previous-btn').css('display', 'none !important');
            } else {
                $('#cob-previous-btn').css('display', 'inline-block !important');
            }
            
            if (isLastStep) {
                $('#cob-continue-btn').css('display', 'none !important');
                $('#cob-submit-btn').css('display', 'inline-block !important');
            } else {
                $('#cob-continue-btn').css('display', 'inline-block !important');
                $('#cob-submit-btn').css('display', 'none !important');
            }
        }

        validateCurrentStep() {
            const currentStepElement = $(`.cob-step-content[data-step="${this.currentStep}"]`);
            const requiredFields = currentStepElement.find('input[required], textarea[required], select[required]');
            let isValid = true;

            // Clear previous errors
            currentStepElement.find('.cob-error').removeClass('cob-error');
            currentStepElement.find('.cob-error-message').remove();

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

            // Validate checkbox arrays that require at least one selection
            const checkboxArrayFields = [
                'paid_media_history', 'current_paid_media', 'industry_entities', 'target_age_range'
            ];

            checkboxArrayFields.forEach(fieldName => {
                const $checkboxes = currentStepElement.find(`input[name="${fieldName}[]"]`);
                if ($checkboxes.length > 0) {
                    const checkedBoxes = $checkboxes.filter(':checked');
                    if (checkedBoxes.length === 0) {
                        // Show error on the first checkbox
                        this.showFieldError($checkboxes.first(), `Please select at least one option for ${fieldName.replace(/_/g, ' ')}`);
                        isValid = false;
                    }
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

                // Remove array brackets from field names for consistent handling
                const cleanName = name.replace(/\[\]$/, '');

                if ($field.attr('type') === 'checkbox') {
                    if (!formData[cleanName]) formData[cleanName] = [];
                    if ($field.is(':checked')) {
                        formData[cleanName].push($field.val());
                    }
                } else if ($field.attr('type') === 'radio') {
                    if ($field.is(':checked')) {
                        formData[cleanName] = $field.val();
                    }
                } else {
                    formData[cleanName] = $field.val();
                }
            });

            return formData;
        }

        setFormData(data) {
            Object.keys(data).forEach(name => {
                const value = data[name];
                
                // Try both with and without array brackets for field selection
                const $fields = $(`[name="${name}"], [name="${name}[]"]`);

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

            const saveWithRetry = (retryCount = 0, maxRetries = 3) => {
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
                                // Generate share token and ask if client wants to complete later
                                this.generateShareTokenAndAsk();
                            }
                            this.updateSaveStatus(data.last_saved);
                        } else if (data.retry_after && retryCount < maxRetries) {
                            // Handle deadlock retry
                            console.log(`Deadlock detected, retrying in ${data.retry_after} seconds... (attempt ${retryCount + 1}/${maxRetries})`);
                            
                            setTimeout(() => {
                                saveWithRetry(retryCount + 1, maxRetries);
                            }, data.retry_after * 1000);
                            
                            return; // Don't show error message yet
                        } else {
                            // Show error message
                            if (showMessage) {
                                alert(data.message || 'Failed to save draft. Please try again.');
                            }
                        }
                    } catch (e) {
                        console.error('Error parsing save response:', e);
                        if (showMessage) {
                            alert('Error processing response. Please try again.');
                        }
                    }
                }).fail((xhr, status, error) => {
                    console.error('Save draft failed:', status, error);
                    
                    // Retry on network errors
                    if (retryCount < maxRetries && (status === 'timeout' || status === 'error')) {
                        console.log(`Network error, retrying... (attempt ${retryCount + 1}/${maxRetries})`);
                        
                        setTimeout(() => {
                            saveWithRetry(retryCount + 1, maxRetries);
                        }, 1000 * (retryCount + 1)); // Exponential backoff
                        
                        return;
                    }
                    
                    if (showMessage) {
                        alert('Failed to save draft. Please try again.');
                    }
                }).always(() => {
                    if (showMessage) {
                        $('#cob-save-draft').removeClass('cob-loading').prop('disabled', false);
                    }
                });
            };

            return saveWithRetry();
        }

        generateShareTokenAndAsk() {
            // Generate share token via AJAX
            $.ajax({
                url: cob_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'cob_generate_share_token',
                    nonce: cob_ajax.nonce,
                    session_id: this.sessionId
                }
            }).done((response) => {
                try {
                    const data = typeof response === 'string' ? JSON.parse(response) : response;
                    if (data.success && data.token) {
                        console.log('COB: Share token generated successfully:', data.token);
                        this.askForCompletionLink(data.token);
                    } else {
                        console.error('COB: Failed to generate share token:', data.message);
                        // Fallback: ask without token
                        this.askForCompletionLink(this.sessionId);
                    }
                } catch (e) {
                    console.error('COB: Error generating share token:', e);
                    // Fallback: ask without token
                    this.askForCompletionLink(this.sessionId);
                }
            }).fail((xhr, status, error) => {
                console.error('COB: Failed to generate share token:', status, error);
                // Fallback: ask without token
                this.askForCompletionLink(this.sessionId);
            });
        }

        askForCompletionLink(sessionId) {
            const modal = $(`
                <div class="cob-completion-modal" style="
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0,0,0,0.8);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    z-index: 10000;
                ">
                    <div class="cob-modal-content" style="
                        background: #2a2a2a;
                        padding: 40px;
                        border-radius: 12px;
                        max-width: 500px;
                        text-align: center;
                        border: 1px solid #555;
                    ">
                        <h3 style="color: #9dff00; margin-bottom: 20px; font-size: 24px;">Draft Saved Successfully!</h3>
                        <p style="color: #fff; margin-bottom: 30px; line-height: 1.6;">
                            Your form progress has been saved. Would you like to complete the form later?
                        </p>
                        <div class="cob-modal-buttons" style="display: flex; gap: 15px; justify-content: center;">
                            <button class="cob-btn cob-btn-secondary" style="
                                background: #555;
                                color: #fff;
                                border: none;
                                padding: 12px 24px;
                                border-radius: 6px;
                                cursor: pointer;
                                font-weight: 600;
                            ">Continue Now</button>
                            <button class="cob-btn cob-btn-primary" style="
                                background: #9dff00;
                                color: #000;
                                border: none;
                                padding: 12px 24px;
                                border-radius: 6px;
                                cursor: pointer;
                                font-weight: 600;
                            ">Get Completion Link</button>
                        </div>
                    </div>
                </div>
            `);

            $('body').append(modal);

            // Handle button clicks
            modal.find('.cob-btn-secondary').on('click', () => {
                modal.remove();
            });

            modal.find('.cob-btn-primary').on('click', () => {
                this.generateCompletionLink(sessionId);
                modal.remove();
            });
        }

        generateCompletionLink(sessionId) {
            // Generate the completion link using the share token
            const currentUrl = window.location.origin + window.location.pathname;
            const completionLink = `${currentUrl}?cob_share=${sessionId}`;
            
            const linkModal = $(`
                <div class="cob-link-modal" style="
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0,0,0,0.8);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    z-index: 10000;
                ">
                    <div class="cob-modal-content" style="
                        background: #2a2a2a;
                        padding: 40px;
                        border-radius: 12px;
                        max-width: 600px;
                        text-align: center;
                        border: 1px solid #555;
                    ">
                        <h3 style="color: #9dff00; margin-bottom: 20px; font-size: 24px;">Your Completion Link</h3>
                        <p style="color: #fff; margin-bottom: 20px; line-height: 1.6;">
                            Use this link to return and complete your form later. The link will take you directly to where you left off.
                        </p>
                        <div class="cob-link-display" style="
                            background: #1a1a1a;
                            padding: 15px;
                            border-radius: 6px;
                            margin: 20px 0;
                            border: 1px solid #555;
                        ">
                            <input type="text" value="${completionLink}" readonly style="
                                width: 100%;
                                background: transparent;
                                border: none;
                                color: #9dff00;
                                font-family: monospace;
                                font-size: 14px;
                                text-align: center;
                            ">
                        </div>
                        <div class="cob-modal-buttons" style="display: flex; gap: 15px; justify-content: center;">
                            <button class="cob-btn cob-btn-copy" style="
                                background: #9dff00;
                                color: #000;
                                border: none;
                                padding: 12px 24px;
                                border-radius: 6px;
                                cursor: pointer;
                                font-weight: 600;
                            ">Copy Link</button>
                            <button class="cob-btn cob-btn-close" style="
                                background: #555;
                                color: #fff;
                                border: none;
                                padding: 12px 24px;
                                border-radius: 6px;
                                cursor: pointer;
                                font-weight: 600;
                            ">Close</button>
                        </div>
                    </div>
                </div>
            `);

            $('body').append(linkModal);

            // Handle copy button
            linkModal.find('.cob-btn-copy').on('click', () => {
                navigator.clipboard.writeText(completionLink).then(() => {
                    const copyBtn = linkModal.find('.cob-btn-copy');
                    const originalText = copyBtn.text();
                    copyBtn.text('Copied!').css('background', '#4CAF50');
                    setTimeout(() => {
                        copyBtn.text(originalText).css('background', '#9dff00');
                    }, 2000);
                });
            });

            // Handle close button
            linkModal.find('.cob-btn-close').on('click', () => {
                linkModal.remove();
            });
        }

        loadDraft() {
            // Check for shared draft token in URL
            const urlParams = new URLSearchParams(window.location.search);
            const shareToken = urlParams.get('cob_share');
            
            if (shareToken) {
                this.loadSharedDraft();
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
            
            // Debug logging for problematic fields
            const debugFields = ['paid_media_history', 'current_paid_media', 'industry_entities', 'target_age_range'];
            debugFields.forEach(field => {
                const value = formData[field];
                console.log(`COB Debug - Field: ${field}, Value:`, value, 'Type:', typeof value, 'Is Array:', Array.isArray(value));
            });

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

        showSaveStatus(message, type = 'success') {
            const statusElement = $('#cob-save-status');
            statusElement.removeClass('cob-error cob-success').addClass(type);
            statusElement.text(message);
            statusElement.show();
            setTimeout(() => {
                statusElement.fadeOut();
            }, 3000);
        }

        updateSaveStatus(timestamp) {
            if (timestamp) {
                const date = new Date(timestamp);
                $('#cob-save-text').text(`Last saved: ${date.toLocaleTimeString()}`);
                $('#cob-save-status').show();
            }
        }

        loadSharedDraft() {
            // Check for shared draft token in URL
            const urlParams = new URLSearchParams(window.location.search);
            const shareToken = urlParams.get('cob_share');
            
            if (shareToken) {
                console.log('COB: Loading shared draft with token:', shareToken);
                
                // Load the draft using the share token
                $.ajax({
                    url: cob_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'cob_load_shared_draft',
                        nonce: cob_ajax.nonce,
                        share_token: shareToken
                    }
                }).done((response) => {
                    try {
                        const data = typeof response === 'string' ? JSON.parse(response) : response;
                        
                        if (data.success && data.draft) {
                            console.log('COB: Shared draft loaded successfully:', data.draft);
                            
                            // Set the session ID from the shared draft
                            this.sessionId = data.draft.session_id;
                            
                            // Load the form data
                            this.loadFormData(data.draft.form_data);
                            
                            // Set the current step
                            this.currentStep = data.draft.current_step;
                            this.updateStepDisplay();
                            
                            // Show success message
                            this.showSaveStatus('Shared draft loaded successfully! You can continue from where you left off.');
                            
                            // Clean up the URL
                            const newUrl = window.location.pathname;
                            window.history.replaceState({}, document.title, newUrl);
                            
                        } else {
                            console.error('COB: Failed to load shared draft:', data.message);
                            this.showSaveStatus('Failed to load shared draft: ' + (data.message || 'Unknown error'), 'error');
                        }
                    } catch (e) {
                        console.error('COB: Error parsing shared draft response:', e);
                        this.showSaveStatus('Failed to load shared draft: Invalid response', 'error');
                    }
                }).fail((xhr, status, error) => {
                    console.error('COB: Failed to load shared draft:', status, error);
                    this.showSaveStatus('Failed to load shared draft: Network error', 'error');
                });
            }
        }

        loadFormData(formData) {
            // Populate form fields with the loaded data
            Object.keys(formData).forEach(fieldName => {
                const field = $(`[name="${fieldName}"]`);
                if (field.length) {
                    if (field.attr('type') === 'checkbox') {
                        field.prop('checked', formData[fieldName] === true || formData[fieldName] === '1');
                    } else if (field.attr('type') === 'radio') {
                        field.filter(`[value="${formData[fieldName]}"]`).prop('checked', true);
                    } else {
                        field.val(formData[fieldName]);
                    }
                }
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