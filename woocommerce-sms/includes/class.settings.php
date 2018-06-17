<?php

class WoocommerceIR_Settings_SMS {

	private $settings_api;

	function __construct() {

		$this->settings_api = new WoocommerceIR_Settings_Api;

		if ( is_admin() ) {

			//add_action( 'admin_notices', array( $this, 'adminNotice' ) );

			add_action( 'admin_init', array( $this, 'admin_init' ) );
			add_action( 'admin_menu', array( $this, 'adminMenu' ), 60 );
			add_action( 'admin_enqueue_scripts', array( $this, 'script' ) );

			add_action( 'ps_woo_sms_form_submit_sms_main_settings', array( $this, 'submitButton' ) );
			add_action( 'ps_woo_sms_form_submit_sms_buyer_settings', array( $this, 'submitButton' ) );
			add_action( 'ps_woo_sms_form_submit_sms_super_admin_settings', array( $this, 'submitButton' ) );
			add_action( 'ps_woo_sms_form_submit_sms_product_admin_settings', array( $this, 'submitButton' ) );
			add_action( 'ps_woo_sms_form_submit_sms_notif_settings', array( $this, 'submitButton' ) );

			add_filter( 'sms_buyer_settings_settings', array( $this, 'buyerSettings' ) );
			add_filter( 'sms_super_admin_settings_settings', array( $this, 'superAdminSettings' ) );
			add_filter( 'sms_product_admin_settings_settings', array( $this, 'productAdminSettings' ) );
			add_filter( 'admin_footer_text', array( $this, 'footerNote' ) );
			add_filter( 'update_footer', array( $this, 'footerVersion' ), 11 );


			add_action( 'admin_init', array( 'WoocommerceIR_Bulk_SMS', 'bulkSend' ), 11 );
			add_action( 'ps_woo_sms_form_bottom_persianwoosms_send', array( 'WoocommerceIR_Bulk_SMS', 'bulkForm' ) );
			add_action( 'admin_footer', array( 'WoocommerceIR_Bulk_SMS', 'bulkScript' ), 10 );
			add_action( 'load-edit.php', array( 'WoocommerceIR_Bulk_SMS', 'bulkAction' ) );
		}

		if ( class_exists( 'WoocommerceIR_Gateways_SMS' ) ) {
			new WoocommerceIR_Gateways_SMS();
		}

		if ( PWooSMS()->Options( 'enable_admin_bar', 'sms_main_settings', 'off' ) == 'on' && is_admin() ) {
			add_action( 'wp_before_admin_bar_render', array( $this, 'adminBar' ) );
		}

		if ( PWooSMS()->Options( 'enable_plugins', 'sms_main_settings', 'off' ) == 'off' ) {
			return;
		}

		if ( PWooSMS()->Options( 'enable_buyer', 'sms_buyer_settings', 'off' ) == 'on'
		     || PWooSMS()->Options( 'enable_super_admin_sms', 'sms_super_admin_settings', 'off' ) == 'on'
		     || PWooSMS()->Options( 'enable_product_admin_sms', 'sms_product_admin_settings', 'off' ) == 'on' ) {
			require_once PS_WOO_SMS_PLUGIN_LIB_PATH . '/class.order.php';
			if ( class_exists( 'WoocommerceIR_Order_SMS' ) ) {
				new WoocommerceIR_Order_SMS();
			}
		}

		if ( ( PWooSMS()->Options( 'enable_metabox', 'sms_buyer_settings', 'off' ) == 'on' || PWooSMS()->Options( 'enable_notif_sms_main', 'sms_notif_settings', 'off' ) == 'on' ) && is_admin() ) {
			require_once PS_WOO_SMS_PLUGIN_LIB_PATH . '/class.metabox.php';
			if ( class_exists( 'WoocommerceIR_Metabox_SMS' ) ) {
				new WoocommerceIR_Metabox_SMS();
			}
		}

		if ( ( PWooSMS()->Options( 'enable_product_admin_sms', 'sms_product_admin_settings', 'off' ) == 'on' || PWooSMS()->Options( 'enable_notif_sms_main', 'sms_notif_settings', 'off' ) == 'on' ) && is_admin() ) {
			require_once PS_WOO_SMS_PLUGIN_LIB_PATH . '/class.products.tab.php';
			if ( class_exists( 'WoocommerceIR_Tab_SMS' ) ) {
				new WoocommerceIR_Tab_SMS();
			}
		}

		if ( PWooSMS()->Options( 'enable_notif_sms_main', 'sms_notif_settings', 'off' ) == 'on'
		     || PWooSMS()->Options( 'enable_super_admin_sms', 'sms_super_admin_settings', 'off' ) == 'on'
		     || PWooSMS()->Options( 'enable_product_admin_sms', 'sms_product_admin_settings', 'off' ) == 'on' ) {
			require_once PS_WOO_SMS_PLUGIN_LIB_PATH . '/class.notifications.php';
			if ( class_exists( 'WoocommerceIR_Notification_SMS' ) ) {
				new WoocommerceIR_Notification_SMS();
			}
		}

		if ( PWooSMS()->Options( 'enable_notif_sms_main', 'sms_notif_settings', 'off' ) == 'on' ) {
			require_once PS_WOO_SMS_PLUGIN_LIB_PATH . '/class.widget.php';
		}


	}

	public static function init() {
		static $instance = false;

		return $instance = ( ! $instance ? new WoocommerceIR_Settings_SMS() : $instance );
	}

	public function script() {
		global $post;
		if ( is_object( $post ) && ( $post->post_type == 'shop_order' || $post->post_type == 'product' ) ) {
			wp_enqueue_style( 'admin-persianwoosms-styles', PS_WOO_SMS_PLUGIN_PATH . '/assets/css/admin.css', false, date( 'Ymd' ) );
			wp_enqueue_script( 'admin-persianwoosms-scripts', PS_WOO_SMS_PLUGIN_PATH . '/assets/js/admin_script.js', array( 'jquery' ), false, true );
			wp_localize_script( 'admin-persianwoosms-scripts', 'persianwoosms', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
		}
		if ( is_object( $post ) && ( $post->post_type == 'product' ) and ( PWooSMS()->Options( 'enable_plugins', 'sms_main_settings', 'off' ) != 'off' ) ) {
			wp_register_script( 'repeatable-sms-tabs', PS_WOO_SMS_PLUGIN_PATH . '/assets/js/tab_sms.js', array( 'jquery' ), 'all' );
			wp_enqueue_script( 'repeatable-sms-tabs' );
			wp_register_style( 'repeatable-sms-tabs-styles', PS_WOO_SMS_PLUGIN_PATH . '/assets/css/tab_sms.css', '', 'all' );
			wp_enqueue_style( 'repeatable-sms-tabs-styles' );
		}
	}

	function admin_init() {
		$this->settings_api->set_sections( $this->settingSections() );
		$this->settings_api->set_fields( $this->settingFields() );
		$this->settings_api->admin_init();

		if ( get_option( 'redirect_to_woo_sms_about_page_check' ) != 'yes' ) {
			ob_start();
			if ( ! headers_sent() ) {
				wp_redirect( admin_url( 'index.php?page=about-persian-woocommerce-sms-pro' ) );
			} else {
				update_option( 'redirect_to_woo_sms_about_page_check', 'yes' );
				update_option( 'redirect_to_woo_sms_about_page', 'yes' );
			}
		} else {
			update_option( 'redirect_to_woo_sms_about_page', 'yes' );
		}


	}


	function adminNotice() {

		//after update		
		$sms = PWooSMS()->Options( 'enable_sms', 'sms_main_settings', '' );
		$tg  = PWooSMS()->Options( 'enable_tg', 'sms_main_settings', '' );

		if ( empty( $sms ) && empty( $tg ) ) {
			echo '<div class="error"><p>برای استفاده از افزونه پیامک ووکامرس می بایست هر کدام از تنظیمات عمومی ، خریدار ، مدیر ، مدیر محصول و اطلاع رسانی را مجددا به صورت مجزا ذخیره نمایید و روش ارسال پیامک به صورت اس ام اس یا تلگرام را ست نمایید .
			<a href="http://forum.persianscript.ir/topic/16615-تاپیک-پشتیبانی-نسخه-3-افزونه-پیامک-ووکامرس/?p=94519" target="_blank">مشاهده راهنما الزامی است . کلیک نمایید .</a>
			</p></div>';
		}

	}

	function adminMenu() {

		$ps_version = defined( 'PERSIAN_WOOCOMMERCE_VERSION' ) ? PERSIAN_WOOCOMMERCE_VERSION : '';

		if ( empty( $ps_version ) ) {
			$ps_version = defined( 'PW_VERSION' ) ? PW_VERSION : '';
		}

		if ( ! empty( $ps_version ) && version_compare( $ps_version, '2.4.9', '>' ) ) {
			add_submenu_page( 'persian-wc', 'تنظیمات پیامک', 'تنظیمات پیامک', 'manage_woocommerce', 'persian-woocommerce-sms-pro', array(
				$this,
				'settingPage'
			) );
		} else {
			add_submenu_page( 'woocommerce', 'تنظیمات پیامک', 'تنظیمات پیامک', 'manage_woocommerce', 'persian-woocommerce-sms-pro', array(
				$this,
				'settingPage'
			) );
		}

		// if ( get_option( 'redirect_to_woo_sms_about_page' ) != 'yes' ) {
			add_submenu_page( 'index.php', 'درباره پیامک ووکامرس', 'پیامک ووکامرس', 'read', 'about-persian-woocommerce-sms-pro', array(
				$this,
				'aboutPage'
			) );
		// }
	}


	function aboutPage() {
		update_option( 'redirect_to_woo_sms_about_page_check', 'yes' );
		include PS_WOO_SMS_PLUGIN_LIB_PATH . '/about.php';
	}

	function settingPage() {
		echo '<div class="wrap">';
		$this->settings_api->show_navigation();
		$this->settings_api->show_forms();
		echo '</div>';
	}

	function adminBar() {
		global $wp_admin_bar;
		if ( current_user_can( 'manage_woocommerce' ) && is_admin_bar_showing() ) {
			$wp_admin_bar->add_menu( array(
				'id'    => 'adminBar_send',
				'title' => '<span class="ab-icon"></span>ارسال پیامک ووکامرس',
				'href'  => admin_url( 'admin.php?page=persian-woocommerce-sms-pro&send=true' ),
			) );
		}
	}

	function settingSections() {
		$sections = array(
			array(
				'id'    => 'sms_main_settings',
				'title' => 'همگانی'
			),
			array(
				'id'    => 'sms_buyer_settings',
				'title' => 'خریدار'
			),
			array(
				'id'    => 'sms_super_admin_settings',
				'title' => 'مدیر کل'
			),
			array(
				'id'    => 'sms_product_admin_settings',
				'title' => 'مدیر محصول'
			),
			array(
				'id'    => 'sms_notif_settings',
				'title' => 'اطلاع رسانی'
			),
			array(
				'id'    => 'persianwoosms_send',
				'title' => 'ارسال پیامک'
			)
		);

		return apply_filters( 'persianwoosms_settings_sections', $sections );
	}

	function settingFields() {
		$settings_fields = array(

			'sms_main_settings' => apply_filters( 'sms_main_settings_settings', array(
				array(
					'name'  => 'enable_plugins',
					'label' => 'فعال سازی کلی افزونه پیامک',
					'desc'  => 'در صورت فعالسازی این گزینه قابلیت ارسال پیامک به ووکامرس اضافه خواهد شد .',
					'type'  => 'checkbox',
				),
				array(
					'name'  => 'enable_admin_bar',
					'label' => 'لینک ارسال پیامک در ادمین بار',
					'desc'  => 'در صورت فعالسازی این گزینه لینک ارسال پیامک جهت دسترسی سریع تر به ادمین بار اضافه خواهد شد .',
					'type'  => 'checkbox',
				),
				array(
					'name'  => 'header_1',
					'label' => 'تنظیمات وب سرویس پیامک',
					'desc'  => '<hr/>',
					'type'  => 'html',
				),
				array(
					'name'  => 'note',
					'label' => 'تذکر',
					'desc'  => 'ووکامرس پارسی ، مسئولیتی در قبال هیچ یک از پنل های پیامک ندارد. تمامی مسئولیت های هزینه ها ، پاسخگویی و پشتیبانی بر عهده ارائه دهنده پنل می باشد .',
					'type'  => 'html',
				),
				array(
					'name'  => 'enable_sms',
					'label' => 'وبسرویس پیامکی',
					'desc'  => 'فعالسازی وبسرویس پیامک',
					'type'  => 'checkbox',
				),
				array(
					'name'    => 'sms_gateway',
					'label'   => 'انتخاب وب سرویس پیامک',
					'type'    => 'select',
					'default' => '-1',
					'options' => class_exists( 'WoocommerceIR_Gateways_SMS' ) ? WoocommerceIR_Gateways_SMS::get_sms_gateway() : array(),
				),
				array(
					'name'  => 'persian_woo_sms_username',
					'label' => 'نام کاربری پنل پیامک',
					'type'  => 'text',
				),
				array(
					'name'  => 'persian_woo_sms_password',
					'label' => 'کلمه عبور پنل پیامک',
					'type'  => 'text',
				),
				array(
					'name'  => 'persian_woo_sms_sender',
					'label' => 'شماره ارسال کننده پیامک',
					'type'  => 'text',
				),

				array(
					'name'  => 'header_tg',
					'label' => 'تنظیمات وب سرویس تلگرام',
					'desc'  => '<hr/>',
					'type'  => 'html',
				),
				array(
					'name'  => 'note_tg',
					'label' => 'تذکر',
					'desc'  => 'ووکامرس پارسی ، مسئولیتی در قبال هیچ یک از پنل های تلگرامی ندارد. تمامی مسئولیت های هزینه ها ، پاسخگویی و پشتیبانی بر عهده ارائه دهنده پنل می باشد . <a href="http://forum.persianscript.ir/topic/16760-%D8%A7%D8%B6%D8%A7%D9%81%D9%87-%D8%B4%D8%AF%D9%86-%D9%BE%D9%86%D9%84-%D9%87%D8%A7%DB%8C-%D8%AA%D9%84%DA%AF%D8%B1%D8%A7%D9%85%DB%8C-%D8%A8%D9%87-%D9%BE%DB%8C%D8%A7%D9%85%DA%A9-%D9%88%D9%88%DA%A9%D8%A7%D9%85%D8%B1%D8%B3%D8%9F/" target="_blank" >برای اضافه شدن پلن خود کلیک کنید .</a>
					',
					'type'  => 'html',
				),
				array(
					'name'  => 'enable_tg',
					'label' => 'وبسرویس تلگرام',
					'desc'  => 'فعالسازی وبسرویس تلگرام',
					'type'  => 'checkbox',
				),
				array(
					'name'    => 'tg_gateway',
					'label'   => 'انتخاب وب سرویس تلگرام',
					'type'    => 'select',
					'default' => '-1',
					'options' => class_exists( 'WoocommerceIR_Gateways_SMS' ) ? WoocommerceIR_Gateways_SMS::get_tg_gateway() : array(),
				),
				array(
					'name'  => 'persian_woo_tg_username',
					'label' => 'نام کاربری پنل تلگرام',
					'type'  => 'text',
				),
				array(
					'name'  => 'persian_woo_tg_password',
					'label' => 'کلمه عبور پنل تلگرام',
					'type'  => 'text',
				),
				array(
					'name'  => 'persian_woo_tg_sender',
					'label' => 'شماره ارسال کننده تلگرام',
					'type'  => 'text',
					'desc'  => '<br/>در صورت عدم دریافت این شماره از سایت سامانه آن را خالی رها کنید .',
				),


			) ),

			'sms_buyer_settings' => apply_filters( 'sms_buyer_settings_settings', array(
				array(
					'name'  => 'enable_buyer',
					'label' => 'ارسال پیام به خریدار',
					'desc'  => 'با انتخاب این گزینه ، در هنگام ثبت و یا تغییر وضعیت سفارش ، برای خریدار پیام ارسال می گردد .',
					'type'  => 'checkbox',
				),

				array(
					'name'    => 'buyer_phone_label',
					'label'   => 'عنوان فیلد شماره موبایل',
					'desc'    => '<br/>این عنوان در صفحه تسویه حساب نمایش داده خواهد شد و جایگزین کلمه ی "تلفن" میگردد .',
					'type'    => 'text',
					'default' => 'تلفن همراه',
				),
				array(
					'name'    => 'force_enable_buyer',
					'label'   => 'اختیاری بودن دریافت پیام',
					'desc'    => 'فقط در صورت فعال سازی این قسمت ، گزینه "میخواهم از وضعیت سفارش از طریق پیامک آگاه شوم" در صفحه تسویه حساب نمایش داده خواهد شد و در غیر این صورت پیامک همواره ارسال خواهد شد .',
					'type'    => 'select',
					'default' => 'yes',
					'options' => array(
						'yes' => 'خیر',
						'no'  => 'بله' // inja no mishe bale , yes mishe kheyr :D ... doroste . moshkeli nis .
					)
				),
				array(
					'name'    => 'buyer_checkbox_text',
					'label'   => 'متن پذیرش دریافت پیام',
					'desc'    => '<br/>این متن بالای چک باکس انتخاب دریافت پیامک در صفحه تسویه حساب نمایش داده خواهد شد .',
					'type'    => 'text',
					'default' => 'میخواهم از وضعیت سفارش از طریق اس ام اس یا تلگرام آگاه شوم .'
				),
				array(
					'name'  => 'enable_metabox',
					'label' => 'متاباکس ارسال پیام',
					'desc'  => 'با انتخاب این گزینه ، در صفحه سفارشات متاباکس ارسال پیام به خریداران اضافه میشود .',
					'type'  => 'checkbox',
				),

				array(
					'name'  => 'header_type',
					'label' => 'روش ارسال پیام',
					'desc'  => '<hr/>',
					'type'  => 'html',
				),
				array(
					'name'    => 'pm_type_buyer',
					'label'   => 'روش ارسال پیام مجاز',
					'desc'    => 'می توانید مشخص کنید پیام ها از چه طریقی به کاربر ارسال شوند .',
					'type'    => 'multicheck',
					'options' => array( 'sms' => 'اس ام اس', 'tg' => 'تلگرام' ),
				),
				array(
					'name'    => 'allow_buyer_select_pm_type',
					'label'   => 'اجازه به انتخاب روش پیام',
					'desc'    => 'با فعالسازی این گزینه خریدار میتواند روش دریافت پیام از طریق اس ام اس یا تلگرام را مشخص نماید .',
					'type'    => 'select',
					'default' => 'no',
					'options' => array(
						'yes' => 'بله',
						'no'  => 'خیر'
					)
				),
				array(
					'name'    => 'buyer_select_pm_type_text',
					'label'   => 'متن انتخاب روش ارسال پیام',
					'desc'    => '<br/>این عنوان در صفحه تسویه حساب و بالای قسمت انتخاب نوع دریافت پیام توسط خریدار نمایش داده خواهد شد .',
					'type'    => 'text',
					'default' => 'روش دریافت پیام',
				),

				array(
					'name'  => 'header_2',
					'label' => 'وضعیت های دریافت پیام',
					'desc'  => '<hr/>',
					'type'  => 'html',
				),
				array(
					'name'    => 'order_status',
					'label'   => 'وضعیت های سفارش مجاز',
					'desc'    => 'می توانید مشخص کنید خریدار در چه وضعیتی می توانند پیامک دریافت کنند.',
					'type'    => 'multicheck',
					'options' => PWooSMS()->GetAllStatuses(),
				),
				array(
					'name'    => 'allow_buyer_select_status',
					'label'   => 'اجازه به انتخاب وضعیت ها توسط خریدار',
					'desc'    => 'با فعالسازی این گزینه ، خریدار میتواند در صفحه تسویه حساب وضعیت های دلخواه خود را از میان وضعیت های مجاز برای دریافت پیامک را انتخاب نماید . در صورت عدم فعالسازی این قسمت ، در تمام وضعیت های تیک خورده بالا پیامک ارسال میشود .',
					'type'    => 'select',
					'default' => 'no',
					'options' => array(
						'yes' => 'بله',
						'no'  => 'خیر'
					)
				),
				array(
					'name'    => 'buyer_status_mode',
					'label'   => 'روش انتخاب وضعیت ها',
					'desc'    => 'این قسمت ملزم به "بله" بودن تنظیمات "اجازه به انتخاب وضعیت ها توسط خریدار" است .',
					'type'    => 'select',
					'default' => 'selector',
					'options' => array(
						'selector' => 'چند انتخابی',
						'checkbox' => 'چک باکس'
					)
				),
				array(
					'name'    => 'force_buyer_select_status',
					'label'   => 'الزامی بودن انتخاب حداقل یک وضعیت',
					'desc'    => 'با فعال سازی این گزینه ، کاربر می بایست حداقل یک وضعیت سفارش را از بین وضعیت های مجاز انتخاب کند . این قسمت نیز ملزم به "بله" بودن تنظیمات "انتخاب وضعیت ها توسط خریدار" است .',
					'type'    => 'select',
					'default' => 'no',
					'options' => array(
						'yes' => 'بله',
						'no'  => 'خیر'
					)
				),
				array(
					'name'    => 'buyer_select_status_text_top',
					'label'   => 'متن بالای انتخاب وضعیت ها',
					'desc'    => '<br/>این متن بالای لیست وضعیت ها در صفحه تسویه حساب برای انتخاب خریدار قرار میگیرد .',
					'type'    => 'text',
					'default' => 'وضعیت هایی که مایل به دریافت پیامک هستید را انتخاب نمایید'
				),
				array(
					'name'    => 'buyer_select_status_text_bellow',
					'label'   => 'متن پایین انتخاب وضعیت ها',
					'desc'    => '<br/>این متن پایین لیست وضعیت ها در صفحه تسویه حساب برای انتخاب خریدار قرار میگیرد .',
					'type'    => 'text',
					'default' => ''
				),
				array(
					'name'  => 'header_3',
					'label' => 'متن پیام خریدار',
					'desc'  => '<hr/>',
					'type'  => 'html',
				),
			) ),

			'sms_super_admin_settings' => apply_filters( 'sms_super_admin_settings_settings', array(

				array(
					'name'    => 'enable_super_admin_sms',
					'label'   => 'ارسال پیام به مدیران اصلی',
					'desc'    => 'با انتخاب این گزینه ، در هنگام ثبت و یا تغییر سفارش ، برای مدیران اصلی سایت پیامک ارسال می گردد .',
					'type'    => 'checkbox',
					'default' => 'no'
				),
				array(
					'name'    => 'pm_type_super_admin',
					'label'   => 'روش ارسال پیام',
					'desc'    => 'می توانید مشخص کنید پیام ها از چه طریقی به کاربر ارسال شوند .',
					'type'    => 'multicheck',
					'options' => array( 'sms' => 'اس ام اس', 'tg' => 'تلگرام' ),
				),
				array(
					'name'  => 'super_admin_phone',
					'label' => 'شماره مدیران اصلی (پیامک)',
					'desc'  => '<br/>شماره ها را با کاما (,) جدا نمایید',
					'type'  => 'text'
				),
				array(
					'name'  => 'super_admin_phone_tg',
					'label' => 'شماره مدیران اصلی (تلگرام)',
					'desc'  => '<br/>شماره ها را با کاما (,) جدا نمایید',
					'type'  => 'text'
				),
				array(
					'name'    => 'super_admin_order_status',
					'label'   => 'وضعیت های دریافت پیام',
					'desc'    => '<br/>می توانید مشخص کنید مدیران اصلی سایت در چه وضعیت هایی پیامک دریافت کنند .',
					'type'    => 'multicheck',
					'options' => PWooSMS()->GetAllSuperAdminStatuses(),
				),
				array(
					'name'  => 'header_super_admin',
					'label' => 'متن پیام مدیر اصلی',
					'type'  => 'html',
					'desc'  => '<hr/>',
				),

			) ),

			'sms_product_admin_settings' => apply_filters( 'sms_product_admin_settings_settings', array(

				array(
					'name'    => 'enable_product_admin_sms',
					'label'   => 'ارسال پیام مدیران محصول',
					'desc'    => 'با انتخاب این گزینه ، در هنگام ثبت و یا تغییر سفارش ، برای مدیران هر محصول پیامک ارسال می گردد .',
					'type'    => 'checkbox',
					'default' => 'no'
				),
				array(
					'name'    => 'pm_type_product_admin',
					'label'   => 'روش ارسال پیام',
					'desc'    => 'می توانید مشخص کنید پیام ها از چه طریقی به کاربر ارسال شوند .',
					'type'    => 'multicheck',
					'options' => array( 'sms' => 'اس ام اس', 'tg' => 'تلگرام' ),
				),
				array(
					'name'  => 'header_product_admin',
					'label' => 'متن پیام مدیران محصول',
					'type'  => 'html',
					'desc'  => '<hr/>',
				),
			) ),

			'sms_notif_settings' => apply_filters( 'sms_notif_settings_settings', array(
				array(
					'name'    => 'enable_notif_sms_main',
					'label'   => 'فعال سازی اطلاع رسانی',
					'desc'    => 'با فعالسازی این گزینه قابلیت اطلاع رسانی پیامکی محصولات به ووکامرس اضافه خواهد شد . و در صورت غیرفعالسازی کلیه قسمت های زیر بی تاثیر خواهند شد .',
					'type'    => 'checkbox',
					'default' => 'no'
				),
				array(
					'name'    => 'pm_type_notif',
					'label'   => 'روش ارسال پیام',
					'desc'    => 'می توانید مشخص کنید پیام ها از چه طریقی به کاربر ارسال شوند .',
					'type'    => 'multicheck',
					'options' => array( 'sms' => 'اس ام اس', 'tg' => 'تلگرام' ),
				),
				array(
					'name'    => 'allow_notif_select_pm_type',
					'label'   => 'اجازه به انتخاب روش پیام',
					'desc'    => 'با فعالسازی این گزینه کاربر میتواند روش دریافت پیام از طریق اس ام اس یا تلگرام را مشخص نماید .',
					'type'    => 'select',
					'default' => 'no',
					'options' => array(
						'yes' => 'بله',
						'no'  => 'خیر'
					)
				),
				array(
					'name'    => 'notif_select_pm_type_text',
					'label'   => 'متن انتخاب روش ارسال پیام',
					'desc'    => '<br/>این عنوان در صفحه محصول نمایش داده خواهد شد و کاربر میتواند نوع دریافت پیام را مشخص کند .<hr/>',
					'type'    => 'text',
					'default' => 'روش دریافت پیام',
				),

				array(
					'name'    => 'notif_old_pr',
					'label'   => 'اعمال محصولات قدیمی',
					'desc'    => 'منظور از محصولات قدیمی محصولاتی هستند که قبل از نسخه جدید افزونه پیامک ایجاد شده اند و تنظیم نشده اند .',
					'type'    => 'select',
					'default' => 'no',
					'options' => array(
						'yes' => 'اعمال تنظیمات پیشفرض بر روی محصولات قدیمی',
						'no'  => 'اطلاع رسانی پیامکی رو برای محصولات قدیمی نادیده بگیر'
					)
				),
				array(
					'name'  => 'header_1',
					'label' => 'تذکر',
					'desc'  => 'کلیه قسمت های زیر تنظیمات پیشفرض بوده و برای هر محصول قابل تنظیم جدا گانه می باشد .<br/><br/>منظور از اطلاع رسانی محصولات ، آگاه سازی کاربران از وضعیت های هر محصول دلخواه شان نظیر ، فروش حراج ، اتمام محصول . ... می باشد . ',
					'type'  => 'html',
				),
				array(
					'name'  => 'header_2',
					'label' => 'نمایش در صفحه محصول',
					'desc'  => '<hr/>',
					'type'  => 'html',
				),
				array(
					'name'    => 'enable_notif_sms',
					'label'   => 'نمایش خودکار',
					'desc'    => 'با فعالسازی این قسمت گزینه "میخواهم از وضعیت محصول توسط پیامک با خبر شوم" در صفحه محصولات اضافه خواهد شد .<br/>
						میتوانید این قسمت "نمایش خودکار" را غیرفعال نمایید و بجای آن از شورت کد [woo_ps_sms] یا ابزارک "اطلاع رسانی پیامکی ووکامرس" در صفحه محصول استفاده نمایید .<br/><br/>
						تذکر : برای جلوگیری از مشکل تداخل  جیکوئری ، در صفحه هر محصول فقط از یکی از حالت های "نمایش خودکار" ، "ابزارک" یا "شورت کد" استفاده نمایید .',
					'type'    => 'checkbox',
					'default' => 'no'
				),
				array(
					'name'    => 'notif_title',
					'label'   => 'متن سر تیتر گزینه ها',
					'desc'    => '<br/>این متن در صفحه محصول به صورت چک باکس ظاهر خواهد شد و کاربر با فعال کردن آن میتواند شماره خود را برای دریافت اطلاعیه آن محصول وارد نماید .',
					'type'    => 'text',
					'default' => "به من از طریق پیامک اطلاع بده"
				),
				array(
					'name'  => 'header_3',
					'label' => 'گزینه های اصلی',
					'desc'  => '<hr/>',
					'type'  => 'html',
				),
				array(
					'name'  => 'header_4',
					'label' => 'شورت کد های قابل استفاده',
					'desc'  => "شورت کد های قابل استفاده در متن پیامک ها :<br/><br/><code>{product_id}</code> : آیدی محصول ، <code>{sku}</code> : شناسه محصول ، <code>{product_title}</code> : عنوان محصول ، <code>{regular_price}</code> قیمت اصلی ، <code>{onsale_price}</code> : قیمت فروش فوق العاده<br/><code>{onsale_from}</code> : تاریخ شروع فروش فوق العاده ، <code>{onsale_to}</code> : تاریخ اتمام فروش فوق العاده ، <code>{stock}</code> : موجودی انبار<hr/>",
					'type'  => 'html',
				),

				array(
					'name'    => 'enable_onsale',
					'label'   => 'زمانیکه محصول حراج شد',
					'desc'    => 'هنگامی که این گزینه فعال باشد در صورت حراج نبودن محصول گزینه "زمانیکه که محصول حراج شد" نیز به لیست گزینه های اطلاع رسانی اضافه خواهد شد .',
					'type'    => 'checkbox',
					'default' => 'no'
				),
				array(
					'name'    => 'notif_onsale_text',
					'label'   => 'متن گزینه "زمانیکه محصول حراج شد"',
					'desc'    => '<br/>میتوانید متن دلخواه خود را جایگزین جمله "زمانیکه محصول حراج شد" نمایید .',
					'type'    => 'text',
					'default' => "زمانیکه محصول حراج شد"
				),
				array(
					'name'    => 'notif_onsale_sms',
					'label'   => 'متن پیامک "زمانیکه محصول حراج شد"',
					'desc'    => '<hr/>',
					'type'    => 'textarea',
					'default' => "سلام\nمحصول {product_title} از قیمت {regular_price} به قیمت {onsale_price} کاهش یافت ."
				),
				array(
					'name'    => 'enable_notif_no_stock',
					'label'   => 'زمانیکه که محصول موجود شد',
					'desc'    => 'هنگامی که این گزینه فعال باشد در صورت ناموجود شدن محصول گزینه "زمانیکه که محصول موجود شد" نیز به لیست گزینه های اطلاع رسانی اضافه خواهد شد .',
					'type'    => 'checkbox',
					'default' => 'no'
				),
				array(
					'name'    => 'notif_no_stock_text',
					'label'   => 'متن گزینه "زمانیکه محصول موجود شد"',
					'desc'    => '<br/>میتوانید متن دلخواه خود را جایگزین جمله "زمانیکه محصول موجود شد" نمایید .',
					'type'    => 'text',
					'default' => "زمانیکه محصول موجود شد"
				),
				array(
					'name'    => 'notif_no_stock_sms',
					'label'   => 'متن پیامک "زمانیکه محصول موجود شد"',
					'desc'    => '<hr/>',
					'type'    => 'textarea',
					'default' => "سلام\nمحصول {product_title} هم اکنون موجود و قابل خرید می باشد ."
				),
				array(
					'name'    => 'enable_notif_low_stock',
					'label'   => 'زمانیکه موجودی انبار محصول کم شد',
					'desc'    => 'هنگامی که این گزینه فعال باشد ، گزینه "زمانیکه که موجودی انبار محصول کم شد" نیز به لیست گزینه های اطلاع رسانی اضافه خواهد شد .',
					'type'    => 'checkbox',
					'default' => 'no'
				),
				array(
					'name'    => 'notif_low_stock_text',
					'label'   => 'متن گزینه "زمانیکه موجودی انبار محصول کم شد"',
					'desc'    => '<br/>میتوانید متن دلخواه خود را جایگزین جمله "زمانیکه موجودی انبار محصول کم شد" نمایید .',
					'type'    => 'text',
					'default' => "زمانیکه موجودی انبار محصول کم شد"
				),
				array(
					'name'    => 'notif_low_stock_sms',
					'label'   => 'متن پیامک "زمانیکه محصول موجودی انبار کم شد"',
					'desc'    => '',
					'type'    => 'textarea',
					'default' => "سلام\nموجودی محصول {product_title} کم می باشد . لطفا در صورت تمایل به خرید سریعتر اقدام نمایید ."
				),
				array(
					'name'  => 'header_5',
					'label' => 'تذکر',
					'desc'  => 'توجه داشته باشید که عملکرد گزینه های مربوط به "موجودی و انبار" وابسته به <a href="' . admin_url( 'admin.php?page=wc-settings&tab=products&section=inventory' ) . '" target="_blank">تنظیمات ووکامرس</a> خواهد بود .',
					'type'  => 'html',
				),
				array(
					'name'  => 'header_6',
					'label' => 'گزینه های اضافی',
					'desc'  => '<hr/>',
					'type'  => 'html',
				),
				array(
					'name'    => 'notif_options',
					'label'   => 'گزینه های دلخواه',
					'desc'    => 'شما میتوانید گزینه های دلخواه خود را برای نمایش در صفحه محصولات ایجاد نمایید و به صورت دستی به خریدارانی که در گزینه های بالا عضو شده اند پیامک ارسال کنید .<br/>
						برای اضافه کردن گزینه ها ، همانند نمونه بالا ابتدا یک کد عددی دلخواه تعریف کنید سپس بعد از قرار دادن عبارت ":" متن مورد نظر را بنویسید .<br/>
						دقت کنید که کد عددی هر گزینه بسیار مهم بوده و از تغییر کد مربوط به هر گزینه بعد از ذخیره تنظیمات خود داری نمایید .',
					'type'    => 'textarea',
					'default' => "1:زمانیکه محصول توقف فروش شد\n2:زمانیکه نسخه جدید محصول منتشر شد\n"
				),
				array(
					'name'  => 'header_7',
					'label' => 'تذکر',
					'desc'  => 'متن پیامک مربوط به گزینه های اضافی را در میتوانید در صفحه هر محصول در باکس سمت چپ آن نوشته و پیامک را ارسال نمایید .',
					'type'  => 'html',
				),
			) ),
		);

		return apply_filters( 'persianwoosms_settings_section_content', $settings_fields );
	}


	function buyerSettings( $settings ) {

		$statuses = PWooSMS()->GetAllStatuses();

		foreach ( ( array ) $statuses as $status_val => $status_name ) {

			$text = array(
				array(
					'name'    => 'sms_body_' . $status_val,
					'label'   => 'وضعیت ' . $status_name,
					'desc'    => "همچنین میتوانید از شورت کد های معرفی شده در انتهای این بخش استفاده نمایید .<hr/>",
					'type'    => 'textarea',
					'default' => "سلام {b_first_name} {b_last_name}\nسفارش {order_id} دریافت شد و هم اکنون در وضعیت " . $status_name . " می باشد\nآیتم های سفارش : {all_items}\nمبلغ سفارش : {price}\nشماره تراکنش : {transaction_id}"
				),
			);

			$settings = array_merge( $settings, $text );
		}

		$text     = array(
			array(
				'name'  => 'sms_body_shortcodes',
				'label' => 'شورت کد های پیام',
				'type'  => 'html',
				'desc'  => PWooSMS()->ShortCodes(),
			),
		);
		$settings = array_merge( $settings, $text );

		return $settings;
	}


	function superAdminSettings( $settings ) {


		$statuses = PWooSMS()->GetAllStatuses();

		foreach ( ( array ) $statuses as $status_val => $status_name ) {

			$text = array(
				array(
					'name'    => 'super_admin_sms_body_' . $status_val,
					'label'   => 'وضعیت ' . $status_name,
					'desc'    => "همچنین میتوانید از شورت کد های معرفی شده در انتهای این بخش استفاده نمایید .<hr/>",
					'type'    => 'textarea',
					'default' => "سلام مدیر\nسفارش {order_id} ثبت شده است و هم اکنون در وضعیت " . $status_name . " می باشد\nآیتم های سفارش : {all_items}\nمبلغ سفارش : {price}"
				)
			);

			$settings = array_merge( $settings, $text );
		}

		$text     = array(
			array(
				'name'  => 'sms_body_shortcodes_super_admin',
				'label' => 'شورت کد های پیام',
				'type'  => 'html',
				'desc'  => PWooSMS()->ShortCodes(),
			),
			array(
				'name'  => 'header_3',
				'label' => 'متن پیامک های موجودی انبار',
				'desc'  => '<hr/>',
				'type'  => 'html',
			),
			array(
				'name'  => 'header_5',
				'label' => 'تذکر',
				'desc'  => 'توجه داشته باشید که عملکرد گزینه های مربوط به "موجودی و انبار" برای "مدیران محصول" نیز اعمال خواهد شد و وابسته به <a href="' . admin_url( 'admin.php?page=wc-settings&tab=products&section=inventory' ) . '" target="_blank">تنظیمات ووکامرس</a> خواهد بود .',
				'type'  => 'html',
			),
			array(
				'name'    => 'admin_out_stock',
				'label'   => 'اتمام موجودی انبار',
				'desc'    => "متن پیامک زمانیکه موجودی انبار تمام شد",
				'type'    => 'textarea',
				'default' => "سلام\nموجودی انبار محصول {product_title} به اتمام رسیده است ."
			),
			array(
				'name'    => 'admin_low_stock',
				'label'   => 'کاهش موجودی انبار',
				'desc'    => "متن پیامک زمانیکه موجودی انبار کم است",
				'type'    => 'textarea',
				'default' => "سلام\nموجودی انبار محصول {product_title} رو به اتمام است ."
			),
			array(
				'name'  => 'header_4',
				'label' => 'شورت کد های قابل استفاده',
				'desc'  => "شورت کد های قابل استفاده در متن پیامک های مرتبط با موجوی انبار :<br/><br/><code>{product_id}</code> : آیدی محصول ، <code>{sku}</code> : شناسه محصول ، <code>{product_title}</code> : عنوان محصول ، <code>{stock}</code> : موجودی انبار",
				'type'  => 'html',
			),
		);
		$settings = array_merge( $settings, $text );

		return $settings;
	}


	function productAdminSettings( $settings ) {

		$statuses = PWooSMS()->GetAllStatuses();

		foreach ( ( array ) $statuses as $status_val => $status_name ) {

			$text = array(
				array(
					'name'    => 'product_admin_sms_body_' . $status_val,
					'label'   => 'وضعیت ' . $status_name,
					'desc'    => "همچنین علاوه بر شورت کد های معرفی شده در انتهای این بخش می توانید از کد میانبر زیر نیز استفاده نمایید :<br/><code>{vendor_items}</code> : آیتم های سفارش اختصاص یافته به هر شماره <hr/>",
					'type'    => 'textarea',
					'default' => "سلام\nسفارش {order_id} ثبت شده است و هم اکنون در وضعیت " . $status_name . " می باشد\nآیتم های سفارش اختصاص یافته به شماره شما : {vendor_items}",
				),
			);

			$settings = array_merge( $settings, $text );
		}

		$text = array(
			array(
				'name'  => 'sms_body_shortcodes_product_admin',
				'label' => 'شورت کد های پیام',
				'type'  => 'html',
				'desc'  => PWooSMS()->ShortCodes(),
			)
		);

		$settings = array_merge( $settings, $text );

		return $settings;
	}

	function submitButton() {
		submit_button();
	}

	function footerNote( $text ) {
		if ( isset( $_GET['page'] ) && $_GET['page'] == 'persian-woocommerce-sms-pro' ) {
			return ' این افزونه به صورت رایگان از سوی <a href="http://woocommerce.ir/" target="_blank">ووکامرس فارسی</a> ارائه شده است . هر گونه کپی برداری و کسب درآمد از آن توسط سایرین غیر مجاز می باشد .';
		}

		return $text;
	}

	function footerVersion( $text ) {
		if ( isset( $_GET['page'] ) && $_GET['page'] == 'persian-woocommerce-sms-pro' ) {
			$text = 'پیامک نگارش ' . PS_WOO_SMS_VERSION;
		}

		return $text;
	}
}