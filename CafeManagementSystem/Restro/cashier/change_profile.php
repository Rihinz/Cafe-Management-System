<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();

// Update Profile
if (isset($_POST['ChangeProfile'])) {
    $staff_id = $_SESSION['staff_id'];
    $staff_name = $_POST['staff_name'];
    $staff_email = $_POST['staff_email'];

    $qry = "UPDATE rpos_staff SET staff_name = ?, staff_email = ? WHERE staff_id = ?";
    $postStmt = $mysqli->prepare($qry);

    // Bind parameters
    $postStmt->bind_param('ssi', $staff_name, $staff_email, $staff_id);
    $postStmt->execute();

    if ($postStmt->affected_rows > 0) {
        $success = "Account Updated Successfully";
        header("refresh:1; url=dashboard.php");
    } else {
        $err = "No changes were made or update failed. Please try again.";
    }
}

// Change Password
if (isset($_POST['changePassword'])) {
    $error = 0;

    if (empty($_POST['old_password'])) {
        $error = 1;
        $err = "Old Password Cannot Be Empty";
    } else {
        $old_password = sha1(md5($_POST['old_password']));
    }

    if (empty($_POST['new_password'])) {
        $error = 1;
        $err = "New Password Cannot Be Empty";
    } else {
        $new_password = sha1(md5($_POST['new_password']));
    }

    if (empty($_POST['confirm_password'])) {
        $error = 1;
        $err = "Confirmation Password Cannot Be Empty";
    } else {
        $confirm_password = sha1(md5($_POST['confirm_password']));
    }

    if (!$error) {
        $staff_id = $_SESSION['staff_id'];
        $sql = "SELECT * FROM rpos_staff WHERE staff_id = ?";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('i', $staff_id);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows > 0) {
            $row = $res->fetch_assoc();

            if ($old_password != $row['staff_password']) {
                $err = "Incorrect Old Password. Please Try Again.";
            } elseif ($new_password != $confirm_password) {
                $err = "New Password and Confirmation Password Do Not Match.";
            } else {
                $update_query = "UPDATE rpos_staff SET staff_password = ? WHERE staff_id = ?";
                $updateStmt = $mysqli->prepare($update_query);
                $updateStmt->bind_param('si', $new_password, $staff_id);
                $updateStmt->execute();

                if ($updateStmt->affected_rows > 0) {
                    $success = "Password Changed Successfully";
                    header("refresh:1; url=dashboard.php");
                } else {
                    $err = "Password Change Failed. Please Try Again.";
                }
            }
        } else {
            $err = "User Not Found. Please Try Again.";
        }
    }
}
require_once('partials/_head.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Head content here -->
</head>
<body>
    <!-- Sidenav -->
    <?php require_once('partials/_sidebar.php'); ?>

    <!-- Main content -->
    <div class="main-content">
        <!-- Top navbar -->
        <?php require_once('partials/_topnav.php'); ?>

        <!-- Fetch user details -->
        <?php
        $staff_id = $_SESSION['staff_id'];
        $ret = "SELECT * FROM rpos_staff WHERE staff_id = ?";
        $stmt = $mysqli->prepare($ret);
        $stmt->bind_param('i', $staff_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $staff = $res->fetch_object();
        ?>

        <div class="header pb-8 pt-5 pt-lg-8 d-flex align-items-center" style="min-height: 600px; background-image: url(../admin/assets/img/theme/restro00.jpg); background-size: cover; background-position: center top;">
            <span class="mask bg-gradient-default opacity-8"></span>
            <div class="container-fluid d-flex align-items-center">
                <div class="row">
                    <div class="col-lg-7 col-md-10">
                        <h1 class="display-2 text-white">Hello <?php echo $staff->staff_name; ?></h1>
                        <p class="text-white mt-0 mb-5">You can update your profile or change your password here.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Page content -->
        <div class="container-fluid mt--8">
            <div class="row">
                <div class="col-xl-4 order-xl-2 mb-5 mb-xl-0">
                    <div class="card card-profile shadow">
                            <div class="row justify-content-center">
                                <div class="col-lg-3 order-lg-2">
                                    <div class="card-profile-image">
                                        <a href="#">
                                            <img src="../admin/assets/img/theme/user-a-min.png" class="rounded-circle">
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="card-header text-center border-0 pt-8 pt-md-4 pb-0 pb-md-4">
                                <div class="d-flex justify-content-between">
                                </div>
                            </div>
                            <div class="card-body pt-0 pt-md-4">
                                <div class="row">
                                    <div class="col">
                                        <div class="card-profile-stats d-flex justify-content-center mt-md-5">
                                            <div>
                                            </div>
                                            <div>
                                            </div>
                                            <div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-center">
                                    <h3>
                                        <?php echo $staff->staff_name; ?></span>
                                    </h3>
                                    <div class="h5 font-weight-300">
                                        <i class="ni location_pin mr-2"></i><?php echo $staff->staff_email; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                </div>

                <div class="col-xl-8 order-xl-1">
                    <div class="card bg-secondary shadow">
                        <div class="card-header bg-white border-0">
                            <h3 class="mb-0">My Account</h3>
                        </div>
                        <div class="card-body">
                            <!-- Update Profile Form -->
                            <form method="post">
                                <h6 class="heading-small text-muted mb-4">User Information</h6>
                                <div class="pl-lg-4">
                                    <div class="form-group">
                                        <label class="form-control-label">User Name</label>
                                        <input type="text" name="staff_name" value="<?php echo $staff->staff_name; ?>" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-control-label">Email Address</label>
                                        <input type="email" name="staff_email" value="<?php echo $staff->staff_email; ?>" class="form-control">
                                    </div>
                                    <button type="submit" name="ChangeProfile" class="btn btn-success">Update Profile</button>
                                </div>
                            </form>
                            <hr>

                            <!-- Change Password Form -->
                            <form method="post">
                                <h6 class="heading-small text-muted mb-4">Change Password</h6>
                                <div class="pl-lg-4">
                                    <div class="form-group">
                                        <label class="form-control-label">Old Password</label>
                                        <input type="password" name="old_password" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-control-label">New Password</label>
                                        <input type="password" name="new_password" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-control-label">Confirm New Password</label>
                                        <input type="password" name="confirm_password" class="form-control">
                                    </div>
                                    <button type="submit" name="changePassword" class="btn btn-success">Change Password</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <?php require_once('partials/_footer.php'); ?>
        </div>
    </div>
</body>
</html>
