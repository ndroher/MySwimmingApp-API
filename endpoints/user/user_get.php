<?php
    function api_user_get($request) {
        $user = wp_get_current_user();
        $user_id = $user->ID;

        if($user_id === 0) {
            $response = new WP_Error('error', 'Usuário não possui permissão', ['status' => 401]);
            return rest_ensure_response($response);
        }

        $attachment_id = get_user_meta($user_id, 'custom_avatar_id', true);
        
        if ($attachment_id) {
        $avatar_url = wp_get_attachment_url($attachment_id);
        }else {
        $avatar_url = get_avatar_url($user_id);
        }

        $response = [
            'id' => $user_id,
            'username' => $user->user_login,
            'name' => $user->display_name,
            'email' => $user->user_email,
            'avatar_url' => $avatar_url
        ];
        return rest_ensure_response($response);
    }

    function register_api_user_get() {
        register_rest_route('api', '/user', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => 'api_user_get',
        ]);
    }

    add_action('rest_api_init', 'register_api_user_get');
?>