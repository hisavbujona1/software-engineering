<?php

session_start();

include "config/database.php";


if(!isset($_SESSION['user_id'])){

    header("Location: login.php");
    exit();

}


$user_id = $_SESSION['user_id'];


// Current month and year

$current_month = date("F");
$current_year = date("Y");



// Delete Budget

if(isset($_GET['delete'])){


    $delete_id = $_GET['delete'];


    mysqli_query($conn,

    "DELETE FROM budget

    WHERE budget_id='$delete_id'

    AND user_id='$user_id'"

    );


    header("Location: budget.php");

    exit();

}




// Add Budget

if(isset($_POST['add_budget'])){


    $category = $_POST['category'];

    $amount = $_POST['amount'];

    $month = $_POST['month'];

    $year = $_POST['year'];



    $sql = "

    INSERT INTO budget

    (user_id, category_id, limit_amount, month, year)

    VALUES

    ('$user_id',
    '$category',
    '$amount',
    '$month',
    '$year')

    ";



    if(mysqli_query($conn,$sql)){

        $message = "Budget Added Successfully";

    }


}





// Category Fetch

$category_query = mysqli_query($conn,

"SELECT * FROM category"

);





// Budget History

$budget_query = mysqli_query($conn,


"SELECT budget.*, category.category_name

FROM budget

JOIN category

ON budget.category_id = category.category_id

WHERE budget.user_id='$user_id'

ORDER BY budget_id DESC"


);



?>



<!DOCTYPE html>

<html>

<head>


<title>Budget Management</title>



<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">


<link rel="stylesheet" href="css/style.css">


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

Budget Management

</h2>




<?php

if(isset($message)){

echo '

<div class="alert alert-success">

'.$message.'

</div>';

}

?>





<!-- Add Budget -->

<div class="card shadow border-0 mb-5">


<div class="card-header bg-primary text-white">

<h4>
Set New Budget
</h4>


</div>



<div class="card-body">



<form method="POST">



<div class="row">



<div class="col-md-6 mb-3">


<label>
Category
</label>


<select name="category" class="form-select" required>


<?php while($cat=mysqli_fetch_assoc($category_query)){ ?>


<option value="<?php echo $cat['category_id']; ?>">


<?php echo $cat['category_name']; ?>


</option>


<?php } ?>


</select>


</div>





<div class="col-md-6 mb-3">


<label>
Budget Amount
</label>


<input type="number"

name="amount"

class="form-control"

placeholder="Enter budget amount"

min="1"

step="0.01"

required>


</div>



</div>





<div class="row">


<div class="col-md-6 mb-3">


<label>
Month
</label>


<select name="month" class="form-select" required>


<option value="January" <?php echo ($current_month=="January") ? "selected" : ""; ?>>
January
</option>

<option value="February" <?php echo ($current_month=="February") ? "selected" : ""; ?>>
February
</option>

<option value="March" <?php echo ($current_month=="March") ? "selected" : ""; ?>>
March
</option>

<option value="April" <?php echo ($current_month=="April") ? "selected" : ""; ?>>
April
</option>

<option value="May" <?php echo ($current_month=="May") ? "selected" : ""; ?>>
May
</option>

<option value="June" <?php echo ($current_month=="June") ? "selected" : ""; ?>>
June
</option>

<option value="July" <?php echo ($current_month=="July") ? "selected" : ""; ?>>
July
</option>

<option value="August" <?php echo ($current_month=="August") ? "selected" : ""; ?>>
August
</option>

<option value="September" <?php echo ($current_month=="September") ? "selected" : ""; ?>>
September
</option>

<option value="October" <?php echo ($current_month=="October") ? "selected" : ""; ?>>
October
</option>

<option value="November" <?php echo ($current_month=="November") ? "selected" : ""; ?>>
November
</option>

<option value="December" <?php echo ($current_month=="December") ? "selected" : ""; ?>>
December
</option>


</select>


</div>





<div class="col-md-6 mb-3">


<label>
Year
</label>


<input type="number"

name="year"

class="form-control"

value="<?php echo $current_year; ?>"

min="2000"

max="2100"

required>


</div>


</div>






<button name="add_budget"

class="btn btn-primary">

+ Save Budget

</button>




</form>


</div>


</div>







<!-- Budget History -->


<div class="card shadow border-0">


<div class="card-header bg-dark text-white">


<h4>
Budget History
</h4>


</div>




<div class="card-body table-responsive">


<table class="table table-hover">



<thead class="table-primary">


<tr>


<th>
Category
</th>


<th>
Amount
</th>


<th>
Month
</th>


<th>
Year
</th>


<th>
Action
</th>


</tr>


</thead>



<tbody>



<?php while($row=mysqli_fetch_assoc($budget_query)){ ?>


<tr>


<td>

<?php echo $row['category_name']; ?>

</td>


<td>

$<?php echo number_format($row['limit_amount'],2); ?>

</td>


<td>

<?php echo $row['month']; ?>

</td>


<td>

<?php echo $row['year']; ?>

</td>


<td>


<a href="budget.php?delete=<?php echo $row['budget_id']; ?>"

class="btn btn-danger btn-sm"

onclick="return confirm('Delete Budget?')">


Delete


</a>


</td>


</tr>


<?php } ?>



</tbody>


</table>


</div>


</div>




</div>


</div>


</div>


<script src="js/darkmode.js"></script>

</body>


</html>