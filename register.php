<?php

session_start();

include "config/database.php";
include "functions/email_function.php";


$name = "";
$email = "";
$phone = "";


if(isset($_POST['register'])){

    $name = trim($_POST['name'] ?? "");
    $email = trim($_POST['email'] ?? "");
    $phone = trim($_POST['phone'] ?? "");
    $password = $_POST['password'] ?? "";
    $confirm_password = $_POST['confirm_password'] ?? "";

    // Server-side validation
    if(
        $name === "" ||
        $email === "" ||
        $phone === "" ||
        $password === "" ||
        $confirm_password === ""
    ){

        $error = "Please complete all required fields.";

    }
    elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)){

        $error = "Please enter a valid email address.";

    }
    elseif(!preg_match('/^01[3-9][0-9]{8}$/', $phone)){

        $error = "Phone number must be 11 digits and start with 01.";

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

        // Check whether email already exists
        $check_stmt = mysqli_prepare(
            $conn,
            "SELECT user_id, email_verified FROM users WHERE email = ? LIMIT 1"
        );

        mysqli_stmt_bind_param($check_stmt, "s", $email);
        mysqli_stmt_execute($check_stmt);

        $check_result = mysqli_stmt_get_result($check_stmt);
        $existing_user = mysqli_fetch_assoc($check_result);

        mysqli_stmt_close($check_stmt);


        if($existing_user){

            if((int)$existing_user['email_verified'] === 1){

                $error = "An account with this email already exists.";

            }
            else{

                $error = "This email is already registered but not verified. Please complete email verification.";

                $_SESSION['verify_email'] = $email;

            }

        }
        else{

            $hash_password = password_hash($password, PASSWORD_DEFAULT);
            $verification_code = (string) random_int(100000, 999999);

            $insert_stmt = mysqli_prepare(
                $conn,
                "INSERT INTO users
                (name, email, phone, password, email_verified, verification_code)
                VALUES (?, ?, ?, ?, 0, ?)"
            );

            mysqli_stmt_bind_param(
                $insert_stmt,
                "sssss",
                $name,
                $email,
                $phone,
                $hash_password,
                $verification_code
            );

            if(mysqli_stmt_execute($insert_stmt)){

                $_SESSION['verify_email'] = $email;

                $email_sent = sendVerificationEmail(
                    $email,
                    $verification_code
                );

                mysqli_stmt_close($insert_stmt);

                if($email_sent){

                    header("Location: verify_email.php");
                    exit();

                }
                else{

                    $error = "Your account was created, but the verification email could not be sent. Check the SMTP Gmail and App Password settings.";

                }

            }
            else{

                $error = "Registration failed. Please try again.";

                mysqli_stmt_close($insert_stmt);

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

<title>Register - Smart Expense Tracker</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="css/style.css">

<style>

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

.form-control.password-field{
    padding-right:48px;
}

.login-card{
    border-radius:18px;
}

@media(max-width:576px){

    .card-body{
        padding:28px !important;
    }

    .register-wrapper{
        min-height:100vh;
        padding-top:24px;
        padding-bottom:24px;
    }

}

</style>

</head>

<body class="login-body">

<div class="container">

<div class="row justify-content-center align-items-center min-vh-100 register-wrapper">

<div class="col-md-7 col-lg-5">

<div class="card shadow border-0 login-card">

<div class="card-body p-5">

<h2 class="text-center mb-3">
💰 Smart Expense Tracker
</h2>

<h4 class="text-center mb-4">
Create Account
</h4>


<?php if(isset($error)){ ?>

<div class="alert alert-danger">
<?php echo htmlspecialchars($error); ?>

<?php if(isset($_SESSION['verify_email']) && str_contains($error, "not verified")){ ?>

<div class="mt-2">
<a href="verify_email.php" class="alert-link">
Go to email verification
</a>
</div>

<?php } ?>

</div>

<?php } ?>


<form method="POST" id="registerForm" novalidate>

<div class="mb-3">

<label for="fullName" class="form-label">
Full Name
</label>

<input
type="text"
name="name"
id="fullName"
class="form-control"
placeholder="Enter your name"
value="<?php echo htmlspecialchars($name); ?>"
required>

<div class="invalid-feedback">
Please enter your full name.
</div>

</div>


<div class="mb-3">

<label for="registerEmail" class="form-label">
Email
</label>

<input
type="email"
name="email"
id="registerEmail"
class="form-control"
placeholder="Enter email"
value="<?php echo htmlspecialchars($email); ?>"
autocomplete="email"
required>

<div class="invalid-feedback">
Please enter a valid email address, such as name@example.com.
</div>

</div>


<div class="mb-3">

<label for="phone" class="form-label">
Phone
</label>

<input
type="tel"
name="phone"
id="phone"
class="form-control"
placeholder="01XXXXXXXXX"
value="<?php echo htmlspecialchars($phone); ?>"
maxlength="11"
inputmode="numeric"
pattern="^01[3-9][0-9]{8}$"
required>

<div class="invalid-feedback">
Phone number must be 11 digits and start with 01.
</div>

</div>


<div class="mb-3">

<label for="registerPassword" class="form-label">
Password
</label>

<div class="password-wrapper">

<input
type="password"
name="password"
id="registerPassword"
class="form-control password-field"
placeholder="Create password"
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

<ul class="validation-list" id="passwordRules">

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
Confirm Password
</label>

<input
type="password"
name="confirm_password"
id="confirmPassword"
class="form-control"
placeholder="Confirm password"
autocomplete="new-password"
required>

<div class="invalid-feedback" id="confirmFeedback">
Passwords do not match.
</div>

</div>


<button
type="submit"
name="register"
class="btn btn-primary w-100">
Create Account
</button>

</form>


<div class="text-center mt-3">

Already have an account?

<a href="login.php">
Login
</a>

</div>

</div>

</div>

</div>

</div>

</div>


<script>

const registerForm = document.getElementById("registerForm");

const fullNameInput = document.getElementById("fullName");
const emailInput = document.getElementById("registerEmail");
const phoneInput = document.getElementById("phone");
const passwordInput = document.getElementById("registerPassword");
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


function validateRequiredField(input){

    const valid = input.value.trim().length > 0;

    input.classList.toggle("is-valid", valid);
    input.classList.toggle(
        "is-invalid",
        input.value.length > 0 && !valid
    );

    return valid;

}


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


function validatePhone(){

    const phonePattern = /^01[3-9][0-9]{8}$/;
    const value = phoneInput.value.trim();
    const valid = phonePattern.test(value);

    phoneInput.classList.toggle("is-valid", valid);
    phoneInput.classList.toggle(
        "is-invalid",
        value.length > 0 && !valid
    );

    return valid;

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


fullNameInput.addEventListener("input", function(){
    validateRequiredField(fullNameInput);
});

emailInput.addEventListener("input", validateEmail);

phoneInput.addEventListener("input", function(){

    // Keep only numbers and maximum 11 digits
    phoneInput.value = phoneInput.value
        .replace(/\D/g, "")
        .slice(0, 11);

    validatePhone();

});

passwordInput.addEventListener("input", validatePassword);
confirmInput.addEventListener("input", validateConfirmPassword);


passwordToggle.addEventListener("click", function(){

    const showPassword = passwordInput.type === "password";

    passwordInput.type = showPassword ? "text" : "password";
    passwordToggle.textContent = showPassword ? "🙈" : "👁";

});


registerForm.addEventListener("submit", function(event){

    const nameValid = validateRequiredField(fullNameInput);
    const emailValid = validateEmail();
    const phoneValid = validatePhone();
    const passwordValid = validatePassword();
    const confirmValid = validateConfirmPassword();

    if(
        !nameValid ||
        !emailValid ||
        !phoneValid ||
        !passwordValid ||
        !confirmValid
    ){

        event.preventDefault();

        const firstInvalid =
            registerForm.querySelector(".is-invalid");

        if(firstInvalid){
            firstInvalid.focus();
        }

    }

});

</script>

</body>

</html>