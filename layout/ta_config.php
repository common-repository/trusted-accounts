<?php


    function oc_trustedaccounts_layout()
    {
        wp_enqueue_script("scripts", plugins_url('../assets/js/scripts.js', __FILE__));
        wp_enqueue_style("css_styles", plugins_url('../assets/css/layout.css', __FILE__));

        isset($_GET['tab']) ? $active_tab = sanitize_text_field($_GET['tab']) : $active_tab = 'oauthclientconfig';

    ?>


        <div class="wrap">
            <h1 class="wp-heading-inline mb-2"> Setup: Trusted Accounts </h1>
        </div>

        <!--better CSS purpose like container -->
        <?php
        if ($active_tab === 'oauthclientconfig') oauthclientconfig();
    }


    function oauthclientconfig()
    {
    ?>
        <div class="ta-configuration-container">
            <div class="ta-card-row" style="min-width:55%">
                <div class="ta-card">
                    <div class="ta-card-header-container">

                        <div class="ta-card-header-content">
                            <h4 class="ta-h4">Platform credentials</h4>
                            <p class="ta-p">Create a free developer account to get your platform credentials here:
                            <a href="https://developers.trustedaccounts.org" target="_blank">Developer Console</a>.</p>
                        </div>

                        <div class="ta-card-header-image">
                            <img src="<?php echo plugin_dir_url( __FILE__ ) ?>icons/cog.svg">
                        </div>
                    </div>

                    <div class="ta-card-body-container">
                        <form id="oauthconfig" method="post" action="">

                            <input type="hidden" name="action" value="oauthconfig" />
                            <?php wp_nonce_field('OAuthConfig_nonce', 'OAuthConfig_nonce') ?>



                            <div class="ta-input-container">
                                <div class="ta-input-text-container">
                                    <small class="ta-tiny"><b>Client ID</b></small>
                                </div>
                                <input class="ta-input" type="text" id="client_id" name="client_id" style="width:100%" placeholder="e.g. e99abdff-58a3-4c37-b6f5-d4978b" value="<?php if (get_option('oc_clientid')) echo esc_attr(get_option('oc_clientid')); ?>"  required/>
                            </div>

                            <div class="ta-input-container">
                                <div class="ta-input-text-container">
                                    <small class="ta-tiny"><b>Client secret</b></small>
                                </div>
                                <input class="ta-input" type="text" id="client_secret" name="client_secret" style="width:100%" placeholder="e.g. abd4c37$b6/f497jfue34" value="<?php if (get_option('oc_clientsecret')) echo esc_attr(get_option('oc_clientsecret')); ?>"  required />
                            </div>

                            <div class="ta-input-container" style="gap: 0px;">
                                <div class="ta-input-text-container">
                                    <small class="ta-tiny"><b>Redirect URI</b></small>
                                </div>
                                <div>
                                    <div class="mb-2" style="display: flex;
                                                    flex-direction: row;
                                                    width: -webkit-fill-available;">
                                        <input class="ta-input" type="text" id="callback_url" name="callback_url" style="width:100%" placeholder='.home_url().' value="<?php echo home_url(); ?>" readonly />
                                        <button type="button" class="button gray oc-copy-to-clipboard" title="Copy to clipboard" onclick="oc_copy_to_clipboard( this )"></button>
                                    </div>
                                    <div class="ta-settings-info-box">
                                        <img src="<?php echo plugin_dir_url( __FILE__ ) ?>icons/information-circle.svg">
                                        <small class="ta-tiny">Set this as your redirect URI in the <a href="https://developers.trustedaccounts.org/" target="_blank"><b>Developers Console</b></a></small>
                                    </div>
                                </div>
                            </div>

                            <div class="ta-card-body-text">
                                <h6 class="ta-h6">Settings</h6>
                                <!--<p class="ta-p">Choose how you want Trusted Accounts to function on your platform. Here you can customize your integration.</p>-->
                            </div>

                            
                            <!--<p><b>Verify with Trusted</b></p>-->

                            <div>
                                <input id="ta_verify_registered_users" type="checkbox" name="ta_verify_registered_users" <?php if(get_option('oc_ta_verify_registered_users')){echo 'checked';} ?> />
                                <label class="ta-label" for="ta_verify_registered_users">Make verification mandatory for logged-in users</label>
                            </div>

                            <div>
                                <input id="ta_comments_only" type="checkbox" name="ta_comments_only" <?php if(get_option('oc_ta_comments_only')){echo 'checked';} ?> />
                                <label class="ta-label" for="ta_comments_only">Only allow trusted accounts to comment on posts</label>
                            </div>

                            <p class="description">
                                <b>Info:</b> You can adapt the security level in the Developer Console.
                            </p>

                            <!--
                            <div>
                                <input id="ta_show_badge" type="checkbox" name="ta_show_badge" <?php if(get_option('oc_ta_show_badge')){echo 'checked';} ?> />
                                <label class="ta-label" for="ta_show_badge">Show verification badges for verified users</label>
                            </div>

                            <div>
                                <input id="comment_registration" type="checkbox" name="comment_registration" value="1" <?php if(get_option('comment_registration')){echo 'checked';} ?> />
                                <label class="ta-label" for="comment_registration">Users must first sign in before verifying.</label>
                            </div>

                            <p><b>Login with Trusted</b></p>

                            <div>
                                <input id="ta_enable_login_with_trusted" type="checkbox" name="ta_enable_login_with_trusted" <?php if(get_option('oc_ta_enable_login_with_trusted')){echo 'checked';} ?> />
                                <label class="ta-label" for="ta_enable_login_with_trusted">Enable "Login with Trusted"</label>
                            </div>
                            
                            <div>
                                <input id="ta_user_email_required" type="checkbox" name="ta_user_email_required" <?php if(get_option('oc_ta_user_email_required')){echo 'checked';} ?> />
                                <label class="ta-label" for="ta_user_email_required">Ask users that log in with Trusted to set an email.</label>
                            </div>

                            <div>
                                <input id="ta_user_username_required" type="checkbox" name="ta_user_username_required" <?php if(get_option('oc_ta_user_username_required')){echo 'checked';} ?> />
                                <label class="ta-label" for="ta_user_username_required">Ask users that log in with Trusted to set a username.</label>
                            </div>

                            <p><b>General settings</b></p>

                            <div>
                                <input id="ta_user_navigate_home" type="checkbox" name="ta_user_navigate_home" <?php if(get_option('oc_ta_user_navigate_home')){echo 'checked';} ?> />
                                <label class="ta-label" for="ta_user_navigate_home">Navigate users to the home page after log in</label>
                            </div>
                            
                            -->

                            <div class="pt-2"></div>

                            <input type="hidden" id="app_type" name="app_type" value="OpenIdConnect" />
                            <input type="hidden" id="client_scope" name="client_scope" value="openid offline" />
                            <input type="hidden" id="client_authorization" name="client_authorization" value="https://auth.trustedaccounts.org/oauth2/auth" />
                            <input type="hidden" id="client_token_endpoint" name="client_token_endpoint" value="https://auth.trustedaccounts.org/oauth2/token" />
                            <input type="hidden" id="uname" name="uname" value="sub" />
                            <input type="hidden" id="uemail" name="uemail" value="email" />
                            <input type="hidden" name="rquest_in_header" id="rquest_in_header" value="send_with_header">
                            <input type="hidden" id="client_userinfo_endpoint" name="client_userinfo_endpoint" value="<?php if (get_option('oc_client_userinfo_endpoint')) echo esc_attr(get_option('oc_client_userinfo_endpoint')); ?>" />

                            <input class="ta_button_primary" type="submit" id="clientconfig" value="Save configuration" />

                        </form>
                    </div>
                </div>
            </div>
            <div class="ta-card-row">

                <div class="ta-card">

                    <div class="ta-card-header-container">

                        <div class="ta-card-header-content">
                            <h4 class="ta-h4">How it works</h4>
                            <p class="ta-p">Get Trusted Accounts up and running within 5 minutes.</p>
                        </div>

                        <div class="ta-card-header-image">
                            <img src="<?php echo plugin_dir_url( __FILE__ ) ?>icons/question-mark-circle.svg">
                        </div>

                    </div>

                    <div class="ta-card-body-container">

                        <div class="ta-settings-info-box" style="color: #3E3F70">
                            <h6 class="ta-h6">1.</h6>
                            <p class="ta-small">Complete the setup.</p>
                        </div>

                        <div class="ta-settings-info-box" style="color: #3E3F70">
                            <h6 class="ta-h6">2.</h6>
                            <p class="ta-small">Customize your integration in the <b>"Settings"</b> section.</p>
                        </div>

                        <div class="ta-settings-info-box" style="color: #3E3F70">
                            <h6 class="ta-h6">3.</h6>
                            <p class="ta-small">Optionally, use the shortcode <b>[trustedaccounts_button]</b> to place the verification button anywhere on your platform.</p>
                        </div>

                        <a class="pt-2" href="https://www.trustedaccounts.org/contact" target="_blank">
                            <button class="ta_button_secondary">
                                <i><img src="<?php echo plugin_dir_url( __FILE__ ) ?>icons/external-link.svg"></i>
                                Get support
                            </button>
                        </a>

                    </div>
                </div>
            </div>
        </div>

    <?php
    }