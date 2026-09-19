<?php

session_start();

include "config/database.php";
include "functions/email_function.php";


$email = "";
$error = "";
$success = "";


if(isset($_POST['send_reset_code'])){

    $email = trim($_POST['email'] ?? "");


    if(!filter_var($email, FILTER_VALIDATE_EMAIL)){

        $error = "Please enter a valid email address.";

    }
    else{

        $user_stmt = mysqli_prepare(
            $conn,
            "SELECT user_id, email_verified
             FROM users
             WHERE email = ?
             LIMIT 1"
        );

        mysqli_stmt_bind_param($user_stmt, "s", $email);
        mysqli_stmt_execute($user_stmt);

        $user_result = mysqli_stmt_get_result($user_stmt);
        $user = mysqli_fetch_assoc($user_result);

        mysqli_stmt_close($user_stmt);


        if(!$user){

            $error = "No user account was found with this email address.";

        }
        elseif((int)$user['email_verified'] !== 1){

            $_SESSION['verify_email'] = $email;

            $error = "This email is not verified yet. Verify the email before resetting the password.";

            $show_verify_link = true;

        }
        else{

            $reset_code = (string) random_int(100000, 999999);

            $reset_expiry = date(
                "Y-m-d H:i:s",
                time() + (15 * 60)
            );

            $update_stmt = mysqli_prepare(
                $conn,
                "UPDATE users
                 SET reset_code = ?,
                     reset_expiry = ?
                 WHERE user_id = ?"
            );

            mysqli_stmt_bind_param(
                $update_stmt,
                "ssi",
                $reset_code,
                $reset_expiry,
                $user['user_id']
            );


            if(mysqli_stmt_execute($update_stmt)){

                $_SESSION['reset_email'] = $email;
                unset($_SESSION['reset_verified']);

                $email_sent = sendPasswordResetEmail(
                    $email,
                    $reset_code
                );

                mysqli_stmt_close($update_stmt);


                if($email_sent){

                    header("Location: verify_reset.php");
                    exit();

                }
                else{

                    $error = "The reset code was created, but the email could not be sent. Check your SMTP settings.";

                }

            }
            else{

                mysqli_stmt_close($update_stmt);

                $error = "Unable to create a password reset code. Please try again.";

            }

        }

    }

}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Forgot Password - Smart Expense Tracker</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="css/style.css">

<style>

.forgot-card{
    border-radius:18px;
}

.info-box{
    font-size:14px;
}

@media(max-width:576px){

    .card-body{
        padding:28px !important;
    }

}

</style>

</head>

<body class="login-body">

<div class="container">

<div class="row justify-content-center align-items-center min-vh-100">

<div class="col-md-6 col-lg-5">

<div class="card shadow border-0 forgot-card">

<div class="card-body p-5">

<div class="text-center mb-4">

<div class="fs-1">
🔐
</div>

<h2>
Forgot Password
</h2>

<p class="text-muted mb-0">
Enter your verified email to receive a 6-digit reset code.
</p>

</div>


<?php if($error !== ""){ ?>

<div class="alert alert-danger">

<?php echo htmlspecialchars($error); ?>

<?php if(!empty($show_verify_link)){ ?>

<div class="mt-2">

<a href="verify_email.php" class="alert-link">
Verify Email Now
</a>

</div>

<?php } ?>

</div>

<?php } ?>


<?php if($success !== ""){ ?>

<div class="alert alert-success">
<?php echo htmlspecialchars($success); ?>
</div>

<?php } ?>


<form method="POST" id="forgotForm" novalidate>

<div class="mb-3">

<label for="forgotEmail" class="form-label">
Email
</label>

<input
type="email"
name="email"
id="forgotEmail"
class="form-control"
placeholder="Enter your registered email"
value="<?php echo htmlspecialchars($email); ?>"
autocomplete="email"
required>

<div class="invalid-feedback">
Please enter a valid email address.
</div>

</div>


<button
type="submit"
name="send_reset_code"
class="btn btn-primary w-100">
📧 Send Reset Code
</button>

</form>


<div class="alert alert-light border mt-3 mb-0 info-box">

The reset code will be sent to your email and will remain valid for 15 minutes.

</div>


<div class="text-center mt-3">

<a href="login.php">
Back to Login
</a>

</div>

</div>

</div>

</div>

</div>

</div>


<script>

const forgotForm = document.getElementById("forgotForm");
const emailInput = document.getElementById("forgotEmail");


function validateEmail(){

    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;
    const value = emailInput.value.trim();
    const valid = emailPattern.test(value);

    emailInput.classList.toggle("is-valid", valid);
    emailInput.classList.toggle(
        "is-invalid",
        value.length > 0 && !valid
    );

    return valid;

}


emailInput.addEventListener("input", validateEmail);


forgotForm.addEventListener("submit", function(event){

    if(!validateEmail()){

        event.preventDefault();
        emailInput.focus();

    }

});

</script>

</body>

</html>