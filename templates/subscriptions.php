<div class="wrap ich-settings-main-wrap">
	<h3 class="page-header"><?php _e( 'Real Estate Manager - WooCommerce Addon', 'real-estate-manager' ); ?></h3>

	<?php if ( class_exists( 'WooCommerce' ) ) { ?>
	<div class="panel panel-default">
		<div class="panel-heading">
			<b>Woo Estato Subscriptions</b>
		</div>
		<div class="panel-body">
		<table class="table table-bordered">
			<tr>
				<th>Full Name</th>
				<th>Username</th>
				<th>Email</th>
				<th>Purchased Package</th>
				<th>Properties</th>
			</tr>
			<?php
				$agents = get_users( array('role' => 'rem_property_agent') );
				foreach ($agents as $key => $agent) {
					$agent_info = get_userdata($agent->ID); ?>
					<tr>
						<td>
							<?php echo get_user_meta( $agent->ID, 'first_name', true ); ?>
							<?php echo get_user_meta( $agent->ID, 'last_name', true ); ?>
						</td>
						<td><?php echo $agent_info->user_login; ?></td>
						<td><?php echo $agent_info->user_email; ?></td>
						<td>
							<?php
								$pkg = get_user_meta( $agent->ID, 'rem_latest_package', true );
								if ($pkg != '' && is_array($pkg)) {
									echo '<b>'.$pkg['name'].'</b>: '.wc_price($pkg['price']).'<br> ';
									echo '<b>Properties Allow</b>: '.$pkg['count'].'<br> ';
									echo '<b>Purchased</b>: ';
									printf( _x( '%s ago', '%s = human-readable time difference', 'woo-estato' ), human_time_diff( $pkg['time'], current_time( 'timestamp' ) ) );
									echo '<br>';
									echo '<b>Valid Till</b>: ';
									$settings = get_option( 'rem_woo_packages' );
									$s_type = $settings['subscription_type'];
									$valid_time = '';
									if ($s_type == 'monthly') {
										$valid_time = strtotime('+30 days', $pkg['time']);
									}
									if ($s_type == 'annually') {
										$valid_time = strtotime('+365 days', $pkg['time']);
									}									
									echo date('d-M-Y', $valid_time);
								} else {
									echo __( 'No Package Purchased', 'woo-estato' );
								}
							?>
						</td>
						<td><?php echo count_user_posts( $agent->ID, 'rem_property' ); ?></td>
					</tr>
				<?php }
			?>
		</table>
		</div>
	</div>
	<?php } else { ?>
		<div class="alert alert-danger">
			Please make sure WooCommerce Plugin is activated and installed on your site.
		</div>
	<?php } ?>
</div>