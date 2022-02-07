<?php

session_start();

include_once( 'LogException.php' );   //  Exception raised in case of problems
include_once( 'sql_connector.php' );  //  Database Manager
include_once( 'security.php' );       //
include_once( "data_manager.php" );

try {

    if (!isset($_POST['username']))
        throw new LogException(
            ['SERVICE-ANALYSIS'],
            'OTP-LOGIC',
            1,
            'Missing or invalid username during named OTP[Out of client Request]'
        );

    $username = sanitize_username($_POST['username']);  //  checking length and white listing

    //  during registration the user information is not stored inside the db but into the session
    //  checking eventual registration information that match the username requested
    if( isset($_SESSION['registration-expire'], $_SESSION['registration-username'], $_SESSION['registration-phone']) &&
        strcmp($_SESSION['registration-username'], $username) == 0) {

        if ($_SESSION['registration-expire'] < time())
            throw new LogException(
                ['EXPIRED-SESSION'],
                'OTP-LOGIC',
                0,
                'Invalid registration request. The session information are expired'
            );

        $phone = $_SESSION['registration-phone'];

    } else {

        $mySqlConnection = new sqlconnector();
        $phone = $mySqlConnection->getUserPhone( $username );

    }

    $_SESSION['otp-id'] = randBytes();  //  generation of 32 bytes random id
    $_SESSION['otp-value'] = randInt();  //  generation of 6 random number key
    $_SESSION['otp-expire'] = time() + 300;

    ignore_user_abort( true );
    set_time_limit( 0 );

    ob_start();

    //  we need to send the response immediately without waiting the sending of the sms
    //  otherwise attackers evaluating the time of the response can determine the request status
    echo  $_SESSION['otp-id'];

    header( $_SERVER[ "SERVER_PROTOCOL" ] . " 202 Accepted" );
    header( 'Status: 202 Accepted');
    header( 'Connection: close');
    header( 'Content-length: ' . (ob_get_length() + 1) );
    ob_end_flush();
    @ob_flush();
    flush();

    //  time consuming function, need to be executed without make the user aware of it
    sendOTPsms( $phone , $_SESSION[ 'otp-value' ]);


}catch( LogException $e ) {

    try {

        echo randBytes();   //  outsiders cannot know if they have received a valid or invalid otp
        writeLog( $e );

    }catch( LogException $ignored ){}  //  CANNOT HAPPEN

}catch( Exception $e ){

    echo randBytes();   //  outsiders cannot know if they have received a valid or invalid otp
    writeLog( new LogException(
        ['test'],
        'OTP-LOGIC',
        0,
        $e->getMessage()
    ));
}

