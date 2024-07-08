<?php

/**
 * Default Pay Per Post setting values.
 *
 * @package WishListMember/Helpers
 */

$ppp_defaults = [
    'custom_afterreg_redirect' => null,
    'afterreg_redirect_type'   => 'message',
    'afterreg_message'         => $this->page_templates['ppp_after_registration_internal'],
    'afterreg_page'            => null,
    'afterreg_url'             => null,

    'custom_login_redirect'    => null,
    'login_redirect_type'      => 'message',
    'login_message'            => $this->page_templates['after_login_internal'],
    'login_page'               => null,
    'login_url'                => null,
];
