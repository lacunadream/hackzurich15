<?php
	require 'PHPMailer/PHPMailerAutoload.php';

	class Mailer {
		
		private static $hostname;
		private static $username;
		private static $password;
		
		public static function init($hostname, $username, $password) {
			self::$hostname = $hostname;
			self::$username = $username;
			self::$password = $password;
		}
		
		public static function sendMail($fromAddress, $fromName, $toAddress, $subject, $body) {
			$mail = new PHPMailer();
			$mail->CharSet = 'UTF-8';
			
			$mail->IsSMTP();
			$mail->SMTPAuth = true;
			
			$mail->Host = self::$hostname;
			$mail->Username = self::$username;
			$mail->Password = self::$password;
			
			$mail->AddAddress($toAddress);
			$mail->From = $fromAddress;
			$mail->FromName = $fromName;
			
			$mail->Subject = $subject;
			
			$mail->isHTML(true);
			$mail->Body = '<html><head><title>'.$subject.'</title><style>
				td {
					vertical-align: top;
				}
			</style></head><body>'.$body.'</body></html>';
			
			$mail->Send();
		}
		
		
	}
?>