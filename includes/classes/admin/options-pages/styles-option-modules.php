<?php

// Register "Module" field group
acf_add_local_field_group(
    array(
        'key'                   => 'group_styles_modules',
        'title'                 => 'Modules',
        'fields'                => array(
            array(
                'key'                        => 'field_modules_message',
                'label'                      => '',
                'name'                       => '',
                'type'                       => 'message',
                'instructions'               => '',
                'required'                   => 0,
                'conditional_logic'          => 0,
                'wrapper'                    => array(
                    'width' => '',
                    'class' => '',
                    'id'    => '',
                ),
                'acfe_save_meta'             => 0,
                'message'                    => 'You can enabled or disabled available modules in this tab.',
                'new_lines'                  => 'wpautop',
                'esc_html'                   => 0,
                'acfe_field_group_condition' => 0,
            ),
            array(
                'key'                 => 'field_pip_modules',
                'label'               => '',
                'name'                => 'pip_modules',
                'type'                => 'group',
                'instructions'        => '',
                'required'            => 0,
                'conditional_logic'   => 0,
                'wrapper'             => array(
                    'width' => '',
                    'class' => '',
                    'id'    => '',
                ),
                'acfe_permissions'    => '',
                'layout'              => 'row',
                'acfe_seamless_style' => 0,
                'acfe_group_modal'    => 0,
                'sub_fields'          => array(
                    array(
                        'key'               => 'field_module_tailwind',
                        'label'             => 'TailwindCSS',
                        'name'              => 'tailwind',
                        'type'              => 'true_false',
                        'instructions'      => 'Activate TailwindCSS module.<br>You will be able to compile styles through Pilo\'Press API or enable local compilation.',
                        'required'          => 0,
                        'conditional_logic' => 0,
                        'wrapper'           => array(
                            'width' => '',
                            'class' => '',
                            'id'    => '',
                        ),
                        'acfe_permissions'  => '',
                        'message'           => '',
                        'default_value'     => 1,
                        'ui'                => 1,
                        'ui_on_text'        => '',
                        'ui_off_text'       => '',
                    ),
                    array(
                        'key'               => 'field_module_pilopress_api',
                        'label'             => 'Compile TailwindCSS using Pilo\'Press remote API?',
                        'name'              => 'use_pilopress_api',
                        'type'              => 'true_false',
                        'instructions'      => 'It will generate a new TailwindCSS build everytime you click "Update & compile" button. No need to install / use CLI to update TailwindCSS build',
                        'required'          => 0,
                        'conditional_logic' => array(
                            array(
                                array(
                                    'field'    => 'field_module_tailwind',
                                    'operator' => '==',
                                    'value'    => '1',
                                ),
                            ),
                        ),
                        'wrapper'           => array(
                            'width' => '',
                            'class' => '',
                            'id'    => '',
                        ),
                        'message'           => '',
                        'default_value'     => 1,
                        'ui'                => 1,
                        'ui_on_text'        => '',
                        'ui_off_text'       => '',
                    ),
                    array(
                        'key'                => 'field_module_pilopress_api_version',
                        'label'              => 'TailwindCSS version',
                        'name'               => 'tailwindcss_version',
                        'type'               => 'select',
                        'instructions'       => '',
                        'required'           => 0,
                        'conditional_logic'  => array(
                            array(
                                array(
                                    'field'    => 'field_module_tailwind',
                                    'operator' => '==',
                                    'value'    => '1',
                                ),
                                array(
                                    'field'    => 'field_module_pilopress_api',
                                    'operator' => '==',
                                    'value'    => '1',
                                ),
                            ),
                        ),
                        'wrapper'            => array(
                            'width' => '',
                            'class' => '',
                            'id'    => '',
                        ),
                        'choices'            => array(
                            2 => '2.x.x',
                            3 => '3.x.x',
                        ),
                        'default_value'      => 2,
                        'allow_null'         => 0,
                        'multiple'           => 0,
                        'ui'                 => 1,
                        'ajax'               => 0,
                        'return_format'      => 'value',
                        'allow_custom'       => 0,
                        'search_placeholder' => '',
                        'placeholder'        => '',
                    ),
                    array(
                        'key'               => 'field_module_tinymce',
                        'label'             => 'TinyMCE',
                        'name'              => 'tinymce',
                        'type'              => 'true_false',
                        'instructions'      => 'Activate TinyMCE module.<br>Your styles configuration will be available through dropdowns in TinyMCE editors. Compiled styles will be enqueued in editor.',
                        'required'          => 0,
                        'conditional_logic' => 0,
                        'wrapper'           => array(
                            'width' => '',
                            'class' => '',
                            'id'    => '',
                        ),
                        'acfe_permissions'  => '',
                        'message'           => '',
                        'default_value'     => 1,
                        'ui'                => 1,
                        'ui_on_text'        => '',
                        'ui_off_text'       => '',
                    ),
                    array(
                        'key'               => 'field_module_',
                        'label'             => 'AlpineJS',
                        'name'              => 'alpinejs',
                        'type'              => 'true_false',
                        'instructions'      => 'Activate AlpineJS module.<br>It will enqueue AlpineJS and you will be able to use it in your layouts.',
                        'required'          => 0,
                        'conditional_logic' => 0,
                        'wrapper'           => array(
                            'width' => '',
                            'class' => '',
                            'id'    => '',
                        ),
                        'acfe_permissions'  => '',
                        'message'           => '',
                        'default_value'     => 0,
                        'ui'                => 1,
                        'ui_on_text'        => '',
                        'ui_off_text'       => '',
                    ),
                    array(
                        'key'               => 'field_alpinejs_version',
                        'label'             => 'AlpineJS version',
                        'name'              => 'alpinejs_version',
                        'type'              => 'text',
                        'instructions'      => 'See <a href="https://unpkg.com/browse/alpinejs/" target="_blank">unpkg.com</a> for available versions.',
                        'required'          => 0,
                        'conditional_logic' => array(
                            array(
                                array(
                                    'field'    => 'field_module_tailwind',
                                    'operator' => '==',
                                    'value'    => '1',
                                ),
                            ),
                        ),
                        'wrapper'           => array(
                            'width' => '',
                            'class' => '',
                            'id'    => '',
                        ),
                        'acfe_save_meta'    => 0,
                        'default_value'     => '3.8.0',
                        'placeholder'       => '',
                        'prepend'           => '',
                        'append'            => '',
                        'maxlength'         => '',
                    ),
                ),
            ),
        ),
        'location'              => array(
            array(
                array(
                    'param'    => 'options_page',
                    'operator' => '==',
                    'value'    => 'pip_styles_modules',
                ),
            ),
        ),
        'menu_order'            => 0,
        'position'              => 'normal',
        'style'                 => 'seamless',
        'label_placement'       => 'top',
        'instruction_placement' => 'label',
        'hide_on_screen'        => '',
        'active'                => true,
        'description'           => '',
        'show_in_rest'          => 0,
        'acfe_display_title'    => '',
        'acfe_autosync'         => '',
        'acfe_form'             => 0,
        'acfe_meta'             => '',
        'acfe_note'             => '',
        'acfe_categories'       => array(
            'options' => 'Options',
        ),
    )
);
