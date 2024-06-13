<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="refresh" content="5">
    <title>ENVIRONMENT AIR QUALITY MONITORING</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 20px;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        h1 {
            color: #333;
            margin-bottom: 20px;
            font-weight: bold;
            font-size: 1.8em;
            text-align: center;
        }

        table {
            width: 100%;
            max-width: 800px;
            border-collapse: collapse;
            margin: 20px 0;
            box-shadow: 0 2px 3px rgba(0, 0, 0, 0.1);
            background-color: #fff;
        }

        table th,
        table td {
            padding: 10px;
            text-align: center;
            border-bottom: 1px solid #ddd;
            font-size: 1em;
        }

        table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        table tr:hover {
            background-color: #f1f1f1;
        }

        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }

        .pagination a {
            color: #333;
            padding: 8px 16px;
            text-decoration: none;
            border: 1px solid #ddd;
            margin: 0 2px;
        }

        .pagination a.active {
            background-color: #333;
            color: #fff;
        }

        .pagination a:hover {
            background-color: #ddd;
        }

        .chart-container {
            width: 100%;
            max-width: 800px;
            margin: 20px 0;
            box-shadow: 0 2px 3px rgba(0, 0, 0, 0.1);
            background-color: #fff;
            border-radius: 5px;
            padding: 15px;
        }

        .chart-title {
            font-size: 1.2em;
            font-weight: bold;
            margin-bottom: 10px;
            text-align: center;
        }

        canvas {
            background-color: #fff;
            border: 1px solid #ddd;
        }

        @media (max-width: 600px) {
            h1 {
                font-size: 1.5em;
            }

            table th,
            table td {
                padding: 8px;
                font-size: 0.9em;
            }

            .chart-container {
                padding: 10px;
            }

            canvas {
                width: 100%;
            }
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
    <h1>ENVIRONMENT AIR QUALITY MONITORING</h1>
    <?php
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "environment_monitoring";

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Pagination
    $limit = 20; // Number of entries to show in a page.
    if (isset($_GET["page"])) {
        $page = $_GET["page"];
    } else {
        $page = 1;
    }
    $start_from = ($page - 1) * $limit;

    $sql = "SELECT id, datetime, temperature, humidity, gas, quality FROM sensor_data ORDER BY id DESC LIMIT $start_from, $limit";
    $result = $conn->query($sql);

    $timestamps = [];
    $temperatures = [];
    $humidities = [];
    $gasValues = [];

    echo '<table cellspacing="5" cellpadding="5">
          <tr> 
            <th>ID</th> 
            <th>DateTime</th> 
            <th>Temperature &deg;C</th> 
            <th>Humidity &#37;</th>
            <th>Gas Value</th>  
            <th>Air Quality</th>    
          </tr>';

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo '<tr> 
                    <td>' . $row["id"] . '</td> 
                    <td>' . $row["datetime"] . '</td> 
                    <td>' . $row["temperature"] . '</td>
                    <td>' . $row["humidity"] . '</td> 
                    <td>' . $row["gas"] . '</td> 
                    <td>' . $row["quality"] . '</td>
                  </tr>';

            $timestamps[] = $row["datetime"];
            $temperatures[] = $row["temperature"];
            $humidities[] = $row["humidity"];
            $gasValues[] = $row["gas"];
        }
    } else {
        echo '<tr><td colspan="6">No data found</td></tr>';
    }
    echo '</table>';

    // Pagination links
    $sql = "SELECT COUNT(id) FROM sensor_data";
    $result = $conn->query($sql);
    $row = $result->fetch_row();
    $total_records = $row[0];
    $total_pages = ceil($total_records / $limit);

    $pagLink = "<div class='pagination'>";
    for ($i = 1; $i <= $total_pages; $i++) {
        $pagLink .= "<a href='index.php?page=" . $i . "'";
        if ($i == $page) {
            $pagLink .= " class='active'";
        }
        $pagLink .= ">" . $i . "</a>";
    }
    echo $pagLink . "</div>";

    $conn->close();
    ?>

    <div class="chart-container">
        <div class="chart-title">Temperature Changes Over Time</div>
        <canvas id="temperatureChart"></canvas>
    </div>
    <div class="chart-container">
        <div class="chart-title">Humidity Changes Over Time</div>
        <canvas id="humidityChart"></canvas>
    </div>
    <div class="chart-container">
        <div class="chart-title">Gas Value Changes Over Time</div>
        <canvas id="gasChart"></canvas>
    </div>

    <script>
        const labels = <?php echo json_encode($timestamps); ?>;
        const temperatureData = <?php echo json_encode($temperatures); ?>;
        const humidityData = <?php echo json_encode($humidities); ?>;
        const gasData = <?php echo json_encode($gasValues); ?>;

        const temperatureConfig = {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Temperature (°C)',
                    data: temperatureData,
                    borderColor: 'red',
                    fill: false
                }]
            },
            options: {
                scales: {
                    x: {
                        display: true,
                        title: {
                            display: true,
                            text: 'DateTime'
                        }
                    },
                    y: {
                        display: true,
                        title: {
                            display: true,
                            text: 'Temperature (°C)'
                        }
                    }
                }
            }
        };

        const humidityConfig = {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Humidity (%)',
                    data: humidityData,
                    borderColor: 'blue',
                    fill: false
                }]
            },
            options: {
                scales: {
                    x: {
                        display: true,
                        title: {
                            display: true,
                            text: 'DateTime'
                        }
                    },
                    y: {
                        display: true,
                        title: {
                            display: true,
                            text: 'Humidity (%)'
                        }
                    }
                }
            }
        };

        const gasConfig = {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Gas Value',
                    data: gasData,
                    borderColor: 'green',
                    fill: false
                }]
            },
            options: {
                scales: {
                    x: {
                        display: true,
                        title: {
                            display: true,
                            text: 'DateTime'
                        }
                    },
                    y: {
                        display: true,
                        title: {
                            display: true,
                            text: 'Gas Value'
                        }
                    }
                }
            }
        };

        new Chart(
            document.getElementById('temperatureChart'),
            temperatureConfig
        );

        new Chart(
            document.getElementById('humidityChart'),
            humidityConfig
        );

        new Chart(
            document.getElementById('gasChart'),
            gasConfig
        );
    </script>
</body>

</html>