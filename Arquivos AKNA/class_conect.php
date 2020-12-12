<?php
	
class AknaWebService 
{
	// Url necessária para a comunicação
	const URL_BASE = 'http://app.akna.com.br/emkt/int/integracao.php';

	private $_User;																				// Usuário de integração
	private $_Pass;																				// Senha de integração
	private $_Client;																			// Código da empresa para integração
	private $_Remetente;																		// Remetente de comunicação

	private $_erro;																				// Armazena informação sobre o erro ocorrido
	private $_tokenEmCache;																		// Armazena a última token processada pelo método obterToken()

	/** Construtor do Consumidor de WebService Akna
	 * @param string $User Usuário de integração
	 * @param string $Pass Senha do usuario de integração
	 * @param interger $Client Código da empresa 
	 * @param string $Remetente 
	 */
	function __construct($User,$Pass,$Client,$Remetente)
	{
		// Variaveis de criação do objeto
		$this->_User		=& $User;
		$this->_Pass		=& $Pass;
		$this->_Client		=& $Client;
		$this->_Remetente	=& $Remetente;
	}

	/** Inicia as configurações do Curl útil para realizar as requisições
	 * @return resource Curl pré-configurado
	 */
	private function _prepararCurl()
	{
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_BINARYTRANSFER => true,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_TIMEOUT => 0,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_SSL_VERIFYHOST => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_POST => true,
			CURLOPT_MAXREDIRS => 10
		));
		return $curl;
	}

	/** Função para eliminar caracteres especiais
	 * 
	 */
	function caractereEspecial($string) {
		
		$string = preg_replace("/&([a-z])[a-z]+;/i", "$1", htmlentities(trim($string)));
		
		//Retornando o resultado convertido
		return $string;	
	}

	/** Função que realiza a chamada e retorna o resultado da Akna no formato de XML.
	 * @return array Transcrição da resposta da Akna em array
	 */
	function conectApiListaEquipes()
	{
		$this->_erro = false;
		
		// Montar envelope contendo a requisição do serviço
		$requisicao = '<?xml version="1.0" encoding="UTF-8"?><main><emkt trans="15.50"></emkt></main>';

		//Imprimindo as informações que serão enviadas por XML
		//echo "Requisicao XML: ";
		//print_r($requisicao);
		
		$curl = self::_prepararCurl();															// Preparar requisição sem cabeçalho, todos os dados 
		curl_setopt_array($curl, array(															// devem ser enviados via POSTFIELDS e a senha sempre em "MD5"
			CURLOPT_URL => self::URL_BASE,
			CURLOPT_POSTFIELDS => array(
				'User'=> $this->_User,
				'Pass'=> md5($this->_Pass),
				'XML'=> $requisicao,
				'Client'=> $this->_Client,
				'Remetente'=> $this->_Remetente
				)
		));
		$resposta = curl_exec($curl);															// Obtendo resultado da comunicação
		curl_close($curl);
		//print_r($resposta);																	// Exibindo dados obtidos

		if ($resposta) {
			$resposta = preg_replace('/(?<=>)\\s+(?=<)/', '', $resposta);						// Retirando os espaços entre os nós e depois o cabeçalho do XML
			$resposta = preg_replace('/(<\?(\w+)\s(\w+)="\d\.\d"\s(\w+)="(\w+)\-\d"\?>)*/', '', $resposta);
			$resposta = "<Resultado>" . $resposta . "</Resultado>";								// Inserindo nó pai, caos ocorrea alguma repetição do nó MAIN
			$xml = simplexml_load_string($resposta);											// Lendo a resposta
			
			$resultado = json_encode($xml);														// Transformando em JSON o resultado 
			$resultado = json_decode($resultado,true);											// Transformando em array o resultado
			
			for ($i=0;$i<count($resultado['MAIN']);$i++) {										// Pegando as respostas quando for mais de uma
				if (count($resultado['MAIN']) == 1)
					$resultadoShort = $resultado['MAIN']['EMKT']['RETURN'];
				else
					$resultadoShort[$i] = $resultado['MAIN'][$i]['EMKT']['RETURN'];
			}
		} else {
			$this->_erro = 'Não foi possível conectar-se à Akna';
			return false;
		}

		$resultadoShort = array("RETURN"=> $resultadoShort);									// Motando array de retorno
		
		// Unindo a Requisição e o resultado para serem retornados
		$RequisicaoResultado = $requisicao . "¹" . json_encode($resultadoShort) . "¹" . json_encode($resultado);
		$this->_erro = 'Conexão realizada com sucesso!';
		//print_r($RequisicaoResultado);

		return $RequisicaoResultado;															// Retornado a requisição e o resultado
		
	}

	/** Função recebe um array com os parametros e realiza a conexão, é destinada para o envio de SMS para os contatos.
	 * CAMPOS NÃO OBRIGATÓRIOS: "remetente" quando se tem somente um cadastrado e "identificador"
	 * CAMPOS OBRIGATÓRIOS: "telefone" e "mensagem" que estão ambos dentro de <sms>...</sms> e também "encurtar url" que deve ser sempre "S".
	 * Obs.: Pode ser enviado mais de uma mensagem em uma única chamada da função basta passar por parametros os campos de 
	 * <telefone>...</telefone><mensagem>...</mensagem>
	 * @param array $parametros	Array com mapeamento nome -> valor
	 * @return array Transcrição da resposta da Akna em array
	 */
	function conectApiEnvioSMS($parametros)
	{
		$this->_erro = false;
		
		// Montando a requisição dsem inseri o atributo "identificador" que não é obrigatório
		$requisicao = '<?xml version="1.0" encoding="UTF-8"?><main><emkt trans="40.01"><remetente>Conecta</remetente><encurtar_url>S</encurtar_url>';
		for ($i=0;$i<count($parametros['sms']);$i++) {
			$requisicao .= "<sms><telefone>" . $parametros['sms'][$i]['telefone'] . "</telefone>";
			$requisicao .= "<mensagem>" . $parametros['sms'][$i]['mensagem'] . "</mensagem></sms>";
		}
		$requisicao .= '</emkt></main>';														// Fecha o nó da requisição, o corpo da mensagem e o envelope

		//echo "Requisicao XML: ";																//Imprimindo as informações que serão enviadas por XML
		//print_r($requisicao);echo "\n\n";
		
		$curl = self::_prepararCurl();															// Preparar requisição sem cabeçalho, todos os dados 
		curl_setopt_array($curl, array(															// devem ser enviados via POSTFIELDS e a senha sempre em "MD5"
			CURLOPT_URL => self::URL_BASE,
			CURLOPT_POSTFIELDS => array(
				'User'=> $this->_User,
				'Pass'=> md5($this->_Pass),
				'XML'=> $requisicao,
				'Client'=> $this->_Client,
				'Remetente'=> $this->_Remetente
			)
		));
		$resposta = curl_exec($curl);															// Obtendo resultado da comunicação
		curl_close($curl);
		//print_r($resposta);																	// Exibindo dados obtidos

		if ($resposta) {
			$resposta = preg_replace('/(?<=>)\\s+(?=<)/', '', $resposta);						// Retirando os espaços entre os nós e depois o cabeçalho do XML
			$resposta = preg_replace('/(<\?(\w+)\s(\w+)="\d\.\d"\s(\w+)="(\w+)\-\d"\?>)*/', '', $resposta);
			$resposta = "<Resultado>" . $resposta . "</Resultado>";								// Inserindo nó pai, caos ocorrea alguma repetição do nó MAIN
			$xml = simplexml_load_string($resposta);											// Lendo a resposta
			
			$resultado = json_encode($xml);														// Transformando em JSON o resultado 
			$resultado = json_decode($resultado,true);											// Transformando em array o resultado
			//print_r($resultado);echo "\n\n";
			for ($i=0;$i<count($resultado['MAIN']);$i++) {										// Pegando as respostas quando for mais de uma
				if (count($resultado['MAIN']) == 1)
					$resultadoShort = $resultado['MAIN']['EMKT']['RETURN'];
				else
					$resultadoShort[$i] = $resultado['MAIN'][$i]['EMKT']['RETURN'];
			}
		} else {
			$this->_erro = 'Não foi possível conectar-se à Akna';
			return false;
		}

		$resultadoShort = array("RETURN"=> $resultadoShort);									// Motando array de retorno
		//print_r($resultadoShort);echo "\n\n";
		// Unindo a Requisição e o resultado para serem retornados
		$RequisicaoResultado = $requisicao . "¹" . json_encode($resultadoShort) . "¹" . json_encode($resultado);
		$this->_erro = 'Conexão realizada com sucesso!';
		//print_r($RequisicaoResultado);echo "\n\n";

		return $RequisicaoResultado;															// Retornado a requisição e o resultado
		
	}

	/** Recebe uma string contendo o codigo do envio da SMS, realiza a chamada e retorna o resultado da Akna no formato de XML.
	 * @param string $parametro	string com o cod do envio
	 * @return array Transcrição da resposta da Akna em array
	 */
	function conectApiConsultarStatusEnvio($parametro)
	{
		$this->_erro = false;
		
		// Montar envelope contendo a requisição do serviço
		$requisicao = '<?xml version="1.0" encoding="UTF-8"?><main><emkt trans="40.02">';
		$requisicao .= "<sms><codigo>" . htmlspecialchars($parametro['codigo']) . "</codigo></sms>";			
		$requisicao .= '</emkt></main>';														// Fecha o nó da requisição, o corpo da mensagem e o envelope

		//echo "Requisicao XML: ";																//Imprimindo as informações que serão enviadas por XML
		//print_r($requisicao);echo "\n\n";
		
		$curl = self::_prepararCurl();															// Preparar requisição sem cabeçalho, todos os dados 
		curl_setopt_array($curl, array(															// devem ser enviados via POSTFIELDS e a senha sempre em "MD5"
			CURLOPT_URL => self::URL_BASE,
			CURLOPT_POSTFIELDS => array(
				'User'=> $this->_User,
				'Pass'=> md5($this->_Pass),
				'XML'=> $requisicao,
				'Client'=> $this->_Client,
				'Remetente'=> $this->_Remetente
			)
		));
		$resposta = curl_exec($curl);															// Obtendo resultado da comunicação
		curl_close($curl);
		//print_r($resposta);																	// Exibindo dados obtidos

		if ($resposta) {
			$resposta = preg_replace('/(?<=>)\\s+(?=<)/', '', $resposta);						// Retirando os espaços entre os nós e depois o cabeçalho do XML
			$resposta = preg_replace('/(<\?(\w+)\s(\w+)="\d\.\d"\s(\w+)="(\w+)\-\d"\?>)*/', '', $resposta);
			$resposta = "<Resultado>" . $resposta . "</Resultado>";								// Inserindo nó pai, caos ocorrea alguma repetição do nó MAIN
			$xml = simplexml_load_string($resposta);											// Lendo a resposta
			
			$resultado = json_encode($xml);														// Transformando em JSON o resultado 
			$resultado = json_decode($resultado,true);											// Transformando em array o resultado
			//print_r($resultado);echo "\n\n";
			for ($i=0;$i<count($resultado['MAIN']);$i++) {										// Pegando as respostas quando for mais de uma
				if (count($resultado['MAIN']) == 1)
					$resultadoShort = $resultado['MAIN']['EMKT']['RETURN'];
				else
					$resultadoShort[$i] = $resultado['MAIN'][$i]['EMKT']['RETURN'];
			}
		} else {
			$this->_erro = 'Não foi possível conectar-se à Akna';
			return false;
		}

		$resultadoShort = array("RETURN"=> $resultadoShort);									// Motando array de retorno
		//print_r($resultadoShort);echo "\n\n";
		// Unindo a Requisição e o resultado para serem retornados
		$RequisicaoResultado = $requisicao . "¹" . json_encode($resultadoShort) . "¹" . json_encode($resultado);
		$this->_erro = 'Conexão realizada com sucesso!';
		//print_r($RequisicaoResultado);echo "\n\n";

		return $RequisicaoResultado;															// Retornado a requisição e o resultado
			
	}
	
	/** Recebe uma string contendo o codigo do envio da SMS, realiza a chamada e retorna o resultado da Akna no formato de XML.
	 * @param string $parametro	string com o cod do envio
	 * @return array Transcrição da resposta da Akna em array
	 */
	function conectApiConsultarRespDest($parametro)
	{
		$this->_erro = false;
		
		// Montar envelope contendo a requisição do serviço
		$requisicao = '<?xml version="1.0" encoding="UTF-8"?><main><emkt trans="40.03">';
		$requisicao .= "<sms><codigo>" . htmlspecialchars($parametro['codigo']) . "</codigo></sms>";			
		$requisicao .= '</emkt></main>';														// Fecha o nó da requisição, o corpo da mensagem e o envelope

		//echo "Requisicao XML: ";																//Imprimindo as informações que serão enviadas por XML
		//print_r($requisicao);echo "\n\n";
		
		$curl = self::_prepararCurl();															// Preparar requisição sem cabeçalho, todos os dados 
		curl_setopt_array($curl, array(															// devem ser enviados via POSTFIELDS e a senha sempre em "MD5"
			CURLOPT_URL => self::URL_BASE,
			CURLOPT_POSTFIELDS => array(
				'User'=> $this->_User,
				'Pass'=> md5($this->_Pass),
				'XML'=> $requisicao,
				'Client'=> $this->_Client,
				'Remetente'=> $this->_Remetente
			)
		));
		$resposta = curl_exec($curl);															// Obtendo resultado da comunicação
		curl_close($curl);
		//print_r($resposta);																	// Exibindo dados obtidos

		if ($resposta) {
			$resposta = preg_replace('/(?<=>)\\s+(?=<)/', '', $resposta);						// Retirando os espaços entre os nós e depois o cabeçalho do XML
			$resposta = preg_replace('/(<\?(\w+)\s(\w+)="\d\.\d"\s(\w+)="(\w+)\-\d"\?>)*/', '', $resposta);
			$resposta = "<Resultado>" . $resposta . "</Resultado>";								// Inserindo nó pai, caos ocorrea alguma repetição do nó MAIN
			$xml = simplexml_load_string($resposta);											// Lendo a resposta
			
			$resultado = json_encode($xml);														// Transformando em JSON o resultado 
			$resultado = json_decode($resultado,true);											// Transformando em array o resultado
			//print_r($resultado);echo "\n\n";
			for ($i=0;$i<count($resultado['MAIN']);$i++) {										// Pegando as respostas quando for mais de uma
				if (count($resultado['MAIN']) == 1)
					$resultadoShort = $resultado['MAIN']['EMKT']['RETURN'];
				else
					$resultadoShort[$i] = $resultado['MAIN'][$i]['EMKT']['RETURN'];
			}
		} else {
			$this->_erro = 'Não foi possível conectar-se à Akna';
			return false;
		}

		$resultadoShort = array("RETURN"=> $resultadoShort);									// Motando array de retorno
		//print_r($resultadoShort);echo "\n\n";
		// Unindo a Requisição e o resultado para serem retornados
		$RequisicaoResultado = $requisicao . "¹" . json_encode($resultadoShort) . "¹" . json_encode($resultado);
		$this->_erro = 'Conexão realizada com sucesso!';
		//print_r($RequisicaoResultado);echo "\n\n";

		return $RequisicaoResultado;															// Retornado a requisição e o resultado
		
	}
	
	/** Recebe um array contendo o identificador(es), telefone(s), envio ou clique, realiza a chamada e retorna o resultado da Akna no formato de XML.
	 * @param array $parametros Array com mapeamento nome -> valor
	 * @return array Transcrição da resposta da Akna em array
	 */
	function conectApiSolicitaRelatorioSMS($parametros)
	{
		$this->_erro = false;
		
		// Montar envelope contendo a requisição do serviço
		$requisicao = '<?xml version="1.0" encoding="UTF-8"?><main><emkt trans="40.04">';
		foreach ($parametros as $no => &$valor)
			$requisicao .= "<$no>" . htmlspecialchars($valor) . "</$no>";
			
		$requisicao .= '</emkt></main>';														// Fecha o nó da requisição, o corpo da mensagem e o envelope

		//echo "Requisicao XML: ";																//Imprimindo as informações que serão enviadas por XML
		//print_r($requisicao);echo "\n\n";
		
		$curl = self::_prepararCurl();															// Preparar requisição sem cabeçalho, todos os dados 
		curl_setopt_array($curl, array(															// devem ser enviados via POSTFIELDS e a senha sempre em "MD5"
			CURLOPT_URL => self::URL_BASE,
			CURLOPT_POSTFIELDS => array(
				'User'=> $this->_User,
				'Pass'=> md5($this->_Pass),
				'XML'=> $requisicao,
				'Client'=> $this->_Client,
				'Remetente'=> $this->_Remetente
			)
		));
		$resposta = curl_exec($curl);															// Obtendo resultado da comunicação
		curl_close($curl);
		//print_r($resposta);																	// Exibindo dados obtidos

		if ($resposta) {
			$resposta = preg_replace('/(?<=>)\\s+(?=<)/', '', $resposta);						// Retirando os espaços entre os nós e depois o cabeçalho do XML
			$resposta = preg_replace('/(<\?(\w+)\s(\w+)="\d\.\d"\s(\w+)="(\w+)\-\d"\?>)*/', '', $resposta);
			$resposta = "<Resultado>" . $resposta . "</Resultado>";								// Inserindo nó pai, caos ocorrea alguma repetição do nó MAIN
			$xml = simplexml_load_string($resposta);											// Lendo a resposta
			
			$resultado = json_encode($xml);														// Transformando em JSON o resultado 
			$resultado = json_decode($resultado,true);											// Transformando em array o resultado
			//print_r($resultado);echo "\n\n";
			for ($i=0;$i<count($resultado['MAIN']);$i++) {										// Pegando as respostas quando for mais de uma
				if (count($resultado['MAIN']) == 1)
					$resultadoShort = $resultado['MAIN']['EMKT']['RETURN'];
				else
					$resultadoShort[$i] = $resultado['MAIN'][$i]['EMKT']['RETURN'];
			}
		} else {
			$this->_erro = 'Não foi possível conectar-se à Akna';
			return false;
		}

		$resultadoShort = array("RETURN"=> $resultadoShort);									// Motando array de retorno
		//print_r($resultadoShort);echo "\n\n";
		// Unindo a Requisição e o resultado para serem retornados
		$RequisicaoResultado = $requisicao . "¹" . json_encode($resultadoShort) . "¹" . json_encode($resultado);
		$this->_erro = 'Conexão realizada com sucesso!';
		//print_r($RequisicaoResultado);echo "\n\n";

		return $RequisicaoResultado;															// Retornado a requisição e o resultado
		
	}


	/** Descrição do erro
	 * @return string|bool	Descrição do erro ou "false", se não ocorreu erro
	 */
	function obterErro()
	{
		return $this->_erro ?: false;
	}
}

?>