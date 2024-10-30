<?php

$countryCode = $_POST['countryCode'];  // Get the country code from the request

// Geonames API call to fetch airports
$url = "http://api.geonames.org/searchJSON?q=airport&country=" . $countryCode . "&maxRows=50&username=zionremojo";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$response = curl_exec($ch);
curl_close($ch);

$decodedResponse = json_decode($response, true);

// Prepare the response
$output = ['status' => ['name' => 'ok', 'code' => 200, 'description' => 'success'], 'data' => $decodedResponse['geonames']];
header('Content-Type: application/json');
echo json_encode($output);

?>
