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
  $query = "SELECT * FROM carrier";
  $stmt = $pdo->prepare($query);
  $stmt->execute();
  $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
  echo json_encode($items);
}

// Function to fetch a single item by ID
function getItem($id)
{
  global $pdo;
  $query = "SELECT * FROM carrier WHERE id = :id";
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

// Function to add a new item and upload file
function addItem()
{
    global $pdo;

    // Get form data from the request
    $name = $_POST['name'];
    $email = $_POST['email'];
    $mobile = $_POST['mobile'];
    $position = $_POST['position'];

    // Check if a file is uploaded
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        // Validate file type
        $allowedMimeTypes = [
            'application/pdf', 
            'application/msword', 
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ];
        $fileMimeType = mime_content_type($_FILES['file']['tmp_name']);

        if (!in_array($fileMimeType, $allowedMimeTypes)) {
            echo json_encode(["message" => "Invalid file format. Only PDF, DOC, and DOCX are allowed."]);
            return;
        }

        // File size validation (optional, e.g., max 5MB)
        $maxFileSize = 5 * 1024 * 1024; // 5MB
        if ($_FILES['file']['size'] > $maxFileSize) {
            echo json_encode(["message" => "File size exceeds the limit of 5MB."]);
            return;
        }

        // Prepare SQL query without storing the file
        $query = "INSERT INTO carrier (name, email, mobile, position, createdDt) 
                  VALUES (:name, :email, :mobile, :position, now())";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':mobile', $mobile);
        $stmt->bindParam(':position', $position);

        if ($stmt->execute()) {
            echo json_encode(["message" => "Data saved successfully."]);

            // Send email notification with file attachment
            sendEmail([
                'name' => $name,
                'email' => $email,
                'mobile' => $mobile,
                'position' => $position,
                'file_tmp' => $_FILES['file']['tmp_name'], // Temporary file path
                'file_name' => $_FILES['file']['name'] // Original file name
            ]);
        } else {
            echo json_encode(["message" => "Failed to save data in the database."]);
        }
    } else {
        echo json_encode(["message" => "No file uploaded or upload error."]);
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
        $mail->Subject = 'Resume Submission of ' . $data['name'];
        $mail->Body    = '
            <h1>Resume Submission</h1>
            <p><strong>Full Name:</strong> ' . $data['name'] . '</p>
            <p><strong>Email:</strong> ' . $data['email'] . '</p>
            <p><strong>Mobile:</strong> ' . $data['mobile'] . '</p>
            <p><strong>Position:</strong> ' . $data['position'] . '</p>';

        // Attach the file directly from the temporary upload directory
        if (isset($data['file_tmp']) && file_exists($data['file_tmp'])) {
            $mail->addAttachment($data['file_tmp'], $data['file_name']); // Attach the uploaded file
        }

        $mail->send();
        echo json_encode(["message" => "Email sent successfully with attachment."]);
    } catch (Exception $e) {
        echo json_encode(["message" => "Email could not be sent. Mailer Error: {$mail->ErrorInfo}"]);
    }
}

// Function to update an existing item
function updateItem($id)
{
  global $pdo;
  $data = json_decode(file_get_contents("php://input"), true);
  $query = "UPDATE carrier SET name = :name, description = :description WHERE id = :id";
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
  $query = "DELETE FROM carrier WHERE id = :id";
  $stmt = $pdo->prepare($query);
  $stmt->bindParam(':id', $id);
  if ($stmt->execute()) {
    echo json_encode(["message" => "Item deleted"]);
  } else {
    echo json_encode(["message" => "Failed to delete item"]);
  }
}
