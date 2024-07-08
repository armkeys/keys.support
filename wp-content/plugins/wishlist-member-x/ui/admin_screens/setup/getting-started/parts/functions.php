<?php

/**
 * Wizard Functions
 *
 * @package WishListMember/Wizard
 */

/**
 * Helper function to generate single-column table
 *
 * @param string $title  Table Title.
 * @param string $header Column Header.
 * @param array  $data   Array of column data.
 */
function wlm_wizard_table($title, $header, $data)
{
    printf('<h3 class="pb-3">%s</h3>', esc_html($title));
    echo '<div class="table-wrapper -special table-responsive"><table class="table table-striped table-condensed table-fixed">';
    printf('<thead><tr class="d-flex"><th class="col-12">%s</th></tr></thead><tbody style="max-height:208px">', esc_html($header));
    $data = array_filter($data);
    if ($data) {
        echo '<tr class="d-flex"><td class="col-12">';
        echo wp_kses(
            implode('</td></tr><tr class="d-flex"><td class="col-12">', $data),
            [
                'tr' => ['class' => true],
                'td' => ['class' => true],
            ]
        );
        echo '</td></tr>';
    }
    echo '</tbody></table></div>';
}
