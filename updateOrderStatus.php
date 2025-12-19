<?php
// Inicia sessão
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verifica se o usuário é administrador
if (!isset($_SESSION['Tipo_USER']) || $_SESSION['Tipo_USER'] !== 'Admin') {
    echo '<p style="color:red; text-align:center;">Acesso negado. Apenas administradores podem acessar esta página.</p>';
    die();
}

// Inclui dependências
require_once('goodies/header.php');
require_once('goodies/DatabaseManager.php');

// Conexão com o banco de dados
$myDb = establishDbConnection();

// Atualizar o status da encomenda
if (isset($_POST['update_status'])) {
    if (isset($_POST['order_id'], $_POST['status'])) {
        $order_id = intval($_POST['order_id']);
        $new_status = $_POST['status'];

        $queryUpdateStatus = "UPDATE orders SET Status = ? WHERE ID_ORDERS = ?";
        $result = executeQuery($myDb, $queryUpdateStatus, ['s', 'i'], [$new_status, $order_id]);

    }
}

// Obter todas as encomendas
$queryOrders = "SELECT ID_ORDERS, ID_CLIENT, Data, Status, Morada, Telefone, Total FROM orders";
$resultOrders = executeQuery($myDb, $queryOrders, [], []);

if (!$resultOrders || is_string($resultOrders)) {
    echo '<p style="color:red; text-align:center;">Erro ao carregar as encomendas. Por favor, tente novamente mais tarde.</p>';
    die();
}

?>
<br>
<br>
<br>
<br>
<br>
<h1 style="text-align: center;">Gestão de Encomendas</h1>
<table border="1" style="width: 80%; margin: auto; text-align: center; border-collapse: collapse;">
    <thead>
        <tr>
            <th>ID Encomenda</th>
            <th>ID Cliente</th>
            <th>Data</th>
            <th>Status</th>
            <th>Morada</th>
            <th>Telefone</th>
            <th>Total</th>
            <th>Ação</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($order = mysqli_fetch_assoc($resultOrders)) : ?>
            <tr>
                <td><?= htmlspecialchars($order['ID_ORDERS']) ?></td>
                <td><?= htmlspecialchars($order['ID_CLIENT']) ?></td>
                <td><?= htmlspecialchars($order['Data']) ?></td>
                <td><?= htmlspecialchars($order['Status']) ?></td>
                <td><?= htmlspecialchars($order['Morada']) ?></td>
                <td><?= htmlspecialchars($order['Telefone']) ?></td>
                <td>€<?= number_format($order['Total'], 2, ',', '.') ?></td>
                <td>
                    <form method="POST" action="updateOrderStatus.php" style="display: inline;">
                        <input type="hidden" name="order_id" value="<?= htmlspecialchars($order['ID_ORDERS']) ?>">
                        <select name="status" required>
                            <option value="Pendente" <?= $order['Status'] === 'Pendente' ? 'selected' : '' ?>>Pendente</option>
                            <option value="Pago" <?= $order['Status'] === 'Pago' ? 'selected' : '' ?>>Pago</option>
                            <option value="Enviado" <?= $order['Status'] === 'Enviado' ? 'selected' : '' ?>>Enviado</option>
                        </select>
                        <button type="submit" name="update_status">Atualizar</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<a href="index.php" style="display: block; text-align: center; margin-top: 20px;">← Voltar ao Painel</a>