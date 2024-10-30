<?php
$country = isset($_GET['country']) ? urlencode($_GET['country']) : null;
$countryCode = isset($_GET['countryCode']) ? urlencode($_GET['countryCode']) : null;
$username = 'zionremojo';

if (!$country || !$countryCode) {
    echo json_encode(['error' => 'Country name and country code are required']);
    exit;
}

// GeoNames API URL
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

// Extract population and area from GeoNames
if (isset($dataGeoNames['geonames'][0])) {
    $population = $dataGeoNames['geonames'][0]['population'] ?? 'N/A';
    $area = $dataGeoNames['geonames'][0]['areaInSqKm'] ?? 'N/A';
    $populationDensity = $area > 0 ? round($population / $area, 2) : 'N/A';
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

// Extract demonym from Rest Countries
if (isset($dataRestCountries[0])) {
    $demonym = $dataRestCountries[0]['demonyms']['eng']['m'] ?? 'N/A';
} else {
    $demonym = 'N/A';
}

$demographics = [
    'population' => $population,
    'area' => $area,
    'populationDensity' => $populationDensity,
    'demonym' => $demonym
];

echo json_encode($demographics);
?>
