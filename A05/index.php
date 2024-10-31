<?php
include('connect.php');
session_start();
$error = '';

// Handle login form submission
if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Check the database
    $query = "SELECT * FROM Users WHERE userName = ? AND password = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $_SESSION['userID'] = $user['userID'];
        $_SESSION['userInfoID'] = $user['userInfoID'];
    } else {
        $error = 'Invalid username or password';
    }
}

// logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}

// Fetch user data if logged in
$userData = null;
if (isset($_SESSION['userID'])) {
    $query = "
        SELECT u.userName, u.email, u.phoneNumber, u.isOnline, 
               info.firstName, info.lastName, info.birthDay 
        FROM Users u 
        JOIN userInfo info ON u.userInfoID = info.userInfoID 
        WHERE u.userID = ?
    ";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $_SESSION['userID']);
    $stmt->execute();
    $userData = $stmt->get_result()->fetch_assoc();
}
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Stylish Website</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">Postagram</a>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if (isset($_SESSION['userID'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="?logout">Logout</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <?php if (isset($_SESSION['userID'])): ?>
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
                        </div>
                    </div>
                    <a href="?logout" class="btn btn-danger mt-3">Logout</a>
                </div>
            </div>
        <?php else: ?>
            <!-- Login Form -->
            <div class="card login-card">
                <div class="card-body">
                    <h3 class="text-center">Login to Your Account</h3>
                    <?php if ($error): ?>
                        <div class="alert alert-danger" role="alert">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>
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
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>