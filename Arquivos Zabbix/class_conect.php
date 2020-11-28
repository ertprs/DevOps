<?php

class ApiZabbixConection {

	private $_user;																										// Nome do usuario
	private $_password;																									// Senha do usuario
	private $_url;																										// Url necessária para a conexão

	private $_timeout = 20;																								// Tempo limite para obter resposta de 20 segundos

	// Caminho da pasta para salvar arquivos de cache
	static private $_caminhoPastaCache_estatico = __DIR__ . DIRECTORY_SEPARATOR . 'cache';
	private $_caminhoPastaCache;																						// Armazena o caminha da pasta da token obtida

	private $_erro;																										// Armazena informação sobre o erro ocorrido
	private $_tokenEmCache;																								// Armazena a última token processada pelo método obterToken()

	/** Construtor da API
	 * @param $user Nome do requisitante
	 * @param $password Senha do requisitante
	 */
	function __construct($user,$password,$url) {

		$this->_caminhoPastaCache = self::$_caminhoPastaCache_estatico;													// Padrão definido no atributo estático "_caminhoPastaCache_estatico"

		$this->_user		=& $user;
		$this->_password	=& $password;
		$this->_url			=& $url;

	}

	/** Alterar o tempo máximo para aguardar resposta
	 * @param $timeout	Tempo > 0 (em segundos) para aguardar resposta
	 */
	function alterarLimiteDeResposta($timeout) {

		$this->_timeout =& $timeout;

	}

	/** Alterar o caminho da pasta usada para cache
	 * @param $novocaminho	Novo caminho
	 * @param $usaremnovasinstancias	Usar o novo caminho em instâncias futuras?
	 */
	function trocarCaminhoDaPastaDeCache($novocaminho, $usaremnovasinstancias = false) {

		$this->_caminhoPastaCache =& $novocaminho;

		if ($usaremnovasinstancias)
			self::$_caminhoPastaCache_estatico =& $novocaminho;

	}

	/** Inicia as configurações do Curl útil para realizar as requisições de token busca de dados
	 * @return resource Curl pré-configurado
	 */
	private function _prepararCurl() {

		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_BINARYTRANSFER => true,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_SSL_VERIFYHOST => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_TIMEOUT => $this->_timeout,
			CURLOPT_MAXREDIRS => 3
		));
		
		return $curl;

	}


	/** Função para Buscar token caso não haja nenhuma em chache ou a mesma tenha expirado
	 * @param $naousarcache variavel para decidir se busca em cache o token ou nao
	 * @return String|bool Retorna uma String(token) caso tenha sucesso na confirmação do token ja cadatrado ou na nova requisição, ou false se houver erro
	 */
	function obterToken($naousarcache = true) {
		
		$this->_erro = false;																							// Declarando erro como false
		mkdir($this->_caminhoPastaCache, 0775, true);																	// Cria pasta para cache, caso ela ainda não exista

		// Define o caminho para o arquivo de cache e separa um arquivo para cada Servidor
		if (($this->_url === "http://187.94.192.153/zabbix/api_jsonrpc.php") || ($this->_url === "https://187.94.192.153/zabbix/api_jsonrpc.php")){
			$caminhodoarquivodecache = $this->_caminhoPastaCache . DIRECTORY_SEPARATOR . 'zabbix_token_cache_153.json';
		} else 
			$caminhodoarquivodecache = $this->_caminhoPastaCache . DIRECTORY_SEPARATOR . 'zabbix_token_cache_150.json';
		
		if ($naousarcache) { 																							// Se for TRUE pega da cache, se for false busca uma nova token
			if (file_exists($caminhodoarquivodecache)) {																// Testa se o arquivo existe
				
				$arquivo = fopen($caminhodoarquivodecache, 'c+');														// Abrindo o arquivo para leitura

				if ($arquivo) {																							// Se conseguir-se abrir o arquivo
					$dados = file_get_contents($caminhodoarquivodecache);												// Lê o conteúdo do arquivo
					$dados = json_decode($dados);																		// Convertendo dados Json

					// Se as credenciais informadas forem o que já está registrado em cache entao pega o token, 
					// senao chama função novamente para autenticar as novas credenciais.
					if (($dados->user == $this->_user) && ($dados->password == $this->_password)){ 					
						$this->_erro = "Token Ok! ";																	// Retorno de erro
						return $dados;																					// Retornando o resultado
					} else {
						$this->_tokenEmCache = $this->obterToken(false);												// Chamado a função para obter uma nova Token para conexão na API
						$this->_erro = $this->_erro;																	// Retorno de erro
						if ($this->_tokenEmCache)																		// Conferindo se o resultado é válido(true)
							return $this->_tokenEmCache;																// Retornando o resultado
					}						
				} else
					$this->_erro = "Não foi possível abrir o arquivo para pegar a Token em cache! ";		
				
				fclose($arquivo);																						// Fecha o arquivo				
			} else { 
				$this->_erro = "Não existe arquivo salvo em Cache para obter a Token, realizado nova solicitação! ";	// Retorno de erro
				$this->_tokenEmCache = $this->obterToken(false);									// Chamado a função para obter uma nova Token para conexão na API
				
				// Se o valor salvo em "$resultado" for um objeto e se existir o atributo "result" nele, entao salva
				// Senao emite o erro gerado da conexão
				if ($this->_tokenEmCache) {
					if (isset($this->_tokenEmCache->result)) {					
						$arquivo = fopen($caminhodoarquivodecache, 'c+');												// Tenta abrir o arquivo para leitura e escrita
						
						if ($arquivo) {																					// Se conseguir-se abrir o arquivo
							ftruncate($arquivo, 0);																		// Apaga todo o seu conteúdo
							fwrite($arquivo, json_encode($this->_tokenEmCache));										// Escreve o resultado no arquivo
						} else
							$this->_erro = "Não foi possível abrir o arquivo para salvar a Token em cache! ";		
					
						fclose($arquivo);																				// Fecha o arquivo
						return $this->_tokenEmCache;																	// Retornando o resultado				
					} else 
						$this->_erro = $this->_tokenEmCache->error;
				} else 
					$this->_erro = $this->_tokenEmCache->error;
			}
		} else {		
			$param = array(																								// Preparando transmissão dos dados
				"jsonrpc"=> "2.0",
				"method"=> "user.login",
				"params"=> array(
					"user"=> $this->_user,
					"password"=> $this->_password),
				"id"=> 1,
				"auth"=> null
			);
			$postparam = json_encode($param);																			// Convertendo para JSON
			
			$curl = self::_prepararCurl();																				// Preparar requisição do curl
			curl_setopt_array($curl, array(
				CURLOPT_CUSTOMREQUEST => 'POST',
				CURLOPT_URL => $this->_url,
				CURLOPT_POSTFIELDS => $postparam,
				CURLOPT_HTTPHEADER => array(
					'Content-Type: application/json-rpc',
					'Accept: application/json',
					'Accept-Language: pt-br'
				)
			));
			$resposta = curl_exec($curl);																				// Obtendo resultado da comunicação
			curl_close($curl);
			
			// Recebe os dados do WebService no formato JSON e realiza o parse da resposta e retorna.
			// Caso seja um valor vazio ou fora do formato, retorna false.
			$resultado = json_decode($resposta, true);
			// Acrescentando as credenciais no arquivo que ficará em cache
			$res = array(
				"user"=> $this->_user,
				"password"=> $this->_password
			);
			$resultado = json_encode(array_merge($resultado,$res));														// Concatenando os dois array
			$resultado = json_decode($resultado);
			//print_r($resultado);

			// Se o valor salvo em "$resultado" for um objeto e se existir o atributo "result" nele, entao salva
			// Senao emite o erro gerado da conexão
			if ($resultado) {
				if (isset($resultado->result)) {					
					$arquivo = fopen($caminhodoarquivodecache, 'c+');													// Tenta abrir o arquivo para leitura e escrita
					
					if ($arquivo) {																						// Se conseguir-se abrir o arquivo
						ftruncate($arquivo, 0);																			// Apaga todo o seu conteúdo
						fwrite($arquivo, json_encode($resultado));														// Escreve o resultado no arquivo
					} else
						$this->_erro = "Não foi possível abrir o arquivo para salvar a Token em cache! ";		
				
					fclose($arquivo);																					// Fecha o arquivo
					return $resultado;																					// Retornando o resultado				
				} else 
					$this->_erro = $resultado->error;
			} else 
				$this->_erro = $resultado->error;
		}
		
		return false;																									// Retornando FALSE caso não cosiga obter o token
	
	}

	
	/** Função destinada à execução de comunicação GET
	 * @param $metodo ação que será utilizada
	 * @param $parametros informações que serão passadas para buscar os dados específicos
	 * @return Object|bool Retorna um Objeto caso tenha sucesso ou false caso contrário
	 */
	function buscarDados($metodo,$parametros) {

		$token = $this->obterToken(true);																				// Obter token para conexão na API
		//var_dump($token);
		if (!$token) {																									// Se der qualquer error em obter a token, retorna "false"
			$this->_erro = $this->_erro;
			return false;
		}
		
		$param = array(																									// Preparando transmissão dos dados
			"jsonrpc"=> "2.0",
			"method"=> $metodo,
			"params"=> $parametros,
			"id"=> 1,
			"auth"=> "$token->result"
		);
		$postparam = json_encode($param);
		//print_r($postparam);
		$curl = self::_prepararCurl();																					// Preparar requisição do curl
		curl_setopt_array($curl, array(
			CURLOPT_CUSTOMREQUEST => 'POST',
			CURLOPT_URL => $this->_url,
			CURLOPT_POSTFIELDS => $postparam,
			CURLOPT_HTTPHEADER => array(
				'Content-Type: application/json-rpc',
				'Accept: application/json',
				'Accept-Language: pt-br'
			)
		));
		$resposta = curl_exec($curl);																					// Obtendo resultado da comunicação
		curl_close($curl);
		
		// Recebe os dados do WebService no formato JSON e realiza o parse da resposta e retorna.
		// Caso seja um valor vazio ou fora do formato, retorna false.
		$resultado = json_decode($resposta);
		//var_dump($resultado);

		// Se o valor salvo em "$resultado" for um objeto e não possuir erros, retorna o objeto
		if($resultado) {
			if (isset($resultado->error)) {
				$this->_erro = $resultado->error . $this->_erro;														// Retorno do erro obtido
			} else {
				return $resultado;																						// Retornando o Objeto com dados obtidos da solicitação
			}
		} else 
			$this->_erro = $resultado->error;																			// Retorno do erro obtido
		
		return false;																									// Retornando FALSE caso não cosiga obter os dados
	
	}


	/** Função destinada à execução de busca do sinal quando atraves do SN passado por parametro
	 * @param ...
	 * @return Object|bool Retorna um Objeto caso tenha sucesso ou false caso contrário
	 */
	function buscarSinal($sn,$limite) {
		
		$token = $this->obterToken(true);																				// Obter token para conexão na API
		//var_dump($token);
		if (!$token) {																									// Se der qualquer error em obter a token, retorna "false"
			$this->_erro = $this->_erro;
			return false;
		}

		// Inicializando variaveis
		$itemId = "";
		$i = 0;

		// Executa apenas duas vezes, para buscar o item e depois buscar o historico
		do {

			if ($itemId == NULL){																						// 1ª interação ainda nao se tem o itemId, buscar o mesmo pelo SN
				$param = array(																							// Preparando transmissão dos dados passnado o SN
					"jsonrpc"=> "2.0",
					"method"=> "item.get",
					"params"=> array(
						"output"=> ["extend"],
						"limit"=> ["0"],
						"search"=> array(
							"description"=> [$sn]
						)
					),
					"id"=> 1,
					"auth"=> "$token->result"
				);
			} else {																									// 2ª interação tem-se o itemId, buscar a quantidade de historico desejada
				$param = array(																							// Preparando transmissão dos dados passando o limite e o itemId
					"jsonrpc"=> "2.0",
					"method"=> "history.get",
					"params"=> array(
						"history"=> "0",
						"limit"=> $limite,																				// Quantos registros do historico deve pegar, ultimos registros
						"output"=> "extend",
						"itemids"=> $itemId
					),
					"id"=> 1,
					"auth"=> "$token->result"
				);
			}

			$postparam = json_encode($param);
			//print_r($postparam); echo "\n\n---------------\n\n";
			$curl = self::_prepararCurl();																				// Preparar requisição do curl
			curl_setopt_array($curl, array(
				CURLOPT_CUSTOMREQUEST => 'POST',
				CURLOPT_URL => $this->_url,
				CURLOPT_POSTFIELDS => $postparam,
				CURLOPT_HTTPHEADER => array(
					'Content-Type: application/json-rpc',
					'Accept: application/json',
					'Accept-Language: pt-br'
				)
			));
			$resposta = curl_exec($curl);																				// Obtendo resultado da comunicação
			curl_close($curl);

			$resultado = json_decode($resposta);
			if ($itemId == NULL)
				$itemId = $resultado->result[0]->itemid;

		
			$i = $i + 1;
		} while($i < 2);

		// Se o valor salvo em "$resultado" for um objeto e não possuir erros, retorna o objeto
		if($resultado) {
			if (isset($resultado->error)) {
				$this->_erro = $resultado->error . $this->_erro;														// Retorno do erro obtido
			} else {
				return $resultado;																						// Retornando o Objeto com dados obtidos da solicitação
			}
		} else 
			$this->_erro = $resultado->error;																			// Retorno do erro obtido
		
		return false;																									// Retornando FALSE caso não cosiga obter os dados

	}


	/** Descrição do erro
	 * @return string|bool	Descrição do erro ou "false", se não ocorreu erro
	 */
	function obterErro() {

		return $this->_erro ?: false;
	
	}
}

?>