<?php
function api_treino_put($request) {
    $user = wp_get_current_user();
    $user_id = $user->ID;

    if ($user_id === 0) {
        return new WP_Error('error', 'Usuário não autorizado', ['status' => 401]);
    }

    // Obtém o ID do treino da requisição
    $treino_id = $request['id'];
    $treino = get_post($treino_id);

    if (!$treino || $treino->post_type !== 'treinos' || $treino->post_author != $user_id) {
        return new WP_Error('error', 'Treino não encontrado ou não autorizado', ['status' => 404]);
    }

    // Campos principais
    $nome = sanitize_text_field($request['nome']);
    $tamanho_da_piscina = sanitize_text_field($request['tamanho_da_piscina']);
    $chegadas = $request['chegadas'];

    if (empty($chegadas) || !is_array($chegadas)) {
        return new WP_Error('error', 'Nenhum exercício fornecido', ['status' => 400]);
    }

    // Processamento dos exercícios e validação
    $distancia_total = 0;
    $repeticoes_por_tipo_de_nado = [];
    $equipamentos_utilizados = [];
    $exercicios_realizados = [];

    foreach ($chegadas as $chegada) {
        $exercicio_ida_id = $chegada['exercicio_ida']['id'];
        $exercicio_volta_id = $chegada['exercicio_volta']['id'];

        $exercicio_ida = get_post($exercicio_ida_id);
        $exercicio_volta = get_post($exercicio_volta_id);

        if (!$exercicio_ida || !$exercicio_volta) {
            return new WP_Error('error', 'Exercício não encontrado', ['status' => 404]);
        }

        // Recupera os termos das taxonomias
        $tipo_nado_ida = wp_get_post_terms($exercicio_ida_id, 'tipo_nado', array("fields" => "names"));
        $tipo_nado_volta = wp_get_post_terms($exercicio_volta_id, 'tipo_nado', array("fields" => "names"));

        // Coleta os equipamentos utilizados
        $equipamentos_ida = wp_get_post_terms($exercicio_ida_id, 'equipamentos', array("fields" => "names"));
        $equipamentos_volta = wp_get_post_terms($exercicio_volta_id, 'equipamentos', array("fields" => "names"));

        $repeticoes = $chegada['repeticoes'];

        // Calcula a distância total
        $distancia_total += ($tamanho_da_piscina * 2) * $repeticoes;

        // Calcula repetições por tipo de nado
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

        // Coleta os equipamentos utilizados
        $equipamentos_utilizados = array_merge($equipamentos_utilizados, $equipamentos_ida, $equipamentos_volta);

        // Armazena os dados do exercício realizado
        $exercicios_realizados[] = array(
            'exercicio_ida' => $exercicio_ida_id,
            'exercicio_volta' => $exercicio_volta_id,
            'repeticoes' => $repeticoes,
        );
    }

    // Remove equipamentos duplicados
    $equipamentos_utilizados = array_unique($equipamentos_utilizados);
    $equipamentos_utilizados = array_values($equipamentos_utilizados);

    // Atualiza os campos personalizados do treino
    wp_update_post(array(
        'ID' => $treino_id,
        'post_title' => $nome,  // Atualiza o nome do treino
    ));

    update_post_meta($treino_id, 'tamanho_da_piscina', $tamanho_da_piscina);
    update_post_meta($treino_id, 'distancia_total', $distancia_total);
    update_post_meta($treino_id, 'repeticoes_por_tipo_de_nado', json_encode($repeticoes_por_tipo_de_nado));
    update_post_meta($treino_id, 'equipamentos_utilizados', json_encode($equipamentos_utilizados));

    // Atualiza o campo de exercícios realizados
    update_post_meta($treino_id, 'exercicios_realizados', json_encode($exercicios_realizados));

    // Resposta com os dados do treino atualizado
    $response = array(
        'id' => $treino_id,
        'nome' => $nome,
        'tamanho_da_piscina' => $tamanho_da_piscina,
        'distancia_total' => $distancia_total,
        'repeticoes_por_tipo_de_nado' => $repeticoes_por_tipo_de_nado,
        'equipamentos_utilizados' => $equipamentos_utilizados,
        'exercicios_realizados' => $exercicios_realizados,
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