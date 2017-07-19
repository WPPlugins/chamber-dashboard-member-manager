<div class="my_meta_control">
	<p class="alert"><?php _e( 'Make sure you save your invoice before emailing it!', 'cdashmm' ); ?></p>
	<form id="invoice_email">
		<label><?php _e( 'Send Invoice Notification To:', 'cdashmm' ); ?></label>
			<?php
				if( isset( $_GET['post'] ) ) {
				// Find connected business
				$connected = new WP_Query( array(
				  'connected_type' => 'invoices_to_businesses',
				  'connected_items' => $_GET['post'],
				  'nopaging' => true,
				) );

				if ( $connected->have_posts() ) {
					while ( $connected->have_posts() ) : $connected->the_post();
						// get the billing email 
						global $billing_metabox;
						$billingmeta = $billing_metabox->the_meta();
						if( isset ( $billingmeta['billing_email'] ) ) {
							if( strpos( $billingmeta['billing_email'], ',' ) ) {
								// we have a comma-separated list, so we need to split this into different fields
								$emails = explode( ',', $billingmeta['billing_email'] );
								foreach( $emails as $email ) { ?>
									<input type="checkbox" name="send_to[]" class="send_to" value="<?php echo $email; ?>" checked="checked"><?php echo $email; ?><br />
								<?php }
							} else { ?>
				    			<input type="checkbox" name="send_to[]" class="send_to" value="<?php echo $billingmeta['billing_email']; ?>" checked="checked"><?php echo $billingmeta['billing_email']; ?><br />
				    	<?php }
				    	}
				    	// get other associated emails
				    	global $buscontact_metabox;
						$contactmeta = $buscontact_metabox->the_meta();
						if( isset( $contactmeta['location'] ) ) {
							$locations = $contactmeta['location'];
							foreach($locations as $location) {
								if( isset( $location['email'] ) && is_array( $location['email'] ) ) {
									$emails = $location['email'];
									foreach( $emails as $email ) { ?>
										<input type="checkbox" name="send_to[]" class="send_to" value="<?php echo $email['emailaddress']; ?>"><?php echo $email['emailaddress']; ?><br />
									<?php }
								}
							}
						}
					endwhile; 
				} else {
					_e( 'Before you can send this invoice, you must connect it to a business (see the "Connected Businesses" box below this one).', 'cdashmm' );
				}
				wp_reset_postdata(); 
				$post = get_post( $_GET['post'] ); // this is weird.  this query destroys $post, and resetting postdata doesn't reset it, so I had to add this
			} else {
				_e( 'You must save this invoice before you can send it.', 'cdashmm' );
			}
			?>
		<label><?php _e( 'Send Copy To:', 'cdashmm' ); ?></label>
			<input type="checkbox" name="copy_to[]" class="copy_to" value="<?php echo get_option( 'admin_email' ); ?>"><?php echo get_option( 'admin_email' ); ?><br />
			<?php $options = $options = get_option( 'cdashmm_options' );
			$reply_to = $options['receipt_from_email']; 
			if( isset( $reply_to ) && $reply_to !== get_option( 'admin_email' ) ) { ?>
				<input type="checkbox" name="copy_to[]" class="copy_to" value="<?php echo $reply_to; ?>"><?php echo $reply_to; ?><br />
			<?php } ?>
		<label><?php _e( 'Custom Message:', 'cdashmm' ); ?></label>
			<textarea id="custom-message" name="message" placeholder="<?php _e( 'Optional. This message will appear at the top of your email.', 'cdashmm' ); ?>"></textarea>
		
		<input name="cdashmm_notification_nonce" id="cdashmm_notification_nonce" type="hidden" value="<?php echo wp_create_nonce( 'cdashmm_notification_nonce' ); ?>">
		<input name="invoice_id" id="invoice_id" type="hidden" value="<?php echo $post->ID; ?>">
		<a class="button" id="notification_submit"><?php _e( 'Send Email', 'cdashmm' ); ?></a>

		<div id="result"></div>
	</form>

	<label><?php _e( 'Notification history', 'cdashmm' ); ?></label>
	<?php $meta = get_post_meta(get_the_id()); 
	$oldnotifications = get_post_meta( get_the_id(), '_cdashmm_notification' ); ?>
		<?php while($mb->have_fields_and_multi('notification')): ?>
		<?php $mb->the_group_open(); ?>

			<p><?php $mb->the_field('notification_date'); ?>
			<strong><?php _e('Date', 'cdashmm'); ?>:</strong> <?php $mb->the_value(); ?>
			<input type="hidden" class="notification_date" name="<?php $mb->the_name(); ?>" value="<?php $mb->the_value(); ?>"/><br />

			<?php $mb->the_field('notification_to'); ?>
			<strong><?php _e('Recipient(s)', 'cdashmm'); ?></label>:</strong> <?php $mb->the_value(); ?>
			<input type="hidden" class="notification_to" name="<?php $mb->the_name(); ?>" value="<?php $mb->the_value(); ?>"/>
			</p>
		<?php $mb->the_group_close(); ?>
		<?php endwhile; ?>
</div>
