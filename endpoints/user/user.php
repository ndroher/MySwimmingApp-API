<?php
// Definir metas padrão quando um novo usuário é registrado
function set_default_training_goals($user_id) {
    // Definir valores padrão
    $default_weekly_goal = 3; // Meta semanal padrão
    $default_monthly_goal = 12; // Meta mensal padrão
    $default_yearly_goal = 144; // Meta anual padrão

    // Verifica se os meta fields já existem e, caso não, adiciona o valor padrão
    if (!get_user_meta($user_id, 'weekly_goal', true)) {
        update_user_meta($user_id, 'weekly_goal', $default_weekly_goal);
    }
    if (!get_user_meta($user_id, 'monthly_goal', true)) {
        update_user_meta($user_id, 'monthly_goal', $default_monthly_goal);
    }
    if (!get_user_meta($user_id, 'yearly_goal', true)) {
        update_user_meta($user_id, 'yearly_goal', $default_yearly_goal);
    }
}
add_action('user_register', 'set_default_training_goals');
?>