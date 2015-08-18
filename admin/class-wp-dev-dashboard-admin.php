<?php

/**
 * The dashboard-specific functionality of the plugin.
 *
 * @link       http://wordpress.org/plugins/wp-dev-dashboard
 * @since      1.0.0
 *
 * @package    WP_Dev_Dashboard
 * @subpackage WP_Dev_Dashboard/admin
 */

/**
 * The dashboard-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the dashboard-specific stylesheet and JavaScript.
 *
 * @package    WP_Dev_Dashboard
 * @subpackage WP_Dev_Dashboard/admin
 * @author     Mickey Kay Creative mickey@mickeykaycreative.com
 */
class WP_Dev_Dashboard_Admin {

	/**
	 * The main plugin instance.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      WP_Dev_Dashboard    $plugin    The main plugin instance.
	 */
	private $plugin;

	/**
	 * The slug of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_slug    The slug of this plugin.
	 */
	private $plugin_slug;

	/**
	 * The display name of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The plugin display name.
	 */
	protected $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * The ID of the settings page screen.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $screen_id    The ID of the settings page screen.
	 */
	private $screen_id;

	/**
	 * The instance of this class.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      WP_Dev_Dashboard_Admin    $instance    The instance of this class.
	 */
	private static $instance = null;

	/**
     * Creates or returns an instance of this class.
     *
     * @return    WP_Dev_Dashboard_Admin    A single instance of this class.
     */
    public static function get_instance( $plugin ) {

        if ( null == self::$instance ) {
            self::$instance = new self( $plugin );
        }

        return self::$instance;

    }

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @var      string    $plugin_slug       The name of this plugin.
	 * @var      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin ) {

		$this->plugin = $plugin;
		$this->plugin_slug = $this->plugin->get( 'slug' );
		$this->plugin_name = $this->plugin->get( 'name' );
		$this->version = $this->plugin->get( 'version' );
		$this->options = get_option( $this->plugin_slug );

	}

	/**
	 * Get any plugin property.
	 *
	 * @since     1.0.0
	 * @return    mixed    The plugin property.
	 */
	public function get( $property = '' ) {
		return $this->$property;
	}

	/**
	 * Register the scripts for the admin.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->plugin_slug, plugin_dir_url( __FILE__ ) . 'css/wp-dev-dashboard-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the scripts for the admin.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( $this->plugin_slug, plugin_dir_url( __FILE__ ) . 'js/wp-dev-dashboard-admin.js', array( 'jquery' ), $this->version, true );

		// Enqueue necessary scripts for metaboxes.
		wp_enqueue_script( 'postbox' );

	}

	/**
	 * Add settings page.
	 *
	 * @since 1.0.0
	 */
	function add_settings_page() {

		$this->screen_id = add_menu_page(
			$this->plugin_name, // Page title
			esc_html__( 'Dev Dashboard', 'wp-dev-dashboard' ), // Menu title
			'manage_options', // Capability
			$this->plugin_slug, // Page ID
			array( $this, 'do_settings_page' ), // Callback
			'dashicons-hammer' // Icon
		);

	}

	/**
	 * Output contents of settings page.
	 *
	 * @since 1.0.0
	 */
	function do_settings_page() {

		// Set up tab/settings.
		$tab_base_url = "?page={$this->plugin_slug}";
		$active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'plugins';
		$show_secondary_tabs = ! empty( $this->options['username'] ) || ! empty( $this->options['plugin_slugs'] ) || ! empty( $this->options['theme_slugs'] );
		$is_secondary_tab = ( 'plugins' == $active_tab || 'themes' == $active_tab ) && ( $show_secondary_tabs ) ;

		// Check force refresh param passed via Ajax.
		$force_refresh = isset( $_POST['force_refresh'] ) ? true : false;

		// Set up refresh button atts.
		$refresh_button_atts = array(
			'href'  => '',
			'data-wpdd-refreshing-text' => esc_attr__( 'Fetching data, this can take a bit&hellip;', 'wp-dev-dashboard' ),
		);

		?>
		<?php screen_icon(); ?>
        <div id="<?php echo "{$this->plugin_slug}-settings"; ?>" class="wrap">
	        <h1><?php echo $this->plugin_name; ?></h1><br />
	        <h2 class="nav-tab-wrapper">
	        	<?php if ( $show_secondary_tabs ) : ?>
	        	<a href="<?php echo $tab_base_url; ?>&tab=plugins" class="nav-tab <?php echo $active_tab == 'plugins' ? 'nav-tab-active' : ''; ?>"><span class="dashicons dashicons-admin-plugins"></span> <?php echo __( 'Plugins', 'wp-dev-dashboard '); ?></a>
	        	<a href="<?php echo $tab_base_url; ?>&tab=themes" class="nav-tab <?php echo $active_tab == 'themes' ? 'nav-tab-active' : ''; ?>"><span class="dashicons dashicons-admin-appearance"></span> <?php echo __( 'Themes', 'wp-dev-dashboard '); ?></a>
	        	<?php endif; ?>
	        	<a href="<?php echo $tab_base_url; ?>&tab=settings" class="nav-tab <?php echo $active_tab == 'settings' ? 'nav-tab-active' : ''; ?>"><span class="dashicons dashicons-admin-generic"></span> <?php echo __( 'Settings', 'wp-dev-dashboard '); ?></a>
	        </h2>
			<div id="poststuff" data-wpdd-tab="<?php echo $active_tab; ?>">
				<form action='options.php' method='post'>
					<?php

					// Set up settings fields.
					settings_fields( $this->plugin_slug );

					if ( $is_secondary_tab ) {

						// Output refresh button.
						if ( $is_secondary_tab ) {
							submit_button( esc_attr__( 'Refresh List', 'wp-dev-dashboard' ), 'button button-refresh', '', false, $refresh_button_atts );
							echo '<span class="spinner"></span>';
						}

						$this->do_meta_boxes( $active_tab );
						$this->output_settings_fields( true );
					} else {
						$this->output_settings_fields();
						submit_button( '', 'primary', '', false );
					}

					// Output refresh button.
					if ( $is_secondary_tab ) {
						submit_button( esc_attr__( 'Refresh List', 'wp-dev-dashboard' ), 'button button-refresh', '', false, $refresh_button_atts );
						echo '<span class="spinner"></span>';
					}

					?>
				</form>
			</div><!-- #poststuff -->
		</div><!-- .wrap -->
		<?php
	}

	/**
	 * Add settings fields to the settings page.
	 *
	 * @since 1.0.0
	 */
	function add_settings_fields() {

		register_setting(
			$this->plugin_slug, // Option group
			$this->plugin_slug, // Option name
			null // Sanitization
		);

		add_settings_section(
			'main-settings', // Section ID
			null, // Title
			null, // Callback
			$this->plugin_slug // Page
		);

		add_settings_field(
			'username', // ID
			__( 'WordPress.org username', 'wp-dev-dashboard' ), // Title
			array( $this, 'render_text_input' ), // Callback
			$this->plugin_slug, // Page
			'main-settings', // Section
			array( // Args
				'id' => 'username',
			)
		);

		add_settings_field(
			'plugin_slugs', // ID
			__( 'Additional plugins', 'wp-dev-dashboard' ), // Title
			array( $this, 'render_text_input' ), // Callback
			$this->plugin_slug, // Page
			'main-settings', // Section
			array( // Args
				'id' => 'plugin_slugs',
				'description' => __( 'Comma-separated list of slugs for additional plugins to include.', 'wp-dev-dashboard' ),
			)
		);

		add_settings_field(
			'theme_slugs', // ID
			__( 'Additional plugins', 'wp-dev-dashboard' ), // Title
			array( $this, 'render_text_input' ), // Callback
			$this->plugin_slug, // Page
			'main-settings', // Section
			array( // Args
				'id' => 'theme_slugs',
				'description' => __( 'Comma-separated list of slugs for additional themes to include.', 'wp-dev-dashboard' ),
			)
		);

		add_settings_field(
			'show_all_tickets', // ID
			__( 'Show all tickets', 'wp-dev-dashboard' ), // Title
			array( $this, 'render_checkbox' ), // Callback
			$this->plugin_slug, // Page
			'main-settings', // Section
			array( // Args
				'id' => 'show_all_tickets',
				'description' => sprintf( '<i>%s</i>', __( '(Only unresolved tickets are shown by default.)', 'wp-dev-dashboard' ) ),
			)
		);

	}

	public function output_settings_fields( $hidden = false ) {

		ob_start();
		do_settings_sections( $this->plugin_slug );
		$settings = ob_get_clean();

		if ( $hidden ) {
			$settings = '<div class="hidden">' . $settings .'</div>';
		}

		echo $settings;

	}

	/**
	 * Text input settings field callback.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args Args from add_settings_field().
	 */
	public function render_text_input( $args ) {

		$option_name = $this->plugin_slug . '[' . $args['id'] . ']';
		$option_value = ! empty( $this->options[ $args['id'] ] ) ? $this->options[ $args['id'] ] : '';
		printf(
            '%s<input type="text" value="%s" id="%s" name="%s" class="regular-text %s"/><br /><p class="description" for="%s">%s</p>',
            ! empty( $args['sub_heading'] ) ? '<b>' . $args['sub_heading'] . '</b><br />' : '',
            $option_value,
            $args['id'],
            $option_name,
            ! empty( $args['class'] ) ? $args['class'] : '',
            $option_name,
            ! empty( $args['description'] ) ? $args['description'] : ''
        );

	}

	/**
	 * Checkbox settings field callback.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args Args from add_settings_field().
	 */
	function render_checkbox( $args ) {

		$option_name = $this->plugin_slug . '[' . $args['id'] . ']';
		$option_value = ! empty( $this->options[ $args['id'] ] ) ? $this->options[ $args['id'] ] : '';
		printf(
            '<label for="%s"><input type="checkbox" value="1" id="%s" name="%s" %s/> %s</label>',
            $option_name,
            $option_name,
            $option_name,
            checked( 1, $option_value, false ),
            ! empty( $args['description'] ) ? $args['description'] : ''
        );

	}

	/**
	 * Output metaboxes for tickets.
	 *
	 * @since 1.0.0
	 *
	 * @param string $ticket_type   Type of ticket to output.
	 * @param bool   $force_refresh Whether or not to force an uncached refresh.
	 */
	public function do_meta_boxes( $ticket_type = 'plugins', $force_refresh = false ) {
		?>
		<div class="<?php echo "{$this->plugin_slug}-metaboxes"; ?>">
		<?php
			// Generate nonces for metabox state/order.
			wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );
			wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );

			$this->add_ticket_metaboxes( $ticket_type, $force_refresh );
			?>
			<div id="postbox-container-1" class="postbox-container"><?php do_meta_boxes( $this->plugin_slug, 'normal', null ); ?></div>
		</div>
		<?php
	}

	/**
	 * Helper function to update ticket metaboxes via Ajax.
	 *
	 * @since 1.0.0
	 */
	public function get_ajax_meta_boxes() {
		$ticket_type = isset( $_POST['ticket_type'] ) ? $_POST['ticket_type'] : 'plugins';
		$this->do_meta_boxes( $ticket_type, true );
		wp_die(); // this is required to terminate immediately and return a proper response
	}

	/**
	 * Register ticket metaboxes.
	 *
	 * @since 1.0.0
	 *
	 * @param string $ticket_type   Type of ticket to check for.
	 * @param bool   $force_refresh Whether or not to force an uncached refresh.
	 */
	public function add_ticket_metaboxes( $ticket_type = 'plugins', $force_refresh = false ) {

		// Get username to pull plugin data.
		$username = $this->options['username'];

		// Set transient slug for this specific username and plugin/theme slugs.
		$transient_slug = $ticket_type;

		// Append username to transient.
		if ( $username ) {
			$transient_slug .= "-{$username}";
		}

		// Append plugin slugs to transient.
		if ( 'plugins' == $ticket_type && ! empty( $this->options['plugin_slugs'] ) ) {
			$transient_slug .= '-' . $this->options['plugin_slugs'];
		}

		// Append theme slugs to transient.
		if ( 'themes' == $ticket_type && ! empty( $this->options['theme_slugs'] ) ) {
			$transient_slug .= '-' . $this->options['theme_slugs'];
		}

		$transient_slug = 'wpdd-' . md5( $transient_slug );

		if ( $force_refresh || false === ( $plugins_themes = get_transient( $transient_slug ) ) ) {

			$plugins_themes = $this->get_tickets_data( $username, $ticket_type );

			if ( $plugins_themes ) {

				/**
				 * Filter transient expiration time.
				 *
				 * @since 1.0.0
				 *
				 * @param $expiration Expiration in seconds (default 3600 - one hour).
				 */
				$transient_expiration = apply_filters( 'wpdd_transient_expiration', HOUR_IN_SECONDS );
				set_transient( $transient_slug, $plugins_themes, $transient_expiration );
			}

		}

		// Omit resolved tickets per admin setting.
		if ( empty ( $this->options['show_all_tickets'] ) ) {

			foreach ( $plugins_themes as $plugin_theme_index => $plugin_theme ) {

				// Don't do anything if there's no ticket data for this plugin/theme.
				if ( empty( $plugin_theme->tickets_data ) ) {
					continue;
				}

				// Remove any tickets that are resolved.
				foreach ( $plugin_theme->tickets_data as $ticket_index => $ticket ) {
					if ( 'unresolved' != $ticket['status'] ) {
						unset( $plugins_themes[ $plugin_theme_index ]->tickets_data[ $ticket_index ] );
					}
				}

			}

		}

		// Return if no plugins are found.
		if ( ! $plugins_themes ) {
			echo '<p>' . sprintf( esc_html__( 'There are no %s to display.', 'wp-dev-dashboard' ), $ticket_type )  . '</p>';
			return;
		}

		// Sort plugins alphabetically for their first load.
		uasort( $plugins_themes, function( $plugin_1, $plugin_2 ) {
			return strnatcmp( strtolower( $plugin_1->name ), strtolower( $plugin_2->name ) );
		});

		// Loop through all plugins.
		foreach ( $plugins_themes as $plugin_theme ) {

			// Skip if there are no tickets.
			if ( empty ( $plugin_theme->tickets_data ) ) {
				continue;
			}

			$tickets_data = $plugin_theme->tickets_data;

			// Get count of unresolved tickets.
			$resolved_count = $unresolved_count = 0;
			foreach ( $tickets_data as $ticket_data ) {

				if ( 'unresolved' == $ticket_data['status'] ) {
					$unresolved_count++;
				} else {
					$resolved_count++;
				}

			}

			// Generate icon/count for unresolved tickets.
			$ticket_html = sprintf( '<span class="dashicons dashicons-editor-help wpdd-unresolved" title="%s"></span> %d', __( 'Unresolved', 'wp-dev-dashboard' ), $unresolved_count );

			// Generate icon/count for resolved tickets if need be.
			if ( ! empty( $this->options['show_all_tickets'] ) ) {
				$ticket_html .= sprintf( ' <span class="dashicons dashicons-yes wpdd-resolved" title="%s"></span> %d', __( 'Resolved', 'wp-dev-dashboard' ), $resolved_count );
			}

			$title = "{$plugin_theme->name} <span class='wpdd-ticket-count'>{$ticket_html}</span>";

			add_meta_box(
				"{$plugin_theme->slug}",
				$title,
				array( $this, 'do_plugin_metabox' ),
				$this->plugin_slug,
				'normal',
				'core',
				array(
					'tickets_data' => $tickets_data,
					'plugin_theme' => $plugin_theme
				)
			);

		}

	}

	/**
	 * Get tickets data for a specific user's plugin or themes.
	 *
	 * @since 1.0.0
	 *
	 * @param string $username    WordPress.org username.
	 * @param string $ticket_type Type of ticket to query for.
	 *
	 * @return array $plugins_themes Array of plugins|themes and associated info.
	 */
	public function get_tickets_data( $username, $ticket_type = 'plugins' ) {

		// Get tickets by user.
		$plugins_themes_by_user = $this->get_plugins_themes_by_user( $username, $ticket_type );

		// Get any plugins/themes that are manually set via the plugin settings.
		$plugins_themes_from_setting = $this->get_plugins_themes_from_settings( $ticket_type );

		// Merge plugins/themes for 1. user and 2. manually set in settings.
		$plugins_themes = array_merge( $plugins_themes_by_user, $plugins_themes_from_setting );

		// Loop through all plugins.
		foreach ( $plugins_themes as $index => $plugins_theme ) {

			$tickets_data = $this->get_unresolved_tickets( $plugins_theme->slug, $ticket_type );

			if ( ! $tickets_data ) {
				continue;
			}

			$plugins_themes[ $index ]->tickets_data = $tickets_data;

		}

		return $plugins_themes;

	}

	/**
	 * Output ticket metabox for a specific plugin/theme.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Post $post  Current post.
	 * @param array $metabox Current metabox.
	 */
	public function do_plugin_metabox( $post, $metabox ) {

		//$plugin_theme_data_html = $this->get_plugin_theme_data_html( $metabox['args']['plugin_theme'] );
		$plugin_theme_data_html = '';
		$tickets_html = $this->get_tickets_html( $metabox['args']['tickets_data'] );

		$output = $plugin_theme_data_html . $tickets_html;
		echo $output;

	}

	/**
	 * Generate HTML for output for a plugin's/theme's tickets.
	 *
	 * @since 1.0.0
	 *
	 * @param array $tickets_data Array of tickets data.
	 *
	 * @return string $html HTML output.
	 */
	public function get_tickets_html( $tickets_data ) {

		$html = '<ul>';

		// Get output for all tickets for this plugin.
		$i = 0;
		foreach ( $tickets_data as $ticket_data ) {

			$icon_html = '';

			// Generate status icons if ALL tickets are set to display.
			if ( ! empty ( $this->options['show_all_tickets'] ) ) {
				// Generate status icon.
				if ( 'resolved' == $ticket_data['status'] ) {
					$icon_class = 'yes';
				} else {
					$icon_class = 'editor-help';
				}

				$icon_html = sprintf( '<span class="dashicons dashicons-%s" title="%s"></span> ', $icon_class, ucfirst( $ticket_data['status'] ) );
			}

			$ticket_output = sprintf( '<li class="%s">%s<a href="%s" target="_blank">%s</a> (%s)</li>',
				'wpdd-' . $ticket_data['status'],
				$icon_html,
				$ticket_data['href'],
				$ticket_data['text'],
				$ticket_data['time']
			);

			/**
			 * Filter ticket output.
			 *
			 * @param string $ticket_output The <li> ticket output.
			 * @param array $ticket_data The ticket data array.
			 */
			$html .= apply_filters( 'wpdd_ticket_output', $ticket_output, $ticket_data );

			$i++;

		}

		$html .= '</ul>';

		return $html;

	}

	/**
	 * [Unused] Generate HTML for output for a plugin's/theme's meta data.
	 *
	 * @since 1.0.0
	 *
	 * @param stdClass Object $plugin_theme Plugin/theme object.
	 *
	 * @return string HTML output of plugin/theme meta data.
	 */
	public function get_plugin_theme_data_html( $plugin_theme ) {

		ob_start();
		?>
		<ul class="plugin-theme-data">
			<li class="version"><?php printf( '<h4>%s</h4>%s', esc_html__( 'Version', 'wp-dev-dashboard' ), $plugin_theme->version ); ?></li>
			<li class="wp-versions"><?php printf( '<h4>%s</h4>%s - %s', esc_html__( 'WP Versions', 'wp-dev-dashboard' ), $plugin_theme->requires, $plugin_theme->tested ); ?></li>
			<li class="rating"><?php printf( '<h4>%s</h4>%s', esc_html__( 'Rating', 'wp-dev-dashboard' ), $plugin_theme->rating ); ?></li>
		</ul>
		<?php

		return ob_get_clean();

	}

	/**
	 * Output JS necessary to trigger metabox sorting/toggling.
	 *
	 * @since 1.0.0
	 */
	public function print_metabox_trigger_scripts() {
		?>
		<script>
			jQuery( document ).on( 'ready wpddRefreshAfter', function(){
				postboxes.add_postbox_toggles( '<?php echo $this->plugin_slug; ?>' );
			});
		</script>
		<?php
	}

	/**
	 * Get data for a specific plugin/theme.
	 *
	 * @since 1.0.0
	 *
	 * @param string $username    WordPress.org username.
	 * @param string $ticket_type Type of ticket to check for.
	 *
	 * @return stdClass Object Plugin/theme object.
	 */
	public function get_plugins_themes_by_user( $username, $ticket_type = 'plugins' ) {

		// Return empty array if no username is specified.
		if ( ! $username ) {
			return array();
		}

		// Require file that includes plugin API functions.
		if ( 'plugins' == $ticket_type ) {
			require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
			$query_function = 'plugins_api';
			$query_action = 'query_plugins';
		} else {
			require_once ABSPATH . 'wp-admin/includes/theme.php';
			$query_function = 'themes_api';
			$query_action = 'query_themes';
		}

		$args = array(
			'author' => $this->options['username'],
			'fields' => array(
				'author'          => false,
				'active_installs' => false,
				'banners'         => false,
				'compatibility'   => false,
				'description'     => false,
				'downloaded'      => false,
				'homepage'        => false,
				'icons'           => false,
				'last_updated'    => false,
				'num_ratings'     => false,
				'ratings'         => false,
			),
		);

		$data = call_user_func( $query_function, $query_action, $args );

		$plugins_themes_by_user = array();

		if ( $data && ! is_wp_error( $data ) ) {
			$plugins_themes_by_user = ( 'plugins' == $ticket_type ) ? $data->plugins : $data->themes;
		}

		return $plugins_themes_by_user;

	}

	public function get_plugin_theme_data_by_slug( $slug, $ticket_type = 'plugins' ) {

		// Require file that includes plugin API functions.
		if ( 'plugins' == $ticket_type ) {
			require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
			$query_function = 'plugins_api';
			$query_action = 'plugin_information';
		} else {
			require_once ABSPATH . 'wp-admin/includes/theme.php';
			$query_function = 'themes_api';
			$query_action = 'theme_information';
		}

		$args = array(
			'slug' => $slug,
			'fields' => array(
				'author'          => false,
				'active_installs' => false,
				'banners'         => false,
				'compatibility'   => false,
				'description'     => false,
				'downloaded'      => false,
				'homepage'        => false,
				'icons'           => false,
				'last_updated'    => false,
				'num_ratings'     => false,
				'ratings'         => false,
			),
		);

		$data = call_user_func( $query_function, $query_action, $args );

		return $data;

	}

	/**
	 * Get array of plugin/theme slugs manually specified in the plugin settings.
	 *
	 * @since 1.0.0
	 *
	 * @param string $ticket_type Type of ticket to fetch.
	 *
	 * @return array Array of plugin/theme slugs.
	 */
	public function get_plugins_themes_from_settings( $ticket_type = 'plugins' ) {

		// Get manually added plugin/theme slugs from settings.
		if ( 'plugins' == $ticket_type ) {
			$plugins_themes_string = ( ! empty( $this->options['plugin_slugs'] ) ) ? $this->options['plugin_slugs'] : '';
		} else {
			$plugins_themes_string = ( ! empty( $this->options['theme_slugs'] ) ) ? $this->options['theme_slugs'] : '';
		}

		// Remove whitespace from string.
		$plugins_themes_string = str_replace( ' ', '', $plugins_themes_string );

		// Return empty array if there is no settings data to parse.
		if ( empty( $plugins_themes_string ) ) {
			return array();
		}

		// Convert to array from comma-separated list.
		$plugins_themes_array = explode( ',', $plugins_themes_string );

		// Create array of objects to match that returned by the plugin/theme API.
		$plugins_themes = array();
		foreach ( $plugins_themes_array as $plugin_theme_slug ) {
			$plugin_theme_data = $this->get_plugin_theme_data_by_slug( $plugin_theme_slug, $ticket_type );

			if ( $plugin_theme_data && ! is_wp_error( $plugin_theme_data ) ) {
				$plugins_themes[] = $this->get_plugin_theme_data_by_slug( $plugin_theme_slug, $ticket_type );
			}

		}

		return $plugins_themes;
	}

	/**
	 * Get unresolved ticket data for a specific plugin/theme.
	 *
	 * @since 1.0.0
	 *
	 * @param string $plugin_theme_slug Plugin/theme slug.
	 * @param string $ticket_type       plugins|themes
	 *
	 * @return array Array of all unresolved ticket data.
	 */
	public function get_unresolved_tickets( $plugin_theme_slug, $ticket_type = 'plugins' ) {

		$rows_data = array();

		$i = 1;
		while (	$new_rows_data = $this->get_unresolved_tickets_for_page( $plugin_theme_slug, $ticket_type, $i ) ) {
			$rows_data = array_merge( $rows_data, $new_rows_data );
			$i++;
		}

		return $rows_data;

	}

	/**
	 * Get unresolved ticket for a specific page of a plugin/theme support forum.
	 *
	 * @since 1.0.0
	 *
	 * @param string $plugin_theme_slug Plugin/theme slug.
	 * @param string $ticket_type       plugins|themes
	 * @param string $page_num          Support forum page to query.
	 *
	 * @return array Array of ticket data.
	 */
	public function get_unresolved_tickets_for_page( $plugin_theme_slug, $ticket_type = 'plugins', $page_num ) {

		$html = $this->get_page_html( $plugin_theme_slug, $page_num, $ticket_type );

		$html = str_get_html( $html );

		$table = $html->find( 'table#latest', 0 );

		// Return false if no table is found.
		if ( empty ( $table ) ) {
			return false;
		}

		// Remove thead row.
		$table->find( 'tr', 0 )->outertext = '';

		// Generate array of row data.
		$rows = $table->find( 'tr' );
		$rows_data = array();

		foreach ( $rows as $row ) {

			/**
			 * Make sure not to include any empty rows, which can also
			 * arise as a result of manually omitting a row via the
			 * following code: $row->outertext = '';
			 */
			if ( empty ( $row->outertext ) ) {
				continue;
			}

			// Get row attributes.
			$link = $row->find( 'a', 0 );
			$time = $row->find( 'small', 0 );

			$row_data['href'] = $link->href;
			$row_data['text'] = $link->innertext;
			$row_data['time'] = $time->innertext;
			$row_data['status'] = ( strpos( $row->class, 'resolved') !== false ) ? 'resolved' : 'unresolved';

			$rows_data[] = $row_data;

		}

		return $rows_data;

	}

	public function get_page_html( $plugin_theme_slug, $page_num, $ticket_type ) {

		if ( ! $page_num ) {
			return false;
		}

		if ( 'plugins' == $ticket_type ) {
			$remote_url = "https://wordpress.org/support/plugin/{$plugin_theme_slug}/page/{$page_num}";
		} else {
			$remote_url = "https://wordpress.org/support/theme/{$plugin_theme_slug}/page/{$page_num}";
		}

		$response = wp_remote_get( $remote_url );

		if ( 200 == wp_remote_retrieve_response_code( $response ) ) {

			// Decode the API data and grab the versions info.
			$response = wp_remote_retrieve_body( $response );

		} else {
			return WP_Error( 'error', __( 'Attempt to fetch failed', 'wp-dev-dashboard' ) );
			// Log errors?
		}

		return $response;

	}

}
