# Client Onboarding Form - WordPress Plugin

A professional WordPress plugin that streamlines client onboarding through a modern, multi-step form interface. Built specifically for digital agencies like FLUX to collect comprehensive client information across multiple categories.

## Features

### 🎯 Multi-Step Form Interface
- **4 Progressive Steps**: Client Information, Technical Information, Reporting Information, Marketing Information
- **Step Navigation**: Visual progress indicator with clickable step navigation
- **Form Validation**: Real-time field validation with clear error messages
- **Auto-save**: Automatic draft saving every 30 seconds
- **Responsive Design**: Works seamlessly on all device sizes

### 🎨 Modern UI Design
- **Dark Theme**: Professional dark interface matching the FLUX brand
- **FLUX Branding**: Integrated logo and brand colors (#9dff00 lime green accent)
- **Clean Layout**: Grid-based responsive form layout
- **Accessibility**: WCAG 2.1 AA compliant with keyboard navigation support

### 💾 Data Management
- **Database Storage**: Custom tables for optimal performance
- **Draft System**: Save and restore form progress
- **Session Management**: Client-generated session IDs for draft persistence
- **Data Validation**: Server-side sanitization and validation

### 🔧 Admin Interface
- **WordPress Native**: Seamlessly integrated with WordPress admin
- **Dashboard**: Statistics overview and recent submissions
- **Submissions Manager**: Search, filter, view, and manage submissions
- **Settings Panel**: Configure email notifications and general settings
- **Activity Logs**: Monitor system events and errors

### 📧 Email System
- **Admin Notifications**: Alert when new submissions are received
- **Customizable Templates**: Dynamic content insertion with template variables
- **Reliable Delivery**: Built-in email queue system

### 🔌 Integration Ready
- **Webhook Support**: Send data to third-party services (Zapier, Make.com, etc.)
- **JSON API**: Structured data delivery
- **Testing Tools**: Built-in webhook testing functionality

## Installation

1. **Upload Plugin**: Upload the `client-onboarding-form` folder to `/wp-content/plugins/`
2. **Activate**: Activate the plugin through the 'Plugins' menu in WordPress
3. **Database Setup**: The plugin will automatically create necessary database tables
4. **Configure**: Go to 'Client Onboarding' → 'Settings' to configure email and webhook settings

## Usage

### Display the Form

Use the shortcode to display the form on any page or post:

```
[client_onboarding_form]
```

### Shortcode Parameters

```
[client_onboarding_form theme="dark" show_progress="true" auto_save="true"]
```

- **theme**: "dark" (default) or "light"
- **show_progress**: true (default) or false
- **auto_save**: true (default) or false

### Form Structure

#### Step 1: Client Information
- Project Name (required)
- Business Name (required)
- Primary Contact Name (required)
- Primary Contact Email (required)
- Primary Contact Number
- Milestone Approver
- Billing Email Address
- VAT Number (optional)
- Preferred Contact Method (Phone/Email)
- Billing Address (complete address fields)

#### Step 2: Technical Information
- Current Website URL (required)
- Current Hosting Provider
- Domain Provider
- Technical Contact Name (required)
- Technical Contact Email (required)
- Preferred CMS/Platform
- Integration Requirements
- Current Technology Stack (checkboxes)

#### Step 3: Reporting Information
- Reporting Frequency (required)
- Preferred Report Format
- Reporting Contact Name (required)
- Reporting Contact Email (required)
- Dashboard Access Level
- Key Metrics to Track (checkboxes)
- Additional Reporting Requirements

#### Step 4: Marketing Information
- Target Audience (required)
- Marketing Goals (required)
- Marketing Budget Range
- Current Marketing Channels (checkboxes)
- Brand Guidelines
- Competitor Analysis
- Current Marketing Challenges
- Success Metrics

## Admin Management

### Dashboard
- View submission statistics (total, this month, this week)
- Quick access to recent submissions
- Shortcode information and usage guide

### Submissions Manager
- List all form submissions
- View detailed submission information
- Delete submissions
- Search and filter capabilities

### Settings
- Configure admin email for notifications
- Set company name for email templates
- Enable/disable email notifications
- Adjust auto-save interval (10-300 seconds)
- Configure webhook integration
- View database statistics

## Database Structure

### Tables Created
- `wp_cob_submissions` - Store form submissions
- `wp_cob_drafts` - Store draft progress
- `wp_cob_logs` - Activity and error logs

### Data Security
- SQL injection protection (prepared statements)
- XSS protection with proper escaping
- CSRF protection with nonces
- Data sanitization and validation

## Customization

### Styling
The plugin includes comprehensive CSS that can be customized:
- Main form styles: `/assets/css/form-style.css`
- Admin styles: `/assets/css/admin-style.css`

### Hooks and Filters
- `cob_before_form_render` - Before form display
- `cob_after_submission` - After successful submission
- `cob_email_template` - Customize email templates
- `cob_webhook_data` - Modify webhook payload

## Technical Requirements

- **WordPress**: 5.0+
- **PHP**: 7.4+
- **MySQL**: 5.6+
- **Dependencies**: None (pure WordPress implementation)

## Performance

- **Minimal Database Queries**: Optimized query structure
- **Efficient Caching**: Where appropriate
- **Lightweight Assets**: Optimized CSS and JavaScript
- **Mobile Optimized**: Responsive design with mobile-first approach

## Security Features

- **Nonce Verification**: All admin actions protected
- **Data Sanitization**: All input sanitized and validated
- **SQL Injection Protection**: Prepared statements only
- **XSS Protection**: Proper output escaping
- **CSRF Protection**: Cross-site request forgery prevention

## Browser Support

- **Modern Browsers**: Chrome 70+, Firefox 65+, Safari 12+, Edge 79+
- **Mobile**: iOS Safari 12+, Chrome Mobile 70+
- **Accessibility**: Screen reader compatible, keyboard navigation

## License

GPL2+ - See LICENSE file for details

## Support

For support and feature requests, please contact the development team or create an issue in the plugin repository.

## Changelog

### Version 1.0.0
- Initial release
- Multi-step form with 4 progressive steps
- Admin dashboard and submission management
- Email notification system
- Webhook integration support
- Draft auto-save functionality
- Responsive dark theme design
- Complete WordPress integration