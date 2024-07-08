<?php

/**
 * Team management table row markup
 *
 * @package WishListMember/Features/TeamAccounts
 */

return <<<'ROW_HTML'
<tr data-team-member-id="%1$s" data-team-member-email="%3$s" data-active="%5$d">
 <td>%2$s</td>
 <td><a href="mailto:%3$s">%3$s</a></td>
 <td>%4$s</td>
 <td style="text-align:right">
	 <div class="wlm-team-accounts-team-btngroup">
		 <a href="#" class="wlm-team-accounts-remove-team-member" title="Remove">&#x1F5D1;</a>
	 </div>
 </td>
</tr>
ROW_HTML;
