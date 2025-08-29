<?php
/**
 * Single Submission Detail View
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!$submission) {
    echo '<div class="notice notice-error"><p>' . __('Submission not found.', 'client-onboarding-form') . '</p></div>';
    return;
}
?>

<div class="wrap">
    <h1><?php printf(__('Submission #%d - %s', 'client-onboarding-form'), $submission->id, esc_html($submission->business_name)); ?></h1>
    
    <div class="cob-submission-detail">
        <!-- Submission Meta -->
        <div class="cob-submission-meta">
            <p><strong><?php _e('Submitted:', 'client-onboarding-form'); ?></strong> 
               <?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($submission->submitted_at))); ?>
            </p>
            <p><strong><?php _e('Status:', 'client-onboarding-form'); ?></strong> 
               <span class="cob-status cob-status-<?php echo esc_attr($submission->status); ?>">
                   <?php echo esc_html(ucfirst($submission->status)); ?>
               </span>
            </p>
            <p><strong><?php _e('Session ID:', 'client-onboarding-form'); ?></strong> <?php echo esc_html($submission->session_id); ?></p>
        </div>

        <!-- Step 1: Client Information -->
        <div class="cob-section">
            <h2><?php _e('STEP 1: CLIENT INFORMATION', 'client-onboarding-form'); ?></h2>
            <table class="form-table">
                <tr>
                    <th><?php _e('Project Name', 'client-onboarding-form'); ?></th>
                    <td><?php echo esc_html($submission->project_name); ?></td>
                </tr>
                <tr>
                    <th><?php _e('Business Name', 'client-onboarding-form'); ?></th>
                    <td><?php echo esc_html($submission->business_name); ?></td>
                </tr>
                <tr>
                    <th><?php _e('Primary Contact Name', 'client-onboarding-form'); ?></th>
                    <td><?php echo esc_html($submission->primary_contact_name); ?></td>
                </tr>
                <tr>
                    <th><?php _e('Primary Contact Email', 'client-onboarding-form'); ?></th>
                    <td><a href="mailto:<?php echo esc_attr($submission->primary_contact_email); ?>"><?php echo esc_html($submission->primary_contact_email); ?></a></td>
                </tr>
                <tr>
                    <th><?php _e('Primary Contact Number', 'client-onboarding-form'); ?></th>
                    <td><?php echo esc_html($submission->primary_contact_number); ?></td>
                </tr>
                <tr>
                    <th><?php _e('Main Approver of Milestones', 'client-onboarding-form'); ?></th>
                    <td><?php echo esc_html($submission->main_approver); ?></td>
                </tr>
                <tr>
                    <th><?php _e('Billing Email Address', 'client-onboarding-form'); ?></th>
                    <td><a href="mailto:<?php echo esc_attr($submission->billing_email); ?>"><?php echo esc_html($submission->billing_email); ?></a></td>
                </tr>
                <?php if ($submission->vat_number): ?>
                <tr>
                    <th><?php _e('VAT Number', 'client-onboarding-form'); ?></th>
                    <td><?php echo esc_html($submission->vat_number); ?></td>
                </tr>
                <?php endif; ?>
                <tr>
                    <th><?php _e('Preferred Contact Method', 'client-onboarding-form'); ?></th>
                    <td><?php echo esc_html(ucfirst($submission->preferred_contact_method)); ?></td>
                </tr>
                <tr>
                    <th><?php _e('Address Line 1', 'client-onboarding-form'); ?></th>
                    <td><?php echo esc_html($submission->address_line_1); ?></td>
                </tr>
                <?php if ($submission->address_line_2): ?>
                <tr>
                    <th><?php _e('Address Line 2', 'client-onboarding-form'); ?></th>
                    <td><?php echo esc_html($submission->address_line_2); ?></td>
                </tr>
                <?php endif; ?>
                <tr>
                    <th><?php _e('City', 'client-onboarding-form'); ?></th>
                    <td><?php echo esc_html($submission->city); ?></td>
                </tr>
                <tr>
                    <th><?php _e('Country', 'client-onboarding-form'); ?></th>
                    <td><?php echo esc_html($submission->country); ?></td>
                </tr>
                <tr>
                    <th><?php _e('Postal Code', 'client-onboarding-form'); ?></th>
                    <td><?php echo esc_html($submission->postal_code); ?></td>
                </tr>
            </table>
        </div>

        <!-- Step 2: Technical Information -->
        <div class="cob-section">
            <h2><?php _e('STEP 2: TECHNICAL INFORMATION', 'client-onboarding-form'); ?></h2>
            <table class="form-table">
                <tr>
                    <th><?php _e('Current CMS', 'client-onboarding-form'); ?></th>
                    <td><?php echo esc_html($submission->current_cms); ?></td>
                </tr>
                <tr>
                    <th><?php _e('Website Hosting Company', 'client-onboarding-form'); ?></th>
                    <td><?php echo esc_html($submission->website_hosting_company); ?></td>
                </tr>
                <tr>
                    <th><?php _e('Website Contact Email', 'client-onboarding-form'); ?></th>
                    <td><a href="mailto:<?php echo esc_attr($submission->website_contact_email); ?>"><?php echo esc_html($submission->website_contact_email); ?></a></td>
                </tr>
                <tr>
                    <th><?php _e('Domain Hosting Company', 'client-onboarding-form'); ?></th>
                    <td><?php echo esc_html($submission->domain_hosting_company); ?></td>
                </tr>
                <tr>
                    <th><?php _e('Domain Contact Email', 'client-onboarding-form'); ?></th>
                    <td><a href="mailto:<?php echo esc_attr($submission->domain_contact_email); ?>"><?php echo esc_html($submission->domain_contact_email); ?></a></td>
                </tr>
                <tr>
                    <th><?php _e('CMS Backend Link', 'client-onboarding-form'); ?></th>
                    <td><a href="<?php echo esc_url($submission->cms_link); ?>" target="_blank"><?php echo esc_html($submission->cms_link); ?></a></td>
                </tr>
                <tr>
                    <th><?php _e('CMS Username', 'client-onboarding-form'); ?></th>
                    <td><?php echo esc_html($submission->cms_username); ?></td>
                </tr>
                <tr>
                    <th><?php _e('CMS Password', 'client-onboarding-form'); ?></th>
                    <td>[PROTECTED]</td>
                </tr>
                <?php if ($submission->current_crm): ?>
                <tr>
                    <th><?php _e('CRM Integration', 'client-onboarding-form'); ?></th>
                    <td><?php echo esc_html($submission->current_crm); ?></td>
                </tr>
                <?php endif; ?>
                <tr>
                    <th><?php _e('3rd Party Integrations', 'client-onboarding-form'); ?></th>
                    <td><?php echo esc_html($submission->third_party_integrations); ?></td>
                </tr>
                <?php if ($submission->third_party_name): ?>
                <tr>
                    <th><?php _e('3rd Party Integration Name', 'client-onboarding-form'); ?></th>
                    <td><?php echo esc_html($submission->third_party_name); ?></td>
                </tr>
                <?php endif; ?>
                <?php if ($submission->third_party_contact_number): ?>
                <tr>
                    <th><?php _e('3rd Party Contact Number', 'client-onboarding-form'); ?></th>
                    <td><?php echo esc_html($submission->third_party_contact_number); ?></td>
                </tr>
                <?php endif; ?>
                <?php if ($submission->third_party_contact_email): ?>
                <tr>
                    <th><?php _e('3rd Party Contact Email', 'client-onboarding-form'); ?></th>
                    <td><a href="mailto:<?php echo esc_attr($submission->third_party_contact_email); ?>"><?php echo esc_html($submission->third_party_contact_email); ?></a></td>
                </tr>
                <?php endif; ?>
                <?php if ($submission->booking_engine_name): ?>
                <tr>
                    <th><?php _e('Booking Engine Name', 'client-onboarding-form'); ?></th>
                    <td><?php echo esc_html($submission->booking_engine_name); ?></td>
                </tr>
                <?php endif; ?>
                <?php if ($submission->booking_engine_username): ?>
                <tr>
                    <th><?php _e('Booking Engine Username', 'client-onboarding-form'); ?></th>
                    <td><?php echo esc_html($submission->booking_engine_username); ?></td>
                </tr>
                <?php endif; ?>
                <?php if ($submission->booking_engine_password): ?>
                <tr>
                    <th><?php _e('Booking Engine Password', 'client-onboarding-form'); ?></th>
                    <td>[PROTECTED]</td>
                </tr>
                <?php endif; ?>
                <?php if ($submission->booking_engine_contact_email): ?>
                <tr>
                    <th><?php _e('Booking Engine Contact Email', 'client-onboarding-form'); ?></th>
                    <td><a href="mailto:<?php echo esc_attr($submission->booking_engine_contact_email); ?>"><?php echo esc_html($submission->booking_engine_contact_email); ?></a></td>
                </tr>
                <?php endif; ?>
                <tr>
                    <th><?php _e('Technical Objective', 'client-onboarding-form'); ?></th>
                    <td><?php echo esc_html($submission->technical_objective); ?></td>
                </tr>
            </table>
        </div>

        <!-- Step 3: Reporting Information -->
        <div class="cob-section">
            <h2><?php _e('STEP 3: REPORTING INFORMATION', 'client-onboarding-form'); ?></h2>
            <table class="form-table">
                <tr>
                    <th><?php _e('Main Objective', 'client-onboarding-form'); ?></th>
                    <td><?php echo esc_html($submission->main_objective); ?></td>
                </tr>
                <tr>
                    <th><?php _e('Google Analytics Account', 'client-onboarding-form'); ?></th>
                    <td><?php echo esc_html($submission->google_analytics_account); ?></td>
                </tr>
                <?php if ($submission->google_analytics_account): ?>
                <tr>
                    <th><?php _e('Google Analytics Account ID', 'client-onboarding-form'); ?></th>
                    <td><?php echo esc_html($submission->google_analytics_account); ?></td>
                </tr>
                <?php endif; ?>
                <tr>
                    <th><?php _e('Google Tag Manager Account', 'client-onboarding-form'); ?></th>
                    <td><?php echo esc_html($submission->google_tag_manager_account); ?></td>
                </tr>
                <?php if ($submission->google_tag_manager_account): ?>
                <tr>
                    <th><?php _e('Google Tag Manager Admin', 'client-onboarding-form'); ?></th>
                    <td><?php echo esc_html($submission->google_tag_manager_account); ?></td>
                </tr>
                <?php endif; ?>
                <tr>
                    <th><?php _e('Google Ads Account', 'client-onboarding-form'); ?></th>
                    <td><?php echo esc_html($submission->google_ads_account); ?></td>
                </tr>
                <?php if ($submission->google_ads_account): ?>
                <tr>
                    <th><?php _e('Google Ads Admin', 'client-onboarding-form'); ?></th>
                    <td><?php echo esc_html($submission->google_ads_account); ?></td>
                </tr>
                <?php endif; ?>
                <?php if ($submission->google_ads_customer_id): ?>
                <tr>
                    <th><?php _e('Google Ads Customer ID', 'client-onboarding-form'); ?></th>
                    <td><?php echo esc_html($submission->google_ads_customer_id); ?></td>
                </tr>
                <?php endif; ?>
                <tr>
                    <th><?php _e('Meta Business Manager Account', 'client-onboarding-form'); ?></th>
                    <td><?php echo esc_html($submission->meta_business_manager_account); ?></td>
                </tr>
                <?php if ($submission->meta_business_manager_account): ?>
                <tr>
                    <th><?php _e('Meta Business Manager Admin', 'client-onboarding-form'); ?></th>
                    <td><?php echo esc_html($submission->meta_business_manager_account); ?></td>
                </tr>
                <?php endif; ?>
                <?php if ($submission->meta_business_manager_id): ?>
                <tr>
                    <th><?php _e('Meta Business Manager ID', 'client-onboarding-form'); ?></th>
                    <td><?php echo esc_html($submission->meta_business_manager_id); ?></td>
                </tr>
                <?php endif; ?>
                <tr>
                    <th><?php _e('Paid Media History', 'client-onboarding-form'); ?></th>
                    <td><?php echo esc_html($submission->paid_media_history); ?></td>
                </tr>
                <?php if ($submission->paid_media_history_other): ?>
                <tr>
                    <th><?php _e('Other Paid Media Specify', 'client-onboarding-form'); ?></th>
                    <td><?php echo esc_html($submission->paid_media_history_other); ?></td>
                </tr>
                <?php endif; ?>
                <tr>
                    <th><?php _e('Current Paid Media', 'client-onboarding-form'); ?></th>
                    <td><?php echo esc_html($submission->current_paid_media); ?></td>
                </tr>
                <?php if ($submission->current_paid_media_other): ?>
                <tr>
                    <th><?php _e('Other Current Paid Media Specify', 'client-onboarding-form'); ?></th>
                    <td><?php echo esc_html($submission->current_paid_media_other); ?></td>
                </tr>
                <?php endif; ?>
            </table>
        </div>

        <!-- Step 4: Marketing Information -->
        <div class="cob-section">
            <h2><?php _e('STEP 4: MARKETING INFORMATION', 'client-onboarding-form'); ?></h2>
            
            <!-- Objectives -->
            <h3><?php _e('Objectives', 'client-onboarding-form'); ?></h3>
            <table class="form-table">
                <tr>
                    <th><?php _e('Main Objective', 'client-onboarding-form'); ?></th>
                    <td><?php echo esc_html($submission->main_objective); ?></td>
                </tr>
                <tr>
                    <th><?php _e('Brand Focus', 'client-onboarding-form'); ?></th>
                    <td><?php echo esc_html($submission->brand_focus); ?></td>
                </tr>
                <tr>
                    <th><?php _e('Commercial Objective', 'client-onboarding-form'); ?></th>
                    <td><?php echo esc_html($submission->commercial_objective); ?></td>
                </tr>
                <tr>
                    <th><?php _e('Push Impact', 'client-onboarding-form'); ?></th>
                    <td><?php echo esc_html($submission->push_impact); ?></td>
                </tr>
            </table>

            <!-- Brand Story -->
            <h3><?php _e('Brand Story', 'client-onboarding-form'); ?></h3>
            <table class="form-table">
                <tr>
                    <th><?php _e('Founder Inspiration', 'client-onboarding-form'); ?></th>
                    <td><?php echo esc_html($submission->founder_inspiration); ?></td>
                </tr>
                <tr>
                    <th><?php _e('Brand Tone & Mission', 'client-onboarding-form'); ?></th>
                    <td><?php echo esc_html($submission->brand_tone_mission); ?></td>
                </tr>
                <tr>
                    <th><?php _e('Brand Perception', 'client-onboarding-form'); ?></th>
                    <td><?php echo esc_html($submission->brand_perception); ?></td>
                </tr>
                <tr>
                    <th><?php _e('Global Team Introduction', 'client-onboarding-form'); ?></th>
                    <td><?php echo esc_html($submission->global_team_introduction); ?></td>
                </tr>
                <tr>
                    <th><?php _e('Service Introduction', 'client-onboarding-form'); ?></th>
                    <td><?php echo esc_html($submission->service_introduction); ?></td>
                </tr>
            </table>

            <!-- Brand Values -->
            <h3><?php _e('Brand Values', 'client-onboarding-form'); ?></h3>
            <table class="form-table">
                <tr>
                    <th><?php _e('Brand Line 1', 'client-onboarding-form'); ?></th>
                    <td><?php echo esc_html($submission->brand_line_1); ?></td>
                </tr>
                <tr>
                    <th><?php _e('Mission 1', 'client-onboarding-form'); ?></th>
                    <td><?php echo esc_html($submission->mission_1); ?></td>
                </tr>
                <tr>
                    <th><?php _e('Brand Line 2', 'client-onboarding-form'); ?></th>
                    <td><?php echo esc_html($submission->brand_line_2); ?></td>
                </tr>
                <tr>
                    <th><?php _e('Mission 2', 'client-onboarding-form'); ?></th>
                    <td><?php echo esc_html($submission->mission_2); ?></td>
                </tr>
                <tr>
                    <th><?php _e('Brand Line 3', 'client-onboarding-form'); ?></th>
                    <td><?php echo esc_html($submission->mission_3); ?></td>
                </tr>
                <tr>
                    <th><?php _e('Mission 3', 'client-onboarding-form'); ?></th>
                    <td><?php echo esc_html($submission->mission_3); ?></td>
                </tr>
            </table>

            <!-- Current Website -->
            <h3><?php _e('Current Website', 'client-onboarding-form'); ?></h3>
            <table class="form-table">
                <tr>
                    <th><?php _e('Current Website', 'client-onboarding-form'); ?></th>
                    <td><a href="<?php echo esc_url($submission->current_website); ?>" target="_blank"><?php echo esc_html($submission->current_website); ?></a></td>
                </tr>
            </table>

            <!-- Brand Guidelines Upload -->
            <h3><?php _e('Brand Guidelines Upload', 'client-onboarding-form'); ?></h3>
            <table class="form-table">
                <tr>
                    <th><?php _e('Brand Guidelines Upload', 'client-onboarding-form'); ?></th>
                    <td><?php echo esc_html($submission->brand_guidelines_upload); ?></td>
                </tr>
                <?php if ($submission->brand_guidelines_upload): ?>
                <tr>
                    <th><?php _e('Brand Guidelines Files', 'client-onboarding-form'); ?></th>
                    <td><?php echo esc_html($submission->brand_guidelines_upload); ?></td>
                </tr>
                <?php endif; ?>
            </table>

            <!-- File Uploads -->
            <h3><?php _e('UPLOADED FILES', 'client-onboarding-form'); ?></h3>
            <table class="form-table">
                <?php if (!empty($submission->logo_file_url)): ?>
                <tr>
                    <th><?php _e('Logo File', 'client-onboarding-form'); ?></th>
                    <td>
                        <a href="<?php echo esc_url($submission->logo_file_url); ?>" target="_blank" class="button button-small">
                            <span class="dashicons dashicons-download"></span>
                            <?php echo esc_html($submission->logo_file_name ?: 'Download Logo'); ?>
                        </a>
                        <small style="color: #666; display: block; margin-top: 5px;">
                            <?php _e('File ID:', 'client-onboarding-form'); ?> <?php echo esc_html($submission->logo_file_id); ?>
                        </small>
                    </td>
                </tr>
                <?php endif; ?>
                
                <?php if (!empty($submission->brand_guidelines_url)): ?>
                <tr>
                    <th><?php _e('Brand Guidelines', 'client-onboarding-form'); ?></th>
                    <td>
                        <a href="<?php echo esc_url($submission->brand_guidelines_url); ?>" target="_blank" class="button button-small">
                            <span class="dashicons dashicons-download"></span>
                            <?php echo esc_html($submission->brand_guidelines_name ?: 'Download Brand Guidelines'); ?>
                        </a>
                        <small style="color: #666; display: block; margin-top: 5px;">
                            <?php _e('File ID:', 'client-onboarding-form'); ?> <?php echo esc_html($submission->brand_guidelines_id); ?>
                        </small>
                    </td>
                </tr>
                <?php endif; ?>
                
                <?php if (!empty($submission->brand_guidelines_upload_url)): ?>
                <tr>
                    <th><?php _e('Brand Guidelines Upload', 'client-onboarding-form'); ?></th>
                    <td>
                        <a href="<?php echo esc_url($submission->brand_guidelines_upload_url); ?>" target="_blank" class="button button-small">
                            <span class="dashicons dashicons-download"></span>
                            <?php echo esc_html($submission->brand_guidelines_upload_name ?: 'Download Brand Guidelines Upload'); ?>
                        </a>
                        <small style="color: #666; display: block; margin-top: 5px;">
                            <?php _e('File ID:', 'client-onboarding-form'); ?> <?php echo esc_html($submission->brand_guidelines_upload_id); ?>
                        </small>
                    </td>
                </tr>
                <?php endif; ?>
            </table>

            <!-- Communication Preferences -->
            <h3><?php _e('Communication Preferences', 'client-onboarding-form'); ?></h3>
            <table class="form-table">
                <tr>
                    <th><?php _e('Communication Tone', 'client-onboarding-form'); ?></th>
                    <td><?php echo esc_html($submission->communication_tone); ?></td>
                </tr>
                <?php if ($submission->casual_tone_explanation): ?>
                <tr>
                    <th><?php _e('Casual Tone Explanation', 'client-onboarding-form'); ?></th>
                    <td><?php echo esc_html($submission->casual_tone_explanation); ?></td>
                </tr>
                <?php endif; ?>
                <?php if ($submission->formal_tone_explanation): ?>
                <tr>
                    <th><?php _e('Formal Tone Explanation', 'client-onboarding-form'); ?></th>
                    <td><?php echo esc_html($submission->formal_tone_explanation); ?></td>
                </tr>
                <?php endif; ?>
            </table>

            <!-- Brand Accounts -->
            <h3><?php _e('Brand Accounts', 'client-onboarding-form'); ?></h3>
            <table class="form-table">
                <tr>
                    <th><?php _e('Brand Accounts', 'client-onboarding-form'); ?></th>
                    <td><?php echo esc_html($submission->brand_accounts); ?></td>
                </tr>
                <?php if ($submission->facebook_page): ?>
                <tr>
                    <th><?php _e('Facebook Page', 'client-onboarding-form'); ?></th>
                    <td><?php echo esc_html($submission->facebook_page); ?></td>
                </tr>
                <?php endif; ?>
                <?php if ($submission->instagram_username): ?>
                <tr>
                    <th><?php _e('Instagram Username', 'client-onboarding-form'); ?></th>
                    <td><?php echo esc_html($submission->instagram_username); ?></td>
                </tr>
                <?php endif; ?>
            </table>

            <!-- Social Media Presence -->
            <h3><?php _e('Social Media Presence', 'client-onboarding-form'); ?></h3>
            <table class="form-table">
                <tr>
                    <th><?php _e('Brand Accounts', 'client-onboarding-form'); ?></th>
                    <td><?php echo esc_html($submission->brand_accounts); ?></td>
                </tr>
                <?php if ($submission->facebook_page): ?>
                <tr>
                    <th><?php _e('Facebook Page', 'client-onboarding-form'); ?></th>
                    <td><?php echo esc_html($submission->facebook_page); ?></td>
                </tr>
                <?php endif; ?>
                <?php if ($submission->instagram_username): ?>
                <tr>
                    <th><?php _e('Instagram Username', 'client-onboarding-form'); ?></th>
                    <td><?php echo esc_html($submission->instagram_username); ?></td>
                </tr>
                <?php endif; ?>
            </table>

            <!-- Industry & Competitors -->
            <h3><?php _e('Industry & Competitors', 'client-onboarding-form'); ?></h3>
            <table class="form-table">
                <tr>
                    <th><?php _e('Industry Entities', 'client-onboarding-form'); ?></th>
                    <td><?php echo esc_html($submission->industry_entities); ?></td>
                </tr>
                <tr>
                    <th><?php _e('Market Insights', 'client-onboarding-form'); ?></th>
                    <td><?php echo esc_html($submission->market_insights); ?></td>
                </tr>
                <tr>
                    <th><?php _e('Content/Social Media', 'client-onboarding-form'); ?></th>
                    <td><?php echo esc_html($submission->content_social_media); ?></td>
                </tr>
                <tr>
                    <th><?php _e('Business Focus Elements', 'client-onboarding-form'); ?></th>
                    <td><?php echo esc_html($submission->business_focus_elements); ?></td>
                </tr>
            </table>

            <!-- Business Insights -->
            <h3><?php _e('Business Insights', 'client-onboarding-form'); ?></h3>
            <table class="form-table">
                <tr>
                    <th><?php _e('Market Insights', 'client-onboarding-form'); ?></th>
                    <td><?php echo esc_html($submission->market_insights); ?></td>
                </tr>
                <tr>
                    <th><?php _e('Content/Social Media', 'client-onboarding-form'); ?></th>
                    <td><?php echo esc_html($submission->content_social_media); ?></td>
                </tr>
                <tr>
                    <th><?php _e('Business Focus Elements', 'client-onboarding-form'); ?></th>
                    <td><?php echo esc_html($submission->business_focus_elements); ?></td>
                </tr>
            </table>

            <!-- Social Media Strategy -->
            <h3><?php _e('Social Media Strategy', 'client-onboarding-form'); ?></h3>
            <table class="form-table">
                <?php if ($submission->facebook_page_url): ?>
                <tr>
                    <th><?php _e('Facebook Page URL', 'client-onboarding-form'); ?></th>
                    <td><a href="<?php echo esc_url($submission->facebook_page_url); ?>" target="_blank"><?php echo esc_html($submission->facebook_page_url); ?></a></td>
                </tr>
                <?php endif; ?>
                <?php if ($submission->instagram_page_url): ?>
                <tr>
                    <th><?php _e('Instagram Page URL', 'client-onboarding-form'); ?></th>
                    <td><a href="<?php echo esc_url($submission->instagram_page_url); ?>" target="_blank"><?php echo esc_html($submission->instagram_page_url); ?></a></td>
                </tr>
                <?php endif; ?>



                <?php if ($submission->twitter_accounts_url): ?>
                <tr>
                    <th><?php _e('Twitter Account URL', 'client-onboarding-form'); ?></th>
                    <td><a href="<?php echo esc_url($submission->twitter_accounts_url); ?>" target="_blank"><?php echo esc_html($submission->twitter_accounts_url); ?></a></td>
                </tr>
                <?php endif; ?>


            </table>

            <!-- Target Audience -->
            <h3><?php _e('Target Audience', 'client-onboarding-form'); ?></h3>
            <table class="form-table">
                <tr>
                    <th><?php _e('Ideal Customer Description', 'client-onboarding-form'); ?></th>
                    <td><?php echo esc_html($submission->ideal_customer_description); ?></td>
                </tr>
                <tr>
                    <th><?php _e('Potential Client View', 'client-onboarding-form'); ?></th>
                    <td><?php echo esc_html($submission->potential_client_view); ?></td>
                </tr>
                <tr>
                    <th><?php _e('Target Age Range', 'client-onboarding-form'); ?></th>
                    <td><?php echo esc_html($submission->target_age_range); ?></td>
                </tr>


                <tr>
                    <th><?php _e('Problems Solved', 'client-onboarding-form'); ?></th>
                    <td><?php echo esc_html($submission->problems_solved); ?></td>
                </tr>
            </table>

            <!-- Business Performance -->
            <h3><?php _e('Business Performance', 'client-onboarding-form'); ?></h3>
            <table class="form-table">
                <tr>
                    <th><?php _e('Business Challenges', 'client-onboarding-form'); ?></th>
                    <td><?php echo esc_html($submission->business_challenges); ?></td>
                </tr>
                <tr>
                    <th><?php _e('Tracking/Accounting', 'client-onboarding-form'); ?></th>
                    <td><?php echo esc_html($submission->tracking_accounting); ?></td>
                </tr>
                <?php if ($submission->additional_information): ?>
                <tr>
                    <th><?php _e('Additional Information', 'client-onboarding-form'); ?></th>
                    <td><?php echo esc_html($submission->additional_information); ?></td>
                </tr>
                <?php endif; ?>
            </table>
        </div>

        <!-- Actions -->
        <div class="cob-section">
            <h2><?php _e('Actions', 'client-onboarding-form'); ?></h2>
            <p>
                <a href="<?php echo admin_url('admin.php?page=cob-submissions'); ?>" class="button button-secondary">
                    <?php _e('← Back to Submissions', 'client-onboarding-form'); ?>
                </a>
                <button type="button" id="cob-send-test-email" class="button button-primary" 
                        data-submission-id="<?php echo esc_attr($submission->id); ?>">
                    <?php _e('Send Test Email', 'client-onboarding-form'); ?>
                </button>
                <a href="<?php echo admin_url('admin.php?page=cob-submissions&action=delete&view=' . $submission->id); ?>" 
                   class="button button-link-delete" 
                   onclick="return confirm('<?php _e('Are you sure you want to delete this submission?', 'client-onboarding-form'); ?>')">
                    <?php _e('Delete Submission', 'client-onboarding-form'); ?>
                </a>
            </p>
            
            <!-- Test Email Result -->
            <div id="cob-test-email-result" style="margin-top: 10px;"></div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            $('#cob-send-test-email').on('click', function() {
                var $button = $(this);
                var $result = $('#cob-test-email-result');
                var submissionId = $button.data('submission-id');
                
                $button.prop('disabled', true).text('<?php _e('Sending...', 'client-onboarding-form'); ?>');
                $result.html('');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'cob_send_test_notification',
                        nonce: '<?php echo wp_create_nonce('cob_admin_nonce'); ?>',
                        submission_id: submissionId
                    },
                    success: function(response) {
                        if (response.success) {
                            $result.html('<div class="notice notice-success"><p>' + response.data + '</p></div>');
                        } else {
                            $result.html('<div class="notice notice-error"><p>Error: ' + response.data + '</p></div>');
                        }
                    },
                    error: function() {
                        $result.html('<div class="notice notice-error"><p>AJAX request failed. Please try again.</p></div>');
                    },
                    complete: function() {
                        $button.prop('disabled', false).text('<?php _e('Send Test Email', 'client-onboarding-form'); ?>');
                    }
                });
            });
        });
        </script>
    </div>
</div>