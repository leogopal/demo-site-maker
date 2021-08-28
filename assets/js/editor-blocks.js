(function () {
    'use strict';

    var __ = wp.i18n.__,
        registerBlockType = wp.blocks.registerBlockType,
        createElement = wp.element.createElement,
        withInstanceId = wp.compose.withInstanceId;

    var InspectorControls = wp.editor.InspectorControls,
        PanelBody = wp.components.PanelBody,
        TextControl = wp.components.TextControl,
        TextareaControl = wp.components.TextareaControl,
        SelectControl = wp.components.SelectControl,
        ToggleControl = wp.components.ToggleControl,
        BlockControls = wp.editor.BlockControls,
        BlockAlignmentToolbar = wp.editor.BlockAlignmentToolbar,
        Disabled = wp.components.Disabled,
        ServerSideRender = wp.components.ServerSideRender,
        Placeholder = wp.components.Placeholder,
        Dashicon = wp.components.Dashicon,
        PlainText = wp.editor.PlainText;

    registerBlockType(
        'demo-site-maker/is-sandbox',
        {
            title: __('Is Sandbox', 'mp-demo'),
            description: __('This content will be visible in a created sandbox only', 'mp-demo'),
            category: 'demo-site-maker',
            icon: 'visibility',
            supports: {
                customClassName: false
            },
            attributes: {
                content: {type: 'string', default: ''}
            },
            edit: withInstanceId(function (props) {
                var className = 'wp-block-demo-site-maker-is-sandbox';
                var inputId = className + '-' + props.instanceId;

                return createElement(
                    'div',
                    {className: className},
                    [
                        createElement(
                            'label',
                            {
                                htmlFor: inputId,
                                key: 'control-label'
                            },
                            [
                                createElement(
                                    Dashicon,
                                    {
                                        icon: 'visibility',
                                        key: 'control-icon'
                                    }
                                ),
                                __('Is Sandbox', 'mp-demo')
                            ]
                        ),
                        createElement(
                            PlainText,
                            {
                                className: 'input-control',
                                id: inputId,
                                value: props.attributes.content,
                                onChange: function (value) { props.setAttributes({content: value}); },
                                placeholder: __('This content will be visible in a created sandbox only', 'mp-demo'),
                                key: 'content-control'
                            }
                        )
                    ]
                );
            }),
            save: function () {
                return null;
            }
        }
    );

    registerBlockType(
        'demo-site-maker/is-not-sandbox',
        {
            title: __('Is Not Sandbox', 'mp-demo'),
            description: __('Content is not visible in the sandbox', 'mp-demo'),
            category: 'demo-site-maker',
            icon: 'hidden',
            supports: {
                customClassName: false
            },
            attributes: {
                content: {type: 'string', default: ''}
            },
            edit: withInstanceId(function (props) {
                var className = 'wp-block-demo-site-maker-is-not-sandbox';
                var inputId = className + '-' + props.instanceId;

                return createElement(
                    'div',
                    {className: className},
                    [
                        createElement(
                            'label',
                            {
                                htmlFor: inputId,
                                key: 'control-label'
                            },
                            [
                                createElement(
                                    Dashicon,
                                    {
                                        icon: 'hidden',
                                        key: 'control-icon'
                                    }
                                ),
                                __('Is Not Sandbox', 'mp-demo')
                            ]
                        ),
                        createElement(
                            PlainText,
                            {
                                className: 'input-control',
                                id: inputId,
                                value: props.attributes.content,
                                onChange: function (value) { props.setAttributes({content: value}); },
                                placeholder: __('Content is not visible in the sandbox', 'mp-demo'),
                                key: 'content-control'
                            }
                        )
                    ]
                );
            }),
            save: function () {
                return null;
            }
        }
    );

    registerBlockType(
        'demo-site-maker/try-demo',
        {
            title: __('Try Demo', 'mp-demo'),
            category: 'demo-site-maker',
            icon: 'networking',
            supports: {
                customClassName: MP_Demo_Data.is_sandbox == 0
            },
            attributes: {
                title: {type: 'string', default: __('To create your demo website provide the following data', 'mp-demo')},
                label: {type: 'string', default: __('Your email:', 'mp-demo')},
                placeholder: {type: 'string', default: 'example@mail.com'},
                content: {type: 'string', default: __('An activation email will be sent to this email address. After the confirmation you will be redirected to WordPress Dashboard.', 'mp-demo')},
                select_label: {type: 'string', default: ''},
                source_id: {type: 'array', default: [1]},
                captcha: {type: 'boolean', default: false},
                submit_btn: {type: 'string', default: __('Submit', 'mp-demo')},
                loader_url: {type: 'string', default: MP_Demo_Data.default_loader},
                success: {type: 'string', default: __('An activation email was sent to your email address.', 'mp-demo')},
                fail: {type: 'string', default: __('An error has occurred. Please notify the website Administrator.', 'mp-demo')},
                align: {type: 'string', default: ''}
            },
            edit: function (props) {
                if (MP_Demo_Data.is_sandbox == 1) {
                    return createElement(
                        Placeholder,
                        {
                            icon: 'networking',
                            label: __('Try Demo', 'mp-demo'),
                            instructions: __('You are not allowed to edit this block in sandbox mode.', 'mp-demo')
                        }
                    );
                }

                var isSelected = !!props.isSelected;
                var isValid = MP_Demo_Data.blogs.length > 0 && props.attributes.source_id.length > 0;

                return [
                    isSelected && createElement(
                        InspectorControls,
                        {key: 'inspector-controls'},
                        createElement(
                            PanelBody,
                            {title: __('Settings', 'mp-demo')},
                            [
                                createElement(
                                    TextareaControl,
                                    {
                                        label: __('Form Title', 'mp-demo'),
                                        value: props.attributes.title,
                                        onChange: function (value) { props.setAttributes({title: value}); },
                                        key: 'title-control'
                                    }
                                ),
                                createElement(
                                    TextControl,
                                    {
                                        label: __('Label for email', 'mp-demo'),
                                        value: props.attributes.label,
                                        onChange: function (value) { props.setAttributes({label: value}); },
                                        key: 'label-control'
                                    }
                                ),
                                createElement(
                                    TextControl,
                                    {
                                        label: __('Email placeholder', 'mp-demo'),
                                        value: props.attributes.placeholder,
                                        onChange: function (value) { props.setAttributes({placeholder: value}); },
                                        key: 'placeholder-control'
                                    }
                                ),
                                createElement(
                                    TextareaControl,
                                    {
                                        label: __('Description', 'mp-demo'),
                                        help: __('Description under the email field', 'mp-demo'),
                                        value: props.attributes.content,
                                        onChange: function (value) { props.setAttributes({content: value}); },
                                        key: 'content-control'
                                    }
                                ),
                                createElement(
                                    TextControl,
                                    {
                                        label: __('Select label', 'mp-demo'),
                                        help: __('Write a label for drop-down list of the items available for creating sandboxes', 'mp-demo'),
                                        value: props.attributes.select_label,
                                        onChange: function (value) { props.setAttributes({select_label: value}); },
                                        key: 'select_label-control'
                                    }
                                ),
                                createElement(
                                    SelectControl,
                                    {
                                        label: __('Source ID', 'mp-demo'),
                                        help: __('Blog ID to create Demo from, default is 1', 'mp-demo'),
                                        value: props.attributes.source_id,
                                        options: MP_Demo_Data.blogs,
                                        multiple: true,
                                        onChange: function (values) { props.setAttributes({source_id: values}); },
                                        key: 'source_id-control'
                                    }
                                ),
                                createElement(
                                    ToggleControl,
                                    {
                                        label: __('Use reCAPTCHA', 'mp-demo'),
                                        checked: props.attributes.captcha,
                                        onChange: function (value) { props.setAttributes({captcha: value}); },
                                        key: 'captcha-control'
                                    }
                                ),
                                createElement(
                                    TextControl,
                                    {
                                        label: __('Submit button label', 'mp-demo'),
                                        value: props.attributes.submit_btn,
                                        onChange: function (value) { props.setAttributes({submit_btn: value}); },
                                        key: 'submit_btn-control'
                                    }
                                ),
                                createElement(
                                    TextControl,
                                    {
                                        label: __('Loader URL', 'mp-demo'),
                                        value: props.attributes.loader_url,
                                        onChange: function (value) { props.setAttributes({loader_url: value}); },
                                        key: 'loader_url-control'
                                    }
                                ),
                                createElement(
                                    TextareaControl,
                                    {
                                        label: __('Success message', 'mp-demo'),
                                        value: props.attributes.success,
                                        onChange: function (value) { props.setAttributes({success: value}); },
                                        key: 'success-control'
                                    }
                                ),
                                createElement(
                                    TextareaControl,
                                    {
                                        label: __('Fail message', 'mp-demo'),
                                        value: props.attributes.fail,
                                        onChange: function (value) { props.setAttributes({fail: value}); },
                                        key: 'fail-control'
                                    }
                                )
                            ]
                        )
                    ),
                    createElement(
                        BlockControls,
                        {key: 'block-controls'},
                        createElement(
                            BlockAlignmentToolbar,
                            {
                                value: props.attributes.align,
                                controls: ['wide', 'full'],
                                onChange: function (value) { props.setAttributes({align: value}); }
                            }
                        )
                    ),
                    isValid && createElement(
                        Disabled,
                        {key: 'server-side-render'},
                        createElement(
                            ServerSideRender,
                            {
                                block: 'demo-site-maker/try-demo',
                                attributes: props.attributes
                            }
                        )
                    ),
                    !isValid && createElement(
                        Placeholder,
                        {
                            icon: 'networking',
                            label: __('Try Demo', 'mp-demo'),
                            key: 'block-placeholder'
                        }
                    )
                ];
            },
            getEditWrapperProps: function (atts) {
                var align = atts.align;

                if (align == 'wide' || align == 'full') {
                    return {'data-align': align};
                }
            },
            save: function () {
                return null;
            }
        }
    );

    registerBlockType(
        'demo-site-maker/try-demo-popup',
        {
            title: __('Try Demo Popup', 'mp-demo'),
            category: 'demo-site-maker',
            icon: 'networking',
            attributes: {
                launch_btn: {type: 'string', default: __('Launch demo', 'mp-demo')},
                title: {type: 'string', default: __('To create your demo website provide the following data', 'mp-demo')},
                label: {type: 'string', default: __('Your email:', 'mp-demo')},
                placeholder: {type: 'string', default: 'example@mail.com'},
                content: {type: 'string', default: __('An activation email will be sent to this email address. After the confirmation you will be redirected to WordPress Dashboard.', 'mp-demo')},
                select_label: {type: 'string', default: ''},
                source_id: {type: 'array', default: [1]},
                captcha: {type: 'boolean', default: false},
                submit_btn: {type: 'string', default: __('Submit', 'mp-demo')},
                loader_url: {type: 'string', default: MP_Demo_Data.default_loader},
                success: {type: 'string', default: __('An activation email was sent to your email address.', 'mp-demo')},
                fail: {type: 'string', default: __('An error has occurred. Please notify the website Administrator.', 'mp-demo')}
            },
            supports: {
                customClassName: MP_Demo_Data.is_sandbox == 0
            },
            edit: function (props) {
                if (MP_Demo_Data.is_sandbox == 1) {
                    return createElement(
                        Placeholder,
                        {
                            icon: 'networking',
                            label: __('Try Demo Popup', 'mp-demo'),
                            instructions: __('You are not allowed to edit this block in sandbox mode.', 'mp-demo')
                        }
                    );
                }

                var isSelected = !!props.isSelected;

                return [
                    isSelected && createElement(
                        InspectorControls,
                        {key: 'inspector-controls'},
                        createElement(
                            PanelBody,
                            {title: __('Settings', 'mp-demo')},
                            [
                                createElement(
                                    TextControl,
                                    {
                                        label: __('Open popup button label', 'mp-demo'),
                                        value: props.attributes.launch_btn,
                                        onChange: function (value) { props.setAttributes({launch_btn: value}); },
                                        key: 'launch_btn-control'
                                    }
                                ),
                                createElement(
                                    TextareaControl,
                                    {
                                        label: __('Form Title', 'mp-demo'),
                                        value: props.attributes.title,
                                        onChange: function (value) { props.setAttributes({title: value}); },
                                        key: 'title-control'
                                    }
                                ),
                                createElement(
                                    TextControl,
                                    {
                                        label: __('Label for email', 'mp-demo'),
                                        value: props.attributes.label,
                                        onChange: function (value) { props.setAttributes({label: value}); },
                                        key: 'label-control'
                                    }
                                ),
                                createElement(
                                    TextControl,
                                    {
                                        label: __('Email placeholder', 'mp-demo'),
                                        value: props.attributes.placeholder,
                                        onChange: function (value) { props.setAttributes({placeholder: value}); },
                                        key: 'placeholder-control'
                                    }
                                ),
                                createElement(
                                    TextareaControl,
                                    {
                                        label: __('Description', 'mp-demo'),
                                        help: __('Description under the email field', 'mp-demo'),
                                        value: props.attributes.content,
                                        onChange: function (value) { props.setAttributes({content: value}); },
                                        key: 'content-control'
                                    }
                                ),
                                createElement(
                                    TextControl,
                                    {
                                        label: __('Select label', 'mp-demo'),
                                        help: __('Write a label for drop-down list of the items available for creating sandboxes', 'mp-demo'),
                                        value: props.attributes.select_label,
                                        onChange: function (value) { props.setAttributes({select_label: value}); },
                                        key: 'select_label-control'
                                    }
                                ),
                                createElement(
                                    SelectControl,
                                    {
                                        label: __('Source ID', 'mp-demo'),
                                        help: __('Blog ID to create Demo from, default is 1', 'mp-demo'),
                                        value: props.attributes.source_id,
                                        options: MP_Demo_Data.blogs,
                                        multiple: true,
                                        onChange: function (values) { props.setAttributes({source_id: values}); },
                                        key: 'source_id-control'
                                    }
                                ),
                                createElement(
                                    ToggleControl,
                                    {
                                        label: __('Use reCAPTCHA', 'mp-demo'),
                                        checked: props.attributes.captcha,
                                        onChange: function (value) { props.setAttributes({captcha: value}); },
                                        key: 'captcha-control'
                                    }
                                ),
                                createElement(
                                    TextControl,
                                    {
                                        label: __('Submit button label', 'mp-demo'),
                                        value: props.attributes.submit_btn,
                                        onChange: function (value) { props.setAttributes({submit_btn: value}); },
                                        key: 'submit_btn-control'
                                    }
                                ),
                                createElement(
                                    TextControl,
                                    {
                                        label: __('Loader URL', 'mp-demo'),
                                        value: props.attributes.loader_url,
                                        onChange: function (value) { props.setAttributes({loader_url: value}); },
                                        key: 'loader_url-control'
                                    }
                                ),
                                createElement(
                                    TextareaControl,
                                    {
                                        label: __('Success message', 'mp-demo'),
                                        value: props.attributes.success,
                                        onChange: function (value) { props.setAttributes({success: value}); },
                                        key: 'success-control'
                                    }
                                ),
                                createElement(
                                    TextareaControl,
                                    {
                                        label: __('Fail message', 'mp-demo'),
                                        value: props.attributes.fail,
                                        onChange: function (value) { props.setAttributes({fail: value}); },
                                        key: 'fail-control'
                                    }
                                )
                            ]
                        )
                    ),
                    createElement(
                        Disabled,
                        {key: 'server-side-render'},
                        createElement(
                            ServerSideRender,
                            {
                                block: 'demo-site-maker/try-demo-popup',
                                attributes: props.attributes
                            }
                        )
                    )
                ];
            },
            save: function () {
                return null;
            }
        }
    );
})();
