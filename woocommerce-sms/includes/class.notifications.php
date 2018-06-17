<?php

class WoocommerceIR_Notification_SMS {
	public function __construct() {

		if ( PWooSMS()->Options( 'enable_notif_sms_main', 'sms_notif_settings', 'off' ) == 'on' ) {

			add_action( 'widgets_init', 'psLoadWidget' );
			add_shortcode( 'woo_ps_sms', array( $this, 'shortCode' ) );


			add_action( 'woocommerce_product_thumbnails', array( $this, 'showNotifBox' ), 100 );
			add_action( 'woocommerce_single_product_summary', array( $this, 'showNotifBox' ), 39 );
			add_action( 'woocommerce_product_sms', array( $this, 'smsTabContent' ) );
			add_action( 'woocommerce_product_set_stock_status', array( $this, 'smsInStock' ) );
			add_action( 'send_sms_onsale_event', array( $this, 'smsIsOnSale' ) );

			//Ajax
			add_action( 'wp_ajax_save_numbers_to_product_meta', array( $this, 'saveNumbersMeta' ) );
			add_action( 'wp_ajax_nopriv_save_numbers_to_product_meta', array( $this, 'saveNumbersMeta' ) );
		}

		add_action( 'woocommerce_process_product_meta', array( $this, 'saveSmsData' ), 9999, 1 );
		add_action( 'woocommerce_low_stock', array( $this, 'smsIsLowStock' ) );
		add_action( 'woocommerce_product_set_stock_status', array( $this, 'smsOutStock' ) );
	}


	public function smsTabContent( $thepostid ) {

		woocommerce_wp_checkbox( array(
			'id'            => 'enable_notif_sms',
			'wrapper_class' => 'enable_notif_sms',
			'label'         => __( 'فعالسازی (نمایش خودکار در محصول)', 'woocommerce' ),
			'cbvalue'       => 'on',
			'desc_tip'      => true,
			'value'         => ( get_post_meta( $thepostid, '_is_sms_set', true ) ? get_post_meta( $thepostid, '_enable_notif_sms', true ) : PWooSMS()->Options( 'enable_notif_sms', 'sms_notif_settings', 'no' ) ),
			'description'   => __( 'با فعالسازی این قسمت گزینه "میخواهم از وضعیت محصول توسط پیامک با خبر شوم" در صفحه محصولات اضافه خواهد شد .<br/>میتوانید این قسمت "نمایش خودکار" را غیرفعال نمایید و بجای آن از شورت کد "[woo_ps_sms]" یا ابزارک "اطلاع رسانی پیامکی ووکامرس" در صفحه محصول استفاده نمایید .', 'woocommerce' )
		) );

		echo '<p>تذکر : برای جلوگیری از مشکل تداخل  جیکوئری ، در صفحه هر محصول فقط از یکی از حالت های "نمایش خودکار" ، "ابزارک" یا "شورت کد" استفاده نمایید .</p>';

		echo '<hr/>';

		woocommerce_wp_text_input( array(
			'id'          => 'notif_title',
			'class'       => '',
			'label'       => __( 'متن سر تیتر گزینه ها', 'persianwoosms' ),
			'desc_tip'    => true,
			'placeholder' => '',
			'description' => 'این متن در صفحه محصول به صورت چک باکس ظاهر خواهد شد و خریدار با فعال کردن آن میتواند شماره خود را برای دریافت اطلاعیه آن محصول وارد نماید .',
			'value'       => ( get_post_meta( $thepostid, '_is_sms_set', true ) ? get_post_meta( $thepostid, '_notif_title', true ) : PWooSMS()->Options( 'notif_title', 'sms_notif_settings', '' ) ),
		) );

		echo '<hr/>';

		echo '<div class="hannanstd-woo-tabs-hidden-how-to-info" style="display: none;">
				<h3 style="padding-top:0;padding-bottom:0;">شورت کد های قابل استفاده در متن پیامک ها :</h3>
				<p style="margin:0;padding-left:13px;"><code>{product_id}</code> : آیدی محصول ، <code>{sku}</code> : شناسه محصول ، <code>{product_title}</code> : عنوان محصول ، <code>{regular_price}</code> قیمت اصلی ، <code>{onsale_price}</code> : قیمت فروش فوق العاده<br/><code>{onsale_from}</code> : تاریخ شروع فروش فوق العاده ، <code>{onsale_to}</code> : تاریخ اتمام فروش فوق العاده ، <code>{stock}</code> : موجودی انبار</p>
			</div>';

		echo '<div class="dashicons dashicons-editor-help hannanstd-tabs-how-to-toggle" title="راهنمایی"></div>';

		woocommerce_wp_checkbox( array(
			'id'            => 'enable_onsale',
			'wrapper_class' => 'enable_onsale',
			'label'         => __( 'زمانیکه که محصول حراج شد', 'woocommerce' ),
			'cbvalue'       => 'on',
			'desc_tip'      => true,
			'value'         => ( get_post_meta( $thepostid, '_is_sms_set', true ) ? get_post_meta( $thepostid, '_enable_onsale', true ) : PWooSMS()->Options( 'enable_onsale', 'sms_notif_settings', 'no' ) ),
			'description'   => __( 'هنگامی که این گزینه فعال باشد در صورت حراج نبودن محصول گزینه "زمانیکه که محصول حراج شد" نیز به لیست گزینه های اطلاع رسانی اضافه خواهد شد .', 'woocommerce' )
		) );

		woocommerce_wp_text_input( array(
			'id'          => 'notif_onsale_text',
			'class'       => '',
			'label'       => __( 'متن گزینه "زمانیکه محصول حراج شد"', 'persianwoosms' ),
			'desc_tip'    => true,
			'placeholder' => '',
			'description' => 'میتوانید متن دلخواه خود را جایگزین جمله "زمانیکه محصول حراج شد" نمایید .',
			'value'       => ( get_post_meta( $thepostid, '_is_sms_set', true ) ? get_post_meta( $thepostid, '_notif_onsale_text', true ) : PWooSMS()->Options( 'notif_onsale_text', 'sms_notif_settings', '' ) ),
		) );

		woocommerce_wp_textarea_input( array(
			'id'          => 'notif_onsale_sms',
			'class'       => 'short',
			'label'       => __( 'متن پیامک "زمانیکه محصول حراج شد"', 'woocommerce' ),
			'desc_tip'    => true,
			'value'       => ( get_post_meta( $thepostid, '_is_sms_set', true ) ? get_post_meta( $thepostid, '_notif_onsale_sms', true ) : PWooSMS()->Options( 'notif_onsale_sms', 'sms_notif_settings', '' ) ),
			'description' => __( '', 'woocommerce' )
		) );

		echo '<hr/>';

		woocommerce_wp_checkbox( array(
			'id'            => 'enable_notif_no_stock',
			'wrapper_class' => 'enable_notif_no_stock',
			'label'         => __( 'زمانیکه که محصول موجود شد', 'woocommerce' ),
			'cbvalue'       => 'on',
			'desc_tip'      => true,
			'value'         => ( get_post_meta( $thepostid, '_is_sms_set', true ) ? get_post_meta( $thepostid, '_enable_notif_no_stock', true ) : PWooSMS()->Options( 'enable_notif_no_stock', 'sms_notif_settings', 'no' ) ),
			'description'   => __( 'هنگامی که این گزینه فعال باشد در صورت ناموجود شدن محصول گزینه "زمانیکه که محصول موجود شد" نیز به لیست گزینه های اطلاع رسانی اضافه خواهد شد .', 'woocommerce' )
		) );

		woocommerce_wp_text_input( array(
			'id'          => 'notif_no_stock_text',
			'class'       => '',
			'label'       => __( 'متن گزینه "زمانیکه محصول موجود شد"', 'persianwoosms' ),
			'desc_tip'    => true,
			'placeholder' => '',
			'description' => 'میتوانید متن دلخواه خود را جایگزین جمله "زمانیکه محصول موجود شد" نمایید .',
			'value'       => ( get_post_meta( $thepostid, '_is_sms_set', true ) ? get_post_meta( $thepostid, '_notif_no_stock_text', true ) : PWooSMS()->Options( 'notif_no_stock_text', 'sms_notif_settings', '' ) ),
		) );

		woocommerce_wp_textarea_input( array(
			'id'          => 'notif_no_stock_sms',
			'class'       => 'short',
			'label'       => __( 'متن پیامک "زمانیکه محصول موجود شد"', 'woocommerce' ),
			'desc_tip'    => true,
			'value'       => ( get_post_meta( $thepostid, '_is_sms_set', true ) ? get_post_meta( $thepostid, '_notif_no_stock_sms', true ) : PWooSMS()->Options( 'notif_no_stock_sms', 'sms_notif_settings', '' ) ),
			'description' => __( '', 'woocommerce' )
		) );

		echo '<hr/>';

		woocommerce_wp_checkbox( array(
			'id'            => 'enable_notif_low_stock',
			'wrapper_class' => 'enable_notif_low_stock',
			'label'         => __( 'زمانیکه موجودی انبار محصول کم شد', 'woocommerce' ),
			'cbvalue'       => 'on',
			'desc_tip'      => true,
			'value'         => ( get_post_meta( $thepostid, '_is_sms_set', true ) ? get_post_meta( $thepostid, '_enable_notif_low_stock', true ) : PWooSMS()->Options( 'enable_notif_low_stock', 'sms_notif_settings', 'no' ) ),
			'description'   => __( 'هنگامی که این گزینه فعال باشد ، گزینه "زمانیکه که موجودی انبار محصول کم شد" نیز به لیست گزینه های اطلاع رسانی اضافه خواهد شد .', 'woocommerce' )
		) );
		woocommerce_wp_text_input( array(
			'id'          => 'notif_low_stock_text',
			'class'       => '',
			'label'       => __( 'متن گزینه "زمانیکه موجودی انبار محصول کم شد"', 'persianwoosms' ),
			'desc_tip'    => true,
			'placeholder' => '',
			'description' => 'میتوانید متن دلخواه خود را جایگزین جمله "زمانیکه موجودی انبار محصول کم شد" نمایید .',
			'value'       => ( get_post_meta( $thepostid, '_is_sms_set', true ) ? get_post_meta( $thepostid, '_notif_low_stock_text', true ) : PWooSMS()->Options( 'notif_low_stock_text', 'sms_notif_settings', '' ) ),
		) );

		woocommerce_wp_textarea_input( array(
			'id'          => 'notif_low_stock_sms',
			'class'       => 'short',
			'label'       => __( 'متن پیامک "زمانیکه محصول موجودی انبار کم شد"', 'woocommerce' ),
			'desc_tip'    => true,
			'value'       => ( get_post_meta( $thepostid, '_is_sms_set', true ) ? get_post_meta( $thepostid, '_notif_low_stock_sms', true ) : PWooSMS()->Options( 'notif_low_stock_sms', 'sms_notif_settings', '' ) ),
			'description' => __( '', 'woocommerce' )
		) );

		echo '<hr/>';

		woocommerce_wp_textarea_input( array(
			'id'          => 'notif_options',
			'class'       => 'short',
			'label'       => __( 'گزینه های دلخواه', 'woocommerce' ),
			'desc_tip'    => true,
			'style'       => 'height:100px;',
			'value'       => ( get_post_meta( $thepostid, '_is_sms_set', true ) ? get_post_meta( $thepostid, '_notif_options', true ) : PWooSMS()->Options( 'notif_options', 'sms_notif_settings', '' ) ),
			'description' => __( 'شما میتوانید گزینه های دلخواه خود را برای نمایش در صفحه محصولات ایجاد نمایید و به صورت دستی به خریدارانی که در گزینه های بالا عضو شده اند پیامک ارسال کنید .<br/>
		برای اضافه کردن گزینه ها ، همانند نمونه بالا ابتدا یک کد عددی دلخواه تعریف کنید سپس بعد از قرار دادن عبارت ":" متن مورد نظر را بنویسید .<br/>
		دقت کنید که کد عددی هر گزینه بسیار مهم بوده و از تغییر کد مربوط به هر گزینه بعد از ذخیره تنظیمات خود داری نمایید .', 'woocommerce' )
		) );

		echo '<hr/>';
	}

	public function saveSmsData( $post_id = 0 ) {

		$product = wc_get_product( $post_id );

		if ( PWooSMS()->Options( 'enable_notif_sms_main', 'sms_notif_settings', 'off' ) == 'on' ) {

			update_post_meta( $post_id, '_is_sms_set', 'yes' );

			if ( ! empty( $_POST['enable_notif_sms'] ) ) {
				update_post_meta( $post_id, '_enable_notif_sms', $_POST['enable_notif_sms'] );
			} else {
				delete_post_meta( $post_id, '_enable_notif_sms' );
			}

			if ( ! empty( $_POST['notif_title'] ) ) {
				update_post_meta( $post_id, '_notif_title', $_POST['notif_title'] );
			} else {
				delete_post_meta( $post_id, '_notif_title' );
			}

			if ( ! empty( $_POST['enable_onsale'] ) ) {
				update_post_meta( $post_id, '_enable_onsale', $_POST['enable_onsale'] );
			} else {
				delete_post_meta( $post_id, '_enable_onsale' );
			}

			if ( ! empty( $_POST['notif_onsale_text'] ) ) {
				update_post_meta( $post_id, '_notif_onsale_text', $_POST['notif_onsale_text'] );
			} else {
				delete_post_meta( $post_id, '_notif_onsale_text' );
			}

			if ( ! empty( $_POST['notif_onsale_sms'] ) ) {
				update_post_meta( $post_id, '_notif_onsale_sms', $_POST['notif_onsale_sms'] );
			} else {
				delete_post_meta( $post_id, '_notif_onsale_sms' );
			}

			if ( ! empty( $_POST['enable_notif_low_stock'] ) ) {
				update_post_meta( $post_id, '_enable_notif_low_stock', $_POST['enable_notif_low_stock'] );
			} else {
				delete_post_meta( $post_id, '_enable_notif_low_stock' );
			}

			if ( ! empty( $_POST['notif_low_stock_text'] ) ) {
				update_post_meta( $post_id, '_notif_low_stock_text', $_POST['notif_low_stock_text'] );
			} else {
				delete_post_meta( $post_id, '_notif_low_stock_text' );
			}

			if ( ! empty( $_POST['notif_low_stock_sms'] ) ) {
				update_post_meta( $post_id, '_notif_low_stock_sms', $_POST['notif_low_stock_sms'] );
			} else {
				delete_post_meta( $post_id, '_notif_low_stock_sms' );
			}

			if ( ! empty( $_POST['enable_notif_no_stock'] ) ) {
				update_post_meta( $post_id, '_enable_notif_no_stock', $_POST['enable_notif_no_stock'] );
			} else {
				delete_post_meta( $post_id, '_enable_notif_no_stock' );
			}

			if ( ! empty( $_POST['notif_no_stock_text'] ) ) {
				update_post_meta( $post_id, '_notif_no_stock_text', $_POST['notif_no_stock_text'] );
			} else {
				delete_post_meta( $post_id, '_notif_no_stock_text' );
			}

			if ( ! empty( $_POST['notif_no_stock_sms'] ) ) {
				update_post_meta( $post_id, '_notif_no_stock_sms', $_POST['notif_no_stock_sms'] );
			} else {
				delete_post_meta( $post_id, '_notif_no_stock_sms' );
			}

			if ( ! empty( $_POST['notif_options'] ) ) {
				update_post_meta( $post_id, '_notif_options', $_POST['notif_options'] );
			} else {
				delete_post_meta( $post_id, '_notif_options' );
			}

			if ( get_post_meta( $post_id, '_onsale_send', true ) != 'yes' && ( ! empty( $_POST['_sale_price'] ) && $_POST['_sale_price'] < $_POST['_regular_price'] ) ) {
				$date_from = isset( $_POST['_sale_price_dates_from'] ) ? wc_clean( $_POST['_sale_price_dates_from'] ) : '';
				$date_from = strtotime( $date_from );
				if ( ! $date_from || ! is_numeric( $date_from ) || $date_from <= strtotime( 'NOW', current_time( 'timestamp' ) ) ) {
					$this->smsIsOnSale( $product );
				} else {
					wp_schedule_single_event( $date_from, 'send_sms_onsale_event', array( $product ) );
				}

			} elseif ( get_post_meta( $post_id, '_onsale_send', true ) == 'yes' && ( empty( $_POST['_sale_price'] ) || $_POST['_sale_price'] >= $_POST['_regular_price'] ) ) {
				update_post_meta( $post_id, '_onsale_send', 'no' );
			}
		}

		if ( get_post_meta( $post_id, '_low_stock_send', true ) != 'yes' && isset( $_REQUEST['_manage_stock'] ) && ( isset( $_POST['_stock_status'] ) && $_POST['_stock_status'] == 'instock' ) && isset( $_POST['_stock'] ) && $_POST['_stock'] < get_option( 'woocommerce_notify_low_stock_amount' ) && $_POST['_stock'] >= get_option( 'woocommerce_notify_no_stock_amount' ) ) {
			$this->smsIsLowStock( $product );
		} else if ( get_post_meta( $post_id, '_low_stock_send', true ) == 'yes' && ( ( isset( $_POST['_stock'] ) && $_POST['_stock'] >= get_option( 'woocommerce_notify_low_stock_amount' ) ) || ! isset( $_POST['_stock'] ) || ! isset( $_POST['_manage_stock'] ) ) ) {
			update_post_meta( $post_id, '_low_stock_send', 'no' );
		}
	}


	public function showNotifBox() {

		if ( current_action() == 'woocommerce_product_thumbnails' ) {
			if ( apply_filters( 'ps_woo_sms_after_product_thumbnails', false ) ) {
				remove_action( 'woocommerce_single_product_summary', array( $this, 'showNotifBox' ), 39 );
			} else {
				return;
			}
		}


		global $product;
		$thepostid = PWooSMS()->ProductId( $product );
		$is_old    = get_post_meta( $thepostid, '_is_sms_set', true ) ? false : true;

		if ( $is_old ) {
			if ( PWooSMS()->Options( 'notif_old_pr', 'sms_notif_settings', 'no' ) == 'yes' ) {
				if ( PWooSMS()->Options( 'enable_notif_sms', 'sms_notif_settings', 'no' ) != 'on' ) {
					return;
				}
			} else {
				return;
			}
		} else if ( get_post_meta( $thepostid, '_enable_notif_sms', true ) != 'on' ) {
			return;
		}

		global $woo_notif;
		if ( $woo_notif == 'yes' || $GLOBALS['woo_notif'] == 'yes' ) {
			return;
		} else {
			$GLOBALS['woo_notif'] = $woo_notif = 'yes';
		}

		$this->notification( $product );
	}

	public function shortCode() {

		if ( ! is_product() ) {
			return false;
		}

		global $woo_notif;
		if ( $woo_notif == 'yes' || $GLOBALS['woo_notif'] == 'yes' ) {
			return false;
		} else {
			$GLOBALS['woo_notif'] = $woo_notif = 'yes';
		}

		$product_id = get_the_ID();
		$product    = wc_get_product( $product_id );

		if ( is_object( $product ) ) {
			$this->notification( $product );

			return true;
		} else {
			return false;
		}
	}

	public function notification( $product ) {

		$thepostid = PWooSMS()->ProductId( $product );

		$is_sms_set = get_post_meta( $thepostid, '_is_sms_set', true );

		$groups        = array();
		$onsale_meta   = $is_sms_set && get_post_meta( $thepostid, '_enable_onsale', true ) == 'on';
		$onsale_option = ! $is_sms_set && PWooSMS()->Options( 'enable_onsale', 'sms_notif_settings', '' ) == 'on';
		if ( ( $onsale_meta || $onsale_option ) && ! $product->is_on_sale() ) {
			$groups['_onsale'] = $is_sms_set ? get_post_meta( $thepostid, '_notif_onsale_text', true ) : PWooSMS()->Options( 'notif_onsale_text', 'sms_notif_settings', '' );
		}

		$options = $is_sms_set ? get_post_meta( $thepostid, '_notif_options', true ) : PWooSMS()->Options( 'notif_options', 'sms_notif_settings', '' );
		$options = ! empty( $options ) ? $options : '';
		$options = explode( PHP_EOL, $options );
		foreach ( ( array ) $options as $option ) {
			$array = explode( ":", $option );
			if ( count( $array ) == 2 ) {
				list( $code, $text ) = $array;

				if ( strlen( $text ) > 1 ) {
					$groups[ $code ] = $text;
				}
			}
		}

		$low_meta    = $is_sms_set && get_post_meta( $thepostid, '_enable_notif_low_stock', true ) == 'on';
		$low_option  = ! $is_sms_set && PWooSMS()->Options( 'enable_notif_low_stock', 'sms_notif_settings', '' ) == 'on';
		$qty_compare = PWooSMS()->ProductStockQty( $product ) >= get_option( 'woocommerce_notify_low_stock_amount' );
		if ( ( $low_meta || $low_option ) && $qty_compare && $product->is_in_stock() ) {
			$groups['_low'] = $is_sms_set ? get_post_meta( $thepostid, '_notif_low_stock_text', true ) : PWooSMS()->Options( 'notif_low_stock_text', 'sms_notif_settings', '' );
		}

		$in_meta   = $is_sms_set && get_post_meta( $thepostid, '_enable_notif_no_stock', true ) == 'on';
		$in_option = ! $is_sms_set && PWooSMS()->Options( 'enable_notif_no_stock', 'sms_notif_settings', '' ) == 'on';
		if ( ( $in_meta || $in_option ) && ! $product->is_in_stock() ) {
			$groups['_in'] = $is_sms_set ? get_post_meta( $thepostid, '_notif_no_stock_text', true ) : PWooSMS()->Options( 'notif_no_stock_text', 'sms_notif_settings', '' );
		}

		if ( empty( $groups ) ) {
			return;
		}

		do_action( 'ps_woo_sms_before_notif_form', $product );
		?>

        <form class="sms-notif-form" id="sms-notif-form" method="post">
            <div style="display:none !important;width:0 !important;height:0 !important;">
                <img src="<?php echo PS_WOO_SMS_PLUGIN_PATH; ?>/assets/images/tick.png"/>
                <img src="<?php echo PS_WOO_SMS_PLUGIN_PATH; ?>/assets/images/false.png"/>
                <img src="<?php echo PS_WOO_SMS_PLUGIN_PATH; ?>/assets/images/ajax-loader.gif"/>
            </div>

            <p class="sms-notif-enable-p" id="sms-notif-enable-p">
                <input type="checkbox" id="sms-notif-enable" class="sms-notif-enable" name="sms_notif_enable"
                       value="1">

                <label id="sms-notif-enable-label" class="sms-notif-enable-label" for="sms-notif-enable">
                    <strong><?php echo $is_sms_set ? get_post_meta( $thepostid, '_notif_title', true ) : PWooSMS()->Options( 'notif_title', 'sms_notif_settings', '' ); ?></strong>
                </label>
            </p>

            <div class="sms-notif-content" id="sms-notif-content">

				<?php foreach ( $groups as $code => $text ) : ?>

                    <p id="sms-notif-groups-p-<?php echo $code; ?>" class="sms-notif-groups-p">
                        <input type="checkbox" id="sms-notif-groups-<?php echo $code; ?>"
                               class="sms-notif-groups" name="sms_notif_groups[]" value="<?php echo $code; ?>"/>
                        <label class="sms-notif-groups-label sms-notif-groups-label-<?php echo $code; ?>"
                               for="sms-notif-groups-<?php echo $code; ?>"><?php echo $text; ?></label>
                    </p>

				<?php endforeach; ?>

                <div class="sms-notif-mobile-div">
                    <input type="text" id="sms-notif-mobile" class="sms-notif-mobile" name="sms_notif_mobile"
                           title="شماره موبایل" placeholder="شماره موبایل"/>
                </div>

				<?php
				if ( PWooSMS()->Options( 'allow_notif_select_pm_type', 'sms_notif_settings', '' ) == 'yes' ) {

					if ( in_array( 'sms', ( (array) PWooSMS()->Options( 'pm_type_notif', 'sms_notif_settings', array() ) ) ) || in_array( 'tg', ( (array) PWooSMS()->Options( 'pm_type_notif', 'sms_notif_settings', array() ) ) ) ) {
						echo '<p class="sms-notif-types-header-p"><label class="sms-notif-types-header-label"><strong>' . PWooSMS()->Options( 'notif_select_pm_type_text', 'sms_notif_settings', '' ) . '</strong></label></p>';
					}

					if ( in_array( 'sms', ( (array) PWooSMS()->Options( 'pm_type_notif', 'sms_notif_settings', array() ) ) ) ) {
						$text = 'اس ام اس';
						$code = '_sms';
						?>
                        <p class="sms-notif-types-p" id="sms-notif-types-p-<?php echo $code; ?>">
                            <input type="checkbox" id="sms-notif-types-<?php echo $code; ?>"
                                   class="sms-notif-types" name="sms_notif_types[]"
                                   value="<?php echo $code; ?>">

                            <label class="sms-notif-types-label"
                                   for="sms-notif-types-<?php echo $code; ?>"><?php echo $text; ?></label>
                        </p>
						<?php
					}

					if ( in_array( 'tg', ( (array) PWooSMS()->Options( 'pm_type_notif', 'sms_notif_settings', array() ) ) ) ) {
						$text = 'تلگرام';
						$code = '_tg';
						?>
                        <p class="sms-notif-types-p" id="sms-notif-types-p-<?php echo $code; ?>">
                            <input type="checkbox" id="sms-notif-types-<?php echo $code; ?>"
                                   class="sms-notif-types" name="sms_notif_types[]"
                                   value="<?php echo $code; ?>">

                            <label class="sms-notif-types-label"
                                   for="sms-notif-types-<?php echo $code; ?>"><?php echo $text; ?></label>
                        </p>
						<?php
					}
				}
				?>

                <button id="sms-notif-submit" class="sms-notif-submit single_add_to_cart_button button alt"
                        type="submit">
                    ثبت
                </button>

                <p id="sms-notif-result-p" class="sms-notif-result-p">
                    <span id="sms-notif-result" class="sms-notif-result"></span>
                </p>
            </div>
        </form>

		<?php
		do_action( 'ps_woo_sms_after_notif_form', $product );

		$this->script( $thepostid );
	}

	public function script( $product_id ) {
		wc_enqueue_js( '
			jQuery(document).ready(function($){
				$(".sms-notif-content").hide();
			    $(document.body).on( "change", "#sms-notif-enable", function() {
					if( $("#sms-notif-enable:checked").val() )
						$("#sms-notif-content").fadeIn();			
					else
				    	$("#sms-notif-content").fadeOut();
				}).on( "click", "#sms-notif-submit", function() {
				    $("#sms-notif-result").html( "<img src=\"' . PS_WOO_SMS_PLUGIN_PATH . '/assets/images/ajax-loader.gif\" />" );
				    var sms_number = $("#sms-notif-mobile").val();
			    	var sms_group = [];
				    $(".sms-notif-groups:checked").each(function(i){
					    sms_group[i] = $(this).val();
			    	});
				    var pm_type = [];
				    $(".sms-notif-types:checked").each(function(i){
					    pm_type[i] = $(this).val();
			    	});
				    $.ajax({
					    url : "' . admin_url( "admin-ajax.php" ) . '",
				    	type : "post",
					    data : {
						    action : "save_numbers_to_product_meta",
					    	security: "' . wp_create_nonce( "save-numbers-to-product-meta" ) . '",
						    sms_number : sms_number,
						    sms_group : sms_group,
						    pm_type : pm_type,
						    product_id : "' . $product_id . '",
					    },
				    	success : function( response ) {
					    	$("#sms-notif-result").html( response );
					    }
			    	});
				    return false;
		    	});
		    });
		' );
	}

	function saveNumbersMeta() {

		check_ajax_referer( 'save-numbers-to-product-meta', 'security' );

		$product_id = isset( $_POST['product_id'] ) ? intval( $_POST['product_id'] ) : 0;

		$sms_number = isset( $_POST['sms_number'] ) ? sanitize_text_field( $_POST['sms_number'] ) : '';
		$sms_number = PWooSMS()->EnglishNumberMobile( $sms_number );


		$group = $pm_type = array();

		if ( isset( $_POST['sms_group'] ) ) {
			$group = is_array( $_POST['sms_group'] ) ? array_map( 'sanitize_text_field', $_POST['sms_group'] ) : sanitize_text_field( $_POST['sms_group'] );
		}

		if ( isset( $_POST['pm_type'] ) ) {
			$pm_type = is_array( $_POST['pm_type'] ) ? array_map( 'sanitize_text_field', $_POST['pm_type'] ) : sanitize_text_field( $_POST['pm_type'] );
		}


		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			if ( ! $sms_number || strlen( $sms_number ) < 1 ) {
				echo '<img src="' . PS_WOO_SMS_PLUGIN_PATH . '/assets/images/false.png">&nbsp;';
				echo 'شماره تلفن را وارد نمایید';
				die();
			} else if ( ! PWooSMS()->validateMobile( $sms_number ) ) {
				echo '<img src="' . PS_WOO_SMS_PLUGIN_PATH . '/assets/images/false.png">&nbsp;';
				echo 'شماره تلفن معتبر نیست';
				die();
			} else if ( ! $group || empty( $group ) ) {
				echo '<img src="' . PS_WOO_SMS_PLUGIN_PATH . '/assets/images/false.png">&nbsp;';
				echo 'انتخاب یکی از گزینه ها الزامیست';
				die();
			} else {

				if ( PWooSMS()->Options( 'allow_notif_select_pm_type', 'sms_notif_settings', '' ) == 'yes' ) {

					if ( ! $pm_type || empty( $pm_type ) ) {
						echo '<img src="' . PS_WOO_SMS_PLUGIN_PATH . '/assets/images/false.png">&nbsp;';
						echo 'حداقل یکی از روش های پیام الزامیست';
						die();
					} else {
						$old_meta = get_post_meta( $product_id, '_hannanstd_sms_notification', true ) ? get_post_meta( $product_id, '_hannanstd_sms_notification', true ) : '';
						$new_meta = $sms_number . '|' . implode( ',', $group ) . '_vsh_' . implode( ',', $pm_type ) . '***';
						if ( empty( $old_meta ) || ( ! empty( $old_meta ) && strpos( $new_meta, $old_meta ) === false ) ) {

							if ( strlen( $new_meta ) > 2 ) {
								$meta = $old_meta . $new_meta;
								update_post_meta( $product_id, '_hannanstd_sms_notification', $meta );
								echo '<img src="' . PS_WOO_SMS_PLUGIN_PATH . '/assets/images/tick.png">&nbsp;';
								echo 'شماره شما ثبت شد';
								die();
							} else {
								echo '<img src="' . PS_WOO_SMS_PLUGIN_PATH . '/assets/images/false.png">&nbsp;';
								echo 'این شماره پیش تر ثبت شده است';
								die();
							}
						} else {
							echo '<img src="' . PS_WOO_SMS_PLUGIN_PATH . '/assets/images/false.png">&nbsp;';
							echo 'این شماره پیش تر ثبت شده است';
							die();
						}
					}
				} else {

					$old_meta = get_post_meta( $product_id, '_hannanstd_sms_notification', true ) ? get_post_meta( $product_id, '_hannanstd_sms_notification', true ) : '';
					$new_meta = $sms_number . '|' . implode( ',', $group ) . '***';
					if ( empty( $old_meta ) || ( ! empty( $old_meta ) && strpos( $new_meta, $old_meta ) === false ) ) {
						if ( strlen( $new_meta ) > 2 ) {
							$meta = $old_meta . $new_meta;
							update_post_meta( $product_id, '_hannanstd_sms_notification', $meta );
							echo '<img src="' . PS_WOO_SMS_PLUGIN_PATH . '/assets/images/tick.png">&nbsp;';
							echo 'شماره شما ثبت شد';
							die();
						} else {
							echo '<img src="' . PS_WOO_SMS_PLUGIN_PATH . '/assets/images/false.png">&nbsp;';
							echo 'این شماره پیش تر ثبت شده است';
							die();
						}
					} else {
						echo '<img src="' . PS_WOO_SMS_PLUGIN_PATH . '/assets/images/false.png">&nbsp;';
						echo 'این شماره پیش تر ثبت شده است';
						die();
					}
				}
			}
		} else {
			echo 'خطایی در ثبت اطلاعات رخ داده است';
			die();
		}
	}


	// وقتی محصول فروش ویژه شد : کاربر
	function smsIsOnSale( $product ) {
		$product_id = PWooSMS()->ProductId( $product );

		if ( ( ( get_post_meta( $product_id, '_is_sms_set', true ) && get_post_meta( $product_id, '_enable_onsale', true ) == 'on' ) ||
		       ( ! get_post_meta( $product_id, '_is_sms_set', true ) && PWooSMS()->Options( 'enable_onsale', 'sms_notif_settings', '' ) == 'on' ) ) &&
		     ( ( PWooSMS()->Options( 'enable_sms', 'sms_main_settings', 'no' ) == 'on' && in_array( 'sms', ( (array) PWooSMS()->Options( 'pm_type_notif', 'sms_notif_settings', array() ) ) ) ) ||
		       ( PWooSMS()->Options( 'enable_tg', 'sms_main_settings', 'no' ) == 'on' && in_array( 'tg', ( (array) PWooSMS()->Options( 'pm_type_notif', 'sms_notif_settings', array() ) ) ) ) )
		) {

			if ( get_post_meta( $product_id, '_onsale_send', true ) == 'yes' ) {
				return;
			}

			$product_metas    = get_post_meta( $product_id, '_hannanstd_sms_notification', true ) ? get_post_meta( $product_id, '_hannanstd_sms_notification', true ) : '';
			$contacts         = explode( '***', $product_metas );
			$numbers_list_sms = array();
			$numbers_list_tg  = array();
			foreach ( (array) $contacts as $contact_type ) {
				$contact_types = explode( '_vsh_', $contact_type );
				if ( count( $contact_types ) == 2 ) {
					list( $contact, $type ) = $contact_types;
				} else {
					$contact = $contact_type;
					$type    = '';
				}

				if ( strlen( $contact ) < 2 ) {
					break;
				}

				list( $number, $groups ) = explode( '|', $contact );
				$groups = explode( ',', $groups );
				$type   = $type == '' ? '' : explode( ',', $type );

				if ( in_array( '_onsale', $groups ) ) {
					if ( strlen( $number ) > 5 ) {
						if ( empty( $type ) || ( ! empty( $type ) && in_array( '_sms', $type ) ) ) {
							$numbers_list_sms[] = $number;
						}
						if ( empty( $type ) || ( ! empty( $type ) && in_array( '_tg', $type ) ) ) {
							$numbers_list_tg[] = $number;
						}
					}
				}
			}
			$numbers_list_sms = array_unique( explode( ',', implode( ',', $numbers_list_sms ) ) );
			$numbers_list_tg  = array_unique( explode( ',', implode( ',', $numbers_list_tg ) ) );

			$receiver_sms_data['sms_body'] = get_post_meta( $product_id, '_is_sms_set', true ) ? get_post_meta( $product_id, '_notif_onsale_sms', true ) : PWooSMS()->Options( 'notif_onsale_sms', 'sms_notif_settings', '' );
			$receiver_sms_data['sms_body'] = PWooSMS()->ReplaceTags( $receiver_sms_data['sms_body'], $product_id );

			if ( PWooSMS()->Options( 'enable_sms', 'sms_main_settings', 'no' ) == 'on' && in_array( 'sms', ( (array) PWooSMS()->Options( 'pm_type_notif', 'sms_notif_settings', array() ) ) ) ) {
				$active_sms_gateway          = PWooSMS()->Options( 'sms_gateway', 'sms_main_settings', '' );
				$receiver_sms_data['number'] = $numbers_list_sms;

				$receiver_sms_data['number'] = PWooSMS()->EnglishNumberMobile( $receiver_sms_data['number'] );

				$receiver_response_sms = WoocommerceIR_Gateways_SMS::init()->$active_sms_gateway( $receiver_sms_data );
			}

			if ( PWooSMS()->Options( 'enable_tg', 'sms_main_settings', 'no' ) == 'on' && in_array( 'tg', ( (array) PWooSMS()->Options( 'pm_type_notif', 'sms_notif_settings', array() ) ) ) ) {
				$active_tg_gateway           = PWooSMS()->Options( 'tg_gateway', 'sms_main_settings', '' );
				$receiver_sms_data['number'] = $numbers_list_tg;

				$receiver_sms_data['number'] = PWooSMS()->EnglishNumberMobile( $receiver_sms_data['number'] );

				$receiver_response_tg = WoocommerceIR_Gateways_SMS::init()->$active_tg_gateway( $receiver_sms_data );
			}

			if ( ! empty( $receiver_response_sms ) || ! empty( $receiver_response_tg ) ) {
				update_post_meta( $product_id, '_onsale_send', 'yes' );
			}
		}
	}

	// وقتی محصول موجود شد : کاربر
	function smsInStock( $product_id ) {
		$product = wc_get_product( $product_id );
		if ( ( get_post_meta( $product_id, '_is_sms_set', true ) && get_post_meta( $product_id, '_enable_notif_no_stock', true ) == 'on' ) ||
		     ( ! get_post_meta( $product_id, '_is_sms_set', true ) && PWooSMS()->Options( 'enable_notif_no_stock', 'sms_notif_settings', '' ) == 'on' ) &&
		     ( ( PWooSMS()->Options( 'enable_sms', 'sms_main_settings', 'no' ) == 'on' && in_array( 'sms', ( (array) PWooSMS()->Options( 'pm_type_notif', 'sms_notif_settings', array() ) ) ) ) ||
		       ( PWooSMS()->Options( 'enable_tg', 'sms_main_settings', 'no' ) == 'on' && in_array( 'tg', ( (array) PWooSMS()->Options( 'pm_type_notif', 'sms_notif_settings', array() ) ) ) ) ) ) {

			if ( ! $product->is_in_stock() ) {
				update_post_meta( $product_id, '_in_stock_send', 'no' );

				return;
			}

			if ( get_post_meta( $product_id, '_in_stock_send', true ) == 'yes' ) {
				return;
			}

			$product_metas    = get_post_meta( $product_id, '_hannanstd_sms_notification', true ) ? get_post_meta( $product_id, '_hannanstd_sms_notification', true ) : '';
			$contacts         = explode( '***', $product_metas );
			$numbers_list_sms = array();
			$numbers_list_tg  = array();
			foreach ( (array) $contacts as $contact_type ) {
				$contact_types = explode( '_vsh_', $contact_type );
				if ( count( $contact_types ) == 2 ) {
					list( $contact, $type ) = $contact_types;
				} else {
					$contact = $contact_type;
					$type    = '';
				}

				if ( strlen( $contact ) < 2 ) {
					break;
				}

				list( $number, $groups ) = explode( '|', $contact );
				$groups = explode( ',', $groups );
				$type   = $type == '' ? '' : explode( ',', $type );

				if ( in_array( '_in', $groups ) ) {
					if ( strlen( $number ) > 5 ) {
						if ( empty( $type ) || ( ! empty( $type ) && in_array( '_sms', $type ) ) ) {
							$numbers_list_sms[] = $number;
						}
						if ( empty( $type ) || ( ! empty( $type ) && in_array( '_tg', $type ) ) ) {
							$numbers_list_tg[] = $number;
						}
					}
				}
			}

			$numbers_list_sms = array_unique( explode( ',', str_ireplace( ',,', ',', implode( ',', $numbers_list_sms ) ) ) );
			$numbers_list_tg  = array_unique( explode( ',', str_ireplace( ',,', ',', implode( ',', $numbers_list_tg ) ) ) );

			$receiver_sms_data['sms_body'] = get_post_meta( $product_id, '_is_sms_set', true ) ? get_post_meta( $product_id, '_notif_no_stock_sms', true ) : PWooSMS()->Options( 'notif_no_stock_sms', 'sms_notif_settings', '' );
			$receiver_sms_data['sms_body'] = PWooSMS()->ReplaceTags( $receiver_sms_data['sms_body'], $product_id );


			if ( PWooSMS()->Options( 'enable_sms', 'sms_main_settings', 'no' ) == 'on' && in_array( 'sms', ( (array) PWooSMS()->Options( 'pm_type_notif', 'sms_notif_settings', array() ) ) ) ) {
				$active_sms_gateway          = PWooSMS()->Options( 'sms_gateway', 'sms_main_settings', '' );
				$receiver_sms_data['number'] = $numbers_list_sms;

				$receiver_sms_data['number'] = PWooSMS()->EnglishNumberMobile( $receiver_sms_data['number'] );

				$receiver_response_sms = WoocommerceIR_Gateways_SMS::init()->$active_sms_gateway( $receiver_sms_data );
			}

			if ( PWooSMS()->Options( 'enable_tg', 'sms_main_settings', 'no' ) == 'on' && in_array( 'tg', ( (array) PWooSMS()->Options( 'pm_type_notif', 'sms_notif_settings', array() ) ) ) ) {
				$active_tg_gateway           = PWooSMS()->Options( 'tg_gateway', 'sms_main_settings', '' );
				$receiver_sms_data['number'] = $numbers_list_tg;

				$receiver_sms_data['number'] = PWooSMS()->EnglishNumberMobile( $receiver_sms_data['number'] );

				$receiver_response_tg = WoocommerceIR_Gateways_SMS::init()->$active_tg_gateway( $receiver_sms_data );
			}


			if ( ! empty( $receiver_response_sms ) || ! empty( $receiver_response_tg ) ) {
				update_post_meta( $product_id, '_in_stock_send', 'yes' );
			}
		}
	}


	// وقتی موجودی انبار کم شد : مدیر و کاربر
	function smsIsLowStock( $product ) {

		$product_id = PWooSMS()->ProductId( $product );
		if ( get_post_meta( $product_id, '_low_stock_send', true ) == 'yes' ) {
			return;
		}

		// کاربر
		if ( ( get_post_meta( $product_id, '_is_sms_set', true ) && get_post_meta( $product_id, '_enable_notif_low_stock', true ) == 'on' ) ||
		     ( ! get_post_meta( $product_id, '_is_sms_set', true ) && PWooSMS()->Options( 'enable_notif_low_stock', 'sms_notif_settings', '' ) == 'on' ) &&
		     ( ( PWooSMS()->Options( 'enable_sms', 'sms_main_settings', 'no' ) == 'on' && in_array( 'sms', ( (array) PWooSMS()->Options( 'pm_type_notif', 'sms_notif_settings', array() ) ) ) ) ||
		       ( PWooSMS()->Options( 'enable_tg', 'sms_main_settings', 'no' ) == 'on' && in_array( 'tg', ( (array) PWooSMS()->Options( 'pm_type_notif', 'sms_notif_settings', array() ) ) ) ) ) ) {

			$product_metas    = get_post_meta( $product_id, '_hannanstd_sms_notification', true ) ? get_post_meta( $product_id, '_hannanstd_sms_notification', true ) : '';
			$contacts         = explode( '***', $product_metas );
			$numbers_list_sms = array();
			$numbers_list_tg  = array();
			foreach ( (array) $contacts as $contact_type ) {
				$contact_types = explode( '_vsh_', $contact_type );
				if ( count( $contact_types ) == 2 ) {
					list( $contact, $type ) = $contact_types;
				} else {
					$contact = $contact_type;
					$type    = '';
				}

				if ( strlen( $contact ) < 2 ) {
					break;
				}

				list( $number, $groups ) = explode( '|', $contact );
				$groups = explode( ',', $groups );
				$type   = $type == '' ? '' : explode( ',', $type );


				if ( in_array( '_low', $groups ) ) {
					if ( strlen( $number ) > 5 ) {
						if ( empty( $type ) || ( ! empty( $type ) && in_array( '_sms', $type ) ) ) {
							$numbers_list_sms[] = $number;
						}
						if ( empty( $type ) || ( ! empty( $type ) && in_array( '_tg', $type ) ) ) {
							$numbers_list_tg[] = $number;
						}
					}
				}
			}

			$numbers_list_sms = array_unique( explode( ',', str_ireplace( ',,', ',', implode( ',', $numbers_list_sms ) ) ) );
			$numbers_list_tg  = array_unique( explode( ',', str_ireplace( ',,', ',', implode( ',', $numbers_list_tg ) ) ) );


			$receiver_sms_data['sms_body'] = get_post_meta( $product_id, '_is_sms_set', true ) ? get_post_meta( $product_id, '_notif_low_stock_sms', true ) : PWooSMS()->Options( 'notif_low_stock_sms', 'sms_notif_settings', '' );
			$receiver_sms_data['sms_body'] = PWooSMS()->ReplaceTags( $receiver_sms_data['sms_body'], $product_id );

			if ( PWooSMS()->Options( 'enable_sms', 'sms_main_settings', 'no' ) == 'on' && in_array( 'sms', ( (array) PWooSMS()->Options( 'pm_type_notif', 'sms_notif_settings', array() ) ) ) ) {
				$active_sms_gateway          = PWooSMS()->Options( 'sms_gateway', 'sms_main_settings', '' );
				$receiver_sms_data['number'] = $numbers_list_sms;

				$receiver_sms_data['number'] = PWooSMS()->EnglishNumberMobile( $receiver_sms_data['number'] );

				$receiver_response_sms = WoocommerceIR_Gateways_SMS::init()->$active_sms_gateway( $receiver_sms_data );
			}

			if ( PWooSMS()->Options( 'enable_tg', 'sms_main_settings', 'no' ) == 'on' && in_array( 'tg', ( (array) PWooSMS()->Options( 'pm_type_notif', 'sms_notif_settings', array() ) ) ) ) {
				$active_tg_gateway           = PWooSMS()->Options( 'tg_gateway', 'sms_main_settings', '' );
				$receiver_sms_data['number'] = $numbers_list_tg;

				$receiver_sms_data['number'] = PWooSMS()->EnglishNumberMobile( $receiver_sms_data['number'] );

				$receiver_response_tg = WoocommerceIR_Gateways_SMS::init()->$active_tg_gateway( $receiver_sms_data );
			}

			if ( ! empty( $receiver_response_sms ) || ! empty( $receiver_response_tg ) ) {
				$user_sent = 'yes';
			} else {
				$user_sent = 'no';
			}
		} else {
			$user_sent = 'yes';
		}


		$numbers_list_sms = array();
		$numbers_list_tg  = array();

		if ( PWooSMS()->Options( 'enable_super_admin_sms', 'sms_super_admin_settings', 'on' ) == 'on' ) {

			if ( in_array( 'low', PWooSMS()->Options( 'super_admin_order_status', 'sms_super_admin_settings', array() ) ) ) {

				if ( PWooSMS()->Options( 'enable_sms', 'sms_main_settings', 'no' ) == 'on' && in_array( 'sms', ( (array) PWooSMS()->Options( 'pm_type_super_admin', 'sms_super_admin_settings', array() ) ) ) ) {
					$numbers_list_sms[] = PWooSMS()->Options( 'super_admin_phone', 'sms_super_admin_settings', '' );
				}

				if ( PWooSMS()->Options( 'enable_tg', 'sms_main_settings', 'no' ) == 'on' && in_array( 'tg', ( (array) PWooSMS()->Options( 'pm_type_super_admin', 'sms_super_admin_settings', array() ) ) ) ) {
					$numbers_list_tg[] = PWooSMS()->Options( 'super_admin_phone_tg', 'sms_super_admin_settings', '' );
				}
			}
		}

		if ( PWooSMS()->Options( 'enable_product_admin_sms', 'sms_product_admin_settings', 'on' ) == 'on' ) {
			$admin_datas = maybe_unserialize( get_post_meta( $product_id, '_hannanstd_woo_products_tabs', true ) );
			foreach ( (array) $admin_datas as $admin_data ) {

				$content = isset( $admin_data['content'] ) ? explode( '-sv-', $admin_data['content'] ) : array();

				if ( in_array( 'low', $content ) && ( in_array( 'sms', $content ) || ( ! in_array( 'sms', $content ) && ! in_array( 'tg', $content ) ) )
				     && ( PWooSMS()->Options( 'enable_sms', 'sms_main_settings', 'no' ) == 'on' && in_array( 'sms', ( (array) PWooSMS()->Options( 'pm_type_product_admin', 'sms_product_admin_settings', array() ) ) ) ) ) {
					$numbers_list_sms[] = $admin_data['title'];
				}

				if ( in_array( 'low', $content ) && in_array( 'tg', $content )
				     && ( PWooSMS()->Options( 'enable_tg', 'sms_main_settings', 'no' ) == 'on' && in_array( 'tg', ( (array) PWooSMS()->Options( 'pm_type_product_admin', 'sms_product_admin_settings', array() ) ) ) ) ) {
					$numbers_list_tg[] = $admin_data['title'];
				}
			}
		}

		$receiver_sms_data['sms_body'] = PWooSMS()->Options( 'admin_low_stock', 'sms_super_admin_settings', '' );
		$receiver_sms_data['sms_body'] = PWooSMS()->ReplaceTags( $receiver_sms_data['sms_body'], $product_id );

		$numbers_sms = array_unique( explode( ',', str_ireplace( ',,', ',', implode( ',', $numbers_list_sms ) ) ) );
		if ( ! empty( $numbers_sms ) ) {
			$active_sms_gateway          = PWooSMS()->Options( 'sms_gateway', 'sms_main_settings', '' );
			$receiver_sms_data['number'] = $numbers_sms;

			$receiver_sms_data['number'] = PWooSMS()->EnglishNumberMobile( $receiver_sms_data['number'] );

			$receiver_response_sms = WoocommerceIR_Gateways_SMS::init()->$active_sms_gateway( $receiver_sms_data );
			if ( $receiver_response_sms ) {
				$admin_send_sms = 'yes';
			} else {
				$admin_send_sms = 'no';
			}

		}


		$numbers_tg = array_unique( explode( ',', str_ireplace( ',,', ',', implode( ',', $numbers_list_tg ) ) ) );
		if ( ! empty( $numbers_tg ) ) {
			$active_tg_gateway           = PWooSMS()->Options( 'tg_gateway', 'sms_main_settings', '' );
			$receiver_sms_data['number'] = $numbers_tg;

			$receiver_sms_data['number'] = PWooSMS()->EnglishNumberMobile( $receiver_sms_data['number'] );

			$receiver_response_tg = WoocommerceIR_Gateways_SMS::init()->$active_tg_gateway( $receiver_sms_data );
			if ( $receiver_response_tg ) {
				$admin_send_tg = 'yes';
			} else {
				$admin_send_tg = 'no';
			}
		}

		$admin_send_sms = ! empty( $admin_send_sms ) ? $admin_send_sms : 'no';
		$admin_send_tg  = ! empty( $admin_send_tg ) ? $admin_send_tg : 'no';

		if ( ( $admin_send_sms == 'yes' || $admin_send_tg == 'yes' ) || $user_sent == 'yes' ) {
			update_post_meta( $product_id, '_low_stock_send', 'yes' );
		}
	}


	// وقتی محصول تمام شد : مدیران
	function smsOutStock( $product_id ) {

		if ( ! metadata_exists( 'post', $product_id, '_stock' ) ) {
			return;
		}

		$product = wc_get_product( $product_id );
		if ( $product->is_in_stock() ) {
			update_post_meta( $product_id, '_out_stock_send_sms', 'no' );
			update_post_meta( $product_id, '_out_stock_send_tg', 'no' );

			return;
		}

		$numbers_list_sms = array();
		$numbers_list_tg  = array();
		if ( PWooSMS()->Options( 'enable_super_admin_sms', 'sms_super_admin_settings', 'on' ) == 'on' ) {
			if ( in_array( 'out', PWooSMS()->Options( 'super_admin_order_status', 'sms_super_admin_settings', array() ) ) ) {

				if ( PWooSMS()->Options( 'enable_sms', 'sms_main_settings', 'no' ) == 'on' && in_array( 'sms', ( (array) PWooSMS()->Options( 'pm_type_super_admin', 'sms_super_admin_settings', array() ) ) ) ) {
					$numbers_list_sms[] = PWooSMS()->Options( 'super_admin_phone', 'sms_super_admin_settings', '' );
				}

				if ( PWooSMS()->Options( 'enable_tg', 'sms_main_settings', 'no' ) == 'on' && in_array( 'tg', ( (array) PWooSMS()->Options( 'pm_type_super_admin', 'sms_super_admin_settings', array() ) ) ) ) {
					$numbers_list_tg[] = PWooSMS()->Options( 'super_admin_phone_tg', 'sms_super_admin_settings', '' );
				}
			}
		}


		if ( PWooSMS()->Options( 'enable_product_admin_sms', 'sms_product_admin_settings', 'on' ) == 'on' ) {
			$admin_datas = maybe_unserialize( get_post_meta( $product_id, '_hannanstd_woo_products_tabs', true ) );
			foreach ( (array) $admin_datas as $admin_data ) {


				$content = isset( $admin_data['content'] ) ? explode( '-sv-', $admin_data['content'] ) : array();

				if ( in_array( 'out', $content ) && ( in_array( 'sms', $content ) || ( ! in_array( 'sms', $content ) && ! in_array( 'tg', $content ) ) )
				     && ( PWooSMS()->Options( 'enable_sms', 'sms_main_settings', 'no' ) == 'on' && in_array( 'sms', ( (array) PWooSMS()->Options( 'pm_type_product_admin', 'sms_product_admin_settings', array() ) ) ) ) ) {
					$numbers_list_sms[] = $admin_data['title'];
				}

				if ( in_array( 'out', $content ) && in_array( 'tg', $content )
				     && ( PWooSMS()->Options( 'enable_tg', 'sms_main_settings', 'no' ) == 'on' && in_array( 'tg', ( (array) PWooSMS()->Options( 'pm_type_product_admin', 'sms_product_admin_settings', array() ) ) ) ) ) {
					$numbers_list_tg[] = $admin_data['title'];
				}
			}
		}

		$receiver_sms_data['sms_body'] = PWooSMS()->Options( 'admin_out_stock', 'sms_super_admin_settings', '' );
		$receiver_sms_data['sms_body'] = PWooSMS()->ReplaceTags( $receiver_sms_data['sms_body'], $product_id );


		$numbers_sms = array_unique( explode( ',', str_ireplace( ',,', ',', implode( ',', $numbers_list_sms ) ) ) );

		if ( ! empty( $numbers_sms ) && get_post_meta( $product_id, '_out_stock_send_sms', true ) != 'yes' ) {
			$active_sms_gateway          = PWooSMS()->Options( 'sms_gateway', 'sms_main_settings', '' );
			$receiver_sms_data['number'] = $numbers_sms;

			$receiver_sms_data['number'] = PWooSMS()->EnglishNumberMobile( $receiver_sms_data['number'] );

			$receiver_response_sms = WoocommerceIR_Gateways_SMS::init()->$active_sms_gateway( $receiver_sms_data );
			if ( $receiver_response_sms ) {
				update_post_meta( $product_id, '_out_stock_send_sms', 'yes' );
			}
		}


		$numbers_tg = array_unique( explode( ',', str_ireplace( ',,', ',', implode( ',', $numbers_list_tg ) ) ) );

		if ( ! empty( $numbers_tg ) && get_post_meta( $product_id, '_out_stock_send_tg', true ) != 'yes' ) {
			$active_tg_gateway           = PWooSMS()->Options( 'tg_gateway', 'sms_main_settings', '' );
			$receiver_sms_data['number'] = $numbers_tg;

			$receiver_sms_data['number'] = PWooSMS()->EnglishNumberMobile( $receiver_sms_data['number'] );

			$receiver_response_tg = WoocommerceIR_Gateways_SMS::init()->$active_tg_gateway( $receiver_sms_data );
			if ( $receiver_response_tg ) {
				update_post_meta( $product_id, '_out_stock_send_tg', 'yes' );
			}
		}
	}
}