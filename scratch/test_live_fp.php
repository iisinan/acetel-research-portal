<?php
// Test POST to live site forgot-password with actual user email
$ch = curl_init('https://aceteltms.nou.edu.ng/forgot-password');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, __DIR__ . '/cookies.txt');
$response = curl_exec($ch);
curl_close($ch);

preg_match('/<input type="hidden" name="_token" value="([^"]+)"/', $response, $matches);
$token = $matches[1] ?? null;

if ($token) {
    $ch = curl_init('https://aceteltms.nou.edu.ng/forgot-password');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEFILE, __DIR__ . '/cookies.txt');
    curl_setopt($ch, CURLOPT_COOKIEJAR, __DIR__ . '/cookies.txt');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        '_token' => $token,
        'email' => 'admin@acetel.noun.edu.ng'
    ]));
    $finalHtml = curl_exec($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);

    echo "Final HTTP Status: " . $info['http_code'] . PHP_EOL;
    echo "Final URL: " . $info['url'] . PHP_EOL;
    
    // Check if error or status is present in HTML
    if (strpos($finalHtml, 'Request Received') !== false) {
        echo "RESULT: Success message 'Request Received' found in page!" . PHP_EOL;
    } elseif (strpos($finalHtml, 'We can\'t find a user') !== false) {
        echo "RESULT: 'We can\'t find a user' found in page!" . PHP_EOL;
    } else {
        echo "Page excerpt:" . PHP_EOL;
        // Print alert / error div if any
        preg_match('/<div class="mb-6.*?<\/div>/s', $finalHtml, $alert);
        echo ($alert[0] ?? substr($finalHtml, 0, 1000)) . PHP_EOL;
    }
}
