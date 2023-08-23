<?php
session_start();
$clientId = '288ef94232504494af46c0fa1da6ac3e';
$clientSecret = '99df0c42fa394111bc82c67dcef5d734';
$redirectUri = 'http://localhost/kinnaa-web/';

if (isset($_GET['check'])) {
    $_SESSION['check'] = $_GET['check'];
}

// Check if the user logged in
if ($_SESSION['check'] == 1) {
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

        //Some Information of User
        $display_name = $profileData['display_name'];
        $user_url = $profileData['external_urls']['spotify'];
        $user_photo = $profileData['images'][1]['url'];
        $user_region = $profileData['country'];
        $current_subs = $profileData['product'];
        if ($current_subs == 'premium') {
            $current_subs = str_replace('p', 'P', $current_subs);
        } elseif ($current_subs == 'free') {
            $current_subs = str_replace('f', 'F', $current_subs);
        }
        // print_r($profileData);

        // Taking The Name of Country and The Flag
        $countryData = file_get_contents('assets/json/countries.json');
        $countryArray = json_decode($countryData, true);
        $countryLower = strtolower($user_region);
        $countryName = $countryArray[$countryLower];
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
} else {
    $state = bin2hex(random_bytes(16));
    $_SESSION['state'] = $state;
    header('Location: login.php?state=' . $_SESSION['state']);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">
    <link rel="stylesheet" href="assets/css/index.css">
    <title>Kins | Spotify</title>
</head>

<body>
    <div class="frame">
        <nav class="navbar navbar-expand-lg bg-body-tertiary bg-dark border-bottom border-body" data-bs-theme="dark">
            <div class="container-fluid">
                <a class="navbar-brand" href="#">Navbar</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <li class="nav-item">
                            <a class="nav-link active" aria-current="page" href="#">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">Link</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
        <form method="post">
            <input type="submit" value="Log Out" name="logout" id="logout">
        </form>
    </div>
    <div class="content content--canvas">
        <div class="content__title" id="main" style="display: none;">
            <img src="<?= $user_photo ?>" alt="user_image" class="user_image">
            <h1 class="welcome">Welcome Back! <?= $display_name ?></h1>
            <button type="submit" class="btn btn-top" id="see_top">
                <img src="assets/icon/white/thumbs-up.svg" class="icon-fix">See Your Top Play / Artist
            </button>
            <button type="submit" class="btn btn-top" id="see_profile">
                <img src="assets/icon/white/user.svg" class="icon-fix">See Your Profile
            </button>
        </div>

        <div class="content__title" id="top-plays" style="display: none;">
            <h1><?= $display_name ?>'s Top</h1>
        </div>

        <div class="content__title" id="profile" style="display: block;">
            <img src="<?= $user_photo ?>" alt="user_image" class="user_image">
            <h1 class="profile-name"><?= $display_name ?>'s Profile</h1>
            <hr class="profile-line">
            <table>
                <tr>
                    <td>Link to Profile</td>
                    <td class="table-dot">:</td>
                    <td><a href="<?= $user_url ?>">Here!</a></td>
                </tr>
                <tr>
                    <td>Region</td>
                    <td class="table-dot">:</td>
                    <td><?=$user_region?> | <?=$countryName?></td>
                </tr>
            </table>
        </div>
    </div>
    <footer class="footer">
        <p>Footer</p>
    </footer>
    <!-- <script src="assets/js/noise.min.js"></script>
    <script src="assets/js/util.js"></script>
    <script src="assets/js/swirl.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous"></script>
    <script>
        document.documentElement.className = "js";
        var supportsCssVars = function() {
            var e, t = document.createElement("style");
            return t.innerHTML = "root: { --tmp-var: bold; }", document.head.appendChild(t), e = !!(window.CSS && window.CSS.supports && window.CSS.supports("font-weight", "var(--tmp-var)")), t.parentNode.removeChild(t), e
        };
        supportsCssVars() || alert("Please view this demo in a modern browser that supports CSS Variables.");
    </script> -->
</body>

</html>