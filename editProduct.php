<?php
// ️Inicia sessão
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Valida permissões de administrador
if (!isset($_SESSION['Tipo_USER']) || $_SESSION['Tipo_USER'] !== 'Admin') {
    echo '<p> Acesso negado. Apenas administradores podem editar produtos.</p>';
    die();
}

// Inclui arquivos essenciais
$headerPath = 'goodies/header.php';
$dbPath = 'goodies/DatabaseManager.php';

if (file_exists($headerPath)) {
    require_once($headerPath);
} else {
    echo '<p>Erro ao carregar cabeçalho.</p>';
    die();
}

if (file_exists($dbPath)) {
    require_once($dbPath);
} else {
    echo '<p>Erro ao carregar base de dados.</p>';
    die();
}

// Conexão com a base de Dados
$myDb = establishDbConnection();
if (is_string($myDb)) {
    echo '<p>Erro ao conectar à base de dados.</p>';
    die();
}

// Valida o ID do produto
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo '<p>ID do produto inválido.</p>';
    endDbConnection($myDb);
    die();
}

$productId = intval($_GET['id']);

// Busca detalhes do produto para edição
$query = "SELECT ID_PRODUCT, Nome, Descricao, Preco, Stock FROM product WHERE ID_PRODUCT = ?";
$result = executeQuery($myDb, $query, ['i'], [$productId]);

if (!$result || mysqli_num_rows($result) === 0) {
    echo '<p>Produto não encontrado.</p>';
    endDbConnection($myDb);
    die();
}

$product = mysqli_fetch_assoc($result);

// Atualiza produto
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $descricao = $_POST['descricao'];
    $preco = floatval($_POST['preco']);
    $stock = intval($_POST['stock']);

    $queryUpdate = "UPDATE product SET Nome = ?, Descricao = ?, Preco = ?, Stock = ? WHERE ID_PRODUCT = ?";
    executeQuery($myDb, $queryUpdate, ['s', 's', 'd', 'i', 'i'], [$nome, $descricao, $preco, $stock, $productId]);

    echo '<p>Produto atualizado com sucesso.</p>';
    endDbConnection($myDb);
    echo '<a href="index.php">Voltar à Página Inicial</a>';
    die();
}

// Fecha conexão
endDbConnection($myDb);
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Editar Produto</title>
</head>
<body>
    <h1> Editar Produto</h1>
    <form method="POST">
        <label>Nome: <input type="text" name="nome" value="<?= htmlspecialchars($product['Nome']) ?>" required></label><br> <br>
        <label>Descrição: <textarea name="descricao" required><?= htmlspecialchars($product['Descricao']) ?></textarea></label><br> <br> 
        <label>Preço: <input type="number" step="0.01" name="preco" value="<?= htmlspecialchars($product['Preco']) ?>" required></label><br> <br>
        <label>Stock: <input type="number" name="stock" value="<?= htmlspecialchars($product['Stock']) ?>" required></label><br> <br> 
        <button type="submit">Guardar Alterações</button>
    </form>
    <a href="index.php">Voltar</a>
</body>
</html>
