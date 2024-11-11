<?php
function api_exercicio_put($request) {
    $user = wp_get_current_user();
    $user_id = $user->ID;

    if ($user_id === 0) {
        return new WP_Error('error', 'Usuário não autorizado', ['status' => 401]);
    }

    $exercicio_id = $request['id'];
    $exercicio = get_post($exercicio_id);

    if (!$exercicio || $exercicio->post_type !== 'exercicios' || $exercicio->post_author != $user_id) {
        return new WP_Error('error', 'Exercício não encontrado', ['status' => 404]);
    }

    $nome = sanitize_text_field($request['nome']);
    $tipo_nado = array_map('sanitize_text_field', $request['tipo_nado']);
    $equipamentos = array_map('sanitize_text_field', $request['equipamentos']);
    $personalizado = filter_var($request['personalizado'], FILTER_VALIDATE_BOOLEAN);

    wp_update_post(array(
        'ID' => $exercicio_id,
        'post_title' => $nome,
    ));

    wp_set_object_terms($exercicio_id, $tipo_nado, 'tipo_nado');
    wp_set_object_terms($exercicio_id, $equipamentos, 'equipamentos');
    update_post_meta($exercicio_id, 'personalizado', $personalizado);

    $response = array(
        'id' => $exercicio_id,
        'nome' => $nome,
        'tipo_nado' => $tipo_nado,
        'equipamentos' => $equipamentos,
        'personalizado' => $personalizado,
    );

    return rest_ensure_response($response);
}

function register_api_exercicio_put() {
    register_rest_route('api', '/exercicio/(?P<id>\d+)', array(
        'methods' => WP_REST_Server::EDITABLE,
        'callback' => 'api_exercicio_put',
    ));
}

add_action('rest_api_init', 'register_api_exercicio_put');
?>