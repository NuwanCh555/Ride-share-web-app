<?php
// register.php - This file handles receiving data from the HTML registration form and saving it to the database.

// We use 'require' to include the code from config.php. 
// This gives this script access to the $pdo variable, meaning we are now connected to the database.
require 'config.php';

// The header function tells the browser that this PHP script will send data back in JSON format.
// JSON is a standard way to send data between the server (PHP) and the frontend (JavaScript).
header('Content-Type: application/json');

// We use an 'if' statement to check if the incoming HTTP request method is 'POST'.
// HTML forms should use method="POST" when sending sensitive data like passwords.
// $_SERVER is a built-in "superglobal" array containing information about headers, paths, and script locations.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // $_POST is a superglobal array that automatically collects all data sent from an HTML form via the POST method.
    // The keys inside $_POST (like 'username') match the 'name' attribute of your HTML <input> tags.
    // The trim() function removes any extra whitespace (spaces, tabs) from the beginning and end of what the user typed.
    // The '??' is the null coalescing operator. It means "if $_POST['username'] doesn't exist, use an empty string ('') instead."
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    
    // We grab the password, but we DO NOT trim it. Passwords can intentionally contain spaces!
    $password = $_POST['password'] ?? '';
    $user_type = $_POST['user_type'] ?? 'passenger';

    // We check if any of the fields are empty. The empty() function checks if a variable is blank, null, or zero.
    if (empty($username) || empty($email) || empty($password)) {
        // If they are missing something, we use json_encode() to convert a PHP array into a JSON string.
        // We echo (print) this string back to the Javascript on the frontend.
        echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
        // The exit function stops the script from continuing to run. We don't want to save empty data!
        exit;
    }

    // filter_var() is a powerful built-in PHP function. We use it with the FILTER_VALIDATE_EMAIL flag
    // to check if the string typed into the email field is actually a properly formatted email address (like user@example.com).
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // If the email is invalid, we send an error message back.
        echo json_encode(['status' => 'error', 'message' => 'Invalid email format.']);
        exit;
    }

    // password_hash() is a built-in PHP function that scrambles the password using a strong cryptographic algorithm (like bcrypt).
    // You must NEVER store passwords in plain text in a database. If a hacker steals the database, they will know everyone's passwords.
    // PASSWORD_DEFAULT tells PHP to use the strongest hashing algorithm available on the server.
    // The result is a long, random-looking string (e.g., $2y$10$abcdefg...) that we will store in the database.
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    // We use a try-catch block to handle the database insertion step safely.
    try {
        // $pdo->prepare() prepares an SQL statement for execution. 
        // We write the SQL query, but instead of putting the variables directly into the string, we use question marks (?).
        // These question marks are called "placeholders". Using placeholders prevents "SQL Injection", a common hacking technique.
        // It guarantees that the database treats the input as raw data, not as malicious SQL commands.
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, user_type) VALUES (?, ?, ?, ?)");
        
        // $stmt->execute() takes the prepared statement and actually runs it on the database.
        // We pass it an array of the values we want to insert. 
        // The order of the array exactly matches the order of the question marks (?) in the query above.
        // Value 1 ($username) replaces the first ?, Value 2 ($email) replaces the second ?, etc.
        $stmt->execute([$username, $email, $passwordHash, $user_type]);
        
        // If the execute() function finishes without throwing any errors, it means the user was successfully saved in the database!
        // We send a JSON success message back to the frontend javascript so it knows to show a success popup.
        echo json_encode(['status' => 'success', 'message' => 'Registration successful! You can now login.']);
    } 
    // If something goes wrong during the execute() step, PDO throws an exception, and we catch it here.
    catch (\PDOException $e) {
        // $e->getCode() gets the specific error code from the database. 
        // In MySQL, the code '23000' means "Integrity constraint violation". 
        // Since we made our username and email columns 'UNIQUE' in the database, trying to insert a duplicate throws this specific error.
        if ($e->getCode() == 23000) { 
            // We tell the user that the username or email is already taken.
            echo json_encode(['status' => 'error', 'message' => 'Username or email already exists.']);
        } else {
            // For any other unexpected database error, we send the exact error message
            echo json_encode(['status' => 'error', 'message' => 'Registration failed. SQL Error: ' . $e->getMessage()]);
        }
    }
}
?>
