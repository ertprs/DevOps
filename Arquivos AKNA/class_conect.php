<?php
	
	include __DIR__ . DIRECTORY_SEPARATOR . "conexao.php";										//Buscando arquivo de conexão com os bancos de dados que serão utilizados

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

	// Caminho da pasta para salvar arquivos de cache
	static private $_caminhoPastaCache_estatico = __DIR__ . DIRECTORY_SEPARATOR . 'cache';
	private $_caminhoPastaCache;

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

	/** Inicia as configurações do Curl útil para realizar as requisições de token e registro de boleto
	 * @param bool $naousarcache Especifica se o programador aceita ou não obter uma token já salva em cache
	 * @return object|bool Objeto, caso o token foi recebido com êxito, ou false, caso contrário
	 */
	function obterToken($naousarcache = true) {
		
		$this->_erro = false;																	// Declarando erro como falso
		@mkdir($this->_caminhoPastaCache, 0757, true);											// Cria pasta para cache, caso ela ainda não exista

		// Define o caminho para o arquivo de cache
		$caminhodoarquivodecache = $this->_caminhoPastaCache . DIRECTORY_SEPARATOR . 'bb_token_cache.json';
		
		if ($naousarcache) { 																	// Se for TRUE pega da cache, se for false busca uma nova token
			if (file_exists($caminhodoarquivodecache)) {										// Testa se o arquivo existe

				// Se o arquivo existir, retorna o timestamp da última modificação. Se não, retorna "false"
				$timedamodificacao = filemtime($caminhodoarquivodecache);
				
				//Obtendo a data do sistema
				$timezone  = -3;
				$dataSistem = gmdate(time() + 3600*($timezone+date("I")));//gmdate("d F Y H:i:s.", time() + 3600*($timezone+date("I")));
				echo "Sistema:\t";print_r($dataSistem);//date("d F Y H:i:s.",$dataSistem));

				$arquivo = fopen($caminhodoarquivodecache, 'c+');								// Abrindo arquivo para leitura e escrita

				if ($arquivo) {																	// Se conseguir-se abrir o arquivo
					$dados = file_get_contents($caminhodoarquivodecache);						// Lê o conteúdo do arquivo
					$dados = json_decode($dados);												// Convertendo dados Json

					$validadeVencida = $dados->expires_in/2 + $timedamodificacao;//date("d F Y H:i:s.",$dados->expires_in/2 + $timedamodificacao);
					echo "\n";
					echo "Cache:\t\t";print_r($validadeVencida);//date("d F Y H:i:s.",$validadeVencida));//
					echo "\n\n";

					if ($dataSistem < $validadeVencida){ 										// Testa se o token chegou ate antes da metade do tempo de expiração
						$this->_erro = "Token OK! ";											// Retorno de erro
						return $this->_tokenEmCache = (object) array(							// Retorno do objeto com a token em chache
							'token' => $dados->access_token,
							'cache' => true
						);
					} else {																	// Buscando uma nova token
						echo "\npassou";
						$this->_erro = "Token em cache expirou, foi necessário atualizar a Token! ";
						$this->_tokenEmCache = $this->obterToken(false);						// Chamado a função para obter uma nova Token para conexão na API
						return $this->_tokenEmCache = (object) array(							// Retorno do objeto com a token
							'token' => $dados->access_token,
							'cache' => false
						);
					}
				} else
					$this->_erro = "Não foi possível abrir o arquivo para pegar a Token em cache! ";		
			
				fclose($arquivo);																// Fecha o arquivo
			} else {
				$this->_erro = "Não existe arquivo salvo em Cache para obter a Token, realizado nova solicitação de Token! ";
				$this->_tokenEmCache = $this->obterToken(false);								// Chamado a função para obter uma nova Token para conexão na API
				return $this->_tokenEmCache = (object) array(									// Retorno do objeto com a token
					'token' => $this->_tokenEmCache->access_token,
					'cache' => false
				);
			}
		} else {

			$curl = self::_prepararCurl();														//Preparar requisição curl
			curl_setopt_array($curl, array(
				CURLOPT_URL => self::$_urls[$this->_ambiente]['token'],
				CURLOPT_POSTFIELDS => 'grant_type=client_credentials&scope=cobranca.registro-boletos',
				CURLOPT_HTTPHEADER => array(
					'Content-Type: application/x-www-form-urlencoded',
					'Authorization: Basic [' . base64_encode($this->_clientID . ':' . $this->_secret),
					'Cache-Control: no-cache'
				)
			));
			$resposta = curl_exec($curl);														// Obtendo resultado da comunicação
			curl_close($curl);
			
			// Recebe os dados do WebService no formato JSON e realiza o parse da resposta e retorna.
			// Caso seja um valor vazio ou fora do formato, retorna false.
			$resultado = json_decode($resposta);
			//print_r($resultado);

			// Se o valor salvo em "$resultado" for um objeto e se existir o atributo "access_token" nele...
			if ($resultado) {
				if (isset($resultado->access_token)) {
					$arquivo = fopen($caminhodoarquivodecache, 'c+');							// Tenta abrir o arquivo para leitura e escrita

					if ($arquivo) {																// Se conseguir-se abrir o arquivo...
						ftruncate($arquivo, 0);													// Apaga todo o seu conteúdo
						fwrite($arquivo, json_encode($resultado));								// Escreve a token no arquivo
					} else
						$this->_erro = "Não foi possível abrir o arquivo para salvar a Token em cache! ";

					fclose($arquivo);															// Fecha o arquivo
					return $resultado = (object) array(											// Retorno do objeto com a token
						'token' => $resultado->access_token,
						'cache' => false
					);
				} else 
					$this->_erro = $resultado->error_description ?: 'Erro inesperado na resposta do Banco do Brasil';
			} else
				$this->_erro = 'Não foi possível conectar-se ao Banco do Brasil';
		}	

		return false;
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

	/** Recebe um array contendo o mapeamento "campo WSDL" -> "valor", conforme descrito na página 18 e 19 da especificação do WebService, realiza a chamada
	 * e retorna o resultado do Banco do Brasil no formato array ao invés de XML.
	 * @param array $data	Array com mapeamento nome -> valor conforme descrito na página 18 e 19 da especificação (vide)
	 * @param string $token Token recebida após requisição ao método "obterToken". Se não for informada, o método o obtém automaticamente. O método prioriza uma token já obtida e salva em cache, mas se ela já expirou, ele tenta renová-la automaticamente. Não é parâmetro obrigatório. Se for informada, o método apenas tenta registrar o boleto a usando. Se a token já expirou, ele não tenta renová-la automaticamente.
	 * @return array|bool Transcrição da resposta do WebService em array ou "false" em caso de falha
	 */
	function conectApi()
	{
		$this->_erro = false;

		// Montar envelope contendo a requisição do serviço
		$requisicao = ' <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:sch="http://www.tibco.com/schemas/bws_registro_cbr/Recursos/XSD/Schema.xsd">
						<soapenv:Header/>
						<soapenv:Body>
						<sch:requisicao>';

		// Coloca cada parâmetro na requisição
		foreach ($parametros as $no => &$valor)
			$requisicao .= "<sch:$no>" . htmlspecialchars($valor) . "</sch:$no>";

		// Fecha o nó da requisição, o corpo da mensagem e o envelope
		$requisicao .= '</sch:requisicao>
						</soapenv:Body>
						</soapenv:Envelope>';

		//Imprimindo as informações que serão enviadas por XML
		//echo "Requisicao com os dados do cliente em XML: \n";
		//print_r($requisicao);

		$token = $this->obterToken(true);														// Obter token para conexão na API
		print_r($token);

		$curl = self::_prepararCurl();															// Preparar requisição
		curl_setopt_array($curl, array(
			CURLOPT_URL => self::$_urls[$this->_ambiente]['registro'],
			CURLOPT_POSTFIELDS => &$requisicao,
			CURLOPT_HTTPHEADER => array(
				'Content-Type: text/xml;charset=UTF-8',
				"Authorization: Bearer " . $token->token,
				'SOAPAction: registrarBoleto'
			)
		));
		$resposta = curl_exec($curl);															// Obtendo resultado da comunicação
		curl_close($curl);
		//var_dump($resposta);																	// Exibindo dados obtidos

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
			$this->_erro = 'Não foi possível conectar-se ao Banco do Brasil';
			return false;
		}

		// Se ocorreu o registro do boleto de forma correta, retorna o resultado com Sucesso
		if (is_array($resultado) && array_key_exists('codigoRetornoPrograma', $resultado) && $resultado['codigoRetornoPrograma'] == 0){
			$this->_erro = " Boleto foi registrado com Sucesso. ";
			return $resultado;																		// Retorna o resultado com SUCESSO
		}

		//Imprime resposta recebida do banco
		//echo "\n\n------------------------- ERRO OCORRIDO ------------------------------\nResposta recebida do Banco com ERRO de validação do boleto: \n";
		//print_r($resultado);

		// Se ocorreu erro no registro do boleto, retorna o resultado com o erro.
		if (is_array($resultado) || array_key_exists('textoMensagemErro', $resultado)) {
			$this->_erro = is_array($resultado) ? @$resultado['detail']['erro']['Mensagem'] ?: @$resultado['textoMensagemErro'] : 'Erro inesperado na resposta do Banco do Brasil';
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