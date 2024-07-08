<div class="modal-box" id="wlm-ppp-modal" style="display: none">
    <div>
        <br>

        <!-- Filter Form -->
        <form onsubmit="return false;" method="GET">
            <div class="wlm-search-box-container clearfix">
                <div class="wlm-search-box wlm-search-box-1">
                    <select style="width: 160px" class="form-control wlm-select" name="search_by" id="wlm_user_search_by">
                        <option value="by_user"><?php esc_html_e('Search by User', 'wishlist-member'); ?></option>
                        <option value="by_level"><?php esc_html_e('Search by Level', 'wishlist-member'); ?></option>
                    </select><select style="width: 130px" class="form-control wlm-select" name="user_access" id="wlm_user_access">
                        <option value="all"><?php esc_html_e('All Users', 'wishlist-member'); ?></option>
                        <option value="yes"><?php esc_html_e('Has Access', 'wishlist-member'); ?></option>
                        <option value="no"><?php esc_html_e('No Access', 'wishlist-member'); ?></option>
                    </select>
                </div>
                <div class="wlm-search-box wlm-search-box-2">
                    <span id="wlm_search_by_user" class="wlm_search_types_field">
                        <input style="line-height: 18px" class="form-control" type="search" id="wlm_user_search_input" placeholder="Name, Username, Email" value="" />
                    </span>
                    <span id="wlm_search_by_level" style="display:none" class="wlm_search_types_field">
                        <select style="width: 100%" class="form-control wlm-select" id="wlm_level_search_input" name="search_level" multiple="multiple">
                            <?php foreach ($wpm_levels as $level_id => $level) : ?>
                            <option value="<?php echo esc_attr($level_id); ?>"><?php echo esc_html($level['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </span>
                </div>
                <div class="wlm-search-box wlm-search-box-3">
                    <input id="wlm3-ppp-search-button" class="wlm-btn" type="button" class="button-secondary" value="<?php esc_attr_e('Search', 'wishlist-member'); ?>" />
                </div>
            </div>
        </form>

        <!-- Users Table -->

        <div id="wlm3-pagination" class="pagination pull-right -action-leveled" style="display: none">
            <input type="hidden" id="wlm3-pagination-page" value="1">
            <input type="hidden" id="wlm3-pagination-number" value="10">
            <div class="count pull-left">
                <div role="presentation" class="dropdown page-rows">
                    <span class="dropdown-toggle">
                        <a href="#" class="" id="wlm3-drop-page" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                            <span id="wlm3-pagination-from">1</span> - <span id="wlm3-pagination-to">10</span>
                        </a>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="#_10">10</a>
                            <a class="dropdown-item" href="#_25">25</a>
                            <a class="dropdown-item" href="#_50">50</a>
                            <a class="dropdown-item" href="#_100">100</a>
                            <a class="dropdown-item" href="#_250">250</a>
                            <a class="dropdown-item" href="#_500">500</a>
                        </div>
                    </span>
                    <?php esc_html_e('of', 'wishlist-member'); ?> <span class="wlm3-pagination-total"></span>

                    <a href="#_wlm3-ppp-prev" class="wlm-icon-arrow">
                        <img style="color: #fff" src="<?php echo esc_url($this->plugin_url3); ?>/ui/images/baseline-keyboard_arrow_left-24px.svg" alt="">                     
                    </a>
                    <a href="#_wlm3-ppp-next" class="wlm-icon-arrow">
                        <img style="color: #fff" src="<?php echo esc_url($this->plugin_url3); ?>/ui/images/baseline-keyboard_arrow_right-24px.svg" alt="">                        
                    </a>
                </div>
            </div>
        </div>
        <table class="widefat" id="wlm_payperpost_table">
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
