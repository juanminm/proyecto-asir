<h1>Alta usuario</h1>
<?php
$password = filter_input(INPUT_POST, 'password', $filter = FILTER_SANITIZE_STRING); // The hashed password.
$repassword = filter_input(INPUT_POST, 'repassword', $filter = FILTER_SANITIZE_STRING); // The hashed password.

/* Si se llama desde el form, contendra datos de 'input' y al no ser
 * $_POST falso se ejecutar primero el PHP, si no hubiese datos y fuese
 * falso se ignoraria el PHP y mostraria el formulario.
 */
if($_POST){
/*	// include conexion a la BD -> de aqui obtenemos $conexion
	include 'connection.php';*/
	if (!($password == $repassword)){
		echo "<h4>Las contraseñas no coinciden</h4><br>";
	} else {
		// insert query
		$query = "INSERT INTO `users` (userNick, userMail, userPass, "
				. "userSignedDate) VALUES (?, ?, ?, ?)";

		// prepare query for execution -> Aquí se comprueba la sintaxis
		//  de la consulta y se reservan los recursos necesarios
		//  para ejecutarla.
		if (! $stmt = $conexion->prepare($query)){
			die('Imposible preparar el registro.'.$conexion->error);
		}

		// asociar los parámetros
		$date = date("Y-m-d H:i:s");
		$password = password_hash($password, PASSWORD_DEFAULT);
		$stmt->bind_param('ssss', $_POST['usuario'], $_POST['email'],
				$password, $date);

		// ejecutar la query
		if($stmt->execute()){
			echo "<div>El usuario ha sido creado.</div>";
		} else {
			die('Imposible guardar el registro:'.$conexion->error);
		}
	}
}
?>
<form action='./index.php?action=register' method='post'>
	<table border='0'>
		<tr>
			<td>Usuario</td>
			<td><input type='text' name='usuario' /></td>
		</tr>
		<tr>
			<td>Correo electronico</td>
			<td><input type='text' name='email' /></td>
		</tr>
		<tr>
			<td>Contraseña</td>
			<td><input type='password' name='password' /></td>
		</tr>
			<td>Repite contraseña</td>
			<td><input type='password' name='repassword' /></td>
		</tr>
		<tr>
			<td colspan="2" style="direction: rtl">
				<input type="submit" name="save" value="Crear" />
			</td>
		</tr>
	</table>
</form>
