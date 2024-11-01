<div class="wrap ich-settings-main-wrap">
	<h3 class="page-header"><?php _e( 'Real Estate Manager - WooCommerce Addon', 'real-estate-manager' ); ?></h3>

	<?php if ( class_exists( 'WooCommerce' ) ) {
		$existing_settings = get_option( 'rem_woo_packages' ); 
	?>
	<form id="rem-woo-form" class="form-horizontal">
		<div class="panel panel-default">
			<div class="panel-heading">
				<b>Woo Estato Settings</b>
			</div>
			<div class="panel-body">
	            <div class="form-group wrap_subscription_type">
	                <label for="subscription_type" class="col-sm-4 control-label">Subscription Type</label>
	                <div class="col-sm-8">
	                    <select class="form-control input-sm" id="subscription_type">
	                    	<option value="disable" <?php echo (isset($existing_settings['subscription_type']) && $existing_settings['subscription_type'] == 'disable') ? 'selected' : '' ; ?>>Disable Subscription</option>
	                    	<option value="monthly" <?php echo (isset($existing_settings['subscription_type']) && $existing_settings['subscription_type'] == 'monthly') ? 'selected' : '' ; ?>>Monthly</option>
	                    	<option value="annually" <?php echo (isset($existing_settings['subscription_type']) && $existing_settings['subscription_type'] == 'annually') ? 'selected' : '' ; ?>>Annually</option>
	                    </select>
	                    <span class="help-block">Choose the type of subscription</span>
	                </div>
	            </div>

	            <div class="form-group">
	                <label for="woo_product" class="col-sm-4 control-label">WooCommerce Product</label>
	                <div class="col-sm-8">
					    <?php
					    	$saved_product = (isset($existing_settings['product_id'])) ? $existing_settings['product_id'] : '' ;
					        $args = array(
					            'post_type' => 'product',
					            'posts_per_page' => -1
					            );
					        $loop = new WP_Query( $args );
					        if ( $loop->have_posts() ) { ?>
	                    		<select class="form-control input-sm" id="woo_product">
						            <?php while ( $loop->have_posts() ) : $loop->the_post(); ?>
		                    			<option value="<?php the_id(); ?>" <?php echo ($saved_product == get_the_id()) ? 'selected' : '' ; ?>><?php the_title(); ?></option>
						            <?php endwhile; ?>
	                    		</select>

					        <?php } else {
					            echo __( 'No woocommerce products found' );
					        }
					        wp_reset_postdata();
					    ?>
	                    <span class="help-block">Choose product to use for payments.</span>
	                </div>
	            </div>

	            <div class="form-group wrap_field_title">
	                <label for="field_title" class="col-sm-4 control-label">Field Title</label>
	                <div class="col-sm-8">
	                	<input id="field_title" type="text" class="form-control input-sm" name="field_title" value="<?php echo (isset($existing_settings['field_title'])) ? $existing_settings['field_title'] : '' ; ?>">
	                    <span class="help-block">Title will display on product page</span>
	                </div>
	            </div>

	            <div class="form-group">
	                <label for="packages" class="col-sm-4 control-label">Packages</label>
	                <div class="col-sm-8">
	                    <table class="table table-bordered table-woo-packages">
	                    	<tr class="rem-table-header">
	                    		<th>Package Title</th>
	                    		<th>Maximum Number of Properties</th>
	                    		<th>Price in <?php echo get_woocommerce_currency(); ?></th>
	                    		<th>Actions</th>
	                    	</tr>
	                    	<?php
	                    		if (isset($existing_settings['packages']) && is_array($existing_settings['packages'])) {
	                    			foreach ($existing_settings['packages'] as $index => $pkg) { ?>
				                    	<tr class="<?php echo ($index == 0) ? 'first-element' : '' ; ?>">
				                    		<td><input type="text" class="form-control input-sm pkg_name" value="<?php echo $pkg['pkg_name']; ?>"></td>
				                    		<td><input type="number" class="form-control input-sm count" value="<?php echo $pkg['count']; ?>"></td>
				                    		<td><input type="number" class="form-control input-sm price" value="<?php echo $pkg['price']; ?>"></td>
				                    		<td>
				                    			<div class="button-group">
					                    			<button class="btn btn-sm btn-info add-field">Add</button>
					                    			<button class="btn btn-sm btn-danger delete-field">Delete</button>
				                    			</div>
				                    		</td>
				                    	</tr>
	                    			<?php }
	                    		} else { ?>
			                    	<tr class="first-element">
			                    		<td><input type="text" class="form-control input-sm pkg_name"></td>
			                    		<td><input type="number" class="form-control input-sm count"></td>
			                    		<td><input type="number" class="form-control input-sm price"></td>
			                    		<td>
			                    			<button class="btn btn-sm btn-info add-field">Add</button>
			                    			<button class="btn btn-sm btn-danger delete-field">Delete</button>
			                    		</td>
			                    	</tr>
	                    		<?php }
	                    	?>
	                    </table>
	                    <span class="help-block">Provide number of listings that an agent can create with provided price. 0 for unlimited.</span>
	                </div>
	            </div>

	            <div class="form-group">
	            	<div class="col-sm-12 text-right">
	            		<span class="wcp-progress">Please wait...</span>
	            		<input type="submit" class="btn btn-success" value="Save Changes">
	            	</div>
	            </div>
			</div>
		</div>
	</form>
	<?php } else { ?>
		<div class="alert alert-danger">
			Please make sure WooCommerce Plugin is activated and installed on your site.
		</div>
	<?php } ?>
</div>