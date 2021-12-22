<?php
 
/*
* Write your logic to manage the data
* like storing data in database
*/
 
// POST Data
    $dat = [ 'test' => 'mytest', 'bho' => 'mybho'];

    header('Content-type: application/json');
    echo json_encode($dat);
 
?>