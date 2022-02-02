<?php

session_start();

include_once( 'sql_connector.php' );   //  database management
include_once( 'security.php');         //  security functionalities [sanitization/checks/randomization]
include_once( 'data_manager.php' );    //  data management [extraction of data from system]

try {

    //  session captcha brute force prevention, one captcha at most every 20s[for testing]
    //  requires secondary protection on session brute forcing
    //  ALL BRUTE FORCING PREVENTION MECHANISMS ARE LINKED TO THE SESSION
    //  => WE HAVE A UNIQUE POINT TO PROTECT AND CONTROL
    //      => IF SESSION IS SECURE ALL IS SECURE
    //      => IF AN ATTACKER WILL DO ANY BRUTE FORCE WE CAN SEE IT LOOKING ONLY ON SESSION
    if( isset( $_SESSION[ 'captcha-expire' ]) && $_SESSION[ 'captcha-expire' ] != null &&  $_SESSION[ 'captcha-expire' ] > time()){

        //  monitoring of captcha requests
        if( !isset( $_SESSION[ 'captcha-counter' ])) {  //  initialization

            $_SESSION['captcha-counter'] = 0;
            $_SESSION['captcha-counter-expire'] = time() + 10;

        }

        //  count the number of captcha requests, the more are the requests the more will be the severity advertised by the log
        if( $_SESSION[ 'captcha-counter-expire' ] < time() )
            $_SESSION[ 'captcha-counter' ] += 1;  //  if a new request is received during the monitoring time we increase it
        else
            $_SESSION['captcha-counter'] = 1; //  time window expired we reset the count and so the severity

        $_SESSION[ 'captcha-counter-expire' ] = time() + 10;

        throw new LogException(
            ['DOS', 'USER-CAPTCHA-SCRAPE'],
            'CAPTCHA-LOGIC',
            floor(min($_SESSION['captcha-counter'], 50) / 5),
            "Request of captcha code out of authorized time: " . $_SESSION['captcha-counter']
        );

    }

    $_SESSION[ 'captcha-expire' ] = time() + 10;  //  cannot request another captcha before 2m
    $mySqlConnection = new sqlconnector();
    $max = $mySqlConnection->getMaxCaptcha() - 1;  //  extraction number of captchas
    $captcha_info = $mySqlConnection->getCaptcha( random_int( 0, $max ));  // extract one captcha at random
    $captcha_value = randBytes( 16 );  //  generate a random key of 16bytes

    $_SESSION[ 'captcha-id' ] = randBytes( 20 );

    //  generation of auth key by applying a mask[user will generate the same mask resolving the captcha]
    $_SESSION['captcha-value'] = generateAuthCaptcha($captcha_value, $captcha_info['mask']);
    echo json_encode([
        "captcha-id" => $_SESSION['captcha-id'],
        "captcha-value" => $captcha_value,
        "captcha-clue" => $captcha_info['clue'],
        "captcha-content" => exposeCaptcha($captcha_info['src'])
    ]);

}catch( LogException $e ) {

    http_response_code( 400 );
    writeLog( $e );
    exit( 'Something goes wrong during the execution of the request' );

} catch (Exception $e) {

    http_response_code( 400 );
    exit( 'Something goes wrong during the execution of the request' );

}