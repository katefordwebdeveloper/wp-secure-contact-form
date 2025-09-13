# WP Secure Contact Form

A modern, accessible, and secure WordPress contact form plugin with toast notifications and custom styling.

## Features

- Choose which fields to display: Name, Email, Subject, Message
- Admin settings page for easy configuration
- Toast notifications for success and error messages
- Accessible and keyboard-friendly
- Stylish, responsive, and customizable CSS

## Security Highlights

- Nonce protection to prevent CSRF attacks
- Strict input sanitization and validation for all fields
- Admin-only access to plugin settings
- Secure email handling with safe headers

## Installation

1. Upload the `wp-secure-contact-form` folder to your WordPress `/wp-content/plugins/` directory.
2. Activate the plugin from the WordPress admin dashboard.
3. Go to **Settings > Secure Contact Form** to configure fields and recipient email.
4. Use the `[wp_secure_contact_form]` shortcode to display the form on any page or post.

## Usage

Add the following shortcode to any page or post:

```
[wp_secure_contact_form]
```

## Screenshots

**Contact Form (all fields enabled):**  
![Contact Form](screenshots/contact-form-all-fields.jpg)

**Settings Page:**  
![Settings Page](screenshots/plugin-settings-page.jpg)

**Success Message:**  
![Success Message](screenshots/contact-form-success.jpg)

**Error Message – Invalid Email Format:**  
![Email Format Error](screenshots/email-formatting-error.jpg)

**Error Message – Message Field Required:**  
![Message Field Error](screenshots/mandatory-message-missing-error.jpg)

**Mailtrap Email Example:**  
![Mailtrap Email](screenshots/mailtrap-email.jpg)

## Customization

- Edit `wp-secure-contact-form.css` to change the form and toast styles.
- Edit `wp-secure-contact-form.php` for advanced customization.

## License

GPL2

---

**View the full source code and more screenshots:**  
[https://github.com/katefordwebdeveloper/wp-secure-contact-form](https://github.com/katefordwebdeveloper/wp-secure-contact-form)