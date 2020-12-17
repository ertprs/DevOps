<?php 

//Definição de variação para conexão ao banco de dados do novo site
$HOST = '10.5.116.14:33060';
$USER = 'usersql';
$PASS = 'r4GwlgGge';
$DBSA = 'zabbix';	
	
//Conexão ao baco de dados
$CONEXAO = mysqli_connect($HOST, $USER, $PASS, $DBSA);

if(!$CONEXAO){
	die("Falha de conexao: " . mysqli_connect_error());
}else{
	//echo "Conexao foi realizada com sucesso!";
}	

?>