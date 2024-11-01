<?php

class REM_WOO_ESTATO {
	
	function __construct()
	{
		add_action( 'admin_notices', array($this, 'check_if_rem_activated') );
		add_filter( 'admin_menu', array($this, 'menu_pages') );
		add_action( 'admin_enqueue_scripts', array($this, 'admin_scripts' ) );
		add_action( 'wp_ajax_wcp_rem_save_woo_estato', array($this, 'rem_save_woo_estato' ) );

		add_action( 'woocommerce_before_add_to_cart_button', array($this, 'render_list_of_packages' ) );
		add_filter( 'woocommerce_add_cart_item_data', array($this, 'add_custom_price_to_cart_item_data' ), 20, 2 );
		add_filter( 'woocommerce_get_item_data', array($this, 'render_pkg_on_cart_checkout'), 10, 2 );
		add_action( 'woocommerce_add_order_item_meta', array($this, 'save_custom_order_meta'), 10, 3 );
		add_action( 'woocommerce_before_calculate_totals', array($this, 'set_cutom_cart_item_price' ), 20, 1);
		add_action( 'woocommerce_order_status_completed', array($this, 'order_completed' ), 10, 1);

		add_filter( 'rem_property_publish_status', array($this, 'limit_user_properties_frontend'), 20, 2 );
		add_filter( 'admin_print_footer_scripts', array( $this, 'remove_capability_publish' ) );
		add_filter( 'post_submitbox_start', array( $this, 'display_alert' ) );
		add_filter( 'rem_create_property_after_submit', array( $this, 'display_alert_front' ) );
	}

	function check_if_rem_activated() {
		if (!class_exists('WCP_Real_Estate_Management')) { ?>
		    <div class="notice notice-info is-dismissible">
		        <p>Please install and activate <a target="_blank" href="https://wordpress.org/plugins/real-estate-manager/">Real Estate Manager</a> for using <strong>Woo Estato</strong></p>
		    </div>
		<?php }
	}

	function admin_scripts($check){
        if ($check == 'rem_property_page_rem_woo_estato') {
        	wp_enqueue_script( 'sweet-alerts', REM_URL . '/assets/admin/js/sweetalert.min.js' , array('jquery'));
            wp_enqueue_style( 'rem-bs-css', REM_URL . '/assets/admin/css/bootstrap.min.css' );
            wp_enqueue_script( 'rem-woo-admin', plugin_dir_url( __FILE__ ).'js/admin.js', array('jquery') );
        }

        if ($check == 'rem_property_page_rem_woo_estato_subs') {
        	wp_enqueue_script( 'sweet-alerts', REM_URL . '/assets/admin/js/sweetalert.min.js' , array('jquery'));
            wp_enqueue_style( 'rem-bs-css', REM_URL . '/assets/admin/css/bootstrap.min.css' );
            wp_enqueue_script( 'rem-woo-subs', plugin_dir_url( __FILE__ ).'js/subscriptions.js', array('jquery') );
        }
	}

	function menu_pages($settings){
	    add_submenu_page( 'edit.php?post_type=rem_property', 'Real Estate Manager - WooCommerce Addon', __( 'Woo Estato Subscriptions', 'real-estate-manager' ), 'manage_options', 'rem_woo_estato_subs', array($this, 'render_woo_estato_subs') );
	    add_submenu_page( 'edit.php?post_type=rem_property', 'Real Estate Manager - WooCommerce Addon', __( 'Woo Estato Settings', 'real-estate-manager' ), 'manage_options', 'rem_woo_estato', array($this, 'render_woo_estato') );
	}

	function render_woo_estato(){
		include 'templates/settings.php';
	}

	function render_woo_estato_subs(){
		include 'templates/subscriptions.php';
	}

	function rem_save_woo_estato(){
		if (isset($_REQUEST)) {
			$resp = array(
				'status' => 'success',
				'message' => '',
			);
			$data_to_save = array(
				'subscription_type' => sanitize_text_field( $_REQUEST['subscription_type'] ),
				'product_id' => sanitize_text_field( $_REQUEST['product_id'] ),
				'field_title' => sanitize_text_field( $_REQUEST['field_title'] ),
				'packages' => array(),
			);
			if (isset($_REQUEST['packages']) && is_array($_REQUEST['packages'])) {
				foreach ($_REQUEST['packages'] as $pkg) {
					$data_to_save['packages'][] = array(
						'pkg_name' => sanitize_text_field( $pkg['pkg_name'] ),
						'count' => sanitize_text_field( $pkg['count'] ),
						'price' => sanitize_text_field( $pkg['price'] ),
					);
				}
			}
			if (update_option( 'rem_woo_packages', $data_to_save )) {
				$resp['status'] = 'success';
				$resp['title'] = __( 'Settings Saved!', 'woo-estato' );
				$resp['message'] = __( 'All settings are saved in database successfully', 'woo-estato' );
			} else {
				$resp['status'] = 'error';
				$resp['title'] = __( 'Oops!', 'woo-estato' );
				$resp['message'] = __( 'There was some error saving your settings, please try again', 'woo-estato' );
			}
			echo json_encode($resp);
			die(0);
		}
	}

	function render_list_of_packages(){
	    global $product;
	    $existing_settings = get_option( 'rem_woo_packages' );
	    // var_dump($product->get_id());
	    if (isset($existing_settings['subscription_type']) && $existing_settings['subscription_type'] != 'disable') {
	    	if ($existing_settings['product_id'] == $product->get_id()) {	    		
			    $curs = get_woocommerce_currency_symbol();
			    ?>
			    <div> 	
			    <input type="hidden" name="rem_package_name" class="rem_package_name">
			    <input type="hidden" name="rem_field_name" class="rem_field_name" value="<?php echo esc_attr( $existing_settings['field_title'] ); ?>">
				<p class="form-row form-row-wide validate-required" id="rem_package_price_field" data-priority="">
					<label for="rem_package_price" class=""><?php echo esc_attr( $existing_settings['field_title'] ); ?>&nbsp;<abbr class="required" title="required">*</abbr></label>
					<span class="woocommerce-input-wrapper">
						<select name="rem_package_price" id="rem_package_price" class="select" data-placeholder="" required>
							<?php
								if (is_array($existing_settings['packages'])) {
									foreach ($existing_settings['packages'] as $index => $pkg) {
										if ($index == 0) {
											$first_pkg_price = wc_price($pkg['price']);
										}
										echo '<option data-price="'.strip_tags(wc_price($pkg['price'])).'" value="'.$pkg['price'].'">'.esc_attr( $pkg['pkg_name'] ).'</option>';
									}
								}
							?>
						</select>
					</span>
				</p>	
			    </div>
			    <?php
			    ?>
			    <script type="text/javascript">
				    jQuery( function($){
				    	var price_html = $('#rem_package_price option:selected').data('price');
				    	$('.rem_package_name').val($('#rem_package_price option:selected').text());
				    	$('#rem_package_price').closest('.entry-summary').find('.price .amount').html(price_html);
				        $('.entry-summary').on('change', '#rem_package_price', function(event) {
				        	event.preventDefault();
				        	var val = $(this).find(':selected').data('price');
				    		$('#rem_package_price').closest('.entry-summary').find('.price .amount').html(val);
				    		$('.rem_package_name').val($(this).find(':selected').text());
				        });
				    });
			    </script>
			    <?php
	    	}
	    }
	}

	function add_custom_price_to_cart_item_data( $cart_item_data, $product_id ){
	    if( ! isset($_POST['rem_package_price'])  || $_POST['rem_package_price'] == '' )
	        return $cart_item_data;
	    $cart_item_data['rem_package_price'] = (float) sanitize_text_field( $_POST['rem_package_price'] );
	    $cart_item_data['rem_field_name'] = sanitize_text_field( $_POST['rem_field_name'] );
	    $cart_item_data['rem_package_name'] = sanitize_text_field( $_POST['rem_package_name'] );
	    return $cart_item_data;
	}

	function render_pkg_on_cart_checkout( $cart_data, $cart_item = null ){
	    $custom_items = array();

	    if( !empty( $cart_data ) ) {
	        $custom_items = $cart_data;
	    }
	    if( isset( $cart_item["rem_package_price"] ) ) {
	        $custom_items[] = array(
	        	"name" => $cart_item['rem_field_name'],
	        	"value" => $cart_item['rem_package_name'].' ('.wc_price($cart_item["rem_package_price"]).')'
	        );
	    }
	    return $custom_items;
	}

	function save_custom_order_meta( $item_id, $values, $cart_item_key ) {
	    if( isset( $values["rem_package_price"] ) ) {
	        wc_add_order_item_meta( $item_id, "rem_package_price", $values["rem_package_price"] );
	        wc_add_order_item_meta( $item_id, "rem_field_name", $values["rem_field_name"] );
	        wc_add_order_item_meta( $item_id, "rem_package_name", $values["rem_package_name"] );
	    }
	}	

	function set_cutom_cart_item_price( $cart ) {
	    if ( is_admin() && ! defined( 'DOING_AJAX' ) )
	        return;

	    if ( did_action( 'woocommerce_before_calculate_totals' ) >= 2 )
	        return;

	    foreach (  $cart->get_cart() as $cart_item ) {
	        if ( isset( $cart_item['rem_package_price'] ) )
	            $cart_item['data']->set_price( $cart_item['rem_package_price'] );
	    }
	}

	function order_completed($order_id){
		$order = wc_get_order( $order_id );
		$user_id = $order->get_user_id();
		$order_items = $order->get_items();		
		$existing_settings = get_option( 'rem_woo_packages' );

		// Loop through all purchased items to find the rem package item
		if (is_array($order_items)) {
			foreach( $order_items as $item_id => $item_product ){

			    $product_id = $item_product->get_product_id();

			    if (isset($existing_settings['product_id']) && $existing_settings['product_id'] == $product_id ) {

					$rem_package_price = wc_get_order_item_meta($item_id, 'rem_package_price', true);
					$rem_field_name = wc_get_order_item_meta($item_id, 'rem_field_name', true);
					$rem_package_name = wc_get_order_item_meta($item_id, 'rem_package_name', true);

					if (is_array($existing_settings['packages'])) {
						foreach ($existing_settings['packages'] as $index => $pkg) {
							if ($pkg['price'] == $rem_package_price) {
								$this->set_agent_package($user_id, $pkg);
							}
						}
					}					
			    }
				echo $rem_package_price;
			}
		}
	}

	function set_agent_package($user_id, $pkg){
		$rem_packages = get_user_meta( $user_id, 'rem_packages', true );

		$data = array(
			'time' 	=> time(),
			'count' => sanitize_text_field( $pkg['count'] ),
			'price' => sanitize_text_field( $pkg['price'] ),
			'name' 	=> sanitize_text_field( $pkg['pkg_name'] ),
		);
		update_user_meta( $user_id, 'rem_latest_package', $data );

		if ($rem_packages != '' && is_array($rem_packages)) {
			$rem_packages[] = $data;
		} else {
			$rem_packages = array($data);
		}

		update_user_meta( $user_id, 'rem_packages', $rem_packages );
	}

	function limit_user_properties_frontend($status, $agent_id){
		if ($this->can_publish_properties($agent_id) && $this->is_subscription_valid($agent_id)) {
			return $status;
		} else {
			return 'draft';
		}
	}

	function remove_capability_publish(){
		global $post;

		$currID = is_user_logged_in() ? get_current_user_id() : 0;
		$user = wp_get_current_user();
		if ( $currID && in_array( 'rem_property_agent', (array) $user->roles ) && $currID != 1) {
			if (isset($post->ID) && get_post_status( $post->ID ) != 'publish') {
				if (!$this->can_publish_properties($currID) || !$this->is_subscription_valid($currID)) {
					?>
					<script type="text/javascript">
						jQuery(document).ready(function($){$('#publish').remove();});
					</script>
					<?php
				}
			}
		}
	}

	function display_alert(){
		global $post;
		$currID = is_user_logged_in() ? get_current_user_id() : 0;
		$user = wp_get_current_user();
		if ( $currID && in_array( 'rem_property_agent', (array) $user->roles ) && $currID != 1) {
			if (isset($post->ID) && get_post_status( $post->ID ) != 'publish') {
				if (!$this->can_publish_properties($currID) || !$this->is_subscription_valid($currID)) {
					_e( 'Your limit to publish properties is over.', 'woo-estato' );
				}
			}
		}
	}

	function display_alert_front(){
		$currID = is_user_logged_in() ? get_current_user_id() : 0;
		$user = wp_get_current_user();
		if ( $currID && in_array( 'rem_property_agent', (array) $user->roles ) && $currID != 1) {
			if (1) {
				if (!$this->can_publish_properties($currID) || !$this->is_subscription_valid($currID)) {
					_e( 'Your limit to publish properties is over.', 'woo-estato' );
				}
			}
		}
	}

	function can_publish_properties($agent_id){
		$pkg_data = get_user_meta( $agent_id, 'rem_latest_package', true );
		if ($pkg_data != '' && is_array($pkg_data)) {
			$max_properties = $pkg_data['count'];
			$active_properties = count_user_posts( $agent_id, 'rem_property' );
			if ($active_properties<$max_properties) {
				return true;
			} else {
				return false;
			}
		} else {
			return true;
		}
	}

	function is_subscription_valid($agent_id){
		$pkg_data = get_user_meta( $agent_id, 'rem_latest_package', true );
		if ($pkg_data != '' && is_array($pkg_data)) {
			$settings = get_option( 'rem_woo_packages' );
			$s_type = $settings['subscription_type'];
			$valid_time = '';
			if ($s_type == 'monthly') {
				$valid_time = strtotime('+30 days', $pkg_data['time']);
			}
			if ($s_type == 'annually') {
				$valid_time = strtotime('+365 days', $pkg_data['time']);
			}
			if( $valid_time < time() ){
			    // Date is passed
			    return false;
			} else {
			    // date is in the future
			    return true;
			}
		} else {
			return true;
		}
	}
}
?>