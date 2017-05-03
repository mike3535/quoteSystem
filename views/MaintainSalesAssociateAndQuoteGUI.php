<?php
require_once("../php/LoadCustomerData.php");
require_once("../php/Quote.php");
require_once("../php/QuoteStore.php");
require_once("../php/SalesAssociate.php");
require_once("../php/SalesAssociateStore.php");
require_once("../functions/password_functions.php");

$loadCustomerData = new LoadCustomerData();
$salesAssociateStore = new SalesAssociateStore();
$quoteStore = new QuoteStore();

if (isset($_POST["action"])) {
    switch ($_POST["action"]) {
        case "addAssociate":
            $associate = new SalesAssociate($_POST["name"], $_POST["username"], password_encrypt($_POST["password"]), $_POST["commission"], $_POST["address"]);
            $associate->save();
            break;
        case "loadAssociate":
            $curAssociate = $salesAssociateStore->getAssociateArr($_POST["associateID"]);
            echo json_encode($curAssociate);
            break;
        case "updateAssociate":
            $curAssociate = $salesAssociateStore->getAssociate($_POST["associateID"]);
            $curAssociate->name = $_POST["name"];
            $curAssociate->username = $_POST["username"];
            $curAssociate->password = password_encrypt($_POST["password"]);
            $curAssociate->commission = $_POST["commission"];
            $curAssociate->address = $_POST["address"];
            $curAssociate->update($_POST["associateID"]);
            break;
        case "deleteAssociate":
            $curAssociate = $salesAssociateStore->getAssociate($_POST["associateID"]);
            $curAssociate->delete($_POST["associateID"]);
            break;
        case "loadQuotesByStatus":
            $quotes = $quoteStore->getQuotesByStatus($_POST["status"]);
            echo json_encode($quotes);
            break;
        case "loadQuotesByDate":
            $quotes = $quoteStore->getQuotesByDate($_POST["date"]);
            echo json_encode($quotes);
            break;
        case "loadQuotesByAssociate":
            $quotes = $quoteStore->getQuotesByAssociate($_POST["associateID"]);
            echo json_encode($quotes);
            break;
        case "loadQuotesByCustomer":
            $quotes = $quoteStore->getQuotesByCustomer($_POST["customerID"]);
            echo json_encode($quotes);
            break;
    }
    exit;
}
?>
<html>
    <head>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    </head>
    <body>
        <h1>Maintain Sales Associates And Quotes</h1>
        
        <button style="margin-right: 16px" onclick="toggleViewSalesAssociate()">Sales Associates</button>
        <button style="margin-right: 16px" onclick="toggleViewQuotes()">Quotes</button>

        <div id="viewSalesAssociate" style="display: none; padding-top: 10px;">
            <select id="associateSelect" name="salesAssociateID" onchange="populateForm()">
                <option disabled selected value> -- select an associate -- </option>
                <?php
                $associateData = $salesAssociateStore->getAssociates();
            
                foreach ($associateData as $associate) {
                    echo "<option value='" . $associate["id"] . "'>" . $associate["name"] . "</option>";
                }
                ?>
            </select>
            <br>
            <br>
            <h3>Creating/Editing for Sales Associate:
            <br>
            <br>
            <button style="margin-right: 16px" onclick="toggleCreateSalesAssociate()">Add Sales Associate</button>
            <button style="margin-right: 16px" onclick="toggleEditSalesAssociate()">Edit Sales Associate</button>

            <div id="createSalesAssociate" style="display: none; padding-top: 10px;">
                Name:<br>
                <input type="text" id="assocName" name="assocName"><br>
                User ID:<br>
                <input type="text" id="userID" name="userID"><br> 
                Password:<br>
                <input type="text" id="password" name="password"><br> 
                Commission:<br>
                <input type="text" id="commission" name="commission"><br> 
                Address:<br>
                <input type="text" id="address" name="address"><br>
                <button style="margin-right: 16px" onclick="addAssociate()">Save Associate</button>
            </div>

            <div id="editSalesAssociate" style="display: none; padding-top: 10px;">
                Name:<br>
                <input type="text" id="assocName" name="assocName"><br>
                User ID:<br>
                <input type="text" id="userID" name="userID"><br> 
                Password:<br>
                <input type="text" id="password" name="password"><br> 
                Commission:<br>
                <input type="text" id="commission" name="commission"><br> 
                Address:<br>
                <input type="text" id="address" name="address"><br> 
                <button style="margin-right: 16px" onclick="updateAssociate()">Save Associate</button>
                <button style="margin-right: 16px" onclick="deleteAssociate()">Delete Associate</button>
            </div>
        </div>

        <div id="viewQuote" style="display: none; padding-top: 10px;">
            <h3>Search Quotes</h3>
            <select id="statusSelect" name="salesAssociateID" style="padding-left: 200px;">
                <option disabled selected value> -- select by status -- </option>
                <option value="unresolved">Unresolved</option>
                <option value="sanctioned">Sanctioned</option>                    
            </select>
            <input type="submit" value="Select" onclick="loadQuotesByStatus()" /><br><br>

            <select id="dateSelect" name="salesAssociateID">
                <option disabled selected value> -- select by date -- </option>
                <?php
                for ($i = 0; $i < 50; $i++) {
                    echo "<option value='" . date("Y-m-d", strtotime("-" . $i . " days")) . "'>" . date("Y-m-d", strtotime("-" . $i . " days")) . "</option>";
                }
                ?>
            </select>
            <input type="submit" value="Select" onclick="loadQuotesByDate()" /><br><br>

            <select id="associateSelect" name="salesAssociateID">
                <option disabled selected value> -- select by associate -- </option>
                <?php
                $associateData = $salesAssociateStore->getAssociates();
            
                foreach ($associateData as $associate) {
                    echo "<option value='" . $associate["id"] . "'>" . $associate["name"] . "</option>";
                }
                ?>
            </select>
            <input type="submit" value="Select" onclick="loadQuotesByAssociate()" /><br><br>

            <select id="customerSelect" name="salesAssociateID">
                <?php if (isset($_POST["customerID"])): ?>
                    <?php $customerName = $loadCustomerData->getCustomer($_POST["customerID"])["name"]; ?>

                    <option value='<?php echo $_POST["customerID"]; ?>'><?php echo $customerName; ?></option>                
                <? else: ?>
                        <option disabled selected value> -- select a customer -- </option>                    
                <? endif; ?>
                <?php
                $customerData = $loadCustomerData->loadFromLegacy();
            
                if ($customerData->num_rows > 0) {
                    while($row = $customerData->fetch_assoc()) {
                        echo "<option value='" . $row["id"] . "'>" . $row["name"] . "</option>";
                    }
                }
                ?>
            </select>
            <input type="submit" value="Select" onclick="loadQuotesByCustomer()" /><br><br>
            <h3>Returned Quotes</h3>
            <div id="returnedQuotes">

            </div>
        </div>
    </body>
       
    <script>
        function toggleCreateSalesAssociate() {
            var createElem = document.getElementById("createSalesAssociate");
            var editElem = document.getElementById("editSalesAssociate");

            if (createElem.style.display === "none") {
                createElem.style.display = "block";
                editElem.style.display = "none";
            } else {
                createElem.style.display = "none";
            }
        }

        function toggleEditSalesAssociate() {
            var editElem = document.getElementById("editSalesAssociate");
            var createElem = document.getElementById("createSalesAssociate"); 

            if (editElem.style.display === "none") {
                editElem.style.display = "block";
                createElem.style.display = "none";
            } else {
                editElem.style.display = "none";
            }
        }
        
        function toggleViewSalesAssociate() {
            var viewAssocElem = document.getElementById("viewSalesAssociate");
            var viewQuoteElem = document.getElementById("viewQuote");

            if (viewAssocElem.style.display === "none") {
                viewAssocElem.style.display = "block";
                viewQuoteElem.style.display = "none";
            } else {
                viewAssocElem.style.display = "none";
            }
        }

        function toggleViewQuotes() {
            var viewQuoteElem = document.getElementById("viewQuote");
            var viewAssocElem = document.getElementById("viewSalesAssociate"); 

            if (viewQuoteElem.style.display === "none") {
                viewQuoteElem.style.display = "block";
                viewAssocElem.style.display = "none";
            } else {
                viewQuoteElem.style.display = "none";
            }
        }

        function addAssociate() {
            var data = {};

            data["action"] = "addAssociate";
            data["name"] = $("#createSalesAssociate > #assocName").val();
            data["username"] = $("#createSalesAssociate > #userID").val();
            data["password"] = $("#createSalesAssociate > #password").val();
            data["commission"] = $("#createSalesAssociate > #commission").val();
            data["address"] = $("#createSalesAssociate > #address").val();

            $.post("", data, function(output) {
                window.location.reload();
            });
        }

        function updateAssociate() {
            var data = { };

            if ($("#editSalesAssociate > #password").val() != "") {
                data["action"] = "updateAssociate";
                data["associateID"] = $("#associateSelect").val();
                data["name"] = $("#editSalesAssociate > #assocName").val();
                data["username"] = $("#editSalesAssociate > #userID").val();
                data["password"] = $("#editSalesAssociate > #password").val();
                data["commission"] = $("#editSalesAssociate > #commission").val();
                data["address"] = $("#editSalesAssociate > #address").val();
                
                $.post("", data);
            }
        }

        function deleteAssociate() {
            var data = {};

            data["action"] = "deleteAssociate";
            data["associateID"] = $("#associateSelect").val();

            $.post("", data, function(output) {
                window.location.reload();
            });
        }

        function populateForm() {
            var data = { };

            data["action"] = "loadAssociate";
            data["associateID"] = $("#associateSelect").val();

            $.post("", data, function(output) {
                var obj = jQuery.parseJSON(output);

                $("#editSalesAssociate > #assocName").val(obj.name);
                $("#editSalesAssociate > #userID").val(obj.username);
                $("#editSalesAssociate > #password").val("");
                $("#editSalesAssociate > #commission").val(obj.commission);
                $("#editSalesAssociate > #address").val(obj.address);
            });
        }

        function loadQuotesByStatus() {
            var data = { };

            data["action"] = "loadQuotesByStatus";
            data["status"] = $("#viewQuote > #statusSelect").val();

            $.post("", data, function(output) {
                var obj = jQuery.parseJSON(output);

                var quotesElem = $("#returnedQuotes");
                    
                quotesElem.html("");

                $.each(obj, function(index, value) {
                    quotesElem.append("<div class='quote'>Associate ID: " + value["associateID"] + "<br>Customer ID: " + value["customerID"] + "<br>Created: " + value["creationDate"] + "<br>Secret Note: " + value["secretNote"] + "<br>Discount: " + value["discount"] + "<br>Final Price: " + value["finalPrice"] + "<br>Status: " + value["status"] + "<br>Finalized: " + value["finalized"] + "<br>Email: " + value["email"] + "</p></div><br>");
                });
            });
        }

        function loadQuotesByDate() {
            var data = { };

            data["action"] = "loadQuotesByDate";
            data["date"] = $("#viewQuote > #dateSelect").val();

            $.post("", data, function(output) {
                var obj = jQuery.parseJSON(output);

                var quotesElem = $("#returnedQuotes");
                    
                quotesElem.html("");

                $.each(obj, function(index, value) {
                    quotesElem.append("<div class='quote'>Associate ID: " + value["associateID"] + "<br>Customer ID: " + value["customerID"] + "<br>Created: " + value["creationDate"] + "<br>Secret Note: " + value["secretNote"] + "<br>Discount: " + value["discount"] + "<br>Final Price: " + value["finalPrice"] + "<br>Status: " + value["status"] + "<br>Finalized: " + value["finalized"] + "<br>Email: " + value["email"] + "</p></div><br>");
                });
            });
        }

        function loadQuotesByAssociate() {
            var data = { };

            data["action"] = "loadQuotesByAssociate";
            data["associateID"] = $("#viewQuote > #associateSelect").val();

            $.post("", data, function(output) {
                var obj = jQuery.parseJSON(output);

                var quotesElem = $("#returnedQuotes");
                    
                quotesElem.html("");

                $.each(obj, function(index, value) {
                    quotesElem.append("<div class='quote'>Associate ID: " + value["associateID"] + "<br>Customer ID: " + value["customerID"] + "<br>Created: " + value["creationDate"] + "<br>Secret Note: " + value["secretNote"] + "<br>Discount: " + value["discount"] + "<br>Final Price: " + value["finalPrice"] + "<br>Status: " + value["status"] + "<br>Finalized: " + value["finalized"] + "<br>Email: " + value["email"] + "</p></div><br>");
                });
            });
        }

        function loadQuotesByCustomer() {
            var data = { };

            data["action"] = "loadQuotesByCustomer";
            data["customerID"] = $("#viewQuote > #customerSelect").val();

            $.post("", data, function(output) {
                var obj = jQuery.parseJSON(output);

                var quotesElem = $("#returnedQuotes");
                    
                quotesElem.html("");

                $.each(obj, function(index, value) {
                    quotesElem.append("<div class='quote'>Associate ID: " + value["associateID"] + "<br>Customer ID: " + value["customerID"] + "<br>Created: " + value["creationDate"] + "<br>Secret Note: " + value["secretNote"] + "<br>Discount: " + value["discount"] + "<br>Final Price: " + value["finalPrice"] + "<br>Status: " + value["status"] + "<br>Finalized: " + value["finalized"] + "<br>Email: " + value["email"] + "</p></div><br>");
                });
            });
        }
    </script>
</html>