<?php

namespace WishListMember\Webinars;

class EverGreenIntegration
{
    public $slug = 'evergreen';
    public function __construct()
    {
        // Hook to our subscribe function.
        add_action('wishlistmember_webinar_subscribe', [$this, 'subscribe']);
    }

    /**
     * Action: wishlistmember_webinar_subscribe
     * Subscribes a user to a webinar
     *
     * @param array $data
     */
    public function subscribe($data)
    {

        $webinars = wishlistmember_instance()->get_option('webinar');
        $settings = $webinars[ $this->slug ];
        $settings = $settings[ $data['level'] ];
        if (empty($settings)) {
            return;
        }

        $url      = $settings;
        $urlparts = parse_url($url);
        parse_str($urlparts['query'], $args);
        $args['name']  = sprintf('%s %s', $data['first_name'], $data['last_name']);
        $args['email'] = $data['email'];

        // Subscribe to next day.
        $args['date']     = wlm_date('Y-m-d', time() + ( 3600 * 24 ));
        $args['timezone'] = 'UTC';

        $query             = http_build_query($args);
        $urlparts['query'] = $query;

        $url = sprintf('%s://%s%s?%s', $urlparts['scheme'], $urlparts['host'], $urlparts['path'], $urlparts['query']);
        wishlistmember_instance()->ReadURL($url);
    }
}

new EverGreenIntegration();
