<h1>Login</h1>

<ul class="nav nav-tabs">
	<li class="active">
		<a data-toggle="tab" href="#userpass">Usuario/Contraseña</a>
	</li>
	<li><a data-toggle="tab" href="#certs">Certificado</a></li>
	<li><a data-toggle="tab" href="#register">Registrarse</a></li>
</ul>

<div class="tab-content">
	<div id="userpass" class="tab-pane fade in active">
		<form action="index.php?action=login" method="post" name="login_form">
			<table>
				<tr>
					<td>Usuario: </td>
					<td>
						<input type="text" name="usuario" />
					</td>
				</tr>
				<tr>
					<td>Password: </td>
					<td>
						<input type="password" name="password" id="password"/>
					</td>
				</tr>
				<tr>
					<td colspan="2" style="direction: rtl">
						<input class="margXL" type="submit" value="Login" />
					</td>
				</tr>
			</table>
		</form>
		<a href="forgotpassword.php">¿Olvidaste su contraseña?</a>
	</div>
	<div id="certs" class="tab-pane fade">
		<button style="margin: 10px 0px 10px 20px" type="button"
				onclick="location.href='/certlogin.php';"
				class="btn btn-default">Certificado</button>
	</div>
	<div id="register" class="tab-pane fade">
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
	</div>
</div>
