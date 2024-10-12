<?php
// Registro do CPT de Exercícios
function register_exercicios_cpt() {
    $labels = array(
        'name' => 'Exercícios',
        'singular_name' => 'Exercício',
        'add_new' => 'Adicionar Novo',
        'add_new_item' => 'Adicionar Novo Exercício',
        'edit_item' => 'Editar Exercício',
        'new_item' => 'Novo Exercício',
        'view_item' => 'Ver Exercício',
        'search_items' => 'Pesquisar Exercícios',
        'not_found' => 'Nenhum exercício encontrado',
        'not_found_in_trash' => 'Nenhum exercício encontrado na lixeira'
    );

    $args = array(
        'labels' => $labels,
        'public' => true,
        'has_archive' => true,
        'show_in_rest' => true,
        'supports' => array('title', 'editor', 'custom-fields'),
        'menu_position' => 5,
        'rewrite' => array('slug' => 'exercicios'),
    );

    register_post_type('exercicios', $args);
}

add_action('init', 'register_exercicios_cpt');

//TAXONOMY

// Registro da Taxonomia de Tipo de Nado
function register_tipo_nado_taxonomy() {
    $labels = array(
        'name' => 'Tipos de Nado',
        'singular_name' => 'Tipo de Nado',
        'search_items' => 'Procurar Tipos de Nado',
        'all_items' => 'Todos os Tipos de Nado',
        'edit_item' => 'Editar Tipo de Nado',
        'update_item' => 'Atualizar Tipo de Nado',
        'add_new_item' => 'Adicionar Novo Tipo de Nado',
        'new_item_name' => 'Novo Tipo de Nado',
    );

    $args = array(
        'labels' => $labels,
        'public' => true,
        'hierarchical' => false,
        'show_in_rest' => true,
    );

    register_taxonomy('tipo_nado', array('exercicios'), $args);
}

add_action('init', 'register_tipo_nado_taxonomy');

// Registro da Taxonomia de Equipamentos
function register_equipamentos_taxonomy() {
    $labels = array(
        'name' => 'Equipamentos',
        'singular_name' => 'Equipamento',
        'search_items' => 'Procurar Equipamentos',
        'all_items' => 'Todos os Equipamentos',
        'edit_item' => 'Editar Equipamento',
        'update_item' => 'Atualizar Equipamento',
        'add_new_item' => 'Adicionar Novo Equipamento',
        'new_item_name' => 'Novo Equipamento',
    );

    $args = array(
        'labels' => $labels,
        'public' => true,
        'hierarchical' => false,
        'show_in_rest' => true,
    );

    register_taxonomy('equipamentos', array('exercicios'), $args);
}

add_action('init', 'register_equipamentos_taxonomy');

//CUSTOM FIELDS
function add_exercicios_custom_fields() {
    register_post_meta('exercicios', 'personalizado', array(
        'show_in_rest' => true,
        'type' => 'boolean',
        'single' => true,
    ));
}

add_action('init', 'add_exercicios_custom_fields');

?>