<?php
    $login_styling_custom_template = $this->get_option('login_styling_custom_template');

    $style_files = glob($this->plugin_dir3 . '/assets/templates/login-styles/*', GLOB_ONLYDIR);
    $styles      = [];
foreach ($style_files as $style) {
    if (! file_exists($style . '/style.css')) {
        continue;
    }
    $code = file_get_contents($style . '/style.css');
    if (preg_match('#/\*\s+name:\s*(.+)?\*/#i', $code, $match)) {
        $styles[ basename($style) ] = [
            'name' => wlm_trim($match[1]),
            'path' => $style,
        ];
    }
}
?>
<div id="template-list" class="row mb-4 h-100" style="overflow-y: scroll;">
    <?php foreach ($styles as $template => $meta) : ?>
        <?php
            $selected = $template == $login_styling_custom_template ? 'chosen-template active-template' : '';
        ?>
        <div class="col-sm-6 col-md-4 col-lg-3">
            <div class="mt-3 mb-1 template-choices <?php echo esc_attr($selected); ?>" data-template-name="<?php echo esc_attr($template); ?>">
                <img class="img-fluid" src="<?php echo esc_attr(plugins_url($template . '/screenshot.jpg', $meta['path'])); ?>" data-name="<?php echo esc_attr($styles[ $template ]['name']); ?>">
                <span class="marker text-center"><i class="wlm-icons md-18">check</i></span>
                <span class="template-name small text-center"><?php echo esc_html($meta['name']); ?></span>
            </div>
        </div>
    <?php endforeach; ?>
    <input type="hidden" name="login_styling_custom_template" value="<?php echo esc_attr($login_styling_custom_template); ?>">
</div>
