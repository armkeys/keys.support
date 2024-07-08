<?php

/**
 * File Protection Methods Feature
 *
 * @package WishListMember/Features/File_Protection
 */

namespace WishListMember\Features\File_Protection;

/**
 * File Protection Methods class
 */
class File_Protection_Methods
{
    /**
     * Get file's mime type
     *
     * Retrieves the correct mime type of a file
     * This function is based on Chris Jean's recommendations:
     * http://chrisjean.com/2009/02/14/generating-mime-type-in-php-is-not-magic/
     *
     * @param  string $filename Path to file.
     * @return string          Mime type (or an empty string if it failed)
     */
    public function get_mime_type($filename)
    {

        if (file_exists($filename)) {
            // First, let's see if we can get the mime type using finfo functions.
            if (function_exists('finfo_open') && function_exists('finfo_file') && function_exists('finfo_close')) {
                    $finfo = finfo_open(FILEINFO_MIME);
                    $mime  = finfo_file($finfo, $filename);
                    finfo_close($finfo);
                if (! empty($mime)) {
                        return $mime;
                }
            }
        }

        // Next, let's try to retrieve the mime type from our array.
        $mime_types = [
            'ai'      => 'application/postscript',
            'aif'     => 'audio/x-aiff',
            'aifc'    => 'audio/x-aiff',
            'aiff'    => 'audio/x-aiff',
            'asc'     => 'text/plain',
            'asf'     => 'video/x-ms-asf',
            'asx'     => 'video/x-ms-asf',
            'au'      => 'audio/basic',
            'avi'     => 'video/x-msvideo',
            'bcpio'   => 'application/x-bcpio',
            'bin'     => 'application/octet-stream',
            'bmp'     => 'image/bmp',
            'bz2'     => 'application/x-bzip2',
            'cdf'     => 'application/x-netcdf',
            'chrt'    => 'application/x-kchart',
            'class'   => 'application/octet-stream',
            'cpio'    => 'application/x-cpio',
            'cpt'     => 'application/mac-compactpro',
            'csh'     => 'application/x-csh',
            'css'     => 'text/css',
            'dcr'     => 'application/x-director',
            'dir'     => 'application/x-director',
            'djv'     => 'image/vnd.djvu',
            'djvu'    => 'image/vnd.djvu',
            'dll'     => 'application/octet-stream',
            'dms'     => 'application/octet-stream',
            'doc'     => 'application/msword',
            'dvi'     => 'application/x-dvi',
            'dxr'     => 'application/x-director',
            'eps'     => 'application/postscript',
            'etx'     => 'text/x-setext',
            'exe'     => 'application/octet-stream',
            'dmg'     => 'application/octet-stream',
            'msi'     => 'application/octet-stream',
            'ez'      => 'application/andrew-inset',
            'flv'     => 'video/x-flv',
            'gif'     => 'image/gif',
            'gtar'    => 'application/x-gtar',
            'gz'      => 'application/x-gzip',
            'hdf'     => 'application/x-hdf',
            'hqx'     => 'application/mac-binhex40',
            'htm'     => 'text/html',
            'html'    => 'text/html',
            'ice'     => 'x-conference/x-cooltalk',
            'ief'     => 'image/ief',
            'iges'    => 'model/iges',
            'igs'     => 'model/iges',
            'img'     => 'application/octet-stream',
            'iso'     => 'application/octet-stream',
            'jad'     => 'text/vnd.sun.j2me.app-descriptor',
            'jar'     => 'application/x-java-archive',
            'jnlp'    => 'application/x-java-jnlp-file',
            'jpe'     => 'image/jpeg',
            'jpeg'    => 'image/jpeg',
            'jpg'     => 'image/jpeg',
            'js'      => 'application/x-javascript',
            'kar'     => 'audio/midi',
            'kil'     => 'application/x-killustrator',
            'kpr'     => 'application/x-kpresenter',
            'kpt'     => 'application/x-kpresenter',
            'ksp'     => 'application/x-kspread',
            'kwd'     => 'application/x-kword',
            'kwt'     => 'application/x-kword',
            'latex'   => 'application/x-latex',
            'lha'     => 'application/octet-stream',
            'lzh'     => 'application/octet-stream',
            'm3u'     => 'audio/x-mpegurl',
            'man'     => 'application/x-troff-man',
            'me'      => 'application/x-troff-me',
            'mesh'    => 'model/mesh',
            'mid'     => 'audio/midi',
            'midi'    => 'audio/midi',
            'mif'     => 'application/vnd.mif',
            'mov'     => 'video/quicktime',
            'movie'   => 'video/x-sgi-movie',
            'mp2'     => 'audio/mpeg',
            'mp3'     => 'audio/mpeg',
            'mp4'     => 'video/mp4',
            'mpe'     => 'video/mpeg',
            'mpeg'    => 'video/mpeg',
            'mpg'     => 'video/mpeg',
            'mpga'    => 'audio/mpeg',
            'ms'      => 'application/x-troff-ms',
            'msh'     => 'model/mesh',
            'mxu'     => 'video/vnd.mpegurl',
            'nc'      => 'application/x-netcdf',
            'odb'     => 'application/vnd.oasis.opendocument.database',
            'odc'     => 'application/vnd.oasis.opendocument.chart',
            'odf'     => 'application/vnd.oasis.opendocument.formula',
            'odg'     => 'application/vnd.oasis.opendocument.graphics',
            'odi'     => 'application/vnd.oasis.opendocument.image',
            'odm'     => 'application/vnd.oasis.opendocument.text-master',
            'odp'     => 'application/vnd.oasis.opendocument.presentation',
            'ods'     => 'application/vnd.oasis.opendocument.spreadsheet',
            'odt'     => 'application/vnd.oasis.opendocument.text',
            'oga'     => 'audio/ogg',
            'ogg'     => 'application/ogg',
            'ogv'     => 'video/ogg',
            'otg'     => 'application/vnd.oasis.opendocument.graphics-template',
            'oth'     => 'application/vnd.oasis.opendocument.text-web',
            'otp'     => 'application/vnd.oasis.opendocument.presentation-template',
            'ots'     => 'application/vnd.oasis.opendocument.spreadsheet-template',
            'ott'     => 'application/vnd.oasis.opendocument.text-template',
            'pbm'     => 'image/x-portable-bitmap',
            'pdb'     => 'chemical/x-pdb',
            'pdf'     => 'application/pdf',
            'pgm'     => 'image/x-portable-graymap',
            'pgn'     => 'application/x-chess-pgn',
            'png'     => 'image/png',
            'pnm'     => 'image/x-portable-anymap',
            'ppm'     => 'image/x-portable-pixmap',
            'ppt'     => 'application/vnd.ms-powerpoint',
            'ps'      => 'application/postscript',
            'qt'      => 'video/quicktime',
            'ra'      => 'audio/x-realaudio',
            'ram'     => 'audio/x-pn-realaudio',
            'ras'     => 'image/x-cmu-raster',
            'rgb'     => 'image/x-rgb',
            'rm'      => 'audio/x-pn-realaudio',
            'roff'    => 'application/x-troff',
            'rpm'     => 'application/x-rpm',
            'rtf'     => 'text/rtf',
            'rtx'     => 'text/richtext',
            'sgm'     => 'text/sgml',
            'sgml'    => 'text/sgml',
            'sh'      => 'application/x-sh',
            'shar'    => 'application/x-shar',
            'silo'    => 'model/mesh',
            'sis'     => 'application/vnd.symbian.install',
            'sit'     => 'application/x-stuffit',
            'skd'     => 'application/x-koan',
            'skm'     => 'application/x-koan',
            'skp'     => 'application/x-koan',
            'skt'     => 'application/x-koan',
            'smi'     => 'application/smil',
            'smil'    => 'application/smil',
            'snd'     => 'audio/basic',
            'so'      => 'application/octet-stream',
            'spl'     => 'application/x-futuresplash',
            'src'     => 'application/x-wais-source',
            'stc'     => 'application/vnd.sun.xml.calc.template',
            'std'     => 'application/vnd.sun.xml.draw.template',
            'sti'     => 'application/vnd.sun.xml.impress.template',
            'stw'     => 'application/vnd.sun.xml.writer.template',
            'sv4cpio' => 'application/x-sv4cpio',
            'sv4crc'  => 'application/x-sv4crc',
            'svg'     => 'image/svg+xml',
            'swf'     => 'application/x-shockwave-flash',
            'sxc'     => 'application/vnd.sun.xml.calc',
            'sxd'     => 'application/vnd.sun.xml.draw',
            'sxg'     => 'application/vnd.sun.xml.writer.global',
            'sxi'     => 'application/vnd.sun.xml.impress',
            'sxm'     => 'application/vnd.sun.xml.math',
            'sxw'     => 'application/vnd.sun.xml.writer',
            't'       => 'application/x-troff',
            'tar'     => 'application/x-tar',
            'tcl'     => 'application/x-tcl',
            'tex'     => 'application/x-tex',
            'texi'    => 'application/x-texinfo',
            'texinfo' => 'application/x-texinfo',
            'tgz'     => 'application/x-gzip',
            'tif'     => 'image/tiff',
            'tiff'    => 'image/tiff',
            'torrent' => 'application/x-bittorrent',
            'tr'      => 'application/x-troff',
            'tsv'     => 'text/tab-separated-values',
            'txt'     => 'text/plain',
            'ustar'   => 'application/x-ustar',
            'vcd'     => 'application/x-cdlink',
            'vrml'    => 'model/vrml',
            'wav'     => 'audio/x-wav',
            'wax'     => 'audio/x-ms-wax',
            'webm'    => 'video/webm',
            'wbmp'    => 'image/vnd.wap.wbmp',
            'wbxml'   => 'application/vnd.wap.wbxml',
            'wm'      => 'video/x-ms-wm',
            'wma'     => 'audio/x-ms-wma',
            'wml'     => 'text/vnd.wap.wml',
            'wmlc'    => 'application/vnd.wap.wmlc',
            'wmls'    => 'text/vnd.wap.wmlscript',
            'wmlsc'   => 'application/vnd.wap.wmlscriptc',
            'wmv'     => 'video/x-ms-wmv',
            'wmx'     => 'video/x-ms-wmx',
            'wrl'     => 'model/vrml',
            'wvx'     => 'video/x-ms-wvx',
            'xbm'     => 'image/x-xbitmap',
            'xht'     => 'application/xhtml+xml',
            'xhtml'   => 'application/xhtml+xml',
            'xls'     => 'application/vnd.ms-excel',
            'xml'     => 'text/xml',
            'xpm'     => 'image/x-xpixmap',
            'xsl'     => 'text/xml',
            'xwd'     => 'image/x-xwindowdump',
            'xyz'     => 'chemical/x-xyz',
            'zip'     => 'application/zip',
            'xlsx'    => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'xltx'    => 'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
            'potx'    => 'application/vnd.openxmlformats-officedocument.presentationml.template',
            'ppsx'    => 'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
            'pptx'    => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'sldx'    => 'application/vnd.openxmlformats-officedocument.presentationml.slide',
            'docx'    => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'dotx'    => 'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
            'xlam'    => 'application/vnd.ms-excel.addin.macroEnabled.12',
            'xlsb'    => 'application/vnd.ms-excel.sheet.binary.macroEnabled.12',
        ];

        $ext = explode('.', $filename);
        $ext = strtolower(array_pop($ext));
        if (! empty($mime_types[ $ext ])) {
            return $mime_types[ $ext ];
        }

        /*
         * last, we try to retrieve it using mime_content_type
         * Why is this last??? Because it's unreliable...
         */

        if (function_exists('mime_content_type')) {
            if (file_exists($filename)) {
                $mime = mime_content_type($filename);
                if (! empty($mime)) {
                    return $mime;
                }
            }
        }

        // Still nothing? we return an empty string.
        return '';
    }

    /**
     * Processes file protection
     *
     * @param string $wlm_file File to process (relative to the WP Upload Folder).
     */
    public function file_protect($wlm_file)
    {

        $attachments = wishlistmember_instance()->file_attachments();

        // Remove upload path from $wlm_file.
        if (0 === strpos($wlm_file, wishlistmember_instance()->wp_upload_path . '/')) {
            $wlm_file = str_replace(wishlistmember_instance()->wp_upload_path . '/', '', $wlm_file);
        }

        /*
         * There's an issue where /wp-content/uploads/ are also being returned in NGINX web servers
         * So we check if the $wlm_file have those and trim them out
         */
        if (0 === strpos($wlm_file, '/wp-content/uploads/')) {
            $wlm_file = str_replace('/wp-content/uploads/', '', $wlm_file);
        }

        // Fis for when WP is installed at subfolder but home url is main domain.
        $upload_dir         = wp_upload_dir();
        $pathtouploads_file = $upload_dir['baseurl'] . '/' . $wlm_file;

        if (isset($attachments[ $wlm_file ]) || isset($attachments[ $pathtouploads_file ])) {
            $finfo = $attachments[ $wlm_file ];
            if (empty($finfo)) {
                $finfo = $attachments[ $pathtouploads_file ];
            }

            $redirect = false;

            $finfo = (object) $finfo;

            $levels    = wishlistmember_instance()->get_content_levels('attachment', $finfo->ID);
            $protected = array_search('Protection', $levels, true);
            if (false !== $protected) { // We're protected.
                unset($levels[ $protected ]);
                $user = wp_get_current_user();
                if (! $user->caps['administrator']) {
                    if ($user->ID) {
                        $ulevels = wishlistmember_instance()->get_membership_levels($user->ID);
                        $levels  = array_intersect($levels, $ulevels);
                        if (count($levels)) {
                            // Check if any of the levels are for confirmation.
                            foreach ((array) $levels as $key => $level) {
                                if (wishlistmember_instance()->level_unconfirmed($level, $user->ID)) {
                                    $unconfirmedlevels[] = $level;
                                }
                            }
                            if ($unconfirmedlevels) {
                                $redirect = wishlistmember_instance()->for_confirmation_url();
                            }
                            // Check if any of the levels are cancelled.
                            foreach ((array) $levels as $key => $level) {
                                if (wishlistmember_instance()->level_cancelled($level, $user->ID)) {
                                        unset($levels[ $key ]);
                                }
                            }
                            // No more levels left for this member? if so, redirect to cancelled page.
                            if (! count($levels)) {
                                $redirect = wishlistmember_instance()->cancelled_url();
                            }
                        } else {
                            $redirect = wishlistmember_instance()->wrong_level_url();
                        }
                    } else {
                        $redirect = wishlistmember_instance()->non_members_url();
                    }
                }
            }

            // No access rights, we redirect.
            if ($redirect) {
                header("Location:{$redirect}");
                exit;
            }
        }

        $file = wishlistmember_instance()->wp_upload_path . '/' . $wlm_file;
        // Load the correct mime type.
        $mime = wishlistmember_instance()->get_mime_type($file);
        if (empty($mime)) {
            $mime = $finfo->mime;
        }

        if (file_exists($file)) {
            wishlistmember_instance()->download($file);
        } else {
            // File does not exist.
            header('HTTP/1.0 404 Not Found');
            print( '404 - File Not Found' );
        }
        exit;
    }

    /**
     * Adds/Removes .htaccess code to the upload folder's htaccess file
     *
     * @param boolean $remove Default False. True removes the code.
     */
    public function file_protect_htaccess($remove = false)
    {
        if (file_exists(wishlistmember_instance()->wp_upload_path)) {
            // File protection code markers.
            $old_htaccess_start = '# BEGIN WishList Member Attachment Protection';
            $old_htaccess_end   = '# END WishList Member Attachment Protection';

            $site_host = wp_parse_url(get_bloginfo('wpurl'), PHP_URL_HOST);

            $htaccess_start = '# BEGIN WishList Member Attachment Protection for ' . $site_host;
            $htaccess_end   = '# END WishList Member Attachment Protection for ' . $site_host;

            // Apache - read .htaccess.
            $htaccess_file = wishlistmember_instance()->wp_upload_path . '/.htaccess';
            $htaccess      = (string) @file_get_contents($htaccess_file);

            // Apache - remove .htaccess code.
            list($start)   = @preg_split('#^' . preg_quote($htaccess_start, '#') . '$#m', $htaccess);
            list($x, $end) = wlm_or(@preg_split('#^' . preg_quote($htaccess_end, '#') . '$#m', $htaccess), []) + [1 => ''];
            $htaccess      = trim(wlm_trim($start) . "\n" . wlm_trim($end));

            // Apache - remove old .htaccess code.
            list($start)   = @preg_split('#^' . preg_quote($old_htaccess_start, '#') . '$#m', $htaccess);
            list($x, $end) = wlm_or(@preg_split('#^' . preg_quote($old_htaccess_end, '#') . '$#m', $htaccess), []) + [1 => ''];
            $htaccess      = trim(wlm_trim($start) . "\n" . wlm_trim($end));

            // Nginx - read config.
            $nginx_file = wishlistmember_instance()->wp_upload_path . '/wlm_file_protect_nginx.conf';
            $nginx      = wlm_trim(@file_get_contents($nginx_file));

            // Nginx - remove config code.
            list($start)   = @preg_split('#^' . preg_quote($htaccess_start, '#') . '$#m', $nginx);
            list($x, $end) = wlm_or(@preg_split('#^' . preg_quote($htaccess_end, '#') . '$#m', $nginx), []) + [1 => ''];
            $nginx         = trim(wlm_trim($start) . "\n" . wlm_trim($end));

            // Nginx - remove old config code.
            list($start)   = @preg_split('#^' . preg_quote($old_htaccess_start, '#') . '$#m', $nginx);
            list($x, $end) = wlm_or(@preg_split('#^' . preg_quote($old_htaccess_end, '#') . '$#m', $nginx), []) + [1 => ''];
            $nginx         = trim(wlm_trim($start) . "\n" . wlm_trim($end));

            $site_host = preg_quote($site_host);
            if (! $remove) {
                // Generate ignore list.
                $ignorelist = wlm_trim(wishlistmember_instance()->get_option('file_protection_ignore'));
                if (empty($ignorelist)) {
                    $ignorelist = 'jpg,jpeg,png,gif,bmp,css,js';
                }

                $ignorelist = explode(',', $ignorelist);
                foreach ($ignorelist as $i => $ext) {
                    $ext              = preg_replace('/[^A-Za-z0-9]/', '', wlm_trim($ext));
                    $ignorelist[ $i ] = $ext;
                }
                wishlistmember_instance()->save_option('file_protection_ignore', implode(', ', $ignorelist));
                $ignorelist = implode('|', $ignorelist);

                // Apache - prepare .htaccess rules.
                $siteurl   = wp_parse_url(home_url('/index.php'), PHP_URL_PATH);
                $htaccess .= "\n{$htaccess_start}\nRewriteEngine on\nRewriteCond %{HTTP_HOST} ^{$site_host}\$ [NC]\nRewriteCond %{REQUEST_URI} !\.({$ignorelist})\$ [NC]\nRewriteRule ^(.*)\$ {$siteurl}?wlmfile=\$1 [L]\n{$htaccess_end}";

                // Nginx - prepare config rules.
                $upload_dir = wp_upload_dir();
                $upload_dir = wp_parse_url($upload_dir['baseurl'] . '/', PHP_URL_PATH);

                $nginx_header = "# Include this file in your site configuration's server {} block";
                $nginx        = "{$nginx_header}\n" . trim(str_replace($nginx_header, '', $nginx));

                $nginx .= sprintf("\n%4\$s\nlocation ~ %1\$s.+?\.(%2\$s)\$ {}\nlocation %1\$s {\n\trewrite ^(.*)\$ %3\$s/index.php?wlmfile=\$1 break;\n}\n%5\$s", $upload_dir, $ignorelist, home_url(), $htaccess_start, $htaccess_end);
            }

            $htaccess = preg_replace('#^\h*for ' . $site_host . '\h*$#m', '', $htaccess);
            $nginx    = preg_replace('#^\h*for ' . $site_host . '\h*$#m', '', $nginx);

            // Write it.
            // Apache - .htaccess.
            file_put_contents($htaccess_file, wlm_trim($htaccess) . "\n");

            // Nginx - config.
            file_put_contents($nginx_file, wlm_trim($nginx) . "\n");
        }
    }

    /**
     * Edit attachment hook
     *
     * @wp-hook edit_attachment
     * @param   integer $attachment_id Attachment ID.
     */
    public function edit_attachment($attachment_id)
    {
        wishlistmember_instance()->add_attachment($attachment_id, true);
    }

    /**
     * Add attachment hook
     *
     * @wp-hook add_attachment
     * @param   integer $attachment_id Attachment ID.
     * @param   boolean $editing       True if editing. Default false.
     */
    public function add_attachment($attachment_id, $editing = false)
    {
        // Short circuit if filter returns false.
        $obj = apply_filters('wishlistmember_pre_add_attachment', get_post($attachment_id));
        if (false === $obj) {
            return;
        }

        $pathtouploads = get_bloginfo('url') . '/' . wishlistmember_instance()->wp_upload_path_relative . '/';
        $obj           = get_post($attachment_id);

        $attachments = wishlistmember_instance()->get_option('AttachmentsData');
        if (empty($attachments)) {
            $attachments = null;
        }
        $sizes = ['thumbnail', 'medium', 'large', 'full'];

        $attachments[ str_replace($pathtouploads, '', wp_get_attachment_url($obj->ID)) ] = [
            'ID'          => $obj->ID,
            'post_parent' => $obj->post_parent,
            'mime'        => $obj->post_mime_type,
        ];
        foreach ((array) $sizes as $size) {
            list($x) = wp_get_attachment_image_src($obj->ID, $size);
            if ($x) {
                $attachments[ str_replace($pathtouploads, '', $x) ] = [
                    'ID'          => $obj->ID,
                    'post_parent' => $obj->post_parent,
                    'mime'        => $obj->post_mime_type,
                ];
            }
        }
        wishlistmember_instance()->save_option('AttachmentsHash', '');
        wishlistmember_instance()->save_option('AttachmentsData', $attachments);

        if (! $editing || ( $editing && wishlistmember_instance()->special_content_level($attachment_id, 'Inherit') )) {
            wishlistmember_instance()->inherit_protection($attachment_id);
        }
    }

    /**
     * Delete attachment hook
     *
     * @wp-hook delete_attachment
     * @param   integer $attachment_id Attachment ID.
     */
    public function delete_attachment($attachment_id)
    {

        $pathtouploads = get_bloginfo('url') . '/' . wishlistmember_instance()->wp_upload_path_relative . '/';
        $obj           = get_post($attachment_id);

        $attachments = wishlistmember_instance()->get_option('AttachmentsData');
        $sizes       = ['thumbnail', 'medium', 'large', 'full'];

        if (isset($attachments[ str_replace($pathtouploads, '', wp_get_attachment_url($obj->ID)) ])) {
            unset($attachments[ str_replace($pathtouploads, '', wp_get_attachment_url($obj->ID)) ]);
        }

        foreach ((array) $sizes as $size) {
            list($x) = wp_get_attachment_image_src($obj->ID, $size);
            if ($x) {
                if (isset($attachments[ str_replace($pathtouploads, '', $x) ])) {
                    unset($attachments[ str_replace($pathtouploads, '', $x) ]);
                }
            }
        }

        wishlistmember_instance()->save_option('AttachmentsHash', '');
        wishlistmember_instance()->save_option('AttachmentsData', $attachments);
    }

    /**
     * Loads all attachments from the database
     * and saves it using file_attachments method
     */
    public function file_protect_load_attachments()
    {
        // Bug fix.  get_posts do not return all posts for none admin user.
        if (current_user_can('manage_options')) {
            // Admin user.
            $pathtouploads   = get_bloginfo('url') . '/' . wishlistmember_instance()->wp_upload_path_relative . '/';
            $objs            = get_posts('post_type=attachment&post_status=inherit&numberposts=1000000&suppress_filters=false');
            $objmd5          = md5(serialize($objs));
            $chk_attachments = wishlistmember_instance()->get_option('AttachmentsData');
            $rebuild         = empty($chk_attachments) ? 'YES' : '';
            if (wishlistmember_instance()->get_option('AttachmentsHash') !== $objmd5 || 'YES' === $rebuild) {
                $attachments = [];
                $sizes       = ['thumbnail', 'medium', 'large', 'full'];
                foreach ((array) $objs as $obj) {
                    $attachments[ str_replace($pathtouploads, '', wp_get_attachment_url($obj->ID)) ] = (object) [
                        'ID'          => $obj->ID,
                        'post_parent' => $obj->post_parent,
                        'mime'        => $obj->post_mime_type,
                    ];

                    foreach ((array) $sizes as $size) {
                        list($x) = wp_get_attachment_image_src($obj->ID, $size);
                        if ($x) {
                            $attachments[ str_replace($pathtouploads, '', $x) ] = (object) [
                                'ID'          => $obj->ID,
                                'post_parent' => $obj->post_parent,
                                'mime'        => $obj->post_mime_type,
                            ];
                        }
                    }
                }
                wishlistmember_instance()->save_option('AttachmentsHash', $objmd5);
                wishlistmember_instance()->save_option('AttachmentsData', $attachments);
            } else {
                // Admin user. nothing is changed.
                $attachments = wishlistmember_instance()->get_option('AttachmentsData');
            }
        } else {
                // None admin user.
                $attachments = wishlistmember_instance()->get_option('AttachmentsData');
        }

        // Attachments.
        wishlistmember_instance()->file_attachments($attachments);
    }

    /**
     * Saves and Returns File Attachments
     *
     * @param  array $attachments Array of attachments to save.
     * @return array             Array of attachments
     */
    public function file_attachments($attachments = null)
    {
        static $a = [];
        if (! is_null($attachments)) {
            $a = $attachments;
        }
        return $a;
    }

    /**
     * Download a big file
     *
     * @param string  $file           Full path to file.
     * @param boolean $force_download True to force download. Default false.
     */
    public function download($file, $force_download = false)
    {
        @@ini_set('zlib.output_compression', 'Off');

        // Prevent ../ paths.
        if (preg_match('/\.\.\//', $file)) {
            header('HTTP/1.0 403 Forbidden');
            exit;
        }

        $len            = filesize($file);
        $filename       = basename($file);
        $file_extension = strtolower(substr(strrchr($filename, '.'), 1));

        // Determine correct MIME type.
        $ctype = wishlistmember_instance()->get_mime_type($filename);

        session_write_close();
        $a_header   = [];
        $a_header[] = 'Cache-Control: no-cache, must-revalidate'; // HTTP/1.1.
        $a_header[] = 'Expires: Sat, 26 Jul 1997 05:00:00 GMT'; // Date in the past.
        // Use the switch-generated Content-Type.
        $a_header[] = "Content-Type: $ctype";

        // Accounts for IE 11 - User Agent has Changed.
        if (strstr(wlm_server_data()['HTTP_USER_AGENT'], 'MSIE') || strstr(wlm_server_data()['HTTP_USER_AGENT'], ' rv:11.')) {
            /*
             * workaround for IE filename bug with multiple periods / multiple dots in filename
             * that adds square brackets to filename - eg. setup.abc.exe becomes setup[1].abc.exe
             */
            $iefilename = preg_replace('/\./', '%2e', $filename, substr_count($filename, '.') - 1);

            if ($force_download) {
                $a_header[] = "Content-Disposition: attachment; filename=\"$iefilename\"";
            } else {
                $a_header[] = "Content-Disposition:  filename=\"$iefilename\"";
            }
        } elseif ($force_download) {
                $a_header[] = "Content-Disposition: attachment; filename=\"$filename\"";
        } else {
            $a_header[] = "Content-Disposition:   filename=\"$filename\"";
        }

        $a_header[] = 'Accept-Ranges: bytes';

        $size = filesize($file);

        while (ob_get_level()) {
            ob_end_clean();
        }

        // If it's a negative number, then it can't be handled by this system!
        if ($size < 0) {
            header('HTTP/1.1 416 Requested Range Not Satisfiable');
            header('Content-Range: bytes */' . $size); // Required in 416.
            echo '<pre>File Too Big: Invalid File Length (' . (int) $size . ')</pre>';
            exit;
        }

        // Multipart-download and download resuming support.
        if (isset(wlm_server_data()['HTTP_RANGE'])) {
            list($a, $range)         = explode('=', wlm_server_data()['HTTP_RANGE'], 2);
            list($range)             = explode(',', $range, 2);
            list($range, $range_end) = explode('-', $range);
            $range                   = intval($range);
            if (! $range_end) {
                $range_end = $size - 1;
            } else {
                $range_end = intval($range_end);
            }
            $new_length = $range_end - $range + 1;
            $a_header[] = 'HTTP/1.1 206 Partial Content';
            $a_header[] = "Content-Length: $new_length";
            $a_header[] = "Content-Range: bytes $range-$range_end/$size";
        } else {
            $new_length = $size;
            $a_header[] = 'Content-Length: ' . $size;
        }

        $partial = isset($partial) ? $partial : '';
        // Execute Actions related to this event... Allow for Header Changes!
        do_action(
            'wishlistmember_download_folder_action',
            $file,
            [
                'header'        => &$a_header,
                'forcedownload' => $force_download,
                'partial'       => $partial,
                'this'          => wishlistmember_instance(),
                'debug'         => false,
            ]
        );

        // Echo the header()
        // Publish all the Header details...
        // NOTE: MUST be done before ob_clean & flush!
        foreach ($a_header as $header) {
            header($header);
        }

        @ob_clean();
        @flush();

        // Output the file itself.
        $chunksize      = $new_length;
        $max_chunk_size = 10 * ( 1024 * 1024 );  // 1 MB chunksize
        if ($new_length > $max_chunk_size) {
            $chunksize = $max_chunk_size;
        }
        $bytes_sent = 0;
        $file       = fopen($file, 'r');
        if ($file) {
            if (isset(wlm_server_data()['HTTP_RANGE'])) {
                fseek($file, $range);
            }
            while (! feof($file) && ! connection_aborted() && $bytes_sent < $new_length) {
                $bytes_sent += stream_copy_to_stream($file, WLM_STDOUT, $chunksize);
                flush();
            }
            fclose($file);
        } else {
            die('Error - can not open file.');
        }
    }

    /**
     * Migrate file protection settings to wlm_contentlevels
     */
    public function migrate_file_protection()
    {
        if (wishlistmember_instance()->get_option('file_protection_migrated') < 2) {
            $file_not_inherit = (array) wishlistmember_instance()->get_option('FileNotInherit');
            $file_protect     = (array) wishlistmember_instance()->get_option('FileProtect');

            wlm_set_time_limit(0);

            $data             = [
                'post_type'   => 'attachment',
                'numberposts' => -1,
                'fields'      => 'id=>parent',
            ];
            $file_attachments = get_posts($data);

            $api_queue = new \WishListMember\API_Queue();

            foreach ($file_attachments as $file_attachment_id => $file_attachment_parent) {
                $levels = [];
                if (! in_array($file_attachment_id, $file_not_inherit) && $file_attachment_parent) {
                    $api_queue->add_queue('file_protect_migrate', serialize(['inherit', $file_attachment_id]));
                } else {
                    $api_queue->add_queue('file_protect_migrate', serialize(['set', $file_attachment_id]));
                }
            }
            wishlistmember_instance()->save_option('file_protection_migrated', 2);
        }
    }

    /**
     * Schedule the loading of attachments
     */
    public function schedule_reload_attachments()
    {
        wp_schedule_single_event(time(), 'wishlistmember_attachments_load');
        spawn_cron(time());
    }

    /**
     * Load the attachments
     */
    public function reload_attachments()
    {
        wishlistmember_instance()->file_protect_load_attachments();
    }

    /**
     * Migrate file protection data
     */
    public function run_file_protect_migration()
    {
        wlm_set_time_limit(0);
        $old_protect = (array) wishlistmember_instance()->get_option('FileProtect');
        $api_queue   = new \WishListMember\API_Queue();
        $queue       = $api_queue->get_queue('file_protect_migrate');
        foreach ($queue as $q) {
            $v = maybe_unserialize($q->value);
            if (is_array($v) && 2 === count($v)) {
                list($action, $file_attachment_id) = $v;
                switch ($action) {
                    case 'inherit':
                        wishlistmember_instance()->inherit_protection($file_attachment_id);
                        break;
                    case 'set':
                        $levels = [];
                        foreach (array_keys($old_protect) as $level) {
                            if (in_array($file_attachment_id, (array) $old_protect[ $level ])) {
                                if ('Protection' === $level) {
                                    wishlistmember_instance()->protect($file_attachment_id, 'Y');
                                } else {
                                    $levels[] = $level;
                                }
                            }
                        }
                        wishlistmember_instance()->set_content_levels('attachment', $file_attachment_id, $levels);
                        break;
                }
            }
            $api_queue->delete_queue($q->ID);
        }
    }
}

// Register hooks.
add_action(
    'wishlistmember_register_hooks',
    function ($wlm) {
        add_action('add_attachment', [$wlm, 'add_attachment']);
        add_action('clean_attachment_cache', [$wlm, 'edit_attachment']);
        add_action('delete_attachment', [$wlm, 'delete_attachment']);
        add_action('edit_attachment', [$wlm, 'edit_attachment']);
        add_action('wishlistmember_migrate_file_protection', [$wlm, 'run_file_protect_migration']);
    }
);
