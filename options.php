<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/* Options Page for Chamber Dashboard Member Manager */

// --------------------------------------------------------------------------------------
// CALLBACK FUNCTION FOR: register_uninstall_hook(__FILE__, 'cdashmm_delete_plugin_options')
// --------------------------------------------------------------------------------------

// Delete options table entries ONLY when plugin deactivated AND deleted

function cdashmm_delete_plugin_options() {
	//delete_option('cdashmm_options');
}
// ------------------------------------------------------------------------------
// CALLBACK FUNCTION FOR: register_activation_hook(__FILE__, 'cdashmm_add_defaults')
// ------------------------------------------------------------------------------
// Define default option settings
function cdashmm_add_defaults() {
	$tmp = get_option('cdashmm_options');
    if(!is_array($tmp)) {
		$arr = array(	
					"paypal_email" => get_bloginfo( 'admin_email' ),
					"orgname" => get_bloginfo( 'name' ),
					"receipt_subject" => __( 'Thank you for your payment!', 'cdashmm' ),
					"receipt_message" => __( 'We appreciate your support!  Payment details are below.', 'cdashmm' ),
					"receipt_from_name" => get_bloginfo( 'name' ),
					"receipt_from_email" => get_bloginfo( 'admin_email' ),
					"check_message" => __( 'We look forward to receiving your payment!', 'cdashmm' ),
					"admin_email" => get_bloginfo( 'admin_email' ),
					"suggested_donation" => '0',
					"lapse_membership" => '1',
					"hide_lapsed_members" => '1',
					"invoice_from" => '',
					"invoice_footer" => '',
					"donation_explanation" => ''
		);
		update_option('cdashmm_options', $arr);
	}
}
// Add menu page
function cdashmm_add_options_page() {
	add_submenu_page( '/chamber-dashboard-business-directory/options.php', __( 'Member Manager Options', 'cdashmm' ), __( 'Member Manager Options', 'cdashmm' ), 'manage_options', 'cdashmm', 'cdashmm_options_page' );
    
}
// ------------------------------------------------------------------------------
// REGISTER AND RENDER SETTINGS
// ------------------------------------------------------------------------------
add_action( 'admin_init', 'cdashmm_options_init' );
function cdashmm_options_init(  ) { 
	register_setting( 'cdashmm_plugin_options', 'cdashmm_options', 'cdashmm_validate_options' );
	add_settings_section(
		'cdashmm_main_section', 
		__( 'Member Manager Settings', 'cdashmm' ), 
		'cdashmm_options_section_callback', 
		'cdashmm_plugin_options'
	);
    
	add_settings_field( 
		'paypal_email', 
		__( 'PayPal Email Address', 'cdashmm' ), 
		'cdashmm_paypal_email_render', 
		'cdashmm_plugin_options', 
		'cdashmm_main_section',
		array( 
			__( 'Email address associated with your PayPal account.  Payments will be sent to this email address.  If you leave this blank, users will not have an option to pay by PayPal.', 'cdashmm')  
		)
	);
    
	add_settings_field( 
		'orgname', 
		__( 'Organization Name', 'cdashmm' ), 
		'cdashmm_orgname_render', 
		'cdashmm_plugin_options', 
		'cdashmm_main_section',
		array( 
			__( 'This will appear on your invoice and on the PayPal payment page.', 'cdashmm') ,
		) 
	);

	add_settings_field( 
		'receipt_subject',
		__( 'Receipt Email Subject', 'cdashmm' ), 
		'cdashmm_receipt_subject_render', 
		'cdashmm_plugin_options', 
		'cdashmm_main_section',
		array(
			__( 'Subject of email to be sent when an invoice is paid.', 'cdashmm') ,
		)
	);

	add_settings_field( 
		'receipt_from_name', 
		__( 'Receipt Email From Name', 'cdashmm' ), 
		'cdashmm_receipt_from_name_render', 
		'cdashmm_plugin_options', 
		'cdashmm_main_section',
		array(
			__( 'Name to appear in the "From" field on the receipt email and at the top of your invoice.', 'cdashmm' )
		)
	);

	add_settings_field( 
		'receipt_from_email', 
		__( 'Receipt Reply-To Email', 'cdashmm' ), 
		'cdashmm_receipt_from_email_render', 
		'cdashmm_plugin_options', 
		'cdashmm_main_section',
		array(
			__( 'Name to appear in the "From" field on the receipt email.', 'cdashmm' )
		)
	);

	add_settings_field( 
		'receipt_message', 
		__( 'Receipt Email Message', 'cdashmm' ), 
		'cdashmm_receipt_message_render', 
		'cdashmm_plugin_options', 
		'cdashmm_main_section',
		array(
			__( 'Body of email to be sent when an invoice is paid.  This message will be followed by the transaction details.', 'cdashmm' )
		)
	);

	add_settings_field( 
		'check_message', 
		__( 'Pay By Check Email Message', 'cdashmm' ), 
		'cdashmm_check_message_render', 
		'cdashmm_plugin_options', 
		'cdashmm_main_section',
		array(
			__( 'Body of email to be sent when a member agrees to pay by check.  This message will be followed by a link to the invoice.', 'cdashmm' )
		)
	);

	add_settings_field( 
		'admin_email', 
		__( 'Admin Notification Email', 'cdashmm' ), 
		'cdashmm_admin_email_render', 
		'cdashmm_plugin_options', 
		'cdashmm_main_section',
		array(
			__( 'Email address that will receive notification of membership payment.', 'cdashmm' )
		)
	);

	add_settings_field( 
		'invoice_from', 
		__( 'Invoice Contact Information', 'cdashmm' ), 
		'cdashmm_invoice_from_render', 
		'cdashmm_plugin_options', 
		'cdashmm_main_section',
		array(
			__( 'Enter your organization\'s name and contact information as you would like to to appear at the top of your invoices.', 'cdashmm' )
		)
	);

	add_settings_field( 
		'invoice_footer', 
		__( 'Invoice Footer', 'cdashmm' ), 
		'cdashmm_invoice_footer_render', 
		'cdashmm_plugin_options', 
		'cdashmm_main_section',
		array(
			__( 'Payment information, or anything else you want to appear at the bottom of your invoice.', 'cdashmm' )
		)
	);

	add_settings_field( 
		'no_donation', 
		__( 'Remove Donation Field', 'cdashmm' ), 
		'cdashmm_no_donation_render', 
		'cdashmm_plugin_options', 
		'cdashmm_main_section',
		array(
			 __( 'By default, the membership form includes an optional donation field.  Check this box if you do not want the donation field.', 'cdashmm' )
		)
	);

	add_settings_field( 
		'suggested_donation', 
		__( 'Suggested Donation Amount', 'cdashmm' ), 
		'cdashmm_suggested_donation_render', 
		'cdashmm_plugin_options', 
		'cdashmm_main_section',
		array(
			 __( 'If you want the suggested donation field to be pre-filled with a donation amount, enter that amount here (number only, no currency symbols).', 'cdashmm' )
		)
	);

	add_settings_field( 
		'donation_explanation', 
		__( 'Suggested Donation Text', 'cdashmm' ), 
		'cdashmm_donation_explanation_render', 
		'cdashmm_plugin_options', 
		'cdashmm_main_section',
		array(
			__( 'This text will appear on the membership form next to the suggested donation field.  It can include information about how your donation will be used, such as "Your donation supports local schools."', 'cdashmm' )
		)
	);

	add_settings_field( 
		'use_processing_fee', 
		__( 'Processing Fee', 'cdashmm' ), 
		'cdashmm_use_processing_fee_render', 
		'cdashmm_plugin_options', 
		'cdashmm_main_section',
		array(
			__( 'Check this box if you want to add a processing fee to membership transactions.', 'cdashmm' )
		)
	);

	add_settings_field( 
		'processing_fee_amount', 
		__( 'Processing Fee', 'cdashmm' ), 
		'cdashmm_processing_fee_amount_render', 
		'cdashmm_plugin_options', 
		'cdashmm_main_section',
		array(
			__( 'Enter the amount of the processing fee (number only, no currency symbols).', 'cdashmm' )
		)
	);

	add_settings_field(
		'charge_tax',
		__( 'Tax', 'cdashmm' ),
		'cdashmm_charge_tax_render',
		'cdashmm_plugin_options',
		'cdashmm_main_section',
		array(
			__( 'Check this box if you want to charge tax on membership.', 'cdashmm' )
		)
	);

	add_settings_field(
		'tax_rate',
		__( 'Tax Rate', 'cdashmm' ),
		'cdashmm_tax_rate_render',
		'cdashmm_plugin_options',
		'cdashmm_main_section',
		array(
			__( 'Enter the tax rate (number only, no currency symbols or percentage signs).', 'cdashmm' )
		)
	);

	add_settings_field( 
		'lapse_membership', 
		__( 'Automatically Lapse Membership?', 'cdashmm' ), 
		'cdashmm_lapse_membership_render', 
		'cdashmm_plugin_options', 
		'cdashmm_main_section',
		array(
			__( 'If this box is checked, businesses\' membership status will be marked as lapsed if they have an overdue membership invoice.', 'cdashmm' )
		) 
	);

	add_settings_field( 
		'hide_lapsed_members', 
		__( 'Hide lapsed members?', 'cdashmm' ),
		'cdashmm_hide_lapsed_members_render', 
		'cdashmm_plugin_options', 
		'cdashmm_main_section',
		array(
			__( 'If this box is checked, businesses whose membership is not current will not be shown anywhere on the site.', 'cdashmm' )
		) 
	);
}

function cdashmm_paypal_email_render( $args ) { 
	$options = get_option( 'cdashmm_options' );
	?>
	<input type='email' name='cdashmm_options[paypal_email]' value='<?php echo $options['paypal_email']; ?>'>
	<br /><span class="description"><?php echo $args[0]; ?></span>
	<?php
}

function cdashmm_orgname_render( $args ) { 
	$options = get_option( 'cdashmm_options' );
	?>
	<input type='text' name='cdashmm_options[orgname]' value='<?php echo $options['orgname']; ?>'>
	<br /><span class="description"><?php echo $args[0]; ?></span>
	<?php
}

function cdashmm_receipt_subject_render( $args ) { 
	$options = get_option( 'cdashmm_options' );
	?>
	<input type='text' name='cdashmm_options[receipt_subject]' value='<?php echo $options['receipt_subject']; ?>'>
	<br /><span class="description"><?php echo $args[0]; ?></span>
	<?php
}

function cdashmm_receipt_from_name_render( $args ) { 
	$options = get_option( 'cdashmm_options' );
	?>
	<input type='text' name='cdashmm_options[receipt_from_name]' value='<?php echo $options['receipt_from_name']; ?>'>
	<br /><span class="description"><?php echo $args[0]; ?></span>
	<?php
}
function cdashmm_receipt_from_email_render( $args ) { 
	$options = get_option( 'cdashmm_options' );
	?>
	<input type='text' name='cdashmm_options[receipt_from_email]' value='<?php echo $options['receipt_from_email']; ?>'>
	<br /><span class="description"><?php echo $args[0]; ?>
	<?php
}

function cdashmm_receipt_message_render( $args ) { 
	$options = get_option( 'cdashmm_options' );
	?>
 	<span class="description"><?php echo $args[0]; ?></span><br />
	<?php
		$args = array("media_buttons" => false, "textarea_name" => "cdashmm_options[receipt_message]", "textarea_rows" => "5");
		wp_editor( $options['receipt_message'], "receipt", $args );
	?>
	<?php
}

function cdashmm_check_message_render( $args ) { 
	$options = get_option( 'cdashmm_options' );
	?>
 	<span class="description"><?php echo $args[0]; ?></span><br />
	<?php
		$args = array("media_buttons" => false, "textarea_name" => "cdashmm_options[check_message]", "textarea_rows" => "5");
		wp_editor( $options['check_message'], "check", $args );
	?>
	<?php
}

function cdashmm_admin_email_render( $args ) { 
	$options = get_option( 'cdashmm_options' );
	?>
	<input type='text' name='cdashmm_options[admin_email]' value='<?php echo $options['admin_email']; ?>'>
	<br/ ><span class="description"><?php echo $args[0]; ?></span>
	<?php
}

function cdashmm_invoice_from_render( $args ) { 
	$options = get_option( 'cdashmm_options' );
	?>
 	<span class="description"><?php echo $args[0]; ?></span><br />
		<?php
			$args = array("textarea_name" => "cdashmm_options[invoice_from]", "textarea_rows" => "5", "media_buttons" => false);
			wp_editor( $options['invoice_from'], "from", $args );
		?>
	<?php
}

function cdashmm_invoice_footer_render( $args ) { 
	$options = get_option( 'cdashmm_options' );
	?>
 	<span class="description"><?php echo $args[0]; ?></span><br />
	<?php
		$args = array("textarea_name" => "cdashmm_options[invoice_footer]", "textarea_rows" => "5", "media_buttons" => false);

		wp_editor( $options['invoice_footer'], "footer", $args );

	?>

	<?php
	}

	function cdashmm_no_donation_render( $args ) { 	$options = get_option( 'cdashmm_options' );
	?>
	<input type='checkbox' name='cdashmm_options[no_donation]' <?php checked( $options['no_donation'], 1 ); ?> value='1'>
	<span class="description"><?php echo $args[0]; ?></span>
	<?php

}

function cdashmm_suggested_donation_render( $args ) { 

	$options = get_option( 'cdashmm_options' );
	?>
	<input type='number' name='cdashmm_options[suggested_donation]' value='<?php echo $options['suggested_donation']; ?>'>
	<br /><span class="description"><?php echo $args[0]; ?></span>
	<?php

}

function cdashmm_donation_explanation_render( $args ) { 

	$options = get_option( 'cdashmm_options' );
	?>
	<textarea cols='50' rows='3' name='cdashmm_options[donation_explanation]'><?php echo $options['donation_explanation']; ?></textarea>
 	<br /><span class="description"><?php echo $args[0]; ?></span>
	<?php

	}
function cdashmm_use_processing_fee_render( $args ) { 

	$options = get_option( 'cdashmm_options' );
	?>
	<input type='checkbox' name='cdashmm_options[use_processing_fee]' <?php checked( $options['use_processing_fee'], 1 ); ?> value='1'>
	<span class="description"><?php echo $args[0]; ?></span>
	<?php

}

function cdashmm_processing_fee_amount_render( $args ) { 

	$options = get_option( 'cdashmm_options' );
	?>
	<input type='number' name='cdashmm_options[processing_fee_amount]' value='<?php echo $options['processing_fee_amount']; ?>'>
	<br /><span class="description"><?php echo $args[0]; ?></span>
	<?php

}

function cdashmm_charge_tax_render( $args ) {

	$options = get_option( 'cdashmm_options' );
	?>
	<input type='checkbox' name='cdashmm_options[charge_tax]' <?php checked( $options['charge_tax'], 1 ); ?> value='1'>
	<span class="description"><?php echo $args[0]; ?></span>
	<?php

}

function cdashmm_tax_rate_render( $args ) {

	$options = get_option( 'cdashmm_options' );
	?>
	<input type='number' id="options_tax_rate" step='.01' name='cdashmm_options[tax_rate]' value='<?php echo $options['tax_rate']; ?>'>
	<br /><span class="description"><?php echo $args[0]; ?></span>
	<?php

}

function cdashmm_lapse_membership_render( $args ) { 

	$options = get_option( 'cdashmm_options' );
	?>
	<input type='checkbox' name='cdashmm_options[lapse_membership]' <?php checked( $options['lapse_membership'], 1 ); ?> value='1'>
	<span class="description"><?php echo $args[0]; ?></span>
	<?php

}

function cdashmm_hide_lapsed_members_render( $args ) { 

	$options = get_option( 'cdashmm_options' );
	?>
	<input type='checkbox' name='cdashmm_options[hide_lapsed_members]' <?php checked( $options['hide_lapsed_members'], 1 ); ?> value='1'>
	<span class="description"><?php echo $args[0]; ?></span>
	<?php

}

function cdashmm_options_section_callback(  ) { 

	// echo __( 'Chamber Dashboard Member Manager General Settings', 'cdashmm' );

}

function cdashmm_validate_options( $input ) {

	$allowed = array(
	    'a' => array(
	        'href' => array(),
	        'title' => array()
	    ),
	    'br' => array(),
	    'p' => array(),
	    'em' => array(),
	    'strong' => array(),
	    'li' => array(),
	    'ul' => array(),
	    'ol' => array(),
	    'h1' => array(),
	    'h2' => array(),
	    'h3' => array(),
	    'h4' => array(),
	);

    if( isset( $input['paypal_email'] ) ) {
    	$input['paypal_email'] = strip_tags( stripslashes( $input['paypal_email'] ) );
    }

    if( isset( $input['orgname'] ) ) {
    	$input['orgname'] = strip_tags( stripslashes( $input['orgname'] ) );
    }

    if( isset( $input['receipt_subject'] ) ) {
    	$input['receipt_subject'] = strip_tags( stripslashes( $input['receipt_subject'] ) );
    }

    if( isset( $input['receipt_from_name'] ) ) {
    	$input['receipt_from_name'] = strip_tags( stripslashes( $input['receipt_from_name'] ) );
    }

    if( isset( $input['receipt_from_email'] ) ) {
    	$input['receipt_from_email'] = strip_tags( stripslashes( $input['receipt_from_email'] ) );
    }

    if( isset( $input['receipt_message'] ) ) {
    	$input['receipt_message'] = wp_kses( $input['receipt_message'], $allowed );
    }

    if( isset( $input['check_message'] ) ) {
    	$input['check_message'] = wp_kses( $input['check_message'], $allowed );
    }

    if( isset( $input['admin_email'] ) ) {
    	$input['admin_email'] = strip_tags( stripslashes( $input['admin_email'] ) );
    }

    if( isset( $input['invoice_from'] ) ) {
    	$input['invoice_from'] = wp_kses( $input['invoice_from'], $allowed );
    }

    if( isset( $input['invoice_footer'] ) ) {
    	$input['invoice_footer'] = wp_kses( $input['invoice_footer'], $allowed );
    }

    if( isset( $input['suggested_donation'] ) ) {
    	$input['suggested_donation'] = strip_tags( stripslashes( $input['suggested_donation'] ) );
    }

    if( isset( $input['donation_explanation'] ) ) {
    	$input['donation_explanation'] = strip_tags( stripslashes( $input['donation_explanation'] ) );
    }
    if( isset( $input['processing_fee_amount'] ) ) {
    	$input['processing_fee_amount'] = strip_tags( stripslashes( $input['processing_fee_amount'] ) );
    }

    if( isset( $input['invoice_cc'] ) ) {
    	$input['invoice_cc'] = strip_tags( stripslashes( $input['invoice_cc'] ) );
    }

    if( isset( $input['invoice_subject'] ) ) {
    	$input['invoice_subject'] = strip_tags( stripslashes( $input['invoice_subject'] ) );
    }

    if( isset( $input['invoice_message'] ) ) {
    	$input['invoice_message'] = wp_kses( $input['invoice_message'], $allowed );
    }

    if( isset( $input['reminder_frequency'] ) ) {
    	$input['reminder_frequency'] = strip_tags( stripslashes( $input['reminder_frequency'] ) );
    }

	if( isset( $input['reminder_subject'] ) ) {
    	$input['reminder_subject'] = strip_tags( stripslashes( $input['reminder_subject'] ) );
    }

	if( isset( $input['reminder_message'] ) ) {
    	$input['reminder_message'] = wp_kses( $input['reminder_message'], $allowed );
    }

    if( isset( $input['recurring_payments_license'] ) ) {
		$options = get_option( 'cdashmm_options' );
	    $old = $options['recurring_payments_license'];
		if( $old && $old != $input['recurring_payments_license'] ) {
			delete_option( 'recurring_payments_license_status' ); // new license has been entered, so must reactivate
		}
		$input['recurring_payments_license'] = strip_tags( stripslashes( $input['recurring_payments_license'] ) );
	}

    return $input;
}

function cdashmm_options_page(  ) { 
?>
	<h1><?php _e( 'Chamber Dashboard Member Manager', 'cdashmm' ); ?></h1>
	<?php settings_errors(); ?>
	
	<div id="main" style="width: 70%; min-width: 350px; float: left;">
      	<form action='options.php' method='post'>
			<?php
				settings_fields( 'cdashmm_plugin_options' );
				do_settings_sections( 'cdashmm_plugin_options' );
				submit_button();
			?>
		</form>    
		
	</div>
	<?php
}
// Display a Settings link on the main Plugins page
function cdashmm_plugin_action_links( $links, $file ) {
	if ( $file == plugin_basename( __FILE__ ) ) {
		$cdashmm_links = '<a href="'.get_admin_url().'options-general.php?page=cdash-member-manager/options.php">'.__('Settings').'</a>';
		// make the 'Settings' link appear first
		array_unshift( $links, $cdashmm_links );
	}
	return $links;
}
?>