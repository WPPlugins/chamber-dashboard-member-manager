<?php
// add submenu page
function cdashmm_add_payment_reports_page() {
	global $reports_page;
	$reports_page = add_submenu_page( 'edit.php?post_type=invoice', 'Payment Report', 'Payment Report', 'delete_posts', 'payment-report', 'cdashmm_generate_payment_report' );
}
add_action('admin_menu', 'cdashmm_add_payment_reports_page');
function cdashmm_generate_payment_report() { ?>
		<h1><?php _e( 'Payment Report', 'cdashmm' ); ?></h1>
		<form method='post' action='<?php echo admin_url( 'edit.php?post_type=invoice&page=payment-report'); ?>' id='payment-report'>
			<table class="form-table">
				<tr>
					<th scope="row"><?php _e( 'Select Date Range:', 'cdashmm' ); ?></th>
					<td>
						From <input type="text" size="10" name="start_date" id="start_date" value="" required /> to <input type="text" size="10" name="end_date" id="end_date" value="" required />
					</td>					
				</tr>
			</table>


			<p class="submit">


			<input type="hidden" name="view" value="view">


			<input type="submit" class="button-primary" value="<?php _e( 'Generate Report', 'cdashmm' ); ?>" />


			</p>


		</form>





		<?php if( $_POST ) { 


			if( ( $_POST['start_date'] == '' ) || ( $_POST['end_date'] == '' ) ) {


				_e('You must enter both a start date and an end date to create a report.', 'cdashmm');


			} elseif( $_POST['start_date'] > $_POST['end_date'] ) {


				_e('The report end date must be after the report begin date.', 'cdashmm');


			} else {


				// find the payments and generate the report


				$args = array( 


				    'post_type' => 'invoice',


				    'posts_per_page' => -1, 


				    'invoice_status' => 'paid',


				    'meta_query' => array(


				    	//'relation' => 'AND',


						array(


							'key'     => '_cdashmm_paiddate',


							'value'   => $_POST['start_date'],


							'compare' => '>=',


						),


						array(


							'key'     => '_cdashmm_paiddate',


							'value'   => $_POST['end_date'],


							'compare' => '<=',


						),


					),


					'meta_key' => '_cdashmm_paiddate',


					'orderby' => 'meta_value',


					'order' => 'ASC',


					);


				


				$invoice_query = new WP_Query( $args );





				$total = 0;


				


				// The Loop


				if ( $invoice_query->have_posts() ) { ?>


					<form method='post' action='<?php echo admin_url( 'edit.php?post_type=invoice&page=payment-report'); ?>' id='payment-report'>


						<input type="hidden" name="start_date" id="start_date" value="<?php echo $_POST['start_date']; ?>" />


						<input type="hidden" name="end_date" id="end_date" value="<?php echo $_POST['end_date']; ?>"  />


						<input type="hidden" name="cdashmm_download_payment_report" value="cdashmm_download_payment_report">


						<p>


							<input type="submit" class="button-primary" value="<?php _e( 'Download This Report', 'cdashmm' ) ?>" />


						</p>


					</form>





					<table id="payment-report" class="wp-list-table widefat fixed">


						<thead>


							<tr>


								<th><?php _e( 'Date Paid', 'cdashrp' ); ?></th>


								<th><?php _e( 'Title', 'cdashrp' ); ?></th>


								<th><?php _e( 'Business', 'cdashrp' ); ?></th>


								<th><?php _e( 'Invoice #', 'cdashrp' ); ?></th>


								<th><?php _e( 'Amount', 'cdashrp' ); ?></th>


								<th><?php _e( 'Membership Level', 'cdashrp' ); ?></th>


							</tr>


						</thead>


						<tbody>


							<?php while ( $invoice_query->have_posts() ) : $invoice_query->the_post(); 


								global $invoice_metabox;


	        					$invoice_meta = $invoice_metabox->the_meta();


	        					$date = '';


	        					if( isset( $invoice_meta['paiddate'] ) )


	        						$date = $invoice_meta['paiddate'];


	        					$amount = '';


	        					if( isset( $invoice_meta['amount'] ) ) {


	        						$total += $invoice_meta['amount'];


	        						$amount = cdashmm_display_price( $invoice_meta['amount'] );


	        					}


	        					$level = '';


	        					if( isset( $invoice_meta['item_membershiplevel'] ) ) {


	        						$level_term = get_term_by( 'id', $invoice_meta['item_membershiplevel'], 'membership_level' );;


	        						$level = $level_term->name;


	        					}


	        					$thisbusiness = get_posts( array(


								  'connected_type' => 'invoices_to_businesses',


								  'connected_items' => get_the_id(),


								  'nopaging' => true,


								  'suppress_filters' => false,


								  'post_status' => 'any'


								) );


								foreach( $thisbusiness as $thisone ) {


									$business = $thisone->post_title . '<br />';


									$business .= '<a href="' . get_edit_post_link( $thisone->ID ) . '">' . __( 'Edit', 'cdashmm' ) . '</a> |';


									$business .= '<a href="' . get_permalink( $thisone->ID ) . '">' . __( 'View', 'cdashrp' ) . '</a>';


								}


	        					$invoice_number = '';


	        					if( isset( $invoice_meta['invoice_number'] ) )


	        						$invoice_number = '<a href="' . get_edit_post_link( get_the_id() ) . '">' . $invoice_meta['invoice_number'] . '</a>';


								?>


								<tr class="alternate">


									<td>


										<?php echo $date; ?>


									</td>


									<td>


										<?php the_title(); ?><br />


										<a href="<?php echo get_edit_post_link(); ?>"><?php _e( 'Edit', 'cdashrp' ); ?></a> |


										<a href="<?php the_permalink(); ?>"?><?php _e( 'View', 'cdashrp' ); ?></a>


									</td>


									<td>


										<?php echo $business; ?>


									</td>


									<td>


										<?php echo $invoice_number; ?>


									</td>


									<td>


										<?php echo $amount; ?>


									</td>


									<td>


										<?php echo $level; ?>


									</td>


								</tr>


							<?php endwhile; ?>


						</tbody>


						<tfoot>


							<tr>


								<th></th>


								<th></th>


								<th></th>


								<th></th>


								<th><?php _e( 'Total: ', 'cdashrp' ); ?> <?php echo cdashmm_display_price( $total ); ?></th>


							</tr>


						</tfoot>


					</table>





					<form method='post' action='<?php echo admin_url( 'edit.php?post_type=invoice&page=payment-report'); ?>' id='payment-report'>


						<input type="hidden" name="start_date" id="start_date" value="<?php echo $_POST['start_date']; ?>" />


						<input type="hidden" name="end_date" id="end_date" value="<?php echo $_POST['end_date']; ?>"  />


						<input type="hidden" name="cdashmm_download_payment_report" value="cdashmm_download_payment_report">


						<p>


							<input type="submit" class="button-primary" value="<?php _e( 'Download This Report', 'cdashmm' ) ?>" />


						</p>


					</form>


				<?php } else {


					_e( 'No payments were found within that date range', 'cdashmm' ); 


				}


				


				// Reset Post Data


				wp_reset_postdata();


			





			} 


		} 





}





add_action('admin_init','cdashmm_download_payment_report_csv');





function cdashmm_download_payment_report_csv() {


	if ( isset( $_POST['cdashmm_download_payment_report'] ) ) {


		// set up the file


			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");


			header('Content-Description: File Transfer');


			header("Content-type: text/csv");


			header("Content-Disposition: attachment; filename=chamber-dashboard-payment-report.csv");


			header("Expires: 0");


			header("Pragma: public");





			//open the file 


			$fh = @fopen( 'php://output', 'w' );





			// create the header row


			$header = array(


				__( 'Date Paid', 'cdashmm' ),


				__( 'Invoice #', 'cdashmm' ),


				__( 'Amount', 'cdashmm' ),


				__( 'Membership Level', 'cdashmm' ),


				__( 'Business', 'cdashmm' ),


				__( 'View Invoice', 'cdashmm' ),


				__( 'Edit Invoice', 'cdashmm' ),


				__( 'View Business', 'cdashmm' ),


				__( 'Edit Business', 'cdashmm' )


			);





			// Add the headers to the CSV			


			fputcsv($fh, $header);





			// find the payments and generate the report


				$args = array( 


				    'post_type' => 'invoice',


				    'posts_per_page' => -1, 


				    'invoice_status' => 'paid',


				    'meta_query' => array(


				    	//'relation' => 'AND',


						array(


							'key'     => '_cdashmm_paiddate',


							'value'   => $_POST['start_date'],


							'compare' => '>=',


						),


						array(


							'key'     => '_cdashmm_paiddate',


							'value'   => $_POST['end_date'],


							'compare' => '<=',


						),


					),


					'meta_key' => '_cdashmm_paiddate',


					'orderby' => 'meta_value',


					'order' => 'ASC',


					);


				


				$invoice_query = new WP_Query( $args );





				// The Loop


				if ( $invoice_query->have_posts() ) : 


					while ( $invoice_query->have_posts() ) : $invoice_query->the_post(); 


						$fields = array();


						global $invoice_metabox;


    					$invoice_meta = $invoice_metabox->the_meta();





    					// Date


    					if( isset( $invoice_meta['paiddate'] ) ) {


    						$fields[] = $invoice_meta['paiddate'];


    					} else {


    						$fields[] = ' ';


    					}





    					// Invoice Number


    					if( isset( $invoice_meta['invoice_number'] ) ) {


    						$fields[] = $invoice_meta['invoice_number'];


						} else {


							$fields[] = ' ';


						}





						// Amount


						if( isset( $invoice_meta['amount'] ) ) {


    						$fields[] = cdashmm_display_price( $invoice_meta['amount'] );


    					} else {


    						$fields[] = ' ';


    					}





    					// Membership Level


    					if( isset( $invoice_meta['item_membershiplevel'] ) ) {


    						$level_term = get_term_by( 'id', $invoice_meta['item_membershiplevel'], 'membership_level' );;


    						$fields[] = $level_term->name;


    					} else {


    						$fields[] = ' ';


    					}





    					// Business Name


						$thisbusiness = get_posts( array(


						  'connected_type' => 'invoices_to_businesses',


						  'connected_items' => get_the_id(),


						  'nopaging' => true,


						  'suppress_filters' => false


						) );


						foreach( $thisbusiness as $thisone ) {


							$fields[] = $thisone->post_title . '<br />';


							$edit_business = get_edit_post_link( $thisone->ID );


							$view_business = get_permalink( $thisone->ID );


						}





						// View Invoice


						$fields[] = get_permalink();





						// Edit Invoice


						$fields[] = get_edit_post_link();





						// View Business


						$fields[] = $view_business;





						// Edit Business


						$fields[] = $edit_business;





						// Add the row to the CSV


						fputcsv($fh, $fields);


					endwhile;


				endif;


				


				// Reset Post Data


				wp_reset_postdata();





			// Close the file stream


			fclose($fh);


			exit;


	}


}








?>