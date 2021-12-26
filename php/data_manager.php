<?php


//  extract configuration from local directory[directory must be protected from external access]
function getConfiguration(){
    return json_decode(file_get_contents( '../conf/conf' ));
}

/**
 * @throws ErrorException
 */
function randBytes($length = 256){

    static $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $string = '';
    try{
        for( $i = 0; $i<$length; $i++ )
            $string .= $characters[random_int(0, $charactersLength-1)];
        return $string;
    }catch( Exception $e ){
        throw new ErrorException("Error during the generation of random string\n" );
    }

}

//  place the given data into the temp folder with a randomic name of 32 bytes
//  making external users to access to it. It automatically remove the file after the $lease time[expressed in seconds]
/**
 * @throws ErrorException
 */
function exposeData($type, $data_path, $lease ): string
{


    $realPath = null;
    switch( $type ){

        case 'captcha':
            $realPath = "../captcha/" . $data_path;
            break;

        case 'song':
            $realPath = "../music/" . $data_path;
            break;

    }

    if( file_exists( $realPath )){

        $ext = substr( $data_path, strrpos( $data_path, "." ));
        $randName = randBytes( 32 );
        $exposedPath = "../temp/" . $randName . $ext;

        //  file will be dropped automatically after 1m by the temp_dropper.php cron executable
        copy( $realPath, $exposedPath );
        return "temp/" . $randName . $ext;

    }else
        throw new ErrorException("File " . $data_path . " not found\n");

}

function exposeCaptcha( $name ): array
{

    $captchas = [];
    for( $x = 0; $x<4; $x++ )
        for( $y = 0; $y<4; $y++ ){
            $pos = $x . '' . $y;
            $captchas[$pos] = exposeData( 'captcha', $name . $pos . ".jpg" , 120 );
        }

    return $captchas;
}

/**
 * @throws ErrorException
 */
function generateAuthCaptcha($captcha, $mask ): string
{

    if( strlen( $captcha ) != strlen( $mask ))
        throw new ErrorException( "Captcha and Mask len doesn't match[".strlen($captcha)."!=".strlen($mask)."]\n");
    $authCaptcha = '';

    for( $a = 0; $a<strlen($mask); $a++ )
        if( $mask[$a] == '1' )
            $authCaptcha .= $captcha[$a];

    return $authCaptcha;

}
