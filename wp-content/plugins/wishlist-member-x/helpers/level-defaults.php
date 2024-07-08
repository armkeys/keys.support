<?php

/**
 * Default level setting values.
 *
 * @package WishListMember/Helpers
 */

$level_defaults = [
    'id'                                => time(),
    'addToLevel'                        => null,
    'afterregredirect'                  => null, // defunct.
    'allcategories'                     => null,
    'allcomments'                       => null,
    'allpages'                          => null,
    'allposts'                          => null,
    'calendar'                          => 'Days',
    'disableexistinglink'               => 0,
    'disableprefilledinfo'              => null,
    'expire'                            => 7,
    'inheritparent'                     => null,
    'isfree'                            => null,
    'levelOrder'                        => time(),
    'loginredirect'                     => null, // defunct.
    'name'                              => null,
    'registrationdatereset'             => null,
    'registrationdateresetactive'       => null,
    'removeFromLevel'                   => null,
    'requireadminapproval'              => null,
    'requireadminapproval_integrations' => null,
    'requirecaptcha'                    => null,
    'requireemailconfirmation'          => null,
    'role'                              => 'subscriber',
    'salespage'                         => null,
    'uncancelonregistration'            => null,
    'url'                               => $this->pass_gen(10),
    'allow_free_reg'                    => 1,
    'enable_custom_reg_form'            => null,
    'enable_salespage'                  => null,
    'enable_tos'                        => null,
    'expire_date'                       => null,
    'expire_option'                     => null,
    'tos'                               => 'I agree to the following terms and conditions.',
    'count'                             => null,
    'upgradeAfter'                      => null,
    'upgradeAfterPeriod'                => null,
    'upgradeMethod'                     => null,
    'upgradeOnDate'                     => null,
    'upgradeSchedule'                   => null,
    'upgradeTo'                         => null,
    'enable_header_footer'              => null,
    'regform_before'                    => null,
    'regform_after'                     => null,

    'custom_reg_form'                   => null,

    'custom_afterreg_redirect'          => null,
    'afterreg_redirect_type'            => 'message',
    'afterreg_message'                  => $this->page_templates['after_registration_internal'],
    'afterreg_page'                     => null,
    'afterreg_url'                      => null,

    'custom_login_redirect'             => null,
    'login_redirect_type'               => 'message',
    'login_message'                     => $this->page_templates['after_login_internal'],
    'login_page'                        => null,
    'login_url'                         => null,

    'custom_logout_redirect'            => null,
    'logout_redirect_type'              => 'message',
    'logout_message'                    => $this->page_templates['after_logout_internal'],
    'logout_page'                       => null,
    'logout_url'                        => null,

    // Auto-creation of accounts for integrations.
    'autocreate_account_enable'         => 0,
    'autocreate_account_username'       => '{email}',
    'autocreate_account_enable_delay'   => 0,
    'autocreate_account_delay'          => 15,
    'autocreate_account_delay_type'     => 1,
    'autoadd_other_registrations'       => 0,
];
