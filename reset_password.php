<?php

session_start();

include "config/database.php";


$error = "";
$success = "";


// Only allow access after reset code verification
if(
    empty($_SESSION['reset_verified']) ||
    empty($_SESSION['reset_email']) ||
    empty($_SESSION['reset_user_id'])
){

    header("Location: forgot_password.php");
    exit();

}


$reset_email = $_SESSION['reset_email'];
$reset_user_id = (int) $_SESSION['reset_user_id'];


if(isset($_POST['reset_password'])){

    $password = $_POST['password'] ?? "";
    $confirm_password = $_POST['confirm_password'] ?? "";


    if($password === "" || $confirm_password === ""){

        $error = "Please complete both password fields.";

    }
    elseif(
        strlen($password) < 8 ||
        !preg_match('/[A-Z]/', $password) ||
        !preg_match('/[a-z]/', $password) ||
        !preg_match('/[0-9]/', $password) ||
        !preg_match('/[^A-Za-z0-9]/', $password)
    ){

        $error = "Password must contain at least 8 characters, including uppercase, lowercase, number and special character.";

    }
    elseif($password !== $confirm_password){

        $error = "Password and Confirm Password do not match.";

    }
    else{

        $hash_password = password_hash(
            $password,
            PASSWORD_DEFAULT
        );


        $update_stmt = mysqli_prepare(
            $conn,
            "UPDATE users
             SET password = ?,
                 reset_code = NULL,
                 reset_expiry = NULL
             WHERE user_id = ?
             AND email = ?"
        );

        mysqli_stmt_bind_param(
            $update_stmt,
            "sis",
            $hash_password,
            $reset_user_id,
            $reset_email
        );


        if(mysqli_stmt_execute($update_stmt)){

            mysqli_stmt_close($update_stmt);

            unset(
                $_SESSION['reset_email'],
                $_SESSION['reset_verified'],
                $_SESSION['reset_user_id']
            );

            $success = "Password reset successful. You can now log in with your new password.";

        }
        else{

            mysqli_stmt_close($update_stmt);

            $error = "Unable to reset the password. Please try again.";

        }

    }

}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Reset Password - Smart Expense Tracker</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="css/style.css">

<style>

.reset-card{
    border-radius:18px;
}

.validation-list{
    padding-left:0;
    margin-top:10px;
    margin-bottom:0;
    list-style:none;
    font-size:14px;
}

.validation-list li{
    margin-bottom:4px;
}

.rule-invalid{
    color:#dc3545;
}

.rule-valid{
    color:#198754;
}

.password-wrapper{
    position:relative;
}

.password-field{
    padding-right:48px;
}

.password-toggle{
    position:absolute;
    top:50%;
    right:12px;
    transform:translateY(-50%);
    border:0;
    background:transparent;
    cursor:pointer;
    z-index:5;
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

<div class="card shadow border-0 reset-card">

<div class="card-body p-5">

<div class="text-center mb-4">

<div class="fs-1">
🔑
</div>

<h2>
Create New Password
</h2>

<p class="text-muted mb-0">
Set a strong new password for your account.
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

<div class="mt-2">

<a href="login.php" class="alert-link">
Go to Login
</a>

</div>

</div>

<?php } ?>


<?php if($success === ""){ ?>

<form method="POST" id="resetPasswordForm" novalidate>

<div class="mb-3">

<label for="newPassword" class="form-label">
New Password
</label>

<div class="password-wrapper">

<input
type="password"
name="password"
id="newPassword"
class="form-control password-field"
placeholder="Enter new password"
autocomplete="new-password"
required>

<button
type="button"
class="password-toggle"
id="passwordToggle"
aria-label="Show or hide password">
👁
</button>

</div>


<ul class="validation-list">

<li id="ruleLength" class="rule-invalid">
❌ Minimum 8 characters
</li>

<li id="ruleUpper" class="rule-invalid">
❌ One uppercase letter
</li>

<li id="ruleLower" class="rule-invalid">
❌ One lowercase letter
</li>

<li id="ruleNumber" class="rule-invalid">
❌ One number
</li>

<li id="ruleSpecial" class="rule-invalid">
❌ One special character
</li>

</ul>

</div>


<div class="mb-3">

<label for="confirmPassword" class="form-label">
Confirm New Password
</label>

<input
type="password"
name="confirm_password"
id="confirmPassword"
class="form-control"
placeholder="Confirm new password"
autocomplete="new-password"
required>

<div class="invalid-feedback">
Passwords do not match.
</div>

</div>


<button
type="submit"
name="reset_password"
class="btn btn-primary w-100">
✅ Reset Password
</button>

</form>

<?php } ?>


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

const resetForm = document.getElementById("resetPasswordForm");

if(resetForm){

    const passwordInput = document.getElementById("newPassword");
    const confirmInput = document.getElementById("confirmPassword");
    const passwordToggle = document.getElementById("passwordToggle");

    const ruleLength = document.getElementById("ruleLength");
    const ruleUpper = document.getElementById("ruleUpper");
    const ruleLower = document.getElementById("ruleLower");
    const ruleNumber = document.getElementById("ruleNumber");
    const ruleSpecial = document.getElementById("ruleSpecial");


    function updateRule(element, valid, text){

        element.textContent = (valid ? "✅ " : "❌ ") + text;

        element.classList.toggle("rule-valid", valid);
        element.classList.toggle("rule-invalid", !valid);

    }


    function validatePassword(){

        const password = passwordInput.value;

        const lengthValid = password.length >= 8;
        const upperValid = /[A-Z]/.test(password);
        const lowerValid = /[a-z]/.test(password);
        const numberValid = /[0-9]/.test(password);
        const specialValid = /[^A-Za-z0-9]/.test(password);

        updateRule(ruleLength, lengthValid, "Minimum 8 characters");
        updateRule(ruleUpper, upperValid, "One uppercase letter");
        updateRule(ruleLower, lowerValid, "One lowercase letter");
        updateRule(ruleNumber, numberValid, "One number");
        updateRule(ruleSpecial, specialValid, "One special character");

        const valid =
            lengthValid &&
            upperValid &&
            lowerValid &&
            numberValid &&
            specialValid;

        passwordInput.classList.toggle("is-valid", valid);
        passwordInput.classList.toggle(
            "is-invalid",
            password.length > 0 && !valid
        );

        validateConfirmPassword();

        return valid;

    }


    function validateConfirmPassword(){

        const value = confirmInput.value;

        const valid =
            value.length > 0 &&
            value === passwordInput.value;

        confirmInput.classList.toggle("is-valid", valid);
        confirmInput.classList.toggle(
            "is-invalid",
            value.length > 0 && !valid
        );

        return valid;

    }


    passwordInput.addEventListener("input", validatePassword);
    confirmInput.addEventListener("input", validateConfirmPassword);


    passwordToggle.addEventListener("click", function(){

        const showPassword = passwordInput.type === "password";

        passwordInput.type = showPassword ? "text" : "password";
        passwordToggle.textContent = showPassword ? "🙈" : "👁";

    });


    resetForm.addEventListener("submit", function(event){

        const passwordValid = validatePassword();
        const confirmValid = validateConfirmPassword();

        if(!passwordValid || !confirmValid){

            event.preventDefault();

            const firstInvalid =
                resetForm.querySelector(".is-invalid");

            if(firstInvalid){
                firstInvalid.focus();
            }

        }

    });

}

</script>

</body>

</html>