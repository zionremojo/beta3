<?php
    $countryCode = $_POST['countryCode'];

    $username = 'zionremojo';

    $url = "http://api.geonames.org/searchJSON?country=$countryCode&featureClass=P&maxRows=1000&username=$username";

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

    $response = curl_exec($curl);
    curl_close($curl);

    $data = json_decode($response, true);

    if (isset($data['geonames'])) {
        echo json_encode(['status' => ['name' => 'ok'], 'data' => $data['geonames']]);
    } else {
        echo json_encode(['status' => ['name' => 'error'], 'message' => 'No cities found']);
    }
?>
