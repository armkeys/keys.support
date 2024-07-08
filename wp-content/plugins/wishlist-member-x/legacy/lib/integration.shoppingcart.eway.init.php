<?php

require_once dirname(__FILE__) . '/../extlib/eway/EwayWebServiceClient.php';
require_once dirname(__FILE__) . '/../extlib/eway/EwayRecurWebserviceClient.php';
require_once dirname(__FILE__) . '/../lib/integration.shoppingcart.eway.php';


class WishListEwayIntegrationInit
{
    private $forms;

    public function __construct()
    {
        add_action('wishlistmember_pre_remove_user_levels', [$this, 'user_levels_removed'], 10, 2);
        add_action('wishlistmember_eway_sync', [$this, 'eway_sync']);

        if (! wp_next_scheduled('wishlistmember_eway_sync')) {
            wp_schedule_event(time(), 'daily', 'wishlistmember_eway_sync');
        }
    }

    public function eway_sync()
    {
        WLM_INTEGRATION_EWAY::sync();
    }
    public function user_levels_removed($uid, $levels)
    {
        global $WishListMemberInstance;
        $settings = $WishListMemberInstance->get_option('ewaysettings');
        $eway_ws  = new EwayRecurWebserviceClient(
            $settings['eway_customer_id'],
            $settings['eway_username'],
            $settings['eway_password'],
            $settings['eway_sandbox']
        );

        foreach ($levels as $lid) {
            // Retrieve the trans id.
            $txn = $WishListMemberInstance->get_membership_levels_txn_id($uid, $lid);
            list($tmp, $rebill_id, $invoice_ref, $cust_id) = explode('-', $txn);

            // Do not run the call if this is not an eway rebill.
            if ('EWAYRB' !== $tmp) {
                return;
            }

            $resp = $eway_ws->call(
                'DeleteRebillEvent',
                [
                    'RebillCustomerID' => $cust_id,
                    'RebillID'         => $rebill_id,
                ]
            );
        }
    }
}

class WLM_Eway_ShortCodes
{
    protected $folder = 'wlm_eway';
    public function __construct()
    {
        add_shortcode('wlm_eway_btn', [$this, 'wlm_eway_btn']);

        // Register tinymce shortcodes.
        // Hook after the regform resources are already loaded.
        add_action('wp_footer', [$this, 'footer']);

        /**
         * Add integration shortcodes to shortcodes manifest
         *
         * @param  array $shortcodes
         * @return array
         */
        add_filter(
            'wishlistmember_integration_shortcodes',
            function ($shortcodes) {
                $levels = wishlistmember_instance()->get_option('wpm_levels');

                $wlm_shortcodes = [];
                $str            = __(' Registration Button', 'wishlist-member');
                foreach ($levels as $i => $l) {
                    $wlm_shortcodes[ sprintf('wlm_eway_btn sku=%s', $i) ] = ['label' => $l['name'] . $str];
                }
                if ($wlm_shortcodes) {
                    $shortcodes['eWAY Integration'] = $wlm_shortcodes;
                }
                return $shortcodes;
            }
        );
    }
    public function get_view_path($handle)
    {
        global $WishListMemberInstance;
        return sprintf($WishListMemberInstance->plugin_dir . '/extlib/' . $this->folder . '/%s.php', $handle);
    }
    public function profile_form($user)
    {
        $user_id = $user;
        if (is_object($user)) {
            $user_id = $user->ID;
        }

        global $WishListMemberInstance;
        global $pagenow;
        if ('profile.php' === $pagenow || 'user-edit.php' === $pagenow) {
            $stripe_cust_id = $WishListMemberInstance->Get_UserMeta($user_id, 'stripe_cust_id');
            include $this->get_view_path('stripe_user_profile');
        }
    }
    public function update_profile($user)
    {
        $user_id = $user;
        if (is_object($user)) {
            $user_id = $user->ID;
        }
        if (current_user_can('manage_options')) {
            global $WishListMemberInstance;
            if (isset(wlm_post_data()['stripe_cust_id'])) {
                $WishListMemberInstance->Update_UserMeta($user_id, 'stripe_cust_id', trim(wlm_post_data()['stripe_cust_id']));
            }
        }
    }
    public function notices()
    {
        if (extension_loaded('curl')) {
            return;
        }

        if ('WishListMember' === wlm_get_data()['page'] && 'integration' === wlm_get_data()['wl']) {
            ?>
            <div class="error fade">
                <p>
                    <?php echo wp_kses_data(__('<strong>WishList Member Notice:</strong> The <strong>Stripe</strong> integration will not work properly. Please enable <strong>Curl</strong>.', 'wishlist-member')); ?>
                </p>
            </div>
            <?php
        }
    }

    public function load_popup()
    {
        global $WishListMemberInstance;
        wp_enqueue_script('wlm-jquery-fancybox');
        wp_enqueue_style('wlm-jquery-fancybox');
        wp_enqueue_script('wlm-popup-regform');
        wp_enqueue_style('wlm-popup-regform-style');
    }
    public function wlm_eway_btn($atts, $content)
    {
        global $WishListMemberInstance;
        global $current_user;
        $this->load_popup();
        extract(
            shortcode_atts(
                [
                    'sku' => null,
                ],
                $atts
            )
        );

        if (empty($sku)) {
            return null;
        }

        $wpm_levels               = $WishListMemberInstance->get_option('wpm_levels');
        $ewaysettings             = $WishListMemberInstance->get_option('ewaysettings');
        $ewaysettings['skip_cvc'] = true;
        extract($ewaysettings);

        $ppp_level  = $WishListMemberInstance->is_ppp_level($sku);
        $level_name = $wpm_levels[ $sku ]['name'];

        if ($ppp_level) {
            $level_name = $ppp_level->post_title;
        }

        $btn_label       = empty($buttonlabel) ? 'Join %level' : $buttonlabel;
        $btn_label       = str_replace('%level', $level_name, $btn_label);
        $panel_btn_label = empty($ewaysettings['panelbuttonlabel']) ? 'Pay' : $ewaysettings['panelbuttonlabel'];
        $panel_btn_label = str_replace('%level', $level_name, $panel_btn_label);
        $settings        = $connections[ $sku ];
        $amt             = $settings['rebill_init_amount'];
        $currency        = empty($ewaysettings['currency']) ? 'USD' : $ewaysettings['currency'];
        $thankyouurl     = $WishListMemberInstance->make_thankyou_url($WishListMemberInstance->get_option('ewaythankyouurl'));

        ob_start();
        ?>
        <?php if (empty($content)) : ?>
            <button class="regform-button go-regform" style="width: auto" id="go-regform-<?php echo esc_attr($sku); ?>" class="" href="#regform-<?php echo esc_attr($sku); ?>"><?php echo esc_html($btn_label); ?></button>
        <?php else : ?>
            <a id="go-regform-<?php echo esc_attr($sku); ?>" class="go-regform" href="#regform-<?php echo esc_attr($sku); ?>"><?php echo wp_kses_post($content); ?></a>
        <?php endif; ?>

        <?php
        $btn = ob_get_clean();
        ob_start();
        ?>


        <?php
            // Retrieve fields.
            global $current_user;
            $path = sprintf($WishListMemberInstance->plugin_dir . '/extlib/' . $this->folder . '/form_new_fields.php');
            include $path;
            $this->forms[ $sku ] = wlm_build_payment_form($data);

        ?>

        <?php
        return $btn;
    }

    public function footer()
    {
        if (isset($this->forms) && ! empty($this->forms) && is_array($this->forms)) :
            foreach ((array) $this->forms as $f) {
                fwrite(WLM_STDOUT, $f);
            }
            ?>
        <script type="text/javascript">
        jQuery(function($) {
            <?php
            $skus = array_keys($this->forms);
            foreach ($skus as $sku) {
                if (is_user_logged_in()) {
                    printf(
                        "
							$('#regform-%s .regform-form').PopupRegForm({
							validate_first_name: false,
							validate_last_name: false,
							validate_email: false,
							validate_cvc: false
							});",
                        esc_js($sku)
                    );
                } else {
                    printf("$('#regform-%s .regform-form').PopupRegForm({validate_cvc: false});", esc_js($sku));
                }
            }
            ?>
        });
        </script>
            <?php
        endif;
    }
}



$sc        = new WLM_Eway_ShortCodes();
$eway_init = new WishListEwayIntegrationInit();
