<?php
// ️ Inicia sessão para identificar o utilizador logado
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verifica se o utilizador está autenticado
if (!isset($_SESSION['id_users'])) {
    echo 'Acesso negado. Por favor, faça login para visualizar as suas encomendas.';
    die();
}

// Inclui o cabeçalho
$path = 'goodies/header.php';
if (file_exists($path)) {
    require_once($path);
} else {
    echo '<p style="color:red;">Erro interno do servidor: falha ao carregar o cabeçalho.</p>';
    die();
}

// Inclui o gerenciador de banco de dados
$dbPath = 'goodies/DatabaseManager.php';
if (file_exists($dbPath)) {
    require_once($dbPath);
} else {
    echo '<p style="color:red;">Erro interno do servidor: falha ao carregar a base de dados.</p>';
    die();
}

// Conecta ao banco de dados
$myDb = establishDbConnection();
if (is_string($myDb)) {
    echo '<p style="color:red;">Erro ao conectar à base de dados.</p>';
    die();
}

// Obtém o ID do utilizador logado
if (isset($_SESSION['id_users'])) {
    $userId = intval($_SESSION['id_users']);
} else {
    echo '<p style="color:red;">Erro ao identificar o utilizador. Faça login novamente.</p>';
    endDbConnection($myDb);
    die();
}

// Busca as encomendas do utilizador
$queryOrders = "
    SELECT o.ID_ORDERS, o.Data, o.Status, o.Total, o.Morada, o.Telefone
    FROM orders o
    WHERE o.ID_CLIENT = ?
    ORDER BY o.Data DESC
";
$resultOrders = executeQuery($myDb, $queryOrders, ['i'], [$userId]);

if (!$resultOrders) {
    if (is_string($resultOrders)) {
        echo '<p style="color:red;">Erro ao carregar as suas encomendas: ' . htmlspecialchars($resultOrders) . '</p>';
    } else {
        echo '<p style="color:red;">Erro ao carregar as suas encomendas. Tente novamente mais tarde.</p>';
    }
    endDbConnection($myDb);
    die();
}

// Verifica se existem encomendas
if (mysqli_num_rows($resultOrders) === 0) {
    echo '<p style="text-align:center;">Nenhuma encomenda encontrada.</p>';
    endDbConnection($myDb);
    die();
}

?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Minhas Encomendas - RESELL CLOTHES</title>
    <style>
        table {
            width: 90%;
            margin: 20px auto;
            border-collapse: collapse;
			background-color:#FFE4B5;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f4f4f4;
        }
        .details {
            margin: 20px auto;
            width: 90%;
            border: 1px solid #ccc;
            padding: 10px;
            border-radius: 5px;
        }
        .back-button {
            display: block;
            margin: 20px auto;
            text-align: center;
        }
        .back-button a {
            padding: 10px 15px;
            background-color: #0078D7;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .back-button a:hover {
            background-color: #005ea2;
        }
		body{background-color:#FFEBCD;}
    </style>
</head>
<body>
<br>
<br>
<br>
<br>
<br>
<h1 style="text-align: center;">Minhas Encomendas</h1>

<!-- Encomendas -->
<table>
    <tr>
        <th>#</th>
        <th>Data</th>
        <th>Status</th>
        <th>Total (€)</th>
        <th>Morada</th>
        <th>Telefone</th>
        <th>Produtos</th>
    </tr>
    <?php while ($order = mysqli_fetch_assoc($resultOrders)): ?>
        <tr>
            <td><?= htmlspecialchars($order['ID_ORDERS']) ?></td>
            <td><?= htmlspecialchars($order['Data']) ?></td>
            <td><?= htmlspecialchars($order['Status']) ?></td>
            <td>€<?= number_format($order['Total'], 2, ',', '.') ?></td>
            <td><?= htmlspecialchars($order['Morada']) ?></td>
            <td><?= htmlspecialchars($order['Telefone']) ?></td>
            <td>
                <button onclick="toggleDetails(<?= htmlspecialchars($order['ID_ORDERS']) ?>)">Ver Produtos</button>
                <div id="details-<?= htmlspecialchars($order['ID_ORDERS']) ?>" class="details" style="display: none;">
                    <table>
                        <tr>
                            <th>Produto</th>
                            <th>Quantidade</th>
                            <th>Preço Unitário (€)</th>
                            <th>Total (€)</th>
                        </tr>
                        <?php
                        $queryProducts = "
                            SELECT p.Nome, po.Quantidade, p.Preco
                            FROM product_orders po
                            JOIN product p ON po.ID_PRODUCT = p.ID_PRODUCT
                            WHERE po.ID_ORDERS = ?
                        ";
                        $resultProducts = executeQuery($myDb, $queryProducts, ['i'], [$order['ID_ORDERS']]);

                        if ($resultProducts) {
                            if (is_string($resultProducts)) {
                                echo '<tr><td colspan="4">Erro ao carregar produtos: ' . htmlspecialchars($resultProducts) . '</td></tr>';
                            } else {
                                while ($product = mysqli_fetch_assoc($resultProducts)) {
                                    $totalPrice = $product['Quantidade'] * $product['Preco'];
                                    ?>
                                    <tr>
                                        <td><?= htmlspecialchars($product['Nome']) ?></td>
                                        <td><?= htmlspecialchars($product['Quantidade']) ?></td>
                                        <td>€<?= number_format($product['Preco'], 2, ',', '.') ?></td>
                                        <td>€<?= number_format($totalPrice, 2, ',', '.') ?></td>
                                    </tr>
                                    <?php
                                }
                            }
                        } else {
                            echo '<tr><td colspan="4">Nenhum produto encontrado.</td></tr>';
                        }
                        ?>
                    </table>
                </div>
            </td>
        </tr>
    <?php endwhile; ?>
</table>

<div class="back-button">
    <a href="index.php">Voltar à Página Inicial</a>
</div>

<script>
    function toggleDetails(orderId) {
        const details = document.getElementById(`details-${orderId}`);
        if (details.style.display === 'none') {
            details.style.display = 'block';
        } else {
            details.style.display = 'none';
        }
    }
</script>

</body>
</html>

<?php
// Fecha a conexão no final
endDbConnection($myDb);
?>
