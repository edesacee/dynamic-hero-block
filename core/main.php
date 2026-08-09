<?php
namespace dphb;

if (!defined('ABSPATH')) exit;

class Main {
	protected $_plugin_url; 
	protected $_plugin_folder; 
	protected $_script_version;
	protected $_style_version;

	protected $_plugin_path;

	static protected $__plugin_path;
	static protected $__plugin_url;
	static protected $__plugin_file;

	public function __construct($file, $options = array()) {
		$class_name = get_class();

		if (!$file) {
			throw new Error('Missing 1 argument'); 
		}

		$this->_plugin_url		= plugin_dir_url($file);
		$this->_plugin_folder	= basename(dirname($file));
		$this->_plugin_path		= plugin_dir_path($file);

		// echo '~~~ ' . $this->_plugin_path . '!<br />';

		if (isset($options ['script_version'])) {
			$this->_script_version	= $options ['script_version'];	
		}

		if (isset($options ['style_version'])) {
			$this->_style_version	= $options ['style_version'];
		}

		self::$__plugin_url			= plugin_dir_url($file);
		self::$__plugin_path		= plugin_dir_path($file);
		self::$__plugin_file		= $file;

		add_action('admin_enqueue_scripts', array($this, 'addAdminScripts'));
		add_action('wp_enqueue_scripts', array($this, 'addWebsiteScripts'));
	}

	public function getPluginUrl() {
		return $this->_plugin_url;
	}

	public function getPluginPath() {
		return $this->_plugin_path;
	}	

	public static function __log($msg, $filename = 'log.txt') {
		global $wp_filesystem;

		if (!is_a( $wp_filesystem, 'WP_Filesystem_Base')){
		    include_once(ABSPATH . 'wp-admin/includes/file.php');
		    $creds = request_filesystem_credentials(site_url());
		    wp_filesystem($creds);
		}

		$uploads_dir = wp_get_upload_dir();

		$logs_dir_parent = $uploads_dir ['basedir'] . '/' . basename(self::$__plugin_file, '.php');
		
		$logs_dir = $logs_dir_parent . '/logs';

		wp_mkdir_p($logs_dir_parent);
		wp_mkdir_p($logs_dir);		

		$file = $logs_dir . '/' . $filename;
		$contents = $wp_filesystem->get_contents($file);
		$wp_filesystem->put_contents($file, $contents .  "\n" . current_time('m d y h:i:s A') . ' => '. $msg);		
	}

	/**
	* adds scripts and css to website pages
	* @action wp_footer
	*/
	public function addWebsiteScripts() {
		wp_enqueue_media();
		wp_enqueue_script('jquery');

		//should make sure these files exists

		$wp_scripts	= $this->_plugin_url . 'scripts/wp-scripts.js';
		$wp_styles	= $this->_plugin_url . 'styles/wp-styles.css';

		$args = array('in_footer' => true);

		if (file_exists(self::$__plugin_path . 'scripts/wp-scripts.js')) {
			wp_enqueue_script($this->_plugin_folder . '-wp-scripts', $wp_scripts, array(), $this->_script_version ? $this->_script_version : 100, $args);
		}

		if (file_exists(self::$__plugin_path . 'styles/wp-styles.css')) {
			wp_enqueue_style($this->_plugin_folder . '-wp-styles', $wp_styles, array(), $this->_style_version ? $this->_style_version : 100);
		}
	}

	/**
	* adds scripts and css to admin pages
	* @action admin_enqueue_scripts
	*/
	public function addAdminScripts() {
		wp_enqueue_media();
		wp_enqueue_script('jquery');

		$admin_scripts = $this->_plugin_url . 'scripts/admin-scripts.js';
		$admin_styles  = $this->_plugin_url . 'styles/admin-styles.css';

		$args = array('in_footer' => true);

		if (file_exists(self::$__plugin_path . 'scripts/admin-scripts.js')) {
			wp_enqueue_script($this->_plugin_folder . '-admin-scripts', $admin_scripts, array(), $this->_script_version ? $this->_script_version : false, $args);
		}

		if (file_exists(self::$__plugin_path . 'styles/admin-styles.css')) {
			wp_enqueue_style($this->_plugin_folder . '-admin-styles', $admin_styles, array(), $this->_style_version ? $this->_style_version : false);
		}
	}

	public static function __getPluginPath() {
		return self::$__plugin_path;
	}

	public static function __getPluginURL() {
		return self::$__plugin_url;
	}

	public static function __getPluginVersion($file = '') {
		$headers = array('plugin_name' => 'Plugin Name',
	 					 'description' => 'Description',
	 					 'author' => 'Author',
	 					 'version' => 'Version');

		if (!$file) {
			$file = self::$__plugin_file;
		}
		$pluginfo = get_file_data($file, $headers);
		return $pluginfo['version'];
	}
} // end of class
