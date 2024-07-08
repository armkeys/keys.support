<?php

/**
 * Autoloads classes namespaced under WishListMember
 *
 * Translates:
 * - WishListMember\Some_Class_Name to classes/some-class-name.php
 * - WishListMember\SubNameSpace\Some_Class_Namne to classes/subnamespace/subclass.php
 *
 * @package WishListMember\Loaders
 */

spl_autoload_register(
    function ($class) {
        // For classes in /classes folder.
        if (preg_match('/^WishListMember\\\(.+)/i', $class, $match)) {
            $file1 = dirname(__DIR__) . '/classes/' . str_replace(['_', '\\'], ['-', '/'], strtolower($match[1])) . '.php';
            $file2 = dirname(__DIR__) . '/classes/class-' . str_replace(['_', '\\'], ['-', '/'], strtolower($match[1])) . '.php';
            if (file_exists($file1)) {
                require_once $file1;
            } elseif (file_exists($file2)) {
                require_once $file2;
            }
        }

        // For autoresponder classes.
        if (preg_match('/^WishListMember\\\Autoresponders\\\(.+)/i', $class, $match)) {
            $file = dirname(__DIR__) . '/integrations/emails/' . str_replace(['_', '\\'], ['-', '/'], strtolower($match[1])) . '/handler.php';
            if (file_exists($file)) {
                require_once $file;
            }
        }
    }
);
