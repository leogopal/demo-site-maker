<?php

namespace demo_site_maker\classes\editor_blocks;

use demo_site_maker\classes\shortcodes\Shortcode_Try_Demo_Popup;
use demo_site_maker\classes\Gutenberg;

class Block_Try_Demo_Popup extends Gutenberg
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
        $atts = $this->shortcode_atts($atts);
        return Shortcode_Try_Demo_Popup::get_instance()->render_shortcode($atts);
    }

    protected function shortcode_atts($atts)
    {
        $atts['wrapper_class'] = trim($atts['className']);

        unset($atts['className']);

        return $atts;
    }
}
