<?php 
if ( ! defined( 'ABSPATH' ) ) exit;
// Create a query var so PayPal has somewhere to go
// https://willnorris.com/2009/06/wordpress-plugin-pet-peeve-2-direct-calls-to-plugin-files

function cdashmm_register_query_var($vars) {
    $vars[] = 'cdash-member-manager';
    return $vars;
}
add_filter('query_vars', 'cdashmm_register_query_var');

// If PayPal has gone to our query var, check that it is correct and process the payment
function cdashmm_parse_paypal_ipn_request( $wp ) {
    // only process requests with "cdash-member-manager=paypal-ipn"
   if ( array_key_exists( 'cdash-member-manager', $wp->query_vars ) && $wp->query_vars['cdash-member-manager'] == 'paypal-ipn' ) {
        if( !isset( $_POST['txn_id'] ) ) {
            // send a 200 message to PayPal IPN so it knows this happened
            header('HTTP/1.1 200 OK'); 
            // POST data isn't there, so we aren't going to do anything else
        } else {
            // we have valid POST, so we're going to do stuff with it
            // send a 200 message to PayPal IPN so it knows this happened
            header('HTTP/1.1 200 OK'); 
            // check whether we already have an invoice with this transaction ID, and if so, stop
            // this prevents PayPal from sending notifications over and over and over and over and over
            $args = array( 
                'post_type' => 'invoice',
                'meta_key' => '_cdashmm_transaction',
                'meta_value' => $_POST['txn_id'], 
            );
            $existing = new WP_Query( $args );
            if ( $existing->have_posts() ) :
                die();
            endif;
            wp_reset_postdata();

            // we don't already have an invoice with this transaction ID, so we'll process the request.
            $req = 'cmd=_notify-validate';
            foreach($_POST as $key => $value) :
              $value = urlencode(stripslashes($value));
              $req .= "&$key=$value";
            endforeach;
            $pp_url = 'https://www.paypal.com/cgi-bin/webscr';
            $pp_args['method'] = 'POST';
            $pp_args['headers']['content-type'] = 'application/x-www-form-urlencoded';
            $pp_args['headers']['content-length'] = strlen($req);
            $pp_args['headers']['connection'] = 'Close';
            $pp_args['body'] = $req;
            $result = wp_remote_request( $pp_url, $pp_args );

// mail('morgan@alchemycs.com', 'stuff', 'req: ' . print_r($req, true) . PHP_EOL. 'result: '.  print_r($result, true) . PHP_EOL . ' args:' . print_r( $pp_args, true )  . PHP_EOL . 'url: ' . $pp_url ) ;
            if( is_wp_error( $result ) || $result['response']['code'] != 200 ) {
              // HTTP ERROR
                if( is_wp_error( $result ) ){
                    // email error to site admin
                    $error_to = get_option( 'admin_email' );
                    $error_subject = __( 'Problem with PayPal transaction', 'cdashmm' );
                    $error_message = '<p>' . __( 'The Chamber Dashboard Member Manager encountered the following error trying to process a PayPal payment on your site, ', 'cdashmm' ) . get_option( 'blogname' )  . '<br>';
                    $error_message .= $result . '</p>';
                    $error_message .= '<p>' . __( 'Please contact <a href="https://chamberdashboard.com/forums/">Chamber Dashboard support</a> for assistance.', 'cdashmm' ) . '</p>';
                    $error_from = "From: Chamber Dashboard <" . get_option( 'admin_email' ) . ">";
                    cdashmm_send_email( $error_from, $error_to, '', $error_subject, $error_message );
                    die();
                } else {
                    // generic http error message
                }
            } else {
                $options = get_option( 'cdashmm_options' );
                if (strcmp( trim( $result['body'] ), "VERIFIED" ) == 0 ) {
// mail( "morgan@alchemycs.com", "post data", "<pre>" . print_r($_POST, true) . "</pre>" );

                    // gather variables
                    $invoice_id = $_POST['custom'];
                    $transaction_id = $_POST['txn_id'];
                    $paid = get_term_by( 'slug', 'paid', 'invoice_status' );
                    $current = get_term_by( 'slug', 'current', 'membership_status' );
                    $today = date('Y-m-d');

// mail( "morgan@alchemycs.com", "transaction id", $_POST['txn_id'] );
                    // get the invoice
                    $args = array( 
                        'post_type' => 'invoice',
                        'meta_key' => '_cdashmm_invoice_number',
                        'meta_value' => $invoice_id, 
                    );
                    $thisinvoice = new WP_Query( $args );
                    if ( $thisinvoice->have_posts() ) :
                        while ( $thisinvoice->have_posts() ) : $thisinvoice->the_post();
// mail( "morgan@alchemycs.com", "result of thisinvoice query", get_the_title() );
                            $invoice = get_the_id();
                            // if this is a subscription payment, check whether this invoice has already been paid
                            if( "subscr_payment" == $_POST['txn_type'] ) {
// mail( "morgan@alchemycs.com", "this is a subscription payment", $_POST['txn_type'] );                                
                                if( has_term( 'paid', 'invoice_status' ) ) {
                                    // since this one has been paid, we need to create a duplicate of it
// mail( "morgan@alchemycs.com", "has been paid", "this invoice has paid status" );
                                    // first we'll get some information about this invoice
                                    global $invoice_metabox;
                                    $invoice_meta = $invoice_metabox->the_meta();
                                    $today = date( 'Y-m-d' );
                                    $new_invoice_id = cdashmm_calculate_invoice_number();
                                    $args = array( 
                                        'post_type' => 'business',
                                        'connected_type' => 'invoices_to_businesses',
                                        'connected_items' => get_the_id(),
                                    );
                                    $thisbusiness = new WP_Query( $args );
                                    if ( $thisbusiness->have_posts() ) {
                                        while ( $thisbusiness->have_posts() ) : $thisbusiness->the_post();
                                            $business_id = get_the_id();
                                        endwhile;
                                    } 
                                    wp_reset_postdata();

                                    // create the new invoice
                                    $title = __( 'Recurring payment from ', 'cdashrp' ) . get_the_title();
                                    $new_invoice = array(
                                        'post_status'       => 'publish', 
                                        'post_type'         => 'invoice',
                                        'post_title'        => $title,
                                        'post_content'      => __( 'This invoice was automatically generated by a PayPal recurring payment.', 'cdashmm' ),
                                    );
                                    $invoice = wp_insert_post( $new_invoice );
// mail( "morgan@alchemycs.com", "new invoice id", $invoice );                                    
                                    // add a serialised array for wpalchemy to work
                                    $invoicefields = array( 
                                        '_cdashmm_invoice_number',
                                        '_cdashmm_amount', 
                                        '_cdashmm_duedate', 
                                        '_cdashmm_item_membershiplevel', 
                                        '_cdashmm_item_membershipamt',
                                        '_cdashmm_item_donation', 
                                        '_cdashmm_paidamt', 
                                        '_cdashmm_paiddate',
                                        '_cdashmm_paymethod',
                                        '_cdashmm_transaction' 
                                        );
                                    $str = $invoicefields;
                                    update_post_meta( $invoice, 'invoice_meta_fields', $str );
                                    // update the individual fields
                                    update_post_meta( $invoice, '_cdashmm_invoice_number', $new_invoice_id );
                                    update_post_meta( $invoice, '_cdashmm_amount', $invoice_meta['amount'] );
                                    update_post_meta( $invoice, '_cdashmm_duedate', $today );
                                    update_post_meta( $invoice, '_cdashmm_item_membershiplevel', $invoice_meta['item_membershiplevel'] );
                                    update_post_meta( $invoice, '_cdashmm_item_membershipamt', $invoice_meta['item_membershipamt'] );
                                    update_post_meta( $invoice, '_cdashmm_item_donation', $invoice_meta['item_donation'] );
                                    $pending = get_term_by( 'slug', 'pending', 'invoice_status' );
                                    wp_set_post_terms( $invoice, $pending->term_id, 'invoice_status', false );
                                    // connect the invoice to the business
                                    p2p_type( 'invoices_to_businesses' )->connect( $invoice, $business_id, array(
                                        'date' => current_time('mysql')
                                    ) );
                                }
                            } 
                        endwhile;
                    endif;
                    wp_reset_postdata();
// mail( "morgan@alchemycs.com", "invoice id", $invoice );
                    // update the invoice status
                    if( isset( $invoice ) ) {
                        wp_set_post_terms( $invoice, $paid->term_id, 'invoice_status', false );
                        // add a serialised array for wpalchemy to work
                        $invoicefields = array( 
                            '_cdashmm_invoice_number',
                            '_cdashmm_amount', 
                            '_cdashmm_duedate', 
                            '_cdashmm_item_membershiplevel', 
                            '_cdashmm_item_membershipamt',
                            '_cdashmm_item_donation', 
                            '_cdashmm_paidamt', 
                            '_cdashmm_paiddate',
                            '_cdashmm_paymethod',
                            '_cdashmm_transaction' 
                            );

                        $str = $invoicefields;
                        update_post_meta( $invoice, 'invoice_meta_fields', $str );
                        // update the invoice - add transaction ID
                        update_post_meta( $invoice, '_cdashmm_transaction', $transaction_id );
                        // update the invoice - add payment method
                        update_post_meta( $invoice, '_cdashmm_paymethod', 'PayPal' );
                        // update the invoice - add the date paid
                        update_post_meta( $invoice, '_cdashmm_paiddate', $today );
                        // update the invoice - add payment amount
                        update_post_meta( $invoice, '_cdashmm_paidamt', $_POST['payment_gross'] ); 
                        // get the business
                        $args = array( 
                            'post_type' => 'business',
                            'connected_type' => 'invoices_to_businesses',
                            'connected_items' => $invoice,
                        );
                        $thisbusiness = new WP_Query( $args );
                        if ( $thisbusiness->have_posts() ) :
                            while ( $thisbusiness->have_posts() ) : $thisbusiness->the_post();
                                $business = get_the_id();
                            endwhile;
                        endif;
                        wp_reset_postdata();

                        // if the business paid for membership or this is a subscription, update membership status and renewal date
                        if( isset( $_POST['item_name1'] )  || 'subscr_payment' == $_POST['txn_type'] ) {
                            if( isset( $business ) ) {
                                // change business membership status
                                wp_set_post_terms( $business, $current->term_id, 'membership_status', false );
                                // update the business's membership renewal date
                                if( "subscr_payment" == $_POST['txn_type'] ) {
                                    $renewaldate = date('Y-m-d', strtotime('+1 year'));
                                    $fields = array( '_cdashmm_renewal_date', '_cdashmm_recurring', '_cdashmm_paypal_subscriber_id' );
                                    $str = $fields;
                                    update_post_meta( $business, 'membership_renewal_fields', $str );
                                    update_post_meta( $business, '_cdashmm_renewal_date', $renewaldate );
                                    update_post_meta( $business, '_cdashmm_recurring', '1' );
                                    update_post_meta( $business, '_cdashmm_paypal_subscriber_id', $_POST['subscr_id'] );
                                } else {
                                    $renewaldate = date('Y-m-d', strtotime('+1 year'));
                                    $fields = array( '_cdashmm_renewal_date' );
                                    $str = $fields;
                                    update_post_meta( $business, 'membership_renewal_fields', $str );
                                    update_post_meta( $business, '_cdashmm_renewal_date', $renewaldate );  
                                }
                            } 
                        }
                        // send email receipt to business
                        $receipt_to = $_POST['payer_email'];
                        $receipt_subject = $options['receipt_subject'];
                        $receipt_message = $options['receipt_message'];
                        $receipt_message .= '<p><strong>' . __( 'Transaction details: ', 'cdashmm' ) . '</strong>';
                        $receipt_message .= '<p><strong>' . __( 'Payment amount: ', 'cdashmm' ) . '</strong>' . cdashmm_display_price( $_POST['mc_gross'] ) . '<br />';
                        $receipt_message .= '<strong>' . __( 'Payment date: ', 'cdashmm' ) . '</strong>' . $_POST['payment_date'] . '<br />';
                        $receipt_message .= '<strong>' . __( 'View the invoice: ', 'cdashmm' ) . '</strong><a href="' . get_the_permalink( $invoice ) . '">' . get_the_permalink( $invoice ) . '</a></p>';
                        // if this is a subscription payment, tell the business they have signed up for automatic renewal and give instructions for cancelling it
                        if( "subscr_payment" == $_POST['txn_type'] ) {
                             $receipt_message .= '<p>' . __( 'You have signed up for automatic recurring payments through PayPal.  If you need to cancel this recurring payment, you can do so by finding the "My preapproved payments" page of your PayPal account.', 'cdashmm' ) . '</p>';
                        }
                        $receipt_from = $options['receipt_from_name'] . " <" . $options['receipt_from_email'] . ">";
                        cdashmm_send_email( $receipt_from, $receipt_to, '', $receipt_subject, $receipt_message );
                        // send email to site admin 
                        $admin_to = $options['admin_email'];
                        $admin_subject = __( 'New Payment Received', 'cdashmm' );
                        $admin_message = '<p><strong>' . __( 'You have just received a new payment from ', 'cdashmm' ) . '</strong>' . get_the_title( $business ) . '</p>';
                        $admin_message .= '<p><strong>' . __( 'Payment amount: ', 'cdashmm' ) . '</strong>' . cdashmm_display_price( $_POST['mc_gross'] ) . '</p>';
                        $admin_message .= '<p><strong>' . __( 'View the invoice: ', 'cdashmm' ) . '</strong><a href="' . get_the_permalink( $invoice ) . '">' . get_the_permalink( $invoice ) . '</a></p>';
                        $admin_message .= '<p><strong>' . __( 'View the business: ', 'cdashmm' ) . '</strong><a href="' . get_the_permalink( $business ) . '">' . get_the_permalink( $business ) . '</a></p>';
                        if( "draft" == get_post_status( $business) ) {
                            $admin_message .= '<p>' . get_the_title( $business ) . __( 'is a new business, so you need to publish the new business before it will appear in your member directory.', 'cdashmm' ) . '</p>';
                        }
                        // if this is a subscription payment, say so
                        if( "subscr_payment" == $_POST['txn_type'] ) {
                            $admin_message .= '<p>' . __( 'This business has signed up for automatic recurring payments.', 'cdashmm') . '</p>';
                        }
                        // $admin_message = "<pre>" . print_r($_POST, true) . "</pre>";
                        $admin_from = "Chamber Dashboard <" . $options['receipt_from_email'] . ">";
                        cdashmm_send_email( $admin_from, $admin_to, '', $admin_subject, $admin_message );
                    } else {
                        $invoice_error = __( 'Chamber Dashboard couldn\'t find the member or the invoice associated with this transaction in the database.', 'cdashmm' );
                        $error_from = "Chamber Dashboard <" . $options['receipt_from_email'] . ">";
                        $error_to = $options['admin_email'];
                        $error_subject = __( 'Error Processing Payment', 'cdashmm' );
                        $error_message = '<p>' . __( 'Chamber Dashboard encountered an error processing a payment on your site.  We were unable to find the invoice associated with the transaction.  Please contact <a href="https://chamberdashboard.com/forums">Chamber Dashboard Support</a>.', 'cdashmm' ) . '</p>';
                        $error_message .= '<p>' . __( 'Debug information: ', 'cdashmm' ) . '</p>';
                        $error_message .= '<pre>' . print_r($_POST, true) . '</pre>';
                        cdashmm_send_email( $error_from, $error_to, '', $error_subject, $error_message );
                    }
                  }
                elseif( strcmp( trim( $result ), "INVALID") == 0 ) {
                    $error_from = "Chamber Dashboard <" . $options['receipt_from_email'] . ">";
                    $error_to = $options['admin_email'];
                    $error_subject = __( 'Error Processing Payment', 'cdashmm' );
                    $error_message = '<p>' . __( 'Chamber Dashboard encountered an error processing a payment on your site.  Please contact <a href="https://chamberdashboard.com/forums">Chamber Dashboard Support</a>.', 'cdashmm' ) . '</p>';
                    $error_message .= '<p>' . __( 'Debug information: ', 'cdashmm' ) . '</p>';
                    $error_message .= '<pre>' . print_r( $result , true ) . '</pre>';
                    cdashmm_send_email( $error_from, $error_to, '', $error_subject, $error_message );
                }
            }
        }
    }
}
add_action('parse_request', 'cdashmm_parse_paypal_ipn_request');
?>