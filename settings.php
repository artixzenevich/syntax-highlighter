<?php

function hjs_enqueue_admin_assets($hook_suffix) {
    // Проверяем, что мы находимся на странице настроек Highlight.js
    if ($hook_suffix !== 'settings_page_highlightjs-settings') {
        return;
    }

    // Подключаем скрипт Highlight.js
    wp_enqueue_script('highlightjs', plugin_dir_url(__FILE__) . 'assets/highlight.min.js', [], '11.9.0', false);

    // Подключаем начальный стиль, выбранный пользователем
    $selected_theme = get_option('hjs_selected_theme', 'default');
    wp_enqueue_style('highlightjs-theme', plugin_dir_url(__FILE__) . 'assets/styles/' . $selected_theme);
}
add_action('admin_enqueue_scripts', 'hjs_enqueue_admin_assets');

// Добавляем пункт меню настроек
function hjs_settings_menu() {
    add_options_page(
        'Highlight.js Settings',
        'Highlight.js',
        'manage_options',
        'highlightjs-settings',
        'hjs_render_settings_page'
    );
}
add_action('admin_menu', 'hjs_settings_menu');

// Отображение страницы настроек
function hjs_render_settings_page() {
    ?>
    <div class="wrap">
        <h1>Настройки Highlight.js</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('hjs_settings_group');
            do_settings_sections('highlightjs-settings');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// Регистрация настроек
function hjs_register_settings() {
    register_setting('hjs_settings_group', 'hjs_enabled_post_types');
    register_setting('hjs_settings_group', 'hjs_selected_theme');

    add_settings_section(
        'hjs_settings_section',
        'Конфигурация Highlight.js',
        null,
        'highlightjs-settings'
    );

    add_settings_field(
        'hjs_post_types',
        'Включенные типы записей',
        'hjs_post_types_callback',
        'highlightjs-settings',
        'hjs_settings_section'
    );

    add_settings_field(
        'hjs_theme_selection',
        'Выбор цветовой темы',
        'hjs_theme_selection_callback',
        'highlightjs-settings',
        'hjs_settings_section'
    );
}
add_action('admin_init', 'hjs_register_settings');

// Функция обратного вызова для отображения типов записей
function hjs_post_types_callback() {
    $post_types = get_post_types(['public' => true], 'objects');
    $enabled_post_types = get_option('hjs_enabled_post_types', []);

    // Проверка, что $enabled_post_types является массивом
    if (!is_array($enabled_post_types)) {
        $enabled_post_types = [];
    }
    ?>
    <fieldset>
        <?php foreach ($post_types as $post_type): ?>
            <label>
                <input type="checkbox" name="hjs_enabled_post_types[]" value="<?php echo esc_attr($post_type->name); ?>" <?php checked(in_array($post_type->name, $enabled_post_types)); ?>>
                <?php echo esc_html($post_type->label); ?>
            </label><br>
        <?php endforeach; ?>
    </fieldset>
    <p class="description">Отметьте типы записей, для которых нужно включить Highlight.js.</p>
    <?php
}

// Функция обратного вызова для выбора цветовой темы
function hjs_theme_selection_callback() {
    $themes_dir = plugin_dir_path(__FILE__) . 'assets/styles/';
    $theme_files = scandir($themes_dir);
    $themes = [];

    // Получаем список доступных тем из директории
    foreach ($theme_files as $file) {
        // Проверяем, что файл заканчивается на .min.css
        if (substr($file, -8) === '.min.css') {
            $themes[] = $file; // Добавляем файл целиком
        }
    }

    $selected_theme = get_option('hjs_selected_theme', 'default');
    ?>
    <select id="hjs-theme-selector" name="hjs_selected_theme" style="width: 100%;">
        <?php foreach ($themes as $theme): ?>
            <option value="<?php echo esc_attr($theme); ?>" <?php selected($selected_theme, $theme); ?>>
                <?php echo esc_html(ucfirst($theme)); ?>
            </option>
        <?php endforeach; ?>
    </select>
    <p class="description">Выберите цветовую тему для Highlight.js. Выбранная тема будет отображена ниже.</p>

    <!-- Блок кода для предварительного просмотра темы -->
    <pre><code id="hjs-preview-code" class="javascript" style="width:50%">
        function exampleFunction() {
            console.log("Hello, world!");
        }
    </code></pre>

    <script>
        // Подключение текущей темы при загрузке страницы
        (function() {
            const linkTag = document.createElement('link');
            linkTag.id = 'hjs-theme-style';
            linkTag.rel = 'stylesheet';
            linkTag.href = '<?php echo plugin_dir_url(__FILE__) . 'assets/styles/' . $selected_theme; ?>'; // Добавляем .css
            document.head.appendChild(linkTag);

            // Функция инициализации Highlight.js
            function initHighlightJS() {
                hljs.highlightElement(document.getElementById('hjs-preview-code'));
            }

            // Инициализация Highlight.js при загрузке стиля
            linkTag.onload = function() {
                // Проверяем, загружен ли Highlight.js, и инициализируем
                if (typeof hljs !== 'undefined') {
                    initHighlightJS();
                } else {
                    console.error('Highlight.js не загружен.');
                }
            };
        })();

        // Функция для обновления темы подсветки при выборе новой темы
        document.getElementById('hjs-theme-selector').addEventListener('change', function () {
            const selectedTheme = this.value;
            const linkTag = document.getElementById('hjs-theme-style');

            // Заменяем текущий стиль темы на новый, добавляя .css к имени файла
            linkTag.href = '<?php echo plugin_dir_url(__FILE__) . 'assets/styles/'; ?>' + selectedTheme + '';

            // Инициализация Highlight.js на блоке примера
            linkTag.onload = function() {
                if (typeof hljs !== 'undefined') {
                    hljs.highlightElement(document.getElementById('hjs-preview-code'));
                } else {
                    console.error('Highlight.js не загружен.');
                }
            };
        });
    </script>
    <?php
}