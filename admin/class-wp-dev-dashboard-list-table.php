<?php

/**
 * The WPDD_List_Table class.
 *
 * Used to generate a list table of plugin/theme data.
 *
 * @link       http://wordpress.org/plugins/wp-dev-dashboard
 * @since      1.2.0
 *
 * @package    WP_Dev_Dashboard
 * @subpackage WP_Dev_Dashboard/admin
 */

// Require list table file if not already included.
if( ! class_exists( 'WP_List_Table' ) ){
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class WPDD_List_Table extends WP_List_Table {

	private $plugin_admin;

	private $table_type;

	private $current_url;

	function __construct( $screen_id = null, $current_url = null ){
	    global $status, $page;

	    $this->plugin_admin = WP_Dev_Dashboard_Admin::get_instance();

	    // Add passed current URL.
	    $this->current_url = $current_url;

	    //Set parent defaults
	    parent::__construct( array(
	        'singular'  => 'plugin_theme', //singular name of the listed records
	        'plural'    => 'plugins_themes', //plural name of the listed records
	        'ajax'      => true, //does this table support ajax?
	       	'screen'    => $screen_id,
	    ) );

	}

	/**
	 * Remove the default "fixed" class so sorting triangles don't wrap.
	 *
	 * @since 1.3.1
	 *
	 * @return array List of CSS classes for the table tag.
	 */
	protected function get_table_classes() {
		$classes = parent::get_table_classes();

		if ( ( $key = array_search( 'fixed', $classes ) ) !== false ) {
		    unset( $classes[ $key ] );
		}

		return $classes;
	}

	function get_columns(){
		$columns = array(
			'name'             => __( 'Title', 'wp-dev-dashboard' ),
			'type'             => __( 'Type', 'wp-dev-dashboard' ),
			'version'          => __( 'Version', 'wp-dev-dashboard' ),
			'tested'           => __( 'WP Version Tested', 'wp-dev-dashboard' ),
			'rating'           => __( 'Rating', 'wp-dev-dashboard' ),
			'num_ratings'      => __( '# of Reviews', 'wp-dev-dashboard' ),
			'active_installs'  => __( 'Active Installs', 'wp-dev-dashboard' ),
			'downloaded'       => __( 'Downloads', 'wp-dev-dashboard' ),
			'unresolved_count' => __( 'Unresolved', 'wp-dev-dashboard' ),
			'resolved_count'   => __( 'Resolved', 'wp-dev-dashboard' ),
		);

		return $columns;
	}

	/**
	 * Print column headers, accounting for hidden and sortable columns.
	 *
	 * It is necessary to override the default function in
	 * WP_List_Table because sorting is broken when called via Ajax.
	 * The $current_url ends up set to the admin-ajax.php file, when
	 * we need it to refer to the actual referring url.
	 *
	 * @since 1.2.0
	 *
	 * @param bool $with_id Whether to set the id attribute or not
	 */
	public function print_column_headers( $with_id = true ) {
		list( $columns, $hidden, $sortable, $primary ) = $this->get_column_info();

		// Get passed URL if it exists.
		$current_url = $this->current_url;

		// Add check to see if current_url has been manually passed.
		if ( ! $this->current_url ) {
			$current_url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
		}

		$current_url = remove_query_arg( 'paged', $current_url );

		// Check for manually passed current_url for Ajax calls.
		if ( $this->current_url ) {

			$url_parts = parse_url( $this->current_url );

			if ( is_array( $url_parts ) && array_key_exists( 'query', $url_parts ) ) {
				parse_str( $url_parts['query'], $query );
			} else {
				$query = array();
			}

			$current_orderby = ( ! empty( $query['orderby'] ) ) ? $query['orderby'] : 'name';
			$current_order = ( ! empty( $query['order'] ) ) ? $query['order'] : 'asc';

		} else {
			// If no sort, default to title
			$current_orderby = ( ! empty( $_GET['orderby'] ) ) ? $_GET['orderby'] : 'name';

	  		// If no order, default to asc
			$current_order = ( ! empty( $_GET['order'] ) ) ? $_GET['order'] : 'asc';
		}

		if ( ! empty( $columns['cb'] ) ) {
			static $cb_counter = 1;
			$columns['cb'] = '<label class="screen-reader-text" for="cb-select-all-' . $cb_counter . '">' . __( 'Select All' ) . '</label>'
				. '<input id="cb-select-all-' . $cb_counter . '" type="checkbox" />';
			$cb_counter++;
		}

		foreach ( $columns as $column_key => $column_display_name ) {
			$class = array( 'manage-column', "column-$column_key" );

			if ( in_array( $column_key, $hidden ) ) {
				$class[] = 'hidden';
			}

			if ( 'cb' == $column_key )
				$class[] = 'check-column';
			elseif ( in_array( $column_key, array( 'posts', 'comments', 'links' ) ) )
				$class[] = 'num';

			if ( $column_key === $primary ) {
				$class[] = 'column-primary';
			}

			if ( isset( $sortable[$column_key] ) ) {
				list( $orderby, $desc_first ) = $sortable[$column_key];

				if ( $current_orderby == $orderby ) {
					$order = 'asc' == $current_order ? 'desc' : 'asc';
					$class[] = 'sorted';
					$class[] = $current_order;
				} else {
					$order = $desc_first ? 'desc' : 'asc';
					$class[] = 'sortable';
					$class[] = $desc_first ? 'asc' : 'desc';
				}

				$column_display_name = '<a href="' . esc_url( add_query_arg( compact( 'orderby', 'order' ), $current_url ) ) . '"><span>' . $column_display_name . '</span><span class="sorting-indicator"></span></a>';
			}

			$tag = ( 'cb' === $column_key ) ? 'td' : 'th';
			$scope = ( 'th' === $tag ) ? 'scope="col"' : '';
			$id = $with_id ? "id='$column_key'" : '';

			if ( !empty( $class ) )
				$class = "class='" . join( ' ', $class ) . "'";

			echo "<$tag $scope $id $class>$column_display_name</$tag>";
		}
	}

	function prepare_items() {
		$columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );
		$this->items = array_merge( $this->plugin_admin->get_plugins_themes( 'plugins' ), $this->plugin_admin->get_plugins_themes( 'themes' ) );

		// Sort items to reflect any table sorting.
		usort( $this->items, array( $this, 'usort_reorder' ) );
	}

	function column_default( $item, $column_name ) {

		switch( $column_name ) {
			case 'name':
				return sprintf( '<b><a href="%s" target="_blank">%s</a><b>', 'https://wordpress.org/plugins/' . $item->slug, $item->name );
			case 'type':
				return 'plugins' == $item->type ? __( 'Plugin', 'wp-dev-dashboard' ) : __( 'Theme', 'wp-dev-dashboard' );
			case 'version':
				return $item->version;
			case 'tested':
				$update_data = get_site_transient( 'update_core' );
				$wp_branches = $update_data->updates;

				$wp_version = '';
				foreach( $wp_branches as $index => $branch ) {
					if ( 'latest' == $branch->response ) {
						$wp_version = $wp_branches[ $index ]->version;
					}
				}

				$class = '';

				if ( $wp_version ) {
					if ( version_compare( $item->tested, $wp_version ) >= 0 ) {
						$class = 'wpdd-current';
					} else {
						$class = 'wpdd-needs-update';
					}
				}

				if ( '' == $item->tested ) {
					$class = '';
					$item->tested = __( 'N/A', 'wp-dev-dashboard' );
				}

				return sprintf( '<span class="%s">%s</span>', $class, $item->tested );
			case 'rating':
				return $item->rating ? $item->rating : __( 'NA', 'wp-dev-dashboard' );
		case 'active_installs':
		case 'downloaded':
		case 'num_ratings':
		case 'unresolved_count':
		case 'resolved_count':
				return number_format_i18n( $item->$column_name );
			default:
				return $item->$column_name;
  		}
	}

	function get_sortable_columns() {
		$sortable_columns = array(
			'name'             => array( 'name', false ),
			'version'          => array( 'version', false ),
			'tested'           => array( 'tested', false ),
			'rating'           => array( 'rating', false ),
			'num_ratings'      => array( 'num_ratings', false ),
			'active_installs'  => array( 'active_installs', false ),
			'downloaded'        => array( 'downloaded', false ),
			'unresolved_count' => array( 'unresolved_count', false ),
			'resolved_count'   => array( 'resolved_count', false ),
		);
		return $sortable_columns;
	}

	function usort_reorder( $a, $b ) {

		// Check for manually passed current_url for Ajax calls.
		if ( $this->current_url ) {

			$url_parts = parse_url( $this->current_url );

			if ( is_array( $url_parts ) && array_key_exists( 'query', $url_parts ) ) {
				parse_str( $url_parts['query'], $query );
			} else {
				$query = array();
			}

			$orderby = ( ! empty( $query['orderby'] ) ) ? $query['orderby'] : 'name';
			$order = ( ! empty( $query['order'] ) ) ? $query['order'] : 'asc';

		} else {
			// If no sort, default to title
			$orderby = ( ! empty( $_GET['orderby'] ) ) ? $_GET['orderby'] : 'name';

	  		// If no order, default to asc
			$order = ( ! empty( $_GET['order'] ) ) ? $_GET['order'] : 'asc';
		}

  		// Determine sort order
  		switch( $orderby ) {
  			case 'version':
  			case 'tested':
  				$result = version_compare( $b->$orderby, $a->$orderby );
  				break;
  			case 'rating':
  			case 'num_ratings':
  			case 'active_installs':
  			case 'downloaded':
  			case 'unresolved_count':
  			case 'resolved_count':
  				$result = ( $a->$orderby < $b->$orderby ) ? 1 : -1;
  				break;
  			default:
  				$result = strnatcasecmp( ucwords( $a->$orderby ), ucwords( $b->$orderby ) );
  		}

  		// Send final sort direction to usort
		return ( $order === 'asc' ) ? $result : -$result;
	}

}
