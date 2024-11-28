<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
include('config/code-generator.php');

check_login();

if (isset($_POST['pay'])) {
    //Prevent Posting Blank Values
    if (empty($_POST["pay_code"]) || empty($_POST["pay_amt"]) || empty($_POST['pay_method']) || ($_POST['pay_method'] == 'Cash' && empty($_POST['amount_paid']))) {
        $err = "Blank Values Not Accepted";
    } else {
        $pay_code = $_POST['pay_code'];
        $order_code = $_GET['order_code'];
        $customer_id = $_GET['customer_id'];
        $pay_amt = $_POST['pay_amt'];
        $pay_method = $_POST['pay_method'];
        $pay_id = $_POST['pay_id'];
        $amount_paid = isset($_POST['amount_paid']) ? $_POST['amount_paid'] : 0;
        $balance = ($pay_method == 'Cash') ? ($amount_paid - $pay_amt) : 0;

        if ($balance < 0) {
            $err = "Insufficient cash amount";
        } else {
            $order_status = $_GET['order_status'];

            // Insert payment info to database
            $postQuery = "INSERT INTO rpos_payments (pay_id, pay_code, order_code, customer_id, pay_amt, pay_method) VALUES(?,?,?,?,?,?)";
            $upQry = "UPDATE rpos_orders SET order_status =? WHERE order_code =?";

            $postStmt = $mysqli->prepare($postQuery);
            $upStmt = $mysqli->prepare($upQry);

            $postStmt->bind_param('ssssss', $pay_id, $pay_code, $order_code, $customer_id, $pay_amt, $pay_method);
            $upStmt->bind_param('ss', $order_status, $order_code);

            $postStmt->execute();
            $upStmt->execute();

            if ($upStmt && $postStmt) {
                $success = "Paid" && header("refresh:1; url=receipts.php");
            } else {
                $err = "Please Try Again Or Try Later";
            }
        }
    }
}
require_once('partials/_head.php');

// Retrieve order total
$order_code = $_GET['order_code'];
$ret = "SELECT * FROM rpos_orders WHERE order_code = '$order_code'";
$stmt = $mysqli->prepare($ret);
$stmt->execute();
$res = $stmt->get_result();

$total = 0; // Initialize $total with a default value

if ($order = $res->fetch_object()) {
    $total = $order->prod_price * $order->prod_qty;
}
?>

<body>
  <!-- Sidenav -->
  <?php require_once('partials/_sidebar.php'); ?>
  
  <div class="main-content">
    <?php require_once('partials/_topnav.php'); ?>
    
    <div style="background-image: url(assets/img/theme/restro00.jpg); background-size: cover;" class="header  pb-8 pt-5 pt-md-8">
      <span class="mask bg-gradient-dark opacity-8"></span>
      <div class="container-fluid">
        <div class="header-body"></div>
      </div>
    </div>

    <div class="container-fluid mt--8">
      <div class="row">
        <div class="col">
          <div class="card shadow">
            <div class="card-header border-0">
              <h3>Please Fill All Fields</h3>
            </div>
            <div class="card-body">
              <form method="POST" enctype="multipart/form-data" id="paymentForm">
                <div class="form-row">
                  <div class="col-md-6">
                    <label>Payment ID</label>
                    <input type="text" name="pay_id" readonly value="<?php echo $payid;?>" class="form-control">
                  </div>
                  <div class="col-md-6">
                    <label>Payment Code</label>
                    <input type="text" name="pay_code" value="<?php echo $mpesaCode; ?>" class="form-control" value="">
                  </div>
                </div>
                
                <hr>
                
                <div class="form-row">
                  <div class="col-md-6">
                    <label>Amount (RM)</label>
                    <input type="text" name="pay_amt" readonly value="<?php echo $total; ?>" class="form-control" id="payAmount">
                  </div>
                  <div class="col-md-6">
                    <label>Payment Method</label>
                    <select class="form-control" name="pay_method" id="payMethod" onchange="toggleCashFields()">
                      <option selected value="Cash">Cash</option>
                      <option value="Paypal">Paypal</option>
                    </select>
                  </div>
                </div>

                <!-- Cash-specific fields -->
                <div class="form-row" id="cashFields">
                  <div class="col-md-6">
                    <label>Amount Paid (RM)</label>
                    <input type="number" name="amount_paid" id="amountPaid" class="form-control" placeholder="Enter amount paid">
                  </div>
                  <div class="col-md-6">
                    <label>Balance (RM)</label>
                    <input type="text" id="balance" class="form-control" readonly>
                  </div>
                </div>

                <br>
                <div class="form-row">
                  <div class="col-md-6">
                    <input type="submit" name="pay" value="Pay Order" class="btn btn-success">
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>

      <?php require_once('partials/_footer.php'); ?>
    </div>
  </div>

  <!-- Argon Scripts -->
  <?php require_once('partials/_scripts.php'); ?>

  <script>
    function toggleCashFields() {
      var payMethod = document.getElementById('payMethod').value;
      var cashFields = document.getElementById('cashFields');
      if (payMethod === 'Cash') {
        cashFields.style.display = 'flex';
      } else {
        cashFields.style.display = 'none';
      }
    }

    document.getElementById('amountPaid').addEventListener('input', function() {
      var payAmount = parseFloat(document.getElementById('payAmount').value) || 0;
      var amountPaid = parseFloat(this.value) || 0;
      var balance = amountPaid - payAmount;
      document.getElementById('balance').value = balance >= 0 ? balance.toFixed(2) : 'Insufficient';
    });

    window.onload = toggleCashFields; // Hide or show cash fields on page load based on selected method
  </script>

</body>
</html>
