<?php 
/* Este arquivo contém todas as funções de validação. Além disso, cada formulário diferente
 * na aplicação web em desenvolvimento terá sua própria função de validação, responsável por chamar cada 
 * validação individual para os diferentes campos enviados.
 * Como cada função de validação pode depender de parâmetros disponíveis na aplicação web - descritos no arquivo ConfigApp.php -
 * há necessidade de incluí-los no código sempre que necessário.
 
 
 */
	
function validateUpdateForm($data){
		
	# Caminho relativo para a pasta raiz do site.
	$path = 'goodies/ConfigApp.php';	
	if (file_exists($path)){
		require($path);				
   }
	else{
	   return 'Erro interno no servidor: por favor, tente novamente mais tarde (Código: 9).';
	   die();		
	}	
	
	/* Declara um array de erros para acompanhar possíveis erros nos campos do formulário enviado. Essa estrutura
	 * permitirá retornar ao chamador da função a lista de possíveis erros, para que possam ser exibidos ao usuário
	 * sempre e onde for considerado mais eficaz. Por essa razão, cada campo do formulário deve estar neste array.
	 */
	$errors = array( 'email' => array(false, "Formato de email inválido"),
	                 'password' => array(false, "A senha deve ter entre $minPassword e $maxPassword caracteres alfanuméricos ou especiais."),
	                 'rpassword' => array(false, "As senhas não coincidem.")
            	   );
   
	// Verifica se o array de dados enviado contém todos os campos necessários.
	if (count(array_diff(array_keys($errors), array_keys($data))) != 0){
		// Os arrays não são iguais. Algo está errado e os campos obrigatórios, o array de erros e os dados enviados precisam ser corrigidos.		
		return ('Os dados do formulário não correspondem. Por favor, corrija-os.');
	}
   			   			
	// Começa a validar os campos assumindo que todos são obrigatórios. Além disso, declara um sinalizador de erro para simplificar no final.
	$flag = false; # Nenhum campo do formulário tem erros atualmente
	if (!validateEmail($data['email'])){
		// O campo de email é inválido.
		$errors['email'][0] = true;
		$flag = true;
	}			
			
	if (!validatePassword($data['password'], $minPassword, $maxPassword)){
		// O campo de senha não está correto.
	   $errors['password'][0] = true;
		$flag = true;
	}
	elseif ($data['rpassword'] != $data['password']){
		// O conteúdo de rpassword não é igual ao de password, o que é um erro.
		$errors['rpassword'][0] = true;
		$flag = true;
	}			
						
	// O formulário foi validado. Existe algum erro? Se sim, retorna o array de erros. Caso contrário, retorna true.
	if (!$flag){
		return(true);
	}	
	else{
		return($errors);			
	}
} 
  
function validateLoginForm($data){
		
	# Caminho relativo para a pasta raiz do site. 
	$path = 'goodies/ConfigApp.php';	
	if (file_exists($path)){
		require($path);				
	}
	else{
		return 'Erro interno no servidor: por favor, tente novamente mais tarde (Código: 9).';
		die();		
	}	

	/* Declara um array de erros para acompanhar possíveis erros nos campos do formulário enviado. Essa estrutura
	 * permitirá retornar ao chamador da função a lista de possíveis erros, para que possam ser exibidos ao usuário
	 * sempre e onde for considerado mais eficaz. Por essa razão, cada campo do formulário deve estar neste array.
	 */
	$errors = array( 'username' => array(false, "Nome de usuário inválido: deve ter entre $minUsername e $maxUsername caracteres alfabéticos e/ou numéricos. O caractere underscore também é permitido."),
	                 'password' => array(false, "A senha deve ter entre $minPassword e $maxPassword caracteres alfanuméricos ou especiais.")
            	   );
   
	// Verifica se o array de dados enviado contém todos os campos necessários.
	if (count(array_diff(array_keys($errors), array_keys($data))) != 0){
		// Os arrays não são iguais. Algo está errado e os campos obrigatórios, o array de erros e os dados enviados precisam ser corrigidos.		
		return ('Os dados do formulário não correspondem. Por favor, corrija-os.');
	}
   			   			
	// Começa a validar os campos assumindo que todos são obrigatórios. Além disso, declara um sinalizador de erro para simplificar no final.
	$flag = false; # Nenhum campo do formulário tem erros atualmente
	if (!validateUsername($data['username'], $minUsername, $maxUsername)){
		// O campo de nome de usuário não está correto.
		$errors['username'][0] = true;
		$flag = true;
	}			
						
	if (!validatePassword($data['password'], $minPassword, $maxPassword)){
		// O campo de senha não está correto.
		$errors['password'][0] = true;
		$flag = true;
	}

	// O formulário foi validado. Existe algum erro? Se sim, retorna o array de erros. Caso contrário, retorna true.
	if (!$flag){
		return(true);
	}	
	else{
		return($errors);			
	}
}
	
function validateRegisterForm($data, $files){
		
	# Caminho relativo para a pasta raiz do site.
	$path = 'goodies/ConfigApp.php';	
	if (file_exists($path)){
		require($path);				
	}
	else{
		return 'Erro interno no servidor: por favor, tente novamente mais tarde (Código: 9).';
		die();		
	}	

	/* Declara um array de erros para acompanhar possíveis erros nos campos do formulário enviado. Essa estrutura
	 * permitirá retornar ao chamador da função a lista de possíveis erros, para que possam ser exibidos ao usuário
	 * sempre e onde for considerado mais eficaz. Por essa razão, cada campo do formulário deve estar neste array.
	 */
	$errors = array( 'username' => array(false, "Nome de usuário inválido: deve ter entre $minUsername e $maxUsername caracteres alfabéticos e/ou numéricos. O caractere underscore também é permitido."),
	                 'email' => array(false, 'Formato de email inválido.'),
	                 'password' => array(false, "A senha deve ter entre $minPassword e $maxPassword caracteres alfanuméricos ou especiais."),
	                 'rpassword' => array(false, "As senhas não coincidem.")
					);
	
	// Verifica se o array de dados enviado contém todos os campos necessários.
	if (count(array_diff(array_keys($errors), array_keys($data))) != 0){
		// Os arrays não são iguais. Algo está errado e os campos obrigatórios, o array de erros e os dados enviados precisam ser corrigidos.		
		return ('Os dados do formulário não correspondem. Por favor, corrija-os.');
	}
   			   			
	// Começa a validar os campos assumindo que todos são obrigatórios. Além disso, declara um sinalizador de erro para simplificar no final.
	$flag = false; # Nenhum campo do formulário tem erros atualmente
	if (!validateUsername($data['username'], $minUsername, $maxUsername)){
		// O campo de nome de usuário não está correto.
		$errors['username'][0] = true;
		$flag = true;
	}
	if (!validatePassword($data['password'], $minPassword, $maxPassword)){
		// O campo de senha não está correto.
		$errors['password'][0] = true;
		$flag = true;
	}
	elseif ($data['rpassword'] != $data['password']){
		// O conteúdo de "rpassword" não é igual ao da senha, o que é um erro.
		$errors['rpassword'][0] = true;
		$flag = true;
	}	

	// Agora vamos validar o arquivo de imagem escolhido pelo usuário no formulário de registro. Isso retornará um array de erro para tornar a função mais flexível.
	if (is_string($result = validateUserImage($tempImage = array_pop($files), $fileSize, $allowedTypes))){
		// O campo de imagem é inválido. Vamos adicionar um campo de erro ao array de erros com a mensagem adequada a ser exibida ao usuário.					
		$errors['userImage'] = array(true, $result);
		$flag = true;

		// Se o campo de imagem do formulário não estiver vazio, o arquivo temporário carregado no servidor deve ser apagado.
		if (!empty($tempImage['tmp_name'])){
			unlink($tempImage['tmp_name']); # Apaga a imagem inválida da pasta temporária.
		}
	}

	// O formulário foi validado. Existe algum erro? Se sim, retorna o array de erros. Caso contrário, retorna true.
	if (!$flag){
		return(true);
	}	
	else{
		return($errors);			
	}
}

/* ----------------------------------------------------------------------------------- */
// Funções de validação individuais que podem ser usadas várias vezes, se necessário.

// Esta função valida uma imagem com relação ao seu tamanho e tipo de arquivo. Outras validações podem ser adicionadas, bem como mensagens de erro personalizadas.
function validateUserImage($image, $size, $type){
	// A função recebe um array $_FILES para a imagem a ser validada. Dessa forma, ela pode funcionar com qualquer tipo de formulário.

	// Primeiro, verifica se houve um erro ao carregar o arquivo para a pasta temporária.
	if ($image['error'] !== UPLOAD_ERR_OK){
		return("Erro ao carregar o arquivo. Por favor, tente novamente.");
		die();
	}
	elseif ($image['size'] > $size) { # Agora, vamos verificar o tamanho.
		return("O tamanho do arquivo não pode exceder 5MB."); # Note que o tamanho pode ser automaticamente incluído na mensagem de erro. Para isso, uma conversão deve ser feita. Parece estar fora do escopo do UC.
		die();
	}
	elseif (!in_array(mime_content_type($image['tmp_name']), $type)) { # Agora, o tipo de arquivo será verificado.
		return("O tipo de arquivo não é permitido. Apenas arquivos JPG e PNG, por favor."); # Novamente, isso pode ser otimizado para ser mais genérico.
		die();
	}
	else{
		# O arquivo é válido.
		return(false);
	}
}

// Esta função valida um email com relação à sua estrutura.
function validateEmail($email){
	return(filter_var($email, FILTER_VALIDATE_EMAIL)); # Retorna false se o email for inválido e true se for válido.		
}

// Esta função valida um nome de usuário com relação à sua estrutura e conteúdo.
function validateUsername($username, $min, $max){
	$expression = '/^(?=[\W]+[a-zA-Z0-9]|[a-zA-Z0-9]+[\W]|[a-zA-Z0-9]+).{'. $min . ',' . $max .'}$/';
	/* Esta expressão foi adaptada da web: permite caracteres especiais e alfanuméricos, mas apenas 
	 * na presença um do outro. Note que \W significa qualquer caractere não alfanumérico (incluindo especiais).
	 */ 
	return(preg_match($expression, $username));		
}

// Esta função valida uma senha com relação à estrutura e conteúdo.
function validatePassword($password, $min, $max){
	$expression = '/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{' . $min . ',' . $max . '}$/';
	/* Esta expressão foi obtida em https://uibakery.io/regex-library/password-regex-php
	 * Leia a fonte para obter uma explicação mais detalhada, mas, em resumo, a senha deve conter:
	 * uma letra maiúscula, uma minúscula, um número e um caractere especial, além de estar entre um mínimo e um máximo de caracteres.
	 */
	return(preg_match($expression, $password));	
}

function validateAndUploadImage($image, $uploadDir, $maxSize, $allowedTypes) {
    if ($image['error'] !== UPLOAD_ERR_OK) {
        return "Erro ao fazer upload da imagem. Código de erro: " . $image['error'];
    }

    // Verifica o tamanho da imagem
    if ($image['size'] > $maxSize) {
        return "A imagem excede o tamanho máximo permitido de " . ($maxSize / 1024 / 1024) . " MB.";
    }

    // Verifica o tipo de arquivo
    $fileType = mime_content_type($image['tmp_name']);
    if (!in_array($fileType, $allowedTypes)) {
        return "O tipo de arquivo não é permitido. Apenas " . implode(", ", $allowedTypes) . " são aceitos.";
    }

    // Gera um nome único para a imagem
    $fileName = uniqid() . "_" . basename($image['name']);
    $filePath = $uploadDir . $fileName;

    // Move a imagem para o pasta de upload
    if (move_uploaded_file($image['tmp_name'], $filePath)) {
        return $filePath; // Retorna o caminho completo da imagem em caso de sucesso
    } else {
        return "Erro ao mover a imagem para o pasta de upload.";
    }
}



function validateProductForm($data, $files) {
    # Caminho relativo para a pasta raiz do site.
    $path = 'goodies/ConfigApp.php';
    if (file_exists($path)) {
        require($path);
    } else {
        return 'Erro interno no servidor: por favor, tente novamente mais tarde (Código: 9).';
        die();
    }

    /* Declara um array de erros para acompanhar possíveis erros nos campos do formulário enviado.
     * Essa estrutura permitirá retornar ao chamador da função a lista de possíveis erros,
     * para que possam ser exibidos ao usuário sempre e onde for considerado mais eficaz.
     */
    $errors = array(
        'product_name' => array(false, "Nome do produto inválido: deve ter entre 3 e 100 caracteres."),
        'product_desc' => array(false, "A descrição do produto não pode ultrapassar 500 caracteres."),
        'preco' => array(false, "Preço inválido: deve ser um número positivo com até duas casas decimais."),
        'stock' => array(false, "Stock inválido: deve ser um número inteiro positivo."),
        'categorias' => array(false, "Deve selecionar uma categoria válida."),
    );
		var_dump(array_keys($errors));
		var_dump(array_keys($data));
    // Verifica se o array de dados enviado contém todos os campos necessários.
    if (count(array_diff(array_keys($errors), array_keys($data))) != 0) {
        return ('Os dados do formulário não correspondem. Por favor, corrija-os.');
    }

    // Declara sinalizador de erro
    $flag = false;

    // Validação do Nome do Produto
    if (empty($data['product_name']) || strlen($data['product_name']) < 3 || strlen($data['product_name']) > 100) {
        $errors['product_name'][0] = true;
        $flag = true;
    }

    // Validação da Descrição do Produto (opcional, mas com limite de caracteres)
    if (!empty($data['product_desc']) && strlen($data['product_desc']) > 500) {
        $errors['product_desc'][0] = true;
        $flag = true;
    }

    //  Validação do Preço
    if (!is_numeric($data['preco']) || floatval($data['preco']) <= 0) {
        $errors['preco'][0] = true;
        $flag = true;
    }

    // Validação do Stock
    if (!is_numeric($data['stock']) || intval($data['stock']) < 0 || intval($data['stock']) != $data['stock']) {
        $errors['stock'][0] = true;
        $flag = true;
    }

    // Validação da Categoria
    if (empty($data['categorias']) || !is_numeric($data['categorias']) || intval($data['categorias']) <= 0) {
        $errors['categorias'][0] = true;
        $flag = true;
    }

    //  Validação da Imagem
    if (is_string($result = validateUserImage($tempImage = array_pop($files), $fileSize, $allowedTypes))) {
        $errors['product_image'] = array(true, $result);
        $flag = true;

        // Remove imagem temporária inválida
        if (!empty($tempImage['tmp_name'])) {
            unlink($tempImage['tmp_name']);
        }
    }

    // Verifica se há algum erro
    if (!$flag) {
        return true; // Validação bem-sucedida
    } else {
        return $errors; // Retorna os erros
    }
}
// Função auxiliar para executar queries
function executeSafeQuery($db, $query, $params, $types) {
    $stmt = mysqli_prepare($db, $query);
    if (!$stmt) {
        return "Erro ao preparar query: " . mysqli_error($db);
    }
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    if (!mysqli_stmt_execute($stmt)) {
        $error = mysqli_stmt_error($stmt);
        mysqli_stmt_close($stmt);
        return "Erro ao executar query: $error";
    }
    mysqli_stmt_close($stmt);
    return true;
}


?>

	
