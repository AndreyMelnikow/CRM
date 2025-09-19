<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die(); //Защита от подключения файла напрямую без подключения ядра
use Bitrix\Main\Page\Asset; //Подключение библиотеки для использования  Asset::getInstance()->addCss() 
global $USER;
?>
<!DOCTYPE html>
<html>
<head>
		<title><? $APPLICATION->ShowTitle(); ?></title> <!-- Отображение заголовка страницы -->
		<? 
$APPLICATION->ShowHead(); 
		Asset::getInstance()->addCss(SITE_TEMPLATE_PATH . "/styles.css"); 
?>
</head>
<body>
    <? $APPLICATION->ShowPanel(); ?> <!-- Отображение панели администратора -->
