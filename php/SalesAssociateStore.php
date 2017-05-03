<?php
require_once("SalesAssociate.php");

class SalesAssociateStore {

    public function getAssociates() {
        try {
            $username = MYSQL_USER;
            $password = MYSQL_PASS;
            $dsn = "mysql:host=" . MYSQL_DB . ";dbname=" . MYSQL_USER;
            $pdo = new PDO($dsn, $username, $password);
        } catch (PDOexception $e) {
            echo "Connection to database failed: ";
        }

        $prepared = $pdo->prepare("SELECT * FROM associate;");

        $prepared->execute();

        return $prepared->fetchAll();
    }

    public function getAssociate($associateID) {
        try {
            $username = MYSQL_USER;
            $password = MYSQL_PASS;
            $dsn = "mysql:host=" . MYSQL_DB . ";dbname=" . MYSQL_USER;
            $pdo = new PDO($dsn, $username, $password);
        } catch (PDOexception $e) {
            echo "Connection to database failed: ";
        }

        $prepared = $pdo->prepare("SELECT * FROM associate WHERE id=?;");

        $prepared->execute(array($associateID));
        $associateArr = $prepared->fetchAll()[0];

        return new SalesAssociate($associateArr["name"], $associateArr["username"], $associateArr["password"], $associateArr["commission"], $associateArr["address"]);
    }

    public function getAssociateByUsername($associateUsername) {
        try {
            $username = MYSQL_USER;
            $password = MYSQL_PASS;
            $dsn = "mysql:host=" . MYSQL_DB . ";dbname=" . MYSQL_USER;
            $pdo = new PDO($dsn, $username, $password);
        } catch (PDOexception $e) {
            echo "Connection to database failed: ";
        }

        $prepared = $pdo->prepare("SELECT * FROM associate WHERE username=?;");

        $prepared->execute(array($associateUsername));
        $associateArr = $prepared->fetchAll()[0];

        return new SalesAssociate($associateArr["name"], $associateArr["username"], $associateArr["password"], $associateArr["commission"], $associateArr["address"]);
    }

    public function getAssociateIDByUsername($associateUsername) {
        try {
            $username = MYSQL_USER;
            $password = MYSQL_PASS;
            $dsn = "mysql:host=" . MYSQL_DB . ";dbname=" . MYSQL_USER;
            $pdo = new PDO($dsn, $username, $password);
        } catch (PDOexception $e) {
            echo "Connection to database failed: ";
        }

        $prepared = $pdo->prepare("SELECT id FROM associate WHERE username=?;");

        $prepared->execute(array($associateUsername));
        $result = $prepared->fetchColumn();

        return $result;
    }

    public function getAssociateArr($associateID) {
        try {
            $username = MYSQL_USER;
            $password = MYSQL_PASS;
            $dsn = "mysql:host=" . MYSQL_DB . ";dbname=" . MYSQL_USER;
            $pdo = new PDO($dsn, $username, $password);
        } catch (PDOexception $e) {
            echo "Connection to database failed: ";
        }

        $prepared = $pdo->prepare("SELECT * FROM associate WHERE id=?;");

        $prepared->execute(array($associateID));
        $associateArr = $prepared->fetchAll()[0];

        return $associateArr;
    }
}
?>