<?php
require_once("../config/config.default.php");

class LoadCustomerData {

    function loadFromLegacy() {
        // Create connection
        $conn = new mysqli(MYSQL_LEGACY_HOST, MYSQL_LEGACY_USER, MYSQL_LEGACY_PASS, MYSQL_LEGACY_DB, MYSQL_LEGACY_PORT);

        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        $sql = "SELECT * FROM customers";
        $result = $conn->query($sql);

        $conn->close();

        return $result;
    }

    function getCustomer($customerID) {
        // Create connection
        $conn = new mysqli(MYSQL_LEGACY_HOST, MYSQL_LEGACY_USER, MYSQL_LEGACY_PASS, MYSQL_LEGACY_DB, MYSQL_LEGACY_PORT);

        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        $sql = "SELECT * FROM customers WHERE id=$customerID";
        $result = $conn->query($sql);
        $row = mysqli_fetch_assoc($result);

        $conn->close();

        return $row;
    }
}
?>