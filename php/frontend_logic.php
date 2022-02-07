<?php

session_start();
include_once( "sql_connector.php" );
include_once( "security.php" );

try{

    if( isset( $_POST['type'] )){  //  every message must have a 'type' field
        switch( $_POST[ 'type' ]) {

            case 'login':
                //  checking all the message fields are present
                if(isset( $_POST[ 'username' ], $_POST[ 'password' ], $_POST[ 'otp-id' ], $_POST[ 'otp-value' ])) {

                    //  sanitization of tainted data
                    sanitize_login(
                        $_POST[ 'username' ],
                        $_POST[ 'password' ],
                           $_POST[ 'otp-id' ],
                        $_POST[ 'otp-value' ]
                    );

                    //  verification of otp session information presence and eventual check
                    check_otp( $_POST[ 'otp-id' ], $_POST[ 'otp-value' ]);

                    //  perform credential check
                    $mySqlConnection = new sqlconnector();
                    $result = $mySqlConnection->login( $_POST['username'], $_POST['password'] );

                    //  caching user data into the session for autologin/internal service functions
                    $_SESSION[ 'user-id' ] = $result[ 'uID' ];
                    $_SESSION[ 'user-auth' ] = randBytes();
                    $_SESSION[ 'user-phone' ] = $result[ 'phone' ];

                    //  setting additional cookie on client-side for security improvement
                    //  [brute force on sessionID useless]
                    $_COOKIE[ 'auth' ] = $_SESSION[ 'user-auth' ];
                    setcookie( 'auth', $_SESSION[ 'user-auth' ], time() + (86400 * 30), "store.php" );  //  force browser to save cookie in memory

                }else
                    throw new LogException(
                        [ 'SERVICE-ANALYSIS' ],
                        'FRONTEND-LOGIC',
                        7,
                        'Bad Login Request missing request fields[Out of client Request]'
                    );

                break;

            case 'registration':

                //  checking all the message fields are present
                if( isset( $_POST[ 'username' ], $_POST[ 'password' ], $_POST[ 'phone' ])){

                    //  sanitization of tainted data
                    sanitize_registration(
                        $_POST[ 'username' ],
                        $_POST[ 'password' ],
                          $_POST[ 'phone' ]
                    );

                    //  caching registration information for activation phase[only after that will be stored]
                    $_SESSION[ 'registration-username' ] = $_POST[ 'username' ];
                    $_SESSION[ 'registration-password' ] = $_POST[ 'password' ];
                    $_SESSION[ 'registration-phone' ] = $_POST[ 'phone' ];
                    $_SESSION[ 'registration-expire' ] = time() + 300;

                }else
                    throw new LogException(
                        [ 'SERVICE-ANALYSIS' ],
                        'FRONTEND-LOGIC',
                        7,
                        'Bad Registration Request missing request fields[Out of client Request]'
                    );

                break;

            case 'activation':

                //  checking all the message fields are present
                if( isset( $_POST[ 'username' ], $_POST[ 'otp-id' ], $_POST[ 'otp-value' ])){

                    //  sanitization of tainted data and check of registration cached information presence
                    sanitize_activation( $_POST[ 'username'], $_POST[ 'otp-id' ], $_POST[ 'otp-value' ]);

                    //  extraction and removal of registration information
                    $username = $_SESSION[ 'registration-username' ];
                    $password = $_SESSION[ 'registration-password' ];
                    $phone = $_SESSION[ 'registration-phone' ];
                    $registrationExpire = $_SESSION[ 'registration-expire' ];

                    //  information are one-shot, whenever it goes they will be consumed
                    unset( $_SESSION[ 'registration-username' ], $_SESSION[ 'registration-password' ], $_SESSION[ 'registration-phone' ], $_SESSION[' registration-expire']);

                    if( $registrationExpire < time())
                        throw new LogException(
                            [ 'USER-ERROR' ],
                            'FRONTEND-LOGIC',
                            0,
                            'Invalid activation. Registration already expired'
                        );

                    //  verification of otp session information presence and eventual check
                    check_otp( $_POST[ 'otp-id' ], $_POST[ 'otp-value' ]);

                    //  account registration
                    $mySqlConnection = new sqlconnector();
                    if( !$mySqlConnection->registerUser( $username, $password, $phone ))
                        throw new LogException(
                            [ 'USER-ERROR', 'USER-SCRAPING' ],
                            'FRONTEND-LOGIC',
                            2,
                            'Invalid user activation. User already exists'
                        );

                }else
                    throw new LogException(
                        [ 'SERVICE-ANALYSIS' ],
                        'FRONTEND-LOGIC',
                        7,
                        'Bad Activation Request missing request fields[Out of client Request]'
                    );
                break;

            case 'change_password':

                //  checking all the message fields are present
                if( isset( $_POST[ 'username' ], $_POST[ 'password' ], $_POST[ 'old-password' ], $_POST[ 'otp-id' ], $_POST[ 'otp-value' ])){

                    //  sanitization of tainted data and check of registration cached information presence
                    sanitize_password_change(
                           $_POST[ 'username' ],
                        $_POST[ 'old-password' ],
                           $_POST[ 'password' ],
                              $_POST[ 'otp-id' ],
                           $_POST[ 'otp-value' ]
                    );

                    //  verification of otp session information presence and eventual check
                    check_otp( $_POST[ 'otp-id' ], $_POST[ 'otp-value' ]);

                    //  change of the password
                    $mySqlConnection = new sqlconnector();

                    //  verification of credentials
                    $result = $mySqlConnection->login( $_POST['username'], $_POST['old-password'] );

                    //  application of the new password
                    if( !$mySqlConnection->changePassword( $result[ 'uID' ], $_POST[ 'password' ]))
                        throw new LogException(
                            [ 'INTERNAL-ERROR' ],
                            'FRONTEND-LOGIC',
                            2,
                            'Unable to change user ' . $_POST[ 'username' ] . ' password'
                        );
                }else
                    throw new LogException(
                        [ 'SERVICE-ANALYSIS' ],
                        'FRONTEND-LOGIC',
                        7,
                        'Bad Password Change Request missing request fields [Out of client Request]'
                    );
                break;

            default:
                throw new LogException(
                    [ 'SERVICE-ANALYSIS' ],
                    'FRONTEND-LOGIC',
                    8,
                    'Bad General Request invalid field "type" [Out of client Request]'
                );
        }
    }else
        throw new LogException(
            [ 'SERVICE-ANALYSIS' ],
            'FRONTEND-LOGIC',
            8,
            'Bad General Request missing basic field "type"[Out of client Request]'
        );

}catch( LogException $e ){

    writeLog( $e );
    http_response_code( 400 );

}catch( Exception $e ){
    http_response_code( 400 );
}
