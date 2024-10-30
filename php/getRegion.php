<?php
$country = isset($_GET['country']) ? urlencode($_GET['country']) : null;
$countryCode = isset($_GET['countryCode']) ? urlencode($_GET['countryCode']) : null;
$username = 'zionremojo';

if (!$country || !$countryCode) {
    echo json_encode(['error' => 'Country name and country code are required']);
    exit;
}

// GeoNames API URL for country info
$geoNamesUrl = "http://api.geonames.org/countryInfoJSON?country=$countryCode&username=$username";

// Initialize cURL session for GeoNames API
$chGeoNames = curl_init();
curl_setopt($chGeoNames, CURLOPT_URL, $geoNamesUrl);
curl_setopt($chGeoNames, CURLOPT_RETURNTRANSFER, true);

// Execute cURL request for GeoNames API
$responseGeoNames = curl_exec($chGeoNames);
if ($responseGeoNames === false) {
    echo json_encode(['error' => 'Error making API request: ' . curl_error($chGeoNames)]);
    exit;
}

// Close cURL session
curl_close($chGeoNames);

$dataGeoNames = json_decode($responseGeoNames, true);

// Extract data from GeoNames
if (isset($dataGeoNames['geonames'][0])) {
    $continent = $dataGeoNames['geonames'][0]['continentName'] ?? 'N/A';
    $isoAlpha3 = $dataGeoNames['geonames'][0]['isoAlpha3'] ?? 'N/A';
    $countryGeonameId = $dataGeoNames['geonames'][0]['geonameId'] ?? null;
} else {
    echo json_encode(['error' => 'Country information not found in GeoNames']);
    exit;
}

// Rest Countries API URL
$restCountriesUrl = "https://restcountries.com/v3.1/name/$country";

// Initialize cURL session for Rest Countries API
$chRestCountries = curl_init();
curl_setopt($chRestCountries, CURLOPT_URL, $restCountriesUrl);
curl_setopt($chRestCountries, CURLOPT_RETURNTRANSFER, true);

// Execute cURL request for Rest Countries API
$responseRestCountries = curl_exec($chRestCountries);
if ($responseRestCountries === false) {
    echo json_encode(['error' => 'Error making API request: ' . curl_error($chRestCountries)]);
    exit;
}

// Close cURL session
curl_close($chRestCountries);

$dataRestCountries = json_decode($responseRestCountries, true);

// Extract subregion and calling code from Rest Countries
if (isset($dataRestCountries[0])) {
    $subregion = $dataRestCountries[0]['subregion'] ?? 'N/A';
    $callingCode = $dataRestCountries[0]['idd']['root'] ?? '';
    if (isset($dataRestCountries[0]['idd']['suffixes']) && !empty($dataRestCountries[0]['idd']['suffixes'])) {
        $callingCode .= $dataRestCountries[0]['idd']['suffixes'][0];
    }
} else {
    $subregion = 'N/A';
    $callingCode = 'N/A';
}

// GeoNames API URL for neighbors
$geoNamesNeighborsUrl = "http://api.geonames.org/neighboursJSON?geonameId=$countryGeonameId&username=$username";

// Initialize cURL session for GeoNames Neighbors API
$chGeoNamesNeighbors = curl_init();
curl_setopt($chGeoNamesNeighbors, CURLOPT_URL, $geoNamesNeighborsUrl);
curl_setopt($chGeoNamesNeighbors, CURLOPT_RETURNTRANSFER, true);

// Execute cURL request for GeoNames Neighbors API
$responseGeoNamesNeighbors = curl_exec($chGeoNamesNeighbors);
if ($responseGeoNamesNeighbors === false) {
    echo json_encode(['error' => 'Error making API request: ' . curl_error($chGeoNamesNeighbors)]);
    exit;
}

// Close cURL session
curl_close($chGeoNamesNeighbors);

$dataGeoNamesNeighbors = json_decode($responseGeoNamesNeighbors, true);

// Extract neighbors from GeoNames
if (isset($dataGeoNamesNeighbors['geonames'])) {
    $neighbors = implode(', ', array_column($dataGeoNamesNeighbors['geonames'], 'countryName'));
} else {
    $neighbors = 'N/A';
}

$region = [
    'continent' => $continent,
    'subregion' => $subregion,
    'neighbors' => $neighbors,
    'isoAlpha3' => $isoAlpha3,
    'callingCode' => $callingCode
];

echo json_encode($region);
?>
