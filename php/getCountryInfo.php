<?php
$username = 'zionremojo';

if (isset($_GET['country'])) {
    $countryCode = $_GET['country'];

    // Fetch country data from GeoNames API
    $geonames_url = "http://api.geonames.org/countryInfoJSON?country=" . $countryCode . "&username=" . $username;
    $ch = curl_init($geonames_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $json = curl_exec($ch);
    curl_close($ch);
    $country_data = json_decode($json, true);

    if (!empty($country_data['geonames'])) {
        $country_info = $country_data['geonames'][0];
        $languages = explode(",", $country_info['languages']);
        $response = array(
            'name' => $country_info['countryName'],
            'capital' => $country_info['capital'],
            'currency' => $country_info['currencyCode'],
            'languages' => $languages
        );
    } else {
        $response = array('error' => 'Country data not found');
    }
} else {
    $response = array('error' => 'Country code not provided');
}

header('Content-Type: application/json');
echo json_encode($response);
?>
