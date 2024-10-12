<?php
function api_treino_delete($request) {
    $user = wp_get_current_user();
    $user_id = $user->ID;

    if ($user_id === 0) {
        return new WP_Error('error', 'Usuário não autorizado', ['status' => 401]);
    }

    $treino_id = $request['id'];
    $treino = get_post($treino_id);

    if (!$treino || $treino->post_type !== 'treinos' || $treino->post_author != $user_id) {
        return new WP_Error('error', 'Treino não encontrado', ['status' => 404]);
    }

    wp_delete_post($treino_id, true);

    return rest_ensure_response('Treino deletado com sucesso.');
}

function register_api_treino_delete() {
    register_rest_route('api', '/treino/(?P<id>\d+)', array(
        'methods' => WP_REST_Server::DELETABLE,
        'callback' => 'api_treino_delete',
    ));
}

add_action('rest_api_init', 'register_api_treino_delete');
?>