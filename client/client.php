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
// clientcontact.php - REST API
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
  $query = "SELECT * FROM clientcontact";
  $stmt = $pdo->prepare($query);
  $stmt->execute();
  $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
  echo json_encode($items);
}

// Function to fetch a single item by ID
function getItem($id)
{
  global $pdo;
  $query = "SELECT * FROM clientcontact WHERE id = :id";
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
  if (!isset($data['fname'], $data['lname'], $data['email'], $data['company'], $data['mobile'], $data['description'])) {
    echo json_encode(["message" => "Invalid input"]);
    return;
  }

  // Insert into database
  $query = "INSERT INTO clientcontact (fname, lname, email, company, mobile, description, createdDt) VALUES (:fname, :lname, :email, :company, :mobile, :description, now())";
  $stmt = $pdo->prepare($query);

  $stmt->bindParam(':fname', $data['fname']);
  $stmt->bindParam(':lname', $data['lname']);
  $stmt->bindParam(':email', $data['email']);
  $stmt->bindParam(':company', $data['company']);
  $stmt->bindParam(':mobile', $data['mobile']);
  $stmt->bindParam(':description', $data['description']);

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
    $mail->addAddress('rajattecknify0110@gmail.com', 'Tecknify'); // Send email to admin-mail

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Client Contact Form Submission';
    $mail->Body    = '
      <h1>Client Contact Form Submission</h1>
      <p><strong>Full Name:</strong> ' . $data['fname'] ." ".$data['lname'] . '</p>
      <p><strong>Email:</strong> ' . $data['email'] . '</p>
      <p><strong>Company:</strong> ' . $data['company'] . '</p>
      <p><strong>Mobile:</strong> ' . $data['mobile'] . '</p>
      <p><strong>Description:</strong> ' . $data['description'] . '</p>';

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
  $query = "UPDATE clientcontact SET name = :name, description = :description WHERE id = :id";
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
  $query = "DELETE FROM clientcontact WHERE id = :id";
  $stmt = $pdo->prepare($query);
  $stmt->bindParam(':id', $id);
  if ($stmt->execute()) {
    echo json_encode(["message" => "Item deleted"]);
  } else {
    echo json_encode(["message" => "Failed to delete item"]);
  }
}