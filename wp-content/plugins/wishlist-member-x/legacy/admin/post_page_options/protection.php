<?php

/**
 * Post/Page Options Protection
 *
 * @package WishListMember/Legacy/PostPageOptions
 */

if (! $post) {
    return;
}
?>
<div class="wlm-inside wlm-inside01">
    <!-- Content Protection Toggle -->
    <div class="form-group">
        <div class="switch-toggle switch-toggle-wlm -semi-compressed">
            <input id="protection-settings-unprotected" name="protection_settings" type="radio" value="0" <?php if (0 === (int) $protection_settings) {
                echo 'checked';
                                                                                                          } ?>>
            <label for="protection-settings-unprotected" onclick=""><?php esc_html_e('Unprotected', 'wishlist-member'); ?></label>
            <input id="protection-settings-protected" name="protection_settings" type="radio" value="1"
            <?php
            if (1 === (int) $protection_settings) {
                echo 'checked';
            }
            ?>
            >
            <label for="protection-settings-protected" onclick=""><?php esc_html_e('Protected', 'wishlist-member'); ?></label>
            <input id="protection-settings-inherited" name="protection_settings" type="radio" value="2"
            <?php
            if (2 === (int) $protection_settings) {
                echo 'checked';
            }
            ?>
            >
            <label for="protection-settings-inherited" onclick=""><?php esc_html_e('Inherited', 'wishlist-member'); ?></label>
            <a href="" class="btn btn-primary"></a>
        </div>
    </div>
    <input type="hidden" name="wpm_protect" value="">
    <input type="hidden" name="wlm_inherit_protection" value="">
    <div id="wpm-access-options">
        <hr>
        <!-- Membership Levels -->
        <?php if (count($wpm_levels)) : ?>
        <h2 class="wlm-h2"><?php esc_html_e('Access', 'wishlist-member'); ?></h2>
            <?php
            $all_access = [];
            $options    = '';
            foreach ((array) $wpm_levels as $level_id => $level) {
                if (wlm_arrval($level, $allindex)) {
                    $all_access[] = $level['name'];
                    continue;
                }
                $options .= sprintf('<option value="%s" %s>%s</option>', esc_attr($level_id), in_array($level_id, $wpm_access) ? 'selected' : '', esc_html($level['name']));
            }
            if ($all_access) {
                printf(
                    '<p>%s <strong><em>%s</em></strong></p><br>',
                    esc_html(
                        sprintf(
                            // Translators: %s: posts / pages.
                            _n(
                                'Level with access to all %s:',
                                'Levels with access to all %s:',
                                count($all_access),
                                'wishlist-member'
                            ),
                            'allposts' === $allindex ? 'posts' : 'pages'
                        )
                    ),
                    esc_html(
                        implode(', ', $all_access)
                    )
                );
            }
            ?>
        <div class="form-group" id="wpm-access-form">
            <p class="float-left" for=""><?php esc_html_e('Select the membership level(s) that can access this content:', 'wishlist-member'); ?></p>
            <a class="float-right -text-light" id="select-all-levels" href="#"><?php esc_html_e('Select All', 'wishlist-member'); ?></a>
            <a class="float-right -text-light" id="clear-all-levels" href="#" style="display: none;"><?php esc_html_e('Clear All', 'wishlist-member'); ?></a>
            <div style="clear: both">
                <select name="wpm_access[]" id="" class="form-control wlm-select" style="width: 100%" multiple="multiple">
                    <?php
                    echo wp_kses(
                        $options,
                        [
                            'option' => [
                                'value' => true,
                                'selected' => true,
                            ],
                        ]
                    );
                    ?>
                </select>
            </div>
            <br>
            <div class="form-group">
                <?php
                    $_post_type_object = get_post_type_object($post->post_type);
                    $post_type_labels  = get_post_type_labels($_post_type_object);
                    $parent_name       = $post_type_labels->singular_name;
                    /**
                     * Filters the name of the child post
                     *
                     * @param string name of the child
                     * @param object post type object
                     */
                    $child_name              = apply_filters('wishlistmember_post_inheritance_child_name', 'Content', $_post_type_object);
                    $pass_content_protection = wishlistmember_instance()->special_content_level($post->ID, 'Pass_Content_Protection');
                ?>
                <p><?php /* translators: 1: child name, 2: parent name */ printf(esc_html__('Automatically apply Protection Settings to new %1$s under this %2$s', 'wishlist-member'), esc_html($child_name), esc_html($parent_name)); ?></p>
                <div class="switch-toggle switch-toggle-wlm" style="width:140px">
                    <input id="pass_content_protection_yes" name="pass_content_protection" type="radio" value="Y" <?php echo $pass_content_protection ? '' : 'checked'; ?>>
                    <label for="pass_content_protection_yes" onclick=""><?php esc_html_e('Yes', 'wishlist-member'); ?></label>
                    <input id="pass_content_protection_no" name="pass_content_protection" type="radio" value="N" <?php echo $pass_content_protection ? '' : 'checked'; ?>>
                    <label for="pass_content_protection_no" onclick=""><?php esc_html_e('No', 'wishlist-member'); ?></label>
                    <a href="" class="btn btn-primary"></a>
                </div>
            </div>
            <div id="pass-content-protection">
                <br>
                <p><?php /* translators: 1: child name, 2: parent name */ printf(esc_html__('Apply Protection Settings to %1$s under this %2$s', 'wishlist-member'), esc_html($child_name), esc_html($parent_name)); ?></p>
                <a href="#" id="pass-protection-to-existing" class="wlm-btn -with-icons">
                    <i class="wlm-icons"><img src="<?php echo esc_url($this->plugin_url3); ?>/ui/images/settings.svg" alt=""></i>
                    <span><?php esc_html_e('Protect Existing Now', 'wishlist-member'); ?></span>
                </a>
                <span class="notif1" style="display:none; padding-left: 1em;">Applying protection...</span>
                <span class="notif2 wlm-saved" style="display:none; padding-left: 1em;">Applied</span>
                <div id="pass-protection-confirmation" class="wlm-grey-box" style="display: none">
                    <p>
                        <?php /* translators: 1: child name, 2: parent name */ printf(esc_html__('Are you sure you want to apply Protection Settings to all existing %1$s under this %2$s?', 'wishlist-member'), esc_html($child_name), esc_html($parent_name)); ?>
                        &nbsp;
                        <a href="#" id="pass-protection-to-existing-confirm" data-postid="<?php echo esc_attr($post->ID); ?>" class="wlm-btn">
                            <span><?php esc_html_e('Yes', 'wishlist-member'); ?></span>
                        </a>
                        <a href="#" id="pass-protection-to-existing-cancel" class="wlm-btn -default">
                            <span><?php esc_html_e('No', 'wishlist-member'); ?></span>
                        </a>
                    </p>
                </div>
            </div>
        </div>
        <div id="wpm-access-inherited">
            <?php if ($protected_taxonomies || $ancestor) : ?>
                <p>
                    <?php
                    esc_html_e('Inherited From:', 'wishlist-member');
                    $titles = [];
                    if ($protected_taxonomies) {
                        foreach ($protected_taxonomies as $protected_taxonomy) {
                            $t        = get_term($protected_taxonomy);
                            $titles[] = $t->name;
                        }
                    } else {
                        foreach ($ancestor as $ancestor_id) {
                            $titles[] = get_the_title($ancestor_id);
                        }
                    }
                    echo esc_html(' ' . implode(', ', $titles));
                    ?>
                </p>
                <p><?php esc_html_e('Inherited Status:', 'wishlist-member'); ?> <?php echo esc_html($parent_protect ? __('Protected', 'wishlist-member') : __('Unprotected', 'wishlist-member')); ?></p>
                <?php if ($parent_protect) : ?>
                    <p>
                        <?php
                        $inherited_levels = [];
                        foreach ((array) $parent_levels as $parent_level_id) {
                            if (isset($wpm_levels[$parent_level_id][$allindex]) && $wpm_levels[$parent_level_id][$allindex]) {
                                continue;
                            }
                            if (empty($wpm_levels[$parent_level_id])) {
                                continue;
                            }
                            $inherited_levels[] = $wpm_levels[$parent_level_id]['name'];
                        }
                        $inherited_levels = array_unique($inherited_levels);
                        echo esc_html(_n('Inherited Level:', 'Inherited Levels:', count($inherited_levels), 'wishlist-member'));
                        echo ' ';
                        echo esc_html(count($inherited_levels) ? implode(', ', $inherited_levels) : __('None', 'wishlist-member'));
                        ?>
                    </p>
                <?php endif; ?>
            <?php else : ?>
                <p><?php esc_html_e('No parent to inherit protection from.', 'wishlist-member'); ?></p>
            <?php endif; ?>
        </div>
        <?php else : ?>
            <p><?php esc_html_e('No membership levels found', 'wishlist-member'); ?></p>
        <?php endif; ?>
    </div>
    <br>
    <hr>
    <div style="text-align: right;">
        <div class="wlm-saved" style="display: none"><?php esc_html_e('Saved', 'wishlist-member'); ?></div>
        <div class="wlm-saving" style="display: none"><?php esc_html_e('Saving...', 'wishlist-member'); ?></div>
        <a href="#" class="wlm-btn -with-icons -success -centered-span wlm-postpage-apply">
            <i class="wlm-icons"><img src="<?php echo esc_url($this->plugin_url3); ?>/ui/images/baseline-save-24px.svg" alt=""></i>
            <span><?php esc_html_e('Apply Settings', 'wishlist-member'); ?></span>
        </a>
    </div>

</div>
