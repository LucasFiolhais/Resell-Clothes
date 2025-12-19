<?php
// 📝 Carrega o cabeçalho
$path = 'goodies/header.php';
if (file_exists($path)) {
    require_once($path);
} else {
    die('Erro interno do servidor: por favor, tente novamente mais tarde (Código: 8).');
}

// Verifica permissões de administrador
if (!isset($_SESSION['username']) || $_SESSION['Tipo_USER'] !== 'Admin') {
    die('Acesso negado. Apenas administradores podem acessar esta página.');
	
}

// Inclui o arquivo DatabaseManager.php
$path = 'goodies/DatabaseManager.php';
if (file_exists($path)) {
    require_once($path);
} else {
    die('Erro interno do servidor: por favor, tente novamente mais tarde (Código: 11).');
}

// Conecta ao banco de dados
$myDb = establishDbConnection();
if (is_string($myDb)) {
    die('A aplicação web não está funcionando corretamente no momento. Por favor, tente novamente mais tarde.');
}

//  **Adicionar Nova Categoria**
if (isset($_POST['add_category'])) {
    $categoryName = trim($_POST['category_name'] ?? '');

    if (!empty($categoryName)) {
        $query = "INSERT INTO category (category_name) VALUES (?)";
        $type = ['s'];
        $arguments = [$categoryName];
        $result = executeQuery($myDb, $query, $type, $arguments);		
		
    } else {
        echo "<p class='error-message'>O nome da categoria não pode estar vazio.</p>";
    }
}

// **Apagar Categoria Sem Produtos**
if (isset($_POST['delete_category'])) {
    $categoryId = intval($_POST['category_id'] ?? 0);

    if ($categoryId > 0) {
        // Verifica se há produtos associados
        $checkQuery = "SELECT COUNT(*) AS total FROM product WHERE ID_CATEGORY = ?";
        $type = ['i'];
        $arguments = [$categoryId];
        $checkResult = executeQuery($myDb, $checkQuery, $type, $arguments);

        if ($checkResult) {
            $row = mysqli_fetch_assoc($checkResult);
            if ($row['total'] == 0) {
                $deleteQuery = "DELETE FROM category WHERE ID_CATEGORY = ?";
                $deleteResult = executeQuery($myDb, $deleteQuery, $type, $arguments);

            } else {
                echo "<p class='warning-message'>⚠️ Não é possível apagar categorias com produtos associados.</p>";
            }
        } else {
            echo "<p class='error-message'>Erro ao verificar produtos associados. Tente novamente.</p>";
        }
    } else {
        echo "<p class='error-message'>ID da categoria inválido.</p>";
    }
}

// **Listar Categorias**
$query = "SELECT ID_CATEGORY, category_name FROM category";
$result = executeQuery($myDb, $query, [], []);

if (!$result || is_string($result)) {
    die("<p class='error-message'> Erro ao carregar as categorias. Por favor, tente novamente mais tarde.</p>");
}

//  Fecha a conexão com a base de dados
endDbConnection($myDb);
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Gestão de Categorias</title>
    <style>
        .success-message { color: green; font-weight: bold; }
        .error-message { color: red; font-weight: bold; }
        .warning-message { color: orange; font-weight: bold; }

        table {
            width: 80%;
            margin: 20px auto;
            border-collapse: collapse;
        }

        table, th, td {
            border: 1px solid #ccc;
        }

        th, td {
            padding: 10px;
            text-align: center;
        }

        th {
            background-color: #f4f4f4;
        }

        form {
            margin: 0;
        }

        input[type="text"] {
            padding: 5px;
        }

        button {
            margin-top: 5px;
            padding: 5px 10px;
            cursor: pointer;
        }
    </style>
</head>
<body>
<br>
<br>
<br>
<br>
<br>
<h1>Gestão de Categorias</h1>

<h2>Adicionar Nova Categoria</h2>
<form action="" method="POST">
    <label for="category_name">Nome da Categoria:</label>
    <input type="text" id="category_name" name="category_name" required>
    <button type="submit" name="add_category">Adicionar</button>
</form>

<h2>Categorias Existentes</h2>
<table>
    <tr>
        <th>ID</th>
        <th>Nome</th>
        <th>Ação</th>
    </tr>
    <?php while ($category = mysqli_fetch_assoc($result)): ?>
        <tr>
            <td><?= htmlspecialchars($category['ID_CATEGORY']) ?></td>
            <td><?= htmlspecialchars($category['category_name']) ?></td>
            <td>
                <form action="" method="POST">
                    <input type="hidden" name="category_id" value="<?= htmlspecialchars($category['ID_CATEGORY']) ?>">
                    <button type="submit" name="delete_category">Apagar</button>
                </form>
            </td>
        </tr>
    <?php endwhile; ?>
</table>
</body>
</html>
