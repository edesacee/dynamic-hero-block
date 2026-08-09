<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class DPHB_Main extends dphb\Main {

    public function __construct($file, $options = array()) {
		$class_name = get_class();

		parent::__construct($file, $options);

		add_action('admin_enqueue_scripts', array($class_name , '__adminScripts'));

		new dphb\Hero_MetaBox('dphb-hero-settings', esc_html__('Dynamic Hero', 'dynhero'), array('page'), 'side', 'high');
	} //func:__construct

	public static function __adminScripts() {
		$core_form_scripts	= self::$__plugin_url . 'core/scripts/form.js';

		$args = array('in_footer' => true);

		$core_form_styles	= self::$__plugin_url . 'core/styles/form.css';

		$args = array('in_footer' => true);

		if (file_exists(self::$__plugin_path . 'core/styles/form.css')) {
			wp_enqueue_style('flowy-core-forms', $core_form_styles, array(), time());
		}

		$core_form_scripts	= self::$__plugin_url . 'core/scripts/form.js';

		if (file_exists(self::$__plugin_path . 'core/scripts/form.js')) {
			wp_enqueue_script('flowy-core-forms', $core_form_scripts, array(), time(), $args);
		}
	} // function	
} //class