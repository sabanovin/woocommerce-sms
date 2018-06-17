<?php

class WoocommerceIR_Dashboeard {

    private $username = array();

    private $password = array();

    private $from = array();

    public function __construct() {

        add_action( 'wp_ajax_nopriv_pwsms_notice_dismissed', array( $this, 'ajax_callback' ) );
        add_action( 'wp_ajax_pwsms_notice_dismissed', array( $this, 'ajax_callback' ) );

        add_action( 'admin_notices', array( $this, 'display_admin_notice' ) );

    }

    public function ajax_callback() {

        update_option( 'mellisms-notice-dismissed', 1 );

        die();
    }

    public function display_admin_notice() {
        global $pagenow;

        if( $pagenow != "admin.php" ) {
            return false;
        }

        if( ! ( isset( $_GET['page'] ) && in_array( $_GET['page'], array(
                'about-persian-woocommerce-sms-pro',
                'persian-woocommerce-sms-pro'
            ) ) )
        ) {
            return false;
        }

        ?>
        <div class="updated" id="sms-notic-block"><p>
                شما برای شروع کار با پلاگین نیاز به یک پنل اس ام اس دارید. پیشنهاد ما استفاده از <strong>پنل اس ام اس
                    ملی پیامک</strong> است. این سامانه 30% تخفیف با کد <code>wcsms30</code> به مدیران سایت های وردپرس ارائه می‌دهد.
                <br/><br/>
                <a href='http://www.melipayamak.com/' class='button button-primary button-large' target='_blank'>خرید
                    پنل ملی پیامک با 30% تخفیف</a>
                <a href='' onclick='return false;' class='button button-secondary button-large'>من از قبل پنل اس ام
                    اس دارم</a>
            </p></div>
        <script>
			jQuery(document).on('click', '#sms-notic-block .button-secondary', function () {

				jQuery.ajax({
					url: ajaxurl,
					data: {
						action: 'pwsms_notice_dismissed'
					}
				}).done(function () {
					jQuery("#sms-notic-block").slideUp();
				});

			})
        </script>
        <?php
    }

}

if( empty( get_option( 'mellisms-notice-dismissed' ) ) ) {
    new WoocommerceIR_Dashboeard();
}