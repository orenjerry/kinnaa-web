<?php
session_start();
$clientId = '288ef94232504494af46c0fa1da6ac3e';
$clientSecret = '99df0c42fa394111bc82c67dcef5d734';
$redirectUri = 'http://localhost/kinnaa-web/';

// Check if the user logged in
if (isset($_GET['code']) && isset($_GET['state'])) {
    // Verify the integrity of the authorization flow
    $stateReceived = $_GET['state'];
    if ($stateReceived === $_SESSION['state']) {
        $authorizationCode = $_GET['code'];

        // Store the authorization code in session
        $_SESSION['authorization_code'] = $authorizationCode;

        // Redirect to the same page to remove the query parameters from the URL
        header('Location: /kinnaa-web/spotify/');
        exit();
    } else {
        // Handle state mismatch error
        echo "State mismatch. Something went wrong.";
    }
}

//Taking Request Token
if (isset($_SESSION['authorization_code'])) {
    // Exchange authorization code for access token
    $authorizationCode = $_SESSION['authorization_code'];
    $tokenUrl = 'https://accounts.spotify.com/api/token';
    $tokenData = [
        'grant_type' => 'authorization_code',
        'code' => $authorizationCode,
        'redirect_uri' => $redirectUri,
        'client_id' => $clientId,
        'client_secret' => $clientSecret,
    ];
    $tokenOptions = [
        'http' => [
            'method' => 'POST',
            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
            'content' => http_build_query($tokenData),
        ],
    ];
    $tokenContext = stream_context_create($tokenOptions);
    $tokenResponse = file_get_contents($tokenUrl, false, $tokenContext);

    if ($tokenResponse === false) {
        session_destroy();
        die('Error exchanging authorization code for access token');
    }

    $tokenInfo = json_decode($tokenResponse, true);
    $accessToken = $tokenInfo['access_token'];
    $_SESSION['accessToken'] = $accessToken;

    // // Add access_token to Database
    // $query = "SELECT state FROM spotify_access WHERE state = ?";
    // $stmt = $conn->prepare($query);
    // $stmt->bind_param("s", $_SESSION['state']);
    // $stmt->execute();
    // $result = $stmt->get_result();
    // if ($result->num_rows > 0) {
    //     $query = "DELETE FROM spotify_access WHERE state = ?";
    //     $stmt = $conn->prepare($query);
    //     $stmt->bind_param('i', $_SESSION['state']);

    //     $query = "INSERT INTO spotify_access (state, token) VALUES (?,?)";
    //     $stmt = $conn -> prepare($query);
    //     $stmt -> execute([$_SESSION['state'], $accessToken]);
    // } 

    unset($_SESSION['authorization_code']);
}

// Get Profile
if (isset($_SESSION['accessToken'])) {
    $accessToken = $_SESSION['accessToken'];
    $profileUrl = 'https://api.spotify.com/v1/me';
    $profileOptions = [
        'http' => [
            'header' => "Authorization: Bearer $accessToken",
        ],
    ];

    $profileContext = stream_context_create($profileOptions);
    $profileResponse = file_get_contents($profileUrl, false, $profileContext);

    if ($profileResponse === false) {
        // Handle error
        session_destroy();
        die('Error getting profile');
    }
    $profileData = json_decode($profileResponse, true);
    print_r($profileData);
}

// Check if the user didn't logged in
if (!isset($_SESSION['state'])) {
    $state = bin2hex(random_bytes(16));
    $_SESSION['state'] = $state;
    header('Location: login.php?state=' . $_SESSION['state']);
}

if (isset($_POST['logout'])) {
    session_destroy();
    header('Location: #');
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">
    <link rel="stylesheet" href="assets/css/index.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous"></script>
    <title>Kins | Spotify</title>
</head>

<body>
    <form method="post">
        <input type="submit" value="Log Out" name="logout" id="logout">
    </form>
    <div class="content">
        
    </div>
    <footer class="web-player">
        <p>Footer</p>
    </footer>
</body>

</html>