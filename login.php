<?php

session_start();

include "config/database.php";


$email = "";
$login_type = "user";


if(isset($_POST['login'])){

    $email = trim($_POST['email'] ?? "");
    $password = $_POST['password'] ?? "";
    $login_type = $_POST['login_type'] ?? "user";


    // Basic server-side validation
    if(!filter_var($email, FILTER_VALIDATE_EMAIL)){

        $error = "Please enter a valid email address.";

    }
    elseif($password === ""){

        $error = "Please enter your password.";

    }
    elseif(!in_array($login_type, ["user", "admin"], true)){

        $error = "Please select a valid login type.";

    }
    else{

        // =====================
        // ADMIN LOGIN
        // =====================

        if($login_type === "admin"){

            $admin_stmt = mysqli_prepare(
                $conn,
                "SELECT admin_id, name, email, password
                 FROM admin
                 WHERE email = ?
                 LIMIT 1"
            );

            mysqli_stmt_bind_param($admin_stmt, "s", $email);
            mysqli_stmt_execute($admin_stmt);

            $admin_result = mysqli_stmt_get_result($admin_stmt);
            $admin = mysqli_fetch_assoc($admin_result);

            mysqli_stmt_close($admin_stmt);


            // Your current admin table uses a plain-text password.
            if($admin && hash_equals((string)$admin['password'], $password)){

                // Remove any existing user session
                unset(
                    $_SESSION['user_id'],
                    $_SESSION['name'],
                    $_SESSION['email']
                );

                $_SESSION['admin_id'] = $admin['admin_id'];
                $_SESSION['admin_name'] = $admin['name'];

                header("Location: admin/dashboard.php");
                exit();

            }
            else{

                $error = "Invalid Admin Email or Password.";

            }

        }

        // =====================
        // USER LOGIN
        // =====================

        else{

            $user_stmt = mysqli_prepare(
                $conn,
                "SELECT user_id, name, email, password, email_verified
                 FROM users
                 WHERE email = ?
                 LIMIT 1"
            );

            mysqli_stmt_bind_param($user_stmt, "s", $email);
            mysqli_stmt_execute($user_stmt);

            $user_result = mysqli_stmt_get_result($user_stmt);
            $user = mysqli_fetch_assoc($user_result);

            mysqli_stmt_close($user_stmt);


            if(!$user || !password_verify($password, $user['password'])){

                $error = "Invalid User Email or Password.";

            }
            elseif((int)$user['email_verified'] !== 1){

                $_SESSION['verify_email'] = $user['email'];

                $error = "Your email is not verified yet. Please verify your email before logging in.";

                $show_verify_link = true;

            }
            else{

                // Remove any existing admin session
                unset(
                    $_SESSION['admin_id'],
                    $_SESSION['admin_name']
                );

                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['email'] = $user['email'];

                header("Location: dashboard.php");
                exit();

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

<title>Login - Smart Expense Tracker</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="css/style.css">

<style>

.login-card{
    border-radius:18px;
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

.login-links{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:15px;
    margin-top:12px;
    font-size:14px;
}

@media(max-width:576px){

    .card-body{
        padding:28px !important;
    }

    .login-links{
        flex-direction:column;
        gap:8px;
    }

}

</style>

</head>

<body class="login-body">

<div class="container">

<div class="row justify-content-center align-items-center min-vh-100">

<div class="col-md-6 col-lg-5">

<div class="card shadow border-0 login-card">

<div class="card-body p-5">

<h2 class="text-center mb-3">
💰 Smart Expense Tracker
</h2>

<h4 class="text-center mb-4">
Login
</h4>


<?php if(isset($error)){ ?>

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


<form method="POST" id="loginForm" novalidate>

<div class="mb-3">

<label for="loginEmail" class="form-label">
Email
</label>

<input
type="email"
name="email"
id="loginEmail"
class="form-control"
placeholder="Enter Email"
value="<?php echo htmlspecialchars($email); ?>"
autocomplete="email"
required>

<div class="invalid-feedback">
Please enter a valid email address, such as name@example.com.
</div>

</div>


<div class="mb-2">

<label for="loginPassword" class="form-label">
Password
</label>

<div class="password-wrapper">

<input
type="password"
name="password"
id="loginPassword"
class="form-control password-field"
placeholder="Enter Password"
autocomplete="current-password"
required>

<button
type="button"
class="password-toggle"
id="passwordToggle"
aria-label="Show or hide password">
👁
</button>

</div>

<div class="invalid-feedback" id="passwordFeedback">
Please enter your password.
</div>

</div>


<div class="text-end mb-3">

<a href="forgot_password.php">
Forgot Password?
</a>

</div>


<div class="mb-3">

<label for="loginType" class="form-label">
Login As
</label>

<select
name="login_type"
id="loginType"
class="form-select"
required>

<option
value="user"
<?php echo ($login_type === "user") ? "selected" : ""; ?>>
👤 User
</option>

<option
value="admin"
<?php echo ($login_type === "admin") ? "selected" : ""; ?>>
⚙ Admin
</option>

</select>

</div>


<button
type="submit"
name="login"
class="btn btn-primary w-100">
Login
</button>

</form>


<div class="login-links">

<span>
Don't have an account?
<a href="register.php">Register</a>
</span>

<span>
Need email verification?
<a href="verify_email.php">Verify Email</a>
</span>

</div>

</div>

</div>

</div>

</div>

</div>


<script>

const loginForm = document.getElementById("loginForm");
const loginEmail = document.getElementById("loginEmail");
const loginPassword = document.getElementById("loginPassword");
const passwordToggle = document.getElementById("passwordToggle");


function validateLoginEmail(){

    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;
    const value = loginEmail.value.trim();
    const valid = emailPattern.test(value);

    loginEmail.classList.toggle("is-valid", valid);
    loginEmail.classList.toggle(
        "is-invalid",
        value.length > 0 && !valid
    );

    return valid;

}


function validateLoginPassword(){

    const value = loginPassword.value;
    const valid = value.trim().length > 0;

    loginPassword.classList.toggle("is-valid", valid);
    loginPassword.classList.toggle(
        "is-invalid",
        value.length > 0 && !valid
    );

    return valid;

}


loginEmail.addEventListener("input", validateLoginEmail);
loginPassword.addEventListener("input", validateLoginPassword);


passwordToggle.addEventListener("click", function(){

    const showPassword = loginPassword.type === "password";

    loginPassword.type = showPassword ? "text" : "password";
    passwordToggle.textContent = showPassword ? "🙈" : "👁";

});


loginForm.addEventListener("submit", function(event){

    const emailValid = validateLoginEmail();
    const passwordValid = validateLoginPassword();

    if(!emailValid || !passwordValid){

        event.preventDefault();

        const firstInvalid =
            loginForm.querySelector(".is-invalid");

        if(firstInvalid){
            firstInvalid.focus();
        }

    }

});

</script>

</body>

</html>