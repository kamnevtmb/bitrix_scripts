<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Main\PhoneNumber\Format;
use Bitrix\Main\PhoneNumber\Parser;
global $USER;

//проверяем номер
if (!$phone = $_POST['phone']) {
    echo '<span class="errortext">Укажите телефон</span>';
    exit();
} else {
	$phone = preg_replace("/([^\+0-9])/i", "", $phone);
	$parsedPhone = Parser::getInstance()->parse($phone);
	if ($parsedPhone->getNumberType() != "mobile") {
		echo '<span class="errortext">Укажите правильный телефон</span>';
		exit();
	}
}

//получаем юзера по номеру
$order = array('sort' => 'asc');
$filter = array("PERSONAL_PHONE" => $parsedPhone->format(Format::E164));
$tmp = 'sort'; // параметр проигнорируется методом, но обязан быть
$rsUsers = CUser::GetList($order, $tmp, $filter);
$arUser = $rsUsers->Fetch();

//если нет такого юзера
if (!$arUser['ID']) {
	echo '<span class="errortext">Номер не найден.</span>';
	exit();
}

$rsU = CUser::GetByID($arUser['ID']);
$arUf = $rsU->Fetch();
//отладочная инфа
//echo '<span class="errortext successRegistration">Ваш код: '.$arUf['UF_SMSCODE'].'. Номер:'.$parsedPhone->format(Format::E164).'. ID: '.$arUser['ID'].'</span><br>';

//проверяем актуальность
$nowdate = time();
$smsdate = strtotime($arUf['UF_SMSCODE_DATE']);
$smscode = $arUf['UF_SMSCODE'];

if($nowdate < $smsdate) {
	//echo "Актуально";
	//проверяем код
	if ($smscode == $_POST['code']) {
		echo '<span class="errortext successAutorisation">Код введён верно</span><br>';
		$USER->Authorize($arUser['ID']);
		//LocalRedirect('https://crazybeach.ru/personal/');
		echo '<a href="/personal/">Перейти в кабинет</a>';

		//Деактивируем код
		$user = new CUser;
		$fields = Array(
			"UF_SMSCODE_DATE" => "",
			"UF_SMSCODE"      => "",
		);
		$user->Update($arUser['ID'], $fields);
		$strError .= $user->LAST_ERROR;
	} else {
		echo '<span class="errortext">Код введён неверно</span>';
	}
} else {
	//генерим код и дату истечения
	$smscode = randString(6, array(
		"1234567890",
	));
	$smsdate_end = 60*5;
	$smsdate = time()+$smsdate_end;
	$smsdate = date("d.m.Y H:i:s", $smsdate);

	//кладём данные в юзера
	$user = new CUser;
	$fields = Array(
		"UF_SMSCODE_DATE" => $smsdate,
		"UF_SMSCODE"      => $smscode,
	);
	$user->Update($arUser['ID'], $fields);
	$strError .= $user->LAST_ERROR;
	
//	echo $smscode;
//	echo "<br>";
	//отправляем SMS

	$ch = curl_init();
	curl_setopt_array($ch, array(
		CURLOPT_URL => 'https://clk.prontosms.ru/sendsms.php?user=CrazyBeach&pwd=cr1az7&sadr=CrazyBeach&dadr='.$parsedPhone->format(Format::E164).'&text='.$smscode.'%20ваш%20код',
		CURLOPT_RETURNTRANSFER => 1,
	));
	$result = curl_exec($ch);

    if (!is_numeric($result)) {
		echo "<span class='errortext'>Ошибка отправки SMS<br>".$result."</span>";
		exit();
    }

	echo "<span class='errortext sms_sent'>Ваш код отправлен</span>";
}
