<?php

function randBytes($length = 256)
    {
        if (function_exists('random_bytes')) {
            // PHP 7
            return random_bytes($length);
        }
        if (function_exists('openssl_random_pseudo_bytes')) {
            // OpenSSL
            $result = openssl_random_pseudo_bytes($length, $strong);
            if (!$strong) {
                throw new Exception('OpenSSL failed to generate secure randomness.');
            }
            return $result;
        }
        if (file_exists('/dev/urandom') && is_readable('/dev/urandom')) {
            // Unix
            $fh = fopen('/dev/urandom', 'rb');
            if ($fh !== false) {
                $result = fread($fh, $length);
                fclose($fh);
                return $result;
            }
        }
        throw new Exception('No secure random source available.');
    }

    session_start();
  //  $_SESSION['captcha-id'] = '12345';
  //  $_SESSION['captcha-value'] = 'Tutsplus';
/*
* Write your logic to manage the data
* like storing data in database
*/

//  GET TOTAL CAPTCHA STORED FROM DATABASE

   // $captchaId = random_bytes( 20 );
  //  $captchaValue = random_bytes( 16 );
  //  $captcha = random_int( 0, 5 );
    $len = 20;
    $dat = [ 'captcha-id' => 'test', 'captcha-value' => 'test'];
//  GENERATE A RANDOM VALUE BETWEEN 0 - N_CAPTCHA
//  GET ASSOCIATED IMAGE
//  MAKE A COPY OF THE ASSOCIATED IMAGE INTO THE TEMP FOLDER[ACCESSIBLE]
//  GENERATE A RANDOM STRING OF 16 BYTES[CAPTCHA-VALUE]
//  GENERATE A RANDOM STRING OF 20 BYTES[CAPTCHA-ID]
//  GENERATE KEY[CAPTCHA-VALUE && CAPTCHA-MASK]
//  STORE INTO DB CAPTCHA-ID/KEY + TIMESTAMP 1M
 
// POST Data


    header('Content-type: application/json');
    echo json_encode($dat);
 
?>