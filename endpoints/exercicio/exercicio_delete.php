<?php
function api_exercicio_delete($request) {
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

    wp_delete_post($exercicio_id, true);

    return rest_ensure_response('Exercício deletado com sucesso.');
}

function register_api_exercicio_delete() {
    register_rest_route('api', '/exercicio/(?P<id>\d+)', array(
        'methods' => WP_REST_Server::DELETABLE,
        'callback' => 'api_exercicio_delete',
    ));
}

add_action('rest_api_init', 'register_api_exercicio_delete');
?>