<?php

session_start();
include_once("sql_connector.php");
include_once( "LogException.php" );
include_once( "security.php" );

try {

    //  session otp brute force prevention, one otp at most every 20s[for testing]
    //  requires secondary protection on session brute forcing
    //  ALL BRUTE FORCING PREVENTION MECHANISMS ARE LINKED TO THE SESSION
    //  => WE HAVE A UNIQUE POINT TO PROTECT AND CONTROL
    //      => IF SESSION IS SECURE ALL IS SECURE
    //      => IF AN ATTACKER WILL DO ANY BRUTE FORCE WE CAN SEE IT LOOKING ONLY ON SESSION
    if( isset( $_SESSION[ 'otp-expire' ]) && $_SESSION[ 'otp-expire' ] != null &&  $_SESSION[ 'otp-expire' ] > time()){

        //  monitoring of OTP requests
        if( !isset( $_SESSION[ 'otp-counter' ])) {  //  initialization

            $_SESSION['otp-counter'] = 0;
            $_SESSION['otp-counter-expire'] = time() + 20;

        }

        //  count the number of OTP requests, the more are the requests the more will be the severity advertised by the log
        if( $_SESSION[ 'otp-counter-expire' ] < time() )
            $_SESSION[ 'otp-counter' ] += 1;  //  if a new request is received during the monitoring time we increase it
        else
            $_SESSION['otp-counter'] = 1; //  time window expired we reset the count and so the severity

        $_SESSION[ 'otp-counter-expire' ] = time() + 20;

        throw new LogException(
            ['DOS', 'USER-SMS-DOS'],
            'OTP-LOGIC',
            floor(min($_SESSION['otp-counter'], 50) / 5),
            "Request of OTP code out of authorized time: " . $_SESSION['otp-counter']
        );

    }

    $_SESSION[ 'otp-expire' ] = time() + 20;  //  cannot request another otp before 3m

    if( !isset( $_POST[ "type" ]) )  //  checking type field is present
        throw new LogException(
            [ 'SERVICE-ANALYSIS' ],
            'OTP-LOGIC',
            7,
            'Bad Request missing "type" field[Out of client Request]'
        );

    $username = null;
    switch( $_POST[ 'type' ]){

        case 'named':   //  named OTP will send username to retrieve user's phone[anonymous users]
            if( isset( $_POST[ 'username' ]))
                $username = sanitize_username( $_POST[ 'username' ] );  //  checking length and white listing
            else
                throw new LogException(
                    [ 'SERVICE-ANALYSIS' ],
                    'OTP-LOGIC',
                    7,
                    'Missing or invalid username during named OTP[Out of client Request]'
                );
            break;

        case "unnamed":  //  unnamed OTP will not send username, it is stored inside the session[authenticated users]
            if( isset( $_SESSION[ 'username' ]))
                $username = $_SESSION[ 'username' ];
            else
                throw new LogException(
                    [ 'SERVICE-ANALYSIS', 'EXPIRED-SESSION' ],
                    'OTP-LOGIC',
                    3,
                    'Missing session username during unnamed OTP'
                );
            break;
    }

    //  during registration the user information is not stored inside the db but into the session
    //  checking eventual registration information that match the username requested
    if( isset( $_SESSION[ 'registration-expire'],  $_SESSION[ 'registration-username' ], $_SESSION[ 'registration-phone' ]) &&
            strcmp( $_SESSION[ 'registration-username' ], $username ) == 0 ){

        if( $_SESSION['registration-expire'] < time() )
            throw new LogException(
                [ 'EXPIRED-SESSION' ],
                'OTP-LOGIC',
                0,
                'Invalid registration request. The session information are expired'
            );

        $phone = $_SESSION[ 'registration-phone' ];

    }else {

        $mySqlConnection = new sqlconnector();
        $phone = $mySqlConnection->getUserPhone( $username );

    }

    $_SESSION[ 'otp-id' ] = randBytes();  //  generation of 32 bytes random id
    $_SESSION[ 'otp-value' ] = "123456";  //  generation of 6 random number key

    echo $_SESSION[ 'otp-id' ];
    sendOTPsms( $phone, $_SESSION[ 'otp-value' ]);

}catch( LogException $e ) {

    try {

        echo randBytes();   //  outsiders cannot know if they have received a valid or invalid otp
        writeLog( $e );

    }catch( LogException $ignored ){}  //  CANNOT HAPPEN

}
