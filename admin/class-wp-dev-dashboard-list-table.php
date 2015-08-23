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

	function __construct( $table_type = 'plugins', $screen_id = null ){
        global $status, $page;

        $this->plugin_admin = WP_Dev_Dashboard_Admin::get_instance();
        $this->table_type = $table_type;

        //Set parent defaults
        parent::__construct( array(
            'singular'  => 'plugin_theme', //singular name of the listed records
            'plural'    => 'plugins_themes', //plural name of the listed records
            'ajax'      => true, //does this table support ajax?
           	'screen'    => $screen_id,
        ) );

    }

    function get_columns(){
    	$columns = array(
			'name'             => __( 'Title', 'wp-dev-dashboard' ),
			'version'          => __( 'Version', 'wp-dev-dashboard' ),
			'tested'           => __( 'WP Version Tested', 'wp-dev-dashboard' ),
			'rating'           => __( 'Rating', 'wp-dev-dashboard' ),
			'num_ratings'      => __( '# of Reviews', 'wp-dev-dashboard' ),
			'active_installs'  => __( 'Active Installs', 'wp-dev-dashboard' ),
			'downloaded'       => __( 'Downloads', 'wp-dev-dashboard' ),
			'unresolved_count' => __( 'Unresolved', 'wp-dev-dashboard' ),
			'resolved_count'   => __( 'Resolved', 'wp-dev-dashboard' ),
    	);

    	// Remove tested column for themes, since it doesn't apply.
    	if ( 'themes' == $this->table_type ) {
    		unset( $columns['tested'] );
    	}

    	return $columns;
    }

    function prepare_items() {
    	$columns = $this->get_columns();
    	$hidden = array();
    	$sortable = $this->get_sortable_columns();
    	$this->_column_headers = array( $columns, $hidden, $sortable );
    	$this->items = $this->plugin_admin->get_plugins_themes( $this->table_type );

    	// Sort items to reflect any table sorting.
    	usort( $this->items, array( $this, 'usort_reorder' ) );
    }

    function column_default( $item, $column_name ) {
    	switch( $column_name ) {
    		case 'name':
    			return sprintf( '<b><a href="%s" target="_blank">%s</a><b>', 'https://wordpress.org/plugins/' . $item->slug, $item->name );
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

    			if ( $wp_version ) {
    				if ( version_compare( $item->tested, $wp_version ) >= 0 ) {
    					$class = 'wpdd-current';
    				} else {
    					$class = 'wpdd-needs-update';
    				}
    			}

    			return sprintf( '<span class="%s">%s</span>', $class, $item->tested );
    		case 'rating':
    			return $item->rating ? $item->rating : __( 'NA', 'wp-dev-dashboard' );
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

  		// If no sort, default to title
		$orderby = ( ! empty( $_GET['orderby'] ) ) ? $_GET['orderby'] : 'name';

  		// If no order, default to asc
		$order = ( ! empty( $_GET['order'] ) ) ? $_GET['order'] : 'asc';

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
