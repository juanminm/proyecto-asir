<?php
$token = filter_input(INPUT_GET, 'certrequestid', FILTER_SANITIZE_STRING);

if (!empty($token)) {
	$sql = "SELECT `uCVerifDate`, `uCVerifUsed` FROM `uCertVerification` WHERE `uCVerifToken` = ?";
	$stmt = $conexion->prepare($sql);
	$stmt->bind_param('s', $token);
	$stmt->execute();
	$stmt->bind_result($date,$tokenUsed);
	$stmt->fetch();
	$stmt->close();

	if (date("Y-m-d H:i:s") < strtotime($date . ' + 1 day') && $tokenUsed == 0) {
		$sql = "SELECT `userMail`, `userNick` FROM `users` WHERE `userId` = ?";
		$stmt = $conexion->prepare($sql);
		$stmt->bind_param('i', $_SESSION['userId']);
		$stmt->execute();
		$stmt->bind_result($email, $username);
		$stmt->fetch();
		$stmt->close();

		$sql = "SELECT MAX(`userCertSerial`) FROM `userCerts`";
		$stmt = $conexion->prepare($sql);
		$stmt->execute();
		$stmt->bind_result($serial);
		$stmt->fetch();
		$stmt->close();

		$serial = hexdec($serial)+1;


		$dn = array(
			"commonName" => $username,
			"emailAddress" => $email
		);

		$SSLcnf = array('config'=>'/etc/apache2/certs/openssl.cnf');
		$caCert = "file:///etc/apache2/certs/proyectoasir_CA.crt";
		$caPrivKey = array("file:///etc/apache2/certs/private/proyectoasir_CA.key", "c0d3ly0k0");

		$args = array(
			'extracerts' => $caCert,
			'friendly_name' => 'Signed certificate'
		);

		$privKey = openssl_pkey_new();
		$csr = openssl_csr_new($dn, $privKey, $SSLcnf);
		$sscert = openssl_csr_sign($csr, $caCert, $caPrivKey, 730, $SSLcnf, $serial);

		//openssl_pkey_export($privKey, $pkeyout);
		//openssl_csr_export($csr, $csrout);
		//openssl_x509_export($sscert, $certout);
		openssl_pkcs12_export($sscert, $pk12out, $privKey, null, $args);
		$parsed = openssl_x509_parse($caCert,TRUE);

		$issuerCN = "CN=".$parsed['issuer']['CN'].",C=".$parsed['issuer']['C'].",OU=".$parsed['issuer']['OU'].",O=".$parsed['issuer']['O'];
		if((strlen($serial = strtoupper(dechex($serial)))%2)!=0){
			$serial = "0".$serial;
		}
		$revoked = 0;

		$htmlBody = <<<_HTMLMAIL_
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<title>Certíficado generado</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
	</head>
	<body>
		<h2>Saludos,</h2>

		<p>Su certificado ha sido generado. Configura tu navegador para añadir el certificado en el almacen de certificados propio.</p>

		<p>Atentamente,<br>
		ProyectoASIR.com</p>
	</body>
</html>
_HTMLMAIL_;

		// Mensaje en texto plano
		$altBody = <<<_TEXTMAIL_
Saludos,

Su certificado ha sido generado. Configura tu navegador para añadir el certificado en el almacen de certificados propio.

Atentamente,
ProyectoASIR.com
_TEXTMAIL_;

		require './inc/PHPMailer/PHPMailerAutoload.php';
		$mailer = new PHPMailer();
		$mailer->IsSMTP();
		$mailer->IsHTML(true);
		$mailer->Host = $config['mailHost'];
		$mailer->SMTPSecure = $config['mailSecure'];
		$mailer->Port = $config['mailPort'];
		$mailer->SMTPAuth = TRUE;
		$mailer->Username = $config['mailUser'];
		$mailer->Password = $config['mailPass'];
		$mailer->From = $config['mailUser'];
		$mailer->FromName = $config['mailRealName'];
		$mailer->CharSet = 'UTF-8';
		$mailer->Body = $htmlBody;
		$mailer->AltBody = $altBody;
		$mailer->Subject = "Certificado generado";
		$mailer->addStringAttachment($pk12out, 'certificate.p12', 'base64');
		$mailer->AddAddress($email);

		if (!$mailer->Send()){	// El mensaje no se ha podido enviar
			echo "El mensaje no ha sido enviado <br/>";
			echo "Mailer Error: " . $mailer->ErrorInfo;
		} else {	// El mensaje se ha enviado
			$sql = "INSERT INTO `userCerts` (`userId`, "
					. "`userCertSerial`, `userCertIssuerDn`, `userCertName`, "
					. "`userCertEmail`, `userCertRevoked`) "
					. "VALUES (?, ?, ?, ?, ?, ?)";
			$stmt = $conexion->prepare($sql);
			$stmt->bind_param('issssi', $_SESSION['userId'], $serial, $issuerCN, $username, $email, $revoked);
			$stmt->execute();
			$stmt->close();

			$sql = "UPDATE `uCertVerification` "
					. "SET `uCVerifUsed` = 1 "
					. "WHERE `uCVerifToken` = ?";
			$stmt = $conexion->prepare($sql);
			$stmt->bind_param('i', $token);
			$stmt->execute();
			$stmt->close();

			$sql = "UPDATE `userCerts` "
					. "SET `userCertRevoked` = 1 "
					. "WHERE `userId` = ? AND "
					. "`userCertSerial` != ?";
			$stmt = $conexion->prepare($sql);
			$stmt->bind_param('is',$_SESSION['userId'],$serial);
			$stmt->execute();
			$stmt->close();
			?>
			<div class="alert alert-success">
				<p>Se te ha enviado un correo con los archivos usados para la certificacióna.</p>

				<a href="index.php">Volver</a> a la página de inicio de sesión.
			</div>
		<?php
		}
	} else {
		?>
		<div class="alert alert-info">
			<p>El token ha expirado.</p>

			<a href="index.php">Volver</a> a la página de inicio de sesión.
		</div>
		<?php
	}
} else {
	?>
	<div class="alert alert-danger">
		<p>Operación no soportada.</p>

		<a href="index.php">Volver</a> a la página de inicio de sesión.
	</div>
	<?php
}