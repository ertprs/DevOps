<?php 
	
	//Buscando arquivo de conexão com os bancos de dados que serão utilizados
	require "conexaoDB.php";
	//Buscando arquivos de funções
	require "funcoes.php";


	if($_POST){
		$nome = $_POST['form_fields']['nome_ped'];
		$cpf = $_POST['form_fields']['cpf_ped'];
		$email = $_POST['form_fields']['email_ped'];
		$genero = $_POST['form_fields']['sexo_ped'];
		$telefone = $_POST['form_fields']['telefone_ped'];
		$logradouro = $_POST['form_fields']['lograd_ped'];
		$numero = $_POST['form_fields']['numero_ped'];
		$bairro = $_POST['form_fields']['bairro_ped'];
		$complemento = $_POST['form_fields']['comp_ped'];
		$cidade = $_POST['form_fields']['cidade_ped'];
		$cep = $_POST['form_fields']['cep_ped'];
		$uf = $_POST['form_fields']['uf_ped'];
		$planos = $_POST['form_fields']['planos_escolhidos'];
		$planosTv = $_POST['form_fields']['planos_tv'];
		$planosAdc = $_POST['form_fields']['planos_adicional'];
		$planosPlay = $_POST['form_fields']['planos_play'];
		$planosCombos = $_POST['form_fields']['planos_combos'];
		$ibge = $_POST['form_fields']['ibge_ped'];
		$ids = $_POST['form_fields']['ids_ped'];
		
		//Especificando o Genero do candidato
		$genero = CaractereEspecial($genero);
		$genero = strtolower($genero);
		if($genero == 'sexo'){ $genero = 0;
		}elseif($genero == 'masculino'){ $genero = 1;
		}elseif($genero == 'feminino'){ $genero = 2;
		}else{ $genero = 3; }
	
		//Obtendo a data do sistema
		$timezone  = -3;
		$dataSistem = gmdate('Y-m-d H:i:s', time() + 3600*($timezone+date("I")));
		
		//Removendo caracteres especiais
		$nome = CaractereEspecial($nome);
		$logradouro = CaractereEspecial($logradouro);
		$cidade = CaractereEspecial($cidade);
		$bairro = CaractereEspecial($bairro);
		$complemento = CaractereEspecial($complemento);
		
		//var_dump($_POST);
		
		//Inserindo no banco de dados do site os candidatos inscritos pela página
		$Insere_pedido = "INSERT INTO Pedidos(Id,DataInsercao,Nome,Cpf,Email,Genero,Telefone,Endereco,Numero,Complemento,Bairro,Cidade,Estado,Cep,Ibge,IdsPlanos,PlanosInternet,PlanosTv,PlanosAdicional,PlanosCombo,PlanosPlay) VALUES ('','$dataSistem','$nome','$cpf','$email','$genero','$telefone','$logradouro','$numero','$complemento','$bairro','$cidade','$uf','$cep','$ibge','$ids','$planos','$planosTv','$planosAdc','$planosCombos','$planosPlay')";
		$Resultado = mysqli_query($CONEXAO_vg,$Insere_pedido) or die("Erro ao retornar dados!");
		
		//Redirecinando para página de Trabalhe Conosco
		header('Location: /assine-ja/');
	} else {
		//Redirecinando para página de Trabalhe Conosco
		header('Location: /assine-ja/');
	}

?>



