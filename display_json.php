<?php

function loadJson($jsonFile)
{
    $jsonData = file_get_contents($jsonFile);
    return json_decode($jsonData, true);
}

function searchByCarNumber($jsonFile, $carNumber)
{
    $data = loadJson($jsonFile);
    $results = array_filter($data['booking'], function ($booking) use ($carNumber) {
        return isset($booking['details']['carNumber']) && $booking['details']['carNumber'] === $carNumber;
    });

    if ($results) {
        echo "<h2>Tulemused auto numbri $carNumber järgi:</h2>";
        foreach ($results as $booking) {
            echo "Nimi: " . htmlspecialchars($booking['contact']['name']) . "<br>";
            echo "Telefon: " . htmlspecialchars($booking['contact']['@attributes']['phone']) . "<br>";
            echo "Teenuse nimi: " . htmlspecialchars($booking['details']['service']) . "<br>";
            echo "Auto number: " . htmlspecialchars($booking['details']['carNumber']) . "<br>";
            echo "Aeg: " . htmlspecialchars($booking['datetime']) . "<br>";
        }
    } else {
        echo "Broneeringu auto numbriga $carNumber ei leitud.<br>";
    }
}

function searchByName($jsonFile, $name)
{
    $data = loadJson($jsonFile);
    $results = array_filter($data['booking'], function ($booking) use ($name) {
        return isset($booking['contact']['name']) && strpos($booking['contact']['name'], $name) !== false;
    });

    if ($results) {
        echo "<h2>Tulemused nime järgi: $name</h2>";
        foreach ($results as $booking) {
            echo "Nimi: " . htmlspecialchars($booking['contact']['name']) . "<br>";
            echo "Telefon: " . htmlspecialchars($booking['contact']['@attributes']['phone']) . "<br>";
            echo "Teenuse nimi: " . htmlspecialchars($booking['details']['service']) . "<br>";
            echo "Auto number: " . htmlspecialchars($booking['details']['carNumber']) . "<br>";
            echo "Aeg: " . htmlspecialchars($booking['datetime']) . "<br>";
        }
    } else {
        echo "Broneeringut nimega $name ei leitud.<br>";
    }
}

function getUniqueServices($jsonFile)
{
    $data = loadJson($jsonFile);
    $services = array_map(function ($booking) {
        return $booking['details']['service'];
    }, $data['booking']);

    return array_unique($services);
}

function countTotalBookings($jsonFile)
{
    $data = loadJson($jsonFile);
    return count($data['booking']);
}

echo '<!DOCTYPE html>';
echo '<html lang="et">';
echo '<head>';
echo '<meta charset="UTF-8">';
echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
echo '<title>Broneeringute Haldamine</title>';
echo '<link rel="stylesheet" href="styles.css">';
echo '</head>';
echo '<body>';
echo '<nav>';
echo '<ul>';
echo '<li><a href="display_json.php">JSON Broneeringud</a></li>';
echo '<li><a href="bookings.php">XML Broneeringud</a></li>';
echo '</ul>';
echo '</nav>';
?>
<div id="searchForm" style="display:block;">
    <h2>Numbrimärkide otsing</h2>
    <form method="get" action="">
        <label for="carNumber">Sisestage auto number otsimiseks:</label>
        <input type="text" id="carNumber" name="carNumber" required>
        <input type="submit" value="Otsi">
    </form>
</div>

<div id="searchForm" style="display:block;">
    <h2>Otsing nime järgi</h2>
    <form method="get" action="">
        <label for="name">Sisestage nimi otsimiseks:</label>
        <input type="text" id="name" name="name" required>
        <input type="submit" value="Otsi">
    </form>
</div>
<?php
$jsonFile = 'bookings.json';

if (isset($_GET['carNumber'])) {
    $carNumber = htmlspecialchars($_GET['carNumber']);
    searchByCarNumber($jsonFile, $carNumber);
}

if (isset($_GET['name'])) {
    $name = htmlspecialchars($_GET['name']);
    searchByName($jsonFile, $name);
}

$uniqueServices = getUniqueServices($jsonFile);
echo "<h2>Unikaalsed teenused:</h2><ul>";
foreach ($uniqueServices as $service) {
    echo "<li>" . htmlspecialchars($service) . "</li>";
}
echo "</ul>";

$totalBookings = countTotalBookings($jsonFile);
echo "<h2>Broneeringute kogus: $totalBookings</h2>";

echo "<h2>Broneeringud:</h2><table border='1'>";
echo "<tr><th>Nimi</th><th>Telefon</th><th>Teenuse nimi</th><th>Auto number</th><th>Aeg</th></tr>";
$data = loadJson($jsonFile);
foreach ($data['booking'] as $booking) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($booking['contact']['name']) . "</td>";
    echo "<td>" . htmlspecialchars($booking['contact']['@attributes']['phone']) . "</td>";
    echo "<td>" . htmlspecialchars($booking['details']['service']) . "</td>";
    echo "<td>" . htmlspecialchars($booking['details']['carNumber']) . "</td>";
    echo "<td>" . htmlspecialchars($booking['datetime']) . "</td>";
    echo "</tr>";
}
echo "</table>";

?>

</body>
</html>
