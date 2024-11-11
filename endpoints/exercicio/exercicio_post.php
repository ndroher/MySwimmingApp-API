<?php
function api_exercicio_post($request) {
    $user = wp_get_current_user();
    $user_id = $user->ID;

    if ($user_id === 0) {
        return new WP_Error('error', 'Usuário não autorizado', ['status' => 401]);
    }

    $nome = sanitize_text_field($request['nome']);
    $tipo_nado = array_map('sanitize_text_field', $request['tipo_nado']);
    $equipamentos = array_map('sanitize_text_field', $request['equipamentos']);
    $personalizado = filter_var($request['personalizado'], FILTER_VALIDATE_BOOLEAN);

    $exercicio_id = wp_insert_post(array(
        'post_type' => 'exercicios',
        'post_title' => $nome,
        'post_status' => 'publish',
        'post_author' => $user_id,
    ));

    if ($exercicio_id) {
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

    return new WP_Error('error', 'Erro ao criar exercício', ['status' => 500]);
}

function register_api_exercicio_post() {
    register_rest_route('api', '/exercicio', array(
        'methods' => WP_REST_Server::CREATABLE,
        'callback' => 'api_exercicio_post',
        'permission_callback' => '__return_true',
    ));
}

add_action('rest_api_init', 'register_api_exercicio_post');
?>