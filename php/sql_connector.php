<<?php

include_once( 'data_manager.php' );

/**
 * Module in charge of establishing a connection with a local mySQL database and perform all the operations
 * of creation/update/retrieval of the data
 */
class sqlconnector{

    private $connection;            //  connection to the local database
    private static $conf = null;    //  configuration of the subsystem

    /**
     * Constructor of the class. Generates a connection with the configured mySQL database
     */
    function __construct() {

        //  retrieval of configuration properties for security improve
        //  [git repo attacks/code security]
        if( sqlconnector::$conf == null )
            sqlconnector::$conf = getConfiguration();

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

    /**
     * Gives a captcha starting from an index. It is used to select randomly a captcha with the usage of getMaxCaptcha
     * @param string $id Position of the captcha the retrieve
     * @throws LogException If the system is unable to retrieve the given captcha
     * @return array      A dictionary containing the captcha information[src,clue,mask]
     */
    public function getCaptcha( string $id ): array{

        try {

            $stmt = $this->connection->prepare('SELECT src, clue, mask FROM captchas LIMIT ?,1');
            $stmt->bind_param("i", $id);

            if ($stmt->execute()) {

                $stmt->bind_result($src, $clue, $mask); //  one result, captchaID is PK
                $stmt->fetch();
                $result = ['src' => $src, 'clue' => $clue, 'mask' => $mask];

                if ($result['src'] == null || strlen($result['src']) == 0)
                    throw new LogException(
                        ['INTERNAL-ERROR'],
                        'SQL-CONNECTOR',
                        0,
                        'Unable to retrieve the captcha with id -> ' . $id
                    );

            } else {

                $stmt->close();
                throw new LogException(
                    ['INTERNAL-ERROR'],
                    'SQL-CONNECTOR',
                    0,
                    'Unable to execute the query for getting a captcha with id -> ' . $id
                );
            }

            return $result;

        }catch( Exception $e ){
            throw new LogException(
                ['INTERNAL-ERROR'],
                'SQL-CONNECTOR',
                3,
                'Unable to connect to the remote mySQL database'
            );
        }
    }

    /**
     * Retrieval of max captchaID[used by upper layer for random captcha selection between 0,maxID]
     * @throws LogException  If the system is unable to retrieve the max captcha ID
     * @return int The max captchaID retrievable
     */
    public function getMaxCaptcha(): int{

        try {
            $stmt = $this->connection->prepare('SELECT COUNT(*) as "max" FROM captchas' );

            if ($stmt->execute()) {

                $stmt->bind_result($max);  //  only one result(aggregation op. without grouping)
                $stmt->fetch();
                $stmt->close();

            } else {

                $stmt->close();
                throw new LogException(
                    ['INTERNAL-ERROR'],
                    'SQL-CONNECTOR',
                    3,
                    'Unable to execute the query for getting the max captcha value'
                );

            }

            return $max;

        }catch( Exception $e ){

            throw new LogException(
                ['INTERNAL-ERROR'],
                'SQL-CONNECTOR',
                3,
                'Unable to connect to the remote mySQL database'
            );

        }
    }

    /**
     * Registers a new user inside the database
     * @param string $username  username of the user to be registered into the database
     * @param string $password  password associated to the user
     * @param string $phone     Phone associated to the user
     * @throws LogException     If the system is unable to execute the request
     * @return bool      Returs true in case of success otherwise false[username/phone already present]
     */
    public function registerUser( string $username, string $password, string $phone ): bool{

        try {

            //  uniqueness of user and phone number defined as mysql constraints
            $stmt = $this->connection->prepare( 'INSERT INTO users VALUES( NULL, ?, ?, ? )' );
            $stmt->bind_param( "sss", $username, $password, $phone );

            $result = $stmt->execute();

            $stmt->close();
            return $result;

        }catch( Exception $e ){

            throw new LogException(
                ['INTERNAL-ERROR'],
                'SQL-CONNECTOR',
                3,
                'Unable to connect to the remote mySQL database'
            );
        }

    }

    /**
     * Performs the login by verifying the presence of a row into the result
     * Into the row there is the phone number which will be used into the next phase[OTP]
     * @param string $username Username by which performing the login
     * @param string $password Password associated with the username
     * @throws LogException    If the system is unable to execute the request
     * @return array True in case of success otherwise false
     */
    public function login( string $username, string $password ): array{

        try {

            $stmt = $this->connection->prepare( 'SELECT userid, phone FROM users WHERE username = ? AND password = ?' );
            $stmt->bind_param( "ss", $username, $password );

            if ($stmt->execute()) {

                $stmt->bind_result( $uID, $phone );  //  only one result
                $stmt->fetch();
                $stmt->close();

                if( $uID == null || strlen( $uID ) == 0)
                    throw new LogException(
                        [ 'USER-ERROR', 'BRUTE-FORCING' ],
                        'SQL-CONNECTOR',
                        2,
                        'Invalid login credentials. Login for user ' . $username . ' failed'
                    );

                return [ 'uID' => $uID, 'phone' => $phone ];

            }else{

                $stmt->close();
                throw new LogException(
                    ['INTERNAL-ERROR'],
                    'SQL-CONNECTOR',
                    3,
                    'Unable to connect to the remote mySQL database'
                );
            }

        }catch( Exception $e ){

            throw new LogException(
                ['INTERNAL-ERROR'],
                'SQL-CONNECTOR',
                3,
                'Unable to connect to the remote mySQL database'
            );
        }
    }

    /**
     * Performs the change of the user password
     * @param string $username Name of the username to which change the password
     * @param string $password New password to be set
     * @throws LogException    If the system is unable to execute the request
     * @return bool  Returns in case of success otherwise false
     */
    public function changePassword( string $username, string $password ): bool{

        try {
            $stmt = $this->connection->prepare("UPDATE users SET password=? WHERE username=?");
            $stmt->bind_param("ss", $password, $username);

            $result = $stmt->execute();

            $stmt->close();
            return $result;

        }catch( Exception $e ){

            $stmt->close();
            throw new LogException(
                ['INTERNAL-ERROR'],
                'SQL-CONNECTOR',
                3,
                'Unable to connect to the remote mySQL database'
            );

        }
    }

    //  Adds a song to the user archive by registering his payment
    public function addPayment( $transactionID, $userID, $musicID, $price ): bool{

        $stmt = $this->connection->prepare( "INSERT INTO purchases VALUES( NULL, ?,?,?, DEFAULT, ? )" );
        $stmt->bind_param( "iiss", $userID, $musicID, $price, $transactionID );

        $result = $stmt->execute();

        $stmt->close();
        return $result;

    }

    //  Retrieves music to be displayed
    public function getMusic( $type, $userID, $filter, $genre, $page ): array{

        $page *= 8;   //  songs grouped by pages containing 8 elements

        if( $type == 'default_search' ){   //  search into the user purchased songs

            $stmt = $this->connection->prepare(

                "SELECT musicid, title, artist, music.song, pic, null
                       FROM purchases INNER JOIN music ON purchases.song = music.musicid 
                       WHERE purchases.user = ? LIMIT ?,8"

            );
            $stmt->bind_param( "ii", $userID, $page );

        }else {  //  search into the songs collection

            if( $filter == null ){

                if( $genre == null ) {  //  filter not applied filter not applied

                    $stmt = $this->connection->prepare(

                        "SELECT musicid, title, artist, song, pic, price 
                               FROM music WHERE musicid NOT IN ( select song from purchases where user=? ) LIMIT ?,8"

                    );
                    $stmt->bind_param("ii", $userID, $page );

                }else{   //  filter not applied, genre applied

                    $stmt = $this->connection->prepare(

                        "SELECT musicid, title, artist, song, pic , price
                               FROM music 
                               WHERE genre = ? AND musicid NOT IN ( select song from purchases where user=? ) LIMIT ?,8"

                    );
                    $stmt->bind_param("sii", $genre, $userID, $page );

                }

            } else {

                $filter = '%' . $filter . '%';
                if( $genre == null ){  //  filter applied, genre not applied

                    $stmt = $this->connection->prepare(
                        "SELECT musicid, title, artist, song, pic, price 
                           FROM music
                           WHERE title LIKE ? OR artist LIKE ? AND musicid NOT IN ( select song from purchases where user=? ) LIMIT ?,8"
                    );
                    $stmt->bind_param("ssii", $filter, $filter, $userID, $page);

                }else{

                    $stmt = $this->connection->prepare(
                        "SELECT musicid, title, artist, song, pic, price 
                           FROM music
                           WHERE title LIKE ? OR artist LIKE ? AND genre = ? AND musicid NOT IN ( select song from purchases where user=? ) LIMIT ?,8"
                    );
                    $stmt->bind_param("sssii", $filter, $filter, $genre, $userID, $page );

                }

            }
        }
        $data = [];
        if( $stmt->execute() ){

            $stmt->bind_result( $musicID, $title, $artist, $song, $pic, $price );
            while( $stmt->fetch()) {

                $data[] = [
                    'songID' => $musicID,
                    'title' => $title,
                    'artist' => $artist,
                    'price' => $price,
                    'song' => $song,
                    'img' => $pic
                ];
            }
        }

        $stmt->close();
        return $data;

    }

    /**
     * Gets detailed information of a song given its id(used by cart management)
     * @throws LogException In case the songID isn't present inside the database
     */
    public function getSongInfo(int $songID ): array{

        $title = null;
        $artist = null;
        $price = null;
        $musicid = null;
        $stmt = $this->connection->prepare( "SELECT musicid, title, artist, price FROM music WHERE musicid = ?" );
        $stmt->bind_param( "i", $songID );

        if( $stmt->execute() ){

            $stmt->bind_result( $musicid, $title, $artist, $price );
            $stmt->fetch();

        }

        $stmt->close();
        if( $title == null || strlen($title) == 0 )
            throw new LogException(
                [ 'INTERNAL ERROR' ],
                'SQL-CONNECTOR',
                1,
                'Invalid songID request. Song ' . $songID . ' not present'
            );

        return [ "song-id" => $musicid, "title" => $title, "artist" => $artist, "price" => $price ];

    }
    /**
     * Retrieves the phone number associated with a user
     * @param string $username Name of the username to which change the password
     * @throws LogException    If the system is unable to execute the request
     * @return string The phone number of the user as a string
     */
    public function getUserPhone( string $username ): string{

        $phone = null;

        $stmt = $this->connection->prepare( "SELECT phone FROM users WHERE username = ?" );
        $stmt->bind_param( "s", $username );

        if( $stmt->execute() ){

            $stmt->bind_result( $phone );
            $stmt->fetch();

        }

        $stmt->close();
        if( $phone == null || strlen($phone) == 0 )
            throw new LogException(
                [ 'SERVICE-ANALYSIS', 'BRUTE-FORCING', 'USER-ERROR' ],
                'SQL-CONNECTOR',
                4,
                'Invalid phone number request. User ' . $username . ' not present'
            );
        return $phone;

    }
}