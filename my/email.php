<?php
ini_set( 'display_errors', 1 );
error_reporting( E_ALL );

require_once('../config.php');

$message = $_POST['message'];
$from = 'webmaster@tasa.com.pe';
$subject = 'TASA - Mensaje de seguimiento';

var_dump($_POST);
exit;

if($_POST['idUsersAll']) {
	$userIds = explode( ',', $_POST['idUsersAll']);

	$existingMails = array();

	foreach($userIds as $userId) {
		$foruser = core_user::get_user($userId);
		$emailTo = $foruser->email;

		if(in_array($emailTo, $existingMails)) {
			continue;
		} else {
			var_dump('email => ' . $emailTo);
			var_dump('mensaje => ' . $message);
			var_dump('de => '. $from);
			var_dump('subject => ' . $subject);
			$existingMails[] = $emailTo;
			email_to_user($foruser, $from, $subject, $message);
		}
	}
} else {
	$foruser = core_user::get_user($_POST['idUser']);

	$emailTo = $foruser->email;

	var_dump('email => ' . $emailTo);
	var_dump('mensaje => ' . $message);
	var_dump('de => '. $from);
	var_dump('subject => ' . $subject);
	email_to_user($foruser, $from, $subject, $message);
}