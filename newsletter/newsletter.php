<?php
// Include config if needed
require_once '../config.php'; // Adjust this path based on where your config.php is located
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
  $query = "SELECT * FROM newsletter";
  $stmt = $pdo->prepare($query);
  $stmt->execute();
  $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
  echo json_encode($items);
}

// Function to fetch a single item by ID
function getItem($id)
{
  global $pdo;
  $query = "SELECT * FROM newsletter WHERE id = :id";
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

  // Ensure the POST request contains the 'email'
  if (!isset($_POST['email']) || empty($_POST['email'])) {
    echo json_encode(["message" => "Invalid input: Email is required"]);
    return;
  }

  $email = $_POST['email'];

  // Basic email validation (optional)
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["message" => "Invalid input: Invalid email format"]);
    return;
  }

  // Insert into the database
  $query = "INSERT INTO newsletter (email, createdDt) VALUES (:email, now())";
  $stmt = $pdo->prepare($query);
  $stmt->bindParam(':email', $email);

  // Execute and check for errors
  try {
    if ($stmt->execute()) {
      // After inserting, send email using PHPMailer
      sendEmail($email);
      echo json_encode(["message" => "Email added and confirmation sent successfully"]);
    } else {
      echo json_encode(["message" => "Failed to add email"]);
    }
  } catch (Exception $e) {
    // Log any exception for debugging
    error_log($e->getMessage());
    echo json_encode(["message" => "Error occurred: " . $e->getMessage()]);
  }
}

function sendEmail($email)
{
  // Create a new PHPMailer instance
  $mail = new PHPMailer(true);

  try {
    // SMTP server settings
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';           // Set the SMTP server to send through
    $mail->SMTPAuth = true;                   // Enable SMTP authentication
    $mail->Username = 'rajat.web71@gmail.com'; // Your Gmail address
    $mail->Password = 'ctwh vyny rrdh nwcu';    // Your Gmail app password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // Enable TLS encryption
    $mail->Port = 465;                        // TCP port for SSL

    // Recipients
    $mail->setFrom('rajat.web71@gmail.com', 'Verify-ads');
    $mail->addAddress($email); // Add recipient

    // Email content
    $mail->isHTML(true);                                  // Set email format to HTML
    $mail->Subject = 'Newsletter Subscription Confirmation';
    // HTML Body
    $mail->Body = '
        <html>
        <body>
            <p>Dear Subscriber,</p>
            <p>Thank you for subscribing to our Newsletter! Stay tuned for our latest Blogs & Updates.</p>
            <br>
            <p>Regards,</p>
            <p><strong>Tecknify</strong></p>
        </body>
        </html>';
    $mail->AltBody = 'Thank you for subscribing to our newsletter!'; // Plain text version

    // Send the email
    $mail->send();
  } catch (Exception $e) {
    error_log('Mail could not be sent. PHPMailer Error: ' . $mail->ErrorInfo);
    echo json_encode(["message" => "Failed to send confirmation email."]);
  }
}

// Function to update an existing item
/* function updateItem($id)
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
} */

// Function to delete an item
/* function deleteItem($id)
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
} */