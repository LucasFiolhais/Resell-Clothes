<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>-- RESSEL CLOTHES --</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<?php
// Inclui o cabeçalho (menu e estilos comuns)
$path = 'goodies/header.php';
if (file_exists($path)) {
    require_once($path);
} else {
    echo '<p class="message" style="color:red;">Erro interno do servidor: por favor, tente novamente mais tarde (Código: 8).</p>';
    die();
}

// Inicia sessão para exibir mensagens (não obrigatório para filtros)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Exibe mensagens armazenadas na sessão
if (!empty($_SESSION['code'])) {
    require_once('goodies/codes.php');
    if (isset($codes[$_SESSION['code']])) {
        echo "<div class='message'>{$codes[$_SESSION['code']]}</div>";
        unset($_SESSION['code']);
    }
}

// Conexão com a base de dados
$dbPath = 'goodies/DatabaseManager.php';
if (file_exists($dbPath)) {
    require_once($dbPath);
} else {
    echo '<p class="message" style="color:red;">Erro interno do servidor: falha ao carregar a base de dados.</p>';
    die();
}

$myDb = establishDbConnection();
if (is_string($myDb)) {
    echo '<p class="message" style="color:red;">Erro ao conectar à base de dados.</p>';
    die();
}

// Obter categorias para o filtro
$categoryQuery = "SELECT ID_CATEGORY, category_name FROM category";
$categoryResult = executeQuery($myDb, $categoryQuery, [], []);

if (!$categoryResult || is_string($categoryResult)) {
    echo '<p class="message" style="color:red;">Erro ao carregar as categorias. Tente novamente mais tarde.</p>';
    die();
}

// Verificar a categoria selecionada
$selectedCategory = $_GET['category'] ?? 'all';
$filterCondition = "";
$params = [];
$types = [];

// Adicionar condição de filtro se uma categoria específica foi escolhida
if ($selectedCategory !== 'all') {
    $filterCondition = "WHERE p.ID_CATEGORY = ?";
    $params[] = (int)$selectedCategory;
    $types[] = 'i';
}

// Busca produtos na base de dados
$query = "SELECT p.ID_PRODUCT, p.Nome, p.Preco, p.Stock, i.Path AS Imagem 
          FROM product p
          LEFT JOIN product_image pi ON p.ID_PRODUCT = pi.ID_PRODUCT
          LEFT JOIN image i ON pi.ID_IMAGE = i.ID_IMAGE
          $filterCondition
          LIMIT 100";

$result = executeQuery($myDb, $query, $types, $params);

if (!$result || is_string($result)) {
    echo '<p class="message" style="color:red;">Erro ao carregar os produtos. Por favor, tente novamente mais tarde.</p>';
    die();
}

endDbConnection($myDb);
?>
<br>
<br>
<br>
<br>
<br>

<h1 style="text-align: center;">-- RESSEL CLOTHES --</h1>

<!-- Formulário de Filtro -->
<form method="GET" action="">
    <label for="category">Filtrar por Categoria:</label>
    <select name="category" id="category">
        <option value="all" <?= $selectedCategory === 'all' ? 'selected' : '' ?>>Todas</option>
        <?php while ($category = mysqli_fetch_assoc($categoryResult)) : ?>
            <option value="<?= $category['ID_CATEGORY'] ?>" 
                <?= $selectedCategory == $category['ID_CATEGORY'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($category['category_name']) ?>
            </option>
        <?php endwhile; ?>
    </select>
    <button type="submit">Filtrar</button>
</form>

<!-- Lista de Produtos -->
<div class="product-grid">
    <?php while ($product = mysqli_fetch_assoc($result)) : ?>
        <div class="product-card">
            <img src="/trabalho/images/<?= htmlspecialchars($product['Imagem'] ?? 'default-product.png') ?>" 
                 alt="<?= htmlspecialchars($product['Nome']) ?>" />
            <h3><?= htmlspecialchars($product['Nome']) ?></h3>
            <p>Preço: €<?= number_format($product['Preco'], 2, ',', '.') ?></p>
            <p>Stock: <?= $product['Stock'] > 0 ? $product['Stock'] : '<span style="color:red;">Indisponível</span>' ?></p>
            <a href="productDetail.php?id=<?= htmlspecialchars($product['ID_PRODUCT']) ?>">Ver Detalhes</a>
            <form method="POST" action="cart.php">
                <input type="hidden" name="product_id" value="<?= htmlspecialchars($product['ID_PRODUCT']) ?>">
                <input type="hidden" name="product_name" value="<?= htmlspecialchars($product['Nome']) ?>">
                <input type="hidden" name="product_price" value="<?= htmlspecialchars($product['Preco']) ?>">
                <button type="submit" name="add_to_cart" 
                        <?= $product['Stock'] <= 0 ? 'disabled' : '' ?>>Adicionar ao Carrinho</button>
            </form>
        </div>
    <?php endwhile; ?>
</div>

</body>
</html>