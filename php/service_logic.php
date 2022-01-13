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
                    echo json_encode( $connector->getMusic( 'default_search', $_SESSION[ 'user-id' ], null, null, $_POST[ 'page' ]));

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

            case 'buy':
                if( !isset( $_POST[ 'transactionID' ]))
                    throw new LogException(
                        [ 'SERVICE-ANALYSIS' ],
                        'SERVICE-LOGIC',
                        7,
                        'Bad Buy Request. Missing transactionID[Out of client Request]'
                    );

                $cart = get_cart();

                if( count( $cart ) != 0 ){
                    $connection = new sqlconnector();
                    foreach( $cart as $song ){
                        $connection->addPayment( $_POST[ 'transactionID' ], $_SESSION[ 'user-id' ], $song['song-id'], $song[ 'price' ]);
                        remove_cart( $song[ 'song-id' ] );
                    }
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
