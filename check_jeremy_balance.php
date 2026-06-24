<?php
$conn = new mysqli('localhost', 'root', 'admin', 'primehrismagdalena');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$query = "SELECT lb.id, lb.employee_id, lb.leave_code, lb.year, lb.available_credits, lb.total_credits, 
          e.employee_id as emp_num, e.first_name, e.last_name 
          FROM leave_balances lb 
          JOIN employees e ON lb.employee_id = e.id 
          WHERE e.employee_id = '2024001' AND lb.leave_code = 'SL' 
          ORDER BY lb.year DESC LIMIT 1";

$result = $conn->query($query);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "Employee: " . $row['first_name'] . " " . $row['last_name'] . " (" . $row['emp_num'] . ")\n";
        echo "Leave Code: " . $row['leave_code'] . "\n";
        echo "Year: " . $row['year'] . "\n";
        echo "Available Credits: " . $row['available_credits'] . "\n";
        echo "Total Credits: " . $row['total_credits'] . "\n";
    }
} else {
    echo "No records found\n";
}

$conn->close();
?>
