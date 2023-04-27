<html>
	<head>
		<meta charset="UTF-8">
		<title>Электронная очередь в бауманскую поликлинику</title>
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
	</head>
	<body bgcolor=#eeeeee>
		<a href="authPage.php">Вход в личный кабинет</a><br><br>
<?php
	require_once "myLib.php";

	// Пункты для выпадающего списка врачей
	function getOptions()
	{
		global $link;
		$query = mysqli_query($link, "SELECT DoctorID, secondName, firstName FROM doctors");
		while ($array = mysqli_fetch_assoc($query))
		{
			$spaceIndex = strpos($array["firstName"], ' ');
			$initials = substr($array["firstName"],0, 2).'. '.substr($array["firstName"], $spaceIndex + 1, 2).'.';
			$name = $array["secondName"].' '.$initials;
			echo '<option value='.$array["DoctorID"].'>'.$name.'</option>';
		}
	}

	$query = mysqli_query($link, "SELECT * FROM doctors");

	// Рисуем таблицу с расписанием
?>
	<table align="center" border=1px cellpadding=5px>
		<tr><td>Имя врача</td><td>Специальность</td><td>Кабинет</td>
		<td>Понедельник</td><td>Вторник</td><td>Среда</td><td>Четверг</td><td>Пятница</td><td>Суббота</td><td>Примечания</td></tr>
<?php
	while ($array = mysqli_fetch_assoc($query))
	{
		echo '<tr>';
		echo '<td>'.$array["secondName"].'<br>'.$array["firstName"].'</td>';
		echo '<td>'.$array["position"].'</td>';
		echo '<td>'.$array["room"].'</td>';
		
		for ($i = 1; $i <= 6; $i++)
		{
			if ($array['day'.$i.'Start'] != NULL)
			{
				echo '<td>'.$array['day'.$i.'Start'].'<br>';
				echo 		$array['day'.$i.'End'].'</td>';
			}
			else
				echo '<td>Выходной</td>';
		}

		echo '<td>'.$array["notes"].'</td>';
		echo '</tr>';
	}
	echo '</table><br>';

	// Сообщение из другого пхпшника (например из book.php сообщение об успешном приёме)
	if (isset($message))
	{
		echo $message;
	}
	
	$today = date("Y-m-d");
	$now = date("H:i");
?>
	<script>
		$(document).ready(function(){
			refreshTimeBlock();
			$("#doctorSelect").on("change", refreshTimeBlock);
			//$("#bookDate").on("change", refreshTimeBlock);
		});

		function refreshTimeBlock() // Обновить время
        {
			//$("#timeBlock").html("Загрузка...");
            $.ajax(
            {  
                type: "GET",
                url: "refreshTimeBlock.php",
                data: 'doctorID=' + $("#doctorSelect").val() + '&bookDate=' + $("#bookDate").val(),
                success: function(html){
                    $("#timeBlock").html(html);
                }  
            });
            return false;
        };
	</script>
	
	<br>
	<form method="GET" id="bookForm" action="book.php">
		<table>
		<tr><td align=right style="padding-right: 30px">
		
			<label>Ваше имя: <input type="text" name="patientName" placeholder="Аристарх Палочкин" maxlength=255 required pattern="^[а-яА-ЯЁёa-zA-Z .-]+$" value="<?php echo $_COOKIE["patientName"] ?>"></label><br><br>
			
			<label>Ваша группа: <input type="text" name="patientGroup" placeholder="ИУ8-11" maxlength=15 required pattern="^[а-яА-ЯЁёa-zA-Z0-9-]{5,15}$" value="<?php echo $_COOKIE["patientGroup"] ?>"></label><br><br>
			
			<label>Мобильный телефон: <input type="tel" name="patientTel" placeholder="(необязательно)" maxlength=31 pattern="^[0-9-+() ]{0,31}$" value="<?php echo $_COOKIE["patientTel"] ?>"></label><br><br>
			
			<label>Примечания: <textarea name="notes" rows=6 cols=28 placeholder="(необязательно) Здесь можно указать любую информацию, для доктора, например, цель визита."><?php echo $_COOKIE["notes"] ?></textarea></label></br><br>
			
			<input type="submit">
			
			</td><td>
			
			<label>Врач: <select name="doctorID" id="doctorSelect">
			<?php getOptions(); ?>
			</select></label><br><br>
			
			Дата:<br>
			<?php require_once "calendar.php"; ?>
			<input type="hidden" name="bookDate" id="bookDate" value="<?php echo $today ?>">
			<script>setDate(<?php echo date("Y, m, d"); ?>)</script> <!-- При загрузке страницы, дата сама поставится на сегодняшнюю -->
			<!--<label>Дата: <input type="date" name="bookDate" id="bookDate" min="<?php echo $today ?>" value="<?php echo $today ?>"></label><br>-->
			
			<label>Время: <div id="timeBlock">Включите JavaScript!</div></label><br>
			
		</td></tr>
		</table>
	</form>
	</body>
</html>