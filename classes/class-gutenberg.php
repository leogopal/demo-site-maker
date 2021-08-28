<?php

namespace demo_site_maker\classes;

use demo_site_maker\classes\editor_blocks\Block_Try_Demo;
use demo_site_maker\classes\editor_blocks\Block_Try_Demo_Popup;
use demo_site_maker\classes\editor_blocks\Block_Is_Not_Sandbox;
use demo_site_maker\classes\editor_blocks\Block_Is_Sandbox;
use demo_site_maker\classes\models\Sandbox;

class Gutenberg extends Core
{
    protected static $instance;

    public static function get_instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    static function install()
    {
        $blocksPath = \Demo_Site_Maker::get_plugin_part_path('classes/editor-blocks/');
        Core::include_all($blocksPath);
    }

    public function register_category($categories)
    {
        $categories[] = array(
            'slug' => 'demo-site-maker',
            'title' => __('MotoPress Demo', 'mp-demo')
        );

        return $categories;
    }

    public function enqueue_scripts()
    {
        $isDebug = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG;
        $blocksJs = $isDebug ? 'editor-blocks.js' : 'editor-blocks.min.js';
        $editorCss = $isDebug ? 'editor-styles.css' : 'editor-styles.min.css';
        $version = Core::get_version();

        wp_enqueue_script('mp-demo-editor-blocks', \Demo_Site_Maker::get_plugin_url("assets/js/{$blocksJs}"), array('wp-i18n', 'wp-editor', 'wp-element', 'wp-blocks', 'wp-components', 'wp-compose'), $version);
        wp_enqueue_style('mp-demo-editor-styles', \Demo_Site_Maker::get_plugin_url("assets/css/{$editorCss}"), array('wp-edit-blocks'), $version);

        $isSandbox = Sandbox::get_instance()->is_sandbox();
        $blogs = !$isSandbox ? $this->get_blogs() : array();

        wp_localize_script('mp-demo-editor-blocks', 'MP_Demo_Data', array(
            'blogs' => $blogs,
            'default_loader' => \Demo_Site_Maker::get_plugin_url('assets/images/loader.gif'),
            'is_sandbox' => (int)$isSandbox
        ));
    }

    public function register_blocks()
    {
        register_block_type(
            'demo-site-maker/is-sandbox',
            array(
                'editor_script' => 'mp-demo-editor-blocks',
                'editor_style' => 'mp-demo-editor-styles',
                'render_callback' => array(Block_Is_Sandbox::get_instance(), 'render'),
                'attributes' => array(
                    'content' => array('type' => 'string', 'default' => '')
                )
            )
        );

        register_block_type(
            'demo-site-maker/is-not-sandbox',
            array(
                'editor_script' => 'mp-demo-editor-blocks',
                'editor_style' => 'mp-demo-editor-styles',
                'render_callback' => array(Block_Is_Not_Sandbox::get_instance(), 'render'),
                'attributes' => array(
                    'content' => array('type' => 'string', 'default' => '')
                )
            )
        );

        register_block_type(
            'demo-site-maker/try-demo',
            array(
                'editor_script' => 'mp-demo-editor-blocks',
                'editor_style' => 'mp-demo-editor-styles',
                'render_callback' => array(Block_Try_Demo::get_instance(), 'render'),
                'attributes' => array(
                    'title' => array('type' => 'string', 'default' => __('To create your demo website provide the following data', 'mp-demo')),
                    'label' => array('type' => 'string', 'default' => __('Your email:', 'mp-demo')),
                    'placeholder' => array('type' => 'string', 'default' => 'example@mail.com'),
                    'content' => array('type' => 'string', 'default' => __('An activation email will be sent to this email address. After the confirmation you will be redirected to WordPress Dashboard.', 'mp-demo')),
                    'select_label' => array('type' => 'string', 'default' => ''),
                    'source_id' => array('type' => 'array', 'default' => array(1), 'items' => array('type' => 'number')),
                    'captcha' => array('type' => 'boolean', 'default' => false),
                    'submit_btn' => array('type' => 'string', 'default' => __('Submit', 'mp-demo')),
                    'loader_url' => array('type' => 'string', 'default' => \Demo_Site_Maker::get_plugin_url('assets/images/loader.gif')),
                    'success' => array('type' => 'string', 'default' => __('An activation email was sent to your email address.', 'mp-demo')),
                    'fail' => array('type' => 'string', 'default' => __('An error has occurred. Please notify the website Administrator.', 'mp-demo')),
                    'align' => array('type' => 'string', 'default' => ''),
                    'className' => array('type' => 'string', 'default' => '')
                )
            )
        );

        register_block_type(
            'demo-site-maker/try-demo-popup',
            array(
                'editor_script' => 'mp-demo-editor-blocks',
                'editor_style' => 'mp-demo-editor-styles',
                'render_callback' => array(Block_Try_Demo_Popup::get_instance(), 'render'),
                'attributes' => array(
                    'launch_btn' => array('type' => 'string', 'default' => __('Launch demo', 'mp-demo')),
                    'title' => array('type' => 'string', 'default' => __('To create your demo website provide the following data', 'mp-demo')),
                    'label' => array('type' => 'string', 'default' => __('Your email:', 'mp-demo')),
                    'placeholder' => array('type' => 'string', 'default' => 'example@mail.com'),
                    'content' => array('type' => 'string', 'default' => __('An activation email will be sent to this email address. After the confirmation you will be redirected to WordPress Dashboard.', 'mp-demo')),
                    'select_label' => array('type' => 'string', 'default' => ''),
                    'source_id' => array('type' => 'array', 'default' => array(1), 'items' => array('type' => 'number')),
                    'captcha' => array('type' => 'boolean', 'default' => false),
                    'submit_btn' => array('type' => 'string', 'default' => __('Submit', 'mp-demo')),
                    'loader_url' => array('type' => 'string', 'default' => \Demo_Site_Maker::get_plugin_url('assets/images/loader.gif')),
                    'success' => array('type' => 'string', 'default' => __('An activation email was sent to your email address.', 'mp-demo')),
                    'fail' => array('type' => 'string', 'default' => __('An error has occurred. Please notify the website Administrator.', 'mp-demo')),
                    'className' => array('type' => 'string', 'default' => '')
                )
            )
        );
    }

    protected function get_blogs()
    {
        $sites = Core::get_sites(array('public' => 1));

        $blogs = array_map(function ($site) {
            return array(
                'value' => $site['blog_id'],
                'label' => get_blog_option($site['blog_id'], 'blogname')
            );
        }, $sites);

        return $blogs;
    }
}
