<?php

	//Buscando a class com os metodos e a conexão com o Zabbix
	require_once(__DIR__ . DIRECTORY_SEPARATOR . "class_conect.php");
	require_once(__DIR__ . DIRECTORY_SEPARATOR . "conexao.php");


	// Se for uma requisição na Web, retorna no formato texto plano com suporte à codificação de caracteres UTF-8
	header('Content-Type: text/plain;charset=UTF-8');
	
	// Definição das variaveis para criação do objeto e execução da busca
	$user = "api_user";//"Admin";
	$password = "gN9b5Gsm";//"zabbix";
	$url = "https://187.94.192.150/zabbix/api_jsonrpc.php";//"https://187.94.192.153/zabbix/api_jsonrpc.php";
	$met = "trigger.get";	
	$param = array(
		"output"=> ["value", "lastchange"],
        "active"=> "true", 
        "triggerids"=> "684321"
	);
	$sn = "FHTT002a4ca9";
	$limite = 5;

	// Cria objeto de ApiZabbixConection
	$apiZb = new ApiZabbixConection($user,$password,$url);
	
	// O diretório de cache pode ser alterado pelo método "trocarCaminhoDaPastaDeCache"
	// $apiZb->trocarCaminhoDaPastaDeCache('./cache'); // exemplo

	// Se parametro passado for TRUE pega token da cache, se for FALSE busca uma nova token
	//$resultado = $apiZb->obterToken(true);														// Obter token da cache.

	//Obtendo a data do sistema
	$timezone  = -3;																				// Definindo o Timerzone do horário
	$dataSistem = gmdate('Y-m-d H:i:s',time() + 3600*($timezone+date("I")));						// gmdate('F d Y H:i:s', time() + 3600*($timezone+date("I")));

	$resultado = $apiZb->buscarDados($met,$param); 													// Executando a busca de Dados no webservice do Zabbix
	//$resultado = $apiZb->buscarSinal($sn,$limite); 													// Executando a busca de Sinal no webservice do Zabbix

	$result = json_encode($resultado->result);														// Convertendo o objeto em JSON para ser salvo no banco
	$param = json_encode($param);																	// Convertendo o array em JSON para ser salvo no banco

	// Salvando resultado no Banco de Dados
	//$Insere = "INSERT INTO dados(dataSistema,metodo,parametros,resultado) VALUES ('$dataSistem','$met','$param','$result')";
	//$Resposta = mysqli_query($CONEXAO,$Insere);
	// Impressão de erros na conexão com o DB
	//if(!$Resposta){ echo "Falha de conexao: " . mysqli_error($CONEXAO); }
	//else{ //echo "Conexao foi realizada com sucesso!";
	//}


	// As linhas abaixo apenas testam o resultado
	if ($resultado) {
		print_r($resultado);
	} else {
		echo "\nInformações: ";
		$err = $apiZb->obterErro();
		echo "Cod".$err->code ." / ". $err->message ." ". $err->data;
	}
	echo "\n\n";

	// Liberando cache utilizada.
	flush();

?>