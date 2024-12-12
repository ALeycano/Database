<?php
include("connect.php");

$airlineNameFilter = isset($_GET['airlineName']) ? $_GET['airlineName'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : '';
$order = isset($_GET['order']) ? $_GET['order'] : '';

$flightLogsQuery = "SELECT * FROM flightLogs";

$filters = [];

if ($airlineNameFilter != '') {
    $filters[] = "airlineName = '$airlineNameFilter'";
}

if (count($filters) > 0) {
    $flightLogsQuery .= " WHERE " . implode(' AND ', $filters);
}

if ($sort != '') {
    $flightLogsQuery .= " ORDER BY $sort";

    if ($order != '') {
        $flightLogsQuery .= " $order";
    }
}

$flightLogsResults = executeQuery($flightLogsQuery);
$airlineNamesQuery = "SELECT DISTINCT airlineName FROM flightLogs ORDER BY airlineName";
$airlineNamesResult = executeQuery($airlineNamesQuery);
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PUP Airport | Flight Logs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="assets\css\style.css">
</head>

<body>

    <div class="container my-5">
        <div class="row my-5">
            <div class="col">
                <form method="GET">
                    <div class="card p-4 rounded-5 shadow">
                        <div class="d-flex flex-wrap justify-content-between">
                            <div class="mb-3">
                                <label for="airlineName" class="filter-label">Airline Name</label>
                                <select id="airlineName" name="airlineName" class="form-control">
                                    <option value="">Select Airline</option>
                                    <?php
                                    while ($row = mysqli_fetch_assoc($airlineNamesResult)) {
                                        $selected = ($row['airlineName'] == $airlineNameFilter) ? 'selected' : '';
                                        echo "<option value='{$row['airlineName']}' {$selected}>{$row['airlineName']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="sort" class="filter-label">Sort By</label>
                                <select id="sort" name="sort" class="form-control">
                                    <option value="">None</option>
                                    <option value="flightNumber" <?php if ($sort == 'flightNumber')
                                        echo 'selected'; ?>>
                                        Flight Number</option>
                                    <option value="airlineName" <?php if ($sort == 'airlineName')
                                        echo 'selected'; ?>>
                                        Airline Name</option>
                                    <option value="passengerCount" <?php if ($sort == 'passengerCount')
                                        echo 'selected'; ?>>Passenger Count</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="order" class="filter-label">Order</label>
                                <select id="order" name="order" class="form-control">
                                    <option value="ASC" <?php if ($order == 'ASC')
                                        echo 'selected'; ?>>Ascending</option>
                                    <option value="DESC" <?php if ($order == 'DESC')
                                        echo 'selected'; ?>>Descending
                                    </option>
                                </select>
                            </div>

                            <div class="text-center mt-4">
                                <button class="btn btn-primary w-100">Filter</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="row my-5">
            <div class="col">
                <div class="card p-4 rounded-5 shadow table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th scope="col" id="flightNumber">Flight Number</th>
                                <th scope="col" id="airlineName">Airline Name</th>
                                <th scope="col" id="passengerCount">Passenger Count</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (mysqli_num_rows($flightLogsResults) > 0) {
                                while ($flightLogsRow = mysqli_fetch_assoc($flightLogsResults)) {
                                    echo "<tr>
                        <td>{$flightLogsRow['flightNumber']}</td>
                        <td>{$flightLogsRow['airlineName']}</td>
                        <td>{$flightLogsRow['passengerCount']}</td>
                      </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='3'>No records found</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
</body>

</html>