<?php
function api_treino_post($request) {
    $user = wp_get_current_user();
    $user_id = $user->ID;

    if ($user_id === 0) {
        return new WP_Error('error', 'Usuário não autorizado', ['status' => 401]);
    }

    // Campos principais
    $nome = sanitize_text_field($request['nome']);
    $tamanho_da_piscina = sanitize_text_field($request['tamanho_da_piscina']);
    $exercicios = $request['exercicios']; // Recebe os exercícios como array

    if (empty($exercicios) || !is_array($exercicios)) {
        return new WP_Error('error', 'Nenhum exercício fornecido', ['status' => 400]);
    }

    // Criação do post do treino
    $treino_id = wp_insert_post(array(
        'post_type' => 'treinos',
        'post_title' => $nome,
        'post_status' => 'publish',
        'post_author' => $user_id,
    ));

    if (!$treino_id) {
        return new WP_Error('error', 'Erro ao criar treino', ['status' => 500]);
    }

    // Processa os exercícios e calcula informações
    $distancia_total = 0;
    $repeticoes_por_tipo_de_nado = [];
    $equipamentos_utilizados = [];

    foreach ($exercicios as $exercicio) {
        $laps = $exercicio['laps']; // Pega as chegadas (ida/volta)
        foreach ($laps as $lap) {
            $repetitions = $lap['repetitions'];

            // Calcula a distância: multiplica o tamanho da piscina pela ida e volta, e multiplica pelo número de repetições
            $distancia_total += ($tamanho_da_piscina * 2) * $repetitions; // Ida e volta, multiplicado pelas repetições

            // Calcula repetições por tipo de nado
            $tipo_nado_ida = $lap['exercise_ida']['stroke_type'];
            $tipo_nado_volta = $lap['exercise_volta']['stroke_type'];
            
            if (!isset($repeticoes_por_tipo_de_nado[$tipo_nado_ida])) {
                $repeticoes_por_tipo_de_nado[$tipo_nado_ida] = 0;
            }
            if (!isset($repeticoes_por_tipo_de_nado[$tipo_nado_volta])) {
                $repeticoes_por_tipo_de_nado[$tipo_nado_volta] = 0;
            }
            
            $repeticoes_por_tipo_de_nado[$tipo_nado_ida] += $repetitions;
            $repeticoes_por_tipo_de_nado[$tipo_nado_volta] += $repetitions;

            // Coleta os equipamentos utilizados
            $equipamentos_ida = $lap['exercise_ida']['equipment'];
            $equipamentos_volta = $lap['exercise_volta']['equipment'];

            $equipamentos_utilizados = array_merge($equipamentos_utilizados, $equipamentos_ida, $equipamentos_volta);
        }
    }

    // Remove duplicados dos equipamentos
    $equipamentos_utilizados = array_unique($equipamentos_utilizados);

    // Atualiza os campos personalizados
    update_post_meta($treino_id, 'tamanho_da_piscina', $tamanho_da_piscina);
    update_post_meta($treino_id, 'distancia_total', $distancia_total);
    update_post_meta($treino_id, 'repeticoes_por_tipo_de_nado', json_encode($repeticoes_por_tipo_de_nado));
    update_post_meta($treino_id, 'equipamentos_utilizados', json_encode($equipamentos_utilizados));

    $response = array(
        'id' => $treino_id,
        'nome' => $nome,
        'tamanho_da_piscina' => $tamanho_da_piscina,
        'distancia_total' => $distancia_total,
        'repeticoes_por_tipo_de_nado' => $repeticoes_por_tipo_de_nado,
        'equipamentos_utilizados' => $equipamentos_utilizados,
    );

    return rest_ensure_response($response);
}

function register_api_treino_post() {
    register_rest_route('api', '/treino', array(
        'methods' => WP_REST_Server::CREATABLE,
        'callback' => 'api_treino_post',
        'permission_callback' => '__return_true'
    ));
}

add_action('rest_api_init', 'register_api_treino_post');
?>