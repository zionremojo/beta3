<?php

$countryCode = $_POST['countryCode'];

// Load country borders GeoJSON data
$geojson = file_get_contents('../data/countryBorders.geo.json');
$geoData = json_decode($geojson, true);

// Initialize the response array
$response = [
    'status' => ['name' => 'error', 'code' => 400, 'description' => 'Country not found'],
    'data' => []
];

// Find the country in the GeoJSON data by matching the country code
foreach ($geoData['features'] as $feature) {
    if ($feature['properties']['iso_a2'] === $countryCode) {
        // Extract the country feature and its bounds
        $response['data'] = $feature;

        // Calculate the bounding box (north, south, east, west) from the coordinates
        $coordinates = $feature['geometry']['coordinates'];

        // Flatten the coordinates array to get all latitude and longitude values
        $flattenedCoords = [];
        array_walk_recursive($coordinates, function($coord) use (&$flattenedCoords) {
            $flattenedCoords[] = $coord;
        });

        // Extract latitude and longitude values
        $lats = [];
        $lngs = [];
        for ($i = 0; $i < count($flattenedCoords); $i += 2) {
            $lngs[] = $flattenedCoords[$i];   // Longitude is first in GeoJSON
            $lats[] = $flattenedCoords[$i + 1]; // Latitude follows
        }

        // Calculate the bounding box (north, south, east, west)
        $response['data']['bounds'] = [
            'north' => max($lats),
            'south' => min($lats),
            'east' => max($lngs),
            'west' => min($lngs),
        ];

        // Set status to success
        $response['status'] = ['name' => 'ok', 'code' => 200, 'description' => 'success'];
        break;
    }
}

// Send the response
header('Content-Type: application/json; charset=UTF-8');
echo json_encode($response);

?>
