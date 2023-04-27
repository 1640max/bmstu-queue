<?php
	if (isset($_POST["login"]) and isset($_POST["password"]))
	{
		require_once "myLib.php";
		if (preg_match("/^[а-яА-ЯЁёa-zA-Z .-]+$/u", $_POST["login"]))
		{
			$login = $_POST["login"];
		}
		$query = mysqli_query($link, "SELECT hash FROM accounts WHERE login = '".$login."'");
		$storedHash = mysqli_fetch_array($query)[0];
		if ($storedHash == NULL)
		{
			echo "Такого логина нет<br>";
			require_once "authPage.php";
		}
		else
		{
			if (password_verify($_POST["password"], $storedHash)) // Если пароль верный
			{
				// Генерим токен
				$token = openssl_random_pseudo_bytes(16);
				$token = bin2hex($token);
				// Обрабатываем галочку "Запомнить меня"
				if (isset($_POST["remember"]))
					$expires = time()+3600*24*30*12*10;
				else
					$expires = 0;
				// Записываем токен в куки. Последний true отвечает на httponly
				setcookie("token", $token, $expires, "", "", false, true);
				// И в нашу БД
				$query = mysqli_query($link, "INSERT INTO `Queue`.`sessions` (`login`, `token`) VALUES ('".$login."', '".$token."')");
				header("Location: profile.php");
				exit();
			}
			else
			{
				echo "Неверный пароль<br>";
				require_once "authPage.php";
			}
		}
	}
?>