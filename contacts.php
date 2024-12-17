<?php
ob_start();

$xmlFile = 'bookings.xml';
$jsonFile = 'bookings.json';

function loadXml($xmlFile) {
    return simplexml_load_file($xmlFile);
}

function convertXmlToJson($xmlFile, $jsonFile) {
    $xml = loadXml($xmlFile);
    if ($xml === false) {
        die("Error loading XML file.");
    }

    $jsonArray = json_decode(json_encode($xml), true);
    $jsonData = json_encode($jsonArray, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

    file_put_contents($jsonFile, $jsonData);
}

function addBooking($xmlFile, $jsonFile, $country, $city, $id, $firstName, $phoneNumber, $gender) {
    $xml = loadXml($xmlFile);
    $targetCountry = null;
    foreach ($xml->Country as $existingCountry) {
        if ($existingCountry['name'] == $country) {
            $targetCountry = $existingCountry;
            break;
        }
    }
    if (!$targetCountry) {
        $targetCountry = $xml->addChild('Country');
        $targetCountry->addAttribute('name', $country);
    }

    $targetCity = null;
    foreach ($targetCountry->City as $existingCity) {
        if ($existingCity['name'] == $city) {
            $targetCity = $existingCity;
            break;
        }
    }
    if (!$targetCity) {
        $targetCity = $targetCountry->addChild('City');
        $targetCity->addAttribute('name', $city);
    }

    $newContact = $targetCity->addChild('Contact');
    $newContact->addChild('ID', htmlspecialchars($id));
    $newContact->addChild('FirstName', htmlspecialchars($firstName));
    $newContact->addChild('PhoneNumber', htmlspecialchars($phoneNumber));
    $newContact->addChild('Gender', htmlspecialchars($gender));

    if ($xml->asXML($xmlFile)) {
        echo "Uus kontakt on edukalt lisatud.<br>";
    } else {
        echo "Kontakti lisamine ebaõnnestus.<br>";
    }

    convertXmlToJson($xmlFile, $jsonFile);
}

function displayBookings($xmlFile) {
    $xml = loadXml($xmlFile);
    foreach ($xml->Country as $country) {
        $countryName = $country['name'];
        foreach ($country->City as $city) {
            $cityName = $city['name'];
            foreach ($city->Contact as $contact) {
                echo "<tr>";
                echo "<td>" . $contact->ID . "</td>";
                echo "<td>" . $contact->FirstName . "</td>";
                echo "<td>" . $contact->PhoneNumber . "</td>";
                echo "<td>" . $contact->Gender . "</td>";
                echo "<td>" . $countryName . "</td>";
                echo "<td>" . $cityName . "</td>";
                echo "</tr>";
            }
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $newCountry = htmlspecialchars($_POST['newCountry']);
    $newCity = htmlspecialchars($_POST['newCity']);
    $newID = htmlspecialchars($_POST['newID']);
    $newFirstName = htmlspecialchars($_POST['newFirstName']);
    $newPhoneNumber = htmlspecialchars($_POST['newPhoneNumber']);
    $newGender = htmlspecialchars($_POST['newGender']);

    addBooking($xmlFile, $jsonFile, $newCountry, $newCity, $newID, $newFirstName, $newPhoneNumber, $newGender);

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

echo '<link rel="stylesheet" href="styles.css">';
?>

<!DOCTYPE html>
<html lang="et">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phone Book</title>
</head>
<body>

<nav class="nav">
    <ul>
        <li><a href="bookings.php">XML Kontaktid</a></li>
        <li><a href="bookings.json" target="_blank">Vaata JSON faili</a></li>
        <li><a href="<?php echo $xmlFile; ?>" target="_blank">Vaata XML faili</a></li>
    </ul>
</nav>

<h2>Kontaktid:</h2>
<table>
    <tr><th>ID</th><th>Eesnimi</th><th>Telefon</th><th>Sugu</th><th>Riik</th><th>Linn</th></tr>
    <?php displayBookings($xmlFile); ?>
</table>

<form method="post" action="">
    <h3>Lisa uus kontakt</h3>
    <label for="newCountry">Riik:</label>
    <input type="text" id="newCountry" name="newCountry" required>

    <label for="newCity">Linn:</label>
    <input type="text" id="newCity" name="newCity" required>

    <label for="newID">ID:</label>
    <input type="number" id="newID" name="newID" required>

    <label for="newFirstName">Eesnimi:</label>
    <input type="text" id="newFirstName" name="newFirstName" required>

    <label for="newPhoneNumber">Telefon:</label>
    <input type="text" id="newPhoneNumber" name="newPhoneNumber" pattern="\d+" title="Ainult numbrid on lubatud" required>

    <label for="newGender">Sugu:</label>
    <select id="newGender" name="newGender" required>
        <option value="Male">Male</option>
        <option value="Female">Female</option>
    </select>

    <input type="submit" value="Lisa kontakt">
</form>

</body>
</html>

<?php ob_end_flush(); ?>
