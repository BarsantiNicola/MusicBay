<?php

session_start();

include_once( 'sql_connector.php' );
include_once( 'security.php' );
include_once( 'LogException.php' );

function add_cart( $songId ): int{


    if( !isset( $_SESSION[ 'cart' ]))
        $_SESSION[ 'cart' ] = [];

    if( !in_array( $songId, $_SESSION[ 'cart' ] ))
        $_SESSION['cart'][] = $songId;

    return sizeof( $_SESSION[ 'cart' ] );
}

function remove_cart( $songId ): int{

    if( !isset( $_SESSION[ 'cart' ]))
        $_SESSION[ 'cart' ] = [];

    if( in_array( $songId, $_SESSION[ 'cart' ] ))
        $_SESSION[ 'cart' ] = array_diff( $_SESSION[ 'cart' ], [$songId] );

    return count( $_SESSION[ 'cart' ] );
}

/**
 * Returns a cart representation for the user payment form
 * @throws LogException If the given songID isn't present
 */
function get_cart(): array{

    if( !isset( $_SESSION[ 'cart' ]))
        $_SESSION[ 'cart' ] = [];

    $data = [];
    $connection = new sqlconnector();
    foreach( $_SESSION[ 'cart' ] as $song )
        $data[] = $connection->getSongInfo($song);

    return $data;

}

try {

    if( isset( $_POST[ 'type' ])) {  //  every message must have a 'type' field

        if( strcmp( $_POST[ 'type' ], 'logout' ) == 0 ){
            session_destroy();
            unset( $_COOKIE[ 'auth' ]);   //  manual clean of cookie
            setcookie( 'auth', '', time() - 3600, '/'); // empty value and old timestamp -> browser drops cookie from memory
            header("index.php", true, 301 );
            exit();
        }

        check_authentication();
        switch( $_POST[ 'type' ]) {

            case 'default_search':

                if( isset( $_POST[ 'page' ]) && is_numeric( $_POST[ 'page' ])) {

                    $connector = new sqlconnector();
                    echo json_encode( $connector->getMusic( 'default_search', $_SESSION[ 'user-id' ], '', '', $_POST[ 'page' ]));

                }else
                    throw new LogException(
                        [ 'SERVICE-ANALYSIS' ],
                        'SERVICE-LOGIC',
                        7,
                        'Bad Default Search Request, missing or invalid request fields[Out of client Request]'
                    );
                break;

            case 'search':

                if( isset( $_POST[ 'genre' ], $_POST[ 'filter' ], $_POST[ 'page' ])) {

                    check_search( $_POST[ 'genre' ], $_POST[ 'filter' ], $_POST[ 'page' ]);

                    $connector = new sqlconnector();
                    echo json_encode( $connector->getMusic( 'search', $_SESSION[ 'user-id' ], $_POST[ 'filter' ], $_POST[ 'genre' ], $_POST[ 'page' ]));

                }else
                    throw new LogException(
                        [ 'SERVICE-ANALYSIS' ],
                        'SERVICE-LOGIC',
                        7,
                        'Bad Default Search Request, missing request fields[Out of client Request]'
                    );
                break;

            case 'add_cart':

                if( isset( $_POST[ 'song-id' ]) && is_numeric( $_POST[ 'song-id' ])) {
                    echo add_cart( $_POST[ 'song-id' ]);
                }else
                    throw new LogException(
                        [ 'SERVICE-ANALYSIS' ],
                        'SERVICE-LOGIC',
                        7,
                        'Bad Add Cart Request. Missing or invalid song-ID[Out of client Request]'
                    );
                break;

            case 'remove_cart':
                if( isset( $_POST[ 'song-id' ]) && is_numeric( $_POST[ 'song-id' ]))
                    echo remove_cart( $_POST[ 'song-id' ]);

                else
                    throw new LogException(
                        [ 'SERVICE-ANALYSIS' ],
                        'SERVICE-LOGIC',
                        7,
                        'Bad Remove Cart Request. Missing or invalid song-ID[Out of client Request]'
                    );
                break;

            case 'get_cart':
                echo json_encode(get_cart());
                break;

            case 'order':

                if( !isset( $_POST[ 'CCN' ], $_POST[ 'CVV' ], $_POST[ 'name' ], $_POST[ 'surname' ], $_POST[ 'card-expire' ] ))
                    throw new LogException(
                        [ 'SERVICE-ANALYSIS' ],
                        'SERVICE-LOGIC',
                        7,
                        'Bad Buy Request. Missing credit card data[Out of client Request]'
                    );

                $cart = get_cart();

                if( count( $cart ) != 0 ){

                    $connection = new sqlconnector();
                    do {
                        $transaction_id = randBytes();
                    }while( $connection->checkTransaction( $transaction_id ));

                    $_SESSION[ 'order' ] = [
                        "cart" => $cart,
                        "transactionId" => $transaction_id,
                        "CCN" => $_POST[ 'CCN' ],
                        "CVV" => $_POST[ 'CVV' ],
                        "name" => $_POST[ 'name' ],
                        "surname" => $_POST[ 'surname' ],
                        "card-expire" => $_POST[ 'card-expire' ],
                        "transaction-expire" => time() + 120
                    ];

                    $price = 0;
                    $songs = [];

                    foreach( $cart as $song ) {
                        $price += $song['price'];
                        $songs[] = $song[ 'title' ];
                    }

                    echo json_encode( [
                        "transactionID" => $transaction_id,
                        "price" => $price,
                        "cart" => $songs
                    ]);

                }
                break;
                
            case 'buy':
                if( !isset( $_POST[ 'transactionID' ], $_SESSION[ 'order' ]))
                    throw new LogException(
                        [ 'SERVICE-ANALYSIS' ],
                        'SERVICE-LOGIC',
                        7,
                        'Bad Buy Request. Missing transactionID[Out of client Request]'
                    );


                if( $_SESSION[ 'order' ][ 'transaction-expire' ] > time() ){
                    echo "checked";
                    $cart = $_SESSION[ 'order' ][ 'cart' ];
                    if( count( $cart ) != 0 ){
                        $connection = new sqlconnector();
                        foreach( $cart as $song ){
                            $connection->addPayment(
                                $_POST[ 'transactionID' ],
                                $_SESSION[ 'user-id' ],
                                $song['song-id'],
                                $song[ 'price' ],
                                $_SESSION[ 'order' ][ 'CCN' ],
                                $_SESSION[ 'order' ][ 'name' ],
                                $_SESSION[ 'order' ][ 'surname' ]
                            );
                            remove_cart( $song[ 'song-id' ] );
                        }
                    }
                }
                echo "checked2";
                break;

            case 'download':
                if( !isset( $_POST[ 'song-id' ]))
                    throw new LogException(
                        [ 'SERVICE-ANALYSIS' ],
                        'SERVICE-LOGIC',
                        9,
                        'Bad Buy Request. Missing songID[Out of client Request]'
                    );
                $connection = new sqlconnector();
                $songTitle = $connection->checkSong( $_SESSION[ 'user-id' ], $_POST[ 'song-id' ]);
                $filename = exposeData( 'song', $songTitle );

                if(file_exists($filename)){

                    //Get file type and set it as Content Type
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    header('Content-Type: ' . finfo_file($finfo, $filename));
                    finfo_close($finfo);

                    //Use Content-Disposition: attachment to specify the filename
                    header('Content-Disposition: attachment; filename='.basename($filename));

                    //No cache
                    header('Expires: 0');
                    header('Cache-Control: must-revalidate');
                    header('Pragma: public');

                    //Define file size
                    header('Content-Length: ' . filesize($filename));

                    ob_clean();
                    flush();
                    readfile($filename);

                }
                break;

            default:
                throw new LogException(
                    [ 'SERVICE-ANALYSIS' ],
                    'SERVICE-LOGIC',
                    8,
                    'Bad General Request invalid field "type"[Out of client Request]'
                );
        }

    }else
        throw new LogException(
            [ 'SERVICE-ANALYSIS' ],
            'SERVICE-LOGIC',
            8,
            'Bad General Request missing basic field "type"[Out of client Request]'
        );

}catch( LogException $e ){

    echo json_encode( $e->getMessage() );
    writeLog( $e );
    http_response_code( 400 );

}
