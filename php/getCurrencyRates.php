<?php
$app_id = 'c362b071f03a4d1c88c9c178b2cb4a9b';
$geonames_username = 'zionremojo';

// Fetch exchange rates
$oxr_url = "https://openexchangerates.org/api/latest.json?app_id=" . $app_id;
$ch = curl_init($oxr_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$json = curl_exec($ch);
curl_close($ch);
$oxr_latest = json_decode($json, true);

if (isset($_GET['countryCode'])) {
    $countryCode = $_GET['countryCode'];
    // Fetch country data from GeoNames API
    $geonames_url = "http://api.geonames.org/countryInfoJSON?country=" . $countryCode . "&username=" . $geonames_username;
    $ch = curl_init($geonames_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $json = curl_exec($ch);
    curl_close($ch);
    $country_data = json_decode($json, true);

    if (!empty($country_data['geonames'])) {
        $country_info = $country_data['geonames'][0];
        $oxr_latest['local_currency'] = $country_info['currencyCode'];
        $oxr_latest['local_currency_name'] = $country_info['currencyCode']; // GeoNames does not provide currency name, so using code
    } else {
        $oxr_latest['local_currency'] = 'N/A';
        $oxr_latest['local_currency_name'] = 'N/A';
    }
} else {
    $oxr_latest['local_currency'] = 'N/A';
    $oxr_latest['local_currency_name'] = 'N/A';
}

header('Content-Type: application/json');
echo json_encode($oxr_latest);
?>
