<?php
	// Este é o arquivo de configuração da aplicação, onde todos os parâmetros relevantes são definidos.
	
	// Parâmetros de validação
	$minUsername = 4;
	$maxUsername = 32;
	$minPassword = 6;
	$maxPassword = 48;
	$fileSize = 5 * 1024 * 1024; # Tamanho máximo permitido por arquivo enviado: 5 MB
	$imageFolder = $_SERVER['DOCUMENT_ROOT'] . "/trabalho/images"; # Pasta onde as imagens serão armazenadas
	
	// Parâmetros da base de dados
	$dbHost = "localhost";
	$dbUsername = "karen";
	$dbPassword = "cm";
	$dbName = "trabalho";
	
	// Páginas que podem ser visualizadas sem autenticação -
	$pages = array(
		'index',
		'registerForm',
		'loginForm',
		'registerProduct',
	);
	
	// Páginas que podem ser visualizadas apenas pelo utilizador administrador.
	$adminPages = array('admin');

	// Tipos de arquivo permitidos para a imagem do utilizador ao se registrar na aplicação web
	// Define os tipos MIME permitidos
	$allowedTypes = array(
		'image/jpeg',
		'image/jpg',
		'image/png',
	); 
?>
