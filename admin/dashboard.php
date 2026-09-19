<?php

session_start();

include "../config/database.php";


if(!isset($_SESSION['admin_id'])){

    header("Location: ../login.php");
    exit();

}


$admin_name = $_SESSION['admin_name'];
$current_year = (int) date("Y");



// Total Users

$user_query = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total_users FROM users"
);

$user_data = mysqli_fetch_assoc($user_query);

$total_users = (int) ($user_data['total_users'] ?? 0);



// Current Year Income

$income_stmt = mysqli_prepare(
    $conn,
    "SELECT COALESCE(SUM(amount),0) AS total_income
     FROM income
     WHERE YEAR(date)=?"
);

mysqli_stmt_bind_param($income_stmt,"i",$current_year);
mysqli_stmt_execute($income_stmt);

$income_result = mysqli_stmt_get_result($income_stmt);
$income_data = mysqli_fetch_assoc($income_result);

$total_income = (float) ($income_data['total_income'] ?? 0);

mysqli_stmt_close($income_stmt);



// Current Year Expense

$expense_stmt = mysqli_prepare(
    $conn,
    "SELECT COALESCE(SUM(amount),0) AS total_expense
     FROM expense
     WHERE YEAR(date)=?"
);

mysqli_stmt_bind_param($expense_stmt,"i",$current_year);
mysqli_stmt_execute($expense_stmt);

$expense_result = mysqli_stmt_get_result($expense_stmt);
$expense_data = mysqli_fetch_assoc($expense_result);

$total_expense = (float) ($expense_data['total_expense'] ?? 0);

mysqli_stmt_close($expense_stmt);



// Current Year Transactions

$transaction_stmt = mysqli_prepare(
    $conn,
    "
    SELECT
    (
        SELECT COUNT(*)
        FROM income
        WHERE YEAR(date)=?
    )
    +
    (
        SELECT COUNT(*)
        FROM expense
        WHERE YEAR(date)=?
    )
    AS total_transactions
    "
);

mysqli_stmt_bind_param(
    $transaction_stmt,
    "ii",
    $current_year,
    $current_year
);

mysqli_stmt_execute($transaction_stmt);

$transaction_result = mysqli_stmt_get_result($transaction_stmt);
$transaction_data = mysqli_fetch_assoc($transaction_result);

$total_transactions = (int) ($transaction_data['total_transactions'] ?? 0);

mysqli_stmt_close($transaction_stmt);



// Actual Notification Row Count

$notification_query = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total_notification
     FROM notification"
);

$notification_data = mysqli_fetch_assoc($notification_query);

$total_notification = (int) ($notification_data['total_notification'] ?? 0);

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Admin Dashboard</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body{
    background:#f8fafc;
}

.sidebar{
    min-height:100vh;
    background:#111827;
    color:white;
    padding:25px;
}

.sidebar h4{
    color:white;
}

.sidebar a{
    display:block;
    color:white;
    text-decoration:none;
    padding:12px 15px;
    border-radius:10px;
    margin-bottom:8px;
}

.sidebar a:hover,
.sidebar a.active{
    background:#374151;
}

.card-box{
    border-radius:18px;
    border:0;
    box-shadow:0 10px 25px rgba(0,0,0,0.08);
    min-height:150px;
    display:flex;
    justify-content:center;
    align-items:center;
}

.quick-card{
    border:0;
    border-radius:18px;
    box-shadow:0 10px 25px rgba(0,0,0,0.08);
}

.quick-btn{
    border-radius:10px;
    padding:12px 18px;
}

@media(max-width:767px){

    .sidebar{
        min-height:auto;
    }

}

</style>

</head>

<body>

<div class="container-fluid">

<div class="row">


<!-- Sidebar -->

<div class="col-md-3 col-lg-2 sidebar">

<h4>
⚙ Admin Panel
</h4>

<hr>

<a href="dashboard.php" class="active">
🏠 Dashboard
</a>

<a href="users.php">
👥 Manage Users
</a>

<a href="category.php">
📂 Manage Category
</a>

<a href="reports.php">
📊 Reports
</a>

<a href="notification.php">
🔔 Notification
</a>

<a href="admin_logout.php">
🚪 Logout
</a>

</div>


<!-- Main Content -->

<div class="col-md-9 col-lg-10 p-4">

<h2>
Welcome, <?php echo htmlspecialchars($admin_name); ?> 👋
</h2>

<p class="text-muted">
Smart Expense Tracker Administration — <?php echo $current_year; ?> Overview
</p>


<!-- Summary Cards -->

<div class="row g-3 mt-3">

<div class="col-md-6 col-xl">

<div class="card card-box text-center p-3">

<div>

<h6>
👥 Total Users
</h6>

<h3>
<?php echo $total_users; ?>
</h3>

</div>

</div>

</div>


<div class="col-md-6 col-xl">

<div class="card card-box text-center p-3">

<div>

<h6>
💰 <?php echo $current_year; ?> Income
</h6>

<h3>
$<?php echo number_format($total_income,2); ?>
</h3>

</div>

</div>

</div>


<div class="col-md-6 col-xl">

<div class="card card-box text-center p-3">

<div>

<h6>
💳 <?php echo $current_year; ?> Expense
</h6>

<h3>
$<?php echo number_format($total_expense,2); ?>
</h3>

</div>

</div>

</div>


<div class="col-md-6 col-xl">

<div class="card card-box text-center p-3">

<div>

<h6>
📊 <?php echo $current_year; ?> Transactions
</h6>

<h3>
<?php echo $total_transactions; ?>
</h3>

</div>

</div>

</div>


<div class="col-md-6 col-xl">

<div class="card card-box text-center p-3">

<div>

<h6>
🔔 Total Notifications
</h6>

<h3>
<?php echo $total_notification; ?>
</h3>

</div>

</div>

</div>

</div>


<!-- Quick Management -->

<div class="card quick-card mt-4 p-4">

<h4>
Quick Management
</h4>

<div class="mt-3">

<a href="users.php" class="btn btn-primary quick-btn me-2 mb-2">
👥 Manage Users
</a>

<a href="category.php" class="btn btn-success quick-btn me-2 mb-2">
📂 Manage Category
</a>

<a href="reports.php?year=<?php echo $current_year; ?>" class="btn btn-warning quick-btn me-2 mb-2">
📊 View <?php echo $current_year; ?> Reports
</a>

<a href="notification.php" class="btn btn-info text-white quick-btn mb-2">
🔔 Send Notification
</a>

</div>

</div>

</div>

</div>

</div>

</body>

</html>