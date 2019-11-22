<?php

// add custom menu item
add_filter( 'gform_export_menu', 'my_custom_export_menu_item' );
function my_custom_export_menu_item( $menu_items ) {

    $menu_items[] = array(
        'name' => 'publipostage_export_page',
        'label' => __( 'Publipostage au format PDF' )
        );

    return $menu_items;
}