<?php
/**
 * Bootstraps the communications configurations. E-mail, SMS, etc.
 * These configurations depend on system configuration values from the database set by an administrator.
*/
use li3_swiftmailer\mailer\Transports;

// Try to use PHP's mail() as a default.
$emailConfiguration['blackprint_mail'] = array('adapter' => 'PhpMail');

// Use (and the system will prefer) SMTP if configured.
if($blackprintConfig && isset($blackprintConfig['communications']) && isset($blackprintConfig['communications']['smtp'])) {
	$smtpConfig = $blackprintConfig['communications']['smtp'];
	// Make sure we have enough info to configure of course.
	if(
		isset($smtpConfig['host']) && isset($smtpConfig['username']) && isset($smtpConfig['password']) &&
		!empty($smtpConfig['host']) && !empty($smtpConfig['username']) && !empty($smtpConfig['password'])
	) {
		$port = isset($smtpConfig['port']) && !empty($smtpConfig['port']) ? (int)$smtpConfig['port']:25;
		$domain = isset($smtpConfig['domain']) && !empty($smtpConfig['domain']) ? $smtpConfig['domain']:$_SERVER['HTTP_HOST'];
		$blackprintSmtpConfig = array(
				'adapter' => 'Smtp',
				'host' => $smtpConfig['host'],
				'port' => $port,
				//'encryption' => 'tls',
				'username' => $smtpConfig['username'],
				'password' => $smtpConfig['password'],
				'domain' => $domain
		);
		if(isset($smtpConfig['tls']) && !empty($smtpConfig['tls'])) {
			$blackprintSmtpConfig['encryption'] = 'tls';
			// SwiftMailer won't work with TLS if not also using port 587
			// @see http://stackoverflow.com/questions/15093702/swiftmailer-completely-broken-for-smtp
			// So change the port to 587 if using TLS, unless strictly set.
			$blackprintSmtpConfig['port'] = (isset($smtpConfig['port']) && !empty($smtpConfig['port'])) ? $smtpConfig['port']:587;
		}
		
		$emailConfiguration['blackprint_smtp'] = $blackprintSmtpConfig;
	}
}

Transports::config($emailConfiguration);
?>