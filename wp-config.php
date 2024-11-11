<?php
/**
 * Основные параметры WordPress.
 *
 * Скрипт для создания wp-config.php использует этот файл в процессе установки.
 * Необязательно использовать веб-интерфейс, можно скопировать файл в "wp-config.php"
 * и заполнить значения вручную.
 *
 * Этот файл содержит следующие параметры:
 *
 * * Настройки базы данных
 * * Секретные ключи
 * * Префикс таблиц базы данных
 * * ABSPATH
 *
 * @link https://ru.wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Параметры базы данных: Эту информацию можно получить у вашего хостинг-провайдера ** //
/** Имя базы данных для WordPress */
define( 'DB_NAME', 'exampledb' );

/** Имя пользователя базы данных */
define( 'DB_USER', 'exampleuser' );

/** Пароль к базе данных */
define( 'DB_PASSWORD', 'examplepass' );

/** Имя сервера базы данных */
define( 'DB_HOST', 'db' );

/** Кодировка базы данных для создания таблиц. */
define( 'DB_CHARSET', 'utf8' );

/** Схема сопоставления. Не меняйте, если не уверены. */
define( 'DB_COLLATE', '' );

define('AUTH_KEY',         '#ZH19X7QHWO*b]}jA0?.O;XM&');
define('SECURE_AUTH_KEY',  'GNDIft#Ur@#Q-LEO{[5(<E');
define('LOGGED_IN_KEY',    '0:Es80c:vK6V-97$Y=#s#7H');
define('NONCE_KEY',        'Q,B}4t,Jq-v09C:m)z`');
define('AUTH_SALT',        '{a;/p-)z}l0]mPz2R1VuKq|0-');
define('SECURE_AUTH_SALT', '(;/qT|E_tV)SWr,9<4fl0<WOg_u$');
define('LOGGED_IN_SALT',   '6>o?u2j,I#8GLBN^l~`GV0Gp`N6$q)');
define('NONCE_SALT',       'V=9t51}bZGIF.$W[r>]06h');

/**
 * Префикс таблиц в базе данных WordPress.
 *
 * Можно установить несколько сайтов в одну базу данных, если использовать
 * разные префиксы. Пожалуйста, указывайте только цифры, буквы и знак подчеркивания.
 */
$table_prefix = 'wps_';

/**
 * Для разработчиков: Режим отладки WordPress.
 *
 * Измените это значение на true, чтобы включить отображение уведомлений при разработке.
 * Разработчикам плагинов и тем настоятельно рекомендуется использовать WP_DEBUG
 * в своём рабочем окружении.
 *
 * Информацию о других отладочных константах можно найти в документации.
 *
 * @link https://ru.wordpress.org/support/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_DISPLAY', false );
define( 'WP_DEBUG_LOG', true );
/* Произвольные значения добавляйте между этой строкой и надписью "дальше не редактируем". */



/* Это всё, дальше не редактируем. Успехов! */

/** Абсолютный путь к директории WordPress. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Инициализирует переменные WordPress и подключает файлы. */
require_once ABSPATH . 'wp-settings.php';

