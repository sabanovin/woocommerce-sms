<?php

class WoocommerceIR_Widget_SMS extends WP_Widget {

	function __construct() {
		parent::__construct(
			'WoocommerceIR_Widget_SMS',
			__( 'اطلاع رسانی پیامکی ووکامرس', 'persianwoosms' ),
			array( 'description' => __( 'این ابزارک را فقط باید در صفحه محصولات استفاده کنید .', 'persianwoosms' ), )
		);
	}

	public function form( $instance ) {

		if ( isset( $instance['title'] ) ) {
			$title = $instance['title'];
		} else {
			$title = __( 'اطلاع رسانی پیامکی', 'persianwoosms' );
		}
		?>

        <p>
            <label for="<?php echo $this->get_field_id( 'title' ); ?>">
                <?php _e( 'Title:' ); ?>
            </label>

            <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>"
                   name="<?php echo $this->get_field_name( 'title' ); ?>" type="text"
                   value="<?php echo esc_attr( $title ); ?>"/>
        </p>
		<?php
	}

	public function widget( $args, $instance ) {

		if ( ! is_product() ) {
			return;
		}

		echo $args['before_widget'];

		$title = apply_filters( 'widget_title', $instance['title'] );
		if ( ! empty( $title ) ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

		echo do_shortcode( '[woo_ps_sms]' );

		echo $args['after_widget'];
	}

	public function update( $new_instance, $old_instance ) {
		$instance          = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		return $instance;
	}
}

function psLoadWidget() {
	register_widget( 'WoocommerceIR_Widget_SMS' );
}