# Share Link Functionality

This document explains how the share link functionality works in the Client Onboarding Form plugin.

## How Share Links Work

1. **Admin Generates Link**: Administrators can generate share links from the Drafts management page
2. **Token Creation**: A unique token is created and stored in the database
3. **Link Format**: Links follow the format: `yoursite.com/page-with-form?cob_share=share_TOKEN123`
4. **Automatic Redirect**: When someone visits a share link, they're automatically redirected to the form page
5. **Draft Loading**: The form automatically loads the shared draft data

## Testing Share Links

### Step 1: Create a Draft
1. Go to your form page
2. Fill out some information and save a draft
3. Go to **Client Onboarding > Drafts** in WordPress admin

### Step 2: Generate Share Link
1. Find your draft in the list
2. Click **Generate Share Link**
3. Copy the generated link

### Step 3: Test the Link
1. Open the copied link in a new browser window/tab
2. You should be automatically redirected to the form page
3. The form should load with the saved draft data
4. The URL should update to remove the share parameter

## Troubleshooting Share Links

### Link Not Working?
1. **Check if form page exists**: Ensure you have a page with the `[client_onboarding_form]` shortcode
2. **Verify token exists**: Check the database to ensure the share token was created
3. **Check redirects**: Ensure your hosting doesn't block redirects

### Form Not Loading Draft Data?
1. **Check browser console**: Look for JavaScript errors
2. **Verify AJAX endpoint**: Ensure the `cob_get_shared_draft` action works
3. **Check database**: Verify the draft data exists and is valid JSON

### Common Issues

#### Issue: "Page not found" when visiting share link
**Solution**: The plugin needs to find a page with the form shortcode. Create a page with `[client_onboarding_form]` shortcode.

#### Issue: Form loads but no draft data
**Solution**: Check the browser console for AJAX errors. Verify the share token is valid in the database.

#### Issue: Redirect loop
**Solution**: Ensure the redirect logic only runs once. Check if the form page URL is correct.

## Technical Details

### Database Structure
- Share tokens are stored in the `wp_cob_drafts` table
- Each token is unique and linked to a session ID
- Tokens are automatically generated when requested

### URL Processing
- The plugin hooks into `template_redirect` to catch share links
- It searches for pages containing the form shortcode
- Users are redirected to the form page with the share token

### Frontend Handling
- JavaScript detects the share token in the URL
- AJAX request fetches the draft data
- Form is populated with the shared data
- URL is cleaned up to remove the token

## Security Considerations

- Share tokens are randomly generated and secure
- Tokens are tied to specific draft sessions
- No sensitive data is exposed in the URL
- Access is controlled through WordPress permissions

## Customization

### Change Share Link Format
Modify the `handle_generate_share_link` method in `class-admin.php`:

```php
$link = home_url('/custom-path/' . $token); // Custom URL structure
```

### Add Expiration to Tokens
Add an expiration field to the database and check it when loading drafts.

### Custom Redirect Logic
Modify the `handle_share_token_redirect` method to implement custom redirect behavior.

## Support

If you continue to have issues with share links:

1. Check the WordPress debug log for errors
2. Verify the form shortcode is properly placed on a page
3. Test with a fresh draft and new share link
4. Check browser console for JavaScript errors
5. Verify database connectivity and table structure
