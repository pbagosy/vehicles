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
    $service_data = fetch_service_data($vehicle_data->vehicle_id, $path[5]);
} else {
    $process = "ADD";
    $service_data = new stdClass();
    $service_data->service_id = NULL;
    $service_data->vehicle_id = $vehicle_data->vehicle_id;
    $service_data->service_date = NULL;
    $service_data->company = NULL;
    $service_data->city = NULL;
    $service_data->state = NULL;
    $service_data->latitude = 0.00;
    $service_data->longitude = 0.00;
    $service_data->cost = NULL;
    $service_data->summary = NULL;
    $service_data->mileage = NULL;
    $service_data->description = NULL;
}

if (!empty($_POST)) {
    $service_data->vehicle_id = $vehicle_data->vehicle_id;
    $service_data->company = $_POST["company"];
    $service_data->city = $_POST["city"];
    $service_data->state = $_POST["state"];
    $service_data->latitude = floatval($_POST["latitude"]);
    $service_data->longitude = floatval($_POST["longitude"]);
    $service_data->cost = $_POST["cost"];
    $service_data->summary = $_POST["summary"];
    $service_data->description = $_POST["description"];
    $service_data->mileage = $_POST["mileage"];

    if (!empty($_POST["date"])
        && !empty($_POST["time"])
        && !empty($_POST["company"])
        && !empty($_POST["city"])
        && !empty($_POST["state"])
        && !empty($_POST["cost"])
        && !empty($_POST["summary"])
        && !empty($_POST["description"])
        && !empty($_POST["mileage"])
    ) {
        $service_date = DateTime::createFromFormat("m/j/Y h:i a", $_POST["date"] . " " . $_POST["time"]);
        $service_data->service_date = $service_date->format("Y-m-d H:i:s");

        $success = insert_vehicle_service_data($service_data);

        if ($success) {
            header("Location: /vehicle/" . url_name($vehicle_name) . "/service");
        }
    }
}

$page_title = $vehicle_data->name . " - Update Fuel Log";

include_once("includes/header.php");

$current_datetime = new DateTime($service_data->service_date);
?>
    <form method="POST">
        <table cellpadding="0" cellspacing="0">
            <tr class="header">
                <td colspan="3" scope="colgroup">
                    <img src="/images/<?php print(url_name($vehicle_data->name)); ?>.png" alt="<?php print($vehicle_data->name); ?> - <?php print($vehicle_data->year); ?> <?php print($vehicle_data->make); ?> <?php print($vehicle_data->model); ?> <?php print($vehicle_data->type); ?>" style="width:600px;"><br />
                    <p>Add Service Record</p>
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
                <td><input type="text" name="company" id="company" value="<?php print($service_data->company); ?>"></td>
            </tr>
            <tr>
                <td class="label"><label for="city">City</label></td>
                <td><input type="text" name="city" id="city" value="<?php print($service_data->city); ?>"></td>
            </tr>
            <tr>
                <td class="label"><label for="state">State</label></td>
                <td><input type="text" name="state" id="state" value="<?php print($service_data->state); ?>"></td>
            </tr>
            <tr>
                <td class="label"><label for="mileage">Mileage</label></td>
                <td><input type="text" name="mileage" id="mileage" value="<?php print($service_data->mileage); ?>"></td>
            </tr>
            <tr>
                <td class="label"><label for="cost">Cost</label></td>
                <td><input type="text" name="cost" id="cost" value="<?php print($service_data->cost); ?>"></td>
            </tr>
            <tr>
                <td class="label"><label for="summary">Summary</label></td>
                <td><input type="text" name="summary" id="summary" value="<?php print($service_data->summary); ?>"></td>
            </tr>
            <tr>
                <td class="label"><label for="description">Description</label></td>
                <td><textarea name="description" id="description"><?php print($service_data->description); ?></textarea></td>
            </tr>
        </table>
        <button type="submit"><?php print($process); ?></button>
        <input type="hidden" name="latitude" id="latitude" value="<?php print($service_data->latitude); ?>">
        <input type="hidden" name="longitude" id="longitude" value="<?php print($service_data->longitude); ?>">
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
