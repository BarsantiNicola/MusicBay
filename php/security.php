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
    $charactersLength = strlen($characters);
    $string = '';

    try{
        for( $i = 0; $i<$length; $i++ )
            $string .= $characters[random_int(0, $charactersLength-1)];
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
 * @param int $length    Length of the random string to be generated
 * @throws LogException  In case the system is unable to generate the random string
 * @return string        The random string generated
 */
function randBytes( int $length = 32): string{

    static $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen( $characters );
    $string = '';

    try{

        for( $i = 0; $i<$length; $i++ )
            $string .= $characters[ random_int( 0, $charactersLength-1 )];

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

/**  SANITIZATION  **/


/**
 * Checks the given username is valid
 * Condition on username:
 *       - must be between 3 and 20 chars
 *       - must contain only letters, digits or some basic symbols(@ _ #)
 * @param string $username   Username to check
 * @throws LogException      In case the username is not valid(wrong length, missing data, invalid characters)
 * @return string  The given field cleaned from spacing and tabulations
 */
function sanitize_username( string $username ): string{

    if( $username == null )
        throw new LogException(
            [ 'SERVICE-ANALYSIS' ],
            'SECURITY',
            8,
            'Sanitization of username failed. Missing field[Out of client Request]'
        );

    $text = trim( str_replace( '"', "" , str_replace( "'", "", utf8_encode( $username ))));

    //  checking username length is in [3-20] and it is composed only by letters, digits and @ _ #
    if( !preg_match( '/^[a-zA-Z0-9_@#]{3,20}$/', $text ))
        throw new LogException(
            [ 'SERVICE-ANALYSIS' ],
            'SECURITY',
            9,
            'Sanitization of username failed. Some invalid characters present[Out of client Request] -> ' . $username
        );

    return $text;

}

/**
 * Checks the given password is valid
 * Condition on password:
 *       - length must be of 64 bytes[SHA256 hash]
 * @param string $password   Password to check
 * @throws LogException      In case the password is not valid(wrong length, missing data, invalid characters)
 * @return string  The given field cleaned from spacing and tabulations
 */
function sanitize_password( string $password ): string{

    if( $password == null )
        throw new LogException(
            [ 'SERVICE-ANALYSIS' ],
            'SECURITY',
            8,
            'Sanitization of password failed. Missing field[Out of client Request]'
        );

    //  checking username length is in [3-20] and it is composed only by letters, digits and @ _ #
    if( strlen( $password ) != 64 )
        throw new LogException(
            [ 'SERVICE-ANALYSIS' ],
            'SECURITY',
            9,
            'Sanitization of password failed. Invalid password length[Out of client Request] -> [' . $password . ':' . strlen( $password )
        );

    return $password;

}

/**
 * Checks the given phone is valid
 * Condition on phone:
 *       - must be 10 digits
 * @param string $phone  Mobile phone number to check
 * @throws LogException  In case the phone is not valid(wrong length, invalid characters)
 * @return string  The given field cleaned from spacing and tabulations
 */
function sanitize_phone( string $phone ): string{

    if( $phone == null )
        throw new LogException(
            [ 'SERVICE-ANALYSIS' ],
            'SECURITY',
            8,
            'Sanitization of phone failed. Missing field[Out of client Request]'
        );

    $text = trim( str_replace( '"', "" , str_replace( "'", "", utf8_encode( $phone ))));

    if( strlen( $text ) != 10 )
        throw new LogException(
            [ 'SERVICE-ANALYSIS' ],
            'SECURITY',
            9,
            'Sanitization of phone failed. Invalid phone length[Out of client Request] -> ' . $phone
        );

    if( !preg_match( '/^[0-9]/', $text))
        throw new LogException(
            [ 'SERVICE-ANALYSIS' ],
            'SECURITY',
            9,
            'Sanitization of phone failed. Invalid chars[Out of client Request] -> ' . $phone
        );

    return $text;
}

/**
 * Checks the given otpID is valid
 * Condition on otpID:
 *       - must be 32 characters
 *       - must contain only characters and digits
 * @param string $otpID  otpID to check
 * @throws LogException  In case the otpID is not valid(wrong length, missing data, invalid characters)
 * @return string  The given field cleaned from spacing and tabulations
 */
function sanitize_otpId( string $otpID ): string{

    if( $otpID == null )
        throw new LogException(
            [ 'SERVICE-ANALYSIS' ],
            'SECURITY',
            8,
            'Sanitization of otpID failed. Missing field[Out of client Request]'
        );

    $text = trim( str_replace( '"', "" , str_replace( "'", "", utf8_encode( $otpID ))));

    if( $text == null || strlen( $text ) != 32 )
        throw new LogException(
            [ 'SERVICE-ANALYSIS' ],
            'SECURITY',
            8,
            'Sanitization of otpID failed. Invalid length -> [' . $text . ':' . strlen( $text ) . ']'
        );

    if( !preg_match( '/^[a-zA-Z0-9]/', $text ))
        throw new LogException(
            [ 'SERVICE-ANALYSIS' ],
            'SECURITY',
            8,
            'Sanitization of otpID failed. Invalid characters found -> ' . $text
        );

    return $text;

}

/**
 * Checks the given otpValue is valid
 * Condition on otpValue:
 *       - must be of 6 digits
 * @param string $otpValue  otpValue to check
 * @throws LogException     In case the otpValue is not valid(wrong length, missing data, invalid characters)
 * @return string  The given field cleaned from spacing and tabulations
 */
function sanitize_otpValue( string $otpValue ): string{

    if( $otpValue == null )
        throw new LogException(
            [ 'SERVICE-ANALYSIS' ],
            'SECURITY',
            8,
                'Sanitization of otpValue failed. Missing field[Out of client Request]'
        );

    $text = trim( str_replace( '"', "" , str_replace( "'", "", utf8_encode( $otpValue ))));

    if( strlen( $text ) != 6 )
        throw new LogException(
            [ 'SERVICE-ANALYSIS' ],
            'SECURITY',
            8,
            'Sanitization of otpValue failed. Invalid length -> [' . $text . ':' . strlen( $text ) . ']'
        );

    if( !preg_match('/^[0-9]/', $text ))
        throw new LogException(
            [ 'SERVICE-ANALYSIS' ],
            'SECURITY',
            8,
            'Sanitization of otpValue failed. Invalid characters found -> ' . $text
        );

    return $text;

}

/**
 * Checks the given captchaID is valid
 * Condition on captchaID:
 *       - must be 20 characters
 *       - must contain only characters and digits
 * @param string $captchaID  CaptchaID to check
 * @throws LogException  In case the captchaID is not valid(wrong length, missing data, invalid characters)
 * @return string  The given field cleaned from spacing and tabulations
 */
function sanitize_captchaID( string $captchaID ): string{

    if( $captchaID == null )
        throw new LogException(
            [ 'SERVICE-ANALYSIS' ],
            'SECURITY',
            8,
            'Sanitization of captchaID failed. Missing field[Out of client Request]'
        );

    $text = trim( str_replace( '"', "" , str_replace( "'", "", utf8_encode( $captchaID ))));

    if( strlen( $text ) != 20 )
        throw new LogException(
            [ 'SERVICE-ANALYSIS' ],
            'SECURITY',
            8,
            'Sanitization of captchaID failed. Invalid length -> [' . $text . ':' . strlen( $text ) . ']'
        );

    if( !preg_match( '/^[a-zA-Z0-9]/', $text ))
        throw new LogException(
            [ 'SERVICE-ANALYSIS' ],
            'SECURITY',
            8,
            'Sanitization of captchaID failed. Invalid characters found -> ' . $text
        );

    return $text;

}

/**
 * Checks the given captchaValue is valid
 * Condition on captchaValue:
 *       - must be at most of 16 characters
 *       - must contain only characters and digits
 * @param string $captchaValue  CaptchaValue to check
 * @throws LogException  In case the captchaValue is not valid(wrong length, missing data, invalid characters)
 * @return string  The given field cleaned from spacing and tabulations
 */
function sanitize_captchaValue( string $captchaValue ): string{

    if( $captchaValue == null )
        throw new LogException(
            [ 'SERVICE-ANALYSIS' ],
            'SECURITY',
            8,
            'Sanitization of captchaValue failed. Missing field[Out of client Request]'
        );

    $text = trim( str_replace( '"', "" , str_replace( "'", "", utf8_encode( $captchaValue ))));

    if( !preg_match('/^[a-zA-Z0-9]{3,16}$/', $text ))
        throw new LogException(
            [ 'SERVICE-ANALYSIS' ],
            'SECURITY',
            9,
            'Sanitization of captchaValue failed. Some invalid characters present[Out of client Request] -> ' . $text
        );

    return $text;

}

/**
 * Checks the given captchaValue is valid
 * Condition on captchaValue:
 *       - must be at most of 16 characters
 *       - must contain only characters and digits
 * @param string $src  source file to be checked
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

/**
 * Checks the given mask is valid
 * Condition on captchaValue:
 *       - must be 16 characters
 *       - must contain only 0 and 1
 * @param string $mask  mask to be checked
 * @throws LogException  If the given source is invalid or not contained into the data folder
 */
function sanitize_mask( string $mask ): string{

    if( $mask == null || strlen( $mask ) != 16 )
        throw new LogException(
            [ 'XSS-ATTACK' ],
            'SECURITY',
            10,
            'Invalid mask found into the database: ' . $mask
        );

    if( !preg_match('/^[0-1]/', $mask ))
        throw new LogException(
            [ 'XSS-ATTACK' ],
            'SECURITY',
            9,
            'Sanitization of mask failed. Some invalid characters present -> ' . $mask
        );

    return $mask;

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
/**
 * Checks the validity of a registration request by sanitizing all the given inputs. The function automatically cleans
 * the given data from any space/tab
 * @param string $username     Username to be registered
 * @param string $password     Password associated with the new user
 * @param string $phone        Mobile phone number associated with the new user
 * @param string $captchaID    ID of the captcha to check[20 bytes]
 * @param string $captchaValue Value of the captcha to check[max 16 bytes]
 * @throws LogException  In case invalid fields are present into the request
 */
function sanitize_registration( string &$username, string &$password, string &$phone, string &$captchaID, string &$captchaValue ){

    $username = sanitize_username( $username );
    $password = sanitize_password( $password );
    $phone = sanitize_phone( $phone );
    $captchaID = sanitize_captchaID( $captchaID );
    $captchaValue = sanitize_captchaValue( $captchaValue );

}

/**
 * Checks the validity of an account activation request by sanitizing all the given inputs. The function automatically cleans
 * the given data from any space/tab
 * @param string $username  Username linked with a previous registration
 * @param string $otpId     ID of the OTP to check[32 bytes]
 * @param string $otpValue  Value of the OTP to check[6 digits]
 * @throws LogException  In case invalid fields are present into the request or session information are missing
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
 * @param string $username      Username associated with the request
 * @param string $password      New password to be associated to the username
 * @param string $otpId         ID of the OTP to check[32 bytes]
 * @param string $otpValue      Value of the OTP to check[6 digits]
 * @param string $captchaId     ID of the captcha to check[20 bytes]
 * @param string $captchaValue  Value of the captcha to check[max 16 bytes]
 * @throws LogException  In case invalid fields are present into the request
 */
function sanitize_password_change( string &$username, string &$password, string &$otpId, string &$otpValue, string &$captchaId, string &$captchaValue ){

    $username = sanitize_username( $username );
    $password = sanitize_password( $password );
    $otpId = sanitize_otpId( $otpId );
    $otpValue = sanitize_otpValue( $otpValue );
    $captchaId = sanitize_captchaID( $captchaId );
    $captchaValue = sanitize_captchaValue( $captchaValue );

}

/**
 * Checks the validity of an account password change request by sanitizing all the given inputs. The function automatically cleans
 * the given data from any space/tab
 * @param string $username      Username associated with the request
 * @param string $password      New password to be associated to the username
 * @param string $otpId         ID of the OTP to check[32 bytes]
 * @param string $otpValue      Value of the OTP to check[6 digits]
 * @throws LogException  In case invalid fields are present into the request
 */
function sanitize_login( string &$username, string &$password, string &$otpId, string &$otpValue ){

    $username = sanitize_username( $username );
    $password = sanitize_password( $password );
    $otpId = sanitize_otpId( $otpId );
    $otpValue = sanitize_otpValue( $otpValue );

}


/**  SUBSYSTEMS CONDITION CHECKS  */

/**
 * Checks the validity of the given OTP by comparing it with eventual session-stored OTP information
 * @param string $otpID    ID of the OTP to check[32 bytes]
 * @param string $otpValue Value of the OTP to check[6 digits]
 * @throws LogException  In case the OTP is invalid(missing information/un-matching OTP)
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
 * Checks the validity of the given captcha by comparing it with eventual session-stored captcha information
 * @param string $captchaID    ID of the captcha to check[20 bytes]
 * @param string $captchaValue Value of the captcha to check[max 16 bytes]
 * @throws LogException  In case the captcha is invalid(missing information/un-matching captcha)
 */
function check_captcha( string $captchaID, string $captchaValue ){

    //  checking captcha session information are present
    if( !isset( $_SESSION[ 'captcha-id' ], $_SESSION[ 'captcha-value' ], $_SESSION[ 'captcha-expire' ]))
        throw new LogException(
            [ 'SERVICE-ANALYSIS', 'EXPIRED-SESSION' ],
            'SECURITY',
            5,
            'Invalid captcha request. Missing session information for ' . $captchaID . ' -> ' . $captchaValue
        );

    //  extraction and unsetting captcha-info[however it goes after a trial we clean captcha info]
    $sessionCaptchaid = $_SESSION[ 'captcha-id' ];
    $sessionCaptchavalue = $_SESSION[ 'captcha-value' ];
    $sessionCaptchaexpire = $_SESSION[ 'captcha-expire' ];

    unset( $_SESSION[ 'captcha-expire' ], $_SESSION[ 'captcha-id' ], $_SESSION[ 'captcha-value'] );

    if( $sessionCaptchaexpire < time() )
        throw new LogException(
            [ 'EXPIRED-SESSION' ],
            'SECURITY',
            0,
            'Invalid captcha request. Captcha already expired'
        );

    if( strcmp( $sessionCaptchaid , $captchaID) != 0 || strcmp( $sessionCaptchavalue, $captchaValue ) != 0 )
        throw new LogException(
            [ 'USER-ERROR', 'BRUTE-FORCING' ],
            'SECURITY',
            1,
            'Invalid captcha request. Values doesn\'t match: IDS[' . $sessionCaptchaid . ':' . $captchaID . '] VALUES[' . $sessionCaptchavalue . ':' . $captchaValue . ']'
        );

}

/**
 * Checks if the user request is valid and in the case update the authentication token
 * @throws LogException  In case of invalid request
 */
function check_authentication(){

    if( !isset( $_SESSION[ 'user-id' ], $_SESSION[ 'user-auth' ], $_COOKIE[ 'auth' ]))
        throw new LogException(
            [ 'EXPIRED-SESSION', 'USER-ERROR', 'SERVICE-ANALYSIS' ],
            'SECURITY',
            2,
            'Bad Service Request. Missing session or cookie information[SESSION: ' . isset( $_SESSION['user-id' ]) . " COOKIE:" . isset( $_COOKIE[ 'user-auth' ]) . ']'
        );

    if( strcmp( $_SESSION[ 'user-auth' ], $_COOKIE[ 'auth' ]) != 0 )
        throw new LogException(
            [ 'EXPIRED-SESSION', 'USER-ERROR', 'SERVICE-ANALYSIS' ],
            'SECURITY',
            2,
            'Bad Service Request. Missing session or cookie information[PHP_SESSION: ' .  $_SESSION[ 'user-id' ] . " COOKIE:" . $_COOKIE[ 'user-auth' ] . ']'
        );

    $_SESSION[ 'user-auth' ] = randBytes();
    setcookie( 'auth', $_SESSION[ 'user-auth' ], time() + (86400 * 30), "store.php" );  //  force browser to save cookie in memory

}

function check_search( $genre, $filter, $page ){

}
