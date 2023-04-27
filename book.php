<?php
	require_once "myLib.php";
	
	// Если что-то обязательное незаполнено, то отправляем назад
	if (!isset($_GET["bookDate"])) exit("Не заполнено поле ДАТА");		
	if (!isset($_GET["bookTime"])) exit("Не заполнено поле ВРЕМЯ");
	if (!isset($_GET["patientName"])) exit("Не заполнено поле ИМЯ");
	if (!isset($_GET["patientGroup"])) exit("Не заполнено поле ГРУППА");
	
	// Фильтруем базар
	$doctorID = (int)$_GET["doctorID"];
	if ($doctorID < 1) exit("Ну давай, давай, ломай меня");
	
	// Дата истечения куков
	$expires = time()+3600*24*30*12*10;
	
	// Дату проверяем по регулярке
	if (preg_match("/^20\d\d-\d\d-\d\d$/", $_GET["bookDate"]))
	{
		$bookDate = $_GET["bookDate"];
		setcookie("bookDate", $bookDate, $expires); // Запоминаем введённые данные, чтобы потом автоматически отобразить их в форме записи.
	}
	else
	{
		exit("Некорректная дата");
	}
	
	// Время тоже по регулярке
	if (preg_match("/^\d\d:\d\d$/", $_GET["bookTime"]))
	{
		$bookTime = $_GET["bookTime"];
		setcookie("bookTime", $bookTime, $expires);
	}
	else
	{
		exit("Некорректное время");
	}
	
	// Имя тоже
	if (preg_match("/^([а-яА-ЯЁёa-zA-Z .-]+)$/u", $_GET["patientName"]))
	{
		$patientName = $_GET["patientName"];
		setcookie("patientName", $patientName, $expires);
	}
	else
	{
		exit("Некорректное имя");
	}
	
	// Группа
	if (preg_match("/^([а-яА-ЯЁёa-zA-Z0-9-]{5,16})$/u", $_GET["patientGroup"]))
	{
		$patientGroup = $_GET["patientGroup"];
		setcookie("patientGroup", $patientGroup, $expires);
	}
	else
	{
		exit("Некорректная группа");
	}
	
	// Телефон
	if (preg_match("/^([\d-+() ]{0,32})$/u", $_GET["patientTel"]))
	{
		$patientTel = $_GET["patientTel"];
		setcookie("patientTel", $patientTel, $expires);
	}
	else
	{
		exit("Некорректный мобильник");
	}
	
	// Экранируем примечания
	$notes = htmlspecialchars($_GET["notes"]);
	$notes = str_replace("'", "&quot;", $notes); // ' надо экранировать вручную
	setcookie("notes", $notes, $expires);
	
	// echo "<b>ID доктора: </b>".$doctorID.'<br>';
	// echo "<b>Дата: </b>".$bookDate.'<br>';
	// echo "<b>Время: </b>".$bookTime.'<br>';
	// echo "<b>Имя: </b>".$patientName.'<br>';
	// echo "<b>Группа: </b>".$patientGroup.'<br>';
	// echo "<b>Телефон: </b>".$patientTel.'<br>';
	// echo "<b>Примечания: </b>".$notes.'<br><br>';
	
	// Получаем доступное для записи время
	$availableTime = getTimes($doctorID, $bookDate);
	// Обрабатываем ошибки, полученные от getTimes
	if ($availableTime == NULL) exit("В этот день доктор больше не примет");
	if ($availableTime === 1) exit("В воскресенье приёма нет");
	if ($availableTime === 2) exit("Хьюстон, нас пытаются хакнуть");
	if ($availableTime === 3) exit("Доктор не работает в этот день");
	if ($availableTime === 4) exit("Выбран прошедший день");
	if ($availableTime === 5) exit("Запись больше, чем на ".$limitDate." недели вперёд запрещена!"); // Переменная из библиотеки
	if (!in_array($bookTime, $availableTime)) exit("Это время недоступно!");
	
	// Данные прошли все испытания, теперь можно их записать в БД
	$INSERT = "INSERT INTO `Queue`.`bookings` (`doctorID`, `bookDate`, `bookTime`, `patientName`, `patientGroup`, `patientTel`, `notes`)
	VALUES ('".$doctorID."', '".$bookDate."', '".$bookTime."', '".$patientName."', '".$patientGroup."', '".$patientTel."', '".$notes."');";
	
	// Формируем сообщение, которое отправится в index.php
	// Сначала получим имя врача, к которому записывались
	$query = mysqli_query($link, "SELECT secondName, firstName, room FROM doctors WHERE doctorID = ".$doctorID);
	$array = mysqli_fetch_assoc($query);
	$name = $array["secondName"]." ".$array["firstName"];
	
	// Переформатируем дату
	$date = new DateTime($bookDate);
	$date = $date -> format("d.m.Y");
	
	// Заносим имя в сообщение
	$message = "Вы успешно записаны на приём!<br>
	<b>Врач:</b> ".$name."<br><b>Когда:</b> ".$date." ".$bookTime."<br>
	<b>Где: </b>Кабинет ".$array["room"]."<br>";
	$query = mysqli_query($link, $INSERT);
	require_once "index.php";
?>