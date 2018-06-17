<?php

class WoocommerceIR_Helper {

	function Options( $option, $section, $default = '' ) {

		$options = get_option( $section );

		return isset( $options[ $option ] ) ? $options[ $option ] : $default;
	}

	function GetAllStatuses() {

		if ( ! function_exists( 'wc_get_order_statuses' ) ) {
			return false;
		}

		$statuses     = wc_get_order_statuses() ?: array();
		$opt_statuses = array();
		foreach ( (array) $statuses as $status_val => $status_name ) {
			$opt_statuses[ substr( $status_val, 3 ) ] = $status_name;
		}

		return $opt_statuses;
	}

	function GetAllSuperAdminStatuses() {
		if ( ! function_exists( 'wc_get_order_statuses' ) ) {
			return false;
		}
		$statuses     = wc_get_order_statuses() ?: array();
		$opt_statuses = array();
		foreach ( (array) $statuses as $status_val => $status_name ) {
			$opt_statuses[ substr( $status_val, 3 ) ] = $status_name;
		}
		$opt_statuses['low'] = __( 'کم بودن موجودی انبار', 'persianwoosms' );
		$opt_statuses['out'] = __( 'تمام شدن موجودی انبار', 'persianwoosms' );

		return $opt_statuses;
	}

	function GetAllProductAdminStatuses() {

		if ( ! function_exists( 'wc_get_order_statuses' ) ) {
			return false;
		}

		$statuses     = wc_get_order_statuses() ?: array();
		$opt_statuses = array();

		$enable_sms = $this->Options( 'enable_sms', 'sms_main_settings', 'no' );
		$enable_tg  = $this->Options( 'enable_tg', 'sms_main_settings', 'no' );

		$type = (array) $this->Options( 'pm_type_product_admin', 'sms_product_admin_settings', array() );

		$opt_statuses['sms'] = __( 'دریافت اس ام اس', 'persianwoosms' );
		$opt_statuses['tg']  = __( 'دریافت تلگرام', 'persianwoosms' );

		if ( $enable_sms != 'on' || ! in_array( 'sms', $type ) ) {
			$opt_statuses['sms'] .= '(غیرفعال)';
		}

		if ( $enable_tg != 'on' || ! in_array( 'tg', $type ) ) {
			$opt_statuses['tg'] .= '(غیرفعال)';
		}

		foreach ( (array) $statuses as $status_val => $status_name ) {
			$opt_statuses[ substr( $status_val, 3 ) ] = $status_name;
		}

		$opt_statuses['low'] = __( 'کم بودن موجودی انبار', 'persianwoosms' );
		$opt_statuses['out'] = __( 'تمام شدن موجودی انبار', 'persianwoosms' );

		return $opt_statuses;
	}

	function GetAllowedStatuses() {

		if ( ! function_exists( 'wc_get_order_statuses' ) ) {
			return false;
		}

		$statuses              = wc_get_order_statuses() ? wc_get_order_statuses() : array();
		$order_status_settings = (array) $this->Options( 'order_status', 'sms_buyer_settings', array() );
		$allowed_statuses      = array();
		foreach ( (array) $statuses as $status_val => $status_name ) {
			if ( in_array( substr( $status_val, 3 ), $order_status_settings ) ) {
				$allowed_statuses[ substr( $status_val, 3 ) ] = $status_name;
			}
		}

		return $allowed_statuses;
	}

	function GetProdcutLists( $order ) {

		$order_item  = $order->get_items();
		$prodct_name = $prodct_id = $prodct_name_qty = array();

		foreach ( (array) $order_item as $product ) {
			$prodct_id[]       = $product['product_id'];
			$prodct_name[]     = $product['name'];
			$prodct_name_qty[] = $product['name'] . '(' . $product['qty'] . ')';
		}

		$product_names    = implode( '-', $prodct_name );
		$prodct_name_qtys = implode( '-', $prodct_name_qty );
		$prodct_ids       = implode( ',', $prodct_id );

		return array(
			'names_qty' => $prodct_name_qtys,
			'names'     => $product_names,
			'ids'       => $prodct_ids
		);
	}

	static function multiSelectAndCheckbox( $field, $key, $args, $value ) {

		$after = ! empty( $args['clear'] ) ? '<div class="clear"></div>' : '';

		if ( $args['required'] ) {
			$args['class'][] = 'validate-required';
			$required        = ' <abbr class="required" title="' . esc_attr__( 'required', 'persian_woo_sms' ) . '">*</abbr>';
		} else {
			$required = '';
		}

		$custom_attributes = array();
		if ( ! empty( $args['custom_attributes'] ) && is_array( $args['custom_attributes'] ) ) {
			foreach ( $args['custom_attributes'] as $attribute => $attribute_value ) {
				$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
			}
		}

		if ( $args['type'] == "persian_woo_sms_multiselect" ) {
			$value = is_array( $value ) ? $value : array( $value );
			if ( ! empty( $args['options'] ) ) {
				$options = '';
				foreach ( $args['options'] as $option_key => $option_text ) {
					$options .= '<option value="' . esc_attr( $option_key ) . '" ' . selected( in_array( $option_key, $value ), 1, false ) . '>' . esc_attr( $option_text ) . '</option>';
				}
				$field = '<p class="form-row ' . esc_attr( implode( ' ', $args['class'] ) ) . '" id="' . esc_attr( $key ) . '_field">';
				if ( $args['label'] ) {
					$field .= '<label for="' . esc_attr( $key ) . '" class="' . implode( ' ', $args['label_class'] ) . '">' . $args['label'] . $required . '</label>';
				}
				$field .= '<select name="' . esc_attr( $key ) . '[]" id="' . esc_attr( $key ) . '" class="select" multiple="multiple" ' . implode( ' ', $custom_attributes ) . '>'
				          . $options
				          . ' </select>';

				if ( $args['description'] ) {
					$field .= '<span class="description">' . ( $args['description'] ) . '</span>';
				}

				$field .= '</p>' . $after;
			}
		}

		if ( $args['type'] == "persian_woo_sms_multicheckbox" ) {
			$value = is_array( $value ) ? $value : array( $value );
			if ( ! empty( $args['options'] ) ) {
				$field .= '<p class="form-row ' . esc_attr( implode( ' ', $args['class'] ) ) . '" id="' . esc_attr( $key ) . '_field">';
				if ( $args['label'] ) {
					$field .= '<label for="' . esc_attr( current( array_keys( $args['options'] ) ) ) . '" class="' . implode( ' ', $args['label_class'] ) . '">' . $args['label'] . $required . '</label>';
				}
				foreach ( $args['options'] as $option_key => $option_text ) {
					$field .= '<input type="checkbox" class="input-checkbox" value="' . esc_attr( $option_key ) . '" name="' . esc_attr( $key ) . '[]" id="' . esc_attr( $key ) . '_' . esc_attr( $option_key ) . '"' . checked( in_array( $option_key, $value ), 1, false ) . ' />';
					$field .= '<label for="' . esc_attr( $key ) . '_' . esc_attr( $option_key ) . '" class="checkbox ' . implode( ' ', $args['label_class'] ) . '">' . $option_text . '</label><br>';
				}
				if ( $args['description'] ) {
					$field .= '<span class="description">' . ( $args['description'] ) . '</span>';
				}
				$field .= '</p>' . $after;
			}
		}

		return $field;
	}

//از سایت jdf
	function JalaliDate( $g_y, $g_m, $g_d, $mod = '' ) {
		$d_4   = $g_y % 4;
		$g_a   = array( 0, 0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334 );
		$doy_g = $g_a[ (int) $g_m ] + $g_d;
		if ( $d_4 == 0 and $g_m > 2 ) {
			$doy_g ++;
		}
		$d_33 = (int) ( ( ( $g_y - 16 ) % 132 ) * .0305 );
		$a    = ( $d_33 == 3 or $d_33 < ( $d_4 - 1 ) or $d_4 == 0 ) ? 286 : 287;
		$b    = ( ( $d_33 == 1 or $d_33 == 2 ) and ( $d_33 == $d_4 or $d_4 == 1 ) ) ? 78 : ( ( $d_33 == 3 and $d_4 == 0 ) ? 80 : 79 );
		if ( (int) ( ( $g_y - 10 ) / 63 ) == 30 ) {
			$a --;
			$b ++;
		}
		if ( $doy_g > $b ) {
			$jy    = $g_y - 621;
			$doy_j = $doy_g - $b;
		} else {
			$jy    = $g_y - 622;
			$doy_j = $doy_g + $a;
		}
		if ( $doy_j < 187 ) {
			$jm = (int) ( ( $doy_j - 1 ) / 31 );
			$jd = $doy_j - ( 31 * $jm ++ );
		} else {
			$jm = (int) ( ( $doy_j - 187 ) / 30 );
			$jd = $doy_j - 186 - ( $jm * 30 );
			$jm += 7;
		}

		return ( $mod == '' ) ? array( $jy, $jm, $jd ) : $jy . $mod . $jm . $mod . $jd;
	}


	function ShortCodes() {

		$shortcode_list = apply_filters( 'persian_woo_sms_shortcode_list', '' );

		return "
		<strong>جزییات سفارش : </strong><br/>
		<code>{phone}</code> = شماره موبایل خریدار   ، 
		<code>{email}</code> = ایمیل خریدار   ، 	
		<code>{order_id}</code> = شماره سفارش  ،    
		<code>{post_id}</code> = شماره پست (شماره سفارش اصلی)  ،  
		<code>{status}</code> = وضعیت سفارش<br/> 
		<code>{price}</code> = مبلغ سفارش   ، 
		<code>{all_items}</code> = آیتم های سفارش  ، 
		<code>{all_items_qty}</code> = آیتم های سفارش همراه تعداد ، 
		<code>{count_items}</code> = تعداد آیتم های سفارش  <br/> 
		<code>{payment_method}</code> = روش پرداخت  ، 
		<code>{shipping_method}</code> = روش ارسال  ، 
		<code>{description}</code> = توضیحات خریدار  ، 
		<code>{transaction_id}</code> = شماره تراکنش<br/><br/>
		
		<strong>جزییات صورت حساب : </strong><br/>
		<code>{b_first_name}</code> = نام خریدار   ، 
		<code>{b_last_name}</code> = نام خانوادگی خریدار   ، 
		<code>{b_company}</code> = نام شرکت   <br/> 
		<code>{b_country}</code> = کشور   ، 
		<code>{b_state}</code> = ایالت/استان   ، 
		<code>{b_city}</code> = شهر   ، 
		<code>{b_address_1}</code> = آدرس 1   ، 
		<code>{b_address_2}</code> = آدرس 2   ، 
		<code>{b_postcode}</code> = کد پستی<br/><br/>
		
		
		<strong>جزییات حمل و نقل : </strong><br/>
		<code>{sh_first_name}</code> = نام خریدار   ، 
		<code>{sh_last_name}</code> = نام خانوادگی خریدار   ، 
		<code>{sh_company}</code> = نام شرکت   <br/> 
		<code>{sh_country}</code> = کشور   ، 
		<code>{sh_state}</code> = ایالت/استان   ، 
		<code>{sh_city}</code> = شهر   ، 
		<code>{sh_address_1}</code> = آدرس 1   ،
		<code>{sh_address_2}</code> = آدرس 2   ، 
		<code>{sh_postcode}</code> = کد پستی<br/><br/>
		
		{$shortcode_list}
		
	";
	}

	function ReplaceShortCodes( $content, $order_status, $order_id, $order, $all_items, $vendor_items ) {

		$price = strip_tags( $order->get_formatted_order_total() );

		$count_items = count( explode( '-', $all_items ) );
		list( $all_items, $all_items_qty ) = explode( '__vsh__', $all_items );

		$payment_gateways = array();
		if ( WC()->payment_gateways() ) {
			$payment_gateways = WC()->payment_gateways->payment_gateways();
		}

		$payment_method  = $this->OrderProp( $order, 'payment_method' );
		$payment_method  = ( isset( $payment_gateways[ $payment_method ] ) ? esc_html( $payment_gateways[ $payment_method ]->get_title() ) : esc_html( $payment_method ) );
		$shipping_method = esc_html( $order->get_shipping_method() );

		$country = WC()->countries;

		$bill_country = ( isset( $country->countries[ $this->OrderProp( $order, 'billing_country' ) ] ) ) ? $country->countries[ $this->OrderProp( $order, 'billing_country' ) ] : $this->OrderProp( $order, 'billing_country' );
		$bill_state   = ( $this->OrderProp( $order, 'billing_country' ) && $this->OrderProp( $order, 'billing_state' ) && isset( $country->states[ $this->OrderProp( $order, 'billing_country' ) ][ $this->OrderProp( $order, 'billing_state' ) ] ) ) ? $country->states[ $this->OrderProp( $order, 'billing_country' ) ][ $this->OrderProp( $order, 'billing_state' ) ] : $this->OrderProp( $order, 'billing_state' );

		$shipp_country = ( isset( $country->countries[ $this->OrderProp( $order, 'shipping_country' ) ] ) ) ? $country->countries[ $this->OrderProp( $order, 'shipping_country' ) ] : $this->OrderProp( $order, 'shipping_country' );
		$shipp_state   = ( $this->OrderProp( $order, 'shipping_country' ) && $this->OrderProp( $order, 'shipping_state' ) && isset( $country->states[ $this->OrderProp( $order, 'shipping_country' ) ][ $this->OrderProp( $order, 'shipping_state' ) ] ) ) ? $country->states[ $this->OrderProp( $order, 'shipping_country' ) ][ $this->OrderProp( $order, 'shipping_state' ) ] : $this->OrderProp( $order, 'shipping_state' );

		$post = get_post( $order_id );

		$tags = array(
			'{b_first_name}'    => $this->OrderProp( $order, 'billing_first_name' ),
			'{b_last_name}'     => $this->OrderProp( $order, 'billing_last_name' ),
			'{b_company}'       => $this->OrderProp( $order, 'billing_company' ),
			'{b_address_1}'     => $this->OrderProp( $order, 'billing_address_1' ),
			'{b_address_2}'     => $this->OrderProp( $order, 'billing_address_2' ),
			'{b_state}'         => $bill_state,
			'{b_city}'          => $this->OrderProp( $order, 'billing_city' ),
			'{b_postcode}'      => $this->OrderProp( $order, 'billing_postcode' ),
			'{b_country}'       => $bill_country,
			'{sh_first_name}'   => $this->OrderProp( $order, 'shipping_first_name' ),
			'{sh_last_name}'    => $this->OrderProp( $order, 'shipping_last_name' ),
			'{sh_company}'      => $this->OrderProp( $order, 'shipping_company' ),
			'{sh_address_1}'    => $this->OrderProp( $order, 'shipping_address_1' ),
			'{sh_address_2}'    => $this->OrderProp( $order, 'shipping_address_2' ),
			'{sh_state}'        => $shipp_state,
			'{sh_city}'         => $this->OrderProp( $order, 'shipping_city' ),
			'{sh_postcode}'     => $this->OrderProp( $order, 'shipping_postcode' ),
			'{sh_country}'      => $shipp_country,
			'{phone}'           => get_post_meta( $order_id, '_billing_phone', true ),
			'{email}'           => $this->OrderProp( $order, 'billing_email' ),
			'{order_id}'        => $order->get_order_number(),
			'{post_id}'         => $order_id,
			'{status}'          => wc_get_order_status_name( $order_status ),
			'{price}'           => $price,
			'{all_items}'       => $all_items,
			'{all_items_qty}'   => $all_items_qty,
			'{count_items}'     => $count_items,
			'{vendor_items}'    => $vendor_items,
			'{transaction_id}'  => get_post_meta( $order_id, '_transaction_id', true ),
			'{payment_method}'  => $payment_method,
			'{shipping_method}' => $shipping_method,
			'{description}'     => nl2br( esc_html( $post->post_excerpt ) ),
		);


		$product_list = $this->GetProdcutLists( $order );
		$product_ids  = explode( ',', $product_list['ids'] );

		$content = apply_filters( 'persian_woo_sms_content_replace', $content, array_keys( $tags ), array_values( $tags ), $order_id, $order, $product_ids );

		$content = str_replace( array( '<br>', '<br/>', '<br />', '&nbsp;' ),
			array( '', '', '', ' ' ), str_replace( array_keys( $tags ), array_values( $tags ), $content ) );

		$content = apply_filters( 'persian_woo_sms_content', $content, $order_id, $order, $product_ids );

		return $content;
	}

	function validateMobile( $phone ) {

		//return preg_match('/^(((\+|00)98)|0)?9[0-9]{9}$/', $phone );

		$phone = $this->EnglishNumberMobile( $phone );

		$_phone = str_replace( array( '+', '-', '_', ',', '(', ')' ), array( '', '', '', '', '', '' ), $phone );

		return is_numeric( $_phone );
	}

	function EnglishNumberMobile( $phone ) {
		if ( is_array( $phone ) ) {
			return array_map( array( $this, 'EnglishNumberMobile' ), $phone );
		} else {
			$phone = str_ireplace( array( '۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹' ),
				array( '0', '1', '2', '3', '4', '5', '6', '7', '8', '9' ), $phone ); //farsi
			$phone = str_ireplace( array( '٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩' ),
				array( '0', '1', '2', '3', '4', '5', '6', '7', '8', '9' ), $phone ); //arabi

			return $phone;
		}
	}


	function ReplaceTags( $content, $product_id ) {

		$product = wc_get_product( $product_id );

		$sale_price_dates_from = ( $date = get_post_meta( $product_id, '_sale_price_dates_from', true ) ) ? date_i18n( 'Y-m-d', $date ) : '';
		if ( $sale_price_dates_from != '' ) {
			list( $year, $month, $day ) = explode( '-', $sale_price_dates_from );
			if ( $year > 2000 ) {
				$sale_price_dates_from = $this->JalaliDate( $year, $month, $day, '/' );
			} else {
				$sale_price_dates_from = $year . '/' . $month . '/' . $day;
			}
		}

		$sale_price_dates_to = ( $date = get_post_meta( $product_id, '_sale_price_dates_to', true ) ) ? date_i18n( 'Y-m-d', $date ) : '';
		if ( $sale_price_dates_to != '' ) {
			list( $year, $month, $day ) = explode( '-', $sale_price_dates_to );
			if ( $year > 2000 ) {
				$sale_price_dates_to = $this->JalaliDate( $year, $month, $day, '/' );
			} else {
				$sale_price_dates_to = $year . '/' . $month . '/' . $day;
			}
		}


		$tags = array(
			'{product_id}'    => $product_id,
			'{sku}'           => $product->get_sku(),
			'{product_title}' => get_the_title( $product_id ),
			'{regular_price}' => strip_tags( wc_price( $product->get_regular_price() ) ),
			'{onsale_price}'  => strip_tags( wc_price( $product->get_sale_price() ) ),
			'{onsale_from}'   => $sale_price_dates_from,
			'{onsale_to}'     => $sale_price_dates_to,
			'{stock}'         => $this->ProductStockQty( $product ),
		);

		return str_replace( array( '<br>', '<br/>', '<br />', '&nbsp;' ),
			array( '', '', '', ' ' ),
			str_replace( array_keys( $tags ), array_values( $tags ), $content ) );
	}


	function ProductId( $product ) {
		return method_exists( $product, 'get_id' ) ? $product->get_id() : $product->id;
	}

	function ProductStockQty( $product ) {

		if ( method_exists( $product, 'get_stock_quantity' ) ) {
			$quantity = $product->get_stock_quantity();
		} else {
			$quantity = method_exists( $product, 'get_total_stock' ) ? $product->get_total_stock() : '';
		}

		if ( empty( $quantity ) ) {
			$quantity = ( (int) get_post_meta( $this->ProductId( $product ), '_stock', true ) );
		}

		return ! empty( $quantity ) ? $quantity : 0;
	}

	function OrderId( $order ) {
		return method_exists( $order, 'get_id' ) ? $order->get_id() : $order->id;
	}

	function OrderProp( $order, $prop ) {
		$method = 'get_' . $prop;

		return method_exists( $order, $method ) ? $order->$method() : $order->{$prop};
	}

}

function PWooSMS() {
	global $persianwoohelper;

	if ( ! empty( $persianwoohelper ) && is_object( $persianwoohelper ) ) {
		return $persianwoohelper;
	}

	return $persianwoohelper = new WoocommerceIR_Helper();
}