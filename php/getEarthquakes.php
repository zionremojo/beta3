<?php
    $north = $_POST['north'];
    $south = $_POST['south'];
    $east = $_POST['east'];
    $west = $_POST['west'];

    $username = "zionremojo";
    $url = "http://api.geonames.org/earthquakesJSON?north=$north&south=$south&east=$east&west=$west&username=$username";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec($ch);
    curl_close($ch);

    echo $response;
?>
