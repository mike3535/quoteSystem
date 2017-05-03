<?php
require_once("../functions/session.php");
require_once("../php/LoadCustomerData.php");
require_once("../php/Quote.php");
require_once("../php/QuoteStore.php");
require_once("../php/SalesAssociateStore.php");
require_once("../functions/functions.php");

confirm_logged_in();

$loadCustomerData = new LoadCustomerData();
$quoteStore = new QuoteStore();
$salesAssociateStore = new SalesAssociateStore();

if (isset($_POST["action"])) {
    switch ($_POST["action"]) {
        case "save":
            $quote = new Quote($_POST["customerID"], $salesAssociateStore->getAssociateIDByUsername($_SESSION["userid"]), date("Y-m-d"), $_POST["secretNote"], 0, $_POST["finalPrice"], "unresolved", $_POST["lineItems"], $_POST["finalized"], $_POST["email"]);
            $quote->save();
            break;
        case "loadQuote":
            $curQuote = $quoteStore->getQuoteArr($_POST["quoteID"]);
            $curQuote["lineItems"] = unserialize($curQuote["lineItems"]);
            echo json_encode($curQuote);
            break;
        case "updateQuote":
            $curQuote = $quoteStore->getQuote($_POST["quoteID"]);
            $curQuote->secretNote = $_POST["secretNote"];
            $curQuote->finalized = $_POST["finalized"];
            $curQuote->email = $_POST["email"];
            $curQuote->lineItems = $_POST["lineItems"];
            $curQuote->finalPrice = $_POST["finalPrice"];
            $curQuote->update($_POST["quoteID"]);
            break;
        case "deleteQuote":
            $curQuote = $quoteStore->getQuote($_POST["quoteID"]);
            $curQuote->delete($_POST["quoteID"]);
            break;
    }
    exit;
}
?>

<html>
    <head>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>

        <script>
            var lineItems = [];
            var finalPrice = 0;

            function toggleCreateQuote() {
                var createElem = document.getElementById("createNewQuote");
                var editElem = document.getElementById("editQuote");

                if (createElem.style.display === "none") {
                    createElem.style.display = "block";
                    editElem.style.display = "none";
                } else {
                    createElem.style.display = "none";
                }
            }

            function toggleEditQuote() {
                var editElem = document.getElementById("editQuote");
                var createElem = document.getElementById("createNewQuote"); 

                if (editElem.style.display === "none") {
                    editElem.style.display = "block";
                    createElem.style.display = "none";
                } else {
                    editElem.style.display = "none";
                }
            }

            function saveQuote() {
                var data = { };

                if ($("#createNewQuote > #email").val() != "") {
                    data["action"] = "save";                  
                    data["customerID"] = $("#customerID").val();
                    data["secretNote"] = $("#createNewQuote > #secretNote").val();
                    data["email"] = $("#createNewQuote > #email").val();
                    data["lineItems"] = lineItems;
                    data["finalPrice"] = finalPrice;
                    data["finalized"] = $("#createNewQuote > #finalizeQuote").is(":checked") | 0;

                    lineItems = [];
                    finalPrice = 0;

                    $.post("CreateQuotesGUI.php", data, function(output) {
                        window.location.reload();
                    });
                }
            }

            function addLineItem() {
                var lineDes = $("#lineDes");
                var linePrice = $("#linePrice");

                if (lineDes.val() != "" && linePrice.val() != "") {
                    lineItems.push({ des: lineDes.val(), price: linePrice.val()});
                    finalPrice += parseInt(linePrice.val());

                    $("#listItemsList").append("<li><p>" + lineDes.val() + " - $" + linePrice.val() + "</p></li>");                

                    lineDes.val("");
                    linePrice.val(0);
                }
            }

            function addLineItemEdit() {
                var lineDes = $("#editQuote > #lineDes");
                var linePrice = $("#editQuote > #linePrice");
                var lineItemsElem = $("#editQuote > #lineItems");
                
                if (lineDes.val() != "" && linePrice.val() != "") {
                    finalPrice += parseInt(linePrice.val());

                    lineItemsElem.append("<div class='lineItem'><input name='des' type='text' value='"+ lineDes.val() + "'><input name='price' type='number' min='0.01' step='0.01' value='" + linePrice.val() + "'></div><br>");

                    lineDes.val("");
                    linePrice.val(0);
                }
            }

            function populateForm() {
                var data = { };

                data["action"] = "loadQuote";
                data["quoteID"] = $("#quoteSelect").val();

                $.post("CreateQuotesGUI.php", data, function(output) {
                    var obj = jQuery.parseJSON(output);

                    $("#editQuote > #secretNote").val(obj.secretNote);
                    $("#editQuote > #email").val(obj.email);
                    $("#editQuote > #finalizeQuote").prop("checked", parseInt(obj.finalized));

                    var lineItemsElem = $("#editQuote > #lineItems");
                    $.each(obj.lineItems, function(index, value) {
                        lineItemsElem.append("<div class='lineItem'><input name='des' type='text' value='"+ value["des"] + "'><input name='price' type='number' min='0.01' step='0.01' value='" + value["price"] + "'></div><br>");
                    });
                });
            }

            function updateQuote() {
                var data = { };
                finalPrice = 0;
                lineItems = [];

                data["action"] = "updateQuote";
                data["quoteID"] = $("#quoteSelect").val();
                data["secretNote"] = $("#editQuote > #secretNote").val();
                data["email"] = $("#editQuote > #email").val();
                data["finalized"] = $("#editQuote > #finalizeQuote").is(":checked") | 0;
                
                $("#editQuote > #lineItems > .lineItem").each(function() {
                    var row = {};
                    $(this).find("input").each(function() {
                        row[this.name] = this.value;
                    });
                    
                    finalPrice += parseInt(row["price"]);
                    lineItems.push(row);
                });

                data["lineItems"] = lineItems;
                data["finalPrice"] = finalPrice;

                $.post("CreateQuotesGUI.php", data, function(output) {
                    window.location.reload();
                });
            }

            function deleteQuote() {
                var data = {};

                data["action"] = "deleteQuote";
                data["quoteID"] = $("#quoteSelect").val();

                $.post("CreateQuotesGUI.php", data, function(output) {
                    window.location.reload();
                });
            }
        </script>
    </head>
    <body>
        <h1>Create Quote</h1>

        <p id="successMessage"></p>
        
        <!--
            Select Customer
        -->
        <form action="CreateQuotesGUI.php" method="post">
            <select id="customerID" name="customerID">
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
            <input type="submit" value="Select" />
        </form>

        <!--
            Create New Quote
        -->
        <?php if (isset($_POST["customerID"])): ?>
            <?php $customerName = $loadCustomerData->getCustomer($_POST["customerID"])["name"]; ?>

            <h3>Creating/Editing Quotes for <?php echo $customerName; ?></h3>

            <button style="margin-right: 16px" onclick="toggleCreateQuote()">Create New Quote</button>
            <button style="margin-right: 16px" onclick="toggleEditQuote()">Edit Existing Quote</button>
            
            <div id="createNewQuote" style="display: none; padding-top: 10px;">
                Line Description:<br>
                <input type="text" id="lineDes" name="lineDes"><br>
                Line Price:<br>
                <input type="number" min="0.01" step="0.01" value="0" id="linePrice" name="linePrice"><br>
                <button onclick="addLineItem()">Add Line Item</button>
                <br>
                <br>
                Secret Note:<br>
                <textarea id="secretNote" rows="4" cols="50" name="Secret Note"></textarea><br>
                Email:<br>
                <input id="email" type="text" name="Email" require><br>
                <input type="checkbox" id="finalizeQuote" name="finalizeQuote" value="finalizeQuote">
                <label for="finalizeQuote">Finialize Quote</label><br>
                <button style="margin-right: 16px" onclick="saveQuote()">Save Quote</button>

                <ol id="listItemsList"></ol>
            </div>

            <!--
                Edit Quote
            -->
            <div id="editQuote" style="display: none">
                <br>
                <select id="quoteSelect" onchange="populateForm()">
                    <option disabled selected value> -- select a quote -- </option>                    
                    
                    <?php
                    $customerQuoteData = $quoteStore->getCustomersQuotes($_POST["customerID"]);

                    foreach ($customerQuoteData as $quote) {
                        echo "<option value='" . $quote["id"] . "'>Quote ID: " . $quote["id"] . "</option>";
                    }
                    ?>
                </select>
                <br>
                Line Description:<br>
                <input type="text" id="lineDes" name="lineDes"><br>
                Line Price:<br>
                <input type="number" min="0.01" step="0.01" value="0" id="linePrice" name="linePrice"><br>
                <button onclick="addLineItemEdit()">Add Line Item</button>
                <br>
                <br>
                Secret Note:<br>
                <textarea id="secretNote" rows="4" cols="50" name="Secret Note"></textarea><br><br>
                Email:<br>
                <input id="email" type="text" name="Email" require><br><br>
                <input type="checkbox" id="finalizeQuote" name="finalizeQuote" value="finalizeQuote">
                <label for="finalizeQuote">Finalize Quote</label><br>
                <br>
                <div id="lineItems">
                    <h3>Line Items</h3>
                </div>
                <br>
                <button style="margin-right: 16px" onclick="updateQuote()">Update Quote</button>
                <button style="margin-right: 16px" onclick="deleteQuote()">Delete Quote</button>
            </div>
        <?php endif; ?>
    </body>
</html>