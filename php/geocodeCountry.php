<?php
  $apiKey = "6d6792ea7c4345468c5f112956049474";

  if (isset($_POST['lat']) && isset($_POST['lng'])) {
    $lat = $_POST['lat'];
    $lng = $_POST['lng'];

    // Make a request to the OpenCage API for reverse geocoding
    $url = "https://api.opencagedata.com/geocode/v1/json?q=$lat+$lng&key=$apiKey";
    $response = file_get_contents($url);
    $data = json_decode($response, true);

    if (isset($data['results'][0]['components']['country_code'])) {
      $countryCode = strtoupper($data['results'][0]['components']['country_code']);
      echo json_encode([
        'status' => ['name' => 'ok'],
        'data' => ['countryCode' => $countryCode]
      ]);
    } else {
      echo json_encode([
        'status' => ['name' => 'fail'],
        'data' => []
      ]);
    }
  } else {
    echo json_encode([
      'status' => ['name' => 'fail'],
      'data' => []
    ]);
  }
?>
