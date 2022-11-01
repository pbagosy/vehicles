<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header("Content-Type: text/html; charset=utf-8");
include_once("db.php");

$path = explode("/", $_SERVER["REQUEST_URI"]);
if(!array_key_exists(2, $path) != NULL) {
    header("Location: /");
}

$vehicle_name = $path[2];
$vehicle_data = fetch_vehicle_data($vehicle_name);

if ($vehicle_data == NULL) {
    $page_title = "No Data Found";
} else {
    $page_title = $vehicle_data->name . " - Vehicle Data";
}

include_once("includes/header.php");

if ($vehicle_data == NULL) {
    ?>
    <table cellspacing="0" cellpadding="0">
        <tr>
            <th><b>No vehicle data found for "<?php print($vehicle_name); ?>."</b></th>
        </tr>
    </table>
    <?php
} else {
    $commission_date = DateTime::createFromFormat("Y-m-d H:i:s", $vehicle_data->commission_date);
    $commission_date_display = $commission_date->format("d M Y");
    $decommission_date_display = "In Service";
    $decommissioned = false;
    if ($vehicle_data->decommission_date != NULL) {
        $decommissioned = true;
        $decommission_date = DateTime::createFromFormat("Y-m-d H:i:s", $vehicle_data->decommission_date);
        $decommission_date_display = "Decommissioned: " . $decommission_date->format("d M Y");
    }
?>

<?php
    $fuel_data = calculate_fuel_data($vehicle_data);
    $service_total = fetch_service_total($vehicle_data->vehicle_id);
?>

    <table class="vehicle_log" cellspacing="0" cellpadding="0">
        <tr>
            <th colspan="4" scope="colgroup">
                <img src="/images/<?php print(url_name($vehicle_data->name)); ?>.png" alt="<?php print($vehicle_data->name); ?> - <?php print($vehicle_data->year); ?> <?php print($vehicle_data->make); ?> <?php print($vehicle_data->model); ?> <?php print($vehicle_data->type); ?>" style="width:600px;"><br />
                <?php print($vehicle_data->year); ?> <?php print($vehicle_data->make); ?> <?php print($vehicle_data->model); ?> <?php print($vehicle_data->type); ?><br />
                Commissioned: <?php print($commission_date_display); ?><br />
                <?php print($decommission_date_display); ?>
            </th>
        </tr>
<?php
    if (!$decommissioned) {
?>
        <tr class="header">
            <td colspan="4"><p><a href="/vehicle/<?php print(url_name($vehicle_data->name)); ?>/mileage"><?php print(number_format($fuel_data["overall"]["total_miles"], 0, "", ",")); ?> Miles</a></p></td>
        </tr>
<?php
    } else {
?>
        <tr class="header">
            <td colspan="4"><?php print(number_format($fuel_data["overall"]["total_miles"], 0, "", ",")); ?> Miles</td>
        </tr>
<?php
    }
?>
        <tr class="header">
            <td colspan="2">
                <a href="/vehicle/<?php print(url_name($vehicle_name)); ?>/fuel"><img src="/images/fuel.png" style="width:200px; cursor:pointer;" alt="View Fuel Record"></a>
                <?php if (!$decommissioned) { ?>&nbsp;&nbsp;<a href="/vehicle/<?php print(url_name($vehicle_name)); ?>/fuel/add"><img src="/images/add.png" style="width:200px; cursor:pointer;" alt="Add Fuel Record"></a><?php } ?>
            </td>
            <td colspan="2">
                <a href="/vehicle/<?php print(url_name($vehicle_name)); ?>/service"><img src="/images/service.png" style="width:200px;" alt="View Service Record"></a>
                <?php if (!$decommissioned) { ?>&nbsp;&nbsp;<a href="/vehicle/<?php print(url_name($vehicle_name)); ?>/service/add"><img src="/images/add.png" style="width:200px;" alt="Add Service Record"></a><?php } ?>
            </td>
        </tr>
        <tr>
            <td class="hline">Days:</td>
            <td><?php print($fuel_data["overall"]["total_days"]); ?></td>
            <td class="hline">Gallons:</td>
            <td><?php print($fuel_data["overall"]["total_gallons"]); ?></td>
        </tr>
        <tr>
            <td class="hline">Miles/Day:</td>
            <td><?php print($fuel_data["overall"]["miles_per_day"]); ?></td>
            <td class="hline">Economy:</td>
            <td><?php print($fuel_data["overall"]["total_economy"]); ?> MPG</td>
        </tr>
        <tr>
            <td class="hline">Fuel Cost:</td>
            <td>$<?php print($fuel_data["overall"]["total_cost"]); ?></td>
            <td class="hline">Operating:</td>
            <td>$<?php print($fuel_data["overall"]["total_operating"]); ?>/mile</td>
        </tr>
        <tr>
            <td class="hline">Average Price:</td>
            <td>$<?php print($fuel_data["overall"]["average_price"]); ?>/gallon</td>
            <td class="hline">Average Tank:</td>
            <td><?php print($fuel_data["overall"]["average_days"]); ?> days</td>
        </tr>
        <tr>
            <td class="hline">Origination Cost:</td>
            <td>$<?php print(number_format($vehicle_data->origination_cost, 2, ".", ",")); ?></td>
            <td class="hline">Service Cost:</td>
            <td>$<?php print(number_format($service_total->total_price, 2, ".", ",")); ?></td>
        </tr>
    </table>

    <button id="add_button_top" class="float-left submit-button" onclick="vehicleSelect();">VEHICLES</button>

    <script type="text/javascript">
        function vehicleSelect(){
            window.location="/";
        }
    </script>
    <?php
}

include_once("includes/footer.php");
