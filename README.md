# Электронная очередь в поликлинику при МГТУ им. Баумана

Курсовой проект на PHP, написанный в 2018 году. 

## Использованные технологии
* PHP
* MySQL
* HTML/CSS/JS
* jQuery

## Фичи
* Авторизация. Реализована вручную без сторонних библиотек для практики.
* Два типа аккаунтов: доктор и администратор.
* Настраиваемая продолжительность визита.
* Личный кабинет для доктора, где он может управлять своими записями.
* Фильтрация входных данных, защита от SQL-инъекций, XSS- и CSRF-атак и пр.
* Просканировано статическим анализатором кода.

## Недостатки

В проекте не проработан пользовательский интерфейс и фронт, так как задача стояла в разработке бэкэнда. Также я был зелёным студентом и ещё не знал некоторых вещей, например
* Самодокументируемость кода
* Стандарты оформления кода
* HTML, CSS и JS нужно разносить по отдельным файлам
