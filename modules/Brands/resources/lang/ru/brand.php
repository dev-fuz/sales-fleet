<?php
/**
 * Concord CRM - https://www.concordcrm.com
 *
 * @version   1.3.1
 *
 * @link      Releases - https://www.concordcrm.com/releases
 * @link      Terms Of Service - https://www.concordcrm.com/terms
 *
 * @copyright Copyright (c) 2022-2023 KONKORD DIGITAL
 */

return [
    'brand' => 'Компания',
    'brands' => 'Компании',
    'create' => 'Создать компанию',
    'update' => 'Обновить компанию',
    'form' => [
        'sections' => [
            'general' => 'Основная',
            'navigation' => 'Навигация',
            'email' => 'Email',
            'thank_you' => 'Спасибо',
            'signature' => 'Подпись',
            'pdf' => 'PDF',
        ],
        'is_default' => 'Это компания по умолчанию?',
        'name' => 'Как вы относитесь к этой компании внутри организации?',
        'display_name' => 'Как вы хотите, чтобы это отображалось для ваших клиентов?',
        'primary_color' => 'Выберите основной цвет компании',
        'upload_logo' => 'Загрузите логотип вашей компании',
        'navigation' => [
            'background_color' => 'Цвет фона навигации',
            'upload_logo_info' => 'Если у вас темный фон, используйте светлый логотип. Если вы используете светлый цвет фона, используйте логотип с темным текстом.',
        ],
        'pdf' => [
            'default_font' => 'Семейство шрифтов по умолчанию',
            'default_font_info' => 'Шрифт :fontName обеспечивает наиболее приличное покрытие символов Unicode по умолчанию, обязательно выберите правильный шрифт, если специальные символы или символы Unicode не отображаются должным образом в документе PDF.',
            'size' => 'Размер',
            'orientation' => 'Ориентация',
            'orientation_portrait' => 'Книжный',
            'orientation_landscape' => 'Альбомный',
        ],
        'email' => [
            'upload_logo_info' => 'Убедитесь, что логотип подходит для белого фона, если логотип не загружен, вместо него будет использоваться темный логотип, загруженный в общих настройках.',
        ],
        'document' => [
            'send' => [
                'info' => 'Когда вы отправляете документ',
                'subject' => 'Тема по умолчанию',
                'message' => 'Сообщение электронной почты по умолчанию при отправке документа',
                'button_text' => 'Текст кнопки электронной почты',
            ],
            'sign' => [
                'info' => 'Когда кто-то подписывает ваш документ',
                'subject' => 'Строка темы по умолчанию для благодарственного письма',
                'message' => 'Сообщение электронной почты для отправки, когда кто-то подписывает ваш документ',
                'after_sign_message' => 'Что должно быть в сообщении после подписания?',
            ],
            'accept' => [
                'after_accept_message' => 'Что должно быть в сообщении после принятия (без ЭЦП)?',
            ],
        ],
        'signature' => [
            'bound_text' => 'Юридический связанный текст',
        ],
    ],
    'delete_documents_usage_warning' => 'Компания уже связана с документами, поэтому ее нельзя удалить.',
    'created' => 'Компания успешно создана.',
    'updated' => 'Компания успешно изменена.',
    'deleted' => 'Компания успешно удалена.',
];
