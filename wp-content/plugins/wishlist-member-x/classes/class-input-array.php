<?php

/**
 * WLM_Post_Data
 *
 * @package WishListMember
 */

namespace WishListMember;

/**
 * Post_Data Class
 */
class Input_Array implements \ArrayAccess
{
    /**
     * Data
     *
     * @var array
     */
    protected $data = [];

    /**
     * Nonce verification status
     *
     * @var boolean
     */
    protected $nonced = null;

    /**
     * Nonce key
     *
     * @var string
     */
    protected $nonce_key = '';

    /**
     * Nonce action
     *
     * @var string|integer
     */
    protected $nonce_action = -1;

    /**
     * Constructor
     *
     * @param string         $source       Data source. Any of 'post', 'get', 'cookie', 'server' or 'request'.
     * @param string         $nonce_key    (Optional) Nonce key. Default '_wlm_nonce'.
     * @param string|integer $nonce_action (Optional) Nonce action. Default -1.
     */
    public function __construct($source, $nonce_key = '_wlm_nonce', $nonce_action = -1)
    {
        switch (trim(strtolower($source))) {
            case 'post':
                $this->data = (array) filter_input_array(INPUT_POST);
                break;
            case 'get':
                $this->data = (array) filter_input_array(INPUT_GET);
                break;
            case 'cookie':
                $this->data = (array) filter_input_array(INPUT_COOKIE);
                break;
            case 'server':
                $this->data = $_SERVER; // Note: filter_input_array( INPUT_SERVER) does not work for certain server configurations.
                break;
            case 'request':
                $this->data = array_merge((array) filter_input_array(INPUT_POST), (array) filter_input_array(INPUT_GET));
                break;
            default:
                wp_die('Invalid source type');
        }

        $this->nonce_key    = $nonce_key;
        $this->nonce_action = $nonce_action;
    }

    /**
     * Set a property.
     *
     * @param string|integer $offset Offset.
     * @param string         $value  Value.
     */
    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
                $this->data[] = $value;
        } else {
                $this->data[ $offset ] = $value;
        }
    }

    /**
     * Check if property exists.
     *
     * @param string|integer $offset Offset.
     */
    #[\ReturnTypeWillChange]
    public function offsetExists($offset)
    {
            return isset($this->data[ $offset ]);
    }

    /**
     * Unset property
     *
     * @param string|integer $offset Offset.
     */
    #[\ReturnTypeWillChange]
    public function offsetUnset($offset)
    {
            unset($this->data[ $offset ]);
    }

    /**
     * Get property
     *
     * @param string $offset Offset.
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
            return isset($this->data[ $offset ]) ? $this->data[ $offset ] : null;
    }

    /**
     * Return data on '__invoke()'
     * Allows all of data to be returned via $object();
     *
     * @param  array|null $data (Optional) If array, then replace data property with this.
     * @return array
     */
    public function __invoke($data = null)
    {
        if (is_array($data)) {
            $this->data = $data;
        }
        return $this->data;
    }

    /**
     * Verify nonce status
     *
     * @return boolean
     */
    public function is_nonced()
    {
        if (is_null($this->nonced)) {
            if (! function_exists('wp_verify_nonce')) {
                require_once ABSPATH . '/wp-includes/pluggable.php';
            }
            $this->nonced = ! empty($this->nonce_key) && ! empty($this->data[ $this->nonce_key ]) && wp_verify_nonce($this->data[ $this->nonce_key ], $this->nonce_action);
        }
        return $this->nonced;
    }
}
