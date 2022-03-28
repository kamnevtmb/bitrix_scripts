<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Main\PhoneNumber\Format;
use Bitrix\Main\PhoneNumber\Parser;
global $USER;
$phone = '';
$email = '';
if (strpos($_GET['phone'], '@') == false) {
    $phone = Parser::getInstance()->parse($_GET['phone']);
    $db = CUser::GetList($by, $order, array('PERSONAL_PHONE' => $phone->format(Format::E164)));
} else {
    $email = $_GET['phone'];
    $db = CUser::GetList($by, $order, array('EMAIL' => $email));
}
$arUser = $db->Fetch();
if ($arUser) {
    $USER->SendPassword($arUser['LOGIN'], $arUser['EMAIL']);
    echo '<span class="notetext">Ваша заявка принята. Код для восстановления пароля будет выслан вам на e-mail и телефон.</span>';

}
else
    echo '<span class="errortext">Пользователь не найден</span>';