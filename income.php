<?php

session_start();



include "config/database.php";


if(!isset($_SESSION['user_id'])){

    header("Location: login.php");
    exit();

}
$search = "";

if(isset($_GET['search'])){

    $search = $_GET['search'];

}

$user_id = $_SESSION['user_id'];



// Delete Income

if(isset($_GET['delete'])){


    $delete_id = $_GET['delete'];


    $delete_query = "
    DELETE FROM income 
    WHERE income_id='$delete_id'
    AND user_id='$user_id'
    ";


    mysqli_query($conn,$delete_query);


    header("Location: income.php");

    exit();

}





// Add Income


if(isset($_POST['add_income'])){


    $source = $_POST['source'];

    $amount = $_POST['amount'];

    $date = $_POST['date'];

    $description = $_POST['description'];



    $sql = "

    INSERT INTO income

    (user_id,source,amount,date,description)

    VALUES

    ('$user_id',
    '$source',
    '$amount',
    '$date',
    '$description')

    ";



    if(mysqli_query($conn,$sql)){

        $message = "Income Added Successfully";

    }


}




// Total Income


$total_query = "

SELECT SUM(amount) AS total_income

FROM income

WHERE user_id='$user_id'

";


$total_result = mysqli_query($conn,$total_query);


$total_data = mysqli_fetch_assoc($total_result);


$total_income = $total_data['total_income'] ?? 0;




// Income History


$income_query = "

SELECT * FROM income

WHERE user_id='$user_id'

AND (

source LIKE '%$search%'

OR amount LIKE '%$search%'

OR date LIKE '%$search%'

OR description LIKE '%$search%'

)

ORDER BY income_id DESC

";


$income_result = mysqli_query($conn,$income_query);



?>



<!DOCTYPE html>

<html>

<head>

<title>Income Management</title>


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
📈 Report
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



<div class="d-flex justify-content-between align-items-center mb-4">


<h2>
Income Management
</h2>



<div class="alert alert-success mb-0">

Total Income:
<b>
$<?php echo number_format($total_income,2); ?>
</b>

</div>



</div>





<?php

if(isset($message)){

echo '

<div class="alert alert-success">

'.$message.'

</div>

';

}

?>







<!-- Add Income Form -->


<div class="card shadow border-0 mb-5">


<div class="card-header bg-success text-white">

<h4>
Add New Income
</h4>

</div>



<div class="card-body">


<form method="POST">



<div class="row">


<div class="col-md-6 mb-3">


<label>
Source
</label>


<select name="source" class="form-select">


<option>Salary</option>

<option>Business</option>

<option>Freelance</option>

<option>Investment</option>

<option>Others</option>


</select>


</div>





<div class="col-md-6 mb-3">


<label>
Amount
</label>


<input type="number"
name="amount"
class="form-control"
required>


</div>



</div>






<div class="row">


<div class="col-md-6 mb-3">


<label>
Date
</label>


<input type="date"
name="date"
class="form-control"
required>


</div>



<div class="col-md-6 mb-3">


<label>
Description
</label>


<input type="text"
name="description"
class="form-control">


</div>


</div>





<button 
name="add_income"
class="btn btn-success">

+ Add Income

</button>



</form>


</div>

</div>





<!-- Search Income -->

<div class="card shadow border-0 mb-4">

<div class="card-body">


<form method="GET" class="row g-3">


<div class="col-md-10">


<input 
type="text"
name="search"
class="form-control"
placeholder="Search Income..."
value="<?php echo $search; ?>">


</div>


<div class="col-md-2">


<button class="btn btn-primary w-100">

🔎 Search

</button>


</div>


</form>


</div>

</div>

<!-- Income History -->


<div class="card shadow border-0">


<div class="card-header bg-dark text-white">

<h4>
Income History
</h4>

</div>




<div class="card-body">


<div class="table-responsive">


<table class="table table-hover">


<thead class="table-success">


<tr>

<th>
Source
</th>


<th>
Amount
</th>


<th>
Date
</th>


<th>
Description
</th>


<th>
Action
</th>


</tr>


</thead>



<tbody>


<?php while($row=mysqli_fetch_assoc($income_result)){ ?>


<tr>


<td>

<?php echo $row['source']; ?>

</td>


<td>

$<?php echo number_format($row['amount'],2); ?>

</td>


<td>

<?php echo $row['date']; ?>

</td>


<td>

<?php echo $row['description']; ?>

</td>


<td>


<a href="income.php?delete=<?php echo $row['income_id']; ?>"

class="btn btn-danger btn-sm"

onclick="return confirm('Delete this income?')">

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


</div>

<script src="js/darkmode.js"></script>
</body>


</html>