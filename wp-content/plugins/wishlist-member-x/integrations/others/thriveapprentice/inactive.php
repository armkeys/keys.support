<div class="row">
    <div class="col">
        <p>
            <?php
            echo wp_kses(
                sprintf(
                    // Translators: 1 - Link to Thrive Apprentice.
                    __('The <a href="%1$s" target="_blank">Thrive Apprentice</a> plugin is required to be installed and activated to use this integration. The integration will be enabled when you install and activate Thrive Apprentice in the WordPress Plugins > Add New section.', 'wishlist-member'),
                    'https://thrivethemes.com/apprentice'
                ),
                [
                    'a' => [
                        'href'   => [],
                        'target' => [],
                    ],
                ]
            );
            ?>
        </p>
        <p>
            <?php
            echo wp_kses(
                sprintf(
                    // Translators: 1 - Link to knowledgebase article.
                    __('<a href="%1$s" target="_blank">Click Here</a> for additional information.', 'wishlist-member'),
                    'https://wishlistmember.com/docs/thrive-apprentice/',
                ),
                [
                    'a' => [
                        'href'   => [],
                        'target' => [],
                    ],
                ]
            );
            ?>
        </p>
    </div>
</div>
<style>
.integration-toggle-switch[data-provider="thriveapprentice"] .marker {
    background-color: #15C384;
    border-radius: 50%;
    height: 28px;
    width: 28px;
    display: inline-block;
    color: #ffffff;
    padding-top: 3px;
    margin-top: 3px;
}
.-is-inactive .integration-toggle-switch[data-provider="thriveapprentice"] .marker {
    background-color: #cccccc;
}
</style>
<script>
$('.integration-toggle-switch[data-provider="thriveapprentice"]').html('<span class="marker text-center"><i class="wlm-icons md-22">check</i></span>');
</script>
