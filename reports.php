<?php

session_start();

include "config/database.php";


if(!isset($_SESSION['user_id'])){

    header("Location: login.php");
    exit();

}


$user_id = $_SESSION['user_id'];


// Default Month & Year

$selected_month = date('m');

$selected_year = date('Y');



// Filter Submit

if(isset($_POST['filter_report'])){

    $selected_month = $_POST['month'];

    $selected_year = $_POST['year'];

}



// =========================
// Monthly Income
// =========================


$income_query = mysqli_query($conn,


"SELECT SUM(amount) AS total_income

FROM income

WHERE user_id='$user_id'

AND MONTH(date)='$selected_month'

AND YEAR(date)='$selected_year'

"

);


$income_data=mysqli_fetch_assoc($income_query);


$total_income=$income_data['total_income'] ?? 0;



// =========================
// Monthly Expense
// =========================


$expense_query=mysqli_query($conn,


"SELECT SUM(amount) AS total_expense

FROM expense

WHERE user_id='$user_id'

AND MONTH(date)='$selected_month'

AND YEAR(date)='$selected_year'

"

);


$expense_data=mysqli_fetch_assoc($expense_query);


$total_expense=$expense_data['total_expense'] ?? 0;



// Balance

$balance = $total_income - $total_expense;




// =========================
// Category Wise Expense
// =========================


$category_query=mysqli_query($conn,


"SELECT 

category.category_name,

SUM(expense.amount) AS total


FROM expense


JOIN category

ON expense.category_id = category.category_id


WHERE expense.user_id='$user_id'


AND MONTH(expense.date)='$selected_month'

AND YEAR(expense.date)='$selected_year'


GROUP BY category.category_name"


);



$categories=[];

$category_amount=[];



while($row=mysqli_fetch_assoc($category_query)){


    $categories[]=$row['category_name'];

    $category_amount[]=$row['total'];


}



?>



<!DOCTYPE html>

<html>

<head>


<title>Reports & Analytics</title>



<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">


<link rel="stylesheet" href="css/style.css">


<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>


</head>


<body>


<div class="container-fluid">


<div class="row">



<!-- Sidebar -->

<div class="col-md-3 col-lg-2 sidebar" id="sidebar">


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


<a href="logout.php">
🚪 Logout
</a>


</div>

<button 
class="btn btn-dark mobile-menu-btn"
id="menuToggle">

☰ Menu

</button>



<!-- Main Content -->


<div class="col-md-9 col-lg-10 p-4">


<h2 class="mb-4">

📊 Reports & Analytics

</h2>




<!-- Filter -->


<div class="card shadow mb-4 p-4">


<form method="POST" class="row g-3">


<div class="col-md-5">


<label class="form-label">

Select Month

</label>


<select name="month" class="form-select">


<option value="1" <?php if($selected_month=="1") echo "selected"; ?>>
January
</option>


<option value="2" <?php if($selected_month=="2") echo "selected"; ?>>
February
</option>


<option value="3" <?php if($selected_month=="3") echo "selected"; ?>>
March
</option>


<option value="4" <?php if($selected_month=="4") echo "selected"; ?>>
April
</option>


<option value="5" <?php if($selected_month=="5") echo "selected"; ?>>
May
</option>


<option value="6" <?php if($selected_month=="6") echo "selected"; ?>>
June
</option>


<option value="7" <?php if($selected_month=="7") echo "selected"; ?>>
July
</option>


<option value="8" <?php if($selected_month=="8") echo "selected"; ?>>
August
</option>


<option value="9" <?php if($selected_month=="9") echo "selected"; ?>>
September
</option>


<option value="10" <?php if($selected_month=="10") echo "selected"; ?>>
October
</option>


<option value="11" <?php if($selected_month=="11") echo "selected"; ?>>
November
</option>


<option value="12" <?php if($selected_month=="12") echo "selected"; ?>>
December
</option>


</select>


</div>





<div class="col-md-5">


<label class="form-label">

Select Year

</label>


<select name="year" class="form-select">


<option <?php if($selected_year=="2025") echo "selected"; ?>>
2025
</option>


<option <?php if($selected_year=="2026") echo "selected"; ?>>
2026
</option>


<option <?php if($selected_year=="2027") echo "selected"; ?>>
2027
</option>


<option <?php if($selected_year=="2028") echo "selected"; ?>>
2028
</option>


</select>


</div>





<div class="col-md-2 d-flex align-items-end">

<button 
name="filter_report"
class="btn btn-primary w-100">

Generate

</button>

</div>


<div class="col-md-2 d-flex align-items-end">

<a href="export_pdf.php?month=<?php echo $selected_month; ?>&year=<?php echo $selected_year; ?>"

class="btn btn-danger w-100">

📥 Export PDF

</a>

</div>



</form>


</div>





<!-- Summary -->


<div class="row mb-4">


<div class="col-md-4">


<div class="card shadow p-3 text-center">


<h6>
Income
</h6>


<h3>
$<?php echo number_format($total_income,2); ?>
</h3>


</div>


</div>





<div class="col-md-4">


<div class="card shadow p-3 text-center">


<h6>
Expense
</h6>


<h3>
$<?php echo number_format($total_expense,2); ?>
</h3>


</div>


</div>





<div class="col-md-4">


<div class="card shadow p-3 text-center">


<h6>
Balance
</h6>


<h3>
$<?php echo number_format($balance,2); ?>
</h3>


</div>


</div>



</div>





<!-- Charts -->


<div class="row g-4">


<div class="col-lg-6">


<div class="card shadow p-4">


<h5 class="text-center">

Income vs Expense

</h5>


<canvas id="incomeExpenseChart"></canvas>


</div>


</div>





<div class="col-lg-6">


<div class="card shadow p-4">


<h5 class="text-center">

Expense By Category

</h5>


<canvas id="categoryChart"></canvas>


</div>


</div>


</div>



</div>


</div>


</div>






<script>


new Chart(document.getElementById('incomeExpenseChart'),{


type:'bar',


data:{


labels:[

"Income",

"Expense"

],


datasets:[{


label:"Amount",


data:[

<?php echo $total_income; ?>,

<?php echo $total_expense; ?>

]


}]


},


options:{

responsive:true

}


});







new Chart(document.getElementById('categoryChart'),{


type:'pie',


data:{


labels:

<?php echo json_encode($categories); ?>,


datasets:[{


data:

<?php echo json_encode($category_amount); ?>


}]


},


options:{

responsive:true

}


});



</script>

<script src="js/darkmode.js"></script>

</body>

</html>