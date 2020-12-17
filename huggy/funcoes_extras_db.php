<?php

    /** Função que possui os feriados Nacionais e Municipais
     * @return array $feriados Retorna um array com todos os feriados
     */
    function diasFeriadosNacionalMunicipal()
    {
        $ano = intval(date('Y'));                                                       // Pegando o ano atual

        $pascoa = easter_date($ano);                                                    // Limite de 1970 ou após 2037 da easter_date PHP consulta http://www.php.net/manual/pt_BR/function.easter-date.php
        $dia_pascoa = date('j', $pascoa);
        $mes_pascoa = date('n', $pascoa);
        $ano_pascoa = date('Y', $pascoa);

        $feriados = array(                                                              // Array com datas Fixas dos feriados Nacionail e Municipais
            date('d/m/Y',mktime(0, 0, 0, 1, 1, $ano)),                                  // Confraternização Universal - Lei nº 662, de 06/04/49
            date('d/m/Y',mktime(0, 0, 0, 4, 21, $ano)),                                 // Tiradentes - Lei nº 662, de 06/04/49
            date('d/m/Y',mktime(0, 0, 0, 5, 1, $ano)),                                  // Dia do Trabalhador - Lei nº 662, de 06/04/49
            date('d/m/Y',mktime(0, 0, 0, 6, 14, $ano)),                                 // Dia de Nhá Chica - Feriado Municipal
            date('d/m/Y',mktime(0, 0, 0, 8, 15, $ano)),                                 // Dia de assunção de Nossa Senhora - Feriado Municipal
            date('d/m/Y',mktime(0, 0, 0, 9, 7, $ano)),                                  // Dia da Independência - Lei nº 662, de 06/04/49
            date('d/m/Y',mktime(0, 0, 0, 10, 12, $ano)),                                // N. S. Aparecida - Lei nº 6802, de 30/06/80
            date('d/m/Y',mktime(0, 0, 0, 11, 2, $ano)),                                 // Todos os santos - Lei nº 662, de 06/04/49
            date('d/m/Y',mktime(0, 0, 0, 11, 15, $ano)),                                // Proclamação da republica - Lei nº 662, de 06/04/49
            date('d/m/Y',mktime(0, 0, 0, 12, 8, $ano)),                                 // Aniversário da Cidade - Feriado Municipal
            date('d/m/Y',mktime(0, 0, 0, 12, 25, $ano)),                                // Natal - Lei nº 662, de 06/04/49
            //date('d/m/Y',mktime(0, 0, 0, 12, 15, $ano)),                                // Teste

            // Essas Datas depem diretamente da data de Pascoa
            // mktime(0, 0, 0, $mes_pascoa, $dia_pascoa - 48, $ano_pascoa),             //2ºferia Carnaval
            date('d/m/Y',mktime(0, 0, 0, $mes_pascoa, $dia_pascoa - 47, $ano_pascoa)),  //3ºferia Carnaval
            date('d/m/Y',mktime(0, 0, 0, $mes_pascoa, $dia_pascoa - 2, $ano_pascoa)),   //6ºfeira Santa
            date('d/m/Y',mktime(0, 0, 0, $mes_pascoa, $dia_pascoa, $ano_pascoa)),       //Pascoa
            date('d/m/Y',mktime(0, 0, 0, $mes_pascoa, $dia_pascoa + 60, $ano_pascoa))   //Corpus Cirist
        );

        return $feriados;
    }

    /** Função que possui somente os feriados Nacionais
     * @return array $feriados Retorna um array com todos os feriados
     */
    function diasFeriadosNacionais()
    {
        $ano = intval(date('Y'));    

        $pascoa = easter_date($ano);                                                    // Limite de 1970 ou após 2037 da easter_date PHP consulta http://www.php.net/manual/pt_BR/function.easter-date.php
        $dia_pascoa = date('j', $pascoa);
        $mes_pascoa = date('n', $pascoa);
        $ano_pascoa = date('Y', $pascoa);

        $feriados = array(                                                              // Array com datas Fixas dos feriados Nacionail e Municipais
            date('d/m/Y',mktime(0, 0, 0, 1, 1, $ano)),                                  // Confraternização Universal - Lei nº 662, de 06/04/49
            date('d/m/Y',mktime(0, 0, 0, 4, 21, $ano)),                                 // Tiradentes - Lei nº 662, de 06/04/49
            date('d/m/Y',mktime(0, 0, 0, 5, 1, $ano)),                                  // Dia do Trabalhador - Lei nº 662, de 06/04/49
            date('d/m/Y',mktime(0, 0, 0, 9, 7, $ano)),                                  // Dia da Independência - Lei nº 662, de 06/04/49
            date('d/m/Y',mktime(0, 0, 0, 10, 12, $ano)),                                // N. S. Aparecida - Lei nº 6802, de 30/06/80
            date('d/m/Y',mktime(0, 0, 0, 11, 2, $ano)),                                 // Todos os santos - Lei nº 662, de 06/04/49
            date('d/m/Y',mktime(0, 0, 0, 11, 15, $ano)),                                // Proclamação da republica - Lei nº 662, de 06/04/49
            date('d/m/Y',mktime(0, 0, 0, 12, 25, $ano)),                                // Natal - Lei nº 662, de 06/04/49
            //date('d/m/Y',mktime(0, 0, 0, 12, 15, $ano)),                                // Teste

            // Essas Datas depem diretamente da data de Pascoa
            // mktime(0, 0, 0, $mes_pascoa, $dia_pascoa - 48, $ano_pascoa),             //2ºferia Carnaval
            date('d/m/Y',mktime(0, 0, 0, $mes_pascoa, $dia_pascoa - 47, $ano_pascoa)),  //3ºferia Carnaval
            date('d/m/Y',mktime(0, 0, 0, $mes_pascoa, $dia_pascoa - 2, $ano_pascoa)),   //6ºfeira Santa
            date('d/m/Y',mktime(0, 0, 0, $mes_pascoa, $dia_pascoa, $ano_pascoa)),       //Pascoa
            date('d/m/Y',mktime(0, 0, 0, $mes_pascoa, $dia_pascoa + 60, $ano_pascoa))   //Corpus Cirist
        );

        return $feriados;
    }

    
    /* Dias da Semana: 0 - Domingo | 1 -segunda | 2 - Terça | 3 - Quarta | 4 - Quinta | 5 - Sexta | 6 - Sábado */


    /** Função que verifica horário de funcionamento do Setor de Suporte técnico nos dias da semana
     * Segunda-feira à Sexta-feira de 07:00:00 às 22:00:00 | Sábados de 08:00:00 ás 20:00:00
     * Domingos e feriados Nacionais de 13:00:00 às 19:00:00
     * Feriados Municipais o horário de funcionamento é o horário de Sábado que é de 08:00:00 às 20:00:00
     * @return bool $horario Caso seja TRUE está em horario de atendimento, caso FALSE sem atendimento
     */
    function verificaHorarioSuporte()
    {
        include __DIR__ . DIRECTORY_SEPARATOR . "conexao.php";

        //Obtendo a data do sistema
        $timezone  = -3;   										                            // Definindo o Timerzone do horário
        $dataSistem = 	gmdate('d/m/Y',time() + 3600*($timezone+date("I")));                // Buscando somente a data - gmdate('F d Y H:i:s', time() + 3600*($timezone+date("I")));
        $horaSistem = gmdate('H:i:s',time() + 3600*($timezone+date("I")));                  // Buscando somente a hora - gmdate('F d Y H:i:s', time() + 3600*($timezone+date("I")));
        $diaSemana = gmdate('w',time() + 3600*($timezone+date("I")));                       // Buscando somente o dia da semana - gmdate('F d Y H:i:s', time() + 3600*($timezone+date("I")));
        
        // Busca no DB pelo dia da semana o horario correspondente
		$Busca = "SELECT * FROM horarioAtendimento WHERE numeroDiaSemana = $diaSemana AND setor = 'Suporte'";
		$Result = mysqli_query($CONEXAO,$Busca);
		// Impressão de erros na conexão com o DB
		if(!$Result){ echo "Falha de conexao ao buscar horario do setor de Contratos: " . mysqli_error($CONEXAO); }
		else{ //echo "Conexao foi realizada com sucesso!";
        }
        
        while ($res = mysqli_fetch_array($Result)) {                                        // Pegando e inserindo em variaveis separadas o resultado
            //echo $res['setor'] . "-" . $res['horaInicio']. "-" . $res['horaFim']. "-" . $res['diaSemana']. "-" . $res['numeroDiaSemana'] . "\n";
            $horaLimite1 = $res['horaInicio'];                                              // Inicio do atendimento por telefone
            $horaLimite2 = $res['horaFim'];                                                 // Fim do atendimento por telefone
        }
        
        echo $dataSistem ."-". $horaSistem ."\n";
        $diasFeriados = diasFeriadosNacionais();
        //print_r($diasFeriados);
        $feriado = 0;

        if (($diaSemana >= 1) && ($diaSemana <= 5)) {                                       // Verifica se o dia atual está entre Segunda-feira e Sexta-feira
            foreach($diasFeriados as &$fer) {                                               // Percorre o array com os feriados nacionais
                if ($fer == $dataSistem)
                    $feriado = 1;
            }

            if ($feriado == 1) {                                                            // Verifica se variavel feriado é '1' que indica ser feriado nacional
                if (($horaSistem > $horaLimite1) && ($horaSistem < $horaLimite2)) {         // Verificando se está dentro do horário de atendimento
                    echo "Tem atendimento e é feriado";
                    return true;
                } else {
                    echo "NÃO tem atendimento e é feriado";
                    return false;
                }
            } else {
                if (($horaSistem > $horaLimite1) && ($horaSistem < $horaLimite2)) {         // Verificando se está dentro do horário de atendimento
                    echo "Tem atendimento e não é feriado";
                    return true;
                } else {
                    echo "NÃO tem atendimento e não é feriado";
                    return false;
                }
            }        
        } else if ($diaSemana == 0){                                                        // Verifica se o dia atual é Domingo
            if (($horaSistem > $horaLimite1) && ($horaSistem < $horaLimite2)) {             // Verificando se está dentro do horário de atendimento
                echo "Tem atendimento e é Domingo";
                return true;
            } else {
                echo "NÃO tem atendimento e é Domingo";
                return false;
            }
        } else if ($diaSemana == 6) {                                                       // Verifica se o dia atual é Sábado
            foreach($diasFeriados as &$fer) {                                               // Percorre o array com os feriados nacionais
                if ($fer == $dataSistem)
                    $feriado = 1;
            }

            if ($feriado == 1){                                                             // Verifica se variavel feriado é '1' que indica ser feriado nacional
                if (($horaSistem > $horaLimite1) && ($horaSistem < $horaLimite2)) {         // Verificando se está dentro do horário de atendimento
                    echo "Tem atendimento, é feriado e é Sábado";
                    return true;
                } else {
                    echo "NÃO tem atendimento, é feriado e é Sábado";
                    return false;
                }
            } else {
                if (($horaSistem > $horaLimite1) && ($horaSistem < $horaLimite2)) {         // Verificando se está dentro do horário de atendimento
                    echo "Tem atendimento e é sábado";
                    return true;
                } else {
                    echo "NÃO tem atendimento e é sábado";
                    return false;
                }
            }
        }
    }

    /** Função que verifica horário de funcionamento do Setor Comercial nos dias da semana
     * Segunda-feira à Sábado de 08:00:00 às 20:00:00
     * Domingos e feriados Nacionais não tem Atendimento Telefônico
     * @return bool Caso seja TRUE está em horario de atendimento, caso FALSE sem atendimento
     */
    function verificaHorarioComercial()
    {
        include __DIR__ . DIRECTORY_SEPARATOR . "conexao.php";

        //Obtendo a data do sistema
        $timezone  = -3;   										                            // Definindo o Timerzone do horário
        $dataSistem = 	gmdate('d/m/Y',time() + 3600*($timezone+date("I")));                // Buscando somente a data - gmdate('F d Y H:i:s', time() + 3600*($timezone+date("I")));
        $horaSistem = gmdate('H:i:s',time() + 3600*($timezone+date("I")));                  // Buscando somente a hora - gmdate('F d Y H:i:s', time() + 3600*($timezone+date("I")));
        $diaSemana = gmdate('w',time() + 3600*($timezone+date("I")));                       // Buscando somente o dia da semana - gmdate('F d Y H:i:s', time() + 3600*($timezone+date("I")));
       
        // Busca no DB pelo dia da semana o horario correspondente
		$Busca = "SELECT * FROM horarioAtendimento WHERE numeroDiaSemana = $diaSemana AND setor = 'Comercial'";
		$Result = mysqli_query($CONEXAO,$Busca);
		// Impressão de erros na conexão com o DB
		if(!$Result){ echo "Falha de conexao ao buscar horario do setor Comercial: " . mysqli_error($CONEXAO); }
		else{ //echo "Conexao foi realizada com sucesso!";
        }
        
        while ($res = mysqli_fetch_array($Result)) {                                        // Pegando e inserindo em variaveis separadas o resultado
            //echo $res['setor'] . "-" . $res['horaInicio']. "-" . $res['horaFim']. "-" . $res['diaSemana']. "-" . $res['numeroDiaSemana'] . "\n";
            $horaLimite1 = $res['horaInicio'];                                              // Inicio do atendimento por telefone
            $horaLimite2 = $res['horaFim'];                                                 // Fim do atendimento por telefone
        }
        
        echo $dataSistem ."-". $horaSistem ."\n";
        $diasFeriados = diasFeriadosNacionais();
        //print_r($diasFeriados);
        $feriado = 0;

        if (($diaSemana >= 1) && ($diaSemana <= 6)) {                                       // Verifica se o dia atual está entre Segunda-feria e Sábado
            foreach($diasFeriados as &$fer) {                                               // Percorre o array com os feriados nacionais
                if ($fer == $dataSistem)
                    $feriado = 1;
            }

            if ($feriado == 1) {                                                            // Verifica se variavel feriado é '1' que indica ser feriado
                echo "NÃO tem atendimento e é feriado";
                return false;
            } else {
                if (($horaSistem > $horaLimite1) && ($horaSistem < $horaLimite2)) {         // Verificando se está dentro do horário de atendimento
                    echo "Tem atendimento e não é feriado";
                    return true;
                } else {
                    echo "NÃO tem atendimento e não é feriado";
                    return false;
                }
            }        
        } else if ($diaSemana == 0) {                                                       // Verifica se o dia atual é Domingo
            echo "NÃO tem atendimento e é Domingo";
            return false;
        }
    }

    /** Função que verifica horário de funcionamento do Setor de Pagamentos nos dias da semana
     * Segunda-feira à Sexta-feira de 08:00:00 às 22:00:00 | Sábado de 08:00:00 às 20:00:00
     * Domingos e feriados(Nacionais ou muncipais) não tem Atendimento Telefônico
     * @return bool $horario Caso seja TRUE está em horario de atendimento, caso FALSE sem atendimento
     */
    function verificaHorarioPagamentos()
    {
        include __DIR__ . DIRECTORY_SEPARATOR . "conexao.php";

        //Obtendo a data do sistema
        $timezone  = -3;   										                            // Definindo o Timerzone do horário
        $dataSistem = 	gmdate('d/m/Y',time() + 3600*($timezone+date("I")));                // Buscando somente a data - gmdate('F d Y H:i:s', time() + 3600*($timezone+date("I")));
        $horaSistem = gmdate('H:i:s',time() + 3600*($timezone+date("I")));                  // Buscando somente a hora - gmdate('F d Y H:i:s', time() + 3600*($timezone+date("I")));
        $diaSemana = gmdate('w',time() + 3600*($timezone+date("I")));                       // Buscando somente o dia da semana - gmdate('F d Y H:i:s', time() + 3600*($timezone+date("I")));
        
        // Busca no DB pelo dia da semana o horario correspondente
		$Busca = "SELECT * FROM horarioAtendimento WHERE numeroDiaSemana = $diaSemana AND setor = 'Pagamentos'";
		$Result = mysqli_query($CONEXAO,$Busca);
		// Impressão de erros na conexão com o DB
		if(!$Result){ echo "Falha de conexao ao buscar horario do setor de Pagamentos: " . mysqli_error($CONEXAO); }
		else{ //echo "Conexao foi realizada com sucesso!";
        }
        
        while ($res = mysqli_fetch_array($Result)) {                                        // Pegando e inserindo em variaveis separadas o resultado
            //echo $res['setor'] . "-" . $res['horaInicio']. "-" . $res['horaFim']. "-" . $res['diaSemana']. "-" . $res['numeroDiaSemana'] . "\n";
            $horaLimite1 = $res['horaInicio'];                                              // Inicio do atendimento por telefone
            $horaLimite2 = $res['horaFim'];                                                 // Fim do atendimento por telefone
        }
        
        echo $dataSistem ."-". $horaSistem ."\n";
        $diasFeriados = diasFeriadosNacionalMunicipal();
        //print_r($diasFeriados);
        $feriado = 0;

        if (($diaSemana >= 1) && ($diaSemana <= 5)) {                                       // Verifica se o dia atual está entre Segunda-feira e Sábado
            foreach($diasFeriados as &$fer) {                                               // Percorre o array com os feriados nacionais e municipais
                if ($fer == $dataSistem)
                    $feriado = 1;
            }

            if ($feriado == 1) {                                                            // Verifica se variavel feriado é '1' que indica ser feriado
                echo "NÃO tem atendimento e é feriado";
                return false;
            } else {
                if (($horaSistem > $horaLimite1) && ($horaSistem < $horaLimite2)) {         // Verificando se está dentro do horário de atendimento
                    echo "Tem atendimento e não é feriado";
                    return true;
                } else {
                    echo "NÃO tem atendimento e não é feriado";
                    return false;
                }
            }    
        } else if ($diaSemana == 0) {                                                       // Verifica se o dia atual é Domingo
            echo "NÃO tem atendimento e é Domingo";
            return false;
        } else if ($diaSemana == 6) {
            foreach($diasFeriados as &$fer) {                                               // Percorre o array com os feriados nacionais e municipais
                if ($fer == $dataSistem)
                    $feriado = 1;
            }

            if ($feriado == 1) {                                                            // Verifica se variavel feriado é '1' que indica ser feriado
                echo "NÃO tem atendimento e é feriado";
                return false;
            } else {
                if (($horaSistem > $horaLimite1) && ($horaSistem < $horaLimite2)) {         // Verificando se está dentro do horário de atendimento
                    echo "Tem atendimento e não é feriado";
                    return true;
                } else {
                    echo "NÃO tem atendimento e não é feriado";
                    return false;
                }
            } 
        }
    }

    /** Função que verifica horário de funcionamento do Setor de Contratos nos dias da semana
     * Segunda-feira à Sexta-feira de 07:00:00 às 22:00:00 | Sábado de 08:00:00 às 20:00:00
     * Domingos e feriados(Nacionais ou muncipais) não tem Atendimento Telefônico
     * @return bool $horario Caso seja TRUE está em horario de atendimento, caso FALSE sem atendimento
     */
    function verificaHorarioContratos()
    {
        include __DIR__ . DIRECTORY_SEPARATOR . "conexao.php";

        //Obtendo a data do sistema
        $timezone  = -3;   										                            // Definindo o Timerzone do horário
        $dataSistem = 	gmdate('d/m/Y',time() + 3600*($timezone+date("I")));                // Buscando somente a data - gmdate('F d Y H:i:s', time() + 3600*($timezone+date("I")));
        $horaSistem = gmdate('H:i:s',time() + 3600*($timezone+date("I")));                  // Buscando somente a hora - gmdate('F d Y H:i:s', time() + 3600*($timezone+date("I")));
        $diaSemana = gmdate('w',time() + 3600*($timezone+date("I")));                       // Buscando somente o dia da semana - gmdate('F d Y H:i:s', time() + 3600*($timezone+date("I")));
       
        // Busca no DB pelo dia da semana o horario correspondente
		$Busca = "SELECT * FROM horarioAtendimento WHERE numeroDiaSemana = $diaSemana AND setor = 'Contratos'";
		$Result = mysqli_query($CONEXAO,$Busca);
		// Impressão de erros na conexão com o DB
		if(!$Result){ echo "Falha de conexao ao buscar horario do setor de Contratos: " . mysqli_error($CONEXAO); }
		else{ //echo "Conexao foi realizada com sucesso!";
        }
        
        while ($res = mysqli_fetch_array($Result)) {                                        // Pegando e inserindo em variaveis separadas o resultado
            //echo $res['setor'] . "-" . $res['horaInicio']. "-" . $res['horaFim']. "-" . $res['diaSemana']. "-" . $res['numeroDiaSemana'] . "\n";
            $horaLimite1 = $res['horaInicio'];                                              // Inicio do atendimento por telefone
            $horaLimite2 = $res['horaFim'];                                                 // Fim do atendimento por telefone
        }
        
        echo $dataSistem ."-". $horaSistem ."\n";
        $diasFeriados = diasFeriadosNacionalMunicipal();
        //print_r($diasFeriados);
        $feriado = 0;

        if (($diaSemana >= 1) && ($diaSemana <= 5)) {                                       // Verifica se o dia atual está entre Segunda-feira e Sábado
            foreach($diasFeriados as &$fer) {                                               // Percorre o array com os feriados nacionais e municipais
                if ($fer == $dataSistem)
                    $feriado = 1;
            }

            if ($feriado == 1) {                                                            // Verifica se variavel feriado é '1' que indica ser feriado
                echo "NÃO tem atendimento e é feriado";
                return false;
            } else {
                if (($horaSistem > $horaLimite1) && ($horaSistem < $horaLimite2)) {         // Verificando se está dentro do horário de atendimento
                    echo "Tem atendimento e não é feriado";
                    return true;
                } else {
                    echo "NÃO tem atendimento e não é feriado";
                    return false;
                }
            }    
        } else if ($diaSemana == 0) {                                                       // Verifica se o dia atual é Domingo
            echo "NÃO tem atendimento e é Domingo";
            return false;
        } else if ($diaSemana == 6) {
            foreach($diasFeriados as &$fer) {                                               // Percorre o array com os feriados nacionais e municipais
                if ($fer == $dataSistem)
                    $feriado = 1;
            }

            if ($feriado == 1) {                                                            // Verifica se variavel feriado é '1' que indica ser feriado
                echo "NÃO tem atendimento e é feriado";
                return false;
            } else {
                if (($horaSistem > $horaLimite1) && ($horaSistem < $horaLimite2)) {         // Verificando se está dentro do horário de atendimento
                    echo "Tem atendimento e não é feriado";
                    return true;
                } else {
                    echo "NÃO tem atendimento e não é feriado";
                    return false;
                }
            } 
        }
    }
    
    echo "\n+---------------------------------------+\n| Comercial:\t\t\t\t|\n+---------------------------------------+\n";
    verificaHorarioComercial();
    echo "\n\n+---------------------------------------+\n| Pagamentos:\t\t\t\t|\n+---------------------------------------+\n";
    verificaHorarioPagamentos();
    echo "\n\n+---------------------------------------+\n| Contratos:\t\t\t\t|\n+---------------------------------------+\n";
    verificaHorarioContratos();
    echo "\n\n+---------------------------------------+\n| Suporte:\t\t\t\t|\n+---------------------------------------+\n";
    verificaHorarioSuporte();

    
?>