<?php
session_start();
require 'assets/vendor/autoload.php'; // Composer autoloader
include('config/config.php');
require_once('config/code-generator.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (isset($_POST['reset_pwd'])) {
    $reset_email = $_POST['reset_email'];

    // Validate email format
    if (!filter_var($reset_email, FILTER_VALIDATE_EMAIL)) {
        $err = 'Invalid Email';
    } else {
        // Check if email exists in the database
        $checkEmail = mysqli_query($mysqli, "SELECT `customer_email` FROM `rpos_customers` WHERE `customer_email` = '$reset_email'");
        if (mysqli_num_rows($checkEmail) > 0) {
            // Generate a new password
            $new_password = bin2hex(random_bytes(4)); // Random 8-character password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT); // Hash password

            // Update the password in the database
            $updateQuery = "UPDATE `rpos_customers` SET `customer_password` = ? WHERE `customer_email` = ?";
            $stmt = $mysqli->prepare($updateQuery);
            $stmt->bind_param('ss', $hashed_password, $reset_email);
            if ($stmt->execute()) {
                // Send email with PHPMailer
                $mail = new PHPMailer(true);
                try {
                    // Server settings
                    $mail->isSMTP();
                    $mail->Host = 'smtp.example.com'; // Replace with your SMTP server
                    $mail->SMTPAuth = true;
                    $mail->Username = 'your-email@example.com'; // Replace with your email
                    $mail->Password = 'your-email-password';   // Replace with your email password
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = 587;

                    // Email settings
                    $mail->setFrom('your-email@example.com', 'Cafe Management System'); // Replace with your email
                    $mail->addAddress($reset_email); // Recipient's email
                    $mail->isHTML(true);
                    $mail->Subject = 'Password Reset Request';
                    $mail->Body = "
                        <h4>Password Reset Successful</h4>
                        <p>Your password has been reset. Use the following credentials to log in:</p>
                        <p><strong>Email:</strong> $reset_email</p>
                        <p><strong>New Password:</strong> $new_password</p>
                        <p>Please change your password after logging in for security purposes.</p>
                    ";

                    $mail->send();
                    $success = "Password reset instructions have been sent to your email.";
                } catch (Exception $e) {
                    $err = "Failed to send email. Mailer Error: {$mail->ErrorInfo}";
                }
            } else {
                $err = "Failed to update password. Please try again later.";
            }
        } else {
            $err = "No account found with that email.";
        }
    }
}

require_once('partials/_head.php');
?>

<body class="bg-dark">
  <div>
    <div class="main-content">
      <div class="header bg-gradient-primary py-7">
        <div class="container">
          <div class="header-body text-center mb-7">
            <div class="row justify-content-center">
              <div class="col-lg-5 col-md-6">
                <h1 class="text-white">Cafe Management System</h1>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- Page content -->
      <div class="container mt--8 pb-5">
        <div class="row justify-content-center">
          <div class="col-lg-5 col-md-7">
            <div class="card">
              <div class="card-body px-lg-5 py-lg-5">
                <?php if (isset($success)) { ?>
                  <div class="alert alert-success"><?php echo $success; ?></div>
                <?php } ?>
                <?php if (isset($err)) { ?>
                  <div class="alert alert-danger"><?php echo $err; ?></div>
                <?php } ?>
                <form method="post" role="form">
                  <div class="form-group mb-3">
                    <div class="input-group input-group-alternative">
                      <div class="input-group-prepend">
                        <span class="input-group-text"><i class="ni ni-email-83"></i></span>
                      </div>
                      <input class="form-control" required name="reset_email" placeholder="Email" type="email">
                    </div>
                  </div>
                  <div class="text-center">
                    <button type="submit" name="reset_pwd" class="btn btn-primary my-4">Reset Password</button>
                  </div>
                </form>
              </div>
            </div>
            <div class="row mt-3">
              <div class="col-6">
                <a href="index.php" class="text-light"><small>Log In?</small></a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- Footer -->
    <?php require_once('partials/_footer.php'); ?>
    <!-- Argon Scripts -->
    <?php require_once('partials/_scripts.php'); ?>
  </div>
</body>

</html>
