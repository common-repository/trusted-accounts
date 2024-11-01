<?php

/**
 * Register Short Code Login Button
 *
 */
add_shortcode( 'trustedaccounts_button', 'trustedaccounts_button_handler' );
function trustedaccounts_button_handler() {
  $current_user = wp_get_current_user();
  $trusted_id = get_user_meta($current_user->ID, 'verified_ta', true);

	if (!$trusted_id){
    ?>
      <button class="ta-button" onclick="window.location.href='<?php echo site_url('?loginaction=oauthclientverification'); ?>';">
        <?php echo __('I am human', 'trusted-accounts') ?>
      </button>
    <?php
  }
}

/**
 * Register Short Code Change Visible Name
 *
 */
add_shortcode( 'trustedaccounts_change_name', 'trustedaccounts_change_name_handler' );
function trustedaccounts_change_name_handler() {
	if (is_user_logged_in() && get_user_meta( get_current_user_id(), 'verified_ta', true )){
    echo '<div>
            <form id="newNicename" method="post" action="">
                    <input type="text" name="nicename" placeholder="Choose a new username"/>
                    <br/>
                        <button style="background: #2E31FF;
              color: white;
              border-radius: 4px;
              font-family: Epilogue,sans-serif;
              font-style: normal;
              font-weight: 700;
              font-size: 16px;
              height: 50px;
              text-transform: none;
              text-decoration: none;
              border: none;
              min-width: 230px;
              line-height: 16px;">
            <img src="'.plugin_dir_url( __FILE__ ) . "assets/img/TrustedIcon.svg".'"
              style="float: left;
                height: 24px;
                margin-right: 13px;
                margin-top: -6px;">
            <span style="padding-top:5px">Set username</span>		                      
            </form>
          </div>';
    }
}

?>