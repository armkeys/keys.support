<?php

$content_title = '';
$content       = <<<content
<p><strong>Invalid Registration URL</strong></p>
<p>Please contact the site owner.</p>
<p>Error Code: IR002</p>

content;

$content = sprintf($content, get_bloginfo('url'));
