<style>
#calendar2 {
	width: 200px;
	line-height: 1.2em;
	font-size: 15px;
	text-align: center;
}
#calendar2 thead tr:last-child {
	font-size: small;
	color: rgb(85, 85, 85);
}
#calendar2 thead tr:nth-child(1) td:nth-child(2) {
	color: rgb(50, 50, 50);
}
#calendar2 thead tr:nth-child(1) td:nth-child(1):hover, #calendar2 thead tr:nth-child(1) td:nth-child(3):hover, #calendar2 tbody:hover {
	cursor: pointer;
}
#calendar2 tbody td {
	color: rgb(0, 0, 0);
}
#calendar2 tbody td:hover {
	font-weight: bold;
}
#calendar2 tbody td:nth-child(n+6), #calendar2 .holiday {
	color: rgb(231, 50, 92);
}
#calendar2 tbody td.selected {
	background: rgb(220, 0, 0);
	color: #fff;
}
</style>

<table id="calendar2">
  <thead>
    <tr><td>‹<td colspan="5"><td>›
    <tr><td>Пн<td>Вт<td>Ср<td>Чт<td>Пт<td>Сб<td>Вс
  <tbody>
</table>

<script>
// Функция корректирует скрытый параметр с датой (index.php), на которую ткнули
function setDate(year, month, day)
{
	// Делаем подсветку выбранному дню. Но сначала удаляем старую подсветку.
	var elem = document.getElementsByClassName('selected')[0];
	// Если она, конечно, есть
	if (elem) elem.removeAttribute('class');
	
	// Подсвечиваем
	document.getElementById('day' + day).setAttribute('class', 'selected');
	
	// Добавляем ведущие нули
	month = String(month);
	if (month.length == 1)
		month = 0 + month;
	
	day = String(day);
	if (day.length == 1)
		day = 0 + day;
	
	var strDate = '' + year + '-' + month + '-' + day;
	//alert(strDate);
	document.getElementById('bookDate').value = strDate;
	refreshTimeBlock(); // описана в index.php
}

function Calendar2(id, year, month)
{
	var Dlast = new Date(year,month + 1,0).getDate(),
		D = new Date(year,month,Dlast),
		DNlast = new Date(D.getFullYear(),D.getMonth(),Dlast).getDay(),
		DNfirst = new Date(D.getFullYear(),D.getMonth(),1).getDay(),
		calendar = '<tr>',
		monthInt = month + 1,
		month=["Январь", "Февраль", "Март", "Апрель", "Май", "Июнь", "Июль", "Август", "Сентябрь", "Октябрь", "Ноябрь", "Декабрь"];

	if (DNfirst != 0)
	{
		for(var  i = 1; i < DNfirst; i++)
			calendar += '<td>';
	}
	else
	{
		for(var  i = 0; i < 6; i++)
			calendar += '<td>';
	}
	
	for(var  i = 1; i <= Dlast; i++)
	{
		calendar += '<td id="day' + i + '" onclick="setDate(' + year + ',' + monthInt + ',' + i + ')">' + i;
		
		if (new Date(D.getFullYear(),D.getMonth(),i).getDay() == 0)
		{
			calendar += '<tr>';
		}
	}

	for(var  i = DNlast; i < 7; i++)
		calendar += '<td>&nbsp;';
	
	document.querySelector('#'+id+' tbody').innerHTML = calendar;
	document.querySelector('#'+id+' thead td:nth-child(2)').innerHTML = month[D.getMonth()] +' '+ D.getFullYear();
	document.querySelector('#'+id+' thead td:nth-child(2)').dataset.month = D.getMonth();
	document.querySelector('#'+id+' thead td:nth-child(2)').dataset.year = D.getFullYear();

	if (document.querySelectorAll('#'+id+' tbody tr').length < 6)
	{
		// чтобы при перелистывании месяцев не "подпрыгивала" вся страница, добавляется ряд пустых клеток. Итог: всегда 6 строк для цифр
		document.querySelector('#'+id+' tbody').innerHTML += '<tr><td>&nbsp;<td>&nbsp;<td>&nbsp;<td>&nbsp;<td>&nbsp;<td>&nbsp;<td>&nbsp;';
	}
}

Calendar2("calendar2", new Date().getFullYear(), new Date().getMonth());

// переключатель минус месяц
document.querySelector('#calendar2 thead tr:nth-child(1) td:nth-child(1)').onclick = function()
{
  Calendar2("calendar2", document.querySelector('#calendar2 thead td:nth-child(2)').dataset.year, parseFloat(document.querySelector('#calendar2 thead td:nth-child(2)').dataset.month)-1);
}

// переключатель плюс месяц
document.querySelector('#calendar2 thead tr:nth-child(1) td:nth-child(3)').onclick = function()
{
  Calendar2("calendar2", document.querySelector('#calendar2 thead td:nth-child(2)').dataset.year, parseFloat(document.querySelector('#calendar2 thead td:nth-child(2)').dataset.month)+1);
}
</script>