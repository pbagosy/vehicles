<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$path = explode("/", $_SERVER["REQUEST_URI"]);
if(!array_key_exists(2, $path) != NULL) {
    header("Location: /");
}

header("Content-Type: text/html; charset=utf-8");
include_once("db.php");

$vehicle_name = $path[2];
$vehicle_data = fetch_vehicle_data($vehicle_name);

if ($vehicle_data == NULL) {
    $page_title = "No Vehicle Found";
} else {
    $page_title = $vehicle_data->name . " - Fuel Log";
}

include_once("includes/header.php");

if ($vehicle_data == NULL) {
    ?>
    <table>
        <tr>
            <th><b>No vehicle data found for "<?php print($vehicle_name); ?>."</b></th>
        </tr>
    </table>
<?php
} else {
    $commission_date = DateTime::createFromFormat("Y-m-d H:i:s", $vehicle_data->commission_date);
    $commission_date_display = $commission_date->format("d M Y");
    $decommission_date_display = "Present";
    if ($vehicle_data->decommission_date != NULL) {
        $decommission_date = DateTime::createFromFormat("Y-m-d H:i:s", $vehicle_data->decommission_date);
        $decommission_date_display = $decommission_date->format("d M Y");
    }
?>
    <table cellpadding="0" cellspacing="0">
        <tr class="header">
            <td colspan="8" scope="colgroup">
                <img src="/images/<?php print(url_name($vehicle_data->name)); ?>.png" alt="<?php print($vehicle_data->name); ?> - <?php print($vehicle_data->year); ?> <?php print($vehicle_data->make); ?> <?php print($vehicle_data->model); ?> <?php print($vehicle_data->type); ?>" style="width:600px;"><br />
                <p>Fuel Record</p>
            </td>
        </tr>
        <tr>
            <th>PPG</th>
            <th>Gallons</th>
            <th>Cost</th>
            <th>Mileage</th>
            <th>Days</th>
            <th>Miles</th>
            <th>Economy</th>
            <th>Operating</th>
        </tr>
<?php
        $fuel_data = calculate_fuel_data($vehicle_data);

        if (!array_key_exists("records", $fuel_data)) {
?>
            <tr class="r0">
                <td colspan="8" style="text-align:center">No data yet.</td>
            </tr>
<?php
        } else {
            $row_style = "";
            foreach ($fuel_data["records"] as $row) {
                $row_style = ($row_style == "r0") ? "r1" : "r0";
?>
                <tr class="<?php print($row_style); ?>">
                    <td colspan="3"><b><a href="/vehicle/<?php print(url_name($vehicle_name)); ?>/fuel/edit/<?php print($row["fuel_id"]); ?>"><?php print($row["date"]); ?> <?php print($row["time"]); ?></a></b></td>
                    <td colspan="5"><b><?php print($row["company"]); ?> in <?php print($row["city"]); ?>, <?php print($row["state"]); ?></b></td>
                </tr>
                <tr class="<?php print($row_style); ?>">
                    <td class="right nowrap">$<?php print($row["price"]); ?></td>
                    <td class="right nowrap"><?php print($row["gallons"]); ?></td>
                    <td class="right nowrap">$<?php print($row["cost"]); ?></td>
                    <td class="right nowrap"><?php print($row["mileage"]); ?></td>
                    <td class="right nowrap"><?php print($row["days"]); ?></td>
                    <td class="right nowrap"><?php print($row["miles"]); ?></td>
                    <td class="right nowrap"><?php print($row["economy"]); ?> MPG</td>
                    <td class="right nowrap">$<?php print($row["operating"]); ?> /mile</td>
                </tr>
<?php
            }
        }
?>
    </table>

    <button id="add_button_top" class="float-left submit-button" onclick="goBack();">RETURN</button>

    <script type="text/javascript">
        function goBack(){
            window.location="/vehicle/<?php print(url_name($vehicle_name)); ?>";
        }
    </script>
<?php
}

include_once("includes/footer.php");
