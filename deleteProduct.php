<?php
// Inicia sessão
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Valida permissões de administrador
if (!isset($_SESSION['Tipo_USER']) || $_SESSION['Tipo_USER'] !== 'Admin') {
    echo '<p class="error-message">Acesso negado. Apenas administradores podem remover produtos.</p>';
    exit();
}

// Inclui arquivos essenciais
require_once('goodies/header.php');
require_once('goodies/DatabaseManager.php');
require_once('goodies/BagOfTricks.php');

// Conexão com a base de dados
$myDb = establishDbConnection();
if (is_string($myDb)) {
    echo '<p class="error-message">Erro ao conectar à base de dados.</p>';
    exit();
}

// Valida ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo '<p>ID do produto inválido.</p>';
    endDbConnection($myDb);
    die();
}

$productId = intval($_GET['id']);

// Inicia transação
mysqli_begin_transaction($myDb);

// Remove dependências na tabela product_orders
$result = executeSafeQuery($myDb, "DELETE FROM product_orders WHERE ID_PRODUCT = ?", [$productId], 'i');
if ($result !== true) {
    mysqli_rollback($myDb);
    echo "<p class='error-message'>Erro ao remover referências de pedidos: $result</p>";
    endDbConnection($myDb);
    exit();
}

// Remove dependências na tabela product_image
$result = executeSafeQuery($myDb, "DELETE FROM product_image WHERE ID_PRODUCT = ?", [$productId], 'i');
if ($result !== true) {
    mysqli_rollback($myDb);
    echo "<p class='error-message'>Erro ao remover referências de imagens: $result</p>";
    endDbConnection($myDb);
    exit();
}

// Remove o produto da tabela product
$result = executeSafeQuery($myDb, "DELETE FROM product WHERE ID_PRODUCT = ?", [$productId], 'i');
if ($result !== true) {
    mysqli_rollback($myDb);
    echo "<p class='error-message'>Erro ao remover produto: $result</p>";
    endDbConnection($myDb);
    exit();
}

// Finaliza a transação
mysqli_commit($myDb);
echo "<p class='success-message'>Produto removido com sucesso!</p>";

// Fecha conexão
endDbConnection($myDb);
?>