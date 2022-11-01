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

if (!empty($_POST)) {
    if (!empty($_POST["mileage"])) {
        $success = update_vehicle_mileage($vehicle_data->vehicle_id, $_POST["mileage"]);

        if ($success) {
            header("Location: /vehicle/" . url_name($vehicle_name));
        }
    }
}

$page_title = $vehicle_data->name . " - Update Fuel Log";

include_once("includes/header.php");

?>
    <form method="POST">
        <table cellpadding="0" cellspacing="0">
            <tr class="header">
                <td colspan="2" scope="colgroup">
                    <img src="/images/<?php print(url_name($vehicle_data->name)); ?>.png" alt="<?php print($row["name"]); ?> - <?php print($row["year"]); ?> <?php print($row["make"]); ?> <?php print($row["model"]); ?> <?php print($row["type"]); ?>" style="width:600px;"><br />
                    <p>Update Mileage</p>
                </td>
            </tr>
            <tr>
                <td class="label"><label for="mileage">Mileage</label></td>
                <td><input type="text" name="mileage" id="mileage" value="<?php echo $vehicle_data->mileage; ?>"></td>
            </tr>
        </table>
        <button type="submit">EDIT</button>
    </form>
    <button id="cancel_button" onclick="cancelUpdate();">RETURN</button>
    <script type="text/javascript">
        function cancelUpdate(){
            window.location="/vehicle/<?php print(url_name($vehicle_name)); ?>";
        }
    </script>
<?php
include_once("includes/footer.php");
