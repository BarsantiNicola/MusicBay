<?php

include_once( 'LogException.php' );
require_once 'vendor/autoload.php';

//  THE FILE CONTAINS METHOD FOR ACCESSING SYSTEM DATA
/**
 * Function for taking the configuration file needed by the webserver
 * @return mixed A dictionary containing the configuration file
 */
function getConfiguration( $file ){
    return json_decode( file_get_contents( '../conf/' . $file ));
}

/**
 * Function for plotting the given exception into the log file of the webServer
 * @param LogException $logException Object generated by an anomalous condition
 */
function writeLog( LogException $logException ){

    $conf = getConfiguration( 'conf' );

    file_put_contents(
            $conf->log_path,
           '[' . $logException->getTimestamp() . ']['
            . $logException->getRoutine() . ']['
            . json_encode( $logException->getVulnerability() )  . ']['
            . $logException->getSeverity()  . ']['
            . $logException->getAddress()   . ']['
            . $logException->getSessionID() . ']['
            . $logException->getVulnerability() . '] -> ' . $logException->getMessage() . "\n", FILE_APPEND | LOCK_EX );

}

/**
 * Places the given data into a temp folder with a random name of 64 bytes. Permits sharing internal files to users
 * preventing attacker to steal the data. Stored data will be automatically removed after the lease time[by a system cron application]
 * @param  string $name   The name of the file to be exposed
 * @throws LogException   In case the given file doesn't exist
 * @return string         The exposed path containing the file
 */
function get_file( string $name ): string{

    $configuration = getConfiguration( 'conf' );

    //  to improve security the database contains only the file name without any reference of the absolute position
    $realPath  = realPath( $configuration->music_path . $name );

    if( $realPath != null && file_exists( $realPath ) && strstr( $realPath, $configuration->music_path ) == $realPath )
        return $realPath;
    else
        throw new LogException(
            [ 'INTERNAL-ERROR' ],
            'DATA-MANAGER',
            0,
            'Unable to find the requested file -> ' . $realPath
        );
}

use Twilio\Rest\Client;

/**
 * Function to send a predefined sms containing the otpValue to the given number
 * @param string $phone    Random string of 16 bytes
 * @param string $otpValue The otp value composed by 6 digits
 * @throws LogException    In case the system is unable to send to sms
 */
function sendOTPsms( string $phone, string $otpValue ){

    try {

        $configuration = getConfiguration('conf');

        $sid = $configuration->twilloID;
        $token = $configuration->twilloToken;
        $twilio = new Client($sid, $token);

        $twilio->messages->create(
            '+39' . $phone,
                [
                    "body" => "Your MusicBay verification code is: " . $otpValue,
                    "from" => $configuration->twilloNumber
                ]
            );

    }catch( Exception $e ){
        throw new LogException(
            [ 'INTERNAL-ERROR' ],
            'DATA-MANAGER',
            0,
            'Unable to send token to phone number -> ' . $phone
        );
    }
}