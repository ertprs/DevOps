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

            // Essas Datas depem diretamente da data de Pascoa
            // mktime(0, 0, 0, $mes_pascoa, $dia_pascoa - 48, $ano_pascoa),             //2ºferia Carnaval
            date('d/m/Y',mktime(0, 0, 0, $mes_pascoa, $dia_pascoa - 47, $ano_pascoa)),  //3ºferia Carnaval
            date('d/m/Y',mktime(0, 0, 0, $mes_pascoa, $dia_pascoa - 2, $ano_pascoa)),   //6ºfeira Santa
            date('d/m/Y',mktime(0, 0, 0, $mes_pascoa, $dia_pascoa, $ano_pascoa)),       //Pascoa
            date('d/m/Y',mktime(0, 0, 0, $mes_pascoa, $dia_pascoa + 60, $ano_pascoa))   //Corpus Cirist
        );

        return $feriados;
    }

    /** Função que possui somente os feriados Municipais
     * @return array $feriados Retorna um array com todos os feriados
     */
    function diasFeriadosMunicipais()
    {
        $ano = intval(date('Y'));    
        
        $feriados = array(                                                              // Array com datas Fixas dos feriados Nacionail e Municipais
            date('d/m/Y',mktime(0, 0, 0, 6, 14, $ano)),                                 // Dia de Nhá Chica - Feriado Municipal
            date('d/m/Y',mktime(0, 0, 0, 8, 15, $ano)),                                 // Dia de assunção de Nossa Senhora - Feriado Municipal
            date('d/m/Y',mktime(0, 0, 0, 12, 8, $ano))                                  // Aniversário da Cidade - Feriado Municipal
        );

        return $feriados;
    }

    /** Função que verifica horário de funcionamento do Setor de Suporte técnico nos dias da semana
     * Segunda-feira à Sexta-feira de 07:00:00 às 22:00:00 | Sábados de 08:00:00 ás 20:00:00
     * Domingos e feriados Nacionais de 13:00:00 às 19:00:00
     * Feriados Municipais o horário de funcionamento é o horário de Sábado que é de 08:00:00 às 20:00:00
     * @return bool $horario Caso seja TRUE está em horario de atendimento, caso FALSE sem atendimento
     */
    function verificaHorarioSuporte()
    {
        //Obtendo a data do sistema
        $timezone  = -3;   										                            // Definindo o Timerzone do horário
        $dataSistem = 	gmdate('d/m/Y',time() + 3600*($timezone+date("I")));                // Buscando somente a data - gmdate('F d Y H:i:s', time() + 3600*($timezone+date("I")));
        $horaSistem = gmdate('H:i:s',time() + 3600*($timezone+date("I")));                  // Buscando somente a hora - gmdate('F d Y H:i:s', time() + 3600*($timezone+date("I")));
        $diaSemana = gmdate('w',time() + 3600*($timezone+date("I")));                       // Buscando somente o dia da semana - gmdate('F d Y H:i:s', time() + 3600*($timezone+date("I")));
        $horaLimite1 = '07:00:00';                                                          // Inicio do atendimento por telefone
        $horaLimite1fer = '13:00:00';                                                       // Inicio do atendimento por telefone do feriado nacional
        $horaLimite2 = '08:00:00';                                                          // Inicio do atendimento por telefone do sabado de feriado municipal
        $horaLimite2fer = '20:00:00';                                                       // Fim do atendimento por telefone do sabado de feriado municipal
        $horaLimite3 = '22:00:00';                                                          // Fim do atendimento por telefone
        $horaLimite3fer = '19:00:00';                                                       // Fim do atendimento por telefone do feriado nacional
        
        //echo $dataSistem ."-". $horaSistem ."\n";
        //echo $horaLimite1."\n";
        //echo $horaLimite2."\n";
        //echo $diaSemana ."\n";

        $diasFeriados = diasFeriadosNacionais();
        $diasFeriadosM = diasFeriadosMunicipais();
        //print_r($diasFeriados);
        $feriado = 0;

        if (($diaSemana >= 1) && ($diaSemana <= 5)) {                                       // Verifica se o dia atual está entre Segunda-feira e Sexta-feira
            foreach($diasFeriados as &$fer) {                                               // Percorre o array com os feriados nacionais
                if ($fer == $dataSistem)
                    $feriado = 1;
            }
            
            foreach($diasFeriadosM as &$ferM) {                                             // Percorre o array com os feriados municipais
                if ($ferM == $dataSistem)
                    $feriado = 2;
            }

            if ($feriado == 1) {                                                            // Verifica se variavel feriado é '1' que indica ser feriado nacional
                if (($horaSistem > $horaLimite1fer) && ($horaSistem < $horaLimite3fer)) {   // Verificando se está dentro do horário de atendimento
                    //echo "Tem atendimento e é feriado Nacional";
                    return true;
                } else {
                    //echo "NÃO tem atendimento e é feriado Nacional";
                    return false;
                }
            } else if ($feriado == 2) {                                                     // Verifica se variavel feriado é '2' que indica ser feriado municipal
                if (($horaSistem > $horaLimite2) && ($horaSistem < $horaLimite2fer)) {      // Verificando se está dentro do horário de atendimento
                    //echo "Tem atendimento e é feriado Municpal";
                    return true;
                } else {
                    //echo "NÃO tem atendimento e não é feriado Municipal";
                    return false;
                }
            } else {
                if (($horaSistem > $horaLimite1) && ($horaSistem < $horaLimite3)) {         // Verificando se está dentro do horário de atendimento
                    //echo "Tem atendimento e não é feriado";
                    return true;
                } else {
                    //echo "NÃO tem atendimento e não é feriado";
                    return false;
                }
            }        
        } else if ($diaSemana == 0){                                                        // Verifica se o dia atual é Domingo
            if (($horaSistem > $horaLimite1fer) && ($horaSistem < $horaLimite3fer)) {       // Verificando se está dentro do horário de atendimento
                //echo "Tem atendimento e é Domingo";
                return true;
            } else {
                //echo "NÃO tem atendimento e é Domingo";
                return false;
            }
        } else if ($diaSemana == 6) {                                                       // Verifica se o dia atual é Sábado
            foreach($diasFeriados as &$fer) {                                               // Percorre o array com os feriados nacionais
                if ($fer == $dataSistem)
                    $feriado = 1;
            }
            
            foreach($diasFeriadosM as &$ferM) {                                             // Percorre o array com os feriados municipais
                if ($ferM == $dataSistem) {
                    $feriado = 2; 
            }

            if ($feriado == 1){                                                             // Verifica se variavel feriado é '1' que indica ser feriado nacional
                if (($horaSistem > $horaLimite1fer) && ($horaSistem < $horaLimite3fer)) {   // Verificando se está dentro do horário de atendimento
                    //echo "Tem atendimento, é feriado Nacional e é Sábado";
                    return true;
                } else {
                    //echo "NÃO tem atendimento, é feriado Nacional e é Sábado";
                    return false;
                }
            } else if ($feriado == 2) {                                                     // Verifica se variavel feriado é '2' que indica ser feriado municipal
                if (($horaSistem > $horaLimite2) && ($horaSistem < $horaLimite2fer)) {      // Verificando se está dentro do horário de atendimento
                    //echo "Tem atendimento, é feriado Municpal e é Sábado";
                    return true;
                } else {
                    //echo "NÃO tem atendimento, não é feriado Municipal e é Sábado";
                    return false;
                }
            } else {
                if (($horaSistem > $horaLimite2) && ($horaSistem < $horaLimite2fer)) {         // Verificando se está dentro do horário de atendimento
                    //echo "Tem atendimento, não é feriado e é sábado";
                    return true;
                } else {
                    //echo "NÃO tem atendimento, não é feriado e é sábado";
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
    function varificaHorarioComercial()
    {
        //Obtendo a data do sistema
        $timezone  = -3;   										                            // Definindo o Timerzone do horário
        $dataSistem = 	gmdate('d/m/Y',time() + 3600*($timezone+date("I")));                // Buscando somente a data - gmdate('F d Y H:i:s', time() + 3600*($timezone+date("I")));
        $horaSistem = gmdate('H:i:s',time() + 3600*($timezone+date("I")));                  // Buscando somente a hora - gmdate('F d Y H:i:s', time() + 3600*($timezone+date("I")));
        $diaSemana = gmdate('w',time() + 3600*($timezone+date("I")));                       // Buscando somente o dia da semana - gmdate('F d Y H:i:s', time() + 3600*($timezone+date("I")));
        $horaLimite1 = '08:00:00';                                                          // Inicio do atendimento por telefone
        $horaLimite2 = '20:00:00';                                                          // Fim do atendimento por telefone

        echo $dataSistem ."-". $horaSistem ."\n";
        echo $horaLimite1."\n";
        echo $horaLimite2."\n";
        echo $diaSemana ."\n";

        $diasFeriados = diasFeriadosNacionais();
        print_r($diasFeriados);
        $feriado = 0;

        if (($diaSemana >= 1) && ($diaSemana <= 6)) {                                       // Verifica se o dia atual está entre Segunda-feria e Sábado
            foreach($diasFeriados as &$fer) {                                               // Percorre o array com os feriados nacionais
                if ($fer == $dataSistem){
                    $feriado = 1;}
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
        //Obtendo a data do sistema
        $timezone  = -3;   										                            // Definindo o Timerzone do horário
        $dataSistem = 	gmdate('d/m/Y',time() + 3600*($timezone+date("I")));                // Buscando somente a data - gmdate('F d Y H:i:s', time() + 3600*($timezone+date("I")));
        $horaSistem = gmdate('H:i:s',time() + 3600*($timezone+date("I")));                  // Buscando somente a hora - gmdate('F d Y H:i:s', time() + 3600*($timezone+date("I")));
        $diaSemana = gmdate('w',time() + 3600*($timezone+date("I")));                       // Buscando somente o dia da semana - gmdate('F d Y H:i:s', time() + 3600*($timezone+date("I")));
        $horaLimite1 = '08:00:00';                                                          // Inicio do atendimento por telefone
        $horaLimite2 = '20:00:00';                                                          // Fim do atendimento por telefone
        $horaLimite3 = '22:00:00';                                                          // Fim do atendimento por telefone

        //echo $dataSistem ."-". $horaSistem ."\n";
        //echo $horaLimite1."\n";
        //echo $horaLimite2."\n";
        //echo $diaSemana ."\n";

        $diasFeriados = diasFeriadosNacionalMunicipal();
        //print_r($diasFeriados);
        $feriado = 0;

        if (($diaSemana >= 1) && ($diaSemana <= 5)) {                                       // Verifica se o dia atual está entre Segunda-feira e Sábado
            foreach($diasFeriados as &$fer) {                                               // Percorre o array com os feriados nacionais e municipais
                if ($fer == $dataSistem)
                    $feriado = 1;
            }

            if ($feriado == 1) {                                                            // Verifica se variavel feriado é '1' que indica ser feriado
                //echo "NÃO tem atendimento e é feriado";
                return false;
            } else {
                if (($horaSistem > $horaLimite1) && ($horaSistem < $horaLimite3)) {         // Verificando se está dentro do horário de atendimento
                    //echo "Tem atendimento e não é feriado";
                    return true;
                } else {
                    //echo "NÃO tem atendimento e não é feriado";
                    return false;
                }
            }    
        } else if ($diaSemana == 0) {                                                       // Verifica se o dia atual é Domingo
            //echo "NÃO tem atendimento e é Domingo";
            return false;
        } else if ($diaSemana == 6) {
            foreach($diasFeriados as &$fer) {                                               // Percorre o array com os feriados nacionais e municipais
                if ($fer == $dataSistem)
                    $feriado = 1;
            }

            if ($feriado == 1) {                                                            // Verifica se variavel feriado é '1' que indica ser feriado
                //echo "NÃO tem atendimento e é feriado";
                return false;
            } else {
                if (($horaSistem > $horaLimite1) && ($horaSistem < $horaLimite2)) {         // Verificando se está dentro do horário de atendimento
                    //echo "Tem atendimento e não é feriado";
                    return true;
                } else {
                    //echo "NÃO tem atendimento e não é feriado";
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
        //Obtendo a data do sistema
        $timezone  = -3;   										                            // Definindo o Timerzone do horário
        $dataSistem = 	gmdate('d/m/Y',time() + 3600*($timezone+date("I")));                // Buscando somente a data - gmdate('F d Y H:i:s', time() + 3600*($timezone+date("I")));
        $horaSistem = gmdate('H:i:s',time() + 3600*($timezone+date("I")));                  // Buscando somente a hora - gmdate('F d Y H:i:s', time() + 3600*($timezone+date("I")));
        $diaSemana = gmdate('w',time() + 3600*($timezone+date("I")));                       // Buscando somente o dia da semana - gmdate('F d Y H:i:s', time() + 3600*($timezone+date("I")));
        $horaLimite1 = '07:00:00';                                                          // Inicio do atendimento por telefone
        $horaLimite2 = '08:00:00';                                                          // Inicio do atendimento por telefone
        $horaLimite3 = '20:00:00';                                                          // Fim do atendimento por telefone
        $horaLimite4 = '22:00:00';                                                          // Fim do atendimento por telefone

        //echo $dataSistem ."-". $horaSistem ."\n";
        //echo $horaLimite1."\n";
        //echo $horaLimite2."\n";
        //echo $diaSemana ."\n";

        $diasFeriados = diasFeriadosNacionalMunicipal();
        //print_r($diasFeriados);
        $feriado = 0;

        if (($diaSemana >= 1) && ($diaSemana <= 5)) {                                       // Verifica se o dia atual está entre Segunda-feira e Sábado
            foreach($diasFeriados as &$fer) {                                               // Percorre o array com os feriados nacionais e municipais
                if ($fer == $dataSistem)
                    $feriado = 1;
            }

            if ($feriado == 1) {                                                            // Verifica se variavel feriado é '1' que indica ser feriado
                //echo "NÃO tem atendimento e é feriado";
                return false;
            } else {
                if (($horaSistem > $horaLimite1) && ($horaSistem < $horaLimite4)) {         // Verificando se está dentro do horário de atendimento
                    //echo "Tem atendimento e não é feriado";
                    return true;
                } else {
                    //echo "NÃO tem atendimento e não é feriado";
                    return false;
                }
            }    
        } else if ($diaSemana == 0) {                                                       // Verifica se o dia atual é Domingo
            //echo "NÃO tem atendimento e é Domingo";
            return false;
        } else if ($diaSemana == 6) {
            foreach($diasFeriados as &$fer) {                                               // Percorre o array com os feriados nacionais e municipais
                if ($fer == $dataSistem)
                    $feriado = 1;
            }

            if ($feriado == 1) {                                                            // Verifica se variavel feriado é '1' que indica ser feriado
                //echo "NÃO tem atendimento e é feriado";
                return false;
            } else {
                if (($horaSistem > $horaLimite2) && ($horaSistem < $horaLimite3)) {         // Verificando se está dentro do horário de atendimento
                    //echo "Tem atendimento e não é feriado";
                    return true;
                } else {
                    //echo "NÃO tem atendimento e não é feriado";
                    return false;
                }
            } 
        }
    }
    
    echo "passou";
?>