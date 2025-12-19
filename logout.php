<!DOCTYPE html>
<html>
<head>
  <title>Logout</title>
</head>
<body>
<?php

	// Este script não precisava de nenhum HTML, mas não há problema em incluí-lo.
	
	// Verifica as informações de autenticação e um possível menu de navegação.
	$path = 'goodies/header.php';		
	if (file_exists($path)) {
	   require_once($path);				
	}
	else {
	   echo 'Erro interno do servidor: por favor, tente novamente mais tarde (Código: 8).';
	   die();		
	}

	// Verifica se uma sessão já foi iniciada para evitar avisos.
	if (session_status() === PHP_SESSION_NONE) {
    	session_start();
   	}
   
	// A operação de logout é realmente simples, mas precisa ser feita corretamente.
	unset($_SESSION['id_users']);   
    unset($_SESSION['username']);
	session_destroy();
	$_SESSION = array();
	
	// Agora redireciona o utilizador para a página inicial.
	header('Location:index.php');
	die();
?>
</body>
</html>
