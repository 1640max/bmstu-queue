<?php
	require_once "myLib.php";
	
	if (verifySession() != NULL)
	{
		header("Location: profile.php");
		exit();
	}
?>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Электронная очередь в бауманскую поликлинику</title>
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
	</head>
	<body bgcolor=#eeeeee>
		Вход в личный кабинет<br>
		<form method="POST" action="login.php">
			<label>Логин: <input name="login" type="text" required pattern="[а-яА-ЯЁёa-zA-Z .-]+"></label><br>
			<label>Пароль: <input name="password" type="password" required></label><br>
			<label><input type="checkbox" name="remember" value="yes" checked>Запомнить меня</label><br>
			<button type="submit">Войти</button>
		</form>
		<a href="index.php">На главную</a>
	</body>
</html>