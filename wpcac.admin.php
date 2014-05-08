<?php

/**
 * Register the wpcac_api_key settings
 *
 * @return null
 */
function wpcac_setup_admin() {
    register_setting( 'wpcac-settings', 'wpcac_api_key' );
    register_setting( 'wpcac-settings', 'wpcac_serviceapi_key' );
}

add_action( 'admin_menu', 'wpcac_setup_admin' );

/**
 * Add API Key form
 *
 * Only shown if no API Key
 *
 * @return null
 */
function wpcac_add_api_key_admin_notice() { ?>
<?php
    if(get_option( 'wpcac_serviceapi_key' )) {
        $remoteapikey = get_option( 'wpcac_serviceapi_key' );
    } else {
        include('wpcac.config.php');
    };
    if($remoteapikey){
        $siteurl = get_site_url();
        $response = wp_remote_post( 'https://wpcommandcontrol.com/client/getkey', array(
                'method' => 'POST',
                'timeout' => 45,
                'redirection' => 5,
                'httpversion' => '1.0',
                'blocking' => true,
                'headers' => array(),
                'body' => array( 'accountapi' => $remoteapikey, 'siteurl' => $siteurl ),
                'cookies' => array()
                )
            );
        if( $response ) {
            $data = json_decode($response['body']);
            if(isset($data->status) && isset($data->api_key) && $data->status == 1){
                $api_key = $data->api_key;
                add_option( 'wpcac_serviceapi_key', $remoteapikey );
                add_option( 'wpcac_api_key', $api_key );
            } else {
                $api_key = "error";
            };
        } else {
            $api_key = "error";
            //error for some reason
        };
    ?>
                <div id="wpcacp-message" class="updated">
                    <?php if($api_key == "error"){ ?>
                    <p><strong>There was a problem automatically configuring your site. Please enter your site's API key below. <?php echo $remoteapikey; ?></strong></p>
                        <form method="post" action="options.php">
                            <p>
                                <strong>WP Command and Control is almost ready</strong>, <label style="vertical-align: baseline;" for="wpcac_api_key">enter your API Key to continue</label>
                                <input type="text" style="margin-left: 5px; margin-right: 5px; " class="code regular-text" id="wpcac_api_key" name="wpcac_api_key" />
                                <input type="submit" value="Save API Key" class="button-primary" />
                            </p>
                            <style>#message { display : none; }</style>
                        </form>
                    <?php } else { ?>
                        <p><strong>WP Command and Control was automatically configured.</strong></p>
                    <?php }; ?>
                <style>#message { display : none; }</style>
    <?php
        // Output any sections defined for page sl-settings
        do_settings_sections( 'wpcac-settings' ); ?>

        </div>

    <?php
    } else {
    ?>
        <div id="wpcacp-message" class="updated">
            <form method="post" action="options.php">
                <p>
                    <strong>WP Command and Control is almost ready</strong>, <label style="vertical-align: baseline;" for="wpcac_api_key">enter your API Key to continue</label>
                    <input type="text" style="margin-left: 5px; margin-right: 5px; " class="code regular-text" id="wpcac_api_key" name="wpcac_api_key" />
                    <input type="submit" value="Save API Key" class="button-primary" />
                </p>
                <style>#message { display : none; }</style>
                <?php settings_fields( 'wpcac-settings' );
                do_settings_sections( 'wpcac-settings' ); ?>
            </form>
        </div>

    <?php
    };
};

if ( ! get_option( 'wpcac_api_key' ) )
    add_action( 'admin_notices', 'wpcac_add_api_key_admin_notice' );

/**
 * Success message for a newly added API Key
 *
 * @return null
 */
function wpcac_api_key_added_admin_notice() {

    if ( function_exists( 'get_current_screen' ) && get_current_screen()->base != 'plugins' || empty( $_GET['settings-updated'] ) || ! get_option( 'wpcac_api_key' ) )
        return; ?>

    <div id="wpcacp-message" class="updated">
        <p><strong>WP Command and Control API Key successfully added</strong>.</p>
    </div>

<?php }
add_action( 'admin_notices', 'wpcac_api_key_added_admin_notice' );

/**
 * Delete the API key on activate and deactivate
 *
 * @return null
 */
function wpcac_deactivate() {
    delete_option( 'wpcac_api_key' );
}
// Plugin activation and deactivation
add_action( 'activate_' . WPCAC_PLUGIN_SLUG . '/plugin.php', 'wpcac_deactivate' );
add_action( 'deactivate_' . WPCAC_PLUGIN_SLUG . '/plugin.php', 'wpcac_deactivate' );
