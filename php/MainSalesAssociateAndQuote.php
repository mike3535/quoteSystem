<?php

require_once("../config/config.default.php");
require_once("../functions/password_functions.php");

class MainSalesAssociateAndQuote {
    
    function getSalesAssociate($associateID=NULL) {
        // Create connection
        $conn = new mysqli(MYSQL_HOST, MYSQL_USER, MYSQL_PASS, MYSQL_DB, MYSQL_PORT);

        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        
        if (is_null($associateID)) {                                        
            $sql = "SELECT * FROM associate";
        }elseif (is_numeric($associateID)) {
            $sql = "SELECT * FROM associate WHERE associateID={$associateID}";
        }else{
            $sql = "SELECT * FROM associate WHERE userid='{$associateID}'";
        }
        echo $sql;
        $result = $conn->query($sql);
        //$row = mysqli_fetch_assoc($result);

        $conn->close();

        //return $row;
        return $result;
    }
    
    function updateSalesAssociate() {
        
    }
    
    function deleteSalesAssociate() {
        
    }
    
    function checkValidLogin($userid, $password) {
        $result = $this->getSalesAssociate($userid);
        $valid_user = false;
        
        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();
            $existing_password = $row["password"];
                
            $valid_user = password_check($password, $existing_password);
        }
        return $valid_user;
    }
}
?>