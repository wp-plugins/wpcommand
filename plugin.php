<?php

/*
Plugin Name: WP Command and Control
Description: Manage your WordPress site with <a href="https://wpcommandcontrol.com/">WP Command and Control</a>. <strong>Deactivate to clear your API Key.</strong>
Version: 1.10
Author: SoJu Studios
Author URI: http://supersoju.com/
 */

/*  Copyright 2013 Soju LLC  (email : support@supersoju.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

define( 'WPCAC_PLUGIN_SLUG', 'wpcommand' );
define( 'WPCAC_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

if ( ! defined( 'WPCAC_API_URL' ) )
    define( 'WPCAC_API_URL', 'https://wpcommandcontrol.com/api/json/' );

// Don't activate on anything less than PHP 5.2.4
if ( version_compare( phpversion(), '5.2.4', '<' ) ) {

    require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    deactivate_plugins( WPCAC_PLUGIN_SLUG . '/plugin.php' );

    if ( isset( $_GET['action'] ) && ( $_GET['action'] == 'activate' || $_GET['action'] == 'error_scrape' ) )
        die( __( 'WP Command and Control requires PHP version 5.2.4 or greater.', 'wpcommand' ) );

}

require_once( WPCAC_PLUGIN_PATH . '/wpcac.admin.php' );
require_once( WPCAC_PLUGIN_PATH . '/wpcac.compatability.php' );

// Backups require 3.1
if ( version_compare( get_bloginfo( 'version' ), '3.1', '>=' ) ) {

    require_once( WPCAC_PLUGIN_PATH . '/wpcac.hm.backup.php' );
    require_once( WPCAC_PLUGIN_PATH . '/wpcac.backups.php' );

}

// Don't include when doing a core update
if ( empty( $_GET['action'] ) || $_GET['action'] != 'do-core-upgrade' ) :

    require_once ( ABSPATH . 'wp-admin/includes/class-wp-upgrader.php' );

require_once WPCAC_PLUGIN_PATH . 'inc/class-wpcac-plugin-upgrader-skin.php';

class WPCAC_Theme_Upgrader_Skin extends Theme_Installer_Skin {

    var $feedback;
    var $error;

    function error( $error ) {
        $this->error = $error;
    }

    function feedback( $feedback ) {
        $this->feedback = $feedback;
    }

    function before() { }

        function after() { }

        function header() { }

        function footer() { }

}

class WPCAC_Core_Upgrader_Skin extends WP_Upgrader_Skin {

    var $feedback;
    var $error;

    function error( $error ) {
        $this->error = $error;
    }

    function feedback( $feedback ) {
        $this->feedback = $feedback;
    }

    function before() { }

        function after() { }

        function header() { }

        function footer() { }

}

endif;

/**
 * Catch the API calls and load the API
 *
 * @return null
 */
function WPCAC_catch_api_call() {

    if ( empty( $_GET['wpcac_api_key'] ) || ! urldecode( $_GET['wpcac_api_key'] ) || ! isset( $_GET['actions'] ) )
        return;

    require_once( WPCAC_PLUGIN_PATH . '/wpcac.plugins.php' );
    require_once( WPCAC_PLUGIN_PATH . '/wpcac.themes.php' );

    require_once( WPCAC_PLUGIN_PATH . '/wpcac.api.php' );

    exit;

}
add_action( 'init', 'WPCAC_catch_api_call', 1 );

function WPCAC_plugin_update_check() {

    $plugin_data = get_plugin_data( __FILE__ );

    // define the plugin version
    define( 'WPCAC_VERSION', $plugin_data['Version'] );

    // Fire the update action
    if ( WPCAC_VERSION !== get_option( 'WPCAC_plugin_version' ) )
        WPCAC_update();

}
add_action( 'admin_init', 'WPCAC_plugin_update_check' );

/**
 * Run any update code and update the current version in the db
 *
 * @access public
 * @return void
 */
function WPCAC_update() {

    /**
     * Remove the old _wpcommand_backups directory
     */
    $uploads_dir = wp_upload_dir();

    $old_wpcommand_dir = trailingslashit( $uploads_dir['basedir'] ) . '_wpcommand_backups';

    if ( file_exists( $old_wpcommand_dir ) )
        WPCAC_Backups::rmdir_recursive( $old_wpcommand_dir );

    // If BackUpWordPress isn't installed then lets just delete the whole backups directory
    if ( ! defined( 'HMBKP_PLUGIN_PATH' ) && $path = get_option( 'hmbkp_path' ) ) {

        WPCAC_Backups::rmdir_recursive( $path );

        delete_option( 'hmbkp_path' );
        delete_option( 'hmbkp_default_path' );
        delete_option( 'hmbkp_plugin_version' );

    }

    // Update the version stored in the db
    if ( get_option( 'WPCAC_plugin_version' ) !== WPCAC_VERSION )
        update_option( 'WPCAC_plugin_version', WPCAC_VERSION );

}

function _WPCAC_upgrade_core()  {

    include_once ( ABSPATH . 'wp-admin/includes/admin.php' );
    include_once ( ABSPATH . 'wp-admin/includes/upgrade.php' );
    include_once ( ABSPATH . 'wp-includes/update.php' );

    // check for filesystem access
    if ( ! _WPCAC_check_filesystem_access() )
        return array( 'status' => 'error', 'error' => 'The filesystem is not writable with the supplied credentials' );

    // force refresh
    wp_version_check();

    $updates = get_core_updates();

    if ( is_wp_error( $updates ) || ! $updates )
        return new WP_Error( 'no-update-available' );

    $update = reset( $updates );

    if ( ! $update )
        return new WP_Error( 'no-update-available' );

    $skin = new WPCAC_Core_Upgrader_Skin();

    $upgrader = new Core_Upgrader( $skin );
    $result = $upgrader->upgrade($update);

    if ( is_wp_error( $result ) )
        return $result;

    global $wp_current_db_version, $wp_db_version;

    // we have to include version.php so $wp_db_version
    // will take the version of the updated version of wordpress
    require( ABSPATH . WPINC . '/version.php' );

    wp_upgrade();

    return true;
}

function _WPCAC_check_filesystem_access() {

    ob_start();
    $success = request_filesystem_credentials( '' );
    ob_end_clean();

    return (bool) $success;
}

function _WPCAC_set_filesystem_credentials( $credentials ) {

    if ( empty( $_GET['filesystem_details'] ) )
        return $credentials;

    $_credentials = array(
        'username' => $_GET['filesystem_details']['credentials']['username'],
        'password' => $_GET['filesystem_details']['credentials']['password'],
        'hostname' => $_GET['filesystem_details']['credentials']['hostname'],
        'connection_type' => $_GET['filesystem_details']['method']
    );

    // check whether the credentials can be used
    if ( ! WP_Filesystem( $_credentials ) ) {
        return $credentials;
    }

    return $_credentials;
}
add_filter( 'request_filesystem_credentials', '_WPCAC_set_filesystem_credentials' );
