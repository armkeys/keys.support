<div class="tab-pane" id="coderedemption-campaign-modal-codes">
  <div class="horizontal-tabs">
    <div class="row no-gutters">
      <div class="col-12 col-md-auto">
        <!-- Nav tabs -->
        <div class="horizontal-tabs-sidebar" style="min-width:120px">
          <ul class="nav nav-tabs -h-tabs flex-column" role="tablist">
            <li role="presentation" class="hide-on-0-codes nav-item">
              <a href="#coderedemption-campaign-modal-codes-manage" class="nav-link" role="tab" data-toggle="tab"><?php esc_html_e('Manage', 'wishlist-member'); ?></a>
            </li>
            <li role="presentation" class="nav-item">
              <a href="#coderedemption-campaign-modal-codes-generate" class="generate-code nav-link" role="tab" data-toggle="tab"><?php esc_html_e('Generate', 'wishlist-member'); ?></a>
            </li>
            <li role="presentation" class="nav-item">
              <a href="#coderedemption-campaign-modal-codes-import" class="nav-link" role="tab" data-toggle="tab"><?php esc_html_e('Import', 'wishlist-member'); ?></a>
            </li>
            <li role="presentation" class="hide-on-0-codes nav-item">
              <a href="#coderedemption-campaign-modal-codes-export" class="nav-link" role="tab" data-toggle="tab"><?php esc_html_e('Export', 'wishlist-member'); ?></a>
            </li>
          </ul>
        </div>
      </div>
      <div class="col">
        <!-- Tab panes -->
        <div class="tab-content">
          <?php
            require_once 'codes/generate.php';
            require_once 'codes/manage.php';
            require_once 'codes/import.php';
            require_once 'codes/export.php';
            ?>
        </div>
      </div>
    </div>
  </div>
  <div class="row">
    <div class="col">
      <br>
        <p>
          <span class="coderedemption-code-total"></span><br>
          <span class="coderedemption-code-stats"></span>
        </p>
    </div>
  </div>
</div>
