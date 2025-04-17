<?php
// Esta é uma implementação simples para salvar dados em um arquivo texto
// Configurações
$dataFile = 'data.json';
$logFile = 'debug_log.txt';

// Funções de log para diagnóstico
function logMessage($message) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[{$timestamp}] {$message}\n";
    file_put_contents($logFile, $logEntry, FILE_APPEND);
}

// Log no início da execução
logMessage("Script started. REQUEST_METHOD: {$_SERVER['REQUEST_METHOD']}");
logMessage("Current directory: " . getcwd());
logMessage("PHP version: " . phpversion());

// Verificar se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obter dados enviados
    $jsonData = file_get_contents('php://input');
    
    // Log dos dados recebidos (apenas um resumo, não todo o conteúdo por questões de privacidade)
    $dataSize = strlen($jsonData);
    logMessage("Received POST data. Size: {$dataSize} bytes");
    
    // Verificar se os dados são JSON válido
    $data = json_decode($jsonData, true);
    if ($data === null) {
        // Responder com erro caso os dados não sejam válidos
        logMessage("ERROR: Invalid JSON data received");
        header('Content-Type: application/json');
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
        exit;
    }
    
    // Log de resumo dos dados recebidos
    $submissionsCount = isset($data['submissions']) ? count($data['submissions']) : 0;
    $leadsCount = isset($data['leads']) ? count($data['leads']) : 0;
    logMessage("Valid JSON parsed. Submissions: {$submissionsCount}, Leads: {$leadsCount}");
    
    // Verificar se diretório tem permissão de escrita
    if (!is_writable(dirname($dataFile))) {
        logMessage("ERROR: Directory not writable: " . dirname($dataFile));
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Diretório não tem permissão de escrita']);
        exit;
    }
    
    // Se o arquivo existe, verifica se é gravável
    if (file_exists($dataFile) && !is_writable($dataFile)) {
        logMessage("ERROR: File exists but is not writable: {$dataFile}");
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Arquivo existe mas não tem permissão de escrita']);
        exit;
    }
    
    // Salvar dados no arquivo
    $result = file_put_contents($dataFile, $jsonData);
    
    // Verificar se a gravação foi bem-sucedida
    if ($result === false) {
        logMessage("ERROR: Failed to write to file: {$dataFile}");
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erro ao salvar dados']);
        exit;
    }
    
    // Log do sucesso da operação
    logMessage("SUCCESS: Data written to file. Bytes written: {$result}");
    
    // Responder sucesso
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'bytesWritten' => $result]);
    exit;
} 
// Se não for POST e for GET, devolver o conteúdo do arquivo
else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Verificar se o arquivo existe
    if (!file_exists($dataFile)) {
        // Criar arquivo vazio com estrutura básica
        $initialData = ['submissions' => [], 'leads' => []];
        $jsonContent = json_encode($initialData);
        $writeResult = file_put_contents($dataFile, $jsonContent);
        
        if ($writeResult === false) {
            logMessage("ERROR: Failed to create initial data file: {$dataFile}");
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erro ao criar arquivo de dados inicial']);
            exit;
        }
        
        logMessage("Created new data file with initial structure");
    } else {
        logMessage("Reading existing data file: {$dataFile}");
    }
    
    // Ler e retornar o conteúdo do arquivo
    $jsonContent = file_get_contents($dataFile);
    
    if ($jsonContent === false) {
        logMessage("ERROR: Failed to read data file: {$dataFile}");
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erro ao ler arquivo de dados']);
        exit;
    }
    
    // Verificar se o conteúdo é JSON válido
    $data = json_decode($jsonContent, true);
    if ($data === null) {
        logMessage("ERROR: Data file contains invalid JSON");
        // Se o conteúdo for inválido, retornar uma estrutura vazia
        header('Content-Type: application/json');
        echo json_encode(['submissions' => [], 'leads' => []]);
        exit;
    }
    
    // Log de informações sobre os dados lidos
    $submissionsCount = isset($data['submissions']) ? count($data['submissions']) : 0;
    $leadsCount = isset($data['leads']) ? count($data['leads']) : 0;
    logMessage("SUCCESS: Data file read. Submissions: {$submissionsCount}, Leads: {$leadsCount}");
    
    header('Content-Type: application/json');
    echo $jsonContent;
    exit;
} else {
    // Método não permitido
    logMessage("ERROR: Method not allowed: {$_SERVER['REQUEST_METHOD']}");
    header('Content-Type: application/json');
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}
?> 