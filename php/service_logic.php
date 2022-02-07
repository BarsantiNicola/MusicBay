<?php

session_start();

include_once( 'LogException.php' );     //  EXCEPTION RAISED FOR LOG FUNCTIONALITIES
include_once( 'sql_connector.php' );    //  DATABASE MANAGEMENT
include_once( 'security.php' );         //  SECURITY FUNCTIONALITIES[SANITIZATION/RANDOMIZATION]


//  CART MANAGEMENT


/**  If the song is not present it will add it into the session cart
 * @param  int $songId id of the song to be added to the cart
 * @return int The new number of songs listed into the cart
 */
function add_cart( int $songId ): int{

    if( !isset( $_SESSION[ 'cart' ]))
        $_SESSION[ 'cart' ] = [];

    if( !in_array( $songId, $_SESSION[ 'cart' ] ))
        $_SESSION['cart'][] = $songId;

    return count( $_SESSION[ 'cart' ] );

}

/**  If the song is present it will remove it from the session cart
 * @param  int $songId id of the song to be added to the cart
 * @return int The new number of songs listed into the cart
 */
function remove_cart( int $songId ): int{

    if( !isset( $_SESSION[ 'cart' ]))
        $_SESSION[ 'cart' ] = [];

    if( in_array( $songId, $_SESSION[ 'cart' ] ))
        $_SESSION[ 'cart' ] = array_diff( $_SESSION[ 'cart' ], [$songId] );

    return count( $_SESSION[ 'cart' ] );

}

/**  Returns the songs contained into the cart adding information obtained from the database
 * @throws LogException If the given songID isn't present
 */
function get_cart(): array{

    if( !isset( $_SESSION[ 'cart' ]))
        $_SESSION[ 'cart' ] = [];

    $connection = new sqlconnector();  //  connection with database
    $data = [];

    foreach( $_SESSION[ 'cart' ] as $song )
        $data[] = $connection->getSongInfo( $song );

    return $data;

}

try {

    if( isset( $_GET[ 'type' ]))
        $type = $_GET[ 'type' ];
    elseif ( isset( $_POST[ 'type' ]))
        $type = $_POST[ 'type' ];
    else
        throw new LogException(
            [ 'SERVICE-ANALYSIS' ],
            'SERVICE-LOGIC',
            8,
            'Bad General Request missing basic field "type"[Out of client Request]'
        );

    //  the system will not automatically remove the authentication credentials after an authentication error
    //  otherwise a brute force of session id can be used to randomly logout users
    check_authentication();

    switch( $type ) {

        case 'default_search':    //  search inside user's bought songs

            //  parameter check[ existence + sanitization ]
            if( isset( $_POST[ 'page' ]) && is_numeric( $_POST[ 'page' ]) && $_POST[ 'page' ] > -1 ){

                $connector = new sqlconnector(); //  connection with the database

                //  user-id already checked by check_authentication[line 75]
                echo json_encode( $connector->getMusic( 'default_search', $_SESSION[ 'user-id' ], '', '', $_POST[ 'page' ]));

            }else
                throw new LogException(
                    [ 'SERVICE-ANALYSIS' ],
                    'SERVICE-LOGIC',
                    1,
                    'Bad [DEFAULT_SEARCH] Request, missing or invalid request field [Out of client Request]'
                );
            break;

        case 'search':    //  search inside music archive

            //  parameters existence check
            if( isset( $_POST[ 'genre' ], $_POST[ 'filter' ], $_POST[ 'page' ])) {

                check_search( $_POST[ 'genre' ], $_POST[ 'filter' ], $_POST[ 'page' ] );

                $connector = new sqlconnector();  //  connection with the database

                //  user-id checked into check_authentication()[ line 75 ]
                echo json_encode( $connector->getMusic( 'search', $_SESSION[ 'user-id' ], $_POST[ 'filter' ], $_POST[ 'genre' ], $_POST[ 'page' ]));

            }else
                throw new LogException(
                    [ 'SERVICE-ANALYSIS' ],
                    'SERVICE-LOGIC',
                    1,
                    'Bad [SEARCH] Request, missing request fields [Out of client Request]'
                );
            break;

        case 'add_cart':    //  add a song to the cart

            //  parameter check[ existence + sanitization ]
            if( isset( $_POST[ 'song-id' ]) && is_numeric( $_POST[ 'song-id' ]) && $_POST[ 'song-id' ] > -1 )
                echo add_cart( $_POST[ 'song-id' ]);  //  returns the number of songs inside the cart
            else
                throw new LogException(
                    [ 'SERVICE-ANALYSIS' ],
                    'SERVICE-LOGIC',
                    1,
                    'Bad [ADD_CART] Request. Missing or invalid song-ID [Out of client Request]'
                );
            break;

        case 'remove_cart':

            //  parameter check[ existence + sanitization ]
            if( isset( $_POST[ 'song-id' ]) && is_numeric( $_POST[ 'song-id' ]) && $_POST[ 'song-id' ] > -1 )
                echo remove_cart( $_POST[ 'song-id' ]);  //  returns the number of songs inside the cart
            else
                throw new LogException(
                    [ 'SERVICE-ANALYSIS' ],
                    'SERVICE-LOGIC',
                    1,
                    'Bad [REMOVE_CART] Request. Missing or invalid song-ID [Out of client Request]'
                );
            break;

        case 'get_cart':

            echo json_encode( get_cart() );
            break;

        case 'order':

            //  parameters presence check
            if( !isset( $_POST[ 'CCN' ], $_POST[ 'CVV' ], $_POST[ 'name' ], $_POST[ 'surname' ], $_POST[ 'card-expire' ] ))
                throw new LogException(
                    [ 'SERVICE-ANALYSIS' ],
                    'SERVICE-LOGIC',
                    1,
                    'Bad [ORDER] Request. Missing some credit card data [Out of client Request]'
                );

            //  checking credit card information
            check_credit_card( $_POST[ 'CCN' ], $_POST[ 'CVV' ], $_POST[ 'name' ], $_POST[ 'surname' ], $_POST[ 'card-expire' ] );

            $cart = get_cart();

            if( count( $cart ) != 0 ){

                $connection = new sqlconnector();

                //  generation of unique transactionID
                do {

                    $transaction_id = randBytes();

                }while( $connection->checkTransaction( $transaction_id )); //  check id not already used

                //  caching of credit card information
                $_SESSION[ 'order' ] = [
                    "cart" => $cart,                     //  actual cart
                    "transactionId" => $transaction_id,  //  transactionID associated with the order
                    "CCN" => $_POST[ 'CCN' ],            //  credit card information for the payment
                    "CVV" => $_POST[ 'CVV' ],
                    "name" => $_POST[ 'name' ],
                    "surname" => $_POST[ 'surname' ],
                    "card-expire" => $_POST[ 'card-expire' ],
                    "transaction-expire" => time() + 120  //  expire of the order[2m]
                ];

                //  extraction of titles and total price for user confirm advertisement
                $price = 0;
                $songs = [];
                foreach( $cart as $song ){

                    $price += $song['price'];
                    $songs[] = $song[ 'title' ];

                }

                echo json_encode([
                    "transactionID" => $transaction_id,
                    "price" => $price,
                    "cart" => $songs
                ]);

            }else
                throw new LogException(
                    [ 'SERVICE-ANALYSIS' ],
                    'SERVICE-LOGIC',
                    1,
                    'Bad [ORDER] Request. No elements into the cart [Out of client Request]'
                );
            break;

        case 'buy':

            //  parameters presence check
            if( !isset( $_POST[ 'transactionID' ], $_SESSION[ 'order' ]))
                throw new LogException(
                    [ 'SERVICE-ANALYSIS' ],
                    'SERVICE-LOGIC',
                    2,
                    'Bad [BUY]. Missing request field[Out of client Request]'
                );

            //  checking transactionID is valid[32 bytes + only digits and chars]
            $pure_id = sanitize_transactionID( $_POST[ 'transactionID' ]);

            //  checking transactionID is equal to the one stored during the order
            if( strncmp( $_SESSION[ 'order' ][ 'transactionId' ], $pure_id, 32 ) != 0 )
                throw new LogException(
                    [ 'SERVICE-ANALYSIS' ],
                    'SERVICE-LOGIC',
                    2,
                    'Bad [BUY]. Invalid transactionID, not matching current order[ ' . $_SESSION[ 'order' ][ 'transactionId' ] . ':' . $pure_id . ']'
                );

            //  checking the transactionID has not been used from the time of the order and its usage[TOCTOU]
            $connection = new sqlconnector();
            if( $connection->checkTransaction( $pure_id ))
                throw new LogException(
                    [ 'SERVICE-ANALYSIS' ],
                    'SERVICE-LOGIC',
                    2,
                    'Bad payment request. Transaction id already present ' . $pure_id
                );

            //  verification order is not too old
            if( $_SESSION[ 'order' ][ 'transaction-expire' ] > time() ){

                $cart = $_SESSION[ 'order' ][ 'cart' ];

                //  user-id checked into check_authentication()[ line 75 ]
                foreach( $cart as $song ){

                    $connection->addPayment(
                        $_SESSION[ 'order' ][ 'transactionId' ],
                        $_SESSION[ 'user-id' ],
                        $song[ 'song-id' ],
                        $song[ 'price' ],
                        $_SESSION[ 'order' ][ 'CCN' ],
                        $_SESSION[ 'order' ][ 'name' ],
                        $_SESSION[ 'order' ][ 'surname' ]
                    );
                    remove_cart( $song[ 'song-id' ] );

                }

            }else
                throw new LogException(
                    [ 'SERVICE-ANALYSIS' ],
                    'SERVICE-LOGIC',
                    1,
                    'Bad [BUY] Request. Order expired by ' . (time() - $_SESSION[ 'order' ][ 'transaction-expire' ]) . '. Transaction aborted'
                );
            break;

        case 'download':

            //  parameter check[ existence + sanitization ]
            if( !isset( $_GET[ 'song-id' ]) || !is_numeric( $_GET[ 'song-id' ]) || $_GET[ 'song-id' ] < 0 )
                throw new LogException(
                    [ 'SERVICE-ANALYSIS' ],
                    'SERVICE-LOGIC',
                    1,
                    'Bad [DOWNLOAD] Request. Missing or invalid songID [Out of client Request]'
                );

            $connection = new sqlconnector();

            //  checking the user has bought the song
            //  user-id checked into check_authentication()[ line 75 ]
            $songTitle = $connection->checkSong( $_SESSION[ 'user-id' ], $_GET[ 'song-id' ]);
            $filename = get_file( $songTitle );

            //  get file type and set it as Content Type
            header('Content-type: audio/mpeg');
            header("Content-Transfer-Encoding: Binary");

            //  use Content-Disposition: attachment to specify the filename
            header( 'Content-Disposition: attachment; filename="' . basename( $filename ).'"' );

            //  no cache
            header( 'Expires: 0' );
            header( 'Cache-Control: must-revalidate' );
            header('Pragma: no-cache');

            //  define file size
            header( 'Content-Length: ' . filesize( $filename ));

            ob_clean();

            readfile( $filename );
            flush();
            break;

        case 'logout':
            session_destroy();
            unset( $_COOKIE[ 'auth' ]);   //  manual clean of cookie
            setcookie( 'auth', '', time() - 3600, '/' ); // empty value and old timestamp -> browser drops cookie from memory

            header( "index.php", true, 301 );  //  redirection to the login page
            break;

        default:
            throw new LogException(
                [ 'SERVICE-ANALYSIS' ],
                'SERVICE-LOGIC',
                1,
                'Bad Request invalid field "type"[Out of client Request]'
            );
    }

    exit();


}catch( LogException $e ){

    /*session_destroy();
    unset( $_COOKIE[ 'auth' ]);   //  manual clean of cookie
    setcookie( 'auth', '', time() - 3600, '/' ); // empty value and old timestamp -> browser drops cookie from memory

    header( "index.php", true, 301 );  //  redirection to the login page*/
    writeLog( $e );

}
