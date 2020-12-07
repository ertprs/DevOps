<?php
	
	include __DIR__ . DIRECTORY_SEPARATOR . "conexao.php";										//Buscando arquivo de conexão com os DB que serão utilizados

class AknaWebService 
{
	// Url necessária para a comunicação
	const URL_BASE = 'https://app.akna.com.br/emkt/int/integracao.php';

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
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_SSL_VERIFYHOST => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_POST => true,
			CURLOPT_MAXREDIRS => 3
		));
		return $curl;
	}

	/** Passa por todos os nós do XML e retorna no formato de array considerando apenas o valor do nó (nodeValue) e o nome do nó (nodeName sem namespace)
	 * @param DOMNode $no Nó a ser percorrido pela função
	 * @param Array &$resultado	Variável que deverá armazenar o resultado encontrado
	 * @return array Transcrição do formato XML em array
	 */
	static private function _converterNosXMLEmArray($no, &$resultado)							// função recursiva para incremento dos nós da montagem do XML
	{
		if ($no->firstChild && $no->firstChild->nodeType == XML_ELEMENT_NODE)
			foreach ($no->childNodes as $pos)
				self::_converterNosXMLEmArray($pos, $resultado[$pos->localName]);
		else
			$resultado = html_entity_decode(trim($no->nodeValue));
	}

	/** Recebe um array contendo o telefone e a mensagem a ser enviada, realiza a chamada e retorna o resultado da Akna no formato de XML.
	 * @param array $parametros	Array com mapeamento nome -> valor
	 * @return array Transcrição da resposta da Akna em array
	 */
	function conectApiEnvioSMS($parametros)
	{
		$this->_erro = false;
		
		// Montar envelope contendo a requisição do serviço
		$requisicao = '<?xml version="1.0" encoding="UTF-8"?><main><emkt trans="40.01"><remetente>Conecta</remetente><encurtar_url>S</encurtar_url>';
		foreach ($parametros as $no => &$valor)
			$requisicao .= "<$no>" . htmlspecialchars($valor) . "</$no>";
			
		$requisicao .= '</emkt></main>';																// Fecha o nó da requisição, o corpo da mensagem e o envelope

		//Imprimindo as informações que serão enviadas por XML
		echo "Requisicao com os dados em XML: \n";
		print_r($requisicao);

		$curl = self::_prepararCurl();															// Preparar requisição
		curl_setopt_array($curl, array(
			CURLOPT_URL => self::URL_BASE,
			CURLOPT_POSTFIELDS => &$requisicao,
			CURLOPT_HTTPHEADER => array('Content-Type: text/xml;charset=UTF-8')
		));
		$resposta = curl_exec($curl);															// Obtendo resultado da comunicação
		curl_close($curl);
		var_dump($resposta);																	// Exibindo dados obtidos

		if ($resposta) {
			$dom = new DOMDocument('1.0', 'UTF-8');												// Criar documento XML para percorrer os nós da resposta
			// Verificar se o formato recebido é um XML válido. A expressão regular executada por "preg_replace" retira espaços vazios entre tags.
			if (@$dom->loadXML(preg_replace('/(?<=>)\\s+(?=<)/', '', $resposta))) {
				// Realiza o "parse" da resposta a partir do primeiro nó no corpo do documento dentro do envelope
				$resultado = array();
				self::_converterNosXMLEmArray($dom->documentElement->firstChild->firstChild, $resultado);
			} else
				$resultado = false;
		} else {
			$this->_erro = 'Não foi possível conectar-se à Akna';
			return false;
		}

		// Se ocorreu o registro do boleto de forma correta, retorna o resultado com Sucesso
		if (is_array($resultado) && !array_key_exists('resultado', $resultado) && !isset($resultado['resultado']['erro'])){
			$this->_erro = "Sucesso! ";
			return $resultado;																		// Retorna o resultado com SUCESSO
		}

		//Imprime resposta recebida da Akna
		echo "\n\n------------------------- ERRO OCORRIDO ------------------------------\nResposta recebida da Akna com ERRO: \n";
		print_r($resultado);

		// Se ocorreu erro no registro do boleto, retorna o resultado com o erro.
		if (is_array($resultado) || array_key_exists('resultado', $resultado) || isset($resultado['resultado']['erro'])) {
			$this->_erro = is_array($resultado) ? @$resultado['resultado']['erro'] ?: @$resultado['resultado']['erro'] : 'Erro inesperado na resposta da Akna';
			return $resultado;																		// Retorna o resultado com FALHS
		}
		
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

		//Imprimindo as informações que serão enviadas por XML
		echo "Requisicao com os dados em XML: \n";
		print_r($requisicao);

		$curl = self::_prepararCurl();															// Preparar requisição
		curl_setopt_array($curl, array(
			CURLOPT_URL => self::URL_BASE,
			CURLOPT_POSTFIELDS => &$requisicao,
			CURLOPT_HTTPHEADER => array('Content-Type: text/xml;charset=UTF-8')
		));
		$resposta = curl_exec($curl);															// Obtendo resultado da comunicação
		curl_close($curl);
		var_dump($resposta);																	// Exibindo dados obtidos

		if ($resposta) {
			$dom = new DOMDocument('1.0', 'UTF-8');												// Criar documento XML para percorrer os nós da resposta
			// Verificar se o formato recebido é um XML válido. A expressão regular executada por "preg_replace" retira espaços vazios entre tags.
			if (@$dom->loadXML(preg_replace('/(?<=>)\\s+(?=<)/', '', $resposta))) {
				// Realiza o "parse" da resposta a partir do primeiro nó no corpo do documento dentro do envelope
				$resultado = array();
				self::_converterNosXMLEmArray($dom->documentElement->firstChild->firstChild, $resultado);
			} else
				$resultado = false;
		} else {
			$this->_erro = 'Não foi possível conectar-se à Akna';
			return false;
		}

		// Se ocorreu o registro do boleto de forma correta, retorna o resultado com Sucesso
		if (is_array($resultado) && !array_key_exists('resultado', $resultado) && !isset($resultado['resultado']['erro'])){
			$this->_erro = "Sucesso! ";
			return $resultado;																		// Retorna o resultado com SUCESSO
		}

		//Imprime resposta recebida da Akna
		echo "\n\n------------------------- ERRO OCORRIDO ------------------------------\nResposta recebida da Akna com ERRO: \n";
		print_r($resultado);

		// Se ocorreu erro no registro do boleto, retorna o resultado com o erro.
		if (is_array($resultado) || array_key_exists('resultado', $resultado) || isset($resultado['resultado']['erro'])) {
			$this->_erro = is_array($resultado) ? @$resultado['resultado']['erro'] ?: @$resultado['resultado']['erro'] : 'Erro inesperado na resposta da Akna';
			return $resultado;																		// Retorna o resultado com FALHS
		}
		
	}
	
	/** Recebe uma string contendo o codigo do envio da SMS, realiza a chamada e retorna o resultado da Akna no formato de XML.
	 * @param string $parametro	string com o cod do envio
	 * @return array Transcrição da resposta da Akna em array
	 */
	function conectApiConsultarResDest($parametro)
	{
		$this->_erro = false;
		
		// Montar envelope contendo a requisição do serviço
		$requisicao = '<?xml version="1.0" encoding="UTF-8"?><main><emkt trans="40.03">';
		$requisicao .= "<sms><codigo>" . htmlspecialchars($parametro['codigo']) . "</codigo></sms>";			
		$requisicao .= '</emkt></main>';														// Fecha o nó da requisição, o corpo da mensagem e o envelope

		//Imprimindo as informações que serão enviadas por XML
		echo "Requisicao com os dados em XML: \n";
		print_r($requisicao);

		$curl = self::_prepararCurl();															// Preparar requisição
		curl_setopt_array($curl, array(
			CURLOPT_URL => self::URL_BASE,
			CURLOPT_POSTFIELDS => &$requisicao,
			CURLOPT_HTTPHEADER => array('Content-Type: text/xml;charset=UTF-8')
		));
		$resposta = curl_exec($curl);															// Obtendo resultado da comunicação
		curl_close($curl);
		var_dump($resposta);																	// Exibindo dados obtidos

		if ($resposta) {
			$dom = new DOMDocument('1.0', 'UTF-8');												// Criar documento XML para percorrer os nós da resposta
			// Verificar se o formato recebido é um XML válido. A expressão regular executada por "preg_replace" retira espaços vazios entre tags.
			if (@$dom->loadXML(preg_replace('/(?<=>)\\s+(?=<)/', '', $resposta))) {
				// Realiza o "parse" da resposta a partir do primeiro nó no corpo do documento dentro do envelope
				$resultado = array();
				self::_converterNosXMLEmArray($dom->documentElement->firstChild->firstChild, $resultado);
			} else
				$resultado = false;
		} else {
			$this->_erro = 'Não foi possível conectar-se à Akna';
			return false;
		}

		// Se ocorreu o registro do boleto de forma correta, retorna o resultado com Sucesso
		if (is_array($resultado) && !array_key_exists('resultado', $resultado) && !isset($resultado['resultado']['erro'])){
			$this->_erro = "Sucesso! ";
			return $resultado;																		// Retorna o resultado com SUCESSO
		}

		//Imprime resposta recebida da Akna
		echo "\n\n------------------------- ERRO OCORRIDO ------------------------------\nResposta recebida da Akna com ERRO: \n";
		print_r($resultado);

		// Se ocorreu erro no registro do boleto, retorna o resultado com o erro.
		if (is_array($resultado) || array_key_exists('resultado', $resultado) || isset($resultado['resultado']['erro'])) {
			$this->_erro = is_array($resultado) ? @$resultado['resultado']['erro'] ?: @$resultado['resultado']['erro'] : 'Erro inesperado na resposta da Akna';
			return $resultado;																		// Retorna o resultado com FALHS
		}
		
	}
	
	/** Recebe um array contendo o identificador(es), telefone(s), envio ou clique, realiza a chamada e retorna o resultado da Akna no formato de XML.
	 * @param array $parametros Array com mapeamento nome -> valor
	 * @return array Transcrição da resposta da Akna em array
	 */
	function conectApiEnvioSMS($parametros)
	{
		$this->_erro = false;
		
		// Montar envelope contendo a requisição do serviço
		$requisicao = '<?xml version="1.0" encoding="UTF-8"?><main><emkt trans="40.04">';
		foreach ($parametros as $no => &$valor)
			$requisicao .= "<$no>" . htmlspecialchars($valor) . "</$no>";
			
		$requisicao .= '</emkt></main>';																// Fecha o nó da requisição, o corpo da mensagem e o envelope

		//Imprimindo as informações que serão enviadas por XML
		echo "Requisicao com os dados em XML: \n";
		print_r($requisicao);

		$curl = self::_prepararCurl();															// Preparar requisição
		curl_setopt_array($curl, array(
			CURLOPT_URL => self::URL_BASE,
			CURLOPT_POSTFIELDS => &$requisicao,
			CURLOPT_HTTPHEADER => array('Content-Type: text/xml;charset=UTF-8')
		));
		$resposta = curl_exec($curl);															// Obtendo resultado da comunicação
		curl_close($curl);
		var_dump($resposta);																	// Exibindo dados obtidos

		if ($resposta) {
			$dom = new DOMDocument('1.0', 'UTF-8');												// Criar documento XML para percorrer os nós da resposta
			// Verificar se o formato recebido é um XML válido. A expressão regular executada por "preg_replace" retira espaços vazios entre tags.
			if (@$dom->loadXML(preg_replace('/(?<=>)\\s+(?=<)/', '', $resposta))) {
				// Realiza o "parse" da resposta a partir do primeiro nó no corpo do documento dentro do envelope
				$resultado = array();
				self::_converterNosXMLEmArray($dom->documentElement->firstChild->firstChild, $resultado);
			} else
				$resultado = false;
		} else {
			$this->_erro = 'Não foi possível conectar-se à Akna';
			return false;
		}

		// Se ocorreu o registro do boleto de forma correta, retorna o resultado com Sucesso
		if (is_array($resultado) && !array_key_exists('resultado', $resultado) && !isset($resultado['resultado']['erro'])){
			$this->_erro = "Sucesso! ";
			return $resultado;																		// Retorna o resultado com SUCESSO
		}

		//Imprime resposta recebida da Akna
		echo "\n\n------------------------- ERRO OCORRIDO ------------------------------\nResposta recebida da Akna com ERRO: \n";
		print_r($resultado);

		// Se ocorreu erro no registro do boleto, retorna o resultado com o erro.
		if (is_array($resultado) || array_key_exists('resultado', $resultado) || isset($resultado['resultado']['erro'])) {
			$this->_erro = is_array($resultado) ? @$resultado['resultado']['erro'] ?: @$resultado['resultado']['erro'] : 'Erro inesperado na resposta da Akna';
			return $resultado;																		// Retorna o resultado com FALHS
		}
		
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