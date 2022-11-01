<?php
include_once("db.php");

$path = explode("/", $_SERVER["REQUEST_URI"]);
if(!array_key_exists(2, $path) != NULL) {
    header("Location: /");
}

$vehicle_name = $path[2];

$vehicle_data = fetch_vehicle_data($vehicle_name);
if($vehicle_data == NULL) {
    header("Location: /");
}

if(array_key_exists(5, $path) != NULL) {
    $process = "EDIT";
    $fuel_data = fetch_fuel_data($vehicle_data->vehicle_id, $path[5]);
} else {
    $process = "ADD";
    $fuel_data = new stdClass();
    $fuel_data->fuel_id = NULL;
    $fuel_data->vehicle_id = $vehicle_data->vehicle_id;
    $fuel_data->fuel_date = NULL;
    $fuel_data->company = NULL;
    $fuel_data->city = NULL;
    $fuel_data->state = NULL;
    $fuel_data->latitude = 0.00;
    $fuel_data->longitude = 0.00;
    $fuel_data->price = NULL;
    $fuel_data->gallons = NULL;
    $fuel_data->mileage = NULL;
}

if (!empty($_POST)) {
    $fuel_data->vehicle_id = $vehicle_data->vehicle_id;
    $fuel_data->company = $_POST["company"];
    $fuel_data->city = $_POST["city"];
    $fuel_data->state = $_POST["state"];
    $fuel_data->latitude = floatval($_POST["latitude"]);
    $fuel_data->longitude = floatval($_POST["longitude"]);
    $fuel_data->price = $_POST["price"];
    $fuel_data->gallons = $_POST["gallons"];
    $fuel_data->mileage = $_POST["mileage"];

    if (!empty($_POST["date"])
        && !empty($_POST["time"])
        && !empty($_POST["company"])
        && !empty($_POST["city"])
        && !empty($_POST["state"])
        && !empty($_POST["price"])
        && !empty($_POST["gallons"])
        && !empty($_POST["mileage"])
    ) {
        $fuel_date = DateTime::createFromFormat("m/j/Y h:i a", $_POST["date"] . " " . $_POST["time"]);
        $fuel_data->fuel_date = $fuel_date->format("Y-m-d H:i:s");

        $success = insert_vehicle_fuel_data($fuel_data);

        if ($success) {
            header("Location: /vehicle/" . url_name($vehicle_name) . "/fuel");
        }
    }
}

$page_title = $vehicle_data->name . " - Update Fuel Log";

include_once("includes/header.php");

$current_datetime = new DateTime($fuel_data->fuel_date);
?>
    <form method="POST">
        <table cellpadding="0" cellspacing="0">
            <tr class="header">
                <td colspan="2" scope="colgroup">
                    <img src="/images/<?php print(url_name($vehicle_data->name)); ?>.png" alt="<?php print($vehicle_data->name); ?> - <?php print($vehicle_data->year); ?> <?php print($vehicle_data->make); ?> <?php print($vehicle_data->model); ?> <?php print($vehicle_data->type); ?>" style="width:600px;"><br />
                    <p>Add Fuel Record</p>
                </td>
            </tr>
            <tr>
                <td class="label"><label for="date">Date</label></td>
                <td><input type="text" name="date" id="date" value="<?php echo $current_datetime->format("m/d/Y"); ?>"></td>
            </tr>
            <tr>
                <td class="label"><label for="time">Time</label></td>
                <td><input type="text" name="time" id="time" value="<?php echo $current_datetime->format("g:i a"); ?>"></td>
            </tr>
            <tr>
                <td class="label"><label for="company">Company</label></td>
                <td><input type="text" name="company" id="company" value="<?php print($fuel_data->company); ?>"></td>
            </tr>
            <tr>
                <td class="label"><label for="city">City</label></td>
                <td><input type="text" name="city" id="city" value="<?php print($fuel_data->city); ?>"></td>
            </tr>
            <tr>
                <td class="label"><label for="state">State</label></td>
                <td><input type="text" name="state" id="state" value="<?php print($fuel_data->state); ?>"></td>
            </tr>
            <tr>
                <td class="label"><label for="price">Price</label></td>
                <td><input type="text" name="price" id="price" value="<?php print($fuel_data->price); ?>"></td>
            </tr>
            <tr>
                <td class="label"><label for="gallons">Gallons</label></td>
                <td><input type="text" name="gallons" id="gallons" value="<?php print($fuel_data->gallons); ?>"></td>
            </tr>
            <tr>
                <td class="label"><label for="mileage">Mileage</label></td>
                <td><input type="text" name="mileage" id="mileage" value="<?php print($fuel_data->mileage); ?>"></td>
            </tr>
        </table>
        <button type="submit"><?php print($process); ?></button>
        <input type="hidden" name="latitude" id="latitude" value="<?php print($fuel_data->latitude); ?>">
        <input type="hidden" name="longitude" id="longitude" value="<?php print($fuel_data->longitude); ?>">
    </form>
    <button id="cancel_button" onclick="cancelUpdate();">RETURN</button>
    <script type="text/javascript">
<?php
if ($process == "ADD") {
?>
        const geoOptions = {
            enableHighAccuracy: true,
            timeout: 5000,
            maximumAge: 0
        };

        navigator.geolocation.getCurrentPosition(populateLocation, errorCallback, geoOptions);

        function populateLocation(position) {
            const coordinates = position.coords;

            document.getElementById("latitude").value = coordinates.latitude;
            document.getElementById("longitude").value = coordinates.longitude;

            return true;
        }

        function errorCallback(error) {
            console.log(error);
            return false;
        }
<?php
}
?>
        function cancelUpdate(){
            window.location="/vehicle/<?php print(url_name($vehicle_name)); ?>";
        }
    </script>
<?php
include_once("includes/footer.php");
