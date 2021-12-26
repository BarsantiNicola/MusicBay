<<?php

include( 'data_manager.php' );

//  Module in charge of establishing a connection to the local mySQL database
//  and perform all the operation of creation/update/retrieval of data
class sqlconnector{

    private $connection;  //  connection to the local database
    private static $conf = null;

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

    function __destruct(){

        mysqli_close( $this->connection );

    }

    //  Retrieval of a captcha information starting from an id[extracted randomly by upper layer]
    public function getCaptcha( $id ){

        $result = null;

        $stmt = $this->connection->prepare( "SELECT src, clue, mask FROM captchas LIMIT ?,1" );
        $stmt->bind_param( "i", $id );

        if( $stmt->execute()) {

            $stmt->bind_result( $src, $clue, $mask ); //  one result, captchaID is PK
            $stmt->fetch();
            $result = [ 'src' => $src, 'clue' => $clue, 'mask' => $mask ];

        }

        $stmt->close();
        return $result;

    }

    //  Retrieval of max captchaID[used by upper layer for random captcha selection between 0,maxID]
    public function getMaxCaptcha(){

        $max = null;

        $stmt = $this->connection->prepare( "SELECT COUNT(*) as 'max' FROM captchas" );

        if( $stmt->execute()) {

            $stmt->bind_result( $max );  //  only one result(aggregation op. without grouping)
            $stmt->fetch();

        }

        $stmt->close();
        return $max;

    }

    //  Registers a new user inside the database
    public function registerUser( $username, $password, $phone ){

        $stmt = $this->connection->prepare( "INSERT INTO users VALUES(NULL,?,?,?)" );
        $stmt->bind_param( "sss", $username, $password, $phone );

        $result = $stmt->execute();

        $stmt->close();
        return $result;

    }

    //  Performs the login by verifying the presence of a row into the result
    //  Into the row there is the phone number which will be used into the next phase[OTP]
    public function login( $username, $password ){

        $result = null;

        $stmt = $this->connection->prepare( "SELECT userid, phone FROM users WHERE username = ? AND password = ?" );
        $stmt->bind_param( "ss", $username, $password );

        if( $stmt->execute()){

            $stmt->bind_result( $uID, $phone );  //  only one result
            $stmt->fetch();
            $result = [ "uID" => $uID, "phone" => $phone ];

        }

        $stmt->close();
        return $result;

    }

    //  Performs the change of the user password
    public function changePassword( $username, $password ){

        $stmt = $this->connection->prepare( "UPDATE users SET password=? WHERE userid=?" );
        $stmt->bind_param( "ss", $password, $username );

        $result = $stmt->execute();

        $stmt->close();
        return $result;

    }

    //  Adds a song to the user archive by registering his payment
    public function addPayment( $userID, $musicID, $price ){

        $stmt = $this->connection->prepare( "INSERT INTO purchases VALUES( NULL, ?,?,?, DEFAULT )" );
        $stmt->bind_param( "iis", $userID, $musicID, $price );

        $result = $stmt->execute();

        $stmt->close();
        return $result;

    }

    //  Retrieves music to be displayed
    public function getMusic( $userID, $filter, $genre, $page ){

        $page *= 8;   //  songs grouped by pages containing 8 elements

        if( $userID != null ){   //  search into the user purchased songs

            $stmt = $this->connection->prepare(

                "SELECT musicid, title, artist, music.song, pic 
                       FROM purchases INNER JOIN music ON purchases.song = music.musicid 
                       WHERE purchases.user = ?"

            );
            $stmt->bind_param( "i", $userID );

        }else {  //  search into the songs collection

            if( $filter == null ){

                if( $genre == null ) {  //  filter not applied filter not applied

                    $stmt = $this->connection->prepare(

                        "SELECT musicid, title, artist, song, pic 
                               FROM music LIMIT ? 8"

                    );
                    $stmt->bind_param("i", $page);

                }else{   //  filter not applied, genre applied

                    $stmt = $this->connection->prepare(

                        "SELECT musicid, title, artist, song, pic 
                               FROM music 
                               WHERE genre = ? LIMIT ? 8"

                    );
                    $stmt->bind_param("si", $genre, $page );

                }

            } else {

                if( $genre == null ){  //  filter applied, genre not applied

                    $stmt = $this->connection->prepare(
                        "SELECT musicid, title, artist, song, pic 
                           FROM music
                           WHERE title LIKE ? OR artist LIKE ? LIMIT ? 8" );
                    $stmt->bind_param("ssi", $filter, $filter, $page);

                }else{

                    $stmt = $this->connection->prepare(
                        "SELECT musicid, title, artist, song, pic 
                           FROM music
                           WHERE title LIKE ? OR artist LIKE ? AND genre = ? LIMIT ? 8" );
                    $stmt->bind_param("sssi", $filter, $filter, $genre, $page );

                }

            }
        }

        $data = [];
        if( $stmt->execute() ){

            $stmt->bind_result( $musicID, $title, $artist, $song, $pic );
            while( $stmt->fetch())
                $data[] = [
                    'song-id' => $musicID,
                    'title' => $title,
                    'artist' => $artist,
                    'demo' => $song,
                    'pic' => $pic
                ];
        }

        $stmt->close();
        return $data;

    }

    //  Retrieves the phone number associated with a user
    private function getUserPhone( $username ){

        $phone = null;

        $stmt = $this->connection->prepare( "SELECT phone FROM users WHERE username = ?" );
        $stmt->bind_param( "s", $username );

        if( $stmt->execute() ){

            $stmt->bind_result( $phone );
            $stmt->fetch();

        }

        $stmt->close();
        return $phone;

    }
}