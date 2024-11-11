<?php
function api_user_statistics($request) {
    $username = sanitize_text_field($request['username']);
    $user = get_user_by('login', $username);

    if (!$user) {
        return new WP_Error('not_found', 'Usuário não encontrado', ['status' => 404]);
    }

    // Total de treinos realizados pelo usuário
    $args = array(
        'author' => $user->ID,
        'post_type' => 'treinos',
        'posts_per_page' => -1,
    );
    $treinos_query = new WP_Query($args);

    $total_treinos = 0;
    $total_metros_nadados = 0;
    $repeticoes_por_tipo_de_nado = [
        "Crawl" => 0,
        "Peito" => 0,
        "Borboleta" => 0,
        "Costas" => 0
    ];;
    $distancia_por_dia = [];
    $atividades_por_semana = [];
    $exercicio_mais_realizado = [];
    $treinos_por_data = [];

    // Define o intervalo de 90 dias atrás até hoje
    $current_time = current_time('timestamp');
    $start_date = strtotime('-90 days', $current_time);

    // Ajusta para o início da semana do intervalo (domingo)
    $start_date = strtotime("last Sunday", $start_date);

    // Define o início da semana (domingo) e o fim (sábado)
    $start_of_week = strtotime("last sunday", $current_time); // Começa no domingo atual
    $end_of_week = strtotime("next saturday", $current_time); // Termina no sábado seguinte
    $end_of_week = strtotime("23:59:59", $end_of_week); // Define a hora para 23:59:59

    // Inicializa o array de distancia_por_dia para a semana atual
    $dias_da_semana = ['Domingo', 'Segunda-feira', 'Terça-feira', 'Quarta-feira', 'Quinta-feira', 'Sexta-feira', 'Sábado'];
    foreach ($dias_da_semana as $dia) {
        $distancia_por_dia[] = [
            'dia' => $dia,
            'distancia' => 0
        ];
    }
    
    if ($treinos_query->have_posts()) {
        while ($treinos_query->have_posts()) {
            $treinos_query->the_post();
            $treino_id = get_the_ID();
            $total_treinos++;

             // Armazena a data do treino
             $data_do_treino = get_the_date('Y-m-d');
             $timestamp_treino = strtotime($data_do_treino);
 
             if (!isset($treinos_por_data[$data_do_treino])) {
                 $treinos_por_data[$data_do_treino] = 0;
             }
             $treinos_por_data[$data_do_treino]++;

            // Pega a distância total do treino
            $distancia_total = get_post_meta($treino_id, 'distancia_total', true);
            $total_metros_nadados += floatval($distancia_total);

            // Pega a data do treino
            $data_do_treino = get_the_date('Y-m-d');
            $timestamp_treino = strtotime($data_do_treino);

            // Verifica se o treino está na semana atual
            if ($timestamp_treino >= $start_of_week && $timestamp_treino <= $end_of_week) {
                $dia_semana = date('w', $timestamp_treino); // 0 (domingo) a 6 (sábado)

                // Atualiza a distância para o dia correspondente
                $distancia_por_dia[$dia_semana]['distancia'] += floatval($distancia_total);
            }

            // Obtém os dados dos exercícios
            $exercicios_json = get_post_meta($treino_id, 'exercicios_realizados', true);
            $exercicios = json_decode($exercicios_json, true);

            if ($exercicios && is_array($exercicios)) {
                foreach ($exercicios as $exercicio) {
                    $exercicio_ida_id = $exercicio['exercicio_ida'];
                    $exercicio_volta_id = $exercicio['exercicio_volta'];
                    $repeticoes = $exercicio['repeticoes'];
            
                    $exercicio_ida = get_post($exercicio_ida_id);
                    $exercicio_volta = get_post($exercicio_volta_id);
            
                    if ($exercicio_ida && $exercicio_volta) {
                        // Conta os tipos de nado das idas e voltas
                        $tipo_nado_ida = wp_get_post_terms($exercicio_ida->ID, 'tipo_nado', array("fields" => "names"));
                        $tipo_nado_volta = wp_get_post_terms($exercicio_volta->ID, 'tipo_nado', array("fields" => "names"));
            
                        foreach ($tipo_nado_ida as $tipo) {
                            if (!isset($repeticoes_por_tipo_de_nado[$tipo])) {
                                $repeticoes_por_tipo_de_nado[$tipo] = 0;
                            }
                            $repeticoes_por_tipo_de_nado[$tipo] += $repeticoes;
                        }
            
                        foreach ($tipo_nado_volta as $tipo) {
                            if (!isset($repeticoes_por_tipo_de_nado[$tipo])) {
                                $repeticoes_por_tipo_de_nado[$tipo] = 0;
                            }
                            $repeticoes_por_tipo_de_nado[$tipo] += $repeticoes;
                        }
            
                        // Conta a frequência de exercícios realizados usando o ID
                        if (!isset($exercicio_mais_realizado[$exercicio_ida_id])) {
                            $exercicio_mais_realizado[$exercicio_ida_id] = 0;
                        }
                        if (!isset($exercicio_mais_realizado[$exercicio_volta_id])) {
                            $exercicio_mais_realizado[$exercicio_volta_id] = 0;
                        }
            
                        $exercicio_mais_realizado[$exercicio_ida_id] += $repeticoes;
                        $exercicio_mais_realizado[$exercicio_volta_id] += $repeticoes;
                    }
                }
            }
            
            // Verifica se existem exercícios realizados antes de buscar o ID do mais realizado
            $exercicio_mais_realizado_id = null;
            if (!empty($exercicio_mais_realizado)) {
                $exercicio_mais_realizado_id = array_search(max($exercicio_mais_realizado), $exercicio_mais_realizado);
            }
        }

        wp_reset_postdata();
    }

    // Contabiliza treinos por semana, dentro do intervalo de 90 dias
    $current_start = $start_date;
    while ($current_start <= $current_time) {
        $current_end = strtotime("next Saturday", $current_start);

        // Contador de treinos semanais
        $contagem_semanal = 0;
        foreach ($treinos_por_data as $data => $treinos_no_dia) {
            $timestamp_treino = strtotime($data);
            if ($timestamp_treino >= $current_start && $timestamp_treino <= $current_end) {
                $contagem_semanal += $treinos_no_dia;
            }
        }

        // Formata o intervalo da semana
        $semana_formatada = date('d/m', $current_start) . ' à ' . date('d/m', $current_end);
        $atividades_por_semana[] = [
            'semana' => $semana_formatada,
            'treinos' => $contagem_semanal
        ];

        // Move para a próxima semana
        $current_start = strtotime("+1 week", $current_start);
    }

    // Encontra o exercício mais realizado
    $exercicio_mais_realizado_nome = null;
    if (!empty($exercicio_mais_realizado)) {
        $exercicio_mais_realizado_nome = array_search(max($exercicio_mais_realizado), $exercicio_mais_realizado);
    }

    $response = array(
        'total_treinos' => $total_treinos,
        'total_metros_nadados' => $total_metros_nadados,
        'repeticoes_por_tipo_de_nado' => $repeticoes_por_tipo_de_nado,
        'distancia_por_dia' => $distancia_por_dia,
        'atividades_por_semana' => $atividades_por_semana,
        'exercicio_mais_realizado_id' => $exercicio_mais_realizado_id,
    );

    return rest_ensure_response($response);
}

function register_api_user_statistics() {
    register_rest_route('api', '/user/(?P<username>[a-zA-Z0-9_-]+)/estatisticas', [
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'api_user_statistics',
    ]);
}

add_action('rest_api_init', 'register_api_user_statistics');
?>