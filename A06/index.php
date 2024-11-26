<?php
include('connect.php');
session_start();

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Query to check if the username and password match
    $query = "SELECT * FROM Users WHERE userName = '$username' AND password = '$password'";
    $result = executeQuery($query);

    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        $_SESSION['userID'] = $user['userID'];
        $_SESSION['userInfoID'] = $user['userInfoID'];
    }
}

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}

if (isset($_GET['deleteAccount']) && isset($_SESSION['userID'])) {
    $userID = $_SESSION['userID'];

    $userQuery = "SELECT userInfoID FROM Users WHERE userID = '$userID'";
    $userResult = executeQuery($userQuery);

    if ($userResult && mysqli_num_rows($userResult) > 0) {
        $userData = mysqli_fetch_assoc($userResult);
        $userInfoID = $userData['userInfoID'];

        executeQuery("DELETE FROM addresses WHERE userInfoID = '$userInfoID'");
        executeQuery("DELETE FROM userInfo WHERE userInfoID = '$userInfoID'");
        executeQuery("DELETE FROM Users WHERE userID = '$userID'");
        executeQuery("DELETE FROM city WHERE cityID NOT IN (SELECT cityID FROM addresses)");
        executeQuery("DELETE FROM province WHERE provinceID NOT IN (SELECT provinceID FROM addresses)");

        session_destroy();
        header("Location: index.php");
        exit();
    }
}

$userData = null;
if (isset($_SESSION['userID'])) {
    $query = "
    SELECT u.userName, u.email, u.phoneNumber, u.isOnline, 
           info.firstName, info.lastName, info.birthDay, 
           city.name AS cityName, province.name AS provinceName
    FROM Users u 
    JOIN userInfo info ON u.userInfoID = info.userInfoID 
    LEFT JOIN addresses addr ON addr.userInfoID = u.userInfoID
    LEFT JOIN city ON city.cityID = addr.cityID
    LEFT JOIN province ON province.provinceID = addr.provinceID
    WHERE u.userID = {$_SESSION['userID']}
    ";

    $result = executeQuery($query);

    if ($result) {
        $userData = mysqli_fetch_assoc($result);
    }
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

<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="index.php">Postagram</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <?php if (isset($_SESSION['userID'])) { ?>
                    <a href="?logout" class="btn btn-danger mt-3">Logout</a>
                <?php } else { ?>
                    <a href="registration.php" class="btn btn-success mt-3 d-block w-auto">Register</a>
                <?php } ?>
            </ul>
        </div>
    </div>
</nav>


    <div class="container mt-5">
    <?php if (isset($_SESSION['userID'])) { ?>
    <div class="card">
        <div class="card-header">
            Welcome, <?php echo htmlspecialchars($userData['firstName']); ?>!
        </div>
        <div class="card-body text-center">
            <p class="text-muted">You are successfully logged in.</p>
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Username:</strong> <?php echo htmlspecialchars($userData['userName']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($userData['email']); ?></p>
                    <p><strong>Phone Number:</strong> <?php echo htmlspecialchars($userData['phoneNumber']); ?></p>
                </div>
                <div class="col-md-6">
                    <p><strong>Full Name:</strong> 
                        <?php echo htmlspecialchars($userData['firstName'] . ' ' . $userData['lastName']); ?></p>
                    <p><strong>Birthday:</strong> <?php echo htmlspecialchars($userData['birthDay']); ?></p>
                    <p><strong>Status:</strong> <?php echo $userData['isOnline'] ? 'Online' : 'Offline'; ?></p>
                    <p><strong>City:</strong> <?php echo htmlspecialchars($userData['cityName']) ?: 'Not set'; ?></p>
                    <p><strong>Province:</strong> <?php echo htmlspecialchars($userData['provinceName']) ?: 'Not set'; ?></p>

                </div>
            </div>
            <a href="address.php" class="btn btn-primary mt-3">Update Address</a>
            <a href="?deleteAccount=true" class="btn btn-danger mt-3">Delete Account</a>
        </div>
    </div>
    <?php } else { ?>
    <div class="card login-card">
        <div class="card-body">
            <h3 class="text-center">Login to Your Account</h3>
            <form method="POST" action="">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <button type="submit" name="login" class="btn btn-primary w-100">Login</button>
            </form>
        </div>
    </div>
    <?php } ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
