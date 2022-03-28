<? require( $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php" );
global $USER;
$mail = addslashes( htmlspecialchars( trim( $_POST['mail'] ) ) );

$users = CUser::GetList( $by = 'ID', $order = 'ASC', [ '=EMAIL' => $mail ] );

while( $ob_user = $users->GetNext() ) {
    $u_arr[$ob_user['ID']] = $ob_user;
}

if ( isset( $u_arr ) && !empty( $u_arr ) ) {
    echo json_encode( [
        'status'    => 'exist',
        'users'     => $u_arr
    ] );
    
    die();
}
echo json_encode( [ 'status' => 'not_exists' ] ); ?>