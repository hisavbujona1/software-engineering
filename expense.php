<?php

session_start();

include "config/database.php";
include "functions/notification_function.php";


if(!isset($_SESSION['user_id'])){

    header("Location: login.php");
    exit();

}


$user_id = (int) $_SESSION['user_id'];
$search = trim($_GET['search'] ?? "");



// Delete Expense

if(isset($_GET['delete'])){

    $delete_id = (int) $_GET['delete'];

    $delete_stmt = mysqli_prepare(
        $conn,
        "DELETE FROM expense
         WHERE expense_id = ?
         AND user_id = ?"
    );

    mysqli_stmt_bind_param(
        $delete_stmt,
        "ii",
        $delete_id,
        $user_id
    );

    mysqli_stmt_execute($delete_stmt);
    mysqli_stmt_close($delete_stmt);

    header("Location: expense.php");
    exit();

}



// Add Expense

if(isset($_POST['add_expense'])){

    $category = (int) ($_POST['category'] ?? 0);
    $title = trim($_POST['title'] ?? "");
    $amount = (float) ($_POST['amount'] ?? 0);
    $payment = trim($_POST['payment'] ?? "");
    $date = trim($_POST['date'] ?? "");
    $description = trim($_POST['description'] ?? "");


    // Use current date when date is empty or invalid
    $date_object = DateTime::createFromFormat("Y-m-d", $date);

    if(!$date_object || $date_object->format("Y-m-d") !== $date){

        $date = date("Y-m-d");

    }


    if($category <= 0){

        $error = "Please select a valid category.";

    }
    elseif($title === ""){

        $error = "Please enter an expense title.";

    }
    elseif($amount <= 0){

        $error = "Expense amount must be greater than zero.";

    }
    else{

        $insert_stmt = mysqli_prepare(
            $conn,
            "INSERT INTO expense
            (user_id, category_id, title, amount, payment_method, date, description)
            VALUES (?, ?, ?, ?, ?, ?, ?)"
        );

        mysqli_stmt_bind_param(
            $insert_stmt,
            "iisdsss",
            $user_id,
            $category,
            $title,
            $amount,
            $payment,
            $date,
            $description
        );


        if(mysqli_stmt_execute($insert_stmt)){

            mysqli_stmt_close($insert_stmt);


            checkBudgetNotification(
                $conn,
                $user_id,
                $category,
                $amount,
                $date
            );


            checkBalanceNotification(
                $conn,
                $user_id
            );


            $message = "Expense Added Successfully";

        }
        else{

            mysqli_stmt_close($insert_stmt);

            $error = "Unable to add the expense. Please try again.";

        }

    }

}



// Category Fetch

$category_query = mysqli_query(
    $conn,
    "SELECT category_id, category_name
     FROM category
     ORDER BY category_name ASC"
);



// Expense History With Search

$search_pattern = "%" . $search . "%";

$expense_stmt = mysqli_prepare(
    $conn,
    "SELECT expense.*, category.category_name
     FROM expense
     JOIN category
     ON expense.category_id = category.category_id
     WHERE expense.user_id = ?
     AND (
        expense.title LIKE ?
        OR CAST(expense.amount AS CHAR) LIKE ?
        OR expense.payment_method LIKE ?
        OR expense.date LIKE ?
        OR expense.description LIKE ?
        OR category.category_name LIKE ?
     )
     ORDER BY expense.expense_id DESC"
);

mysqli_stmt_bind_param(
    $expense_stmt,
    "issssss",
    $user_id,
    $search_pattern,
    $search_pattern,
    $search_pattern,
    $search_pattern,
    $search_pattern,
    $search_pattern
);

mysqli_stmt_execute($expense_stmt);
$expense_query = mysqli_stmt_get_result($expense_stmt);



// Total Expense

$total_stmt = mysqli_prepare(
    $conn,
    "SELECT COALESCE(SUM(amount), 0) AS total_expense
     FROM expense
     WHERE user_id = ?"
);

mysqli_stmt_bind_param(
    $total_stmt,
    "i",
    $user_id
);

mysqli_stmt_execute($total_stmt);

$total_result = mysqli_stmt_get_result($total_stmt);
$total_data = mysqli_fetch_assoc($total_result);

$total_expense = (float) ($total_data['total_expense'] ?? 0);

mysqli_stmt_close($total_stmt);


?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Expense Management</title>

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



<div class="d-flex justify-content-between align-items-center mb-4">


<h2>
Expense Management
</h2>


<div class="alert alert-danger mb-0">

Total Expense:

<b>
$<?php echo number_format($total_expense, 2); ?>
</b>

</div>


</div>



<?php if(isset($message)){ ?>

<div class="alert alert-success">
<?php echo htmlspecialchars($message); ?>
</div>

<?php } ?>


<?php if(isset($error)){ ?>

<div class="alert alert-danger">
<?php echo htmlspecialchars($error); ?>
</div>

<?php } ?>



<!-- Add Expense -->


<div class="card shadow border-0 mb-5">


<div class="card-header bg-danger text-white">

<h4>
Add New Expense
</h4>

</div>


<div class="card-body">


<form method="POST">


<div class="row">


<div class="col-md-6 mb-3">

<label class="form-label">
Category
</label>


<select
name="category"
class="form-select"
required>


<option value="">
Select Category
</option>


<?php while($cat = mysqli_fetch_assoc($category_query)){ ?>


<option value="<?php echo $cat['category_id']; ?>">

<?php echo htmlspecialchars($cat['category_name']); ?>

</option>


<?php } ?>


</select>


</div>



<div class="col-md-6 mb-3">


<label class="form-label">
Title
</label>


<input
type="text"
name="title"
class="form-control"
placeholder="Expense title"
required>


</div>


</div>



<div class="row">


<div class="col-md-6 mb-3">


<label class="form-label">
Amount
</label>


<input
type="number"
name="amount"
class="form-control"
min="0.01"
step="0.01"
placeholder="Enter expense amount"
required>


</div>



<div class="col-md-6 mb-3">


<label class="form-label">
Payment Method
</label>


<select
name="payment"
class="form-select"
required>


<option value="Cash">Cash</option>

<option value="Card">Card</option>

<option value="Bank">Bank</option>

<option value="Mobile Banking">Mobile Banking</option>


</select>


</div>


</div>



<div class="row">


<div class="col-md-6 mb-3">


<label class="form-label">
Date
</label>


<input
type="date"
name="date"
class="form-control"
value="<?php echo date('Y-m-d'); ?>"
required>


</div>



<div class="col-md-6 mb-3">


<label class="form-label">
Description
</label>


<input
type="text"
name="description"
class="form-control"
placeholder="Optional description">


</div>


</div>



<button
type="submit"
name="add_expense"
class="btn btn-danger">

+ Add Expense

</button>


</form>


</div>


</div>



<!-- Search Expense -->


<div class="card shadow border-0 mb-4">


<div class="card-body">


<form method="GET" class="row g-3">


<div class="col-md-10">


<input
type="text"
name="search"
class="form-control"
placeholder="Search Expense..."
value="<?php echo htmlspecialchars($search); ?>">


</div>



<div class="col-md-2">


<button class="btn btn-primary w-100">

🔎 Search

</button>


</div>


</form>


</div>


</div>



<!-- History -->


<div class="card shadow">


<div class="card-header bg-dark text-white">

<h4>
Expense History
</h4>

</div>


<div class="card-body table-responsive">


<table class="table table-hover">


<thead>


<tr class="table-danger">

<th>Category</th>

<th>Title</th>

<th>Amount</th>

<th>Date</th>

<th>Action</th>

</tr>


</thead>


<tbody>


<?php while($row = mysqli_fetch_assoc($expense_query)){ ?>


<tr>


<td>
<?php echo htmlspecialchars($row['category_name']); ?>
</td>


<td>
<?php echo htmlspecialchars($row['title']); ?>
</td>


<td>
$<?php echo number_format($row['amount'], 2); ?>
</td>


<td>
<?php echo htmlspecialchars($row['date']); ?>
</td>


<td>


<a
href="expense.php?delete=<?php echo $row['expense_id']; ?>"
onclick="return confirm('Delete Expense?')"
class="btn btn-danger btn-sm">

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

<?php

mysqli_stmt_close($expense_stmt);

?>