<?php
class SalesAssociate {
    public $name;
    public $username;
    public $password;
    public $commission;
    public $address;

    public function __construct($name, $username, $password, $commission, $address) {
        $this->name = $name;
        $this->username = $username;
        $this->password = $password;
        $this->commission = $commission;
        $this->address = $address;
    }

    public function save() {
        try {
            $username = MYSQL_USER;
            $password = MYSQL_PASS;
            $dsn = "mysql:host=" . MYSQL_DB . ";dbname=" . MYSQL_USER;
            $pdo = new PDO($dsn, $username, $password);
        } catch (PDOexception $e) {
            echo "Connection to database failed: ";
        }

        $prepared = $pdo->prepare("INSERT INTO associate (name, username, password, commission, address) VALUES (?, ?, ?, ?, ?);");

        $prepared->execute(array($this->name, $this->username, $this->password, $this->commission, $this->address));
    }

    public function update($associateID) {
        try {
            $username = MYSQL_USER;
            $password = MYSQL_PASS;
            $dsn = "mysql:host=" . MYSQL_DB . ";dbname=" . MYSQL_USER;
            $pdo = new PDO($dsn, $username, $password);
        } catch (PDOexception $e) {
            echo "Connection to database failed: ";
        }
        $prepared = $pdo->prepare("UPDATE associate SET name=?, username=?, password=?, commission=?, address=? WHERE id=?;");

        $prepared->execute(array($this->name, $this->username, $this->password, $this->commission, $this->address, $associateID));
    }

    public function delete($associateID) {
        try {
            $username = MYSQL_USER;
            $password = MYSQL_PASS;
            $dsn = "mysql:host=" . MYSQL_DB . ";dbname=" . MYSQL_USER;
            $pdo = new PDO($dsn, $username, $password);
        } catch (PDOexception $e) {
            echo "Connection to database failed: ";
        }

        $prepared = $pdo->prepare("DELETE FROM associate WHERE id=?;");

        $prepared->execute(array($associateID));
    }
}
?>