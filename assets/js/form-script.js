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
            console.log('COB: Initializing ClientOnboardingForm...');
            console.log('COB: Start button element exists:', $('#cob-start-form').length);
            console.log('COB: Start page element exists:', $('#cob-start-page').length);
            console.log('COB: Form page element exists:', $('#cob-form-page').length);
            
            this.bindEvents();
            this.loadDraft();
            this.startAutoSave();
            this.updateStepDisplay();
            this.initSaveStatus();
            
            // Handle initial responsive behavior
            this.handleResize();
            
            // Ensure button visibility is correct on initialization
            setTimeout(() => {
                this.updateButtonVisibility();
                console.log('COB: Initial button visibility update completed');
            }, 100);
        }

        bindEvents() {
            // Start page button
            $('#cob-start-form').on('click', (e) => {
                console.log('COB: Start button clicked!');
                console.log('COB: Start button element:', $('#cob-start-form').length);
                console.log('COB: Event target:', e.target);
                this.showFormPage();
            });

            // Thank you page button
            $('#cob-visit-cx-platform').on('click', () => this.visitCXPlatform());

            // Step navigation
            $('.cob-step-item').on('click', (e) => {
                const step = parseInt($(e.currentTarget).data('step'));
                this.goToStep(step);
            });

            // Mobile tab navigation
            $('.cob-mobile-tab').on('click', (e) => {
                const step = parseInt($(e.currentTarget).data('step'));
                this.goToStep(step);
            });

            // Form navigation buttons
            $('#cob-continue-btn').on('click', () => this.nextStep());
            $('#cob-previous-btn').on('click', () => this.previousStep());
            $('#cob-submit-btn').on('click', (e) => {
                console.log('COB: Submit button clicked!');
                console.log('COB: Current step:', this.currentStep);
                console.log('COB: Total steps:', this.totalSteps);
                console.log('COB: Submit button element:', $('#cob-submit-btn').length);
                console.log('COB: Form element:', $('#cob-onboarding-form').length);
                e.preventDefault();
                this.submitForm();
            });

            // Header buttons
            $('#cob-save-draft').on('click', () => this.saveDraft(true));
            $('#cob-exit-form').on('click', () => this.exitForm());

            // Prevent default form submission and ensure our custom handler is used
            $('#cob-onboarding-form').on('submit', (e) => {
                console.log('COB: Form submit event triggered - preventing default');
                e.preventDefault();
                e.stopPropagation();
                this.submitForm();
                return false;
            });

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

            // Handle window resize for responsive behavior
            $(window).on('resize', () => this.handleResize());
        }

        bindConditionalFields() {
            // Handle radio button changes for conditional fields
            $('input[type="radio"]').on('change', (e) => {
                const name = $(e.target).attr('name');
                const value = $(e.target).val();
                
                console.log(`COB: Radio button changed - ${name}: ${value}`);
                this.toggleConditionalFields(name, value);
            });

            // Handle checkbox changes for conditional fields
            $('input[type="checkbox"]').on('change', (e) => {
                const name = $(e.target).attr('name');
                const isChecked = $(e.target).is(':checked');
                const value = $(e.target).val();
                
                console.log(`COB: Checkbox changed - ${name}: ${value}, checked: ${isChecked}`);
                
                // Check if this checkbox affects conditional fields
                if (value === 'other') {
                    this.toggleConditionalFields(name, isChecked ? 'other' : '');
                }
                
                // Handle checkbox arrays (multiple selections)
                this.handleCheckboxArrayChanges(name);
            });

            // Initialize conditional fields on page load
            this.initializeConditionalFields();
        }

        toggleConditionalFields(fieldName, fieldValue) {
            console.log(`COB: Toggling conditional fields for ${fieldName} = ${fieldValue}`);
            
            $(`.cob-conditional-fields[data-show-when="${fieldName}"]`).each((index, element) => {
                const $element = $(element);
                const showValue = $element.attr('data-show-value');
                
                if (fieldValue === showValue) {
                    $element.addClass('cob-conditional-active');
                    // Make required fields actually required when shown
                    $element.find('[required]').prop('required', true);
                    console.log(`COB: Showing conditional field for ${fieldName} = ${fieldValue}`);
                } else {
                    $element.removeClass('cob-conditional-active');
                    // Remove required attribute when hidden to prevent validation errors
                    $element.find('[required]').prop('required', false);
                    console.log(`COB: Hiding conditional field for ${fieldName} = ${fieldValue}`);
                }
            });
        }

        initializeConditionalFields() {
            console.log('COB: Initializing conditional fields');
            
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
            
            console.log('COB: Conditional fields initialization completed');
        }

        handleCheckboxArrayChanges(fieldName) {
            // Get all checked checkboxes for this field name
            const checkedValues = $(`input[name="${fieldName}"]:checked`).map(function() {
                return this.value;
            }).get();
            
            console.log(`COB: Checkbox array ${fieldName} values:`, checkedValues);
            
            // Check if "other" is selected
            if (checkedValues.includes('other')) {
                this.toggleConditionalFields(fieldName, 'other');
            } else {
                // If "other" is not selected, hide the conditional field
                this.toggleConditionalFields(fieldName, '');
            }
        }

        // Debug method - can be called from browser console
        debugButtonVisibility() {
            console.log('=== COB BUTTON VISIBILITY DEBUG ===');
            console.log('Current Step:', this.currentStep);
            console.log('Total Steps:', this.totalSteps);
            console.log('Is First Step:', this.currentStep === 1);
            console.log('Is Last Step:', this.currentStep === 4);
            console.log('Submit Button Element:', $('#cob-submit-btn').length);
            console.log('Submit Button Classes:', $('#cob-submit-btn').attr('class'));
            console.log('Submit Button Display:', $('#cob-submit-btn').css('display'));
            console.log('Submit Button Computed Display:', window.getComputedStyle($('#cob-submit-btn')[0]).display);
            console.log('=== END DEBUG ===');
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
            
            // Update mobile tabs
            this.updateMobileTabs();
            
            // Update button visibility based on step
            this.updateButtonVisibility();
            
            // Re-initialize conditional fields for the current step
            setTimeout(() => {
                this.initializeConditionalFields();
            }, 100);
        }

        updateMobileTabs() {
            // Update mobile tab states
            $('.cob-mobile-tab').removeClass('active completed');
            
            for (let i = 1; i <= this.currentStep; i++) {
                if (i === this.currentStep) {
                    $(`.cob-mobile-tab[data-step="${i}"]`).addClass('active');
                } else {
                    $(`.cob-mobile-tab[data-step="${i}"]`).addClass('completed');
                }
            }
            
            // Update progress bar
            const progressPercentage = (this.currentStep / this.totalSteps) * 100;
            $('.cob-mobile-progress-fill').css('width', progressPercentage + '%');
        }

        updateButtonVisibility() {
            const isFirstStep = this.currentStep === 1;
            const isLastStep = this.currentStep === 4;
            
            console.log('COB: Updating button visibility - Step:', this.currentStep, 'First:', isFirstStep, 'Last:', isLastStep);
            console.log('COB: Current step element:', $(`.cob-step-content[data-step="${this.currentStep}"]`).length);
            console.log('COB: Submit button element:', $('#cob-submit-btn').length);
            
            // Remove all visibility classes first
            $('#cob-previous-btn').removeClass('cob-visible cob-hidden');
            $('#cob-continue-btn').removeClass('cob-visible cob-hidden');
            $('#cob-submit-btn').removeClass('cob-visible cob-hidden');
            
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
            
            // Force display with CSS classes (more reliable than inline styles)
            if (isFirstStep) {
                $('#cob-previous-btn').removeClass('cob-visible').addClass('cob-hidden');
            } else {
                $('#cob-previous-btn').removeClass('cob-hidden').addClass('cob-visible');
            }
            
            if (isLastStep) {
                $('#cob-continue-btn').removeClass('cob-visible').addClass('cob-hidden');
                $('#cob-submit-btn').removeClass('cob-hidden').addClass('cob-visible');
            } else {
                $('#cob-continue-btn').removeClass('cob-hidden').addClass('cob-visible');
                $('#cob-submit-btn').removeClass('cob-visible').addClass('cob-hidden');
            }
        }

        validateCurrentStep() {
            console.log(`COB: Validating step ${this.currentStep}`);
            const currentStepElement = $(`.cob-step-content[data-step="${this.currentStep}"]`);
            console.log(`COB: Current step element found:`, currentStepElement.length);
            
            const requiredFields = currentStepElement.find('input[required], textarea[required], select[required]');
            console.log(`COB: Required fields found:`, requiredFields.length);
            
            let isValid = true;

            // Clear previous errors
            currentStepElement.find('.cob-error').removeClass('cob-error');
            currentStepElement.find('.cob-error-message').remove();

            requiredFields.each((index, field) => {
                const $field = $(field);
                const fieldName = $field.attr('name');
                const fieldType = $field.attr('type');
                const value = $field.val();
                const trimmedValue = value ? value.trim() : '';
                
                console.log(`COB: Validating field: ${fieldName}, type: ${fieldType}, value: "${value}", trimmed: "${trimmedValue}"`);

                if (!trimmedValue) {
                    console.log(`COB: Field ${fieldName} is empty - validation failed`);
                    this.showFieldError($field, 'This field is required');
                    isValid = false;
                } else if (fieldType === 'email' && !this.isValidEmail(trimmedValue)) {
                    console.log(`COB: Field ${fieldName} has invalid email format - validation failed`);
                    this.showFieldError($field, 'Please enter a valid email address');
                    isValid = false;
                } else if (fieldType === 'url' && !this.isValidUrl(trimmedValue)) {
                    console.log(`COB: Field ${fieldName} has invalid URL format - validation failed`);
                    this.showFieldError($field, 'Please enter a valid URL');
                    isValid = false;
                } else {
                    console.log(`COB: Field ${fieldName} passed validation`);
                }
            });

            // Validate checkbox arrays that require at least one selection
            const checkboxArrayFields = [
                'paid_media_history', 'current_paid_media', 'industry_entities', 'target_age_range'
            ];

            console.log(`COB: Checking checkbox array fields:`, checkboxArrayFields);

            checkboxArrayFields.forEach(fieldName => {
                const $checkboxes = currentStepElement.find(`input[name="${fieldName}[]"]`);
                console.log(`COB: Checkbox array ${fieldName}: found ${$checkboxes.length} checkboxes`);
                
                if ($checkboxes.length > 0) {
                    const checkedBoxes = $checkboxes.filter(':checked');
                    console.log(`COB: Checkbox array ${fieldName}: ${checkedBoxes.length} checked out of ${$checkboxes.length}`);
                    
                    if (checkedBoxes.length === 0) {
                        console.log(`COB: Checkbox array ${fieldName} has no selections - validation failed`);
                        // Show error on the first checkbox
                        this.showFieldError($checkboxes.first(), `Please select at least one option for ${fieldName.replace(/_/g, ' ')}`);
                        isValid = false;
                    } else {
                        console.log(`COB: Checkbox array ${fieldName} passed validation`);
                    }
                } else {
                    console.log(`COB: Checkbox array ${fieldName} not found in current step`);
                }
            });

            console.log(`COB: Step ${this.currentStep} validation result:`, isValid ? 'PASSED' : 'FAILED');
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
            
            console.log('COB: Starting form data collection...');
            console.log('COB: Total form elements found:', $('#cob-onboarding-form').find('input, textarea, select').length);
            
            $('#cob-onboarding-form').find('input, textarea, select').each((index, field) => {
                try {
                    const $field = $(field);
                    const name = $field.attr('name');
                    
                    if (!name) {
                        console.log('COB: Field has no name, skipping');
                        return;
                    }

                    // Remove array brackets from field names for consistent handling
                    const cleanName = name.replace(/\[\]$/, '');
                    const isArrayField = name.endsWith('[]');
                    
                    console.log(`COB: Processing field: ${name}, cleanName: ${cleanName}, isArrayField: ${isArrayField}`);

                    if ($field.attr('type') === 'file') {
                        // Handle file uploads - only include if file is actually selected
                        const fileInput = $field[0];
                        if (fileInput.files && fileInput.files.length > 0) {
                            console.log(`COB: File field: ${cleanName}, file: ${fileInput.files[0].name}`);
                            formData[cleanName] = fileInput.files[0];
                        } else {
                            console.log(`COB: File field: ${cleanName}, no file selected - skipping`);
                            // Don't add file fields with no selection to avoid serialization issues
                        }
                    } else if ($field.attr('type') === 'checkbox') {
                        // Only create arrays for fields that are explicitly marked as arrays
                        if (isArrayField) {
                            console.log(`COB: Array checkbox field: ${cleanName}`);
                            // Ensure the field is always initialized as an array
                            if (!Array.isArray(formData[cleanName])) {
                                formData[cleanName] = [];
                                console.log(`COB: Initialized array for ${cleanName}`);
                            }
                            if ($field.is(':checked')) {
                                console.log(`COB: Adding ${$field.val()} to ${cleanName} array`);
                                // Double-check it's an array before pushing
                                if (Array.isArray(formData[cleanName])) {
                                    formData[cleanName].push($field.val());
                                } else {
                                    console.error(`COB: ERROR - ${cleanName} is not an array! Type: ${typeof formData[cleanName]}`);
                                    // Force it to be an array and add the value
                                    formData[cleanName] = [$field.val()];
                                }
                            }
                        } else {
                            // Single checkbox (boolean)
                            console.log(`COB: Single checkbox field: ${cleanName}, checked: ${$field.is(':checked')}`);
                            formData[cleanName] = $field.is(':checked');
                        }
                    } else if ($field.attr('type') === 'radio') {
                        if ($field.is(':checked')) {
                            console.log(`COB: Radio field: ${cleanName}, value: ${$field.val()}`);
                            formData[cleanName] = $field.val();
                        }
                    } else {
                        console.log(`COB: Other field: ${cleanName}, value: ${$field.val()}`);
                        formData[cleanName] = $field.val();
                    }
                } catch (error) {
                    console.error(`COB: Error processing field ${name || 'unknown'}:`, error);
                    console.error(`COB: Field details:`, { field, name, cleanName, isArrayField });
                }
            });

            // Debug logging for form data
            console.log('COB: Form data collection completed:', formData);
            
            // Additional debugging - check specific required fields
            const requiredFields = [
                'project_name', 'business_name', 'current_website', 'primary_contact_name', 
                'primary_contact_email', 'primary_contact_number', 'main_approver',
                'billing_email', 'preferred_contact_method', 'address_line_1',
                'city', 'country', 'postal_code', 'has_website', 'has_google_analytics', 
                'has_search_console', 'reporting_frequency', 'main_objective', 
                'business_description', 'target_audience', 'main_competitors',
                'unique_value_proposition', 'marketing_budget', 'start_timeline', 
                'brand_guidelines_upload_radio', 'communication_tone_radio', 
                'brand_accounts_radio', 'industry_entities', 'market_insights_radio', 
                'content_social_media_radio', 'business_focus_elements_radio',
                'target_age_range', 'gender_purchase_decision', 'lead_source_markets', 
                'lead_times', 'marketing_goals', 'industry', 'paid_media_history', 'current_paid_media'
            ];
            
            console.log('COB: Checking required fields...');
            requiredFields.forEach(field => {
                const value = formData[field];
                if (value === undefined || value === '' || (Array.isArray(value) && value.length === 0)) {
                    console.warn(`COB: Missing required field: ${field}`);
                } else {
                    console.log(`COB: Field ${field}:`, value);
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
                this.saveDraft().done((response) => {
                    // Update save status even for auto-saves
                    if (response && response.last_saved) {
                        this.updateSaveStatus(response.last_saved);
                    } else {
                        this.updateSaveStatus();
                    }
                });
            }, 30000);
        }

        stopAutoSave() {
            if (this.autoSaveInterval) {
                clearInterval(this.autoSaveInterval);
                this.autoSaveInterval = null;
            }
        }

        submitForm() {
            console.log('=== COB FORM SUBMISSION DEBUG START ===');
            console.log('COB: Form submission started');
            console.log('COB: Current step:', this.currentStep);
            console.log('COB: Total steps:', this.totalSteps);
            console.log('COB: Session ID:', this.sessionId);
            console.log('COB: Is submitting flag:', this.isSubmitting);
            
            if (this.isSubmitting) {
                console.log('COB: Form already submitting, returning');
                return;
            }

            console.log('COB: Validating current step...');
            if (!this.validateCurrentStep()) {
                console.log('COB: Step validation failed');
                console.log('COB: Validation errors found, stopping submission');
                return;
            }

            console.log('COB: Step validation passed, proceeding with submission');
            this.isSubmitting = true;
            $('#cob-submit-btn').addClass('cob-loading').prop('disabled', true).text('SUBMITTING...');

            const formData = this.getFormData();
            console.log('COB: Raw form data collected:', formData);
            
            // Debug logging for problematic fields
            const debugFields = ['paid_media_history', 'current_paid_media', 'industry_entities', 'target_age_range'];
            debugFields.forEach(field => {
                const value = formData[field];
                console.log(`COB Debug - Field: ${field}, Value:`, value, 'Type:', typeof value, 'Is Array:', Array.isArray(value));
            });

            // Check if required fields are present
            const requiredFields = ['project_name', 'business_name', 'primary_contact_name', 'primary_contact_email', 'primary_contact_number', 'main_approver', 'billing_email', 'preferred_contact_method', 'address_line_1', 'city', 'country', 'postal_code', 'has_website', 'has_google_analytics', 'has_search_console', 'reporting_frequency', 'main_objective', 'business_description', 'target_audience', 'main_competitors', 'unique_value_proposition', 'marketing_budget', 'start_timeline'];
            console.log('COB: Checking required fields...');
            requiredFields.forEach(field => {
                const value = formData[field];
                if (!value || value === '') {
                    console.warn(`COB: Missing required field: ${field}`);
                } else {
                    console.log(`COB: Required field ${field}:`, value);
                }
            });

            // Check if there are file uploads
            const hasFileUploads = Object.values(formData).some(value => value instanceof File);
            console.log('COB: Has file uploads:', hasFileUploads);

            if (hasFileUploads) {
                // Use FormData for file uploads
                console.log('COB: Using FormData for file uploads');
                const formDataObj = new FormData();
                formDataObj.append('action', 'cob_submit_form');
                formDataObj.append('nonce', cob_ajax.nonce);
                formDataObj.append('session_id', this.sessionId);
                
                // Add form data
                Object.keys(formData).forEach(key => {
                    const value = formData[key];
                    if (value instanceof File) {
                        formDataObj.append(key, value);
                        console.log(`COB: Added file: ${key} = ${value.name}`);
                    } else if (Array.isArray(value)) {
                        value.forEach(item => {
                            formDataObj.append(key + '[]', item);
                        });
                        console.log(`COB: Added array: ${key}[] = ${value.join(', ')}`);
                    } else {
                        formDataObj.append(key, value);
                        console.log(`COB: Added field: ${key} = ${value}`);
                    }
                });

                console.log('COB: FormData prepared for file uploads');

                $.ajax({
                    url: cob_ajax.ajax_url,
                    type: 'POST',
                    data: formDataObj,
                    processData: false,
                    contentType: false
                }).done((response) => {
                    console.log('COB: File upload AJAX response received:', response);
                    this.handleSubmitResponse(response);
                }).fail((xhr, status, error) => {
                    console.error('COB: File upload AJAX request failed:', {xhr, status, error});
                    this.handleSubmitError(xhr, status, error);
                }).always(() => {
                    console.log('COB: File upload AJAX request completed (always block)');
                    this.isSubmitting = false;
                    $('#cob-submit-btn').removeClass('cob-loading').prop('disabled', false).text('SUBMIT');
                });
            } else {
                // Use regular AJAX for non-file submissions
                console.log('COB: Using regular AJAX (no file uploads)');
                console.log('COB: AJAX request data:', {
                    url: cob_ajax.ajax_url,
                    action: 'cob_submit_form',
                    nonce: cob_ajax.nonce,
                    session_id: this.sessionId,
                    form_data: formData
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
                    console.log('COB: Regular AJAX response received:', response);
                    this.handleSubmitResponse(response);
                }).fail((xhr, status, error) => {
                    console.error('COB: Regular AJAX request failed:', {xhr, status, error});
                    this.handleSubmitError(xhr, status, error);
                }).always(() => {
                    console.log('COB: Regular AJAX request completed (always block)');
                    this.isSubmitting = false;
                    $('#cob-submit-btn').removeClass('cob-loading').prop('disabled', false).text('SUBMIT FORM');
                    console.log('=== COB FORM SUBMISSION DEBUG END ===');
                });
            }
        }

        showSuccessMessage(submissionId) {
            // Show thank you page instead of inline success message
            this.showThankYouPage();
        }

        showFormPage() {
            console.log('COB: showFormPage method called');
            console.log('COB: Start page element:', $('#cob-start-page').length);
            console.log('COB: Form page element:', $('#cob-form-page').length);
            
            // Hide start page and show form page
            $('#cob-start-page').removeClass('cob-page-active').hide();
            $('#cob-form-page').addClass('cob-page-active').show();
            
            console.log('COB: Page transition completed');
        }

        showThankYouPage() {
            // Hide the form and show thank you page
            $('.cob-form-wrapper').hide();
            $('#cob-thank-you-page').addClass('cob-page-active').show();
        }

        visitCXPlatform() {
            // Open CX platform in new tab/window
            window.open('https://fluxcx.com', '_blank');
        }

        showSaveStatus(message, type = 'success') {
            const statusElement = $('#cob-save-status');
            statusElement.removeClass('cob-error cob-success').addClass(type);
            statusElement.text(message);
            statusElement.show();
            
            // Update the header save status with current time
            this.updateSaveStatus();
            
            setTimeout(() => {
                statusElement.fadeOut();
            }, 3000);
        }

        updateSaveStatus(timestamp) {
            if (timestamp) {
                const date = new Date(timestamp);
                $('#cob-save-text').text(`Last saved: ${date.toLocaleTimeString()}`);
            } else {
                // Show current time if no timestamp provided
                const now = new Date();
                $('#cob-save-text').text(`Last saved: ${now.toLocaleTimeString()}`);
            }
            $('#cob-save-status').show();
        }

        // Initialize save status on page load
        initSaveStatus() {
            // Show current time as initial save status
            this.updateSaveStatus();
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

        handleResize() {
            // Handle responsive behavior
            const windowWidth = $(window).width();
            
            if (windowWidth <= 768) {
                // Mobile view - ensure mobile tabs are visible
                $('.cob-mobile-tabs').show();
                $('.cob-step-navigation').hide();
            } else {
                // Desktop view - ensure desktop navigation is visible
                $('.cob-mobile-tabs').hide();
                $('.cob-step-navigation').show();
            }
        }

        /**
         * Handle form submission response
         */
        handleSubmitResponse(response) {
            console.log('COB: Handling submit response:', response);
            console.log('COB: Response type:', typeof response);
            console.log('COB: Response length:', response ? response.length : 'N/A');
            
            try {
                const data = typeof response === 'string' ? JSON.parse(response) : response;
                console.log('COB: Parsed response data:', data);
                console.log('COB: Response success flag:', data.success);
                console.log('COB: Response message:', data.message);
                console.log('COB: Response submission ID:', data.submission_id);
                
                if (data.success) {
                    console.log('COB: Form submission successful, submission ID:', data.submission_id);
                    this.stopAutoSave();
                    this.showSuccessMessage(data.submission_id);
                } else {
                    console.log('COB: Form submission failed:', data.message);
                    console.log('COB: Full error response:', data);
                    alert(data.message || cob_ajax.messages.submit_error);
                }
            } catch (e) {
                console.error('COB: Error parsing submit response:', e);
                console.error('COB: Raw response that failed to parse:', response);
                alert(cob_ajax.messages.submit_error);
            }
        }

        /**
         * Handle form submission error
         */
        handleSubmitError(xhr, status, error) {
            console.error('COB: AJAX request failed:', {xhr, status, error});
            console.error('COB: XHR status:', xhr.status);
            console.error('COB: XHR status text:', xhr.statusText);
            console.error('COB: XHR response text:', xhr.responseText);
            console.error('COB: Error details:', error);
            alert(cob_ajax.messages.submit_error);
        }
    }

    // Initialize when document is ready
    $(document).ready(() => {
        new ClientOnboardingForm();
    });

})(jQuery);