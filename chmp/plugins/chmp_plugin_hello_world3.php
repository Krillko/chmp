<?php

/**
 * Class hello_world3
 *
 * Object-oriented plugin
 *
 * HTML for module can be found in /chmp/documentation/Example_modules.html
 *
 */
class chmp_plugin_hello_world3 {
	private $chmp_plugin_vars,$chmp_plugin_content;
	/**
	 * @var array
	 */
	private $chmp_plugin_settings;

	/**
	 * @param array $chmp_plugin_vars
	 * @param string|null $chmp_plugin_content
	 * @param array $chmp_plugin_settings
	 */
	function __construct($chmp_plugin_vars, $chmp_plugin_content, $chmp_plugin_settings = array()) {
		$this->chmp_plugin_vars = $chmp_plugin_vars;
		$this->chmp_plugin_content = $chmp_plugin_content;
		$this->chmp_plugin_settings = $chmp_plugin_settings;
	}


	/**
	 * Just a test
	 * @return string
	 */
	private function test_table() {
		extract($this->chmp_plugin_settings);

		$out = '<table>
					<tr>
						<td>Text:</td>
						<td>'.$text.'</td>
					</tr>
					<tr>
						<td>Textarea:</td>
						<td>'.$textarea.'</td>
					</tr>
					<tr>
						<td>Checkbox:</td>
						<td>'.($checkbox == 'true' ? 'Yes':'No').'</td>
					</tr>
					<tr>
						<td>Number:</td>
						<td>'.floatval($number).'</td>
					</tr>
					<tr>
						<td>Select:</td>
						<td>'.$select.'</td>
					</tr>
				</table>';

		return $out;

	}



	/**
	 * THIS IS REQUIRED for all plugins
	 * Replaces the plugin tag
	 * @return string
	 */
	public function show_plugin() {
		return $this->test_table();
	}
}