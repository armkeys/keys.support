<?php
    $content_lvls = $this->get_content_levels('categories', $item['ID'], true, false, $immutable);

    $protection_title = [
        'lock'                => 'Protected',
        'lock_open'           => 'Unprotected',
        'inherit'             => 'Inherited',
        'inherit_unprotected' => 'Inherited',
    ];

    $protect_inherit = $this->special_content_level($item['ID'], 'Inherit', null, '~CATEGORY');
    $protection      = $this->cat_protected($item['ID']) ? 'lock' : 'lock_open';
    if ($protect_inherit) {
        if ('lock' === $protection) {
            $protection = 'inherit';
        } else {
            $protection = 'inherit_unprotected';
        }
    }
    $child_class = 'inherit' === $protection || 'inherit_unprotected' === $protection ? "child-tbody-{$item['parent']}" : '';
    ?>
<tbody class="outer-tbody content-tbody-<?php echo esc_attr($item['ID']); ?> <?php echo esc_attr($child_class); ?>">
    <tr class="content-tr content-tr-<?php echo esc_attr($item['ID']); ?>">
        <td class="text-center">
            <div class="form-check -for-tables">
                <input value="<?php echo esc_attr($item['ID']); ?>" type="checkbox" class="chk-contentid form-check-input">
                <label for="" class="form-check-label d-none"></label>
            </div>
        </td>
        <td>
            <a href="#" title="<?php echo esc_attr($protection_title[ $protection ]); ?>" parent="<?php echo esc_attr($item['parent']); ?>" class="toggle-content-protection" content_type="<?php echo esc_attr($content_type); ?>" contentids="<?php echo esc_attr($item['ID']); ?>" content_comment="<?php echo esc_attr($content_comment ? '1' : '0'); ?>">
                <i class="wlm-icons md-24"><?php echo esc_html($protection); ?></i>
            </a>
            <?php echo esc_html(str_repeat('&mdash;', $item['deep']) . ' ' . $item['name']); ?>
        </td>
        <td>
            <?php
                $protection_status = '';
            if ($protect_inherit) {
                $protection_status = $protection_title[ $protection ];
                if ('inherit' === $protection) {
                    $protection_status .= ' - Protected';
                    $prot_cat           = [];
                    if ($item['parent']) {
                        $pcat = get_category($item['parent']);
                        if ($pcat) {
                            $prot_cat[] = $pcat->name;
                        }
                    }
                    $prot_cat           = count($prot_cat) > 0 ? ' from ' . implode(', ', $prot_cat) : '';
                    $protection_status .= $prot_cat;
                } else {
                    $protection_status .= ' - Unprotected';
                }
            }
            if (! empty($protection_status)) {
                echo '<span title="' . esc_attr($protection_status) . '">' . esc_html($protection_title[ $protection ]) . '</span>';
            } else {
                echo esc_html($protection_title[ $protection ]);
            }
            ?>
        </td>
        <td class="text-center"><?php echo esc_html($item['taxonomy']); ?></td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td colspan="3">
            <?php
                $wpm_levels     = $this->get_option('wpm_levels');
                $content_mylvls = [];
            foreach ($content_lvls as $key => $value) {
                if (! isset($wpm_levels[ $key ]['allcategories']) || empty($wpm_levels[ $key ]['allcategories'])) {
                    $content_mylvls[ $key ] = wlm_trim($value);
                    unset($content_lvls[ $key ]);
                }
            }
                $levels = '';
            if (count($content_lvls)) {
                $_title = "This level has access to all {$content_type}";
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
</tbody>
