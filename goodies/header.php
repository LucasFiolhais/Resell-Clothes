<?php
	/* Esta página deve ser incluída em todas as páginas. Pode conter um menu de navegação. O utilizador deve ser verificado para saber se está autenticado ou não, e proceder em conformidade.
	 */

	// Verifica se uma sessão já está iniciada para evitar avisos.
	if (session_status() === PHP_SESSION_NONE) {
		session_start();
	}

	// Inclui o arquivo de configuração da aplicação para acessar variáveis importantes para este script.
	$path = 'goodies/ConfigApp.php';
	if (file_exists($path)) {
		require_once($path);
	} else {
		echo 'Erro interno do servidor: por favor, tente novamente mais tarde (Código: 8).';
		die();
	}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<!-- Link para o arquivo CSS externo -->
	<link rel="stylesheet" href="goodies/style_header.css">
</head>
<body>
<?php
	if (empty($_SESSION) || !array_key_exists('username', $_SESSION) || !isset($_SESSION['username'])) {
		// O utilizador não está autenticado. Isto pode ser um erro ou um acesso direto. Redireciona para a página de login ou verifica se esta página pode ser acessada sem autorização.

		// Qual é o script onde o cabeçalho está sendo incluído? Obtém a última posição do URL, remove o "." e o "php".
		$scriptName = explode('/', $_SERVER['PHP_SELF']);
		$scriptName = (explode('.', $scriptName[sizeof($scriptName) - 1]))[0];

		// Está na lista de páginas que não requerem autenticação disponível no ConfigApp.php?
		if (in_array($scriptName, $pages)) {
			// Permite que o utilizador navegue aqui - apresenta o menu simples
			echo '<header>';
			echo '<div class="container">';
			echo '<nav><ul>';
			echo '<li><a href="index.php">Início</a></li>';
			echo '<li><a href="loginForm.php">Login</a></li>';
			echo '<li><a href="registerForm.php">Registar</a></li>';
			echo '</ul></nav>';
			echo '</div>';
			echo '</header>';
		} else {
			// Define o erro a ser exibido no formulário de login
			$_SESSION['code'] = 101;

			// Redireciona o utilizador
			header('Location:loginForm.php');
			die();
		}
	} else {
		// O utilizador está autenticado. É, por acaso, um utilizador administrador?
		if (!empty($_SESSION) && array_key_exists('Tipo_USER', $_SESSION) && $_SESSION['Tipo_USER'] == "Admin") {
			// Bem, é um utilizador administrador. Pode acessar praticamente tudo, então apenas um menu de navegação diferente será apresentado.
			echo '<header>';
			echo '<div class="container">';
			echo '<div class="user-info">Olá ' . htmlspecialchars($_SESSION['username']) . '!</div>';
			echo '<nav><ul>';
			echo '<li><a href="index.php">Início</a></li>';
			echo '<li><a href="updateForm.php">Atualizar</a></li>';
			echo '<li><a href="admin.php">Administração</a></li>';
			echo '<li><a href="addCategory.php">Categorias</a></li>';
			echo '<li><a href="registerProduct.php">Produtos</a></li>';
			echo '<li><a href="cart.php">Carrinho</a></li>';
			echo '<li><a href="orders.php">Encomendas</a></li>';
			echo '<li><a href="updateOrderStatus.php">Atualizar Encomendas</a></li>';
			echo '<li><a href="logout.php">Sair</a></li>';
			echo '</ul></nav>';
			echo '</div>';
			echo '</header>';
		} else {
			// Utilizador regular: permite que o utilizador navegue aqui, a menos que seja uma das páginas limitadas à permissão de administrador - apresenta o menu completo

			// Qual é o script onde o cabeçalho está sendo incluído? Obtém a última posição do URL, remove o "." e o "php".
			$scriptName = explode('/', $_SERVER['PHP_SELF']);
			$scriptName = (explode('.', $scriptName[sizeof($scriptName) - 1]))[0];

			// Está na lista de páginas que não requerem autenticação disponível no ConfigApp.php?
			if (!in_array($scriptName, $adminPages)) {
				// Permite que o utilizador navegue aqui - apresenta o menu completo
				echo '<header>';
				echo '<div class="container">';
				echo '<div class="user-info">Olá ' . htmlspecialchars($_SESSION['username']) . '!</div>';
				echo '<nav><ul>';
				echo '<li><a href="index.php">Início</a></li>';
				echo '<li><a href="updateForm.php">Atualizar</a></li>';
				echo '<li><a href="cart.php">Carrinho</a></li>';
				echo '<li><a href="orders.php">Encomendas</a></li>';
				echo '<li><a href="logout.php">Sair</a></li>';
				echo '</ul></nav>';
				echo '</div>';
				echo '</header>';
			} else {
				// Redireciona o utilizador para a página index.php. Pode-se adicionar relatórios de erro se for necessário para a aplicação web.
				header('Location:index.php');
				die();
			}
		}
	}
?>
</body>
</html>

