<?php
// Include config if needed
require_once '../config.php'; // Adjust this path based on where your config.php is located

// Include PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load the PHPMailer classes from the src folder
require_once '../phpmailer/src/Exception.php';
require_once '../phpmailer/src/PHPMailer.php';
require_once '../phpmailer/src/SMTP.php';
// contact.php - REST API
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// include '../config.php';

$requestMethod = $_SERVER["REQUEST_METHOD"];

switch ($requestMethod) {
  case 'GET':
    if (!empty($_GET["id"])) {
      $id = intval($_GET["id"]);
      getItem($id);
    } else {
      getItems();
    }
    break;

  case 'POST':
    addItem();
    break;

  case 'PUT':
    $id = intval($_GET["id"]);
    updateItem($id);
    break;

  case 'DELETE':
    $id = intval($_GET["id"]);
    deleteItem($id);
    break;

  default:
    header("HTTP/1.0 405 Method Not Allowed");
    break;
}

// Function to fetch all items
function getItems()
{
  global $pdo;
  $query = "SELECT * FROM contact";
  $stmt = $pdo->prepare($query);
  $stmt->execute();
  $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
  echo json_encode($items);
}

// Function to fetch a single item by ID
function getItem($id)
{
  global $pdo;
  $query = "SELECT * FROM contact WHERE id = :id";
  $stmt = $pdo->prepare($query);
  $stmt->bindParam(':id', $id);
  $stmt->execute();
  $item = $stmt->fetch(PDO::FETCH_ASSOC);
  if ($item) {
    echo json_encode($item);
  } else {
    echo json_encode(["message" => "Item not found"]);
  }
}

// Function to add a new item and send data to email
function addItem()
{
  global $pdo;
  $data = json_decode(file_get_contents("php://input"), true);

  // Input validation (check if required fields are set)
  if (!isset($data['fname'], $data['lname'], $data['email'], $data['mobile'], $data['services'], $data['schedule'], $data['message'])) {
    echo json_encode(["message" => "Invalid input"]);
    return;
  }

  // Insert into database
  $query = "INSERT INTO contact (fname, lname, email, mobile, services, schedule, message, createdDt, updatedDt) VALUES (:fname, :lname, :email, :mobile, :services, :schedule, :message, now(), now())";
  $stmt = $pdo->prepare($query);

  $stmt->bindParam(':fname', $data['fname']);
  $stmt->bindParam(':lname', $data['lname']);
  $stmt->bindParam(':email', $data['email']);
  $stmt->bindParam(':mobile', $data['mobile']);
  $stmt->bindParam(':services', $data['services']);
  $stmt->bindParam(':schedule', $data['schedule']);
  $stmt->bindParam(':message', $data['message']);

  if ($stmt->execute()) {
    // If the item is successfully created, send an email
    sendEmail($data);
    echo json_encode(["message" => "Item created and email sent"]);
  } else {
    echo json_encode(["message" => "Failed to create item"]);
  }
}

// Function to send email to rajattecknify0110@gmail.com
function sendEmail($data)
{
  $mail = new PHPMailer(true);
  
  try {
    // Server settings
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';  // Specify main SMTP server
    $mail->SMTPAuth   = true;
    $mail->Username   = 'rajat.web71@gmail.com'; // SMTP username
    $mail->Password   = 'ctwh vyny rrdh nwcu';   // SMTP password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Enable TLS encryption
    $mail->Port       = 587;

    // Recipients
    $mail->setFrom('rajat.web71@gmail.com', 'no-reply');
    $mail->addAddress('rajattecknify0110@gmail.com', 'Verify-ads'); // Send email to admin-mail

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'New Contact Form Submission';
    $mail->Body    = '
      <h1>Contact Form Submission</h1>
      <p><strong>Full Name:</strong> ' . $data['fname'] ." ".$data['lname'] . '</p>
      <p><strong>Email:</strong> ' . $data['email'] . '</p>
      <p><strong>Mobile:</strong> ' . $data['mobile'] . '</p>
      <p><strong>Services:</strong> ' . $data['services'] . '</p>
      <p><strong>Schedule:</strong> ' . $data['schedule'] . '</p>
      <p><strong>Message:</strong> ' . $data['message'] . '</p>';

    $mail->send();
  } catch (Exception $e) {
    echo json_encode(["message" => "Email could not be sent. Mailer Error: {$mail->ErrorInfo}"]);
  }
}

// Function to update an existing item
function updateItem($id)
{
  global $pdo;
  $data = json_decode(file_get_contents("php://input"), true);
  $query = "UPDATE contact SET name = :name, description = :description WHERE id = :id";
  $stmt = $pdo->prepare($query);
  $stmt->bindParam(':name', $data['name']);
  $stmt->bindParam(':description', $data['description']);
  $stmt->bindParam(':id', $id);
  if ($stmt->execute()) {
    echo json_encode(["message" => "Item updated"]);
  } else {
    echo json_encode(["message" => "Failed to update item"]);
  }
}

// Function to delete an item
function deleteItem($id)
{
  global $pdo;
  $query = "DELETE FROM contact WHERE id = :id";
  $stmt = $pdo->prepare($query);
  $stmt->bindParam(':id', $id);
  if ($stmt->execute()) {
    echo json_encode(["message" => "Item deleted"]);
  } else {
    echo json_encode(["message" => "Failed to delete item"]);
  }
}