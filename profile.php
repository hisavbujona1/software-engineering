<?php

session_start();

include "config/database.php";


if(!isset($_SESSION['user_id'])){

    header("Location: login.php");
    exit();

}


$user_id=$_SESSION['user_id'];



// Update Profile


if(isset($_POST['update_profile'])){


$name=$_POST['name'];

$phone=$_POST['phone'];



$image=$_FILES['profile_image']['name'];


if($image!=""){


$tmp=$_FILES['profile_image']['tmp_name'];


$path="uploads/".$image;


move_uploaded_file($tmp,$path);



mysqli_query($conn,

"UPDATE users

SET

name='$name',

phone='$phone',

profile_image='$image'

WHERE user_id='$user_id'"

);



}

else{


mysqli_query($conn,

"UPDATE users

SET

name='$name',

phone='$phone'

WHERE user_id='$user_id'"

);


}



$message="Profile Updated Successfully";


}





// Fetch User Data


$result=mysqli_query($conn,

"SELECT * FROM users

WHERE user_id='$user_id'"

);


$user=mysqli_fetch_assoc($result);



?>



<!DOCTYPE html>

<html>

<head>


<title>Profile</title>


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


<a href="logout.php">
🚪 Logout
</a>


</div>







<!-- Main -->


<div class="col-md-9 col-lg-10 p-4">


<h2 class="mb-4">

👤 My Profile

</h2>





<?php

if(isset($message)){

echo "

<div class='alert alert-success'>

$message

</div>";

}

?>





<div class="card shadow border-0 p-4">


<form method="POST" enctype="multipart/form-data">



<div class="text-center mb-4">



<?php

if($user['profile_image']){

?>

<img src="uploads/<?php echo $user['profile_image']; ?>"

width="120"

height="120"

class="rounded-circle">


<?php

}

else{

?>

<img src="https://via.placeholder.com/120"

class="rounded-circle">


<?php

}

?>


</div>







<div class="mb-3">


<label>
Name
</label>


<input type="text"

name="name"

class="form-control"

value="<?php echo $user['name']; ?>">


</div>






<div class="mb-3">


<label>
Email
</label>


<input type="email"

class="form-control"

value="<?php echo $user['email']; ?>"

readonly>


</div>






<div class="mb-3">


<label>
Phone
</label>


<input type="text"

name="phone"

class="form-control"

value="<?php echo $user['phone']; ?>">


</div>






<div class="mb-3">


<label>
Profile Picture
</label>


<input type="file"

name="profile_image"

class="form-control">


</div>







<button 

name="update_profile"

class="btn btn-primary">

Update Profile

</button>

<a href="change_password.php" class="btn btn-warning">
🔐 Change Password
</a>

</form>



</div>






</div>


</div>


</div>


</body>

</html>