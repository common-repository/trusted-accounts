<?php
/*
* Plugin Name: Trusted Accounts: Get abusive user accounts under control
* Plugin URI: https://www.trustedaccounts.org
* Description: Stop harmful users from creating multiple accounts and protect your platform from fraud, spam and abuse.
* Version: 3.0.2
* Author: Trusted Accounts
* Text Domain: trusted-accounts
* Domain Path: /languages
*/

require_once 'ta_shortcodes.php';
require_once 'ta_authenticate.php';
require_once 'ta_post.php';
require_once 'ta_popup.php';
require_once 'layout/ta_profile.php';
require_once 'layout/ta_config.php';
require_once(ABSPATH . 'wp-admin/includes/plugin.php');

class oc_trustedaccounts_controller
{
  protected static $instance = NULL;

  public static function getInstance()
  {
    if (!self::$instance) {
      self::$instance = new self;
    }
    return self::$instance;
  }

  public static function displayTrustedLoginButton() {
    if (isset($_GET['redirect_to'])) {
        $redirect_to = $_GET['redirect_to'];
    } else {
        $redirect_to = home_url();
    }

    ?>
                    <div class="ta-verification-box" id="ta-login">
                        <a class="ta-button branded" onclick="window.location.href='<?php echo site_url('?loginaction=oauthclientlogin'); ?>';">
                           <img class="ta-button-icon" src="<?php echo plugin_dir_url( __FILE__ ) ?>assets/img/TrustedIcon.svg">
                           <div style="margin-top:2px"><?php echo __('Login with Trusted', 'trusted-accounts') ?></div>
                        </a>
                        <!--<div class="ta-info-box">
                            <img src="<?php echo plugin_dir_url( __FILE__ ) ?>assets/img/information-circle.svg">
                            <small class="ta-tiny"><?php echo __('With Trusted you can login and verify while remaining anonymous.', 'trusted-accounts') ?></small>
                        </div>-->
                        <div class="ta-login-after-box">
                            <?php echo __('OR', 'trusted-accounts') ?>
                        </div>
                    </div>

                    <script>
                        (function ( form ) {
                            form.insertBefore( document.getElementById( "ta-login" ), form.childNodes[0] )
                        })( document.getElementById( "loginform" ) );
                    </script>
    <?php
  }

    public static function displayTrustedRegisterButton() {
    ?>
                    <div class="ta-verification-box" id="ta-login">
                    test
                        <a class="ta-button" onclick="window.location.href='<?php echo site_url('?loginaction=oauthclientlogin'); ?>';">
                           <img class="ta-button-icon" src="<?php echo plugin_dir_url( __FILE__ ) ?>assets/img/TrustedIcon.svg">
                           <div style="margin-top:2px"><?php echo __('Register with Trusted', 'trusted-accounts') ?></div>
                        </a>
                        <div class="ta-info-box">
                            <img src="<?php echo plugin_dir_url( __FILE__ ) ?>assets/img/information-circle.svg">
                            <small class="ta-tiny"><?php echo __('With Trusted you can register and verify while remaining anonymous.', 'trusted-accounts') ?></small>
                        </div>
                        <div class="ta-login-after-box">
                            <?php echo __('OR', 'trusted-accounts') ?>
                        </div>
                    </div>

                    <script>
                        (function ( form ) {
                            form.insertBefore( document.getElementById( "ta-login" ), form.childNodes[0] )
                        })( document.getElementById( "registerform" ) );
                    </script>
    <?php
  }

  public static function displayTrustedButton() {
    ?>
            <div class="ta-verification-box">
                <button class="ta-button" onclick="window.location.href='<?php echo site_url('?loginaction=oauthclientlogin'); ?>';">
                   <img class="ta-button-icon" src="<?php echo plugin_dir_url( __FILE__ ) ?>assets/img/TrustedIcon.svg">
                   <div style="margin-top:2px"><?php echo __('Verify with Trusted', 'trusted-accounts') ?></div>
                </button>
                <div class="ta-info-box">
                    <img src="<?php echo plugin_dir_url( __FILE__ ) ?>assets/img/information-circle.svg">
                    <small class="ta-tiny"><?php echo __('Prove you are real and unique while remaining anonymous.', 'trusted-accounts') ?></small>
                </div>
            </div>
    <?php
  }

  public static function displayTACommentBlock() {
      ?>
            <p class="ta-p mb-2 pt-2"><?php echo __('Please prove you are human to comment on this post. It will take less than a minute.', 'trusted-accounts') ?></p>
            <button class="ta-button" onclick="window.location.href='<?php echo site_url('?loginaction=oauthclientverification'); ?>';">
              <!--<img class="ta-button-icon" src="<?php echo plugin_dir_url( __FILE__ ) ?>assets/img/badge-check.svg">-->
              <?php echo __('I am human', 'trusted-accounts') ?>
            </button>
      <?php
    }
  

  public function __construct()
  {
    //get css files for html outputs
    add_action( 'wp_loaded', array($this, 'addCSS') );

    //add actions
    add_action( 'init', array($this, 'trusted_accounts_load_textdomain'));
    add_action('admin_menu', array($this, 'addMenuPage'));
    add_action('init', array($this, 'save_trustedaccounts_config'));
    add_action('init', array($this, 'show_ta_info_bar'));
    //add_action('init', array($this, 'save_new_nicename'));
    add_action('wp_loaded', array($this, 'allow_only_ta_comments'));
    //add_action('wp_loaded', array($this, 'allow_only_ta_register'));
    add_action('wp_loaded', array($this, 'show_verification_badge'));
    add_action('login_form', array($this, 'show_ta_login_button'));
    add_action('register_form', array($this, 'show_ta_register_button'));
    //add_filter('login_redirect', array($this, 'navigateUsersToHomeAfterLogin'));
    add_filter('comment_reply_link', array($this, 'update_comment_reply_link'), 10, 3);
    add_action('wp', array($this, 'verify_all_registered_users'));

    register_uninstall_hook(__FILE__, 'deletePluginDB');
    register_activation_hook(__FILE__, array($this, 'lwliad_activate_oauth_plug'));
  }

  // Load translations
  function trusted_accounts_load_textdomain() {
      load_plugin_textdomain( 'trusted-accounts', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
  }

  function addCSS(){
      wp_enqueue_style("css_styles", plugins_url('./assets/css/layout.css', __FILE__));
  }

  function addMenuPage()
  {
    add_menu_page('trustedaccounts', 'Trusted Accounts', 'manage_options', 'User Verification with Trusted Accounts', 'oc_trustedaccounts_layout');
  }

  function lwliad_activate_oauth_plug()
  {
    wp_redirect('plugins.php');
  }

  function save_trustedaccounts_config()
  {

    if (isset($_GET['loginaction']))
      if ('oauthclientlogin' == $_GET['loginaction']) {

        if (isset($_GET['getattributes']) && $_GET['getattributes'] == true)
          setcookie("getattributes", true);
        else
          setcookie("getattributes", false);

        // Get an url without client branding
        TrustedAccountsAuthenticate::authorizationendpoint(false);
      } else if ('oauthclientverification' == $_GET['loginaction']) {
        
        // Get an url with client branding
        TrustedAccountsAuthenticate::authorizationendpoint(true);
      }


    if (isset($_GET['code'])) {
      $token_endpoint_url = get_option('oc_client_token_endpoint');
      $base_url = home_url();
     
      if (get_option('oc_apptype') != NULL && get_option('oc_apptype') == 'OpenIdConnect') {
        //OpenId flow

        $tokenIdResponse = TrustedAccountsAuthenticate::getIdToken($token_endpoint_url, $base_url, get_option('oc_clientid'),  get_option('oc_clientsecret'), $_GET['code'], 'authorization_code');

        $idToken = isset($tokenIdResponse["id_token"]) ? $tokenIdResponse["id_token"] : $tokenIdResponse["access_token"];

        if (!$idToken)
          exit('Invalid token received.');
        else
          $user_info = TrustedAccountsAuthenticate::get_user_info_from_IdToken($idToken);
      } else {

        //OAuth Flow
        $oauth_access_token = TrustedAccountsAuthenticate::call_to_token_endpoint($token_endpoint_url, $base_url,  get_option('oc_clientid'), get_option('oc_clientsecret'), $_GET['code'], 'authorization_code');

        if (isset($oauth_access_token)) {
          $userinfo_endpoint_url = get_option('oc_client_userinfo_endpoint');
          $user_info = TrustedAccountsAuthenticate::call_to_user_info_endpoint($userinfo_endpoint_url, $oauth_access_token['access_token']);
        }
      }


      if (isset($user_info) && $user_info && isset($_COOKIE['getattributes'])) {
        update_option('test_configuration', 'success');
        update_option('test_data_format', $user_info);

        unset($_COOKIE['getattributes']);
        exit;
      } else if (isset($user_info) && $user_info) {

        $user_info = self::makeNonNested($user_info);
        self::loginOrSignupUser($user_info);
      }
    }

    if (!get_option('ta_initial_setup_done')) {
        //update_option('oc_ta_show_badge', 'on');
        update_option('oc_ta_comments_only', 'on');
        update_option('oc_ta_verify_registered_users', 'on');
        //update_option('oc_ta_enable_login_with_trusted', 'on');
        //update_option('oc_ta_user_username_required', 'on');
        //update_option('oc_ta_user_navigate_home', 'on');
        update_option('ta_initial_setup_done', true);
    }

    if (isset($_POST['action'])) {
      if ($_POST['action'] == 'oauthconfig') {
        if (isset($_POST['OAuthConfig_nonce']) && !empty($_POST['OAuthConfig_nonce'])) {
          update_option('oc_selectedserver', isset($_POST['oauthservers']) ? sanitize_text_field($_POST['oauthservers']) : '');
          update_option('oc_appname', isset($_POST['app_name']) ? sanitize_text_field($_POST['app_name']) : '');
          update_option('oc_apptype', isset($_POST['app_type']) ? sanitize_text_field($_POST['app_type']) : '');
          update_option('oc_clientid', isset($_POST['client_id']) ? sanitize_text_field($_POST['client_id']) : '');
          update_option('oc_clientsecret', isset($_POST['client_secret']) ? sanitize_text_field($_POST['client_secret']) : '');
          update_option('oc_clientscope', isset($_POST['client_scope']) ? sanitize_text_field($_POST['client_scope']) : '');
          update_option('oc_client_authorization', isset($_POST['client_authorization']) ? sanitize_text_field($_POST['client_authorization']) : '');
          update_option('oc_client_token_endpoint', isset($_POST['client_token_endpoint']) ? sanitize_text_field($_POST['client_token_endpoint']) : '');
          update_option('oc_client_userinfo_endpoint', isset($_POST['client_userinfo_endpoint']) ? sanitize_text_field($_POST['client_userinfo_endpoint']) : '');
          update_option('oc_client_request_in_header', isset($_POST['rquest_in_header']) ? sanitize_text_field($_POST['rquest_in_header']) : '');
          update_option('oc_client_request_in_body', isset($_POST['rquest_in_body']) ? sanitize_text_field($_POST['rquest_in_body']) : '');
          update_option('oc_uname', isset($_POST['uname']) ? sanitize_text_field($_POST['uname']) : '');
          update_option('oc_uemail', isset($_POST['uemail']) ? sanitize_text_field($_POST['uemail']) : '');
          update_option('oc_ta_comments_only', isset($_POST['ta_comments_only']) ? sanitize_text_field($_POST['ta_comments_only']) : '');
          update_option('oc_ta_verify_registered_users', isset($_POST['ta_verify_registered_users']) ? sanitize_text_field($_POST['ta_verify_registered_users']) : '');
          update_option('oc_ta_register_only', isset($_POST['ta_register_only']) ? sanitize_text_field($_POST['ta_register_only']) : '');
          update_option('oc_ta_enable_login_with_trusted', isset($_POST['ta_enable_login_with_trusted']) ? sanitize_text_field($_POST['ta_enable_login_with_trusted']) : '');
          update_option('oc_ta_user_email_required', isset($_POST['ta_user_email_required']) ? sanitize_text_field($_POST['ta_user_email_required']) : '');
          update_option('oc_ta_user_username_required', isset($_POST['ta_user_username_required']) ? sanitize_text_field($_POST['ta_user_username_required']) : '');
          update_option('oc_ta_show_badge', isset($_POST['ta_show_badge']) ? sanitize_text_field($_POST['ta_show_badge']) : '');
          update_option('oc_ta_user_navigate_home', isset($_POST['ta_user_navigate_home']) ? sanitize_text_field($_POST['ta_user_navigate_home']) : '');
          update_option('comment_registration', isset($_POST['comment_registration']) ? sanitize_text_field($_POST['comment_registration']) : '');

          update_option('settings_saved', 'saved');
          self::success(__('Successfully saved the configuration.', 'trusted-accounts'));
        }
      } else if ($_POST['action'] == 'only_ta_post') {
          echo 'we are here';
          exit;
      }
      else if ($_POST['action'] == 'saveSettingsForm') {
        if (isset($_POST['saveSettingsForm_nonce']) && !empty($_POST['saveSettingsForm_nonce'])) {
          update_option('restrictWPUserCreation', isset($_POST['restrictWPUserCreation']) ? sanitize_text_field($_POST['restrictWPUserCreation']) : '');
          if (get_option('restrictWPUserCreation') == 'on') {
            self::success('Enabled the check to restrict WP User Creation.');
          } else {
            self::success('Disabled the check to restrict WP User Creation.');
          }
        }
      }
    }
  }

  function show_ta_info_bar() {
    /*
    if(isset($_GET['ta_msg']) && $_GET['ta_msg'] == 'successfully-verified'){
        echo '<div class="ta-success-popup-wrapper">
                <div class="info-bar">
                    <p>'. __('All done. You have successfully verified as a human.', 'trusted-accounts') .'</p>
                </div>
              </div>';

        printf('<div id="info-bar" style="background-color: #f8f9fa; padding: 10px; text-align: center;">
            <p>%1$s</p>
        </div>', esc_html($message));
    }
    */
  }


  function loginOrSignupUser($userinfo)
  {
    $user_exists = Self::checkIfTrustedAccountsExists($userinfo[get_option('oc_uname')]);
    $user = Self::getUserById($user_exists);

    //check if user is blocked
    $is_blocked = get_user_meta($user, 'blocked_ta', true);
    if ($is_blocked) {
        echo '<p class="ta-p">'. __('Your account has been blocked on this platform.', 'trusted-accounts') .'</p>
              <a href="'.home_url().'">'. __('Go back', 'trusted-accounts') .'</a>';
        exit;
    }

    // Convert values to a string
    $fingerprint_value = isset($userinfo['fingerprint_browser_fingerprint'])
      ? $userinfo['fingerprint_browser_fingerprint']
      : null;
    $fingerprint_unique = isset($userinfo['fingerprint_unique']) 
      ? ($userinfo['fingerprint_unique'] ? 'true' : 'false') 
      : null;
    $email_valid_value = isset($userinfo['email_validation_valid']) 
      ? ($userinfo['email_validation_valid'] ? 'true' : 'false') 
      : null;
    $email_disposable_valid = isset($userinfo['email_validation_validators_disposable_valid']) 
      ? ($userinfo['email_validation_validators_disposable_valid'] ? 'true' : 'false') 
      : null;
    $email_mx_valid = isset($userinfo['email_validation_validators_mx_valid']) 
      ? ($userinfo['email_validation_validators_mx_valid'] ? 'true' : 'false') 
      : null;
    $email_regex_valid = isset($userinfo['email_validation_validators_regex_valid']) 
      ? ($userinfo['email_validation_validators_regex_valid'] ? 'true' : 'false') 
      : null;
    $email_smtp_valid = isset($userinfo['email_validation_validators_smtp_valid']) 
      ? ($userinfo['email_validation_validators_smtp_valid'] ? 'true' : 'false') 
      : null;
    $email_typo_valid = isset($userinfo['email_validation_validators_typo_valid']) 
      ? ($userinfo['email_validation_validators_typo_valid'] ? 'true' : 'false') 
      : null;

    var_dump($userinfo);
    //exit;

    //verify user
    if (!$user_exists && is_user_logged_in()) {
      update_user_meta(get_current_user_id(), 'verified_ta', $userinfo[get_option('oc_uname')]);
      update_user_meta(get_current_user_id(), 'login_with_ta', true);
      update_user_meta(get_current_user_id(), 'oc_ta_username_set', true);
      update_user_meta(get_current_user_id(), 'oc_ta_email_set', true);
      update_user_meta(get_current_user_id(), 'ta_unique_browser_fingerprint', $fingerprint_unique);
      update_user_meta(get_current_user_id(), 'ta_verified', $userinfo['user_verified']);
      update_user_meta(get_current_user_id(), 'ta_verification_level', $userinfo['verification_level']);
      update_user_meta(get_current_user_id(), 'ta_phone_country_code', $userinfo['phone_country_code']);
      update_user_meta(get_current_user_id(), 'ta_email', $userinfo['email']);
      update_user_meta(get_current_user_id(), 'ta_email_validation_valid', $email_valid_value);
      update_user_meta(get_current_user_id(), 'ta_email_validation_validators_disposable_valid', $email_disposable_valid);
      update_user_meta(get_current_user_id(), 'ta_email_validation_validators_mx_valid', $email_mx_valid);
      update_user_meta(get_current_user_id(), 'ta_email_validation_validators_regex_valid', $email_regex_valid);
      update_user_meta(get_current_user_id(), 'ta_email_validation_validators_smtp_valid', $email_smtp_valid);
      update_user_meta(get_current_user_id(), 'ta_email_validation_validators_typo_valid', $email_typo_valid);
      header('Location: '.home_url('?ta_msg=successfully-verified'));
      exit;
    } else if ($user_exists && is_user_logged_in()){
      echo '<p class="ta-p">'. __('This Trusted Account has already been used to verify an account on this platform. You can only verify one account per Trusted Account.', 'trusted-accounts') .'</p>
            <a class="ta-a" href="'.home_url().'">'. __('Go back', 'trusted-accounts') .'</a>';
      exit;
    }

    //login user
    if($user_exists && get_user_meta( $user_exists, 'login_with_ta', true )) {
      Self::loginUser($user);
      update_user_meta(get_current_user_id(), 'verified_ta', $userinfo[get_option('oc_uname')]);
      header('Location: '.home_url());
      exit;
    } else if ($user_exists && !get_user_meta( $user_exists, 'login_with_ta', true )) {
      echo '<p class="ta-p">'. __('This Trusted Account is verified, but does not have the "Login with Trusted" activated. Please use another login method.', 'trusted-accounts') .'</p>
            <a class="ta-a" href="'.home_url().'">'. __('Go back', 'trusted-accounts') .'</a>';
      exit;
    }

    //register user
    if (get_option('restrictWPUserCreation') == 'on' && $user == NULL) {
      wp_redirect(site_url('wp-login.php?registration=disabled'));
      exit;
    } else {
      $user_info = array();
      $user_info['first_name'] = "Trusted";
      $user_info['last_name'] = "Account";
      $user_info['display_name'] = "Trusted-".substr($userinfo[get_option('oc_uname')],0,12);
      $user_info['user_email'] = substr($userinfo[get_option('oc_uemail')],0,60);
      $user_info['user_login'] = substr($userinfo[get_option('oc_uname')],0,60);

      if (!$user) {
        $user_info['user_pass'] =  wp_generate_password(12, false);
        $user = wp_insert_user($user_info);
      }

      Self::loginUser($user);
      update_user_meta(get_current_user_id(), 'verified_ta', $userinfo[get_option('oc_uname')]);
      update_user_meta(get_current_user_id(), 'login_with_ta', true);
      update_user_meta(get_current_user_id(), 'show_admin_bar_front', false);
    }
  }

  function loginUser($user){
    wp_set_current_user($user);
    wp_set_auth_cookie($user);
    $user  = get_user_by('ID', $user);
    do_action('wp_login', $user->user_login, $user);
  }

  function navigateUsersToHomeAfterLogin () {
    $navigate_users = get_option('oc_ta_user_navigate_home');
    if ($navigate_users) {
        return home_url();
    }
  }

  function show_verification_badge(){
    add_filter( 'get_comment_author', 'showVerificationBadge', 10, 3);
    function showVerificationBadge ($author, $comment_ID, $comment) {
      $show_badge = get_option('oc_ta_show_badge');
      $current_query_string = add_query_arg( NULL, NULL );

      /**
      * Check if
      * admin selected to show badges
      * the comment is approved and visible
      * and do not show in backend comment list
      */
      if($show_badge && $comment->comment_approved >= 1 && !str_contains($current_query_string, 'edit-comments')) {
        if(get_user_meta( $comment->user_id, 'verified_ta', true )) {
            return  '<div style="display: flex; align-items: center;" class="ta_verify_author_wrapper">'
                        .$author
                        .'<a class="ta-tooltip" href="https://www.trustedaccounts.org" target="_blank">'
                            .'<img style="margin-left: 6px;" class="ta_verify_badge_img" src="'. plugin_dir_url( __FILE__ ) . 'assets/img/TrustedBadge.svg"/>'
                            .'<span class="ta-tooltiptext">'. __("Verified with Trusted", "trusted-accounts") .'</span>'
                        .'</a>'
                   .'</div>';
        } else {
          return $author;
        }
      }
      return $author;
    }

    /**
    * Adapt Reply To title
    */
    add_filter('comment_form_defaults', 'ta_custom_comment_reply_title', 19, 1);
    function ta_custom_comment_reply_title($defaults) {
      ?>
      <script>
          var ta_comment_reply_links = document.getElementsByClassName('comment-reply-link');
          console.log(ta_comment_reply_links);
          for (var i=0; i < ta_comment_reply_links.length; i++) {
              ta_comment_reply_links[i].setAttribute('data-replyto', "<?php echo __('Reply to this comment', 'trusted-accounts') ?>");
          }
      </script>
      <?php
    }
  }


  function allow_only_ta_register() {

    add_filter('register_form', function(){
      $only_ta_register = get_option('oc_ta_register_only');
        
      if($only_ta_register) {
        ?>
          <style>
            #registerform > p {
              display: none;
            }
          </style>
          <div style="text-align: center; width: 100%;">
            <p class="ta-p">
              We verify all users to protect you form haters and trolls. The verification is anonymous.
            </p>
            <p class="ta-p">
              <a class="ta-a" href="https://www.trustedaccounts.org/for-users" target="_blank">Learn about Trusted</a>
            </p>    
          </div>
        <?php
      }
      echo oc_trustedaccounts_controller::displayTrustedButton();
    });
  }

  function verify_all_registered_users(){
    $verify_all_registered_users = get_option('oc_ta_verify_registered_users');
    $current_user = wp_get_current_user();
    $trusted_id = get_user_meta($current_user->ID, 'verified_ta', true);

    if ($verify_all_registered_users == "on" && is_user_logged_in() && !$trusted_id) {
      // Get the email of the logged-in user
      $user_email = $current_user->user_email;

      // Define the redirect URL
      $redirect_url = site_url('?loginaction=oauthclientlogin'); // Replace with your desired URL

      // Perform any additional checks if needed, then redirect
      if ($user_email) {
          // Redirect the user
          wp_redirect($redirect_url);
          exit; // Always call exit after wp_redirect to avoid further execution
      }
    }
  }


  function allow_only_ta_comments(){
      add_filter( 'comment_form_defaults', function( $fields ) {
        $only_ta_global = get_option('oc_ta_comments_only');
        $only_ta_post = get_post_meta( get_the_ID(), 'only_ta_post', true );
        
        if($only_ta_global || $only_ta_post) {
          $fields['must_log_in'] = sprintf(
              __( '' ),
              wp_registration_url(),
              wp_login_url( apply_filters( 'the_permalink', get_permalink() ) )
          );
          return $fields;
        }
      });

      add_filter( 'comment_form_before', function() {
        $only_ta_global = get_option('oc_ta_comments_only');
        $only_ta_post = get_post_meta( get_the_ID(), 'only_ta_post', true );
        
        if($only_ta_global || $only_ta_post) {
          if(!get_user_meta(get_current_user_id(), 'verified_ta')) {
            ?>
                  <style>
                      #commentform{
                        display: none;
                      }
                      #reply-title {
                        display: none;
                      }
                  </style>
                  <div style="max-width: 480px;">
                      <h2 style="margin: 0" class="mb-2">Leave a Reply</h2>

                      <?php
                        $registration_required = get_option('comment_registration');

                        if(!is_user_logged_in() && $registration_required) {
                            //WP automatically displays login link
                        } else {
                            echo oc_trustedaccounts_controller::displayTACommentBlock();
                        }
                      ?>
                  </div>
              <?php
          }
        }
      });
  }

  function update_comment_reply_link($link, $args, $comment) {

    $only_ta_global = get_option('oc_ta_comments_only');
    $only_ta_post = get_post_meta( get_the_ID(), 'only_ta_post', true );
    $registration_required = get_option('comment_registration');

    if($only_ta_global || $only_ta_post) {
        if (!get_user_meta(get_current_user_id(), 'verified_ta')) {
            if(is_user_logged_in() || !$registration_required) {
                $link = sprintf(
                    '<a rel="nofollow" class="comment-reply-login" href="%s">%s</a>',
                    esc_url( site_url('?loginaction=oauthclientverification') ),
                    __('Verify to reply', 'trusted-accounts')
                );
            }
        }
    }
    return $link;
  }

  function save_new_nicename() {
    if (isset($_POST['nicename'])) {
        global $wpdb;
        $user = $wpdb->get_var($wpdb->prepare("SELECT * FROM $wpdb->users WHERE display_name= %s", sanitize_text_field($_POST['nicename'])));

        if (!$user) {
          $user_data = wp_update_user( array( 'ID' => get_current_user_id(), 'display_name' => sanitize_text_field($_POST['nicename']) ) );
            //make sure the admin bar does not show
            update_user_meta(get_current_user_id(), 'show_admin_bar_front', false);
            //return user feedback on success or error
            if ( is_wp_error( $user_data ) ) echo '<div class="ta-error-banner-top">There was an error. Please try again.</div>';
            if ( !is_wp_error( $user_data ) ) echo '<div class="ta-success-popup-wrapper"><div class="ta-success-popup">Profile updated successfully.</div></div>';
        } else {
          echo '<div class="ta-error-banner-top">This username has already been taken. Please choose another name.</div>';
        }
    }
  }

  function getUserByUsername($username)
  {

    global $wpdb;

    $user = $wpdb->get_var($wpdb->prepare("SELECT * FROM $wpdb->users WHERE user_login= %s", $username));


    if ($user) {
      return $user;
    } else {
      return null;
    }
  }

  function getUserById($user_id)
  {

    global $wpdb;

    $user = $wpdb->get_var($wpdb->prepare("SELECT * FROM $wpdb->users WHERE ID= %s", $user_id));


    if ($user) {
      return $user;
    } else {
      return null;
    }
  }

  function checkIfTrustedAccountsExists($verification_id){
    global $wpdb;

    $user = $wpdb->get_var($wpdb->prepare("SELECT user_id FROM $wpdb->usermeta WHERE meta_key = 'verified_ta' AND meta_value = %s", $verification_id));

    if ($user) {
          return $user;
    } else {
          return null;
    }
  }


  /* Success and error messages */
  function success($message)
  {
    printf('<div class="ta-success-popup-wrapper">
                <div class="info-bar">
                    <p class="ta-small">%1$s</p>
                </div>
            </div>', esc_html($message));
  }
  function error($message)
  {
    $class = 'notice notice-error';
    printf('<div class="%1$s"><p class="ta-p">%2$s</p></div>', esc_attr($class), esc_html($message));
  }

  /**
   * Function show_ta_login_button
   * Add login button for SSO on the login form.
   */
  function show_ta_login_button()
  {
    $show_ta_login_button = get_option('oc_ta_enable_login_with_trusted');

    if($show_ta_login_button) {
        echo oc_trustedaccounts_controller::displayTrustedLoginButton();
    }
  }
  function show_ta_register_button()
    {
      $show_ta_login_button = get_option('oc_ta_enable_login_with_trusted');

      if($show_ta_login_button) {
          echo oc_trustedaccounts_controller::displayTrustedRegisterButton();
      }
    }

  function makeNonNestedRecursive(array &$out, $key, array $in)
  {
    foreach ($in as $k => $v) {
      if (is_array($v)) {
        self::makeNonNestedRecursive($out, $key . $k . '_', $v);
      } else {
        $out[$key . $k] = $v;
      }
    }
  }

  function makeNonNested(array $in)
  {
    $out = array();
    self::makeNonNestedRecursive($out, '', $in);

    return $out;
  }
}

/**
 * Add bulk action to block Trusted Accounts
 */
function register_ta_bulk_actions($bulk_actions) {
  $bulk_actions['block_users'] = __( 'Block Trusted Account', 'block_users');
  return $bulk_actions;
}
add_filter( 'bulk_actions-users', 'register_ta_bulk_actions' );

function ta_bulk_action_handler( $redirect_to, $doaction, $user_ids ) {
  if ( $doaction !== 'block_users' ) {
    return $redirect_to;
  }
  foreach ( $user_ids as $user_id ) {
    // Perform action for each user.
    update_user_meta($user_id, 'blocked_ta', true);
    $sessions = WP_Session_Tokens::get_instance($user_id);
    $sessions->destroy_all();
  }
  $redirect_to = add_query_arg( 'bulk_blocked_users', count( $user_ids ), $redirect_to );
  return $redirect_to;
}
add_filter( 'handle_bulk_actions-users', 'ta_bulk_action_handler', 10, 3 );

function ta_bulk_action_admin_notice() {
  if ( ! empty( $_REQUEST['bulk_blocked_users'] ) ) {
    $blocked_count = intval( $_REQUEST['bulk_blocked_users'] );
    printf( '<div id="message" class="updated fade">' .
      _n( '%s Users blocked successfully.',
        '%s Users blocked successfully.',
        $blocked_count,
        'block_users'
      ) . '</div>', $blocked_count );
  }
}
add_action( 'admin_notices', 'ta_bulk_action_admin_notice' );

/**
 * Add bulk action to unblock Trusted Accounts
 */
function register_ta_unlock_bulk_actions($bulk_actions) {
  $bulk_actions['unlock_users'] = __( 'Unlock Trusted Accounts', 'unlock_users');
  return $bulk_actions;
}
add_filter( 'bulk_actions-users', 'register_ta_unlock_bulk_actions' );

function ta_unlock_bulk_action_handler( $redirect_to, $doaction, $user_ids ) {
  if ( $doaction !== 'unlock_users' ) {
    return $redirect_to;
  }
  foreach ( $user_ids as $user_id ) {
    // Perform action for each user.
    update_user_meta($user_id, 'blocked_ta', false);
  }
  $redirect_to = add_query_arg( 'bulk_unlocked_users', count( $user_ids ), $redirect_to );
  return $redirect_to;
}
add_filter( 'handle_bulk_actions-users', 'ta_unlock_bulk_action_handler', 10, 3 );

function ta_unlock_bulk_action_admin_notice() {
  if ( ! empty( $_REQUEST['bulk_unlocked_users'] ) ) {
    $unlocked_count = intval( $_REQUEST['bulk_unlocked_users'] );
    printf( '<div id="message" class="updated fade">' .
      _n( '%s Users unlocked successfully.',
        '%s Users unlocked successfully.',
        $unlocked_count,
        'unlock_users'
      ) . '</div>', $unlocked_count );
  }
}
add_action( 'admin_notices', 'ta_unlock_bulk_action_admin_notice' );

/**
 * Add custom columns to the users list
 */
function custom_add_user_columns($columns) {
    $columns['browser_fingerprint'] = 'Fingerprint';
    $columns['user_blocked'] = 'Blocked';
    $columns['user_verified'] = 'Verified';
    return $columns;
}
add_filter('manage_users_columns', 'custom_add_user_columns');

/**
 * Show custom column content in users list
 */
function custom_show_user_column_content($value, $column_name, $user_id) {
    switch ($column_name) {
        case 'browser_fingerprint':
          $fingerprint = get_user_meta($user_id, 'ta_unique_browser_fingerprint', true);
          
          // Show 'Fingerprint is not unique' with a specific style
          if ($fingerprint === "false") {
              return '<span style="background-color: #ff922e15; color: #FF922E;">Fingerprint is not unique</span>';
          } 
          // Show 'Fingerprint is unique' with a different style
          else if ($fingerprint === "true") {
              return '<span style="background-color: #28cc2515; color: #28CC25;">Fingerprint is unique</span>';
          }
          
          // Otherwise, return the actual value
          return '<span style="background-color: #3E3F7015; color: #3E3F70;">' . ($fingerprint ?: 'No fingerprint available') . '</span>';

          case 'user_blocked':
            $blocked = get_user_meta($user_id, 'blocked_ta', true);
            return '<span style="background-color: ' . ($blocked ? '#ff922e15' : '') . '; color: ' . ($blocked ? '#FF922E' : '') . ';">' . ($blocked ? 'Blocked' : '-') . '</span>';
        
        case 'user_verified':
            $verified = get_user_meta($user_id, 'ta_verified', true);
            return '<span style="background-color: ' . ($verified ? '#28cc2515' : '#3E3F7015') . '; color: ' . ($verified ? '#28CC25' : '#3E3F70') . ';">' . ($verified ? 'Verified' : 'Not verified') . '</span>';

        default:
            return $value;
    }
}
add_filter('manage_users_custom_column', 'custom_show_user_column_content', 10, 3);

/**
 * Prevent verified but blocked WP-Users from logging in
 */
function handle_blocked_wp_user_login( $user_login, $user ) {
    global $wpdb;
    $user_id = $wpdb->get_var($wpdb->prepare("SELECT * FROM $wpdb->users WHERE user_login= %s", $user_login));

    if(get_user_meta($user_id, 'blocked_ta', true )){
      $sessions = WP_Session_Tokens::get_instance($user_id);
      $sessions->destroy_all();
      echo '<p class="ta-p">Your account has been blocked on this platform.</p>
              <a href="'.home_url().'">Go back</a>';
      exit;
    }    
}
add_action('wp_login', 'handle_blocked_wp_user_login', 10, 2);


$OAuth_Client = oc_trustedaccounts_controller::getInstance();