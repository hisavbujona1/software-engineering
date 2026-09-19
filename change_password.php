<?php

session_start();

include "config/database.php";


if(!isset($_SESSION['user_id'])){

    header("Location: login.php");
    exit();

}


$user_id = $_SESSION['user_id'];



if(isset($_POST['change_password'])){


    $current_password = $_POST['current_password'];

    $new_password = $_POST['new_password'];

    $confirm_password = $_POST['confirm_password'];



    // Get Current User Password


    $query = mysqli_query($conn,

    "SELECT password FROM users WHERE user_id='$user_id'"

    );


    $user = mysqli_fetch_assoc($query);



    // Verify Old Password


    if(password_verify($current_password,$user['password'])){


        if($new_password == $confirm_password){



            $hashed_password = password_hash(
                $new_password,
                PASSWORD_DEFAULT
            );



            mysqli_query($conn,

            "UPDATE users

            SET password='$hashed_password'

            WHERE user_id='$user_id'"

            );



            $success = "Password Changed Successfully";



        }

        else{


            $error = "New Password and Confirm Password do not match";


        }



    }

    else{


        $error = "Current Password is Incorrect";


    }



}



?>



<!DOCTYPE html>

<html>

<head>

<title>Change Password</title>


<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">


<link rel="stylesheet" href="css/style.css">


</head>



<body>



<div class="container-fluid">


<div class="row">



<!-- Sidebar -->

<div class="col-md-3 col-lg-2 sidebar">


<h4>
Smart Expense
</h4>


<a href="dashboard.php">
🏠 Dashboard
</a>


<a href="income.php">
💰 Income
</a>


<a href="expense.php">
💳 Expense
</a>


<a href="budget.php">
📊 Budget
</a>


<a href="reports.php">
📈 Reports
</a>


<a href="notification.php">
🔔 Notification
</a>


<a href="profile.php">
👤 Profile
</a>


<a href="change_password.php">
🔐 Change Password
</a>


<a href="logout.php">
🚪 Logout
</a>


</div>






<!-- Main -->

<div class="col-md-9 col-lg-10 p-5">



<h2 class="mb-4">

🔐 Change Password

</h2>




<div class="card shadow border-0 p-4">


<?php

if(isset($success)){

echo '

<div class="alert alert-success">

'.$success.'

</div>';

}



if(isset($error)){

echo '

<div class="alert alert-danger">

'.$error.'

</div>';

}


?>




<form method="POST">



<div class="mb-3">


<label class="fw-bold">

Current Password

</label>


<input 
type="password"
name="current_password"
class="form-control"
required>


</div>






<div class="mb-3">


<label class="fw-bold">

New Password

</label>


<input 
type="password"
name="new_password"
class="form-control"
required>


</div>







<div class="mb-3">


<label class="fw-bold">

Confirm New Password

</label>


<input 
type="password"
name="confirm_password"
class="form-control"
required>


</div>






<button 
name="change_password"
class="btn btn-primary">

Update Password

</button>



</form>



</div>



</div>



</div>


</div>



</body>

</html>