<?php

/**
 * Deprecated Content Methods
 *
 * @package WishListMember
 */

namespace WishListMember;

/**
 * Deprecated Content Methods trait
 */
trait Content_Methods_Deprecated
{
    /**
     * Deprecated method.
     * Wrapper for get_content_levels()
     *
     * @param  string       $content_type  Content type.
     * @param  integer      $id            Content ID.
     * @param  boolean      $names         TRUE to return level names instead of IDs.
     * @param  boolean      $implode_names Implode level names with ', '.
     * @param  string|array $immutable     Array of immutable levels or 'nothing'. This variable is passed by reference.
     * @return array                        Array of level IDs or Array of level names if $names == true
     */
    public function GetContentLevels($content_type, $id, $names = null, $implode_names = null, &$immutable = 'nothing')
    {
        wlm_deprecated_method_error_log(__METHOD__, 'get_content_levels');
        return $this->get_content_levels($content_type, $id, $names, $implode_names, $immutable);
    }
}
