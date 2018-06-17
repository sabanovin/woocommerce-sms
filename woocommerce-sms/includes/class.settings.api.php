<?php

class WoocommerceIR_Settings_Api {

	private $settings_sections = array();
	private $settings_fields = array();

	//private static $_instance;

	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
	}

	function admin_enqueue_scripts() {
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_media();
		wp_enqueue_script( 'wp-color-picker' );
		wp_enqueue_script( 'jquery' );
	}

	function set_sections( $sections ) {
		$this->settings_sections = $sections;

		return $this;
	}

	function add_section( $section ) {
		$this->settings_sections[] = $section;

		return $this;
	}

	function set_fields( $fields ) {
		$this->settings_fields = $fields;

		return $this;
	}

	function add_field( $section, $field ) {
		$defaults                            = array(
			'name'  => '',
			'label' => '',
			'desc'  => '',
			'type'  => 'text'
		);
		$arg                                 = wp_parse_args( $field, $defaults );
		$this->settings_fields[ $section ][] = $arg;

		return $this;
	}

	function admin_init() {
		foreach ( $this->settings_sections as $section ) {
			if ( false == get_option( $section['id'] ) ) {
				add_option( $section['id'] );
			}
			if ( isset( $section['desc'] ) && ! empty( $section['desc'] ) ) {
				$section['desc'] = '<div class="inside">' . $section['desc'] . '</div>';
				$callback        = create_function( '', 'echo "' . str_replace( '"', '\"', $section['desc'] ) . '";' );
			} else {
				$callback = '__return_false';
			}
			add_settings_section( $section['id'], $section['title'], $callback, $section['id'] );
		}

		foreach ( $this->settings_fields as $section => $field ) {
			foreach ( $field as $option ) {
				$type = isset( $option['type'] ) ? $option['type'] : 'text';
				$args = array(
					'id'                => $option['name'],
					'desc'              => isset( $option['desc'] ) ? $option['desc'] : '',
					'name'              => $option['label'],
					'section'           => $section,
					'size'              => isset( $option['size'] ) ? $option['size'] : null,
					'options'           => isset( $option['options'] ) ? $option['options'] : '',
					'std'               => isset( $option['default'] ) ? $option['default'] : '',
					'sanitize_callback' => isset( $option['sanitize_callback'] ) ? $option['sanitize_callback'] : '',
				);
				add_settings_field( $section . '[' . $option['name'] . ']', $option['label'], array(
					$this,
					'callback_' . $type
				), $section, $section, $args );
			}
		}

		foreach ( $this->settings_sections as $section ) {
			register_setting( $section['id'], $section['id'], array( $this, 'sanitize_options' ) );
		}
	}

	function callback_text( $args ) {
		$value = esc_attr( $this->get_option( $args['id'], $args['section'], $args['std'] ) );
		$size  = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';
		$html  = sprintf( '<input type="text" class="%1$s-text" id="%2$s[%3$s]" name="%2$s[%3$s]" value="%4$s"/>', $size, $args['section'], $args['id'], $value );
		$html  .= sprintf( '<span class="description"> %s</span>', $args['desc'] );
		echo $html;
	}

	function callback_checkbox( $args ) {
		$value = esc_attr( $this->get_option( $args['id'], $args['section'], $args['std'] ) );
		$html  = sprintf( '<input type="hidden" name="%1$s[%2$s]" value="off" />', $args['section'], $args['id'] );
		$html  .= sprintf( '<input type="checkbox" class="checkbox" id="wpuf-%1$s[%2$s]" name="%1$s[%2$s]" value="on"%4$s />', $args['section'], $args['id'], $value, checked( $value, 'on', false ) );
		$html  .= sprintf( '<label for="wpuf-%1$s[%2$s]"> %3$s</label>', $args['section'], $args['id'], $args['desc'] );
		echo $html;
	}

	function callback_multicheck( $args ) {
		$value = $this->get_option( $args['id'], $args['section'], $args['std'] );
		$html  = '';
		foreach ( $args['options'] as $key => $label ) {
			$checked = isset( $value[ $key ] ) ? $value[ $key ] : '0';
			$html    .= sprintf( '<input type="checkbox" class="checkbox" id="wpuf-%1$s[%2$s][%3$s]" name="%1$s[%2$s][%3$s]" value="%3$s"%4$s />', $args['section'], $args['id'], $key, checked( $checked, $key, false ) );
			$html    .= sprintf( '<label for="wpuf-%1$s[%2$s][%4$s]"> %3$s</label><br>', $args['section'], $args['id'], $label, $key );
		}
		$html .= sprintf( '<span class="description"> %s</label>', $args['desc'] );
		echo $html;
	}

	function callback_radio( $args ) {
		$value = $this->get_option( $args['id'], $args['section'], $args['std'] );
		$html  = '';
		foreach ( $args['options'] as $key => $label ) {
			$html .= sprintf( '<input type="radio" class="radio" id="wpuf-%1$s[%2$s][%3$s]" name="%1$s[%2$s]" value="%3$s"%4$s />', $args['section'], $args['id'], $key, checked( $value, $key, false ) );
			$html .= sprintf( '<label for="wpuf-%1$s[%2$s][%4$s]"> %3$s</label><br>', $args['section'], $args['id'], $label, $key );
		}
		$html .= sprintf( '<span class="description"> %s</label>', $args['desc'] );
		echo $html;
	}

	function callback_select( $args ) {
		$value = esc_attr( $this->get_option( $args['id'], $args['section'], $args['std'] ) );
		$size  = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'wc-enhanced-select regular';
		$html  = sprintf( '<div style="max-width:350px"><select class="%1$s" name="%2$s[%3$s]" id="%2$s[%3$s]">', $size, $args['section'], $args['id'] );
		foreach ( $args['options'] as $key => $label ) {
			$html .= sprintf( '<option value="%s"%s>%s</option>', $key, selected( $value, $key, false ), $label );
		}
		$html .= sprintf( '</select></div>' );
		$html .= sprintf( '<span class="description"> %s</span>', $args['desc'] );
		echo $html;
	}

	function callback_textarea( $args ) {
		$value = esc_textarea( $this->get_option( $args['id'], $args['section'], $args['std'] ) );
		$size  = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';
		$html  = sprintf( '<textarea rows="5" cols="55" class="%1$s-text" id="%2$s[%3$s]" name="%2$s[%3$s]">%4$s</textarea>', $size, $args['section'], $args['id'], $value );
		$html  .= sprintf( '<br><span class="description"> %s</span>', $args['desc'] );
		echo $html;
	}

	function callback_html( $args ) {
		echo sprintf( '<span class="description"> %s</span>', $args['desc'] );
	}

	function callback_wysiwyg( $args ) {
		$value = $this->get_option( $args['id'], $args['section'], $args['std'] );
		$size  = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : '500px';
		echo '<div style="width: ' . $size . ';">';
		wp_editor( $value, $args['section'] . '-' . $args['id'] . '', array(
			'teeny'         => true,
			'textarea_name' => $args['section'] . '[' . $args['id'] . ']',
			'textarea_rows' => 10
		) );
		echo '</div>';
		echo sprintf( '<br><span class="description"> %s</span>', $args['desc'] );
	}

	function callback_file( $args ) {
		$value = esc_attr( $this->get_option( $args['id'], $args['section'], $args['std'] ) );
		$size  = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';
		$id    = $args['section'] . '[' . $args['id'] . ']';
		$html  = sprintf( '<input type="text" class="%1$s-text ps-sms-url" id="%2$s[%3$s]" name="%2$s[%3$s]" value="%4$s"/>', $size, $args['section'], $args['id'], $value );
		$html  .= '<input type="button" class="button ps-sms-browse" value="' . __( 'Browse' ) . '" />';
		$html  .= sprintf( '<span class="description"> %s</span>', $args['desc'] );
		echo $html;
	}

	function callback_password( $args ) {
		$value = esc_attr( $this->get_option( $args['id'], $args['section'], $args['std'] ) );
		$size  = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';
		$html  = sprintf( '<input type="password" class="%1$s-text" id="%2$s[%3$s]" name="%2$s[%3$s]" value="%4$s"/>', $size, $args['section'], $args['id'], $value );
		$html  .= sprintf( '<span class="description"> %s</span>', $args['desc'] );
		echo $html;
	}

	function callback_color( $args ) {
		$value = esc_attr( $this->get_option( $args['id'], $args['section'], $args['std'] ) );
		$size  = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';
		$html  = sprintf( '<input type="text" class="%1$s-text wp-color-picker-field" id="%2$s[%3$s]" name="%2$s[%3$s]" value="%4$s" data-default-color="%5$s" />', $size, $args['section'], $args['id'], $value, $args['std'] );
		$html  .= sprintf( '<span class="description" style="display:block;"> %s</span>', $args['desc'] );
		echo $html;
	}

	function sanitize_options( $options ) {
		foreach ( $options as $option_slug => $option_value ) {
			$sanitize_callback = $this->get_sanitize_callback( $option_slug );
			if ( $sanitize_callback ) {
				$options[ $option_slug ] = call_user_func( $sanitize_callback, $option_value );
				continue;
			}
		}

		return $options;
	}

	function get_sanitize_callback( $slug = '' ) {
		if ( empty( $slug ) ) {
			return false;
		}
		foreach ( $this->settings_fields as $section => $options ) {
			foreach ( $options as $option ) {
				if ( $option['name'] != $slug ) {
					continue;
				}

				return isset( $option['sanitize_callback'] ) && is_callable( $option['sanitize_callback'] ) ? $option['sanitize_callback'] : false;
			}
		}

		return false;
	}

	function get_option( $option, $section, $default = '' ) {
		$options = get_option( $section );
		if ( isset( $options[ $option ] ) ) {
			return $options[ $option ];
		}

		return $default;
	}

	function show_navigation() {
		$html = '<h2 class="nav-tab-wrapper">';
		foreach ( $this->settings_sections as $tab ) {
			$html .= sprintf( '<a href="#%1$s" class="nav-tab" id="%1$s-tab">%2$s</a>', $tab['id'], $tab['title'] );
		}
		$html .= '</h2>';
		echo $html;
	}

	function show_forms() {
		if ( defined( 'WC_VERSION' ) ) {
			wp_enqueue_style( 'woocommerce_admin_styles', WC()->plugin_url() . '/assets/css/admin.css', array(), WC_VERSION );
			wp_enqueue_script( 'wc-enhanced-select' );
		}
		if ( isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] == 'true' ) {
			?>
            <div class="updated">
                <p>تنظمیات ذخیره شدند.</p>
            </div>
			<?php
		}
		?>
        <style>
            table.form-table th {
                padding-left: 24px !important;
                position: relative;
            }
        </style>
        <div class="metabox-holder">
			<?php foreach ( $this->settings_sections as $form ) { ?>
                <div id="<?php echo $form['id']; ?>" class="group">
                    <form method="post" action="options.php">
						<?php do_action( 'ps_woo_sms_form_top_' . $form['id'], $form ); ?>
						<?php settings_fields( $form['id'] ); ?>
						<?php do_settings_sections( $form['id'] ); ?>
						<?php do_action( 'ps_woo_sms_form_bottom_' . $form['id'], $form ); ?>
                        <div style="padding-right: 10px">
							<?php do_action( 'ps_woo_sms_form_submit_' . $form['id'], $form ); ?>

                        </div>
                    </form>
                </div>
			<?php } ?>
        </div>
		<?php
		$this->script();
	}

	function script() {
		?>
        <script type="text/javascript">
            jQuery(document).ready(function ($) {
                $('.wp-color-picker-field').wpColorPicker();
                $('.group').hide();

                var activetab = '';
                if (typeof(localStorage) !== 'undefined') {
                    activetab = localStorage.getItem("activetab");
                }
                if (activetab !== '' && $(activetab).length) {
                    $(activetab).fadeIn();
                } else {
                    $('.group:first').fadeIn();
                    //$('.group:last').fadeIn();
                }
                $('.group .collapsed').each(function () {
                    $(this).find('input:checked').parent().parent().parent().nextAll().each(
                        function () {
                            if ($(this).hasClass('last')) {
                                $(this).removeClass('hidden');
                                return false;
                            }
                            $(this).filter('.hidden').removeClass('hidden');
                        });
                });

                if (activetab !== '' && $(activetab + '-tab').length) {
                    $(activetab + '-tab').addClass('nav-tab-active');
                }
                else {
                    $('.nav-tab-wrapper a:first').addClass('nav-tab-active');
                }
                $('.nav-tab-wrapper a').click(function (evt) {
                    $('.nav-tab-wrapper a').removeClass('nav-tab-active');
                    $(this).addClass('nav-tab-active').blur();
                    var clicked_group = $(this).attr('href');
                    if (typeof(localStorage) !== 'undefined') {
                        localStorage.setItem("activetab", $(this).attr('href'));
                    }

                    $('.group').hide();
                    $(clicked_group).fadeIn();
                    evt.preventDefault();
                });
                var file_frame = null;
                $('.ps-sms-browse').on('click', function (event) {
                    event.preventDefault();
                    var self = $(this);
                    if (file_frame) {
                        file_frame.open();
                        return false;
                    }
                    file_frame = wp.media.frames.file_frame = wp.media({
                        title: self.data('uploader_title'),
                        button: {
                            text: self.data('uploader_button_text')
                        },
                        multiple: false
                    });
                    file_frame.on('select', function () {
                        attachment = file_frame.state().get('selection').first().toJSON();

                        self.prev('.ps-sms-url').val(attachment.url);
                    });
                    file_frame.open();
                });
            });
        </script>
		<?php
	}
}