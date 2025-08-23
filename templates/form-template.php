<?php
/**
 * Main form template - matches the exact UI design from screenshot
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div id="cob-form-container" class="cob-form-container">
    <!-- Header with FLUX branding and controls -->
    <header class="cob-header">
        <div class="cob-header-left">
            <h1 class="cob-logo">FLUX</h1>
            <span class="cob-form-title">CLIENT ONBOARDING FORM</span>
        </div>
        <div class="cob-header-right">
            <button type="button" class="cob-exit-btn" id="cob-exit-form">
                + EXIT FORM
            </button>
            <button type="button" class="cob-save-btn" id="cob-save-draft">
                SAVE DRAFT
            </button>
        </div>
    </header>

    <div class="cob-form-wrapper">
        <!-- Step Navigation Sidebar -->
        <nav class="cob-step-navigation cob-nav-enhanced">
            <div class="cob-step-item cob-nav-item-enhanced cob-step-active" data-step="1">
                <div class="cob-step-circle cob-circle-enhanced">
                    <span class="cob-step-number cob-number-enhanced">1</span>
                </div>
                <span class="cob-step-name cob-name-enhanced">CLIENT INFORMATION</span>
            </div>
            <div class="cob-step-item cob-nav-item-enhanced" data-step="2">
                <div class="cob-step-circle cob-circle-enhanced">
                    <span class="cob-step-number cob-number-enhanced">2</span>
                </div>
                <span class="cob-step-name cob-name-enhanced">TECHNICAL INFORMATION</span>
            </div>
            <div class="cob-step-item cob-nav-item-enhanced" data-step="3">
                <div class="cob-step-circle cob-circle-enhanced">
                    <span class="cob-step-number cob-number-enhanced">3</span>
                </div>
                <span class="cob-step-name cob-name-enhanced">REPORTING INFORMATION</span>
            </div>
            <div class="cob-step-item cob-nav-item-enhanced" data-step="4">
                <div class="cob-step-circle cob-circle-enhanced">
                    <span class="cob-step-number cob-number-enhanced">4</span>
                </div>
                <span class="cob-step-name cob-name-enhanced">MARKETING INFORMATION</span>
            </div>
        </nav>

        <!-- Main Form Content -->
        <main class="cob-form-content cob-content-enhanced">
            <div class="cob-step-header">
                <h2 id="cob-current-step-title">CLIENT INFORMATION</h2>
            </div>

            <form id="cob-onboarding-form" class="cob-form">
                <!-- Step 1: Client Information -->
                <div class="cob-step-content cob-step-active" data-step="1">
                    <div class="cob-form-grid">
                        <!-- Basic Project Details -->
                        <div class="cob-form-group">
                            <label class="cob-label">PROJECT NAME *</label>
                            <input type="text" name="project_name" class="cob-input" placeholder="Enter your project name" required>
                        </div>
                        <div class="cob-form-group">
                            <label class="cob-label">BUSINESS NAME *</label>
                            <input type="text" name="business_name" class="cob-input" placeholder="Enter your business name" required>
                        </div>

                        <!-- Contact Information -->
                        <div class="cob-form-group">
                            <label class="cob-label">PRIMARY CONTACT NAME *</label>
                            <input type="text" name="primary_contact_name" class="cob-input" placeholder="Enter primary contact name" required>
                        </div>
                        <div class="cob-form-group">
                            <label class="cob-label">PRIMARY CONTACT EMAIL *</label>
                            <input type="email" name="primary_contact_email" class="cob-input" placeholder="Enter primary contact email" required>
                        </div>
                        <div class="cob-form-group">
                            <label class="cob-label">PRIMARY CONTACT NUMBER *</label>
                            <input type="tel" name="primary_contact_number" class="cob-input" placeholder="Enter primary contact number" required>
                        </div>
                        <div class="cob-form-group">
                            <label class="cob-label">WHO IS THE MAIN APPROVER OF MILESTONES? *</label>
                            <input type="text" name="main_approver" class="cob-input" placeholder="Enter main approver name" required>
                        </div>

                        <!-- Billing & Administrative -->
                        <div class="cob-form-group">
                            <label class="cob-label">BILLING EMAIL ADDRESS *</label>
                            <input type="email" name="billing_email" class="cob-input" placeholder="Enter billing email address" required>
                        </div>
                        <div class="cob-form-group">
                            <label class="cob-label">VAT NUMBER</label>
                            <input type="text" name="vat_number" class="cob-input" placeholder="Enter VAT number (optional)">
                        </div>

                        <!-- Contact Preferences -->
                        <div class="cob-form-group cob-form-group-full">
                            <label class="cob-label">PREFERRED CONTACT METHOD *</label>
                            <div class="cob-radio-group">
                                <label class="cob-radio-option">
                                    <input type="radio" name="preferred_contact_method" value="phone" class="cob-radio" required>
                                    <span class="cob-radio-label">Phone</span>
                                </label>
                                <label class="cob-radio-option">
                                    <input type="radio" name="preferred_contact_method" value="email" class="cob-radio" required>
                                    <span class="cob-radio-label">Email</span>
                                </label>
                            </div>
                        </div>

                        <!-- Billing Address -->
                        <div class="cob-form-group cob-form-group-full">
                            <label class="cob-label">ADDRESS LINE 1 *</label>
                            <input type="text" name="address_line_1" class="cob-input" placeholder="Enter address line 1" required>
                        </div>
                        <div class="cob-form-group cob-form-group-full">
                            <label class="cob-label">ADDRESS LINE 2</label>
                            <input type="text" name="address_line_2" class="cob-input" placeholder="Enter address line 2 (optional)">
                        </div>
                        <div class="cob-form-group cob-form-group-full">
                            <label class="cob-label">CITY *</label>
                            <input type="text" name="city" class="cob-input" placeholder="Enter city" required>
                        </div>
                        <div class="cob-form-group">
                            <label class="cob-label">COUNTRY *</label>
                            <input type="text" name="country" class="cob-input" placeholder="Enter country" required>
                        </div>
                        <div class="cob-form-group">
                            <label class="cob-label">POSTAL CODE *</label>
                            <input type="text" name="postal_code" class="cob-input" placeholder="Enter postal code" required>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Technical Information -->
                <div class="cob-step-content" data-step="2">
                    <div class="cob-form-grid">
                        <!-- Content Management System -->
                        <div class="cob-form-group cob-form-group-full">
                            <label class="cob-label">CURRENT CONTENT MANAGEMENT SYSTEM</label>
                            <input type="text" name="current_cms" class="cob-input" placeholder="Enter your current CMS (if applicable)">
                        </div>

                        <!-- Website Hosting -->
                        <div class="cob-form-group">
                            <label class="cob-label">WEBSITE HOSTING COMPANY *</label>
                            <input type="text" name="website_hosting_company" class="cob-input" placeholder="Enter hosting company name" required>
                        </div>
                        <div class="cob-form-group">
                            <label class="cob-label">WEBSITE POINT OF CONTACT EMAIL *</label>
                            <input type="email" name="website_contact_email" class="cob-input" placeholder="Enter website contact email" required>
                        </div>

                        <!-- Domain Hosting -->
                        <div class="cob-form-group">
                            <label class="cob-label">DOMAIN HOSTING COMPANY *</label>
                            <input type="text" name="domain_hosting_company" class="cob-input" placeholder="Enter domain hosting company" required>
                        </div>
                        <div class="cob-form-group">
                            <label class="cob-label">DOMAIN POINT OF CONTACT EMAIL *</label>
                            <input type="email" name="domain_contact_email" class="cob-input" placeholder="Enter domain contact email" required>
                        </div>

                        <!-- Website Backend (CMS) Access -->
                        <div class="cob-form-group">
                            <label class="cob-label">WEBSITE BACKEND (CMS) LINK *</label>
                            <input type="url" name="cms_link" class="cob-input" placeholder="Enter CMS backend URL" required>
                        </div>
                        <div class="cob-form-group">
                            <label class="cob-label">WEBSITE BACKEND (CMS) USERNAME *</label>
                            <input type="text" name="cms_username" class="cob-input" placeholder="Enter CMS username" required>
                        </div>
                        <div class="cob-form-group cob-form-group-full">
                            <label class="cob-label">WEBSITE BACKEND (CMS) PASSWORD *</label>
                            <input type="password" name="cms_password" class="cob-input" placeholder="Enter CMS password" required>
                        </div>

                        <!-- CRM Integration -->
                        <div class="cob-form-group cob-form-group-full">
                            <label class="cob-label">WHICH CRM DO YOU CURRENTLY USE?</label>
                            <input type="text" name="current_crm" class="cob-input" placeholder="Enter your current CRM (if applicable)">
                        </div>

                        <!-- 3rd Party Integrations -->
                        <div class="cob-form-group cob-form-group-full">
                            <label class="cob-label">DO YOU HAVE ANY 3RD PARTY WEBSITE INTEGRATIONS IN PLACE? *</label>
                            <div class="cob-radio-group">
                                <label class="cob-radio-option">
                                    <input type="radio" name="third_party_integrations" value="yes" class="cob-radio" required>
                                    <span class="cob-radio-label">Yes</span>
                                </label>
                                <label class="cob-radio-option">
                                    <input type="radio" name="third_party_integrations" value="no" class="cob-radio" required>
                                    <span class="cob-radio-label">No</span>
                                </label>
                            </div>
                        </div>

                        <!-- Conditional 3rd Party Integration Fields -->
                        <div class="cob-conditional-fields" data-show-when="third_party_integrations" data-show-value="yes" style="display: none;">
                            <div class="cob-form-group">
                                <label class="cob-label">3RD PARTY INTEGRATION NAME *</label>
                                <input type="text" name="third_party_name" class="cob-input" placeholder="Enter integration name">
                            </div>
                            <div class="cob-form-group">
                                <label class="cob-label">3RD PARTY PRIMARY CONTACT NUMBER *</label>
                                <input type="tel" name="third_party_contact_number" class="cob-input" placeholder="Enter contact number">
                            </div>
                            <div class="cob-form-group cob-form-group-full">
                                <label class="cob-label">3RD PARTY PRIMARY CONTACT EMAIL *</label>
                                <input type="email" name="third_party_contact_email" class="cob-input" placeholder="Enter contact email">
                            </div>
                        </div>

                        <!-- Booking Engine -->
                        <div class="cob-form-group">
                            <label class="cob-label">BOOKING ENGINE NAME</label>
                            <input type="text" name="booking_engine_name" class="cob-input" placeholder="Enter booking engine name (if applicable)">
                        </div>
                        <div class="cob-form-group">
                            <label class="cob-label">USERNAME</label>
                            <input type="text" name="booking_engine_username" class="cob-input" placeholder="Enter username (if applicable)">
                        </div>
                        <div class="cob-form-group">
                            <label class="cob-label">PASSWORD</label>
                            <input type="password" name="booking_engine_password" class="cob-input" placeholder="Enter password (if applicable)">
                        </div>
                        <div class="cob-form-group">
                            <label class="cob-label">PRIMARY CONTACT EMAIL</label>
                            <input type="email" name="booking_engine_contact_email" class="cob-input" placeholder="Enter contact email (if applicable)">
                        </div>

                        <!-- Technical Objectives -->
                        <div class="cob-form-group cob-form-group-full">
                            <label class="cob-label">WHAT IS YOUR TECHNICAL OBJECTIVE FOR THIS PROJECT/WEBSITE? *</label>
                            <textarea name="technical_objective" class="cob-textarea" placeholder="Describe your technical objectives and goals" required></textarea>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Reporting Information -->
                <div class="cob-step-content" data-step="3">
                    <div class="cob-form-grid">
                        <!-- Google Analytics -->
                        <div class="cob-form-group cob-form-group-full">
                            <label class="cob-label">DO YOU HAVE A GOOGLE ANALYTICS ACCOUNT? *</label>
                            <div class="cob-radio-group">
                                <label class="cob-radio-option">
                                    <input type="radio" name="google_analytics_account" value="yes" class="cob-radio" required>
                                    <span class="cob-radio-label">Yes</span>
                                </label>
                                <label class="cob-radio-option">
                                    <input type="radio" name="google_analytics_account" value="no" class="cob-radio" required>
                                    <span class="cob-radio-label">No</span>
                                </label>
                            </div>
                        </div>

                        <!-- Conditional Google Analytics Fields -->
                        <div class="cob-conditional-fields" data-show-when="google_analytics_account" data-show-value="yes" style="display: none;">
                            <div class="cob-form-group cob-form-group-full">
                                <label class="cob-label">GOOGLE ANALYTICS ACCOUNT ID</label>
                                <input type="text" name="google_analytics_account_id" class="cob-input" placeholder="Enter your Google Analytics account ID">
                            </div>
                        </div>

                        <!-- Google Tag Manager -->
                        <div class="cob-form-group cob-form-group-full">
                            <label class="cob-label">DO YOU HAVE A GOOGLE TAG MANAGER ACCOUNT? *</label>
                            <div class="cob-radio-group">
                                <label class="cob-radio-option">
                                    <input type="radio" name="google_tag_manager_account" value="yes" class="cob-radio" required>
                                    <span class="cob-radio-label">Yes</span>
                                </label>
                                <label class="cob-radio-option">
                                    <input type="radio" name="google_tag_manager_account" value="no" class="cob-radio" required>
                                    <span class="cob-radio-label">No</span>
                                </label>
                            </div>
                        </div>

                        <!-- Conditional Google Tag Manager Fields -->
                        <div class="cob-conditional-fields" data-show-when="google_tag_manager_account" data-show-value="yes" style="display: none;">
                            <div class="cob-form-group cob-form-group-full">
                                <label class="cob-label">GOOGLE TAG MANAGER ACCOUNT ADMINISTRATOR</label>
                                <input type="text" name="google_tag_manager_admin" class="cob-input" placeholder="Enter GTM account administrator">
                            </div>
                        </div>

                        <!-- Google Ads -->
                        <div class="cob-form-group cob-form-group-full">
                            <label class="cob-label">DO YOU HAVE A GOOGLE ADS ACCOUNT? *</label>
                            <div class="cob-radio-group">
                                <label class="cob-radio-option">
                                    <input type="radio" name="google_ads_account" value="yes" class="cob-radio" required>
                                    <span class="cob-radio-label">Yes</span>
                                </label>
                                <label class="cob-radio-option">
                                    <input type="radio" name="google_ads_account" value="no" class="cob-radio" required>
                                    <span class="cob-radio-label">No</span>
                                </label>
                            </div>
                        </div>

                        <!-- Conditional Google Ads Fields -->
                        <div class="cob-conditional-fields" data-show-when="google_ads_account" data-show-value="yes" style="display: none;">
                            <div class="cob-form-group cob-form-group-full">
                                <label class="cob-label">GOOGLE ADS ACCOUNT ADMINISTRATOR</label>
                                <input type="text" name="google_ads_admin" class="cob-input" placeholder="Enter Google Ads account administrator">
                            </div>
                            <div class="cob-form-group cob-form-group-full">
                                <label class="cob-label">PROVIDE THE GOOGLE ADS CUSTOMER ID (CID)</label>
                                <input type="text" name="google_ads_customer_id" class="cob-input" placeholder="Enter Google Ads Customer ID">
                            </div>
                        </div>

                        <!-- Meta/Facebook Business Manager -->
                        <div class="cob-form-group cob-form-group-full">
                            <label class="cob-label">DO YOU HAVE A META/FACEBOOK BUSINESS MANAGER ACCOUNT? *</label>
                            <div class="cob-radio-group">
                                <label class="cob-radio-option">
                                    <input type="radio" name="meta_business_manager_account" value="yes" class="cob-radio" required>
                                    <span class="cob-radio-label">Yes</span>
                                </label>
                                <label class="cob-radio-option">
                                    <input type="radio" name="meta_business_manager_account" value="no" class="cob-radio" required>
                                    <span class="cob-radio-label">No</span>
                                </label>
                            </div>
                        </div>

                        <!-- Conditional Meta Business Manager Fields -->
                        <div class="cob-conditional-fields" data-show-when="meta_business_manager_account" data-show-value="yes" style="display: none;">
                            <div class="cob-form-group cob-form-group-full">
                                <label class="cob-label">META/FACEBOOK BUSINESS MANAGER ACCOUNT ADMINISTRATOR</label>
                                <input type="text" name="meta_business_manager_admin" class="cob-input" placeholder="Enter Business Manager account administrator">
                            </div>
                            <div class="cob-form-group cob-form-group-full">
                                <label class="cob-label">PROVIDE THE BUSINESS MANAGER ID</label>
                                <input type="text" name="meta_business_manager_id" class="cob-input" placeholder="Enter Business Manager ID">
                            </div>
                        </div>

                        <!-- Paid Media History -->
                        <div class="cob-form-group cob-form-group-full">
                            <label class="cob-label">WHICH PAID MEDIA ADS HAVE YOU RUN BEFORE? *</label>
                            <div class="cob-checkbox-group">
                                <label class="cob-checkbox-option">
                                    <input type="checkbox" name="paid_media_history[]" value="meta" class="cob-checkbox">
                                    <span class="cob-checkbox-label">Meta</span>
                                </label>
                                <label class="cob-checkbox-option">
                                    <input type="checkbox" name="paid_media_history[]" value="google" class="cob-checkbox">
                                    <span class="cob-checkbox-label">Google</span>
                                </label>
                                <label class="cob-checkbox-option">
                                    <input type="checkbox" name="paid_media_history[]" value="linkedin" class="cob-checkbox">
                                    <span class="cob-checkbox-label">LinkedIn</span>
                                </label>
                                <label class="cob-checkbox-option">
                                    <input type="checkbox" name="paid_media_history[]" value="tiktok" class="cob-checkbox">
                                    <span class="cob-checkbox-label">TikTok</span>
                                </label>
                                <label class="cob-checkbox-option">
                                    <input type="checkbox" name="paid_media_history[]" value="other" class="cob-checkbox">
                                    <span class="cob-checkbox-label">Other</span>
                                </label>
                            </div>
                        </div>

                        <!-- Conditional Other Paid Media History -->
                        <div class="cob-conditional-fields" data-show-when="paid_media_history" data-show-value="other" style="display: none;">
                            <div class="cob-form-group cob-form-group-full">
                                <label class="cob-label">IF OTHER, PLEASE SPECIFY</label>
                                <input type="text" name="paid_media_history_other" class="cob-input" placeholder="Specify other paid media platforms">
                            </div>
                        </div>

                        <!-- Current Paid Media -->
                        <div class="cob-form-group cob-form-group-full">
                            <label class="cob-label">WHICH OF THE ABOVE PAID MEDIA ADS ARE YOU STILL RUNNING? *</label>
                            <div class="cob-checkbox-group">
                                <label class="cob-checkbox-option">
                                    <input type="checkbox" name="current_paid_media[]" value="meta" class="cob-checkbox">
                                    <span class="cob-checkbox-label">Meta</span>
                                </label>
                                <label class="cob-checkbox-option">
                                    <input type="checkbox" name="current_paid_media[]" value="google" class="cob-checkbox">
                                    <span class="cob-checkbox-label">Google</span>
                                </label>
                                <label class="cob-checkbox-option">
                                    <input type="checkbox" name="current_paid_media[]" value="linkedin" class="cob-checkbox">
                                    <span class="cob-checkbox-label">LinkedIn</span>
                                </label>
                                <label class="cob-checkbox-option">
                                    <input type="checkbox" name="current_paid_media[]" value="tiktok" class="cob-checkbox">
                                    <span class="cob-checkbox-label">TikTok</span>
                                </label>
                                <label class="cob-checkbox-option">
                                    <input type="checkbox" name="current_paid_media[]" value="other" class="cob-checkbox">
                                    <span class="cob-checkbox-label">Other</span>
                                </label>
                            </div>
                        </div>

                        <!-- Conditional Other Current Paid Media -->
                        <div class="cob-conditional-fields" data-show-when="current_paid_media" data-show-value="other" style="display: none;">
                            <div class="cob-form-group cob-form-group-full">
                                <label class="cob-label">IF OTHER, PLEASE SPECIFY</label>
                                <input type="text" name="current_paid_media_other" class="cob-input" placeholder="Specify other current paid media platforms">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 4: Marketing Information -->
                <div class="cob-step-content" data-step="4">
                    <div class="cob-form-grid">
                        <!-- Objectives -->
                        <div class="cob-form-group cob-form-group-full">
                            <label class="cob-label">WHAT IS THE MAIN OBJECTIVE FOR THE ONLINE PUSH? *</label>
                            <textarea name="main_objective" class="cob-textarea" placeholder="Describe your main objective for the online marketing push" required></textarea>
                        </div>
                        <div class="cob-form-group cob-form-group-full">
                            <label class="cob-label">WHAT DO YOU DO? (CREATE BRIEF TEXT JUST GIVING FOCUS TO THE BRAND) *</label>
                            <textarea name="brand_focus" class="cob-textarea" placeholder="Brief description of what your business does" required></textarea>
                        </div>
                        <div class="cob-form-group cob-form-group-full">
                            <label class="cob-label">WHAT IS YOUR COMMERCIAL OBJECTIVE FOR THIS STRATEGY PUSH? *</label>
                            <textarea name="commercial_objective" class="cob-textarea" placeholder="Describe your commercial objectives and goals" required></textarea>
                        </div>
                        <div class="cob-form-group cob-form-group-full">
                            <label class="cob-label">BRIEF DESCRIPTION OF THIS PUSH IMPACT? *</label>
                            <textarea name="push_impact" class="cob-textarea" placeholder="Describe the expected impact of this marketing push" required></textarea>
                        </div>

                        <!-- Brand Story -->
                        <div class="cob-form-group cob-form-group-full">
                            <label class="cob-label">WHAT INSPIRED THE FOUNDERS OF YOUR BUSINESS TO DO BUSINESS? *</label>
                            <textarea name="founder_inspiration" class="cob-textarea" placeholder="Share the story behind your business founding" required></textarea>
                        </div>
                        <div class="cob-form-group cob-form-group-full">
                            <label class="cob-label">WHAT IS THE BRAND'S TONE, MISSION AND PURPOSE? *</label>
                            <textarea name="brand_tone_mission" class="cob-textarea" placeholder="Describe your brand's tone, mission, and purpose" required></textarea>
                        </div>
                        <div class="cob-form-group cob-form-group-full">
                            <label class="cob-label">HOW HAS YOU CONSIDERED YOUR BRAND/YOUR PERCEIVED AT GROUND LEVEL? *</label>
                            <textarea name="brand_perception" class="cob-textarea" placeholder="How is your brand perceived at the ground level?" required></textarea>
                        </div>
                        <div class="cob-form-group cob-form-group-full">
                            <label class="cob-label">CAN YOU DESCRIBE WHAT IS DISCUSSED WHEN YOU INTRODUCE YOURSELF AS A MEMBER OF A GLOBAL TEAM? *</label>
                            <textarea name="global_team_introduction" class="cob-textarea" placeholder="Describe how you introduce yourself as part of a global team" required></textarea>
                        </div>
                        <div class="cob-form-group cob-form-group-full">
                            <label class="cob-label">HOW DO YOU INTRODUCE YOUR FINAL SERVICE/SERVICE/SERVICE TO OTHERS WHEN IT IS RELATED TO YOUR BUSINESS ACTIVITIES? *</label>
                            <textarea name="service_introduction" class="cob-textarea" placeholder="How do you introduce your services to others?" required></textarea>
                        </div>

                        <!-- Brand Values -->
                        <div class="cob-form-group">
                            <label class="cob-label">BRAND LINE *</label>
                            <input type="text" name="brand_line_1" class="cob-input" placeholder="Enter your brand line" required>
                        </div>
                        <div class="cob-form-group">
                            <label class="cob-label">MISSION *</label>
                            <textarea name="mission_1" class="cob-textarea" placeholder="Enter your mission statement" required></textarea>
                        </div>
                        <div class="cob-form-group">
                            <label class="cob-label">BRAND LINE *</label>
                            <input type="text" name="brand_line_2" class="cob-input" placeholder="Enter your second brand line" required>
                        </div>
                        <div class="cob-form-group">
                            <label class="cob-label">MISSION *</label>
                            <textarea name="mission_2" class="cob-textarea" placeholder="Enter your second mission statement" required></textarea>
                        </div>
                        <div class="cob-form-group">
                            <label class="cob-label">BRAND LINE *</label>
                            <input type="text" name="brand_line_3" class="cob-input" placeholder="Enter your third brand line" required>
                        </div>
                        <div class="cob-form-group">
                            <label class="cob-label">MISSION *</label>
                            <textarea name="mission_3" class="cob-textarea" placeholder="Enter your third mission statement" required></textarea>
                        </div>

                        <!-- Visual Identity -->
                        <div class="cob-form-group cob-form-group-full">
                            <label class="cob-label">DO YOU HAVE YOUR BRAND GUIDELINES/STYLE GUIDE/IDENTITY AS A FILE UPLOAD? *</label>
                            <div class="cob-radio-group">
                                <label class="cob-radio-option">
                                    <input type="radio" name="brand_guidelines_upload" value="yes" class="cob-radio" required>
                                    <span class="cob-radio-label">Yes</span>
                                </label>
                                <label class="cob-radio-option">
                                    <input type="radio" name="brand_guidelines_upload" value="no" class="cob-radio" required>
                                    <span class="cob-radio-label">No</span>
                                </label>
                            </div>
                        </div>

                        <!-- Conditional Brand Guidelines Upload -->
                        <div class="cob-conditional-fields" data-show-when="brand_guidelines_upload" data-show-value="yes" style="display: none;">
                            <div class="cob-form-group cob-form-group-full">
                                <label class="cob-label">UPLOAD BRAND GUIDELINES/LOGO FILES</label>
                                <input type="file" name="brand_guidelines_files[]" class="cob-input" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.ai,.eps">
                            </div>
                        </div>

                        <!-- Communication Preferences -->
                        <div class="cob-form-group cob-form-group-full">
                            <label class="cob-label">WHICH METHOD PREFERRED TO COMMUNICATE WITH YOUR BRAND (NO LOGO)? *</label>
                            <div class="cob-radio-group">
                                <label class="cob-radio-option">
                                    <input type="radio" name="communication_tone" value="formal" class="cob-radio" required>
                                    <span class="cob-radio-label">Formal</span>
                                </label>
                                <label class="cob-radio-option">
                                    <input type="radio" name="communication_tone" value="casual" class="cob-radio" required>
                                    <span class="cob-radio-label">Casual</span>
                                </label>
                            </div>
                        </div>

                        <!-- Conditional Communication Tone Fields -->
                        <div class="cob-conditional-fields" data-show-when="communication_tone" data-show-value="casual" style="display: none;">
                            <div class="cob-form-group cob-form-group-full">
                                <label class="cob-label">EXPLAIN THE CASUAL TONE YOU WANT</label>
                                <textarea name="casual_tone_explanation" class="cob-textarea" placeholder="Describe the casual tone you prefer for communication"></textarea>
                            </div>
                        </div>
                        <div class="cob-conditional-fields" data-show-when="communication_tone" data-show-value="formal" style="display: none;">
                            <div class="cob-form-group cob-form-group-full">
                                <label class="cob-label">EXPLAIN THE FORMAL TONE YOU WANT</label>
                                <textarea name="formal_tone_explanation" class="cob-textarea" placeholder="Describe the formal tone you prefer for communication"></textarea>
                            </div>
                        </div>

                        <!-- Social Media Presence -->
                        <div class="cob-form-group cob-form-group-full">
                            <label class="cob-label">DO YOU HAVE BRAND ACCOUNTS? *</label>
                            <div class="cob-radio-group">
                                <label class="cob-radio-option">
                                    <input type="radio" name="brand_accounts" value="yes" class="cob-radio" required>
                                    <span class="cob-radio-label">Yes</span>
                                </label>
                                <label class="cob-radio-option">
                                    <input type="radio" name="brand_accounts" value="no" class="cob-radio" required>
                                    <span class="cob-radio-label">No</span>
                                </label>
                            </div>
                        </div>

                        <!-- Conditional Social Media Fields -->
                        <div class="cob-conditional-fields" data-show-when="brand_accounts" data-show-value="yes" style="display: none;">
                            <div class="cob-form-group cob-form-group-full">
                                <label class="cob-label">FACEBOOK PAGE</label>
                                <input type="text" name="facebook_page" class="cob-input" placeholder="Enter your Facebook page URL">
                            </div>
                            <div class="cob-form-group cob-form-group-full">
                                <label class="cob-label">INSTAGRAM ACCOUNT USERNAME</label>
                                <input type="text" name="instagram_username" class="cob-input" placeholder="Enter your Instagram username">
                            </div>
                        </div>

                        <!-- Industry & Competitors -->
                        <div class="cob-form-group cob-form-group-full">
                            <label class="cob-label">LIST THE MEMBERS/ENTITIES DOING YOUR BUSINESS COMPARED TO THE SERVICES (LIST 6) *</label>
                            <div class="cob-checkbox-group">
                                <label class="cob-checkbox-option">
                                    <input type="checkbox" name="industry_entities[]" value="direct_competitors" class="cob-checkbox">
                                    <span class="cob-checkbox-label">Direct Competitors</span>
                                </label>
                                <label class="cob-checkbox-option">
                                    <input type="checkbox" name="industry_entities[]" value="indirect_competitors" class="cob-checkbox">
                                    <span class="cob-checkbox-label">Indirect Competitors</span>
                                </label>
                                <label class="cob-checkbox-option">
                                    <input type="checkbox" name="industry_entities[]" value="new" class="cob-checkbox">
                                    <span class="cob-checkbox-label">New</span>
                                </label>
                                <label class="cob-checkbox-option">
                                    <input type="checkbox" name="industry_entities[]" value="sector" class="cob-checkbox">
                                    <span class="cob-checkbox-label">Sector</span>
                                </label>
                                <label class="cob-checkbox-option">
                                    <input type="checkbox" name="industry_entities[]" value="banking" class="cob-checkbox">
                                    <span class="cob-checkbox-label">Banking</span>
                                </label>
                                <label class="cob-checkbox-option">
                                    <input type="checkbox" name="industry_entities[]" value="services" class="cob-checkbox">
                                    <span class="cob-checkbox-label">Services</span>
                                </label>
                                <label class="cob-checkbox-option">
                                    <input type="checkbox" name="industry_entities[]" value="fintech" class="cob-checkbox">
                                    <span class="cob-checkbox-label">Fintech</span>
                                </label>
                                <label class="cob-checkbox-option">
                                    <input type="checkbox" name="industry_entities[]" value="property" class="cob-checkbox">
                                    <span class="cob-checkbox-label">Property</span>
                                </label>
                                <label class="cob-checkbox-option">
                                    <input type="checkbox" name="industry_entities[]" value="insurance" class="cob-checkbox">
                                    <span class="cob-checkbox-label">Insurance</span>
                                </label>
                                <label class="cob-checkbox-option">
                                    <input type="checkbox" name="industry_entities[]" value="health_medical" class="cob-checkbox">
                                    <span class="cob-checkbox-label">Health/Medical</span>
                                </label>
                                <label class="cob-checkbox-option">
                                    <input type="checkbox" name="industry_entities[]" value="retail" class="cob-checkbox">
                                    <span class="cob-checkbox-label">Retail</span>
                                </label>
                                <label class="cob-checkbox-option">
                                    <input type="checkbox" name="industry_entities[]" value="travel" class="cob-checkbox">
                                    <span class="cob-checkbox-label">Travel</span>
                                </label>
                                <label class="cob-checkbox-option">
                                    <input type="checkbox" name="industry_entities[]" value="construction" class="cob-checkbox">
                                    <span class="cob-checkbox-label">Construction</span>
                                </label>
                                <label class="cob-checkbox-option">
                                    <input type="checkbox" name="industry_entities[]" value="technology" class="cob-checkbox">
                                    <span class="cob-checkbox-label">Technology</span>
                                </label>
                                <label class="cob-checkbox-option">
                                    <input type="checkbox" name="industry_entities[]" value="education" class="cob-checkbox">
                                    <span class="cob-checkbox-label">Education</span>
                                </label>
                                <label class="cob-checkbox-option">
                                    <input type="checkbox" name="industry_entities[]" value="gaming" class="cob-checkbox">
                                    <span class="cob-checkbox-label">Gaming</span>
                                </label>
                                <label class="cob-checkbox-option">
                                    <input type="checkbox" name="industry_entities[]" value="food_beverage" class="cob-checkbox">
                                    <span class="cob-checkbox-label">Food & Beverage</span>
                                </label>
                            </div>
                        </div>

                        <!-- Conditional Industry Fields -->
                        <div class="cob-conditional-fields" data-show-when="industry_entities" data-show-value="other" style="display: none;">
                            <div class="cob-form-group cob-form-group-full">
                                <label class="cob-label">OTHER, PLEASE EXPLAIN THE FIELD (NO MATCH)</label>
                                <input type="text" name="industry_entities_other" class="cob-input" placeholder="Specify other industry entities">
                            </div>
                        </div>
                        <div class="cob-form-group cob-form-group-full">
                            <label class="cob-label">EXPLAIN THE STATUS OF YOUR INDUSTRY</label>
                            <textarea name="industry_status" class="cob-textarea" placeholder="Describe the current status and trends in your industry"></textarea>
                        </div>

                        <!-- Business Insights -->
                        <div class="cob-form-group cob-form-group-full">
                            <label class="cob-label">DO YOU RECEIVE INSIGHTS ON YOUR MARKET? *</label>
                            <div class="cob-radio-group">
                                <label class="cob-radio-option">
                                    <input type="radio" name="market_insights" value="yes" class="cob-radio" required>
                                    <span class="cob-radio-label">Yes</span>
                                </label>
                                <label class="cob-radio-option">
                                    <input type="radio" name="market_insights" value="no" class="cob-radio" required>
                                    <span class="cob-radio-label">No</span>
                                </label>
                            </div>
                        </div>

                        <!-- Media Insights -->
                        <div class="cob-form-group cob-form-group-full">
                            <label class="cob-label">DO YOU HAVE A CONTENT/SOCIAL MEDIA? *</label>
                            <div class="cob-radio-group">
                                <label class="cob-radio-option">
                                    <input type="radio" name="content_social_media" value="yes" class="cob-radio" required>
                                    <span class="cob-radio-label">Yes</span>
                                </label>
                                <label class="cob-radio-option">
                                    <input type="radio" name="content_social_media" value="no" class="cob-radio" required>
                                    <span class="cob-radio-label">No</span>
                                </label>
                            </div>
                        </div>

                        <!-- Business Performance -->
                        <div class="cob-form-group cob-form-group-full">
                            <label class="cob-label">CAN YOU EXPLAIN TO US WHAT ELEMENTS SHOULD YOU FOCUS ON YOUR BUSINESS? *</label>
                            <div class="cob-radio-group">
                                <label class="cob-radio-option">
                                    <input type="radio" name="business_focus_elements" value="yes" class="cob-radio" required>
                                    <span class="cob-radio-label">Yes</span>
                                </label>
                                <label class="cob-radio-option">
                                    <input type="radio" name="business_focus_elements" value="no" class="cob-radio" required>
                                    <span class="cob-radio-label">No</span>
                                </label>
                            </div>
                        </div>

                        <!-- Social Media Strategy -->
                        <div class="cob-form-group">
                            <label class="cob-label">PLEASE PROVIDE LINKS TO ANY SOCIAL MEDIA ACCOUNTS/GROUPS</label>
                            <input type="text" name="social_media_accounts" class="cob-input" placeholder="Enter social media account URLs">
                        </div>
                        <div class="cob-form-group">
                            <label class="cob-label">FACEBOOK ACCOUNTS URL</label>
                            <input type="text" name="facebook_accounts_url" class="cob-input" placeholder="Enter Facebook accounts URL">
                        </div>
                        <div class="cob-form-group">
                            <label class="cob-label">FACEBOOK PAGE URL</label>
                            <input type="text" name="facebook_page_url" class="cob-input" placeholder="Enter Facebook page URL">
                        </div>
                        <div class="cob-form-group">
                            <label class="cob-label">TWITTER ACCOUNTS URL</label>
                            <input type="text" name="twitter_accounts_url" class="cob-input" placeholder="Enter Twitter accounts URL">
                        </div>
                        <div class="cob-form-group">
                            <label class="cob-label">INSTAGRAM PAGE URL</label>
                            <input type="text" name="instagram_page_url" class="cob-input" placeholder="Enter Instagram page URL">
                        </div>

                        <!-- Target Audience -->
                        <div class="cob-form-group cob-form-group-full">
                            <label class="cob-label">DESCRIBE YOUR IDEAL CUSTOMER, THEIR PREFERENCES AND WHAT YOU KNOW ABOUT YOUR IDEAL CUSTOMER *</label>
                            <textarea name="ideal_customer_description" class="cob-textarea" placeholder="Describe your ideal customer profile and preferences" required></textarea>
                        </div>
                        <div class="cob-form-group cob-form-group-full">
                            <label class="cob-label">DESCRIBE WHAT YOU KNOW ABOUT OUR POTENTIAL CLIENT/PARTNER VIEW YOUR SKILLS *</label>
                            <textarea name="potential_client_view" class="cob-textarea" placeholder="Describe how potential clients view your skills" required></textarea>
                        </div>

                        <!-- Demographics -->
                        <div class="cob-form-group cob-form-group-full">
                            <label class="cob-label">TARGET AUDIENCE AGE RANGE *</label>
                            <div class="cob-checkbox-group">
                                <label class="cob-checkbox-option">
                                    <input type="checkbox" name="target_age_range[]" value="18-24" class="cob-checkbox">
                                    <span class="cob-checkbox-label">18-24 (Gen Z)</span>
                                </label>
                                <label class="cob-checkbox-option">
                                    <input type="checkbox" name="target_age_range[]" value="25-40" class="cob-checkbox">
                                    <span class="cob-checkbox-label">25-40 (Millennials)</span>
                                </label>
                                <label class="cob-checkbox-option">
                                    <input type="checkbox" name="target_age_range[]" value="41-56" class="cob-checkbox">
                                    <span class="cob-checkbox-label">41-56 (Gen X)</span>
                                </label>
                                <label class="cob-checkbox-option">
                                    <input type="checkbox" name="target_age_range[]" value="57-75" class="cob-checkbox">
                                    <span class="cob-checkbox-label">57-75 (Baby Boomers)</span>
                                </label>
                                <label class="cob-checkbox-option">
                                    <input type="checkbox" name="target_age_range[]" value="76+" class="cob-checkbox">
                                    <span class="cob-checkbox-label">76+ (Silent Gen)</span>
                                </label>
                            </div>
                        </div>

                        <div class="cob-form-group cob-form-group-full">
                            <label class="cob-label">WHAT PROBLEMS DO YOU SOLVE *</label>
                            <textarea name="problems_solved" class="cob-textarea" placeholder="Describe the problems your business solves" required></textarea>
                        </div>

                        <!-- Business Challenges -->
                        <div class="cob-form-group cob-form-group-full">
                            <label class="cob-label">WHAT ARE THE CHALLENGES AND OFTEN HEARD NEGATIVE FEEDBACK FROM YOUR BUSINESS/SERVICE? *</label>
                            <div class="cob-radio-group">
                                <label class="cob-radio-option">
                                    <input type="radio" name="business_challenges" value="yes" class="cob-radio" required>
                                    <span class="cob-radio-label">Yes</span>
                                </label>
                                <label class="cob-radio-option">
                                    <input type="radio" name="business_challenges" value="no" class="cob-radio" required>
                                    <span class="cob-radio-label">No</span>
                                </label>
                            </div>
                        </div>

                        <!-- Product/Service Timing -->
                        <div class="cob-form-group cob-form-group-full">
                            <label class="cob-label">DOES YOUR PRODUCT/SERVICE REQUIRE TRACKING/ACCOUNTING? *</label>
                            <div class="cob-radio-group">
                                <label class="cob-radio-option">
                                    <input type="radio" name="tracking_accounting" value="high" class="cob-radio" required>
                                    <span class="cob-radio-label">High</span>
                                </label>
                                <label class="cob-radio-option">
                                    <input type="radio" name="tracking_accounting" value="mid" class="cob-radio" required>
                                    <span class="cob-radio-label">Mid</span>
                                </label>
                                <label class="cob-radio-option">
                                    <input type="radio" name="tracking_accounting" value="low" class="cob-radio" required>
                                    <span class="cob-radio-label">Low</span>
                                </label>
                            </div>
                        </div>

                        <!-- Additional Information -->
                        <div class="cob-form-group cob-form-group-full">
                            <label class="cob-label">ADDITIONAL INFORMATION</label>
                            <textarea name="additional_information" class="cob-textarea" placeholder="Any additional information you'd like to share"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="cob-form-actions">
                    <button type="button" class="cob-btn cob-btn-secondary" id="cob-previous-btn">
                        PREVIOUS
                    </button>
                    <button type="button" class="cob-btn cob-btn-primary" id="cob-continue-btn">
                        CONTINUE
                    </button>
                    <button type="submit" class="cob-btn cob-btn-primary" id="cob-submit-btn">
                        SUBMIT FORM
                    </button>
                </div>
            </form>

            <!-- Save Status -->
            <div class="cob-save-status" id="cob-save-status" style="display: none;">
                <span id="cob-save-text"></span>
            </div>
        </main>
    </div>
</div>
</div>