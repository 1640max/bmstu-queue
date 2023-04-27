<?php
	require_once "myLib.php";
	
	// Фильтруем базар от инъекций
	if ($_GET["doctorID"] == NULL or $_GET["bookDate"] == NULL) exit("Сначала выберите доктора и дату");
	
	// id доктора приводим к инту
	$doctorID = (int)$_GET["doctorID"];
	if ($doctorID < 1) exit("Ну давай, давай, ломай меня");
	
	// Дату проверяем по регулярке
	if (preg_match("/^20\d\d-\d\d-\d\d$/", $_GET["bookDate"]))
	{
		$bookDate = $_GET["bookDate"];
	}
	else
	{
		exit("Некорректная дата");
	}
	
	
	$availableTime = getTimes($doctorID, $bookDate);
	// Обрабатываем ошибки
	if ($availableTime == NULL) exit("В этот день доктор больше не примет");
	if ($availableTime === 1) exit("В воскресенье приёма нет");
	if ($availableTime === 2) exit("Хьюстон, нас пытаются хакнуть");
	if ($availableTime === 3) exit("Доктор не работает в этот день");
	if ($availableTime === 4) exit("Выбран прошедший день");
	if ($availableTime === 5) exit("Запись больше, чем на ".$limitDate." недели вперёд запрещена!"); // Переменная из библиотеки

	echo '<select name="bookTime">';
	foreach($availableTime as $piece)
	{
		echo '<option value="'.$piece.'">'.$piece.'</option>';
	}
	echo '</select>';
?>