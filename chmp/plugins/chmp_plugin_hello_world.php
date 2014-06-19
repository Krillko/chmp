<?php
/**
 * A non-object oriented plugin
 *
 * This kind of plugin can't have user settings,
 * example usage, send mail
 *
 * PLEASE NOTE: Using this method may overwrite other varibles in use, be
 * really carefull with name. Good names should contain plugin name ex:
 * $chmp_plugin_hello_world_something = 1
 *
 * Inputs:
 * @global array $chmp_plugin_vars - html attributes ex; "data-chmp-plugin"
 * @global string|null $chmp_plugin_content - content of <plugin> tag
 * @global array $chmp_plugin_settings - settings made by the user
 *
 * Output
 * Make a variable $chmp_output_plugin, this will replace entire <plugin> tag
 *
 */


$chmp_output_plugin = 'Hello World Plugin';