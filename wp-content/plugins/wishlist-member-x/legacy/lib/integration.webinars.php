<?php

/*
 * payment provider array
 */

$wishlist_member_webinars = [
    'integration.webinar.easywebinar.php'    => [
        'optionname' => 'easywebinar',
        'name'       => 'Easy Webinar',
        'classname'  => 'WishListMemberWebinarIntegrationEasyWebinar',
    ],
    'integration.webinar.evergreen.php'      => [
        'optionname' => 'evergreen',
        'name'       => 'Evergreen Business System',
        'classname'  => 'WishListMemberWebinarIntegrationEverGreen',
    ],
    'integration.webinar.gotomeeting.php'    => [
        'optionname' => 'gotomeeting',
        'name'       => 'GoToWebinar <sup><small>&reg;</small></sup>',
        'classname'  => 'WishListMemberWebinarIntegrationGotowebinar',
    ],
    'integration.webinar.gotomeetingapi.php' => [
        'optionname' => 'gotomeetingapi',
        'name'       => 'GoToWebinar API <sup><small>&reg;</small></sup>',
        'classname'  => 'WishListMemberWebinarIntegrationGotowebinarApi',
    ],
];
