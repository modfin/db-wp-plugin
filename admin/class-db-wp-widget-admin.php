<?php
/*
    Title: MF Datablocks Widget Admin
    Description: Admin panel for the Modular Finance Datablocks&trade; widget.
    Author: Alexander Forrest // Modular Finance
*/

class DB_WP_Widget_Admin {

	private $loaderVersion = '';
	private $datablocksURL = '';

	public function __construct() {

        $loaderVersionOption = isset(get_option('mfdb_widget_options')['loader_version_option']) && !empty(get_option('mfdb_widget_options')['loader_version_option']) ? get_option('mfdb_widget_options')['loader_version_option'] : DB_DEFAULT_LOADER_VERSION;
        $datablocksURLOption = isset(get_option('mfdb_widget_options')['datablocks_url_option']) && !empty(get_option('mfdb_widget_options')['datablocks_url_option']) ? get_option('mfdb_widget_options')['datablocks_url_option'] : DB_DEFAULT_URL;

        $this->loaderVersion = $loaderVersionOption;
        $this->datablocksURL = $datablocksURLOption;

		$this->pageTitle = 'MF Datablocks WP Widget';
		$this->optionsGroup = 'mf_widget_options';
		$this->pageDesc = 'On this page it is possible to update the URL settings for the ' . $this->pageTitle . '.';

		$this->datablocks_url_label = 'Datablocks URL';
		$this->loader_version_label = 'JS Loader Version';

		add_action( 'admin_menu', array( $this, 'register_mfdb_widget_options_menu' ) );
		add_action( 'admin_init',  array( $this, 'init_mfdb_widget_options' ) );
	}

	public function register_mfdb_widget_options_menu() {

		add_options_page(
			$this->pageTitle,
			$this->pageTitle,
			'manage_options',
			$this->optionsGroup,
			array( $this, 'mfdb_widget_options_page' )
		);

	}

	public function init_mfdb_widget_options() {

		register_setting(
			$this->optionsGroup,
			'mfdb_widget_options',
			array( $this, 'sanitize_input' )
		);

	}

	// Info section
	public function mfdb_widget_setting_section_info() {
		echo '
			<p>
				' . $this->pageDesc . '
			</p>
			<table class="form-table" role="presentation">
				<tbody>
					<tr>
						<th scope="row">Default URL settings</th>
						<td>
							<p>
								' . $this->datablocks_url_label . '
								<code>
									' . $this->datablocksURL . '
								</code>
							</p>
							<p>
								JS Loader URL
								<code>
									' . $this->datablocksURL . '/assets/js/loader-' . $this->loaderVersion . '.js
								</code>
							</p>
						</td>
					</tr>
				</tbody>
			</table>
		';
	}

	// Sanitize input form values
	public function sanitize_input($input) {

		$sanitized = array();

		foreach($input as $key => $value) {
			if ( isset( $value )) {
				$sanitized[$key] = sanitize_text_field( $input[$key] );
			}
		}

		return $sanitized;
	}

	// Add page sections
	public function add_page_sections() {

		add_settings_section(
			'mfdb_widget_setting_section',
			'Settings',
			array( $this, 'mfdb_widget_setting_section_info' ),
			'mfdb_widget_settings_group'
		);

		add_settings_field(
			'datablocks_url_option',
			$this->datablocks_url_label,
			array( $this, 'datablocks_url_cb'),
			'mfdb_widget_settings_group',
			'mfdb_widget_setting_section'
		);

		add_settings_field(
			'loader_version_option',
			$this->loader_version_label,
			array( $this, 'loader_version_cb'),
			'mfdb_widget_settings_group',
			'mfdb_widget_setting_section'
		);

	}

	// Datablocks URL setting callback
	public function datablocks_url_cb() {
		echo '
			<input type="text" class="regular-text ltr" id="datablocks_url_option" placeholder="'. $this->datablocksURL . '" name="mfdb_widget_options[datablocks_url_option]" value="' . $this->datablocksURL . '" />
			<p class="description" id="loader-url-description">
				Here you can update the ' . $this->datablocks_url_label . '.
			</p>
		';
	}

	// Loader URL setting callback
	public function loader_version_cb() {
		echo '
			<input type="text" class="regular-text ltr" id="loader_version_option" placeholder="'. $this->loaderVersion . '" name="mfdb_widget_options[loader_version_option]" value="' . $this->loaderVersion . '" />
			<p class="description" id="loader-version-description">
				Here you can update the ' . $this->loader_version_label . '.
			</p>
		';
	}

	// Frontend
	public function mfdb_widget_options_page() {

		// Function to add sections to frontend
		$this->add_page_sections();
	?>
		<div class="wrap">
			<h2>
				<?php echo $this->pageTitle; ?>
			</h2>

			<form method="post" action="options.php">
				<?php
					settings_fields( $this->optionsGroup );
					do_settings_sections( 'mfdb_widget_settings_group' );
					submit_button();
				?>
			</form>
		</div>
	<?php }

}
