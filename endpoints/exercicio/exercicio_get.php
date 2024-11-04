<?php
function api_exercicio_get($request) {
    $user = wp_get_current_user();
    $user_id = $user->ID;
    $exercicio_id = $request['id'];

    if ($exercicio_id) {
        // Recupera um exercício específico
        $exercicio = get_post($exercicio_id);

        if (!$exercicio || $exercicio->post_type !== 'exercicios') {
            return new WP_Error('error', 'Exercício não encontrado', ['status' => 404]);
        }

        $response = array(
            'id' => $exercicio->ID,
            'nome' => $exercicio->post_title,
            'tipo_nado' => wp_get_post_terms($exercicio->ID, 'tipo_nado', array("fields" => "names")),
            'equipamentos' => wp_get_post_terms($exercicio->ID, 'equipamentos', array("fields" => "names")),
            'personalizado' => get_post_meta($exercicio->ID, 'personalizado', true),
        );
    } else {
        // Recupera todos os exercícios do usuário
        $exercicios = get_posts(array(
            'post_type' => 'exercicios',
            'author' => $user_id,
            'posts_per_page' => -1
        ));

       // Recupera todos os exercícios onde 'personalizado' é vazio ou 'false'
        $exercicios_personalizados = get_posts(array(
            'post_type' => 'exercicios',
            'meta_query' => array(
                'relation' => 'OR',
                array(
                    'key' => 'personalizado',
                    'value' => '',
                    'compare' => '='
                ),
                array(
                    'key' => 'personalizado',
                    'value' => 'false',
                    'compare' => '='
                )
            ),
            'posts_per_page' => -1
        ));

        // Combina os resultados, sem duplicatas
        $all_exercicios = array_unique(array_merge($exercicios, $exercicios_personalizados), SORT_REGULAR);

        $response = array();
        foreach ($all_exercicios as $exercicio) {
            $response[] = array(
                'id' => $exercicio->ID,
                'nome' => $exercicio->post_title,
                'tipo_nado' => wp_get_post_terms($exercicio->ID, 'tipo_nado', array("fields" => "names")),
                'equipamentos' => wp_get_post_terms($exercicio->ID, 'equipamentos', array("fields" => "names")),
                'personalizado' => get_post_meta($exercicio->ID, 'personalizado', true),
            );
        }
    }

    return rest_ensure_response($response);
}

function register_api_exercicio_get() {
    register_rest_route('api', '/exercicio/(?P<id>\d+)', array(
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'api_exercicio_get',
    ));

    register_rest_route('api', '/exercicios', array(
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'api_exercicio_get',
    ));
}

add_action('rest_api_init', 'register_api_exercicio_get');
?>