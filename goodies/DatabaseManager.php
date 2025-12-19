<?php
/* Este arquivo contém funções que lidam com processos de gerenciamento de base de dados, 
	 * como conectar, desconectar e executar instruções preparadas.
	 */

	function establishDbConnection(){
		
		/* Os (quatro) parâmetros necessários para estabelecer uma conexão com o base de dados estão disponíveis no 
 	    * arquivo de configuração da aplicação (ConfigApp.php). Por isso, este arquivo deve ser incluído para continuar.
	    */
	   
	   // Primeiro, vamos verificar se o arquivo de configuração existe neste caminho
		$path = 'goodies/ConfigApp.php';		
		if (  file_exists($path) ){		
			require ($path);				
		}
		else{
			return('Erro interno do servidor: por favor, tente novamente mais tarde (Código: 10).');		
			die();
		}
		
		// Agora, prossiga para estabelecer uma conexão com o base de dados usando os parâmetros especificados. Antes, verifique se eles existem.
		if ( isset($dbHost) && isset($dbUsername) && isset($dbPassword) && isset($dbName) ){
			$myDb = mysqli_connect($dbHost, $dbUsername, $dbPassword, $dbName);		
		}
		else{
			// Os parâmetros necessários não estão definidos. Erro fatal.
			return ("Erro fatal: a aplicação não pode funcionar corretamente no momento (Código: 5). Por favor, tente novamente mais tarde.");		
		}

		/* Por fim, verifique se a conexão foi estabelecida ou se ocorreu algum erro. De fato, sem uma
		 * conexão com o base de dados, esta aplicação (e tantas outras atualmente) não pode continuar, sendo um erro fatal.
		 */

		if ( mysqli_connect_errno() ){
  			return ("Erro fatal: a aplicação não pode funcionar corretamente no momento (Código: 4). Por favor, tente novamente mais tarde.");
  			die();
  		}
  		else{
			// Conexão foi bem-sucedida: retorne o manipulador da conexão e prossiga.
			return($myDb);   		
  		}	
	} //fim da função

	// Esta função receberá uma consulta, a preparará com o mecanismo MySQL e também vinculará os argumentos. Em seguida, a executará e retornará os resultados, caso seja bem-sucedida.
	function executeQuery( $myDb, $query, $type, $arguments){
		
		/* Uma instrução preparada é mais segura e rápida quando se considera múltiplas execuções. No entanto, pode ser mais lenta quando usada apenas
		 * uma vez. É, de fato, uma questão de equilíbrio. Como boa prática, é recomendável usar instruções preparadas sempre, adicionando mais
		 * uma camada de proteção contra ataques de SQL Injection.
		 */
		 
		// Em uma instrução preparada, o primeiro passo é preparar a consulta no mecanismo MySQL. Estrutura e recursos serão alocados para a consulta
		$preparedQuery = mysqli_prepare($myDb, $query);
		
		// Se ocorrer um erro, o valor retornado será falso.
		if ( !$preparedQuery ){
			return("Erro fatal: esta operação está indisponível no momento. Por favor, tente novamente mais tarde. (Código: 1)");		
			die();
		}
		
		if (sizeof ($type) != 0 && sizeof ($arguments) != 0){
			// Agora os parâmetros - $arguments - devem ser vinculados aos placeholders ou tags	
			if ( ! mysqli_stmt_bind_param($preparedQuery, implode($type), ...$arguments) ){
				return("Erro fatal: esta operação está indisponível no momento. Por favor, tente novamente mais tarde (Código: 2).");		
				die();
			}
      }
      
      // Tudo ocorreu bem. A consulta pode ser executada agora.
	   if ( mysqli_stmt_execute($preparedQuery) ){
	   	// Obter resultado e armazená-lo
			$result = mysqli_stmt_get_result($preparedQuery);	
			
			// Libertar recursos alocados
			mysqli_stmt_close($preparedQuery);
			return ($result);
		}
		else{
			// Liberar recursos alocados
			mysqli_stmt_close($preparedQuery);
			return (false);
		}
	}

	// Esta função encerrará uma conexão ativa com o base de dados a pedido do utilizador.
   function endDbConnection( $myDb ) {
		/* Primeiro, vamos verificar se a conexão enviada está ativa. Observe que, se não estiver ativa e a opção global 
		 * mysqli.reconnect estiver ativa, será tentada uma nova conexão.
		 */
		 
		if ( mysqli_ping($myDb) ){
			// Está ativa. Vamos encerrar a conexão.
			if ( mysqli_close( $myDb ) ){		
				return(true);
			}
			else {
				return (false); // Isso significa que a conexão não foi encerrada. No entanto, em scripts curtos, isso realmente não é importante, pois, quando o script termina, a conexão será encerrada automaticamente.  
			}
		}
		else{
			return ("Nenhuma conexão ativa disponível.");		
		}  				 	
   }
   
   function executeInsertQuery($myDb, $query, $types, $params) {
    // Prepara a consulta
    $preparedQuery = mysqli_prepare($myDb, $query);

    if (!$preparedQuery) {
        return [
            'success' => false,
            'error' => 'Falha ao preparar a query: ' . mysqli_error($myDb),
        ];
    }

    // Vincula parâmetros
    if (!empty($types) && !empty($params)) {
        if (!mysqli_stmt_bind_param($preparedQuery, implode($types), ...$params)) {
            return [
                'success' => false,
                'error' => 'Falha ao vincular parâmetros: ' . mysqli_stmt_error($preparedQuery),
            ];
        }
    }

    // Executa a query
    if (!mysqli_stmt_execute($preparedQuery)) {
        mysqli_stmt_close($preparedQuery);
        return [
            'success' => false,
            'error' => 'Falha ao executar a query: ' . mysqli_stmt_error($preparedQuery),
        ];
    }

    // Retorna o ID inserido
    $insertedId = mysqli_insert_id($myDb);
    mysqli_stmt_close($preparedQuery);

    if ($insertedId > 0) {
        return [
            'success' => true,
            'insert_id' => $insertedId,
        ];
    } else {
        return [
            'success' => false,
            'error' => 'Nenhum ID foi gerado para o registo inserido.',
        ];
    }
}

?>