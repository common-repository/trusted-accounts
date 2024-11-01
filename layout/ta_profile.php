<?php

add_action('show_user_profile', 'showUpdateVerificationFields'); // editing your own profile
add_action('personal_options_update', 'saveUpdateVerificationFields'); // only allow admins editing own profile

add_action('edit_user_profile', 'showUpdateVerificationFields');
add_action('edit_user_profile_update', 'saveUpdateVerificationFields');

function showUpdateVerificationFields( $user ) {
	$verified_ta = get_the_author_meta( 'verified_ta', $user->ID );
	$user_level = get_the_author_meta( 'wp_user_level', $user->ID );

	$fingerprint = get_user_meta($user->ID, 'ta_unique_browser_fingerprint', true);
    $blocked = get_user_meta($user->ID, 'blocked_ta', true);
    $verified = get_user_meta($user->ID, 'ta_verified', true);
    $verification_level = get_user_meta($user->ID, 'ta_verification_level', true);
    $phone_country_code = get_user_meta($user->ID, 'ta_phone_country_code', true);
    $email = get_user_meta($user->ID, 'ta_email', true);
    $email_validation_valid = get_user_meta($user->ID, 'ta_email_validation_valid', true);
    $email_validation_validators_disposable_valid = get_user_meta($user->ID, 'ta_email_validation_validators_disposable_valid', true);
    $email_validation_validators_mx_valid = get_user_meta($user->ID, 'ta_email_validation_validators_mx_valid', true);
    $email_validation_validators_regex_valid = get_user_meta($user->ID, 'ta_email_validation_validators_regex_valid', true);
    $email_validation_validators_smtp_valid = get_user_meta($user->ID, 'ta_email_validation_validators_smtp_valid', true);
    $email_validation_validators_typo_valid = get_user_meta($user->ID, 'ta_email_validation_validators_typo_valid', true);

	?>
	<div style="padding-top: 20px"></div>

	<h3><?php _e('User Verification Details', 'trusted-accounts'); ?></h3>
    <table class="form-table">
		<tr>
            <th><label for="email"><?php _e('Validated email', 'trusted-accounts'); ?></label></th>
            <td>
                <?php 
                echo '<span style="background-color: #F0F0F0; color: #333;">' . ($email ?: 'Not available') . '</span>';
                echo '<p class="description">The user used this email to verify via Trusted Accounts.</p>';
				?>
            </td>
        </tr>
        <tr>
            <th><label for="user_verified"><?php _e('Verified', 'trusted-accounts'); ?></label></th>
            <td>
                <?php 
                echo '<span style="background-color: ' . ($verified ? '#28cc2515' : '#ff922e15') . '; color: ' . ($verified ? '#28CC25' : '#FF922E') . ';">' . ($verified ? 'Verified' : 'Not verified') . '</span>';
                ?>
            </td>
        </tr>
		<tr>
            <th><label for="verification_level"><?php _e('Verification Level', 'trusted-accounts'); ?></label></th>
            <td>
                <?php 
				if ($verification_level == 1) {
					echo '<span style="background-color: ' . ($verification_level ? '#28cc2515' : '#F0F0F0') . '; color: ' . ($verification_level ? '#28CC25' : '#333') . ';">' . ($verification_level ? 'Level ' . $verification_level . ' (unique & verified phone number)' : 'Not available') . '</span>';
				} else {
					echo 'Not available';
				}
                ?>
            </td>
        </tr>
        <tr>
            <th><label for="phone_country_code"><?php _e('Phone Country Code', 'trusted-accounts'); ?></label></th>
            <td>
				<?php 
				if ($phone_country_code) {
                	echo '+';
				}
				echo '<span style="background-color: #F0F0F0; color: #333;">' . ($phone_country_code ?: 'Not available') . '</span>';
				?>
            </td>
        </tr>
		<tr>
            <th><label for="browser_fingerprint"><?php _e('Unique Browser Fingerprint', 'trusted-accounts'); ?></label></th>
            <td>
                <?php 
                if ($fingerprint === "false") {
                    echo '<span style="background-color: #ff922e15; color: #FF922E;">Fingerprint is not unique</span>';
                } elseif ($fingerprint === "true") {
                    echo '<span style="background-color: #28cc2515; color: #28CC25;">Fingerprint is unique</span>';
                } else {
                    echo '<span style="background-color: #3E3F7015; color: #3E3F70;">' . ($fingerprint ?: 'No fingerprint available') . '</span>';
                }
				echo '<p class="description">Tells you if no other account with this browser fingerprint exists on your platform</p>';
                ?>
            </td>
        </tr>
		<tr>
			<th><label for="email_valid"><?php _e('Email valid', 'trusted-accounts'); ?></label></th>
			<td>
				<?php 
				if ($email_validation_valid === 'true') {
					echo '<span style="background-color: #28cc2515; color: #28CC25;">True</span>';
				} elseif ($email_validation_valid === 'false') {
					echo '<span style="background-color: #ff922e15; color: #FF922E;">False</span>';
				} else {
					echo '<span style="background-color: #F0F0F0; color: #333;">Not available</span>';
				}
				echo '<p class="description">Email passed all the individual deep email validation checks</p>';
				?>
			</td>
		</tr>
		<tr>
            <th><label for=""><?php _e('More user details', 'trusted-accounts'); ?></label></th>
            <td>
				<a target="_blank" href="https://developers.trustedaccounts.org/user/details/<?php echo esc_attr($verified_ta); ?>" class="button"><?php _e('Show user details', 'trusted-accounts'); ?></a>
				<p class="description">See all the validation details for this user to get a complete picture.</p>
			</td>
        </tr>
		<tr>
	        <?php
                    if($verified_ta && $user_level == "10") {
            	        ?>
                            <th><label for="remove_verification"><?php _e('Remove verification', 'trusted-accounts'); ?></label></th>
                            <td>
							<button class="button" name="subject" type="submit" value="remove-verification"><?php echo __('Remove verification', 'trusted-accounts') ?></button>
                                <p class="description"><?php echo __('When you remove the verification this user will be able to verify another account with the same Trusted Account or phone number.', 'trusted-accounts') ?></p>
            	            </td>
            	        <?php
            	    }
	        ?>
	    </tr>
	</table>

	<!--
	<h2><?php echo __('Account Verification (by Trusted Accounts)', 'trusted-accounts') ?></h2>
	<table class="form-table ta-form-table">
	    
	<?php
	if ($verified_ta) {
	    ?>
        <tr class="enable-login-with-ta user-login-with-ta-wrap">
		    <th scope="row"><?php echo __('Login with Trusted', 'trusted-accounts') ?></th>
			<td>
			    <label for="login_with_ta">
			    <input id="login_with_ta" name="login_with_ta" type="checkbox" value="1" <?php if ( get_the_author_meta( 'login_with_ta', $user->ID ) == 1  ) echo ' checked="checked"'; ?> />
			        <?php echo __('Enable "Login with Trusted" for this account', 'trusted-accounts') ?>
			    </label>
			</td>
		</tr>
		<?php
	}
	?>
	-->
	</table>
	<?php
}

function saveUpdateVerificationFields($user_id) {
    if ( !current_user_can( 'edit_user', $user_id ) ) { return false; } else {

        //update "Login with Trusted"
        if(isset($_POST['login_with_ta']) && $_POST['login_with_ta'] > 0){
            update_usermeta( $user_id, 'login_with_ta', $_POST['login_with_ta'] );
        }else{
            delete_usermeta($user_id, 'login_with_ta');
        }

        //remove verification
        $verified_ta = get_the_author_meta( 'verified_ta', $user_id );
        $user_level = get_the_author_meta( 'wp_user_level', $user_id );
        if ($user_level >= 9) {
            if ($_POST['subject'] == 'remove-verification') {
                delete_user_meta($user_id, 'verified_ta');
				delete_user_meta($user_id, 'ta_unique_browser_fingerprint');
				delete_user_meta($user_id, 'ta_verified');
				delete_user_meta($user_id, 'ta_verification_level');
				delete_user_meta($user_id, 'ta_phone_country_code');
				delete_user_meta($user_id, 'ta_email');
				delete_user_meta($user_id, 'ta_email_validation_valid');
				delete_user_meta($user_id, 'ta_email_validation_validators_disposable_valid');
				delete_user_meta($user_id, 'ta_email_validation_validators_mx_valid');
				delete_user_meta($user_id, 'ta_email_validation_validators_regex_valid');
				delete_user_meta($user_id, 'ta_email_validation_validators_smtp_valid');
				delete_user_meta($user_id, 'ta_email_validation_validators_typo_valid');
            }
        }
    }



// Add custom fields to the user profile page
function ta_add_custom_user_profile_fields($user) {
    // Get user meta data
    $fingerprint = get_user_meta($user->ID, 'ta_unique_browser_fingerprint', true);
    $blocked = get_user_meta($user->ID, 'blocked_ta', true);
    $verified = get_user_meta($user->ID, 'ta_verified', true);
    ?>

    <h3><?php _e('Trusted Accounts Information', 'trusted-accounts'); ?></h3>
    
    <table class="form-table">
        <tr>
            <th><label for="browser_fingerprint"><?php _e('Browser Fingerprint', 'trusted-accounts'); ?></label></th>
            <td>
                <?php 
                if ($fingerprint === "false") {
                    echo '<span style="background-color: #ff922e15; color: #FF922E;">Fingerprint is not unique</span>';
                } elseif ($fingerprint === "true") {
                    echo '<span style="background-color: #28cc2515; color: #28CC25;">Fingerprint is unique</span>';
                } else {
                    echo '<span style="background-color: #3E3F7015; color: #3E3F70;">' . ($fingerprint ?: 'No fingerprint available') . '</span>';
                }
                ?>
            </td>
        </tr>
        <tr>
            <th><label for="user_blocked"><?php _e('Blocked', 'trusted-accounts'); ?></label></th>
            <td>
                <?php 
                echo '<span style="background-color: ' . ($blocked ? '#ff922e15' : '#28cc2515') . '; color: ' . ($blocked ? '#FF922E' : '#28CC25') . ';">' . ($blocked ? 'Blocked' : 'Not blocked') . '</span>';
                ?>
            </td>
        </tr>
        <tr>
            <th><label for="user_verified"><?php _e('Verified', 'trusted-accounts'); ?></label></th>
            <td>
                <?php 
                echo '<span style="background-color: ' . ($verified ? '#28cc2515' : '#ff922e15') . '; color: ' . ($verified ? '#28CC25' : '#FF922E') . ';">' . ($verified ? 'Verified' : 'Not verified') . '</span>';
                ?>
            </td>
        </tr>
    </table>
    <?php
}



}
?>
