<?php
// Include config if needed
require_once '../config.php'; // Adjust this path based on where your config.php is located

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

  // Access form-data from $_POST
  if (!isset($_POST['email'])) {
    echo json_encode(["message" => "Invalid input"]);
    return;
  }

  $email = $_POST['email'];

  // Insert into database
  $query = "INSERT INTO newsletter (email, createdDt) VALUES (:email, now())";
  $stmt = $pdo->prepare($query);

  $stmt->bindParam(':email', $email);

  if ($stmt->execute()) {
    echo json_encode(["message" => "Email added successfully"]);
  } else {
    echo json_encode(["message" => "Failed to add email"]);
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