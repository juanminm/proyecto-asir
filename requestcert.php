<?php

$sql = "SELECT `userMail`, `userNick` FROM `users` WHERE `userId` = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param('i', $_SESSION['userId']);
$stmt->execute();
$stmt->bind_result($email, $username);
$stmt->fetch();
$stmt->close();

if (isset($_POST['submit'])) {	// Se ha recibido un formulario.
	// Limpia y valida el string de tipo correo.
	// Token aleatorio de verificación
	$token = base64_encode(openssl_random_pseudo_bytes(16));

	// Debe de ser codificado de forma valida en una URL.
	$verifystring = urlencode($token);
	$date = date("Y-m-d H:i:s");
	// UPDATE la tabla de la base de datos.

	// Mensaje en HTML
	$htmlBody = <<<_HTMLMAIL_
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<title>Recuperación de contraseña</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
	</head>
	<body>
		<h2>Saludos,</h2>
		<p>Una solicitud ha sido enviada para generar un certificado.<br/>
		Por favor, acceda al siguiente link para completar la operación:<br/></p>

		<a href="{$config['webProto']}://{$config['webHost']}/index.php?action=getcert&certrequestid=$verifystring">Click aquí</a>

		<p>Si usted ha recibido este correo por error o no lo ha solicitado, puede ignorarlo.</p>

		<p>Atentamente,<br/>
		ProyectoASIR.com</p>
	</body>
</html>
_HTMLMAIL_;

	// Mensaje en texto plano
	$altBody = <<<_TEXTMAIL_
Saludos,
Una solicitud ha sido enviada para generar un certificado.
Por favor, acceda al siguiente link para completar la operación:

{$config['webProto']}://{$config['webHost']}/index.php?action=getcert&certrequestid=$verifystring

Si usted ha recibido este correo por error o no lo ha solicitado, puede ignorarlo.

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
	$mailer->Subject = "Generación de certificado";
	$mailer->AddAddress($email);

	if(!$mailer->Send()){	// El mensaje no se ha podido enviar
		echo "El mensaje no ha sido enviado <br/>";
		echo "Mailer Error: " . $mailer->ErrorInfo;
	} else {	// El mensaje se ha enviado
		$sql = "INSERT INTO `uCertVerification` (`userId`, `uCVerifToken`, "
				. "`uCVerifDate`, `uCVerifUsed`) VALUES (?, ?, ?, 0)";
		$stmt = $conexion->prepare($sql);
		$stmt->bind_param('iss', $_SESSION['userId'], $token, $date);
		$stmt->execute();
		$stmt->close();
		?>
		<div class="alert alert-success">
			<p>Se te ha enviado un correo con las instrucciones para restrablecer su contraseña.</p>

			<a href="index.php?action=login">Volver</a> a la página de inicio de sesión.
		</div>
		<?php
	}
} else {	// No se ha recibido un formulario.
	?>
	<div style="margin: 10px 0px">
		<h1>Formulario de generación de certificado</h1>
		<p>
			Como medida de segurirar, tras darle a generar, se te enviará un correo de confirmación.
		</p>
		<form action="index.php?action=requestcert" method="post">
			<div style="margin: 10px 0px">
				<table>
					<th scope="col" colspan="2">Datos del certificado</th>
					<tr>
						<td><strong>Usuario</strong></td>
						<td><?php echo $username; ?></td>
					</tr>
					<tr>
						<td><strong>Correo</strong></td>
						<td><?php echo $email; ?></td>
					</tr>
				</table>
			</div>
			<input class="btn btn-default" type="submit" name="submit" value="Generar" />
			<input class="btn btn-default" type="button" onclick="location.href='index.php'" value="Cancelar" />
		</form>
	</div>
	<?php
}
?>
