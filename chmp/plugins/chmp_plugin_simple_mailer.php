<?php


/**
 * @param string $subject
 * @param string $html_listing
 * @param string|array $to
 * @param string|array $bcc
 * @param string $attachment
 * @param string $sendername
 * @return bool
 */
function chmp_simple_mailer_send($subject, $html_listing, $to, $bcc,  $attachment, $sendername = '') {
	require_once ('swift/swift_required.php');

	// fixing to and bcc
	if (!is_array($to)) {
		$to = array($to);
	}
	if (!is_array($bcc)) {
		if ($bcc != '') {
			$bcc = array($bcc);
		} else {
			$bcc = array();
		}
	}

	// fix sendername
	if ($sendername == '') {
		$sendername = "order@diakrit.com";
	}


	$test = 1;


	// Create the Transport
	$transport = Swift_SmtpTransport::newInstance(CHMP_EMAIL_HOST,  CHMP_EMAIL_PORT, CHMP_EMAIL_ENCRYPTION)
		->setUsername(CHMP_EMAIL_USERNAME)
		->setPassword(CHMP_EMAIL_PASSWORD)
	;

	// Create the Mailer using your created Transport
	$mailer = Swift_Mailer::newInstance($transport);


	// Create the message
	$message = Swift_Message::newInstance()

		// Give the message a subject
		->setSubject($subject)

		// Set the From address with an associative array
		//->setFrom(array('order@diakrit.com' => 'Diakrit Order Test'))

		// Set the To addresses with an associative array
		//->setTo(array('receiver@domain.org', 'other@domain.org' => 'A name'))
		->setTo($to)

		// Fallback content for non-html mail
		//->setBody('Here is the message itself')

		// And optionally an alternative body
		->addPart($html_listing, 'text/html');

	// set sender name
	$message->setFrom(array("order@diakrit.com" => $sendername));




	// Optionally add any attachments

	if ($attachment != '') {

		$message->attach(Swift_Attachment::fromPath($attachment));

	}



	// Send the message
	$result = $mailer->send($message);


	return($result);

}


/**
 * Inputs:
 * @global array $chmp_plugin_vars - html attributes ex; "data-chmp-plugin"
 * @global string|null $chmp_plugin_content - content of <plugin> tag
 *
 * Output
 * Make a variable $chmp_output_plugin, this will replace entire <plugin> tag
 *
 */





if (is_array($_POST['chmp_simple_mailer'])) {

$chmp_simple_mailer_send = '<html>
	<body>
		<h1>'.$chmp_plugin_vars['data-chmp-subject'].'</h1>
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


	$chmp_output_plugin =
		($chmp_plugin_vars['data-chmp-sent-class'] != '' ? '<div class="'.$chmp_plugin_vars['data-chmp-sent-class'].'">':'').
		$chmp_plugin_vars['data-chmp-sent-message'].
		($chmp_plugin_vars['data-chmp-sent-class'] != '' ? '</div>':'');



	$test = 1;

	chmp_simple_mailer_send($chmp_plugin_vars['data-chmp-subject'],$chmp_simple_mailer_send,$chmp_plugin_vars['data-chmp-to'],'','',$chmp_plugin_vars['data-chmp-sender-name']);



} else {


	$chmp_output_plugin = $chmp_plugin_content;

}