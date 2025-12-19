<?php
// Inclui o cabeçalho
$path = 'goodies/header.php';
if (file_exists($path)) {
    require_once($path);
} else {
    die('Erro interno do servidor: por favor, tente novamente mais tarde (Código: 8).');
}

// Verifica permissões de administrador
if (!isset($_SESSION['username'])) {
    die('Acesso negado. Você precisa estar logado para acessar esta página.');
} else {
    if ($_SESSION['Tipo_USER'] !== 'Admin') {
        die('Acesso negado. Apenas administradores podem acessar esta página.');
    }
}

//  Inclui o arquivo DatabaseManager.php
$path = 'goodies/DatabaseManager.php';
if (file_exists($path)) {
    require_once($path);
} else {
    die('Erro interno do servidor: por favor, tente novamente mais tarde (Código: 11).');
}

// Conecta a base de dados
$myDb = establishDbConnection();
if (is_string($myDb)) {
    die('Erro ao conectar à base de dados. Por favor, tente novamente mais tarde.');
}

// ️ Processa atualização de tipo de utilizador
if (!empty($_POST)) {
    if (isset($_POST['id_users']) && isset($_POST['user_type'])) {
        $userId = intval($_POST['id_users']); // Garante que é um número inteiro
        $userType = in_array($_POST['user_type'], ['Client', 'Admin']) ? $_POST['user_type'] : 'Client';

        // Impede que um administrador remova seu próprio privilégio
        if ($userId === $_SESSION['id_users']) {
            if ($userType !== 'Admin') {
                echo "<p class='error-message'> Não pode remover seu próprio privilégio de administrador!</p>";
            } else {
                // Atualiza tipo de utilizador
                $query = "UPDATE user SET Tipo_USER = ? WHERE id_users = ?";
                $stmt = mysqli_prepare($myDb, $query);

                if ($stmt) {
                    mysqli_stmt_bind_param($stmt, 'si', $userType, $userId);
                    $execute = mysqli_stmt_execute($stmt);

                    if ($execute) {
                        if (mysqli_stmt_affected_rows($stmt) > 0) {
                            echo "<p class='success-message'>✅ Tipo de utilizador atualizado com sucesso!</p>";

                            // Atualiza sessão se o próprio utilizador alterou seu tipo
                            if ($userId === $_SESSION['id_users']) {
                                $_SESSION['Tipo_USER'] = $userType;
                            }
                        } else {
                            echo "<p class='warning-message'> Nenhuma alteração foi feita. O tipo de utilizador já estava definido como '$userType'.</p>";
                        }
                    } else {
                        echo "<p class='error-message'> Erro ao executar a query de atualização. Erro: " . mysqli_stmt_error($stmt) . "</p>";
                    }

                    mysqli_stmt_close($stmt);
                } else {
                    echo "<p class='error-message'> Falha ao preparar a query. Erro: " . mysqli_error($myDb) . "</p>";
                }
            }
        } else {
            // Atualiza tipo de outro utilizador
            $query = "UPDATE user SET Tipo_USER = ? WHERE id_users = ?";
            $stmt = mysqli_prepare($myDb, $query);

            if ($stmt) {
                mysqli_stmt_bind_param($stmt, 'si', $userType, $userId);
                $execute = mysqli_stmt_execute($stmt);

                if ($execute) {
                    if (mysqli_stmt_affected_rows($stmt) > 0) {
                        echo "<p class='success-message'>Tipo de utilizador atualizado com sucesso!</p>";
                    } else {
                        echo "<p class='warning-message'>Nenhuma alteração foi feita. O tipo de utilizador já estava definido como '$userType'.</p>";
                    }
                } else {
                    echo "<p class='error-message'> Erro ao executar a query de atualização. Erro: " . mysqli_stmt_error($stmt) . "</p>";
                }

                mysqli_stmt_close($stmt);
            } else {
                echo "<p class='error-message'>Falha ao preparar a query. Erro: " . mysqli_error($myDb) . "</p>";
            }
        }
    } else {
        echo "<p class='error-message'> Parâmetros inválidos no formulário.</p>";
    }
}

// ️ Consulta para listar os utilizadores
$query = "SELECT id_users, username, email, Tipo_USER FROM user";
$result = executeQuery($myDb, $query, [], []);

//  Valida o resultado da consulta
if (is_string($result)) {
    echo "<p class='error-message'> Erro na consulta SQL: {$result}</p>";
    endDbConnection($myDb);
    die();
}

if ($result === false) {
    echo "<p class='error-message'> Erro ao executar a query. Verifique a base de dados.</p>";
    endDbConnection($myDb);
    die();
}

//  Garante que há resultados
if (!$result || mysqli_num_rows($result) === 0) {
    echo "<p class='warning-message'> Nenhum utilizador encontrado.</p>";
}

//  Fecha conexão com a base de dados
endDbConnection($myDb);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Gestão de Utilizadores</title>
    <style>
        .success-message { color: green; margin-top: 10px; }
        .error-message { color: red; margin-top: 10px; }
        .warning-message { color: orange; margin-top: 10px; }

        table { width: 80%; margin: 20px auto; border-collapse: collapse; }
        table, th, td { border: 1px solid #ccc; }
        th, td { padding: 10px; text-align: center; }
        th { background-color: #f4f4f4; }

        select { padding: 5px; }
        button { margin-top: 5px; padding: 5px 10px; cursor: pointer; }
    </style>
</head>
<body>
<h1>Gestão de Utilizadores</h1>
<p>Aqui, pode visualizar e alterar os tipos de utilizador.</p>

<table>
    <tr>
        <th>ID</th>
        <th>Nome</th>
        <th>Email</th>
        <th>Tipo de Utilizador</th>
        <th>Ação</th>
    </tr>
    <?php if ($result && mysqli_num_rows($result) > 0): ?>
        <?php while ($user = mysqli_fetch_assoc($result)) : ?>
            <tr>
                <td><?= htmlspecialchars($user['id_users']) ?></td>
                <td><?= htmlspecialchars($user['username']) ?></td>
                <td><?= htmlspecialchars($user['email']) ?></td>
                <td><?= htmlspecialchars($user['Tipo_USER']) ?></td>
                <td>
                    <form action="" method="POST">
                        <input type="hidden" name="id_users" value="<?= htmlspecialchars($user['id_users']) ?>">
                        <select name="user_type">
                            <option value="Client" <?= $user['Tipo_USER'] === 'Client' ? 'selected' : '' ?>>Client</option>
                            <option value="Admin" <?= $user['Tipo_USER'] === 'Admin' ? 'selected' : '' ?>>Admin</option>
                        </select>
                        <button type="submit">Atualizar</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
    <?php endif; ?>
</table>
</body>
</html>