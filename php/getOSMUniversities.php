<?php
$lat = isset($_GET['lat']) ? $_GET['lat'] : '';
$lng = isset($_GET['lng']) ? $_GET['lng'] : '';

if ($lat && $lng) {
    $overpassUrl = "http://overpass-api.de/api/interpreter";
    $overpassQuery = '
        [out:json];
        node["amenity"="university"](around:200000,' . $lat . ',' . $lng . ');
        out body;
    ';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $overpassUrl);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "data=" . urlencode($overpassQuery));
    $response = curl_exec($ch);
    curl_close($ch);

    if ($response) {
        $data = json_decode($response, true);
        $universities = [];

        if (isset($data['elements'])) {
            foreach ($data['elements'] as $university) {
                $universities[] = [
                    'lat' => $university['lat'],
                    'lng' => $university['lon'],
                    'name' => $university['tags']['name'] ?? 'Unnamed University'
                ];
            }
        }

        echo json_encode($universities);
    } else {
        echo json_encode(['error' => 'Unable to fetch university data.']);
    }
} else {
    echo json_encode(['error' => 'Invalid parameters.']);
}
?>
