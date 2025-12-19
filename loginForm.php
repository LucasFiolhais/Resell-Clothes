<?php
   // Verifica as informações de autenticação e o menu de navegação potencial
	$path = 'goodies/header.php';		
	if (file_exists($path)) {
	   require_once($path);				
	} else {
	   echo 'Erro interno do servidor: por favor, tente novamente mais tarde (Código: 8).';
	   die();		
	}
	
	if (!empty($_POST)) { // O código só entra neste "if" após uma submissão inicial do formulário (com ou sem dados nos campos).
		
		/* Incluímos a função de validação - BagOfTricks.php - 
		 * para que ela possa ser usada neste processo.	
		 */
	   // Este arquivo está em uma pasta chamada "goodies". Portanto, precisa ser incluído no caminho.
	   $path = 'goodies/BagOfTricks.php';		
		if (file_exists($path)) {
		   require_once($path);				
		} else {
		   echo 'Erro interno do servidor: por favor, tente novamente mais tarde (Código: 8).';
		   die();		
		}
		 	
		/* A validação dos campos do formulário é realizada por uma função projetada para cada formulário diferente, 
		 * implementada no arquivo BagOfTricks.php.
		 * Essa função retorna um array se algum campo contiver conteúdo que não esteja em conformidade com as regras estabelecidas,
		 * ou um valor "true" se não forem detectados erros.
		 * Aceita, como parâmetro, todos os dados que o utilizador submeteu via formulário, contidos no $_POST.
		 */
	   
		$validationResult = validateLoginForm($_POST);
		
		// Verifica se houve erros no preenchimento do formulário, verificando o valor da variável $validationResult. 
		if (!is_array($validationResult) && !is_string($validationResult)) {
			/* Não houve erros no formulário. Prossegue para inserir o novo utilizador na tabela "users" da base de dados "aulas". 
			 * A base de dados pode ser acessada via browser em http://localhost/adminer. A tabela "users" será criada em aula.
			 * Todos os processos relacionados à base de dados estão implementados no arquivo DatabaseManager.php. Por isso, 
			 * ele deve ser incluído.
		    */ 			
			$path = 'goodies/DatabaseManager.php';		
		   if (file_exists($path)) {
				 require_once($path);				
		   } else {
			   echo 'Erro interno do servidor: por favor, tente novamente mais tarde (Código: 11).';
			   die();		
		   }
			
			// Estabelece uma conexão com a base de dados chamando a função adequada que existe no DatabaseManager.php			
			$myDb = establishDbConnection();		
         
			// Verifica se ocorreu um erro fatal          
         if (is_string($myDb)) {
				// Não foi possível conectar à base de dados, o que constitui um erro fatal.         
         	echo "A aplicação web não está a funcionar corretamente no momento. Por favor, tente novamente mais tarde.";
         	die();
         } else {
         	// Prepara e executa o comando MySQL para procurar o par username/password na tabela 'users'.
				$query = 'SELECT id_users,username,email,Tipo_USER FROM user WHERE username=? AND password=?';
				$type = array('s','s');
				$arguments = array($_POST['username'], md5($_POST['password']));
				$result = executeQuery($myDb, $query, $type, $arguments);
         	
         	// Fecha a conexão ativa com a base de dados
         	endDbConnection($myDb);
         	
         	// Verifica se ocorreu um erro (resultado é uma string)
         	if (is_string($result)) {
					echo $result;	
				   die(); 
         	} elseif (!$result) {
         		echo "Esta operação está indisponível no momento. Por favor, tente novamente mais tarde (Código: 20)";
				   die(); 
         	} else {
					// Outra verificação de segurança: foi retornada apenas uma linha da tabela, como deveria?         		
					if (mysqli_num_rows($result) > 1) {
						echo "Erro fatal! Algo está errado com o sistema. Por favor, tente novamente mais tarde.";
						
						// Fecha a conexão ativa com a base de dados
         			$result = endDbConnection($myDb); 						
						die();
					} elseif (mysqli_num_rows($result) == 0) {
						// A combinação username/password não existe na tabela da base de dados. O utilizador deve ser informado.
						echo "Username/password inválidos. <br>";							
					} else {
         			// Obtém os dados do utilizador obtidos da tabela da base de dados	
         			$row = mysqli_fetch_assoc($result);
						
						// Cria uma nova sessão para o utilizador autenticado
					   session_start();
					
						// Salva na variável mágica $_SESSION o ID e o username do utilizador para uso futuro, enquanto a sessão durar.					
						$_SESSION['id_users'] = $row['id_users'];
						$_SESSION['username'] = $row['username'];
						$_SESSION['Tipo_USER'] = $row['Tipo_USER'];
						 
						// Redireciona o utilizador para a página inicial, por exemplo
						header('Location:index.php');
						die();
					}
         	}
			}      
		}
		// Se o código atingir esta linha, significa que há pelo menos um erro no formulário. Ele será mostrado próximo ao respetivo campo.	
	} // Fim do if principal
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="style_login.css">
</head>
<body>
<?php
	/* Exibe uma mensagem de erro se a função de validação do formulário estiver sendo usada incorretamente.
	 * Nesse caso, $validationResult terá uma string com o erro.
    */
    if (!empty($validationResult) && is_string($validationResult)) {
    	echo $validationResult;
    }

	// Foi um redirecionamento devido a uma tentativa de acesso não autorizado?
	// Verifica se uma sessão já foi iniciada para evitar avisos.
	if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
   
	// Verifica se há alguma mensagem para apresentar ao utilizador.
	if (!empty($_SESSION) && array_key_exists('code', $_SESSION) && isset($_SESSION['code'])) {
		// Precisamos do "código de mensagens"
		require_once('goodies/codes.php');
		
		// Este código é válido?
		if (isset($codes[$_SESSION['code']])) {
			echo $codes[$_SESSION['code']] . '<br>';
			
			// Limpa a variável e o respetivo código para evitar mensagens repetidas.
			unset($_SESSION['code']);
		}
	}
?>
<form action="" method="POST">
  <label for="username">Nome de Utilizador:</label><br>
  <input type="text" id="username" name="username" value="<?php
  		// Verifica se este campo tem um erro reportado. Se não, exibe o valor que o utilizador submeteu.
  		if (!empty($validationResult) && isset($validationResult['username']) && !$validationResult['username'][0]) {
  			echo $_POST['username'];
  		}  
  ?>"><br>
  <?php
  		// Verifica se este campo tem um erro reportado. Se sim, exibe-o.
  		if (!empty($validationResult) && isset($validationResult['username']) && $validationResult['username'][0]) {
  			echo $validationResult['username'][1] . '<br>';
  		}  
  ?>
 
  <label for="password">Palavra-passe:</label><br>
  <input type="password" id="password" name="password"><br>
  <?php
      // Verifica se este campo tem um erro reportado. Se sim, exibe-o.
  		if (!empty($validationResult) && isset($validationResult['password']) && $validationResult['password'][0]) {
  			echo $validationResult['password'][1] . '<br>';
  		}
  ?>
  <input type="submit" value="Submeter">
</form> 

</body>
</html>
