<?php
session_start();
$clientId = '288ef94232504494af46c0fa1da6ac3e';
$clientSecret = '99df0c42fa394111bc82c67dcef5d734';
$redirectUri = 'http://localhost/kinnaa-web/';

if (!$_SESSION['state']) {
    $_SESSION['state'] = '';
}

if (isset($_GET['state']) != $_SESSION['state']) {
    header('Location: /kinnaa-web/spotify');
}

if(isset($_POST['login'])){
    $state = $_GET['state'];

    $scopes = 'user-read-private user-read-email user-read-playback-state user-modify-playback-state user-read-currently-playing streaming'; // Add more scopes as needed
    $authUrl = 'https://accounts.spotify.com/authorize' .
        '?response_type=code' .
        '&client_id=' . $clientId .
        '&scope=' . urlencode($scopes) .
        '&redirect_uri=' . urlencode($redirectUri) .
        '&state=' . $state;
    header('Location: ' . $authUrl);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous"></script>
    <script src="./assets/js/script.js"></script>
    <link rel="stylesheet" href="./assets/css/style.css">
    <title>Kins | Spotify Login</title>
</head>
<body>
    <div class="bg">
        <div class="container" id="container">
            <div id="login" style="display: block;">
                <h1 class="tLogin">Login</h1>
                <form method="post">
                    <button type="submit" name="login" id="sLogin">
                        <i class="fab fa-spotify i-spotify"></i>Login to Spotify
                    </button>
                </form>
                <h1 class="info">*Its use official Spotify Website to login!</h1>
            </div>
            <div id="info-content" style="display: none;">
                <p>
                    <h1 class="tLearn">Info about This Site</h1>
                    <div class="line"></div>
                </p>
            </div>
            <button id="more-info" class="underline">More Info</button>
        </div>
    </div>
</body>
</html>
