<?php

/**
 * Class hello_world2
 *
 * Object-oriented plugin
 */
class chmp_plugin_hello_world2 {
	private $chmp_plugin_vars,$chmp_plugin_content;

	/**
	 * @param array $chmp_plugin_vars
	 * @param string|null $chmp_plugin_content
	 */
	function __construct($chmp_plugin_vars,$chmp_plugin_content) {
		$this->chmp_plugin_vars = $chmp_plugin_vars;
		$this->chmp_plugin_content = $chmp_plugin_content;
	}




	/**
	 * THIS IS REQUIRED for all plugins
	 * Replaces the plugin tag
	 * @return string
	 */
	public function show_plugin() {
		return 'Hello World Object Orientent';
	}
}