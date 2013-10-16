<?php

// Check the API Key
if ( ! get_option( 'wpcac_api_key' ) ) {

    echo json_encode( 'blank-api-key' );
    exit;

} elseif ( ! isset( $_GET['wpcac_api_key'] ) || urldecode( $_GET['wpcac_api_key'] ) !== get_option( 'wpcac_api_key' ) || ! isset( $_GET['actions'] ) ) {

    echo json_encode( 'bad-api-key' );
    exit;

}

$actions = explode( ',', sanitize_text_field( $_GET['actions'] ) );
$actions = array_flip( $actions );

// Disable error_reporting so they don't break the json request
if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG )
    error_reporting( 0 );

// Log in as admin
$admins = get_users('role=administrator');

foreach($admins as $admin){
    wp_set_current_user( $admin->ID );
};

foreach( $actions as $action => $value ) {

    // TODO Instead should just fire actions which we hook into.
    // TODO should namespace api methods?
    switch( $action ) {

        // TODO should be dynamic
    case 'get_plugin_version' :
        $actions[$action] = '1.10';
        break;

    case 'get_filesystem_method' :
        $actions[$action] = get_filesystem_method();
        break;

    case 'get_supported_filesystem_methods' :
        $actions[$action] = array();
        if ( extension_loaded( 'ftp' ) || extension_loaded( 'sockets' ) || function_exists( 'fsockopen' ) )
            $actions[$action][] = 'ftp';
        if ( extension_loaded( 'ftp' ) )
            $actions[$action][] = 'ftps';
        if ( extension_loaded( 'ssh2' ) && function_exists( 'stream_get_contents' ) )
            $actions[$action][] = 'ssh';
        break;

    case 'get_wp_version' :
        global $wp_version;
        $actions[$action] = get_bloginfo('version');
        break;

    case 'upgrade_core' :
        $actions[$action] = _wpcac_upgrade_core();
        break;

    case 'get_plugins' :
        $actions[$action] = _wpcac_supports_plugin_upgrade() ? _wpcac_get_plugins() : 'not-implemented';
        break;

    case 'upgrade_plugin' :
        $actions[$action] = _wpcac_upgrade_plugin( (string) sanitize_text_field( $_GET['plugin'] ) );
        break;

    case 'activate_plugin' :
        $actions[$action] = _wpcac_activate_plugin( (string) sanitize_text_field( $_GET['plugin'] ) );
        break;

    case 'deactivate_plugin' :
        $actions[$action] = _wpcac_deactivate_plugin( (string) sanitize_text_field( $_GET['plugin'] ) );
        break;

    case 'install_plugin' :
        $api_args = array(
            'version'      => sanitize_text_field( (string) sanitize_text_field( $_GET['version'] ) ),
        );
        $actions[$action] = _wpcac_install_plugin( (string) sanitize_text_field( $_GET['plugin'] ), $api_args );
        break;

    case 'uninstall_plugin' :
        $actions[$action] = _wpcac_uninstall_plugin( (string) sanitize_text_field( $_GET['plugin'] ) );
        break;

    case 'get_themes' :
        $actions[$action] = _wpcac_supports_theme_upgrade() ? _wpcac_get_themes() : 'not-implemented';
        break;

    case 'upgrade_theme' :
        $actions[$action] = _wpcac_upgrade_theme( (string) sanitize_text_field( $_GET['theme'] ) );
        break;

    case 'get_files' :
        $actions[$action] = _wpcac_get_files();
        break;

    case 'do_backup' :
    case 'do_sql_backup' :
    case 'delete_backup' :
    case 'supports_backups' :
    case 'get_backup' :
        $actions[$action] = _wpcac_backups_api_call( $action );
        break;

    case 'get_site_info' :
        $actions[$action] = array(
            'site_url'	=> get_site_url(),
            'home_url'	=> get_home_url(),
            'admin_url'	=> get_admin_url(),
            'site_title' => get_bloginfo('name'),
            'abspath' => WPCAC_HM_Backup::get_home_path(),
            'backups'	=> _wpcac_get_backups_info()

        );
        break;

    case 'get_option_value' :
        $actions[$action] = array(
            sanitize_text_field( $_GET['option_name']) => get_option((string) sanitize_text_field( $_GET['option_name'] ))
        );
        break;

    default :
        $actions[$action] = 'not-implemented';
        break;

    }

}

echo json_encode( $actions );

exit;
