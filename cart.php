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

// Conexão com Banco de Dados
$myDb = establishDbConnection();
if (is_string($myDb)) {
    echo '<p>Erro ao conectar à base de dados.</p>';
    die();
}

// Gerenciamento do Carrinho
$hasOutOfStockItems = false;
$total = 0;

if (isset($_POST['add_to_cart'])) {
    if (isset($_POST['product_id']) && isset($_POST['product_name']) && isset($_POST['product_price'])) {
        $product_id = intval($_POST['product_id']);
        $product_name = $_POST['product_name'];
        $product_price = floatval($_POST['product_price']);

        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        $found = false;
        foreach ($_SESSION['cart'] as $key => $item) {
            if ($item['id'] === $product_id) {
                $_SESSION['cart'][$key]['quantity']++;
                $found = true;
                break;
            }
        }

        if ($found === false) {
            $_SESSION['cart'][] = [
                'id' => $product_id,
                'name' => $product_name,
                'price' => $product_price,
                'quantity' => 1,
                'has_stock' => true
            ];
        }
    }
}

if (isset($_GET['remove'])) {
    if (!empty($_GET['remove'])) {
        $product_id = intval($_GET['remove']);
        $_SESSION['cart'] = array_filter($_SESSION['cart'], function ($item) use ($product_id) {
            if ($item['id'] !== $product_id) {
                return true;
            } else {
                return false;
            }
        });

        $_SESSION['cart'] = array_values($_SESSION['cart']);
    }
}

// Verifica o stock dos produtos no carrinho
if (isset($_SESSION['cart'])) {
    if (!empty($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $key => $item) {
            $queryStockCheck = "SELECT Stock FROM product WHERE ID_PRODUCT = ?";
            $result = executeQuery($myDb, $queryStockCheck, ['i'], [$item['id']]);

            if ($result) {
                $stockData = mysqli_fetch_assoc($result);
                if ($stockData) {
                    if ($stockData['Stock'] < $item['quantity']) {
                        $_SESSION['cart'][$key]['has_stock'] = false;
                        $hasOutOfStockItems = true;
                    } else {
                        $_SESSION['cart'][$key]['has_stock'] = true;
                    }
                } else {
                    $_SESSION['cart'][$key]['has_stock'] = false;
                    $hasOutOfStockItems = true;
                }
            } else {
                $_SESSION['cart'][$key]['has_stock'] = false;
                $hasOutOfStockItems = true;
            }
        }

        foreach ($_SESSION['cart'] as $item) {
            if ($item['has_stock'] === true) {
                $total += $item['price'] * $item['quantity'];
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Carrinho de Compras</title>
    <style>
        table {
            width: 60%;
            margin: auto;
            border-collapse: collapse;
			background-color:#FFE4B5;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: center;
        }
        th {
            background-color: #f4f4f4;
        }
        .finalizar {
            margin-top: 20px;
            text-align: center;
        }
        .finalizar a {
            padding: 10px 20px;
            background-color: green;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .finalizar a.disabled {
            background-color: grey;
            pointer-events: none;
            opacity: 0.5;
        }
		body{background-color:#FFEBCD}
    </style>
</head>
<body>
<br>
<br>
<br>
<br>
<br>
<h1 style="text-align: center;">Carrinho de Compras</h1>
<?php if (isset($_SESSION['cart'])) { ?>
    <?php if (!empty($_SESSION['cart'])) { ?>
        <table>
            <tr>
                <th>Nome</th>
                <th>Preço</th>
                <th>Quantidade</th>
                <th>Total</th>
                <th>Stock</th>
                <th>Ação</th>
            </tr>
            <?php foreach ($_SESSION['cart'] as $item) { ?>
                <tr>
                    <td><?= htmlspecialchars($item['name']) ?></td>
                    <td>€<?= number_format($item['price'], 2, ',', '.') ?></td>
                    <td><?= $item['quantity'] ?></td>
                    <td>€<?= number_format($item['price'] * $item['quantity'], 2, ',', '.') ?></td>
                    <td>
                        <?php if ($item['has_stock'] === true) { ?>
                            <span style="color: green;">Disponível</span>
                        <?php } else { ?>
                            <span style="color: red;">Fora de stock</span>
                        <?php } ?>
                    </td>
                    <td>
                        <a href="cart.php?remove=<?= htmlspecialchars($item['id']) ?>">Remover</a>
                    </td>
                </tr>
            <?php } ?>
            <tr>
                <td colspan="3"><strong>Total:</strong></td>
                <td colspan="3"><strong>€<?= number_format($total, 2, ',', '.') ?></strong></td>
            </tr>
        </table>

        <div class="finalizar">
            <?php if ($hasOutOfStockItems === true) { ?>
                <p style="color: red; font-weight: bold;">Há itens no carrinho com quantidade superior ao stock disponível. Ajuste seu carrinho antes de finalizar a compra.</p>
                <a href="#" class="disabled">Finalizar Compra</a>
            <?php } else { ?>
                <a href="checkout.php">Finalizar Compra</a>
            <?php } ?>
        </div>
    <?php } else { ?>
        <p style="text-align: center;">Seu carrinho está vazio.</p>
    <?php } ?>
<?php } ?>

<a href="index.php" style="display: block; text-align: center; margin-top: 20px;">Voltar às Compras</a>
</body>
</html>
