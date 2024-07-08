<?php

namespace WishListMember\Autoresponders\ConvertKit;

/**
 * ConvertKit SDK
 */
class SDK
{
    /**
     * API Secret
     *
     * @var string
     */
    private $api_secret = '';

    /**
     * API URL
     *
     * @var string
     */
    private $api_url = 'https://api.convertkit.com/v3';

    /**
     * Last error
     *
     * @var string
     */
    public $last_error = '';

    /**
     * Constructor
     *
     * @param string $api_secret
     */
    public function __construct($api_secret)
    {

        $this->api_secret = $api_secret;
        $this->last_error = '';

        if (! function_exists('curl_init') || ! function_exists('curl_setopt')) {
            $this->api_secret = '';
            trigger_error('cURL not supported.');
            $this->last_error = 'cURL not supported.';
        }
    }

    /**
     * Retrieve forms
     *
     * @return object|false
     */
    public function get_forms()
    {
        $request = '/forms';
        return $this->make_request($request, 'GET');
    }

    /**
     * Subscribe to form
     *
     * @param  string $form_id Form ID
     * @param  array  $args    Arguments to pass
     * @return object|false
     */
    public function form_subscribe($form_id, $args)
    {
        $request = "/forms/{$form_id}/subscribe";
        return $this->make_request($request, 'POST', $args);
    }

    /**
     * Unsubscribe from form
     *
     * @param  string $email Email to unsubscribe
     * @return object|false
     */
    public function form_unsubscribe($email)
    {
        $request = '/unsubscribe';
        $args    = [
            'email' => $email,
        ];
        return $this->make_request($request, 'PUT', $args);
    }

    /**
     * Get tags
     *
     * @return object|false
     */
    public function get_tags()
    {
        return $this->make_request('/tags', 'GET');
    }

    /**
     * Adds tags to subscriber
     *
     * @param array  $tags   Array of tag ids
     * @param string $email  Email address
     * @param array  $fields Additional fields to pass (https://developers.convertkit.com/#tag-a-subscriber)
     */
    public function add_tags($tags, $email, $fields = [])
    {
        $fields['email'] = $email;
        foreach ($tags as $tag) {
            $this->make_request('/tags/' . $tag . '/subscribe', 'POST', $fields);
        }
    }

    /**
     * Remove tags from a subscriber
     *
     * @param array  $tags  Array of tag ids
     * @param string $email Email address
     */
    public function remove_tags($tags, $email)
    {
        $fields = [
            'email' => $email,
        ];
        foreach ($tags as $tag) {
            $this->make_request('/tags/' . $tag . '/unsubscribe', 'POST', $fields);
        }
    }

    /**
     * Create a webhook
     *
     * @param  array  $event        Webhook event data (https://developers.convertkit.com/#create-a-webhook)
     * @param  string $webhook_slug Webhook slug to use for checking incoming webhooks sent by ConvertKit
     * @return object|false
     */
    public function create_webhook($event, $webhook_slug)
    {
        $args = [
            'target_url' => admin_url('?wishlist-member-convertkit-webhook=' . urlencode($webhook_slug)),
            'event'      => $event,
        ];

        return $this->make_request('/automations/hooks', 'POST', $args);
    }

    /**
     * Delete a webhook
     *
     * @param  string $webhook_rule_id Webhook Rule ID (https://developers.convertkit.com/#create-a-webhook)
     * @return object|false
     */
    public function delete_webhook($webhook_rule_id)
    {
        if ($webhook_rule_id) {
            return $this->make_request('/automations/hooks/' . $webhook_rule_id, 'DELETE');
        }
    }

    /**
     * Get webhooks
     *
     * @return array Array of Convertkit webhook rules
     */
    public function get_webhooks()
    {
        return $this->make_request('/automations/hooks/', 'GET');
    }

    /**
     * Send API request
     *
     * @param  string $request Request to make
     * @param  string $method  Request method
     * @param  array  $args    Request arguments
     * @return object|false
     */
    private function make_request($request, $method = 'GET', $args = [])
    {
        // Clear last error.
        $this->last_error = '';

        // Add api_secret to arguments.
        $args += ['api_secret' => $this->api_secret];

        // Set request URL.
        $url = $this->api_url . $request . '?' . http_build_query($args);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            [
                'Accept: application/json',
            ]
        );
        curl_setopt($ch, CURLOPT_USERAGENT, 'WLM/CKIntegration(wishlist-member)');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        if ('PUT' === $method) {
            curl_setopt($ch, CURLOPT_PUT, true);
        }
        $results = curl_exec($ch);
        $header  = curl_getinfo($ch);
        curl_close($ch);

        if ($results) {
            $results = json_decode($results, true);
        }

        $status_code = 418;
        if (isset($header['http_code'])) {
            $status_code = (int) $header['http_code'];
        } elseif (isset($results['status'])) {
            $status_code = (int) $results['status'];
        }

        if ($status_code > 201) {
            if (isset($results['error'])) {
                $this->last_error = $results['error'];
            }
            if (isset($results['message'])) {
                $this->last_error .= ':' . $results['message'];
            }
            if (empty($this->last_error)) {
                $this->last_error = 'Unknown error';
            }
            $this->last_error = $status_code . ':' . $this->last_error;
            $results          = false;
        }

        return $results;
    }
}
