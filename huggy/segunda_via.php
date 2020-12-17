<?php 

    require_once(__DIR__ . DIRECTORY_SEPARATOR . "api_huggy.php");                                          // Chamadas dos arquivo de envio de mensagens
    include __DIR__ . DIRECTORY_SEPARATOR . "conexao.php";              									// Incluindo arquvio de conexão com DB

    // Busca na tabela a informação do flowToken tipo TRANSFER_CRM_SEGUNDA_VIA
    $FlowToken = "SELECT chatID,dataCriacao FROM startedAutomationFlow WHERE flowToken LIKE '%TRANSFER_CRM_SEGUNDA_VIA%' AND checking = '0' ORDER BY dataCriacao DESC LIMIT 1";//  LIMIT 1";
    $Resultado = mysqli_query($CONEXAO,$FlowToken);
    // Impressão de erros na conexão com o DB
    if(!$Resultado){ echo "Falha de conexao na busca do Flow: " . mysqli_error($CONEXAO); }
    else{ //echo "Conexao foi realizada com sucesso!";
    }

    if (empty(mysqli_num_rows($Resultado))) {                                                               // Verificando se o retorno está vazio
        echo "<script type='text/javascript'>console.log('Observação: Não foi encontrado nenhum Flow do Tipo TRANSFER_CRM_SEGUNDA_VIA.');</script>";
    } else {
        $Aux = mysqli_fetch_array($Resultado);                                                              // Resultado da busca
        $chatId = $Aux['chatID'];                                                                           // Pegando valor do chatID do array de retorno
        $dataCriacao = $Aux['dataCriacao'];                                                                 // Pegando a data do evento
        $dataCriacao = strtotime($dataCriacao);

        // Inicializando variáveis
        $cpf = "";
        $cnpj = "";
        $servicos = [];
        $servicosMessage = "";
        $count = 0;
        $bloqueio = 0;

        // Declaração de variaveis para a chamada da função de envio de mensagem
        $url = "chats/$chatId/messages";
        $parametros = array(						    									                // O array é convertido dentro da função que será executada
            "text"=> "Por favor, digite somente os números de seu CPF/CNPJ, sem pontos e sem traço.",       // Mensagem a ser passado para o cliente
            "isInternal"=> false
        ); 
        $type = "POST";                                                                                     // Tipo necessario para postagem das mensagens
        enviaMensagemHuggy($url,$parametros,$type);                                                         // Chamando a função de postar mensagem para o cliente
        sleep(5);
        
        // Inicio do Loop com 100x e tempo de espara 2s para poder coletar 
        for ($i=0; $i<10; $i++) {
            // Busca na tabela a ultima informação enviada pelo cliente com o CPF solicitado
            $BuscaCPF = "SELECT body,dataCriacao FROM receivedAllMessage WHERE chatID = $chatId AND dataCriacao > $dataCriacao ORDER BY dataCriacao DESC LIMIT 1";
            $Resultado1 = mysqli_query($CONEXAO,$BuscaCPF);
            // Impressão de erros na conexão com o DB
            if(!$Resultado1){ echo "Falha de conexao na busca do CPF: " . mysqli_error($CONEXAO); }
            else{ //echo "Conexao foi realizada com sucesso!";
            }

            $Aux = mysqli_fetch_array($Resultado1);                                                         // Resultado da busca
            $body = $Aux['body'];                                                                           // Pegando valor do body do array de retorno
            $bodyTam = strlen($Aux['body']);                                                                // Pegando o tamanho do "body" para verificar se contem o CPF
            $dataCriacao = $Aux['dataCriacao'];                                                             // Pegando a data da ultima postagem
            $dataCriacao = strtotime($dataCriacao);
            
            // Tratamento de verificação do CPF informados
            if (($bodyTam >= 11) && ($bodyTam < 14)) {                                                      // Entra somente o CPF
                $body = str_replace(array(".",",","-","/"," "),"",$body);                                   // Tratando CPF, deixando somente números
                $cpfTam = strlen($body);                                                                    // Verifica o tamanho do conteúdo
                if ((is_numeric($body)) && ($cpfTam == 11)) {                                               // Verifica se é somente números e com tamanho 11
                    
                    $cpf = $body;                                                                           // CPF do cliente para que seja buscado no sistema

                    // Busca no CRM os serviços cadastrados no CPF colhido
                    $BuscaServico = "SELECT * FROM servicosClientes WHERE cpf = $cpf";
                    $Resultado2 = mysqli_query($CONEXAO,$BuscaServico);
                    // Impressão de erros na conexão com o DB
                    if(!$Resultado2){ echo "Falha de conexao na busca do serviço: " . mysqli_error($CONEXAO); }
                    else{ //echo "Conexao foi realizada com sucesso!";
                    }

                    if (empty(mysqli_num_rows($Resultado2))) {                                              // Verificando se o CPF foi encontrado
                        $parametros = array(
                            "text"=> "Não identifiquei nenhum cadastro com este CPF. Por favor, digite novamente.",
                            "isInternal"=> false
                        ); 
                        enviaMensagemHuggy($url,$parametros,$type);
                        sleep(5);

                        $cpf = "";                                                                          // Zerando a variavel
                        $i = 0;                                                                             // Iniciando o loop novamente para aguardar retorno do cliente
                    } else {                                                                                // Tratando os dados do CPF encontrado
                        $parametros = array(
                            "text"=> "Cadastro encontrado! Selecione o serviço para o qual deseja atendimento:",
                            "isInternal"=> false
                        ); 
                        enviaMensagemHuggy($url,$parametros,$type);
                        sleep(5);

                        while ($res = mysqli_fetch_array($Resultado2)) {                                    // Gerando o menu com os serviços que o cliente possui
                            $servicos[$count] = $res['servico'];
                            $servicosMessage .= $count + 1 . " - " . $res['servico'] . "-" . $res['descricao'] . " \n";
                            $count = $count + 1;
                        }
                        $parametros = array(
                            "text"=> $servicosMessage,
                            "isInternal"=> false
                        ); 
                        enviaMensagemHuggy($url,$parametros,$type);
                        sleep(5);
                        //print_r($res);
                        
                        $i = 10;                                                                            // Finalizando o loop
                    }
                } else {                                                                                    // Trata o CPF digitado que possui 12 e 13 numeros
                    $parametros = array(
                        "text"=> "Não identifiquei nenhum cadastro com este CPF. Por favor, digite novamente.",
                        "isInternal"=> false
                    ); 
                    enviaMensagemHuggy($url,$parametros,$type);
                    sleep(5);

                    $i = 0;                                                                                 // Iniciando o loop novamente para aguardar retorno do cliente
                }
            } else if ($bodyTam == 14) {                                                                    // Tratando CNPJ e CPF com ponto e traço, deixando somente números
                $body = str_replace(array(".",",","-","/"," "),"",$body);
                $cpfCnpjTam = strlen($body);
                if ((is_numeric($body)) && ($cpfCnpjTam == 11)) {                                           // CPF após ter sido retirado os pontos e traço

                    $cpf = $body;
                
                    // Busca no CRM os serviços cadastro no CPF colhido
                    $BuscaServico = "SELECT * FROM servicosClientes WHERE cpf = $cpf";
                    $Resultado2 = mysqli_query($CONEXAO,$BuscaServico);
                    // Impressão de erros na conexão com o DB
                    if(!$Resultado2){ echo "Falha de conexao na busca do serviço: " . mysqli_error($CONEXAO); }
                    else{ //echo "Conexao foi realizada com sucesso!";
                    }

                    if (empty(mysqli_num_rows($Resultado2))) {
                        $parametros = array(
                            "text"=> "Não identifiquei nenhum cadastro com este CPF. Por favor, digite novamente.",
                            "isInternal"=> false
                        ); 
                        enviaMensagemHuggy($url,$parametros,$type);
                        sleep(5);

                        $cpf = "";
                        $i = 0;
                    } else {
                        $parametros = array(
                            "text"=> "Cadastro encontrado! Selecione o serviço para o qual deseja atendimento:",
                            "isInternal"=> false
                        ); 
                        enviaMensagemHuggy($url,$parametros,$type);
                        sleep(5);

                        while ($res = mysqli_fetch_array($Resultado2)) {
                            $servicos[$count] = $res['servico'];
                            $servicosMessage .= $count + 1 . " - " . $res['servico'] . "-" . $res['descricao'] . " \n";
                            $count = $count + 1;
                        }
                        $parametros = array(
                            "text"=> $servicosMessage,
                            "isInternal"=> false
                        ); 
                        enviaMensagemHuggy($url,$parametros,$type);
                        sleep(5);
                        
                        $i = 10;
                    }
                } else if ((is_numeric($body)) && ($cpfCnpjTam == 14)) {                                    // CNPJ sem ponto, traço e barra
                
                    $cnpj = $body;                                                                          // CNPJ do cliente para que seja buscado no sistema
                    
                    // Busca no CRM os serviços cadastro no CNPJ colhido
                    $BuscaServico = "SELECT * FROM servicosClientes WHERE cnpj = $cnpj";
                    $Resultado2 = mysqli_query($CONEXAO,$BuscaServico);
                    // Impressão de erros na conexão com o DB
                    if(!$Resultado2){ echo "Falha de conexao na busca do serviço: " . mysqli_error($CONEXAO); }
                    else{ //echo "Conexao foi realizada com sucesso!";
                    }

                    if (empty(mysqli_num_rows($Resultado2))) {
                        $parametros = array(
                            "text"=> "Não identifiquei nenhum cadastro com este CNPJ. Por favor, digite novamente.",
                            "isInternal"=> false
                        ); 
                        enviaMensagemHuggy($url,$parametros,$type);
                        sleep(5);

                        $cnpj = "";
                        $i = 0;
                    } else {
                        $parametros = array(
                            "text"=> "Cadastro encontrado! Selecione o serviço para o qual deseja atendimento:",
                            "isInternal"=> false
                        ); 
                        enviaMensagemHuggy($url,$parametros,$type);
                        sleep(5);

                        while ($res = mysqli_fetch_array($Resultado2)) {
                            $servicos[$count] = $res['servico'];
                            $servicosMessage .= $count + 1 . " - " . $res['servico'] . "-" . $res['descricao'] . " \n";
                            $count = $count + 1;
                        }
                        $parametros = array(
                            "text"=> $servicosMessage,
                            "isInternal"=> false
                        ); 
                        enviaMensagemHuggy($url,$parametros,$type);
                        sleep(5);
                        
                        $i = 10;
                    }
                }
            } else if (($bodyTam > 14) && ($bodyTam <= 18)) {                                               // Tratando CNPJ com ponto, traço e barra, deixando somente números
                $body = str_replace(array(".",",","-","/"," "),"",$body);
                $cnpjTam = strlen($body);
                if ((is_numeric($body)) && ($cnpjTam == 14)) {
                    
                    $cnpj = $body;
                    
                    // Busca no CRM os serviços cadastro no CNPJ colhido
                    $BuscaServico = "SELECT * FROM servicosClientes WHERE cnpj = $cnpj";
                    $Resultado2 = mysqli_query($CONEXAO,$BuscaServico);
                    // Impressão de erros na conexão com o DB
                    if(!$Resultado2){ echo "Falha de conexao na busca do serviço: " . mysqli_error($CONEXAO); }
                    else{ //echo "Conexao foi realizada com sucesso!";
                    }

                    if (empty(mysqli_num_rows($Resultado2))) { 
                        $parametros = array(
                            "text"=> "Não identifiquei nenhum cadastro com este CNPJ. Por favor, digite novamente.",
                            "isInternal"=> false
                        ); 
                        enviaMensagemHuggy($url,$parametros,$type);
                        sleep(5);

                        $cnpj = "";
                        $i = 0; 
                    } else {
                        $parametros = array(
                            "text"=> "Cadastro encontrado! Selecione o serviço para o qual deseja atendimento:",
                            "isInternal"=> false
                        ); 
                        enviaMensagemHuggy($url,$parametros,$type);
                        sleep(5);

                        while ($res = mysqli_fetch_array($Resultado2)) {
                            $servicos[$count] = $res['servico'];
                            $servicosMessage .= $count + 1 . " - " . $res['servico'] . "-" . $res['descricao'] . " \n";
                            $count = $count + 1;
                        }
                        $parametros = array(
                            "text"=> $servicosMessage,
                            "isInternal"=> false
                        ); 
                        enviaMensagemHuggy($url,$parametros,$type);
                        sleep(5);
                    
                        $i = 10;
                    }
                } else {                                                                                    // Trata o CNPJ digitado que possui 15,16 e 17 numeros
                    $parametros = array(
                        "text"=> "Não identifiquei nenhum cadastro com este CNPJ. Por favor, digite novamente.",
                        "isInternal"=> false
                    ); 
                    enviaMensagemHuggy($url,$parametros,$type);
                    sleep(5);

                    $i = 0;
                }
            } else if ((is_numeric($body)) && (($bodyTam < 11) || ($bodyTam > 18))) {                       // Trata as informações com tamanhos < 11 e > 18 
                $parametros = array(
                    "text"=> "Informação incorreta! Por favor digite somente os números de seu CPF/CNPJ, sem pontos e sem traço.",
                    "isInternal"=> false
                ); 
                enviaMensagemHuggy($url,$parametros,$type);
                sleep(5);

                $i = 0;
            }

            sleep(2);                                                                                       // Delimitando o tempo de espeara para execução do Loop
        }

        // Se o cliente não digitou nenhuma informação após o tempo aguardado, ele é transferido
        if (($cpf == NULL) && ($cnpj == NULL)) {
            $parametros = array(
                "text"=> "Aguarde um momento, por favor! Você será transferido a um de nossos Atendentes.",
                "isInternal"=> false
            ); 
            enviaMensagemHuggy($url,$parametros,$type);
            sleep(5);

            // Transfere cliente para alguma fila de atendimento
            // Passo 1 - Definir o departamento
            $url1 = "chats/$chatId/department";                                                             // Endpoit para definição de departamento
            $parametros1 = array( "department"=> "11632" );                                                 // Id(código) departamento do Setor de Pagamentos
            $type1 = "PUT";
            /*enviaMensagemHuggy($url1,$parametros1,$type1);*/

            // Passo 2- Recolocar na fila
            $url1 = "chats/$chatId/queue";                                                                  // Endpoit para realocação de chat na fila
            $parametros1 = "";
            $type1 = "PUT";
            /*enviaMensagemHuggy($url1,$parametros1,$type1);*/
            
        } else {
        
            // Inicio do Loop com 100x e tempo de espara 2s para poder coletar 
            for ($i=0; $i<10; $i++) {
                // Busca na tabela a ultima informação enviada pelo cliente com a opção desejada
                $BuscaPostagem = "SELECT body,dataCriacao FROM receivedAllMessage WHERE chatID = $chatId AND dataCriacao > $dataCriacao ORDER BY dataCriacao DESC LIMIT 1";
                $Resultado1 = mysqli_query($CONEXAO,$BuscaPostagem);
                // Impressão de erros na conexão com o DB
                if(!$Resultado1){ echo "Falha de conexao na busca pelo serviço escolhido: " . mysqli_error($CONEXAO); }
                else{ //echo "Conexao foi realizada com sucesso!";
                }

                $Aux = mysqli_fetch_array($Resultado1);                                                     // Pegando o resultado para realizar buscas em outras tabelas
                $body = $Aux['body'];                                                                       // Pegando valor do body do array de retorno
                $bodyTam = strlen($Aux['body']);                                                            // Pegando o tamanho do "body"
                $dataCriacao = $Aux['dataCriacao'];                                                         // Pegando a data da ultima postagem
                $dataCriacao = strtotime($dataCriacao);
                $faturas = 0;
                $count = 0;

                if ((is_numeric($body)) && ($bodyTam == 1)) {                                               // Verificando se body é um número e se tem tamanho = 1
                    if (isset($servicos[$body - 1])) {                                                      // Verificado se o serviço existe no array criado anteriormente, array inicia com '0'
                        //echo $servicos[$body - 1];

                        // Verificar no CRM se há fatura em aberto para o serviço escolhido
                        /*$BuscaSegundavia = "SELECT * FROM ________ WHERE _______________";
                        $Resultado2 = mysqli_query($CONEXAO,$BuscaSegundavia);
                        // Impressão de erros na conexão com o DB
                        if(!$Resultado2){ echo "Falha de conexao na busca pelas faturas: " . mysqli_error($CONEXAO); }
                        else{ //echo "Conexao foi realizada com sucesso!";
                        }*/

                        $servicoEscolhido = $servicos[$body - 1];
                        // Se TEM fatura em aberto: $fatSegundaVia = 1, SENAO: $fatSegundaVia = 0;
                        $fatSegundaVia = 1;

                        if ($fatSegundaVia == 1) {                                                          // Opção para informar da(s) fatura(s) em aberto
                            /*while ($res = mysqli_fetch_array($Resultado2)) {                                // Colhendo as datas das faturas em aberto
                                $servicos[$count] = $res['servico'];
                                $servicosMessage .= $count + 1 . " - " . $res['servico'] . "-" . $res['data'] . " \n";
                                $count = $count + 1;
                            }*/
                                                                                 
                            $parametros = array(
                                "text"=> "Identificamos 2 fatura(s) em aberto deste serviço: $servicoEscolhido.",
                                "isInternal"=> false
                            ); 
                            enviaMensagemHuggy($url,$parametros,$type);
                            sleep(5);

                            $parametros = array(
                                "text"=> "Faturas: \nVencida: 20/10/2020 \n Vencida: 20/11/2020",//$servicosMessage,
                                "isInternal"=> false
                            );
                            enviaMensagemHuggy($url,$parametros,$type);
                            sleep(5);

                            $parametros = array(
                                "text"=> "Caso não conheça, o aplicativo MINHA CONECTA é o meio mais prático para obter sua fatura ou solicitar segunda via. Pelo app você pode solicitar desbloqueio de até 7 dias e se ainda não o possui, baixe agora em sua loja de aplicativos. \n\nAndroid: \nhttp://bit.ly/MinhaConectaAnd. \n\niOS: \nhttp://bit.ly/MinhaConectaiOS.",
                                "isInternal"=> false
                            ); 
                            enviaMensagemHuggy($url,$parametros,$type);
                            sleep(5);

                            $parametros = array(
                                "text"=> "Se já possui o Aplicaitivo, escolha a opção de acessá-lo ou pode falar com uma de nossas Atendentes. Opções: \n\n1 - Aplicativo Minha Conecta \n2 - Falar com um Atendente",
                                "isInternal"=> false
                            ); 
                            enviaMensagemHuggy($url,$parametros,$type);
                            sleep(5);

                            $faturas = 1;                                                                   // Será dado opção ao cliente do APP ou falar com Atendente
                            $i = 10;
                        } else if ($fatSegundaVia == 0) {                                                   // Opção para informar que não há fatura(s) em aberto
                            $parametros = array(
                                "text"=> "Não identificamos nenhuma fatura(s) em aberto deste serviço: $servicoEscolhido. ",
                                "isInternal"=> false
                            ); 
                            enviaMensagemHuggy($url,$parametros,$type);
                            sleep(5);

                            $faturas = 0;                                                                   // Será transferido cliente para falar com Atendente
                            $i = 10;
                        }
                    } else {                                                                                // Trata quando o cliente escolhe o serviço incorreto
                        $parametros = array(
                            "text"=> "Opção inválida, não existe este serviço.",
                            "isInternal"=> false
                        ); 
                        enviaMensagemHuggy($url,$parametros,$type); 
                        sleep(5);

                        $i = 0;
                    }
                } else if ((is_numeric($body)) && ($bodyTam > 1)){                                          // Trata quando o cliente digita o valor incorreto
                    $parametros = array(
                        "text"=> "Opção inválida, selecione o número referente ao serviço desejado.",
                        "isInternal"=> false
                    ); 
                    enviaMensagemHuggy($url,$parametros,$type);
                    sleep(5);

                    $i = 0;
                } 
                    
                sleep(2);                                                                                   // Delimitando o tempo de espeara para execução do Loop
            }

            if ($faturas == 1){                                                                             // Verificação de quando o cliente tem fatura em atraso
                // Inicio do Loop com 100x e tempo de espara 2s para poder coletar
                for ($i=0; $i<10; $i++) {
                    // Busca na tabela a ultima informação enviada pelo cliente com a opção desejada
                    $BuscaPostagem = "SELECT body,dataCriacao FROM receivedAllMessage WHERE chatID = $chatId AND dataCriacao > $dataCriacao ORDER BY dataCriacao DESC LIMIT 1";
                    $Resultado2 = mysqli_query($CONEXAO,$BuscaPostagem);
                    // Impressão de erros na conexão com o DB
                    if(!$Resultado2){ echo "Falha de conexao na busca entre APP e Atendente: " . mysqli_error($CONEXAO); }
                    else{ //echo "Conexao foi realizada com sucesso!";
                    }

                    $Aux = mysqli_fetch_array($Resultado2);
                    $body = $Aux['body'];
                    $bodyTam = strlen($Aux['body']);
                    $dataCriacao = $Aux['dataCriacao'];
                    $dataCriacao = strtotime($dataCriacao);
                    $servicoEscolhido = 0;
                    
                    if ((is_numeric($body)) && ($bodyTam == 1)) {                                           // Verificando se body é um número e se tem tamanho = 1
                        $servicoEscolhido = $body;     
                        if ($servicoEscolhido == 1) {                                                       // Opção escolhida foi a do APP
                            $parametros = array(
                                "text"=> "Acesse pelo link e faço o cadastro, caso ainda não tenha. \nhttps://cntcrmapp01.mgconecta.com.br:9000/index.php",
                                "isInternal"=> false
                            ); 
                            enviaMensagemHuggy($url,$parametros,$type);
                            sleep(5);
            
                            // Código para encerrar o atendimento caso seja necessário. Se passar o $parametros1 vazio o encerramento será feito mas o cliente não saberá
                            // Se passar conteudo no $parametros1 o cleinte saberá que foi encerrado.
                            // Descomnetar caso deseje finalizar atendimento COM ou SEM mensagem para o cliente
                            /*$url1 = "chats/$chatId/close";
                            $parametros1 = "";                                                              // Paramentos para fechamento sem mensagem final
                            $parametros1 = array(                                                           // Parametros para fechamento com envio de mensagem final
                                "tabulation"=> "",
                                "comment"=> "",
                                "sendFeedback"=> true                                                       // Mensagem de agradecimento deve estar configurada no painel do Huggy
                            );
                            $type1 = "PUT";
                            enviaMensagemHuggy($url1,$parametros1,$type1);*/

                            $i = 10;
                        } else if ($servicoEscolhido == 2) {                                                 // Opção escolhida foi a de falar com Atendente
                            $parametros = array(
                                "text"=> "Aguarde um momento, por favor! Você será transferido a um de nossos Atendentes.",
                                "isInternal"=> false
                            ); 
                            enviaMensagemHuggy($url,$parametros,$type);
                            sleep(5);
                            
                            // Transfere cliente para fila de atendimento do Setor de Pagamentos
                            // Passo 1 - Definir o departamento
                            $url1 = "chats/$chatId/department";
                            $parametros1 = array( "department"=> "11632" );                                 // Id(código) departamento do Setor de Pagamentos
                            $type1 = "PUT";
                            /*enviaMensagemHuggy($url1,$parametros1,$type1);*/

                            // Passo 2- Recolocar na fila
                            $url1 = "chats/$chatId/queue";
                            $parametros1 = "";
                            $type1 = "PUT";
                            /*enviaMensagemHuggy($url1,$parametros1,$type1);*/

                            $i = 10;
                        } else {                                                                            // Trata quando o cliente digita o valor incorreto
                            $parametros = array(
                                "text"=> "Opção inválida, não existe este serviço.",
                                "isInternal"=> false
                            ); 
                            enviaMensagemHuggy($url,$parametros,$type); 
                            sleep(5);
    
                            $i = 0;
                        }
                    } else if ((is_numeric($body)) && ($bodyTam > 1)) {                                     // Trata quando o cliente digita o valor incorreto
                        $parametros = array(
                            "text"=> "Opção inválida, escolha entre \"Acessar o aplicativo\" ou \"Falar com um Atendente\".",
                            "isInternal"=> false
                        ); 
                        enviaMensagemHuggy($url,$parametros,$type); 
                        sleep(5);
    
                        $i = 0;
                    }

                    sleep(2);                                                                               // Delimitando o tempo de espeara para execução do Loop
                }

                if ($servicoEscolhido == 0){                                                                // Se o cliente não digitou nada ou digitou algum texto, será transferido
                    $parametros = array(
                        "text"=> "Aguarde um momento, por favor! Você será transferido a um de nossos Atendentes.",
                        "isInternal"=> false
                    ); 
                    enviaMensagemHuggy($url,$parametros,$type);
                    sleep(5);
    
                    // Transfere cliente para fila de atendimento do Setor de Pagamentos
                    // Passo 1 - Definir o departamento
                    $url1 = "chats/$chatId/department";
                    $parametros1 = array( "department"=> "11632" );                                         // Id(código) departamento do Setor de Pagamentos
                    $type1 = "PUT";
                    /*enviaMensagemHuggy($url1,$parametros1,$type1);*/
    
                    // Passo 2- Recolocar na fila
                    $url1 = "chats/$chatId/queue";
                    $parametros1 = "";
                    $type1 = "PUT";
                    /*enviaMensagemHuggy($url1,$parametros1,$type1);*/
                }

            } else {                                                                                        // Se o cliente digitar errado ou não existir fatura, ele será transferido
                $parametros = array(
                    "text"=> "Aguarde um momento, por favor! Você será transferido a um de nossos Atendentes.",
                    "isInternal"=> false
                ); 
                enviaMensagemHuggy($url,$parametros,$type);
                sleep(5);

                // Transfere cliente para fila de atendimento do Setor de Pagamentos
                // Passo 1 - Definir o departamento
                $url1 = "chats/$chatId/department";
                $parametros1 = array( "department"=> "11632" );                                             // Id(código) departamento do Setor de Pagamentos
                $type1 = "PUT";
                /*enviaMensagemHuggy($url1,$parametros1,$type1);*/

                // Passo 2- Recolocar na fila
                $url1 = "chats/$chatId/queue";
                $parametros1 = "";
                $type1 = "PUT";
                /*enviaMensagemHuggy($url1,$parametros1,$type1);*/
            }
            
        }
        
        // Atualizando na tabela a informação do flowToken tipo TRANSFER_CRM_SEGUNDA_VIA
        $UpFlow = "UPDATE startedAutomationFlow SET checking = -1 WHERE chatID = $chatId";
        $Resultado = mysqli_query($CONEXAO,$UpFlow);
        // Impressão de erros na conexão com o DB
        if(!$Resultado){ echo "Falha de conexao na atualização do Flow: " . mysqli_error($CONEXAO); }
        else{ //echo "Conexao foi realizada com sucesso!";
        }
    }

?>