<?php
function api_treino_get($request) {
    $user_id = $request['user_id'];
    $treino_id = $request['treino_id'];

    // Função para obter os dados dos exercícios associados
    function get_exercicios_treino($treino_id) {
        $exercicios_json = get_post_meta($treino_id, 'exercicios_realizados', true);
        $exercicios_data = [];

        // Decodifica o JSON
        $exercicios = json_decode($exercicios_json, true);
        
        if ($exercicios && is_array($exercicios)) {
            foreach ($exercicios as $exercicio) {
                $exercicio_ida_id = $exercicio['exercicio_ida'];
                $exercicio_volta_id = $exercicio['exercicio_volta'];
                $repeticoes = $exercicio['repeticoes'];

                // Busca os detalhes dos exercícios pelo ID
                $exercicio_ida = get_post($exercicio_ida_id);
                $exercicio_volta = get_post($exercicio_volta_id);

                if ($exercicio_ida && $exercicio_volta) {
                    // Coletando informações de ida e volta
                    $lap_data = [
                        'exercicio_ida' => [
                            'id' => $exercicio_ida->ID,
                            'nome' => $exercicio_ida->post_title
                        ],
                        'exercicio_volta' => [
                            'id' => $exercicio_volta->ID,
                            'nome' => $exercicio_volta->post_title
                        ],
                        'repeticoes' => $repeticoes
                    ];

                    $exercicios_data[] = $lap_data;
                }
            }
        }

        return $exercicios_data;
    }

    //Função para formatar Data
    function formatDate($dateString) {
        $date = new DateTime($dateString);
        
        // Define a localidade para português
        setlocale(LC_TIME, 'pt_BR.utf8');
        $formattedDate = strftime('%A, %d de %B de %Y, %H:%M', $date->getTimestamp());
    
        $formattedDate = strtolower($formattedDate);
        
        // Formata dia da semana
        $formattedDate = preg_replace_callback('/^(.*?)([a-z])/', function ($matches) {
            return ucfirst($matches[0]);
        }, $formattedDate);
        
        // Formata mês
        $formattedDate = preg_replace_callback('/(\bde )([a-z])/', function ($matches) {
            return $matches[1] . ucfirst($matches[2]);
        }, $formattedDate);
    
        return $formattedDate;
    }

    if ($treino_id) {
        // Recupera um treino específico
        $treino = get_post($treino_id);

        if (!$treino || $treino->post_type !== 'treinos' || $treino->post_author != $user_id) {
            return new WP_Error('error', 'Treino não encontrado', ['status' => 404]);
        }

        // Obtendo exercícios e repetições
        $exercicios_data = get_exercicios_treino($treino_id);

        $response = array(
            'id' => $treino->ID,
            'nome' => $treino->post_title,
            'post_date' => formatDate($treino->post_date),
            'tamanho_da_piscina' => get_post_meta($treino->ID, 'tamanho_da_piscina', true),
            'distancia_total' => get_post_meta($treino->ID, 'distancia_total', true),
            'repeticoes_por_tipo_de_nado' => json_decode(get_post_meta($treino->ID, 'repeticoes_por_tipo_de_nado', true), true),
            'equipamentos_utilizados' => json_decode(get_post_meta($treino->ID, 'equipamentos_utilizados', true), true),
            'exercicios' => $exercicios_data
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
            // Obtendo exercícios e repetições para cada treino
            $exercicios_data = get_exercicios_treino($treino->ID);

            $response[] = array(
                'id' => $treino->ID,
                'nome' => $treino->post_title,
                'tamanho_da_piscina' => get_post_meta($treino->ID, 'tamanho_da_piscina', true),
                'distancia_total' => get_post_meta($treino->ID, 'distancia_total', true),
                'repeticoes_por_tipo_de_nado' => json_decode(get_post_meta($treino->ID, 'repeticoes_por_tipo_de_nado', true), true),
                'equipamentos_utilizados' => json_decode(get_post_meta($treino->ID, 'equipamentos_utilizados', true), true),
                'exercicios' => $exercicios_data
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