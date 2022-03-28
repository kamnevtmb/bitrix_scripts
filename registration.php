<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Main\PhoneNumber\Format;
use Bitrix\Main\PhoneNumber\Parser;
global $USER;

if (!$name = $_POST['name']) {
  echo '<span class="errortext">Укажите ФИО</span>';
  exit();
} else {
  $name = htmlentities($name);
}

if (!$phone = $_POST['phone']) {
    echo '<span class="errortext">Укажите телефон</span>';
    exit();
} else {
  $phone = preg_replace("/([^\+0-9])/i", "", $phone);
  if (!$phone) {
    echo '<span class="errortext">Укажите правильный телефон</span>';
    exit();
  }
}

if (!$email = $_POST['email']) {
  echo '<span class="errortext">Укажите E-mail</span>';
	exit();
} elseif (strpos($email, '@') === false || strpos($email, '.') === false) {
  echo '<span class="errortext">Укажите правильный E-mail</span>';
  exit();
}

if (!$bdate = $_POST['bdate']) {
  echo '<span class="errortext">Укажите дату рождения</span>';
	exit();
} elseif (!preg_match("/([0-9]{2}\.[0-9]{2}\.[0-9]{4})/i", $bdate)) {
  echo '<span class="errortext">Укажите дату рождения в формате dd.mm.YYYY</span>';
  exit();
}

$password = randString(7, array(
  "abcdefghijklnmopqrstuvwxyz",
  "ABCDEFGHIJKLNMOPQRSTUVWX­YZ",
  "0123456789",
  "!@#\$%^&*()",
));

$user = new CUser;
$arFields = Array(
  "NAME"              => $name,
  "EMAIL"             => $email,
  "LOGIN"             => $email,
  "PERSONAL_PHONE"    => $phone,
  "LID"               => "s1",
  "ACTIVE"            => "Y",
  "GROUP_ID"          => array(2,3),
  "PASSWORD"          => $password,
  "CONFIRM_PASSWORD"  => $password,
  "PERSONAL_BIRTHDAY" => $bdate,
);

$ID = $user->Add($arFields);
if (intval($ID) > 0) {
	$arEventFields = array(
		"USER_ID" => $ID,
		"MESSAGE" => $password,
		"EMAIL" => $email,
	);

	CEvent::Send("USER_INFO", "s1", $arEventFields, "N", 93);

    echo '<span class="errortext successRegistration">Спасибо за регистрацию! На указанный Email будет выслан временный пароль.</span>';
} else {
    echo $user->LAST_ERROR;
}
/*
if (intval($ID) > 0) {
	$message = "<div style='padding-top:20px;'>Логин: " . $email . "<br />Временный пароль: " . $password . "</div>";

	$arEventFields = array(
		"USER_ID" => $ID,
		"MESSAGE" => $message,
		"EMAIL" => $email,
	);

	CEvent::Send("USER_INFO", "s1", $arEventFields, "N", 93);

    echo '<span class="errortext successRegistration">Спасибо за регистрацию! На указанный Email будет выслан временный пароль.</span>';
} else {
    echo $user->LAST_ERROR;
}
*/
?>
