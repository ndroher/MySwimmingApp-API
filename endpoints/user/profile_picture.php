<?php
function api_update_profile_picture($request) {
    $user = wp_get_current_user();
    $user_id = $user->ID;
    $file = $request->get_file_params();

    if ($user_id === 0) {
        return new WP_Error('error', 'Usuário não autorizado', ['status' => 401]);
    }

    if (empty($file)) {
        return new WP_Error('error', 'Nenhuma imagem enviada.', ['status' => 400]);
    }

    require_once ABSPATH . 'wp-admin/includes/image.php';
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';

    $attachment_id = media_handle_upload('profile_picture', 0);

    // Verifica se houve um erro no upload
    if (is_wp_error($attachment_id)) {
        return $attachment_id; // Retorna o erro
    }

    // Pega a URL da imagem
    $image_url = wp_get_attachment_url($attachment_id);
    
    // Atualiza o meta do usuário com o ID do anexo
    update_user_meta($user_id, 'custom_avatar_id', $attachment_id);

    // Retorna a URL da nova imagem como resposta
    return rest_ensure_response([
        'status' => 200,
        'message' => 'Imagem de perfil atualizada com sucesso.',
        'image_url' => $image_url
    ]);
}

// Adicionar Avatar
function add_avatar($avatar, $id_or_email) {
    // Verifica se é um ID de usuário
    if (is_numeric($id_or_email)) {
        $attachment_id = get_user_meta($id_or_email, 'custom_avatar_id', true);
        
        if ($attachment_id) {
            $image_url = wp_get_attachment_url($attachment_id);
            return '<img src="' . esc_url($image_url) . '" width="90" height="90" />';
        }
    }

    return $avatar; // Retorna o avatar original caso não consiga retornar o novo avatar
}

add_filter('pre_get_avatar', 'add_avatar', 10, 5);

function register_api_update_profile_picture() {
    register_rest_route('api', '/conta/foto', array(
        'methods' => WP_REST_Server::CREATABLE,
        'callback' => 'api_update_profile_picture',
        'permission_callback' => '__return_true',
    ));
}

add_action('rest_api_init', 'register_api_update_profile_picture');
?>