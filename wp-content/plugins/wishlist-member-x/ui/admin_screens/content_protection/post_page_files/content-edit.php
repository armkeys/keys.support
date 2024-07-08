<?php
$wpm_levels = $this->get_option('wpm_levels');

if ('comment' !== $data['type']) {
    $protect_inherit = $this->special_content_level($data['id'], 'Inherit');
    $protection      = $this->protect($data['id']) ? 'Protected' : 'Unprotected';
} else {
    $protect_inherit = false;
    $protection      = $this->special_content_level($data['id'], 'Protection', null, '~COMMENT') ? 'Protected' : 'Unprotected';
}
$protection = $protect_inherit ? 'Inherited' : $protection;

$content_type = 'comment' === $data['type'] ? '~COMMENT' : $data['type'];
$content_lvls = $this->get_content_levels($content_type, $data['id'], true, false, $immutable);
$content_lvls = array_keys($content_lvls);

$ppost_status = $this->pay_per_post($data['id']) ? 'Paid' : 'Disabled';
$ppost_status = $this->free_pay_per_post($data['id']) ? 'Free' : $ppost_status;

$ppp_users = $this->get_post_users($content_type, $data['id']);

$protection_items = ['Unprotected', 'Protected'];
$ppp_access_items = ['Disabled', 'Free', 'Paid'];
if ('~COMMENT' !== $content_type) {
    $protection_items[] = 'Inherited';
}

$allprotection = '~COMMENT' === $content_type ? 'allcomments' : 'dummy';
$allprotection = 'post' === $content_type ? 'allposts' : $allprotection;
$allprotection = 'page' === $content_type ? 'allpages' : $allprotection;
?>
<?php if ('~COMMENT' !== $content_type) : ?>
<ul class="nav nav-tabs" role="tablist">
    <li role="presentation" class="nav-item">
        <a class="active edit-tab nav-link" href="#protection" aria-controls="protection" role="tab" data-toggle="tab"><?php esc_html_e('Protection', 'wishlist-member'); ?></a>
    </li>
    <li role="presentation" class="nav-item">
        <a class="edit-tab nav-link" href="#ppp" aria-controls="ppp" role="tab" data-toggle="tab"><?php esc_html_e('Pay Per Post Users', 'wishlist-member'); ?></a>
    </li>
</ul>
<div class="tab-content">
<?php endif; ?>
    <div role="tabpanel" class="tab-pane active" id="protection">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="">Protection Status</label>
                    <select class="form-control wlm-levels wlm-protection" name="protection" style="width: 100%" required>
                        <?php foreach ($protection_items as $value) : ?>
                            <?php $selected = $protection == $value ? 'selected' : ''; ?>
                            <option value="<?php echo esc_attr($value); ?>" <?php echo esc_attr($selected); ?>><?php echo esc_html($value); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <?php if ('~COMMENT' !== $content_type) : ?>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="">Pay Per Post Access</label>
                        <select class="form-control wlm-levels wlm-useraccess" name="useraccess" style="width: 100%" required>
                            <?php foreach ($ppp_access_items as $value) : ?>
                                <?php $selected = $ppost_status == $value ? 'selected' : ''; ?>
                                <option value="<?php echo esc_attr($value); ?>" <?php echo esc_attr($selected); ?>><?php echo esc_html($value); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <div class="row">
            <div class="col-md-12">
                <?php if ('Protected' === $protection) : ?>
                    <div class="row">
                        <div class="col-md-8 col-sm-8 col-xs-8">
                            <div class="form-group no-margin membership-level-select">
                                <label for=""><?php esc_html_e('Add Membership Level', 'wishlist-member'); ?></label>
                                <select class="form-control wlm-levels add-wlm-levels" multiple="multiple" style="width: 100%">
                                    <?php foreach ($wpm_levels as $key => $value) : ?>
                                        <?php $disabled = $value[ $allprotection ] ? 'disabled' : ''; ?>
                                        <?php $disabled = in_array($key, $content_lvls) ? 'disabled' : ''; ?>
                                        <option value="<?php echo esc_attr($key); ?>" <?php echo esc_attr($disabled); ?>><?php echo esc_html($value['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-4 col-xs-4" style="margin-top: 29px;">
                            <div class="form-group no-margin">
                                <a href="#" class="btn -primary -condensed add-contentlvl-btn" user-id="<?php echo esc_attr($profileuser->ID); ?>">
                                    <i class="wlm-icons">add</i>
                                    <span class="text"><?php esc_html_e('Add Level', 'wishlist-member'); ?></span>
                                </a>
                            </div>
                        </div>
                    </div>
                    <br />
                <?php endif; ?>
                 <div class="table-wrapper table-responsive">
                    <table class="table table-condensed table-fixed">
                        <thead>
                             <tr class="d-flex">
                                 <th class="col-10"><?php esc_html_e('Membership Levels', 'wishlist-member'); ?></th>
                                 <th class="col-2">&nbsp;</th>
                             </tr>
                        </thead>
                        <tbody class="contentlevel-holder" style="max-height: 200px">
                            <?php if (count($content_lvls) > 0) : ?>
                                <?php foreach ($content_lvls as $value) : ?>
                                    <?php $allprot = ( ! isset($wpm_levels[ $value ][ $allprotection ]) || empty($wpm_levels[ $value ][ $allprotection ]) ) ? false : true; ?>
                                    <tr title='<?php echo $allprot ? sprintf(/* Translators: %s Content type */ esc_html__('This level has access to all %ss', 'wishlist-member'), esc_html($content_type)) : ''; ?>' class="button-hover d-flex">
                                         <td class="col-10 <?php echo esc_attr($allprot ? 'text-muted' : ''); ?>"><?php echo esc_html($wpm_levels[ $value ]['name']); ?></td>
                                         <td class="col-2">
                                            <?php if (! $allprot && 'Inherited' !== $protection) : ?>
                                                <div class="btn-group-action pull-right">
                                                    <a href="#" level-id="<?php echo esc_attr($value); ?>" class="btn remove-contentlvl-btn -del-btn"><span class="wlm-icons md-24 -icon-only">delete</span></a>
                                                </div>
                                            <?php else : ?>
                                                <?php
                                                    $msg = __('This level is inherited and cannot be removed', 'wishlist-member');
                                                if ($allprot) {
                                                    $ct  = '~COMMENT' === $content_type ? 'comment' : $content_type;
                                                    $msg = "This level has access to all {$ct}s and cannot be removed";
                                                }
                                                ?>
                                                <div class="btn-group-action pull-right">
                                                    <a href="#" class="btn -icon-only -no-delete -disabled" data-placement="left" title="" data-original-title="<?php echo esc_attr($msg); ?>">
                                                        <i class="wlm-icons md-24 -icon-only">delete</i>
                                                    </a>
                                                </div>
                                            <?php endif; ?>
                                         </td>
                                     </tr>
                                <?php endforeach; ?>
                            <?php else : ?>
                                 <tr class="tr-none"><td class="text-center col-12" colspan="2">-None-</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
<?php if ('~COMMENT' !== $content_type) : ?>
    <div role="tabpanel" class="tab-pane" id="ppp">

        <div class="row">
            <template class="wlm3-form-group">
                {
                    type : 'select',
                    style : 'width: 100%;',
                    options : [
                        { value : 'by_user', text : '<?php esc_attr_e('Search by User', 'wishlist-member'); ?>' },
                        { value : 'by_level', text : '<?php esc_attr_e('Search by Level', 'wishlist-member'); ?>' },
                    ],
                    column : 'col-md-3 pr-0',
                    id : 'wlm_user_search_by',
                }
            </template>
            <template class="wlm3-form-group">
                {
                    type : 'select',
                    style : 'width: 100%;',
                    options : [
                        { value : 'all', text : '<?php esc_attr_e('All Users', 'wishlist-member'); ?>' },
                        { value : 'yes', text : '<?php esc_attr_e('Has Access', 'wishlist-member'); ?>' },
                        { value : 'no', text : '<?php esc_attr_e('No Access', 'wishlist-member'); ?>' },
                    ],
                    column : 'col-md-2 pr-0 pl-0',
                    id : 'wlm_user_access',
                }
            </template>
            <template class="wlm3-form-group">
                {
                    type : 'text',
                    column : 'col-md-5 pr-0 pl-0',
                    placeholder : '<?php echo esc_js(__('Name, Username, Email', 'wishlist-member')); ?>',
                    id : 'wlm_user_search_input',
                }
            </template>
            <template class="wlm3-form-group">
                {
                    type : 'select',
                    style : 'width: 100%;',
                    multiple : 'multiple',
                    options :
                    <?php
                            $x = [];
                    foreach ($wpm_levels as $_id => $level) {
                        $x[] = [
                            'value' => $_id,
                            'text'  => $level['name'],
                        ];
                    }
                            echo json_encode($x);
                    ?>
                        ,
                    column : 'col-md-5 d-none pr-0 pl-0',
                    id : 'wlm_level_search_input',
                }
            </template>
            <div class="col-md-2">
                <button style="width: 100%" id="wlm3-ppp-search-button" class="btn -primary -condensed"><?php esc_html_e('Search', 'wishlist-member'); ?></button>
            </div>
            <div class="d-none" id="toggle-markup">
                <template class="wlm3-form-group">
                    {
                        name  : '_toggle_name_',
                        value : '1',
                        _toggle_checked_ : '',
                        type  : 'toggle-switch',
                    }
                </template>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="pagination pull-right -action-leveled" id="wlm3-pagination" style="display: none;"">
                    <div class="count pull-left">
                        <div role="presentation" class="dropdown page-rows">
                            <a href="#" class="dropdown-toggle" id="drop-page" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><span id="wlm3-pagination-from">1</span> - <span id="wlm3-pagination-to">10</span></a> <?php esc_html_e('of', 'wishlist-member'); ?> <span id="wlm3-pagination-total"></span>

                            <ul class="dropdown-menu" id="menu1" aria-labelledby="drop-page">
                                <a class="dropdown-item" href="#_10">10</a>
                                <a class="dropdown-item" href="#_25">25</a>
                                <a class="dropdown-item" href="#_50">50</a>
                                <a class="dropdown-item" href="#_100">100</a>
                                <a class="dropdown-item" href="#_250">250</a>
                                <a class="dropdown-item" href="#_500">500</a>
                            </ul>
                        </div>
                    </div>
                    <div class="arrows pull-right">
                        <a href="#_wlm3-ppp-prev" class="wlm-icons">keyboard_arrow_left</a>
                        <a href="#_wlm3-ppp-next" class="wlm-icons">keyboard_arrow_right</a>
                    </div>
                </div>
                <div class="table-wrapper table-responsive">
                    <table class="table table-condensed" id="wlm_payperpost_table">
                        <colgroup>
                            <col width="70">
                            <col width="30%">
                            <col>
                            <col width="30%">
                            <col width="90">
                        </colgroup>
                        <thead>
                            <tr>
                                <th><?php esc_html_e('ID', 'wishlist-member'); ?></th>
                                <th><?php esc_html_e('Name', 'wishlist-member'); ?></th>
                                <th style="width: 120px"><?php esc_html_e('Username', 'wishlist-member'); ?></th>
                                <th><?php esc_html_e('Email', 'wishlist-member'); ?></th>
                                <th style="text-align: center"><?php esc_html_e('Access', 'wishlist-member'); ?></th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
        <input type="hidden" name="ppp_content_id" value="<?php echo esc_attr($data['id']); ?>" />
        <input type="hidden" id="wlm3-pagination-page" value="1">
        <input type="hidden" id="wlm3-pagination-number" value="10">
    </div>
</div>
<?php endif; ?>
<style type="text/css">
#wlm_payperpost_table tbody td {
  overflow: hidden;
  white-space: nowrap;
  text-overflow: ellipsis;
}
</style>
