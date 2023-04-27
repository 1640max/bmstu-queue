<html>
	<head>
		<meta charset="UTF-8">
		<title>Электронная очередь в бауманскую поликлинику</title>
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
	</head>
	<body bgcolor=#eeeeee>
<?php
	require_once "myLib.php";

	$login = verifySession();
	// Если не авторизован, то отправляем на страницу авторизации
	if ($login == NULL)
	{
		header("Location: authPage.php");
		exit();
	}
	
	// Знаем логин, выясним: это доктор? Это админ?
	$query = mysqli_query($link, "SELECT doctorID, admin FROM accounts WHERE login = '".$login."'");
	$array = mysqli_fetch_assoc($query);
	
	if ($array["doctorID"] == NULL)
	{
		// Если NULL, то это не доктор. Приветствуем по логину.
		$isDoctor = false;
		$name = $login;
	}
	else
	{
		// Иначе, это доктор, приветствуем по имени
		$isDoctor = true;
		$docID = $array["doctorID"];
		$name = getDocName($docID);
	}
	
	if ($array["admin"] == 0)
		$isAdmin = false;
	else
		$isAdmin = true;
	
	echo "Вы вошли как <b>".$name."</b>. <a href='logout.php'>Выйти из аккаунта</a><br><br>";
	
	// Если зашёл доктор, то выводим его пациентов
	if ($isDoctor)
	{
		echo "Ближайшие записи:<br><br>";
		
		// Получаем информацию о записанных пациентах
		$query = mysqli_query($link, "SELECT bookDate, bookTime, patientName, patientGroup, patientTel, notes
		FROM bookings WHERE doctorID = ".$docID." ORDER BY bookDate, bookTime");
		
		while ($array = mysqli_fetch_assoc($query))
		{
			echo "<b>".$array["bookDate"]." ".$array["bookTime"]."</b><br>
				 <b>Пациент: </b>".$array["patientName"]."<br>
				 <b>Группа: </b>".$array["patientGroup"]."<br>";
			if ($array["patientTel"] != "")
				echo "<b>Телефон: </b>".$array["patientTel"]."<br>";
			if ($array["notes"] != "")
				echo "<b>Примечание: </b>".$array["notes"]."<br>";
			echo "<br>";
		}
	}
	
	// Если зашёл админ, то предоставляем ему панель управления
	if ($isAdmin)
	{
		ECHO "ПАНЕЛЬ";
	}
?>
		<a href="index.php">На главную</a>
	</body>
</html>