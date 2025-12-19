<?php
   //verificar as informações de autenticação e o menu de navegação potencial
	$path = 'goodies/header.php';		
	if (  file_exists($path) ){
	   require_once($path);				
	}
	else{
	   echo 'Erro interno do servidor: tente novamente mais tarde (Código: 8).';
	   die();		
	}

	/* É a primeira vez exibindo este formulário? Se sim, os campos devem ser preenchidos com dados do banco de dados, com exceção da senha. 
	 * Neste exemplo, o utilizador poderá alterar o endereço de e-mail e a senha.
   */
	if ( empty($_POST) ){
	
		$path = 'goodies/DatabaseManager.php';		
		if (  file_exists($path) ){
	   	require_once($path);				
		}
		else{
			echo 'Erro interno do servidor: tente novamente mais tarde (Código: 11).';
			die();		
		}
			
		// estabelecer uma conexão com o banco de dados chamando a função apropriada que existe no arquivo DatabaseManager.php			
		$myDb = establishDbConnection();		
         
		//verificar se ocorreu um erro fatal          
      if ( is_string( $myDb) ){
			// não foi possível conectar ao banco de dados, o que constitui um erro fatal.         
         echo "A aplicação web não pode funcionar corretamente no momento. Tente novamente mais tarde.";
         die();
      }
      else {
         // preparar e executar a instrução MySQL para obter os dados do utilizador autenticado (neste exemplo, apenas o endereço de e-mail é necessário).
			$query = 'SELECT email FROM user WHERE id_users=?';
			$type = array('i');
			$arguments = array($_SESSION['id_users']);
			$result = executeQuery( $myDb, $query, $type, $arguments);
         	
         // fechar a conexão ativa com o banco de dados
         endDbConnection( $myDb );
         	
         //verificar se ocorreu um erro (resultado é uma string)
         if ( is_string($result) ){
				echo $result;	
			   die(); 
         }
         elseif( !$result ){
         	echo "Esta operação está indisponível no momento. Tente novamente mais tarde (Código: 20)";
			   die(); 
         }
         else{
         	// mais uma verificação de segurança: foi retornada apenas uma linha da tabela, como deveria ser (apenas um utilizador com esse nome de utilizador/senha)?         		
				if ( mysqli_num_rows($result) != 1 ){
					echo "Erro fatal! Há algo errado com o sistema. Tente novamente mais tarde.";
					die();
				}         		
         	else{
         		// obter os dados do utilizador obtidos da tabela do banco de dados	
         		$row = mysqli_fetch_assoc($result);
					
					//salvar os dados em uma variável para serem usados no formulário. Este código está pronto para ser expandido, se necessário, para outros campos.
					$originalData = array('email' => $row['email']);
				}
			}
		}
	}//fim do if
	elseif ( !empty($_POST) ){ #A execução do código só entra no if após o primeiro envio do formulário (com ou sem dados nos campos do formulário).
		
		/* Vamos incluir a função de validação - BagOfTtricks.php - 
		 * para que ela possa ser usada neste processo.	
	   */
	   # este arquivo está em uma pasta - "goodies". Portanto, é necessário incluir o caminho.
	   $path = 'goodies/BagOfTricks.php';		
		if (  file_exists($path) ){
		   require_once($path);				
		}
		else{
		   echo 'Erro interno do servidor: tente novamente mais tarde (Código: 8).';
		   die();		
		}
		 	
		/* A validação dos campos do formulário é tratada por uma função projetada para cada formulário diferente e implementada no arquivo BagOfTricks.php.
		 * Essa função retornará um array se algum campo contiver dados que não estejam de acordo com as regras estabelecidas ou um valor verdadeiro se 
		 * não forem detectados erros.
		 * Ela aceita, como parâmetro, todos os dados que o utilizador enviou através do formulário, que estão bem organizados em $_POST.
	   */
	   
		$validationResult = validateUpdateForm ($_POST);
		
		//verificar se ocorreram erros no preenchimento do formulário verificando o valor na variável $validationResult. 
		if ( ! is_array($validationResult) && ! is_string($validationResult) ){
			/* não houve erros no formulário. Proceder para atualizar o utilizador existente na tabela "users" do banco de dados "aulas". 
			 * O banco de dados pode ser acessado pelo navegador, usando http://localhost/adminer. A tabela "users" será criada na aula. Todos os processos
			 * relacionados ao banco de dados estão implementados no arquivo DatabaseManager.php. Por isso, ele precisa ser incluído.
		    */ 			
			$path = 'goodies/DatabaseManager.php';		
		   if (  file_exists($path) ){
				 require_once($path);				
		   }
		   else{
			   echo 'Erro interno do servidor: tente novamente mais tarde (Código: 11).';
			   die();		
		   }
			
			// estabelecer uma conexão com o banco de dados chamando a função apropriada que existe no arquivo DatabaseManager.php			
			$myDb = establishDbConnection();		
         
			//verificar se ocorreu um erro fatal          
         if ( is_string( $myDb) ){
				// não foi possível conectar ao banco de dados, o que constitui um erro fatal.         
         	echo "A aplicação web não pode funcionar corretamente no momento. Tente novamente mais tarde.";
         	die();
         }
         else {
         	
				//o session tem os dados necessários para prosseguir (id do utilizador)?          	
         	if ( !array_key_exists('id_users', $_SESSION) || !isset($_SESSION['id_users']) ){
         		//se não, terminar aqui.
         		echo "Ocorreu um erro fatal. Tente novamente mais tarde. (código: 22)";
         		die();
         	}
         	
    			// preparar e executar uma instrução MySQL para atualizar os dados do utilizador na tabela "users" no banco de dados.
				$query = 'UPDATE user SET email=?, password=? WHERE id_users=?';
				$type = array('s','s','i');
				$arguments = array($_POST['email'], md5($_POST['password']), $_SESSION['id_users']);
         	$result = executeQuery( $myDb, $query, $type, $arguments);
         	       		
         	//verificar se ocorreu um erro (resultado é uma string)
         	if (!empty ($result) && is_string($result) ){
					echo $result;	
         	}
         	elseif( !empty($result) && !$result ){
         		echo "Esta operação está indisponível no momento. Tente novamente mais tarde (Código: 20)";
         		die();
         	}
         	else{
					// tudo correu bem. Retornar o utilizador para a página inicial com uma mensagem de sucesso
					$_SESSION['code'] = 102;
					$result = endDbConnection( $myDb );
					header('Location:index.php');
					die();
         	}
         	
         	// fechar a conexão ativa com o banco de dados
         	$result = endDbConnection( $myDb );
				die();         
			}      
		}
		// se a execução do código chegar a essa linha, significa que há pelo menos um erro no formulário. Ele será impresso perto do campo respectivo do formulário.	
	} //fim do principal if
?>
<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="style_login.css">
<style>
		
    </style>
</head>
<body>
<?php
	/* imprimir uma mensagem de erro se a função de validação do formulário estiver sendo usada incorretamente. Se isso acontecer, $validationResult
	 * terá uma string com o erro.
    */
    if ( !empty($validationResult) && is_string($validationResult) ){
    	echo $validationResult;
    }
?>
<form action="" method="POST">
  <label for="email">E-mail:</label><br>
  <input type="text" id="email" name="email" value="<?php
  		// verificar se este campo tem um erro reportado. Se não, colocar o valor que o utilizador enviou.
  		if ( (!empty($validationResult) && isset($validationResult['email']) && !$validationResult['email'][0]) ){
  			echo $_POST['email'];
  		}
  		elseif( !empty($originalData) && array_key_exists('email', $originalData) && isset($originalData['email']) ){
  			echo $originalData['email'];
  			unset ($originalData['email']);
  		} 
  ?>"><br>
  <?php
      // verificar se este campo tem um erro reportado. Se sim, mostrá-lo.
  		if ( !empty( $validationResult) && isset($validationResult['email']) && $validationResult['email'][0] ){
  			echo $validationResult['email'][1] . '<br>';
  		}
  ?>
  <label for="password">Senha:</label><br>
  <input type="password" id="password" name="password"><br>
  <?php
      // verificar se este campo tem um erro reportado. Se sim, mostrá-lo.
  		if ( !empty($validationResult) && isset($validationResult['password']) && $validationResult['password'][0] ){
  			echo $validationResult['password'][1] . '<br>';
  		}
  ?>
  <label for="rpassword">Repetir Senha:</label><br>
  <input type="password" id="rpassword" name="rpassword"><br>
  <?php
      // verificar se este campo tem um erro reportado. Se sim, mostrá-lo.
  		if ( !empty( $validationResult) && isset($validationResult['rpassword']) && $validationResult['rpassword'][0] ){
  			echo $validationResult['rpassword'][1] . '<br>';
  		}
	?>
  <input type="submit" value="Enviar">
</form> 
</body>
</html>
