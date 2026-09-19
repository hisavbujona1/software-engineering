<?php

session_start();

include "../config/database.php";


if(!isset($_SESSION['admin_id'])){

    header("Location: admin_login.php");
    exit();

}



// Add Category

if(isset($_POST['add_category'])){


    $category_name = $_POST['category_name'];



    mysqli_query($conn,

    "INSERT INTO category(category_name)

    VALUES('$category_name')"

    );


    header("Location: category.php");

    exit();


}






// Delete Category


if(isset($_GET['delete'])){


    $delete_id=$_GET['delete'];



    mysqli_query($conn,


    "DELETE FROM category

    WHERE category_id='$delete_id'"

    );



    header("Location: category.php");

    exit();


}







// Fetch Category


$category_query=mysqli_query($conn,


"SELECT * FROM category

ORDER BY category_id DESC"

);


$total_category=mysqli_num_rows($category_query);



?>



<!DOCTYPE html>

<html>

<head>


<title>Manage Category</title>



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

}



.sidebar a:hover{

background:#374151;

}



/* Card */


.card{

border:none;

border-radius:18px;

box-shadow:0 10px 30px rgba(0,0,0,0.08);

}



/* Input */


.form-control{

height:48px;

border-radius:10px;

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



/* Button */


.btn{

border-radius:10px;

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







<!-- Main -->


<div class="col-md-9 col-lg-10 p-4">



<div class="d-flex justify-content-between align-items-center mb-4">


<h2>

📂 Manage Category

</h2>


<span class="badge bg-success fs-6 p-2">

Total Category : <?php echo $total_category; ?>

</span>


</div>








<!-- Add Category -->


<div class="card mb-4">


<div class="card-body">


<form method="POST" class="row g-3">



<div class="col-md-10">


<input

type="text"

name="category_name"

class="form-control"

placeholder="Enter Category Name"

required>


</div>





<div class="col-md-2">


<button

name="add_category"

class="btn btn-success w-100">

+ Add

</button>


</div>



</form>


</div>


</div>








<!-- Category List -->


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

Category Name

</th>


<th>

Action

</th>


</tr>


</thead>



<tbody>



<?php while($row=mysqli_fetch_assoc($category_query)){ ?>



<tr>



<td>

<?php echo $row['category_id']; ?>

</td>




<td>

📂 <?php echo $row['category_name']; ?>

</td>




<td>


<a

href="category.php?delete=<?php echo $row['category_id']; ?>"

class="btn btn-danger btn-sm"

onclick="return confirm('Delete category?')">


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