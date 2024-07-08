<?php
$account_email = \WishListMember\PaymentProviders\Stripe\Auth_Utils::get_account_email();
$secret        = \WishListMember\PaymentProviders\Stripe\Auth_Utils::get_account_secret();
$site_uuid     = \WishListMember\PaymentProviders\Stripe\Auth_Utils::get_account_site_uuid();
?>

<div class="page-header">
        <div class="row">
            <div class="col-md-9 col-sm-9 col-xs-8">
                <h2 class="page-title">
                    <?php esc_html_e('WishList Member Account Login', 'wishlist-member'); ?>      
                </h2>
            </div>
        </div>
    </div>  
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12">
                <?php if ($site_uuid && $account_email && $secret) : ?>
                    <p><?php esc_html_e('Connected to WishListMember.com', 'wishlist-member'); ?></p>
                <?php else : ?>
                    <p><?php esc_html_e('Connect your site to WishListMember.com to enable wishlist-member Cloud Services!', 'wishlist-member'); ?></p>
                <?php endif; ?>
            
                <?php if ($site_uuid && $account_email && $secret) : ?>
                <div class="table-wrapper -no-shadow">
                    <table class="table table-striped table-condensed table-fixed text-center">
                        <tbody>
                            <tr class="d-flex">
                                <td class="col-4 text-left">
                                    <?php esc_html_e('Account Email', 'wishlist-member'); ?>
                                </td>
                                <td class="col-8 text-left">
                                    <?php echo esc_html($account_email); ?>
                                </td>
                            </tr>
                            <tr class="d-flex">
                                <td class="col-4 text-left">
                                    <?php esc_html_e('Site ID', 'wishlist-member'); ?>
                                </td>
                                <td class="col-8 text-left">
                                    <?php echo esc_html($site_uuid); ?>
                                </td>
                            </tr>

                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="panel-footer -content-footer">
            <div class="row">
                <div class="col-lg-12 text-left">
                    <?php
                    if ($site_uuid && $account_email && $secret) :
                        $disconnect_url = add_query_arg(
                            [
                                'wlm-disconnect' => 'true',
                                'nonce'          => wp_create_nonce('wlm-disconnect'),
                            ]
                        );
                        ?>
                    <a href="<?php echo $disconnect_url; ?>" class="btn -primary wlm-confirm" data-message="<?php esc_attr_e('Are you sure? This action will disconnect any of your Stripe payment methods, block webhooks from being processed, and prevent you from charging Credit Cards with and being notified of automatic rebills from Stripe.', 'wishlist-member'); ?>">
                        <span class="text"><?php esc_html_e('Disconnect from WishListMember.com', 'wishlist-member'); ?></span>
                    </a>
                    <?php else : ?>
                    <a href="<?php echo esc_url(\WishListMember\PaymentProviders\Stripe\Authenticator::get_auth_connect_url()); ?>" class="btn -primary">
                        <span class="text"><?php esc_html_e('Connect to WishListMember.com', 'wishlist-member'); ?></span>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
</div>

<?php
add_action(
    'admin_footer',
    function () {
        ?>
    <script>
    (function ( $ ) {
        $(document).on('click','.wlm-confirm', function(e) {
            var oThis = $(this);
            var message = oThis.data( 'message' );
            if( message.length ) {
                return confirm( message );
            }
            return true;
        });
    }( jQuery ));
    </script>
        <?php
    }
);
