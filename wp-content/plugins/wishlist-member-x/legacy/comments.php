<?php
$wlm_closed_comment = wlm_trim($GLOBALS['WishListMemberInstance']->get_option('closed_comments_msg'));
?>
<div><?php echo esc_html($wlm_closed_comment); ?></div>
