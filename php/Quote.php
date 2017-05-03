<?php
class Quote {
    public $customerID;
    public $associateID;
    public $creationDate;
    public $secretNote;
    public $discount;
    public $finalPrice;
    public $status;
    public $lineItems;
    public $finalized;
    public $email;

    public function __construct($customerID, $associateID, $creationDate, $secretNote, $discount, $finalPrice, $status, $lineItems, $finalized, $email) {
        $this->customerID = $customerID;
        $this->associateID = $associateID;
        $this->creationDate = $creationDate;
        $this->secretNote = $secretNote;
        $this->discount = $discount;
        $this->finalPrice = $finalPrice;
        $this->status = $status;
        $this->lineItems = $lineItems;
        $this->finalized = $finalized;
        $this->email = $email;
    }

    /*
     * Finalize the quote
     */
    public function finalizeQuote($quoteID) {
        $this->finalized = 1;

        update($quoteID);
    }

    /*
     * Save quote to database
     */
    public function save() {
        try {
            $username = MYSQL_USER;
            $password = MYSQL_PASS;
            $dsn = "mysql:host=" . MYSQL_DB . ";dbname=" . MYSQL_USER;
            $pdo = new PDO($dsn, $username, $password);
        } catch (PDOexception $e) {
            echo "Connection to database failed: ";
        }

        $this->lineItemsSer = serialize($this->lineItems);        

        $prepared = $pdo->prepare("INSERT INTO quotes (customerID, associateID, creationDate, secretNote, discount, finalPrice, status, lineItems, finalized, email) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?);");

        $prepared->execute(array($this->customerID, $this->associateID, $this->creationDate, $this->secretNote, $this->discount, $this->finalPrice, $this->status, $this->lineItemsSer, $this->finalized, $this->email));
    }

    /*
     * Update quote
     */
    public function update($quoteID) {
        try {
            $username = MYSQL_USER;
            $password = MYSQL_PASS;
            $dsn = "mysql:host=" . MYSQL_DB . ";dbname=" . MYSQL_USER;
            $pdo = new PDO($dsn, $username, $password);
        } catch (PDOexception $e) {
            echo "Connection to database failed: ";
        }

        $this->lineItemsSer = serialize($this->lineItems);        

        $prepared = $pdo->prepare("UPDATE quotes SET customerID=?, associateID=?, creationDate=?, secretNote=?, discount=?, finalPrice=?, status=?, lineItems=?, finalized=?, email=? WHERE id=?;");

        $prepared->execute(array($this->customerID, $this->associateID, $this->creationDate, $this->secretNote, $this->discount, $this->finalPrice, $this->status, $this->lineItemsSer, $this->finalized, $this->email, $quoteID));
    }

    /*
     * Delete quote from database
     */
    public function delete($quoteID) {
        try {
            $username = MYSQL_USER;
            $password = MYSQL_PASS;
            $dsn = "mysql:host=" . MYSQL_DB . ";dbname=" . MYSQL_USER;
            $pdo = new PDO($dsn, $username, $password);
        } catch (PDOexception $e) {
            echo "Connection to database failed: ";
        }

        $prepared = $pdo->prepare("DELETE FROM quotes WHERE id=?;");

        $prepared->execute(array($quoteID));
    }
}
?>