<?php 

	//Buscando arquivo de conexão com os bancos de dados que serão utilizados
	require "conexaoDB.php";
	//Buscando arquivos de funções
	require "funcoes.php";



	//--------------------------- Remover vagas atuais contidas no site ---------------------------//
	//Buscando os Id dos registros de vagas
	$Remov = "SELECT ID FROM conecta_posts WHERE post_type='carrers'";
    $Remover = mysqli_query($CONEXAO,$Remov) or die("Erro ao retornar vagas existentes no banco!");
	
	//Removendo registros da tabela conecta_postmeta
	while($Rm = mysqli_fetch_array($Remover)){
		$idremov = $Rm['ID'];
		//echo $idremov.'<br>';
		$Delet = "DELETE FROM conecta_postmeta WHERE post_id='$idremov'";
		$Deletar = mysqli_query($CONEXAO,$Delet) or die("Erro ao tentar remover registro da tabela conecta_postmeta!");
	}

	//Removendo registros da tabela conecta_posts
	$Deletreg = "DELETE FROM conecta_posts WHERE post_type='carrers'";
	$Deletarregistros = mysqli_query($CONEXAO,$Deletreg) or die("Erro ao tentar remover registro da tabela conecta_posts!");
	


	//-------------------- Limpando o arquivo de vagas atuais contidas no site ---------------------//
	//Excluindo arquivo Vagas.txt
	if(unlink("Vagas.txt") == true){
		echo "Arquivo excluido com sucesso!<br>";
	}else{ echo "Não foi possível excluir o arquivo.<br>";}

	

	//---------- Buscando as vagas atuais contidas no banco de dados para serem inseridas ----------//
	//Variavel para buscar todos os dados contidos no banco de Vagas
	$Vaga = "SELECT * FROM Vagas WHERE Ativo='-1'";
	$Vagas = mysqli_query($CONEXAO_vg,$Vaga) or die("Erro ao retornar Vagas a serem inseridas!");

	while($Vg = mysqli_fetch_array($Vagas)){
		$DataIsercao = $Vg['DataInsercao'];
		$Nome_err = $Vg['Nome'];
		$Descricao = $Vg['Descricao'];
		$Resp = $Vg['Resp'];
		$Qual = $Vg['Qual'];
		$Ativo = $Vg['Ativo'];
		$Benef = $Vg['Benef'];
		$DataHora = $Vg['DataHora'];
		
		//Fazer verificação da opção de ATIVO
		if($Ativo == '0'){ $Ativo = "pending";	}
		else{ $Ativo = "publish"; }
		
		//Faver conversão de caracteres para minusculo e exclusão de espaço com inserção de "-" no campo post_name(Nome)
		$Nome = CaractereEspecial($Nome_err);
		$Nome = RemoveEspaco("$Nome");
		
		//Inserindo no banco de dados do site as vagas
		$Insere_vaga = "INSERT INTO conecta_posts(ID,post_author,post_date,post_date_gmt,post_content,post_title,post_excerpt,post_status,comment_status,ping_status,post_password,post_name,to_ping,pinged,post_modified,post_modified_gmt,post_content_filtered,post_parent,guid,menu_order,post_type,post_mime_type,comment_count) VALUES ('','3','$DataHora','$DataHora','','$Nome_err','','$Ativo','closed','closed','','$Nome','','','$DataHora','$DataHora','','0','','0','carrers','','0')";
		$Resultado = mysqli_query($CONEXAO,$Insere_vaga) or die("Erro ao inserir na tabela conecta_posts!");
	 	
		//Buscando o último ID inserido como post
		$Idpost = "SELECT ID FROM conecta_posts ORDER BY ID DESC LIMIT 1";
		$Idpostmeta = mysqli_query($CONEXAO, $Idpost);
		$Aux = mysqli_fetch_array($Idpostmeta);
		$idrest = $Aux['ID'];
		
		//Inserido demais informações no banco de dados do site
		//_edit_last - descricao_curta - atribuicoes - qualificacao - beneficios
		$Insere_info = "INSERT INTO conecta_postmeta (meta_id,post_id,meta_key,meta_value) VALUES 
				('','$idrest','_edit_last','3'),
				('','$idrest','descricao_curta','$Descricao'),
				('','$idrest','atribuicoes','$Resp'),
				('','$idrest','qualificacao','$Qual'),
				('','$idrest','beneficios','$Benef')";
		$Resultado3 = mysqli_query($CONEXAO,$Insere_info) or die ("Erro ao inserir na tabela conecta_postmeta!");				
	}	



	//-------------------- Buscando as vagas ativas que estão contidas no site ---------------------//
	//Buscando vagas que estão publicadas no site
	$VagaPb = "SELECT Nome FROM Vagas WHERE Ativo='-1'";
	$Vagaspublic = mysqli_query($CONEXAO_vg,$VagaPb) or die("Erro ao retornar vagas ativas!");

	//Salvando no arquivo Vagas.txt
	$fp = fopen("Vagas.txt","a+"); //Abrindo o arquivo

	while($VgPb = mysqli_fetch_array($Vagaspublic)){
		$Nome = $VgPb['Nome'];
		$Nome = utf8_encode($Nome);//decodificando o nome		
		$conteudo = "$conteudo$Nome;"; //Conteudo a ser inserido
	}	
	$conteudo = $conteudo."Outra(s);";
	
	fwrite($fp, $conteudo); //Escrevendo no arquivo 
	fclose($fp); //Fechando o arquivo



	echo "Dados removidos e inseridos com sucesso!";

?>