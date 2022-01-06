<?php

$path = '/var/www/html/musicbay/temp';
$path2 = '/var/www/html/musicbay/download';

$captcha_files = array_diff(scandir($path), ['.', '..']);
$downloadable_files = array_diff(scandir($path2), ['.', '..']);

foreach( $captcha_files as $file ){
    //unix time
    $ctime = filemtime( $path . "/$file" );
    //basic math
    if((time()-$ctime) > 60 )
        unlink( $path . "/$file" );
}

foreach( $downloadable_files as $file ){
    //unix time
    $ctime = filemtime( $path2 . "/$file" );
    //basic math
    if((time()-$ctime) > 120 )
        unlink( $path2 . "/$file" );
}