<?php


function checkBudgetNotification(
    $conn,
    $user_id,
    $category_id,
    $amount,
    $expense_date
){

    $user_id = (int) $user_id;
    $category_id = (int) $category_id;
    $amount = (float) $amount;

    // Use today's date if no valid date was supplied
    $date_object = DateTime::createFromFormat("Y-m-d", $expense_date);

    if(!$date_object || $date_object->format("Y-m-d") !== $expense_date){

        $expense_date = date("Y-m-d");
        $date_object = new DateTime($expense_date);

    }

    $expense_month = $date_object->format("F");
    $expense_year = $date_object->format("Y");


    /*
    |--------------------------------------------------------------------------
    | Get Category Name
    |--------------------------------------------------------------------------
    */

    $category_stmt = mysqli_prepare(
        $conn,
        "SELECT category_name
         FROM category
         WHERE category_id = ?
         LIMIT 1"
    );

    mysqli_stmt_bind_param(
        $category_stmt,
        "i",
        $category_id
    );

    mysqli_stmt_execute($category_stmt);

    $category_result = mysqli_stmt_get_result($category_stmt);
    $category_data = mysqli_fetch_assoc($category_result);

    mysqli_stmt_close($category_stmt);


    if(!$category_data){

        return;

    }


    $category_name = $category_data['category_name'];


    /*
    |--------------------------------------------------------------------------
    | Find Budget For Same Category, Month And Year
    |--------------------------------------------------------------------------
    */

    $budget_stmt = mysqli_prepare(
        $conn,
        "SELECT limit_amount
         FROM budget
         WHERE user_id = ?
         AND category_id = ?
         AND month = ?
         AND year = ?
         ORDER BY budget_id DESC
         LIMIT 1"
    );

    mysqli_stmt_bind_param(
        $budget_stmt,
        "iiss",
        $user_id,
        $category_id,
        $expense_month,
        $expense_year
    );

    mysqli_stmt_execute($budget_stmt);

    $budget_result = mysqli_stmt_get_result($budget_stmt);
    $budget_data = mysqli_fetch_assoc($budget_result);

    mysqli_stmt_close($budget_stmt);


    /*
    |--------------------------------------------------------------------------
    | Total Category Expense For Same Month And Year
    |--------------------------------------------------------------------------
    */

    $expense_stmt = mysqli_prepare(
        $conn,
        "SELECT COALESCE(SUM(amount), 0) AS total_expense
         FROM expense
         WHERE user_id = ?
         AND category_id = ?
         AND MONTH(date) = ?
         AND YEAR(date) = ?"
    );

    $month_number = (int) $date_object->format("m");
    $year_number = (int) $expense_year;

    mysqli_stmt_bind_param(
        $expense_stmt,
        "iiii",
        $user_id,
        $category_id,
        $month_number,
        $year_number
    );

    mysqli_stmt_execute($expense_stmt);

    $expense_result = mysqli_stmt_get_result($expense_stmt);
    $expense_data = mysqli_fetch_assoc($expense_result);

    mysqli_stmt_close($expense_stmt);


    $total_expense = (float) ($expense_data['total_expense'] ?? 0);


    /*
    |--------------------------------------------------------------------------
    | No Budget Set Notification
    |--------------------------------------------------------------------------
    */

    if(!$budget_data){

        $alert_level = 0;

        $message = "⚠️ No Budget Set.

You added a {$category_name} expense without setting a budget.

Expense Added: $" . number_format($amount, 2) . "

Total {$category_name} Expense for {$expense_month} {$expense_year}: $" . number_format($total_expense, 2) . "

Please set a {$category_name} budget to track your spending.";


        // Only one No Budget notification per category, month and year
        $check_stmt = mysqli_prepare(
            $conn,
            "SELECT notification_id
             FROM notification
             WHERE user_id = ?
             AND notification_type = 'Budget Alert'
             AND alert_level = 0
             AND message LIKE ?
             AND message LIKE ?
             LIMIT 1"
        );

        $category_pattern = "%" . $category_name . "%";
        $month_year_pattern = "%" . $expense_month . " " . $expense_year . "%";

        mysqli_stmt_bind_param(
            $check_stmt,
            "iss",
            $user_id,
            $category_pattern,
            $month_year_pattern
        );

        mysqli_stmt_execute($check_stmt);

        $check_result = mysqli_stmt_get_result($check_stmt);
        $already_exists = mysqli_num_rows($check_result) > 0;

        mysqli_stmt_close($check_stmt);


        if(!$already_exists){

            $insert_stmt = mysqli_prepare(
                $conn,
                "INSERT INTO notification
                (user_id, message, notification_type, status, alert_level)
                VALUES (?, ?, 'Budget Alert', 'Unread', 0)"
            );

            mysqli_stmt_bind_param(
                $insert_stmt,
                "is",
                $user_id,
                $message
            );

            mysqli_stmt_execute($insert_stmt);
            mysqli_stmt_close($insert_stmt);

        }

        return;

    }


    /*
    |--------------------------------------------------------------------------
    | Budget Percentage Alert
    |--------------------------------------------------------------------------
    */

    $budget_limit = (float) $budget_data['limit_amount'];

    if($budget_limit <= 0){

        return;

    }


    $percentage = round(
        ($total_expense / $budget_limit) * 100
    );

    $alert_level = 0;
    $message = "";


    if($percentage >= 100){

        $over_amount = $total_expense - $budget_limit;
        $alert_level = 100;

        if($over_amount > 0){

            $message = "🚨 {$category_name} Budget Exceeded.

Month: {$expense_month} {$expense_year}

Budget: $" . number_format($budget_limit, 2) . "

Spent: $" . number_format($total_expense, 2) . "

Over Budget: -$" . number_format($over_amount, 2);

        }
        else{

            $message = "🚨 {$category_name} Budget Fully Used.

Month: {$expense_month} {$expense_year}

Budget: $" . number_format($budget_limit, 2) . "

Spent: $" . number_format($total_expense, 2) . "

Remaining: $0.00";

        }

    }
    elseif($percentage >= 50){

        $alert_level = $percentage;
        $remaining = $budget_limit - $total_expense;

        $message = "⚠️ {$category_name} Budget Alert.

Month: {$expense_month} {$expense_year}

You have used {$percentage}% of your budget.

Budget: $" . number_format($budget_limit, 2) . "

Spent: $" . number_format($total_expense, 2) . "

Remaining: $" . number_format($remaining, 2);

    }


    if($message === ""){

        return;

    }


    // Prevent duplicate alert for same category, month, year and alert level
    $check_stmt = mysqli_prepare(
        $conn,
        "SELECT notification_id
         FROM notification
         WHERE user_id = ?
         AND notification_type = 'Budget Alert'
         AND alert_level = ?
         AND message LIKE ?
         AND message LIKE ?
         LIMIT 1"
    );

    $category_pattern = "%" . $category_name . "%";
    $month_year_pattern = "%" . $expense_month . " " . $expense_year . "%";

    mysqli_stmt_bind_param(
        $check_stmt,
        "iiss",
        $user_id,
        $alert_level,
        $category_pattern,
        $month_year_pattern
    );

    mysqli_stmt_execute($check_stmt);

    $check_result = mysqli_stmt_get_result($check_stmt);
    $already_exists = mysqli_num_rows($check_result) > 0;

    mysqli_stmt_close($check_stmt);


    if(!$already_exists){

        $insert_stmt = mysqli_prepare(
            $conn,
            "INSERT INTO notification
            (user_id, message, notification_type, status, alert_level)
            VALUES (?, ?, 'Budget Alert', 'Unread', ?)"
        );

        mysqli_stmt_bind_param(
            $insert_stmt,
            "isi",
            $user_id,
            $message,
            $alert_level
        );

        mysqli_stmt_execute($insert_stmt);
        mysqli_stmt_close($insert_stmt);

    }

}



function checkBalanceNotification($conn, $user_id)
{

    $user_id = (int) $user_id;


    /*
    |--------------------------------------------------------------------------
    | Total Income
    |--------------------------------------------------------------------------
    */

    $income_stmt = mysqli_prepare(
        $conn,
        "SELECT COALESCE(SUM(amount), 0) AS total_income
         FROM income
         WHERE user_id = ?"
    );

    mysqli_stmt_bind_param(
        $income_stmt,
        "i",
        $user_id
    );

    mysqli_stmt_execute($income_stmt);

    $income_result = mysqli_stmt_get_result($income_stmt);
    $income_data = mysqli_fetch_assoc($income_result);

    mysqli_stmt_close($income_stmt);


    $total_income = (float) ($income_data['total_income'] ?? 0);


    /*
    |--------------------------------------------------------------------------
    | Total Expense
    |--------------------------------------------------------------------------
    */

    $expense_stmt = mysqli_prepare(
        $conn,
        "SELECT COALESCE(SUM(amount), 0) AS total_expense
         FROM expense
         WHERE user_id = ?"
    );

    mysqli_stmt_bind_param(
        $expense_stmt,
        "i",
        $user_id
    );

    mysqli_stmt_execute($expense_stmt);

    $expense_result = mysqli_stmt_get_result($expense_stmt);
    $expense_data = mysqli_fetch_assoc($expense_result);

    mysqli_stmt_close($expense_stmt);


    $total_expense = (float) ($expense_data['total_expense'] ?? 0);


    if($total_income <= 0){

        return;

    }


    $remaining = $total_income - $total_expense;

    $used_percentage = round(
        ($total_expense / $total_income) * 100
    );


    $alert_level = 0;
    $message = "";


    if($remaining <= 0){

        $alert_level = 100;
        $extra = abs($remaining);

        if($remaining < 0){

            $message = "🚨 Income Exceeded.

Total Income: $" . number_format($total_income, 2) . "

Total Expense: $" . number_format($total_expense, 2) . "

Extra Spending: -$" . number_format($extra, 2);

        }
        else{

            $message = "🚨 Income Fully Used.

Total Income: $" . number_format($total_income, 2) . "

Total Expense: $" . number_format($total_expense, 2) . "

Remaining Balance: $0.00";

        }

    }
    elseif($used_percentage >= 90){

        $alert_level = 90;

        $message = "🚨 Balance Critical.

You have used {$used_percentage}% of your total income.

Total Income: $" . number_format($total_income, 2) . "

Total Expense: $" . number_format($total_expense, 2) . "

Remaining Balance: $" . number_format($remaining, 2);

    }
    elseif($used_percentage >= 70){

        $alert_level = 70;

        $message = "⚠️ Balance Warning.

You have used {$used_percentage}% of your total income.

Remaining Balance: $" . number_format($remaining, 2);

    }
    elseif($used_percentage >= 50){

        $alert_level = 50;

        $message = "⚠️ Balance Alert.

You have used {$used_percentage}% of your total income.

Remaining Balance: $" . number_format($remaining, 2);

    }


    if($message === ""){

        return;

    }


    $check_stmt = mysqli_prepare(
        $conn,
        "SELECT notification_id
         FROM notification
         WHERE user_id = ?
         AND notification_type = 'Balance Alert'
         AND alert_level = ?
         LIMIT 1"
    );

    mysqli_stmt_bind_param(
        $check_stmt,
        "ii",
        $user_id,
        $alert_level
    );

    mysqli_stmt_execute($check_stmt);

    $check_result = mysqli_stmt_get_result($check_stmt);
    $already_exists = mysqli_num_rows($check_result) > 0;

    mysqli_stmt_close($check_stmt);


    if(!$already_exists){

        $insert_stmt = mysqli_prepare(
            $conn,
            "INSERT INTO notification
            (user_id, message, notification_type, status, alert_level)
            VALUES (?, ?, 'Balance Alert', 'Unread', ?)"
        );

        mysqli_stmt_bind_param(
            $insert_stmt,
            "isi",
            $user_id,
            $message,
            $alert_level
        );

        mysqli_stmt_execute($insert_stmt);
        mysqli_stmt_close($insert_stmt);

    }

}


?>