<?php

// Common stuff for all 3 paypal integrations.
global $wlm_paypal_buttons;
$wlm_paypal_buttons = [
    'pp_pay:l'      => 'https://www.paypalobjects.com/webstatic/en_AU/i/buttons/btn_paywith_primary_l.png',
    'pp_pay:m'      => 'https://www.paypalobjects.com/webstatic/en_AU/i/buttons/btn_paywith_primary_m.png',
    'pp_pay:s'      => 'https://www.paypalobjects.com/webstatic/en_AU/i/buttons/btn_paywith_primary_s.png',
    'pp_buy:l'      => 'https://www.paypalobjects.com/webstatic/en_US/i/buttons/buy-logo-large.png',
    'pp_buy:m'      => 'https://www.paypalobjects.com/webstatic/en_US/i/buttons/buy-logo-medium.png',
    'pp_buy:s'      => 'https://www.paypalobjects.com/webstatic/en_US/i/buttons/buy-logo-small.png',
    'pp_checkout:l' => 'https://www.paypalobjects.com/webstatic/en_US/i/buttons/checkout-logo-large.png',
    'pp_checkout:m' => 'https://www.paypalobjects.com/webstatic/en_US/i/buttons/checkout-logo-medium.png',
    'pp_checkout:s' => 'https://www.paypalobjects.com/webstatic/en_US/i/buttons/checkout-logo-small.png',
];

function wlm_paypal_create_description($product, $with_name = true)
{
    $description = '';
    if ($with_name) {
        $description = $product['name'] . ' (';
    }
    if ($product['trial'] && $product['trial_amount']) {
        $description .= sprintf(
            // Translators: 1 - Currenct, 2 - Trial amount, 3 - Trial recurring billing frequency, 4 - Trial recurring billing period, 5 - "s" or ""
            __('%1$s %2$0.2f for the first %3$d %4$s%5$s then ', 'wishlist-member'),
            $product['currency'],
            $product['trial_amount'],
            $product['trial_recur_billing_frequency'],
            strtolower($product['trial_recur_billing_period']),
            $product['trial_recur_billing_frequency'] > 1 ? 's' : ''
        );
    }
    if ($product['payflow_recur_pay_period']) {
        switch ($product['payflow_recur_pay_period']) {
            case 'DAY':
                $product_pay_period = 'DAY';
                break;
            case 'WEEK':
                $product_pay_period = 'WEEK';
                break;
            case 'BIWK':
                $product_pay_period = 'TWO WEEKS';
                break;
            case 'MONT':
                $product_pay_period = 'MONTH';
                break;
            case 'FRWK':
                $product_pay_period = 'FOUR WEEKS';
                break;
            case 'QTER':
                $product_pay_period = 'QUARTER';
                break;
            case 'SMYR':
                $product_pay_period = 'TWICE EVERY YEAR';
                break;
            case 'SMMO':
                $product_pay_period = 'TWICE EVERY MONTH';
                break;
            case 'YEAR':
                $product_pay_period = 'YEAR';
                break;
        }
        if ('SMYR' === $product['payflow_recur_pay_period'] || 'SMMO' === $product['payflow_recur_pay_period']) {
            // Translators: 1: currency, 2: recurring amount, 3: frequency, 4: period, 5: 's' appended to period if frequency > 1.
            $description .= sprintf(__('%1$s %2$0.2f %3$d %4$s%5$s', 'wishlist-member'), $product['currency'], $product['recur_amount'], $product['recur_billing_frequency'], strtolower($product_pay_period), $product['recur_billing_frequency'] > 1 ? 's' : '');
        } else {
            // Translators: 1: currency, 2: recurring amount, 3: frequency, 4: period, 5: 's' appended to period if frequency > 1.
            $description .= sprintf(__('%1$s %2$0.2f every %3$d %4$s%5$s', 'wishlist-member'), $product['currency'], $product['recur_amount'], $product['recur_billing_frequency'], strtolower($product_pay_period), $product['recur_billing_frequency'] > 1 ? 's' : '');
        }
    } else {
        // Translators: 1: currency, 2: recurring amount, 3: frequency, 4: period, 5: 's' appended to period if frequency > 1.
        $description .= sprintf(__('%1$s %2$0.2f every %3$d %4$s%5$s', 'wishlist-member'), $product['currency'], $product['recur_amount'], $product['recur_billing_frequency'], strtolower($product['recur_billing_period']), $product['recur_billing_frequency'] > 1 ? 's' : '');
    }
    if ($product['recur_billing_cycles'] > 1) {
        // Translators: %d: number of installments.
        $description .= sprintf(__("\nfor %d installments", 'wishlist-member'), $product['recur_billing_cycles']);
    }
    if ($with_name) {
        $description .= ')';
    }
    return str_replace(' 1 ', ' ', $description);
}

/**
 * Common function to add shortcode inserter for all paypal integrations
 *
 * @param  array  $shortcodes Integration shortcodes manifest
 * @param  string $shortcode  Shortcode of the calling PayPal integration
 * @param  string $label      Shortcode label of the calling PayPal integration
 * @param  array  $products   Configured products of the the calling PayPal integration
 * @return array              Filtered shortcodes manifest
 */
function wlm_paypal_shortcode_buttons($shortcodes, $shortcode, $label, $products, $spb = null)
{
    global $wlm_paypal_buttons;
    if (is_array($products) && count($products)) {
        $skus = [];
        foreach ($products as $product) {
            $skus[ $product['id'] ] = [
                'label' => $product['name'],
            ];
        }

        // Array(6) { ["enable"]=> string(1) "1" ["layout"]=> string(10) "horizontal" ["size"]=> string(6) "medium" ["shape"]=> string(4) "pill" ["color"]=> string(4) "gold" ["funding"]=> array(2) { [0]=> string(4) "CARD" [1]=> string(6) "CREDIT" } }
        // [wlm_paypalec_btn name="Checkout #1" sku="KFBC2OJI" layout="horizontal" size="medium" shape="pill" color="gold" funding="CARD,CREDIT"]
        $wlm_shortcodes = [
            $shortcode => [
                'has_preview' => true,
                'label'       => $label,
                'attributes'  => [
                    'sku' => [
                        'label'       => __('Product', 'wishlist-member'),
                        'type'        => 'select',
                        'options'     => $skus,
                        'placeholder' => __('Select Product', 'wishlist-member'),
                    ],
                ],
            ],
        ];

        if (is_array($spb) && ! empty($spb['enable'])) {
            // Spb buttons.
            $wlm_shortcodes[ $shortcode ]['attributes']['layout']  = [
                'label'      => __('Layout', 'wishlist-member'),
                'columns'    => 6,
                'type'       => 'select',
                'options'    => [
                    'vertical'   => ['label' => __('Vertical', 'wishlist-member')],
                    'horizontal' => ['label' => __('Horizontal', 'wishlist-member')],
                ],
                'default'    => wlm_arrval($spb, 'layout') ? wlm_arrval('lastresult') : 'vertical',
                'dependency' => '[name="sku"] option:selected[value!=""]',
            ];
            $wlm_shortcodes[ $shortcode ]['attributes']['size']    = [
                'label'      => __('Size', 'wishlist-member'),
                'columns'    => 6,
                'type'       => 'select',
                'options'    => [
                    'medium'     => ['label' => __('Medium', 'wishlist-member')],
                    'large'      => ['label' => __('Large', 'wishlist-member')],
                    'responsive' => ['label' => __('Responsive', 'wishlist-member')],
                ],
                'default'    => wlm_arrval($spb, 'size') ? wlm_arrval('lastresult') : 'medium',
                'dependency' => '[name="sku"] option:selected[value!=""]',
            ];
            $wlm_shortcodes[ $shortcode ]['attributes']['shape']   = [
                'label'      => __('Shape', 'wishlist-member'),
                'columns'    => 6,
                'type'       => 'select',
                'options'    => [
                    'pill' => ['label' => __('Pill', 'wishlist-member')],
                    'rect' => ['label' => __('Rectangle', 'wishlist-member')],
                ],
                'default'    => wlm_arrval($spb, 'shape') ? wlm_arrval('lastresult') : 'pill',
                'dependency' => '[name="sku"] option:selected[value!=""]',
            ];
            $wlm_shortcodes[ $shortcode ]['attributes']['color']   = [
                'label'      => __('Shape', 'wishlist-member'),
                'columns'    => 6,
                'type'       => 'select',
                'options'    => [
                    'gold'   => ['label' => __('Gold', 'wishlist-member')],
                    'blue'   => ['label' => __('Blue', 'wishlist-member')],
                    'silver' => ['label' => __('Silver', 'wishlist-member')],
                    'white'  => ['label' => __('White', 'wishlist-member')],
                    'black'  => ['label' => __('Black', 'wishlist-member')],
                ],
                'default'    => wlm_arrval($spb, 'color') ? wlm_arrval('lastresult') : 'gold',
                'dependency' => '[name="sku"] option:selected[value!=""]',
            ];
            $wlm_shortcodes[ $shortcode ]['attributes']['funding'] = [
                'label'      => __('Allowed Funding Source', 'wishlist-member'),
                'type'       => 'checkbox',
                'inline'     => true,
                'options'    => [
                    'CARD'   => ['label' => __('Card', 'wishlist-member')],
                    'CREDIT' => ['label' => __('Credit', 'wishlist-member')],
                    'ELV'    => ['label' => __('ELV', 'wishlist-member')],
                ],
                'default'    => (array) wlm_arrval($spb, 'funding'),
                'dependency' => '[name="sku"] option:selected[value!=""]',
            ];
            $GLOBALS['wlm_paypal_objects']                         = true;
        } else {
            // Non-spb buttons.
            $wlm_shortcodes[ $shortcode ]['attributes']['btn']        = [
                'label'      => __('Button Type', 'wishlist-member'),
                'type'       => 'select',
                'options'    => [
                    'pp_pay'      => ['label' => __('PayPal Button: Pay with PayPal', 'wishlist-member')],
                    'pp_buy'      => ['label' => __('PayPal Button: Buy now with PayPal', 'wishlist-member')],
                    'pp_checkout' => ['label' => __('PayPal Button: Checkout with PayPal', 'wishlist-member')],
                    'custom'      => ['label' => __('Custom Image URL / Plain Text', 'wishlist-member')],
                ],
                'dependency' => '[name="sku"] option:selected[value!=""]',
            ];
            $wlm_shortcodes[ $shortcode ]['attributes']['btn-custom'] = [
                'label'       => __('Custom Image URL / Plain Text', 'wishlist-member'),
                'placeholder' => __('Buy Now', 'wishlist-member'),
                'dependency'  => '[name="sku"]  option:selected[value!=""] && [name="btn"] option:selected[value="custom"]',
            ];
            $wlm_shortcodes[ $shortcode ]['attributes']['btn-size']   = [
                'columns'    => 4,
                'label'      => __('Button Size', 'wishlist-member'),
                'type'       => 'select',
                'options'    => [
                    's' => ['label' => __('Small', 'wishlist-member')],
                    'm' => ['label' => __('Medium', 'wishlist-member')],
                    'l' => ['label' => __('Large', 'wishlist-member')],
                ],
                'dependency' => '[name="sku"]  option:selected[value!=""] && [name="btn"] option:selected[value!="custom"]',
            ];
        }

        $shortcodes = $shortcodes + $wlm_shortcodes;
    }
    return $shortcodes;
}

add_action(
    'admin_enqueue_scripts',
    function () {
        global $wlm_paypal_buttons;
        wp_enqueue_script('wishlistmember-paypal-shortcode-inserter-js', wishlistmember_instance()->plugin_url3 . '/integrations/payments/paypalec/assets/shortcode-inserter-common.js', ['wishlistmember-shortcode-insert-js'], wishlistmember_instance()->version, true);
        wp_localize_script('wishlistmember-paypal-shortcode-inserter-js', 'wlm_paypal_buttons', $wlm_paypal_buttons);

        global $wlm_paypal_objects;
        if (! empty($wlm_paypal_objects)) {
            wp_enqueue_script(
                'wishlistmember-paypalobjects-api-' . sanitize_title(basename(__FILE__)), // handle
                'https://www.paypalobjects.com/api/checkout.min.js', // url
                ['wishlistmember-paypal-shortcode-inserter-js'], // dependency
                wishlistmember_instance()->version // version
            );
        }
    }
);
