<?php

$basicURL = "http://queue/";
$bookPeriod = 15; // Длительность приёма одного пациента в минутах
$limitDate = 2; // За сколько недель вперёд можно записаться на приём.

/* ---------- ГЕНЕРАЦИЯ ТОКЕНА ДЛЯ ПОДКЛЮЧЕНИЯ К БД. ДАЁТ ПЕРЕМЕННУЮ $link ----------*/

	error_reporting(0);
	$db_host = 'localhost';
	$db_user = 'root';
	$db_password = '';
	$db_name = 'Queue';
	
	$link = mysqli_connect($db_host, $db_user, $db_password, $db_name);
	if (!$link) {
    	die('<p style="color:red">'.mysqli_connect_errno().' - '.mysqli_connect_error().'</p>');
	}
	mysqli_query($link, "SET NAMES utf8");
	
/* --------------------------------------------------------------------------------- */



/* ---------- ФУНКЦИЯ, БЕРУЩАЯ НОМЕР ДОКТОРА С ДАТОЙ И ВЫДАЮЩАЯ ДОСТУПНОЕ ДЛЯ ЗАПИСИ ВРЕМЯ ----------*/
// Если на этот день записаться нельзя (дата в прошлом или в далёком будущем, например), то функция вернёт код ошибки.
// Если функция для конкретного врача и даты возвращает какое-то время, значит на это время можно записаться.
// Код ошибки 1 - в воскресенье поликлиника не работает
// Код ошибки 2 - запрос вернул пустой ответ. Скорее всего попытка атаки.
// Код ошибки 3 - доктор не работает в этот день. Если начало рабочего дня равно NULL, то нет приёма.
// Код ошибки 4 - попытка записаться на прошедший день
// Код ошибки 5 - попытка записаться больше, чем на две недели вперёд

	function getTimes($doctorID, $bookDate)
	{
		global $link, $bookPeriod, $limitDate; // Линк на БД и длительность приёма одного пациента в минутах

		// Проверяем допустимость даты
		$bookDateOBJ = new DateTime($bookDate); // Переводим строку в объект DateTime
		$now = new DateTime("now");
		date_time_set($now, 0, 0); // Устанавливаем время в ноль, дату оставляем
		if ($now > $bookDateOBJ)
		{
			return 4; // Код ошибки 4 - попытка записаться на прошедший день
		}
		
		$now -> modify("+".$limitDate." weeks");
		if ($bookDateOBJ > $now)
		{
			return 5; // Код ошибки 5 - попытка записаться больше, чем на $limitDate недель вперёд (см. самый верх)
		}
		
		// Получаем номер дня недели. Кстати, по воскресеньям никто не работает.
		$dayOfWeek = $bookDateOBJ -> format("w");
		if ($dayOfWeek == 0)
		{
			return 1; // Код ошибки 1 - в воскресенье поликлиника не работает
		}
		
		// Начинаем генерировать список доступных времён для записи
		
		// Снова текущее время
		$now = new DateTime("now");
		
		// Получаем часы приёма выбранного врача
		$query = mysqli_query($link, "SELECT day".$dayOfWeek."Start, day".$dayOfWeek."End FROM doctors WHERE doctorID = ".$doctorID);
		// Здесь $array[0] - начало времени приёма, а $array[1] - конец. Но сначала проверим, всё ли ок
		$array = mysqli_fetch_array($query);
		if ($query == false or $array == NULL) // Если вернулась ошибка или пустота
		{
			return 2; // Код ошибки 2 - запрос вернул пустой ответ. Скорее всего юзер хулиганит.
		}
		if ($array[0] == NULL) // Если начало рабочего дня равно NULL, то в этот день нет приёма
		{
			return 3; // Код ошибки 3 - доктор не работает в этот день
		}
		
		$start = new DateTime($array[0]); // Конвертируем строковое время в объект DateTime
		$end = new DateTime($array[1]);
		$end -> modify("-".$bookPeriod." minutes"); // Приём заканчивается за $bookPeriod минут до конца, чтобы врач успел принять последнего
		$current = new DateTime($array[0]); // Итератор для предстоящего цикла
		
		// Получаем занятые времена
		$query = mysqli_query($link, "SELECT bookTime FROM bookings WHERE doctorID = ".$doctorID." AND bookDate = '".$bookDate."' ORDER BY bookTime");
		$availableTime = array();
		
		/* Проходимся по всем возможным временам приёма и сравниваем каждое с $nearestBookedTime. Если какое-то время совпало,
		значит из $query нужно извлечь время следующего занятого приёма */
		$nearestBookedTime = mysqli_fetch_array($query)[0]; // Строковое представление времени 
		do
		{
			// Если рассматриваемое время занято, то нам нужно вытащить время следующего приёма
			if (($current -> format("H:i")) == $nearestBookedTime)
			{
				$nearestBookedTime = mysqli_fetch_array($query)[0];
			}
			else // Если время свободно, то записываем его строковое представление в массив доступных времён
			{
				// Но только если это не сегодняшнее прошедшее время
				if ( !( ($now -> format("Y-m-d") === $bookDate) and ($now >= $current) ) )
					$availableTime[] = $current -> format("H:i");
			}
			$current -> modify("+".$bookPeriod." minutes");
			
		} while($current <= $end);
		
		return $availableTime;
	}

/* --------------------------------------------------------------------------------- */


/* ----------- ФУНКЦИЯ СМОТРИТ КУКИ И ВОЗВРАЩАЕТ ЛОГИН, ЛИБО NULL ---------- */

	function verifySession()
	{
		global $link;
		// Есть ли кука, и нормальная ли она?
		if (isset($_COOKIE["token"]) and preg_match("/^[\da-f]{32}$/", $_COOKIE["token"]))
		{
			$query = mysqli_query($link, "SELECT login FROM sessions WHERE token = '".$_COOKIE["token"]."'");
			return mysqli_fetch_array($query)[0]; // Должно вернуть NULL в случае ошибки запроса
		}
		else
			return NULL;
	}

/* --------------------------------------------------------------------------------- */


/* ------------------- ФУНКЦИЯ ГОВОРИТ ИМЯ ДОКТОРА, ПРИНИМАЯ ЕГО ID ---------------- */

	function getDocName($doctorID)
	{
		global $link;
		$doctorID = (int)$doctorID;
		$query = mysqli_query($link, "SELECT secondName, firstName FROM doctors WHERE doctorID = ".$doctorID);
		$array = mysqli_fetch_array($query);
		return $array[0]." ".$array[1];
	}

/* --------------------------------------------------------------------------------- */

?>