<?php

// =============================================
// Configuration
// =============================================

// Set your Client ID and Client Secret
// You can find these in the settings menu after logging in.  
$xClientId = '{client_id}';

// Don't reveal this secret to anyone!  
// If you lose the client secret, you can generate a new one in the settings menu.  
$xClientSecret = '{client_secret}';

// API Endpoint
$apiEndpoint = '{url}';

if (!$xClientId) {
    echo "------------------------<br>";
    echo "CLIENT_ID is not defined in .env<br>";
    echo "------------------------<br>";
    exit;
}
if (!$xClientSecret) {
    echo "------------------------<br>";
    echo "CLIENT_SECRET is not defined in .env<br>";
    echo "------------------------<br>";
    exit;
}
if (!$apiEndpoint) {
    echo "------------------------<br>";
    echo "API_ENDPOINT is not defined in .env<br>";
    echo "------------------------<br>";
    exit;
}

// =============================================
// Timestamp
// =============================================

// Generate the current epoch timestamp (in seconds)
$xTimestamp = time();

// =============================================
// Request Data
// =============================================

// Define the query string for a GET request (default is an empty string)
$queryString = 'skip=0&take=1';

// Define the JSON body for a POST request (default is an empty object)
$body = new stdClass();
/*
POST data for create a customer
$body['bank_uuid'] = 'xx';
$body['bank_account_number'] = 'xx';
$body['bank_account_name'] = 'xx';
$body['bank_account_name_en'] = 'xx';
$body['status'] = 'active';
*/
$requestBody = json_encode($body);

// =============================================
// Signature Generation
// =============================================

// Combine values to construct a unique string for the signature
$combinedString = implode('|', [$xClientId, $xTimestamp, $requestBody, $queryString]);

// Generate the HMAC SHA-256 signature using the Client Secret
$xSignature = hash_hmac('sha256', $combinedString, $xClientSecret);

// =============================================
// Debug Output
// =============================================

// Print the values for debugging
echo "------------------------<br>";
echo "x-client-id: $xClientId<br>";
echo "x-signature: $xSignature<br>";
echo "x-timestamp: $xTimestamp<br>";
echo "------------------------<br>";

// =============================================
// Make a request
// =============================================


$url = "$apiEndpoint/v1/client/customer?$queryString";
echo "<p>GET: $url</p>";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "x-client-id: $xClientId",
    "x-signature: $xSignature",
    "x-timestamp: $xTimestamp",
    "Content-Type: application/json"
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if (curl_errno($ch)) {
    echo "Error: " . curl_error($ch) . "<br>";
} else {
    if ($httpCode >= 200 && $httpCode < 300) {
        echo "<p>Success:</p>";
        echo '<pre>' . json_encode(json_decode($response), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "</pre><br>";
    } else {
        echo "Error HTTP status code: $httpCode<br>";
        echo "Error message: $response<br>";
    }
}

curl_close($ch);
