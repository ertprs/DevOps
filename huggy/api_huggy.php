<?php

	// Se for uma requisição na Web, retorna no formato texto plano com suporte à codificação de caracteres UTF-8
	header('Content-Type: text/plain;charset=UTF-8');
	
	//Buscando a class com os metodos e a conexão com o DB
	require_once(__DIR__ . DIRECTORY_SEPARATOR . "class_conect.php");
	
	/** Função destinada ao envio de mensagem de forma automatica de acordo com as respostas do cliente
	 * @param $url Contem as informações referentes ao ENDPOINT que será executado
	 * @param $parametros Contem as mensagens que serão enviadas ao clientes
	 * @param $type Tipo de execução GET|POST|PUT|DELETE
	 */
	function enviaMensagemHuggy($url,$parametros,$type){
		include __DIR__ . DIRECTORY_SEPARATOR . "conexao.php";									// Incluindo arquvio de conexão com DB
		
		// Definição das variaveis para criação do objeto
		$clietid = 'APP-744ddc44-4b7f-4c83-b5c8-0aaa4ee6a968';
		$secretid = 'e802b7b2-3c37-43b3-aecc-c0ce5c8b43bd';
		$code = 'def502004f17a9399630b81216ad28bb644cbde6a966712b8b4320813b3482886f32e4a347fc42906510b68803768b696fcad0970d5d1fe82bf42b6dd7a9a606f5ab2a38a437699a936c3061ce107f98769f9d2a24ea458b69559337686a8c8e5776efaffbaaf043e59d029689ba650b6ae0ae43d8e2b83b02fd6280cd922dc26ac819845a8400b059819e1a79f742e38dbe7595bef3c0e2748a013dd00d02f54051046a17fe703873bed7ec8d11d048974c9e00745bc0e290966fcdf0c9630046ceac6bbd73e47d7a1fb594ab92026beb89331c3f73334f9b59f23c5df96adfff672318ee1971d5fade4e140dfe11b3808e7106cb12d77a0aa69a3b7e1ac1581ee867ba3bd931f6b1311edbc5728445a55ab090243460fe60c4683d7847e44d2fd9a19619f23ca5980394e4f894bbe928bc653f5d1070d94a543aaf6c62c6558731103ef3845e6bd239e4c57e2c7ca62facf1843b8fa07867a7430422e67f8b0cbe8ebc0afd9473cf37e615cc8f0f7dad3b922335c1996420ab4332ec9d768510085f589aa35bc02922ab12833c1b6a6e1f58e9f81479150c74694b597ae8a571ff895ed944c36774623c5b2bbc9b0f10c226ea4da73ba79e60560318cdfe84';
		$redirecturi = 'https://cntdevops01.mgconecta.com.br/callback.php';
		
		$apiHg = new ApiHuggyConection($clietid, $secretid, $code, $redirecturi);				// Cria objeto de ApiHuggyConection
		
		// O diretório de cache pode ser alterado pelo método "trocarCaminhoDaPastaDeCache"
		// $bb->trocarCaminhoDaPastaDeCache('./cache'); // exemplo

		// Se parametro passado for TRUE pega token da cache, se for FALSE busca uma nova token
		//$resultado = $apiHg->obterToken(true);												// Obter token da cache.

		//Obtendo a data do sistema
		$timezone  = -3;																		// Definindo o Timerzone do horário
		$dataSistem = gmdate('Y-m-d H:i:s',time() + 3600*($timezone+date("I")));				// gmdate('F d Y H:i:s', time() + 3600*($timezone+date("I")));

		if ($type == "GET"){																	// Chamando a função para buscar - GET
			$resultado = $apiHg->executarGET($url);												// Retorno da função de GET

			$requisicao = json_encode($resultado);												// Retornando a formatação para ser inserida no DB
			
			$url_final = explode("/",$url);														// Separando a url para inserir no banco
			$tamanho = count($url_final);														// Verificando quantos indicies foram passados

			// Verifica até 4 indices passados na url de informada. Existe 4 comandos que precisam ainda ser tratados, pois fogem a regra
			// Comandos: GET agent/profile | PUT agent/status | POST timeline/createComment/{id} | GET department/parents
			if ($tamanho == 1){
				// Inserção no DB para registrar a requisição
				$Insere = "INSERT INTO metodoExecutado(dataSolicitacao,metodo,url1,url2,url3,url4,parametros,resposta) VALUES ('$dataSistem','$type','$url_final[0]',0,'-',0,'-','$requisicao')";
				$Resposta = mysqli_query($CONEXAO,$Insere);
				// Impressão de erros na conexão com o DB
				if(!$Resposta){ echo "Falha de conexao ao inserir na tabela metodo get1: " . mysqli_error($CONEXAO); }
				else{ //echo "Conexao foi realizada com sucesso!";
				}
			} else if ($tamanho == 2){
				// Inserção no DB para registrar a requisição
				$Insere = "INSERT INTO metodoExecutado(dataSolicitacao,metodo,url1,url2,url3,url4,parametros,resposta) VALUES ('$dataSistem','$type','$url_final[0]','$url_final[1]','-',0,'-','$requisicao')";
				$Resposta = mysqli_query($CONEXAO,$Insere);
				// Impressão de erros na conexão com o DB
				if(!$Resposta){ echo "Falha de conexao ao inserir na tabela metodo get2: " . mysqli_error($CONEXAO); }
				else{ //echo "Conexao foi realizada com sucesso!";
				}
			} else if ($tamanho == 3){
				// Inserção no DB para registrar a requisição
				$Insere = "INSERT INTO metodoExecutado(dataSolicitacao,metodo,url1,url2,url3,url4,parametros,resposta) VALUES ('$dataSistem','$type','$url_final[0]','$url_final[1]','$url_final[2]',0,'-','$requisicao')";
				$Resposta = mysqli_query($CONEXAO,$Insere);
				// Impressão de erros na conexão com o DB
				if(!$Resposta){ echo "Falha de conexao ao inserir na tabela metodo get3: " . mysqli_error($CONEXAO); }
				else{ //echo "Conexao foi realizada com sucesso!";
				}
			} else if ($tamanho == 4){
				// Inserção no DB para registrar a requisição
				$Insere = "INSERT INTO metodoExecutado(dataSolicitacao,metodo,url1,url2,url3,url4,parametros,resposta) VALUES ('$dataSistem','$type','$url_final[0]','$url_final[1]','$url_final[2]','$url_final[3]','-','$requisicao')";
				$Resposta = mysqli_query($CONEXAO,$Insere);
				// Impressão de erros na conexão com o DB
				if(!$Resposta){ echo "Falha de conexao ao inserir na tabela metodo get4: " . mysqli_error($CONEXAO); }
				else{ //echo "Conexao foi realizada com sucesso!";
				}
			}
		} else if ($type == "POST"){															// Chamando a função para inserção - POST
			$resultado = $apiHg->executarPOST($url,$parametros);								// Retorno da função de POST
			
			$requisicao = json_encode($resultado);												// Retornando a formatação para ser inserida no DB
			$parametros = json_encode($parametros);												// Retornando a formatação para ser inserida no DB

			$url_final = explode("/",$url);														// Separando a url para inserir no banco
			$tamanho = count($url_final);														// Verificando quantos indicies foram passados
			
			// Verifica até 4 indices passados na url de informada. Existe 4 comandos que precisam ainda ser tratados, pois fogem a regra
			// Comandos: GET agent/profile | PUT agent/status | POST timeline/createComment/{id} | GET department/parents
			if ($tamanho == 1){ 
				// Inserção no DB para registrar a requisição
				$Insere = "INSERT INTO metodoExecutado(dataSolicitacao,metodo,url1,url2,url3,url4,parametros,resposta) VALUES ('$dataSistem','$type','$url_final[0]',0,'-',0,'$parametros','$requisicao')";
				$Resposta = mysqli_query($CONEXAO,$Insere);
				// Impressão de erros na conexão com o DB
				if(!$Resposta){ echo "Falha de conexao ao inserir na tabela metodo post1: " . mysqli_error($CONEXAO); }
				else{ //echo "Conexao foi realizada com sucesso!";
				}
			} else if ($tamanho == 2){
				// Inserção no DB para registrar a requisição
				$Insere = "INSERT INTO metodoExecutado(dataSolicitacao,metodo,url1,url2,url3,url4,parametros,resposta) VALUES ('$dataSistem','$type','$url_final[0]','$url_final[1]','-',0,'$parametros','$requisicao')";
				$Resposta = mysqli_query($CONEXAO,$Insere);
				// Impressão de erros na conexão com o DB
				if(!$Resposta){ echo "Falha de conexao ao inserir na tabela metodo post2: " . mysqli_error($CONEXAO); }
				else{ //echo "Conexao foi realizada com sucesso!";
				}
			} else if ($tamanho == 3){
				// Inserção no DB para registrar a requisição
				$Insere = "INSERT INTO metodoExecutado(dataSolicitacao,metodo,url1,url2,url3,url4,parametros,resposta) VALUES ('$dataSistem','$type','$url_final[0]','$url_final[1]','$url_final[2]',0,'$parametros','$requisicao')";
				$Resposta = mysqli_query($CONEXAO,$Insere);
				// Impressão de erros na conexão com o DB
				if(!$Resposta){ echo "Falha de conexao ao inserir na tabela metodo post3: " . mysqli_error($CONEXAO); }
				else{ //echo "Conexao foi realizada com sucesso!";
				}			
			}		
		} else if ($type == "PUT"){																// Chamando a função para alteração - PUT
			$resultado = $apiHg->executarPUT($url,$parametros);									// Retorno da função de PUT
			
			$requisicao = json_encode($resultado);												// Retornando a formatação para ser inserida no DB
			$parametros = json_encode($parametros);												// Retornando a formatação para ser inserida no DB
			
			$url_final = explode("/",$url);														// Separando a url para inserir no banco
			$tamanho = count($url_final);														// Verificando quantos indicies foram passados

			// Verifica até 4 indices passados na url de informada. Existe 4 comandos que precisam ainda ser tratados, pois fogem a regra
			// Comandos: GET agent/profile | PUT agent/status | POST timeline/createComment/{id} | GET department/parents
			if ($tamanho == 2){ 
				// Inserção no DB para registrar a requisição
				$Insere = "INSERT INTO metodoExecutado(dataSolicitacao,metodo,url1,url2,url3,url4,parametros,resposta) VALUES ('$dataSistem','$type','$url_final[0]','$url_final[1]','-',0,'$parametros','$requisicao')";
				$Resposta = mysqli_query($CONEXAO,$Insere);
				// Impressão de erros na conexão com o DB
				if(!$Resposta){ echo "Falha de conexao ao inserir na tabela metodo put1: " . mysqli_error($CONEXAO); }
				else{ //echo "Conexao foi realizada com sucesso!";
				}
			} else if ($tamanho == 3){
				// Inserção no DB para registrar a requisição
				$Insere = "INSERT INTO metodoExecutado(dataSolicitacao,metodo,url1,url2,url3,url4,parametros,resposta) VALUES ('$dataSistem','$type','$url_final[0]','$url_final[1]','$url_final[2]',0,'$parametros','$requisicao')";
				$Resposta = mysqli_query($CONEXAO,$Insere);
				// Impressão de erros na conexão com o DB
				if(!$Resposta){ echo "Falha de conexao ao inserir na tabela metodo put2: " . mysqli_error($CONEXAO); }
				else{ //echo "Conexao foi realizada com sucesso!";
				}
			} else if ($tamanho == 4){
				// Inserção no DB para registrar a requisição
				$Insere = "INSERT INTO metodoExecutado(dataSolicitacao,metodo,url1,url2,url3,url4,parametros,resposta) VALUES ('$dataSistem','$type','$url_final[0]','$url_final[1]','$url_final[2]','$url_final[3]','$parametros','$requisicao')";
				$Resposta = mysqli_query($CONEXAO,$Insere);
				// Impressão de erros na conexão com o DB
				if(!$Resposta){ echo "Falha de conexao ao inserir na tabela metodo put3: " . mysqli_error($CONEXAO); }
				else{ //echo "Conexao foi realizada com sucesso!";
				}
			}
		} else if ($type == "DELETE"){															// Chamando a função para deletar - DELETE
			$resultado = $apiHg->executarDELETE($url);											// Retorno da função de DELETE
			
			$requisicao = json_encode($resultado);												// Retornando a formatação para ser inserida no DB
			
			$url_final = explode("/",$url);														// Separando a url para inserir no banco
			$tamanho = count($url_final);														// Verificando quantos indicies foram passados

			// Verifica até 4 indices passados na url de informada. Existe 4 comandos que precisam ainda ser tratados, pois fogem a regra
			// Comandos: GET agent/profile | PUT agent/status | POST timeline/createComment/{id} | GET department/parents
			if ($tamanho == 2){ 
				// Inserção no DB para registrar a requisição
				$Insere = "INSERT INTO metodoExecutado(dataSolicitacao,metodo,url1,url2,url3,url4,parametros,resposta) VALUES ('$dataSistem','$type','$url_final[0]','$url_final[1]','-',0,'-','$requisicao')";
				$Resposta = mysqli_query($CONEXAO,$Insere);
				// Impressão de erros na conexão com o DB
				if(!$Resposta){ echo "Falha de conexao ao inserir na tabela metodo delete1: " . mysqli_error($CONEXAO); }
				else{ //echo "Conexao foi realizada com sucesso!";
				}
			} else if ($tamanho == 4){
				// Inserção no DB para registrar a requisição
				$Insere = "INSERT INTO metodoExecutado(dataSolicitacao,metodo,url1,url2,url3,url4,parametros,resposta) VALUES ('$dataSistem','$type','$url_final[0]','$url_final[1]','$url_final[2]','$url_final[3]','-','$requisicao')";
				$Resposta = mysqli_query($CONEXAO,$Insere);
				// Impressão de erros na conexão com o DB
				if(!$Resposta){ echo "Falha de conexao ao inserir na tabela metodo delete2: " . mysqli_error($CONEXAO); }
				else{ //echo "Conexao foi realizada com sucesso!";
				}
			} 
		}

		flush();																				// Liberando cache utilizada.
	}
	

?>