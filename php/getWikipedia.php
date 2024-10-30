<?php
$country = isset($_GET['country']) ? $_GET['country'] : '';

if ($country) {
    $wikiUrl = "https://en.wikipedia.org/api/rest_v1/page/summary/" . urlencode($country);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $wikiUrl);
    $response = curl_exec($ch);
    curl_close($ch);

    if ($response) {
        $wikiData = json_decode($response, true);
        if (isset($wikiData['extract'])) {
            echo json_encode($wikiData);
        } else {
            echo json_encode(['error' => 'No Wikipedia summary found.']);
        }
    } else {
        echo json_encode(['error' => 'Unable to fetch Wikipedia summary.']);
    }
} else {
    echo json_encode(['error' => 'Invalid parameters.']);
}
?>
