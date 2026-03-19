<?php
/*
 * Plugin Name: WhatsApp Light
 * Description: Lightweight WhatsApp plugin.
 * Author: wpfuse
 * Author URI: https://wpfuse.net
 * Version: 1.0.4
 * Text Domain: wa_light
 * Domain Path: /languages
 */

register_activation_hook( __FILE__, 'WhatsApp_Light_Fields::activate' );
register_uninstall_hook( __FILE__, 'WhatsApp_Light_Fields::uninstall' );

class WhatsApp_Light_Fields {
	
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'create_plugin_settings_page' ), 100 );
		add_action( 'admin_init', array( $this, 'setup_sections' ) );
		add_action( 'admin_init', array( $this, 'setup_fields' ) );
	}
	
	static function activate() {
		$options = get_option('wa_light_options', array());
		if ( empty($options) ) {
			$options = array(
				'number' => '',
				'text' => __('Need help? <b>Chat with us</b>', 'wa_light'),
				'position' => 'right',
				'message' => ''
			);
			update_option( 'wa_light_options', $options, true );
		}
	}
	
	static function uninstall() {
		delete_option( 'wa_light_options' );
	}
	
	public function create_plugin_settings_page() {
		$page_title = 'WhatsApp Light';
		$menu_title = 'WhatsApp';
		$capability = 'manage_options';
		$slug = 'wa_light';
		$callback = array( $this, 'plugin_settings_page_content' );
		
		add_submenu_page( 'options-general.php', $page_title, $menu_title, $capability, $slug, $callback );
	}
	
	public function plugin_settings_page_content() { ?>
		<div class="wrap">
			<h1>WhatsApp</h1>
			<form method="post" action="options.php">
				<?php
					settings_fields( 'wa_light' );
					do_settings_sections( 'wa_light' );
					submit_button();
				?>
			</form>
		</div> <?php
	}
	
	public function setup_sections() {
		add_settings_section( 'wa_light_section', '', array( $this, 'section_callback' ), 'wa_light' );
	}
	
	public function section_callback( $arguments ) {}
	
	public function setup_fields() {
		
		$fields = array(
			array(
				'uid' => 'wa_light_number',
				'label' => __('WhatsApp number', 'wa_light'),
				'section' => 'wa_light_section',
				'type' => 'text',
				'supplemental' => __('Your WhatsApp number with <a href="https://countrycode.org" target="_blank">country code</a> (e.g., 5511999999999)', 'wa_light'),
			),
			array(
				'uid' => 'wa_light_text',
				'label' => __('Text label', 'wa_light'),
				'section' => 'wa_light_section',
				'type' => 'text',
				'supplemental' => __('Label shown next to WhatsApp icon. Leave blank to remove.', 'wa_light'),
			),
			array(
				'uid' => 'wa_light_position',
				'label' => __('Position', 'wa_light'),
				'section' => 'wa_light_section',
				'type' => 'select',
				'options' => array(
					'right' => __('Right', 'wa_light'),
					'left' => __('Left', 'wa_light')
				),
				'supplemental' => __('Choose the position of the WhatsApp button.', 'wa_light'),
			),
			array(
				'uid' => 'wa_light_message',
				'label' => __('Default message', 'wa_light'),
				'section' => 'wa_light_section',
				'type' => 'textarea',
				'supplemental' => __('Pre-filled message text (optional).', 'wa_light'),
			)
		);
		
		foreach( $fields as $field ) {
			add_settings_field( $field['uid'], $field['label'], array( $this, 'field_callback' ), 'wa_light', $field['section'], $field );
		}
		
		register_setting( 'wa_light', 'wa_light_options', array( $this, 'sanitize_options' ) );
	}
	
	public function field_callback( $arguments ) {
		
		$options = get_option( 'wa_light_options', array() );
		$field_key = str_replace( 'wa_light_', '', $arguments['uid'] );
		$value = isset( $options[$field_key] ) ? $options[$field_key] : '';
		
		switch( $arguments['type'] ) {
			case 'text': printf( '<input name="wa_light_options[%1$s]" id="%2$s" type="text" value="%3$s" class="regular-text"/>', $field_key, $arguments['uid'], esc_attr($value) ); break;
			case 'textarea': printf( '<textarea name="wa_light_options[%1$s]" id="%2$s" rows="3" class="regular-text">%3$s</textarea>', $field_key, $arguments['uid'], esc_textarea($value) ); break;
			case 'select': printf( '<select name="wa_light_options[%1$s]" id="%2$s">', $field_key, $arguments['uid'] );
				foreach( $arguments['options'] as $key => $label ) {
					printf( '<option value="%s" %s>%s</option>', $key, selected( $value, $key, false ), $label );
				}
				echo '</select>';
				break;
		}
		
		if ( ! empty( $arguments['supplemental'] ) ) {
			printf( '<p class="description">%s</p>', $arguments['supplemental'] );
		}
	}
	
	public function sanitize_options( $input ) {
		$sanitized = array();
		
		// Clean phone number (digits only)
		if ( isset( $input['number'] ) ) {
			$sanitized['number'] = preg_replace( '/[^0-9]/', '', $input['number'] );
		}
		
		// Sanitize text label (allow <b> only)
		if ( isset( $input['text'] ) ) {
			$sanitized['text'] = wp_kses( $input['text'], array('b' => array()) );
		}
		
		// Validate position (whitelist)
		if ( isset( $input['position'] ) && in_array( $input['position'], array('left', 'right') ) ) {
			$sanitized['position'] = $input['position'];
		} else {
			$sanitized['position'] = 'right';
		}
		
		// Sanitize default message
		if ( isset( $input['message'] ) ) {
			$sanitized['message'] = sanitize_textarea_field( $input['message'] );
		}
		
		return $sanitized;
	}
}

if ( is_admin() ) {
	new WhatsApp_Light_Fields();
}

add_action( 'plugins_loaded', function() {
	load_plugin_textdomain( 'wa_light', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
});

add_action( 'wp_footer', function() { 
	
	$options  = get_option( 'wa_light_options', array() );
	$number   = isset( $options['number'] ) ? $options['number'] : '';
	$text     = isset( $options['text'] ) ? $options['text'] : '';
	$position = isset( $options['position'] ) && $options['position'] === 'left' ? 'left' : 'right';
	$message  = isset( $options['message'] ) ? rawurlencode( $options['message'] ) : '';
	
	if ( empty( $number ) ) return;

	$text_html = $text ? '<div class="wa_light_txt">' . wp_kses( $text, array( 'b' => array() ) ) . '</div>' : '';
	$title     = $text ? strip_tags( $text ) : 'WhatsApp';
	$opp       = $position === 'left' ? 'left' : 'right';
	?>

<style>.wa_light{position:fixed;<?php echo $position; ?>:30px;bottom:30px;font-family:Arial,Helvetica,sans-serif;z-index:9999;cursor:pointer;transition:all .2s ease}.wa_light:hover{transform:var(--buttonTransform,translate3d(0,-3px,0))}.wa_light .wa_light_txt{position:absolute;width:auto;<?php echo $opp; ?>:100%;background-color:#f5f7f9;font-size:12px;color:#43474e;top:15px;padding:7px 12px;margin-<?php echo $opp; ?>:7px;letter-spacing:-.03em;border-radius:4px;display:none;white-space:nowrap;line-height:initial}.wa_light .wa_light_icon{width:56px;height:56px;background:#25D366;border-radius:50%;box-shadow:0 6px 8px 2px rgba(0,0,0,.14)}.wa_light .wa_light_icon:before{content:'';position:absolute;z-index:1;width:100%;height:100%;left:0;top:0;background:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 478.2 478.2' width='512' height='512'%3E%3Cpath d='M478 233c0 129-105 233-235 233a236 236 0 01-113-29L0 478l42-125A230 230 0 019 233C9 104 114 0 243 0c130 0 235 104 235 233zM243 37C135 37 46 125 46 233c0 43 14 83 38 115l-25 72 76-24a197 197 0 00108 33c109 0 198-88 198-196S352 37 243 37zm119 250c-1-3-5-4-11-7l-39-19c-6-1-9-2-13 3l-19 23c-3 3-6 4-12 1s-24-9-46-28c-17-15-29-34-32-40-4-6-1-9 2-11l9-11 6-9c2-4 1-7-1-10l-18-42c-4-12-9-10-12-10h-12c-3 0-10 1-15 7-5 5-20 19-20 47s21 56 23 59c3 4 40 64 99 86 58 23 58 16 69 15 10-1 34-14 39-27 4-14 4-25 3-27z' fill='%23FFF'/%3E%3C/svg%3E") center/30px no-repeat}@media(min-width:690px){.wa_light .wa_light_txt{display:block}}</style>
<a href="https://wa.me/<?php echo esc_attr( $number ); ?>?text=<?php echo esc_attr( $message ); ?>" title="<?php echo esc_attr( $title ); ?>" target="_blank" rel="noopener noreferrer">
<div class="wa_light"><?php echo $text_html; ?><div class="wa_light_icon"></div></div>
</a>
<?php });
