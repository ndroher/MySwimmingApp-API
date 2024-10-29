<?php
function api_user_history($request) {
    $username = sanitize_text_field($request['username']);
    $user = get_user_by('login', $username);

    if (!$user) {
        return new WP_Error('not_found', 'Usuário não encontrado', ['status' => 404]);
    }

    $treinos = get_posts(array(
        'author' => $user->ID,
        'post_type' => 'treinos',
        'posts_per_page' => -1,
    ));

    function get_exercicios_treino($treino_id) {
        $exercicios_json = get_post_meta($treino_id, 'exercicios_realizados', true);
        $exercicios_data = [];

        $exercicios = json_decode($exercicios_json, true);
        
        if ($exercicios && is_array($exercicios)) {
            foreach ($exercicios as $exercicio) {
                $exercicio_ida_id = $exercicio['exercicio_ida'];
                $exercicio_volta_id = $exercicio['exercicio_volta'];
                $repeticoes = $exercicio['repeticoes'];

                $exercicio_ida = get_post($exercicio_ida_id);
                $exercicio_volta = get_post($exercicio_volta_id);

                if ($exercicio_ida && $exercicio_volta) {
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
    
    $historico = array();
    foreach ($treinos as $treino) {
        $exercicios_data = get_exercicios_treino($treino->ID);

        $historico[] = array(
            'id' => $treino->ID,
            'nome' => $treino->post_title,
            'post_date' => formatDate($treino->post_date),
            'tamanho_da_piscina' => get_post_meta($treino->ID, 'tamanho_da_piscina', true),
            'distancia_total' => get_post_meta($treino->ID, 'distancia_total', true),
            'repeticoes_por_tipo_de_nado' => json_decode(get_post_meta($treino->ID, 'repeticoes_por_tipo_de_nado', true), true),
            'equipamentos_utilizados' => json_decode(get_post_meta($treino->ID, 'equipamentos_utilizados', true), true),
            'exercicios' => $exercicios_data
        );
    }    

    return rest_ensure_response($historico);
}

function register_api_user_history() {
    register_rest_route('api', '/user/(?P<username>[a-zA-Z0-9_-]+)/historico', [
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'api_user_history',
    ]);
}

add_action('rest_api_init', 'register_api_user_history');
?>