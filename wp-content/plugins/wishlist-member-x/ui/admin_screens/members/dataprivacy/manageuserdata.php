<?php
    $error_message = __('(It appears you do not have the proper WordPress privilege to access this feature)', 'wishlist-member');
    $can_export    = current_user_can('export_others_personal_data');
    $can_erase     = current_user_can('erase_others_personal_data');

    $export_link = $can_export ? sprintf('a target="_parent" href="%s"', admin_url('tools.php?page=export_personal_data')) : 'span';
    $erase_link  = $can_erase ? sprintf('a target="_parent" href="%s"', admin_url('tools.php?page=remove_personal_data')) : 'span';
?>
<div class="content-wrapper">
    <div class="row">
        <div class="col-md-12">
            <p><?php esc_html_e('This functionality is handled directly by WordPress.', 'wishlist-member'); ?></p>
            <ul>
                <li>
                    <<?php echo wp_kses($export_link, []); ?> class="d-inline-block" style="width: 150px">Export Personal Data</<?php echo wp_kses(explode(' ', $export_link)[0], []); ?>>
                    <?php
                    if (! $can_export) :
                        ?>
                        <em class="d-inline-block"><?php echo esc_html($error_message); ?></em><?php
                    endif; ?>
                </li>
                <li>
                    <<?php echo wp_kses($erase_link, []); ?> class="d-inline-block" style="width: 150px">Erase Personal Data</<?php echo wp_kses(explode(' ', $erase_link)[0], []); ?>>
                    <?php
                    if (! $can_erase) :
                        ?>
                        <em class="d-inline-block"><?php echo esc_html($error_message); ?></em><?php
                    endif; ?>
                </li>
            </ul>
        </div>
    </div>
</div>

