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
    $page_title = $vehicle_data->name . " - Service Log";
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
?>
    <table cellpadding="0" cellspacing="0">
        <tr class="header">
            <td colspan="3" scope="colgroup">
                <img src="/images/<?php print(url_name($vehicle_data->name)); ?>.png" alt="<?php print($vehicle_data->name); ?> - <?php print($vehicle_data->year); ?> <?php print($vehicle_data->make); ?> <?php print($vehicle_data->model); ?> <?php print($vehicle_data->type); ?>" style="width:600px;"><br />
                <p>Service Record</p>
            </td>
        </tr>
        <tr>
            <th>Summary</th>
            <th>Cost</th>
            <th>Mileage</th>
        </tr>
<?php
        $service_data = fetch_vehicle_service_data($vehicle_data->vehicle_id);

        if (count($service_data) <= 0) {
?>
            <tr class="r0">
                <td colspan="8" style="text-align:center">No data yet.</td>
            </tr>
<?php
        } else {
            $row_style = "";
            foreach ($service_data as $row) {
                $row_style = ($row_style == "r0") ? "r1" : "r0";
                $service_date = DateTime::createFromFormat("Y-m-d H:i:s", $row["service_date"]);
                $service_date_display = $service_date->format("d M Y");
                $service_time_display = $service_date->format("g:ia");

?>
        <tr class="<?php print($row_style); ?>">
            <td><b><a href="/vehicle/<?php print(url_name($vehicle_name)); ?>/service/edit/<?php print($row["service_id"]); ?>"><?php print($service_date_display); ?> <?php print($service_time_display); ?></a></b></td>
            <td colspan="2"><b><?php print($row["company"]); ?> in <?php print($row["city"]); ?>, <?php print($row["state"]); ?></b></td>
        </tr>
        <tr class="<?php print($row_style); ?>">
            <td class="nowrap"><?php print($row["summary"]); ?></td>
            <td class="right nowrap">$<?php print($row["cost"]); ?></td>
            <td class="right nowrap"><?php print($row["mileage"]); ?></td>
        </tr>
        <tr class="<?php print($row_style); ?>">
            <td colspan="3"><?php print($row["description"]); ?></td>
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
