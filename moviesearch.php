<?php
/*
Plugin Name: Movie Search Plugin
Description: Плагин для отображения курса валют
Version: 1.0
Author: Extreme
*/

// Защита от прямого доступа
if (!defined('ABSPATH')) {
    exit;
}

register_activation_hook(__FILE__, 'ms_plugin_activate');

function ms_plugin_activate() {
    // Добавьте здесь код, который выполнится при активации плагина
    // Например, создание таблицы в базе данных
}

// Деактивация плагина
register_deactivation_hook(__FILE__, 'ms_plugin_deactivate');

function ms_plugin_deactivate() {
    // Добавьте здесь код, который выполнится при деактивации плагина
    // Например, удаление созданных ранее таблиц или очистка данных
}

//----------------------------------------------------------------------------

// Создаем меню в админке
add_action('admin_menu', 'msp_create_menu');

function msp_create_menu() {
    add_menu_page('Movie Search Settings', 'Movie Search', 'administrator', 'movie-search-settings', 'msp_settings_page');
    add_action('admin_init', 'msp_register_settings');
}

// Регистрируем настройки
function msp_register_settings() {
    register_setting('msp-settings-group', 'msp_api_key');
    register_setting('msp-settings-group', 'msp_movie_limit');
    register_setting('msp-settings-group', 'msp_movie_type');
}

 // правильный способ подключить стили и скрипты
add_action( 'wp_enqueue_scripts', 'movie_plugin_scripts' );
// add_action('wp_print_styles', 'theme_name_scripts'); // можно использовать этот хук он более поздний
function movie_plugin_scripts() {
	wp_enqueue_style( 'style', plugins_url('moviesearch/css/style.css') );
	 
}
 
// Страница настроек
function msp_settings_page() {
    ?>
    <div class="wrap">
        <h1>Настройки поиска фильмов</h1>
        <form method="post" action="">
            <?php settings_fields('msp-settings-group'); ?>
            <?php do_settings_sections('msp-settings-group'); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">API ключ</th>
                    <td><input type="text" name="msp_api_key" value="<?php echo get_option('msp_api_key'); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Лимит фильмов</th>
                    <td><input type="number" name="msp_movie_limit" value="<?php echo get_option('msp_movie_limit', 10); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Тип фильмов по умолчанию</th>
                    <td>
                        <select name="msp_movie_type">
                            <option value="movie" <?php selected(get_option('msp_movie_type'), 'movie'); ?>>Фильм</option>
                            <option value="series" <?php selected(get_option('msp_movie_type'), 'series'); ?>>Сериал</option>
                        </select>
                    </td>
                </tr>
            </table>
            <?php submit_button('Ввод'); ?>
        </form>
    </div>
     
    <div>
        <!--Вывести функцию на странице настроек [name attr1='value1' attr2="value2" ]-->
       <p>  
        <?php 
              $api_key = '';
              $movie_limit = '';
              $movie_type = '';
             if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                if ( isset($_POST['msp_api_key']) || 
                     isset($_POST['msp_movie_limit']) || 
                     isset($_POST['msp_movie_type']) ) {

                   $api_key = sanitize_text_field($_POST['msp_api_key']);
                   $movie_limit = sanitize_text_field($_POST['msp_movie_limit']);
                   $movie_type = sanitize_text_field($_POST['msp_movie_type']);
                }
                else echo 'Нет данных';
            }
              //echo $api_key . '<br>'; 
              //echo $movie_limit . '<br>';
              //echo $movie_type . '<br>';
             
        ?>  
        </p>
    </div>
    <?php echo do_shortcode('[movieout api_key='. $api_key .' movie_limit='. $movie_limit .' movie_type='. $movie_type .']');?>
    
     
    <?php
    
}


  
 // получить фильмы через API 
 function get_movies_shortcode($atts){
    // создать атрибут по умолчанию
    // если ничего не приидет 
    $atts= shortcode_atts(
		array('api_key' => 'd93fcb59', 
               'movie_limit' => '10',
               'movie_type' => 'movie'), 
		$atts
	);

    $response = wp_remote_get("https://www.omdbapi.com/?i=tt1285016&apikey={$atts['api_key']}&type={$atts['movie_limit']}&page={$atts['movie_type']}");  

    if (is_wp_error($response)) {
        return ['Nothing'];
    }

    $array = json_decode(wp_remote_retrieve_body($response), true); 
    
    if($array['Response'] !== 'False'){
        /*
        echo '<pre>';
        print_r($array);
        echo '</pre>';
        */
        return '<div class="post"><h1>Title: '. $array['Title'] . '</h1>
                     <p>year: ' . $array['Year'] . '</p>
                     <p>released:' . $array['Released'] . '</p>
                     <p>runtime:' . $array['Runtime'] . '</p>
                     <p>writer:' . $array['Writer'] . '</p>
                     <p>actors:' . $array['Actors'] . '</p>
                     <p>plot:' . $array['Plot'] . '</p>
                     <p>awards:' . $array['Awards'] . '</p>
                      <p><img src="' . $array['Poster'] .'" alt="image"> </p>
               </div>';
    }else echo 'Enter api key d93fcb59';
  
    
}

add_shortcode('movieout', 'get_movies_shortcode');


 
     
 
  