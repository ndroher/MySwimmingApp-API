<?php
function api_display_name_put($request) {
    $user = wp_get_current_user();
    $user_id = $user->ID;
    $display_name = sanitize_text_field($request['display_name']);

    if (empty($user_id)) {
        return new WP_Error('error', 'Usuário não autenticado', ['status' => 401]);
    }

    if (empty($display_name)) {
        return new WP_Error('error', 'Nome de exibição não fornecido', ['status' => 406]);
    }

    $response = wp_update_user([
        'ID' => $user_id,
        'display_name' => $display_name
    ]);

    if (is_wp_error($response)) {
        return new WP_Error('error', 'Erro ao atualizar o nome de exibição', ['status' => 500]);
    }

    return rest_ensure_response([
        'message' => 'Nome de exibição atualizado com sucesso',
        'user_id' => $user_id,
        'display_name' => $display_name,
    ]);
}

function register_api_display_name_put() {
    register_rest_route('api', '/users/me', [
        'methods' => WP_REST_Server::EDITABLE,
        'callback' => 'api_display_name_put',
        'permission_callback' => function() {
            return current_user_can('edit_user', get_current_user_id());
        }
    ]);    
}

add_action('rest_api_init', 'register_api_display_name_put');
?>
