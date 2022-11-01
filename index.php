<?php

include_once("db.php");
include_once("includes/header.php");

$vehicles = fetch_vehicles();

?>
   <table>
<?php
foreach ($vehicles as $row) {
?>
    <tr class="r0">
      <td style="text-align:center;"><a href="/vehicle/<?php print(url_name($row["name"])); ?>"><img src="/images/<?php print(url_name($row["name"])); ?>.png" alt="<?php print($row["name"]); ?> - <?php print($row["year"]); ?> <?php print($row["make"]); ?> <?php print($row["model"]); ?> <?php print($row["type"]); ?>" style="width:600px;<?php if ($row["decommission_date"]) { ?> filter:grayscale(1);<?php } ?>"></a></td>
    </tr>        
<?php
}
?>
   </table>
<?php
include_once("includes/footer.php");
