<?php
session_start();
include( "LogException.php");

try{
    throw new LogException(["LOG-ERROR", "TEST-ERROR"], "TEST", 5, "MYTEST");

}catch( LogException $e ){
    echo $e->getMessage() . "\n";
    echo $e->getSessionID() . "\n";
    echo $e->getTimestamp() . "\n";
    echo $e->getAddress() . "\n";
    echo $e->getVulnerability() . "\n";
}