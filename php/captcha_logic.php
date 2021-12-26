<?php

session_start();

include('sql_connector.php');

try {

    $mySqlConnection = new sqlconnector();
    $max = $mySqlConnection->getMaxCaptcha();
    $captcha_info = $mySqlConnection->getCaptcha( random_int( 0, $max ));
    $captcha_value = randBytes( 16 );

    $_SESSION['captcha-id'] = randBytes( 20 );
    $_SESSION['captcha-value'] = generateAuthCaptcha( $captcha_value, $captcha_info['mask'] );
    echo json_encode([
        "captcha-id" => $_SESSION['captcha-id'],
        "captcha-value" => $captcha_value,
        "captcha-clue" => $captcha_info['clue'],
        "captcha-content" => exposeCaptcha( $captcha_info['src'] )
    ]);

} catch (Exception $e) {

    echo $e->getMessage();
    exit( 'error' );
}