<?php
header('Content-Type: application/json');

$city = $_POST['city'] ?? null;

if ($city) {
    $apiKey = 'abd33997af134aaca8f110531240909';
    $url = "http://api.weatherapi.com/v1/forecast.json?key=$apiKey&q=" . urlencode($city) . "&days=3";

    // Initialize cURL
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    // Execute cURL request
    $response = curl_exec($curl);
    
    if ($response === false) {
        echo json_encode([
            'status' => ['name' => 'fail', 'message' => curl_error($curl)]
        ]);
        exit;
    }

    // Close cURL session
    curl_close($curl);

    // Decode the response
    $data = json_decode($response, true);

    if ($data) {
        // Format data for the modal (today + next 2 days)
        $weatherData = [
            'today' => [
                'condition' => $data['forecast']['forecastday'][0]['day']['condition']['text'],
                'icon' => $data['forecast']['forecastday'][0]['day']['condition']['icon'],
                'maxTemp' => $data['forecast']['forecastday'][0]['day']['maxtemp_c'],
                'minTemp' => $data['forecast']['forecastday'][0]['day']['mintemp_c']
            ],
            'day1' => [
                'date' => date('D jS', strtotime($data['forecast']['forecastday'][1]['date'])),
                'icon' => $data['forecast']['forecastday'][1]['day']['condition']['icon'],
                'maxTemp' => $data['forecast']['forecastday'][1]['day']['maxtemp_c'],
                'minTemp' => $data['forecast']['forecastday'][1]['day']['mintemp_c']
            ],
            'day2' => [
                'date' => date('D jS', strtotime($data['forecast']['forecastday'][2]['date'])),
                'icon' => $data['forecast']['forecastday'][2]['day']['condition']['icon'],
                'maxTemp' => $data['forecast']['forecastday'][2]['day']['maxtemp_c'],
                'minTemp' => $data['forecast']['forecastday'][2]['day']['mintemp_c']
            ],
            'lastUpdated' => $data['current']['last_updated']
        ];

        echo json_encode([
            'status' => ['name' => 'ok'],
            'data' => $weatherData
        ]);
    } else {
        echo json_encode([
            'status' => ['name' => 'fail', 'message' => 'No data returned from Weather API']
        ]);
    }
} else {
    echo json_encode([
        'status' => ['name' => 'fail', 'message' => 'City not provided']
    ]);
}
?>
