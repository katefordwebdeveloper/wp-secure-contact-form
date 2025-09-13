<?php
/**
 * Plugin Name: WP Secure Contact Form
 * Description: A secure contact form plugin for WordPress.
 * Version: 1.1.0
 * Author: Katharine Ford
 * License: GPL2
 * Text Domain: wp-secure-contact-form
 */

/**
 * Shortcode to display the form (wscf_status = form status)
 */
function wp_secure_contact_form_shortcode() {
    ob_start();

    // Get field settings (default: all fields enabled)
    $fields = get_option('wscf_fields', array('name', 'email', 'subject', 'message'));
    if (!is_array($fields)) $fields = array('name', 'email', 'subject', 'message');

    // Show toast message if status is set
    if ( isset( $_GET['wscf_status'] ) ) {
        if ( $_GET['wscf_status'] === 'success' ) {
            echo '
                <div id="wscf-toast" class="wscf-toast wscf-toast-success" style="display:none;">
                    ' . esc_html__('Thank you for contacting us. Your message has been received and we will get back to you soon.', 'wp-secure-contact-form') . '
                </div>
            ';
        } else {
            echo '
                <div id="wscf-toast" class="wscf-toast wscf-toast-error" style="display:none;">
                    ' . esc_html__('There was an error. Please try again.', 'wp-secure-contact-form') . '
                </div>
            ';
        }
    }

    // Always show the form
    ?>
    <form class="wscf-form" method="post" action="">
        <?php wp_nonce_field( 'wp_secure_contact_form_action', 'wp_secure_contact_form_nonce' ); ?>
        <div class="wscf-form-heading"><?php esc_html_e('Contact Us', 'wp-secure-contact-form'); ?></div>
        <?php if (in_array('name', $fields)) : ?>
        <p>
            <label for="wscf_name"><?php echo esc_html__('Name', 'wp-secure-contact-form'); ?></label><br>
            <input type="text" id="wscf_name" name="wscf_name" required aria-required="true">
        </p>
        <?php endif; ?>
        <?php if (in_array('email', $fields)) : ?>
        <p>
            <label for="wscf_email"><?php echo esc_html__('Email', 'wp-secure-contact-form'); ?></label><br>
            <input type="email" id="wscf_email" name="wscf_email" required aria-required="true">
        </p>
        <?php endif; ?>
        <?php if (in_array('subject', $fields)) : ?>
        <p>
            <label for="wscf_subject"><?php echo esc_html__('Subject', 'wp-secure-contact-form'); ?></label><br>
            <input type="text" id="wscf_subject" name="wscf_subject" required aria-required="true">
        </p>
        <?php endif; ?>
        <?php if (in_array('message', $fields)) : ?>
        <p>
            <label for="wscf_message"><?php echo esc_html__('Message', 'wp-secure-contact-form'); ?></label><br>
            <textarea id="wscf_message" name="wscf_message" required aria-required="true"></textarea>
        </p>
        <?php endif; ?>
        <p class="wscf-submit-row">
            <input type="submit" name="wscf_submit" value="<?php echo esc_attr__('Send', 'wp-secure-contact-form'); ?>">
        </p>
    </form>
    <?php

    return ob_get_clean();
}
add_shortcode( 'wp_secure_contact_form', 'wp_secure_contact_form_shortcode' );

/**
 * Handle form submission
 */
function wp_secure_contact_form_handle_post() {
    if ( isset( $_POST['wscf_submit'] ) ) {
        // Verify nonce
        if ( ! isset( $_POST['wp_secure_contact_form_nonce'] ) || 
             ! wp_verify_nonce( $_POST['wp_secure_contact_form_nonce'], 'wp_secure_contact_form_action' ) ) {
            wp_redirect( add_query_arg( 'wscf_status', 'error', wp_get_referer() ) );
            exit;
        }

        // Get field settings
        $fields = get_option('wscf_fields', array('name', 'email', 'subject', 'message'));
        if (!is_array($fields)) $fields = array('name', 'email', 'subject', 'message');

        // Sanitize input
        $name    = isset($_POST['wscf_name']) ? sanitize_text_field( $_POST['wscf_name'] ) : '';
        $email   = isset($_POST['wscf_email']) ? sanitize_email( $_POST['wscf_email'] ) : '';
        $subject = isset($_POST['wscf_subject']) ? sanitize_text_field( $_POST['wscf_subject'] ) : '';
        $message = isset($_POST['wscf_message']) ? sanitize_textarea_field( $_POST['wscf_message'] ) : '';

        // Basic validation, wscf_status = error
        if (
            (in_array('name', $fields) && empty($name)) ||
            (in_array('email', $fields) && (empty($email) || !is_email($email))) ||
            (in_array('subject', $fields) && empty($subject)) ||
            (in_array('message', $fields) && empty($message))
        ) {
            wp_redirect( add_query_arg( 'wscf_status', 'error', wp_get_referer() ) );
            exit;
        }

        // Send email to recipient
        $recipient_email = sanitize_email(get_option('wscf_recipient_email', get_option('admin_email')));
        $mail_subject = (in_array('subject', $fields) && !empty($subject)) ? $subject : 'New Contact Form Submission';
        $body = '';
        if (in_array('name', $fields))    $body .= "Name: $name\n";
        if (in_array('email', $fields))   $body .= "Email: $email\n";
        if (in_array('subject', $fields)) $body .= "Subject: $subject\n";
        if (in_array('message', $fields)) $body .= "Message:\n$message\n";
        $headers = array( 'Content-Type: text/plain; charset=UTF-8' );
        if (in_array('email', $fields) && !empty($email)) {
            $headers[] = "Reply-To: $name <$email>";
        }

        wp_mail( $recipient_email, $mail_subject, $body, $headers );

        // Redirect with success
        wp_redirect( add_query_arg( 'wscf_status', 'success', wp_get_referer() ) );
        exit;
    }
}
add_action( 'init', 'wp_secure_contact_form_handle_post' );

/**
 * Enqueue the plugin's CSS and JS only if the shortcode is present on the page
 */
function wscf_enqueue_assets() {
    if ( is_singular() && has_shortcode( get_post()->post_content, 'wp_secure_contact_form' ) ) {
        wp_enqueue_style(
            'wp-secure-contact-form',
            plugins_url( 'wp-secure-contact-form.css', __FILE__ ),
            array(),
            '1.0.0'
        );
        wp_enqueue_script(
            'wp-secure-contact-form',
            plugins_url( 'wp-secure-contact-form.js', __FILE__ ),
            array(),
            '1.0.0',
            true
        );
    }
}
add_action( 'wp_enqueue_scripts', 'wscf_enqueue_assets' );

// Add settings menu
add_action('admin_menu', function() {
    add_options_page(
        'Secure Contact Form Settings',
        'Secure Contact Form',
        'manage_options',
        'wp-secure-contact-form',
        'wscf_settings_page'
    );
});

// Register settings
add_action('admin_init', function() {
    register_setting('wscf_settings_group', 'wscf_recipient_email', array(
        'type' => 'string',
        'sanitize_callback' => 'sanitize_email',
        'default' => get_option('admin_email')
    ));
    register_setting('wscf_settings_group', 'wscf_fields', array(
        'type' => 'array',
        'sanitize_callback' => function($fields) {
            $allowed = array('name', 'email', 'subject', 'message');
            return array_values(array_intersect($fields, $allowed));
        },
        'default' => array('name', 'email', 'subject', 'message')
    ));
});

// Settings page callback
function wscf_settings_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html__( 'You do not have sufficient permissions to access this page.' ) );
    }
    $fields = get_option('wscf_fields', array('name', 'email', 'subject', 'message'));
    if (!is_array($fields)) $fields = array('name', 'email', 'subject', 'message');
    ?>
    <div class="wrap">
        <h1>Secure Contact Form Settings</h1>
        <form method="post" action="options.php">
            <?php settings_fields('wscf_settings_group'); ?>
            <?php do_settings_sections('wscf_settings_group'); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Recipient Email</th>
                    <td>
                        <input type="email" name="wscf_recipient_email" value="<?php echo esc_attr(get_option('wscf_recipient_email', get_option('admin_email'))); ?>" size="40" />
                        <p class="description">Leave blank to use the site admin email.</p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Fields to Show</th>
                    <td>
                        <label><input type="checkbox" name="wscf_fields[]" value="name" <?php checked(in_array('name', $fields)); ?>> Name</label><br>
                        <label><input type="checkbox" name="wscf_fields[]" value="email" <?php checked(in_array('email', $fields)); ?>> Email</label><br>
                        <label><input type="checkbox" name="wscf_fields[]" value="subject" <?php checked(in_array('subject', $fields)); ?>> Subject</label><br>
                        <label><input type="checkbox" name="wscf_fields[]" value="message" <?php checked(in_array('message', $fields)); ?>> Message</label>
                        <p class="description">Select which fields to display on the contact form.</p>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}