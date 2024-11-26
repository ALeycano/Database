<?php 
include('connect.php');
session_start();

if (!isset($_SESSION['userID'])) {
    header('Location: index.php');
    exit();
}

$success = '';
$error = '';
$userData = null;

$query = "
    SELECT addr.cityID, addr.provinceID, city.name AS cityName, province.name AS provinceName, 
           info.firstName, info.lastName, info.birthDay
    FROM addresses addr
    LEFT JOIN city ON city.cityID = addr.cityID
    LEFT JOIN province ON province.provinceID = addr.provinceID
    LEFT JOIN userInfo info ON info.userInfoID = addr.userInfoID
    WHERE addr.userInfoID = (SELECT userInfoID FROM Users WHERE userID = {$_SESSION['userID']})
";

$result = executeQuery($query);
if ($result) {
    $userData = mysqli_fetch_assoc($result);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cityName = $_POST['cityName'];
    $provinceName = $_POST['provinceName'];

    // Process city
    $cityQuery = "SELECT cityID FROM city WHERE name = '$cityName'";
    $cityResult = executeQuery($cityQuery);
    if ($cityResult && mysqli_num_rows($cityResult) > 0) {
        $cityData = mysqli_fetch_assoc($cityResult);
        $cityID = $cityData['cityID'];
    } else {
        $insertCityQuery = "INSERT INTO city (name) VALUES ('$cityName')";
        executeQuery($insertCityQuery);
        $cityID = mysqli_insert_id($conn);
    }

    // Process province
    $provinceID = null; // Default to NULL if provinceName is empty
    if (!empty($provinceName)) {
        $provinceQuery = "SELECT provinceID FROM province WHERE name = '$provinceName'";
        $provinceResult = executeQuery($provinceQuery);
        if ($provinceResult && mysqli_num_rows($provinceResult) > 0) {
            $provinceData = mysqli_fetch_assoc($provinceResult);
            $provinceID = $provinceData['provinceID'];
        } else {
            $insertProvinceQuery = "INSERT INTO province (name) VALUES ('$provinceName')";
            executeQuery($insertProvinceQuery);
            $provinceID = mysqli_insert_id($conn);
        }
    }

    // Get userInfoID for the current user
    $userInfoQuery = "SELECT userInfoID FROM Users WHERE userID = {$_SESSION['userID']}";
    $userInfoResult = executeQuery($userInfoQuery);
    $userInfoID = ($userInfoResult && mysqli_num_rows($userInfoResult) > 0) ? 
                   mysqli_fetch_assoc($userInfoResult)['userInfoID'] : null;

    if ($userInfoID) {
        // Check if address exists for user
        $checkAddressQuery = "SELECT * FROM addresses WHERE userInfoID = '$userInfoID'";
        $addressResult = executeQuery($checkAddressQuery);

        if ($addressResult && mysqli_num_rows($addressResult) > 0) {
            // Address exists, update the record
            $updateQuery = "
                UPDATE addresses 
                SET cityID = '$cityID', 
                    provinceID = " . ($provinceID ? "'$provinceID'" : "NULL") . " 
                WHERE userInfoID = '$userInfoID'
            ";
            if (executeQuery($updateQuery)) {
                $_SESSION['success'] = 'City and Province information updated successfully!';
            } else {
                $error = 'Failed to update your information. Please try again.';
            }
        } else {
            // Address does not exist, insert a new record
            $insertQuery = "
                INSERT INTO addresses (userInfoID, cityID, provinceID) 
                VALUES ('$userInfoID', '$cityID', " . ($provinceID ? "'$provinceID'" : "NULL") . ")
            ";
            if (executeQuery($insertQuery)) {
                $_SESSION['success'] = 'City and Province information saved successfully!';
            } else {
                $error = 'Failed to save your information. Please try again.';
            }
        }

        // Redirect to clear POST data
        header('Location: address.php');
        exit();
    } else {
        $error = 'Failed to identify user information. Please log in again.';
    }
}
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Postagram | Address Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>

<body>

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
                    <a class="btn btn-secondary mt-3 d-block d-lg-inline-block" href="index.php">Back</a>
                </li>
                <li class="nav-item">
                    <a class="btn btn-danger mt-3 d-block d-lg-inline-block" href="?logout">Logout</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-5 pt-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white mt-3">
                    <h5 class="mb-0">Update Your City and Province Information</h5>
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['success'])) { ?>
                        <div class="alert alert-success"><?php echo $_SESSION['success']; ?></div>
                        <?php unset($_SESSION['success']); ?>
                    <?php } ?>

                    <?php if (!empty($error)) { ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php } ?>

                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="cityName" class="form-label">City Name</label>
                            <input type="text" class="form-control" id="cityName" name="cityName" 
                                   value="<?php echo htmlspecialchars($userData['cityName'] ?? ''); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="provinceName" class="form-label">Province Name</label>
                            <input type="text" class="form-control" id="provinceName" name="provinceName" 
                                   value="<?php echo htmlspecialchars($userData['provinceName'] ?? ''); ?>" required>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Save Info</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
