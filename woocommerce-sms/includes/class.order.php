<?php

class WoocommerceIR_Order_SMS {

	public function __construct() {

		add_action( 'woocommerce_after_order_notes', array( $this, 'smsCheckoutFields' ) );
		add_action( 'woocommerce_checkout_process', array( $this, 'smsCheckoutFieldsValidation' ) );
		add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'saveSmsMeta' ) );
		add_action( 'woocommerce_order_status_changed', array( $this, 'smsStatusChanged' ), 99, 3 );
		add_filter( 'woocommerce_checkout_fields', array( $this, 'smsCheckoutLabel' ), 0 );

		add_filter( 'woocommerce_form_field_persian_woo_sms_multiselect', array(
			'WoocommerceIR_Helper',
			'multiSelectAndCheckbox'
		), 11, 4 );

		add_filter( 'woocommerce_form_field_persian_woo_sms_multicheckbox', array(
			'WoocommerceIR_Helper',
			'multiSelectAndCheckbox'
		), 11, 4 );


		if ( is_admin() ) {

			add_action( 'woocommerce_admin_order_data_after_order_details', array( $this, 'orderDetails' ) );
			add_action( 'woocommerce_admin_order_data_after_billing_address', array( $this, 'orderMetas' ), 10, 1 );

			add_action( 'wp_ajax_change_sms_text', array( $this, 'changeSmsText' ) );
			add_action( 'wp_ajax_nopriv_change_sms_text', array( $this, 'changeSmsText' ) );
		}
		add_action( 'wp_enqueue_scripts', array( $this, 'script' ) );
	}

	public function script() {

		if ( ! function_exists( 'wc_enqueue_js' ) ) {
			return;
		}

		if ( PWooSMS()->Options( 'allow_buyer_select_status', 'sms_buyer_settings', 'no' ) == 'yes' ) {

			wp_register_script( 'persian-woo-sms-frontend', PS_WOO_SMS_PLUGIN_PATH . '/assets/js/status_selector_front_end.js', array( 'jquery' ), PS_WOO_SMS_VERSION, true );
			wp_localize_script( 'persian-woo-sms-frontend', 'persian_woo_sms',
				array(
					'ajax_url'                  => admin_url( 'admin-ajax.php' ),
					'chosen_placeholder_single' => __( 'گزینه مورد نظر را انتخاب نمایید', 'persianwoosms' ),
					'chosen_placeholder_multi'  => __( 'گزینه های مورد نظر را انتخاب نمایید', 'persianwoosms' ),
					'chosen_no_results_text'    => __( 'هیچ گزینه ای وجود ندارد .', 'persianwoosms' ),
				)
			);

			wp_enqueue_script( 'persian-woo-sms-frontend' );

			if ( PWooSMS()->Options( 'force_enable_buyer', 'sms_buyer_settings', 'no' ) != 'yes' && PWooSMS()->Options( 'allow_buyer_select_status', 'sms_buyer_settings', 'no' ) == 'yes' ) {
				wc_enqueue_js( "
					jQuery( '#buyer_sms_status_field' ).hide();
					jQuery( 'input[name=buyer_sms_notify]' ).change( function () {
						if ( jQuery( this ).is( ':checked' ) )
							jQuery( '#buyer_sms_status_field' ).show();
						else
							jQuery( '#buyer_sms_status_field' ).hide();
					} ).change();
				" );
			}
		}

		if ( PWooSMS()->Options( 'force_enable_buyer', 'sms_buyer_settings', 'no' ) != 'yes' && PWooSMS()->Options( 'allow_buyer_select_pm_type', 'sms_buyer_settings', 'no' ) == 'yes' ) {
			wc_enqueue_js( "
				jQuery( '#buyer_pm_type_field' ).hide();
				jQuery( 'input[name=buyer_sms_notify]' ).change( function () {
					if ( jQuery( this ).is( ':checked' ) )
						jQuery( '#buyer_pm_type_field' ).show();
					else
						jQuery( '#buyer_pm_type_field' ).hide();
				} ).change();
			" );
		}

	}


	function smsCheckoutLabel( $fields ) {

		if ( ! empty( $fields['billing']['billing_phone']['label'] ) ) {
			$fields['billing']['billing_phone']['label'] = PWooSMS()->Options( 'buyer_phone_label', 'sms_buyer_settings', '' ) ?: $fields['billing']['billing_phone']['label'];
		}

		return $fields;
	}

	function smsCheckoutFields( $checkout ) {

		if ( PWooSMS()->Options( 'enable_buyer', 'sms_buyer_settings', 'off' ) == 'off' || count( (array) PWooSMS()->GetAllowedStatuses() ) < 0 ) {
			return;
		}

		echo '<div id="smsCheckoutFields">';
		$checkbox_text = PWooSMS()->Options( 'buyer_checkbox_text', 'sms_buyer_settings', 'مرا با ارسال پیامک از وضعیت سفارش آگاه کن' );
		$required      = ( PWooSMS()->Options( 'force_enable_buyer', 'sms_buyer_settings', 'no' ) == 'yes' ) ? true : false;
		if ( ! $required ) {
			woocommerce_form_field( 'buyer_sms_notify',
				array(
					'type'        => 'checkbox',
					'class'       => array( 'buyer-sms-notify form-row-wide' ),
					'label'       => __( $checkbox_text, 'persianwoosms' ) ? __( $checkbox_text, 'persianwoosms' ) : '',
					'label_class' => '',
					'required'    => $required,
				), $checkout->get_value( 'buyer_sms_notify' )
			);
		}

		if ( PWooSMS()->Options( 'allow_buyer_select_pm_type', 'sms_buyer_settings', 'no' ) == 'yes' ) {
			if ( ( PWooSMS()->Options( 'enable_sms', 'sms_main_settings', 'no' ) == 'on' && in_array( 'sms', ( (array) PWooSMS()->Options( 'pm_type_buyer', 'sms_buyer_settings', array() ) ) ) )
			     && ( PWooSMS()->Options( 'enable_tg', 'sms_main_settings', 'no' ) == 'on' && in_array( 'tg', ( (array) PWooSMS()->Options( 'pm_type_buyer', 'sms_buyer_settings', array() ) ) ) ) ) {
				$options = array( 'sms' => 'اس ام اس', 'tg' => 'تلگرام' );
			} else if ( PWooSMS()->Options( 'enable_sms', 'sms_main_settings', 'no' ) == 'on' && in_array( 'sms', ( (array) PWooSMS()->Options( 'pm_type_buyer', 'sms_buyer_settings', array() ) ) ) ) {
				$options = array( 'sms' => 'اس ام اس' );
			} else if ( PWooSMS()->Options( 'enable_tg', 'sms_main_settings', 'no' ) == 'on' && in_array( 'tg', ( (array) PWooSMS()->Options( 'pm_type_buyer', 'sms_buyer_settings', array() ) ) ) ) {
				$options = array( 'tg' => 'تلگرام' );
			} else {
				$options = array();
			}

			woocommerce_form_field( 'buyer_pm_type', array(
				'type'        => 'persian_woo_sms_multicheckbox',
				'class'       => array( 'buyer-sms-status form-row-wide wc-enhanced-select' ),
				'label'       => PWooSMS()->Options( 'buyer_select_pm_type_text', 'sms_buyer_settings', '' ),
				'options'     => $options,
				'required'    => false,
				'description' => '',
			), $checkout->get_value( 'buyer_pm_type' )
			);
		}

		if ( PWooSMS()->Options( 'allow_buyer_select_status', 'sms_buyer_settings', 'no' ) == 'yes' ) {
			$multiselect_text        = PWooSMS()->Options( 'buyer_select_status_text_top', 'sms_buyer_settings', '' );
			$multiselect_text_bellow = PWooSMS()->Options( 'buyer_select_status_text_bellow', 'sms_buyer_settings', '' );
			$required                = ( PWooSMS()->Options( 'force_buyer_select_status', 'sms_buyer_settings', 'no' ) == 'yes' ) ? true : false;
			$mode                    = ( PWooSMS()->Options( 'buyer_status_mode', 'sms_buyer_settings', 'selector' ) == 'selector' ) ? 'persian_woo_sms_multiselect' : 'persian_woo_sms_multicheckbox';
			woocommerce_form_field( 'buyer_sms_status', array(
				'type'        => $mode ? $mode : '',
				'class'       => array( 'buyer-sms-status form-row-wide wc-enhanced-select' ),
				'label'       => $multiselect_text ? $multiselect_text : '',
				'options'     => PWooSMS()->GetAllowedStatuses(),
				'required'    => $required,
				'description' => $multiselect_text_bellow ? ( $multiselect_text_bellow ) : '',
			), $checkout->get_value( 'buyer_sms_status' )
			);
		}

		echo '</div>';
	}


	function smsCheckoutFieldsValidation() {

		if ( ! empty( $_POST['billing_phone'] ) ) {
			$_POST['billing_phone'] = PWooSMS()->EnglishNumberMobile( $_POST['billing_phone'] );
		}

		if ( PWooSMS()->Options( 'enable_buyer', 'sms_buyer_settings', 'off' ) == 'off' || count( (array) PWooSMS()->GetAllowedStatuses() ) < 0 ) {
			return;
		}

		if ( PWooSMS()->Options( 'force_enable_buyer', 'sms_buyer_settings', 'no' ) != 'yes' && ! empty( $_POST['buyer_sms_notify'] ) && empty( $_POST['billing_phone'] ) ) {
			wc_add_notice( __( 'برای دریافت پیامک می بایست فیلد شماره تلفن را پر نمایید .' ), 'error' );
		}

		if ( ( PWooSMS()->Options( 'force_enable_buyer', 'sms_buyer_settings', 'no' ) == 'yes' || ( PWooSMS()->Options( 'force_enable_buyer', 'sms_buyer_settings', 'no' ) != 'yes' && ! empty( $_POST['buyer_sms_notify'] ) ) )
		     && ! PWooSMS()->validateMobile( $_POST['billing_phone'] ) ) {
			wc_add_notice( __( 'شماره موبایل معتبر نیست .' ), 'error' );
		}

		if ( PWooSMS()->Options( 'allow_buyer_select_status', 'sms_buyer_settings', 'no' ) == 'yes'
		     && PWooSMS()->Options( 'force_buyer_select_status', 'sms_buyer_settings', 'no' ) == 'yes'
		     && ( ( PWooSMS()->Options( 'force_enable_buyer', 'sms_buyer_settings', 'no' ) != 'yes' && ! empty( $_POST['buyer_sms_notify'] ) ) || PWooSMS()->Options( 'force_enable_buyer', 'sms_buyer_settings', 'no' ) == 'yes' )
		     && empty( $_POST['buyer_sms_status'] ) ) {
			wc_add_notice( __( 'انتخاب حداقل یکی از وضعیت های سفارش دریافت پیامک الزامی است .' ), 'error' );
		}

		if ( PWooSMS()->Options( 'allow_buyer_select_pm_type', 'sms_buyer_settings', 'no' ) == 'yes'
		     && ( ( PWooSMS()->Options( 'force_enable_buyer', 'sms_buyer_settings', 'no' ) != 'yes' && ! empty( $_POST['buyer_sms_notify'] ) ) || PWooSMS()->Options( 'force_enable_buyer', 'sms_buyer_settings', 'no' ) == 'yes' )
		     && empty( $_POST['buyer_pm_type'] ) ) {
			wc_add_notice( __( 'انتخاب حداقل یکی از روش های دریافت پیامک الزامی است .' ), 'error' );
		}
	}

	function saveSmsMeta( $order_id ) {

		if ( PWooSMS()->Options( 'enable_buyer', 'sms_buyer_settings', 'off' ) == 'off' || count( (array) PWooSMS()->GetAllowedStatuses() ) < 0 ) {
			return;
		}

		update_post_meta( $order_id, '_force_enable_buyer', PWooSMS()->Options( 'force_enable_buyer', 'sms_buyer_settings', 'no' ) );
		update_post_meta( $order_id, '_allow_buyer_select_status', PWooSMS()->Options( 'allow_buyer_select_status', 'sms_buyer_settings', 'no' ) );
		update_post_meta( $order_id, '_allow_buyer_select_pm_type', PWooSMS()->Options( 'allow_buyer_select_pm_type', 'sms_buyer_settings', 'no' ) );

		if ( ! empty( $_POST['buyer_sms_notify'] ) ) {
			update_post_meta( $order_id, '_buyer_sms_notify', 'yes' );
		} else {
			update_post_meta( $order_id, '_buyer_sms_notify', 'no' );
		}

		if ( PWooSMS()->Options( 'force_enable_buyer', 'sms_buyer_settings', 'no' ) == 'yes' ) {
			update_post_meta( $order_id, '_buyer_sms_notify', 'yes' );
		}

		if ( ! empty( $_POST['buyer_sms_status'] ) ) {

			$_buyer_sms_status = is_array( $_POST['buyer_sms_status'] ) ? array_map( 'sanitize_text_field', $_POST['buyer_sms_status'] ) : sanitize_text_field( $_POST['buyer_sms_status'] );

			update_post_meta( $order_id, '_buyer_sms_status', $_buyer_sms_status );
		} else if ( get_post_meta( $order_id, '_buyer_sms_status' ) ) {
			delete_post_meta( $order_id, '_buyer_sms_status' );
		}


		if ( ! empty( $_POST['buyer_pm_type'] ) ) {

			$_buyer_pm_type = is_array( $_POST['buyer_pm_type'] ) ? array_map( 'sanitize_text_field', $_POST['buyer_pm_type'] ) : sanitize_text_field( $_POST['buyer_pm_type'] );

			update_post_meta( $order_id, '_buyer_pm_type', $_buyer_pm_type );
		} else if ( get_post_meta( $order_id, '_buyer_pm_type' ) ) {
			delete_post_meta( $order_id, '_buyer_pm_type' );
		}


	}

	function orderMetas( $order ) {

		if ( PWooSMS()->Options( 'enable_buyer', 'sms_buyer_settings', 'off' ) == 'off' || count( (array) PWooSMS()->GetAllowedStatuses() ) < 0 ) {
			return;
		}

		$want_notification = get_post_meta( PWooSMS()->OrderId( $order ), '_buyer_sms_notify', true );
		$display_info      = ( isset( $want_notification ) && ! empty( $want_notification ) && $want_notification == 'yes' ) ? 'بله' : 'خیر';
		$old_status        = $order->get_status();

		$post        = get_post( PWooSMS()->OrderId( $order ) );
		$post_status = is_object( $post ) && ! empty( $post->post_status ) ? $post->post_status : '';

		if ( ( get_post_meta( PWooSMS()->OrderId( $order ), '_force_enable_buyer', true ) == 'yes' ) || ! PWooSMS()->OrderId( $order ) || ( empty ( $old_status ) || ! isset ( $old_status ) || $old_status == 'draft' || ! in_array( $post_status, array_keys( wc_get_order_statuses() ) ) ) ) {
			echo '<p>خریدار حق انتخاب دریافت یا عدم دریافت پیامک را ندارد .</p>';
		} else {
			echo '<p>آیا خریدار مایل به دریافت پیامک هست : ' . $display_info . '</p>';
		}

		if ( get_post_meta( PWooSMS()->OrderId( $order ), '_allow_buyer_select_status', true ) == 'yes' ) {
			$buyer_sms_status = get_post_meta( PWooSMS()->OrderId( $order ), '_buyer_sms_status', true );
			$display_statuses = ( isset( $buyer_sms_status ) && ! empty( $buyer_sms_status ) ) ? $buyer_sms_status : array();

			echo '<p>وضعیت های انتخابی توسط خریدار برای دریافت پیامک : ';
			if ( count( $display_statuses ) >= 0 && ! empty( $display_statuses ) ) {
				$statuses = '';
				foreach ( (array) $display_statuses as $status ) {
					$statuses .= wc_get_order_status_name( $status ) . ' - ';
				}
				echo substr( $statuses, 0, - 3 );
			} else {
				echo 'وضعیتی انتخاب نشده است';
			}

		} else {
			echo '<p>خریدار حق انتخاب وضعیت های دریافت پیامک را ندارد .';
		}
		echo '</p>';

		if ( get_post_meta( PWooSMS()->OrderId( $order ), '_allow_buyer_select_pm_type', true ) == 'yes' ) {
			$buyer_pm_type = get_post_meta( PWooSMS()->OrderId( $order ), '_buyer_pm_type', true );
			$display_types = ( isset( $buyer_pm_type ) && ! empty( $buyer_pm_type ) ) ? $buyer_pm_type : array();

			echo '<p>روش انتخابی دریافت پیام توسط خریدار : ';
			if ( count( $display_types ) >= 0 && ! empty( $display_types ) ) {
				$types = '';
				foreach ( (array) $display_types as $type ) {
					$types .= ( $type == 'sms' ? 'اس ام اس' : ( $type == 'tg' ? 'تلگرام' : '' ) ) . ' - ';
				}
				echo substr( $types, 0, - 3 );
			} else {
				echo 'روشی انتخاب نشده است';
			}

		} else {
			echo '<p>خریدار حق انتخاب روش های دریافت پیامک را ندارد .';
		}
		echo '</p>';

	}

	public function smsStatusChanged( $order_id, $old_status, $new_status ) {

		if ( ! $order_id ) {
			return;
		}


		$active_sms_gateway = PWooSMS()->Options( 'sms_gateway', 'sms_main_settings', '' );
		$active_tg_gateway  = PWooSMS()->Options( 'tg_gateway', 'sms_main_settings', '' );

		$order_page = ( ! empty( $_POST['shop_order_hannan'] ) && $_POST['shop_order_hannan'] == 'true' ) ? true : false;

		$order          = new WC_Order( $order_id );
		$admin_sms_data = $buyer_sms_data = array();

		$product_list  = PWooSMS()->GetProdcutLists( $order );
		$all_items     = $product_list['names'] . '__vsh__' . $product_list['names_qty'];
		$buyer_pm_type = array();

		// خریدار		 
		if ( ( ! $order_page && $this->canGetPm( $order_id, $new_status ) ) || ( $order_page && ( ! empty( $_POST['sms_order_send'] ) || ! empty( $_POST['tg_order_send'] ) ) ) ) {
			$buyer_sms_data['number'] = explode( ',', get_post_meta( $order_id, '_billing_phone', true ) );

			$buyer_sms_data['number'] = PWooSMS()->EnglishNumberMobile( $buyer_sms_data['number'] );

			$buyer_sms_body             = $order_page ? ( isset( $_POST['sms_order_text'] ) ? esc_textarea( $_POST['sms_order_text'] ) : '' ) : PWooSMS()->Options( 'sms_body_' . $new_status, 'sms_buyer_settings', '' );
			$buyer_sms_data['sms_body'] = PWooSMS()->ReplaceShortCodes( $buyer_sms_body, $new_status, $order_id, $order, $all_items, '' );
			$buyer_pm_type              = get_post_meta( $order_id, '_buyer_pm_type', true ) ? get_post_meta( $order_id, '_buyer_pm_type', true ) : array();
			if ( $order_page || PWooSMS()->Options( 'allow_buyer_select_pm_type', 'sms_buyer_settings', 'no' ) != 'yes' ) {
				$buyer_pm_type = array( 'sms', 'tg' );
			}
		}

		if ( ( ! $order_page && $this->canGetPm( $order_id, $new_status ) ) || ( $order_page && ! empty( $_POST['sms_order_send'] ) ) ) {
			if ( PWooSMS()->Options( 'enable_sms', 'sms_main_settings', 'no' ) == 'on'
			     && in_array( 'sms', ( (array) PWooSMS()->Options( 'pm_type_buyer', 'sms_buyer_settings', array() ) ) )
			     && in_array( 'sms', ( $buyer_pm_type ) ) ) {
				$buyer_response_sms = ( ! empty( $active_sms_gateway ) && $active_sms_gateway != 'none' ) ? WoocommerceIR_Gateways_SMS::init()->$active_sms_gateway( $buyer_sms_data ) : false;
				if ( $buyer_response_sms ) {
					$order->add_order_note( sprintf( 'پیامک با موفقیت به خریدار با شماره %s ارسال گردید', get_post_meta( $order_id, '_billing_phone', true ) ) );
				} else {
					$order->add_order_note( sprintf( 'پیامک بخاطر خطا به خریدار با شماره %s ارسال نشد', get_post_meta( $order_id, '_billing_phone', true ) ) );
				}
			}
		}

		if ( ( ! $order_page && $this->canGetPm( $order_id, $new_status ) ) || ( $order_page && ! empty( $_POST['tg_order_send'] ) ) ) {
			if ( PWooSMS()->Options( 'enable_tg', 'sms_main_settings', 'no' ) == 'on'
			     && in_array( 'tg', ( (array) PWooSMS()->Options( 'pm_type_buyer', 'sms_buyer_settings', array() ) ) )
			     && in_array( 'tg', ( $buyer_pm_type ) ) ) {
				$buyer_response_tg = ( ! empty( $active_tg_gateway ) && $active_tg_gateway != 'none' ) ? WoocommerceIR_Gateways_SMS::init()->$active_tg_gateway( $buyer_sms_data ) : false;
				if ( $buyer_response_tg ) {
					$order->add_order_note( sprintf( 'تلگرام با موفقیت به خریدار با شماره %s ارسال گردید', get_post_meta( $order_id, '_billing_phone', true ) ) );
				} else {
					$order->add_order_note( sprintf( 'تلگرام بخاطر خطا به خریدار با شماره %s ارسال نشد', get_post_meta( $order_id, '_billing_phone', true ) ) );
				}
			}
		}


		// مدیر کل
		if ( PWooSMS()->Options( 'enable_super_admin_sms', 'sms_super_admin_settings', 'on' ) == 'on' ) {

			$super_admin_order_status = (array) PWooSMS()->Options( 'super_admin_order_status', 'sms_super_admin_settings', array() );
			if ( in_array( $new_status, $super_admin_order_status ) ) {

				$super_admin_sms_body             = PWooSMS()->Options( 'super_admin_sms_body_' . $new_status, 'sms_super_admin_settings', '' );
				$super_admin_sms_data['sms_body'] = PWooSMS()->ReplaceShortCodes( $super_admin_sms_body, $new_status, $order_id, $order, $all_items, '' );

				if ( PWooSMS()->Options( 'enable_sms', 'sms_main_settings', 'no' ) == 'on' && in_array( 'sms', ( (array) PWooSMS()->Options( 'pm_type_super_admin', 'sms_super_admin_settings', array() ) ) ) ) {
					$super_admin_sms_data['number'] = explode( ',', PWooSMS()->Options( 'super_admin_phone', 'sms_super_admin_settings', '' ) );

					$super_admin_sms_data['number'] = PWooSMS()->EnglishNumberMobile( $super_admin_sms_data['number'] );

					$super_admin_response_sms = ( ! empty( $active_sms_gateway ) && $active_sms_gateway != 'none' ) ? WoocommerceIR_Gateways_SMS::init()->$active_sms_gateway( $super_admin_sms_data ) : false;
					if ( $super_admin_response_sms ) {
						$order->add_order_note( sprintf( 'پیامک با موفقیت به مدیر کل با شماره %s ارسال گردید', PWooSMS()->Options( 'super_admin_phone', 'sms_super_admin_settings', '' ) ) );
					} else {
						$order->add_order_note( sprintf( 'پیامک بخاطر خطا به مدیر کل با شماره %s ارسال نشد', PWooSMS()->Options( 'super_admin_phone', 'sms_super_admin_settings', '' ) ) );
					}
				}

				if ( PWooSMS()->Options( 'enable_tg', 'sms_main_settings', 'no' ) == 'on' && in_array( 'tg', ( (array) PWooSMS()->Options( 'pm_type_super_admin', 'sms_super_admin_settings', array() ) ) ) ) {
					$super_admin_sms_data['number'] = explode( ',', PWooSMS()->Options( 'super_admin_phone_tg', 'sms_super_admin_settings', '' ) );

					$super_admin_sms_data['number'] = PWooSMS()->EnglishNumberMobile( $super_admin_sms_data['number'] );

					$super_admin_response_tg = ( ! empty( $active_tg_gateway ) && $active_tg_gateway != 'none' ) ? WoocommerceIR_Gateways_SMS::init()->$active_tg_gateway( $super_admin_sms_data ) : false;
					if ( $super_admin_response_tg ) {
						$order->add_order_note( sprintf( 'تلگرام با موفقیت به مدیر کل با شماره %s ارسال گردید', PWooSMS()->Options( 'super_admin_phone_tg', 'sms_super_admin_settings', '' ) ) );
					} else {
						$order->add_order_note( sprintf( 'تلگرام بخاطر خطا به مدیر کل با شماره %s ارسال نشد', PWooSMS()->Options( 'super_admin_phone_tg', 'sms_super_admin_settings', '' ) ) );
					}
				}

			}
		}


		// مدیر محصول
		if ( PWooSMS()->Options( 'enable_product_admin_sms', 'sms_product_admin_settings', 'on' ) == 'on' ) {

			$product_ids = $product_list['ids'];
			$product_ids = explode( ',', $product_ids );

			unset( $product_admin_numbers_sms );
			$product_admin_numbers_sms = array();
			unset( $product_admin_numbers_tg );
			$product_admin_numbers_tg = array();

			foreach ( (array) $product_ids as $product_id ) {
				$admin_datas = maybe_unserialize( get_post_meta( $product_id, '_hannanstd_woo_products_tabs', true ) );
				foreach ( (array) $admin_datas as $admin_data ) {

					$admin_statuses = array();
					if ( isset( $admin_data['content'] ) ) {
						$admin_statuses = explode( '-sv-', $admin_data['content'] );
					}

					if ( in_array( $new_status, $admin_statuses ) && ( in_array( 'sms', $admin_statuses ) || ( ! in_array( 'sms', $admin_statuses ) && ! in_array( 'tg', $admin_statuses ) ) ) ) {
						if ( empty( $product_admin_numbers_sms[ $admin_data['title'] ] ) ) {
							$product_admin_numbers_sms[ $admin_data['title'] ] = get_the_title( $product_id );
						} else {
							$product_admin_numbers_sms[ $admin_data['title'] ] = $product_admin_numbers_sms[ $admin_data['title'] ] . '-' . get_the_title( $product_id );
						}
					}

					if ( in_array( $new_status, $admin_statuses ) && in_array( 'tg', $admin_statuses ) ) {
						if ( empty( $product_admin_numbers_tg[ $admin_data['title'] ] ) ) {
							$product_admin_numbers_tg[ $admin_data['title'] ] = get_the_title( $product_id );
						} else {
							$product_admin_numbers_tg[ $admin_data['title'] ] = $product_admin_numbers_tg[ $admin_data['title'] ] . '-' . get_the_title( $product_id );
						}
					}
				}
			}


			if ( ! empty( $product_admin_numbers_sms ) && count( $product_admin_numbers_sms ) > 0
			     && ( PWooSMS()->Options( 'enable_sms', 'sms_main_settings', 'no' ) == 'on' && in_array( 'sms', ( (array) PWooSMS()->Options( 'pm_type_product_admin', 'sms_product_admin_settings', array() ) ) ) ) ) {
				foreach ( (array) $product_admin_numbers_sms as $number => $vendor_items ) {
					if ( strlen( $number ) > 5 ) {
						$admin_sms_data['number'] = explode( ',', $number );

						$admin_sms_data['number'] = PWooSMS()->EnglishNumberMobile( $admin_sms_data['number'] );

						$product_admin_sms_body     = PWooSMS()->Options( 'product_admin_sms_body_' . $new_status, 'sms_product_admin_settings', '' );
						$admin_sms_data['sms_body'] = PWooSMS()->ReplaceShortCodes( $product_admin_sms_body, $new_status, $order_id, $order, $all_items, $vendor_items );
						$admin_response_sms         = ( ! empty( $active_sms_gateway ) && $active_sms_gateway != 'none' ) ? WoocommerceIR_Gateways_SMS::init()->$active_sms_gateway( $admin_sms_data ) : false;
						if ( $admin_response_sms ) {
							$order->add_order_note( sprintf( 'پیامک با موفقیت به مدیر محصول با شماره %s ارسال گردید', $number ) );
						} else {
							$order->add_order_note( sprintf( 'پیامک بخاطر خطا به مدیر محصول با شماره %s ارسال نشد', $number ) );
						}
					}
				}
			}


			if ( ! empty( $product_admin_numbers_tg ) && count( $product_admin_numbers_tg ) > 0
			     && ( PWooSMS()->Options( 'enable_tg', 'sms_main_settings', 'no' ) == 'on' && in_array( 'tg', ( (array) PWooSMS()->Options( 'pm_type_product_admin', 'sms_product_admin_settings', array() ) ) ) ) ) {
				foreach ( (array) $product_admin_numbers_tg as $number => $vendor_items ) {
					if ( strlen( $number ) > 5 ) {
						$admin_sms_data['number'] = explode( ',', $number );

						$admin_sms_data['number'] = PWooSMS()->EnglishNumberMobile( $admin_sms_data['number'] );

						$product_admin_tg_body      = PWooSMS()->Options( 'product_admin_sms_body_' . $new_status, 'sms_product_admin_settings', '' );
						$admin_sms_data['sms_body'] = PWooSMS()->ReplaceShortCodes( $product_admin_tg_body, $new_status, $order_id, $order, $all_items, $vendor_items );
						$admin_response_tg          = ( ! empty( $active_tg_gateway ) && $active_tg_gateway != 'none' ) ? WoocommerceIR_Gateways_SMS::init()->$active_tg_gateway( $admin_sms_data ) : false;
						if ( $admin_response_tg ) {
							$order->add_order_note( sprintf( 'تلگرام با موفقیت به مدیر محصول با شماره %s ارسال گردید', $number ) );
						} else {
							$order->add_order_note( sprintf( 'تلگرام بخاطر خطا به مدیر محصول با شماره %s ارسال نشد', $number ) );
						}
					}
				}
			}


		}

	}

	function orderDetails( $order ) {
		if ( PWooSMS()->Options( 'enable_buyer', 'sms_buyer_settings', 'no' ) == 'on' && ( ( PWooSMS()->Options( 'enable_sms', 'sms_main_settings', 'no' ) == 'on' && in_array( 'sms', ( (array) PWooSMS()->Options( 'pm_type_buyer', 'sms_buyer_settings', array() ) ) ) )
		                                                                                   || ( PWooSMS()->Options( 'enable_tg', 'sms_main_settings', 'no' ) == 'on' && in_array( 'tg', ( (array) PWooSMS()->Options( 'pm_type_buyer', 'sms_buyer_settings', array() ) ) ) ) ) ) { ?>
            <script type="text/javascript">
                jQuery(document).ready(function ($) {
                    $("#order_status").change(function () {
                        $("#hannanstd_sms_textbox").html("<img src=\"<?php echo PS_WOO_SMS_PLUGIN_PATH ?>/assets/images/ajax-loader.gif\" />");
                        var order_status = $("#order_status").val();
                        $.ajax({
                            url: "<?php echo admin_url( "admin-ajax.php" ) ?>",
                            type: "post",
                            data: {
                                action: "change_sms_text",
                                security: "<?php echo wp_create_nonce( "change-sms-text" ) ?>",
                                order_id: "<?php echo PWooSMS()->OrderId( $order ); ?>",
                                order_status: order_status
                            },
                            success: function (response) {
                                $("#hannanstd_sms_textbox").html(response);
                            }
                        });
                    });
                });
            </script>
            <p class="form-field form-field-wide" id="hannanstd_sms_textbox_p">
                <span id="hannanstd_sms_textbox" class="hannanstd_sms_textbox"></span>
            </p>
			<?php
		}
	}

	function changeSmsText() {

		check_ajax_referer( 'change-sms-text', 'security' );

		$order_id = isset( $_POST['order_id'] ) ? intval( $_POST['order_id'] ) : 0;

		$new_status = false;

		if ( isset( $_POST['order_status'] ) ) {

			$_order_status = is_array( $_POST['order_status'] ) ? array_map( 'sanitize_text_field', $_POST['order_status'] ) : sanitize_text_field( $_POST['order_status'] );

			$new_status = substr( $_order_status, 3 );
		}

		$buyer_sms_body = PWooSMS()->Options( 'sms_body_' . $new_status, 'sms_buyer_settings', '' );
		$order          = new WC_Order( $order_id );
		$product_list   = PWooSMS()->GetProdcutLists( $order );
		$all_items      = $product_list['names'] . '__vsh__' . $product_list['names_qty'];
		$buyer_sms_body = PWooSMS()->ReplaceShortCodes( $buyer_sms_body, $new_status, $order_id, $order, $all_items, '' );

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			echo '<textarea id="sms_order_text" name="sms_order_text" style="width:100%;height:120px;"> ' . $buyer_sms_body . ' </textarea>';
			echo '<input type="hidden" name="shop_order_hannan" value="true" />';

			$buyer_pm_type = get_post_meta( $order_id, '_buyer_pm_type', true ) ? get_post_meta( $order_id, '_buyer_pm_type', true ) : array();
			if ( PWooSMS()->Options( 'allow_buyer_select_pm_type', 'sms_buyer_settings', 'no' ) != 'yes' ) {
				$buyer_pm_type = array( 'sms', 'tg' );
			}

			if ( PWooSMS()->Options( 'enable_sms', 'sms_main_settings', 'no' ) == 'on' && in_array( 'sms', ( (array) PWooSMS()->Options( 'pm_type_buyer', 'sms_buyer_settings', array() ) ) ) ) {
				$sms_checked = $this->canGetPm( $order_id, $new_status ) && in_array( 'sms', ( $buyer_pm_type ) ) ? 'checked="checked"' : '';
				echo '<input type="checkbox" id="sms_order_send" class="sms_order_send" name="sms_order_send" value="true" style="margin-top:2px;width:20px; float:right" ' . $sms_checked . '/>
					<label class="sms_order_send_label"  for="sms_order_send" >ارسال پیام به خریدار از طریق اس ام اس</label>';

			}


			if ( PWooSMS()->Options( 'enable_tg', 'sms_main_settings', 'no' ) == 'on' && in_array( 'tg', ( (array) PWooSMS()->Options( 'pm_type_buyer', 'sms_buyer_settings', array() ) ) ) ) {
				$tg_checked = $this->canGetPm( $order_id, $new_status ) && in_array( 'tg', ( $buyer_pm_type ) ) ? 'checked="checked"' : '';
				echo '<input type="checkbox" id="tg_order_send" class="tg_order_send" name="tg_order_send" value="true" style="margin-top:2px;width:20px; float:right" ' . $tg_checked . '/>
					<label class="tg_order_send_label"  for="tg_order_send" >ارسال پیام به خریدار از طریق تلگرام</label>';

			}


			die();
		} else {
			echo 'خطای آیجکس رخ داده است';
			die();
		}
	}

	function canGetPm( $order_id, $new_status ) {

		$allowed_status = (array) PWooSMS()->Options( 'order_status', 'sms_buyer_settings', array() );

		if ( empty( $order_id ) || ! $order_id ) {
			return true;
		} else {

			$order      = new WC_Order( $order_id );
			$old_status = $order->get_status();

			$post        = get_post( $order_id );
			$post_status = is_object( $post ) && ! empty( $post->post_status ) ? $post->post_status : '';

			if ( empty ( $old_status ) || ! isset ( $old_status ) || $old_status == 'draft' || ! in_array( $post_status, array_keys( wc_get_order_statuses() ) ) ) {

				update_post_meta( $order_id, '_force_enable_buyer', 'yes' );
				update_post_meta( $order_id, '_allow_buyer_select_status', 'no' );
				update_post_meta( $order_id, '_allow_buyer_select_pm_type', 'no' );
				update_post_meta( $order_id, '_buyer_sms_notify', 'no' );

				if ( ! ( in_array( $new_status, $allowed_status ) && count( $allowed_status ) > 0 && count( (array) PWooSMS()->GetAllowedStatuses() ) > 0 ) ) {
					return false;
				}

				return true;
			} else {

				if ( ! ( in_array( $new_status, $allowed_status ) && count( $allowed_status ) > 0 && count( (array) PWooSMS()->GetAllowedStatuses() ) > 0 ) ) {
					return false;
				}

				if ( ( PWooSMS()->Options( 'enable_buyer', 'sms_buyer_settings', 'off' ) == 'on' ) && get_post_meta( $order_id, '_buyer_sms_notify', true ) == 'yes' && strlen( get_post_meta( $order_id, '_billing_phone', true ) ) > 5 ) {

					$buyer_sms_status = get_post_meta( $order_id, '_buyer_sms_status', true ) ? get_post_meta( $order_id, '_buyer_sms_status', true ) : array();

					if ( ( get_post_meta( $order_id, '_allow_buyer_select_status', true ) == 'no' )
					     || ( get_post_meta( $order_id, '_allow_buyer_select_status', true ) == 'yes' && in_array( $new_status, $buyer_sms_status ) ) ) {
						return true;
					}
				}
			}
		}

		return false;
	}
}