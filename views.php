<?php
if ( ! defined( 'ABSPATH' ) ) exit;
// ------------------------------------------------------------------------
// shortcode to display membership levels, perks, and prices
// ------------------------------------------------------------------------
function cdashmm_membership_levels_shortcode( $atts ) {
	// Attributes
	extract( shortcode_atts(
		array(
			'orderby' => 'priority', // options: name, count, priority
			'exclude' => '',
			'order' => 'ASC'
		), $atts )
	);
	// get all of the membership levels
	if( $orderby == 'priority' ) {
		$args = 'orderby=term_order&order='.$order.'&hide_empty=0';
	} elseif( $orderby == 'name' ) {
		$args = 'orderby=name&order='.$order.'&hide_empty=0';
	}
	elseif( $orderby == 'count' ) {
		$args = 'orderby=count&order='.$order.'&hide_empty=0';
	} else {
		$args = 'hide_empty=0'; 
	}
	if( '' !== 'exclude' ) {
		$args .= '&exclude=' . $exclude;
	}
	$levels = get_terms( 'membership_level', $args );
	$levels_view = '<div id="membership-levels">';
	// go through each level and display the information
	foreach( $levels as $level ) {
		$levels_view .= '<div class="level">';
		$levels_view .= '<h3>' . $level->name . '</h3>';
		$levels_view .= do_shortcode( stripslashes( wpautop( get_tax_meta( $level->term_id,'perks' ) ) ) );
		$levels_view .= '<p class="price">' . cdashmm_display_price( get_tax_meta( $level->term_id,'cost' ) ) . __(' per year', 'cdashmm' ) . '</p>';
		$levels_view .= '</div>';
	}
	$levels_view .= '</div>';
	return $levels_view;
}
add_shortcode( 'membership_levels', 'cdashmm_membership_levels_shortcode' );
// ------------------------------------------------------------------------
// shortcode to display membership sign-up/renewal form
// ------------------------------------------------------------------------

function cdashmm_membership_signup_form() {
	// Enqueue stylesheet
	wp_enqueue_style( 'cdashmm-membership', plugin_dir_url(__FILE__) . 'css/cdashmm-membership.css' );
	// Enqueue ajax to make the form work
	wp_enqueue_script( 'html5validate', plugin_dir_url(__FILE__) . 'js/jquery.h5validate.js', array( 'jquery' ) );
	wp_enqueue_script( 'membership-form', plugin_dir_url(__FILE__) . 'js/membership.js', array( 'jquery' ) );
    wp_localize_script( 'membership-form', 'membershipformajax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );

    $options = get_option( 'cdashmm_options' );
    $paypal = true;
    if( !isset( $options['paypal_email'] ) || '' == $options['paypal_email'] ) {
    	$paypal = false;
    }
    $cdash_options = get_option( 'cdash_directory_options' );
    $currency = $cdash_options['currency'];
	$member_form = '';
	global $member_form;

	// Display form
	if( !isset( $currency ) ) {
		$member_form .= __( 'You have not entered in your currency settings.  In your WordPress dashboard, go to the Chamber Dashboard settings page to select what currency you accept.', 'cdashmm' );
	} else {
		$member_form .= 
		'<form id="membership_form" name="membership_form" action="https://www.paypal.com/cgi-bin/webscr" method="post">
			<p class="explain">' . __( '* = Required') . '</p>
			<input name="cdashmm_membership_nonce" id="cdashmm_membership_nonce" type="hidden" value="' . wp_create_nonce( 'cdashmm_membership_nonce' ) . '">
			<p>
				<label>' . __( 'Business Name', 'cdashmm' ) . ' *</label>
				<input name="name" type="text" id="name" required>
			</p>
			<div id="business-picker"></div>			
			<input name="business_id" type="hidden" id="business_id" value="">
			<p>
				<label>' . __( 'Billing Address', 'cdashmm' ) . ' *</label>
				<input name="address" type="text" id="address" required>
			</p>
			<p>
				<label>' . __( 'City', 'cdashmm' ) . ' *</label>
				<input name="city" type="text" id="city" required>
			</p>
			<p>
				<label>' . __( 'State/Province', 'cdashmm' ) . ' *</label>
				<input name="state" type="text" id="state" required>
			</p>
			<p>
				<label>' . __( 'Zip/Postal Code', 'cdashmm' ) . ' *</label>
				<input name="zip" type="text" id="zip" required>
			</p>
			<p>
				<label>' . __( 'Email', 'cdashmm' ) . ' *</label>
				<input name="email" type="email" id="email" required>
			</p>
			<p>
				<label>' . __( 'Phone Number', 'cdashmm' ) . ' *</label>
				<input name="phone" type="text" id="phone" required>
			</p>
			<p>
				<label>' . __( 'Membership Level', 'cdashmm' ) . ' *</label>';
				$levels = get_terms( 'membership_level', 'hide_empty=0' );
				$predetermined_level = false;
				if( isset( $_GET['membership_level'] ) ) {
					// we have a membership level parameter, so first let's make sure it matches an existing membership level
					$level_slugs = array();
					foreach( $levels as $level ) {
						$level_slugs[] = $level->slug;
					}
					if( in_array( $_GET['membership_level'], $level_slugs ) ) {
						$predetermined_level = true;
					}
				}
				if( true == $predetermined_level ) {
					$this_level = get_term_by( 'slug', $_GET['membership_level'], 'membership_level' );
					$price = cdashmm_display_price( get_tax_meta( $this_level->term_id, 'cost' ) );
					$member_form .= '<input name="level" type="text" id="level" disabled value="' . $this_level->name . ' - ' . $price . '">';
					$starting_price = get_tax_meta( $this_level->term_id, 'cost' );
				} else {
					$member_form .= '<select name="level" id="level" required>
						<option value=""></option>';
						// get all the levels
						foreach( $levels as $level ) {
							$price = cdashmm_display_price( get_tax_meta( $level->term_id, 'cost' ) );
							// display the membership level name and price
							$member_form .= '<option value="' . $level->term_id . '">' . $level->name . ':&nbsp' . $price . '</option>';
						}
					$member_form .= '</select>';
					$starting_price = 0;
				}
			$member_form .= '</p>
			<input type="hidden" value="" name="membership_amt" id="membership_amt">';
			if( isset( $options['use_processing_fee'] ) ) {
				if( isset( $options['processing_fee_amount'] ) ) {
					$fee = $options['processing_fee_amount'];
				} else {
					$fee = 0;
				}

				$member_form .= '<p>

					<label>' . __( 'Processing Fee', 'cdashmm' ) . '</label>

					<input disabled name="processing" type="number" min="0" id="processing" value="' . $fee . '">';

					$starting_price += $fee;

				$member_form .= '</p>';

			}

			$member_form .= '

			<input name="subtotal" type="hidden" id="subtotal" value="' . $starting_price . '">';

			if( !isset( $options['no_donation'] ) ) {

				$member_form .= '<p>

					<label>' . __( 'Optional Donation', 'cdashmm' ) . '</label>

					<input name="donation" type="number" min="0" id="donation" value="' . $options['suggested_donation'] . '">';

					$starting_price += $options['suggested_donation'];

					if( isset( $options['donation_explanation'] ) ) {

						$member_form .= '<br /><span class="donation_explanation">' . $options['donation_explanation'] . '</span>';

					}

				$member_form .= '</p>';

			}

			if( isset( $options['charge_tax'] ) ) {

				if( isset( $options['tax_rate'] ) ) {

					$tax = round( ( ( $options['tax_rate'] / 100 ) * $starting_price ), 2 );

				} else {

					$tax = 0;

				}

				$member_form .= '<p>

							<label>' . __( 'Tax', 'cdashmm' ) . '</label>

							<input type="hidden" name="tax-rate" id="tax-rate" value="' . $options['tax_rate'] . '">

							<input disabled name="tax" type="number" step=".01" min="0" id="tax" value="' . $tax . '">';

				$starting_price += $tax;

				$member_form .= '</p>';

			}

			$member_form .= '

			<p class="total">

				<label>' . __( 'Total Due: ', 'cdashmm' ) . '</label>

				<input name="total" id="total" class="total" value="' . $starting_price . '" disabled>

			</p>';

			if( true == $paypal ) {

				$member_form .= '<p class="method">

					<label>' . __( 'Payment Method: ', 'cdashmm' ) . '</label>

					<input name="method" type="radio" value="paypal" id="pay_paypal" class="method" checked>&nbsp;' . __( 'PayPal', 'cdashmm' ) . '<br />

					<input name="method" type="radio" value="check" id="pay_check" class="method">&nbsp;' . __( 'Check', 'cdashmm' ) . '

				</p>';

				do_action( 'cdashmm_recurring_payments_fields', $member_form ); 

			} else {

				$member_form .= '<p class="method" style="display: none;">

					<label>' . __( 'Payment Method: ', 'cdashmm' ) . '</label>

					<input name="method" type="radio" value="check" id="pay_check" class="method" checked>&nbsp;' . __( 'Check', 'cdashmm' ) . '

				</p>';

			}

			$member_form .= '<div class="hidden-paypal-fields">';

			do_action( 'cdashmm_paypal_hidden_fields', $member_form );

			$member_form .= 

			'

				<input type="hidden" class="paypal business" name="business" value="' . $options['paypal_email'] . '">

				<input type="hidden" class="paypal return" name="return" value="' . get_the_permalink() . '">

				<input type="hidden" class="paypal currency_code" name="currency_code" value="' . $currency . '">

				<input type="hidden" class="paypal rm" name="rm" value="2">

				<input type="hidden" class="paypal no_shipping" name="no_shipping" value="1">

				<input type="hidden" class="paypal custom" name="custom" id="invoice_id" value="' . cdashmm_calculate_invoice_number() . '">

				<input type="hidden" class="paypal cbt" name="cbt" value="Return to ' . $options['orgname'] . '">

				<input type="hidden" class="paypal notify_url" name="notify_url" value="' . home_url() . '/?cdash-member-manager=paypal-ipn">

			</div>

			<p>

				<input type="submit" value="' . __( 'Pay Now', 'cdashmm' ) . '">

			</p>

		</form>';

	}
	return $member_form;

}

add_shortcode( 'membership_form', 'cdashmm_membership_signup_form' );

// ------------------------------------------------------------------------
// Display PayPal hidden fields
// ------------------------------------------------------------------------

function cdashmm_paypal_cart_fields() {

	global $member_form;

	$options = get_option( 'cdashmm_options' );

    //$fee = 0;

    if(isset($options['use_processing_fee']) ){

        if(isset($options['processing_fee_amount']) ){

            $fee = $options['processing_fee_amount'];

        }

        else{

            $fee = 0;

        }        

    }

    else{

        $fee = 0;

    }

	

	if( isset( $options['no_donation'] ) ) {

		$donation = 0;

	} else {

		$donation = $options['suggested_donation']; 

	}



    if( isset( $options['charge_tax'] ) ) {

	    if( isset( $options['tax_rate'] ) ) {

		    $tax = round( ( ( $options['tax_rate'] / 100 ) * $starting_price ), 2 );

         } else {

			$tax = 0;        

		}

    }



	// note to self - this code is also in membership.js

	$member_form .= 

	'<input type="hidden" class="paypal cart cmd" name="cmd" value="_cart">

	<input type="hidden" class="paypal cart upload" name="upload" value="1" />

	<input type="hidden" class="paypal cart item_name_1" name="item_name_1" value="' . __( 'Membership', 'cdashmm' ) . '">

	<input type="hidden" class="paypal cart amount_1" name="amount_1" id="amount_1" value="">

	<input type="hidden" class="paypal cart item_name_2" name="item_name_2" value="' . __( 'Donation', 'cdashmm' ) . '">

	<input type="hidden" class="paypal cart amount_2" name="amount_2" id="amount_2" value="' . $donation . '">

	<input type="hidden" class="paypal cart item_name_3" name="item_name_3" value="' . __( 'Processing Fee', 'cdashmm' ) . '">

	<input type="hidden" class="paypal cart amount_3" name="amount_3" id="amount_3" value="' . $fee . '">

	<input type="hidden" class="paypal cart item_name_4" name="item_name_4" value="' . __( 'Tax', 'cdashmm' ) . '">

    <input type="hidden" class="paypal cart amount_4" name="amount_4" id="amount_4" value="' . $tax . '">';



}

add_action( 'cdashmm_paypal_hidden_fields', 'cdashmm_paypal_cart_fields', 10 );

// ------------------------------------------------------------------------
// AJAX functions for membership form
// ------------------------------------------------------------------------

// AJAX - when a membership level is selected, find the price and send it to the form
function cdashmm_update_total_amount() {

    if ( !wp_verify_nonce( $_POST['nonce'], "cdashmm_membership_nonce")) {
        exit( "There was an error." );
    }

    $levelid = $_POST['level_id'];

    $cost = get_tax_meta( $levelid, 'cost' );

	$cost_no_commas = str_replace( ',', '', $cost );

    $results = floatval( $cost_no_commas );



    die( $results );

}

add_action( 'wp_ajax_nopriv_cdashmm_update_total_amount', 'cdashmm_update_total_amount' );

add_action( 'wp_ajax_cdashmm_update_total_amount', 'cdashmm_update_total_amount' );



// AJAX - when a business name is entered, check whether the business is already in the database

function cdashmm_find_existing_business() {

	

    if ( !wp_verify_nonce( $_POST['nonce'], "cdashmm_membership_nonce")) {

        exit( "There was an error." );

    }

 

    $name = $_POST['name'];

    $results = '';



    $args = array( 

        'post_type' => 'business',

        'post_title_like' => $name,

        'posts_per_page' => -1,

        'orderby' => 'title',

        'order' => 'ASC',

    );

    

    $bus_query = new WP_Query( $args );

    

    // The Loop

    if ( $bus_query->have_posts() ) :

    	$results .= '<div class="alert"><p>' . __( 'It looks like your business is already in our database!  To verify, select your business below:', 'cdashmm' ) . '</p>';

	    while ( $bus_query->have_posts() ) : $bus_query->the_post();

	    	$results .= '<input type="radio" name="business_id" class="business_id" value="' . get_the_id() . '">&nbsp;' . get_the_title() . '<br />';

	    endwhile;

	    $results .= '<input type="radio" name="business_id" class="business_id" value="new">&nbsp;None of the above<br /></div>';

    endif;

    

    // Reset Post Data

    wp_reset_postdata();

    

    die($results);

}

add_action( 'wp_ajax_nopriv_cdashmm_find_existing_business', 'cdashmm_find_existing_business' );

add_action( 'wp_ajax_cdashmm_find_existing_business', 'cdashmm_find_existing_business' );





// AJAX - when an existing business is selected, fill in the form

function cdashmm_prefill_membership_form() {

	

    if ( !wp_verify_nonce( $_POST['nonce'], "cdashmm_membership_nonce")) {

        exit( "There was an error." );

    }

 

    $business_id = $_POST['business_id'];

    if( "new" == $_POST['business_id'] ) {

    	die();

    }

    $results = array();



    $args = array( 

        'post_type' => 'business',

        'p' => $business_id,

    );

    

    $bus_query = new WP_Query( $args );

    

    // The Loop

    if ( $bus_query->have_posts() ) :

	    while ( $bus_query->have_posts() ) : $bus_query->the_post();

			$results['business_id'] = get_the_id();

			$results['business_name'] = get_the_title();



			global $billing_metabox;

			$billingmeta = $billing_metabox->the_meta();

			if( isset( $billingmeta['billing_address'] ) || isset( $billingmeta['billing_city'] ) || isset( $billingmeta['billing_state'] ) || isset( $billingmeta['billing_zip'] ) || isset( $billingmeta['billing_phone'] ) || isset( $billingmeta['billing_email'] ) ) {

				// the billing address exists, so we'll insert it into the form

				if( isset ( $billingmeta['billing_address'] ) )

					$results['address'] = $billingmeta['billing_address'];

				if( isset ( $billingmeta['billing_city'] ) )

					$results['city'] = $billingmeta['billing_city'];

				if( isset ( $billingmeta['billing_state'] ) )

					$results['state'] = $billingmeta['billing_state'];

				if( isset ( $billingmeta['billing_zip'] ) )

					$results['zip'] = $billingmeta['billing_zip'];

				if( isset ( $billingmeta['billing_phone'] ) )

					$results['phone'] = $billingmeta['billing_phone'];

				if( isset ( $billingmeta['billing_email'] ) )

					$results['email'] = $billingmeta['billing_email'];



			} else {

				// the billing address doesn't exist, so let's grab the first address and insert it instead

				global $buscontact_metabox;

				$contactmeta = $buscontact_metabox->the_meta();

				if( isset( $contactmeta['location'] ) ) {

					$locations = $contactmeta['location'];

					if( isset( $location['donotdisplay'] ) && "1" == $location['donotdisplay'] ) {

						continue;

					} else {

						foreach ( $locations as $location ) {

							if( isset( $location['phone'] ) ) {

								$phones = $location['phone'];

								foreach($phones as $phone) {

									$results['phone'] = $phone['phonenumber'];

									break; // we only need one, so we'll stop the loop here

								}

							}

							if( isset( $location['email'] ) ) {

								$emails = $location['email'];

								foreach($emails as $email) {

									$results['email'] = $email['emailaddress'];

									break; // we only need one, so we'll stop the loop here

								}

							}	

							if( isset ( $location['address'] ) )

								$results['address'] = $location['address'];

							if( isset ( $location['city'] ) )

								$results['city'] = $location['city'];

							if( isset ( $location['state'] ) )

								$results['state'] = $location['state'];

							if( isset ( $location['zip'] ) )

								$results['zip'] = $location['zip'];



							break; // we only need one, so we'll stop the loop here

						}

					}

				}

			}

	    endwhile;

    endif;

    

    // Reset Post Data

    wp_reset_postdata();



    // $results = json_encode($results);

   	wp_send_json($results);

    

    die();

}

add_action( 'wp_ajax_nopriv_cdashmm_prefill_membership_form', 'cdashmm_prefill_membership_form' );

add_action( 'wp_ajax_cdashmm_prefill_membership_form', 'cdashmm_prefill_membership_form' );



// AJAX - save form data before it gets sent off to PayPal

function cdashmm_process_membership_form() {

	

	// check for the nonce and get out of here right away if it isn't right

    if ( !wp_verify_nonce( $_POST['nonce'], "cdashmm_membership_nonce" ) ) {

        exit( "There was an error." );

    }

 

 	// gather all of the variables

    $business_id = $_POST['business_id'];

    $name = $_POST['name'];

    $address = $_POST['address'];

    $city = $_POST['city'];

	$state = $_POST['state'];

	$zip = $_POST['zip'];

	$phone = $_POST['phone'];

	$email = $_POST['email'];

    $today = date( 'Y-m-d' );

    $total = $_POST['total'];

    $membership_level = $_POST['membership_level'];

    $member_amt = $_POST['member_amt'];

    if( isset( $_POST['donation'] ) ) {

    	$donation = $_POST['donation'];

    }

	$tax = $_POST['tax'];

    $invoice_id = $_POST['invoice_id'];

    $options = get_option( 'cdashmm_options' );



    // Create or update the business

    if( isset( $business_id ) && $business_id !== '' ) {

		// we already have a business, so let's update it



		// add a serialised array for wpalchemy to work - see http://www.2scopedesign.co.uk/wpalchemy-and-front-end-posts/

		$fields = array( 

			'_cdash_billing_address', 

			'_cdash_billing_city', 

			'_cdash_billing_state', 

			'_cdash_billing_zip', 

			'_cdash_billing_email', 

			'_cdash_billing_phone'

		);

		$str = $fields;

		update_post_meta( $business_id, 'billing_meta_fields', $str );



		// Create the array of billing information for wpalchemy

		$billingfields = array(

				array(

				'billing_address',

				'billing_city',

				'billing_state',

				'billing_zip',

				'billing_phone',

				'billing_email',

				)

			);



		// update each individual field

		update_post_meta( $business_id, '_cdash_billing_address', $address );

		update_post_meta( $business_id, '_cdash_billing_city', $city );

		update_post_meta( $business_id, '_cdash_billing_state', $state );

		update_post_meta( $business_id, '_cdash_billing_zip', $zip );

		update_post_meta( $business_id, '_cdash_billing_email', $email );

		update_post_meta( $business_id, '_cdash_billing_phone', $phone );



		// add membership level

		wp_set_post_terms( $business_id, $membership_level, 'membership_level', false );

	} else {

		// we need to create a new business

		$business_details = array(

			'post_status'    	=> 'draft', 

			'post_type'      	=> 'business',

			'post_title'		=> $name,

			'post_content'  	=> __( 'This business draft was automatically generated by the membership form.', 'cdashmm' ),

		);

		$business_id = wp_insert_post( $business_details );



		// add a serialised array for wpalchemy to work - see http://www.2scopedesign.co.uk/wpalchemy-and-front-end-posts/

		$fields = array('_cdash_billing_address', '_cdash_billing_city', '_cdash_billing_state', '_cdash_billing_zip', '_cdash_billing_email', '_cdash_billing_phone' );

		$str = $fields;

		update_post_meta( $business_id, 'billing_meta_fields', $str );



		// Create the array of billing information for wpalchemy

		$billingfields = array(

				array(

				'billing_address',

				'billing_city',

				'billing_state',

				'billing_zip',

				'billing_phone',

				'billing_email',

				)

			);



		// update each individual field

		update_post_meta( $business_id, '_cdash_billing_address', $address );

		update_post_meta( $business_id, '_cdash_billing_city', $city );

		update_post_meta( $business_id, '_cdash_billing_state', $state );

		update_post_meta( $business_id, '_cdash_billing_zip', $zip );

		update_post_meta( $business_id, '_cdash_billing_email', $email );

		update_post_meta( $business_id, '_cdash_billing_phone', $phone );



		// add membership level

		wp_set_post_terms( $business_id, $membership_level, 'membership_level', false );

	}



    $business_name = get_the_title( $business_id );



    // Create an invoice for this transaction

    $invoice_details = array(

		'post_status'    	=> 'publish', 

		'post_type'      	=> 'invoice',

		'post_title'		=> __( 'Membership for ', 'cdashmm' ) . $business_name,

		'post_content'  	=> __( 'This invoice was automatically generated by the membership form.', 'cdashmm' ),

	);

	$invoice = wp_insert_post( $invoice_details );



	// add a serialised array for wpalchemy to work

	$invoicefields = array( 

		'_cdashmm_invoice_number',

		'_cdashmm_amount', 

		'_cdashmm_duedate', 

		'_cdashmm_item_membershiplevel', 

		'_cdashmm_item_membershipamt',

		'_cdashmm_item_tax',

		'_cdashmm_item_donation', 

		'_cdashmm_item_processing_fee',

		'_cdashmm_paidamt', 

		'_cdashmm_paiddate',

		'_cdashmm_paymethod',

		'_cdashmm_transaction' 

		);

	$str = $invoicefields;

	update_post_meta( $invoice, 'invoice_meta_fields', $str );

	 

	// update the individual fields

	update_post_meta( $invoice, '_cdashmm_invoice_number', $invoice_id );

	update_post_meta( $invoice, '_cdashmm_amount', $total );

	update_post_meta( $invoice, '_cdashmm_duedate', $today );

	update_post_meta( $invoice, '_cdashmm_item_membershiplevel', $membership_level );

	update_post_meta( $invoice, '_cdashmm_item_membershipamt', $member_amt );

	update_post_meta( $invoice, '_cdashmm_item_tax', $tax);

	if( isset( $donation ) ) {

		update_post_meta( $invoice, '_cdashmm_item_donation', $donation );

	}

	if( isset( $options['use_processing_fee'] ) ) {

		if( isset( $options['processing_fee_amount'] ) ) {

			$processing_fee = $options['processing_fee_amount'];

		} else {

			$processing_fee = 0;

		}

		update_post_meta( $invoice, '_cdashmm_item_processing_fee', $processing_fee );

	}

	

	if( "paypal" == $_POST['method'] ) {

		// if paying with PayPal, mark invoice status as pending for now

		$pending = get_term_by( 'slug', 'pending', 'invoice_status' );

		wp_set_post_terms( $invoice, $pending->term_id, 'invoice_status', false );

	} elseif( "check" == $_POST['method'] ) {

		// if paying by check, mark invoice status as open

		$open = get_term_by( 'slug', 'open', 'invoice_status' );

		wp_set_post_terms( $invoice, $open->term_id, 'invoice_status', false );

	}



	// connect the invoice to the business

	p2p_type( 'invoices_to_businesses' )->connect( $invoice, $business_id, array(

	    'date' => current_time('mysql')

	) );



	// if paying by check, send emails to business and to admin

	if( "check" == $_POST['method'] ) {

		// send email receipt to business

	    $receipt_to = $email;

	    $receipt_subject = __( 'Invoice from ', 'cdashmm' ) . $options['orgname'];

	    $receipt_message = $options['check_message'];

    	$receipt_message .= '<p><strong>' . __( 'View the invoice: ', 'cdashmm' ) . '</strong><a href="' . get_the_permalink( $invoice ) . '">' . get_the_permalink( $invoice ) . '</a></p>';

        $receipt_from = "From: " . $options['receipt_from_name'] . " <" . $options['receipt_from_email'] . ">";



        cdashmm_send_email( $receipt_from, $receipt_to, '', $receipt_subject, $receipt_message );



        // send email to site admin 

        $admin_to = $options['admin_email'];

        $admin_subject = __( 'New Payment By Check', 'cdashmm' );

        $admin_message = '<p>' . get_the_title( $business_id ) . __( ' has just filled out your membership form, and will pay by check.', 'cdashmm' ) . '</p>';

        $admin_message .= '<p><strong>' . __( 'Payment amount: ', 'cdashmm' ) . '</strong>' . cdashmm_display_price( $total ) . '</p>';

        $admin_message .= '<p><strong>' . __( 'View the invoice: ', 'cdashmm' ) . '</strong><a href="' . get_the_permalink( $invoice ) . '">' . get_the_permalink( $invoice ) . '</a></p>';

        $admin_message .= '<p><strong>' . __( 'View the business: ', 'cdashmm' ) . '</strong><a href="' . get_the_permalink( $business_id ) . '">' . get_the_permalink( $business_id ) . '</a></p>';

        if( "draft" == get_post_status( $business_id ) ) {

            $admin_message .= '<p>' . get_the_title( $business_id ) . __( 'is a new business, so you need to publish the new business before it will appear in your member directory.', 'cdashmm' ) . '</p>';

        }

        $admin_from = "Chamber Dashboard <" . $options['receipt_from_email'] . ">";



        cdashmm_send_email( $admin_from, $admin_to, '', $admin_subject, $admin_message );

    }



	if( "paypal" == $_POST['method'] ) {

		$results = $invoice;

	} elseif( "check" == $_POST['method'] ) {

		$results = get_permalink( $invoice );

	} else {

		$results = __( 'No payment method selected', 'cdashmm' );

	}



    die($results);

}

add_action( 'wp_ajax_nopriv_cdashmm_process_membership_form', 'cdashmm_process_membership_form' );

add_action( 'wp_ajax_cdashmm_process_membership_form', 'cdashmm_process_membership_form' );





// ------------------------------------------------------------------------

// Single Invoice View

// ------------------------------------------------------------------------



// Enqueue stylesheet for single invoice

function cdashmm_single_invoice_style() {

	if( is_singular( 'invoice' ) ) {

		wp_enqueue_style( 'cdashmm-invoice', plugin_dir_url(__FILE__) . 'css/invoice.css' );

	}

}

add_action( 'wp_enqueue_scripts', 'cdashmm_single_invoice_style' );



// Filter the title so it just says "invoice"

function cdashmm_single_invoice_title( $title ) {
	global $post;
	/*if( is_singular('invoice') && $title == $post->post_title ) {

        $title = "Invoice";

    }*/
	if( is_singular('invoice') && 'invoice' == get_post_type($id) && $title == $post->post_title ) {

        $title = "Invoice";

    }

    return $title;	
}
add_filter( 'the_title', 'cdashmm_single_invoice_title', 10, 2 );

// Add a class to previous and next post list on the single invoice page so that it can be hidden using CSS

function cdashmm_invoice_posts_link_attributes($output) {
	if(is_singular('invoice')){
		$code = 'class="single_invoice_nav_link"';
    	return str_replace('<a href=', '<a '.$code.' href=', $output);	
	}    
}

add_filter('next_post_link', 'cdashmm_invoice_posts_link_attributes');
add_filter('previous_post_link', 'cdashmm_invoice_posts_link_attributes');



// add script to footer to print invoice div

// http://stackoverflow.com/a/7532581/1112258

function cdashmm_invoice_footer_scripts() { 

    if( is_singular( 'invoice' ) ) { ?>

        <script type="text/javascript">

            function printDiv(divName) {

		     var printContents = document.getElementById(divName).innerHTML;

		     var originalContents = document.body.innerHTML;



		     document.body.innerHTML = printContents;



		     window.print();



		     document.body.innerHTML = originalContents;

}

        </script>

<?php }

}

add_action('wp_footer', 'cdashmm_invoice_footer_scripts');









// Display single invoice (filter content)

function cdashmm_single_invoice( $content ) {

	if( is_singular('invoice') ) {

		// get options

		$options = get_option( 'cdashmm_options' );

	    $paypal = true;

	    if( !isset( $options['paypal_email'] ) || '' == $options['paypal_email'] ) {

	    	$paypal = false;

	    }

		// get the business associated with this invoice

		$invoice_id = get_the_id();

        $args = array( 

            'post_type' => 'business',

            'connected_type' => 'invoices_to_businesses',

            'connected_items' => $invoice_id,

        );

        

        $thisbusiness = new WP_Query( $args );

        // $msg = "<pre>" . print_r($thisbusiness, true) . "</pre>";

        // wp_die($msg);



        if ( $thisbusiness->have_posts() ) {

            while ( $thisbusiness->have_posts() ) : $thisbusiness->the_post();

        		$business_id = get_the_id();

        		global $billing_metabox;

				$billingmeta = $billing_metabox->the_meta();

				$business_billing = '<p>' . get_post_field( 'post_title', $business_id ) . '<br />'; // this is a convoluted way to get the title, but it bypasses the title filter

				if( isset ( $billingmeta['billing_address'] ) )

					$business_billing .= $billingmeta['billing_address'] . '<br />';

				if( isset ( $billingmeta['billing_city'] ) )

					$business_billing .= $billingmeta['billing_city'] . ', ';

				if( isset ( $billingmeta['billing_state'] ) )

					$business_billing .= $billingmeta['billing_state'] . ' ';

				if( isset ( $billingmeta['billing_zip'] ) )

					$business_billing .= $billingmeta['billing_zip'] . '<br />';

				if( isset ( $billingmeta['billing_phone'] ) )

					$business_billing .= $billingmeta['billing_phone'] . '<br />';

				if( isset ( $billingmeta['billing_email'] ) )

					$business_billing .= $billingmeta['billing_email'];

				$business_billing .= '</p>';

                 

            endwhile;

        } else {

        	$error = __( 'This invoice does not have a business associated with it.  You need to edit the invoice and add a business.', 'cdashmm' );

        	wp_die( $error );

        }

        wp_reset_postdata();



        // get invoice meta

        global $invoice_metabox;

        $invoice_meta = $invoice_metabox->the_meta();

        $statuses = get_the_terms( get_the_id(), 'invoice_status' );

        $this_status = '';

        if( isset( $statuses ) && !is_wp_error( $statuses ) && $statuses != '' ) {

        	foreach( $statuses as $status ) {

        		$this_status = $status->name;

        	}

        }

        if( isset( $invoice_meta['duedate'] ) ) {

        	$duedate = $invoice_meta['duedate'];

        } else {

        	$duedate = __( 'Invoice due on receipt', 'cdashmm' );

        }



        $cdash_options = get_option( 'cdash_directory_options' );

    	$currency = $cdash_options['currency'];



		$invoice_content = 

		'<div id="invoice" class="' . $this_status . '">

			<p><input type="button" onclick="printDiv(\'print-invoice\')" value="' . __( 'Print Invoice', 'cdashmm' ) . '" /></p>

			<div id="print-invoice">

				<div class="invoice-header">

					<div class="invoice-header-contact">

						<div class="invoice-from">

							<h4>From: </h4>

							' . do_shortcode( wpautop( $options['invoice_from'] ) ) . '

						</div>

						<div class="invoice-to">

							<h4>To: </h4>

							' . $business_billing . '

						</div>

					</div>

					<div class="invoice-header-details">

						<ul>

							<li><strong>' . __( 'Invoice #: ', 'cdashmm' ) . '</strong><span class="invoice-number"> ' . $invoice_meta['invoice_number'] . '</span></li>

							<li><strong>' . __( 'Issue Date: ', 'cdashmm' ) . '</strong><span class="issue-date"> ' . get_the_time( 'Y-m-d' ) . '</span></li>

							<li><strong>' . __( 'Due Date: ', 'cdashmm' ) . '</strong><span class="due-date"> ' . $duedate . '</span></li>

							<li><strong>' . __( 'Status: ', 'cdashmm' ) . '</strong><span class="status"> ' . $this_status . '</span></li>';

							if( "Paid" == $this_status && isset( $invoice_meta['paiddate'] ) ) {

								$invoice_content .= '<li><strong>' . __( 'Paid Date: ', 'cdashmm' ) . '</strong><span class="status"> ' . $invoice_meta['paiddate'] . '</span></li>';

							}

						$invoice_content .= '</ul>

					</div>

				</div>

				<div class="invoice-description">'

					. $content .

				'</div>

				<table class="invoice-details">

					<tr>

						<th>Item</th>

						<th>Amount</th>

					</tr>';

					if( isset( $invoice_meta['item_membershiplevel'] ) && isset( $invoice_meta['item_membershipamt'] ) ) {

						$level = get_term_by( 'id', $invoice_meta['item_membershiplevel'], 'membership_level' );

						$invoice_content .=

						'<tr>

							<td>' . __( 'Membership: ', 'cdashmm' ) . $level->name . '</td>

							<td>' . cdashmm_display_price( $invoice_meta['item_membershipamt'] ) . '</td>

						</tr>';

					}

					if( isset( $invoice_meta['items'] ) ) {

						$items = $invoice_meta['items'];

						foreach( $items as $item ) {

							$invoice_content .=

							'<tr>

								<td>' . $item['item_name'] . '</td>

								<td>' . cdashmm_display_price( $item['item_amount'] ) . '</td>

							</tr>';

						}

					}

                    if( isset( $invoice_meta['item_tax'] ) && '' !== $invoice_meta['item_tax'] ) {

						$invoice_content .=

						'<tr>

							<td>' . __( 'Tax', 'cdashmm' ) . '</td>

							<td>' . cdashmm_display_price( $invoice_meta['item_tax'] ) . '</td>

						</tr>';

					}

					if( isset( $invoice_meta['item_donation'] ) && '' !== $invoice_meta['item_donation'] ) {

						$invoice_content .=

						'<tr>

							<td>' . __( 'Donation', 'cdashmm' ) . '</td>

							<td>' . cdashmm_display_price( $invoice_meta['item_donation'] ) . '</td>

						</tr>';

					}

					if( isset( $invoice_meta['item_processing_fee'] ) && '' !== $invoice_meta['item_processing_fee'] ) {

						$invoice_content .=

						'<tr>

							<td>' . __( 'Processing Fee', 'cdashmm' ) . '</td>

							<td>' . cdashmm_display_price( $invoice_meta['item_processing_fee'] ) . '</td>

						</tr>';

					}

					$invoice_content .=

					'<tr class="total">

						<td><strong>' . __( 'Total', 'cdashmm') . '</strong></td>

						<td><strong>' . cdashmm_display_price( $invoice_meta['amount'] ) . '</strong></td>

					</tr>

				</table>

				<div class="invoice-footer">

					' . do_shortcode( wpautop( $options['invoice_footer'] ) ) . '

				</div>

			</div><!-- #print-invoice -->';



			// the invoice hasn't been paid, so we'll include a payment button

			if( 'Paid' !== $this_status && true == $paypal ) {

				$invoice_content .=

				'<div class="payment-form">

					<form id="invoice_form" action="https://www.paypal.com/cgi-bin/webscr" method="post">

						<input type="hidden" name="cmd" value="_cart">

						<input type="hidden" name="upload" value="1" />

						<input type="hidden" name="business" value="' . $options['paypal_email'] . '">

						<input type="hidden" name="return" value="' . get_the_permalink() . '">

						<input type="hidden" name="currency_code" value="' . $currency . '">';

						// membership amount, if needed

						$i = 1;

						if( isset( $invoice_meta['item_membershipamt'] ) ) {

							$invoice_content .=

							'<input type="hidden" name="item_name_'.$i.'" value="Membership">

							<input type="hidden" name="amount_'.$i.'" id="amount_'.$i.'" value="' . $invoice_meta['item_membershipamt'] . '">';

							$i++;

						}

						// other items, if needed 

						if( isset( $invoice_meta['items'] ) ) {

							$items = $invoice_meta['items'];

							foreach( $items as $item ) {

								$invoice_content .=

								'<input type="hidden" name="item_name_'.$i.'" value="'.$item['item_name'].'">

								<input type="hidden" name="amount_'.$i.'" id="amount_'.$i.'" value="' . $item['item_amount'] . '">';

								$i++;

							}



						}

						// donation amount, if needed

						if( isset( $invoice_meta['item_donation']) ) {

							$invoice_content .=

							'<input type="hidden" name="item_name_'.$i.'" value="Donation">

							<input type="hidden" name="amount_'.$i.'" id="amount_'.$i.'" value="' . $invoice_meta['item_donation'] . '">';

                            $i++;

						}

                        

                        // tax amount, if needed

						if( isset( $invoice_meta['item_tax']) ) {

							$invoice_content .=

							'<input type="hidden" name="item_name_'.$i.'" value="Tax">

							<input type="hidden" name="amount_'.$i.'" id="amount_'.$i.'" value="' . $invoice_meta['item_tax'] . '">';

						}

                        $i++;

                        

                        // Processing Fee amount, if needed

						if( isset( $invoice_meta['item_processing_fee']) ) {

							$invoice_content .=

							'<input type="hidden" name="item_name_'.$i.'" value="Processing Fee">

							<input type="hidden" name="amount_'.$i.'" id="amount_'.$i.'" value="' . $invoice_meta['item_processing_fee'] . '">';

                            $i++;

						}

                        

						$invoice_content .=

						'<input type="hidden" name="rm" value="2">

						<input type="hidden" name="custom" id="invoice_id" value="' . $invoice_meta['invoice_number'] . '">

						<input type="hidden" name="cbt" value="Return to ' . $options['orgname'] . '">

						<input type="hidden" name="notify_url" value="' . home_url() . '/?cdash-member-manager=paypal-ipn">

						<p><input type="submit" value="' . __( 'Pay Now With PayPal', 'cdashmm' ) . '"></p>

					</form>

				</div>';

			}

		$invoice_content .=

		'<p><input type="button" onclick="printDiv(\'print-invoice\')" value="' . __( 'Print Invoice', 'cdashmm' ) . '" /></p>

		</div>';



		$content = $invoice_content;

	} 



	return $content;



}

add_filter('the_content', 'cdashmm_single_invoice');



function cdashmm_hide_lapsed_members_search_filter( $query ) {

	$member_options = get_option('cdashmm_options');

	if(isset($member_options['hide_lapsed_members']) && '1' == $member_options['hide_lapsed_members'] ) {

		if ( !is_admin() && $query->is_main_query() ) {

			$query->set('tax_query', array(

				array(

					'taxonomy' => 'membership_status',

					'field' => 'slug',

					'terms' => array('lapsed'),

					'operator' => 'NOT IN'

				)

			));

  		}

	}

}



add_action('pre_get_posts', 'cdashmm_hide_lapsed_members_search_filter');



?>