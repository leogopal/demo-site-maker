<?php

namespace demo_site_maker\classes\editor_blocks;

use demo_site_maker\classes\shortcodes\Shortcode_Is_Not_Sandbox;
use demo_site_maker\classes\Gutenberg;

class Block_Is_Not_Sandbox extends Gutenberg
{
    protected static $instance;

    public static function get_instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function render($atts)
    {
        return Shortcode_Is_Not_Sandbox::get_instance()->render_shortcode(array(), $atts['content']);
    }
}
