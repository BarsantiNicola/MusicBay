<?php

//  Exception class extension for making centralized logging mechanism
//  Every error produced during the service application will be signaled by a LogException caught by a central try-catch
class LogException extends Exception{

    private $severity;       //  severity of the possible attack
    private $vulnerability;  //  array of possible vulnerabilities the attacker wants to exploit
    private $routine;        //  service involved by the problem
    private $address;        //  ip address of the client which has made the request
    private $sessionID;      //  session id of the client
    private $timestamp;      //  timestamp of the encountered error

    function __construct( $vulnerability, $routine, $severity, $message ) {

        parent::__construct( $message );
        $this->vulnerability = $vulnerability;
        $this->severity = $severity;
        $this->routine = $routine;
        $this->address = $_SERVER['REMOTE_ADDR'];
        $this->sessionID = session_id();
        $this->timestamp = date('Y-m-d H:i:s');

    }

    //  GETTERS

    function getVulnerability(){
        return $this->vulnerability;
    }

    function getRoutine(){
        return $this->routine;
    }

    function getSeverity(){
        return $this->severity;
    }

    function getSessionID(){
        return $this->sessionID;
    }

    function getTimestamp(){
        return $this->timestamp;
    }

    function getAddress(){
        return $this->address;
    }
}