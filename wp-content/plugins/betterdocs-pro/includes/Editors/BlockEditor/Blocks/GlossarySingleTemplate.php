<?php

namespace WPDeveloper\BetterDocsPro\Editors\BlockEditor\Blocks;

use WPDeveloper\BetterDocs\Editors\BlockEditor\Block;
use WPDeveloper\BetterDocs\Utils\Helper;


class GlossarySingleTemplate extends Block
{

    public $is_pro = true;



    /**
     * unique name of block
     * @return string
     */
    public function get_name()
    {
        return 'glossary-single-template';
    }

    protected $editor_styles = [
        'betterdocs-encyclopedia'
    ];

    protected $frontend_styles = [
        'betterdocs-encyclopedia'
    ];

    public function render($attributes, $content)
    {

        $view_object             = betterdocs()->views;
        $docs_by_letter = Helper::docs_sort_by_letter();

        ?>
        <div class="betterdocs-wrapper">
            <?php if (is_tax('glossaries')) : ?>
                <?php betterdocs()->template_helper->search(); ?>
                <?php $current_letter = strtoupper(substr(get_the_title(), 0, 1)); ?>
                <?php $alphabet_list_style = betterdocs()->customizer->defaults->get('encyclopedia_alphabet_list_style', 'box'); ?>
                <?php
                            betterdocs_pro()->views->get('layouts/encyclopedia/alphabets', [
                                'docs_by_letter' => $docs_by_letter,
                                'current_letter' => $current_letter,
                                'alphabet_list_style' => $alphabet_list_style,

                            ]);

                            ?>
            <?php endif; ?>
            <div id="betterdocs-single-main" class="glossary-single-content-area betterdocs-content-area docs-content-full-main">
                <div class="betterdocs-content-inner-area">
                    <?php

                            if (is_tax('glossaries')) {
                                $view_object->get('templates/headers/layout-7');
                                $view_object->get('templates/glossary-single/layout-1');
                            } else { ?>
                        <div class="glossary-template-archive-title">
                            <h1><?php the_archive_title(); ?></h1>

                        </div>
                        <div class="beetterdocs-glossary-archive-container">

                            <?php while (have_posts()) : the_post();
                                            ?>

                                <div class="beetterdocs-glossary-archive-content">
                                    <header class="betterdocs-entry-header">
                                        <div class="docs-single-title">
                                            <a href="<?php the_permalink(); ?>">
                                                <?php
                                                                $view_object->get(
                                                                    'templates/parts/title',
                                                                    [
                                                                        'tag' => betterdocs()->customizer->defaults->get('betterdocs_post_title_tag')
                                                                    ]
                                                                );
                                                                ?>
                                            </a>
                                            <a href="<?php the_permalink(); ?>"><?php the_post_thumbnail(); ?></a>

                                        </div>
                                    </header> <!-- .entry-header -->
                                    <div class="betterdocs-entry-content">
                                        <?php the_excerpt(); ?>
                                        <a href="<?php the_permalink(); ?>"><?php echo esc_html__('Read More', 'betterdocs-pro'); ?></a>
                                    </div>

                                </div>

                            <?php
                                        endwhile;
                                        ?>
                        </div>
                    <?php
                            }

                            ?>
                </div>
            </div>
        </div> <!-- .betterdocs-single-wraper -->

<?php
    }
}
