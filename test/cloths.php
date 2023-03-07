<?php


$servername = "localhost";
$con = mysqli_connect("localhost","root","","clothes_shop");

if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

/*$sql = "CREATE DATABASE clothes_shop"*/;
$sql = "UPDATE INTO TABLE products (
  -- product_id INT AUTO_INCREMENT PRIMARY KEY,
  product_name VARCHAR(100) NOT NULL,
  product_description TEXT NOT NULL,
  product_price DECIMAL(10, 2) NOT NULL,
  product_quantity INT NOT NULL,
  product_image VARCHAR(255) NOT NULL,
  category VARCHAR(100) NOT NULL,
  brand VARCHAR(100) NOT NULL,
  size VARCHAR(100) NOT NULL,
  color VARCHAR(100) NOT NULL
);
";

if (mysqli_query($con, $sql)) {
    echo "Database created successfully";
} else {
    echo "Error creating database: " . mysqli_error($con);
}



?>