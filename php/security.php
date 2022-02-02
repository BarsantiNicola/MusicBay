<?php


/**  RANDOM DATA GENERATION  **/

/**
 * Function for generation of random string composed by numbers of a given length
 * The function uses random_int which is based on a cryptographically secure random number generator
 * @throws LogException  In case the system is unable to generate the random string
 * @return string        The random string generated
 */
function randInt( int $length = 6 ): string{

    static $characters = '0123456789';
    $charactersLength = strlen( $characters );
    $string = '';

    try{
        for( $i = 0; $i<$length; $i++ )
            $string .= $characters[ random_int( 0, $charactersLength - 1 )];
        return $string;

    }catch( Exception $e ){  //  CANNOT HAPPEN
        throw new LogException(
            [ 'INTERNAL-ERROR' ],
            'SECURITY',
            8,
            'Unable to generate random string of ' . $length . ' bytes'
        );
    }
}

/**
 * Function for generation of random string composed by a predefined set of available characters
 * The function uses random_int which is based on a cryptographically secure random number generator
 * @param  int $length   Length of the random string to be generated
 * @throws LogException  In case the system is unable to generate the random string
 * @return string        The random string generated
 */
function randBytes( int $length = 32 ): string{  //  default length hard-coded into the security checks, must not be changed

    //  characters allowed inside the string
    static $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen( $characters );
    $string = '';

    try{

        for( $i = 0; $i<$length; $i++ )
            $string .= $characters[ random_int( 0, $charactersLength - 1 )];

        return $string;

    }catch( Exception $e ){   //  CANNOT HAPPEN
        throw new LogException(
            [ 'INTERNAL-ERROR' ],
            'SECURITY',
            8,
            'Unable to generate random string of ' . $length . ' bytes'
        );

    }
}


/**
 * Checks if the user request is valid and in the case update the authentication token
 * @throws LogException  In case of invalid request
 */
function check_authentication(){

    if( !isset( $_SESSION[ 'user-id' ], $_SESSION[ 'user-auth' ], $_COOKIE[ 'auth' ])) {

        header('Location: ' . 'index.php', true, 301 );
        throw new LogException(
            ['EXPIRED-SESSION', 'USER-ERROR', 'SERVICE-ANALYSIS'],
            'SECURITY',
            1,
            'Bad Service Request. Missing session or cookie information[SESSION: ' . isset($_SESSION['user-id']) . " COOKIE:" . isset($_COOKIE['user-auth']) . ']'
        );
    }

    if( strncmp( $_SESSION[ 'user-auth' ], $_COOKIE[ 'auth' ], 32 ) != 0 ){

        header('Location: ' . 'index.php', true, 301 );
        throw new LogException(
            ['EXPIRED-SESSION', 'USER-ERROR', 'SERVICE-ANALYSIS'],
            'SECURITY',
            2,
            'Bad Service Request. Missing session or cookie information[PHP_SESSION: ' . $_SESSION['user-id'] . " COOKIE:" . $_COOKIE['user-auth'] . ']'
        );

    }

    $_SESSION[ 'user-auth' ] = randBytes();
    setcookie( 'auth', $_SESSION[ 'user-auth' ], time() + (86400 * 30), "store.php" );  //  force browser to save cookie in memory

}


/**  OTP MANAGEMENT  **/


/**
 * Checks the validity of the given OTP by comparing it with eventual session-stored OTP information
 * @param  string $otpID    ID of the OTP to check[32 bytes]
 * @param  string $otpValue Value of the OTP to check[6 digits]
 * @throws LogException     In case the OTP is invalid(missing information/un-matching OTP)
 */
function check_otp( string $otpID, string $otpValue ){

    //  checking OTP session information are present
    if( !isset( $_SESSION[ 'otp-id' ], $_SESSION[ 'otp-value' ], $_SESSION[ 'otp-expire' ]))
        throw new LogException(
            [ 'SERVICE-ANALYSIS', 'EXPIRED-SESSION' ],
            'SECURITY',
            5,
            'Invalid OTP request. Missing session information for ' . $otpID . ' -> ' . $otpValue
        );

    //  extraction and unsetting OTP-info[however it goes after a trial we clean OTP info]
    $sessionOTPid = $_SESSION[ 'otp-id' ];
    $sessionOTPvalue = $_SESSION[ 'otp-value' ];
    $sessionOTPexpire = $_SESSION[ 'otp-expire' ];

    unset( $_SESSION[ 'otp-expire' ], $_SESSION[ 'otp-id' ], $_SESSION[ 'otp-value'] );

    if( $sessionOTPexpire < time() )
        throw new LogException(
            [ 'EXPIRED-SESSION' ],
            'SECURITY',
            0,
            'Invalid OTP request. OTP already expired'
        );

    if( strcmp( $sessionOTPid , $otpID ) != 0 || strcmp( $sessionOTPvalue, $otpValue ) != 0 )
        throw new LogException(
            [ 'USER-ERROR', 'BRUTE-FORCING' ],
            'SECURITY',
            2,
            'Invalid OTP request. Values doesn\'t match: IDS[' . $sessionOTPid . ':' . $otpValue . '] VALUES[' . $sessionOTPvalue . ':' . $otpValue . ']'
        );

}


/**
 * Checks the given mask is valid
 * Condition on captchaValue:
 *       - must contain only digits
 * @param string $id  id to be checked
 * @throws LogException  If the given source is invalid or not contained into the data folder
 */
function sanitize_id( string $id ): int{

    if( $id == null || strlen( $id ) == 0 )
        throw new LogException(
            [ 'XSS-ATTACK' ],
            'SECURITY',
            10,
            'Invalid id found into the database: ' . $id
        );

    if( !preg_match('/^[0-9]/', $id ))
        throw new LogException(
            [ 'XSS-ATTACK' ],
            'SECURITY',
            9,
            'Sanitization of id failed. Some invalid characters present -> ' . $id
        );

    return $id;

}

/**  LOGIN PAGE SANITIZATION  **/


/**
 * Checks the given username is valid
 * Condition on username:
 *       - must be between 5 and 20 chars
 *       - must contain only letters, digits or some basic symbols(@ _ #)
 * @param  string $username   Username to check
 * @throws LogException       In case the username is not valid(wrong length, missing data, invalid characters)
 * @return string             The given field cleaned from spacing and tabulations
 */
function sanitize_username( string $username ): string{

    if( $username == null )
        throw new LogException(
            [ 'SERVICE-ANALYSIS' ],
            'SECURITY',
            1,
            'Sanitization of username failed. Missing field [Out of client Request]'
        );

    $text = trim( str_replace( '"', "" , str_replace( "'", "", utf8_encode( $username ))));

    //  checking username length is in [3-20] and it is composed only by letters, digits and @ _ #
    if( !preg_match( '/^[a-zA-Z0-9_@#]{8,20}$/', $text ))
        throw new LogException(
            [ 'SERVICE-ANALYSIS' ],
            'SECURITY',
            1,
            'Sanitization of username failed. Some invalid characters present [Out of client Request] -> ' . $username
        );

    return $text;

}

/**
 * Checks the given password is valid
 * Condition on password:
 *       - length must be of 64 bytes[SHA256 hash]
 *       - must contain only number and digits
 * @param  string $password  Password to check
 * @throws LogException      In case the password is not valid(wrong length, missing data, invalid characters)
 * @return string            The given field cleaned from spacing and tabulations
 */
function sanitize_password( string $password ): string{

    if( $password == null )
        throw new LogException(
            [ 'SERVICE-ANALYSIS' ],
            'SECURITY',
            1,
            'Sanitization of password failed. Missing field [Out of client Request]'
        );

    //  checking username length is in [3-20] and it is composed only by letters, digits and @ _ #
    if( strlen( $password ) != 64 )
        throw new LogException(
            [ 'SERVICE-ANALYSIS' ],
            'SECURITY',
            1,
            'Sanitization of password failed. Invalid password length [Out of client Request] -> [' . $password . ':' . strlen( $password ) . ']'
        );

    if( !preg_match( '/^[a-zA-Z0-9]/', $password ))
        throw new LogException(
            [ 'SERVICE-ANALYSIS' ],
            'SECURITY',
            1,
            'Sanitization of password failed. Invalid characters [Out of client Request] -> [' . $password . ':' . strlen( $password ) . ']'
    );

    return $password;

}

/**
 * Checks the given phone is valid
 * Condition on phone:
 *       - must be 10 digits
 * @param  string $phone  Mobile phone number to check
 * @throws LogException   In case the phone is not valid(wrong length, invalid characters)
 * @return string         The given field cleaned from spacing and tabulations
 */
function sanitize_phone( string $phone ): string{

    if( $phone == null )
        throw new LogException(
            [ 'SERVICE-ANALYSIS' ],
            'SECURITY',
            1,
            'Sanitization of phone failed. Missing field [Out of client Request]'
        );

    $text = trim( str_replace( '"', "" , str_replace( "'", "", utf8_encode( $phone ))));

    if( strlen( $text ) != 10 )
        throw new LogException(
            [ 'SERVICE-ANALYSIS' ],
            'SECURITY',
            1,
            'Sanitization of phone failed. Invalid phone length [Out of client Request] -> ' . $phone
        );

    if( !preg_match( '/^[0-9]/', $text))
        throw new LogException(
            [ 'SERVICE-ANALYSIS' ],
            'SECURITY',
            9,
            'Sanitization of phone failed. Invalid chars [Out of client Request] -> ' . $phone
        );

    return $text;
}

/**
 * Checks the given otpID is valid
 * Condition on otpID:
 *       - must be 32 characters
 *       - must contain only characters and digits
 * @param  string $otpID  otpID to check
 * @throws LogException   In case the otpID is not valid(wrong length, missing data, invalid characters)
 * @return string         The given field cleaned from spacing and tabulations
 */
function sanitize_otpId( string $otpID ): string{

    if( $otpID == null )
        throw new LogException(
            [ 'SERVICE-ANALYSIS' ],
            'SECURITY',
            1,
            'Sanitization of otpID failed. Missing field [Out of client Request]'
        );

    $text = trim( str_replace( '"', "" , str_replace( "'", "", utf8_encode( $otpID ))));

    if( $text == null || strlen( $text ) != 32 )
        throw new LogException(
            [ 'SERVICE-ANALYSIS' ],
            'SECURITY',
            1,
            'Sanitization of otpID failed. Invalid length -> [' . $text . ':' . strlen( $text ) . ']'
        );

    if( !preg_match( '/^[a-zA-Z0-9]/', $text ))
        throw new LogException(
            [ 'SERVICE-ANALYSIS' ],
            'SECURITY',
            1,
            'Sanitization of otpID failed. Invalid characters found -> ' . $text
        );

    return $text;

}

/**
 * Checks the given otpValue is valid
 * Condition on otpValue:
 *       - must be of 6 digits
 * @param  string $otpValue  otpValue to check
 * @throws LogException      In case the otpValue is not valid(wrong length, missing data, invalid characters)
 * @return string            The given field cleaned from spacing and tabulations
 */
function sanitize_otpValue( string $otpValue ): string{

    if( $otpValue == null )
        throw new LogException(
            [ 'SERVICE-ANALYSIS' ],
            'SECURITY',
            1,
                'Sanitization of otpValue failed. Missing field [Out of client Request]'
        );

    $text = trim( str_replace( '"', "" , str_replace( "'", "", utf8_encode( $otpValue ))));

    if( strlen( $text ) != 6 )
        throw new LogException(
            [ 'SERVICE-ANALYSIS' ],
            'SECURITY',
            1,
            'Sanitization of otpValue failed. Invalid length -> [' . $text . ':' . strlen( $text ) . ']'
        );

    if( !preg_match('/^[0-9]/', $text ))
        throw new LogException(
            [ 'SERVICE-ANALYSIS' ],
            'SECURITY',
            1,
            'Sanitization of otpValue failed. Invalid characters found -> ' . $text
        );

    return $text;

}




/**
 * Checks the validity of a registration request by sanitizing all the given inputs. The function automatically cleans
 * the given data from any space/tab
 * @param  string $username     Username to be registered
 * @param  string $password     Password associated with the new user
 * @param  string $phone        Mobile phone number associated with the new user
 * @throws LogException         In case invalid fields are present into the request
 */
function sanitize_registration( string &$username, string &$password, string &$phone ){

    $username = sanitize_username( $username );
    $password = sanitize_password( $password );
    $phone = sanitize_phone( $phone );

}

/**
 * Checks the validity of an account activation request by sanitizing all the given inputs. The function automatically cleans
 * the given data from any space/tab
 * @param  string $username  Username linked with a previous registration
 * @param  string $otpId     ID of the OTP to check[32 bytes]
 * @param  string $otpValue  Value of the OTP to check[6 digits]
 * @throws LogException      In case invalid fields are present into the request or session information are missing
 */
function sanitize_activation( string &$username, string &$otpId, string &$otpValue ){

    if( !isset( $_SESSION[ 'registration-username' ], $_SESSION[ 'registration-password' ], $_SESSION[ 'registration-phone' ], $_SESSION[ 'registration-expire' ]))
        throw new LogException(
            [ 'LOGIC-JUMP', 'EXPIRED-SESSION' ],
            'SECURITY',
            6,
            'Invalid activation request. Missing stored registration info[might be a tentative of get around site behavior]'
        );

    $username = sanitize_username( $username );
    $otpId = sanitize_otpId( $otpId );
    $otpValue = sanitize_otpValue( $otpValue );

}

/**
 * Checks the validity of an account password change request by sanitizing all the given inputs. The function automatically cleans
 * the given data from any space/tab
 * @param  string $username      Username associated with the request
 * @param  string $password      New password to be associated to the username
 * @param  string $otpId         ID of the OTP to check[32 bytes]
 * @param  string $otpValue      Value of the OTP to check[6 digits]
 * @throws LogException          In case invalid fields are present into the request
 */
function sanitize_password_change( string &$username, string &$old_password, string &$password, string &$otpId, string &$otpValue ){

    $username = sanitize_username( $username );
    $old_password = sanitize_password( $password );
    $password = sanitize_password( $password );
    $otpId = sanitize_otpId( $otpId );
    $otpValue = sanitize_otpValue( $otpValue );

}

/**
 * Checks the validity of an account password change request by sanitizing all the given inputs. The function automatically cleans
 * the given data from any space/tab
 * @param  string $username      Username associated with the request
 * @param  string $password      New password to be associated to the username
 * @param  string $otpId         ID of the OTP to check[32 bytes]
 * @param  string $otpValue      Value of the OTP to check[6 digits]
 * @throws LogException          In case invalid fields are present into the request
 */
function sanitize_login( string &$username, string &$password, string &$otpId, string &$otpValue ){

    $username = sanitize_username( $username );
    $password = sanitize_password( $password );
    $otpId = sanitize_otpId( $otpId );
    $otpValue = sanitize_otpValue( $otpValue );

}


/**  SERVICE SANITIZATION  */


/**
 * Checks the given captchaValue is valid
 * Condition on captchaValue:
 *       - must be at most of 16 characters
 *       - must contain only characters and digits
 * @param  string $src   source file to be checked
 * @throws LogException  If the given source is invalid or not contained into the data folder
 */
function sanitize_source( string $src ): string{

    $conf = getConfiguration( 'data' );

    if( $src == null || strlen( $src ) == 0 )
        throw new LogException(
            [ 'XSS-ATTACK' ],
            'SECURITY',
            10,
            'Invalid source found into the database: ' . $src
        );

    $src_sanitized = realpath( "../" . $src );

    if( $src_sanitized == false || strpos( $src_sanitized, $conf->general ) != 0 )
        throw new LogException(
            [ 'XSS-ATTACK' ],
            'SECURITY',
            10,
            'Invalid source found into the database2: ' . $src
        );

    return $src;
}

/**  Checks the credit card information to prevent the sending of invalid request to the credit card gateway
 * @throws LogException  In case the information are not valid
 */
function check_credit_card($cnn, $cvv, $name, $surname, $expire ){
    $time = strtotime( $expire );

    //$newformat = date('Y-m-d',$time);
    if( strlen($cvv) != 3 || strlen( $name ) == 0 || strlen( $surname ) == 0 || $time < time() )
        throw new LogException(
            [ 'USER-ERROR' ],
            'SECURITY',
            0,
            'Invalid credit card. Invalid fields ['. $cnn . ':' . $cvv . ':' . $name . ':' . $surname . ':' . $expire . ']'
        );

    luhn_check( $cnn ); //  checking if cnn has sense

}

/** Luhn algorithm number checker - (c) 2005-2008 shaman - www.planzero.org
 * @throws LogException In case of invalid cnn
 */
function luhn_check($number){

    // Strip any non-digits (useful for credit card numbers with spaces and hyphens)
    $number=preg_replace('/\D/', '', $number);

    // Set the string length and parity
    $number_length=strlen($number);
    $parity=$number_length % 2;

    // Loop through each digit and do the maths
    $total=0;
    for ($i=0; $i<$number_length; $i++) {
        $digit=$number[$i];
        // Multiply alternate digits by two
        if ($i % 2 == $parity) {
            $digit*=2;
            // If the sum is two digits, add them together (in effect)
            if ($digit > 9) {
                $digit-=9;
            }
        }
        // Total up the digits
        $total+=$digit;
    }

    // If the total mod 10 equals 0, the number is valid
    if( $total %10 != 0 )
        throw new LogException(
            [ 'USER-ERROR' ],
            'SECURITY',
            0,
            'Invalid credit card. Bad CNN field'
        );
}
