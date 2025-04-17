<?php
// Set no caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Allow cross-origin requests
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Set content type to JSON
header("Content-Type: application/json");

// Define access constant
define('ACCESSED_THROUGH_SCRIPT', true);

// Include database functions
require_once('db.php');

// Get request method and endpoint
$method = $_SERVER['REQUEST_METHOD'];
$endpoint = isset($_GET['endpoint']) ? $_GET['endpoint'] : '';

// Request handling
switch ($method) {
    case 'GET':
        handleGetRequest($endpoint);
        break;
    case 'POST':
        handlePostRequest($endpoint);
        break;
    case 'PUT':
        handlePutRequest($endpoint);
        break;
    case 'DELETE':
        handleDeleteRequest($endpoint);
        break;
    default:
        sendResponse(405, ['error' => 'Method not allowed']);
        break;
}

// Handle GET requests
function handleGetRequest($endpoint) {
    switch ($endpoint) {
        case 'submissions':
            $submissions = getSubmissions();
            sendResponse(200, $submissions);
            break;
        case 'leads':
            $leads = getLeads();
            sendResponse(200, $leads);
            break;
        case 'all':
            $data = getAllData();
            sendResponse(200, $data);
            break;
        default:
            sendResponse(404, ['error' => 'Endpoint not found']);
            break;
    }
}

// Handle POST requests
function handlePostRequest($endpoint) {
    // Get request body
    $inputJSON = file_get_contents('php://input');
    $input = json_decode($inputJSON, true);
    
    if (!$input) {
        sendResponse(400, ['error' => 'Invalid JSON data']);
        return;
    }
    
    switch ($endpoint) {
        case 'submission':
            if (!isset($input['submission']) || !isset($input['lead'])) {
                sendResponse(400, ['error' => 'Missing submission or lead data']);
                return;
            }
            
            $result = saveSubmission($input['submission'], $input['lead']);
            if ($result) {
                sendResponse(201, ['message' => 'Submission saved successfully']);
            } else {
                sendResponse(500, ['error' => 'Failed to save submission']);
            }
            break;
        default:
            sendResponse(404, ['error' => 'Endpoint not found']);
            break;
    }
}

// Handle PUT requests
function handlePutRequest($endpoint) {
    // Get request body
    $inputJSON = file_get_contents('php://input');
    $input = json_decode($inputJSON, true);
    
    if (!$input) {
        sendResponse(400, ['error' => 'Invalid JSON data']);
        return;
    }
    
    switch ($endpoint) {
        case 'lead':
            if (!isset($input['id']) || !isset($input['status'])) {
                sendResponse(400, ['error' => 'Missing lead ID or status']);
                return;
            }
            
            $note = isset($input['note']) ? $input['note'] : '';
            $result = updateLead($input['id'], $input['status'], $note);
            
            if ($result) {
                sendResponse(200, ['message' => 'Lead updated successfully']);
            } else {
                sendResponse(404, ['error' => 'Lead not found']);
            }
            break;
        default:
            sendResponse(404, ['error' => 'Endpoint not found']);
            break;
    }
}

// Handle DELETE requests
function handleDeleteRequest($endpoint) {
    if (!isset($_GET['id'])) {
        sendResponse(400, ['error' => 'Missing ID parameter']);
        return;
    }
    
    $id = $_GET['id'];
    $type = isset($_GET['type']) ? $_GET['type'] : 'submission';
    
    switch ($endpoint) {
        case 'entry':
            $result = deleteEntry($id, $type);
            
            if ($result) {
                sendResponse(200, ['message' => 'Entry deleted successfully']);
            } else {
                sendResponse(404, ['error' => 'Entry not found']);
            }
            break;
        default:
            sendResponse(404, ['error' => 'Endpoint not found']);
            break;
    }
}

// Send JSON response
function sendResponse($statusCode, $data) {
    http_response_code($statusCode);
    echo json_encode($data);
    exit();
}
?> 