<?php
header('Content-Type: application/json');

$countryCode = $_POST['countryCode'] ?? null;

if ($countryCode) {
    // Initialize cURL session
    $curl = curl_init();

    // RestCountries API endpoint to fetch country details
    $url = 'https://restcountries.com/v3.1/alpha/' . $countryCode;

    // cURL options
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    // Execute the cURL request
    $response = curl_exec($curl);

    // Check for cURL errors
    if ($response === false) {
        echo json_encode([
            'status' => [
                'name' => 'fail',
                'message' => curl_error($curl)
            ]
        ]);
        exit;
    }

    // Close the cURL session
    curl_close($curl);

    // Decode the JSON response
    $data = json_decode($response, true);

    // Format the response data for the modal
    if ($data && is_array($data)) {
        $countryData = [
            'name' => $data[0]['name']['common'],
            'capital' => $data[0]['capital'][0] ?? 'N/A',
            'population' => $data[0]['population'],
            'currency' => array_keys($data[0]['currencies'])[0],
            'continent' => $data[0]['continents'][0],
            'drivingSide' => $data[0]['car']['side'],
            'flag' => $data[0]['flags']['png']
        ];

        echo json_encode([
            'status' => ['name' => 'ok'],
            'data' => $countryData
        ]);
    } else {
        echo json_encode([
            'status' => ['name' => 'fail'],
            'message' => 'Country data not found.'
        ]);
    }
} else {
    echo json_encode([
        'status' => ['name' => 'fail'],
        'message' => 'No country code provided.'
    ]);
}
