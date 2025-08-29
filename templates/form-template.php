<?php
/**
 * Complete Client Onboarding Form Template - FLUX Brand
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div id="cob-form-container" class="cob-form-container">
    <!-- Start Page -->
    <div id="cob-start-page" class="cob-page cob-page-active">
        <div class="cob-start-container">
            <header class="cob-start-header">
                <div class="cob-start-branding">
                    <h1 class="cob-start-logo">FLUX</h1>
                </div>
            </header>
            <main class="cob-start-content">
                <div class="cob-start-content-wrapper">
                    <div class="cob-start-heading">
                        <span class="cob-heading-line-1">CLIENT ONBOARDING</span>
                        <span class="cob-heading-line-2">FORM</span>
                    </div>
                    <div class="cob-start-description">
                        Welcome to FLUX! We're excited to help you get started with your digital marketing journey. 
                        This comprehensive onboarding form will help us understand your business, goals, and requirements 
                        so we can create the perfect strategy for your success.
                    </div>
                    <button type="button" class="cob-start-btn" id="cob-start-form">
                        START
                    </button>
                </div>
            </main>
        </div>
    </div>

    <!-- Thank You Page -->
    <div id="cob-thank-you-page" class="cob-page">
        <?php include_once(plugin_dir_path(__FILE__) . 'thank-you-page.php'); ?>
    </div>

    <!-- Main Form Page -->
    <div id="cob-form-page" class="cob-page">
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
            <!-- Mobile Tabbed Navigation -->
            <div class="cob-mobile-tabs">
                <div class="cob-mobile-tabs-header">
                    <div class="cob-mobile-tabs-list">
                        <div class="cob-mobile-tab active" data-step="1">
                            <span class="cob-mobile-tab-number">1</span>
                            <span class="cob-mobile-tab-name">CLIENT INFO</span>
                        </div>
                        <div class="cob-mobile-tab" data-step="2">
                            <span class="cob-mobile-tab-number">2</span>
                            <span class="cob-mobile-tab-name">TECHNICAL</span>
                        </div>
                        <div class="cob-mobile-tab" data-step="3">
                            <span class="cob-mobile-tab-number">3</span>
                            <span class="cob-mobile-tab-name">REPORTING</span>
                        </div>
                        <div class="cob-mobile-tab" data-step="4">
                            <span class="cob-mobile-tab-number">4</span>
                            <span class="cob-mobile-tab-name">MARKETING</span>
                        </div>
                    </div>
                    <div class="cob-mobile-progress">
                        <div class="cob-mobile-progress-text">PROGRESS</div>
                        <div class="cob-mobile-progress-bar">
                            <div class="cob-mobile-progress-fill" style="width: 25%"></div>
                        </div>
                    </div>
                </div>
            </div>

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

                <form id="cob-onboarding-form" class="cob-form" enctype="multipart/form-data">
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
                            <div class="cob-form-group">
                                <label class="cob-label">CURRENT WEBSITE *</label>
                                <input type="url" name="current_website" class="cob-input" placeholder="Enter your current website URL" required>
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

                            <!-- Address Information -->
                            <div class="cob-form-group">
                                <label class="cob-label">ADDRESS LINE 1 *</label>
                                <input type="text" name="address_line_1" class="cob-input" placeholder="Enter address line 1" required>
                            </div>
                            <div class="cob-form-group">
                                <label class="cob-label">ADDRESS LINE 2</label>
                                <input type="text" name="address_line_2" class="cob-input" placeholder="Enter address line 2 (optional)">
                            </div>
                            <div class="cob-form-group">
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

                            <!-- Preferred Contact Method -->
                            <div class="cob-form-group cob-form-group-full">
                                <label class="cob-label">PREFERRED CONTACT METHOD *</label>
                                <div class="cob-radio-group">
                                    <label class="cob-radio-option">
                                        <input type="radio" name="preferred_contact_method" value="email" class="cob-radio" required>
                                        <span class="cob-radio-label">Email</span>
                                    </label>
                                    <label class="cob-radio-option">
                                        <input type="radio" name="preferred_contact_method" value="phone" class="cob-radio" required>
                                        <span class="cob-radio-label">Phone</span>
                                    </label>
                                    <label class="cob-radio-option">
                                        <input type="radio" name="preferred_contact_method" value="whatsapp" class="cob-radio" required>
                                        <span class="cob-radio-label">WhatsApp</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Technical Information -->
                    <div class="cob-step-content" data-step="2">
                        <div class="cob-form-grid">
                            <!-- Website Information -->
                            <div class="cob-form-group">
                                <label class="cob-label">DO YOU HAVE A WEBSITE? *</label>
                                <div class="cob-radio-group">
                                    <label class="cob-radio-option">
                                        <input type="radio" name="has_website" value="yes" class="cob-radio" required>
                                        <span class="cob-radio-label">Yes</span>
                                    </label>
                                    <label class="cob-radio-option">
                                        <input type="radio" name="has_website" value="no" class="cob-radio" required>
                                        <span class="cob-radio-label">No</span>
                                    </label>
                                </div>
                            </div>

                            <div class="cob-form-group cob-conditional-fields" data-show-when="has_website" data-show-value="yes">
                                <label class="cob-label">WEBSITE URL</label>
                                <input type="url" name="website_url" class="cob-input" placeholder="Enter your website URL">
                            </div>

                            <div class="cob-form-group cob-conditional-fields" data-show-when="has_website" data-show-value="yes">
                                <label class="cob-label">WEBSITE PASSWORD (IF REQUIRED)</label>
                                <input type="password" name="website_password" class="cob-input" placeholder="Enter website password if required">
                            </div>

                            <!-- Website Hosting Information -->
                            <div class="cob-form-group">
                                <label class="cob-label">WEBSITE HOSTING COMPANY</label>
                                <input type="text" name="website_hosting_company" class="cob-input" placeholder="Enter hosting company name">
                            </div>

                            <div class="cob-form-group">
                                <label class="cob-label">WEBSITE CONTACT EMAIL</label>
                                <input type="email" name="website_contact_email" class="cob-input" placeholder="Enter hosting contact email">
                            </div>

                            <!-- Domain Information -->
                            <div class="cob-form-group">
                                <label class="cob-label">DOMAIN HOSTING COMPANY</label>
                                <input type="text" name="domain_hosting_company" class="cob-input" placeholder="Enter domain hosting company">
                            </div>

                            <div class="cob-form-group">
                                <label class="cob-label">DOMAIN CONTACT EMAIL</label>
                                <input type="email" name="domain_contact_email" class="cob-input" placeholder="Enter domain contact email">
                            </div>

                            <!-- CMS Information -->
                            <div class="cob-form-group">
                                <label class="cob-label">CMS LINK</label>
                                <input type="url" name="cms_link" class="cob-input" placeholder="Enter CMS admin URL">
                            </div>

                            <div class="cob-form-group">
                                <label class="cob-label">CMS USERNAME</label>
                                <input type="text" name="cms_username" class="cob-input" placeholder="Enter CMS username">
                            </div>

                            <div class="cob-form-group">
                                <label class="cob-label">CMS PASSWORD</label>
                                <input type="password" name="cms_password" class="cob-input" placeholder="Enter CMS password">
                            </div>

                            <!-- Third Party Integrations -->
                            <div class="cob-form-group cob-form-group-full">
                                <label class="cob-label">DO YOU HAVE ANY 3RD PARTY WEBSITE INTEGRATIONS IN PLACE?</label>
                                <div class="cob-radio-group">
                                    <label class="cob-radio-option">
                                        <input type="radio" name="third_party_integrations_radio" value="yes" class="cob-radio">
                                        <span class="cob-radio-label">Yes</span>
                                    </label>
                                    <label class="cob-radio-option">
                                        <input type="radio" name="third_party_integrations_radio" value="no" class="cob-radio">
                                        <span class="cob-radio-label">No</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Conditional 3rd Party Integration Fields -->
                            <div class="cob-form-group cob-conditional-fields" data-show-when="third_party_integrations_radio" data-show-value="yes">
                                <div class="cob-form-group">
                                    <label class="cob-label">3RD PARTY INTEGRATION NAME</label>
                                    <input type="text" name="third_party_integration_name" class="cob-input" placeholder="Enter integration name">
                                </div>
                                <div class="cob-form-group">
                                    <label class="cob-label">3RD PARTY PRIMARY CONTACT NUMBER</label>
                                    <input type="tel" name="third_party_primary_contact_number" class="cob-input" placeholder="Enter contact number">
                                </div>
                                <div class="cob-form-group cob-form-group-full">
                                    <label class="cob-label">3RD PARTY PRIMARY CONTACT EMAIL</label>
                                    <input type="email" name="third_party_primary_contact_email" class="cob-input" placeholder="Enter contact email">
                                </div>
                            </div>

                            <!-- Current CMS -->
                            <div class="cob-form-group cob-form-group-full">
                                <label class="cob-label">CURRENT CONTENT MANAGEMENT SYSTEM (IF APPLICABLE)</label>
                                <input type="text" name="current_content_management_system" class="cob-input" placeholder="Enter your current CMS">
                            </div>

                            <!-- CRM Integration -->
                            <div class="cob-form-group cob-form-group-full">
                                <label class="cob-label">WHICH CRM DO YOU CURRENTLY USE? (IF APPLICABLE)</label>
                                <input type="text" name="crm_integration" class="cob-input" placeholder="Enter your CRM system">
                            </div>

                            <!-- Booking Engine -->
                            <div class="cob-form-group cob-form-group-full">
                                <label class="cob-label">BOOKING ENGINE (IF APPLICABLE)</label>
                                <div class="cob-form-group">
                                    <label class="cob-label">BOOKING ENGINE NAME</label>
                                    <input type="text" name="booking_engine_name" class="cob-input" placeholder="Enter booking engine name">
                                </div>
                                <div class="cob-form-group">
                                    <label class="cob-label">USERNAME</label>
                                    <input type="text" name="booking_engine_username" class="cob-input" placeholder="Enter username">
                                </div>
                                <div class="cob-form-group">
                                    <label class="cob-label">PASSWORD</label>
                                    <input type="password" name="booking_engine_password" class="cob-input" placeholder="Enter password">
                                </div>
                                <div class="cob-form-group">
                                    <label class="cob-label">PRIMARY CONTACT EMAIL</label>
                                    <input type="email" name="booking_engine_contact_email" class="cob-input" placeholder="Enter contact email">
                                </div>
                            </div>

                            <!-- Technical Objectives -->
                            <div class="cob-form-group cob-form-group-full">
                                <label class="cob-label">TECHNICAL OBJECTIVES</label>
                                <textarea name="technical_objective" class="cob-textarea" placeholder="Describe your technical goals and requirements"></textarea>
                            </div>

                            <!-- Google Analytics -->
                            <div class="cob-form-group">
                                <label class="cob-label">DO YOU HAVE A GOOGLE ANALYTICS ACCOUNT?</label>
                                <div class="cob-radio-group">
                                    <label class="cob-radio-option">
                                        <input type="radio" name="google_analytics_radio" value="yes" class="cob-radio">
                                        <span class="cob-radio-label">Yes</span>
                                    </label>
                                    <label class="cob-radio-option">
                                        <input type="radio" name="google_analytics_radio" value="no" class="cob-radio">
                                        <span class="cob-radio-label">No</span>
                                    </label>
                                </div>
                            </div>

                            <div class="cob-form-group cob-conditional-fields" data-show-when="google_analytics_radio" data-show-value="yes">
                                <label class="cob-label">GOOGLE ANALYTICS ACCOUNT ID (IF APPLICABLE)</label>
                                <input type="text" name="google_analytics_account" class="cob-input" placeholder="Enter GA account ID">
                            </div>

                            <!-- Google Tag Manager -->
                            <div class="cob-form-group">
                                <label class="cob-label">DO YOU HAVE A GOOGLE TAG MANAGER ACCOUNT?</label>
                                <div class="cob-radio-group">
                                    <label class="cob-radio-option">
                                        <input type="radio" name="google_tag_manager_radio" value="yes" class="cob-radio">
                                        <span class="cob-radio-label">Yes</span>
                                    </label>
                                    <label class="cob-radio-option">
                                        <input type="radio" name="google_tag_manager_radio" value="no" class="cob-radio">
                                        <span class="cob-radio-label">No</span>
                                    </label>
                                </div>
                            </div>

                            <div class="cob-form-group cob-conditional-fields" data-show-when="google_tag_manager_radio" data-show-value="yes">
                                <label class="cob-label">GOOGLE TAG MANAGER ACCOUNT ADMINISTRATOR (IF APPLICABLE)</label>
                                <input type="text" name="google_tag_manager_account" class="cob-input" placeholder="Enter GTM administrator">
                            </div>

                            <!-- Google Ads -->
                            <div class="cob-form-group">
                                <label class="cob-label">DO YOU HAVE A GOOGLE ADS ACCOUNT?</label>
                                <div class="cob-radio-group">
                                    <label class="cob-radio-option">
                                        <input type="radio" name="google_ads_radio" value="yes" class="cob-radio">
                                        <span class="cob-radio-label">Yes</span>
                                    </label>
                                    <label class="cob-radio-option">
                                        <input type="radio" name="google_ads_radio" value="no" class="cob-radio">
                                        <span class="cob-radio-label">No</span>
                                    </label>
                                </div>
                            </div>

                            <div class="cob-form-group cob-conditional-fields" data-show-when="google_ads_radio" data-show-value="yes">
                                <label class="cob-label">GOOGLE ADS ACCOUNT ADMINISTRATOR (IF APPLICABLE)</label>
                                <input type="text" name="google_ads_account" class="cob-input" placeholder="Enter Google Ads administrator">
                                <label class="cob-label">PROVIDE THE GOOGLE ADS CUSTOMER ID (CID) (IF APPLICABLE)</label>
                                <input type="text" name="google_ads_customer_id" class="cob-input" placeholder="Enter Customer ID">
                            </div>

                            <!-- Meta Business Manager -->
                            <div class="cob-form-group">
                                <label class="cob-label">DO YOU HAVE A META/FACEBOOK BUSINESS MANAGER ACCOUNT?</label>
                                <div class="cob-radio-group">
                                    <label class="cob-radio-option">
                                        <input type="radio" name="meta_business_manager_radio" value="yes" class="cob-radio">
                                        <span class="cob-radio-label">Yes</span>
                                    </label>
                                    <label class="cob-radio-option">
                                        <input type="radio" name="meta_business_manager_radio" value="no" class="cob-radio">
                                        <span class="cob-radio-label">No</span>
                                    </label>
                                </div>
                            </div>

                            <div class="cob-form-group cob-conditional-fields" data-show-when="meta_business_manager_radio" data-show-value="yes">
                                <label class="cob-label">META/FACEBOOK BUSINESS MANAGER ACCOUNT ADMINISTRATOR (IF APPLICABLE)</label>
                                <input type="text" name="meta_business_manager_account" class="cob-input" placeholder="Enter Business Manager administrator">
                                <label class="cob-label">PROVIDE THE BUSINESS MANAGER ID (IF APPLICABLE)</label>
                                <input type="text" name="meta_business_manager_id" class="cob-input" placeholder="Enter Business Manager ID">
                            </div>

                            <!-- Paid Media History -->
                            <div class="cob-form-group cob-form-group-full">
                                <label class="cob-label">WHICH PAID MEDIA ADS HAVE YOU RUN BEFORE?</label>
                                <div class="cob-checkbox-group">
                                    <label class="cob-checkbox-option">
                                        <input type="checkbox" name="paid_media_history[]" value="meta" class="cob-checkbox">
                                        <span class="cob-checkbox-label">META</span>
                                    </label>
                                    <label class="cob-checkbox-option">
                                        <input type="checkbox" name="paid_media_history[]" value="google" class="cob-checkbox">
                                        <span class="cob-checkbox-label">GOOGLE</span>
                                    </label>
                                    <label class="cob-checkbox-option">
                                        <input type="checkbox" name="paid_media_history[]" value="linkedin" class="cob-checkbox">
                                        <span class="cob-checkbox-label">LINKEDIN</span>
                                    </label>
                                    <label class="cob-checkbox-option">
                                        <input type="checkbox" name="paid_media_history[]" value="tiktok" class="cob-checkbox">
                                        <span class="cob-checkbox-label">TIKTOK</span>
                                    </label>
                                    <label class="cob-checkbox-option">
                                        <input type="checkbox" name="paid_media_history[]" value="other" class="cob-checkbox">
                                        <span class="cob-checkbox-label">OTHER</span>
                                    </label>
                                </div>
                            </div>

                            <div class="cob-form-group cob-conditional-fields" data-show-when="paid_media_history" data-show-value="other">
                                <label class="cob-label">IF OTHER, PLEASE SPECIFY</label>
                                <input type="text" name="other_paid_media_specify" class="cob-input" placeholder="Specify other paid media">
                            </div>

                            <!-- Current Paid Media -->
                            <div class="cob-form-group cob-form-group-full">
                                <label class="cob-label">WHICH OF THE ABOVE PAID MEDIA ADS ARE YOU STILL RUNNING?</label>
                                <div class="cob-checkbox-group">
                                    <label class="cob-checkbox-option">
                                        <input type="checkbox" name="current_paid_media[]" value="meta" class="cob-checkbox">
                                        <span class="cob-checkbox-label">META</span>
                                    </label>
                                    <label class="cob-checkbox-option">
                                        <input type="checkbox" name="current_paid_media[]" value="google" class="cob-checkbox">
                                        <span class="cob-checkbox-label">GOOGLE</span>
                                    </label>
                                    <label class="cob-checkbox-option">
                                        <input type="checkbox" name="current_paid_media[]" value="linkedin" class="cob-checkbox">
                                        <span class="cob-checkbox-label">LINKEDIN</span>
                                    </label>
                                    <label class="cob-checkbox-option">
                                        <input type="checkbox" name="current_paid_media[]" value="tiktok" class="cob-checkbox">
                                        <span class="cob-checkbox-label">TIKTOK</span>
                                    </label>
                                    <label class="cob-checkbox-option">
                                        <input type="checkbox" name="current_paid_media[]" value="other" class="cob-checkbox">
                                        <span class="cob-checkbox-label">OTHER</span>
                                    </label>
                                </div>
                            </div>

                            <div class="cob-form-group cob-conditional-fields" data-show-when="current_paid_media" data-show-value="other">
                                <label class="cob-label">IF OTHER, PLEASE SPECIFY</label>
                                <input type="text" name="other_current_paid_media_specify" class="cob-input" placeholder="Specify other current paid media">
                            </div>

                            <!-- Social Media Presence -->
                            <div class="cob-form-group cob-form-group-full">
                                <label class="cob-label">WHICH SOCIAL MEDIA PLATFORMS DO YOU USE? *</label>
                                <div class="cob-checkbox-group">
                                    <label class="cob-checkbox-option">
                                        <input type="checkbox" name="social_media_platforms[]" value="facebook" class="cob-checkbox">
                                        <span class="cob-checkbox-label">Facebook</span>
                                    </label>
                                    <label class="cob-checkbox-option">
                                        <input type="checkbox" name="social_media_platforms[]" value="instagram" class="cob-checkbox">
                                        <span class="cob-checkbox-label">Instagram</span>
                                    </label>
                                    <label class="cob-checkbox-option">
                                        <input type="checkbox" name="social_media_platforms[]" value="twitter" class="cob-checkbox">
                                        <span class="cob-checkbox-label">Twitter/X</span>
                                    </label>
                                    <label class="cob-checkbox-option">
                                        <input type="checkbox" name="social_media_platforms[]" value="linkedin" class="cob-checkbox">
                                        <span class="cob-checkbox-label">LinkedIn</span>
                                    </label>
                                    <label class="cob-checkbox-option">
                                        <input type="checkbox" name="social_media_platforms[]" value="tiktok" class="cob-checkbox">
                                        <span class="cob-checkbox-label">TikTok</span>
                                    </label>
                                    <label class="cob-checkbox-option">
                                        <input type="checkbox" name="social_media_platforms[]" value="youtube" class="cob-checkbox">
                                        <span class="cob-checkbox-label">YouTube</span>
                                    </label>
                                    <label class="cob-checkbox-option">
                                        <input type="checkbox" name="social_media_platforms[]" value="none" class="cob-checkbox">
                                        <span class="cob-checkbox-label">None</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Marketing Goals -->
                            <div class="cob-form-group cob-form-group-full">
                                <label class="cob-label">MARKETING GOALS *</label>
                                <div class="cob-checkbox-group">
                                    <label class="cob-checkbox-option">
                                        <input type="checkbox" name="marketing_goals[]" value="increase_brand_awareness" class="cob-checkbox">
                                        <span class="cob-checkbox-label">Increase Brand Awareness</span>
                                    </label>
                                    <label class="cob-checkbox-option">
                                        <input type="checkbox" name="marketing_goals[]" value="generate_leads" class="cob-checkbox">
                                        <span class="cob-checkbox-label">Generate Leads</span>
                                    </label>
                                    <label class="cob-checkbox-option">
                                        <input type="checkbox" name="marketing_goals[]" value="increase_sales" class="cob-checkbox">
                                        <span class="cob-checkbox-label">Increase Sales</span>
                                    </label>
                                    <label class="cob-checkbox-option">
                                        <input type="checkbox" name="marketing_goals[]" value="improve_customer_retention" class="cob-checkbox">
                                        <span class="cob-checkbox-label">Improve Customer Retention</span>
                                    </label>
                                    <label class="cob-checkbox-option">
                                        <input type="checkbox" name="marketing_goals[]" value="expand_market_reach" class="cob-checkbox">
                                        <span class="cob-checkbox-label">Expand Market Reach</span>
                                    </label>
                                    <label class="cob-checkbox-option">
                                        <input type="checkbox" name="marketing_goals[]" value="other" class="cob-checkbox">
                                        <span class="cob-checkbox-label">Other</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Industry -->
                            <div class="cob-form-group cob-form-group-full">
                                <label class="cob-label">INDUSTRY *</label>
                                <div class="cob-checkbox-group">
                                    <label class="cob-checkbox-option">
                                        <input type="checkbox" name="industry[]" value="ecommerce" class="cob-checkbox">
                                        <span class="cob-checkbox-label">E-commerce</span>
                                    </label>
                                    <label class="cob-checkbox-option">
                                        <input type="checkbox" name="industry[]" value="healthcare" class="cob-checkbox">
                                        <span class="cob-checkbox-label">Healthcare</span>
                                    </label>
                                    <label class="cob-checkbox-option">
                                        <input type="checkbox" name="industry[]" value="finance" class="cob-checkbox">
                                        <span class="cob-checkbox-label">Finance</span>
                                    </label>
                                    <label class="cob-checkbox-option">
                                        <input type="checkbox" name="industry[]" value="education" class="cob-checkbox">
                                        <span class="cob-checkbox-label">Education</span>
                                    </label>
                                    <label class="cob-checkbox-option">
                                        <input type="checkbox" name="industry[]" value="real_estate" class="cob-checkbox">
                                        <span class="cob-checkbox-label">Real Estate</span>
                                    </label>
                                    <label class="cob-checkbox-option">
                                        <input type="checkbox" name="industry[]" value="technology" class="cob-checkbox">
                                        <span class="cob-checkbox-label">Technology</span>
                                    </label>
                                    <label class="cob-checkbox-option">
                                        <input type="checkbox" name="industry[]" value="hospitality" class="cob-checkbox">
                                        <span class="cob-checkbox-label">Hospitality</span>
                                    </label>
                                    <label class="cob-checkbox-option">
                                        <input type="checkbox" name="industry[]" value="other" class="cob-checkbox">
                                        <span class="cob-checkbox-label">Other</span>
                                    </label>
                                </div>
                            </div>

                            <!-- File Uploads -->
                            <div class="cob-form-group cob-form-group-full">
                                <label class="cob-label">UPLOAD YOUR LOGO (IF AVAILABLE)</label>
                                <input type="file" name="logo_file" class="cob-input" accept="image/*">
                            </div>

                            <div class="cob-form-group cob-form-group-full">
                                <label class="cob-label">UPLOAD BRAND GUIDELINES (IF AVAILABLE)</label>
                                <input type="file" name="brand_guidelines" class="cob-input" accept=".pdf,.doc,.docx">
                            </div>

                            <!-- Current Analytics -->
                            <div class="cob-form-group">
                                <label class="cob-label">DO YOU HAVE GOOGLE ANALYTICS SET UP? *</label>
                                <div class="cob-radio-group">
                                    <label class="cob-radio-option">
                                        <input type="radio" name="has_google_analytics" value="yes" class="cob-radio" required>
                                        <span class="cob-radio-label">Yes</span>
                                    </label>
                                    <label class="cob-radio-option">
                                        <input type="radio" name="has_google_analytics" value="no" class="cob-radio" required>
                                        <span class="cob-radio-label">No</span>
                                    </label>
                                </div>
                            </div>

                            <div class="cob-form-group cob-conditional-fields" data-show-when="has_google_analytics" data-show-value="yes">
                                <label class="cob-label">GOOGLE ANALYTICS PROPERTY ID</label>
                                <input type="text" name="ga_property_id" class="cob-input" placeholder="Enter GA Property ID (e.g., GA4-123456789)">
                            </div>

                            <div class="cob-form-group">
                                <label class="cob-label">DO YOU HAVE GOOGLE SEARCH CONSOLE SET UP? *</label>
                                <div class="cob-radio-group">
                                    <label class="cob-radio-option">
                                        <input type="radio" name="has_search_console" value="yes" class="cob-radio" required>
                                        <span class="cob-radio-label">Yes</span>
                                    </label>
                                    <label class="cob-radio-option">
                                        <input type="radio" name="has_search_console" value="no" class="cob-radio" required>
                                        <span class="cob-radio-label">No</span>
                                    </label>
                                </div>
                            </div>

                            <div class="cob-form-group cob-conditional-fields" data-show-when="has_search_console" data-show-value="yes">
                                <label class="cob-label">GOOGLE SEARCH CONSOLE PROPERTY</label>
                                <input type="text" name="gsc_property" class="cob-input" placeholder="Enter GSC property URL">
                            </div>

                            <!-- Reporting Preferences -->
                            <div class="cob-form-group cob-form-group-full">
                                <label class="cob-label">HOW OFTEN WOULD YOU LIKE TO RECEIVE REPORTS? *</label>
                                <div class="cob-radio-group">
                                    <label class="cob-radio-option">
                                        <input type="radio" name="reporting_frequency" value="weekly" class="cob-radio" required>
                                        <span class="cob-radio-label">Weekly</span>
                                    </label>
                                    <label class="cob-radio-option">
                                        <input type="radio" name="reporting_frequency" value="biweekly" class="cob-radio" required>
                                        <span class="cob-radio-label">Bi-weekly</span>
                                    </label>
                                    <label class="cob-radio-option">
                                        <input type="radio" name="reporting_frequency" value="monthly" class="cob-radio" required>
                                        <span class="cob-radio-label">Monthly</span>
                                    </label>
                                    <label class="cob-radio-option">
                                        <input type="radio" name="reporting_frequency" value="quarterly" class="cob-radio" required>
                                        <span class="cob-radio-label">Quarterly</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Reporting Information -->
                    <div class="cob-step-content" data-step="3">
                        <div class="cob-form-grid">

                            <!-- Additional Reporting -->
                            <div class="cob-form-group cob-form-group-full">
                                <label class="cob-label">WHAT METRICS ARE MOST IMPORTANT TO YOU? *</label>
                                <div class="cob-checkbox-group">
                                    <label class="cob-checkbox-option">
                                        <input type="checkbox" name="important_metrics[]" value="website_traffic" class="cob-checkbox">
                                        <span class="cob-checkbox-label">Website Traffic</span>
                                    </label>
                                    <label class="cob-checkbox-option">
                                        <input type="checkbox" name="important_metrics[]" value="conversions" class="cob-checkbox">
                                        <span class="cob-checkbox-label">Conversions</span>
                                    </label>
                                    <label class="cob-checkbox-option">
                                        <input type="checkbox" name="important_metrics[]" value="keyword_rankings" class="cob-checkbox">
                                        <span class="cob-checkbox-label">Keyword Rankings</span>
                                    </label>
                                    <label class="cob-checkbox-option">
                                        <input type="checkbox" name="important_metrics[]" value="social_media_engagement" class="cob-checkbox">
                                        <span class="cob-checkbox-label">Social Media Engagement</span>
                                    </label>
                                    <label class="cob-checkbox-option">
                                        <input type="checkbox" name="important_metrics[]" value="roi" class="cob-checkbox">
                                        <span class="cob-checkbox-label">ROI</span>
                                    </label>
                                    <label class="cob-checkbox-option">
                                        <input type="checkbox" name="important_metrics[]" value="brand_awareness" class="cob-checkbox">
                                        <span class="cob-checkbox-label">Brand Awareness</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Main Objective -->
                            <div class="cob-form-group cob-form-group-full">
                                <label class="cob-label">MAIN OBJECTIVE *</label>
                                <textarea name="main_objective" class="cob-textarea" placeholder="What is your primary goal for this project?" required></textarea>
                            </div>

                            <!-- Brand Focus -->
                            <div class="cob-form-group cob-form-group-full">
                                <label class="cob-label">BRAND FOCUS</label>
                                <textarea name="brand_focus" class="cob-textarea" placeholder="Describe your brand focus and positioning"></textarea>
                            </div>

                            <!-- Commercial Objective -->
                            <div class="cob-form-group cob-form-group-full">
                                <label class="cob-label">COMMERCIAL OBJECTIVE</label>
                                <textarea name="commercial_objective" class="cob-textarea" placeholder="What are your commercial goals?"></textarea>
                            </div>

                            <!-- Push Impact -->
                            <div class="cob-form-group cob-form-group-full">
                                <label class="cob-label">PUSH IMPACT</label>
                                <textarea name="push_impact" class="cob-textarea" placeholder="What impact do you want to achieve?"></textarea>
                            </div>

                            <!-- Founder Inspiration -->
                            <div class="cob-form-group cob-form-group-full">
                                <label class="cob-label">FOUNDER INSPIRATION</label>
                                <textarea name="founder_inspiration" class="cob-textarea" placeholder="What inspired you to start this business?"></textarea>
                            </div>

                            <!-- Brand Tone & Mission -->
                            <div class="cob-form-group cob-form-group-full">
                                <label class="cob-label">BRAND TONE & MISSION</label>
                                <textarea name="brand_tone_mission" class="cob-textarea" placeholder="Describe your brand tone and mission statement"></textarea>
                            </div>

                            <!-- Brand Perception -->
                            <div class="cob-form-group cob-form-group-full">
                                <label class="cob-label">BRAND PERCEPTION</label>
                                <textarea name="brand_perception" class="cob-textarea" placeholder="How do you want your brand to be perceived?"></textarea>
                            </div>

                            <!-- Team Introduction -->
                            <div class="cob-form-group cob-form-group-full">
                                <label class="cob-label">GLOBAL TEAM INTRODUCTION</label>
                                <textarea name="global_team_introduction" class="cob-textarea" placeholder="Tell us about your team"></textarea>
                            </div>

                            <!-- Service Introduction -->
                            <div class="cob-form-group cob-form-group-full">
                                <label class="cob-label">SERVICE INTRODUCTION</label>
                                <textarea name="service_introduction" class="cob-textarea" placeholder="Describe your services in detail"></textarea>
                            </div>

                            <!-- Brand Lines -->
                            <div class="cob-form-group">
                                <label class="cob-label">BRAND LINE 1</label>
                                <input type="text" name="brand_line_1" class="cob-input" placeholder="Enter your first brand line">
                            </div>

                            <div class="cob-form-group">
                                <label class="cob-label">MISSION 1</label>
                                <textarea name="mission_1" class="cob-textarea" placeholder="Describe your first mission"></textarea>
                            </div>

                            <div class="cob-form-group">
                                <label class="cob-label">BRAND LINE 2</label>
                                <input type="text" name="brand_line_2" class="cob-input" placeholder="Enter your second brand line">
                            </div>

                            <div class="cob-form-group">
                                <label class="cob-label">MISSION 2</label>
                                <textarea name="mission_2" class="cob-textarea" placeholder="Describe your second mission"></textarea>
                            </div>

                            <div class="cob-form-group">
                                <label class="cob-label">BRAND LINE 3</label>
                                <input type="text" name="brand_line_3" class="cob-input" placeholder="Enter your third brand line">
                            </div>

                            <div class="cob-form-group">
                                <label class="cob-label">MISSION 3</label>
                                <textarea name="mission_3" class="cob-textarea" placeholder="Describe your third mission"></textarea>
                            </div>

                            <!-- Communication Tone -->
                            <div class="cob-form-group cob-form-group-full">
                                <label class="cob-label">COMMUNICATION TONE *</label>
                                <div class="cob-radio-group">
                                    <label class="cob-radio-option">
                                        <input type="radio" name="communication_tone_radio" value="professional" class="cob-radio" required>
                                        <span class="cob-radio-label">Professional</span>
                                    </label>
                                    <label class="cob-radio-option">
                                        <input type="radio" name="communication_tone_radio" value="casual" class="cob-radio" required>
                                        <span class="cob-radio-label">Casual</span>
                                    </label>
                                    <label class="cob-radio-option">
                                        <input type="radio" name="communication_tone_radio" value="friendly" class="cob-radio" required>
                                        <span class="cob-radio-label">Friendly</span>
                                    </label>
                                    <label class="cob-radio-option">
                                        <input type="radio" name="communication_tone_radio" value="formal" class="cob-radio" required>
                                        <span class="cob-radio-label">Formal</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Brand Accounts -->
                            <div class="cob-form-group cob-form-group-full">
                                <label class="cob-label">BRAND ACCOUNTS *</label>
                                <div class="cob-radio-group">
                                    <label class="cob-radio-option">
                                        <input type="radio" name="brand_accounts_radio" value="yes" class="cob-radio" required>
                                        <span class="cob-radio-label">Yes, we have brand accounts</span>
                                    </label>
                                    <label class="cob-radio-option">
                                        <input type="radio" name="brand_accounts_radio" value="no" class="cob-radio" required>
                                        <span class="cob-radio-label">No, we don't have brand accounts yet</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Industry Entities -->
                            <div class="cob-form-group cob-form-group-full">
                                <label class="cob-label">INDUSTRY ENTITIES</label>
                                <textarea name="industry_entities" class="cob-textarea" placeholder="List relevant industry organizations or entities"></textarea>
                            </div>

                            <!-- Market Insights -->
                            <div class="cob-form-group cob-form-group-full">
                                <label class="cob-label">MARKET INSIGHTS *</label>
                                <div class="cob-radio-group">
                                    <label class="cob-radio-option">
                                        <input type="radio" name="market_insights_radio" value="yes" class="cob-radio" required>
                                        <span class="cob-radio-label">Yes, we have market insights</span>
                                    </label>
                                    <label class="cob-radio-option">
                                        <input type="radio" name="market_insights_radio" value="no" class="cob-radio" required>
                                        <span class="cob-radio-label">No, we need help with market insights</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Content & Social Media -->
                            <div class="cob-form-group cob-form-group-full">
                                <label class="cob-label">CONTENT & SOCIAL MEDIA STRATEGY *</label>
                                <div class="cob-radio-group">
                                    <label class="cob-radio-option">
                                        <input type="radio" name="content_social_media_radio" value="yes" class="cob-radio" required>
                                        <span class="cob-radio-label">Yes, we have a strategy</span>
                                    </label>
                                    <label class="cob-radio-option">
                                        <input type="radio" name="content_social_media_radio" value="no" class="cob-radio" required>
                                        <span class="cob-radio-label">No, we need help developing a strategy</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Business Focus Elements -->
                            <div class="cob-form-group cob-form-group-full">
                                <label class="cob-label">BUSINESS FOCUS ELEMENTS *</label>
                                <div class="cob-radio-group">
                                    <label class="cob-radio-option">
                                        <input type="radio" name="business_focus_elements_radio" value="yes" class="cob-radio" required>
                                        <span class="cob-radio-label">Yes, we have defined focus elements</span>
                                    </label>
                                    <label class="cob-radio-option">
                                        <input type="radio" name="business_focus_elements_radio" value="no" class="cob-radio" required>
                                        <span class="cob-radio-label">No, we need help defining our focus</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Customer Description -->
                            <div class="cob-form-group cob-form-group-full">
                                <label class="cob-label">IDEAL CUSTOMER DESCRIPTION</label>
                                <textarea name="ideal_customer_description" class="cob-textarea" placeholder="Describe your ideal customer profile"></textarea>
                            </div>

                            <!-- Client View -->
                            <div class="cob-form-group cob-form-group-full">
                                <label class="cob-label">POTENTIAL CLIENT VIEW</label>
                                <textarea name="potential_client_view" class="cob-textarea" placeholder="How do potential clients view your business?"></textarea>
                            </div>

                            <!-- Target Demographics -->
                            <div class="cob-form-group">
                                <label class="cob-label">TARGET AGE RANGE *</label>
                                <input type="text" name="target_age_range" class="cob-input" placeholder="e.g., 25-45, 18-65" required>
                            </div>

                            <!-- Lead Source Markets -->
                            <div class="cob-form-group">
                                <label class="cob-label">LEAD SOURCE MARKETS *</label>
                                <input type="text" name="lead_source_markets" class="cob-input" placeholder="e.g., Local, National, International" required>
                            </div>

                            <!-- Lead Times -->
                            <div class="cob-form-group">
                                <label class="cob-label">LEAD TIMES *</label>
                                <input type="text" name="lead_times" class="cob-input" placeholder="e.g., 1-3 months, 3-6 months" required>
                            </div>

                            <!-- Problems & Challenges -->
                            <div class="cob-form-group cob-form-group-full">
                                <label class="cob-label">PROBLEMS SOLVED</label>
                                <textarea name="problems_solved" class="cob-textarea" placeholder="What problems does your business solve?"></textarea>
                            </div>

                            <div class="cob-form-group cob-form-group-full">
                                <label class="cob-label">BUSINESS CHALLENGES</label>
                                <textarea name="business_challenges" class="cob-textarea" placeholder="What challenges are you currently facing?"></textarea>
                            </div>

                            <!-- Tracking & Accounting -->
                            <div class="cob-form-group cob-form-group-full">
                                <label class="cob-label">TRACKING & ACCOUNTING</label>
                                <textarea name="tracking_accounting" class="cob-textarea" placeholder="Describe your tracking and accounting needs"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Step 4: Marketing Information -->
                    <div class="cob-step-content" data-step="4">
                        <div class="cob-form-grid">
                            <!-- Current Website -->
                            <div class="cob-form-group cob-form-group-full">
                                <label class="cob-label">CURRENT WEBSITE *</label>
                                <input type="url" name="current_website" class="cob-input" placeholder="Enter your current website URL" required>
                            </div>

                            <!-- Brand Guidelines Upload -->
                            <div class="cob-form-group cob-form-group-full">
                                <label class="cob-label">DO YOU HAVE YOUR BRAND GUIDELINES/STYLE GUIDE/IDENTITY AS A FILE UPLOAD? *</label>
                                <div class="cob-radio-group">
                                    <label class="cob-radio-option">
                                        <input type="radio" name="brand_guidelines_upload_radio" value="yes" class="cob-radio" required>
                                        <span class="cob-radio-label">Yes</span>
                                    </label>
                                    <label class="cob-radio-option">
                                        <input type="radio" name="brand_guidelines_upload_radio" value="no" class="cob-radio" required>
                                        <span class="cob-radio-label">No</span>
                                    </label>
                                </div>
                            </div>

                            <div class="cob-form-group cob-conditional-fields" data-show-when="brand_guidelines_upload_radio" data-show-value="yes">
                                <label class="cob-label">UPLOAD BRAND GUIDELINES/LOGO FILES</label>
                                <input type="file" name="brand_guidelines_upload" class="cob-input" accept=".pdf,.doc,.docx,image/*">
                            </div>

                            <!-- Communication Tone -->
                            <div class="cob-form-group cob-form-group-full">
                                <label class="cob-label">WHICH METHOD PREFERRED TO COMMUNICATE WITH YOUR BRAND (NO LOGO)?</label>
                                <div class="cob-radio-group">
                                    <label class="cob-radio-option">
                                        <input type="radio" name="communication_tone_radio" value="formal" class="cob-radio" required>
                                        <span class="cob-radio-label">Formal</span>
                                    </label>
                                    <label class="cob-radio-option">
                                        <input type="radio" name="communication_tone_radio" value="casual" class="cob-radio" required>
                                        <span class="cob-radio-label">Casual</span>
                                    </label>
                                </div>
                            </div>

                            <div class="cob-form-group cob-conditional-fields" data-show-when="communication_tone_radio" data-show-value="casual">
                                <label class="cob-label">EXPLAIN THE CASUAL TONE YOU WANT</label>
                                <textarea name="casual_tone_explanation" class="cob-textarea" placeholder="Describe your preferred casual tone"></textarea>
                            </div>

                            <div class="cob-form-group cob-conditional-fields" data-show-when="communication_tone_radio" data-show-value="formal">
                                <label class="cob-label">EXPLAIN THE FORMAL TONE YOU WANT</label>
                                <textarea name="formal_tone_explanation" class="cob-textarea" placeholder="Describe your preferred formal tone"></textarea>
                            </div>

                            <!-- Brand Accounts -->
                            <div class="cob-form-group cob-form-group-full">
                                <label class="cob-label">DO YOU HAVE BRAND ACCOUNTS? *</label>
                                <div class="cob-radio-group">
                                    <label class="cob-radio-option">
                                        <input type="radio" name="brand_accounts_radio" value="yes" class="cob-radio" required>
                                        <span class="cob-radio-label">Yes</span>
                                    </label>
                                    <label class="cob-radio-option">
                                        <input type="radio" name="brand_accounts_radio" value="no" class="cob-radio" required>
                                        <span class="cob-radio-label">No</span>
                                    </label>
                                </div>
                            </div>

                            <div class="cob-form-group cob-conditional-fields" data-show-when="brand_accounts_radio" data-show-value="yes">
                                <label class="cob-label">FACEBOOK PAGE</label>
                                <input type="text" name="facebook_page" class="cob-input" placeholder="Enter Facebook page URL">
                                <label class="cob-label">INSTAGRAM ACCOUNT USERNAME</label>
                                <input type="text" name="instagram_username" class="cob-input" placeholder="Enter Instagram username">
                            </div>

                            <!-- Business Description -->
                            <div class="cob-form-group cob-form-group-full">
                                <label class="cob-label">DESCRIBE YOUR BUSINESS/SERVICE *</label>
                                <textarea name="business_description" class="cob-textarea" placeholder="Provide a brief description of what your business does" required></textarea>
                            </div>

                            <!-- Target Audience -->
                            <div class="cob-form-group cob-form-group-full">
                                <label class="cob-label">DESCRIBE YOUR TARGET AUDIENCE *</label>
                                <textarea name="target_audience" class="cob-textarea" placeholder="Describe your ideal customer/client" required></textarea>
                            </div>

                            <!-- Marketing Goals -->
                            <div class="cob-form-group cob-form-group-full">
                                <label class="cob-label">WHAT ARE YOUR MAIN MARKETING GOALS? *</label>
                                <div class="cob-checkbox-group">
                                    <label class="cob-checkbox-option">
                                        <input type="checkbox" name="marketing_goals[]" value="increase_website_traffic" class="cob-checkbox">
                                        <span class="cob-checkbox-label">Increase Website Traffic</span>
                                    </label>
                                    <label class="cob-checkbox-option">
                                        <input type="checkbox" name="marketing_goals[]" value="generate_leads" class="cob-checkbox">
                                        <span class="cob-checkbox-label">Generate Leads</span>
                                    </label>
                                    <label class="cob-checkbox-option">
                                        <input type="checkbox" name="marketing_goals[]" value="improve_brand_awareness" class="cob-checkbox">
                                        <span class="cob-checkbox-label">Improve Brand Awareness</span>
                                    </label>
                                    <label class="cob-checkbox-option">
                                        <input type="checkbox" name="marketing_goals[]" value="increase_sales" class="cob-checkbox">
                                        <span class="cob-checkbox-label">Increase Sales</span>
                                    </label>
                                    <label class="cob-checkbox-option">
                                        <input type="checkbox" name="marketing_goals[]" value="improve_customer_retention" class="cob-checkbox">
                                        <span class="cob-checkbox-label">Improve Customer Retention</span>
                                    </label>
                                    <label class="cob-checkbox-option">
                                        <input type="checkbox" name="marketing_goals[]" value="expand_to_new_markets" class="cob-checkbox">
                                        <span class="cob-checkbox-label">Expand to New Markets</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Industry Information -->
                            <div class="cob-form-group cob-form-group-full">
                                <label class="cob-label">WHAT INDUSTRY ARE YOU IN? *</label>
                                <div class="cob-checkbox-group">
                                    <label class="cob-checkbox-option">
                                        <input type="checkbox" name="industry[]" value="ecommerce" class="cob-checkbox">
                                        <span class="cob-checkbox-label">E-commerce</span>
                                    </label>
                                    <label class="cob-checkbox-option">
                                        <input type="checkbox" name="industry[]" value="healthcare" class="cob-checkbox">
                                        <span class="cob-checkbox-label">Healthcare</span>
                                    </label>
                                    <label class="cob-checkbox-option">
                                        <input type="checkbox" name="industry[]" value="finance" class="cob-checkbox">
                                        <span class="cob-checkbox-label">Finance</span>
                                    </label>
                                    <label class="cob-checkbox-option">
                                        <input type="checkbox" name="industry[]" value="education" class="cob-checkbox">
                                        <span class="cob-checkbox-label">Education</span>
                                    </label>
                                    <label class="cob-checkbox-option">
                                        <input type="checkbox" name="industry[]" value="real_estate" class="cob-checkbox">
                                        <span class="cob-checkbox-label">Real Estate</span>
                                    </label>
                                    <label class="cob-checkbox-option">
                                        <input type="checkbox" name="industry[]" value="technology" class="cob-checkbox">
                                        <span class="cob-checkbox-label">Technology</span>
                                    </label>
                                    <label class="cob-checkbox-option">
                                        <input type="checkbox" name="industry[]" value="food_beverage" class="cob-checkbox">
                                        <span class="cob-checkbox-label">Food & Beverage</span>
                                    </label>
                                    <label class="cob-checkbox-option">
                                        <input type="checkbox" name="industry[]" value="fashion" class="cob-checkbox">
                                        <span class="cob-checkbox-label">Fashion</span>
                                    </label>
                                    <label class="cob-checkbox-option">
                                        <input type="checkbox" name="industry[]" value="other" class="cob-checkbox">
                                        <span class="cob-checkbox-label">Other</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Industry Entities -->
                            <div class="cob-form-group cob-form-group-full">
                                <label class="cob-label">LIST THE MEMBERS/ENTITIES DOING YOUR BUSINESS COMPARED TO THE SERVICES (LIST 6) *</label>
                                <div class="cob-checkbox-group">
                                    <label class="cob-checkbox-option">
                                        <input type="checkbox" name="industry_entities[]" value="direct_competitors" class="cob-checkbox" required>
                                        <span class="cob-checkbox-label">Direct Competitors</span>
                                    </label>
                                    <label class="cob-checkbox-option">
                                        <input type="checkbox" name="industry_entities[]" value="indirect_competitors" class="cob-checkbox" required>
                                        <span class="cob-checkbox-label">Indirect Competitors</span>
                                    </label>
                                    <label class="cob-checkbox-option">
                                        <input type="checkbox" name="industry_entities[]" value="new" class="cob-checkbox" required>
                                        <span class="cob-checkbox-label">New</span>
                                    </label>
                                    <label class="cob-checkbox-option">
                                        <input type="checkbox" name="industry_entities[]" value="sector" class="cob-checkbox" required>
                                        <span class="cob-checkbox-label">Sector</span>
                                    </label>
                                    <label class="cob-checkbox-option">
                                        <input type="checkbox" name="industry_entities[]" value="banking" class="cob-checkbox" required>
                                        <span class="cob-checkbox-label">Banking</span>
                                    </label>
                                    <label class="cob-checkbox-option">
                                        <input type="checkbox" name="industry_entities[]" value="services" class="cob-checkbox" required>
                                        <span class="cob-checkbox-label">Services</span>
                                    </label>
                                    <label class="cob-checkbox-option">
                                        <input type="checkbox" name="industry_entities[]" value="fintech" class="cob-checkbox" required>
                                        <span class="cob-checkbox-label">Fintech</span>
                                    </label>
                                    <label class="cob-checkbox-option">
                                        <input type="checkbox" name="industry_entities[]" value="property" class="cob-checkbox" required>
                                        <span class="cob-checkbox-label">Property</span>
                                    </label>
                                    <label class="cob-checkbox-option">
                                        <input type="checkbox" name="industry_entities[]" value="insurance" class="cob-checkbox" required>
                                        <span class="cob-checkbox-label">Insurance</span>
                                    </label>
                                    <label class="cob-checkbox-option">
                                        <input type="checkbox" name="industry_entities[]" value="health_medical" class="cob-checkbox" required>
                                        <span class="cob-checkbox-label">Health/Medical</span>
                                    </label>
                                    <label class="cob-checkbox-option">
                                        <input type="checkbox" name="industry_entities[]" value="retail" class="cob-checkbox" required>
                                        <span class="cob-checkbox-label">Retail</span>
                                    </label>
                                    <label class="cob-checkbox-option">
                                        <input type="checkbox" name="industry_entities[]" value="travel" class="cob-checkbox" required>
                                        <span class="cob-checkbox-label">Travel</span>
                                    </label>
                                    <label class="cob-checkbox-option">
                                        <input type="checkbox" name="industry_entities[]" value="construction" class="cob-checkbox" required>
                                        <span class="cob-checkbox-label">Construction</span>
                                    </label>
                                    <label class="cob-checkbox-option">
                                        <input type="checkbox" name="industry_entities[]" value="gaming" class="cob-checkbox" required>
                                        <span class="cob-checkbox-label">Gaming</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Target Age Range -->
                            <div class="cob-form-group cob-form-group-full">
                                <label class="cob-label">TARGET AUDIENCE AGE RANGE *</label>
                                <div class="cob-checkbox-group">
                                    <label class="cob-checkbox-option">
                                        <input type="checkbox" name="target_age_range[]" value="silent_generation" class="cob-checkbox" required>
                                        <span class="cob-checkbox-label">Silent Generation (78+)</span>
                                    </label>
                                    <label class="cob-checkbox-option">
                                        <input type="checkbox" name="target_age_range[]" value="baby_boomers" class="cob-checkbox" required>
                                        <span class="cob-checkbox-label">Baby Boomers (59-73)</span>
                                    </label>
                                    <label class="cob-checkbox-option">
                                        <input type="checkbox" name="target_age_range[]" value="gen_z" class="cob-checkbox" required>
                                        <span class="cob-checkbox-label">Gen Z (18-34)</span>
                                    </label>
                                    <label class="cob-checkbox-option">
                                        <input type="checkbox" name="target_age_range[]" value="millennials" class="cob-checkbox" required>
                                        <span class="cob-checkbox-label">Millennials (23-38)</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Gender Purchase Decision -->
                            <div class="cob-form-group cob-form-group-full">
                                <label class="cob-label">WHICH GENDER MOST OFTEN MAKES THE PURCHASE DECISION? *</label>
                                <div class="cob-checkbox-group">
                                    <label class="cob-checkbox-option">
                                        <input type="checkbox" name="gender_purchase_decision[]" value="female" class="cob-checkbox" required>
                                        <span class="cob-checkbox-label">Female</span>
                                    </label>
                                    <label class="cob-checkbox-option">
                                        <input type="checkbox" name="gender_purchase_decision[]" value="male" class="cob-checkbox" required>
                                        <span class="cob-checkbox-label">Male</span>
                                    </label>
                                    <label class="cob-checkbox-option">
                                        <input type="checkbox" name="gender_purchase_decision[]" value="unknown" class="cob-checkbox" required>
                                        <span class="cob-checkbox-label">Unknown</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Lead Source Markets -->
                            <div class="cob-form-group cob-form-group-full">
                                <label class="cob-label">WHAT ARE YOUR LEAD SOURCE MARKETS? *</label>
                                <input type="text" name="lead_source_markets" class="cob-input" placeholder="Enter your lead source markets" required>
                            </div>

                            <!-- Lead Times -->
                            <div class="cob-form-group cob-form-group-full">
                                <label class="cob-label">HAVE YOU OBSERVED ANY LEAD TIMES FROM BOOKING TO TRAVEL? IF SO, WHAT ARE THE AVERAGES BY MARKET (IF AVAILABLE)? *</label>
                                <textarea name="lead_times" class="cob-input" placeholder="Describe observed lead times" required></textarea>
                            </div>

                            <!-- Market Insights -->
                            <div class="cob-form-group cob-form-group-full">
                                <label class="cob-label">DO YOU RECEIVE INSIGHTS ON YOUR MARKET? *</label>
                                <div class="cob-radio-group">
                                    <label class="cob-radio-option">
                                        <input type="radio" name="market_insights_radio" value="yes" class="cob-radio" required>
                                        <span class="cob-radio-label">Yes</span>
                                    </label>
                                    <label class="cob-radio-option">
                                        <input type="radio" name="market_insights_radio" value="no" class="cob-radio" required>
                                        <span class="cob-radio-label">No</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Content Social Media -->
                            <div class="cob-form-group cob-form-group-full">
                                <label class="cob-label">DO YOU HAVE A CONTENT/SOCIAL MEDIA? *</label>
                                <div class="cob-radio-group">
                                    <label class="cob-radio-option">
                                        <input type="radio" name="content_social_media_radio" value="yes" class="cob-radio" required>
                                        <span class="cob-radio-label">Yes</span>
                                    </label>
                                    <label class="cob-radio-option">
                                        <input type="radio" name="content_social_media_radio" value="no" class="cob-radio" required>
                                        <span class="cob-radio-label">No</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Business Focus Elements -->
                            <div class="cob-form-group cob-form-group-full">
                                <label class="cob-label">CAN YOU EXPLAIN TO US WHAT ELEMENTS SHOULD YOU FOCUS ON YOUR BUSINESS? *</label>
                                <div class="cob-radio-group">
                                    <label class="cob-radio-option">
                                        <input type="radio" name="business_focus_elements_radio" value="yes" class="cob-radio" required>
                                        <span class="cob-radio-label">Yes</span>
                                    </label>
                                    <label class="cob-radio-option">
                                        <input type="radio" name="business_focus_elements_radio" value="no" class="cob-radio" required>
                                        <span class="cob-radio-label">No</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Competitors -->
                            <div class="cob-form-group cob-form-group-full">
                                <label class="cob-label">WHO ARE YOUR MAIN COMPETITORS? *</label>
                                <textarea name="main_competitors" class="cob-textarea" placeholder="List your main competitors or describe the competitive landscape" required></textarea>
                            </div>

                            <!-- Unique Value Proposition -->
                            <div class="cob-form-group cob-form-group-full">
                                <label class="cob-label">WHAT MAKES YOUR BUSINESS UNIQUE? *</label>
                                <textarea name="unique_value_proposition" class="cob-textarea" placeholder="Describe what sets you apart from competitors" required></textarea>
                            </div>

                            <!-- Marketing Budget -->
                            <div class="cob-form-group cob-form-group-full">
                                <label class="cob-label">WHAT IS YOUR MONTHLY MARKETING BUDGET? *</label>
                                <div class="cob-radio-group">
                                    <label class="cob-radio-option">
                                        <input type="radio" name="marketing_budget" value="under_1000" class="cob-radio" required>
                                        <span class="cob-radio-label">Under $1,000</span>
                                    </label>
                                    <label class="cob-radio-option">
                                        <input type="radio" name="marketing_budget" value="1000_5000" class="cob-radio" required>
                                        <span class="cob-radio-label">$1,000 - $5,000</span>
                                    </label>
                                    <label class="cob-radio-option">
                                        <input type="radio" name="marketing_budget" value="5000_10000" class="cob-radio" required>
                                        <span class="cob-radio-label">$5,000 - $10,000</span>
                                    </label>
                                    <label class="cob-radio-option">
                                        <input type="radio" name="marketing_budget" value="10000_25000" class="cob-radio" required>
                                        <span class="cob-radio-label">$10,000 - $25,000</span>
                                    </label>
                                    <label class="cob-radio-option">
                                        <input type="radio" name="marketing_budget" value="over_25000" class="cob-radio" required>
                                        <span class="cob-radio-label">Over $25,000</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Timeline -->
                            <div class="cob-form-group cob-form-group-full">
                                <label class="cob-label">WHEN DO YOU WANT TO START? *</label>
                                <div class="cob-radio-group">
                                    <label class="cob-radio-option">
                                        <input type="radio" name="start_timeline" value="immediately" class="cob-radio" required>
                                        <span class="cob-radio-label">Immediately</span>
                                    </label>
                                    <label class="cob-radio-option">
                                        <input type="radio" name="start_timeline" value="within_month" class="cob-radio" required>
                                        <span class="cob-radio-label">Within 1 Month</span>
                                    </label>
                                    <label class="cob-radio-option">
                                        <input type="radio" name="start_timeline" value="within_quarter" class="cob-radio" required>
                                        <span class="cob-radio-label">Within 3 Months</span>
                                    </label>
                                    <label class="cob-radio-option">
                                        <input type="radio" name="start_timeline" value="flexible" class="cob-radio" required>
                                        <span class="cob-radio-label">Flexible</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Social Media Accounts -->
                            <div class="cob-form-group cob-form-group-full">
                                <label class="cob-label">PLEASE PROVIDE LINKS TO YOUR SOCIAL MEDIA ACCOUNTS BELOW</label>
                                <div class="cob-form-group">
                                    <label class="cob-label">FACEBOOK PAGE URL</label>
                                    <input type="url" name="facebook_page_url" class="cob-input" placeholder="Enter Facebook page URL">
                                </div>
                                <div class="cob-form-group">
                                    <label class="cob-label">INSTAGRAM ACCOUNT URL</label>
                                    <input type="url" name="instagram_account_url" class="cob-input" placeholder="Enter Instagram account URL">
                                </div>
                                <div class="cob-form-group">
                                    <label class="cob-label">LINKEDIN PAGE URL</label>
                                    <input type="url" name="linkedin_page_url" class="cob-input" placeholder="Enter LinkedIn page URL">
                                </div>
                                <div class="cob-form-group">
                                    <label class="cob-label">TIKTOK ACCOUNT URL</label>
                                    <input type="url" name="tiktok_account_url" class="cob-input" placeholder="Enter TikTok account URL">
                                </div>
                                <div class="cob-form-group">
                                    <label class="cob-label">PINTEREST PAGE URL</label>
                                    <input type="url" name="pinterest_page_url" class="cob-input" placeholder="Enter Pinterest page URL">
                                </div>
                                <div class="cob-form-group">
                                    <label class="cob-label">TWITTER ACCOUNT URL</label>
                                    <input type="url" name="twitter_accounts_url" class="cob-input" placeholder="Enter Twitter account URL">
                                </div>
                                <div class="cob-form-group">
                                    <label class="cob-label">YOUTUBE CHANNEL URL</label>
                                    <input type="url" name="youtube_channel_url" class="cob-input" placeholder="Enter YouTube channel URL">
                                </div>
                                <div class="cob-form-group cob-form-group-full">
                                    <label class="cob-label">OTHER SOCIAL MEDIA PLATFORMS</label>
                                    <textarea name="other_social_media_platforms" class="cob-textarea" placeholder="List any other social media platforms you use"></textarea>
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
            </main>

            <!-- Sticky Save Status - Right Side -->
            <div class="cob-sticky-save-status" id="cob-save-status">
                <div class="cob-save-status-content">
                    <span id="cob-save-text">Last saved: Just now</span>
                </div>
            </div>
        </div>
    </div>
</div>
