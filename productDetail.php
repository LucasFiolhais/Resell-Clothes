<?php
// Inicia sessão
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inclui dependências
$headerPath = 'goodies/header.php';
if (file_exists($headerPath)) {
    require_once($headerPath);
} else {
    echo '<p>Erro interno do servidor: falha ao carregar cabeçalho.</p>';
    die();
}

$dbPath = 'goodies/DatabaseManager.php';
if (file_exists($dbPath)) {
    require_once($dbPath);
} else {
    echo '<p>Erro interno do servidor: falha ao carregar a base de dados.</p>';
    die();
}

// Conexão com a base de Dados
$myDb = establishDbConnection();
if (is_string($myDb)) {
    echo '<p>Erro ao conectar à base de dados.</p>';
    die();
}

// Valida e Obtém ID do Produto
if (!isset($_GET['id'])) {
    echo '<p>ID do produto não especificado.</p>';
    die();
} else {
    if (!is_numeric($_GET['id'])) {
        echo '<p>ID do produto inválido.</p>';
        die();
    } else {
        $productId = intval($_GET['id']);
    }
}

// Busca detalhes do produto
$query = "SELECT p.ID_PRODUCT, p.Nome, p.Descricao, p.Preco, i.Path AS Imagem, p.Stock 
          FROM product p
          LEFT JOIN product_image pi ON p.ID_PRODUCT = pi.ID_PRODUCT
          LEFT JOIN image i ON pi.ID_IMAGE = i.ID_IMAGE
          WHERE p.ID_PRODUCT = ?";
$result = executeQuery($myDb, $query, ['i'], [$productId]);

if (!$result) {
    echo '<p>Erro ao carregar os detalhes do produto.</p>';
    die();
} else {
    if (mysqli_num_rows($result) === 0) {
        echo '<p>Produto não encontrado.</p>';
        die();
    } else {
        $product = mysqli_fetch_assoc($result);
    }
}

// Insere Avaliação do Produto
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_review'])) {
        if (isset($_POST['rating'], $_POST['comment'], $_SESSION['id_users'])) {
            $rating = intval($_POST['rating']);
            $comment = trim($_POST['comment']);
            $clientId = intval($_SESSION['id_users']);

            // Verifica se o cliente já avaliou este produto
            $queryCheckReview = "SELECT 1 FROM reviews WHERE ID_CLIENT = ? AND ID_PRODUCT = ?";
            $hasReviewed = executeQuery($myDb, $queryCheckReview, ['i', 'i'], [$clientId, $productId]);

            if ($hasReviewed && mysqli_num_rows($hasReviewed) > 0) {
                echo '<br><br><br><br><br><p style="color: red; text-align: center;">Já avaliou este produto.</p>';
            } else {
                // Verifica se o cliente comprou este produto
                $queryCheckPurchase = "SELECT 1 FROM product_orders po
                                       INNER JOIN orders o ON po.ID_ORDERS = o.ID_ORDERS
                                       WHERE o.ID_CLIENT = ? AND po.ID_PRODUCT = ?";
                $hasPurchased = executeQuery($myDb, $queryCheckPurchase, ['i', 'i'], [$clientId, $productId]);

                if ($hasPurchased && mysqli_num_rows($hasPurchased) > 0) {
                    // Adiciona a avaliação
                    $queryAddReview = "INSERT INTO reviews (ID_CLIENT, ID_PRODUCT, Rating, Comment) 
                                       VALUES (?, ?, ?, ?)";
                    $reviewAdded = executeQuery($myDb, $queryAddReview, ['i', 'i', 'i', 's'], [$clientId, $productId, $rating, $comment]);

                    if ($reviewAdded) {
                        echo '<br><br><br><br><br><p style="color: green; text-align: center;">Avaliação enviada com sucesso!</p>';
                    } else {
                        echo '<br><br><br><br><br><p style="color: red; text-align: center;">Erro ao enviar a avaliação. Tente novamente mais tarde.</p>';
                    }
                } else {
                    echo '<br><br><br><br><br><p style="color: red; text-align: center;">Só pode avaliar produtos que comprou.</p>';
                }
            }
        } else {
            echo '<p style="color: red; text-align: center;">Todos os campos são obrigatórios.</p>';
        }
    }
}

// Busca avaliações do produto
$queryReviews = "SELECT r.Rating, r.Comment, u.username, r.CreatedAt
                 FROM reviews r
                 INNER JOIN user u ON r.ID_CLIENT = u.id_users
                 WHERE r.ID_PRODUCT = ?";
$reviews = executeQuery($myDb, $queryReviews, ['i'], [$productId]);

// Calcula a média e o número de avaliações
$queryReviewStats = "SELECT AVG(Rating) AS AvgRating, COUNT(Rating) AS TotalReviews
                     FROM reviews
                     WHERE ID_PRODUCT = ?";
$reviewStats = executeQuery($myDb, $queryReviewStats, ['i'], [$productId]);

$avgRating = 0;
$totalReviews = 0;
if ($reviewStats) {
    $stats = mysqli_fetch_assoc($reviewStats);
    $avgRating = $stats['AvgRating'] ? round($stats['AvgRating'], 2) : 0;
    $totalReviews = $stats['TotalReviews'];
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($product['Nome']) ?> - RESELL CLOTHES</title>
	<link rel="stylesheet" href="style1.css">

</head>
<body>
<div class="product-details">
    <img src="/trabalho/images/<?= htmlspecialchars($product['Imagem'] ?? 'default-product.png') ?>" alt="<?= htmlspecialchars($product['Nome']) ?>">
    <h1><?= htmlspecialchars($product['Nome']) ?></h1>
    <p><strong>Descrição:</strong> <?= nl2br(htmlspecialchars($product['Descricao'])) ?></p>
    <p class="price">Preço: €<?= number_format($product['Preco'], 2, ',', '.') ?></p>
    <p><strong>Stock Disponível:</strong> <?= htmlspecialchars($product['Stock']) ?></p>

    <!-- Exibição da Média das Avaliações -->
    <p><strong>Avaliação Média:</strong> <?= $avgRating ?>/5 (<?= $totalReviews ?> Avaliações)</p>

    <!-- Formulário de Avaliação -->
    <?php 
    if (isset($_SESSION['id_users']) && !empty($_SESSION['id_users'])) { 
        // Verifica se o cliente já avaliou este produto
        $queryCheckReview = "SELECT 1 FROM reviews WHERE ID_CLIENT = ? AND ID_PRODUCT = ?";
        $hasReviewed = executeQuery($myDb, $queryCheckReview, ['i', 'i'], [$_SESSION['id_users'], $productId]);

        if ($hasReviewed && mysqli_num_rows($hasReviewed) === 0) { ?>
            <form method="POST">
                <h3>Avalie este produto</h3>
                <label for="rating">Nota:</label>
                <select name="rating" id="rating" required>
                    <option value="1">1 - Muito Mau</option>
                    <option value="2">2 - Mau</option>
                    <option value="3">3 - OK</option>
                    <option value="4">4 - Muito Bom</option>
                    <option value="5">5 - Excelente</option>
                </select>
                <br>
                <label for="comment">Comentário:</label>
                <textarea name="comment" id="comment" rows="3" required></textarea>
                <br>
                <button type="submit" name="add_review">Enviar Avaliação</button>
            </form>
    <?php 
        } else {
            echo '<p style="text-align: center;">Você já avaliou este produto.</p>';
        }
    } else {
        echo '<p style="text-align: center;">Faça login para avaliar este produto.</p>';
    } ?>
	<!-- Botões Administrativos -->
    <?php 
    if (isset($_SESSION['Tipo_USER'])) {
        if ($_SESSION['Tipo_USER'] === 'Admin') { ?>
            <div class="admin-buttons">
                <a href="editProduct.php?id=<?= htmlspecialchars($product['ID_PRODUCT']) ?>" class="edit">Editar Produto</a>
                <a href="deleteProduct.php?id=<?= htmlspecialchars($product['ID_PRODUCT']) ?>" class="delete">Remover Produto</a>
            </div>
			<div class="reviews">
    <h3>Avaliações:</h3>
    <?php 
    if ($reviews) {
        if (mysqli_num_rows($reviews) > 0) {
            while ($review = mysqli_fetch_assoc($reviews)) { ?>
                <div class="review">
                    <p><strong><?= htmlspecialchars($review['username']) ?></strong> - <?= htmlspecialchars($review['CreatedAt']) ?></p>
                    <p>Nota: <?= htmlspecialchars($review['Rating']) ?>/5</p>
                    <p><?= nl2br(htmlspecialchars($review['Comment'])) ?></p>
                </div>
    <?php 
            }
        } else {
            echo '<p>Este produto ainda não possui avaliações.</p>';
        }
    } else {
        echo '<p>Erro ao carregar avaliações. Tente novamente mais tarde.</p>';
    }
    ?>
</div>

			
			
</div>

    <?php 
        }
    } ?>



<a href="index.php">Voltar</a>
</body>
</html>