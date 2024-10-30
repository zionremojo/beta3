<?php
$lat = isset($_GET['lat']) ? $_GET['lat'] : '';
$lng = isset($_GET['lng']) ? $_GET['lng'] : '';

if ($lat && $lng) {
    $overpassUrl = "http://overpass-api.de/api/interpreter";
    $overpassQuery = '
        [out:json];
        node["leisure"="stadium"](around:200000,' . $lat . ',' . $lng . ');
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
        $stadiums = [];

        if (isset($data['elements'])) {
            foreach ($data['elements'] as $stadium) {
                $stadiums[] = [
                    'lat' => $stadium['lat'],
                    'lng' => $stadium['lon'],
                    'name' => $stadium['tags']['name'] ?? 'Unnamed Stadium'
                ];
            }
        }

        echo json_encode($stadiums);
    } else {
        echo json_encode(['error' => 'Unable to fetch stadium data.']);
    }
} else {
    echo json_encode(['error' => 'Invalid parameters.']);
}
?>
