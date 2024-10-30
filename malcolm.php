<?php
/*
Plugin Name: Malcolm!
Description: Bring your Malcolm! content into your Wordpress site.
Author: Malcolm!
Author URI: https://malcolm.app
Version: 1.1
License: GPLv2 or later
*/

/**
 * Run when the plugin is deactivated.
 *
 * @return void
 */
function malcolm_deactivate()
{
    delete_option('malcolm_instance_id');
    delete_option('malcolm_allow_branding');
}

register_deactivation_hook(__FILE__, 'malcolm_deactivate');

/**
 * Add additional actions inside the admin.
 *
 * @param  array  $actions
 * @return array
 */
function malcolm_plugin_action_links($actions)
{
    $links = [
        '<a href="' . admin_url( 'options-general.php?page=malcolm' ) . '">Settings</a>'
    ];

    $actions = array_merge($links, $actions);

    return $actions;
}

add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'malcolm_plugin_action_links');

/**
 * Output the HTML for the plugin options page in the admin tool.
 *
 * @return void
 */
function malcolm_output_admin_options()
{
    if (!current_user_can('manage_options') )  {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }

    ob_start();

    include(plugin_dir_path(__FILE__) . 'malcolm-admin-options.php');

    echo ob_get_clean();
}

/**
 * Define an options page for the admin.
 *
 * @return void
 */
function malcolm_admin_menu()
{
    add_options_page('Malcolm! Settings', 'Malcolm!', 'manage_options', 'malcolm', 'malcolm_output_admin_options');
}

add_action('admin_menu', 'malcolm_admin_menu');

/**
 * Register the admin settings.
 *
 * @return void
 */
function malcolm_register_settings() {

    register_setting(
        'malcolm',
        'malcolm_instance_id',
        'malcolm_sanitize_instance_id'
    );

    register_setting(
        'malcolm',
        'malcolm_allow_branding',
        'malcolm_sanitize_allow_branding'
    );

    add_settings_section(
        'malcolm',
        __('General Settings'),
        'malcolm_output_general_settings_section',
        'malcolm'
    );

    add_settings_field(
        'malcolm_instance_id',
        __('Instance ID'),
        'malcolm_output_instance_id_field',
        'malcolm',
        'malcolm'
    );

    add_settings_field(
        'malcolm_allow_branding',
        __('Malcolm! Branding'),
        'malcolm_output_allow_branding_field',
        'malcolm',
        'malcolm'
    );
}

add_action('admin_init', 'malcolm_register_settings');

/**
 * Output content at the top of the general settings section.
 *
 * @return void
 */
function malcolm_output_general_settings_section() {
    echo __('Configure your Malcolm! Plugin settings here.');
}

/**
 * Output the instance id field.
 *
 * @return void
 */
function malcolm_output_instance_id_field() {
    printf('<input type="text" name="%s" value="%s"><p>%s</p>',
        esc_attr('malcolm_instance_id'),
        esc_attr(get_option('malcolm_instance_id')),
        __('Your Instance ID can be found at the top of the MyMalcolm > Settings area.')
    );
}

/**
 * Output the allow branding field.
 *
 * @return void
 */
function malcolm_output_allow_branding_field() {
    printf('<label for="%s"><input type="checkbox" id="%s" name="%s" value="yes" %s> %s</label><p>%s</p>',
        esc_attr('malcolm_allow_branding'),
        esc_attr('malcolm_allow_branding'),
        esc_attr('malcolm_allow_branding'),
        get_option('malcolm_allow_branding') === 'yes' ? 'checked' : '',
        __('Allow the "Powered By Malcolm!" branding (affects non-blended, inline embeds only)'),
        __('Whether the branding in the footer shows for non-blended, inline embeds depends on the setting in MyMalcolm > Share > Embeds.<br>If enabled then this setting must also be enabled in order for the footer to show.')
    );
}

/**
 * Sanitize and validate the "instance_id" setting.
 *
 * @param string $value
 * @return string
 */
function malcolm_sanitize_instance_id($value) {

    $value = sanitize_text_field($value);

    if (!preg_match('/^[a-z0-9]{10}$/i', $value)) {
        add_settings_error(
            'malcolm_admin_notice',
            esc_attr('malcolm_admin_notice'),
            __('Invalid Instance ID.')
        );
    }

    return $value;
}

/**
 * Sanitize and validate the "allow_branding" setting.
 *
 * @param string $value
 * @return string
 */
function malcolm_sanitize_allow_branding($value) {

    $value = $value ? sanitize_text_field($value) : 'no';

    if (!in_array($value, [ 'yes', 'no' ])) {
        add_settings_error(
            'malcolm_admin_notice',
            esc_attr('malcolm_admin_notice'),
            __('Invalid Malcolm! Branding setting.')
        );
    }

    return $value;
}

/**
 * Add the script tag to the WP footer.
 *
 * @return void
 */
function malcolm_wp_footer()
{
    if (($id = get_option('malcolm_instance_id'))) {
        echo '<script src="https://apis.malcolm.app/mapi.js?id=' . esc_html($id) . '" defer></script>';
    }
}

add_action('wp_footer', 'malcolm_wp_footer');

/**
 * Ouput the HTML for inline embeds.
 *
 * @param  mixed  $atts
 * @return void
 */
function malcolm_output_inline($atts = [])
{
    if (!($embedId = array_shift($atts))) {
        return;
    }

    ob_start();

    include(plugin_dir_path(__FILE__) . 'malcolm-inline.php');

    return ob_get_clean();
}

add_shortcode('malcolm_inline', 'malcolm_output_inline');

/**
 * Ouput the HTML for popup embeds.
 *
 * @param  mixed  $atts
 * @return void
 */
function malcolm_output_popup($atts = [])
{
    if (!($embedId = array_shift($atts))) {
        return;
    }

    ob_start();

    include(plugin_dir_path(__FILE__) . 'malcolm-popup.php');

    return ob_get_clean();
}

add_shortcode('malcolm_popup', 'malcolm_output_popup');
