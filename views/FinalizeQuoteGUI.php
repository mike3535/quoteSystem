<?php
require_once("../php/LoadCustomerData.php");
require_once("../php/QuoteStore.php");
require_once("../php/Quote.php");

$loadCustomerData = new LoadCustomerData();
$quoteStore = new QuoteStore();

if (isset($_POST["action"])) {
    switch ($_POST["action"]) {
        case "save":
            $quote = new Quote($_POST["customerID"], $_POST["secretNote"], 0, $_POST["finalPrice"], "unresolved", $_POST["lineItems"], $_POST["finalized"], $_POST["email"]);
            $quote->save();
            break;
        case "loadQuote":
            $curQuote = $quoteStore->getQuoteArr($_POST["quoteID"]);
            $curQuote["lineItems"] = unserialize($curQuote["lineItems"]);
            echo json_encode($curQuote);
            break;
        case "updateLineItems":
            $curQuote = $quoteStore->getQuote($_POST["quoteID"]);
            $curQuote->lineItems = $_POST["lineItems"];
            $curQuote->finalPrice = $_POST["finalPrice"];
            $curQuote->update($_POST["quoteID"]);
            break;
        case "deleteQuote":
            $curQuote = $quoteStore->getQuote($_POST["quoteID"]);
            $curQuote->delete($_POST["quoteID"]);
            break;
        case "updateDiscountAndStatus":
            $curQuote = $quoteStore->getQuote($_POST["quoteID"]);
            $curQuote->discount = $_POST["discount"];
            $curQuote->status = $_POST["status"];
            $curQuote->update($_POST["quoteID"]);            
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
        <h1>Finalize Quotes</h1>
        
         <form action="FinalizeQuoteGUI.php" method="post">
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
                    // output data of each row
                    while($row = $customerData->fetch_assoc()) {
                        echo "<option value='" . $row["id"] . "'>" . $row["name"] . "</option>";
                    }
                }
                ?>
            </select>
            <input type="submit" value="Select" />
        </form>

        <?php if (isset($_POST["customerID"])): ?>
            <?php
            $customerName = $loadCustomerData->getCustomer($_POST["customerID"])["name"];
            ?>

            <h3>Finializing Quotes for <?php echo $customerName; ?></h3>
            
            <select id="quoteSelect" onchange="loadLineItems()">
                <option disabled selected value> -- select a quote -- </option>                    
                
                <?php
                $customerQuoteData = $quoteStore->getCustomersQuotes($_POST["customerID"]);

                foreach ($customerQuoteData as $quote) {
                    if ($quote["finalized"]) {
                        echo "<option value='" . $quote["id"] . "'>Quote ID: " . $quote["id"] . "</option>";
                    }
                }
                ?>
            </select>
            <br>
            <button style="margin-right: 16px" onclick="toggleAddLineItems()">Add Line Items</button>
            <button style="margin-right: 16px" onclick="toggleEditLineItems()">Edit Line Items</button>
            <button style="margin-right: 16px" onclick="toggleFinalizeQuote()">Discount/Finalize</button>
            
            <div id="addLineItems" style="display: none; padding-top: 10px;">
                Line Description:<br>
                <input id="lineDes" type="text" name="lineDes"><br>
                Line Price:<br>
                <input id="linePrice" type="number" min="0.01" step="0.01" name="linePrice"><br>
                <button style="margin-right: 16px" onclick="addLineItem()">Add Line Item</button>
            </div>
            
            <div id="editLineItems" style="display: none">
                <h3>Line Items</h3>                
                <div id="lineItems">
                </div>
                <br>
                <br>
                <button style="margin-right: 16px" onclick="updateLineItems()">Update Line Items</button>
            </div>
                
            <div id="finalizeQuote" style="display: none; padding-top: 10px;">
                <br>
                Add Discount:<br>
                <input id="discount" type="number" name="discount"><br><br>
                <input type="checkbox" id="markAsSanctioned" name="markAsSanctioned" value="markAsSanctioned Quote">
                <label for="markAsSanctioned">Mark as Sanctioned</label><br><br>
                <button style="margin-right: 16px" onclick="updateDiscountAndStatus()">Save Quote</button>
            </div>
        <?php endif; ?>
    </body>
    
    <script>
        var lineItems = [];
        var finalPrice = 0;

        function toggleAddLineItems() {
            var addLineElem = document.getElementById("addLineItems");
            var editLineElem = document.getElementById("editLineItems");
            var finalizeQuoteElem = document.getElementById("finalizeQuote");

            if (addLineElem.style.display === "none") {
                addLineElem.style.display = "block";
                editLineElem.style.display = "none";
                finalizeQuoteElem.style.display = "none";
            } else {
                addLineElem.style.display = "none";
            }
        }
            
        function toggleEditLineItems() {
            var editLineElem = document.getElementById("editLineItems");
            var addLineElem = document.getElementById("addLineItems");
            var finalizeQuoteElem = document.getElementById("finalizeQuote");

            if (editLineElem.style.display === "none") {
                editLineElem.style.display = "block";
                addLineElem.style.display = "none";
                finalizeQuoteElem.style.display = "none";
            } else {
                editLineElem.style.display = "none";
            }
        }
            
        function toggleFinalizeQuote() {
            var finalizeQuoteElem = document.getElementById("finalizeQuote");
            var editLineElem = document.getElementById("editLineItems");
            var addLineElem = document.getElementById("addLineItems");

            if (finalizeQuoteElem.style.display === "none") {
                finalizeQuoteElem.style.display = "block";
                editLineElem.style.display = "none";
                addLineElem.style.display = "none";
            } else {
                finalizeQuoteElem.style.display = "none";
            }
        }

        function loadLineItems() {
            var data = {};

            data["action"] = "loadQuote";
            data["quoteID"] = $("#quoteSelect").val();

            $.post("", data, function(output) {
                var obj = jQuery.parseJSON(output);
                lineItems = [];
                finalPrice = parseInt(obj.finalPrice);

                $.each(obj.lineItems, function(index, value) {
                    lineItems.push(value);
                });
                
                var lineItemsElem = $("#editLineItems > #lineItems");
                lineItemsElem.html("");

                $.each(lineItems, function(index, value) {
                    lineItemsElem.append("<div class='lineItem'><input name='des' type='text' value='" + value["des"] + "'><input name='price' type='number' min='0.01' step='0.01' value='" + value["price"] + "'><button style='margin-right: 16px' onclick='deleteLineItem(" + index + ")'>Delete Line Item</button></div>");
                });
            });
        }

        function addLineItem() {
            var data = {};
            var lineDes = $("#addLineItems > #lineDes");
            var linePrice = $("#addLineItems > #linePrice");

            if (lineDes.val() != "" && linePrice.val() != "") {
                lineItems.push({ des: lineDes.val(), price: linePrice.val()});
                finalPrice += parseInt(linePrice.val());

                var lineItemsElem = $("#editLineItems > #lineItems");
                lineItemsElem.append("<div class='lineItem'><input name='des' type='text' value='" + lineDes.val() + "'><input name='price' type='number' min='0.01' step='0.01' value='" + linePrice.val() + "'><button style='margin-right: 16px' onclick='deleteLineItem(" + (lineItems.length - 1) + ")'>Delete Line Item</button></div>");
                
                lineDes.val("");
                linePrice.val(0);

                data["action"] = "updateLineItems";
                data["quoteID"] = $("#quoteSelect").val();
                data["lineItems"] = lineItems;
                data["finalPrice"] = finalPrice;

                $.post("", data);
            }
        }

        function updateLineItems() {
            var data = {};
            finalPrice = 0;
            lineItems = [];

            $("#editLineItems > #lineItems > .lineItem").each(function() {
                var row = {};
                $(this).find("input").each(function() {
                    row[this.name] = this.value;
                });
                
                finalPrice += parseInt(row["price"]);
                lineItems.push(row);
            });

            data["action"] = "updateLineItems";
            data["quoteID"] = $("#quoteSelect").val();
            data["lineItems"] = lineItems;
            data["finalPrice"] = finalPrice;

            $.post("", data);
        }

        function deleteLineItem(index) {
            var data = {};

            finalPrice -= lineItems[index]["price"];
            lineItems.splice(index, 1);

            data["action"] = "updateLineItems";
            data["quoteID"] = $("#quoteSelect").val();
            data["lineItems"] = lineItems;
            data["finalPrice"] = finalPrice;

            var lineItemsElem = $("#editLineItems > #lineItems");
            lineItemsElem.html("");

            $.each(lineItems, function(index, value) {
                lineItemsElem.append("<div class='lineItem'><input name='des' type='text' value='" + value["des"] + "'><input name='price' type='number' min='0.01' step='0.01' value='" + value["price"] + "'><button style='margin-right: 16px' onclick='deleteLineItem(" + index + ")'>Delete Line Item</button></div>");
            });

            $.post("", data);
        }

        function updateDiscountAndStatus() {
            var data = {};
            var isSanctioned = $("#finalizeQuote > #markAsSanctioned").is(":checked");

            data["action"] = "updateDiscountAndStatus";
            data["quoteID"] = $("#quoteSelect").val();
            data["discount"] = $("#finalizeQuote > #discount").val();
            data["status"] = isSanctioned ? "sanctioned" : "unresolved";

            $("#finalizeQuote > #discount").val("");
            $("#finalizeQuote > #markAsSanctioned").prop("checked", false);

            $.post("", data);
        }
    </script>
</html>