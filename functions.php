<?php
// remove_action('rest_api_init', 'create_initial_rest_routes', 99);

add_filter('rest_endpoints', function($endpoints) {
    unset($endpoints['/wp/v2/users']);
    unset($endpoints['/wp/v2/users/(?P<id>[\d]+)']);
    return $endpoints;
});

$dirbase = get_template_directory();

//ENDPOINTS USER
require_once $dirbase . '/endpoints/user/user.php';

require_once $dirbase . '/endpoints/user/user_get.php';
require_once $dirbase . '/endpoints/user/user_post.php';
require_once $dirbase . '/endpoints/user/display_name_put.php';
require_once $dirbase . '/endpoints/user/password.php';
require_once $dirbase . '/endpoints/user/profile_picture.php';
require_once $dirbase . '/endpoints/user/user_goals_put.php';
require_once $dirbase . '/endpoints/user/user_goals_progress.php';
require_once $dirbase . '/endpoints/user/user_profile.php';

//CUSTOM POST TYPES
require_once $dirbase . '/custom-post-types/treinos_cpt.php';
require_once $dirbase . '/custom-post-types/exercicios_cpt.php';

//ENDPOINTS CPT TREINO
require_once $dirbase . '/endpoints/treino/treino_get.php';
require_once $dirbase . '/endpoints/treino/treino_post.php';
require_once $dirbase . '/endpoints/treino/treino_put.php';
require_once $dirbase . '/endpoints/treino/treino_delete.php';

//ENDPOINTS CPT EXERCICIO
require_once $dirbase . '/endpoints/exercicio/exercicio_get.php';
require_once $dirbase . '/endpoints/exercicio/exercicio_post.php';
require_once $dirbase . '/endpoints/exercicio/exercicio_put.php';
require_once $dirbase . '/endpoints/exercicio/exercicio_delete.php';

function change_api() {
    return 'json';
}
add_filter('rest_url_prefix', 'change_api');

function expire_token() {
    return time() + (60 * 60 * 24);
}
add_action('jwt_auth_expire', 'expire_token');
?>