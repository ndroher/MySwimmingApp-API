<?php
function api_users_get($request) {
    $args = array(
        'orderby'        => 'display_name',
        'order'          => 'ASC'
    );

    $users = get_users($args);

    $user_data = array();
    foreach ($users as $user) {
        $attachment_id = get_user_meta($user->ID, 'custom_avatar_id', true);
        
        if ($attachment_id) {
        $avatar_url = wp_get_attachment_url($attachment_id);
        }else {
        $avatar_url = get_avatar_url($user->ID);
        }

        $user_data[] = array(
            'username'   => $user->user_login,
            'name'       => $user->display_name,
            'avatar_url' => $avatar_url
        );
    }

    return rest_ensure_response($user_data);
}

function register_api_users_get() {
    register_rest_route('api', '/users', [
        'methods'             => WP_REST_Server::READABLE,
        'callback'            => 'api_users_get',
        'permission_callback' => '__return_true',
    ]);
}

add_action('rest_api_init', 'register_api_users_get');
?>
