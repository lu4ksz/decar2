# Sistema de Cadastro Decar

Este é um sistema de formulário de cadastro com painel administrativo que armazena dados centralmente.

## Configuração

1. Faça o upload de todos os arquivos para seu servidor web com suporte a PHP.
2. O arquivo `data.json` será criado automaticamente na primeira utilização.
3. Certifique-se de que o PHP tem permissão para escrever no diretório onde o `data.json` será criado.

## Estrutura de Arquivos

- `index2.html` - Formulário principal de cadastro
- `admin-panel.html` - Painel administrativo
- `login.html` - Página de login
- `success.html` - Página de sucesso após envio do formulário
- `sync.js` - Script de sincronização de dados
- `save-data.php` - Script PHP para gerenciar o armazenamento de dados
- `data.json` - Arquivo que armazena todos os dados
- `.htaccess` - Arquivo de configuração do servidor para proteger o data.json

## Como Funciona

1. Quando um usuário preenche o formulário, os dados são salvos no arquivo `data.json` através do `save-data.php`.
2. No painel administrativo, todos os registros são carregados deste mesmo arquivo.
3. Caso haja problemas ao acessar o arquivo central, o sistema usa `localStorage` como fallback para evitar perda de dados.
4. O `.htaccess` protege o acesso direto ao arquivo `data.json`.

## Funcionalidades

- Formulário de cadastro com validação
- Painel administrativo com autenticação
- Gerenciamento de leads com status e histórico
- Configuração de campos do formulário
- Pesquisa e filtragem de registros
- Sistema de armazenamento centralizado para sincronização entre dispositivos

## Requisitos

- Servidor web com PHP 7.0+
- Permissões de escrita para o PHP no diretório do projeto

## Acesso ao Painel Administrativo

Para acessar o painel administrativo, você pode:
1. Clicar no link oculto "Admin" no rodapé 
2. Pressionar Ctrl+Alt+A no teclado enquanto estiver no site
3. Digitar "adminaccess" em qualquer página do site
4. Acessar diretamente o URL: `/login.html`

---

© 2023 Decar Veículos - Todos os direitos reservados 