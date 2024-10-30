<?php
$lat = isset($_GET['lat']) ? $_GET['lat'] : '';
$lng = isset($_GET['lng']) ? $_GET['lng'] : '';

if ($lat && $lng) {
    $overpassUrl = "http://overpass-api.de/api/interpreter";
    $overpassQuery = '
        [out:json];
        (
          node["place"="city"](around:200000,' . $lat . ',' . $lng . ');
          node["place"="town"](around:200000,' . $lat . ',' . $lng . ');
        );
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
        $cities = [];

        if (isset($data['elements'])) {
            foreach ($data['elements'] as $city) {
                $cities[] = [
                    'lat' => $city['lat'],
                    'lng' => $city['lon'],
                    'name' => $city['tags']['name']
                ];
            }
        }

        echo json_encode($cities);
    } else {
        echo json_encode(['error' => 'Unable to fetch city data.']);
    }
} else {
    echo json_encode(['error' => 'Invalid parameters.']);
}
?>
