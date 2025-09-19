<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/header.php');
$APPLICATION->SetTitle("Сайт с формой");
?>
<div class="container">
    <form method="POST" action="php\handler.php">
        <p><div>
            Имя:<br>
            <input type="text" name="name" placeholder="Ваше имя" required>
        </div></p
        <p><div>
            Телефон:<br>
            <input type="tel" name="phone" placeholder="+7 (123) 456-78-90" pattern="[\+]\d{1}\s[\(]\d{3}[\)]\s\d{3}[\-]\d{2}[\-]\d{2}" minlength="18" maxlength="18" required>
        </div></p
        <p><div>
            Комментарий:<br>
            <textarea name="comment" placeholder="Ваш комментарий"></textarea>
        </div></p
        <p><input type="submit" class="sendbtn" /></p>
    </form>
</div>

<?
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/footer.php');
?>