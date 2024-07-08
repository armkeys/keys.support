<?php

// Integration handler.
namespace WishListMember\AutoResponders;

if (! class_exists(\MailPoet\API\API::class)) {
    return;
}

class MailPoet
{
    public static function __callStatic($name, $args)
    {
        $interface = self::_interface();
        call_user_func_array([$interface, $name], $args);
    }

    public static function _interface()
    {
        static $interface;
        if (! $interface) {
            $interface = new MailPoet_Interface();
        }
        return $interface;
    }
}

class MailPoet_Interface
{
    private $ar;
    private $mpapi;
    public function __construct()
    {
        $this->mpapi = \MailPoet\API\API::MP('v1');

        $this->ar = ( new \WishListMember\Autoresponder('mailpoet') )->settings;
    }

    public function user_registered($user_id, $data)
    {
        $this->added_to_level($user_id, [$data['wpm_id']]);
    }
    public function added_to_level($user_id, $levels)
    {
        $levels = wlm_remove_inactive_levels($user_id, $levels);
        $this->process($user_id, $levels, 'added');
    }
    public function removed_from_level($user_id, $levels)
    {
        $this->process($user_id, $levels, 'removed');
    }
    public function cancelled_from_level($user_id, $levels)
    {
        $this->process($user_id, $levels, 'cancelled');
    }
    public function uncancelled_from_level($user_id, $levels)
    {
        $this->process($user_id, $levels, 'uncancelled');
    }

    private function process($user_id, $levels, $action)
    {
        $user   = new \WishListMember\User($user_id, true);
        $add    = [];
        $remove = [];
        foreach ($levels as $level) {
            $lists = (array) wlm_arrval(wlm_arrval(wlm_arrval($this->ar, 'lists'), $level), $action);
            foreach ($levels as $level) {
                $add    = $add + (array) wlm_arrval($lists, 'add');
                $remove = $remove + (array) wlm_arrval($lists, 'remove');
            }
        }

        $add    = array_diff($add, ['', false, null]);
        $remove = array_diff($remove, ['', false, null]);
        if ($add) {
            $this->add_to_list($user, $add);
        }
        if ($remove) {
            $this->remove_from_list($user, $remove);
        }
    }

    private function add_to_list($user, $lists)
    {
        try {
            $this->mpapi->addSubscriber(
                [
                    'email'      => $user->user_info->user_email,
                    'first_name' => $user->user_info->first_name,
                    'last_name'  => $user->user_info->last_name,
                ],
                (array) $lists
            );
        } catch (\Exception $e) {
            try {
                $this->mpapi->subscribeToLists(
                    $user->user_info->user_email,
                    (array) $lists
                );
            } catch (\Exception $e) {
                null;
            }
        }
    }
    private function remove_from_list($user, $lists)
    {
        try {
            $this->mpapi->unsubscribeFromLists(
                $user->user_info->user_email,
                (array) $lists
            );
        } catch (\Exception $e) {
            null;
        }
    }
}

new MailPoet();
