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
            
            // Delay loading draft to ensure AJAX object is available
            setTimeout(() => {
                this.loadDraft();
            }, 500);
            
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
            
            // Initialize progress display
            this.updateProgressDisplay();
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
                                // Show the enhanced save draft modal
                                this.showSaveDraftModal();
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
                                this.showSaveStatus('Failed to save draft. Please try again.', 'error');
                            }
                        }
                    } catch (e) {
                        console.error('Error parsing save response:', e);
                        if (showMessage) {
                            this.showSaveStatus('Error processing response. Please try again.', 'error');
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
                        this.showSaveStatus('Failed to save draft. Please try again.', 'error');
                    }
                }).always(() => {
                    if (showMessage) {
                        $('#cob-save-draft').removeClass('cob-loading').prop('disabled', false);
                    }
                });
            };

            return saveWithRetry();
        }

        showSaveDraftModal() {
            // Remove any existing modals
            $('.cob-save-draft-modal').remove();
            
            const modal = $(`
                <div class="cob-save-draft-modal" style="
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0,0,0,0.85);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    z-index: 10000;
                    backdrop-filter: blur(5px);
                ">
                    <div class="cob-modal-content" style="
                        background: linear-gradient(135deg, #2a2a2a 0%, #1a1a1a 100%);
                        padding: 40px;
                        border-radius: 16px;
                        max-width: 600px;
                        width: 90%;
                        text-align: center;
                        border: 2px solid #d5FD72;
                        box-shadow: 0 20px 40px rgba(0,0,0,0.5);
                        position: relative;
                        overflow: hidden;
                    ">
                        <div style="
                            position: absolute;
                            top: 0;
                            left: 0;
                            right: 0;
                            height: 4px;
                            background: linear-gradient(90deg, #d5FD72, #00ff9d, #d5FD72);
                            background-size: 200% 100%;
                            animation: shimmer 2s infinite;
                        "></div>
                        
                        <div style="margin-bottom: 30px;">
                            <div style="
                                width: 80px;
                                height: 80px;
                                background: #d5FD72;
                                border-radius: 50%;
                                margin: 0 auto 20px;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                animation: pulse 2s infinite;
                            ">
                                <svg width="40" height="40" viewBox="0 0 24 24" fill="none">
                                    <path d="M9 12l2 2 4-4" stroke="#000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>
                            <h3 style="color: #d5FD72; margin-bottom: 15px; font-size: 28px; font-weight: 700;">Draft Saved Successfully!</h3>
                            <p style="color: #fff; margin-bottom: 25px; line-height: 1.6; font-size: 16px;">
                                Your form progress has been securely saved. You can now:
                            </p>
                        </div>
                        
                        <div style="
                            background: rgba(255,255,255,0.05);
                            border-radius: 12px;
                            padding: 20px;
                            margin-bottom: 30px;
                            border: 1px solid rgba(157,255,0,0.3);
                        ">
                            <div style="display: flex; align-items: center; margin-bottom: 15px;">
                                <div style="
                                    width: 24px;
                                    height: 24px;
                                    background: #d5FD72;
                                    border-radius: 50%;
                                    margin-right: 15px;
                                    display: flex;
                                    align-items: center;
                                    justify-content: center;
                                ">
                                    <span style="color: #000; font-weight: bold; font-size: 12px;">1</span>
                                </div>
                                <span style="color: #fff; font-weight: 600;">Continue filling out the form now</span>
                            </div>
                            <div style="display: flex; align-items: center; margin-bottom: 15px;">
                                <div style="
                                    width: 24px;
                                    height: 24px;
                                    background: #d5FD72;
                                    border-radius: 50%;
                                    margin-right: 15px;
                                    display: flex;
                                    align-items: center;
                                    justify-content: center;
                                ">
                                    <span style="color: #000; font-weight: bold; font-size: 12px;">2</span>
                                </div>
                                <span style="color: #fff; font-weight: 600;">Get a completion link to finish later</span>
                            </div>
                            <div style="display: flex; align-items: center;">
                                <div style="
                                    width: 24px;
                                    height: 24px;
                                    background: #d5FD72;
                                    border-radius: 50%;
                                    margin-right: 15px;
                                    display: flex;
                                    align-items: center;
                                    justify-content: center;
                                ">
                                    <span style="color: #000; font-weight: bold; font-size: 12px;">3</span>
                                </div>
                                <span style="color: #fff; font-weight: 600;">Send the link to your email</span>
                            </div>
                        </div>
                        
                        <div class="cob-modal-buttons" style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
                            <button class="cob-btn cob-btn-secondary" style="
                                background: rgba(255,255,255,0.1);
                                color: #fff;
                                border: 2px solid rgba(255,255,255,0.3);
                                padding: 15px 30px;
                                border-radius: 8px;
                                cursor: pointer;
                                font-weight: 600;
                                font-size: 16px;
                                transition: all 0.3s ease;
                                min-width: 140px;
                            ">Continue Now</button>
                            <button class="cob-btn cob-btn-primary" style="
                                background: #d5FD72;
                                color: #000;
                                border: none;
                                padding: 15px 30px;
                                border-radius: 8px;
                                cursor: pointer;
                                font-weight: 700;
                                font-size: 16px;
                                transition: all 0.3s ease;
                                min-width: 140px;
                                box-shadow: 0 4px 15px rgba(213,253,114,0.3);
                            ">Get Completion Link</button>
                            <button class="cob-btn cob-btn-email" style="
                                background: linear-gradient(135deg, #ff6b6b 0%, #ff8e53 100%);
                                color: #fff;
                                border: none;
                                padding: 15px 30px;
                                border-radius: 8px;
                                cursor: pointer;
                                font-weight: 700;
                                font-size: 16px;
                                transition: all 0.3s ease;
                                min-width: 140px;
                                box-shadow: 0 4px 15px rgba(255,107,107,0.3);
                            ">Send to Email</button>
                        </div>
                        
                        <button class="cob-modal-close" style="
                            position: absolute;
                            top: 15px;
                            right: 15px;
                            background: rgba(255,255,255,0.1);
                            border: none;
                            color: #fff;
                            width: 30px;
                            height: 30px;
                            border-radius: 50%;
                            cursor: pointer;
                            font-size: 18px;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            transition: all 0.3s ease;
                        ">×</button>
                    </div>
                </div>
            `);

            $('body').append(modal);

            // Add CSS animations
            $('<style>')
                .prop('type', 'text/css')
                .html(`
                    @keyframes shimmer {
                        0% { background-position: 200% 0; }
                        100% { background-position: -200% 0; }
                    }
                    @keyframes pulse {
                        0%, 100% { transform: scale(1); }
                        50% { transform: scale(1.05); }
                    }
                    .cob-btn:hover {
                        transform: translateY(-2px);
                        box-shadow: 0 6px 20px rgba(0,0,0,0.3);
                    }
                    .cob-btn:active {
                        transform: translateY(0);
                    }
                `)
                .appendTo('head');

            // Handle button clicks
            modal.find('.cob-btn-secondary').on('click', () => {
                modal.fadeOut(300, () => modal.remove());
            });

            modal.find('.cob-btn-primary').on('click', () => {
                this.generateCompletionLink();
            });

            modal.find('.cob-btn-email').on('click', () => {
                this.showEmailModal();
            });

            modal.find('.cob-modal-close').on('click', () => {
                modal.fadeOut(300, () => modal.remove());
            });

            // Close modal on outside click
            modal.on('click', (e) => {
                if (e.target === modal[0]) {
                    modal.fadeOut(300, () => modal.remove());
                }
            });

            // Auto-close after 30 seconds
            setTimeout(() => {
                if (modal.is(':visible')) {
                    modal.fadeOut(300, () => modal.remove());
                }
            }, 30000);
        }

        generateCompletionLink() {
            console.log('COB: generateCompletionLink called with session ID:', this.sessionId);
            
            // Remove any existing link modals
            $('.cob-link-modal').remove();
            
            // Generate share token first
            $.ajax({
                url: cob_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'cob_generate_share_token',
                    nonce: cob_ajax.nonce,
                    session_id: this.sessionId
                }
            }).done((response) => {
                console.log('COB: Raw AJAX response for share token generation:', response);
                try {
                    const data = typeof response === 'string' ? JSON.parse(response) : response;
                    console.log('COB: Parsed response data for share token generation:', data);
                    
                    if (data.success && (data.token || (data.data && data.data.token))) {
                        const token = data.token || data.data.token;
                        console.log('COB: Share token generated successfully:', token);
                        this.showCompletionLinkModal(token);
                    } else {
                        console.error('COB: Failed to generate share token. Response data:', data);
                        const errorMessage = data.message || (data.data && typeof data.data === 'string' ? data.data : 'Unknown error occurred');
                        console.error('COB: Error message:', errorMessage);
                        this.showSaveStatus('Failed to generate completion link: ' + errorMessage, 'error');
                    }
                } catch (e) {
                    console.error('Error generating share token:', e);
                    this.showSaveStatus('Error generating completion link. Please try again.', 'error');
                }
            }).fail((xhr, status, error) => {
                console.error('Failed to generate share token:', status, error);
                console.error('COB: XHR response for share token generation:', xhr.responseText);
                console.error('COB: XHR status for share token generation:', xhr.status);
                this.showSaveStatus('Failed to generate completion link. Please try again.', 'error');
            });
        }

        showCompletionLinkModal(token) {
            const currentUrl = window.location.origin + window.location.pathname;
            const completionLink = `${currentUrl}?cob_share=${token}`;
            
            console.log('COB: Generated completion link:', completionLink);
            console.log('COB: Using token:', token);
            
            const linkModal = $(`
                <div class="cob-link-modal" style="
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0,0,0,0.9);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    z-index: 10001;
                    backdrop-filter: blur(10px);
                ">
                    <div class="cob-modal-content" style="
                        background: linear-gradient(135deg, #1a1a1a 0%, #2a2a2a 100%);
                        padding: 40px;
                        border-radius: 16px;
                        max-width: 700px;
                        width: 90%;
                        text-align: center;
                        border: 2px solid #d5FD72;
                        box-shadow: 0 25px 50px rgba(0,0,0,0.7);
                        position: relative;
                    ">
                        <div style="
                            position: absolute;
                            top: 0;
                            left: 0;
                            right: 0;
                            height: 4px;
                            background: linear-gradient(90deg, #d5FD72, #00ff9d, #d5FD72);
                            background-size: 200% 100%;
                            animation: shimmer 2s infinite;
                        "></div>
                        
                        <div style="margin-bottom: 30px;">
                            <div style="
                                width: 60px;
                                height: 60px;
                                background: #d5FD72;
                                border-radius: 50%;
                                margin: 0 auto 20px;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                            ">
                                <svg width="30" height="30" viewBox="0 0 24 24" fill="none">
                                    <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71" stroke="#000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71" stroke="#000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>
                            <h3 style="color: #d5FD72; margin-bottom: 15px; font-size: 26px; font-weight: 700;">Your Completion Link</h3>
                            <p style="color: #fff; margin-bottom: 25px; line-height: 1.6; font-size: 16px;">
                                Use this secure link to return and complete your form later. The link will take you directly to where you left off.
                            </p>
                        </div>
                        
                        <div class="cob-link-display" style="
                            background: rgba(0,0,0,0.3);
                            padding: 20px;
                            border-radius: 12px;
                            margin: 25px 0;
                            border: 2px solid rgba(157,255,0,0.3);
                            position: relative;
                        ">
                            <div style="
                                position: absolute;
                                top: -10px;
                                left: 20px;
                                background: #1a1a1a;
                                padding: 0 10px;
                                color: #d5FD72;
                                font-size: 12px;
                                font-weight: 600;
                                text-transform: uppercase;
                            ">Completion Link</div>
                            <input type="text" value="${completionLink}" readonly style="
                                width: 100%;
                                background: transparent;
                                border: none;
                                color: #d5FD72;
                                font-family: 'Courier New', monospace;
                                font-size: 14px;
                                text-align: center;
                                padding: 10px;
                                outline: none;
                            ">
                        </div>
                        
                        <div style="
                            background: rgba(157,255,0,0.1);
                            border-radius: 8px;
                            padding: 15px;
                            margin: 20px 0;
                            border: 1px solid rgba(157,255,0,0.2);
                        ">
                            <p style="color: #d5FD72; margin: 0; font-size: 14px; font-weight: 600;">
                                💡 <strong>Pro Tip:</strong> Save this link in your bookmarks or send it to your email for easy access later!
                            </p>
                        </div>
                        
                        <div class="cob-modal-buttons" style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
                            <button class="cob-btn cob-btn-copy" style="
                                background: #d5FD72;
                                color: #000;
                                border: none;
                                padding: 15px 30px;
                                border-radius: 8px;
                                cursor: pointer;
                                font-weight: 700;
                                font-size: 16px;
                                transition: all 0.3s ease;
                                min-width: 140px;
                                box-shadow: 0 4px 15px rgba(213,253,114,0.3);
                            ">Copy Link</button>
                            <button class="cob-btn cob-btn-email" style="
                                background: linear-gradient(135deg, #ff6b6b 0%, #ff8e53 100%);
                                color: #fff;
                                border: none;
                                padding: 15px 30px;
                                border-radius: 8px;
                                cursor: pointer;
                                font-weight: 700;
                                font-size: 16px;
                                transition: all 0.3s ease;
                                min-width: 140px;
                                box-shadow: 0 4px 15px rgba(255,107,107,0.3);
                            ">Send to Email</button>
                            <button class="cob-btn cob-btn-close" style="
                                background: rgba(255,255,255,0.1);
                                color: #fff;
                                border: 2px solid rgba(255,255,255,0.3);
                                padding: 15px 30px;
                                border-radius: 8px;
                                cursor: pointer;
                                font-weight: 600;
                                font-size: 16px;
                                transition: all 0.3s ease;
                                min-width: 140px;
                            ">Close</button>
                        </div>
                        
                        <button class="cob-modal-close" style="
                            position: absolute;
                            top: 15px;
                            right: 15px;
                            background: rgba(255,255,255,0.1);
                            border: none;
                            color: #fff;
                            width: 30px;
                            height: 30px;
                            border-radius: 50%;
                            cursor: pointer;
                            font-size: 18px;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            transition: all 0.3s ease;
                        ">×</button>
                    </div>
                </div>
            `);

            $('body').append(linkModal);

            // Handle copy button
            linkModal.find('.cob-btn-copy').on('click', () => {
                console.log('COB: Copy button clicked, link to copy:', completionLink);
                
                const copyBtn = linkModal.find('.cob-btn-copy');
                const originalText = copyBtn.text();
                const originalBackground = copyBtn.css('background');
                
                // Try modern clipboard API first
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    console.log('COB: Using modern clipboard API');
                    navigator.clipboard.writeText(completionLink).then(() => {
                        console.log('COB: Clipboard API success');
                        // Show success feedback
                        copyBtn.text('✅ Copied!').css({
                            'background': 'linear-gradient(135deg, #4CAF50 0%, #45a049 100%)',
                            'color': '#fff'
                        });
                        
                        // Show tooltip
                        this.showCopyTooltip(linkModal, 'Link copied to clipboard!');
                        
                        // Reset button after 3 seconds
                        setTimeout(() => {
                            copyBtn.text(originalText).css({
                                'background': originalBackground,
                                'color': '#000'
                            });
                        }, 3000);
                    }).catch((err) => {
                        console.error('COB: Clipboard API failed:', err);
                        this.fallbackCopyToClipboard(completionLink, copyBtn, originalText, originalBackground, linkModal);
                    });
                } else {
                    console.log('COB: Using fallback copy method');
                    // Fallback for older browsers
                    this.fallbackCopyToClipboard(completionLink, copyBtn, originalText, originalBackground, linkModal);
                }
            });

            // Handle email button
            linkModal.find('.cob-btn-email').on('click', () => {
                linkModal.remove();
                this.showEmailModal(completionLink);
            });

            // Handle close button
            linkModal.find('.cob-btn-close').on('click', () => {
                linkModal.fadeOut(300, () => linkModal.remove());
            });

            // Handle close button
            linkModal.find('.cob-modal-close').on('click', () => {
                linkModal.fadeOut(300, () => linkModal.remove());
            });

            // Close modal on outside click
            linkModal.on('click', (e) => {
                if (e.target === linkModal[0]) {
                    linkModal.fadeOut(300, () => linkModal.remove());
                }
            });
        }

        showEmailModal(completionLink = null) {
            // Remove any existing email modals
            $('.cob-email-modal').remove();
            
            if (!completionLink) {
                // Generate completion link first
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
                        if (data.success && (data.token || (data.data && data.data.token))) {
                            const token = data.token || data.data.token;
                            const currentUrl = window.location.origin + window.location.pathname;
                            console.log('COB: Email modal - generated token:', token);
                            this.showEmailModal(`${currentUrl}?cob_share=${token}`);
                        } else {
                            console.error('COB: Email modal - failed to generate token:', data.message);
                            this.showSaveStatus('Failed to generate completion link for email. Please try again.', 'error');
                        }
                    } catch (e) {
                        console.error('Error generating share token for email:', e);
                        this.showSaveStatus('Error generating completion link for email. Please try again.', 'error');
                    }
                }).fail((xhr, status, error) => {
                    console.error('Failed to generate share token for email:', status, error);
                    this.showSaveStatus('Failed to generate completion link for email. Please try again.', 'error');
                });
                return;
            }

            const formData = this.getFormData();
            const defaultEmail = formData['primary_contact_email'] || '';
            
            const emailModal = $(`
                <div class="cob-email-modal" style="
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0,0,0,0.9);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    z-index: 10002;
                    backdrop-filter: blur(10px);
                ">
                    <div class="cob-modal-content" style="
                        background: linear-gradient(135deg, #1a1a1a 0%, #2a2a2a 100%);
                        padding: 40px;
                        border-radius: 16px;
                        max-width: 600px;
                        width: 90%;
                        text-align: center;
                        border: 2px solid #ff6b6b;
                        box-shadow: 0 25px 50px rgba(0,0,0,0.7);
                        position: relative;
                    ">
                        <div style="
                            position: absolute;
                            top: 0;
                            left: 0;
                            right: 0;
                            height: 4px;
                            background: linear-gradient(90deg, #ff6b6b, #ff8e53, #ff6b6b);
                            background-size: 200% 100%;
                            animation: shimmer 2s infinite;
                        "></div>
                        
                        <div style="margin-bottom: 30px;">
                            <div style="
                                width: 60px;
                                height: 60px;
                                background: linear-gradient(135deg, #ff6b6b 0%, #ff8e53 100%);
                                border-radius: 50%;
                                margin: 0 auto 20px;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                            ">
                                <svg width="30" height="30" viewBox="0 0 24 24" fill="none">
                                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M22 6l-10 7L2 6" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>
                            <h3 style="color: #ff6b6b; margin-bottom: 15px; font-size: 26px; font-weight: 700;">Send Completion Link</h3>
                            <p style="color: #fff; margin-bottom: 25px; line-height: 1.6; font-size: 16px;">
                                Enter your email address and we'll send you a secure link to complete your form later.
                            </p>
                        </div>
                        
                        <form class="cob-email-form" style="margin-bottom: 30px;">
                            <div style="margin-bottom: 20px;">
                                <label for="cob-email-input" style="
                                    display: block;
                                    text-align: left;
                                    color: #fff;
                                    margin-bottom: 8px;
                                    font-weight: 600;
                                    font-size: 14px;
                                ">Email Address</label>
                                <input type="email" id="cob-email-input" value="${defaultEmail}" required style="
                                    width: 100%;
                                    padding: 15px;
                                    border: 2px solid rgba(255,255,255,0.2);
                                    border-radius: 8px;
                                    background: rgba(255,255,255,0.1);
                                    color: #fff;
                                    font-size: 16px;
                                    outline: none;
                                    transition: all 0.3s ease;
                                    box-sizing: border-box;
                                " placeholder="Enter your email address">
                            </div>
                            
                            <div style="
                                background: rgba(255,255,255,0.05);
                                border-radius: 8px;
                                padding: 15px;
                                margin: 20px 0;
                                border: 1px solid rgba(255,255,255,0.1);
                            ">
                                <p style="color: #d5FD72; margin: 0; font-size: 14px; font-weight: 600;">
                                    🔒 <strong>Privacy:</strong> Your email will only be used to send the completion link. We won't spam you or share your information.
                                </p>
                            </div>
                        </form>
                        
                        <div class="cob-modal-buttons" style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
                            <button class="cob-btn cob-btn-send" style="
                                background: linear-gradient(135deg, #ff6b6b 0%, #ff8e53 100%);
                                color: #fff;
                                border: none;
                                padding: 15px 30px;
                                border-radius: 8px;
                                cursor: pointer;
                                font-weight: 700;
                                font-size: 16px;
                                transition: all 0.3s ease;
                                min-width: 140px;
                                box-shadow: 0 4px 15px rgba(255,107,107,0.3);
                            ">Send Link</button>
                            <button class="cob-btn cob-btn-cancel" style="
                                background: rgba(255,255,255,0.1);
                                color: #fff;
                                border: 2px solid rgba(255,255,255,0.3);
                                padding: 15px 30px;
                                border-radius: 8px;
                                cursor: pointer;
                                font-weight: 600;
                                font-size: 16px;
                                transition: all 0.3s ease;
                                min-width: 140px;
                            ">Cancel</button>
                        </div>
                        
                        <button class="cob-modal-close" style="
                            position: absolute;
                            top: 15px;
                            right: 15px;
                            background: rgba(255,255,255,0.1);
                            border: none;
                            color: #fff;
                            width: 30px;
                            height: 30px;
                            border-radius: 50%;
                            cursor: pointer;
                            font-size: 18px;
                            display: flex;
                                align-items: center;
                                justify-content: center;
                                transition: all 0.3s ease;
                        ">×</button>
                    </div>
                </div>
            `);

            $('body').append(emailModal);

            // Handle send button
            emailModal.find('.cob-btn-send').on('click', (e) => {
                e.preventDefault();
                const email = emailModal.find('#cob-email-input').val().trim();
                
                if (!email) {
                    emailModal.find('#cob-email-input').css('border-color', '#ff6b6b');
                    return;
                }
                
                if (!this.isValidEmail(email)) {
                    emailModal.find('#cob-email-input').css('border-color', '#ff6b6b');
                    return;
                }
                
                this.sendCompletionLink(email, completionLink, emailModal);
            });

            // Handle cancel button
            emailModal.find('.cob-btn-cancel').on('click', () => {
                emailModal.fadeOut(300, () => emailModal.remove());
            });

            // Handle close button
            emailModal.find('.cob-modal-close').on('click', () => {
                emailModal.fadeOut(300, () => emailModal.remove());
            });

            // Close modal on outside click
            emailModal.on('click', (e) => {
                if (e.target === emailModal[0]) {
                    emailModal.fadeOut(300, () => emailModal.remove());
                }
            });

            // Handle email input styling
            emailModal.find('#cob-email-input').on('input', function() {
                $(this).css('border-color', 'rgba(255,255,255,0.2)');
            });

            // Handle form submission on enter
            emailModal.find('.cob-email-form').on('submit', (e) => {
                e.preventDefault();
                emailModal.find('.cob-btn-send').click();
            });

            // Focus on email input
            setTimeout(() => {
                emailModal.find('#cob-email-input').focus();
            }, 100);
        }

        sendCompletionLink(email, completionLink, modal) {
            const sendBtn = modal.find('.cob-btn-send');
            const originalText = sendBtn.text();
            
            sendBtn.prop('disabled', true).text('📤 Sending...');
            
            $.ajax({
                url: cob_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'cob_send_completion_link',
                    nonce: cob_ajax.nonce,
                    email: email,
                    completion_link: completionLink,
                    session_id: this.sessionId
                }
            }).done((response) => {
                try {
                    const data = typeof response === 'string' ? JSON.parse(response) : response;
                    
                    if (data.success) {
                        modal.find('.cob-modal-content').html(`
                            <div style="text-align: center; padding: 40px;">
                                <div style="
                                    width: 80px;
                                    height: 80px;
                                    background: #4CAF50;
                                    border-radius: 50%;
                                    margin: 0 auto 20px;
                                    display: flex;
                                    align-items: center;
                                    justify-content: center;
                                ">
                                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none">
                                        <path d="M9 12l2 2 4-4" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                                <h3 style="color: #4CAF50; margin-bottom: 15px; font-size: 26px; font-weight: 700;">Email Sent Successfully!</h3>
                                <p style="color: #fff; margin-bottom: 25px; line-height: 1.6; font-size: 16px;">
                                    We've sent the completion link to <strong>${email}</strong>. Check your inbox (and spam folder) for the email.
                                </p>
                                <button class="cob-btn cob-btn-close" style="
                                    background: #4CAF50;
                                    color: #fff;
                                    border: none;
                                    padding: 15px 30px;
                                    border-radius: 8px;
                                    cursor: pointer;
                                    font-weight: 700;
                                    font-size: 16px;
                                    transition: all 0.3s ease;
                                    min-width: 140px;
                                ">Close</button>
                            </div>
                        `);
                        
                        modal.find('.cob-btn-close').on('click', () => {
                            modal.fadeOut(300, () => modal.remove());
                        });
                    } else {
                        this.showEmailError(modal, data.message || 'Failed to send email. Please try again.');
                    }
                } catch (e) {
                    console.error('Error parsing email response:', e);
                    this.showEmailError(modal, 'Error processing response. Please try again.');
                }
            }).fail((xhr, status, error) => {
                console.error('Failed to send email:', status, error);
                this.showEmailError(modal, 'Network error. Please try again.');
            });
        }

        showEmailError(modal, message) {
            const sendBtn = modal.find('.cob-btn-send');
            sendBtn.prop('disabled', false).text('📧 Send Link');
            
            // Show error message
            if (!modal.find('.cob-email-error').length) {
                modal.find('.cob-email-form').after(`
                    <div class="cob-email-error" style="
                        background: rgba(255,107,107,0.1);
                        border: 1px solid rgba(255,107,107,0.3);
                        border-radius: 8px;
                        padding: 15px;
                        margin: 20px 0;
                        color: #ff6b6b;
                        font-weight: 600;
                    ">${message}</div>
                `);
            } else {
                modal.find('.cob-email-error').text(message);
            }
        }

        isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }

        updateProgressDisplay() {
            // Update all progress bars on the page
            const progress = this.calculateProgress();
            
            // Update main progress bar
            $('.cob-progress-fill').css('width', progress + '%');
            $('.cob-mobile-progress-fill').css('width', progress + '%');
            
            // Update progress text
            $('.cob-progress-text').text(progress + '% COMPLETE');
            $('.cob-mobile-progress-text').text('PROGRESS');
            
            // Update step indicators
            for (let i = 1; i <= this.totalSteps; i++) {
                const stepElement = $(`.cob-step-item[data-step="${i}"]`);
                if (i <= this.currentStep) {
                    stepElement.addClass('cob-step-completed');
                } else {
                    stepElement.removeClass('cob-step-completed');
                }
            }
        }

        calculateProgress() {
            const formData = this.getFormData();
            let completedFields = 0;
            let totalFields = 0;
            
            // Count all form fields
            $('#cob-onboarding-form input, #cob-onboarding-form select, #cob-onboarding-form textarea').each(function() {
                const field = $(this);
                const name = field.attr('name');
                
                if (name && name !== 'submit') {
                    totalFields++;
                    
                    if (field.attr('type') === 'checkbox' || field.attr('type') === 'radio') {
                        if (field.is(':checked')) {
                            completedFields++;
                        }
                    } else {
                        if (field.val() && field.val().trim() !== '') {
                            completedFields++;
                        }
                    }
                }
            });
            
            // Calculate percentage with step bonus
            const fieldProgress = totalFields > 0 ? (completedFields / totalFields) * 70 : 0;
            const stepProgress = (this.currentStep - 1) * 7.5; // 7.5% per step
            
            return Math.min(100, Math.round(fieldProgress + stepProgress));
        }

        scrollToCurrentStep() {
            // Scroll to the current step section
            const currentStepElement = $(`.cob-step[data-step="${this.currentStep}"]`);
            if (currentStepElement.length) {
                $('html, body').animate({
                    scrollTop: currentStepElement.offset().top - 100
                }, 800);
            }
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
            
            // Remove existing classes and add appropriate ones
            statusElement.removeClass('cob-error cob-success cob-warning').addClass(`cob-${type}`);
            
            // Set message and styling
            statusElement.text(message);
            
            // Apply styling based on type
            switch (type) {
                case 'error':
                    statusElement.css({
                        'background-color': 'rgba(255, 107, 107, 0.1)',
                        'border-color': 'rgba(255, 107, 107, 0.3)',
                        'color': '#ff6b6b'
                    });
                    break;
                case 'warning':
                    statusElement.css({
                        'background-color': 'rgba(255, 193, 7, 0.1)',
                        'border-color': 'rgba(255, 193, 7, 0.3)',
                        'color': '#ffc107'
                    });
                    break;
                case 'success':
                default:
                    statusElement.css({
                        'background-color': 'rgba(157, 255, 0, 0.1)',
                        'border-color': 'rgba(157, 255, 0, 0.3)',
                        'color': '#d5FD72'
                    });
                    break;
            }
            
            statusElement.show();
            
            // Update the header save status with current time
            this.updateSaveStatus();
            
            // Auto-hide after appropriate time
            const hideDelay = type === 'error' ? 5000 : 3000;
            setTimeout(() => {
                statusElement.fadeOut();
            }, hideDelay);
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
                
                // Check if AJAX object is available
                if (typeof cob_ajax === 'undefined' || !cob_ajax.ajax_url || !cob_ajax.nonce) {
                    console.error('COB: AJAX object not available, retrying in 1 second...');
                    setTimeout(() => this.loadSharedDraft(), 1000);
                    return;
                }
                
                console.log('COB: AJAX URL:', cob_ajax.ajax_url);
                console.log('COB: Nonce:', cob_ajax.nonce);
                
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
                    console.log('COB: Raw AJAX response:', response);
                    console.log('COB: Response type:', typeof response);
                    console.log('COB: Response keys:', Object.keys(response));
                    console.log('COB: Response success:', response.success);
                    console.log('COB: Response draft:', response.draft);
                    console.log('COB: Response message:', response.message);
                    
                    try {
                        const data = typeof response === 'string' ? JSON.parse(response) : response;
                        console.log('COB: Parsed response data:', data);
                        console.log('COB: Parsed data type:', typeof data);
                        console.log('COB: Parsed data keys:', Object.keys(data));
                        
                        console.log('COB: Checking response conditions:');
                        console.log('COB: data.success:', data.success);
                        console.log('COB: data.data.draft:', data.data ? data.data.draft : 'undefined');
                        console.log('COB: data.success type:', typeof data.success);
                        console.log('COB: data.data.draft type:', data.data && data.data.draft ? typeof data.data.draft : 'undefined');
                        
                        if (data.success === true && data.data && data.data.draft && typeof data.data.draft === 'object') {
                            console.log('COB: Shared draft loaded successfully:', data.data.draft);
                            
                            // Set the session ID from the shared draft
                            this.sessionId = data.data.draft.session_id;
                            
                            // Load the form data
                            this.loadFormData(data.data.draft.form_data);
                            
                            // Set the current step
                            this.currentStep = data.data.draft.current_step;
                            this.updateStepDisplay();
                            
                            // Update progress bars
                            this.updateProgressDisplay();
                            
                            // Show success message with progress info
                            const progress = data.data.draft.progress_percentage || 0;
                            this.showSaveStatus(`Shared draft loaded successfully! You're ${progress}% complete and can continue from Step ${this.currentStep}.`, 'success');
                            
                            // Clean up the URL
                            const newUrl = window.location.pathname;
                            window.history.replaceState({}, document.title, newUrl);
                            
                            // Scroll to the current step
                            this.scrollToCurrentStep();
                            
                        } else {
                            console.error('COB: Failed to load shared draft. Response data:', data);
                            const errorMessage = data.message || (data.data && typeof data.data === 'string' ? data.data : 'Unknown error occurred');
                            console.error('COB: Error message:', errorMessage);
                            this.showSaveStatus('Failed to load shared draft: ' + errorMessage, 'error');
                        }
                    } catch (e) {
                        console.error('COB: Error parsing shared draft response:', e);
                        this.showSaveStatus('Failed to load shared draft: Invalid response', 'error');
                    }
                }).fail((xhr, status, error) => {
                    console.error('COB: Failed to load shared draft:', status, error);
                    console.error('COB: XHR response:', xhr.responseText);
                    console.error('COB: XHR status:', xhr.status);
                    this.showSaveStatus('Failed to load shared draft: Network error', 'error');
                });
            }
        }

        loadFormData(formData) {
            // Populate form fields with the loaded data
            Object.keys(formData).forEach(fieldName => {
                const field = $(`[name="${fieldName}"]`);
                if (field.length) {
                    const value = formData[fieldName];
                    
                    if (field.attr('type') === 'checkbox') {
                        // Handle checkbox arrays
                        if (Array.isArray(value)) {
                            value.forEach(val => {
                                $(`[name="${fieldName}"][value="${val}"]`).prop('checked', true);
                            });
                        } else {
                            field.prop('checked', value === true || value === '1' || value === 'on');
                        }
                    } else if (field.attr('type') === 'radio') {
                        field.filter(`[value="${value}"]`).prop('checked', true);
                    } else if (field.is('select')) {
                        // Handle select fields
                        if (Array.isArray(value)) {
                            // Multiple select
                            field.val(value);
                        } else {
                            field.val(value);
                        }
                    } else if (field.attr('type') === 'textarea') {
                        field.val(value);
                    } else {
                        field.val(value);
                    }
                }
            });
            
            // Trigger change events to update any dependent fields
            $('input, select, textarea').trigger('change');
            
            // Update progress display
            this.updateProgressDisplay();
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

        /**
         * Fallback copy to clipboard for older browsers
         */
        fallbackCopyToClipboard(text, copyBtn, originalText, originalBackground, modal) {
            console.log('COB: Fallback copy method called');
            try {
                const textArea = document.createElement('textarea');
                textArea.value = text;
                textArea.style.position = 'fixed';
                textArea.style.left = '-999999px';
                textArea.style.top = '-999999px';
                document.body.appendChild(textArea);
                textArea.focus();
                textArea.select();
                
                const successful = document.execCommand('copy');
                document.body.removeChild(textArea);
                
                console.log('COB: Fallback copy result:', successful);
                
                if (successful) {
                    console.log('COB: Fallback copy successful');
                    // Show success feedback
                    copyBtn.text('✅ Copied!').css({
                        'background': 'linear-gradient(135deg, #4CAF50 0%, #45a049 100%)',
                        'color': '#fff'
                    });
                    
                    // Show tooltip
                    this.showCopyTooltip(modal, 'Link copied to clipboard!');
                    
                    // Reset button after 3 seconds
                    setTimeout(() => {
                        copyBtn.text(originalText).css({
                            'background': originalBackground,
                            'color': '#000'
                        });
                    }, 3000);
                } else {
                    console.log('COB: Fallback copy failed');
                    // Show error feedback
                    copyBtn.text('❌ Failed').css({
                        'background': 'linear-gradient(135deg, #ff6b6b 0%, #ff8e53 100%)',
                        'color': '#fff'
                    });
                    
                    this.showCopyTooltip(modal, 'Failed to copy link. Please try again.', 'error');
                    
                    setTimeout(() => {
                        copyBtn.text(originalText).css({
                            'background': originalBackground,
                            'color': '#000'
                        });
                    }, 3000);
                }
            } catch (err) {
                console.error('COB: Fallback copy failed:', err);
                
                // Show error feedback
                copyBtn.text('❌ Failed').css({
                    'background': 'linear-gradient(135deg, #ff6b6b 0%, #ff8e53 100%)',
                    'color': '#fff'
                });
                
                this.showCopyTooltip(modal, 'Failed to copy link. Please try again.', 'error');
                
                setTimeout(() => {
                    copyBtn.text(originalText).css({
                        'background': originalBackground,
                        'color': '#000'
                    });
                }, 3000);
            }
        }

        /**
         * Show copy tooltip/alert
         */
        showCopyTooltip(modal, message, type = 'success') {
            // Remove any existing tooltips
            $('.cob-copy-tooltip').remove();
            
            const tooltip = $(`
                <div class="cob-copy-tooltip" style="
                    position: absolute;
                    bottom: -50px;
                    left: 50%;
                    transform: translateX(-50%);
                    background: ${type === 'success' ? 'linear-gradient(135deg, #4CAF50 0%, #45a049 100%)' : 'linear-gradient(135deg, #ff6b6b 0%, #ff8e53 100%)'};
                    color: #fff;
                    padding: 10px 20px;
                    border-radius: 8px;
                    font-size: 14px;
                    font-weight: 600;
                    z-index: 10004;
                    box-shadow: 0 4px 15px rgba(0,0,0,0.3);
                    animation: tooltipSlideIn 0.3s ease;
                    white-space: nowrap;
                ">
                    ${message}
                </div>
            `);
            
            // Add tooltip to the copy button container
            const copyBtnContainer = modal.find('.cob-btn-copy').parent();
            copyBtnContainer.css('position', 'relative').append(tooltip);
            
            // Add CSS animation
            if (!$('#cob-tooltip-styles').length) {
                $('head').append(`
                    <style id="cob-tooltip-styles">
                        @keyframes tooltipSlideIn {
                            from {
                                opacity: 0;
                                transform: translateX(-50%) translateY(10px);
                            }
                            to {
                                opacity: 1;
                                transform: translateX(-50%) translateY(0);
                            }
                        }
                    </style>
                `);
            }
            
            // Remove tooltip after 3 seconds
            setTimeout(() => {
                tooltip.fadeOut(300, () => tooltip.remove());
            }, 3000);
        }
    }

    // Initialize when document is ready
    $(document).ready(() => {
        new ClientOnboardingForm();
    });

})(jQuery);