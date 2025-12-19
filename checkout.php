<?php
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

// Conexão com a base de dados
$myDb = establishDbConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $morada = $_POST['morada'] ?? '';
    $telefone = $_POST['telefone'] ?? '';
    $metodo_pagamento = $_POST['metodo_pagamento'] ?? '';
    $payment_details = $_POST['payment_details'] ?? '';
    $cliente_id = $_SESSION['id_users'] ?? 1;

    $total = 0;

    // Calcula o total do carrinho
    if (!empty($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $item) {
            $total += $item['price'] * $item['quantity'];
        }
    }

    // Validações
    if (empty($morada) || empty($telefone) || empty($metodo_pagamento) || $total <= 0) {
        echo '<p style="color:red; text-align:center;">Preencha todos os campos corretamente.</p>';
        die();
    }

    if (!preg_match('/^\d{9}$/', $telefone)) {
        echo '<p style="color:red; text-align:center;">Número de telefone inválido. Deve conter 9 dígitos.</p>';
        die();
    }

    if ($metodo_pagamento === 'mbway') {
        if (!preg_match('/^\d{9}$/', $payment_details)) {
            echo '<p style="color:red; text-align:center;">Número MB Way inválido. Deve conter 9 dígitos.</p>';
            die();
        }
    } elseif ($metodo_pagamento === 'paypal') {
        if (!filter_var($payment_details, FILTER_VALIDATE_EMAIL)) {
            echo '<p style="color:red; text-align:center;">Email PayPal inválido.</p>';
            die();
        }
    } else {
        echo '<p style="color:red; text-align:center;">Método de pagamento inválido.</p>';
        die();
    }

        // Insere a ordem
        $queryOrder = "INSERT INTO orders (ID_CLIENT, Data, Status, Morada, Telefone,metodo_pagamento,payment_details , Total) VALUES (?, NOW(), 'Pendente', ?, ?, ?,?,?)";
        $orderInsertResult = executeInsertQuery($myDb, $queryOrder, ['i', 's', 's','s','s','d'], [$cliente_id, $morada, $telefone,$metodo_pagamento,$payment_details, $total]);

        if (!$orderInsertResult['success']) {
            echo '<p style="color:red; text-align:center;">Erro ao registrar a encomenda: ' . htmlspecialchars($orderInsertResult['error']) . '</p>';
            die();
        }

        $order_id = $orderInsertResult['insert_id'];

        // Processa os produtos
        foreach ($_SESSION['cart'] as $item) {
			// Verifica se o produto já existe na ordem
			$queryCheckProductOrder = "SELECT Quantidade FROM product_orders WHERE ID_ORDERS = ? AND ID_PRODUCT = ?";
			$result = executeQuery($myDb, $queryCheckProductOrder, ['i', 'i'], [$order_id, $item['id']]);

			if ($result && mysqli_num_rows($result) > 0) {
				// Atualiza a quantidade do produto existente
				$queryUpdateProductOrder = "UPDATE product_orders SET Quantidade = Quantidade + ? WHERE ID_ORDERS = ? AND ID_PRODUCT = ?";
				executeQuery($myDb, $queryUpdateProductOrder, ['i', 'i', 'i'], [$item['quantity'], $order_id, $item['id']]);
			} else {
				// Insere um novo registro na tabela product_orders
				$queryProductOrder = "INSERT INTO product_orders (ID_ORDERS, ID_PRODUCT, Quantidade) VALUES (?, ?, ?)";
				executeQuery($myDb, $queryProductOrder, ['i', 'i', 'i'], [$order_id, $item['id'], $item['quantity']]);
			}

    // Atualiza o stock do produto
    $queryUpdateStock = "UPDATE product SET Stock = Stock - ? WHERE ID_PRODUCT = ?";
    executeQuery($myDb, $queryUpdateStock, ['i', 'i'], [$item['quantity'], $item['id']]);
}
mysqli_commit($myDb);
    unset($_SESSION['cart']);
    echo '<p style="color:green; text-align:center;">Compra finalizada com sucesso!</p>';

}

      
?>
<!-- Formulário -->
<br>
<br>
<br>
<br>
<form method="POST" style="width: 50%; margin: auto;">
    <label>Morada:</label><br>
    <input type="text" name="morada" required style="width: 100%; margin-bottom: 10px;"><br>

    <label>Telefone:</label><br>
    <input type="text" name="telefone" required style="width: 100%; margin-bottom: 10px;"><br>

    <label>Método de Pagamento:</label><br>
    <input type="radio" name="metodo_pagamento" value="mbway" onclick="toggleMbwayInput(true); togglePaypalInput(false);" required> MB Way</label><br>
    <input type="radio" name="metodo_pagamento" value="paypal" onclick="togglePaypalInput(true); toggleMbwayInput(false);" required> PayPal</label><br>

    <input type="hidden" name="payment_details" id="paymentDetails">

    <div id="mbwayInput" style="display: none; margin-top: 10px;">
        <label>Insira o número MB Way:</label><br>
        <input type="text" id="mbwayNumber" style="width: 100%; margin-bottom: 10px;" oninput="updatePaymentDetails('mbway')">
    </div>

    <div id="paypalInput" style="display: none; margin-top: 10px;">
        <label>Insira o email PayPal:</label><br>
        <input type="text" id="paypalEmail" style="width: 100%; margin-bottom: 10px;" oninput="updatePaymentDetails('paypal')">
    </div>

    <button type="submit" style="margin-top: 10px; padding: 10px 20px; font-size: 16px;">Concluir Compra</button>
</form>

<a href="cart.php" style="display: block; text-align: center; margin-top: 20px;"><- Voltar ao Carrinho</a>

<script>
    function toggleMbwayInput(show) {
		if (show === true)
			document.getElementById('mbwayInput').style.display =  'block'; 
		else
			document.getElementById('mbwayInput').style.display =  'none';
    }

    function togglePaypalInput(show) {
        if (show === true)
			document.getElementById('paypalInput').style.display =  'block'; 
		else
			document.getElementById('paypalInput').style.display =  'none';
    }

    function updatePaymentDetails(type) {
        const detailsField = document.getElementById('paymentDetails');
        if (type === 'mbway') {
            detailsField.value = document.getElementById('mbwayNumber').value;
        } else if (type === 'paypal') {
            detailsField.value = document.getElementById('paypalEmail').value;
        }
    }
</script>