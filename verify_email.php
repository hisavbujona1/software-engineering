<?php

session_start();

include "config/database.php";
include "functions/email_function.php";


$email = trim($_SESSION['verify_email'] ?? ($_POST['email'] ?? ""));
$success = "";
$error = "";


// Verify submitted code
if(isset($_POST['verify_email'])){

    $email = trim($_POST['email'] ?? "");
    $verification_code = trim($_POST['verification_code'] ?? "");


    if(!filter_var($email, FILTER_VALIDATE_EMAIL)){

        $error = "Please enter a valid email address.";

    }
    elseif(!preg_match('/^[0-9]{6}$/', $verification_code)){

        $error = "Verification code must contain exactly 6 digits.";

    }
    else{

        $verify_stmt = mysqli_prepare(
            $conn,
            "SELECT user_id, email_verified, verification_code
             FROM users
             WHERE email = ?
             LIMIT 1"
        );

        mysqli_stmt_bind_param($verify_stmt, "s", $email);
        mysqli_stmt_execute($verify_stmt);

        $verify_result = mysqli_stmt_get_result($verify_stmt);
        $user = mysqli_fetch_assoc($verify_result);

        mysqli_stmt_close($verify_stmt);


        if(!$user){

            $error = "No account was found with this email address.";

        }
        elseif((int)$user['email_verified'] === 1){

            unset($_SESSION['verify_email']);

            $success = "Your email is already verified. You can now log in.";

        }
        elseif(!hash_equals((string)$user['verification_code'], $verification_code)){

            $error = "The verification code is incorrect.";

        }
        else{

            $update_stmt = mysqli_prepare(
                $conn,
                "UPDATE users
                 SET email_verified = 1,
                     verification_code = ''
                 WHERE user_id = ?"
            );

            mysqli_stmt_bind_param(
                $update_stmt,
                "i",
                $user['user_id']
            );

            if(mysqli_stmt_execute($update_stmt)){

                unset($_SESSION['verify_email']);

                $success = "Email verified successfully. You can now log in.";

            }
            else{

                $error = "Email verification failed. Please try again.";

            }

            mysqli_stmt_close($update_stmt);

        }

    }

}


// Resend verification code
if(isset($_POST['resend_code'])){

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

            $error = "No account was found with this email address.";

        }
        elseif((int)$user['email_verified'] === 1){

            unset($_SESSION['verify_email']);

            $success = "This email is already verified. You can log in now.";

        }
        else{

            $new_code = (string) random_int(100000, 999999);

            $code_stmt = mysqli_prepare(
                $conn,
                "UPDATE users
                 SET verification_code = ?
                 WHERE user_id = ?"
            );

            mysqli_stmt_bind_param(
                $code_stmt,
                "si",
                $new_code,
                $user['user_id']
            );

            if(mysqli_stmt_execute($code_stmt)){

                $_SESSION['verify_email'] = $email;

                $email_sent = sendVerificationEmail(
                    $email,
                    $new_code
                );

                if($email_sent){

                    $success = "A new verification code has been sent to your email.";

                }
                else{

                    $error = "The code was created, but the email could not be sent. Check the SMTP settings.";

                }

            }
            else{

                $error = "A new verification code could not be generated.";

            }

            mysqli_stmt_close($code_stmt);

        }

    }

}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Verify Email - Smart Expense Tracker</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="css/style.css">

<style>

.verify-card{
    border-radius:18px;
}

.code-input{
    letter-spacing:10px;
    text-align:center;
    font-size:24px;
    font-weight:700;
}

.help-text{
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

<div class="card shadow border-0 verify-card">

<div class="card-body p-5">

<div class="text-center mb-4">

<div class="fs-1">
📧
</div>

<h2>
Verify Your Email
</h2>

<p class="text-muted mb-0">
Enter the 6-digit code sent to your email.
</p>

</div>


<?php if($error !== ""){ ?>

<div class="alert alert-danger">
<?php echo htmlspecialchars($error); ?>
</div>

<?php } ?>


<?php if($success !== ""){ ?>

<div class="alert alert-success">
<?php echo htmlspecialchars($success); ?>
</div>

<?php } ?>


<form method="POST" id="verifyForm" novalidate>

<div class="mb-3">

<label for="verifyEmail" class="form-label">
Email
</label>

<input
type="email"
name="email"
id="verifyEmail"
class="form-control"
placeholder="Enter your registered email"
value="<?php echo htmlspecialchars($email); ?>"
autocomplete="email"
required>

<div class="invalid-feedback">
Please enter a valid email address.
</div>

</div>


<div class="mb-3">

<label for="verificationCode" class="form-label">
Verification Code
</label>

<input
type="text"
name="verification_code"
id="verificationCode"
class="form-control code-input"
placeholder="000000"
maxlength="6"
inputmode="numeric"
autocomplete="one-time-code"
required>

<div class="invalid-feedback">
Enter the 6-digit verification code.
</div>

</div>


<button
type="submit"
name="verify_email"
class="btn btn-primary w-100">
✅ Verify Email
</button>

</form>


<form method="POST" class="mt-3">

<input
type="hidden"
name="email"
value="<?php echo htmlspecialchars($email); ?>">

<button
type="submit"
name="resend_code"
class="btn btn-outline-secondary w-100">
🔄 Resend Verification Code
</button>

</form>


<div class="text-center mt-3 help-text">

<a href="login.php">
Back to Login
</a>

<span class="mx-2">|</span>

<a href="register.php">
Create New Account
</a>

</div>

</div>

</div>

</div>

</div>

</div>


<script>

const verifyForm = document.getElementById("verifyForm");
const emailInput = document.getElementById("verifyEmail");
const codeInput = document.getElementById("verificationCode");


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


function validateCode(){

    codeInput.value = codeInput.value
        .replace(/\D/g, "")
        .slice(0, 6);

    const valid = /^[0-9]{6}$/.test(codeInput.value);

    codeInput.classList.toggle("is-valid", valid);
    codeInput.classList.toggle(
        "is-invalid",
        codeInput.value.length > 0 && !valid
    );

    return valid;

}


emailInput.addEventListener("input", validateEmail);
codeInput.addEventListener("input", validateCode);


verifyForm.addEventListener("submit", function(event){

    const emailValid = validateEmail();
    const codeValid = validateCode();

    if(!emailValid || !codeValid){

        event.preventDefault();

        const firstInvalid =
            verifyForm.querySelector(".is-invalid");

        if(firstInvalid){
            firstInvalid.focus();
        }

    }

});

</script>

</body>

</html>