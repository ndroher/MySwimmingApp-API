<?php
function api_treino_get($request) {
    $user = wp_get_current_user();
    $user_id = $user->ID;

    if ($user_id === 0) {
        return new WP_Error('error', 'Usuário não autorizado', ['status' => 401]);
    }

    $treino_id = $request['id'];

    if ($treino_id) {
        // Recupera um treino específico
        $treino = get_post($treino_id);

        if (!$treino || $treino->post_type !== 'treinos' || $treino->post_author != $user_id) {
            return new WP_Error('error', 'Treino não encontrado', ['status' => 404]);
        }

        $response = array(
            'id' => $treino->ID,
            'nome' => $treino->post_title,
            'tamanho_da_piscina' => get_post_meta($treino->ID, 'tamanho_da_piscina', true),
            'distancia_total' => get_post_meta($treino->ID, 'distancia_total', true),
            'repeticoes_por_tipo_de_nado' => get_post_meta($treino->ID, 'repeticoes_por_tipo_de_nado', true),
            'equipamentos_utilizados' => get_post_meta($treino->ID, 'equipamentos_utilizados', true),
        );
    } else {
        // Recupera todos os treinos do usuário
        $treinos = get_posts(array(
            'post_type' => 'treinos',
            'author' => $user_id,
            'posts_per_page' => -1
        ));

        $response = array();
        foreach ($treinos as $treino) {
            $response[] = array(
                'id' => $treino->ID,
                'nome' => $treino->post_title,
                'tamanho_da_piscina' => get_post_meta($treino->ID, 'tamanho_da_piscina', true),
                'distancia_total' => get_post_meta($treino->ID, 'distancia_total', true),
                'repeticoes_por_tipo_de_nado' => get_post_meta($treino->ID, 'repeticoes_por_tipo_de_nado', true),
                'equipamentos_utilizados' => get_post_meta($treino->ID, 'equipamentos_utilizados', true),
            );
        }
    }

    return rest_ensure_response($response);
}

function register_api_treino_get() {
    register_rest_route('api', '/treino/(?P<id>\d+)', array(
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'api_treino_get',
    ));

    register_rest_route('api', '/treinos', array(
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'api_treino_get',
    ));
}

add_action('rest_api_init', 'register_api_treino_get');

?>