<?php

session_start();

include "config/database.php";


if(!isset($_SESSION['user_id'])){

    header("Location: login.php");
    exit();

}


$user_id = $_SESSION['user_id'];



// Delete Notification

if(isset($_GET['delete'])){


    $id=$_GET['delete'];


    mysqli_query($conn,

    "DELETE FROM notification

    WHERE notification_id='$id'

    AND user_id='$user_id'"

    );


    header("Location: notification.php");

    exit();

}




// Mark Read

if(isset($_GET['read'])){


    $id=$_GET['read'];


    mysqli_query($conn,

    "UPDATE notification

    SET status='Read'

    WHERE notification_id='$id'

    AND user_id='$user_id'"

    );


    header("Location: notification.php");

    exit();

}




// Fetch Notification


$query = mysqli_query($conn,


"SELECT * FROM notification

WHERE user_id='$user_id'

ORDER BY notification_id DESC"


);



?>



<!DOCTYPE html>

<html>

<head>

<title>Notifications</title>


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

🔔 Notifications

</h2>





<?php

if(mysqli_num_rows($query)==0){

echo '

<div class="alert alert-info">

No Notification Available

</div>

';

}

?>







<?php while($row=mysqli_fetch_assoc($query)){ ?>



<div class="card shadow-sm mb-3 border-0 notification-card

<?php echo ($row['status']=="Unread") ? "unread-notification" : ""; ?>

">



<div class="card-body d-flex justify-content-between align-items-center">



<div>



<h5>



<?php


if($row['notification_type']=="Budget Alert"){


echo "⚠️ Budget Alert";


}

elseif($row['notification_type']=="Balance Alert"){


echo "💰 Balance Alert";


}

else{


echo "🔔 Notification";


}



?>



</h5>





<p class="mb-1">


<?php echo nl2br($row['message']); ?>


</p>





<small class="text-muted">


<?php echo $row['created_at']; ?>


</small>





</div>








<div>



<?php if($row['status']=="Unread"){ ?>



<a href="notification.php?read=<?php echo $row['notification_id']; ?>"


class="btn btn-success btn-sm mb-1">


✓ Mark Read


</a>



<?php } ?>







<a href="notification.php?delete=<?php echo $row['notification_id']; ?>"


class="btn btn-danger btn-sm"


onclick="return confirm('Delete notification?')">


Delete


</a>





</div>





</div>


</div>




<?php } ?>






</div>


</div>


</div>




<script src="js/darkmode.js"></script>


</body>


</html>