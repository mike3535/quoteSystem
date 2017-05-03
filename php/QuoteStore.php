<?php
require_once("Quote.php");

class QuoteStore {

    public function getQuoteArr($quoteID) {
        try {
            $username = MYSQL_USER;
            $password = MYSQL_PASS;
            $dsn = "mysql:host=" . MYSQL_DB . ";dbname=" . MYSQL_USER;
            $pdo = new PDO($dsn, $username, $password);
        } catch (PDOexception $e) {
            echo "Connection to database failed: ";
        }

        $prepared = $pdo->prepare("SELECT * FROM quotes WHERE id=?;");

        $prepared->execute(array($quoteID));
        $quoteArr = $prepared->fetchAll()[0];

        return $quoteArr;
    }

    public function getQuote($quoteID) {
        try {
            $username = MYSQL_USER;
            $password = MYSQL_PASS;
            $dsn = "mysql:host=" . MYSQL_DB . ";dbname=" . MYSQL_USER;
            $pdo = new PDO($dsn, $username, $password);
        } catch (PDOexception $e) {
            echo "Connection to database failed: ";
        }

        $prepared = $pdo->prepare("SELECT * FROM quotes WHERE id=?;");

        $prepared->execute(array($quoteID));
        $quoteArr = $prepared->fetchAll()[0];

        return new Quote($quoteArr["customerID"], $quoteArr["associateID"], $quoteArr["creationDate"], $quoteArr["secretNote"], $quoteArr["discount"], $quoteArr["finalPrice"], $quoteArr["status"], unserialize($quoteArr["lineItems"]), $quoteArr["finalized"], $quoteArr["email"]);
    }

    public function getCustomersQuotes($customerID) {
        try {
            $username = MYSQL_USER;
            $password = MYSQL_PASS;
            $dsn = "mysql:host=" . MYSQL_DB . ";dbname=" . MYSQL_USER;
            $pdo = new PDO($dsn, $username, $password);
        } catch (PDOexception $e) {
            echo "Connection to database failed: ";
        }

        $prepared = $pdo->prepare("SELECT * FROM quotes WHERE customerID=?;");

        $prepared->execute(array($customerID));

        return $prepared->fetchAll();
    }

    public function getQuotesByStatus($status) {
        try {
            $username = MYSQL_USER;
            $password = MYSQL_PASS;
            $dsn = "mysql:host=" . MYSQL_DB . ";dbname=" . MYSQL_USER;
            $pdo = new PDO($dsn, $username, $password);
        } catch (PDOexception $e) {
            echo "Connection to database failed: ";
        }

        $prepared = $pdo->prepare("SELECT * FROM quotes WHERE status=?;");

        $prepared->execute(array($status));
        $quoteArr = $prepared->fetchAll();

        return $quoteArr;
    }

    public function getQuotesByDate($date) {
        try {
            $username = MYSQL_USER;
            $password = MYSQL_PASS;
            $dsn = "mysql:host=" . MYSQL_DB . ";dbname=" . MYSQL_USER;
            $pdo = new PDO($dsn, $username, $password);
        } catch (PDOexception $e) {
            echo "Connection to database failed: ";
        }

        $prepared = $pdo->prepare("SELECT * FROM quotes WHERE creationDate=?;");

        $prepared->execute(array($date));
        $quoteArr = $prepared->fetchAll();

        return $quoteArr;
    }

    public function getQuotesByAssociate($associateID) {
        try {
            $username = MYSQL_USER;
            $password = MYSQL_PASS;
            $dsn = "mysql:host=" . MYSQL_DB . ";dbname=" . MYSQL_USER;
            $pdo = new PDO($dsn, $username, $password);
        } catch (PDOexception $e) {
            echo "Connection to database failed: ";
        }

        $prepared = $pdo->prepare("SELECT * FROM quotes WHERE associateID=?;");

        $prepared->execute(array($associateID));
        $quoteArr = $prepared->fetchAll();

        return $quoteArr;
    }

    public function getQuotesByCustomer($customerID) {
        try {
            $username = MYSQL_USER;
            $password = MYSQL_PASS;
            $dsn = "mysql:host=" . MYSQL_DB . ";dbname=" . MYSQL_USER;
            $pdo = new PDO($dsn, $username, $password);
        } catch (PDOexception $e) {
            echo "Connection to database failed: ";
        }

        $prepared = $pdo->prepare("SELECT * FROM quotes WHERE customerID=?;");

        $prepared->execute(array($customerID));
        $quoteArr = $prepared->fetchAll();

        return $quoteArr;
    }
}
?>