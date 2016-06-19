<?php

include 'connection.php';
//include_once 'inc/functions.php';

$action = basename(filter_input(INPUT_GET, 'action', $filter = FILTER_SANITIZE_STRING));


if (!login_check($conexion)) {
	$usuario = filter_input(INPUT_POST, 'usuario', $filter = FILTER_SANITIZE_STRING);
	$password = filter_input(INPUT_POST, 'password', $filter = FILTER_SANITIZE_STRING); // The hashed password.

	$cert = array(
		'Serial'=>filter_input(INPUT_SERVER, 'SSL_CLIENT_M_SERIAL', $filter = FILTER_SANITIZE_STRING),
		'Verified'=>filter_input(INPUT_SERVER, 'SSL_CLIENT_VERIFY', $filter = FILTER_SANITIZE_STRING),
		'DN'=>filter_input(INPUT_SERVER, 'SSL_CLIENT_I_DN', $filter = FILTER_SANITIZE_STRING),
		'User'=>filter_input(INPUT_SERVER, 'SSL_CLIENT_S_DN_CN', $filter = FILTER_SANITIZE_STRING),
		'EndingDate'=>filter_input(INPUT_SERVER, 'SSL_CLIENT_V_END', $filter = FILTER_SANITIZE_STRING),
		'Remaining'=>filter_input(INPUT_SERVER, 'SSL_CLIENT_V_REMAIN', $filter = FILTER_VALIDATE_INT)
	);

	if (isset($usuario, $password) && $action != "altas") {
		if (sys_user_verify($usuario, $password, $conexion) == true) {
			// Éxito
			$action = $default_action; //acción por defecto

			echo "<div class=\"logout\"> <a href=\"index.php?action=logout\"> "
			. "Desconectar " . $_SESSION['userName']
			. "</a> | <a href=\"index.php?action=requestcert\"> Generar certificado </a></div><br>";

		} else {
			// Login error: no coinciden usuario y password
			$action = "login";
			echo "<div class=" . "\"alert alert-danger alert-dismissable text-center clear\" id=\"login_fail\"" . ">
			<button type=" . "button" . " class=" . "close" . " data-dismiss=" . "alert" . ">&times;</button>
				Login incorrecto! Revisa los datos.
			</div>";
		}
	} elseif (isset($cert['Serial']) && isset($cert['EndingDate']) &&
			$cert['Verified'] == 'SUCCESS' && isset($cert['DN']) &&
			isset($cert['User']) && $cert['Remaining'] > 0) {
		if (sys_cert_verify($cert, $conexion) == true){
			$action = $default_action; //acción por defecto

			echo "<div class=\"logout\"> <a href=\"index.php?action=logout\"> "
			. "Desconectar " . $_SESSION['userName']
			. "</a> | <a href=\"index.php?action=requestcert\"> Generar certificado </a></div><br>";

		} else {
			// Login error: no coinciden el certificado
			$action = "login";
			echo "<div class=" . "\"alert alert-danger alert-dismissable text-center clear\" id=\"login_fail\"" . ">
			<button type=" . "button" . " class=" . "close" . " data-dismiss=" . "alert" . ">&times;</button>
				Login incorrecto! Revisa el certificado.
			</div>";
		}
	} elseif (! ($action == "forgotpassword" || $action == "recuperarpass" || $action == "altas")) {
		//significa que aún no has valores para usuario y password
		$action = "login";
	}
} else { // si estas autorizado
	// In case para definir la accion default segun 'login/logout'
	switch ($action) {
		case 'login': $action = $default_action;
			break;
		case 'logout':sys_session_destroy();
			$action = 'login';
	}
	if ($action != "login") {
		echo "<div class=\"logout\"> <a href=\"index.php?action=logout\"> "
		. "Desconectar " . $_SESSION['userName']
		. "</a> | <a href=\"index.php?action=requestcert\"> Generar certificado </a></div><br>";
	}
	if (empty($action)) {
		$action = $default_action; //acción por defecto $default_action = "lista"
	}
	if (!file_exists($action . '.php')) { //comprobamos que el fichero exista
		$action = $default_action; //si no existe mostramos la página por defecto
		echo "Operación no soportada: 404 [Prueba: Default is " . $default_action . " ] and action= " . $action . "!"; //Mostrar un 404
	}
}

include( $action . '.php'); //y ahora mostramos la pagina llamada



