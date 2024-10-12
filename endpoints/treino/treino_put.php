<?php
function api_treino_put($request) {
    $user = wp_get_current_user();
    $user_id = $user->ID;

    if ($user_id === 0) {
        return new WP_Error('error', 'Usuário não autorizado', ['status' => 401]);
    }

    $treino_id = $request['id'];
    $nome = sanitize_text_field($request['nome']);
    $tamanho_da_piscina = sanitize_text_field($request['tamanho_da_piscina']);
    $exercicios = $request['exercicios'];

    // Atualiza o post do treino
    $treino = array(
        'ID' => $treino_id,
        'post_title' => $nome,
        'post_status' => 'publish',
        'post_author' => $user_id,
    );

    // Atualiza o post no banco de dados
    wp_update_post($treino);

    // Inicializa os dados a serem salvos
    $distancia_total = 0;
    $repeticoes_por_tipo_de_nado = [];
    $equipamentos_utilizados = [];

    // Itera sobre os exercícios para calcular as repetições e a distância total
    foreach ($exercicios as $exercicio) {
        foreach ($exercicio['laps'] as $lap) {
            $ida_repetitions = $lap['repetitions'];
            $volta_repetitions = $lap['repetitions'];

            // Calcular a distância total com base nas chegadas
            $distancia_total += ($ida_repetitions + $volta_repetitions) * $tamanho_da_piscina;

            // Contabilizar repetições por tipo de nado
            $repeticoes_por_tipo_de_nado[$lap['exercise_ida']['stroke_type']] = 
                ($repeticoes_por_tipo_de_nado[$lap['exercise_ida']['stroke_type']] ?? 0) + $ida_repetitions;
            $repeticoes_por_tipo_de_nado[$lap['exercise_volta']['stroke_type']] = 
                ($repeticoes_por_tipo_de_nado[$lap['exercise_volta']['stroke_type']] ?? 0) + $volta_repetitions;

            // Coletar equipamentos utilizados
            $equipamentos_utilizados = array_merge($equipamentos_utilizados, $lap['exercise_ida']['equipment'], $lap['exercise_volta']['equipment']);
        }
    }

    // Remove duplicatas de equipamentos
    $equipamentos_utilizados = array_unique($equipamentos_utilizados);

    // Atualiza os meta dados
    update_post_meta($treino_id, 'tamanho_da_piscina', $tamanho_da_piscina);
    update_post_meta($treino_id, 'distancia_total', $distancia_total);
    update_post_meta($treino_id, 'repeticoes_por_tipo_de_nado', $repeticoes_por_tipo_de_nado);
    update_post_meta($treino_id, 'equipamentos_utilizados', $equipamentos_utilizados);

    // Resposta de sucesso
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

function register_api_treino_put() {
    register_rest_route('api', '/treino/(?P<id>\d+)', array(
        'methods' => WP_REST_Server::EDITABLE,
        'callback' => 'api_treino_put',
        'permission_callback' => '__return_true'
    ));
}

add_action('rest_api_init', 'register_api_treino_put');
?>
