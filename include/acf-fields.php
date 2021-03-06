<?
if( function_exists('acf_add_local_field_group') ):

    acf_add_local_field_group(array(
        'key' => 'group_5ed4de8c40101',
        'title' => 'категории на главной',
        'fields' => array(
            array(
                'key' => 'field_5ed4de98dbab9',
                'label' => 'Категории',
                'name' => 'категории',
                'type' => 'repeater',
                'min' => 3,
                'max' => 3,
                'layout' => 'table',
                'button_label' => '',
                'sub_fields' => array(
                    array(
                        'key' => 'field_5ed4dec2dbaba',
                        'label' => 'Название',
                        'name' => 'название',
                        'type' => 'text',
                    ),
                    array(
                        'key' => 'field_5ed4ded1dbabb',
                        'label' => 'Картинка',
                        'name' => 'картинка',
                        'type' => 'image',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => array(
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ),
                        'return_format' => 'url',
                        'preview_size' => 'thumbnail',
                        'library' => 'all',
                    ),
                    array(
                        'key' => 'field_5ed4dedcdbabc',
                        'label' => 'Ссылка',
                        'name' => 'ссылка',
                        'type' => 'text',
                    ),
                ),
            ),
            array(
                'key' => 'field_5f06ee2c2d58a',
                'label' => 'Адрес Шоу-рум',
                'name' => 'адрес_шоу-рум',
                'type' => 'text',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'default_value' => '',
                'placeholder' => '',
                'prepend' => '',
                'append' => '',
                'maxlength' => '',
            ),
            array(
                'key' => 'field_5f06ee2f2d58b',
                'label' => 'Адрес ФОРМЕННАЯ ОДЕЖДА РЖД',
                'name' => 'адрес_форменная_одежда_ржд',
                'type' => 'text',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'default_value' => '',
                'placeholder' => '',
                'prepend' => '',
                'append' => '',
                'maxlength' => '',
            ),
            array(
                'key' => 'field_5f2bb0d832e09',
                'label' => 'Баннеры на главной',
                'name' => 'home_banners',
                'type' => 'repeater',
                'min' => 0,
                'max' => 0,
                'layout' => 'table',
                'button_label' => '',
                'sub_fields' => array(
                    array(
                        'key' => 'field_5f2bb0f932e0a',
                        'label' => 'Изображение',
                        'name' => 'image',
                        'type' => 'image',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => array(
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ),
                        'return_format' => 'url',
                        'preview_size' => 'thumbnail',
                        'library' => 'all',
                    ),
                ),
            ),
            array(
                'key' => 'field_5f3bc9e74f06d',
                'label' => 'Размеры',
                'name' => 'sizes',
                'type' => 'image',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'return_format' => 'url',
                'preview_size' => 'thumbnail',
                'library' => 'all',
                'min_width' => '',
                'min_height' => '',
                'min_size' => '',
                'max_width' => '',
                'max_height' => '',
                'max_size' => '',
                'mime_types' => '',
            ),
        ),
        'location' => array(
            array(
                array(
                    'param' => 'options_page',
                    'operator' => '==',
                    'value' => 'options',
                ),
            ),
        ),
        'menu_order' => 0,
        'position' => 'normal',
        'style' => 'default',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
        'hide_on_screen' => '',
        'active' => true,
        'description' => '',
    ));

    acf_add_local_field_group(array(
        'key' => 'group_5f3d0743e3710',
        'title' => 'Продукт',
        'fields' => array(
            array(
                'key' => 'field_5f3d074c1b44b',
                'label' => 'Рост модели',
                'name' => 'model_height',
                'type' => 'text',
            ),
        ),
        'location' => array(
            array(
                array(
                    'param' => 'post_type',
                    'operator' => '==',
                    'value' => 'product',
                ),
            ),
        ),
        'menu_order' => 0,
        'position' => 'normal',
        'style' => 'default',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
        'hide_on_screen' => '',
        'active' => true,
        'description' => '',
    ));
endif;