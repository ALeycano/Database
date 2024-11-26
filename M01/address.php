<?php
include('connect.php');
session_start();

// Error handling
$error = '';

// Check if user is logged in
if (!isset($_SESSION['userID'])) {
    header('Location: index.php');
    exit();
}

// Fetch user data if logged in
$userData = null;
$query = "
    SELECT u.userName, u.email, u.phoneNumber, u.isOnline, 
           info.firstName, info.lastName, info.birthDay, addr.cityID 
    FROM Users u
    JOIN userInfo info ON u.userInfoID = info.userInfoID
    LEFT JOIN addresses addr ON addr.userInfoID = info.userInfoID
    WHERE u.userID = {$_SESSION['userID']}
";
$result = executeQuery($query);
if ($result) {
    $userData = mysqli_fetch_assoc($result);
}

// Handle form submission for city
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Escape city name to prevent SQL injection
    $cityName = mysqli_real_escape_string($conn, $_POST['cityName']);
    $userInfoID = $_SESSION['userInfoID']; // Assuming you have userInfoID in session
    
    // Check if city already exists in the city table
    $cityQuery = "
        SELECT cityID 
        FROM city 
        WHERE name = '$cityName'
    ";
    $cityResult = executeQuery($cityQuery);
    
    if ($cityResult && mysqli_num_rows($cityResult) > 0) {
        // If city exists, get the cityID
        $cityData = mysqli_fetch_assoc($cityResult);
        $cityID = $cityData['cityID'];
    } else {
        // If city does not exist, insert the new city into the city table
        $insertCityQuery = "
            INSERT INTO city (name) 
            VALUES ('$cityName')
        ";
        executeQuery($insertCityQuery);
        // Get the new cityID after insertion
        $cityID = mysqli_insert_id($conn);
    }

    // Insert the cityID into addresses (or update if needed)
    $insertAddressQuery = "
        INSERT INTO addresses (userInfoID, cityID) 
        VALUES ('$userInfoID', '$cityID')
        ON DUPLICATE KEY UPDATE cityID = '$cityID'
    ";

    if (executeQuery($insertAddressQuery)) {
        $_SESSION['success'] = 'City info saved successfully!';
        header('Location: address.php');
        exit();
    } else {
        $_SESSION['error'] = 'Error saving city info!';
    }
}

// Logout logic
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Postagram</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">Postagram</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Back</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="?logout">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content Container -->
    <div class="container mt-5 pt-5">

        <!-- User Greeting -->
        <div class="row mb-4">
            <div class="col-12 text-center">
                <h2>Welcome, <?php echo htmlspecialchars($userData['firstName']); ?>!</h2>
                <p class="lead">Here you can add or update your city information.</p>
            </div>
        </div>

        <!-- City Form -->
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Add Your City Information</h5>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_SESSION['success'])): ?>
                            <div class="alert alert-success mt-3"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
                        <?php elseif (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger mt-3"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="cityName" class="form-label">City Name</label>
                                <input type="text" class="form-control" id="cityName" name="cityName" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Save Info</button>
                        </form>

                        <!-- Conditional 'Add City' Button -->
                        <?php if (empty($userData['cityID'])): ?>
                            <a href="address.php" class="btn btn-primary mt-3">Add City</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
