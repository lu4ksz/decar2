<?php
// Script simples para salvar dados em arquivo de texto
// Este script aceita qualquer dado enviado por POST e o salva em um arquivo texto
// Para usar: envie um POST com qualquer dado para este arquivo

// Nome do arquivo para armazenar os dados
$arquivo = 'todos_dados.txt';

// Diretório atual
$diretorio_atual = dirname(__FILE__);
$caminho_completo = $diretorio_atual . '/' . $arquivo;

// Registrar solicitação
file_put_contents('log_acesso.txt', date('Y-m-d H:i:s') . ' - ' . $_SERVER['REMOTE_ADDR'] . "\n", FILE_APPEND);

// Se for uma solicitação POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obter dados brutos do POST
    $dados = file_get_contents('php://input');
    
    // Salvar dados no arquivo
    file_put_contents($caminho_completo, $dados);
    
    // Responder com sucesso
    header('Content-Type: application/json');
    echo json_encode(['sucesso' => true, 'mensagem' => 'Dados salvos com sucesso']);
    exit;
}

// Se for uma solicitação GET
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Verificar se o arquivo existe
    if (file_exists($caminho_completo)) {
        // Retornar conteúdo do arquivo
        $conteudo = file_get_contents($caminho_completo);
        
        // Verificar se é JSON válido
        $json_teste = json_decode($conteudo);
        if ($json_teste === null) {
            // Se não for JSON válido, criar estrutura inicial
            $conteudo = '{"submissions":[],"leads":[]}';
            file_put_contents($caminho_completo, $conteudo);
        }
        
        header('Content-Type: application/json');
        echo $conteudo;
    } else {
        // Criar arquivo vazio com estrutura básica
        $estrutura_inicial = '{"submissions":[],"leads":[]}';
        file_put_contents($caminho_completo, $estrutura_inicial);
        
        header('Content-Type: application/json');
        echo $estrutura_inicial;
    }
    exit;
}

// Se o método não for GET nem POST
header('Content-Type: application/json');
http_response_code(405); // Method Not Allowed
echo json_encode(['sucesso' => false, 'mensagem' => 'Método não permitido']);
?> 