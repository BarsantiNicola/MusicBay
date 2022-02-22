<<?php

include_once( 'data_manager.php' );   //  EXCEPTION RAISED FOR LOG FUNCTIONALITIES
include_once( 'security.php' );       //  SECURITY FUNCTIONALITIES[SANITIZATION/RANDOMIZATION]

/**
 * Module in charge of establishing a connection with a local mySQL database and perform all the operations
 * of creation/update/retrieval of the data
 */
class sqlconnector{

    private $connection;             //  connection to the local database
    private static $conf = null;     //  configuration of the subsystem
    private static $dataConf = null; //  configuration of the data management

    /**
     * Constructor of the class. Generates a connection with the configured mySQL database
     */
    function __construct() {

        //  retrieval of configuration properties for security improve
        //  [git repo attacks/code security]
        if( sqlconnector::$conf == null )
            sqlconnector::$conf = getConfiguration( 'conf' );

        if( sqlconnector::$dataConf == null )
            sqlconnector::$dataConf = getConfiguration( 'data' );

        //  creation of a connection with the database
        $this->connection = new mysqli(
            "localhost",
            sqlconnector::$conf->user,
            sqlconnector::$conf->password,
            sqlconnector::$conf->db
        );

    }

    /**
     * Destructor of the class. Closes the connection with the remote mySQL database
     */
    function __destruct(){

        mysqli_close( $this->connection );

    }

    /**  Registers a new user inside the database
     * @param  string $username  Username of the user to be registered into the database
     * @param  string $password  Password associated to the user
     * @param  string $phone     Phone associated to the user
     * @throws LogException      If the system is unable to execute the request
     * @return bool              Returns true in case of success otherwise false[username/phone already present]
     */
    public function registerUser( string $username, string $password, string $phone ): bool{

        try {

            $options = [
                'cost' => 12,
            ];

            $password = password_hash( $password, PASSWORD_BCRYPT, $options );

            //  uniqueness of user and phone number defined as mysql constraints
            $stmt = $this->connection->prepare( 'INSERT INTO users VALUES( NULL, ?, ?, ? )' );
            $stmt->bind_param( "sss", $username, $password, $phone );

            $result = $stmt->execute();

            $stmt->close();
            return $result;

        }catch( Exception $e ){

            throw new LogException(
                [ 'INTERNAL-ERROR' ],
                'SQL-CONNECTOR',
                0,
                'Unable to connect to the remote mySQL database to register new user ' . $username
            );
        }

    }

    /**
     * Performs the login by verifying the presence of a row into the result
     * Into the row there is the phone number which will be used into the next phase[OTP]
     *
     * @param  string $username  Username by which performing the login
     * @param  string $password  Password associated with the username
     * @throws LogException      If the system is unable to execute the request or the credentials are invalid
     * @return array             Return an array containing the userID and the phone of the user(sanitized)
     */
    public function login( string $username, string $password ): array{

        try {

            $stmt = $this->connection->prepare( 'SELECT userid, phone, password FROM users WHERE BINARY(username) = ?' );
            $stmt->bind_param( "s", $username );

            if( $stmt->execute() ){

                $stmt->bind_result( $uID, $phone, $storedPassword );  //  only one result
                $stmt->fetch();
                $stmt->close();

                if( $uID == null || strlen( $uID ) == 0 )
                    throw new LogException(
                        [ 'USER-ERROR', 'BRUTE-FORCING' ],
                        'SQL-CONNECTOR',
                        1,
                        'Invalid login credentials. User ' . $username . ' not present'
                    );

                if( password_verify( $password, $storedPassword ))
                    
                    return [ 'uID' => sanitize_id( htmlspecialchars( $uID )), 'phone' => sanitize_phone( htmlspecialchars( $phone ))];
                else
                    throw new LogException(
                        [ 'USER-ERROR', 'BRUTE-FORCING' ],
                        'SQL-CONNECTOR',
                        1,
                        'Invalid login trial for ' . $username . '. Password not correct'
                    );

            }else{

                $stmt->close();
                throw new LogException(
                    [ 'INTERNAL-ERROR' ],
                    'SQL-CONNECTOR',
                    0,
                    'Unable to connect to the remote mySQL database to perform login of ' . $username . ' -> ' . $password
                );
            }

        }catch( LogException $e ){

            throw $e;

        }catch( Exception $e ){

            throw new LogException(
                [ 'INTERNAL-ERROR' ],
                'SQL-CONNECTOR',
                0,
                'Unable to connect to the remote mySQL database to perform login of ' . $username . ' -> ' . $password
            );
        }
    }

    /**  Performs the change of the user password if the oldPassword match
     * @param  int    $userID        Id of the user in which apply the password change
     * @param  string $password      New password to be set
     * @throws LogException          If the system is unable to execute the request
     * @return bool                  Returns true in case of success otherwise false
     */
    public function changePassword( int $userID, string $password ): bool{

        try {

            $options = [
                'cost' => 12,
            ];

            $password = password_hash( $password, PASSWORD_BCRYPT, $options );

            $stmt = $this->connection->prepare( 'UPDATE users SET password=? WHERE userid=?' );
            $stmt->bind_param( "si", $password, $userID );

            $result = $stmt->execute();

            $stmt->close();
            return $result;

        }catch( Exception $e ){

            throw new LogException(
                [ 'INTERNAL-ERROR' ],
                'SQL-CONNECTOR',
                0,
                'Unable to connect to the remote mySQL database for password change for user ' . $username
            );

        }
    }

    /**  Adds a song to the user archive by registering his payment
     * @param  string $transactionID  Id of the recorded transaction
     * @param  int    $userID         UserID representing the user
     * @param  int    $musicID        MusicID associated with the music to be acquired
     * @param  string $price          Price payed to buy the song
     * @throws LogException           If the system is unable to execute the request
     * @return bool                   Returns true in case of success otherwise false
     */
    public function addPayment( string $transactionID, int $userID, int $musicID, string $price, string $cnn, string $name, string $surname ): bool{

        try {

            $stmt = $this->connection->prepare( 'INSERT INTO purchases VALUES( NULL, ?,?,?, DEFAULT, ?, ?, ?, ? )' );
            $stmt->bind_param( "iisssss", $userID, $musicID, $price, $transactionID, $cnn, $name, $surname );

            $result = $stmt->execute();

            $stmt->close();
            return $result;

        }catch( Exception $e ){

            throw new LogException(
                [ 'INTERNAL-ERROR' ],
                'SQL-CONNECTOR',
                0,
                'Unable to connect to the remote mySQL database for add payment to user ' . $userID
            );

        }
    }

    /**  Checks if a songs exists and can be purchased by a user
     * @param  int $userId  Id of the user which wants to buy the song
     * @param  int $songId  Id of the music to buy
     * @throws LogException If the system is unable to execute the request
     * @return string       Returns the title of the song(sanitized)
     */
    public function checkSong( int $userId, int $songId ): string{

        try {

            $stmt = $this->connection->prepare('SELECT music.song FROM music JOIN purchases ON purchases.song = music.musicid WHERE purchases.user=? AND music.musicid=?' );
            $stmt->bind_param("ii", $userId, $songId);

            if ($stmt->execute()) {

                $stmt->bind_result( $title );  //  only one result
                $stmt->fetch();
                $stmt->close();

                if( $title == null || strlen( $title ) == 0 )
                    throw new LogException(
                        [ 'MUSIC-STOLE-TRIAL', 'SERVICE-ANALYSIS' ],
                        'SQL-CONNECTOR',
                        3,
                        'Invalid song check. The song ' . $songId . ' not found on user ' . $userId
                    );

                return htmlspecialchars( $title );

            } else
                throw new LogException(
                    [ 'INTERNAL-ERROR' ],
                    'SQL-CONNECTOR',
                    0,
                    'Error during the execution of a song check on user ' . $userId . ' for song ' . $songId
                );

        }catch( LogException $e ){

            throw $e;

        }catch( Exception $e ){

            throw new LogException(
                [ 'INTERNAL-ERROR' ],
                'SQL-CONNECTOR',
                0,
                'Unable to connect to the remote mySQL database for checking song presence -> ' . $songId
            );

        }
    }

    /**  Searches music inside the database
     * @param  string $type    Type of search to execute[default_search/search] -> user's songs search/all songs search
     * @param  int    $userID  Id of the user in which search music(for default_search)
     * @param  string $filter  Filter to be applied on the song(search)
     * @param  string $genre   Genre of the music(search)
     * @param  int    $page    Results are returns as sets(pages) of 8 elements
     * @throws LogException    If the system is unable to execute the request
     * @return array           Returns an array containing a set of elements describing songs(sanitized)
     */
    public function getMusic( string $type, int $userID, string $filter, string $genre, int $page ): array{
        try{

            $page *= 8;   //  songs grouped by pages containing 8 elements

            if( $type == 'default_search' ){   //  search into the user purchased songs

                $stmt = $this->connection->prepare(

                    'SELECT musicid, title, artist, music.song, pic, null
                       FROM purchases INNER JOIN music ON purchases.song = music.musicid 
                       WHERE purchases.user = ? LIMIT ?,8'

                );
                $stmt->bind_param( "ii", $userID, $page );

            }else{  //  search into the songs collection

                if( $filter == null ){

                    if( $genre == null ){  //  filter not applied filter not applied

                        $stmt = $this->connection->prepare(

                            'SELECT musicid, title, artist, song, pic, price 
                               FROM music WHERE musicid NOT IN ( select song from purchases where user=? ) LIMIT ?,8'

                        );

                        $stmt->bind_param( "ii", $userID, $page );

                    }else{   //  filter not applied, genre applied

                        $stmt = $this->connection->prepare(

                            'SELECT musicid, title, artist, song, pic , price
                               FROM music 
                               WHERE genre = ? AND musicid NOT IN ( select song from purchases where user=? ) LIMIT ?,8'

                        );
                        $stmt->bind_param( "sii", $genre, $userID, $page );

                    }

                }else{

                    $filter = '%' . $filter . '%';  //  like search
                    if( $genre == null ){  //  filter applied, genre not applied

                        $stmt = $this->connection->prepare(
                            'SELECT musicid, title, artist, song, pic, price 
                           FROM music
                           WHERE title LIKE ? OR artist LIKE ? AND musicid NOT IN ( select song from purchases where user=? ) LIMIT ?,8'
                        );
                        $stmt->bind_param( "ssii", $filter, $filter, $userID, $page );

                    }else{  //  filter applied, genre applied

                        $stmt = $this->connection->prepare(
                            'SELECT musicid, title, artist, song, pic, price 
                           FROM music
                           WHERE title LIKE ? OR artist LIKE ? AND genre = ? AND musicid NOT IN ( select song from purchases where user=? ) LIMIT ?,8'
                        );
                        $stmt->bind_param( "sssii", $filter, $filter, $genre, $userID, $page );

                    }

                }
            }

            $data = [];
            if( $stmt->execute() ){

                $stmt->bind_result( $musicID, $title, $artist, $song, $pic, $price );

                while( $stmt->fetch() ) {

                    if( $title == null || strlen( $title ) == 0 ) {

                        $stmt->close();
                        return $data;

                    }

                    $data[] = [
                        'songID' => htmlspecialchars( $musicID ),
                        'title'  => htmlspecialchars( $title ),
                        'artist' => htmlspecialchars( $artist ),
                        'price'  => htmlspecialchars( $price ),
                        'song'   => sanitize_source( sqlconnector::$dataConf->demo . htmlspecialchars( $song ), 'demo' ),
                        'img'    => sanitize_source( sqlconnector::$dataConf->img . htmlspecialchars( $pic ), 'img' )
                    ];
                }
            }

            $stmt->close();
            return $data;

        }catch( LogException $e ){

            throw $e;

        }catch( Exception $e ){

            throw new LogException(
                [ 'INTERNAL-ERROR' ],
                'SQL-CONNECTOR',
                0,
                'Unable to connect to the remote execute music query: [' . $type . ':' . $userID . ':' . $filter . ':' . $genre . ':' . $page . ']'
            );

        }
    }

    /**  Checks if the transactionID is already assigned to another purchase
     * @throws LogException  In case of connection error
     */
    public function checkTransaction( string $transactionID ): bool{

        $result = 0;
        try{

            $stmt = $this->connection->prepare( 'SELECT count(*) FROM purchases WHERE transactionID = ?' );
            $stmt->bind_param( "s", $transactionID );

            if( $stmt->execute() ) {

                $stmt->bind_result( $result );
                $stmt->fetch();

            }

            if( $result > 0 )
                return true;
            else
                return false;

        }catch( Exception $e ){

            throw new LogException(
                [ 'INTERNAL-ERROR' ],
                'SQL-CONNECTOR',
                0,
                'Unable to connect to the remote execute music query: [' . $type . ':' . $userID . ':' . $filter . ':' . $genre . ':' . $page . ']'
            );

        }

    }

    /**  Gets detailed information of a song given its id(used by cart management)
     * @param  int $songID  Id of the music from which retrieve information
     * @throws LogException In case the songID isn't present inside the database
     */
    public function getSongInfo( int $songID ): array{

        $title = null;
        $artist = null;
        $price = null;
        $musicId = null;

        try {

            $stmt = $this->connection->prepare( 'SELECT musicid, title, artist, price FROM music WHERE musicid = ?' );
            $stmt->bind_param( "i", $songID );

            if( $stmt->execute() ) {

                $stmt->bind_result( $musicId, $title, $artist, $price );
                $stmt->fetch();

            }

            $stmt->close();

            if( $title == null || strlen( $title ) == 0 )
                throw new LogException(
                    [ 'INTERNAL ERROR' ],
                    'SQL-CONNECTOR',
                    1,
                    'Invalid songID request. Song ' . $songID . ' not present'
                );

            return [
                'song-id' => htmlspecialchars( $musicId ),
                'title'   => htmlspecialchars( $title ),
                'artist'  => htmlspecialchars( $artist ),
                'price'   => htmlspecialchars( $price )
            ];

        }catch( LogException $e ){

            throw $e;

        }catch( Exception $e ){

            throw new LogException(
                [ 'INTERNAL-ERROR' ],
                'SQL-CONNECTOR',
                0,
                'Unable to connect to make songID request. Song ' . $songID . ' not present'
            );

        }
    }
    /**
     * Retrieves the phone number associated with a user
     *
     * @param  string $username Name of the username to which change the password
     * @throws LogException     If the system is unable to execute the request
     * @return string           The phone number of the user as a string
     */
    public function getUserPhone( string $username ): string{

        $phone = null;

        try {
            $stmt = $this->connection->prepare("SELECT phone FROM users WHERE username = ?");
            $stmt->bind_param("s", $username);

            if ($stmt->execute()) {

                $stmt->bind_result($phone);
                $stmt->fetch();

            }

            $stmt->close();

            if ( $phone == null || strlen( $phone ) == 0)
                throw new LogException(
                    [ 'SERVICE-ANALYSIS', 'BRUTE-FORCING', 'USER-ERROR' ],
                    'SQL-CONNECTOR',
                    1,
                    'Invalid phone number request. User ' . $username . ' not present'
                );

            return sanitize_phone( htmlspecialchars( $phone ));

        }catch( LogException $e ){
            throw $e;

        }catch( Exception $e ){

            throw new LogException(
                [ 'INTERNAL-ERROR' ],
                'SQL-CONNECTOR',
                0,
                'Unable to connect to database to obtain phone number. User ' . $username . ' not present'
            );

        }

    }
}