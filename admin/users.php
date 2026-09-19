<?php

session_start();

include "../config/database.php";


if(!isset($_SESSION['admin_id'])){

    header("Location: admin_login.php");
    exit();

}



// Delete User

if(isset($_GET['delete'])){


    $delete_id=$_GET['delete'];


    mysqli_query($conn,

    "DELETE FROM users

    WHERE user_id='$delete_id'"

    );


    header("Location: users.php");

    exit();

}





// Fetch Users


$user_query=mysqli_query($conn,


"SELECT * FROM users

ORDER BY user_id DESC"

);



$total_users = mysqli_num_rows($user_query);



?>



<!DOCTYPE html>

<html>

<head>


<title>Manage Users</title>



<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">



<style>


body{

background:#f8fafc;

}



/* Sidebar */


.sidebar{

background:#111827;

min-height:100vh;

padding:25px;

}



.sidebar h4{

color:white;

font-size:24px;

}



.sidebar a{

display:block;

color:white;

text-decoration:none;

padding:12px 15px;

border-radius:10px;

margin-bottom:10px;

font-size:16px;

}



.sidebar a:hover{

background:#374151;

}



/* Main */


.page-title{

font-weight:700;

color:#111827;

}



/* Card */


.card{

border:none;

border-radius:18px;

box-shadow:0 10px 30px rgba(0,0,0,0.08);

}



/* Table */


.table{

margin-bottom:0;

}



.table thead th{

background:#111827;

color:white;

padding:15px;

}



.table tbody td{

padding:15px;

vertical-align:middle;

}



.table tbody tr:hover{

background:#f1f5f9;

}



/* Delete Button */


.btn-delete{

border-radius:8px;

padding:6px 14px;

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



<a href="reports.php">

📊 Reports

</a>



<a href="admin_logout.php">

🚪 Logout

</a>



</div>







<!-- Main Content -->


<div class="col-md-9 col-lg-10 p-4">



<div class="d-flex justify-content-between align-items-center mb-4">


<h2 class="page-title">

👥 Manage Users

</h2>



<span class="badge bg-primary fs-6 p-2">

Total Users : <?php echo $total_users; ?>

</span>


</div>







<div class="card">


<div class="card-body p-0">



<div class="table-responsive">


<table class="table table-hover">


<thead>


<tr>


<th>
ID
</th>


<th>
Name
</th>


<th>
Email
</th>


<th>
Action
</th>


</tr>


</thead>





<tbody>



<?php while($row=mysqli_fetch_assoc($user_query)){ ?>



<tr>


<td>

<?php echo $row['user_id']; ?>

</td>




<td>

👤 <?php echo $row['name']; ?>

</td>




<td>

<?php echo $row['email']; ?>

</td>




<td>



<a 

href="users.php?delete=<?php echo $row['user_id']; ?>"

class="btn btn-danger btn-sm btn-delete"

onclick="return confirm('Delete this user?')">


🗑 Delete


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



</body>


</html>