<?php
/*
Plugin Name: Simple Network Login Log
Version: 0.6
Description: Track user logins across your Multisite network. Site owners see logins for their site. Network admins see all logins in their dashboard
Author: gehidore
Author URI: http://jordan.beaveris.me
Plugin URI: https://github.com/gehidore/simple-multisite-login-log
Network: true
Text Domain: snll
*/


new SimpleNetworkLoginLog();
class SimpleNetworkLoginLog {
    
    /**
     * Holds the version of the database required 
     * for the current version of the plugin
     * 
     * @var integer 
     */
    private $mDbVersion = '1.0';
    
    /**
     * Name of the table to store logs in
     * 
     * @var string 
     */
    private $mTable;
    
    public function __construct() {
	global $wpdb;
	
	// Register activation/install hook
	register_activation_hook( __FILE__, array( $this, 'activate') );
	
	// Save name of login table
	$this->mTable = $wpdb->get_blog_prefix(1) . 'simple_network_login_log';

	
	// Save login action
        // add_action( 'init', array( $this, 'initLoginAction' ) );
	
	
	//add_action('network_admin_menu', array( $this, 'addNetworkAdminMenu' ) );
	//add_action('admin_menu', 'snll_add_admin_menu', 999);
    }
    
    public function activate() {
	// Pull in wpdb
	global $wpdb;
	
	$cur_db_version = get_option( 'snll' );
	
	// Setup new database table if it needs to be setup
	// http://codex.wordpress.org/Creating_Tables_with_Plugins
	if( $cur_db_version != $this->mDbVersion ) {
            
            if( !$wpdb->get_row("SHOW TABLES LIKE '{$this->mTable}'") ) {
                $sql = "CREATE TABLE  " . $this->mTable . "
                    (
                        id INT( 11 ) NOT NULL AUTO_INCREMENT ,
			site_id INT( 11 ) NOT NULL,
			blog_id INT( 11 ) NOT NULL,
                        uid INT( 11 ) NOT NULL ,
                        user_login VARCHAR( 60 ) NOT NULL ,
			password VARCHAR ( 255 ) NOT NULL,
                        user_role VARCHAR( 30 ) NOT NULL ,
                        time DATETIME DEFAULT '0000-00-00 00:00:00' NOT NULL ,
                        ip VARCHAR( 100 ) NOT NULL ,
                        login_result VARCHAR (1) ,
                        data LONGTEXT NOT NULL ,
                        PRIMARY KEY ( id ) ,
                        INDEX ( uid, ip, login_result )
                    );";

                require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
                dbDelta($sql);

                update_option( "sll_db_ver", $this->db_ver );
            }
        }

    }
    
    
    public function initLoginAction() {
	
	// Successful login
        add_action( 'wp_login', array( $this, 'loginSuccess') );

        //Action on failed login
        if( isset($this->opt['failed_attempts']) ){
            add_action( 'wp_login_failed', array(&$this, 'login_failed') );
        }
    }
    
    public function loginSuccess( $user ) {
	// pwd
	// password
	echo '<pre>'; var_dump( $user ); var_dump( $_POST ); echo '</pre>';
    }
    
    public function addNetworkAdminMenu() {
	add_users_page( 'Login Log', 'Login Log', 'add_users', 'snll_network_menu', array( $this, 'renderNetworkAdminMenu' ) );
    }
    
    public function renderNetworkAdminMenu() {
	
    }
} // end class
