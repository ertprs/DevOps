<?php

	// Se for uma requisição na Web, retorna no formato texto plano com suporte à codificação de caracteres UTF-8
	header('Content-Type: text/plain;charset=UTF-8');

	require_once(__DIR__ . DIRECTORY_SEPARATOR . "class_conect.php");								// Buscando arquivos de funções
	include __DIR__ . DIRECTORY_SEPARATOR . "conexao.php";											// Buscando arquivo de conexão com os DB

	// Variaiveis de craição do objeto
	$User = "devops@mgconecta.com.br";
	$Pass = "H2d&5c6d@a!";
	$Client = "26490";
	$Remetente = "Conecta";
	
	$transacao = '40.05';																			// Transação a ser executada
	//Obtendo a data do sistema
	$timezone  = -3;																				// Definindo o Timerzone do horário
	$dataSistem = gmdate('Y-m-d H:i:s',time() + 3600*($timezone+date("I")));						// gmdate('F d Y H:i:s', time() + 3600*($timezone+date("I")));

	$akna = new AknaWebService($User,$Pass,$Client,$Remetente);										// Cria objeto de AknaWebService


	// Modelos de parmentros para as chamadas de funções de transações
	if ($transacao == '40.01') {																	// Dados para realização do envio de SMS
		$parametros = array(																		// Parametros para o envio de SMS
			"sms"=> array(
				["telefone"=> "32984344164",														// Telefone deve ser passado somente os números (DDD + NUMERO)
				"mensagem"=> "TESTE de envio da API AKNA fumaça, aplígio, céu, ações, será, vovó, vovô, emoção."]										// Mensagem deve conter somente 165 caracterese não hpa necessidade de remoção do caracteres especial
				/*["telefone"=> "32988450130",
				"mensagem"=> "TESTE de envio da API AKNA..."],
				["telefone"=> "32999471034",
				"mensagem"=> "TESTE de envio da API AKNA..."]*/
			)
		);
		
		$resultado = $akna->conectApiEnvioSMS($parametros);											// Chamando função para envio de SMS
		$resultado = explode('¹', $resultado);

		// Inserindo resultado no banco de dados
		$Insere = "INSERT INTO dados(dataCriacao,usuario,requisicao,transacao,resposta,resultadoTotal) VALUES ('$dataSistem','$User','$resultado[0]','$transacao','$resultado[1]','$resultado[2]')";
		$Result = mysqli_query($CONEXAO,$Insere);
		// Impressão de erros na conexão com o DB
		if(!$Result){ echo "Falha de conexao na inserção da mensagem: " . mysqli_error($CONEXAO); }
		else{ //echo "Conexao foi realizada com sucesso!";
		}
	}
	
	if ($transacao == '40.02') {																	// Dados para verificação do status do envio
		$parametro = "5fd778f1da53d";																// Parametro para consulta de status do envio	

		$resultado = $akna->conectApiConsultarStatusEnvio($parametro);								// Chamando função para ver status do envio
		$resultado = explode('¹', $resultado);

		// Inserindo resultado no banco de dados
		$Insere = "INSERT INTO dados(dataCriacao,usuario,requisicao,transacao,resposta,resultadoTotal) VALUES ('$dataSistem','$User','$resultado[0]','$transacao','$resultado[1]','$resultado[2]')";
		$Result = mysqli_query($CONEXAO,$Insere);
		// Impressão de erros na conexão com o DB
		if(!$Result){ echo "Falha de conexao na inserção da mensagem: " . mysqli_error($CONEXAO); }
		else{ //echo "Conexao foi realizada com sucesso!";
		}
	}

	if ($transacao == '40.03') {																	// Dados para verificação de resposta dos destinatários
		$parametro = "5fd778f1da53d";																// Parametro para consultar respostas dos destinatários

		$resultado = $akna->conectApiConsultarRespDest($parametro);									// Chamando função para ver respostas dos destinatários
		$resultado = explode('¹', $resultado);

		// Inserindo resultado no banco de dados
		$Insere = "INSERT INTO dados(dataCriacao,usuario,requisicao,transacao,resposta,resultadoTotal) VALUES ('$dataSistem','$User','$resultado[0]','$transacao','$resultado[1]','$resultado[2]')";
		$Result = mysqli_query($CONEXAO,$Insere);
		// Impressão de erros na conexão com o DB
		if(!$Result){ echo "Falha de conexao na inserção da mensagem: " . mysqli_error($CONEXAO); }
		else{ //echo "Conexao foi realizada com sucesso!";
		}
	}

	if ($transacao == '40.04') { 																	// Dados para verificação do relatório de Cliques de SMS transacional
		$parametros = array(																		// Parametros para solicitação de relatorio de um ou mais SMS transacional
			"sms"=> array(
				//"identificador"=> ["5fd778f1da53d"],//"5672faal90w2x"],//["5672faal90w2x"]
				"telefone"=> ["32984344164"],//"32900000001"],//["32900000001"]
				"envio"=> array(																	// Deve ser enviado quando o "clique" não for enviado
					"inicio"=> "2020-12-14 00:01:00",												// Período não pode ser maior que 48horas
					"fim"=> "2020-12-14 12:30:00"
				)//,
				//"clique"=> array(																	// Deve ser enviado quando o "envio" não for enviado
				//	"inicio"=> "2020-01-03 10:00:00",												// Período não pode ser maior que 48horas
				//	"fim"=> "2020-01-03 11:00:00"
				//)
			)
		);

		$resultado = $akna->conectApiSolicitaRelatorioSMS($parametros);								// Chamando função para Solicitação de relatório
		$resultado = explode('¹', $resultado);

		// Inserindo resultado no banco de dados
		$Insere = "INSERT INTO dados(dataCriacao,usuario,requisicao,transacao,resposta,resultadoTotal) VALUES ('$dataSistem','$User','$resultado[0]','$transacao','$resultado[1]','$resultado[2]')";
		$Result = mysqli_query($CONEXAO,$Insere);
		// Impressão de erros na conexão com o DB
		if(!$Result){ echo "Falha de conexao na inserção da mensagem: " . mysqli_error($CONEXAO); }
		else{ //echo "Conexao foi realizada com sucesso!";
		}
	}

	if ($transacao == '40.05') { 																	// Dados para acompanhar progresso de geração do relatório
		$parametro = "3401d5da5bef1a94d2ca1e120e50657a";											// Parametro para acompanhar progresso de geração do relatório

		$resultado = $akna->conectApiAcompGeracRelat($parametro);									// Chamando função para ver status do envio
		$resultado = explode('¹', $resultado);

		// Inserindo resultado no banco de dados
		$Insere = "INSERT INTO dados(dataCriacao,usuario,requisicao,transacao,resposta,resultadoTotal) VALUES ('$dataSistem','$User','$resultado[0]','$transacao','$resultado[1]','$resultado[2]')";
		$Result = mysqli_query($CONEXAO,$Insere);
		// Impressão de erros na conexão com o DB
		if(!$Result){ echo "Falha de conexao na inserção da mensagem: " . mysqli_error($CONEXAO); }
		else{ //echo "Conexao foi realizada com sucesso!";
		}
	}
	
	echo "\n+------------------------- RETORNO DO AKNA -------------------------+\n";
	echo "\nRequisição enviada: " . $resultado[0];													// Requisição enviada
	echo "\nMensagem de Retorno: " . $akna->obterErro();											// Mensagem de retorno
	echo "\nResposta obtida: " . $resultado[1];
	echo "\nRetorno completo: " . $resultado[2];
	echo "\n\n+-------------------------------------------------------------------+";

	flush();																						// Liberando cache utilizada.

?>