<?php 

    require_once(__DIR__ . DIRECTORY_SEPARATOR . "api_huggy.php");                          // Chamadas dos arquivo de envio de mensagens
    include __DIR__ . DIRECTORY_SEPARATOR . "conexao.php";              					// Incluindo arquvio de conexão com DB
    include __DIR__ . DIRECTORY_SEPARATOR . "funcoes_extras.php";              				// Incluindo arquvio de funções extras

    // Busca na tabela a informação do flowToken tipo TRANSFER_CRM_SEM_CPF 
    $FlowToken = "SELECT chatID,dataCriacao FROM startedAutomationFlow WHERE flowToken LIKE '%TRANSFER_CRM_SEM_CPF%' AND checking = '0' ORDER BY dataCriacao DESC LIMIT 1";//  LIMIT 1";
    $Resultado = mysqli_query($CONEXAO,$FlowToken);
    // Impressão de erros na conexão com o DB
    if(!$Resultado){ echo "Falha de conexao na busca do Flow: " . mysqli_error($CONEXAO); }
    else{ //echo "Conexao foi realizada com sucesso!";
    }
    
    if (empty(mysqli_num_rows($Resultado))) {                                               // Verificando se o retorno está vazio
        echo "<script type='text/javascript'>console.log('Observação: Não foi encontrado nenhum Flow do Tipo TRANSFER_CRM_SEM_CPF.');</script>";
    } else {
        $Aux = mysqli_fetch_array($Resultado);                                              // Resultado da busca
        $chatId = $Aux['chatID'];                                                           // Pegando valor do chatID do array de retorno
        $dataCriacao = $Aux['dataCriacao'];                                                 // Pegando a data do evento
        $dataCriacao = strtotime($dataCriacao);
        // Inicialização variaveis
        $cliente = 0;
        $nome = '';
        $cidadeEstado = '';
        $telefone = '';
        $horarioContato = '';
        $saida = 0;

        $parametros = array(                                                                // Mensagem de SAUDAÇÃO
            "text"=> "Para você que ainda não é nosso cliente, seja bem vindo!",
            "isInternal"=> false
        ); 
        enviaMensagemHuggy($url,$parametros,$type);
        sleep(5);

        $validaHorario = varificaHorarioComercial();                                        // Recebendo o horário disponivel do Sistema de Atendimento

        if ($validaHorario) {                                                               // Verificando se está dentro do horário de Atendimento
            $parametros = array(
                "text"=> "Aguarde um momento, por favor! Você será transferido a um de nossos Atendentes.",
                "isInternal"=> false
            ); 
            enviaMensagemHuggy($url,$parametros,$type);
            sleep(5);
            
            $cliente = 0;                                                                   // Transferindo loop para colher a opção desejada
        } else{                                                                             // Verificando se está fora do horário de atendimento
            $parametros = array(                                                            // Mensagem de DESCULPAS pelo horário de atendimento
                "text"=> "Desculpe! No momento nosso atendimento telefônico não está operando, mas daremos sequência no Atendimento",
                "isInternal"=> false
            ); 
            enviaMensagemHuggy($url,$parametros,$type);
            sleep(5);

            $parametros = array(                                                            // Mensagem de PROMOÇÃO
                "text"=> "Estamos com uma promoção em nossos seviços, confira na imagem a seguir.",
                "file"=> "https://endereço_da_imagem/imagem.jpg",
                "isInternal"=> false
            ); 
            enviaMensagemHuggy($url,$parametros,$type);
            sleep(5);        
            
            $parametros = array(                                                            // Mensagem do link do site
                "text"=> "Você pode dar início ao seu pedido pela nossa página.\n\nhttps://mgconecta.com.br/assine-ja/",
                "isInternal"=> false
            ); 
            enviaMensagemHuggy($url,$parametros,$type);
            sleep(5);

            $parametros = array(
                "text"=> "Preencha o formulário e é só aguardar que entraremos em contato para confirmar o seu pedido e tirar suas dúvidas.",
                "isInternal"=> false
            ); 
            enviaMensagemHuggy($url,$parametros,$type);
            sleep(5);

            $parametros = array(                                                            // Mensagem para colher dados
                "text"=> "Caso deseje que lhe retornamos, digite o NOME para contato",
                "isInternal"=> false
            ); 
            enviaMensagemHuggy($url,$parametros,$type);
            sleep(5);

            $cliente = 1;                                                                   // Transferindo loop para finalizar atendimento
        }

        if ($cliente == 1) {                                                                // Verificação de quando o cliente está bloqeuado pelo Pagamentos
            // Inicio do Loop com 100x e tempo de espara 2s para poder coletar
            for ($i=0; $i<10; $i++) {
                // Busca na tabela a ultima informação enviada pelo cliente com a opção desejada
                $BuscaPostagem = "SELECT body,dataCriacao FROM receivedAllMessage WHERE chatID = $chatId AND dataCriacao > $dataCriacao ORDER BY dataCriacao DESC LIMIT 1";
                $Resultado2 = mysqli_query($CONEXAO,$BuscaPostagem);
                // Impressão de erros na conexão com o DB
                if(!$Resultado2){ echo "Falha de conexao na busca pelo nome: " . mysqli_error($CONEXAO); }
                else{ //echo "Conexao foi realizada com sucesso!";
                }

                $Aux = mysqli_fetch_array($Resultado2);
                $body = $Aux['body'];
                $bodyTam = strlen($Aux['body']);
                $dataCriacao = $Aux['dataCriacao'];
                $dataCriacao = strtotime($dataCriacao);
                $escolha = 0;

                if ($bodyTam > 1) {                                                         // Verificando se body possui mais de uma letra
                    $nome = $body;                                                          // Atribindo valor na varaivel nome
                    
                    $parametros = array(
                        "text"=> "Digite o número do TELEFONE para contato.\nEx.:(99)9999-9999",
                        "isInternal"=> false
                    ); 
                    enviaMensagemHuggy($url,$parametros,$type);
                    sleep(5);
                    
                    $escolha = 1;
                    $i = 10;                                                                // Finalizando o loop
                } else {                                                                    // Trata quando o cliente digita o valor incorreto
                    $parametros = array(
                        "text"=> "Informação inválida, por favor insira um NOME para contato.",
                        "isInternal"=> false
                    ); 
                    enviaMensagemHuggy($url,$parametros,$type); 
                    sleep(5);

                    $i = 0;                                                                 // Iniciando o loop
                }

                sleep(2);                                                                   // Delimitando o tempo de espeara para execução do Loop
            }

            if ($escolha == 1) {
                // Inicio do Loop com 100x e tempo de espara 2s para poder coletar
                for ($i=0; $i<10; $i++) {
                    // Busca na tabela a ultima informação enviada pelo cliente com a opção desejada
                    $BuscaPostagem = "SELECT body,dataCriacao FROM receivedAllMessage WHERE chatID = $chatId AND dataCriacao > $dataCriacao ORDER BY dataCriacao DESC LIMIT 1";
                    $Resultado2 = mysqli_query($CONEXAO,$BuscaPostagem);
                    // Impressão de erros na conexão com o DB
                    if(!$Resultado2){ echo "Falha de conexao na busca pelo numero: " . mysqli_error($CONEXAO); }
                    else{ //echo "Conexao foi realizada com sucesso!";
                    }

                    $Aux = mysqli_fetch_array($Resultado2);
                    $body = $Aux['body'];
                    $bodyTam = strlen($Aux['body']);
                    $dataCriacao = $Aux['dataCriacao'];
                    $dataCriacao = strtotime($dataCriacao);
                    $escolha1 = 0;

                    if ($bodyTam >= 10) {                                                   // Verificando se body é um número maior q 10 caracteres
                        $telefone = $body;                                                  // Atribindo valor na varaivel telefone
                        
                        $parametros = array(
                            "text"=> "Digite a CIDADE e ESTADO de onde você deseja o nosso serviço.\nEx.:CIDADE-UF",
                            "isInternal"=> false
                        ); 
                        enviaMensagemHuggy($url,$parametros,$type);
                        sleep(5);
                        
                        $escolha1 = 1;
                        $i = 10;                                                            // Finalizando o loop
                    } else {                                                                // Trata quando o cliente digita o valor incorreto
                        $parametros = array(
                            "text"=> "Informação inválida, por favor insira um número de TELEFONE.",
                            "isInternal"=> false
                        ); 
                        enviaMensagemHuggy($url,$parametros,$type); 
                        sleep(5);

                        $i = 0;                                                             // Iniciando o loop
                    }

                    sleep(2);                                                               // Delimitando o tempo de espeara para execução do Loop
                }

                if ($escolha1 == 1) {
                    // Inicio do Loop com 100x e tempo de espara 2s para poder coletar
                    for ($i=0; $i<10; $i++) {
                        // Busca na tabela a ultima informação enviada pelo cliente com a opção desejada
                        $BuscaPostagem = "SELECT body,dataCriacao FROM receivedAllMessage WHERE chatID = $chatId AND dataCriacao > $dataCriacao ORDER BY dataCriacao DESC LIMIT 1";
                        $Resultado2 = mysqli_query($CONEXAO,$BuscaPostagem);
                        // Impressão de erros na conexão com o DB
                        if(!$Resultado2){ echo "Falha de conexao na busca pelo cidade: " . mysqli_error($CONEXAO); }
                        else{ //echo "Conexao foi realizada com sucesso!";
                        }
    
                        $Aux = mysqli_fetch_array($Resultado2);
                        $body = $Aux['body'];
                        $bodyTam = strlen($Aux['body']);
                        $dataCriacao = $Aux['dataCriacao'];
                        $dataCriacao = strtotime($dataCriacao);
                        $escolha2 = 0;
    
                        if ($bodyTam >= 2) {                                                // Verificando se body não é um número
                            $cidadeEstado = $body;                                          // Atribindo valor na cidade e estado
                            
                            $parametros = array(
                                "text"=> "Digite o melhor HORÁRIO para contato.\nEx.:14:00h",
                                "isInternal"=> false
                            ); 
                            enviaMensagemHuggy($url,$parametros,$type);
                            sleep(5);
                            
                            $escolha2 = 1;
                            $i = 10;                                                        // Finalizando o loop
                        } else {                                                            // Trata quando o cliente digita o valor incorreto
                            $parametros = array(
                                "text"=> "Informação inválida, por favor insira a CIDADE-UF.",
                                "isInternal"=> false
                            ); 
                            enviaMensagemHuggy($url,$parametros,$type); 
                            sleep(5);
    
                            $i = 0;                                                         // Iniciando o loop
                        }
    
                        sleep(2);                                                           // Delimitando o tempo de espeara para execução do Loop
                    }
                    
                    if ($escolha2 == 1) {
                        // Inicio do Loop com 100x e tempo de espara 2s para poder coletar
                        for ($i=0; $i<10; $i++) {
                            // Busca na tabela a ultima informação enviada pelo cliente com a opção desejada
                            $BuscaPostagem = "SELECT body,dataCriacao FROM receivedAllMessage WHERE chatID = $chatId AND dataCriacao > $dataCriacao ORDER BY dataCriacao DESC LIMIT 1";
                            $Resultado2 = mysqli_query($CONEXAO,$BuscaPostagem);
                            // Impressão de erros na conexão com o DB
                            if(!$Resultado2){ echo "Falha de conexao na busca pelo horario: " . mysqli_error($CONEXAO); }
                            else{ //echo "Conexao foi realizada com sucesso!";
                            }
        
                            $Aux = mysqli_fetch_array($Resultado2);
                            $body = $Aux['body'];
                            $bodyTam = strlen($Aux['body']);
                            $dataCriacao = $Aux['dataCriacao'];
                            $dataCriacao = strtotime($dataCriacao);
        
                            if ($bodyTam >= 2) {                                            // Verificando se body não é um número
                                $horarioContato = $body;                                    // Atribindo valor na varaivel telefone
                                
                                $parametros = array(
                                    "text"=> "Agradecemos pelo seu contato e tendo essas informações, peço que aguarde o nosso retorno para darmos sequência no processo do pedido!",
                                    "isInternal"=> false
                                ); 
                                enviaMensagemHuggy($url,$parametros,$type);
                                sleep(5);

                                $parametros = array(
                                    "text"=> "",
                                    "isInternal"=> false
                                ); 
                                enviaMensagemHuggy($url,$parametros,$type);
                                sleep(5);
                                
                                $saida = 1;                                                 // Finalizando o Atendimento
                                $i = 10;                                                    // Finalizando o loop
                            } else {                                                        // Trata quando o cliente digita o valor incorreto
                                $parametros = array(
                                    "text"=> "Informação inválida, por favor insira somente o nome para contato.",
                                    "isInternal"=> false
                                ); 
                                enviaMensagemHuggy($url,$parametros,$type); 
                                sleep(5);
        
                                $i = 0;                                                     // Iniciando o loop
                            }
        
                            sleep(2);                                                       // Delimitando o tempo de espeara para execução do Loop
                        }
        
                    } else if ($escolha2 == 0)                                              // Se o cliente não digitou nada ou digitou algum texto, será transferido
                        $saida = 1;                                                         // Finalizando o Atendimento
                    
                } else if ($escolha1 == 0)                                                  // Se o cliente não digitou nada ou digitou algum texto, será transferido
                     $saida = 1;                                                            // Finalizando o Atendimento

            } else if ($escolha == 0) {                                                     // Se o cliente não digitou nada ou digitou algum texto, será transferido
                $saida = 1;                                                                 // Finalizando o Atendimento

        } else if ($cliente == 0){                                                          // Se o cliente não digitou nada ou digitou algum texto, será transferido
            // Transfere cliente para fila de atendimento do Setor de Pagamentos
            // Passo 1 - Definir o departamento
            $url1 = "chats/$chatId/department";
            $parametros1 = array( "department"=> "16182" );
            $type1 = "PUT";
            /*enviaMensagemHuggy($url1,$parametros1,$type1);*/

            // Passo 2- Recolocar na fila
            $url1 = "chats/$chatId/queue";
            $parametros1 = "";
            $type1 = "PUT";
            /*enviaMensagemHuggy($url1,$parametros1,$type1);*/
        }
    
        if ($saida == 1){                                                                   // Cliente não digitou nenhuma informação durante o fluxo
            $parametros = array(
                "text"=> "Você não digitou a informação necessária para dar sequência no Atendimento.",
                "isInternal"=> false
            ); 
            enviaMensagemHuggy($url,$parametros,$type);
            sleep(5);

            $parametros = array(
                "text"=> "Agradecemos pelo seu contato. Por favor inicie um novo Atendimento.",
                "isInternal"=> false
            ); 
            enviaMensagemHuggy($url,$parametros,$type);
            sleep(5);
            
            // Código para encerrar o atendimento caso seja necessário. Se passar o $parametros1 vazio o encerramento será feito mas o cliente não saberá
            // Se passar conteudo no $parametros1 o cleinte saberá que foi encerrado.
            // Descomnetar caso deseje finalizar atendimento COM ou SEM mensagem para o cliente
            /*$url1 = "chats/$chatId/close";
            $parametros1 = "";                                                  // Paramentos para fechamento sem mensagem final
            $parametros1 = array(                                               // Parametros para fechamento com envio de mensagem final
                "tabulation"=> "",
                "comment"=> "",
                "sendFeedback"=> true                                           // Mensagem de agradecimento deve estar configurada no painel do Huggy
            );
            $type1 = "PUT";
            enviaMensagemHuggy($url1,$parametros1,$type1);*/
        }

        // Atualizando na tabela a informação do flowToken tipo TRANSFER_CRM_SEM_CPF
        $UpFlow = "UPDATE startedAutomationFlow SET checking = -1 WHERE chatID = $chatId";
        $Resultado = mysqli_query($CONEXAO,$UpFlow);
        // Impressão de erros na conexão com o DB
        if(!$Resultado){ echo "Falha de conexao na atualização do Flow: " . mysqli_error($CONEXAO); }
        else{ //echo "Conexao foi realizada com sucesso!";
        }
    }

?>