<?php

/**
 * Show popup for user on registration to set an email and a username
 */

//add_action('wp_loaded', 'show_ta_user_setup');
//add_action('init', 'save_ta_user_setup');

function show_ta_user_setup(){
    //only show for registered Trusted Accounts
    if(is_user_logged_in() && get_user_meta( get_current_user_id(), 'verified_ta', true )) {
        //setup popup fields
        $ask_for_display_name = false;
        $ask_for_email = false;
        $is_ta = get_user_meta(get_current_user_id(), 'login_with_ta', true);

        //check if we want to display username field
        $username_required = get_option('oc_ta_user_username_required');
        if($username_required && $is_ta == true && !get_user_meta(get_current_user_id(), 'oc_ta_username_set', true )){
            $ask_for_display_name = true;
        }
        
        //check if we want to display email field
        $email_required = get_option('oc_ta_user_email_required');
        if ($email_required && $is_ta == true && !get_user_meta(get_current_user_id(), 'oc_ta_email_set', true )) {
            $ask_for_email = true;
        }
        
        //display popup if one field is required and if it is not an admin
        if(($ask_for_display_name || $ask_for_email) && !current_user_can( 'manage_options')) {
            if(isset($_GET['ta_msg'])){
                displayUserDetailsPopup($email_required, $username_required, $_GET['ta_msg']);
            } else {
                displayUserDetailsPopup($email_required, $username_required);
            }
        }
    }
}

function save_ta_user_setup(){
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'user_setup') {
            if (isset($_POST['user_setup_nonce']) && !empty($_POST['user_setup_nonce'])) {
                global $wpdb;

                //check if user canceled
                if (isset($_POST['submit']) && $_POST['submit'] == "Cancel") {
                    //don't show popup again
                    update_user_meta(get_current_user_id(), 'oc_ta_username_set', true);
                    update_user_meta(get_current_user_id(), 'oc_ta_email_set', true);
                    success(__('Settings successfully saved. You are all set.', 'trusted-accounts'));
                    exit;
                }

                //check user_email
                if (isset($_POST['user_email'])) {
                    $email = $wpdb->get_var($wpdb->prepare("SELECT * FROM $wpdb->users WHERE user_email= %s", sanitize_text_field($_POST['user_email'])));
                    if ($email) {
                        wp_redirect(site_url('?ta_msg=email_exists'));
                        exit;
                    }
                } else {
                    //check if email is required
                    $email_required = get_option('oc_ta_user_email_required');
                    if($email_required) {
                        wp_redirect(site_url('?ta_msg=email_required'));
                        exit;
                    }
                }

                //check display_name
                if (isset($_POST['display_name']) && $_POST['display_name'] != "") {
                    $display_name = $wpdb->get_var($wpdb->prepare("SELECT * FROM $wpdb->users WHERE display_name= %s", sanitize_text_field($_POST['display_name'])));
                    if ($display_name) {
                        wp_redirect(site_url('?ta_msg=name_exists'));
                        exit;
                    }
                }

                //don't show popup again
                update_user_meta(get_current_user_id(), 'oc_ta_username_set', true);
                update_user_meta(get_current_user_id(), 'oc_ta_email_set', true);
                
                //save user data
                if(isset($_POST['user_email']) && $_POST['user_email'] != ""){
                    $email_response = wp_update_user( array( 'ID' => get_current_user_id(), 'user_email' => sanitize_text_field($_POST['user_email']) ) );
                    if ( is_wp_error( $email_response ) ) {
                        wp_redirect(site_url('?ta_msg=email_exists'));
                        exit;
                    }
                }

                if(isset($_POST['display_name']) && $_POST['display_name'] != ""){
                    $display_name_response = wp_update_user( array( 'ID' => get_current_user_id(), 'display_name' => sanitize_text_field($_POST['display_name']) ) );
                    if (is_wp_error( $display_name_response )) {
                        wp_redirect(site_url('?ta_msg=name_exists'));
                        exit;
                    }
                }

                success(__('Settings successfully saved. You are all set.', 'trusted-accounts'));
            }
        }
    }
}

function success($message) {
    printf('<div class="ta-success-popup-wrapper">
                <div class="info-bar">
                    <p class="ta-small">%1$s</p>
                </div>
            </div>', esc_html($message));
}

function displayUserDetailsPopup($email_required = NULL, $display_name_required = NULL, $message = NULL) {
    //translate error_msg
    if ($message == "name_exists") $message = __('This username already exists on this platform.', 'trusted-accounts');
    if ($message == "email_exists") $message = __('This email already exists on this platform.', 'trusted-accounts');
    if ($message == "email_required") $message = __('Please provide an email address.', 'trusted-accounts');
    if ($message == "display_name_required") $message = __('Please provide a username.', 'trusted-accounts');
    if ($message == "exit") $message = __('This platform requires at least an email address.', 'trusted-accounts');


    ?>
            <div class="ta-user-setup-popup-container">
                <div class="ta-user-setup-wrapper">
                    <div class="ta-user-setup-wrapper-inner">
                        <div class="ta-user-setup-header-wrapper">
                            <img src="<?php echo plugin_dir_url( __FILE__ ) ?>assets/img/user-circle-blue-big.svg">
                            <div class="ta-user-setup-header-text-wrapper">
                                <h3 class="ta-h3 ta-text-blue"><?php echo __('Setup profile', 'trusted-accounts') ?></h3>
                                <p class="ta-p ta-text-gray-80"><?php echo __('Choose what information you want to share with this platform or just skip.', 'trusted-accounts') ?></p>
                            </div>
                        </div>
                        <div class="ta-user-setup-info-box">
                            <img src="<?php echo plugin_dir_url( __FILE__ ) ?>assets/img/badge-filled-blue.svg">
                            <div class="ta-user-setup-info-box-text-wrapper">
                                <small class="ta-small"><b><?php echo __('Verified & anonymous', 'trusted-accounts') ?></b></small>
                                <small class="ta-small"><?php echo __('You have successfully registered and verified your anonymous account.', 'trusted-accounts') ?></small>
                            </div>
                        </div>
                        <form class="ta-user-setup-form" id="user_setup" method="post" action="">
                            <input type="hidden" name="action" value="user_setup" />
                            <?php wp_nonce_field('user_setup_nonce', 'user_setup_nonce') ?>
                            <?php if($email_required) { echo '<input class="ta-user-setup-input" type="email" name="user_email" placeholder="'. __('Email', 'trusted-accounts') .'" />'; } ?>
                            <?php if($display_name_required) { echo '<input class="ta-user-setup-input" type="text" name="display_name" placeholder="'. __('Username', 'trusted-accounts').'" />'; } ?>
                            <?php if($message) echo '<div class="ta-small ta-error-box">'.$message.'</div>' ?>
                            <input class="ta_button_primary ta-button-block" name="submit" type="submit" id="submit_user_setup" value="<?php echo __('Save', 'trusted-accounts') ?>" />
                            <input class="ta-button-link" name="submit" type="submit" id="cancel_user_setup" value="<?php echo __('Skip', 'trusted-accounts') ?>" />
                        </form>
                    </div>
                </div>
            </div>
    <?php    
}

?>