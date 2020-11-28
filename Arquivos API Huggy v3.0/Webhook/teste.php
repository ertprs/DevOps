<?php

    //Buscando arquivo de Conexão com o DB
    require_once(__DIR__ . DIRECTORY_SEPARATOR . "huggy/conexao.php");
    
    $arquivo = fopen(__DIR__ . DIRECTORY_SEPARATOR . 'resposta_Webhook.log', 'r');
    $dados = fread($arquivo,filesize(__DIR__ . DIRECTORY_SEPARATOR . 'resposta_Webhook.log'));
    fclose($arquivo);
    
    $dados = json_decode($dados, true);
    //$aux = $dados->time;
    //echo $aux;
    //print_r($dados);

    // Convertendo a data para ser salva
    $dataCriacao = date("Y-m-d H:i:s", $dados['time']);
    $token = $dados['token'];
    
    // Salvando corpo da mensagem para ser lida como string
    $f = fopen(__DIR__ . DIRECTORY_SEPARATOR . "teste.txt", "w");
    fwrite($f, json_encode($dados['messages']));
    fclose($f);
    // Lendo informações salva para salvar no banco
    $messages = '';
    $fr = fopen(__DIR__ . DIRECTORY_SEPARATOR . "teste.txt", "r");
    while(!feof($fr)){
        $messages .= fgetc($fr);
    }
    fclose($fr);
   
    echo $messages;
    //var_dump($dados['messages']['agentEntered']);
    // Salvando dados no DB na tabela "dados"
    /*$Insere_dado = "INSERT INTO dados(data,tipoEvento,tipoEventoID,body,senderID,receiverID,channel,customerID,chatID,send_at,read_at,closed_at,companyID,agentID,name,department,token,total) VALUES ('$dataCriacao','1','1','1','1','1','1','1','1','','','','1','1','1','1','$token','$messages')";
    $Resultado = mysqli_query($CONEXAO,$Insere_dado);
    // Impressão de erros na conexão com o DB
    if(!$Resultado){ echo "Falha de conexao: " . mysqli_error($CONEXAO); }
    else{ //echo "Conexao foi realizada com sucesso!";
    }*/
    
    //Excluindo arquivo teste.txt
	if(unlink(__DIR__ . DIRECTORY_SEPARATOR . "teste.txt") == true){
		//echo "Arquivo excluido com sucesso!<br>";
	}else{ echo "Não foi possível excluir o arquivo.<br>";}

    // Tratamento do corpo da mensagem
    // Tratando o evento quando possui o createdCustomer
    if($dados['messages']['createdCustomer'] != NULL){
        for ($i=0;$i<count($dados['messages']['createdCustomer']);$i++){
            $customerID = intval($dados['messages']['createdCustomer'][$i]['id']);
            $name = utf8_decode($dados['messages']['createdCustomer'][$i]['name']);
            $mobile = intval($dados['messages']['createdCustomer'][$i]['mobile']);
            $phone = intval($dados['messages']['createdCustomer'][$i]['phone']);
            $email = utf8_decode($dados['messages']['createdCustomer'][$i]['email']);
            $photo = utf8_decode($dados['messages']['createdCustomer'][$i]['photo']);
            $customFieldsCpf = intval($dados['messages']['createdCustomer'][$i]['custom_fields']['cpf_customer']);
            $channelName = utf8_decode($dados['messages']['createdCustomer'][$i]['channel_name']);
            $channelSource = utf8_decode($dados['messages']['createdCustomer'][$i]['channel_source']);
            $company = intval($dados['messages']['createdCustomer'][$i]['company']['id']);
            // Variaveis default
            $send_at = date("Y-m-d H:i:s", strtotime("0001-01-01 00:00:00"));
            $read_at = date("Y-m-d H:i:s", strtotime("0001-01-01 00:00:00"));
            $closed_at = date("Y-m-d H:i:s", strtotime("0001-01-01 00:00:00"));

            // Inserindo dados na tabela createdCustomer
            $Insere = "INSERT INTO createdCustomer(dataCriacao,customerID,name,mobile,phone,email,photo,customFieldsCpf,channelName,channelSource,companyID) VALUES ('$dataCriacao','$customerID','$name','$mobile','$phone','$email','$photo','$customFieldsCpf','$channelName','$channelSource','$company')";
            $Resultado = mysqli_query($CONEXAO,$Insere);
            // Impressão de erros na conexão com o DB
            if(!$Resultado){ echo "Falha de conexao: " . mysqli_error($CONEXAO);}
            else{//echo "Conexao foi realizada com sucesso!";
            }

            // Salvando dados no DB na tabela "dados"
            $Insere_dado = "INSERT INTO dados(data,tipoEvento,tipoEventoID,body,senderID,receiverID,channel,customerID,chatID,send_at,read_at,closed_at,companyID,agentID,name,department,token,total) VALUES ('$dataCriacao','createdCustomer','$customerID','-','-1','-1','$channelName','$customerID','-1','$send_at','$read_at','$closed_at','$company','-1','$name','-','$token','$messages')";
            $Resultado = mysqli_query($CONEXAO,$Insere_dado);        
            // Impressão de erros na conexão com o DB
            if(!$Resultado){ echo "Falha de conexao: " . mysqli_error($CONEXAO); }
            else{ //echo "Conexao foi realizada com sucesso!";
            }
        }
    } 
    // Tratando o evento quando possui o createdChat
    if($dados['messages']['createdChat'] != NULL){ 
        for ($i=0;$i<count($dados['messages']['createdChat']);$i++){
            $chatID = intval($dados['messages']['createdChat'][$i]['id']);
            $channel = utf8_decode($dados['messages']['createdChat'][$i]['channel']);
            $situation = utf8_decode($dados['messages']['createdChat'][$i]['situation']);
            $department = intval($dados['messages']['createdChat'][$i]['department']);
            $customerID = intval($dados['messages']['createdChat'][$i]['customer']['id']);
            $customer = "name: " . $dados['messages']['createdChat'][$i]['customer']['name'] . ", ";
            $customer .= "mobile: " . $dados['messages']['createdChat'][$i]['customer']['mobile'] . ", ";
            $customer .= "phone: " . $dados['messages']['createdChat'][$i]['customer']['phone'] . ", ";
            $customer .= "email: " . $dados['messages']['createdChat'][$i]['customer']['email'];
            $customer = utf8_decode($customer);
            $workflowID = utf8_decode($dados['messages']['createdChat'][$i]['workflowID']);
            $workflowStepID = utf8_decode($dados['messages']['createdChat'][$i]['workflowStepID']);
            $company = intval($dados['messages']['createdChat'][$i]['company']['id']);
            // Variaveis default
            $send_at = date("Y-m-d H:i:s", strtotime("0001-01-01 00:00:00"));
            $read_at = date("Y-m-d H:i:s", strtotime("0001-01-01 00:00:00"));
            $closed_at = date("Y-m-d H:i:s", strtotime("0001-01-01 00:00:00"));

            // Inserindo dados na tabela createdChat
            $Insere = "INSERT INTO createdChat(dataCriacao,chatID,channel,situation,department,customerID,customer,workflowID,workflowStepID,companyID) VALUES ('$dataCriacao','$chatID','$channel','$situation','$department','$customerID','$customer','$workflowID','$workflowStepID','$company')";
            $Resultado = mysqli_query($CONEXAO,$Insere);      
            // Impressão de erros na conexão com o DB
            if(!$Resultado){ echo "Falha de conexao: " . mysqli_error($CONEXAO);}
            else{//echo "Conexao foi realizada com sucesso!";
            }
            
            // Salvando dados no DB na tabela "dados"
            $Insere_dado = "INSERT INTO dados(data,tipoEvento,tipoEventoID,body,senderID,receiverID,channel,customerID,chatID,send_at,read_at,closed_at,companyID,agentID,name,department,token,total) VALUES ('$dataCriacao','createdChat','$chatID','-','-1','-1','$channel','$customerID','$chatID','$send_at','$read_at','$closed_at','$company','-1','-','$department','$token','$messages')";
            $Resultado = mysqli_query($CONEXAO,$Insere_dado);        
            // Impressão de erros na conexão com o DB
            if(!$Resultado){ echo "Falha de conexao: " . mysqli_error($CONEXAO); }
            else{ //echo "Conexao foi realizada com sucesso!";
            }
        }
    }
    // Tratando o evento quando possui o agentEntered
    if($dados['messages']['agentEntered'] != NULL){ 
        for ($i=0;$i<count($dados['messages']['agentEntered']);$i++){
            $chatID = intval($dados['messages']['agentEntered'][$i]['id']);
            $channel = utf8_decode($dados['messages']['agentEntered'][$i]['channel']);
            $situation = utf8_decode($dados['messages']['agentEntered'][$i]['situation']);
            $department = intval($dados['messages']['agentEntered'][$i]['department']);
            $customerID = intval($dados['messages']['agentEntered'][$i]['customer']['id']);
            $customer = "name: " . $dados['messages']['agentEntered'][$i]['customer']['name'] . ", ";
            $customer .= "mobile: " . $dados['messages']['agentEntered'][$i]['customer']['mobile'] . ", ";
            $customer .= "phone: " . $dados['messages']['agentEntered'][$i]['customer']['phone'] . ", ";
            $customer .= "email: " . $dados['messages']['agentEntered'][$i]['customer']['email'];
            $customer = utf8_decode($customer);
            $workflowID = utf8_decode($dados['messages']['agentEntered'][$i]['workflowID']);
            $workflowStepID = utf8_decode($dados['messages']['agentEntered'][$i]['workflowStepID']);
            $agentID = intval($dados['messages']['agentEntered'][$i]['agent']['id']);
            $agent = "name: " . $dados['messages']['agentEntered'][$i]['agent']['name'] . ", ";
            $agent .= "mobile: " . $dados['messages']['agentEntered'][$i]['agent']['mobile'] . ", ";
            $agent .= "phone: " . $dados['messages']['agentEntered'][$i]['agent']['phone'] . ", ";
            $agent .= "email: " . $dados['messages']['agentEntered'][$i]['agent']['email'];
            $agent = utf8_decode($agent);
            $company = intval($dados['messages']['agentEntered'][$i]['company']['id']);
            // Variaveis default
            $send_at = date("Y-m-d H:i:s", strtotime("0001-01-01 00:00:00"));
            $read_at = date("Y-m-d H:i:s", strtotime("0001-01-01 00:00:00"));
            $closed_at = date("Y-m-d H:i:s", strtotime("0001-01-01 00:00:00"));

            // Inserindo dados na tabela agentEntered
            $Insere = "INSERT INTO agentEntered(dataCriacao,chatID,channel,situation,department,customerID,customer,workflowID,workflowStepID,agentID,agent,companyID) VALUES ('$dataCriacao','$chatID','$channel','$situation','$department','$customerID','$customer','$workflowID','$workflowStepID','$agentID','$agent','$company')";
            $Resultado = mysqli_query($CONEXAO,$Insere);
            // Impressão de erros na conexão com o DB
            if(!$Resultado){ echo "Falha de conexao: " . mysqli_error($CONEXAO);}
            else{//echo "Conexao foi realizada com sucesso!";
            }
            
            // Salvando dados no DB na tabela "dados"
            $Insere_dado = "INSERT INTO dados(data,tipoEvento,tipoEventoID,body,senderID,receiverID,channel,customerID,chatID,send_at,read_at,closed_at,companyID,agentID,name,department,token,total) VALUES ('$dataCriacao','agentEntered','$chatID','-','-1','-1','$channel','$customerID','$chatID','$send_at','$read_at','$closed_at','$company','$agentID','-','$department','$token','$messages')";
            $Resultado = mysqli_query($CONEXAO,$Insere_dado);        
            // Impressão de erros na conexão com o DB
            if(!$Resultado){ echo "Falha de conexao: " . mysqli_error($CONEXAO); }
            else{ //echo "Conexao foi realizada com sucesso!";
            }
        }
    }
    // Tratando o evento quando possui o updatedCustomer
    if($dados['messages']['updatedCustomer'] != NULL){ 
        for ($i=0;$i<count($dados['messages']['updatedCustomer']);$i++){
            $customerID = intval($dados['messages']['updatedCustomer'][$i]['id']);
            $name = utf8_decode($dados['messages']['updatedCustomer'][$i]['name']);
            $mobile = intval($dados['messages']['updatedCustomer'][$i]['mobile']);
            $phone = intval($dados['messages']['updatedCustomer'][$i]['phone']);
            $email = utf8_decode($dados['messages']['updatedCustomer'][$i]['email']);
            $photo = utf8_decode($dados['messages']['updatedCustomer'][$i]['photo']);
            $customFieldsCpf = intval($dados['messages']['updatedCustomer'][$i]['custom_fields']['cpf_customer']);
            $company = intval($dados['messages']['updatedCustomer'][$i]['company']['id']);
            // Variaveis default
            $send_at = date("Y-m-d H:i:s", strtotime("0001-01-01 00:00:00"));
            $read_at = date("Y-m-d H:i:s", strtotime("0001-01-01 00:00:00"));
            $closed_at = date("Y-m-d H:i:s", strtotime("0001-01-01 00:00:00"));

            // Inserindo dados na tabela updatedCustomer
            $Insere = "INSERT INTO updatedCustomer(dataCriacao,customerID,name,mobile,phone,email,photo,customFieldsCpf,companyID) VALUES ('$dataCriacao','$customerID','$name','$mobile','$phone','$email','$photo','$customFieldsCpf','$company')";
            $Resultado = mysqli_query($CONEXAO,$Insere);      
            // Impressão de erros na conexão com o DB
            if(!$Resultado){ echo "Falha de conexao: " . mysqli_error($CONEXAO);}
            else{//echo "Conexao foi realizada com sucesso!";
            }            
            
            // Salvando dados no DB na tabela "dados"
            $Insere_dado = "INSERT INTO dados(data,tipoEvento,tipoEventoID,body,senderID,receiverID,channel,customerID,chatID,send_at,read_at,closed_at,companyID,agentID,name,department,token,total) VALUES ('$dataCriacao','updatedCustomer','$customerID','-','-1','-1','-','$customerID','-1','$send_at','$read_at','$closed_at','$company','-1','$name','-','$token','$messages')";
            $Resultado = mysqli_query($CONEXAO,$Insere_dado);        
            // Impressão de erros na conexão com o DB
            if(!$Resultado){ echo "Falha de conexao: " . mysqli_error($CONEXAO); }
            else{ //echo "Conexao foi realizada com sucesso!";
            }
        }
    } 
    // Tratando o evento quando possui o startedWidgetAttendance
    if($dados['messages']['startedWidgetAttendance'] != NULL){ 
        for ($i=0;$i<count($dados['messages']['startedWidgetAttendance']);$i++){
            $startedID = intval($dados['messages']['startedWidgetAttendance'][$i]['id']);
            $name = utf8_decode($dados['messages']['startedWidgetAttendance'][$i]['name']);
            $mobile = intval($dados['messages']['startedWidgetAttendance'][$i]['mobile']);
            $phone = intval($dados['messages']['startedWidgetAttendance'][$i]['phone']);
            $email = utf8_decode($dados['messages']['startedWidgetAttendance'][$i]['email']);
            $widget = intval($dados['messages']['startedWidgetAttendance'][$i]['widget']);
            $channelName = utf8_decode($dados['messages']['startedWidgetAttendance'][$i]['channel']['name']);
            $channelSource = utf8_decode($dados['messages']['startedWidgetAttendance'][$i]['channel']['source']);
            $company = intval($dados['messages']['startedWidgetAttendance'][$i]['company']['id']);
            // Variaveis default
            $send_at = date("Y-m-d H:i:s", strtotime("0001-01-01 00:00:00"));
            $read_at = date("Y-m-d H:i:s", strtotime("0001-01-01 00:00:00"));
            $closed_at = date("Y-m-d H:i:s", strtotime("0001-01-01 00:00:00"));
    
            // Inserindo dados na tabela startedWidgetAttendance
            $Insere = "INSERT INTO startedWidgetAttendance(dataCriacao,startedID,name,mobile,phone,email,widget,channelName,channelSource,companyID) VALUES ('$dataCriacao','$startedID','$name','$mobile','$phone','$email','$widget','$channelName','$channelSource','$company')";
            $Resultado = mysqli_query($CONEXAO,$Insere);       
            // Impressão de erros na conexão com o DB
            if(!$Resultado){ echo "Falha de conexao: " . mysqli_error($CONEXAO);}
            else{//echo "Conexao foi realizada com sucesso!";
            }
            
            // Salvando dados no DB na tabela "dados"
            $Insere_dado = "INSERT INTO dados(data,tipoEvento,tipoEventoID,body,senderID,receiverID,channel,customerID,chatID,send_at,read_at,closed_at,companyID,agentID,name,department,token,total) VALUES ('$dataCriacao','startedWidgetAttendance','$startedID','-','-1','-1','$channelName','-1','-1','$send_at','$read_at','$closed_at','$company','-1','$name','-','$token','$messages')";
            $Resultado = mysqli_query($CONEXAO,$Insere_dado);        
            // Impressão de erros na conexão com o DB
            if(!$Resultado){ echo "Falha de conexao: " . mysqli_error($CONEXAO); }
            else{ //echo "Conexao foi realizada com sucesso!";
            }
        }
    } 
    // Tratando o evento quando possui o receivedAllMessage
    if($dados['messages']['receivedAllMessage'] != NULL){ 
        for ($i=0;$i<count($dados['messages']['receivedAllMessage']);$i++){
            $receivedID = intval($dados['messages']['receivedAllMessage'][$i]['id']);
            $body = utf8_decode($dados['messages']['receivedAllMessage'][$i]['body']);
            $is_internal = $dados['messages']['receivedAllMessage'][$i]['is_internal'];
            $is_email = $dados['messages']['receivedAllMessage'][$i]['is_email'];
            $senderID = intval($dados['messages']['receivedAllMessage'][$i]['sender']['id']);
            $sender = "name: " . $dados['messages']['receivedAllMessage'][$i]['sender']['name'] . ", ";
            $sender .= "mobile: " . $dados['messages']['receivedAllMessage'][$i]['sender']['mobile'] . ", ";
            $sender .= "phone: " . $dados['messages']['receivedAllMessage'][$i]['sender']['phone'] . ", ";
            $sender .= "email: " . $dados['messages']['receivedAllMessage'][$i]['sender']['email'];
            $sender = utf8_decode($sender);
            $senderType = utf8_decode($dados['messages']['receivedAllMessage'][$i]['senderType']);
            $receiverID = intval($dados['messages']['receivedAllMessage'][$i]['receiver']['id']);
            $receiver = "name: " . $dados['messages']['receivedAllMessage'][$i]['receiver']['name'] . ", ";
            $receiver .= "mobile: " . $dados['messages']['receivedAllMessage'][$i]['receiver']['mobile'] . ", ";
            $receiver .= "phone: " . $dados['messages']['receivedAllMessage'][$i]['receiver']['phone'] . ", ";
            $receiver .= "email: " . $dados['messages']['receivedAllMessage'][$i]['receiver']['email'];
            $receiver = utf8_decode($receiver);
            $receiverType = utf8_decode($dados['messages']['receivedAllMessage'][$i]['receiverType']);
            $file = utf8_decode($dados['messages']['receivedAllMessage'][$i]['file']);
            $channel = utf8_decode($dados['messages']['receivedAllMessage'][$i]['channel']);
            $customerID = intval($dados['messages']['receivedAllMessage'][$i]['customer']['id']);
            $customer = "name: " . $dados['messages']['receivedAllMessage'][$i]['customer']['name'] . ", ";
            $customer .= "mobile: " . $dados['messages']['receivedAllMessage'][$i]['customer']['mobile'] . ", ";
            $customer .= "phone: " . $dados['messages']['receivedAllMessage'][$i]['customer']['phone'] . ", ";
            $customer .= "email: " . $dados['messages']['receivedAllMessage'][$i]['customer']['email'];
            $customer = utf8_decode($customer);
            $chatID = intval($dados['messages']['receivedAllMessage'][$i]['chat']['id']);
            $chat = "channel: " . $dados['messages']['receivedAllMessage'][$i]['chat']['channel'] . ", ";
            $chat .= "situation: " . $dados['messages']['receivedAllMessage'][$i]['chat']['situation'] . ", ";
            $chat .= "department: " . $dados['messages']['receivedAllMessage'][$i]['chat']['department'] . ", ";
            $chat .= "customer[id]: " . $dados['messages']['receivedAllMessage'][$i]['chat']['customer']['id'];
            $chat = utf8_decode($chat);
            $send_at = date("Y-m-d H:i:s", strtotime($dados['messages']['receivedAllMessage'][$i]['send_at']));
            if($dados['messages']['receivedAllMessage'][$i]['read_at'] == ""){
                $read_at = date("Y-m-d H:i:s", strtotime("0001-01-01 00:00:00"));
            } else 
                $read_at = date("Y-m-d H:i:s", strtotime($dados['messages']['receivedAllMessage'][$i]['read_at']));
                        
            $company = intval($dados['messages']['receivedAllMessage'][$i]['company']['id']);
            // Variavel default
            $closed_at = date("Y-m-d H:i:s", strtotime("0001-01-01 00:00:00"));

            // Inserindo dados na tabela receivedAllMessage
            $Insere = "INSERT INTO receivedAllMessage(dataCriacao,receivedID,body,is_internal,is_email,senderID,sender,senderType,receiverID,receiver,receiverType,file,channel,customerID,customer,chatID,chat,send_at,read_at,companyID) VALUES ('$dataCriacao','$receivedID','$body','$is_internal','$is_email','$senderID','$sender','$senderType','$receiverID','$receiver','$receiverType','$file','$channel','$customerID','$customer','$chatID','$chat','$send_at','$read_at','$company')";
            $Resultado = mysqli_query($CONEXAO,$Insere);   
            // Impressão de erros na conexão com o DB
            if(!$Resultado){ echo "Falha de conexao: " . mysqli_error($CONEXAO);}
            else{//echo "Conexao foi realizada com sucesso!";
            }
            
            // Salvando dados no DB na tabela "dados"
            $Insere_dado = "INSERT INTO dados(data,tipoEvento,tipoEventoID,body,senderID,receiverID,channel,customerID,chatID,send_at,read_at,closed_at,companyID,agentID,name,department,token,total) VALUES ('$dataCriacao','receivedAllMessage','$receivedID','$body','$senderID','$receiverID','$channel','$customerID','$chatID','$send_at','$read_at','$closed_at','$company','-1','-','-','$token','$messages')";
            $Resultado = mysqli_query($CONEXAO,$Insere_dado);        
            // Impressão de erros na conexão com o DB
            if(!$Resultado){ echo "Falha de conexao: " . mysqli_error($CONEXAO); }
            else{ //echo "Conexao foi realizada com sucesso!";
            }
        }
    } 
    // Tratando o evento quando possui o sentAllMessage
    if($dados['messages']['sentAllMessage'] != NULL){ 
        for ($i=0;$i<count($dados['messages']['sentAllMessage']);$i++){
            $sentID = intval($dados['messages']['sentAllMessage'][$i]['id']);
            $body = utf8_decode($dados['messages']['sentAllMessage'][$i]['body']);
            $is_internal = $dados['messages']['sentAllMessage'][$i]['is_internal'];
            $is_email = $dados['messages']['sentAllMessage'][$i]['is_email'];
            $senderID = intval($dados['messages']['sentAllMessage'][$i]['sender']['id']);
            $sender = "name: " . $dados['messages']['sentAllMessage'][$i]['sender']['name'] . ", ";
            $sender .= "mobile: " . $dados['messages']['sentAllMessage'][$i]['sender']['mobile'] . ", ";
            $sender .= "phone: " . $dados['messages']['sentAllMessage'][$i]['sender']['phone'] . ", ";
            $sender .= "email: " . $dados['messages']['sentAllMessage'][$i]['sender']['email'];
            $sender = utf8_decode($sender);
            $senderType = utf8_decode($dados['messages']['sentAllMessage'][$i]['senderType']);
            $receiverID = intval($dados['messages']['sentAllMessage'][$i]['receiver']['id']);
            $receiver = "name: " . $dados['messages']['sentAllMessage'][$i]['receiver']['name'] . ", ";
            $receiver .= "mobile: " . $dados['messages']['sentAllMessage'][$i]['receiver']['mobile'] . ", ";
            $receiver .= "phone: " . $dados['messages']['sentAllMessage'][$i]['receiver']['phone'] . ", ";
            $receiver .= "email: " . $dados['messages']['sentAllMessage'][$i]['receiver']['email'];
            $receiver = utf8_decode($receiver);
            $receiverType = utf8_decode($dados['messages']['sentAllMessage'][$i]['receiverType']);
            $file = utf8_decode($dados['messages']['sentAllMessage'][$i]['file']);
            $channel = utf8_decode($dados['messages']['sentAllMessage'][$i]['channel']);
            $customerID = intval($dados['messages']['sentAllMessage'][$i]['customer']['id']);
            $customer = "name: " . $dados['messages']['sentAllMessage'][$i]['customer']['name'] . ", ";
            $customer .= "mobile: " . $dados['messages']['sentAllMessage'][$i]['customer']['mobile'] . ", ";
            $customer .= "phone: " . $dados['messages']['sentAllMessage'][$i]['customer']['phone'] . ", ";
            $customer .= "email: " . $dados['messages']['sentAllMessage'][$i]['customer']['email'];
            $customer = utf8_decode($customer);
            $chatID = intval($dados['messages']['sentAllMessage'][$i]['chat']['id']);
            $chat = "channel: " . $dados['messages']['sentAllMessage'][$i]['chat']['channel'] . ", ";
            $chat .= "situation: " . $dados['messages']['sentAllMessage'][$i]['chat']['situation'] . ", ";
            $chat .= "department: " . $dados['messages']['sentAllMessage'][$i]['chat']['department'] . ", ";
            $chat .= "customer[id]: " . $dados['messages']['sentAllMessage'][$i]['chat']['customer']['id'];
            $chat = utf8_decode($chat);
            $send_at = date("Y-m-d H:i:s", strtotime($dados['messages']['sentAllMessage'][$i]['send_at']));
            if($dados['messages']['sentAllMessage'][$i]['read_at'] == ""){
                $read_at = date("Y-m-d H:i:s", strtotime("0001-01-01 00:00:00"));
            } else 
                $read_at = date("Y-m-d H:i:s", strtotime($dados['messages']['sentAllMessage'][$i]['read_at']));

            $company = intval($dados['messages']['sentAllMessage'][$i]['company']['id']);
            // Variavel default
            $closed_at = date("Y-m-d H:i:s", strtotime("0001-01-01 00:00:00"));
    
            // Inserindo dados na tabela sentAllMessage
            $Insere = "INSERT INTO sentAllMessage(dataCriacao,sentID,body,is_internal,is_email,senderID,sender,senderType,receiverID,receiver,receiverType,file,channel,customerID,customer,chatID,chat,send_at,read_at,companyID) VALUES ('$dataCriacao','$sentID','$body','$is_internal','$is_email','$senderID','$sender','$senderType','$receiverID','$receiver','$receiverType','$file','$channel','$customerID','$customer','$chatID','$chat','$send_at','$read_at','$company')";
            $Resultado = mysqli_query($CONEXAO,$Insere);       
            // Impressão de erros na conexão com o DB
            if(!$Resultado){ echo "Falha de conexao: " . mysqli_error($CONEXAO);}
            else{//echo "Conexao foi realizada com sucesso!";
            }
            
            // Salvando dados no DB na tabela "dados"
            $Insere_dado = "INSERT INTO dados(data,tipoEvento,tipoEventoID,body,senderID,receiverID,channel,customerID,chatID,send_at,read_at,closed_at,companyID,agentID,name,department,token,total) VALUES ('$dataCriacao','sentAllMessage','$sentID','$body','$senderID','$receiverID','$channel','$customerID','$chatID','$send_at','$read_at','$closed_at','$company','-1','-','-','$token','$messages')";
            $Resultado = mysqli_query($CONEXAO,$Insere_dado);        
            // Impressão de erros na conexão com o DB
            if(!$Resultado){ echo "Falha de conexao: " . mysqli_error($CONEXAO); }
            else{ //echo "Conexao foi realizada com sucesso!";
            }
        }
    } 
    // Tratando o evento quando possui o startedAutomationFlow
    if($dados['messages']['startedAutomationFlow'] != NULL){ 
        for ($i=0;$i<count($dados['messages']['startedAutomationFlow']);$i++){
            $chatID = intval($dados['messages']['startedAutomationFlow'][$i]['chatID']);
            $flowID = intval($dados['messages']['startedAutomationFlow'][$i]['flowID']);
            $flowToken = $dados['messages']['startedAutomationFlow'][$i]['flowToken'];
            $context = "SYSTEM.TIME_HELLO:" . $dados['messages']['startedAutomationFlow'][$i]['context']['SYSTEM.TIME_HELLO'] . ", ";
            $context .= "SYSTEM.CHAT_ID:" . $dados['messages']['startedAutomationFlow'][$i]['context']['SYSTEM.CHAT_ID'] . ", ";
            $context .= "SYSTEM.CHAT_CREATED_DATE:" . date('Y-m-d H-i-s',$dados['messages']['startedAutomationFlow'][$i]['context']['SYSTEM.CHAT_CREATED_DATE']) . ", ";
            $context .= "SYSTEM.DEPARTMENT_NAME:" . $dados['messages']['startedAutomationFlow'][$i]['context']['SYSTEM.DEPARTMENT_NAME'] . ", ";
            $context .= "SYSTEM.DEPARTMENT_ORDER:" . $dados['messages']['startedAutomationFlow'][$i]['context']['SYSTEM.DEPARTMENT_ORDER'] . ", ";
            $context .= "SYSTEM.CLIENT_NAME:" . $dados['messages']['startedAutomationFlow'][$i]['context']['SYSTEM.CLIENT_NAME'] . ", ";
            $context .= "SYSTEM.CLIENT_FIRST_NAME:" . $dados['messages']['startedAutomationFlow'][$i]['context']['SYSTEM.CLIENT_FIRST_NAME'] . ", ";
            $context .= "SYSTEM.CLIENT_SECOND_NAME:" . $dados['messages']['startedAutomationFlow'][$i]['context']['SYSTEM.CLIENT_SECOND_NAME'] . ", ";
            $context .= "SYSTEM.CLIENT_NUMBER:" . $dados['messages']['startedAutomationFlow'][$i]['context']['SYSTEM.CLIENT_NUMBER'] . ", ";
            $context .= "SYSTEM.CLIENT_EMAIL:" . $dados['messages']['startedAutomationFlow'][$i]['context']['SYSTEM.CLIENT_EMAIL'] . ", ";
            $context .= "SYSTEM.CLIENT_ORGANIZATION_ID:" . $dados['messages']['startedAutomationFlow'][$i]['context']['SYSTEM.CLIENT_ORGANIZATION_ID'] . ", ";
            $context .= "SYSTEM.CLIENT_ORGANIZATION_NAME:" . $dados['messages']['startedAutomationFlow'][$i]['context']['SYSTEM.CLIENT_ORGANIZATION_NAME'] . ", ";
            $context .= "SYSTEM.COMPANY_NAME:" . $dados['messages']['startedAutomationFlow'][$i]['context']['SYSTEM.COMPANY_NAME'] . ", ";
            $context .= "SYSTEM.COMPANY_NUMBER:" . $dados['messages']['startedAutomationFlow'][$i]['context']['SYSTEM.COMPANY_NUMBER'] . ", ";
            $context .= "SYSTEM.AGENT_NAME:" . $dados['messages']['startedAutomationFlow'][$i]['context']['SYSTEM.AGENT_NAME'] . ", ";
            $context .= "SYSTEM.AGENT_MAIL:" . $dados['messages']['startedAutomationFlow'][$i]['context']['SYSTEM.AGENT_MAIL'] . ", ";
            $context .= "SYSTEM.AGENT_PHONE:" . $dados['messages']['startedAutomationFlow'][$i]['context']['SYSTEM.AGENT_PHONE'] . ", ";
            $context .= "SYSTEM.QUEUE_POSITION:" . $dados['messages']['startedAutomationFlow'][$i]['context']['SYSTEM.QUEUE_POSITION'] . ", ";
            $context .= "SYSTEM.WORKFLOW_ID:" . $dados['messages']['startedAutomationFlow'][$i]['context']['SYSTEM.WORKFLOW_ID'] . ", ";
            $context .= "SYSTEM.WORKFLOW_STEP_ID:" . $dados['messages']['startedAutomationFlow'][$i]['context']['SYSTEM.WORKFLOW_STEP_ID'] . ", ";
            $context .= "SYSTEM.CURRENT_MESSAGE:" . $dados['messages']['startedAutomationFlow'][$i]['context']['SYSTEM.CURRENT_MESSAGE'];
            $context = utf8_decode($context);
            $company = intval($dados['messages']['startedAutomationFlow'][$i]['company']['id']);
            // Variaveis default
            $send_at = date("Y-m-d H:i:s", strtotime("0001-01-01 00:00:00"));
            $read_at = date("Y-m-d H:i:s", strtotime("0001-01-01 00:00:00"));
            $closed_at = date("Y-m-d H:i:s", strtotime("0001-01-01 00:00:00"));

            // Inserindo dados na tabela startedAutomationFlow
            $Insere = "INSERT INTO startedAutomationFlow(dataCriacao,chatID,companyID,flowID,flowToken,context) VALUES ('$dataCriacao','$chatID','$company','$flowID','$flowToken','$context')";
            $Resultado = mysqli_query($CONEXAO,$Insere);       
            // Impressão de erros na conexão com o DB
            if(!$Resultado){ echo "Falha de conexao: " . mysqli_error($CONEXAO);}
            else{//echo "Conexao foi realizada com sucesso!";
            }
            
            // Salvando dados no DB na tabela "dados"
            $Insere_dado = "INSERT INTO dados(data,tipoEvento,tipoEventoID,body,senderID,receiverID,channel,customerID,chatID,send_at,read_at,closed_at,companyID,agentID,name,department,token,total) VALUES ('$dataCriacao','startedAutomationFlow','$chatID','$context','-1','-1','-','-1','$chatID','$send_at','$read_at','$closed_at','$company','-1','-','-','$token','$messages')";
            $Resultado = mysqli_query($CONEXAO,$Insere_dado);        
            // Impressão de erros na conexão com o DB
            if(!$Resultado){ echo "Falha de conexao: " . mysqli_error($CONEXAO); }
            else{ //echo "Conexao foi realizada com sucesso!";
            }
        }
    } 
    // Tratando o evento quando possui o finishedAutomationFlow
    if($dados['messages']['finishedAutomationFlow'] != NULL){ 
        for ($i=0;$i<count($dados['messages']['finishedAutomationFlow']);$i++){
            $chatID = intval($dados['messages']['finishedAutomationFlow'][$i]['chatID']);
            $flowID = intval($dados['messages']['finishedAutomationFlow'][$i]['flowID']);
            $flowToken = $dados['messages']['finishedAutomationFlow'][$i]['flowToken'];
            $context = "SYSTEM.TIME_HELLO:" . $dados['messages']['finishedAutomationFlow'][$i]['context']['SYSTEM.TIME_HELLO'] . ", ";
            $context .= "SYSTEM.CHAT_ID:" . $dados['messages']['finishedAutomationFlow'][$i]['context']['SYSTEM.CHAT_ID'] . ", ";
            $context .= "SYSTEM.CHAT_CREATED_DATE:" . date('Y-m-d H-i-s',$dados['messages']['finishedAutomationFlow'][$i]['context']['SYSTEM.CHAT_CREATED_DATE']) . ", ";
            $context .= "SYSTEM.DEPARTMENT_NAME:" . $dados['messages']['finishedAutomationFlow'][$i]['context']['SYSTEM.DEPARTMENT_NAME'] . ", ";
            $context .= "SYSTEM.DEPARTMENT_ORDER:" . $dados['messages']['finishedAutomationFlow'][$i]['context']['SYSTEM.DEPARTMENT_ORDER'] . ", ";
            $context .= "SYSTEM.CLIENT_NAME:" . $dados['messages']['finishedAutomationFlow'][$i]['context']['SYSTEM.CLIENT_NAME'] . ", ";
            $context .= "SYSTEM.CLIENT_FIRST_NAME:" . $dados['messages']['finishedAutomationFlow'][$i]['context']['SYSTEM.CLIENT_FIRST_NAME'] . ", ";
            $context .= "SYSTEM.CLIENT_SECOND_NAME:" . $dados['messages']['finishedAutomationFlow'][$i]['context']['SYSTEM.CLIENT_SECOND_NAME'] . ", ";
            $context .= "SYSTEM.CLIENT_NUMBER:" . $dados['messages']['finishedAutomationFlow'][$i]['context']['SYSTEM.CLIENT_NUMBER'] . ", ";
            $context .= "SYSTEM.CLIENT_EMAIL:" . $dados['messages']['finishedAutomationFlow'][$i]['context']['SYSTEM.CLIENT_EMAIL'] . ", ";
            $context .= "SYSTEM.CLIENT_ORGANIZATION_ID:" . $dados['messages']['finishedAutomationFlow'][$i]['context']['SYSTEM.CLIENT_ORGANIZATION_ID'] . ", ";
            $context .= "SYSTEM.CLIENT_ORGANIZATION_NAME:" . $dados['messages']['finishedAutomationFlow'][$i]['context']['SYSTEM.CLIENT_ORGANIZATION_NAME'] . ", ";
            $context .= "SYSTEM.COMPANY_NAME:" . $dados['messages']['finishedAutomationFlow'][$i]['context']['SYSTEM.COMPANY_NAME'] . ", ";
            $context .= "SYSTEM.COMPANY_NUMBER:" . $dados['messages']['finishedAutomationFlow'][$i]['context']['SYSTEM.COMPANY_NUMBER'] . ", ";
            $context .= "SYSTEM.AGENT_NAME:" . $dados['messages']['finishedAutomationFlow'][$i]['context']['SYSTEM.AGENT_NAME'] . ", ";
            $context .= "SYSTEM.AGENT_MAIL:" . $dados['messages']['finishedAutomationFlow'][$i]['context']['SYSTEM.AGENT_MAIL'] . ", ";
            $context .= "SYSTEM.AGENT_PHONE:" . $dados['messages']['finishedAutomationFlow'][$i]['context']['SYSTEM.AGENT_PHONE'] . ", ";
            $context .= "SYSTEM.QUEUE_POSITION:" . $dados['messages']['finishedAutomationFlow'][$i]['context']['SYSTEM.QUEUE_POSITION'] . ", ";
            $context .= "SYSTEM.WORKFLOW_ID:" . $dados['messages']['finishedAutomationFlow'][$i]['context']['SYSTEM.WORKFLOW_ID'] . ", ";
            $context .= "SYSTEM.WORKFLOW_STEP_ID:" . $dados['messages']['finishedAutomationFlow'][$i]['context']['SYSTEM.WORKFLOW_STEP_ID'] . ", ";
            $context .= "SYSTEM.CURRENT_MESSAGE:" . $dados['messages']['finishedAutomationFlow'][$i]['context']['SYSTEM.CURRENT_MESSAGE'];
            $context = utf8_decode($context);
            $company = intval($dados['messages']['finishedAutomationFlow'][$i]['company']['id']);
            // Variaveis default
            $send_at = date("Y-m-d H:i:s", strtotime("0001-01-01 00:00:00"));
            $read_at = date("Y-m-d H:i:s", strtotime("0001-01-01 00:00:00"));
            $closed_at = date("Y-m-d H:i:s", strtotime("0001-01-01 00:00:00"));

            // Inserindo dados na tabela finishedAutomationFlow
            $Insere = "INSERT INTO finishedAutomationFlow(dataCriacao,chatID,companyID,flowID,flowToken,context) VALUES ('$dataCriacao','$chatID','$company','$flowID','$flowToken','$context')";
            $Resultado = mysqli_query($CONEXAO,$Insere);      
            // Impressão de erros na conexão com o DB
            if(!$Resultado){ echo "Falha de conexao: " . mysqli_error($CONEXAO);}
            else{//echo "Conexao foi realizada com sucesso!";
            }
            
            // Salvando dados no DB na tabela "dados"
            $Insere_dado = "INSERT INTO dados(data,tipoEvento,tipoEventoID,body,senderID,receiverID,channel,customerID,chatID,send_at,read_at,closed_at,companyID,agentID,name,department,token,total) VALUES ('$dataCriacao','finishedAutomationFlow','$chatID','$context','-1','-1','-','-1','$chatID','$send_at','$read_at','$closed_at','$company','-1','-','-','$token','$messages')";
            $Resultado = mysqli_query($CONEXAO,$Insere_dado);        
            // Impressão de erros na conexão com o DB
            if(!$Resultado){ echo "Falha de conexao: " . mysqli_error($CONEXAO); }
            else{ //echo "Conexao foi realizada com sucesso!";
            }
        }
    } 
    // Tratando o evento quando possui o closedChat
    if($dados['messages']['closedChat'] != NULL){ 
        for ($i=0;$i<count($dados['messages']['closedChat']);$i++){
            $chatID = intval($dados['messages']['closedChat'][$i]['id']);
            $channel = utf8_decode($dados['messages']['closedChat'][$i]['channel']);
            $situation = utf8_decode($dados['messages']['closedChat'][$i]['situation']);
            $tabulation = utf8_decode($dados['messages']['closedChat'][$i]['tabulation']);
            $closed = intval($dados['messages']['closedChat'][$i]['closed']);
            $closed_at = date("Y-m-d H:i:s", strtotime($dados['messages']['closedChat'][$i]['closed_at']));
            $company = intval($dados['messages']['closedChat'][$i]['company']['id']);
            // Variaveis default
            $send_at = date("Y-m-d H:i:s", strtotime("0001-01-01 00:00:00"));
            $read_at = date("Y-m-d H:i:s", strtotime("0001-01-01 00:00:00"));

            // Inserindo dados na tabela closedChat
            $Insere = "INSERT INTO closedChat(dataCriacao,chatID,channel,situation,tabulation,closed_at,closed,companyID) VALUES ('$dataCriacao','$chatID','$channel','$situation','$tabulation','$closed_at','$closed','$company')";
            $Resultado = mysqli_query($CONEXAO,$Insere);  
            // Impressão de erros na conexão com o DB
            if(!$Resultado){ echo "Falha de conexao: " . mysqli_error($CONEXAO);}
            else{//echo "Conexao foi realizada com sucesso!";
            }
            
            // Salvando dados no DB na tabela "dados"
            $Insere_dado = "INSERT INTO dados(data,tipoEvento,tipoEventoID,body,senderID,receiverID,channel,customerID,chatID,send_at,read_at,closed_at,companyID,agentID,name,department,token,total) VALUES ('$dataCriacao','closedChat','$chatID','-','-1','-1','$channel','-1','$chatID','$send_at','$read_at','$closed_at','$company','-1','-','-','$token','$messages')";
            $Resultado = mysqli_query($CONEXAO,$Insere_dado);        
            // Impressão de erros na conexão com o DB
            if(!$Resultado){ echo "Falha de conexao: " . mysqli_error($CONEXAO); }
            else{ //echo "Conexao foi realizada com sucesso!";
            }
        }
    }


?>