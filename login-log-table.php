<?php

if (!class_exists('WP_List_Table')) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * @see http://wpengineer.com/2426/wp_list_table-a-step-by-step-guide/ 
 */
class LoginLogTable extends WP_List_Table {

    private $mTable;

    function __construct($table) {
	$this->mTable = $table;
	
	parent::__construct( array() );
        }

    function get_columns() {
	$columns = array(
	    'id' => 'Login ID',
	    'user_login' => 'Username',
	    'display_name' => 'Name',
	    'user_role' => 'User Role',
	    'time' => 'Time',
	    'login_result' => 'Login Result',
	);
	return $columns;
    }

    function get_sortable_columns() {
	$columns = array(
	    'id' => 'Login ID',
	    'site_id' => 'Network',
	    'blog_id' => 'Site',
	    'user_login' => 'Username',
	    'display_name' => 'Name',
	    'user_role' => 'User Role',
	    'time' => 'Time',
	    'login_result' => 'Login Result',
	);
	return $columns;
    }

    function usort_reorder($a, $b) {
	// If no sort, default to title
	$orderby = (!empty($_GET['orderby']) ) ? $_GET['orderby'] : 'booktitle';
	// If no order, default to asc
	$order = (!empty($_GET['order']) ) ? $_GET['order'] : 'asc';
	// Determine sort order
	$result = strcmp($a[$orderby], $b[$orderby]);
	// Send final sort direction to usort
	return ( $order === 'asc' ) ? $result : -$result;
    }

    function prepare_items() {
	$columns = $this->get_columns();
	$hidden = array();
	$sortable = $this->get_sortable_columns();
	$this->_column_headers = array($columns, $hidden, $sortable);
	
	//$this->items = usort( $this->get_data(), array( $this, 'usort_reorder' ) );
	$this->items = $this->get_data();
    }

    function get_data() {
	global $wpdb, $blog_id;

	$sql = "SELECT * FROM $this->mTable WHERE blog_id='$blog_id' ORDER BY time DESC";
	$data = $wpdb->get_results($sql, 'ARRAY_A');
	return $data;
    }

    function column_default($item, $column_name) {
	switch ($column_name) {
	    case 'display_name':

		if (is_numeric($item['uid']) && $item['uid'] > 0) {
		    $user = get_userdata($item['uid']);
		    return $user->display_name . $this->row_actions( array( '<a href="#">Edit</a>', '<a href="#">Remove</a>') );
		}
		else
		    return '';
		break;

	    case 'site_id':
		$name = $this->get_network_meta($item['site_id'], 'site_name');
		return $name[0]['meta_value'];
		break;

	    case 'blog_id':
		$name = $this->get_site_meta($item['blog_id'], 'blogname');
		return $name;
		break;
	    default:
		return $item[$column_name]; //Show the whole array for troubleshooting purposes
	}
    }

    function get_network_meta($site_id, $name) {
	global $wpdb;

	$table = $wpdb->get_blog_prefix(0) . 'sitemeta';

	$sql = "SELECT * FROM $table WHERE meta_key='$name' AND site_id='$site_id'";
	$data = $wpdb->get_results($sql, 'ARRAY_A');
	return $data;
    }

    function get_site_meta($blog_id, $name) {

	return get_blog_option($blog_id, $name);
	global $wpdb;

	$table = $wpdb->get_blog_prefix(0) . 'sitemeta';

	$sql = "SELECT * FROM $table WHERE meta_key='$name' AND site_id='$site_id'";
	$data = $wpdb->get_results($sql, 'ARRAY_A');
	return $data;
    }

}