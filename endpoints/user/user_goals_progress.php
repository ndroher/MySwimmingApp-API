<?php
function api_user_goals_progress($request) {
    $username = sanitize_text_field($request['username']);
    $user = get_user_by('login', $username);
    $user_id = $user->ID;

    $weekly_goal = get_user_meta($user_id, 'weekly_goal', true);
    $monthly_goal = get_user_meta($user_id, 'monthly_goal', true);
    $yearly_goal = get_user_meta($user_id, 'yearly_goal', true);

    // Obter timestamps atuais
    $current_time = current_time('timestamp');

    // Calcular ínicio e fim da semana atual
    $start_of_week = strtotime("last sunday", $current_time); // Começa no domingo atual
    $end_of_week = strtotime("next saturday", $current_time); // Termina no sábado seguinte
    $end_of_week = strtotime("23:59:59", $end_of_week); // Define a hora para 23:59:59


    // Calcular início e fim do mês atual
    $start_of_month = strtotime(date('Y-m-01', $current_time)); // Primeiro dia do mês
    $end_of_month = strtotime(date('Y-m-t', $current_time)); // Último dia do mês

    // Calcular início e fim do ano atual
    $start_of_year = strtotime(date('Y-01-01', $current_time)); // Primeiro dia do ano
    $end_of_year = strtotime(date('Y-12-31', $current_time)); // Último dia do ano

    // Obter treinos realizados na semana atual
    $weekly_trainings = get_posts([
        'post_type' => 'treinos',
        'author' => $user_id,
        'date_query' => [
    [
        'after' => date('Y-m-d', $start_of_week),
        'before' => date('Y-m-d', $end_of_week),
        'inclusive' => true,
    ]
]

    ]);

    // Obter treinos realizados no mês atual
    $monthly_trainings = get_posts([
        'post_type' => 'treinos',
        'author' => $user_id,
        'date_query' => [
            [
                'after' => date('Y-m-d H:i:s', $start_of_month),
                'before' => date('Y-m-d H:i:s', $end_of_month),
                'inclusive' => true,
            ]
        ]
    ]);

    // Obter treinos realizados no ano atual
    $yearly_trainings = get_posts([
        'post_type' => 'treinos',
        'author' => $user_id,
        'date_query' => [
            [
                'after' => date('Y-m-d H:i:s', $start_of_year),
                'before' => date('Y-m-d H:i:s', $end_of_year),
                'inclusive' => true,
            ]
        ]
    ]);

    // Montar a resposta com metas e progresso
    $response = [
        'weekly_goal' => $weekly_goal,
        'weekly_progress' => count($weekly_trainings),
        'monthly_goal' => $monthly_goal,
        'monthly_progress' => count($monthly_trainings),
        'yearly_goal' => $yearly_goal,
        'yearly_progress' => count($yearly_trainings),
    ];

    return rest_ensure_response($response);
}

function register_api_user_goals_progress() {
    register_rest_route('api', '/user/(?P<username>[a-zA-Z0-9_-]+)/goals', [
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'api_user_goals_progress',
    ]);
}

add_action('rest_api_init', 'register_api_user_goals_progress');
?>