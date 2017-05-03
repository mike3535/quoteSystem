<?php
require_once("../php/LoadCustomerData.php");
require_once("../php/Quote.php");
require_once("../php/QuoteStore.php");
require_once("../php/SalesAssociate.php");
require_once("../php/SalesAssociateStore.php");


$loadCustomerData = new LoadCustomerData();
$quoteStore = new QuoteStore();
$salesAssociateStore = new SalesAssociateStore();

if (isset($_POST["action"])) {
    switch ($_POST["action"]) {
        case "loadQuote":
            $curQuote = $quoteStore->getQuoteArr($_POST["quoteID"]);
            $curQuote["lineItems"] = unserialize($curQuote["lineItems"]);
            echo json_encode($curQuote);
            break;
        case "updateQuote":
            $curQuote = $quoteStore->getQuote($_POST["quoteID"]);
            $curQuote->secretNote = $curQuote->secretNote; 
            $curQuote->discount = $_POST["discount"];
            $curQuote->finalized = $curQuote->finalized;
            $curQuote->email = $curQuote->email;
            $curQuote->lineItems = $curQuote->lineItems;
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
        function populateForm() {
            var data = { };

            data["action"] = "loadQuote";
            data["quoteID"] = $("#quoteSelect").val();

            $.post("", data, function(output) {
                var obj = jQuery.parseJSON(output);
                
                $("#editQuote > #secretNote").val(obj.secretNote);
                $("#editQuote > #email").val(obj.email);
                $("#editQuote > #finalizeQuote").prop("checked", parseInt(obj.finalized));


                $("#popQuote > #finalPrice").val(obj.finalPrice);
                $("#popQuote > #discount").val(obj.discount);

                $("#processForm > #quoteID").val($("#quoteSelect").val());

                var lineItemsElem = $("#popQuote > #lineItems");
                $.each(obj.lineItems, function(index, value) {
                    lineItemsElem.append("<div class='lineItem'><input name='des' type='text' value='"+ value["des"] + "'readonly><input name='price' type='text' min='0.01' step='0.01' value='$" + value["price"] + "'readonly></div><br>");
                });
            });
        }

        function updateQuote() {
            var data = { };
            finalPrice = 0;
            lineItems = [];
            
            data["action"] = "updateQuote";
            data["quoteID"] = $("#quoteSelect").val();
            
            var currentDiscount = parseInt($("#popQuote > #discount").val());
            var newDiscount = parseInt($("#finalizeDiscount > #discount").val());
            var finalDiscount = currentDiscount + newDiscount;
            data["discount"] = finalDiscount ; 
            
            
            var oldFinalPrice = parseInt($("#popQuote > #finalPrice").val());
            var newFinalPrice = oldFinalPrice - finalDiscount;
            
            data["finalPrice"] = newFinalPrice;

            $.post("PurchaseOrderGUI.php", data, function(output) {
                window.location.reload();
            });
        }



    function addFinalDiscount() {
    
        var currentTot = $("#currentTotal");
        var finalDiscount =  $("#finalDiscount");
        
        var finalPrice = ParseInt(currentTot.val()) + ParseInt(finalDiscount.val());

        
        var data = {};
        
        data["action"] = "updateQuote";
        data["finalPrice"]= finalPrice;

        $.post("PurchaseOrderGUI.php", data, function(output) {
            window.location.reload();
        });
    }
 </script>   
        
    </head>
    <body>
        <h1>Convert to Purchase Order</h1>
        
               <?php  
               if (isset($_POST['button1'])) {
                    $curQuote = $quoteStore->getQuote($_POST['quoteID']);
 
                    $amount = $curQuote->finalPrice;
                    $cusID   =  $curQuote->customerID;
                    $assocID = $curQuote->associateID;
                    $email = $curQuote->email;
                    
                    
                    $uniqueOrderNum = bin2hex(openssl_random_pseudo_bytes(8));
                    
                      
                    $url = 'http://blitz.cs.niu.edu/PurchaseOrder/';
                    $data = array(
	                    'order' => $uniqueOrderNum, 
	                    'associate' => $assocID,
	                    'custid' => $cusID, 
	                    'amount' => $amount );
		
                    $options = array(
                        'http' => array(
                        'header' => array('Content-type: application/json', 'Accept: application/json'),
                        'method'  => 'POST',
                        'content' => json_encode($data)
                     )
                );

            $context = stream_context_create($options);
            $result = file_get_contents($url, false, $context);
           
            
            $json = json_decode($result, true);
           
            echo "Order Processed:  <br>";
            
            $returnOrder =  $json['order'];
            $returnAssoc =  $json['associate'];
            $returnCustID =  $json['custid'];
            $returnAmount =  $json['amount'];
            $returnReqIP =  $json['requestIP'];
            $returnName =  $json['name'];
            $processDay =  $json['processDay'];
            $commission =  $json['commission'];
            
            //calculate commission in dollars
            $commissionDollars = ($commission / 100) * $amount;
           
            echo "Information on your purchase order has been sent to " . $email ."<br><br>";
            
            $msg = "Order Number: " . $returnOrder . "<br> Associate: " . $returnAssoc . "<br> Customer ID: " . $returnCustID . "<br> Amount: $" . $returnAmount . "<br> Request IP: " . $returnReqIP . "<br> Name: " . $returnName . "<br> Process Date: " . $processDay . "<br> Commission Percent: " . $commission . "<br> Commission:  $" .$commissionDollars;
          
            echo $msg;
           
            mail($email,"Order Details",$msg);
            
            //calculate commission in dollars
            $commission = ($commission / 100) * $amount;
            
            $curAssociate = $salesAssociateStore->getAssociate($assocID);
            $curAssociate->commission = $curAssociate->commission + $commission;
            $curAssociate->update($assocID);
        }  
        ?>
                
              
                        
         <form action="PurchaseOrderGUI.php" method="post">
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
            <?php $customerName = $loadCustomerData->getCustomer($_POST["customerID"])["name"]; ?>

            <h3>Quotes for <?php echo $customerName; ?></h3>
        
            <!--
                Populate Quote
            -->
            <div id="popQuote" style="">
                <br>
    
                <select id="quoteSelect" onchange="populateForm()">
                    <option disabled selected value> -- select a quote -- </option>                    

                    <?php
                    $customerQuoteData = $quoteStore->getCustomersQuotes($_POST["customerID"]);
                        
                        echo $customerQuoteData;
                        
                    foreach ($customerQuoteData as $quote) {
                        echo "<option value='" . $quote["id"] . "'>Quote ID: " . $quote["id"] . "</option>";
                    }
                    ?>
                </select>
                
                <br>
                <br>
                Current Total:<br>
                $
                <input type="text" id="finalPrice" name="finalPrice" readonly><br>
                Current Discount:<br>
                $
                <input type="text" id="discount" name="discount" readonly><br><br>
                               
                <div id="lineItems">
                    <h3>Line Items</h3>
                </div>
              
            </div>
       
             <!--
                Finalize Discount
            -->
              
            <div id="finalizeDiscount" style="">
            
            Final Discount:<br>
            
            <input type="number" id="discount" name="discount"><br><br>
            
            <button style="margin-right: 16px" onclick="updateQuote()">Add Final Disount</button><br><br>
            
            <form id="processForm" method="POST" action=''>
                <input id="quoteID" type="number" name="quoteID" value="" hidden>
                <input type="submit" name="button1" value="Process Purchase Order">
            </form>
            
            
            </div>   
        <?php endif; ?>
    </body>      
</html>