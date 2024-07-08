<?php
$message = isset($message) ? $message : __('Please enter your username or email address. You will receive an email message with a one-time login link.', 'wishlist-member');
$message = sprintf('<p class="message">%s</p>', $message);

$xuser_login = '';

if (! empty($error)) {
    $message    .= sprintf('<div id="login_error">%s</div>', $error);
    $xuser_login = esc_attr($user_login);
}

login_header(__('WishList Member One-Time Login Link'), $message);
?>
<form id="loginform" name="loginform" method="post" action="<?php echo esc_url(site_url('wp-login.php?action=wishlistmember-otl', 'login_post')); ?>">
  <p>
    <label for="user_login"><?php esc_html_e('Username or Email Address'); ?></label>
    <input type="text" name="user_login" id="user_login" class="input" value="<?php echo esc_attr($xuser_login); ?>" size="20" autocapitalize="off" />
  </p>
  <p class="submit">
    <input type="submit" name="wp-submit" id="wp-submit" class="button button-primary button-large" value="<?php esc_attr_e('Get One-Time Login Link', 'wishlist-member'); ?>" />
        <span id="wishlist-member-otl">
            <a href="<?php echo esc_url(wp_login_url(wlm_arrval($_REQUEST, 'redirect_to') ? wlm_arrval('lastresult') : '')); ?>"><?php esc_html_e('Login using username/email and password', 'wishlist-member'); ?></a>
        </span>
  </p>
</form>
<?php
login_footer();
?>
