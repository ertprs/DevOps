<?php

class ApiHuggyConection {
	// Urls necessárias para a comunicação
	const URL_BASE = 'https://api.huggy.app/v3/';
	const URL_TOKEN = 'https://auth.huggy.app/oauth/access_token';

	private $_clientID;														// Cliente ID de comunicação
	private $_secret;														// Secret ID de comunicação
	private $_code;															// Code obtido para validação e busca da token
	private $_redirect_uri;													// URL que recebe os dados de comunicação da Huggy

	private $_timeout = 20;													// Tempo limite para obter resposta de 20 segundos

	// Caminho da pasta para salvar arquivos de cache
	static private $_caminhoPastaCache_estatico = __DIR__ . DIRECTORY_SEPARATOR . 'cache';
	private $_caminhoPastaCache;											// Armazena o caminha da pasta da token obtida

	private $_erro;															// Armazena informação sobre o erro ocorrido
	private $_tokenEmCache;													// Armazena a última token processada pelo método obterToken()

	/** Construtor da API
	 * @param $clientid Identificação do requisitante
	 * @param $secret Segredo ("Senha") do requisitante
	 * @param $code variavel de direcionamento do App
	 * @param $redirect_uri link de redirecionamento
	 */
	function __construct($clientid, $secret, $code, $redirect_uri) {
		// Usar, por padrão, o caminho definido no atributo estático "_caminhoPastaCache_estatico"
		$this->_caminhoPastaCache = self::$_caminhoPastaCache_estatico;

		$this->_clientID		=& $clientid;
		$this->_secret			=& $secret;
		$this->_code			=& $code;
		$this->_redirect_uri	=& $redirect_uri;
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
	 * @param $naousarcache Especifica se o programador aceita ou não obter uma token já salva em cache
	 * @return Object|bool Retorna um Objeto caso tenha sucesso ou false caso contrário
	 */
	function obterToken($naousarcache = true) {
		
		$this->_erro = false;												// Decalrando erro como false
		@mkdir($this->_caminhoPastaCache, 0757, true); 						// Cria pasta para cache, caso ela ainda não exista

		// Define o caminho para o arquivo de cache
		$caminhodoarquivodecache = $this->_caminhoPastaCache . DIRECTORY_SEPARATOR . 'huggy_token_cache.json';
		
		if ($naousarcache) { 												// Se for TRUE pega da cache, se for false busca uma nova token
			if (file_exists($caminhodoarquivodecache)) {					// Testa se o arquivo existe
				
				// Se o arquivo existir, retorna o timestamp da última modificação. Se não, retorna "false"
				$timedamodificacao = filemtime($caminhodoarquivodecache);	//date("F d Y H:i:s.",filemtime($caminhodoarquivodecache));
				
				//Obtendo a data do sistema
				$timezone  = -3;
				$dataSistem = gmdate(time() + 3600*($timezone+date("I")));	//gmdate('F d Y H:i:s', time() + 3600*($timezone+date("I")));
				//print_r($dataSistem);

				$arquivo = fopen($caminhodoarquivodecache, 'c+');			// Abrindo o arquivo para leitura

				if (!$arquivo) {												// Se conseguir-se abrir o arquivo...
					$dados = file_get_contents($caminhodoarquivodecache);	// Lê o conteúdo do arquivo
					$dados = json_decode($dados);							// Convertendo dados Json

					$validadeVencida = $dados->expires_in + $timedamodificacao;
					//print_r(date("F d Y H:i:s.",$validadeVencida));

					if ($dataSistem < $validadeVencida){ 					// Se excedeu prazo de validação chama função de atualização da token, senao busca em cache
						$this->_erro = "Token OK! ";						// Retorno de erro
						return $this->_tokenEmCache = (object) array(		// Retorno do objeto com a token
							'token' => $dados->access_token,
							'cache' => true
						);
					} else {							
						$this->_tokenEmCache = $this->atualizarToken($dados->access_token,$dados->refresh_token);
						$this->_erro = "Token em cache expirou, foi necessário atualizar a Token! ";
						if ($this->_tokenEmCache){
							return $this->_tokenEmCache;
						} else 
							$this->_erro = "Não foi possível atualizar Token em cache! ";
					}						
				} else
					$this->_erro = "Não foi possível abrir o arquivo para pegar a Token em cache! ";		
				
				fclose($arquivo);											// Fecha o arquivo				
			} else {
				$this->_erro = "Não existe arquivo salvo em Cache para obter a Token, realizado nova solicitação de Token! ";
				$this->_tokenEmCache = $this->obterToken(false);			// Chamado a função para obter uma nova Token para conexão na API
				return $this->_tokenEmCache = (object) array(				// Retorno do objeto com a token
					'token' => $this->_tokenEmCache->access_token,
					'cache' => true
				);
			}
		} else {
		
			$param = array(													// Preparando transmissão dos dados
				"grant_type"=> "authorization_code",
				"redirect_uri"=> $this->_redirect_uri,
				"client_id"=> $this->_clientID,
				"client_secret"=> $this->_secret,
				"code"=> $this->_code				
			);
			$postparam = json_encode($param);								// Convertendo para JSON
					
			$curl = self::_prepararCurl();									// Preparar requisição do curl
			curl_setopt_array($curl, array(
				CURLOPT_POST => true,
				CURLOPT_URL => self::URL_TOKEN,
				CURLOPT_POSTFIELDS => $postparam,
				CURLOPT_HTTPHEADER => array(
					'Content-Type: application/json',
					'Accept: application/json',
					'Authorization: Bearer '. $this->_code,
					'Cache-Control: no-cache',
					'Accept-Language: pt-br'
				)
			));
			$resposta = curl_exec($curl);									// Obtendo resultado da comunicação
			curl_close($curl);
			
			// Recebe os dados do WebService no formato JSON e realiza o parse da resposta e retorna.
			// Caso seja um valor vazio ou fora do formato, retorna false.
			$resultado = json_decode($resposta);
			//print_r($resultado);

			// Se o valor salvo em "$resultado" for um objeto e se existir o atributo "access_token" nele...
			if ($resultado) {
				if (isset($resultado->access_token)) {
					$arquivo = fopen($caminhodoarquivodecache, 'c+');		// Tenta abrir o arquivo para leitura e escrita

					if ($arquivo) {											// Se conseguir-se abrir o arquivo...
						ftruncate($arquivo, 0);								// Apaga todo o seu conteúdo
						fwrite($arquivo, json_encode($resultado));			// Escreve a token no arquivo
					} else
						$this->_erro = "Não foi possível abrir o arquivo para salvar a Token em cache! ";

					fclose($arquivo);										// Fecha o arquivo
					return $this->_tokenEmCache = (object) array(			// Retorno do objeto com a token
						'token' => $resultado->access_token,
						'cache' => true
					);
				} else 
					$this->_erro = $resultado->message;
			} else
				$this->_erro = $resultado->message;
			
		}
		return false;														// Retornando FALSE caso não cosiga obter o token
	}


	/** Função para renovar a token caso tenha expirado
	 * @param $refreshToken Variavel contendo o refresh_token
	 * @return Object|bool Retorna um Objeto caso tenha sucesso ou false caso contrário
	 */
	function atualizarToken($token,$refreshToken){
		
		// Define o caminho para o arquivo de cache
		$caminhodoarquivodecache = $this->_caminhoPastaCache . DIRECTORY_SEPARATOR . 'huggy_token_cache.json';
		
		$param = array(														// Preparando transmissão dos dados
			"grant_type"=> "refresh_token",
			"client_id"=> $this->_clientID,
			"client_secret"=> $this->_secret,
			"refresh_token"=> $refreshToken				
		);
		$postparam = json_encode($param);									// Convertendo para JSON
		
		$curl = self::_prepararCurl();										// Preparar requisição do curl
		curl_setopt_array($curl, array(
			CURLOPT_POST => true,
			CURLOPT_URL => self::URL_TOKEN,
			CURLOPT_POSTFIELDS => $postparam,
			CURLOPT_HTTPHEADER => array(
				'Content-Type: application/json',
				'Accept: application/json',
				'Authorization: Bearer '. $token,
				'Cache-Control: no-cache',
				'Accept-Language: pt-br'
			)
		));
		$resposta = curl_exec($curl);										// Obtendo resultado da comunicação
		curl_close($curl);
		
		// Recebe os dados do WebService no formato JSON e realiza o parse da resposta e retorna.
		// Caso seja um valor vazio ou fora do formato, retorna false.
		$resultado = json_decode($resposta);
		//print_r($resultado);

		// Se o valor salvo em "$resultado" for um objeto e se existir o atributo "access_token" nele...
		if ($resultado) {
			if (isset($resultado->access_token)) {
				$arquivo = fopen($caminhodoarquivodecache, 'c+');			// Tenta abrir o arquivo para leitura e escrita

				if ($arquivo) {												// Se conseguir-se abrir o arquivo...
					ftruncate($arquivo, 0);									// Apaga todo o seu conteúdo
					fwrite($arquivo, $resultado);							// Escreve a token no arquivo
				} else
					$this->_erro = "Não foi possível abrir o arquivo para salvar a Token em cache! ";

				fclose($arquivo);											// Fecha o arquivo
				return $resultado = (object) array(							// Retorno do objeto com a token
					'token' => $resultado->access_token,
					'cache' => true
				);
			} else 
				$this->_erro = $resultado->message;
		} else
			$this->_erro = $resultado->message;

		return false;														// Retornando FALSE caso não cosiga atualizar o token
	}


	/** Função destinada à execução de comunicação GET
	 * @param $url endpoint que será utilizado
	 * @return Object|bool Retorna um Objeto caso tenha sucesso ou false caso contrário
	 */
	function executarGET($url) {
		
		$token = $this->obterToken(true);									// Obter token para conexão na API
		
		if (!$token) {														// Se der qualquer error em obter a token, retorna "false"
			$this->_erro = 'Erro ao obter a token da API da Huggy. ' . $this->_erro;
			return false;
		}

		$curl = self::_prepararCurl();										// Preparar requisição do curl
		curl_setopt_array($curl, array(
			CURLOPT_CUSTOMREQUEST => 'GET',
			CURLOPT_URL => self::URL_BASE . $url,
			CURLOPT_HTTPHEADER => array(
				'Content-Type: application/json',
				'Accept: application/json',
				'Authorization: Bearer '. $token->token,
				'Cache-Control: no-cache',
				'Accept-Language: pt-br'
			)
		));
		$resposta = curl_exec($curl);										// Obtendo resultado da comunicação
		curl_close($curl);
		
		// Recebe os dados do WebService no formato JSON e realiza o parse da resposta e retorna.
		// Caso seja um valor vazio ou fora do formato, retorna false.
		$resultado = json_decode($resposta);
		//print_r($resultado);
		//echo "URL executada: " . self::URL_BASE . $url;

		// Se o valor salvo em "$resultado" for um objeto eão possuir erros, retorna o objeto
		if($resultado) {
			if (isset($resultado->error)) {
				$this->_erro = @$resultado->error .". " . @$resultado->message ." " . @$resultado->hint ;
			} else if (isset($resultado->reason)) {
				$this->_erro = @$resultado->reason . $this->_erro;
			} else {
				return $resultado;											// Retornando o Objeto com dados obtidos da solicitação
			}
		} else 
			$this->_erro = @$resultado->message;
		
		return false;														// Retornando FALSE caso não cosiga obter os dados
	}


	/** Função destinada à execução de comunicação POST tem necessidade e se passar algumas informações
	 * @param $url endpoint que será utilizado
	 * @param $parametros  Variavel com informações a serem enviadas
	 * @return Object|bool Retorna um Objeto caso tenha sucesso ou false caso contrário
	 */
	function executarPOST($url,$parametros) {

		$token = $this->obterToken(true);									// Obter token para conexão na API
		
		if (!$token) {														// Se der qualquer error em obter a token, retorna "false"
			$this->_erro = 'Erro ao obter a token da API da Huggy. ' . $this->_erro;
			return false;
		}
		$parametros = json_encode($parametros);								// Convertendo array passado para JSON
		
		$curl = self::_prepararCurl();										// Preparar requisição do curl
		curl_setopt_array($curl, array(
			CURLOPT_CUSTOMREQUEST => 'POST',
			CURLOPT_URL => self::URL_BASE . $url,
			CURLOPT_POSTFIELDS => $parametros,
			CURLOPT_HTTPHEADER => array(
				'Content-Type: application/json',
				'Accept: application/json',
				'Authorization: Bearer '. $token->token,
				'Cache-Control: no-cache',
				'Accept-Language: pt-br'
			)
		));
		$resposta = curl_exec($curl);										// Obtendo resultado da comunicação
		curl_close($curl);
		
		// Recebe os dados do WebService no formato JSON e realiza o parse da resposta e retorna.
		// Caso seja um valor vazio ou fora do formato, retorna false.
		$resultado = json_decode($resposta);
		//print_r($resultado);
		//echo "URL executada: " . self::URL_BASE . $url;

		// Se o valor salvo em "$resultado" for um objeto eão possuir erros, retorna o objeto
		if($resultado) {
			if (isset($resultado->error)) {
				$this->_erro = @$resultado->error .". " . @$resultado->message ." " . @$resultado->hint ;
			} else if (isset($resultado->reason)) {
				$this->_erro = @$resultado->reason . $this->_erro;
			} else {
				return $resultado;											// Retornando o Objeto com dados obtidos da solicitação
			}
		} else 
			$this->_erro = @$resultado->message;
		
		return false;														// Retornando FALSE caso não cosiga obter os dados
	}


	/** Função destinada à execução de comunicação PUT
	 * @param $url endpoint que será utilizado
	 * @param $parametros  Variavel com informações a serem enviadas
	 * @return Object|bool Retorna um Objeto caso tenha sucesso ou false caso contrário
	 */
	function executarPUT($url,$parametros) {

		$token = $this->obterToken(true);									// Obter token para conexão na API
		
		if (!$token) {														// Se der qualquer error em obter a token, retorna "false"
			$this->_erro = 'Erro ao obter a token da API da Huggy. ' . $this->_erro;
			return false;
		}
		$parametros = json_encode($parametros);								// Convertendo array passado para JSON
		
		$curl = self::_prepararCurl();										// Preparar requisição do curl
		curl_setopt_array($curl, array(
			CURLOPT_CUSTOMREQUEST => 'PUT',
			CURLOPT_URL => self::URL_BASE . $url,
			CURLOPT_POSTFIELDS => $parametros,
			CURLOPT_HTTPHEADER => array(
				'Content-Type: application/json',
				'Accept: application/json',
				'Authorization: Bearer '. $token->token,
				'Cache-Control: no-cache',
				'Accept-Language: pt-br'
			)
		));
		$resposta = curl_exec($curl);										// Obtendo resultado da comunicação
		curl_close($curl);
		
		// Recebe os dados do WebService no formato JSON e realiza o parse da resposta e retorna.
		// Caso seja um valor vazio ou fora do formato, retorna false.
		$resultado = json_decode($resposta);
		//print_r($resultado);
		//echo "URL executada: " . self::URL_BASE . $url;

		// Se o valor salvo em "$resultado" for um objeto eão possuir erros, retorna o objeto
		if($resultado) {
			if (isset($resultado->error)) {
				$this->_erro = @$resultado->error .". " . @$resultado->message ." " . @$resultado->hint ;
			} else if (isset($resultado->reason)) {
				$this->_erro = @$resultado->reason . $this->_erro;
			} else {
				return $resultado;											// Retornando o Objeto com dados obtidos da solicitação
			}
		} else {
			if (isset($resultado->reason)) {
				$this->_erro = @$resultado->reason . $this->_erro;
			} else 
				$this->_erro = @$resultado->message;
		}
		return false;														// Retornando FALSE caso não cosiga obter os dados
	}


	/** Função destinada à execução de comunicação DELETE
	 * @param $url endpoint que será utilizado
	 * @return bool True caso tiver êxito, ou false caso contrário
	 */
	function executarDELETE($url) {

		$token = $this->obterToken(true);									// Obter token para conexão na API
		
		if (!$token) {														// Se der qualquer error em obter a token, retorna "false"
			$this->_erro = 'Erro ao obter a token da API da Huggy. ' . $this->_erro;
			return false;
		}
		
		$curl = self::_prepararCurl();										// Preparar requisição do curl
		curl_setopt_array($curl, array(
			CURLOPT_CUSTOMREQUEST => 'DELETE',
			CURLOPT_URL => self::URL_BASE . $url,
			CURLOPT_HTTPHEADER => array(
				'Content-Type: application/json',
				'Accept: application/json',
				'Authorization: Bearer '. $token->token,
				'Cache-Control: no-cache',
				'Accept-Language: pt-br'
			)
		));
		$resposta = curl_exec($curl);										// Obtendo resultado da comunicação
		curl_close($curl);
		
		// Recebe os dados do WebService no formato JSON e realiza o parse da resposta e retorna.
		// Caso seja um valor vazio ou fora do formato, retorna false.
		$resultado = json_decode($resposta);
		//print_r($resultado);
		//echo "URL executada: " . self::URL_BASE . $url;

		// Se o valor salvo em "$resultado" for um objeto eão possuir erros, retorna o objeto
		if($resultado) {
			if (isset($resultado->error)) {
				$this->_erro = @$resultado->error .". " . @$resultado->message ." " . @$resultado->hint ;
			} else if (isset($resultado->reason)) {
				$this->_erro = @$resultado->reason . $this->_erro;
			} else {
				return $resultado;											// Retornando o Objeto com dados obtidos da solicitação
			}
		} else 
			$this->_erro = @$resultado->message;
		
		return false;														// Retornando FALSE caso não cosiga obter os dados
	}


	/** Descrição do erro
	 * @return string|bool	Descrição do erro ou "false", se não ocorreu erro
	 */
	function obterErro() {
		return $this->_erro ?: false;
	}
}

?>