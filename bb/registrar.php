<?php

	// Se for uma requisição na Web, retorna no formato texto plano com suporte à codificação de caracteres UTF-8
	header('Content-Type: text/plain;charset=UTF-8');

	require_once(__DIR__ . DIRECTORY_SEPARATOR . "class_conect.php");								//Buscando arquivos de funções
	include __DIR__ . DIRECTORY_SEPARATOR . "conexao.php";											//Buscando arquivo de conexão com os bancos de dados que serão utilizados

	//Relaizar conexão no DB para obter os dados do cliente que terá o boleto registrado
	
	// Exemplo de tratamento dos campos recebidos para geração do boleto
	//$data = "10/12/2020";
	//$datado = preg_replace("/[^0-9]/",".", $data);												// Substituindo tudo que nao for numero por ponto
	//$cpfresult = preg_replace("/[^0-9]/","", $cpf); 												// Substituindo tudo que nao for numero por vazio, Serve também para CNPJ
	//$nomeresult = preg_replace("/&([a-z])[a-z]+;/i", "$1", htmlentities(trim($nome)));			// Removendo caracteres especiais do nome
	//$nomeresult = strtoupper($nomeresult);														// Convertendo "string" para maiusculo
	//validando email - Teste
	$email = 'gu.sta_vosi....lva-08@gmail.com.br';
	$padrao = "/^[a-zA-Z0-9\._-]+@[a-zA-Z0-9\._-]+\.([a-zA-Z]{2,4})$/";
	if (preg_match($padrao,$email)) { echo $email."\n";}
	else { echo "Invalido\n"; }
	
	// Variaveis do cliente, buscar no DB
	$numerodoboleto = '33';																			// ID do boleto que será registrado
	$textoNumeroTituloBeneficiario = '33';															// Código para identificação do Título de Cobrança, gerado pelo beneficiário, pode ser o mesmo que $numerodoboleto
	$datadaemissao = '26.11.2020';																	// Segundo a especificação, deve ser no formato DD.MM.AAAA
	$datadovencimento = '28.11.2020';																// Segundo a especificação, deve ser no formato DD.MM.AAAA
	$valor = '99.00';																				// No formato inglês (sem separador de milhar)
	$tipodedocumentodocliente = 1;																	// 1 para CPF e 2 para CNPJ
	$numerodedocumentodocliente = '07343460692';													// CPF ou CNPJ, sem pontos ou traços
	$nomedocliente = 'Gustavo Arnaldo da Silva';
	$enderecodocliente = 'Rua Minas Gerais';
	$bairrodocliente = 'Cassia';
	$municipiodocliente = 'Ritapolis';
	$sigladoestadodocliente = 'MG';
	$cepdocliente = '36335000';																		// Sem pontos ou traços
	$telefonedocliente = '32984344164';																// Sem pontos ou parenteses
	
	// Variaveis estáticas
	$convenio = '2801389';																			// Número do convênico com o banco
	$numerodacarteira = '17';
	$variacaodacarteira = '27';
	$codigoModalidadeTitulo = 1;																	// No momento é permitido somente o número '1' par registro Webservice
	$juros = 1;																						// Juros podem ser 0->sem juros | 1->valor por dia de atraso | 2->taxa mensal | 3->isento
	$percentualjuros = 0;																			// Não obrigatório, valorjuros = 0, deve ser zero quando este for informado e juros = 2
	$valorjuros = 00.33;																			// Não obrigatório, percentualjuros = 0, deve ser zero quando este for informado e juros = 1
	$desconto = 0;																					// Descontos podem ser 0->sem desc. | 1->desc. em valor | 2->des. em percentual | 3->desc. por -dia-antecipação
	$multa = 0;																						// Multas podem ser 0->sem multa | 1->valor da multa | 2->precentua da multa
	$codigoTipoContaCaucao = 0;																		// Informar sempre o zero
	$codigoAceiteTitulo = 'N';																		// A->Aceite ou N->nao aceite
	$codigoTipoTitulo = 17;																			// Possui vários tipo o 17 é de RECIBO
	$indicadorPermissao = 'N';																		// S->permite recebimento parcial | N->não permite recebimento parcial
	$textoDescricaoTipoTitulo = 'RECIBO';
	$textoMensagemBloquetoOcorrencia = 'Pagamento disponível até a data de vencimento';
	$codigoChaveUsuario = 1;
	$codigoTipoCanalSolicitacao = 5;																// Somente o 5, define o canal que será executada, WEBSERVICE
	$numeroBoleto = 0;

	// Cria objeto de BBBoletoWebService
	$bb = new BBBoletoWebService('eyJpZCI6IjgwNDNiNTMtZjQ5Mi00YyIsImNvZGlnb1B1YmxpY2Fkb3IiOjEwOSwiY29kaWdvU29mdHdhcmUiOjEsInNlcXVlbmNpYWxJbnN0YWxhY2FvIjoxfQ','eyJpZCI6IjBjZDFlMGQtN2UyNC00MGQyLWI0YSIsImNvZGlnb1B1YmxpY2Fkb3IiOjEwOSwiY29kaWdvU29mdHdhcmUiOjEsInNlcXVlbmNpYWxJbnN0YWxhY2FvIjoxLCJzZXF1ZW5jaWFsQ3JlZGVuY2lhbCI6MX0');
	
	// $bb->trocarCaminhoDaPastaDeCache('./cache'); // exemplo										// O diretório de cache pode ser alterado pelo método "trocarCaminhoDaPastaDeCache"
	
	$parametros = array(																			// Parâmetros que serão passados para o Banco do Brasil
		'numeroConvenio' => $convenio,
		'numeroCarteira' => $numerodacarteira,
		'numeroVariacaoCarteira' => $variacaodacarteira,
		'codigoModalidadeTitulo' => $codigoModalidadeTitulo,
		'dataEmissaoTitulo' => $datadaemissao,
		'dataVencimentoTitulo' => $datadovencimento,
		'valorOriginalTitulo' => $valor,
		'codigoTipoDesconto' => $desconto,
		'codigoTipoJuroMora' => $juros,
		'percentualJuroMoraTitulo' => $percentualjuros,
		'valorJuroMoraTitulo' => $valorjuros,
		'codigoTipoMulta' => $multa,																// Se for 0-> não precisa setar o datamultatitulo, percentual e valor | 1->um dos dois campos devem estar setados datamultatitulo ou valor | 2->setar somente datamultatitulo e percentual
		'codigoAceiteTitulo' => $codigoAceiteTitulo,
		'codigoTipoTitulo' => $codigoTipoTitulo,
		'textoDescricaoTipoTitulo' => $textoDescricaoTipoTitulo,
		'indicadorPermissaoRecebimentoParcial' => $indicadorPermissao,
		'textoNumeroTituloBeneficiario' => $textoNumeroTituloBeneficiario,
		'codigoTipoContaCaucao' => $codigoTipoContaCaucao,
		'textoNumeroTituloCliente' => '000' . $convenio . sprintf('%010d', $numerodoboleto),
		'textoMensagemBloquetoOcorrencia' => $textoMensagemBloquetoOcorrencia,
		'codigoTipoInscricaoPagador' => $tipodedocumentodocliente,
		'numeroInscricaoPagador' => $numerodedocumentodocliente,
		'nomePagador' => $nomedocliente,
		'textoEnderecoPagador' => $enderecodocliente,
		'numeroCepPagador' => $cepdocliente,
		'nomeMunicipioPagador' => $municipiodocliente,
		'nomeBairroPagador' => $bairrodocliente,
		'siglaUfPagador' => $sigladoestadodocliente,
		'textoNumeroTelefonePagador' => $telefonedocliente,
		'codigoChaveUsuario' => $codigoChaveUsuario,
		'codigoTipoCanalSolicitacao' => $codigoTipoCanalSolicitacao									// Somente o 5, define o canal que será executada, WEBSERVICE
	);
	
	//Obtendo a data do sistema
	$timezone  = -3;																				// Definindo o Timerzone do horário
	$dataSistem = gmdate('Y-m-d H:i:s',time() + 3600*($timezone+date("I")));						// gmdate('F d Y H:i:s', time() + 3600*($timezone+date("I")));

	// Escolha entre ambiente de testes ou produção. Por padrão, o construtor usa o ambiente de produção.
	$bb->alterarParaAmbienteDeTestes();																// Ambiente de Testes
	// $bb->alterarParaAmbienteDeProducao();														// Ambiente de Produção	

	$resultado = $bb->registrarBoleto($parametros);//$bb->obterToken(true);							// Chamando função principal para resgistro do boleto. retorna "false" se houver algum erro
	
	// salvando em DB os resultados obtidos das tentativas de registro de boleto no BB
	if (isset($resultado['faultcode'])) {															// Verificação se houve erro na tentavia de conexao, variavel aparece somente se houver erro na conexao
		$faultcode = $resultado['faultcode'];
		$faultstring = $resultado['faultstring'];
		$faultactor = $resultado['faultactor'];
		$detailErroMensagem = $resultado['detail']['erro']['Mensagem'];

		// Relaizando conexão no DB para salvar os boletos com erros
		$Boleto = "INSERT INTO resultadoErroConexao(dataRegistro,numeroBoleto,faultcode,faultstring,faultactor,detailErroMensagem) VALUE ('$dataSistem','$numeroBoleto','$faultcode','$faultstring','$faultactor','$detailErroMensagem')"; 
		$Resposta = mysqli_query($CONEXAO,$Boleto);
		// Impressão de erros na conexão com o DB
		if(!$Resposta){ echo "Falha de conexao ao inserir na tabela erro de conexao: " . mysqli_error($CONEXAO); }
		else{ //echo "Conexao foi realizada com sucesso!";
		}
	}
	
	if ((isset($resultado['codigoRetornoPrograma'])) && ($resultado['codigoRetornoPrograma'] != 0)) {// Verificando se houve mensagem de erro ao tentar registrar o boleto
		$siglaSistemaMensagem = $resultado['siglaSistemaMensagem']; 
		$codigoRetornoPrograma = $resultado['codigoRetornoPrograma'];
		$nomeProgramaErro = $resultado['nomeProgramaErro']; 
		$textoMensagemErro = $resultado['textoMensagemErro']; 
		$numeroPosicaoErroPrograma = $resultado['numeroPosicaoErroPrograma']; 
		$codigoTipoRetornoPrograma = $resultado['codigoTipoRetornoPrograma']; 
		$textoNumeroTituloCobrancaBb = $resultado['textoNumeroTituloCobrancaBb'];
		$numeroCarteiraCobranca = $resultado['numeroCarteiraCobranca']; 
		$numeroVariacaoCarteiraCobranca = $resultado['numeroVariacaoCarteiraCobranca']; 
		$codigoPrefixoDependenciaBeneficiario = $resultado['codigoPrefixoDependenciaBeneficiario']; 
		$numeroContaCorrenteBeneficiario = $resultado['numeroContaCorrenteBeneficiario']; 
		$codigoCliente = $resultado['codigoCliente']; 
		$linhaDigitavel = $resultado['linhaDigitavel']; 
		$codigoBarraNumerico = $resultado['codigoBarraNumerico']; 
		$codigoTipoEnderecoBeneficiario = $resultado['codigoTipoEnderecoBeneficiario'];
		$nomeLogradouroBeneficiario = $resultado['nomeLogradouroBeneficiario']; 
		$nomeBairroBeneficiario = $resultado['nomeBairroBeneficiario']; 
		$nomeMunicipioBeneficiario = $resultado['nomeMunicipioBeneficiario']; 
		$codigoMunicipioBeneficiario = $resultado['codigoMunicipioBeneficiario']; 
		$siglaUfBeneficiario = $resultado['siglaUfBeneficiario']; 
		$codigoCepBeneficiario = $resultado['codigoCepBeneficiario']; 
		$indicadorComprovacaoBeneficiario = $resultado['indicadorComprovacaoBeneficiario']; 
		$numeroContratoCobranca = $resultado['numeroContratoCobranca'];

		// Relaizando conexão no DB para salvar os boletos com erros
		$Boleto = "INSERT INTO resultadoBoletosComErros(dataRegistro,numeroBoleto,siglaSistemaMensagem,codigoRetornoPrograma,nomeProgramaErro,textoMensagemErro,numeroPosicaoErroPrograma,codigoTipoRetornoPrograma,textoNumeroTituloCobrancaBb,
		numeroCarteiraCobranca,numeroVariacaoCarteiraCobranca,codigoPrefixoDependenciaBeneficiario,numeroContaCorrenteBeneficiario,codigoCliente,linhaDigitavel,codigoBarraNumerico,codigoTipoEnderecoBeneficiario,
		nomeLogradouroBeneficiario,nomeBairroBeneficiario,nomeMunicipioBeneficiario,codigoMunicipioBeneficiario,siglaUfBeneficiario,codigoCepBeneficiario,indicadorComprovacaoBeneficiario,numeroContratoCobranca)
		VALUE ('$dataSistem','$numeroBoleto','$siglaSistemaMensagem','$codigoRetornoPrograma','$nomeProgramaErro','$textoMensagemErro','$numeroPosicaoErroPrograma','$codigoTipoRetornoPrograma','$textoNumeroTituloCobrancaBb','$numeroCarteiraCobranca',
		'$numeroVariacaoCarteiraCobranca','$codigoPrefixoDependenciaBeneficiario','$numeroContaCorrenteBeneficiario','$codigoCliente','$linhaDigitavel','$codigoBarraNumerico','$codigoTipoEnderecoBeneficiario',
		'$nomeLogradouroBeneficiario','$nomeBairroBeneficiario','$nomeMunicipioBeneficiario','$codigoMunicipioBeneficiario','$siglaUfBeneficiario','$codigoCepBeneficiario','$indicadorComprovacaoBeneficiario','$numeroContratoCobranca')"; 
		$Resposta = mysqli_query($CONEXAO,$Boleto);
		// Impressão de erros na conexão com o DB
		if(!$Resposta){ echo "Falha de conexao ao inserir na tabela de Erros: " . mysqli_error($CONEXAO); }
		else{ //echo "Conexao foi realizada com sucesso!";
		}
	} 

	if ((isset($resultado['codigoRetornoPrograma'])) && ($resultado['codigoRetornoPrograma'] == 0)) {// Verificando se houve exito ao tentar registrar o boleto
		$siglaSistemaMensagem = $resultado['siglaSistemaMensagem']; 
		$codigoRetornoPrograma = $resultado['codigoRetornoPrograma'];
		$nomeProgramaErro = $resultado['nomeProgramaErro']; 
		$textoMensagemErro = $resultado['textoMensagemErro']; 
		$numeroPosicaoErroPrograma = $resultado['numeroPosicaoErroPrograma']; 
		$codigoTipoRetornoPrograma = $resultado['codigoTipoRetornoPrograma']; 
		$textoNumeroTituloCobrancaBb = $resultado['textoNumeroTituloCobrancaBb'];
		$numeroCarteiraCobranca = $resultado['numeroCarteiraCobranca']; 
		$numeroVariacaoCarteiraCobranca = $resultado['numeroVariacaoCarteiraCobranca']; 
		$codigoPrefixoDependenciaBeneficiario = $resultado['codigoPrefixoDependenciaBeneficiario']; 
		$numeroContaCorrenteBeneficiario = $resultado['numeroContaCorrenteBeneficiario']; 
		$codigoCliente = $resultado['codigoCliente']; 
		$linhaDigitavel = $resultado['linhaDigitavel']; 
		$codigoBarraNumerico = $resultado['codigoBarraNumerico']; 
		$codigoTipoEnderecoBeneficiario = $resultado['codigoTipoEnderecoBeneficiario'];
		$nomeLogradouroBeneficiario = $resultado['nomeLogradouroBeneficiario']; 
		$nomeBairroBeneficiario = $resultado['nomeBairroBeneficiario']; 
		$nomeMunicipioBeneficiario = $resultado['nomeMunicipioBeneficiario']; 
		$codigoMunicipioBeneficiario = $resultado['codigoMunicipioBeneficiario']; 
		$siglaUfBeneficiario = $resultado['siglaUfBeneficiario']; 
		$codigoCepBeneficiario = $resultado['codigoCepBeneficiario']; 
		$indicadorComprovacaoBeneficiario = $resultado['indicadorComprovacaoBeneficiario']; 
		$numeroContratoCobranca = $resultado['numeroContratoCobranca'];

		// Relaizando conexão no DB para salvar os boletos com erros
		$Boleto = "INSERT INTO resultadoBoletosRegistrados(dataRegistro,numeroBoleto,siglaSistemaMensagem,codigoRetornoPrograma,nomeProgramaErro,textoMensagemErro,numeroPosicaoErroPrograma,codigoTipoRetornoPrograma,textoNumeroTituloCobrancaBb,
		numeroCarteiraCobranca,numeroVariacaoCarteiraCobranca,codigoPrefixoDependenciaBeneficiario,numeroContaCorrenteBeneficiario,codigoCliente,linhaDigitavel,codigoBarraNumerico,codigoTipoEnderecoBeneficiario,
		nomeLogradouroBeneficiario,nomeBairroBeneficiario,nomeMunicipioBeneficiario,codigoMunicipioBeneficiario,siglaUfBeneficiario,codigoCepBeneficiario,indicadorComprovacaoBeneficiario,numeroContratoCobranca)
		VALUE ('$dataSistem','$numeroBoleto','$siglaSistemaMensagem','$codigoRetornoPrograma','$nomeProgramaErro','$textoMensagemErro','$numeroPosicaoErroPrograma','$codigoTipoRetornoPrograma','$textoNumeroTituloCobrancaBb','$numeroCarteiraCobranca',
		'$numeroVariacaoCarteiraCobranca','$codigoPrefixoDependenciaBeneficiario','$numeroContaCorrenteBeneficiario','$codigoCliente','$linhaDigitavel','$codigoBarraNumerico','$codigoTipoEnderecoBeneficiario',
		'$nomeLogradouroBeneficiario','$nomeBairroBeneficiario','$nomeMunicipioBeneficiario','$codigoMunicipioBeneficiario','$siglaUfBeneficiario','$codigoCepBeneficiario','$indicadorComprovacaoBeneficiario','$numeroContratoCobranca')"; 
		$Resposta = mysqli_query($CONEXAO,$Boleto);
		// Impressão de erros na conexão com o DB
		if(!$Resposta){ echo "Falha de conexao ao inserir na tabela registrados: " . mysqli_error($CONEXAO); }
		else{ //echo "Conexao foi realizada com sucesso!";
		}
	}

	echo "\n\n------------------------- RETORNO DO BB -------------------------\n\n";
	echo "Informações do Registro: " . $bb->obterErro() . "\n\n";									// Informando se houve ou se está tudo Certo
	print_r($resultado);																			// Exibindo o retorno obtido, SUCESSO OU ERRO
	echo "-----------------------------------------------------------------";
	
	//Relaizar conexão no DB para atualizar os dados do cliente que terá o boleto registrado para que nao seja registrdao novamente

	flush();																						// Liberando cache utilizada.

?>