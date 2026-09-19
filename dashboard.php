<?php

session_start();


if(!isset($_SESSION['user_id'])){

    header("Location: login.php");
    exit();

}


include "config/database.php";


$user_id = $_SESSION['user_id'];



// Unread Notification Count

$notification_query = "

SELECT COUNT(*) AS total_notification

FROM notification

WHERE user_id='$user_id'

AND status='Unread'

";


$notification_result = mysqli_query($conn,$notification_query);


$notification_data = mysqli_fetch_assoc($notification_result);


$unread_notification = $notification_data['total_notification'] ?? 0;






// Total Income

$income_query = "

SELECT SUM(amount) AS total_income

FROM income

WHERE user_id='$user_id'

";


$income_result = mysqli_query($conn,$income_query);


$income_data = mysqli_fetch_assoc($income_result);


$total_income = $income_data['total_income'] ?? 0;







// Total Expense

$expense_query = "

SELECT SUM(amount) AS total_expense

FROM expense

WHERE user_id='$user_id'

";


$expense_result = mysqli_query($conn,$expense_query);


$expense_data = mysqli_fetch_assoc($expense_result);


$total_expense = $expense_data['total_expense'] ?? 0;






// Balance Calculation


$balance = $total_income - $total_expense;







// Total Budget


$budget_query = "

SELECT SUM(limit_amount) AS total_budget

FROM budget

WHERE user_id='$user_id'

";


$budget_result = mysqli_query($conn,$budget_query);


$budget_data = mysqli_fetch_assoc($budget_result);


$total_budget = $budget_data['total_budget'] ?? 0;



?>



<!DOCTYPE html>

<html>

<head>


<title>Dashboard</title>



<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">



<link rel="stylesheet" href="css/style.css">


</head>



<body>



<nav class="navbar navbar-expand-lg navbar-dark bg-primary">


<div class="container-fluid">


<a class="navbar-brand" href="#">

Smart Expense Tracker

</a>



<button 
class="navbar-toggler"

type="button"

data-bs-toggle="collapse"

data-bs-target="#navbarMenu">


<span class="navbar-toggler-icon"></span>


</button>




<div class="collapse navbar-collapse" id="navbarMenu">


<ul class="navbar-nav ms-auto">


<li class="nav-item">


<a href="javascript:void(0)"

class="nav-link text-white"

id="darkToggle">

🌙 Dark Mode

</a>


</li>



<li class="nav-item">


<a class="nav-link text-white"

href="logout.php">

Logout

</a>


</li>


</ul>


</div>


</div>


</nav>







<div class="container-fluid">


<div class="row">



<!-- Sidebar -->


<div class="col-md-3 col-lg-2 sidebar" id="sidebar">



<h5>

Menu

</h5>



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


<?php if($unread_notification > 0){ ?>

<span class="badge bg-danger">

<?php echo $unread_notification; ?>

</span>

<?php } ?>


</a>



<a href="profile.php">

Profile

</a>



</div>






<button 
class="btn btn-dark mobile-menu-btn"

id="menuToggle">

☰ Menu

</button>







<!-- Main Content -->


<div class="col-md-9 col-lg-10 p-4">



<h2>

Welcome, <?php echo $_SESSION['name']; ?>

</h2>



<p>

Manage your personal finance easily.

</p>







<!-- Cards -->


<div class="row mt-4">






<div class="col-md-3 col-sm-6 mb-3">


<div class="card shadow text-center">


<div class="card-body">


<h5>

Total Income

</h5>


<h3>

$<?php echo number_format($total_income,2); ?>

</h3>


</div>


</div>


</div>








<div class="col-md-3 col-sm-6 mb-3">


<div class="card shadow text-center">


<div class="card-body">


<h5>

Total Expense

</h5>


<h3>

$<?php echo number_format($total_expense,2); ?>

</h3>


</div>


</div>


</div>








<div class="col-md-3 col-sm-6 mb-3">


<div class="card shadow text-center">


<div class="card-body">


<h5>

Balance

</h5>


<h3>

$<?php echo number_format($balance,2); ?>

</h3>


</div>


</div>


</div>








<div class="col-md-3 col-sm-6 mb-3">


<div class="card shadow text-center">


<div class="card-body">


<h5>

Budget

</h5>


<h3>

$<?php echo number_format($total_budget,2); ?>

</h3>


</div>


</div>


</div>






</div>







<!-- Quick Buttons -->


<div class="mt-4">



<a href="income.php"

class="btn btn-success">

+ Add Income

</a>





<a href="expense.php"

class="btn btn-danger">

+ Add Expense

</a>





<a href="reports.php"

class="btn btn-info">

View Report

</a>




</div>







</div>


</div>


</div>







<script src="js/darkmode.js"></script>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>



</body>


</html>