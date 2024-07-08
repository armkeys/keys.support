<?php

/**
 * Selfcheck script.
 *
 * @package WishListMember/SelfCheck
 */

if (! class_exists('SelfChecker')) {
    define('WP_BASE_PATH', preg_replace('#wp-content.+#', '', __DIR__));
    define('WP_USE_THEMES', false);
    require_once WP_BASE_PATH . '/wp-load.php';
    define('WP_VERSION', $wp_version);
    require_once '../includes/functions.php';
    require_once 'class-selfchecker.php';
    $r    = new SelfChecker();
    $data = $r->check();
}
?>

<html>
    <head>
        <title>WishList Member Self Check</title>
        <?php
            wlm_print_style('https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css');
            wlm_print_style(plugins_url('css/style.css', __FILE__));
        ?>
    </head>
    <body>
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <header class="my-5 text-center">
                        <img style="width: 230px" src="../ui/images/WishListMember-logo-dark.svg" alt="WishList Member" />
                    </header>
                </div>
                <div class="col-12">
                    <h1 class="text-center my-5">WishList Member Self Check</h1>
                    <?php echo wp_kses_post($r->pp_report($data)); ?>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <hr>
                    <p class="text-center">&copy; <?php echo esc_html(gmdate('Y')); ?> Membership Software – WordPress Membership Plugin – Membership Sites. <br>All Rights Reserved. Powered by WordPress and WishlList Member&trade;</p>
                </div>
            </div>
        </div>
    </body>
</html>
