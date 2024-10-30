<?php
$username = 'zionremojo'; // Your GeoNames username

// Get the country name from the request
$country = isset($_GET['country']) ? urlencode($_GET['country']) : null;

if (!$country) {
    die('Country name is required');
}

// GeoNames API URL for fetching Wikipedia information
$url = "http://api.geonames.org/wikipediaSearchJSON?q=$country&maxRows=1&username=$username";

// Initialize cURL session
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// Execute cURL request
$response = curl_exec($ch);
if ($response === false) {
    die('Error making API request: ' . curl_error($ch));
}

// Close cURL session
curl_close($ch);

// Return the response as JSON
header('Content-Type: application/json');
echo $response;
?>
