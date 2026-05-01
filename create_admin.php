<?php
require_once("classes/autoload.php");

$DB = new Database();

$admin = [
    'userid' => uniqid(),
    'username' => 'admin1',
    'first_name' => 'Admin',
    'last_name' => 'User',
    'email' => 'admin1@fk.com',
    'phone_number' => '0704735352',
    'password' => password_hash('password', PASSWORD_DEFAULT),
    'role' => 'admin',
    'date_created' => date("Y-m-d H:i:s") // Add this line
];

$query = "INSERT INTO users (username, userid, first_name, last_name, email, phone_number, password, role, date_created) 
          VALUES (:username, :userid, :first_name, :last_name, :email, :phone_number, :password, :role, :date_created)";

$result = $DB->write($query, $admin);

if ($result) {
    echo "✅ Admin user created successfully.";
} else {
    echo "❌ Failed to create admin user.";
}
