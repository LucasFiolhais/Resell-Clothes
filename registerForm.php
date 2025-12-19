<?php

	
	// Verifica as informações de autenticação e um possível menu de navegação
	$path = 'goodies/header.php';		
	if (  file_exists($path) ){
	   require_once($path);				
	}
	else{
	   echo 'Erro interno do servidor: por favor, tente novamente mais tarde (Código: 8).';
	   die();		
	}
	
	if ( !empty($_POST) ){ // A execução do código entra no if apenas após o primeiro envio do formulário (com ou sem dados nos campos do formulário).
		
		/* Vamos incluir a função de validação - BagOfTtricks.php -
		 * para que ela possa ser usada neste processo.	
	   */
	   # este arquivo está numa pasta - "goodies". Portanto, é necessário incluí-lo no caminho.
	   $path = 'goodies/BagOfTricks.php';		
		if (  file_exists($path) ){
		   require_once($path);
		}
		else{
		   echo 'Erro interno do servidor: por favor, tente novamente mais tarde (Código: 8).';
		   die();		
		}
		 	
		/* A validação dos campos do formulário é tratada por uma função projetada para cada formulário diferente e implementada no arquivo BagOfTricks.php.
		 * Essa função retornará um array se algum campo tiver conteúdo que não esteja conforme as regras estabelecidas ou um valor verdadeiro se
		 * nenhum erro for detectado.
		 * Ela aceita, como parâmetro, todos os dados que o Utilizador enviou via formulário, que estão organizados em $_POST e em $_FILES (arquivo de imagem).
	   */
	  
	  	$myFile = $_FILES; // O array será alterado.                                          
		$validationResult = validateRegisterForm ($_POST, $myFile);
	
		// Verifica se houve erros no preenchimento do formulário verificando o valor da variável $validationResult. 
		if ( !is_array($validationResult) && !is_string($validationResult) ){
			/* Não houve erros no formulário. Vamos prosseguir para inserir o novo Utilizador na tabela "users" da base de dados "aulas". Este pode
			 * ser acessado via navegador, utilizando http://localhost/adminer. A tabela "users" será criada na aula. Todos os processos
			 * relacionados à base de dados são implementados no arquivo DatabaseManager.php. Portanto, ele deve ser incluído.
		    */ 			
			$path = 'goodies/DatabaseManager.php';		
			if (  file_exists($path) ){
				 require_once($path);				
			}
			else{
			   echo 'Erro interno do servidor: por favor, tente novamente mais tarde (Código: 11).';
			   die();		
			}
		 
			// Estabelece uma conexão com a base de dados chamando a função apropriada que existe no arquivo DatabaseManager.php			
			$myDb = establishDbConnection();		
         
			// Verifica se ocorreu um erro fatal          
         if ( is_string( $myDb) ){
				// Não foi possível conectar à base de dados, o que constitui um erro fatal.         
         	echo "A aplicação web não está funcionando corretamente no momento. Por favor, tente novamente mais tarde.";
         	die();
         }
         else {
         	
         	// Primeiro: vamos verificar se já existe um nome de Utilizador ou um e-mail como os que o Utilizador está tentando registar.
				
				// Declara uma estrutura de erro simples para informar melhor o Utilizador sobre o que precisa ser alterado nos dados do registo.
				$alreadyInUse = array( 'username' => false, 'email' => false);         	
         	
         	// Agora, a consulta
         	$query = "SELECT username,email FROM user WHERE username=? OR email=?";
         	$type = array('s','s');
			$arguments = array($_POST['username'], $_POST['email']);
			
         	$result = executeQuery($myDb, $query, $type, $arguments);

         	// Verifica se ocorreu um erro (se o resultado for uma string)
         	if (!empty ($result) && is_string($result) ){
					echo $result;	
         	}
         	elseif( !empty($result) && !$result ){
         		echo "Esta operação não está disponível no momento. Por favor, tente novamente mais tarde (Código: 20)";
         		die();
         	}
         	else{
					
					// Consulta bem-sucedida. Existem resultados?					         		
					if ( mysqli_num_rows($result) > 0 ){
						
						// Não se sabe quantas linhas são retornadas. Podemos ter no máximo duas linhas, assumindo que tanto o e-mail quanto o nome de Utilizador estão em registos de Utilizadors diferentes.
						while ($row = mysqli_fetch_assoc($result) ){
						
							// É o e-mail ou o nome de Utilizador que são iguais neste registro?
							if ( $_POST['username'] == $row['username']){
								$alreadyInUse['username'] = true;
							}
							else {
								$alreadyInUse['email'] = true;
							}
						}	
						
					}         		
					else{
						// Agora, o novo utilizador pode ser registrado					
					
						/* No entanto, antes de fazer isso, a imagem válida deve ser movida para a pasta final a partir da pasta temporária
						 * Esteja ciente de que qualquer imagem deve ser salva fora da pasta raiz da web. Além disso, o arquivo 
						 * deve ser lido por uma função PHP e gravado no arquivo para ser armazenado com maior segurança.
						 */
						 
					    $imageNameBits = explode (".", $myFile['userImage']['name']); // Obtém tanto o nome original quanto a extensão em um array.
						$newFileName = time() . "_" . md5($imageNameBits[0]) . "." . $imageNameBits[1]; // Define um nome único para o arquivo a ser armazenado na tabela da base de dados.
						
						if ( !move_uploaded_file($myFile['userImage']['tmp_name'], $imageFolder . "/" . $newFileName )) {
   						 echo "Erro fatal. A aplicação web não está funcionando corretamente. Por favor, tente novamente mais tarde.";
   						 die();
						}
						
						// Prepara e executa a instrução MySQL para inserir os dados de um novo Utilizador na tabela 'users'.
						$query = 'INSERT INTO user(username, email, password, userImage) VALUES(?,?,?,?)';
						$type = array('s','s','s','s');
						$arguments = array($_POST['username'], $_POST['email'], md5($_POST['password']), $newFileName);
						$result = executeQuery( $myDb, $query, $type, $arguments);

         			// Verifica se ocorreu um erro (se o resultado for uma string)
         			if (!empty ($result) && is_string($result) ){
							echo $result;	
         			}
         			elseif( !empty($result) && !$result ){
         				echo "Esta operação não está disponível no momento. Por favor, tente novamente mais tarde (Código: 20)";
         				die();
         			}
         			else{
         				/* Vamos enviar uma mensagem via sessão. Isso não significa que o Utilizador está autenticado: apenas que a sessão está sendo usada como um canal de comunicação indireto.
         				 * Consulte o arquivo codes.php para entender o significado de cada mensagem. 
         				 */
         				
         				// Verifica se uma sessão já foi iniciada para evitar avisos.
							if (session_status() === PHP_SESSION_NONE) {
    							session_start();
   						}
   						
   						// Coloca o código de sucesso na sessão
         				$_SESSION['code'] = 100;
         				
         				// O Utilizador está registrado. Vai para a página inicial.
							header('Location:index.php');
							die();       	
         			}
         	
         			// Fecha a conexão ativa com a base de dados
         			$result = endDbConnection( $myDb );
						die();           	
					}	  
			}      
		}
		// Se a execução do código chegar a esta linha, significa que há pelo menos um erro no formulário. Ele será impresso perto do campo de formulário respectivo.	
	}
} // Fim do if principal
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="style_login.css">
</head>
<body>
<?php
	/* Imprime uma mensagem de erro se a função de validação do formulário estiver a ser usada incorretamente. Se isso acontecer, $validationResult
	 * terá uma string com o erro.
    */
    if ( !empty($validationResult) && is_string($validationResult) ){
    	echo $validationResult;
    }
?>
<form action="" method="POST" enctype="multipart/form-data">
  <label for="username">Nome de Utilizador:</label><br>
  <input type="text" id="username" name="username" value="<?php
  		// Verifica se este campo tem um erro reportado. Se não, coloca o valor que o Utilizador enviou.
  		if ( !empty($validationResult) && isset($validationResult['username']) && !$validationResult['username'][0] ){
  			echo $_POST['username'];
  		}  
  		elseif( !empty($alreadyInUse) && !$alreadyInUse['username'] ){
  			echo $_POST['username'];
  		}
  ?>"><br>
  <?php
  			// Exibe um erro de validação, se houver
  			if ( !empty($validationResult) && isset($validationResult['username']) && !$validationResult['username'][0] ){
  				echo "<span style='color:red;'>" . $validationResult['username'][1] . "</span>";
  			}
  			elseif( !empty($alreadyInUse) && $alreadyInUse['username'] ){
  				echo "<span style='color:red;'>O nome de Utilizador já está em uso. Tente outro.</span>";
  			}
  ?>
  <br>

  <label for="email">E-mail:</label><br>
  <input type="text" id="email" name="email" value="<?php 
  		// Exibe o valor introduzido anteriormente pelo Utilizador.
  		if ( !empty($validationResult) && isset($validationResult['email']) && !$validationResult['email'][0] ){
  			echo $_POST['email'];
  		}  
  		elseif( !empty($alreadyInUse) && !$alreadyInUse['email'] ){
  			echo $_POST['email'];
  		}
  ?>"><br>
  <?php
  			// Exibe um erro de validação, se houver
  			if ( !empty($validationResult) && isset($validationResult['email']) && !$validationResult['email'][0] ){
  				echo "<span style='color:red;'>" . $validationResult['email'][1] . "</span>";
  			}
  			elseif( !empty($alreadyInUse) && $alreadyInUse['email'] ){
  				echo "<span style='color:red;'>O e-mail já está em uso. Tente outro.</span>";
  			}
  ?>
  <br>

  <label for="password">Senha:</label><br>
  <input type="password" id="password" name="password" value="<?php
  		// Exibe o valor introduzido anteriormente pelo Utilizador.
  		if ( !empty($validationResult) && isset($validationResult['password']) && !$validationResult['password'][0] ){
  			echo $_POST['password'];
  		}  
  ?>"><br>
  <?php
  			// Exibe um erro de validação, se houver
  			if ( !empty($validationResult) && isset($validationResult['password']) && !$validationResult['password'][0] ){
  				echo "<span style='color:red;'>" . $validationResult['password'][1] . "</span>";
  			}
  ?>
  <br>
	<label for="rpassword">Repeat Password:</label><br>
  <input type="password" id="rpassword" name="rpassword"><br>
  <?php
      // check if this field has a reported error. If so, show it.
  		if ( !empty( $validationResult) && isset($validationResult['rpassword']) && $validationResult['rpassword'][0] ){
  			echo $validationResult['rpassword'][1] . '<br>';
  		}
  ?>	
  <label for="userImage">Imagem de Perfil:</label><br>
  <input type="file" id="userImage" name="userImage"><br>
  <?php
  			// Exibe um erro de validação, se houver
  			if ( !empty($validationResult) && isset($validationResult['userImage']) && !$validationResult['userImage'][0] ){
  				echo "<span style='color:red;'>" . $validationResult['userImage'][1] . "</span>";
  			}
  ?>
  <br>

  <input type="submit" value="Submeter">
</form>
</body>
</html>
