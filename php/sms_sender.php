<?php

    include_once( 'LogException.php' );
    include_once( 'data_manager.php' );
    require_once 'vendor/autoload.php';
    use Twilio\Rest\Client;
    /**
        * Function to send a predefined sms containing the otpValue to the given number
        * @param string $phone    Random string of 16 bytes
        * @param string $otpValue The otp value composed by 6 digits
        * @throws LogException    In case the system is unable to send to sms
    */
    function sendOTPsms( $phone, $otpValue ){

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


    $content = file_get_contents('/home/nico/Scrivania/otpReq' );
    unlink( '/home/nico/Scrivania/otpReq' );
    touch( '/home/nico/Scrivania/otpReq' );
    chmod('/home/nico/Scrivania/otpReq',0666);

    foreach(preg_split("/((\r?\n)|(\r\n?))/", $content) as $line){
        try {
            if( strlen( $line ) > 5 ) {
                $data = json_decode($line);
                sendOTPsms($data->phone, $data->value);
            }
        }catch( LogException $e ){
            writeLog( $e );
        }catch( Exception $e ){}
    }