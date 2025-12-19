<?php
// Inclui o cabeçalho
$path = 'goodies/header.php';
if (file_exists($path)) {
    require_once($path);
} else {
    echo 'Erro interno do servidor: por favor, tente novamente mais tarde (Código: 8).';
    die();
}

// Verifica permissões de administrador
if (!isset($_SESSION['username']) || $_SESSION['Tipo_USER'] !== 'Admin') {
    echo 'Acesso negado. Apenas administradores podem acessar esta página.';
    die();
}

// Inclui arquivos necessários
$bagPath = 'goodies/BagOfTricks.php';
$dbPath = 'goodies/DatabaseManager.php';

if (file_exists($bagPath)) {
    require_once($bagPath);
} else {
    echo 'Erro interno do servidor: falha ao carregar funções adicionais.';
    die();
}

if (file_exists($dbPath)) {
    require_once($dbPath);
} else {
    echo 'Erro interno do servidor: falha ao carregar o gerenciador de banco de dados.';
    die();
}

// Conecta ao banco de dados
$myDb = establishDbConnection();
if (is_string($myDb)) {
    echo "Erro ao conectar à base de dados.";
    die();
}

// Processa o formulário
if (!empty($_POST)) {
    $myFile = $_FILES;
    $validationResult = validateProductForm($_POST, $myFile);


    // Validação bem-sucedida
    if ($validationResult === true) {
        $alreadyInUse = array('product_name' => false);

        //  Verifica se já existe um produto com o mesmo nome
        $query = "SELECT Nome FROM product WHERE Nome = ?";
        $type = array('s');
        $arguments = array($_POST['product_name']);

        $result = executeQuery($myDb, $query, $type, $arguments);

        if (!empty($result) && is_string($result)) {
            echo $result;
        } elseif (!empty($result) && mysqli_num_rows($result) > 0) {
            $alreadyInUse['product_name'] = true;
        } else {
            //  Upload de imagem
            $imageNameBits = explode(".", $myFile['product_image']['name']);
            $newFileName = time() . "_" . md5($imageNameBits[0]) . "." . $imageNameBits[1];

            if (!move_uploaded_file($myFile['product_image']['tmp_name'], $imageFolder . "/" . $newFileName)) {
                echo "Erro fatal. A aplicação web não está funcionando corretamente. Por favor, tente novamente mais tarde.";
                die();
            }

            //  Inserir imagem
            $query = 'INSERT INTO image (Path) VALUES (?)';
            $type = array('s');
            $arguments = array($newFileName);

            $result = executeQuery($myDb, $query, $type, $arguments);

            if (!empty($result) && is_string($result)) {
                echo $result;
            } else {
                $imageId = mysqli_insert_id($myDb);

                // Inserir produto
                $query = 'INSERT INTO product (Nome, Descricao, Preco, Stock, ID_CATEGORY) VALUES (?, ?, ?, ?, ?)';
                $type = array('s', 's', 'd', 'i', 'i');
                $arguments = array(
                    $_POST['product_name'],
                    $_POST['product_desc'],
                    $_POST['preco'],
                    $_POST['stock'],
                    $_POST['categorias']
                );

                $result = executeQuery($myDb, $query, $type, $arguments);

                if (!empty($result) && is_string($result)) {
                    echo $result;
                } else {
                    $productId = mysqli_insert_id($myDb);

                    // 🔗 Associa imagem ao produto
                    $query = 'INSERT INTO product_image (ID_PRODUCT, ID_IMAGE) VALUES (?, ?)';
                    $type = array('i', 'i');
                    $arguments = array($productId, $imageId);

                    $result = executeQuery($myDb, $query, $type, $arguments);

                    if (!empty($result) && is_string($result)) {
                        echo $result;
                    } else {
                        $_SESSION['code'] = 200;
                        header('Location: index.php');
                        die();
                    }
                }
            }
        }
    } 	elseif (is_string($validationResult)) {
			// Caso a validação retorne uma string (erro crítico)
			echo "<p class='error-message'>{$validationResult}</p>";
		} elseif (is_array($validationResult)) {
			// Caso a validação retorne um array com erros específicos
			foreach ($validationResult as $field => $error) {
				if ($error[0]) { // Apenas exibe erros ativos
					echo "<p class='error-message'> {$error[1]}</p>";
				}
			}
		} else {
			echo "<p class='error-message'> Erro desconhecido na validação.</p>";
		}
   
	

    endDbConnection($myDb);
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registar Produto</title>
    <style>
        .success-message { color: green;  }
        .error-message { color: red;  }
        form { max-width: 600px; margin: auto; }
        input, textarea, select { display: block; width: 100%; margin-bottom: 10px; padding: 8px; }
    </style>
</head>
<body>
<br>
<br>
<br>
<br>
<br>
    <h1>Registrar Novo Produto</h1>
    <form action="" method="POST" enctype="multipart/form-data">
        <label>Nome do Produto:</label>
        <input type="text" name="product_name" required>

        <label>Descrição do Produto:</label>
        <textarea name="product_desc"></textarea>

        <label>Preço (€):</label>
        <input type="number" step="0.01" name="preco" required>

        <label>Stock:</label>
        <input type="number" name="stock" required>

        <label>Categoria:</label>
        <select name="categorias" required>
            <option value="">Selecione uma categoria</option>
            <?php
            $query = "SELECT ID_CATEGORY, category_name FROM category";
            $result = executeQuery($myDb, $query, [], []);
            while ($category = mysqli_fetch_assoc($result)) {
                echo "<option value='{$category['ID_CATEGORY']}'>{$category['category_name']}</option>";
            }
            ?>
        </select>

        <label>Imagem do Produto:</label>
        <input type="file" name="product_image" accept="image/*" required>

        <button type="submit">Registrar Produto</button>
    </form>
	
</body>
</html>
