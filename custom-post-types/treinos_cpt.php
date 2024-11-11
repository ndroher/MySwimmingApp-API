<?php
// Registro do CPT de Treinos
function register_treinos_cpt() {
    $labels = array(
        'name' => 'Treinos',
        'singular_name' => 'Treino',
        'add_new' => 'Adicionar Novo',
        'add_new_item' => 'Adicionar Novo Treino',
        'edit_item' => 'Editar Treino',
        'new_item' => 'Novo Treino',
        'view_item' => 'Ver Treino',
        'search_items' => 'Pesquisar Treinos',
        'not_found' => 'Nenhum treino encontrado',
        'not_found_in_trash' => 'Nenhum treino encontrado na lixeira'
    );

    $args = array(
        'labels' => $labels,
        'public' => true,
        'has_archive' => true,
        'show_in_rest' => true,
        'supports' => array('title', 'editor', 'custom-fields'),
        'menu_position' => 5,
        'rewrite' => array('slug' => 'treinos'),
    );

    register_post_type('treinos', $args);
}

add_action('init', 'register_treinos_cpt');

// CUSTOM FIELDS
function add_treinos_custom_fields() {
    register_post_meta('treinos', 'tamanho_da_piscina', array(
        'show_in_rest' => true,
        'type' => 'integer',
        'single' => true,
    ));

    register_post_meta('treinos', 'distancia_total', array(
        'show_in_rest' => true,
        'type' => 'integer',
        'single' => true,
    ));

    register_post_meta('treinos', 'repeticoes_por_tipo_de_nado', array(
        'show_in_rest' => true,
        'type' => 'object',
        'single' => true,
    ));

    register_post_meta('treinos', 'equipamentos_utilizados', array(
        'show_in_rest' => true,
        'type' => 'object',
        'single' => true,
    ));

    register_post_meta('treinos', 'exercicios_realizados', array(
        'show_in_rest' => true,
        'type' => 'array',
        'single' => true,
        'items' => array(
            'type' => 'object',
            'properties' => array(
                'exercicio_id' => array(
                    'type' => 'integer',
                ),
                'repeticoes' => array(
                    'type' => 'integer',
                ),
            ),
        ),
    ));
}

add_action('init', 'add_treinos_custom_fields');
?>