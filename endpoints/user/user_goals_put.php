<?php
function update_user_goals($request) {
    $user_id = get_current_user_id();
    if ($user_id === 0) {
        return new WP_Error('error', 'Unauthorized', ['status' => 401]);
    }

    $weekly_goal = sanitize_text_field($request['weekly_goal']);
    $monthly_goal = sanitize_text_field($request['monthly_goal']);
    $yearly_goal = sanitize_text_field($request['yearly_goal']);

    update_user_meta($user_id, 'weekly_goal', $weekly_goal);
    update_user_meta($user_id, 'monthly_goal', $monthly_goal);
    update_user_meta($user_id, 'yearly_goal', $yearly_goal);

    return rest_ensure_response([
        'weekly_goal' => $weekly_goal,
        'monthly_goal' => $monthly_goal,
        'yearly_goal' => $yearly_goal,
    ]);
}

function register_user_goals_endpoint() {
    register_rest_route('api', '/user/goals', array(
        'methods' => 'PUT',
        'callback' => 'update_user_goals',
        'permission_callback' => '__return_true'
    ));
}

add_action('rest_api_init', 'register_user_goals_endpoint');
?>