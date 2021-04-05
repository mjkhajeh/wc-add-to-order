<?php
namespace MJATO;

class Options {
	PRIVATE $prefix = 'mjato_';

	public static function get_instance() {
		static $instance = null;
		if( $instance === null ) {
			$instance = new self;
		}
		return $instance;
	}

	private function __construct() {
		add_action( 'admin_menu', array( $this, 'add_options_page' ) );
		add_action( 'admin_init', array( $this, 'page_init' ) );
	}

	public function add_options_page() {
		add_options_page( 
			__( 'Add to order', 'mjato' ),
			__( 'Add to order', 'mjato' ),
			'manage_options',
			'mjato',
			array( $this, 'options_page' )
		);
	}

	public function options_page() {
		$this->options = get_option( 'mjato' );
		?>
		<div class="wrap">
			<h1><?php _e( 'Add to order', 'mjato' ) ?></h1>
			<form method="post" action="options.php">
				<?php
					settings_fields( $this->prefix );
					do_settings_sections( "{$this->prefix}slug" );
					submit_button();
				?>
			</form>
		</div>
		<?php
	}

	public function page_init() {
		add_settings_section(
			$this->prefix,			// section ID
			'',						// title (if needed)
			'',						// callback function (if needed)
			"{$this->prefix}slug"	// page slug
		);
		register_setting(
			$this->prefix,
			"{$this->prefix}default_status",
			array( $this, 'default_status_sanitize' )
		);

		add_settings_field(
			"{$this->prefix}default_status",
			__( "Default status", 'mjato' ),
			array( $this, 'default_status' ),	// function which prints the field
			"{$this->prefix}slug",				// page slug
			$this->prefix,						// section ID
			array( 
				'label_for'	=> "{$this->prefix}default_status",
				'class'		=> "{$this->prefix}row",	// for <tr> element
			)
		);
	}

	public function default_status() {
		$statuses = wc_get_order_statuses();
		$selected_status = get_option( 'mjato_default_status', 'pending' );
		?>
		<select name="mjato_default_status" id="mjato_default_status" class="regular-text">
			<?php foreach( $statuses as $name => $status ) { ?>
				<option value="<?php echo $name ?>" <?php selected( $name, "wc-{$selected_status}" ) ?>><?php echo $status ?></option>
			<?php } ?>
		</select>
		<?php
	}

	public function default_status_sanitize( $input ) {
		return str_replace( "wc-", "", $input );
	}
}
Options::get_instance();