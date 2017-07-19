<div class="my_meta_control">
	<?php $mb->the_field('renewal_date'); ?>
	<label><?php _e('Next Membership Renewal Date', 'cdashmm'); ?></label>
	<p><input type="text" name="<?php $mb->the_name(); ?>" value="<?php $mb->the_value(); ?>" id="renewal_date" /></p> 

	<?php do_action( 'cdashmm_membership_renewal_metabox', $mb ); ?>
</div>