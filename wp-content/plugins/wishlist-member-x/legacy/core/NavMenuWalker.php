<?php

class WishListMember_Walker_Nav_Menu extends Walker_Nav_Menu_Edit
{
    public function start_el(&$output, $item, $depth = 0, $args = [], $id = 0)
    {
        $item_output = '';
        $output     .= parent::start_el($item_output, $item, $depth, $args, $id);
        $output     .= preg_replace(
            '/(?=<fieldset[^>]+class="[^"]*field-move)/',
            $this->fetch_custom_fields($item, $depth, $args),
            $item_output
        );
    }
    protected function fetch_custom_fields($item, $depth, $args = [], $id = 0)
    {
        ob_start();
        $item_id = intval($item->ID);
        do_action('wp_nav_menu_item_custom_fields', $item_id, $item, $depth, $args);
        return ob_get_clean();
    }
}
