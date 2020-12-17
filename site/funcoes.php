<?php

//Função para eliminar caracteres especiais
function CaractereEspecial($string) {
    
	$string = preg_replace("/&([a-z])[a-z]+;/i", "$1", htmlentities(trim($string)));
	
	//Retornando o resultado convertido
	return $string;	
}

//Função trocar espação por "-"
function RemoveEspaco($string) {
    
	$string = str_replace(" ","-",preg_replace("/&([a-z])[a-z]+;/i", "$1", htmlentities(trim($string))));
	
	//Retornando o resultado convertido
	return $string;	
}

//Função para enviar email de confirmação de recebimento do curriculo
function EnviaEmailCandidato($nome,$email_Candidato,$vaga){

	//Destinatário, remetente e assunto
	$to = $email_Candidato;
	$remetente = "conecta.autoform@mgconecta.com.br";
	$assunto = "Candidato para vaga de: ".$vaga;
	//Cabeçalho da mensagem  
	$boundary = "XYZ-" . date("dmYis") . "-ZYX";
	$headers = "MIME-Version: 1.0\n";
	$headers.= "From: Conecta <$remetente>\n";
	$headers.= "Reply-To: $remetente\n"; 
	$headers.= "Content-type: multipart/mixed; boundary=\"$boundary\"\r\n"; 
	$headers.= "$boundary\n";

	//Layout da mensagem 
	$corpo_mensagem = "<style type='text/css'>
		.rps_102b p
		{ margin-top: 0; margin-bottom: 0; }
		.rps_102b #outlook a
		{ padding: 0; }
		.rps_102b body
		{ width: 100%!important; }
		.rps_102b .ReadMsgBody
		{ width: 100%; }
		.rps_102b .ExternalClass
		{ width: 100%; }
		.rps_102b body
		{ margin: 0; padding: 0; }
		.rps_102b img
		{ border: 0; height: auto; line-height: 100%; outline: none; text-decoration: none; }
		.rps_102b table td
		{ border-collapse: collapse; }
		.rps_102b #backgroundTable
		{ height: 100%!important; margin: 0; padding: 0; width: 100%!important; }
		.rps_102b body, .rps_102b #backgroundTable
		{ background-color: #FAFAFA; }
		.rps_102b #templateContainer
		{ border: 1px solid #DDDDDD; }
		.rps_102b h1, .rps_102b .h1
		{ color: #202020; display: block; font-family: Arial; font-size: 34px; font-weight: bold; line-height: 100%; margin-top: 0; margin-right: 0; margin-bottom: 10px; margin-left: 0; text-align: justify; }
		.rps_102b h2, .rps_102b .h2
		{ color: #202020; display: block; font-family: Arial; font-size: 30px; font-weight: bold; line-height: 100%; margin-top: 0; margin-right: 0; margin-bottom: 10px; margin-left: 0; text-align: justify; }
		.rps_102b h3, .rps_102b .h3
		{ color: #202020; display: block; font-family: Arial; font-size: 26px; font-weight: bold; line-height: 100%; margin-top: 0; margin-right: 0; margin-bottom: 10px; margin-left: 0; text-align: justify; }
		.rps_102b h4, .rps_102b .h4
		{ color: #F60; display: block; font-family: Arial; font-size: 22px; font-weight: bold; line-height: 100%; margin-top: 0; margin-right: 0; margin-bottom: 10px; margin-left: 0; text-align: justify; }
		.rps_102b #templateHeader
		{ background-color: #FFFFFF; border-bottom: 0; }
		.rps_102b .headerContent
		{ color: #202020; font-family: Arial; font-size: 34px; font-weight: bold; line-height: 100%; padding: 0; text-align: center; vertical-align: middle; }
		.rps_102b .headerContent a .yshortcuts
		{ color: #336699; font-weight: normal; text-decoration: underline; }
		.rps_102b #headerImage
		{ height: auto; max-width: 600px!important; }
		.rps_102b #templateContainer, .rps_102b .bodyContent
		{ background-color: #FFFFFF; }
		.rps_102b .bodyContent div
		{ color: #505050; font-family: Arial; font-size: 14px; line-height: 150%; text-align: justify; }
		.rps_102b .bodyContent div a .yshortcuts
		{ color: #336699; font-weight: normal; text-decoration: underline; }
		.rps_102b .templateDataTable
		{ background-color: #FFFFFF; border: 1px solid #DDDDDD; }
		.rps_102b .dataTableHeading
		{ background-color: #FFD9B3; color: #F60; font-family: Helvetica; font-size: 14px; font-weight: bold; line-height: 150%; text-align: justify; }
		.rps_102b .dataTableHeading a .yshortcuts
		{ color: #FFFFFF; font-weight: bold; text-decoration: underline; }
		.rps_102b .dataTableContent
		{ border-top: 1px solid #DDDDDD; border-bottom: 0; color: #202020; font-family: Helvetica; font-size: 12px; font-weight: bold; line-height: 150%; text-align: justify; }
		.rps_102b .dataTableContent a .yshortcuts
		{ color: #202020; font-weight: bold; text-decoration: underline; }
		.rps_102b .templateButton
		{ background-color: #F60; border: 0; border-collapse: separate!important; }
		.rps_102b .templateButton, .rps_102b .templateButton a .yshortcuts
		{ color: #FFFFFF; font-family: Arial; font-size: 15px; font-weight: bold; letter-spacing: -.5px; line-height: 100%; text-align: center; text-decoration: none; }
		.rps_102b .bodyContent img
		{ display: inline; height: auto; }
		.rps_102b #templateFooter
		{ background-color: #FFFFFF; border-top: 0; }
		.rps_102b .footerContent div
		{ color: #707070; font-family: Arial; font-size: 12px; line-height: 125%; text-align: center; }
		.rps_102b .footerContent div a .yshortcuts
		{ color: #336699; font-weight: normal; text-decoration: underline; }
		.rps_102b .footerContent img
		{ display: inline; }
		.rps_102b #utility
		{ background-color: #FFFFFF; border: 0; }
		.rps_102b #utility div
		{ text-align: center; }
		.rps_102b #monkeyRewards img
		{ max-width: 190px; }
	</style>
	<div class='rps_102b'>
		<div style='color:rgb(33,33,33)'>
			<div>
				<center>
					<table id='backgroundTable' width='100%' height='100%' cellspacing='0' cellpadding='0' border='0'>
						<tbody>
							<tr>
								<td valign='top' align='center' style='padding-top:20px'>
									<table id='templateContainer' width='600' cellspacing='0' cellpadding='0' border='0'>
										<tbody>
											<tr>
												<td valign='top' align='center'>
													<table id='templateHeader' width='600' cellspacing='0' cellpadding='0' border='0'>
														<tbody>
															<tr>
																<td class='headerContent'><img src='http://www.mgconecta.com.br/imagens/rh_confirmacao-cabecalho.png'> </td>
															</tr>
														</tbody>
													</table>
												</td>
											</tr>
											<tr>
												<td valign='top' align='center'>
													<table id='templateBody' width='600' cellspacing='0' cellpadding='0' border='0'>
														<tbody>
															<tr>
																<td valign='top'>
																	<table width='100%' cellspacing='0' cellpadding='20' border='0'>
																		<tbody>
																			<tr>
																				<td class='bodyContent' valign='top'>
																					<div>
																						<h4 class='h4'>Confirmação de recebimento de informações</h4>
																						<br>
																						<strong>$nome,</strong><br>
																						<br>
																						Confirmamos o recebimento de seu currículo e a inclusão de suas informações em nosso Banco de Dados. Entraremos em contato para iniciarmos o Processo Seletivo tão logo surja uma vaga para seu perfil. <br>
																						<br>
																						Agradecemos seu interesse em fazer parte de nossa equipe.<br>
																						<br>
																						Atenciosamente, <br>
																						<br>
																						Recursos Humanos - Conecta<br>
																						<br>
																					</div>
																				</td>
																			</tr>
																			<tr>
																				<td valign='top' style='padding-top:0; padding-bottom:0'></td>
																			</tr>
																			<tr>
																				<td valign='top' align='center' style='padding-top:0'>
																					<table class='templateButton' cellspacing='0' cellpadding='15' border='0'>
																						<tbody>
																							<tr>
																								<td class='templateButtonContent' valign='middle'>
																									<div><a href='http://www.mgconecta.com.br/' target='_blank'>Acesse o site da Conecta para saber mais</a> </div>
																								</td>
																							</tr>
																						</tbody>
																					</table>
																				</td>
																			</tr>
																		</tbody>
																	</table>
																</td>
															</tr>
														</tbody>
													</table>
												</td>
											</tr>
											<tr>
												<td valign='top' align='center'>
													<table id='templateFooter' width='600' cellspacing='0' cellpadding='10' border='0'>
														<tbody>
															<tr>
																<td class='footerContent' valign='top'>
																	<table width='100%' cellspacing='0' cellpadding='10' border='0'>
																		<tbody>
																			<tr>
																				<td valign='top'>
																					<div><em>Copyright © 2020, Conecta. Todos os direitos reservados.</em> <br>
																					</div>
																				</td>
																			</tr>
																			<tr>
																				<td id='utility' valign='middle'></td>
																			</tr>
																		</tbody>
																	</table>
																</td>
															</tr>
														</tbody>
													</table>
												</td>
											</tr>
										</tbody>
									</table>
									<br>
								</td>
							</tr>
						</tbody>
					</table>
				</center>
			</div>
		</div>
		<hr tabindex='-1' style='display:inline-block; width:98%'>
	</div>";
	
	//Criando a mensagem a ser enviada
	$mensagem = "--$boundary\n"; 
	$mensagem.= "Content-Transfer-Encoding: 8bits\n"; 
	$mensagem.= "Content-Type: text/html; charset=\"utf-8\"\n\n";
	$mensagem.= "$corpo_mensagem\n";
	
	//Função que envia a mensagem  
	if(mail($to, $assunto, $mensagem, $headers)){
		echo "<script>console.log('Mensagem enviada com sucesso!');</script>";
		//return true;
	} else {
		echo "<script>console.log('Ocorreu um erro ao enviar a mensagem!');</script>";
		//return false;
	}

}
?>