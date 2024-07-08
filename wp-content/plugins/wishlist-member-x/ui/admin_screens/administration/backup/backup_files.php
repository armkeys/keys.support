<?php $listOfBackups = $this->backup_list_all(); ?>
<div class="table-wrapper table-responsive">
    <table class="table table-condensed table-striped">
        <thead>
            <tr>
                <th><?php esc_html_e('Date', 'wishlist-member'); ?></th>
                <th><?php esc_html_e('Contains', 'wishlist-member'); ?></th>
                <th class="text-center"><?php esc_html_e('WishList Member Version', 'wishlist-member'); ?></th>
                <th class="text-center">
                    <?php esc_html_e('Actions', 'wishlist-member'); ?>
                    <?php $this->tooltip(__(' Hover over the restore point you’d like to restore and click the Restore icon.', 'wishlist-member'), 'lg'); ?>
                </th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($listOfBackups)) : ?>
                <?php foreach ($listOfBackups as $backup) : ?>
                    <tr class="button-hover backup-holder-<?php echo esc_attr($backup['date']); ?>">
                        <td><?php echo esc_html($this->format_date($backup['date'])); ?></td>
                        <td>
                            <?php
                                $contains = [];
                            if ($backup['settings']) {
                                $contains[] = __('WishList Member Settings', 'wishlist-member');
                            }
                            if ($backup['users']) {
                                $contains[] = __('Members', 'wishlist-member');
                            }
                            if ($backup['posts']) {
                                $contains[] = __('Content', 'wishlist-member');
                            }
                                $contains = implode(', ', $contains);
                            if (empty($contains)) {
                                $contains = __('WishList Member Settings', 'wishlist-member');
                            }
                                echo esc_html($contains);
                            if (isset($backup['size'])) {
                                echo " &nbsp;<em class='text-muted'>" . number_format($backup['size'] / 1048576, 2) . ' MB</em>';
                            }
                            ?>
                        </td>
                        <td class="text-center"><?php echo esc_html($backup['ver']); ?></td>
                        <td>
                            <div class="btn-group-action text-center" style="min-width: 82px">
                                <a title="Restore" href="#" data-date="<?php echo esc_attr($this->format_date($backup['date'])); ?>" data-name="<?php echo esc_attr($backup['full']); ?>" class="btn restore-backup-btn"><span class="wlm-icons md-24 -icon-only">update</span></a>
                                <a title="Download" href="#" data-name="<?php echo esc_attr($backup['full']); ?>" class="btn download-backup-btn"><span class="wlm-icons md-24 -icon-only">file_download</span></a>
                                <a title="Delete Backup" href="#" data-date="<?php echo esc_attr($this->format_date($backup['date'])); ?>" data-name="<?php echo esc_attr($backup['full']); ?>" class="btn delete-backup-btn -del-btn"><span class="wlm-icons md-24 -icon-only">delete</span></a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr><td colspan="6" class="text-center">No backup found</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
