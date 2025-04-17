<?php
// Prevent direct file access
if (!defined('ACCESSED_THROUGH_SCRIPT')) {
    header("HTTP/1.0 403 Forbidden");
    exit;
}

// Database file path
$dbFile = 'decar_database.json';

// Initialize database if it doesn't exist
function initializeDatabase() {
    global $dbFile;
    
    if (!file_exists($dbFile)) {
        $initialData = [
            'submissions' => [],
            'leads' => []
        ];
        
        file_put_contents($dbFile, json_encode($initialData, JSON_PRETTY_PRINT));
        chmod($dbFile, 0600); // Set secure permissions
    }
}

// Get all submissions and leads
function getAllData() {
    global $dbFile;
    initializeDatabase();
    
    $jsonData = file_get_contents($dbFile);
    return json_decode($jsonData, true);
}

// Save submission and lead data
function saveSubmission($submission, $leadEntry) {
    global $dbFile;
    
    $data = getAllData();
    
    // Add submission to submissions array
    $data['submissions'][] = $submission;
    
    // Add lead to leads array
    $data['leads'][] = $leadEntry;
    
    // Save back to file
    file_put_contents($dbFile, json_encode($data, JSON_PRETTY_PRINT));
    
    return true;
}

// Get all submissions
function getSubmissions() {
    $data = getAllData();
    return $data['submissions'];
}

// Get all leads
function getLeads() {
    $data = getAllData();
    return $data['leads'];
}

// Update a lead's status and notes
function updateLead($leadId, $newStatus, $note) {
    global $dbFile;
    
    $data = getAllData();
    $leadFound = false;
    
    // Find and update the lead
    foreach ($data['leads'] as &$lead) {
        if ($lead['id'] == $leadId) {
            $lead['status'] = $newStatus;
            $lead['ultimaAtualizacao'] = date('c'); // ISO 8601 date format
            
            if (!isset($lead['notas'])) {
                $lead['notas'] = [];
            }
            
            if (!empty($note)) {
                $lead['notas'][] = [
                    'data' => date('c'),
                    'status' => $newStatus,
                    'texto' => $note
                ];
            }
            
            $leadFound = true;
            break;
        }
    }
    
    if ($leadFound) {
        file_put_contents($dbFile, json_encode($data, JSON_PRETTY_PRINT));
        return true;
    }
    
    return false;
}

// Delete a submission or lead
function deleteEntry($id, $type = 'submission') {
    global $dbFile;
    
    $data = getAllData();
    $entryFound = false;
    
    if ($type === 'lead') {
        // Find and remove the lead
        foreach ($data['leads'] as $key => $lead) {
            if ($lead['id'] == $id) {
                unset($data['leads'][$key]);
                $data['leads'] = array_values($data['leads']); // Re-index array
                $entryFound = true;
                break;
            }
        }
    } else {
        // Find and remove the submission
        foreach ($data['submissions'] as $key => $submission) {
            if ($submission['id'] == $id) {
                unset($data['submissions'][$key]);
                $data['submissions'] = array_values($data['submissions']); // Re-index array
                $entryFound = true;
                break;
            }
        }
    }
    
    if ($entryFound) {
        file_put_contents($dbFile, json_encode($data, JSON_PRETTY_PRINT));
        return true;
    }
    
    return false;
}

// Initialize database when script loads
initializeDatabase();
?> 