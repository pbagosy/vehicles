<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
date_default_timezone_set("America/New_York");

define("HOSTNAME", "mysql.bagosy.com");
define("USERNAME", "ocn_cld");
define("PASSWORD", 'MyOceanCloudP4$$word');
define("DATABASE", "bagosy_ocncld");
define("CHARSET",  "utf8mb4");

function pretty_print($output) {
    print("<pre>");
    print_r($output);
    print("</pre>");
}

function url_name($vehicle_name) {
    return strtolower(str_replace(" ", "_", $vehicle_name));
}

function createConnection() {
    /**
     * Returns a PDO connection.
     *
     * @return PDO
     */
    $pdo = false;
    try {
        $dsn = "mysql:host=".HOSTNAME.";dbname=".DATABASE.";charset=".CHARSET;
        $pdo = new PDO($dsn, USERNAME, PASSWORD);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        pretty_print($e);
    }

    return $pdo;
}

function fetch_vehicles() {
    /**
     * Returns a list of all vehicles.
     *
     * @return  array   An array with every vehicle's meta data.
     */
    $conn = createConnection();

    try {
        $query = $conn->prepare("SELECT * FROM vehicle ORDER BY decommission_date ASC");
        $query->execute();
        return $query->fetchAll();
    } catch (Exception $e) {
        pretty_print($e);
        return false;
    }
}

function fetch_vehicle_data($vehicle_id) {
    /**
     * Returns specific vehicle metadata.
     *
     * @param   int    $vehicle_id   The name or id of the vehicle to get data for.
     *
     * @return  array                An array with parsed values.
     */
    $conn = createConnection();

    $vehicle_name = strtoupper(str_replace("_", " ", $vehicle_id));

    try {
        $query = $conn->prepare("SELECT * FROM vehicle WHERE vehicle_id = :vehicle_id OR UPPER(name) = :vehicle_name");
        $query->bindParam(":vehicle_id", $vehicle_id, PDO::PARAM_INT);
        $query->bindParam(":vehicle_name", $vehicle_name, PDO::PARAM_STR);
        $query->execute();

        return $query->fetchObject();
    } catch (Exception $e) {
        pretty_print($e);
        return false;
    }
}

function fetch_service_total($vehicle_id)
{
    /**
     * Sums the total of all service logs for a specific vehicle.
     *
     * @param   int $vehicle_id The id of the vehicle to get totals for.
     *
     * @return  array               An array with parsed values.
     */
    try {
        $conn = createConnection();

        $query = $conn->prepare("SELECT SUM(cost) as total_price FROM service_log WHERE vehicle_id = :vehicle_id");
        $query->bindParam(":vehicle_id", $vehicle_id, PDO::PARAM_INT);
        $query->execute();
        return $query->fetchObject();

    } catch (Exception $e) {
        pretty_print($e);
        return false;
    }
}

function update_vehicle_mileage($vehicle_id, $mileage) {
    /**
     * Updates the current mileage of a specific vehicle.
     *
     * @param   int    $vehicle_id  The id of the vehicle to update.
     * @param   int    $mileage     The new mileage.
     *
     * @return  bool                True on success, otherwise false.
     */
    $conn = createConnection();

    try {
        $query = $conn->prepare("UPDATE vehicle SET mileage = :mileage WHERE vehicle_id = :vehicle_id");
        $query->bindParam(":mileage", $mileage, PDO::PARAM_INT);
        $query->bindParam(":vehicle_id", $vehicle_id, PDO::PARAM_INT);
        $query->execute();

        return true;
    } catch (Exception $e) {
        pretty_print($e);
        return false;
    }
}

function fetch_vehicle_fuel_data($vehicle_id) {
    /**
     * Returns specific vehicle fuel record.
     *
     * @param   int    $vehicle_id   The id of the vehicle to get data for.
     *
     * @return  array                An array with vehicle fuel data values.
     */
    $conn = createConnection();

    try {
        $query = $conn->prepare("SELECT * FROM fuel_log WHERE vehicle_id = :vehicle_id");
        $query->bindParam(":vehicle_id", $vehicle_id, PDO::PARAM_INT);
        $query->execute();
        return $query->fetchAll();
    } catch (Exception $e) {
        pretty_print($e);
        return false;
    }
}

function insert_vehicle_service_data($service_data) {
    /**
     * Inserts or updates a vehicle service record.
     *
     * @param   object  $fuel_data  An object containing all data for a fuel entry.
     *
     * @return  bool                True on success, otherwise false.
     */
    $conn = createConnection();

    try {
        $raw_query = "INSERT INTO service_log (
                             service_id,
                             vehicle_id,
                             company,
                             city,
                             state,
                             latitude,
                             longitude,
                             cost,
                             summary,
                             description,
                             mileage,
                             service_date)
                      VALUES (
                             :fuel_id,
                             :vehicle_id,
                             :company,
                             :city,
                             :state,
                             :latitude,
                             :longitude,
                             :cost,
                             :summary,
                             :description,
                             :mileage,
                             :service_date)
                ON DUPLICATE KEY UPDATE
                             company = :company,
                             city = :city,
                             state = :state,
                             latitude = :latitude,
                             longitude = :longitude,
                             cost = :cost,
                             summary = :summary,
                             description = :description,
                             mileage = :mileage,
                             service_date = :service_date";

        $query = $conn->prepare($raw_query);
        $query->bindParam(":fuel_id", $service_data->fuel_id, PDO::PARAM_INT);
        $query->bindParam(":vehicle_id", $service_data->vehicle_id, PDO::PARAM_INT);
        $query->bindParam(":company", $service_data->company);
        $query->bindParam(":city", $service_data->city);
        $query->bindParam(":state", $service_data->state);
        $query->bindParam(":latitude", $service_data->latitude);
        $query->bindParam(":longitude", $service_data->longitude);
        $query->bindParam(":cost", $service_data->cost);
        $query->bindParam(":summary", $service_data->summary);
        $query->bindParam(":description", $service_data->description);
        $query->bindParam(":mileage", $service_data->mileage, PDO::PARAM_INT);
        $query->bindParam(":service_date", $service_data->service_date);
        $query->execute();

        return true;
    } catch (Exception $e) {
        pretty_print($e);
        return false;
    }
}

function insert_vehicle_fuel_data($fuel_data) {
    /**
     * Inserts or updates a vehicle fuel record.
     *
     * @param   object  $fuel_data   An object containing all data for a fuel entry.
     *
     * @return  bool                 True on success, otherwise false.
     */
    $conn = createConnection();

    try {
        $raw_query = "INSERT INTO fuel_log (
                             fuel_id,
                             vehicle_id,
                             company,
                             city,
                             state,
                             latitude,
                             longitude,
                             price,
                             gallons,
                             mileage,
                             fuel_date)
                      VALUES (
                             :fuel_id,
                             :vehicle_id,
                             :company,
                             :city,
                             :state,
                             :latitude,
                             :longitude,
                             :price,
                             :gallons,
                             :mileage,
                             :fuel_date)
                ON DUPLICATE KEY UPDATE
                             company = :company,
                             city = :city,
                             state = :state,
                             latitude = :latitude,
                             longitude = :longitude,
                             price = :price,
                             gallons = :gallons,
                             mileage = :mileage,
                             fuel_date = :fuel_date";

        $query = $conn->prepare($raw_query);
        $query->bindParam(":fuel_id", $fuel_data->fuel_id, PDO::PARAM_INT);
        $query->bindParam(":vehicle_id", $fuel_data->vehicle_id, PDO::PARAM_INT);
        $query->bindParam(":company", $fuel_data->company);
        $query->bindParam(":city", $fuel_data->city);
        $query->bindParam(":state", $fuel_data->state);
        $query->bindParam(":latitude", $fuel_data->latitude);
        $query->bindParam(":longitude", $fuel_data->longitude);
        $query->bindParam(":price", $fuel_data->price);
        $query->bindParam(":gallons", $fuel_data->gallons);
        $query->bindParam(":mileage", $fuel_data->mileage, PDO::PARAM_INT);
        $query->bindParam(":fuel_date", $fuel_data->fuel_date);
        $query->execute();

        return true;
    } catch (Exception $e) {
        pretty_print($e);
        return false;
    }
}

function fetch_vehicle_service_data($vehicle_id) {
    /**
     * Returns vehicle service record.
     *
     * @param   int    $vehicle_id   The id of the vehicle to get service data for.
     *
     * @return  array                An array with parsed values.
     */
    $conn = createConnection();

    try {
        $query = $conn->prepare("SELECT * FROM service_log WHERE vehicle_id = :vehicle_id");
        $query->bindParam(":vehicle_id", $vehicle_id, PDO::PARAM_INT);
        $query->execute();
        return $query->fetchAll();
    } catch (Exception $e) {
        pretty_print($e);
        return false;
    }
}

function fetch_fuel_data($vehicle_id, $fuel_id) {
    /**
     * Returns vehicle fuel record.
     *
     * @param   int    $vehicle_id  The id of the vehicle to get data for.
     * @param   int    $fuel_id     The id of the fuel record to get data for.
     *
     * @return  array                An array with parsed values.
     */
    $conn = createConnection();

    try {
        $query = $conn->prepare("SELECT * FROM fuel_log WHERE vehicle_id = :vehicle_id AND fuel_id = :fuel_id");
        $query->bindParam(":vehicle_id", $vehicle_id, PDO::PARAM_INT);
        $query->bindParam(":fuel_id", $fuel_id, PDO::PARAM_INT);
        $query->execute();
        return $query->fetchObject();
    } catch (Exception $e) {
        pretty_print($e);
        return false;
    }
}

function fetch_service_data($vehicle_id, $service_id) {
    /**
     * Returns vehicle service record.
     *
     * @param   int    $vehicle_id      The id of the vehicle to get data for.
     * @param   int    $service_id  The id of the service record to get data for.
     *
     * @return  array                An array with parsed values.
     */
    $conn = createConnection();

    try {
        $query = $conn->prepare("SELECT * FROM service_log WHERE vehicle_id = :vehicle_id AND service_id = :service_id");
        $query->bindParam(":vehicle_id", $vehicle_id, PDO::PARAM_INT);
        $query->bindParam(":service_id", $service_id, PDO::PARAM_INT);
        $query->execute();
        return $query->fetchObject();
    } catch (Exception $e) {
        pretty_print($e);
        return false;
    }
}

function calculate_fuel_data($vehicle_data) {
    /**
     * @param   $vehicle_data   object The vehicle dataset to parse.
     *
     * @return  array           An array with parsed values.
     */

    $fuel_data = fetch_vehicle_fuel_data($vehicle_data->vehicle_id);

    $record_count = 0;
    $total_price = 0;
    $total_gallons = 0;
    $total_cost = 0;
    $total_miles = $vehicle_data->initial_mileage;
    $row_style = "r1";
    $start_date = DateTime::createFromFormat("Y-m-d H:i:s", $vehicle_data->commission_date);
    $last_date = "";

    $return_data = array();

    if (count($fuel_data) > 0) {

        foreach ($fuel_data as $row) {
            $end_date = DateTime::createFromFormat("Y-m-d H:i:s", $row["fuel_date"]);
            $end_date_display = array("date" => $end_date->format("d M Y"), "time" => $end_date->format("g:ia"));
            $display_miles = $row["mileage"] - $total_miles;
            $current_miles = $record_count >= 0 ? $row["mileage"] - $total_miles : 0;
            $gallons = $row["gallons"] > 0 ? $row["gallons"] : 1;
            $economy = $current_miles / $gallons;
            $cost = $row["price"] * $row["gallons"];
            $operating = $current_miles > 0 ? ($cost / $current_miles) : 0;
            $days = 0;
            if ((is_a($last_date, "DateTime"))) {
                $interval_start = $start_date;
                $interval_end = $end_date;
                $interval_start->setTime(0, 0, 0);
                $interval_end->setTime(23, 59, 59);
                $interval = date_diff($last_date, $end_date);
                $days = $interval->format("%a");
            }

            $return_data["records"][] = array(
                "fuel_id" => $row["fuel_id"],
                "fuel_date" => $end_date,
                "date" => $end_date_display["date"],
                "time" => $end_date_display["time"],
                "company" => $row["company"],
                "city" => $row["city"],
                "state" => $row["state"],
                "price" => number_format($row["price"], 3),
                "gallons" => number_format($gallons, 3),
                "cost" => number_format($cost, 2),
                "mileage" => intval($row["mileage"]),
                "miles" => intval($display_miles),
                "days" => $days,
                "economy" => number_format($economy, 2),
                "operating" => number_format($operating, 2),
            );

            $record_count++;
            $total_price += $row["price"];
            $total_gallons += $row["gallons"];
            $total_cost += $cost;
            $total_miles += $current_miles;
            $row_style = ($row_style == "r1") ? "r0" : "r1";
            $last_date = $end_date;
        }

        $end_date = new DateTime("now");
        if ($vehicle_data->decommission_date != NULL) {
            $end_date = DateTime::createFromFormat("Y-m-d H:i:s", date($vehicle_data->decommission_date));
        }

        $interval = date_diff($start_date, $end_date);
        $total_days = $interval->format("%a");
        if (0 >= $total_days) {
            $total_days = 1;
        }
        if ($vehicle_data->mileage > $total_miles) {
            $total_miles = $vehicle_data->mileage;
        }
        $average_price = $total_price / $record_count;
        $total_economy = $total_miles / $total_gallons;
        $total_operating = $total_cost / $total_miles;
        $average_days = $total_days / $record_count;

        $return_data["overall"] = [
            "total_days" => intval($total_days),
            "total_gallons" => number_format($total_gallons, 3),
            "miles_per_day" => number_format($total_miles / $total_days, 2),
            "total_economy" => number_format($total_economy, 2),
            "total_cost" => number_format($total_cost, 2),
            "total_operating" => number_format($total_operating, 2),
            "average_price" => number_format($average_price, 3),
            "total_miles" => intval($total_miles),
            "average_days" => intval($average_days),
        ];
    }

    return $return_data;
}
