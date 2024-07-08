<?php

/**
 * Content Protection Page items
 *
 * @package Wishlist_Member
 */

if (! $content_comment) {
    $content_lvls = $that->get_content_levels($item->post_type, $item->ID, true, false, $immutable);
} else {
    $content_lvls = $that->get_content_levels('~COMMENT', $item->ID, true, false, $immutable);
}
$checkbox_check   = isset($checkbox_check) ? $checkbox_check : false; // Not used anymore, used to check the checkbox whenever something is change on the record.
$protection_title = [
    'lock'                => 'Protected',
    'lock_open'           => 'Unprotected',
    'inherit'             => 'Inherited',
    'inherit_unprotected' => 'Inherited',
];
if (! $content_comment) {
    $protect_inherit = $that->special_content_level($item->ID, 'Inherit');
    $protection      = $that->protect($item->ID) ? 'lock' : 'lock_open';
} else {
    $protect_inherit = $that->special_content_level($item->ID, 'Inherit', null, '~COMMENT');
    $protection      = $that->special_content_level($item->ID, 'Protection', null, '~COMMENT') ? 'lock' : 'lock_open';
}
if ($protect_inherit) {
    if ('lock' === $protection) {
        $protection = 'inherit';
    } else {
        $protection = 'inherit_unprotected';
    }
}

$post_categories = [];
if (! $content_comment) {
    $cats = wp_get_post_categories($item->ID);
    foreach ($cats as $c) {
        $_cat                  = get_category($c);
        $post_categories[ $c ] = $_cat->name;
    }
}

$taxonomy_names = get_taxonomies(['_builtin' => false], 'names');
array_unshift($taxonomy_names, 'category');
$taxonomies           = wp_get_object_terms($item->ID, $taxonomy_names, ['fields' => 'ids']);
$protected_taxonomies = [];
if (! is_wp_error($taxonomies) && ! empty($taxonomies)) {
    foreach ($taxonomies as $_taxonomy) {
        if ($that->cat_protected($_taxonomy)) {
            $protected_taxonomies[] = $_taxonomy;
        }
    }
}
$ancestor       = get_post_ancestors($item->ID);
$allprotection  = '~COMMENT' === $content_type ? 'allcomments' : 'dummy';
$allprotection  = 'post' === $content_type ? 'allposts' : $allprotection;
$allprotection  = 'page' === $content_type ? 'allpages' : $allprotection;
$item_post_type = '';
if (! empty($post_children[ $item->ID ]) && $content_type !== $item->post_type) {
    $item_post_type = get_post_type_object($item->post_type);
}
?>
<tbody class="outer-tbody button-hover content-tbody-<?php echo esc_attr($item->ID); ?>">
    <?php if ($is_system_page || $item_post_type) : ?>
    <tr class="content-tr content-tr-<?php echo esc_attr($item->ID); ?>"  >
        <td ></td>
        <td style="vertical-align: middle;" >
            <i class="wlm-icons md-24">lock_open</i>
            <?php echo $is_heirarchical ? esc_html(str_repeat('&mdash; ', count($ancestor))) : ''; ?>
            <div class="d-inline-block" style="max-width: calc(100% - 25px);">
                <?php echo esc_html($item->post_title); ?>
            </div>
        </td>
        <td colspan="5" class="text-center">
            <p class="text-center" style="text-align:center;">
                <?php if ($item_post_type) : ?>
                    <?php
                    printf(
                        wp_kses(
                            // Translators: 1$s is for the URL  of post type, 2$s for Post type name, 3$s for URL to the documentation.
                            __(
                                'The protection settings of this content is in <a href="%1$s">%2$s</a> menu.
								<a href="%3$s" target="_blank">Click Here for more details.</a>',
                                'wishlist-member'
                            ),
                            [
                                'a' => [
                                    'href'   => [],
                                    'target' => [],
                                ],
                            ]
                        ),
                        esc_url(admin_url('admin.php?page=WishListMember&wl=content_protection/' . $item->post_type . '/content')),
                        esc_html($item_post_type->labels->singular_name),
                        'https://wishlistmember.com/docs/pages-unprotected-by-default-not-appearing-in-content-protection-section-in-wishlist-member/'
                    );
                    ?>
                <?php else : ?>
                    <?php
                    printf(
                        wp_kses(
                            // Translators: %1$s represents the content type, %2$s represents the URL for more details.
                            __('This %1$s can not be protected as it is currently set as a WishList Member Error/Redirect Page. <a href="%2$s" target="_blank">Click Here for more details.</a>', 'wishlist-member'),
                            [
                                'a' => [
                                    'href'   => [],
                                    'target' => [],
                                ],
                            ]
                        ),
                        esc_html($item->post_type),
                        'https://wishlistmember.com/docs/pages-unprotected-by-default-not-appearing-in-content-protection-section-in-wishlist-member/'
                    );
                    ?>
                <?php endif; ?>
            </p>
        </td>
        <td class="text-center">
            <div class="btn-group-action">
                <a href="<?php echo esc_url(get_permalink($item->ID)); ?>" target="_blank" title="View Content" class="btn wlm-icons md-24 -icon-only"><span>remove_red_eye</span></a>
            </div>
        </td>
    </tr>
    <?php else : ?>
    <tr class="content-tr content-tr-<?php echo esc_attr($item->ID); ?>">
        <td class="text-center">
            <div class="form-check -for-tables">
                <input value="<?php echo esc_attr($item->ID); ?>" type="checkbox" class="chk-contentid form-check-input" title="<?php echo esc_attr($item->ID); ?>">
                <label class="form-check-label d-none" for=""></label>
            </div>
        </td>
        <td>
            <a href="#" title="<?php echo esc_attr($protection_title[ $protection ]); ?>" class="toggle-content-protection pull-left" content_type="<?php echo esc_attr($content_type); ?>" contentids="<?php echo esc_attr($item->ID); ?>" content_comment="<?php echo esc_attr($content_comment ? '1' : '0'); ?>">
                <i class="wlm-icons md-24"><?php echo esc_html($protection); ?></i>
            </a>
            <?php echo $is_heirarchical ? esc_html(str_repeat('&mdash; ', count($ancestor))) : ''; ?>
            <div class="d-inline-block" style="max-width: calc(100% - 25px);">
                <?php if ('attachment' === $content_type) : ?>
                    <?php echo esc_html($item->post_title); ?><br>
                    <em><?php echo esc_html(basename(get_attached_file($item->ID))); ?></em>
                <?php else : ?>
                    <a href="#" data-contenttype="<?php echo esc_attr($content_comment ? 'comment' : $content_type); ?>"  data-contentid="<?php echo esc_attr($item->ID); ?>" class="edit-btn">
                        <?php echo esc_html($item->post_title); ?>
                    </a>
                <?php endif; ?>
            </div>
        </td>
        <?php if (! $content_comment) : ?>
        <td>
            <?php
            $protection_status = '';
            if ($protect_inherit) {
                $protection_status = $protection_title[ $protection ];
                if ('inherit' === $protection) {
                    $protection_status .= ' - Protected';
                } else {
                    $protection_status .= ' - Unprotected';
                }

                $prot_cat = [];
                if ($protected_taxonomies) {
                    foreach ($protected_taxonomies as $_id) {
                        $t          = get_term($_id);
                        $prot_cat[] = $t->name;
                    }
                } else {
                    foreach ($ancestor as $_id) {
                        $prot_cat[] = get_the_title($_id);
                    }
                }
                $prot_cat           = count($prot_cat) > 0 ? ' from ' . implode(', ', $prot_cat) : '';
                $protection_status .= $prot_cat;
            }
            if (! empty($protection_status)) {
                echo '<span title="' . esc_attr($protection_status) . '">' . esc_html($protection_title[ $protection ]) . '</span>';
            } else {
                echo esc_html($protection_title[ $protection ]);
            }
            ?>
        </td>
        <?php endif; ?>
        <?php if (! $content_comment && 'attachment' !== $content_type) : ?>
            <td class="text-center">
                <?php
                    $ppost_status = $that->pay_per_post($item->ID) ? 'Paid' : 'Disabled';
                    $ppost_status = $that->free_pay_per_post($item->ID) ? 'Free' : $ppost_status;
                ?>
                <?php echo esc_html($ppost_status); ?>
            </td>
            <td class="text-center ppp-user-count-holder"><?php echo esc_html($that->count_post_users($item->ID, $item->post_type)); ?></td>
            <?php if ($has_categories) : ?>
                <td class="text-center">
                    <?php echo esc_html(implode(', ', $post_categories)); ?>
                </td>
            <?php endif; ?>
        <?php endif; ?>
        <?php if ('attachment' === $content_type || ! $has_categories) : ?>
            <td>
                <?php
                $content_parent = [];
                foreach ($ancestor as $_id) {
                    $content_parent[] = get_the_title($_id);
                }
                $content_parent = implode(' > ', $content_parent);
                ?>
                <?php if ($content_parent) : ?>
                    <span class="wlm-content-parent d-block text-truncate" title="<?php echo esc_attr($content_parent); ?>" style="cursor: default; max-width: 120px;">
                        <?php echo esc_html($content_parent); ?>
                    </span>
                <?php endif; ?>
            </td>
        <?php endif; ?>
        <td class="text-center"><?php echo esc_html(wlm_date(get_option('date_format'), strtotime($item->post_date))); ?></td>
        <?php if ('attachment' !== $content_type) : ?>
            <td class="text-center">
                <div class="btn-group-action">
                    <a href="<?php echo esc_url(get_permalink($item->ID)); ?>" target="_blank" title="View Content" class="btn wlm-icons md-24 -icon-only"><span>remove_red_eye</span></a>
                    <a href="#" data-contenttype="<?php echo esc_attr($content_comment ? 'comment' : $content_type); ?>"  data-contentid="<?php echo esc_attr($item->ID); ?>" class="btn wlm-icons md-24 -icon-only edit-btn"><span><?php esc_html_e('edit', 'wishlist-member'); ?></span></a>
                </div>
            </td>
        <?php endif; ?>
    </tr>
    <tr>
        <?php
        $colspan = 7;
        $colspan = $content_comment ? 3 : $colspan;
        $colspan = 'attachment' === $content_type ? 4 : $colspan;
        ?>
        <td>&nbsp;</td>
        <td colspan="<?php echo esc_attr($colspan); ?>">
            <?php
            $wpm_levels     = $that->get_option('wpm_levels');
            $content_mylvls = [];
            foreach ($content_lvls as $key => $value) {
                if (! isset($wpm_levels[ $key ][ $allprotection ]) || empty($wpm_levels[ $key ][ $allprotection ])) {
                    $content_mylvls[ $key ] = wlm_trim($value);
                    unset($content_lvls[ $key ]);
                }
            }
            $levels = '';
            if (count($content_lvls)) {
                $ct     = $content_comment ? 'comment' : $content_type;
                $_title = "This level has access to all {$ct}s";
                $levels = "<em class='text-muted' title='{$_title}' style='cursor: default;'>" . implode("</em>, <em class='text-muted' title='{$_title}' style='cursor: default;'>", $content_lvls) . '</em>';
            }
            if (count($content_mylvls)) {
                $levels = ! empty($levels) ? $levels . ', ' : $levels;
                if ('lock_open' === $protection) {
                    $levels .= '<span>' . implode('</span>, <span>', $content_mylvls) . '</span>';
                } else {
                    $levels .= implode(', ', $content_mylvls);
                }
            }
            $levels = empty($levels) ? "<span class='text-muted'>(No Membership Levels Assigned)</span>" : $levels;
            ?>
            <div class="overflow-ellipsis" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <span class="wlm-icons text-muted -o-3" title="Membership Levels" style="cursor: default;">levels_icon</span>
                <?php 'lock_open' === $protection || 'inherit_unprotected' === $protection ? 'text-decoration: line-through;' : ''; ?>
                <span style="vertical-align: middle;" >
                    <?php echo wp_kses_post($levels); ?>
                </span>
            </div>
        </td>
    </tr>
    <?php endif; ?>
</tbody>
