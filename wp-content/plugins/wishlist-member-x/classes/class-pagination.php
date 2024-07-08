<?php

/**
 * Pagination Helper Class file
 *
 * @package WishListMember
 */

namespace WishListMember;

/**
 * Pagination Helper Class
 */
class Pagination
{
    /**
     * Number of items to display
     *
     * @var integer
     */
    public $items;
    /**
     * Number of items to display per page
     *
     * @var integer
     */
    public $per_page;
    /**
     * Number of pages
     *
     * @var integer
     */
    public $pages;
    /**
     * Current page
     *
     * @var integer
     */
    public $current;
    /**
     * Page offset variable to use for links generated.
     *
     * @var string
     */
    public $variable;
    /**
     * Base URL
     *
     * @var string
     */
    public $url;

    /**
     * Quick jump URL
     *
     * @var string
     */
    public $quickjump_url;
    /**
     * From
     *
     * @var integer
     */
    public $from;
    /**
     * To
     *
     * @var integer
     */
    public $to;
    /**
     * Previous
     *
     * @var integer
     */
    public $prev;
    /**
     * Next
     *
     * @var integer
     */
    public $next;
    /**
     * Per page options
     *
     * @var array
     */
    public $per_page_options;

    /**
     * Pagination Constructor
     *
     * @param integer $items            Number of items.
     * @param integer $per_page         Number of items per page.
     * @param integer $current          Current page.
     * @param string  $variable         Page offset variable to use for links generated.
     * @param string  $url              Base URL.
     * @param array   $per_page_options (optional) Per page options. Default [ 10, 25, 50, 100, 250, 500 ].
     */
    public function __construct($items, $per_page, $current, $variable, $url, $per_page_options = [])
    {
        $this->items    = $items;
        $this->per_page = (int) $per_page ? (int) $per_page : PHP_INT_MAX;
        $this->pages    = ceil($items / $this->per_page);
        $current        = max((int) $current, 1);
        $this->current  = $current > $this->pages ? $this->pages : $current;

        $this->variable      = $variable;
        $this->url           = $url;
        $this->quickjump_url = add_query_arg('offset', '%d', $url);

        $this->from = ( $this->current - 1 ) * $this->per_page + 1;
        if ($this->from > $items) {
            $this->from = $items;
        }

        $to       = $this->from + $this->per_page - 1;
        $this->to = $to > $items ? $items : $to;

        $this->prev = $current - 1;
        if ($this->prev < 1) {
            $this->prev = $this->pages; // Rotate.
        }

        $this->next = $current + 1;
        if ($this->next > $this->pages) {
            $this->next = 1; // Rotate.
        }

        $this->per_page_options = ! is_array($per_page_options) ? [10, 25, 50, 100, 200, __('Show All', 'wishlist-member')] : $per_page_options;
    }

    /**
     * Get Pagination HTML
     *
     * @return string HTML Markup
     */
    public function get_html()
    {
        if ($this->items < 1) {
            return '<div class="pagination pull-right"></div>';
        }
        if ($this->pages > 1) {
            $markup = sprintf(
                '
				<div class="pagination pull-right">
					<div class="input-group">
						<div class="input-group-prepend">
								%s
								%s
						</div>
						%s
						<div class="input-group-append">
							<span class="mt-9px"> of %d</span>
							%s
						</div>

					</div>
				</div>
			',
                $this->get_range_markup(),
                $this->get_prev_link_markup(),
                $this->get_input_markup(),
                $this->pages,
                $this->get_next_link_markup()
            );
        } else {
            $markup = sprintf(
                '
				<div class="pagination pull-right">
					<div class="input-group">
						<div class="input-group-prepend">
								%s
						</div>
					</div>
				</div>
			',
                $this->get_range_markup()
            );
        }
        return $markup;
    }

    public function kses_allowed_html()
    {
        return [
            'a' => [
                'aria-expanded' => true,
                'aria-haspopup' => true,
                'class' => true,
                'data-toggle' => true,
                'disabled' => true,
                'href' => true,
                'id' => true,
                'role' => true,
                'target' => true,
            ],
            'div' => [
                'class' => true,
                'role' => true,
            ],
            'i' => [
                'class' => true,
            ],
            'input' => [
                'class' => true,
                'data-link' => true,
                'data-orig' => true,
                'data-pages' => true,
                'type' => true,
                'value' => true,
            ],
            'span' => [
                'class' => true,
            ],
            'ul' => [
                'aria-labelledby' => true,
                'class' => true,
                'id' => true,
            ],
        ];
    }

    /**
     * Prints pagination HTML
     */
    public function print_html()
    {
        echo wp_kses($this->get_html(), $this->kses_allowed_html());
    }

    /**
     * Get previous link
     *
     * @return string
     */
    private function get_prev_link()
    {
        return $this->prev ? add_query_arg($this->variable, $this->prev, $this->url) : remove_query_arg($this->variable, $this->url);
    }

    /**
     * Get next link
     *
     * @return string
     */
    private function get_next_link()
    {
        return $this->next ? add_query_arg($this->variable, $this->next, $this->url) : remove_query_arg($this->variable, $this->url);
    }

    /**
     * Get range markup
     *
     * @param  string $per_page_query_var Per page query variable.
     * @return string
     */
    private function get_range_markup($per_page_query_var = 'howmany')
    {
        $per_page_options_markup = '';
        foreach ($this->per_page_options as $x) {
            $per_page_options_markup .= sprintf('<a class="dropdown-item" target="_parent" href="%s">%s</a>', add_query_arg($per_page_query_var, $x, $this->url), $x);
        }
        $markup = '<span class="text-muted pr-2">
			<div role="presentation" class="dropdown mt-9px">
				<a href="#" class="dropdown-toggle" id="drop-page" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">%d - %d</a> of %d
				<ul class="dropdown-menu" id="menu1" aria-labelledby="drop-page">%s</ul>
			</div>
		</span>';
        $markup = sprintf($markup, $this->from, $this->to, $this->items, $per_page_options_markup);
        return $markup;
    }

    /**
     * Get previous link markup
     *
     * @return string
     */
    private function get_prev_link_markup()
    {
        if ($this->current > 1) {
            $markup = sprintf('<a href="%s" class="mt-6px"><i class="wlm-icons md-26">first_page</i></a>', remove_query_arg($this->variable, $this->url));
        } else {
            $markup = '<a class="mt-6px text-muted disabled" disabled="disabled"><i class="wlm-icons md-26">first_page</i></a>';
        }

        $markup .= sprintf('<a href="%s" class="mt-6px"><i class="wlm-icons md-26">keyboard_arrow_left</i></a>', $this->get_prev_link());

        return $markup;
    }

    /**
     * Get next link markup
     *
     * @return string
     */
    private function get_next_link_markup()
    {
        $markup = sprintf('<a href="%s" class="mt-6px"><i class="wlm-icons md-26">keyboard_arrow_right</i></a>', $this->get_next_link());

        if ($this->current < $this->pages) {
            $markup .= sprintf('<a href="%s" class="mt-6px"><i class="wlm-icons md-26">last_page</i></a>', add_query_arg($this->variable, $this->pages, $this->url));
        } else {
            $markup .= '<a class="mt-6px text-muted disabled" disabled="disabled"><i class="wlm-icons md-26">last_page</i></a>';
        }

        return $markup;
    }

    /**
     * Get quick jump input field markup
     *
     * @return string
     */
    private function get_input_markup()
    {
        $markup = sprintf('<input type="text" value="%d" data-orig="%d" class="form-control text-center pagination-pagenum" data-pages="%d" data-link="%s">', $this->current, $this->current, $this->pages, $this->quickjump_url);
        return $markup;
    }
}
