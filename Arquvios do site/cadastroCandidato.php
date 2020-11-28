<?php 
	
	//Buscando arquivo de conexão com os bancos de dados que serão utilizados
	require "conexaoDB.php";
	//Buscando arquivos de funções
	require "funcoes.php";
	
	if($_POST){
		$nome = $_POST['form_fields']['nome_cand'];
		$cpf = $_POST['form_fields']['cpf_cand'];
		$email = $_POST['form_fields']['email_cand'];
		$genero = $_POST['form_fields']['genero_cand'];
		$data = $_POST['form_fields']['data_cand'];
		$celular = $_POST['form_fields']['telefone1_cand'];
		$telefone = $_POST['form_fields']['telefone2_cand'];
		$logradouro = $_POST['form_fields']['lograd_cand'];
		$numero = $_POST['form_fields']['numero_cand'];
		$bairro = $_POST['form_fields']['bairro_cand'];
		$complemento = $_POST['form_fields']['comp_cand'];
		$cidade = $_POST['form_fields']['cidade_cand'];
		$cep = $_POST['form_fields']['cep_cand'];
		$uf = $_POST['form_fields']['uf_cand'];
		$vaga = $_POST['form_fields']['vaga_cand'];
		$ibge = $_POST['form_fields']['ibge_cand'];
		
		//Especificando o Genero do candidato
		$genero = CaractereEspecial($genero);
		$genero = strtolower($genero);
		if($genero == 'selecionar'){ $genero = 0;
		}elseif($genero == 'masculino'){ $genero = 1;
		}elseif($genero == 'feminino'){ $genero = 2;
		}else{ $genero = 3; }

		//Obtendo a data do sistema
		$timezone  = -3;
		$dataSistem = gmdate('Y-m-d H-i-s', time() + 3600*($timezone+date("I")));
		
		//Tratando a data de nascimento
		$data = date('Y-m-d',strtotime(str_replace('/','-',$data)));	
		
		//Removendo caracteres especiais
		$nomeEmail = $nome;
		$nome = CaractereEspecial($nome);
		$dataSistem2 = RemoveEspaco($dataSistem);
		$logradouro = CaractereEspecial($logradouro);
		$cidade = CaractereEspecial($cidade);
		$bairro = CaractereEspecial($bairro);
		$vaga = CaractereEspecial($vaga);
		$complemento = CaractereEspecial($complemento);
		
		//Tratando envio do curriculo
		$destino = './curriculos/';
		//Conferindo se foi enviado algum arquivo
		$file = isset($_FILES['curriculo']) ? $_FILES['curriculo'] : FALSE;
		//Pegando a extensão do arquivo
		preg_match("/\.(pdf|doc|docx){1}$/i", $file['name'], $ext);
		//Nome final do arquivo que será salvo
		$nome2 = RemoveEspaco($nome);
		$nome2 = $nome2.'-'.$dataSistem2.'.'.$ext[1];
		
		if(move_uploaded_file($file['tmp_name'], $destino . $nome2)){ echo 'Arquivo valido'; }
		else{ echo 'Erro ao enviar!<br>'; }
		//Tratando nome para inserção no banco
		$destino2 = $destino . $nome2;
		
		//var_dump($_POST);
		//echo '<br><br>';
		//print_r($_FILES);		

		//Inserindo no banco de dados do site os candidatos inscritos pela página
		$Insere_candidato = "INSERT INTO Candidatos(Id,DataHoraCadastro,Nome,Genero,CPF,DataNasc,Tel1,Tel2,Tel3,Email,Endereco,Numero,Complemento,Bairro,Cidade,Estado,CEP,Vaga,Curriculo,Ibge,Avaliado,Categoria,ProcSelecao) VALUES ('','$dataSistem','$nome','$genero','$cpf','$data','$celular','$telefone','','$email','$logradouro','$numero','$complemento','$bairro','$cidade','$uf','$cep','$vaga','$destino2','$ibge','','','')";
		$Resultado = mysqli_query($CONEXAO_vg,$Insere_candidato) or die("Erro ao retornar dados!");

		//Envio de email de confirmação ao candidato
		EnviaEmailCandidato($nomeEmail,$email,$vaga);

		//Redirecinando para página de Trabalhe Conosco
		header('Location: /trabalhe-na-conecta/');
	} else {
		//Redirecinando para página de Trabalhe Conosco se não houver nenhuma informação inserida.
		header('Location: /trabalhe-na-conecta/');
	}


?>