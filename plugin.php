<?php

/*
Plugin Name: WP Command and Control
Description: Manage your WordPress site with <a href="https://wpcommandcontrol.com/">WP Command and Control</a>. <strong>Deactivate to clear your API Key.</strong>
Version: 2.2.0
Author: SoJu Studios
Author URI: http://supersoju.com/
 */

/*  Copyright 2014 Soju LLC  (email : support@supersoju.com)

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
if ( empty( $_POST['action'] ) || $_POST['action'] != 'do-core-upgrade' ) :

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
 * Admin Panel
 */

if ( ! class_exists( 'AdminPageFramework' ) ) {
    include_once( dirname( __FILE__ ) . '/library/admin-page-framework.min.php' );
};

class WPCAC_CreatePageGroup extends AdminPageFramework {
    // Define the setUp() method to set how many pages, page titles and icons etc.
    public function setUp() {
        // Creates the root menu
        $this->setRootMenuPage(
            'WPCmdCtrl',    // specify the name of the page group
            get_bloginfo('url') . '/wp-content/plugins/wpcommand/images/16x16.png'    // use 16 by 16 image for the menu icon.
        );
        // Adds the sub menus and the pages
        $this->addSubMenuItems(
            array(
                'title' => 'Site Status',        // page title
                'page_slug' => 'wpcac_status',    // page slug
            ),
            array(
                'title' => 'API Keys',        // page title
                'page_slug' => 'wpcac_api_keys',    // page slug
            )
            
        );
    }
    // Notice that the name of the method is 'do_' + the page slug.
    // So the slug should not contain characters which cannot be used in function names such as dots and hyphens.
    public function do_wpcac_status() {
        $api_url = "https://wpcommandcontrol.com";
        ?>
        <div class="row">
            <div class="col-md-12">
                <h2>Site Status</h2>
                <div id="wpcac-service-messages"></div>
            </div>
            <div class="col-md-7 wpcac-results">
                <h3>Malware Check <a href="#" id="btn-scannow" class="btn btn-primary">Scan Now</a></h3>
                <div id="wpcac-malware-messages"></div>
                <div id="wpcac-malware">
                    <img src="<?php echo get_bloginfo('url'); ?>/wp-content/plugins/wpcommand/images/spinner.gif" />
                </div>
                <h3>Backups</h3>
                <div id="wpcac-backups">
                    <img src="<?php echo get_bloginfo('url'); ?>/wp-content/plugins/wpcommand/images/spinner.gif" />
                </div>
                <h3>Site Response</h3>
                <div id="wpcac-pingtime">
                <p class="avg"><i class="glyphicon"></i> Average Pingtime over last 24 hours: <span><img src="<?php echo get_bloginfo('url'); ?>/wp-content/plugins/wpcommand/images/spinner.gif" /></span> seconds</p>
                <p class="shortest"><i class="glyphicon"></i> Best: <span><img src="<?php echo get_bloginfo('url'); ?>/wp-content/plugins/wpcommand/images/spinner.gif" /></span> seconds</p>
                <p class="longest"><i class="glyphicon"></i> Worst: <span><img src="<?php echo get_bloginfo('url'); ?>/wp-content/plugins/wpcommand/images/spinner.gif" /></span> seconds</p>
                </div>
            </div>
            <div id="wpcac-sucuri" class="col-md-4">
                <div class="well">
                    <h3><a href="http://wpcommandcontrol.com/client/sucuri" target="_blank">Preventive Website Security in the cloud from Sucuri!</a></h3>
                    <ul>
                        <li>Web Application Firewall (WAF) Protection</li>
                        <li>Virtual Website Patching</li>
                        <li>Cloud Intrusion Prevention System (IPS)</li>
                        <li>High Security Website Monitoring</li>
                    <li>Malicious Traffic Filtering</li>
                    </ul>
                    <p><a href="http://wpcommandcontrol.com/client/sucuri" class="btn btn-primary" target="_blank">Sign up now</a> <a href="http://wpcommandcontrol.com/client/sucuri" class="btn btn-primary" target="_blank">Read more</a></p>
                    <iframe width="100%" height="315" src="//www.youtube-nocookie.com/embed/QV3OfHmEq5c" frameborder="0" allowfullscreen></iframe>

                </div>
            </div>
        </div>
<link rel="stylesheet" href="<?php echo get_bloginfo('url'); ?>/wp-content/plugins/wpcommand/css/bootstrap.css">
        <script type="text/javascript">
        jQuery(function() {
            jQuery("#btn-scannow").click( function() {
                jQuery(this).fadeOut();
                jQuery.post( "<?php echo $api_url; ?>/client/api/json?callback=", { method: "scan", site_url: "<?php echo get_bloginfo('url'); ?>", api_key: "<?php echo get_option( 'wpcac_api_key' ); ?>" });
                jQuery("<p class='bg-info' style='padding: 0.5em'>Security scan scheduled, please check back in a few minutes.</p>").appendTo("#wpcac-malware-messages");
            });
            <?php
        $blog_url = get_bloginfo('url');
        $api_key = get_option('wpcac_api_key');
            ?>
            jQuery.post( "<?php echo $api_url; ?>/client/api/json?callback=", { method: "site_info", site_url: "<?php echo $blog_url; ?>", api_key: "<?php echo $api_key; ?>" }, function(data) {
                if(data.status == "success"){
                    jQuery('#wpcac-pingtime .avg span').text(data.pingtime.avg);
                    jQuery('#wpcac-pingtime .shortest span').text(data.pingtime.shortest);
                    jQuery('#wpcac-pingtime .longest span').text(data.pingtime.longest);
                    if(data.last_backup){
                        jQuery('#wpcac-backups').html("<i class='glyphicon glyphicon-ok text-success'></i> Last Backup on: " + data.last_backup + " (GMT)");
                    } else {
                        jQuery('#wpcac-backups').html("<i class='glyphicon glyphicon-flash text-warning'></i> No backups yet.");
                    };

                    if(data.pingtime.avg < 2){
                        jQuery('#wpcac-pingtime .avg span').addClass('text-success');
                        jQuery('#wpcac-pingtime .avg i').addClass('glyphicon-ok text-success');
                    } else {
                        if(data.pingtime.avg > 2 && data.pingtime.avg < 5){
                            jQuery('#wpcac-pingtime .avg span').addClass('text-warning');
                            jQuery('#wpcac-pingtime .avg i').addClass('glyphicon-flash text-warning');
                        } else {
                            jQuery('#wpcac-pingtime .avg span').addClass('text-danger');
                            jQuery('#wpcac-pingtime .avg i').addClass('glyphicon-remove text-danger');
                        };
                    };
                    if(data.pingtime.shortest < 2){
                        jQuery('#wpcac-pingtime .shortest span').addClass('text-success');
                        jQuery('#wpcac-pingtime .shortest i').addClass('glyphicon-ok text-success');
                    } else {
                        if(data.pingtime.shortest > 2 && data.pingtime.shortest < 5){
                            jQuery('#wpcac-pingtime .shortest span').addClass('text-warning');
                            jQuery('#wpcac-pingtime .shortest i').addClass('glyphicon-flash text-warning');
                        } else {
                            jQuery('#wpcac-pingtime .shortest span').addClass('text-danger');
                            jQuery('#wpcac-pingtime .shortest i').addClass('glyphicon-remove text-danger');
                        };
                    };
                    if(data.pingtime.longest < 2){
                        jQuery('#wpcac-pingtime .longest span').addClass('text-success');
                        jQuery('#wpcac-pingtime .longest i').addClass('glyphicon-ok text-success');
                    } else {
                        if(data.pingtime.longest > 2 && data.pingtime.longest < 4){
                            jQuery('#wpcac-pingtime .longest span').addClass('text-warning');
                            jQuery('#wpcac-pingtime .longest i').addClass('glyphicon-flash text-warning');
                        } else {
                            jQuery('#wpcac-pingtime .longest span').addClass('text-danger');
                            jQuery('#wpcac-pingtime .longest i').addClass('glyphicon-remove text-danger');
                        };
                    };
    
                    var malwaretext = "";
                    malwaretext = malwaretext + "<p>Last scan: " + data.scandate + "</p>";
                    if(data.rawscan.MALWARE && data.rawscan.MALWARE.WARN){
                        malwaretext = malwaretext + "<p><i class='glyphicon glyphicon-remove text-danger'></i> <strong>Possible Malware: </strong></p>";
                        jQuery.each(data.rawscan.MALWARE.WARN, function(i, warning){
                            malwaretext = malwaretext + "<li><i class='glyphicon glyphicon-remove text-danger'></i> <strong>" + warning + "</strong></li>";
                        });
                    } else {
                        malwaretext = malwaretext + "<p><i class='glyphicon glyphicon-ok text-success'></i> <strong>No Malware Detected</strong></p>";
                    };
                    if(data.rawscan.BLACKLIST && data.rawscan.BLACKLIST.WARN){
                        malwaretext = malwaretext + "<p><i class='glyphicon glyphicon-remove text-danger'></i> <strong>Site is on the following blacklists:</strong></p>";
                    } else {
                        malwaretext = malwaretext + "<p><i class='glyphicon glyphicon-ok text-success'></i> <strong>Site is not blacklisted</strong></p>";
                        jQuery.each(data.rawscan.BLACKLIST.INFO, function(i, warning){
                            malwaretext = malwaretext + "<li><i class='glyphicon glyphicon-ok text-success'></i> <strong>" + warning + "</strong></li>";
                        });
                    };
                    jQuery('#wpcac-malware').html(malwaretext);
                } else {
                    jQuery('#wpcac-service-messages').html("<p class='bg-warning' style='padding: 0.5em'>Error contacting WPCmdCtrl Service.</p>");
                    jQuery('.wpcac-results').hide();
                };
                }, "json");
            });
        </script>
        <?php
    }

    public function do_wpcac_api_keys() {
        $api_key = get_option( 'wpcac_api_key' );
        $remoteapikey = get_option( 'wpcac_serviceapi_key' );
?>
        <h3>API Keys</h3>
<?php
        if($api_key){
        ?>
        <p>Site API Key active!</p>

<?php
            if($remoteapikey){
?>
            <p>Account API Key active!</p>
    <?php
            };
        } else {
?>
                        <form method="post" action="options.php">
                            <p>
                                <strong>WP Command and Control is almost ready</strong>, <label style="vertical-align: baseline;" for="wpcac_api_key">enter your API Key to continue</label></p>
                                <p><input type="text" class="code regular-text" id="wpcac_api_key" name="wpcac_api_key" />
                                <input type="submit" value="Save API Key" class="button-primary" />
                            </p>
                            <style>#message { display : none; }</style>
                        </form>
        <?php
        };
    }
    
}

// Instantiate the class object.
if ( is_admin() ) {
    new WPCAC_CreatePageGroup;
};

/**
 * Catch the API calls and load the API
 *
 * @return null
 */
function WPCAC_catch_api_call() {

    if ( empty( $_POST['wpcac_api_key'] ) || ! urldecode( $_POST['wpcac_api_key'] ) || ! isset( $_POST['actions'] ) )
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
 * Flush rewrite rules
 *
 * @access public
 * @return void
 */
function _wpcac_flush_rewrite_rules() {

    global $wp_rewrite;
    return $wp_rewrite->flush_rules(true);
    return "success";
}

/**
 * Set WP Option Value
 *
 * @access public
 * @return void
 */
function _wpcac_set_option( $option_name, $option_value ) {

    update_option( $option_name, $option_value );
    return "success";
}


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

function _WPCAC_generate_hashes($vars) {
    $api_key = get_option( 'wpcac_api_key' );
    if ( ! $api_key ){
        return array();
    };

    $hashes = array();
    $hashes[] = hash_hmac( 'sha256', serialize( $vars ), $api_key );
    return $hashes;
}

function _WPCAC_check_filesystem_access() {

    ob_start();
    $success = request_filesystem_credentials( '' );
    ob_end_clean();

    return (bool) $success;
}

function _WPCAC_set_filesystem_credentials( $credentials ) {

    if ( empty( $_POST['filesystem_details'] ) )
        return $credentials;

    $_credentials = array(
        'username' => $_POST['filesystem_details']['credentials']['username'],
        'password' => $_POST['filesystem_details']['credentials']['password'],
        'hostname' => $_POST['filesystem_details']['credentials']['hostname'],
        'connection_type' => $_POST['filesystem_details']['method']
    );

    // check whether the credentials can be used
    if ( ! WP_Filesystem( $_credentials ) ) {
        return $credentials;
    }

    return $_credentials;
}
add_filter( 'request_filesystem_credentials', '_WPCAC_set_filesystem_credentials' );
