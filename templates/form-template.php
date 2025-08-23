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
                        <div class="cob-form-group">
                            <label class="cob-label">PROJECT NAME</label>
                            <input type="text" name="project_name" class="cob-input" required>
                        </div>
                        <div class="cob-form-group">
                            <label class="cob-label">BUSINESS NAME</label>
                            <input type="text" name="business_name" class="cob-input" required>
                        </div>
                        <div class="cob-form-group">
                            <label class="cob-label">PRIMARY CONTACT NAME</label>
                            <input type="text" name="primary_contact_name" class="cob-input" required>
                        </div>
                        <div class="cob-form-group">
                            <label class="cob-label">PRIMARY CONTACT EMAIL</label>
                            <input type="email" name="primary_contact_email" class="cob-input" required>
                        </div>
                        <div class="cob-form-group">
                            <label class="cob-label">PRIMARY CONTACT NUMBER</label>
                            <input type="tel" name="primary_contact_phone" class="cob-input">
                        </div>
                        <div class="cob-form-group">
                            <label class="cob-label">WHO IS THE MAIN APPROVER OF MILESTONES?</label>
                            <input type="text" name="milestone_approver" class="cob-input">
                        </div>
                        <div class="cob-form-group">
                            <label class="cob-label">BILLING EMAIL ADDRESS</label>
                            <input type="email" name="billing_email" class="cob-input">
                        </div>
                        <div class="cob-form-group">
                            <label class="cob-label">VAT NUMBER (OPTIONAL)</label>
                            <input type="text" name="vat_number" class="cob-input">
                        </div>
                        <div class="cob-form-group cob-form-group-full">
                            <label class="cob-label">PREFERRED CONTACT METHOD</label>
                            <div class="cob-radio-group">
                                <label class="cob-radio-option">
                                    <input type="radio" name="preferred_contact_method" value="phone" class="cob-radio">
                                    <span class="cob-radio-label">PHONE</span>
                                </label>
                                <label class="cob-radio-option">
                                    <input type="radio" name="preferred_contact_method" value="email" class="cob-radio" checked>
                                    <span class="cob-radio-label">EMAIL</span>
                                </label>
                            </div>
                        </div>
                        <div class="cob-form-group cob-form-group-full">
                            <label class="cob-label">BILLING ADDRESS</label>
                            <div class="cob-address-grid">
                                <input type="text" name="billing_address_line1" class="cob-input" placeholder="Address Line 1">
                                <input type="text" name="billing_address_line2" class="cob-input" placeholder="Address Line 2">
                                <input type="text" name="billing_address_city" class="cob-input" placeholder="City">
                                <div class="cob-address-row">
                                    <input type="text" name="billing_address_country" class="cob-input" placeholder="Country">
                                    <input type="text" name="billing_address_postal_code" class="cob-input" placeholder="Postal Code">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Technical Information -->
                <div class="cob-step-content" data-step="2">
                    <div class="cob-form-grid">
                        <div class="cob-form-group">
                            <label class="cob-label">CURRENT WEBSITE URL</label>
                            <input type="url" name="current_website" class="cob-input" placeholder="https://" required>
                        </div>
                        <div class="cob-form-group">
                            <label class="cob-label">CURRENT HOSTING PROVIDER</label>
                            <input type="text" name="hosting_provider" class="cob-input">
                        </div>
                        <div class="cob-form-group">
                            <label class="cob-label">DOMAIN PROVIDER</label>
                            <input type="text" name="domain_provider" class="cob-input">
                        </div>
                        <div class="cob-form-group">
                            <label class="cob-label">TECHNICAL CONTACT NAME</label>
                            <input type="text" name="technical_contact_name" class="cob-input" required>
                        </div>
                        <div class="cob-form-group">
                            <label class="cob-label">TECHNICAL CONTACT EMAIL</label>
                            <input type="email" name="technical_contact_email" class="cob-input" required>
                        </div>
                        <div class="cob-form-group">
                            <label class="cob-label">PREFERRED CMS/PLATFORM</label>
                            <select name="preferred_cms" class="cob-select">
                                <option value="">Select CMS</option>
                                <option value="wordpress">WordPress</option>
                                <option value="shopify">Shopify</option>
                                <option value="webflow">Webflow</option>
                                <option value="wix">Wix</option>
                                <option value="squarespace">Squarespace</option>
                                <option value="custom">Custom Development</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="cob-form-group cob-form-group-full">
                            <label class="cob-label">INTEGRATION REQUIREMENTS</label>
                            <textarea name="integration_requirements" class="cob-textarea" placeholder="Describe any third-party integrations needed (CRM, payment gateways, APIs, etc.)"></textarea>
                        </div>
                        <div class="cob-form-group cob-form-group-full">
                            <label class="cob-label">CURRENT TECHNOLOGY STACK</label>
                            <div class="cob-checkbox-group">
                                <label class="cob-checkbox-option">
                                    <input type="checkbox" name="technology_stack[]" value="react" class="cob-checkbox">
                                    <span class="cob-checkbox-label">React</span>
                                </label>
                                <label class="cob-checkbox-option">
                                    <input type="checkbox" name="technology_stack[]" value="angular" class="cob-checkbox">
                                    <span class="cob-checkbox-label">Angular</span>
                                </label>
                                <label class="cob-checkbox-option">
                                    <input type="checkbox" name="technology_stack[]" value="vue" class="cob-checkbox">
                                    <span class="cob-checkbox-label">Vue.js</span>
                                </label>
                                <label class="cob-checkbox-option">
                                    <input type="checkbox" name="technology_stack[]" value="php" class="cob-checkbox">
                                    <span class="cob-checkbox-label">PHP</span>
                                </label>
                                <label class="cob-checkbox-option">
                                    <input type="checkbox" name="technology_stack[]" value="nodejs" class="cob-checkbox">
                                    <span class="cob-checkbox-label">Node.js</span>
                                </label>
                                <label class="cob-checkbox-option">
                                    <input type="checkbox" name="technology_stack[]" value="python" class="cob-checkbox">
                                    <span class="cob-checkbox-label">Python</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Reporting Information -->
                <div class="cob-step-content" data-step="3">
                    <div class="cob-form-grid">
                        <div class="cob-form-group">
                            <label class="cob-label">REPORTING FREQUENCY</label>
                            <select name="reporting_frequency" class="cob-select" required>
                                <option value="">Select frequency</option>
                                <option value="weekly">Weekly</option>
                                <option value="biweekly">Bi-weekly</option>
                                <option value="monthly">Monthly</option>
                                <option value="quarterly">Quarterly</option>
                                <option value="on-demand">On-demand</option>
                            </select>
                        </div>
                        <div class="cob-form-group">
                            <label class="cob-label">PREFERRED REPORT FORMAT</label>
                            <select name="reporting_format" class="cob-select">
                                <option value="">Select format</option>
                                <option value="pdf">PDF Report</option>
                                <option value="dashboard">Online Dashboard</option>
                                <option value="email">Email Summary</option>
                                <option value="presentation">Presentation</option>
                                <option value="spreadsheet">Spreadsheet</option>
                            </select>
                        </div>
                        <div class="cob-form-group">
                            <label class="cob-label">REPORTING CONTACT NAME</label>
                            <input type="text" name="reporting_contact_name" class="cob-input" required>
                        </div>
                        <div class="cob-form-group">
                            <label class="cob-label">REPORTING CONTACT EMAIL</label>
                            <input type="email" name="reporting_contact_email" class="cob-input" required>
                        </div>
                        <div class="cob-form-group">
                            <label class="cob-label">DASHBOARD ACCESS LEVEL</label>
                            <select name="dashboard_access" class="cob-select">
                                <option value="">Select access level</option>
                                <option value="view-only">View Only</option>
                                <option value="limited-edit">Limited Edit</option>
                                <option value="full-access">Full Access</option>
                                <option value="admin">Admin Access</option>
                            </select>
                        </div>
                        <div class="cob-form-group cob-form-group-full">
                            <label class="cob-label">KEY METRICS TO TRACK</label>
                            <div class="cob-checkbox-group">
                                <label class="cob-checkbox-option">
                                    <input type="checkbox" name="key_metrics[]" value="website-traffic" class="cob-checkbox">
                                    <span class="cob-checkbox-label">Website Traffic</span>
                                </label>
                                <label class="cob-checkbox-option">
                                    <input type="checkbox" name="key_metrics[]" value="conversion-rate" class="cob-checkbox">
                                    <span class="cob-checkbox-label">Conversion Rate</span>
                                </label>
                                <label class="cob-checkbox-option">
                                    <input type="checkbox" name="key_metrics[]" value="sales-revenue" class="cob-checkbox">
                                    <span class="cob-checkbox-label">Sales Revenue</span>
                                </label>
                                <label class="cob-checkbox-option">
                                    <input type="checkbox" name="key_metrics[]" value="lead-generation" class="cob-checkbox">
                                    <span class="cob-checkbox-label">Lead Generation</span>
                                </label>
                                <label class="cob-checkbox-option">
                                    <input type="checkbox" name="key_metrics[]" value="seo-rankings" class="cob-checkbox">
                                    <span class="cob-checkbox-label">SEO Rankings</span>
                                </label>
                                <label class="cob-checkbox-option">
                                    <input type="checkbox" name="key_metrics[]" value="social-media" class="cob-checkbox">
                                    <span class="cob-checkbox-label">Social Media</span>
                                </label>
                                <label class="cob-checkbox-option">
                                    <input type="checkbox" name="key_metrics[]" value="email-marketing" class="cob-checkbox">
                                    <span class="cob-checkbox-label">Email Marketing</span>
                                </label>
                                <label class="cob-checkbox-option">
                                    <input type="checkbox" name="key_metrics[]" value="customer-satisfaction" class="cob-checkbox">
                                    <span class="cob-checkbox-label">Customer Satisfaction</span>
                                </label>
                            </div>
                        </div>
                        <div class="cob-form-group cob-form-group-full">
                            <label class="cob-label">ADDITIONAL REPORTING REQUIREMENTS</label>
                            <textarea name="additional_reporting_requirements" class="cob-textarea" placeholder="Describe any specific reporting requirements, custom metrics, or additional data you'd like to track"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Step 4: Marketing Information -->
                <div class="cob-step-content" data-step="4">
                    <div class="cob-form-grid">
                        <div class="cob-form-group cob-form-group-full">
                            <label class="cob-label">TARGET AUDIENCE</label>
                            <textarea name="target_audience" class="cob-textarea" placeholder="Describe your ideal customers, demographics, and target market" required></textarea>
                        </div>
                        <div class="cob-form-group cob-form-group-full">
                            <label class="cob-label">MARKETING GOALS</label>
                            <textarea name="marketing_goals" class="cob-textarea" placeholder="What are your primary marketing objectives? (e.g., increase brand awareness, generate leads, drive sales)" required></textarea>
                        </div>
                        <div class="cob-form-group">
                            <label class="cob-label">MARKETING BUDGET RANGE</label>
                            <select name="marketing_budget" class="cob-select">
                                <option value="">Select budget range</option>
                                <option value="under-1k">Under $1,000/month</option>
                                <option value="1k-5k">$1,000 - $5,000/month</option>
                                <option value="5k-10k">$5,000 - $10,000/month</option>
                                <option value="10k-25k">$10,000 - $25,000/month</option>
                                <option value="25k-50k">$25,000 - $50,000/month</option>
                                <option value="over-50k">Over $50,000/month</option>
                            </select>
                        </div>
                        <div class="cob-form-group cob-form-group-full">
                            <label class="cob-label">CURRENT MARKETING CHANNELS</label>
                            <div class="cob-checkbox-group">
                                <label class="cob-checkbox-option">
                                    <input type="checkbox" name="current_marketing_channels[]" value="social-media" class="cob-checkbox">
                                    <span class="cob-checkbox-label">Social Media</span>
                                </label>
                                <label class="cob-checkbox-option">
                                    <input type="checkbox" name="current_marketing_channels[]" value="email-marketing" class="cob-checkbox">
                                    <span class="cob-checkbox-label">Email Marketing</span>
                                </label>
                                <label class="cob-checkbox-option">
                                    <input type="checkbox" name="current_marketing_channels[]" value="content-marketing" class="cob-checkbox">
                                    <span class="cob-checkbox-label">Content Marketing</span>
                                </label>
                                <label class="cob-checkbox-option">
                                    <input type="checkbox" name="current_marketing_channels[]" value="paid-advertising" class="cob-checkbox">
                                    <span class="cob-checkbox-label">Paid Advertising</span>
                                </label>
                                <label class="cob-checkbox-option">
                                    <input type="checkbox" name="current_marketing_channels[]" value="seo" class="cob-checkbox">
                                    <span class="cob-checkbox-label">SEO</span>
                                </label>
                                <label class="cob-checkbox-option">
                                    <input type="checkbox" name="current_marketing_channels[]" value="influencer-marketing" class="cob-checkbox">
                                    <span class="cob-checkbox-label">Influencer Marketing</span>
                                </label>
                                <label class="cob-checkbox-option">
                                    <input type="checkbox" name="current_marketing_channels[]" value="referral-programs" class="cob-checkbox">
                                    <span class="cob-checkbox-label">Referral Programs</span>
                                </label>
                                <label class="cob-checkbox-option">
                                    <input type="checkbox" name="current_marketing_channels[]" value="events-webinars" class="cob-checkbox">
                                    <span class="cob-checkbox-label">Events & Webinars</span>
                                </label>
                            </div>
                        </div>
                        <div class="cob-form-group cob-form-group-full">
                            <label class="cob-label">BRAND GUIDELINES</label>
                            <textarea name="brand_guidelines" class="cob-textarea" placeholder="Describe your brand voice, tone, colors, fonts, and any existing brand guidelines"></textarea>
                        </div>
                        <div class="cob-form-group cob-form-group-full">
                            <label class="cob-label">COMPETITOR ANALYSIS</label>
                            <textarea name="competitor_analysis" class="cob-textarea" placeholder="List your main competitors and what makes your business different"></textarea>
                        </div>
                        <div class="cob-form-group cob-form-group-full">
                            <label class="cob-label">CURRENT MARKETING CHALLENGES</label>
                            <textarea name="marketing_challenges" class="cob-textarea" placeholder="What marketing challenges are you currently facing? What hasn't worked well for you?"></textarea>
                        </div>
                        <div class="cob-form-group cob-form-group-full">
                            <label class="cob-label">SUCCESS METRICS</label>
                            <textarea name="success_metrics" class="cob-textarea" placeholder="How do you measure marketing success? What KPIs are most important to your business?"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="cob-form-actions">
                    <button type="button" class="cob-btn cob-btn-secondary" id="cob-previous-btn" style="display: none;">
                        PREVIOUS
                    </button>
                    <button type="button" class="cob-btn cob-btn-primary" id="cob-continue-btn">
                        CONTINUE
                    </button>
                    <button type="submit" class="cob-btn cob-btn-primary" id="cob-submit-btn" style="display: none;">
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