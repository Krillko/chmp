<?php
/**
 * Inputs:
 * @global array $chmp_plugin_vars - html attributes ex; "data-chmp-plugin"
 * @global string|null $chmp_plugin_content - content of <plugin> tag
 *
 * Output
 * Make a variable $chmp_output_plugin, this will replace entire <plugin> tag
 *
 */


if (isset($_POST['chmp_simple_mailer_send'])) {

	$test = 1;

	if (is_array($_POST['chmp_simple_mailer'])) {

		$chmp_simple_mailer_send = '<html>
	<body>
		<table>';

		foreach ($_POST['chmp_simple_mailer'] as $chmp_simple_mailer_key => $chmp_simple_mailer_value) {
			$chmp_simple_mailer_send .= '<tr>
				<td>'.$chmp_simple_mailer_key.'</td>
				<td>'.$chmp_simple_mailer_value.'</td>
			</tr>';
		}

		$chmp_simple_mailer_send .= '
		</table>
	</body>
</html>';


	}


} else {


	$chmp_output_plugin = $chmp_plugin_content;

}