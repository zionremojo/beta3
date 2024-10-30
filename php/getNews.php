<?php
$apiKey = 'pub_48098d03e0a00ba955ad13957872995c80c17';
$country = isset($_GET['country']) ? $_GET['country'] : '';

if ($country) {
    $newsUrl = "https://newsdata.io/api/1/news?apikey={$apiKey}&country={$country}";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $newsUrl);
    $response = curl_exec($ch);
    curl_close($ch);

    if ($response) {
        $newsData = json_decode($response, true);
        if (isset($newsData['results'])) {
            echo json_encode($newsData['results']);
        } else {
            echo json_encode(['error' => 'No news data found.']);
        }
    } else {
        echo json_encode(['error' => 'Unable to fetch news data.']);
    }
} else {
    echo json_encode(['error' => 'Invalid parameters.']);
}
?>
