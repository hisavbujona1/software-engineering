<?php

session_start();

include "config/database.php";


$email = trim($_SESSION['reset_email'] ?? ($_POST['email'] ?? ""));
$error = "";
$success = "";


if(isset($_POST['verify_reset_code'])){

    $email = trim($_POST['email'] ?? "");
    $reset_code = trim($_POST['reset_code'] ?? "");


    if(!filter_var($email, FILTER_VALIDATE_EMAIL)){

        $error = "Please enter a valid email address.";

    }
    elseif(!preg_match('/^[0-9]{6}$/', $reset_code)){

        $error = "Reset code must contain exactly 6 digits.";

    }
    else{

        $stmt = mysqli_prepare(
            $conn,
            "SELECT user_id, reset_code, reset_expiry
             FROM users
             WHERE email = ?
             LIMIT 1"
        );

        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);

        mysqli_stmt_close($stmt);


        if(!$user){

            $error = "No account was found with this email address.";

        }
        elseif(
            empty($user['reset_code']) ||
            !hash_equals((string)$user['reset_code'], $reset_code)
        ){

            $error = "The reset code is incorrect.";

        }
        elseif(
            empty($user['reset_expiry']) ||
            strtotime($user['reset_expiry']) < time()
        ){

            $error = "The reset code has expired. Please request a new code.";

        }
        else{

            $_SESSION['reset_email'] = $email;
            $_SESSION['reset_verified'] = true;
            $_SESSION['reset_user_id'] = $user['user_id'];

            header("Location: reset_password.php");
            exit();

        }

    }

}


// Request another code
if(isset($_POST['request_new_code'])){

    header("Location: forgot_password.php");
    exit();

}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Verify Reset Code - Smart Expense Tracker</title>

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
🔢
</div>

<h2>
Verify Reset Code
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


<form method="POST" id="verifyResetForm" novalidate>

<div class="mb-3">

<label for="resetEmail" class="form-label">
Email
</label>

<input
type="email"
name="email"
id="resetEmail"
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

<label for="resetCode" class="form-label">
Reset Code
</label>

<input
type="text"
name="reset_code"
id="resetCode"
class="form-control code-input"
placeholder="000000"
maxlength="6"
inputmode="numeric"
autocomplete="one-time-code"
required>

<div class="invalid-feedback">
Enter the 6-digit reset code.
</div>

</div>


<button
type="submit"
name="verify_reset_code"
class="btn btn-primary w-100">
✅ Verify Code
</button>

</form>


<form method="POST" class="mt-3">

<button
type="submit"
name="request_new_code"
class="btn btn-outline-secondary w-100">
🔄 Request New Code
</button>

</form>


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

const verifyResetForm = document.getElementById("verifyResetForm");
const emailInput = document.getElementById("resetEmail");
const codeInput = document.getElementById("resetCode");


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


verifyResetForm.addEventListener("submit", function(event){

    const emailValid = validateEmail();
    const codeValid = validateCode();

    if(!emailValid || !codeValid){

        event.preventDefault();

        const firstInvalid =
            verifyResetForm.querySelector(".is-invalid");

        if(firstInvalid){
            firstInvalid.focus();
        }

    }

});

</script>

</body>

</html>