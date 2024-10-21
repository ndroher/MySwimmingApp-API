<?php
$dirbase = get_template_directory();
require_once $dirbase . '/endpoints/user/user_goals_progress.php';
require_once $dirbase . '/endpoints/treino/treino_get.php';

function api_user_profile($request) {
    $username = sanitize_text_field($request['username']);
    $user = get_user_by('login', $username);

    if (!$user) {
        return new WP_Error('not_found', 'Usuário não encontrado', ['status' => 404]);
    }

    $user_id = $user->ID;
    $display_name = $user->display_name;

    $attachment_id = get_user_meta($user_id, 'custom_avatar_id', true);
    $avatar_url = $attachment_id ? wp_get_attachment_url($attachment_id) : get_avatar_url($user_id);

    $goals_progress = api_user_goals_progress(['username' => $username]);

    if (is_wp_error($goals_progress)) {
        return $goals_progress;
    }

    $ultimo_treino_busca = get_posts([
        'post_type' => 'treinos',  // Supondo que o tipo de post seja 'treinos'
        'author' => $user_id,      // Filtra pelo ID do usuário
        'posts_per_page' => 1,     // Limita a apenas 1 post
        'orderby' => 'date',       // Ordena pela data de criação
        'order' => 'DESC',         // Em ordem decrescente (o mais recente primeiro)
    ]);

    $ultimo_treino_id = $ultimo_treino_busca[0]->ID;

    $ultimo_treino = api_treino_get(['user_id' => $user_id, 'treino_id' => $ultimo_treino_id]);

    $response = [
        'id' => $user_id,
        'username' => $username,
        'display_name' => $display_name,
        'avatar_url' => $avatar_url,
        'goals' => $goals_progress->data,
        'ultimo_treino' => $ultimo_treino->data,
    ];

    return rest_ensure_response($response);
}

function register_api_user_profile() {
    register_rest_route('api', '/user/(?P<username>[a-zA-Z0-9_-]+)', [
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'api_user_profile',
    ]);
}

add_action('rest_api_init', 'register_api_user_profile');
?>