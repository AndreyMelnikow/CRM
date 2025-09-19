<?php
require_once 'amo/amo.php';
require_once 'bitrix/bitrix.php';
require_once '../vendor/autoload.php';


// Получение данных из формы
$name = $_POST['name'];
$phone = $_POST['phone'];
$comment = $_POST['comment'];

// Отправка данных с формы в AmoCRM
sendToAmoCRM($name, $phone, $comment);

// Отправка данных с формы в Bitrix24
sendToBitrix24($name, $phone, $comment);
?>