<?php

session_start();

include "../config/database.php";


if(!isset($_SESSION['admin_id'])){

    header("Location: ../login.php");
    exit();

}


// Selected report year

$current_year = (int) date("Y");

$selected_year = isset($_GET['year'])
    ? (int) $_GET['year']
    : $current_year;

if($selected_year < 2000 || $selected_year > 2100){

    $selected_year = $current_year;

}


// Available years from income and expense tables

$year_query = mysqli_query(
    $conn,
    "
    SELECT DISTINCT YEAR(date) AS report_year
    FROM income
    WHERE date IS NOT NULL

    UNION

    SELECT DISTINCT YEAR(date) AS report_year
    FROM expense
    WHERE date IS NOT NULL

    ORDER BY report_year DESC
    "
);

$available_years = [];

while($year_row = mysqli_fetch_assoc($year_query)){

    if(!empty($year_row['report_year'])){

        $available_years[] = (int) $year_row['report_year'];

    }

}

if(!in_array($current_year, $available_years, true)){

    $available_years[] = $current_year;
    rsort($available_years);

}



// Total Users

$user_query = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM users"
);

$user_data = mysqli_fetch_assoc($user_query);

$total_users = (int) ($user_data['total'] ?? 0);



// Total Income for selected year

$income_stmt = mysqli_prepare(
    $conn,
    "
    SELECT COALESCE(SUM(amount), 0) AS total
    FROM income
    WHERE YEAR(date) = ?
    "
);

mysqli_stmt_bind_param(
    $income_stmt,
    "i",
    $selected_year
);

mysqli_stmt_execute($income_stmt);

$income_result = mysqli_stmt_get_result($income_stmt);
$income_data = mysqli_fetch_assoc($income_result);

$total_income = (float) ($income_data['total'] ?? 0);

mysqli_stmt_close($income_stmt);



// Total Expense for selected year

$expense_stmt = mysqli_prepare(
    $conn,
    "
    SELECT COALESCE(SUM(amount), 0) AS total
    FROM expense
    WHERE YEAR(date) = ?
    "
);

mysqli_stmt_bind_param(
    $expense_stmt,
    "i",
    $selected_year
);

mysqli_stmt_execute($expense_stmt);

$expense_result = mysqli_stmt_get_result($expense_stmt);
$expense_data = mysqli_fetch_assoc($expense_result);

$total_expense = (float) ($expense_data['total'] ?? 0);

mysqli_stmt_close($expense_stmt);



// Total Transactions for selected year

$transaction_stmt = mysqli_prepare(
    $conn,
    "
    SELECT
    (
        SELECT COUNT(*)
        FROM income
        WHERE YEAR(date) = ?
    )
    +
    (
        SELECT COUNT(*)
        FROM expense
        WHERE YEAR(date) = ?
    )
    AS total
    "
);

mysqli_stmt_bind_param(
    $transaction_stmt,
    "ii",
    $selected_year,
    $selected_year
);

mysqli_stmt_execute($transaction_stmt);

$transaction_result = mysqli_stmt_get_result($transaction_stmt);
$transaction_data = mysqli_fetch_assoc($transaction_result);

$total_transactions = (int) ($transaction_data['total'] ?? 0);

mysqli_stmt_close($transaction_stmt);



// Category-wise expense for selected year

$category_stmt = mysqli_prepare(
    $conn,
    "
    SELECT
        category.category_name,
        COALESCE(SUM(expense.amount), 0) AS total

    FROM expense

    JOIN category
    ON expense.category_id = category.category_id

    WHERE YEAR(expense.date) = ?

    GROUP BY
        category.category_id,
        category.category_name

    HAVING total > 0

    ORDER BY total DESC
    "
);

mysqli_stmt_bind_param(
    $category_stmt,
    "i",
    $selected_year
);

mysqli_stmt_execute($category_stmt);

$category_result = mysqli_stmt_get_result($category_stmt);

$categories = [];
$category_amount = [];

while($row = mysqli_fetch_assoc($category_result)){

    $categories[] = $row['category_name'];
    $category_amount[] = (float) $row['total'];

}

mysqli_stmt_close($category_stmt);



// Initialize all 12 months with zero

$income_month = array_fill(1, 12, 0);
$expense_month = array_fill(1, 12, 0);



// Monthly income data

$monthly_income_stmt = mysqli_prepare(
    $conn,
    "
    SELECT
        MONTH(date) AS month_number,
        COALESCE(SUM(amount), 0) AS total

    FROM income

    WHERE YEAR(date) = ?

    GROUP BY MONTH(date)

    ORDER BY MONTH(date)
    "
);

mysqli_stmt_bind_param(
    $monthly_income_stmt,
    "i",
    $selected_year
);

mysqli_stmt_execute($monthly_income_stmt);

$monthly_income_result = mysqli_stmt_get_result($monthly_income_stmt);

while($row = mysqli_fetch_assoc($monthly_income_result)){

    $month_number = (int) $row['month_number'];

    if($month_number >= 1 && $month_number <= 12){

        $income_month[$month_number] = (float) $row['total'];

    }

}

mysqli_stmt_close($monthly_income_stmt);



// Monthly expense data

$monthly_expense_stmt = mysqli_prepare(
    $conn,
    "
    SELECT
        MONTH(date) AS month_number,
        COALESCE(SUM(amount), 0) AS total

    FROM expense

    WHERE YEAR(date) = ?

    GROUP BY MONTH(date)

    ORDER BY MONTH(date)
    "
);

mysqli_stmt_bind_param(
    $monthly_expense_stmt,
    "i",
    $selected_year
);

mysqli_stmt_execute($monthly_expense_stmt);

$monthly_expense_result = mysqli_stmt_get_result($monthly_expense_stmt);

while($row = mysqli_fetch_assoc($monthly_expense_result)){

    $month_number = (int) $row['month_number'];

    if($month_number >= 1 && $month_number <= 12){

        $expense_month[$month_number] = (float) $row['total'];

    }

}

mysqli_stmt_close($monthly_expense_stmt);


// Convert month arrays to normal zero-based JavaScript arrays

$income_chart_data = [];

$expense_chart_data = [];

for($month = 1; $month <= 12; $month++){

    $income_chart_data[] = $income_month[$month];
    $expense_chart_data[] = $expense_month[$month];

}


$has_category_data = count($categories) > 0;

$has_monthly_data =
    array_sum($income_chart_data) > 0 ||
    array_sum($expense_chart_data) > 0;

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Admin Reports</title>

<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>

body{
    background:#f8fafc;
}

.sidebar{
    background:#111827;
    min-height:100vh;
    padding:25px;
}

.sidebar h4{
    color:white;
}

.sidebar a{
    display:block;
    color:white;
    text-decoration:none;
    padding:12px;
    border-radius:10px;
    margin-bottom:10px;
}

.sidebar a:hover,
.sidebar a.active{
    background:#374151;
}

.card{
    border:0;
    border-radius:18px;
    box-shadow:0 10px 30px rgba(0,0,0,0.08);
}

.chart-box{
    position:relative;
    min-height:360px;
}

.chart-box canvas{
    max-height:330px;
}

.summary-card{
    min-height:145px;
    display:flex;
    align-items:center;
    justify-content:center;
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

<hr class="text-white">

<a href="dashboard.php">
🏠 Dashboard
</a>

<a href="users.php">
👥 Users
</a>

<a href="category.php">
📂 Category
</a>

<a href="reports.php" class="active">
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

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">

<div>

<h2 class="mb-1">
📊 System Reports
</h2>

<p class="text-muted mb-0">
All-user financial activity for <?php echo $selected_year; ?>
</p>

</div>


<form method="GET" class="d-flex align-items-end gap-2">

<div>

<label for="reportYear" class="form-label">
Report Year
</label>

<select
name="year"
id="reportYear"
class="form-select">

<?php foreach($available_years as $year){ ?>

<option
value="<?php echo $year; ?>"
<?php echo ($year === $selected_year) ? "selected" : ""; ?>>

<?php echo $year; ?>

</option>

<?php } ?>

</select>

</div>

<button class="btn btn-primary">
Generate
</button>

</form>

</div>



<!-- Summary Cards -->

<div class="row g-4">

<div class="col-md-6 col-xl-3">

<div class="card summary-card p-4 text-center">

<div>

<h5>
👥 Users
</h5>

<h2>
<?php echo $total_users; ?>
</h2>

</div>

</div>

</div>


<div class="col-md-6 col-xl-3">

<div class="card summary-card p-4 text-center">

<div>

<h5>
💰 Income
</h5>

<h2>
$<?php echo number_format($total_income, 2); ?>
</h2>

</div>

</div>

</div>


<div class="col-md-6 col-xl-3">

<div class="card summary-card p-4 text-center">

<div>

<h5>
💳 Expense
</h5>

<h2>
$<?php echo number_format($total_expense, 2); ?>
</h2>

</div>

</div>

</div>


<div class="col-md-6 col-xl-3">

<div class="card summary-card p-4 text-center">

<div>

<h5>
📊 Transactions
</h5>

<h2>
<?php echo $total_transactions; ?>
</h2>

</div>

</div>

</div>

</div>



<!-- Charts -->

<div class="row mt-5 g-4">

<div class="col-lg-6">

<div class="card p-4 h-100">

<h5 class="text-center mb-3">
Category-wise Expense
</h5>

<?php if($has_category_data){ ?>

<div class="chart-box">

<canvas id="categoryChart"></canvas>

</div>

<?php } else{ ?>

<div class="alert alert-info text-center mb-0">

No category expense data found for <?php echo $selected_year; ?>.

</div>

<?php } ?>

</div>

</div>


<div class="col-lg-6">

<div class="card p-4 h-100">

<h5 class="text-center mb-3">
Monthly Income vs Expense
</h5>

<?php if($has_monthly_data){ ?>

<div class="chart-box">

<canvas id="monthlyChart"></canvas>

</div>

<?php } else{ ?>

<div class="alert alert-info text-center mb-0">

No monthly transaction data found for <?php echo $selected_year; ?>.

</div>

<?php } ?>

</div>

</div>

</div>

</div>

</div>

</div>


<script>

const currencyFormatter = new Intl.NumberFormat(
    "en-US",
    {
        style: "currency",
        currency: "USD"
    }
);


<?php if($has_category_data){ ?>

new Chart(
    document.getElementById("categoryChart"),
    {
        type: "pie",

        data: {
            labels: <?php echo json_encode($categories); ?>,

            datasets: [{
                data: <?php echo json_encode($category_amount); ?>
            }]
        },

        options: {
            responsive: true,
            maintainAspectRatio: false,

            plugins: {
                legend: {
                    position: "bottom"
                },

                tooltip: {
                    callbacks: {
                        label: function(context){
                            return context.label + ": "
                                + currencyFormatter.format(context.raw);
                        }
                    }
                }
            }
        }
    }
);

<?php } ?>


<?php if($has_monthly_data){ ?>

new Chart(
    document.getElementById("monthlyChart"),
    {
        type: "bar",

        data: {
            labels: [
                "Jan",
                "Feb",
                "Mar",
                "Apr",
                "May",
                "Jun",
                "Jul",
                "Aug",
                "Sep",
                "Oct",
                "Nov",
                "Dec"
            ],

            datasets: [
                {
                    label: "Income",
                    data: <?php echo json_encode($income_chart_data); ?>
                },
                {
                    label: "Expense",
                    data: <?php echo json_encode($expense_chart_data); ?>
                }
            ]
        },

        options: {
            responsive: true,
            maintainAspectRatio: false,

            scales: {
                y: {
                    beginAtZero: true
                }
            },

            plugins: {
                legend: {
                    position: "bottom"
                },

                tooltip: {
                    callbacks: {
                        label: function(context){
                            return context.dataset.label + ": "
                                + currencyFormatter.format(context.raw);
                        }
                    }
                }
            }
        }
    }
);

<?php } ?>

</script>

</body>

</html>