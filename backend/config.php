<?php
// config.php - This file handles connecting your application to the MySQL Database.

// Define the hostname of your database server. 
// Since we are running this locally (e.g., using XAMPP/WAMP), we use 'localhost'.
$host = '127.0.0.1'; 

// Define the name of the database you created in phpMyAdmin.
$db   = 'rideshare_db'; 

// Define the username used to log into the database.
// In XAMPP, the default username is always 'root'.
$user = 'root';

// Define the password for the database.
// In XAMPP, the default password is an empty string (no password).
$pass = '';

// Define the character set. 'utf8mb4' is the standard for safely supporting all characters (including emojis).
$charset = 'utf8mb4';

// DSN stands for Data Source Name. It is a string that contains the information required to connect to the database.
// We are combining our variables above into a format that PDO understands: "mysql:host=...;dbname=...;charset=..."
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

// We create an array of options to configure how PDO behaves.
$options = [
    // ATTR_ERRMODE: This tells PDO to throw an "Exception" whenever there is a database error.
    // This makes it much easier to catch and debug errors in your code.
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    
    // ATTR_DEFAULT_FETCH_MODE: When we fetch data from the database, this tells PDO to return it as an associative array.
    // For example, instead of getting data like $row[0], you get it like $row['username']. It is much easier to read.
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    
    // ATTR_EMULATE_PREPARES: Setting this to 'false' forces PDO to use real prepared statements rather than emulating them.
    // This provides maximum security against SQL Injection attacks.
    PDO::ATTR_EMULATE_PREPARES   => false, 
];

// A try-catch block is used to "try" running a piece of code, and "catch" any errors so the whole website doesn't break.
try {
    // We create a new instance of the PDO object. This actually opens the connection to the database.
    // We pass in our DSN string, the username, the password, and our configuration options array.
    $pdo = new PDO($dsn, $user, $pass, $options);
} 
// If the connection fails, PDO throws a PDOException. We "catch" it here in the variable $e.
catch (\PDOException $e) {
    // We print out a message and the specific error message ($e->getMessage()) so we know why it failed.
    // WARNING: In a real production app, you should not show the real error to users, as it might expose database details.
    echo "Database connection failed: " . $e->getMessage();
    
    // The exit function stops the rest of the PHP script from running since there's no database connection.
    exit;
}
?>
