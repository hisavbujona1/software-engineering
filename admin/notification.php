<?php

session_start();

include "../config/database.php";


if(!isset($_SESSION['admin_id'])){

    header("Location: ../login.php");
    exit();

}


$success = "";
$error = "";
$selected_recipient = $_POST['recipient'] ?? "all";
$message_value = trim($_POST['message'] ?? "");



// Fetch Users For Recipient Dropdown

$user_list_query = mysqli_query(
    $conn,
    "SELECT user_id, name, email
     FROM users
     ORDER BY name ASC, email ASC"
);

$users_for_dropdown = [];

while($user_row = mysqli_fetch_assoc($user_list_query)){

    $users_for_dropdown[] = $user_row;

}



// Send Notification

if(isset($_POST['send_notification'])){

    $recipient = $_POST['recipient'] ?? "all";
    $message = trim($_POST['message'] ?? "");


    if($message === ""){

        $error = "Please write a notification message.";

    }
    elseif($recipient !== "all" && !ctype_digit((string)$recipient)){

        $error = "Please select a valid recipient.";

    }
    else{

        $insert_stmt = mysqli_prepare(
            $conn,
            "INSERT INTO notification
            (user_id, message, notification_type, status, alert_level)
            VALUES (?, ?, 'Admin Notification', 'Unread', 0)"
        );


        if($recipient === "all"){

            $users_query = mysqli_query(
                $conn,
                "SELECT user_id FROM users"
            );

            $sent_count = 0;


            while($user = mysqli_fetch_assoc($users_query)){

                $user_id = (int) $user['user_id'];

                mysqli_stmt_bind_param(
                    $insert_stmt,
                    "is",
                    $user_id,
                    $message
                );


                if(mysqli_stmt_execute($insert_stmt)){

                    $sent_count++;

                }

            }


            if($sent_count > 0){

                $success = "Notification sent successfully to {$sent_count} user(s).";

                $message_value = "";
                $selected_recipient = "all";

            }
            else{

                $error = "No users were found, so the notification was not sent.";

            }

        }
        else{

            $selected_user_id = (int) $recipient;


            // Confirm selected user exists

            $user_check_stmt = mysqli_prepare(
                $conn,
                "SELECT name, email
                 FROM users
                 WHERE user_id = ?
                 LIMIT 1"
            );

            mysqli_stmt_bind_param(
                $user_check_stmt,
                "i",
                $selected_user_id
            );

            mysqli_stmt_execute($user_check_stmt);

            $user_check_result = mysqli_stmt_get_result($user_check_stmt);
            $selected_user = mysqli_fetch_assoc($user_check_result);

            mysqli_stmt_close($user_check_stmt);


            if(!$selected_user){

                $error = "The selected user does not exist.";

            }
            else{

                mysqli_stmt_bind_param(
                    $insert_stmt,
                    "is",
                    $selected_user_id,
                    $message
                );


                if(mysqli_stmt_execute($insert_stmt)){

                    $success = "Notification sent successfully to "
                        . $selected_user['name']
                        . " (" . $selected_user['email'] . ").";

                    $message_value = "";
                    $selected_recipient = "all";

                }
                else{

                    $error = "The notification could not be sent.";

                }

            }

        }


        mysqli_stmt_close($insert_stmt);

    }

}



// Actual Notification Count

$count_query = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM notification"
);

$count_data = mysqli_fetch_assoc($count_query);

$total_notifications = (int) ($count_data['total'] ?? 0);



// Previous Notifications

$notification_query = mysqli_query(
    $conn,
    "
    SELECT
        notification.notification_id,
        notification.user_id,
        users.name AS user_name,
        users.email AS user_email,
        notification.message,
        notification.notification_type,
        notification.status,
        notification.created_at

    FROM notification

    LEFT JOIN users
    ON notification.user_id = users.user_id

    ORDER BY notification.notification_id DESC
    "
);

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Admin Notification</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body{
    background:#f8fafc;
}

.sidebar{
    background:#111827;
    min-height:100vh;
    padding:25px;
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

.message-cell{
    min-width:300px;
    white-space:pre-line;
}

.recipient-help{
    font-size:13px;
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

<h4 class="text-white">
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

<a href="notification.php" class="active">
🔔 Notification
</a>

<a href="admin_logout.php">
🚪 Logout
</a>

</div>


<!-- Main -->

<div class="col-md-9 col-lg-10 p-4">

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3">

<div>

<h2 class="mb-1">
🔔 Send Notification
</h2>

<p class="text-muted mb-0">
Send a notification to everyone or to one selected user.
</p>

</div>

<div class="alert alert-info mb-0">

Total Notifications:
<strong><?php echo $total_notifications; ?></strong>

</div>

</div>


<?php if($success !== ""){ ?>

<div class="alert alert-success mt-4">
<?php echo htmlspecialchars($success); ?>
</div>

<?php } ?>


<?php if($error !== ""){ ?>

<div class="alert alert-danger mt-4">
<?php echo htmlspecialchars($error); ?>
</div>

<?php } ?>


<div class="card mt-4">

<div class="card-body p-4">

<form method="POST">

<div class="mb-3">

<label for="recipient" class="form-label">
Send To
</label>

<select
name="recipient"
id="recipient"
class="form-select"
required>

<option
value="all"
<?php echo ($selected_recipient === "all") ? "selected" : ""; ?>>

🌐 All Users

</option>


<?php foreach($users_for_dropdown as $user){ ?>

<option
value="<?php echo $user['user_id']; ?>"
<?php echo ((string)$selected_recipient === (string)$user['user_id']) ? "selected" : ""; ?>>

👤 <?php echo htmlspecialchars($user['name']); ?>
— <?php echo htmlspecialchars($user['email']); ?>

</option>

<?php } ?>

</select>

<div class="form-text recipient-help" id="recipientHelp">
Select “All Users” to send the message to everyone, or choose one user.
</div>

</div>


<div class="mb-3">

<label for="message" class="form-label">
Notification Message
</label>

<textarea
name="message"
id="message"
class="form-control"
rows="4"
placeholder="Write notification message..."
required><?php echo htmlspecialchars($message_value); ?></textarea>

</div>


<button
type="submit"
name="send_notification"
id="sendButton"
class="btn btn-primary">

📤 Send Notification

</button>

</form>

</div>

</div>


<div class="card mt-4">

<div class="card-body">

<h4>
Previous Notifications (<?php echo $total_notifications; ?>)
</h4>

<div class="table-responsive">

<table class="table table-hover align-middle">

<thead class="table-dark">

<tr>

<th>ID</th>
<th>User</th>
<th>Type</th>
<th>Message</th>
<th>Status</th>
<th>Created</th>

</tr>

</thead>

<tbody>

<?php if($total_notifications === 0){ ?>

<tr>

<td colspan="6" class="text-center text-muted py-4">
No notifications found.
</td>

</tr>

<?php } ?>


<?php while($row = mysqli_fetch_assoc($notification_query)){ ?>

<tr>

<td>
<?php echo $row['notification_id']; ?>
</td>

<td>

<?php if(!empty($row['user_name'])){ ?>

<strong>
<?php echo htmlspecialchars($row['user_name']); ?>
</strong>

<br>

<small class="text-muted">
ID: <?php echo $row['user_id']; ?> |
<?php echo htmlspecialchars($row['user_email']); ?>
</small>

<?php } else{ ?>

User ID: <?php echo $row['user_id']; ?>

<?php } ?>

</td>

<td>
<?php echo htmlspecialchars($row['notification_type'] ?? "Notification"); ?>
</td>

<td class="message-cell">
<?php echo htmlspecialchars($row['message']); ?>
</td>

<td>
<?php echo htmlspecialchars($row['status']); ?>
</td>

<td>
<?php echo htmlspecialchars($row['created_at'] ?? ""); ?>
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


<script>

const recipientSelect = document.getElementById("recipient");
const recipientHelp = document.getElementById("recipientHelp");
const sendButton = document.getElementById("sendButton");


function updateRecipientUI(){

    const isAllUsers = recipientSelect.value === "all";

    recipientHelp.textContent = isAllUsers
        ? "This message will be sent separately to every registered user."
        : "This message will be sent only to the selected user.";

    sendButton.textContent = isAllUsers
        ? "📤 Send To All Users"
        : "📤 Send To Selected User";

}


recipientSelect.addEventListener(
    "change",
    updateRecipientUI
);


updateRecipientUI();

</script>

</body>

</html>