<?php

session_start();

require 'vendor/autoload.php';

include "config/database.php";


use Dompdf\Dompdf;


if(!isset($_SESSION['user_id'])){

    header("Location: login.php");
    exit();

}


$user_id = $_SESSION['user_id'];



// Receive Month Year

$month = $_GET['month'];

$year  = $_GET['year'];




// Income

$income_query = mysqli_query($conn,

"SELECT SUM(amount) AS total_income

FROM income

WHERE user_id='$user_id'

AND MONTH(date)='$month'

AND YEAR(date)='$year'

"

);


$income_data=mysqli_fetch_assoc($income_query);


$total_income=$income_data['total_income'] ?? 0;





// Expense

$expense_query=mysqli_query($conn,

"SELECT SUM(amount) AS total_expense

FROM expense

WHERE user_id='$user_id'

AND MONTH(date)='$month'

AND YEAR(date)='$year'

"

);


$expense_data=mysqli_fetch_assoc($expense_query);


$total_expense=$expense_data['total_expense'] ?? 0;



$balance=$total_income-$total_expense;





// Category Expense

$category_query=mysqli_query($conn,


"SELECT 

category.category_name,

SUM(expense.amount) AS total


FROM expense


JOIN category

ON expense.category_id=category.category_id


WHERE expense.user_id='$user_id'


AND MONTH(expense.date)='$month'

AND YEAR(expense.date)='$year'


GROUP BY category.category_name"

);





$html='';


$html .= '

<h2 style="text-align:center">

Smart Expense Tracker

</h2>


<h3>

Monthly Financial Report

</h3>


<hr>


<p>

<strong>Report Month:</strong>

'.$month.'/'.$year.'

</p>



<table width="100%" border="1" cellspacing="0" cellpadding="8">


<tr>

<th>Total Income</th>

<th>Total Expense</th>

<th>Balance</th>

</tr>


<tr>

<td>$'.$total_income.'</td>

<td>$'.$total_expense.'</td>

<td>$'.$balance.'</td>

</tr>


</table>



<br><br>


<h3>

Expense Category Summary

</h3>



<table width="100%" border="1" cellspacing="0" cellpadding="8">


<tr>

<th>Category</th>

<th>Amount</th>

</tr>

';



while($row=mysqli_fetch_assoc($category_query)){


$html .= '

<tr>

<td>'.$row['category_name'].'</td>

<td>$'.$row['total'].'</td>

</tr>

';


}



$html .= '

</table>

';





$dompdf = new Dompdf();


$dompdf->loadHtml($html);


$dompdf->setPaper('A4','portrait');


$dompdf->render();



$dompdf->stream(

"Expense_Report_".$month."_".$year.".pdf",

[
"Attachment"=>true
]

);


?>