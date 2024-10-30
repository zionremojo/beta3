<?php
$lat = isset($_GET['lat']) ? $_GET['lat'] : '';
$lng = isset($_GET['lng']) ? $_GET['lng'] : '';
$type = isset($_GET['type']) ? $_GET['type'] : '';
$username = 'zionremojo';

if ($lat && $lng && $type) {
    $url = "http://api.geonames.org/findNearbyPOIsOSMJSON?lat={$lat}&lng={$lng}&username={$username}";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    $response = curl_exec($ch);
    curl_close($ch);

    if ($response) {
        $data = json_decode($response, true);
        $filteredData = [];

        foreach ($data['poi'] as $poi) {
            switch ($type) {
                case 'cities':
                    if ($poi['fclass'] == 'P' && $poi['fcode'] == 'PPL') { // Filter for populated places (cities)
                        $filteredData[] = [
                            'lat' => $poi['lat'],
                            'lng' => $poi['lng'],
                            'name' => $poi['name']
                        ];
                    }
                    break;
                case 'stadiums':
                    if (strpos(strtolower($poi['name']), 'stadium') !== false) { // Filter for stadiums
                        $filteredData[] = [
                            'lat' => $poi['lat'],
                            'lng' => $poi['lng'],
                            'name' => $poi['name']
                        ];
                    }
                    break;
                case 'universities':
                    if (strpos(strtolower($poi['name']), 'university') !== false) { // Filter for universities
                        $filteredData[] = [
                            'lat' => $poi['lat'],
                            'lng' => $poi['lng'],
                            'name' => $poi['name']
                        ];
                    }
                    break;
            }
        }

        echo json_encode($filteredData);
    } else {
        echo json_encode(['error' => 'Unable to fetch POI data.']);
    }
} else {
    echo json_encode(['error' => 'Invalid parameters.']);
}
?>
