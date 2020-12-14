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

	/** Função >> 40.01 << recebe um array com os parametros e realiza a conexão, destinada para o envio de SMS para os contatos.
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
		
		// Montando a requisição sem inseri o atributo "identificador" que não é obrigatório
		$requisicao = '<?xml version="1.0" encoding="UTF-8"?><main><emkt trans="40.01"><remetente>Conecta</remetente><encurtar_url>S</encurtar_url>';
		for ($i=0;$i<count($parametros['sms']);$i++) {
			$requisicao .= "<sms><telefone>" . $parametros['sms'][$i]['telefone'] . "</telefone>";
			$requisicao .= "<mensagem>" . $parametros['sms'][$i]['mensagem'] . "</mensagem></sms>";
		}
		$requisicao .= '</emkt></main>';														// Fecha o nó da requisição, o corpo da mensagem e o envelope

		//echo "Requisicao XML: ";																// Imprimindo as informações que serão enviadas por XML
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
		//print_r($resposta);echo "\n\n";														// Exibindo dados obtidos

		$mensagemInformativa = '';																// Mensagem de retorno

		if ($resposta) {
			$resposta = preg_replace('/(?<=>)\\s+(?=<)/', '', $resposta);						// Retirando os espaços entre os nós e depois o cabeçalho do XML
			$resposta = preg_replace('/(<\?(\w+)\s(\w+)="\d\.\d"\s(\w+)="(\w+)\-\d"\?>)*/', '', $resposta);
			$resposta = "<Resultado>" . $resposta . "</Resultado>";								// Inserindo nó pai, caos ocorrea alguma repetição do nó MAIN
			$xml = simplexml_load_string($resposta);											// Lendo a resposta
			
			$resultado = json_encode($xml);														// Transformando em JSON o resultado 
			$resultado = json_decode($resultado,true);											// Transformando em array o resultado
			//print_r($resultado);echo "\n\n";
			for ($i=0;$i<count($resultado['MAIN']);$i++) {										// Pegando as respostas quando for mais de uma
				if (count($resultado['MAIN']) == 1){
					$resultadoShort = $resultado['MAIN']['EMKT']['RETURN'];
					$mensagemInformativa .= $resultadoShort;
				} else {
					$resultadoShort[$i] = $resultado['MAIN'][$i]['EMKT']['RETURN'];
					$mensagemInformativa .= $resultadoShort[$i];
				}
			}

			$this->_erro = $mensagemInformativa;												// Mensagem de SUCESSO ou ERRO
			
			if (isset($resultado['AKNA'])) {													// Tratando quando houver algum erro no texto da mensagem
				for ($i=0;$i<count($resultado['AKNA']);$i++) {									
					if (isset($resultado['AKNA'][$i]['FUNC'])){
						$resultadoShort[$i] = $resultado['AKNA'][$i]['FUNC']['RETURN'];
						$mensagemInformativa .= $resultadoShort[$i];
					}						
					if (isset($resultado['AKNA'][$i]['EMKT'])){
						$resultadoShort[$i] = $resultado['AKNA'][$i]['EMKT']['RETURN'];
						$mensagemInformativa .= $resultadoShort[$i];
					}
				}
				
				$this->_erro = $mensagemInformativa;											// Mensagem de ERRO
			}
		} else {
			$this->_erro = 'Não foi possível conectar-se à Akna';
			return false;
		}

		$resultadoShort = array("RETURN"=> $resultadoShort);									// Motando array de retorno
		//print_r($resultadoShort);echo "\n\n";
		// Unindo a Requisição e o resultado para serem retornados
		$RequisicaoResultado = $requisicao . "¹" . json_encode($resultadoShort) . "¹" . json_encode($resultado);
		//print_r($RequisicaoResultado);echo "\n\n";

		return $RequisicaoResultado;															// Retornado a requisição e o resultado
		
	}

	/** Função >> 40.02 << recebe uma string contendo o codigo de retorno do envio da SMS,
	 * destinad a verificar o status do envio do SMS
	 * @param string $parametro	string com o cod do envio
	 * @return array Transcrição da resposta da Akna em array
	 */
	function conectApiConsultarStatusEnvio($parametro)
	{
		$this->_erro = false;
		
		// Montar envelope contendo a requisição do serviço
		$requisicao = '<?xml version="1.0" encoding="UTF-8"?><main><emkt trans="40.02">';
		$requisicao .= "<sms><codigo>" . $parametro . "</codigo></sms>";			
		$requisicao .= '</emkt></main>';														// Fecha o nó da requisição, o corpo da mensagem e o envelope

		//echo "Requisicao XML: ";																// Imprimindo as informações que serão enviadas por XML
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
		//print_r($resposta);echo "\n\n";														// Exibindo dados obtidos

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
					$resultadoShort = $resultado['MAIN']['EMKT']['SMS'];
				else
					$resultadoShort[$i] = $resultado['MAIN'][$i]['EMKT']['SMS'];
			}
			
			$mensagemInformativa = '';
			
			if (isset($resultado['MAIN']['EMKT']['RETURN'])) {
				$resultadoShort = $resultado['MAIN']['EMKT']['RETURN'];
				$mensagemInformativa .= $resultadoShort;
				$resultadoShort = array("RETURN"=> $resultadoShort);							// Motando array de retorno
				//print_r($resultadoShort);echo "\n\n";
				$this->_erro = $mensagemInformativa;											// Mensagem de ERRO
			} else {
				$resultadoShort = array("SMS"=> $resultadoShort);								// Motando array de retorno
				$mensagemInformativa .= json_encode($resultadoShort);
				//print_r($resultadoShort);echo "\n\n";
				$this->_erro = $mensagemInformativa;											// Mensagem de SUCESSO
			}

		} else {
			$this->_erro = 'Não foi possível conectar-se à Akna';
			return false;
		}

		// Unindo a Requisição e o resultado para serem retornados
		$RequisicaoResultado = $requisicao . "¹" . json_encode($resultadoShort) . "¹" . json_encode($resultado);
		//print_r($RequisicaoResultado);echo "\n\n";

		return $RequisicaoResultado;															// Retornado a requisição e o resultado
			
	}
	
	/** Função >> 40.03 << Recebe uma string contendo o codigo de retorno do envio da SMS, 
	 * destinada a verificar se houve resposta do destinatário do SMS enviado
	 * @param string $parametro	string com o cod do envio
	 * @return array Transcrição da resposta da Akna em array
	 */
	function conectApiConsultarRespDest($parametro)
	{
		$this->_erro = false;
		
		// Montar envelope contendo a requisição do serviço
		$requisicao = '<?xml version="1.0" encoding="UTF-8"?><main><emkt trans="40.03">';
		$requisicao .= "<sms><codigo>" . $parametro . "</codigo></sms>";			
		$requisicao .= '</emkt></main>';														// Fecha o nó da requisição, o corpo da mensagem e o envelope

		//echo "Requisicao XML: ";																// Imprimindo as informações que serão enviadas por XML
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
		//print_r($resposta);echo "\n\n";														// Exibindo dados obtidos

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
					$resultadoShort = $resultado['MAIN']['EMKT']['SMS'];
				else
					$resultadoShort[$i] = $resultado['MAIN'][$i]['EMKT']['SMS'];
			}
			
			$mensagemInformativa = '';
			
			if (isset($resultado['MAIN']['EMKT']['RETURN'])) {
				$resultadoShort = $resultado['MAIN']['EMKT']['RETURN'];
				$mensagemInformativa .= $resultadoShort;
				$resultadoShort = array("RETURN"=> $resultadoShort);							// Motando array de retorno
				//print_r($resultadoShort);echo "\n\n";
				$this->_erro = $mensagemInformativa;											// Mensagem de ERRO
			} else {
				$resultadoShort = array("SMS"=> $resultadoShort);								// Motando array de retorno
				$mensagemInformativa .= json_encode($resultadoShort);
				//print_r($resultadoShort);echo "\n\n";
				$this->_erro = $mensagemInformativa;											// Mensagem de SUCESSO
			}

		} else {
			$this->_erro = 'Não foi possível conectar-se à Akna';
			return false;
		}

		// Unindo a Requisição e o resultado para serem retornados
		$RequisicaoResultado = $requisicao . "¹" . json_encode($resultadoShort) . "¹" . json_encode($resultado);
		//print_r($RequisicaoResultado);echo "\n\n";

		return $RequisicaoResultado;															// Retornado a requisição e o resultado
		
	}
	
	/** Função >> 40.04 << recebe um array com os parametros e realiza a conexão, destinada à solicitação
	 * de relatórios de clique de SMS TRANSACIONAL
	 * CAMPOS NÃO OBRIGATÓRIOS: "identificador" quando se tem o código de retorno da função "40.01" e pode ser passado mais de um,
	 * "telefone" para os quais foram enviados os SMS e pode ser passado mais de um,
	 * "envio" para informar o "inicio" e "fim" do período q deseja pesquisar, com no máximo das ultimas 48horas,
	 * "clique" para informar o "inicio" e "fim" do período q deseja pesquisar, com no máximo das ultimas 48horas,
	 * Obs.: deve ser passado ou o campo "envio" ou o "clique", nunca os dois juntos e como retorno, será
	 * um código codificado em MD5
	 * @param array $parametros Array com mapeamento nome -> valor
	 * @return array Transcrição da resposta da Akna em array
	 */
	function conectApiSolicitaRelatorioSMS($parametros)
	{
		$this->_erro = false;
		
		// Montar envelope contendo a requisição do serviço
		$requisicao = '<?xml version="1.0" encoding="UTF-8"?><main><emkt trans="40.04"><sms>';
		if (isset($parametros['sms']['identificador'])) {										// Obtendo informações do identificador(es)
			$requisicao .= "<identificador>";
			for ($i=0;$i<count($parametros['sms']['identificador']);$i++) {
				if ($i == (count($parametros['sms']['identificador']) - 1))
					$requisicao .= "[" . $parametros['sms']['identificador'][$i] . "]";
				else
					$requisicao .= "[" . $parametros['sms']['identificador'][$i] . "],";
			}
			$requisicao .= "</identificador>";
		}
		if (isset($parametros['sms']['telefone'])) {											// Obtendo informações do telefone(s)
			$requisicao .= "<telefone>";
			for ($i=0;$i<count($parametros['sms']['telefone']);$i++) {
				if ($i == (count($parametros['sms']['telefone']) - 1))
					$requisicao .= "[" . $parametros['sms']['telefone'][$i] . "]";
				else
					$requisicao .= "[" . $parametros['sms']['telefone'][$i] . "],";
			}
			$requisicao .= "</telefone>";
		}
		if (isset($parametros['sms']['envio'])) {												// Obtendo informações do horário do envio
			$requisicao .= "<envio><inicio>" . $parametros['sms']['envio']['inicio'] . "</inicio>";
			$requisicao .= "<fim>" . $parametros['sms']['envio']['fim'] . "</fim></envio>";
		}
		if (isset($parametros['sms']['clique'])) {												// Obtendo informações do horário do clique
			$requisicao .= "<clique><inicio>" . $parametros['sms']['clique']['inicio'] . "</inicio>";
			$requisicao .= "<fim>" . $parametros['sms']['clique']['fim'] . "</fim></clique>";
		}
		$requisicao .= '</sms></emkt></main>';													// Fecha o nó da requisição, o corpo da mensagem e o envelope

		echo "Requisicao XML: ";																//Imprimindo as informações que serão enviadas por XML
		print_r($requisicao);echo "\n\n";
		
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
		print_r($resposta);echo "\n\n";														// Exibindo dados obtidos

		if ($resposta) {
			$resposta = preg_replace('/(?<=>)\\s+(?=<)/', '', $resposta);						// Retirando os espaços entre os nós e depois o cabeçalho do XML
			$resposta = preg_replace('/(<\?(\w+)\s(\w+)="\d\.\d"\s(\w+)="(\w+)\-\d"\?>)*/', '', $resposta);
			$resposta = "<Resultado>" . $resposta . "</Resultado>";								// Inserindo nó pai, caos ocorrea alguma repetição do nó MAIN
			$xml = simplexml_load_string($resposta);											// Lendo a resposta
			
			$resultado = json_encode($xml);														// Transformando em JSON o resultado 
			$resultado = json_decode($resultado,true);											// Transformando em array o resultado
			print_r($resultado);echo "\n\n";
			for ($i=0;$i<count($resultado['MAIN']);$i++) {										// Pegando as respostas quando for mais de uma
				if (count($resultado['MAIN']) == 1)
					$resultadoShort = $resultado['MAIN']['EMKT']['PROCESSO'];
				else
					$resultadoShort[$i] = $resultado['MAIN'][$i]['EMKT']['PROCESSO'];
			}
			
			$mensagemInformativa = '';
			
			if (isset($resultado['MAIN']['EMKT']['RETURN'])) {
				$resultadoShort = $resultado['MAIN']['EMKT']['RETURN'];
				$mensagemInformativa .= $resultadoShort;
				$resultadoShort = array("RETURN"=> $resultadoShort);							// Motando array de retorno
				print_r($resultadoShort);echo "\n\n";
				$this->_erro = $mensagemInformativa;											// Mensagem de ERRO
			} else {
				$resultadoShort = array("PROCESSO"=> $resultadoShort);							// Motando array de retorno
				$mensagemInformativa .= json_encode($resultadoShort);
				print_r($resultadoShort);echo "\n\n";
				$this->_erro = $mensagemInformativa;											// Mensagem de SUCESSO
			}

		} else {
			$this->_erro = 'Não foi possível conectar-se à Akna';
			return false;
		}

		// Unindo a Requisição e o resultado para serem retornados
		$RequisicaoResultado = $requisicao . "¹" . json_encode($resultadoShort) . "¹" . json_encode($resultado);
		print_r($RequisicaoResultado);echo "\n\n";

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