<?php

$content_title = '';
$content       = <<<content
<p><strong>Registration Error</strong></p>
<p>Your registration failed. Please contact the site owner.</p>
<p>Error Code: IR001</p>

content;

$content = sprintf($content, get_bloginfo('url'));
