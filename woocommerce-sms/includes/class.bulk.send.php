<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WoocommerceIR_Bulk_SMS {

	public static function bulkForm() {

		self::bulkNotice(); ?>

        <div class="persianwoosms_send_sms_any" id="persianwoosms_send_sms_any">

            <div class="inside">
                <form class="initial-form" id="persianwoosms-send-sms-any-form" method="post" name="post"
                      action="<?php echo admin_url( 'admin.php?page=persian-woocommerce-sms-pro&send=true' ) ?>">

                    <p>
                        <label for="persianwoosms_receiver_number">شماره دریافت کننده</label><br>
                        <input type="text" name="persianwoosms_receiver_number" id="persianwoosms_receiver_number"
                               value="<?php echo isset( $_POST['ps_sms_numbers'] ) ? sanitize_text_field( $_POST['ps_sms_numbers'] ) : '' ?>"
                               style="direction:ltr; text-align:left; width:700px; max-width:100% !important"/><br/>
                        <span>شماره موبایل دریافت کننده پیامک را وارد کنید . شماره ها را با کاما (,) جدا نمایید .</span>
                    </p>

                    <p>
                        <label for="persianwoosms_buyer_sms_body">متن پیامک</label><br>
                        <textarea name="persianwoosms_buyer_sms_body" id="persianwoosms_buyer_sms_body" rows="6"
                                  style="width:700px; max-width:100% !important"></textarea><br/>
                        <span>متن دلخواهی که میخواهید به دریافت کننده ارسال کنید را وارد کنید</span>
                    </p>

                    <p>
						<?php if ( PWooSMS()->Options( 'enable_sms', 'sms_main_settings', 'no' ) == 'on' ): ?>
                            <input type="checkbox" name="persianwoosms_pm_type_sms" id="persianwoosms_pm_type_sms"
                                   checked="checked"/>
                            <label for="persianwoosms_pm_type_sms">اس ام اس</label><br>
						<?php endif;

						if ( PWooSMS()->Options( 'enable_tg', 'sms_main_settings', 'no' ) == 'on' ): ?>
                            <input type="checkbox" name="persianwoosms_pm_type_tg" id="persianwoosms_pm_type_tg"
                                   checked="checked"/>
                            <label for="persianwoosms_pm_type_tg">تلگرام</label><br>
						<?php endif; ?>
                    </p>

                    <p>
                        <input type="submit" class="button button-primary" name="persianwoosms_send_sms"
                               value="ارسال پیامک">
                    </p>
                </form>
            </div>
        </div>
		<?php
	}

	public static function bulkScript() {

		if ( PWooSMS()->Options( 'enable_plugins', 'sms_main_settings', 'off' ) == 'off' ) {
			return;
		}

		global $post_type;
		if ( 'shop_order' == $post_type ) : ?>
            <script type="text/javascript">
                jQuery(function () {
                    jQuery('<option>').val('send_sms').text('<?php _e( 'ارسال پیامک دسته جمعی', 'woocommerce' )?>').appendTo("select[name='action']");
                    jQuery('<option>').val('send_sms').text('<?php _e( 'ارسال پیامک دسته جمعی', 'woocommerce' )?>').appendTo("select[name='action2']");
                });
            </script>
			<?php
		endif;
	}

	public static function bulkAction() {

		if ( PWooSMS()->Options( 'enable_plugins', 'sms_main_settings', 'off' ) == 'off' ) {
			return;
		}

		$wp_list_table = _get_list_table( 'WP_Posts_List_Table' );
		$action        = $wp_list_table->current_action();
		if ( $action != 'send_sms' ) {
			return;
		}

		$post_ids = array_map( 'absint', (array) $_REQUEST['post'] );
		$numbers  = array();
		foreach ( $post_ids as $post_id ) {
			$numbers[] = get_post_meta( $post_id, '_billing_phone', true );
		}

		$numbers = implode( ',', array_unique( $numbers ) );
		echo '<form method="POST" name="ps_sms_post_form" action="' . admin_url( 'admin.php?page=persian-woocommerce-sms-pro&send=true' ) . '">
		<input type="hidden" value="' . $numbers . '" name="ps_sms_numbers" />
		</form><script language="javascript">document.ps_sms_post_form.submit(); </script>';
		exit();
	}

	public static function bulkSend() {

		if ( isset( $_POST['persianwoosms_send_sms'] ) ) {

			if ( empty( $_POST['persianwoosms_receiver_number'] ) ) {
				wp_redirect( add_query_arg( array(
					'page'    => 'persian-woocommerce-sms-pro',
					'send'    => 'true',
					'message' => 'error'
				), admin_url( 'admin.php' ) ) );
				exit;

			} else {

				$receiver_sms_data['number']   = isset( $_POST['persianwoosms_receiver_number'] ) ? explode( ',', sanitize_text_field( $_POST['persianwoosms_receiver_number'] ) ) : '';
				$receiver_sms_data['number']   = PWooSMS()->EnglishNumberMobile( $receiver_sms_data['number'] );
				$receiver_sms_data['sms_body'] = esc_textarea( $_POST['persianwoosms_buyer_sms_body'] );

				if ( isset( $_POST['persianwoosms_pm_type_tg'] ) && isset( $_POST['persianwoosms_pm_type_sms'] ) ) {

					$active_tg_gateway    = PWooSMS()->Options( 'tg_gateway', 'sms_main_settings', '' );
					$receiver_response_tg = ( empty( $active_tg_gateway ) || $active_tg_gateway == 'none' ) ? false : WoocommerceIR_Gateways_SMS::init()->$active_tg_gateway( $receiver_sms_data );

					$active_sms_gateway    = PWooSMS()->Options( 'sms_gateway', 'sms_main_settings', '' );
					$receiver_response_sms = ( empty( $active_sms_gateway ) || $active_sms_gateway == 'none' ) ? false : WoocommerceIR_Gateways_SMS::init()->$active_sms_gateway( $receiver_sms_data );

					if ( $receiver_response_tg && $receiver_response_sms ) {

						wp_redirect( add_query_arg( array(
							'page'    => 'persian-woocommerce-sms-pro',
							'send'    => 'true',
							'message' => 'success'
						), admin_url( 'admin.php' ) ) );
						exit;

					} else if ( $receiver_response_tg && ! $receiver_response_sms ) {

						wp_redirect( add_query_arg( array(
							'page'    => 'persian-woocommerce-sms-pro',
							'send'    => 'true',
							'message' => 'success_tg'
						), admin_url( 'admin.php' ) ) );
						exit;

					} else if ( ! $receiver_response_tg && $receiver_response_sms ) {

						wp_redirect( add_query_arg( array(
							'page'    => 'persian-woocommerce-sms-pro',
							'send'    => 'true',
							'message' => 'success_sms'
						), admin_url( 'admin.php' ) ) );
						exit;

					} else {

						wp_redirect( add_query_arg( array(
							'page'    => 'persian-woocommerce-sms-pro',
							'send'    => 'true',
							'message' => 'sending_failed'
						), admin_url( 'admin.php' ) ) );
						exit;

					}
				} else if ( isset( $_POST['persianwoosms_pm_type_sms'] ) ) {

					$active_sms_gateway = PWooSMS()->Options( 'sms_gateway', 'sms_main_settings', '' );
					if ( empty( $active_sms_gateway ) || $active_sms_gateway == 'none' ) {
						wp_redirect( add_query_arg( array(
							'page'    => 'persian-woocommerce-sms-pro',
							'send'    => 'true',
							'message' => 'gateway_problem'
						), admin_url( 'admin.php' ) ) );
						exit;
					}

					$receiver_response_sms = WoocommerceIR_Gateways_SMS::init()->$active_sms_gateway( $receiver_sms_data );

					if ( $receiver_response_sms ) {
						wp_redirect( add_query_arg( array(
							'page'    => 'persian-woocommerce-sms-pro',
							'send'    => 'true',
							'message' => 'success'
						), admin_url( 'admin.php' ) ) );
						exit;

					} else {

						wp_redirect( add_query_arg( array(
							'page'    => 'persian-woocommerce-sms-pro',
							'send'    => 'true',
							'message' => 'sending_failed'
						), admin_url( 'admin.php' ) ) );
						exit;

					}
				} else if ( isset( $_POST['persianwoosms_pm_type_tg'] ) ) {

					$active_tg_gateway = PWooSMS()->Options( 'tg_gateway', 'sms_main_settings', '' );
					if ( empty( $active_tg_gateway ) || $active_tg_gateway == 'none' ) {
						wp_redirect( add_query_arg( array(
							'page'    => 'persian-woocommerce-sms-pro',
							'send'    => 'true',
							'message' => 'gateway_problem'
						), admin_url( 'admin.php' ) ) );
						exit;
					}

					$receiver_response_tg = WoocommerceIR_Gateways_SMS::init()->$active_tg_gateway( $receiver_sms_data );

					if ( $receiver_response_tg ) {
						wp_redirect( add_query_arg( array(
							'page'    => 'persian-woocommerce-sms-pro',
							'send'    => 'true',
							'message' => 'success'
						), admin_url( 'admin.php' ) ) );
						exit;

					} else {
						wp_redirect( add_query_arg( array(
							'page'    => 'persian-woocommerce-sms-pro',
							'send'    => 'true',
							'message' => 'sending_failed'
						), admin_url( 'admin.php' ) ) );
						exit;

					}
				} else {

					wp_redirect( add_query_arg( array(
						'page'    => 'persian-woocommerce-sms-pro',
						'send'    => 'true',
						'message' => 'select_type'
					), admin_url( 'admin.php' ) ) );
					exit;

				}
			}
		}
	}

	private static function bulkNotice() {

		if ( isset( $_POST['ps_sms_numbers'] ) ) : ?>
            <div class="updated">
                <p><strong>تعداد مخاطبین با حذف شماره های تکراری </strong>
                    => <?php echo count( explode( ',', sanitize_text_field( $_POST['ps_sms_numbers'] ) ) ) . ' شماره ' ?>
                </p>
            </div>
		<?php endif;

		if ( ! empty( $_GET['message'] ) ) {

			switch ( $_GET['message'] ) {

				case 'gateway_problem' :
					$error = 'تنظیمات درگاه پیامک انجام نشده است !';
					break;
				case 'error' :
					$error = 'وارد کردن شماره دریافت کننده الزامی است !';
					break;
				case 'select_type' :
					$error = 'انتخاب یکی از گزینه های "اس ام اس" یا "تلگرام" اجباری است !';
					break;
				case 'sending_failed' :
					$error = 'ارسال پیام با مشکل مواجه گردید. لطفا شماره دریافت کننده یا تنظیمات سیستم پیام را بررسی کنید !';
					break;
				case 'success' :
					$updated = 'پیام ها با موفقیت به دریافت کننده ارسال گردیدند !';
					break;
				case 'success_sms' :
					$updated = 'پیامک با موفقیت به دریافت کننده ارسال گردید ولی تلگرام با خطا مواجه شد !';
					break;
				case 'success_tg' :
					$updated = 'تلگرام با موفقیت به دریافت کننده ارسال گردید ولی پیامک با خطا مواجه شد !';
					break;
			}

			if ( ! empty( $updated ) ) : ?>
                <div class="updated">
                    <p><?php echo $updated; ?></p>
                </div>
			<?php endif;

			if ( ! empty( $error ) ) : ?>
                <div class="error">
                    <p><strong>خطا:</strong> <?php echo $error; ?></p>
                </div>
			<?php endif;
		}
	}
}