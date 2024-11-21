<?php

/**
 * Plugin Name: Сампо плагин
 * Plugin URI: https://wordpresslab.ru/plugins/kak-sozdat-plagin-dlya-wordpress/
 * Description: Ргистарция ЮЛ/ИП
 * Version: 1.0.0
 * Author: WordPress лаборатория
 * Author URI: https://wordpresslab.ru/
 * License: GPL2
 */





function sampo_plugin_activate()
{

  add_option('Activated_Plugin', 'Plugin-Slug');

  wp_clear_scheduled_hook('sampo_reg_bis_api_task');
  wp_schedule_event(time(), 'five_min', 'sampo_reg_bis_api_task');
  
  
}
register_activation_hook(__FILE__,  'sampo_plugin_activate');

function load_plugin()
{

  if (is_admin() && get_option('Activated_Plugin') == 'Plugin-Slug') {

    delete_option('Activated_Plugin');

    /* do stuff once right after activation */
    // example: add_action( 'init', 'my_init_function' );
   
    

  }
}




add_action( 'admin_menu', 'add_plugin_page' );
function add_plugin_page(){

  add_options_page(
    'Настройки СМПО плагин',
    'Sampo',
    'manage_options',
    'sampo_slug',
    'sampo_options_page_output'
  );
}

function sampo_options_page_output(){
	?>
	<div class="wrap">
		<h2><?php echo get_admin_page_title() ?></h2>

		<form action="options.php" method="POST">
			<?php
				settings_fields( 'option_group' );     // скрытые защитные поля
				do_settings_sections( 'primer_page' ); // секции с настройками (опциями). У нас она всего одна 'section_id'
				submit_button();
			?>
		</form>
	</div>
	<?php
}

add_action( 'admin_init', 'plugin_settings' );

function plugin_settings(){

	// параметры: $option_group, $option_name, $sanitize_callback
	register_setting( 'option_group', 'option_name', 'sanitize_callback' );

	// параметры: $id, $title, $callback, $page
	add_settings_section( 'section_id', 'Основные настройки', '', 'sampo_page' );

	// параметры: $id, $title, $callback, $page, $section, $args
	add_settings_field('sampo_field1', 'Название опции', 'fill_sampo_field1', 'sampo_page', 'section_id' );
	add_settings_field('sampo_field2', 'Другая опция', 'fill_sampo_field2', 'sampo_page', 'section_id' );
}

function fill_sampo_field1(){

	$val = get_option('option_name');
	$val = $val ? $val['input'] : null;
	?>
	<input type="text" name="option_name[input]" value="<?php echo esc_attr( $val ) ?>" />
	<?php
}

function fill_sampo_field2(){

	$val = get_option('option_name');
	$val = $val ? $val['checkbox'] : null;
	?>
	<label><input type="checkbox" name="option_name[checkbox]" value="1" <?php checked( 1, $val ) ?> /> отметить</label>
	<?php
}

function sanitize_callback( $options ){

	foreach( $options as $name => & $val ){
		if( $name == 'input' )
			$val = strip_tags( $val );

		if( $name == 'checkbox' )
			$val = intval( $val );
	}

	//die(print_r( $options )); // Array ( [input] => aaaa [checkbox] => 1 )

	return $options;
}

function sampo_admin_menu() {
  add_menu_page(
      __( 'Сампо настройки плагина', 'Sampo' ),
      __( 'Sampo', 'Sampo' ),
      'manage_options',
      'sampo-setings-page',
      'sampo_admin_page_contents',
      'dashicons-schedule',
      3
  );
}
add_action( 'admin_menu', 'sampo_admin_menu' );

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Reg_Bis_Orders_List_Table extends WP_List_Table {
  private $table_data;
 
  function get_columns() {
    $columns = array(
      'cb'  => '<input type="checkbox" />',
      'id' => __( 'ID', 'sp' ),
      'order_date' =>__( 'Дата', 'sp' ), 
      'ApplicantType' =>__( 'Тип заявителя', 'sp' ),  
      'Status' =>__( 'Статус', 'sp' ),
      "LastName" =>__( 'Фамилия', 'sp' ),
      "FirstName" =>__( 'Имя', 'sp' ),
      "MiddleName" =>__( 'Отчество', 'sp' ),
      
      "user_id"=>__( "Пользователь", 'sp' ), 
    );
  
    return $columns;
  }

  function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'id':
			case 'order_date':
			case 'ApplicantType':
			case 'Status':
      case 'user_id':
			default:
				return $item[ $column_name ];
		}
	}
  function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="element[]" value="%s" />',
			$item['id']
		);
	}

  function prepare_items() {

    $per_page = 5;
    $current_page = $this->get_pagenum();
    $total_items = self::record_count();

$this->set_pagination_args( [
'total_items' => $total_items, 
'per_page' => $per_page 
] );


    $this->table_data = $this->get_table_data($per_page, $current_page);
    
    $columns = $this->get_columns();
    $hidden = array();
    $sortable = $this->get_sortable_columns();
    $primary = 'id';




    $this->_column_headers = array($columns, $hidden, $sortable, $primary);
  
    $this->items = $this->table_data;
  }

  private function get_table_data($per_page = 5, $page_number = 1) {
    $sql = "SELECT 
                                            orders_registration.id, orders_registration.order_date, 
                                            orders_registration.FirstName, orders_registration.MiddleName, orders_registration.LastName,
                                            (CASE WHEN orders_registration.ApplicantType =1 THEN 'ЮЛ'
                                             WHEN orders_registration.ApplicantType =2 THEN 'ИП' 
                                             END ) AS ApplicantType,
                                             OrderStatuses.value AS Status, orders_registration.user_id
                                            FROM orders_registration 
                                            LEFT JOIN OrderStatuses on orders_registration.StatusId =OrderStatuses.Type " ;

    if ( ! empty( $_REQUEST['orderby'] ) ) {
      $sql .= ' ORDER BY ' . esc_sql( $_REQUEST['orderby'] );
      $sql .= ! empty( $_REQUEST['order'] ) ? ' ' . esc_sql( $_REQUEST['order'] ) : ' ASC';
      }

      $sql .= " LIMIT $per_page";

      $sql .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;
    return  $GLOBALS['wpdb']->get_results( $sql, ARRAY_A);

 
  }

  public static function record_count() {
    global $wpdb;
    
    $sql = "SELECT COUNT(*) FROM orders_registration ";
    
    return $wpdb->get_var( $sql );
   }
  
  function get_sortable_columns() {
    $sortable_columns = array(
    'id' => array( 'id', true ),
    'ApplicantType' => array( 'ApplicantType', true ),
    'LastName' => array( 'LastName', false )
    );
    error_log("get_sortable_columns");
    return $sortable_columns;
  }


  
  }


function sampo_admin_order_list_init() {
  // Создаем экземпляр класса Employees_List_Table 
    $table = new Reg_Bis_Orders_List_Table();
  
    echo '<div class="wrap"><h2>Список сотрудников</h2>';
  // Формируем таблицу 
    $table->prepare_items();
  // Выводим таблицу 
    $table->display();
    echo '</div>';
  }


  

function sampo_admin_menu_orders_reg_bis_content(){
  sampo_admin_order_list_init();
}


function sampo_admin_sub_menu(){

  add_submenu_page( 
  'sampo-setings-page', 
  __( 'Сампо настройки плагина', 'Sampo' ),
  __( 'Сампо настройки плагина', 'Sampo' ),
  'manage_options',
  'sampo-setings-page',
"sampo_admin_page_contents");

}


add_action( 'admin_menu', 'sampo_admin_sub_menu' );



function sampo_admin_menu_orders_reg_bis(){

  add_submenu_page( 
  'sampo-setings-page', 
  'Список заявок на рег. ЮР/ИП', 
  'Список заявок на рег. ЮР/ИП', 
  'manage_options', 
  'sampo-setings-page-orders-list',
"sampo_admin_menu_orders_reg_bis_content");

}


add_action( 'admin_menu', 'sampo_admin_menu_orders_reg_bis' );


function sampo_admin_page_contents() {
  echo '<div class="wrap">
	<h1>' . get_admin_page_title() . '</h1>
	<form method="post" action="options.php">';
 
		settings_fields( 'sampo_user_api_settings' ); // название настроек
		do_settings_sections( 'sampo-setings-page' ); // ярлык страницы, не более
		submit_button(); // функция для вывода кнопки сохранения
 
	echo '</form></div>';
}

add_action( 'admin_init',  'sampo_fields' );



 
function sampo_fields(){
 
	// регистрируем опцию
	register_setting(
		'sampo_user_api_settings', // название настроек из предыдущего шага
		'sampo_login', // ярлык опции
		'string' // функция очистки
	);
  register_setting(
		'sampo_user_api_settings', // название настроек из предыдущего шага
		'sampo_pass', // ярлык опции
		'string' // функция очистки
	);
 
	// добавляем секцию без заголовка
	add_settings_section(
		'slider_settings_section_id', // ID секции, пригодится ниже
		'', // заголовок (не обязательно)
		'', // функция для вывода HTML секции (необязательно)
		'sampo-setings-page' // ярлык страницы
	);
 
	// добавление поля
	add_settings_field(
		'sampo_login',
		'API логин',
		'sampo_login_field', // название функции для вывода
		'sampo-setings-page', // ярлык страницы
		'slider_settings_section_id', // // ID секции, куда добавляем опцию
		array( 
			'label_for' => 'sampo_login',
			'class' => 'misha-class', // для элемента <tr>
			'name' => 'sampo_login', // любые доп параметры в колбэк функцию
		)
	);
  add_settings_field(
		'sampo_pass',
		'API пароль',
		'sampo_pass_field', // название функции для вывода
		'sampo-setings-page', // ярлык страницы
		'slider_settings_section_id', // // ID секции, куда добавляем опцию
		array( 
			'label_for' => 'sampo_pass',
			'class' => 'misha-class', // для элемента <tr>
			'name' => 'sampo_pass', // любые доп параметры в колбэк функцию
		)
	);
 
}
 
function sampo_login_field( $args ){
	// получаем значение из базы данных
	$value = get_option( $args[ 'name' ] );
 
	printf(
		'<input type="text"  id="%s" name="%s" value="%s" />',
		esc_attr( $args[ 'name' ] ),
		esc_attr( $args[ 'name' ] ),
		$value 
	);
 
}
function sampo_pass_field( $args ){
	// получаем значение из базы данных
	$value = get_option( $args[ 'name' ] );
 
	printf(
		'<input type="text"  id="%s" name="%s" value="%s" />',
		esc_attr( $args[ 'name' ] ),
		esc_attr( $args[ 'name' ] ),
		$value 
	);
 
}
 add_action( 'admin_notices', 'sampo_custom_notice' );
 
function sampo_custom_notice() {
 
	if(
		isset( $_GET[ 'page' ] )
		&& 'sampo-setings-page' == $_GET[ 'page' ]
		&& isset( $_GET[ 'settings-updated' ] )
		&& true == $_GET[ 'settings-updated' ]
	) {
		echo '<div class="notice notice-success is-dismissible"><p>Настройки сохранены!</p></div>';
	}
 
}






////Переодический опрос по API 

add_action('sampo_reg_bis_api_task', 'do_this_hourly');
function do_this_hourly()
{
  $request_args = [
    'method' => 'GET',
    'timeout' => 2,
  ];


  $result = wp_remote_get("https://reqres.in/api/users?page=1", $request_args);
  $code = wp_remote_retrieve_response_code($result);
  error_log(print_r($code, true));
  if ($code === "200" || $code == 200) {
    $body = json_decode(wp_remote_retrieve_body($result), true);
    error_log(print_r($body, true));
    foreach ($body as $key => $value) {
      error_log($key . "=>" . $value);
    }
  }
  error_log("Задача выполнене по расписанию");
}


/// Добавление CSS

add_action("wp_enqueue_scripts", "sampo_load_assets");
function sampo_load_assets()
{
  wp_enqueue_style("sampo-plugin", plugin_dir_url(__FILE__) . "/css/sampo-plugin.css", array(), 1, "all");
}

add_action('admin_init', 'load_plugin');


/// JS

wp_register_script("sampo-sig-script",plugins_url( '/js/sign-script.js' , __FILE__ ), array(), '1.0.0', true );
wp_register_script("cryptohelper",plugins_url( '/js/cryptohelper.js' , __FILE__ ), array(), '1.0.0', false );
wp_register_script("cadesplugin_api","https://www.cryptopro.ru/sites/default/files/products/cades/cadesplugin_api.js", array(), '1.0.0', false );
wp_register_script("sampo-reg-bis-script",plugins_url( '/js/reg-bis-form.js' , __FILE__ ),array(), '1.0.0', true );



function sampo_footer_scripts()
{
  
  wp_localize_script('wp-api', 'wpApiSettings', array(
    'root' => esc_url_raw(rest_url()),
    'nonce' => wp_create_nonce('wp_rest')
  ));

?>

  <script>
    function OnApplicationTypeChange() {
      AppType = document.querySelector('#ApplicantType').value;
      if (AppType == 2) {
        document.querySelector('#urlic').hidden = true;

      } else {
        document.querySelector('#urlic').hidden = false;
      }
      console.log("on change!")
    }

    function OnTaxTypeChange() {
      AppType = document.querySelector('#TaxSystem').value;
      if (AppType == 2) {
        document.querySelector('#TaxObjectGroup').hidden = true;

      } else {
        document.querySelector('#TaxObjectGroup').hidden = false;
      }
      console.log("on tax change!")
    }

    document.querySelector('#sampo-reg-bisnes-form').addEventListener('submit',
      async function(event) {
        var edit = <?php echo  (isset($_GET["editid"]) && !empty($_GET["editid"]))? "true":"false";?>;
      
        var data = {};
        var nonce = wpApiSettings.nonce;

        const form = document.querySelector("#sampo-reg-bisnes-form");
        const formData = new FormData(this);
        formData.forEach(function(value, key) {
          data[key] = value;
        });

        event.preventDefault();
        const result = await fetch(
          "/wp-json/sampo-reg-bis/v1/"+(edit?"upd_register/":"register/"), {
            method: "POST",
            mode: "cors",
            cache: "no-cache",
            credentials: "same-origin",
            headers: {
              "Content-Type": "application/json",
              "X-WP-Nonce": nonce,
              // 'Content-Type': 'application/x-www-form-urlencoded',
            },
            redirect: "follow", // mdnual, *follow, error
            referrerPolicy: "no-referrer", // no-referrer, *client
            body: JSON.stringify(
              data
            ),
          }
        );
        //   window.location.href = "/"
      });
  </script>


<?php }
///// JS 



//add_action('wp_footer', "sampo_footer_scripts");



add_shortcode('scmpo_reg_bis_list', 'scmpo_reg_bis_list');

function scmpo_reg_bis_list($atts)
{
  $results = $GLOBALS['wpdb']->get_results("select orders_registration.id,orders_registration.order_date,orders_registration.ApplicantType, OrderStatuses.value as StatusName from orders_registration left join OrderStatuses on OrderStatuses.type=StatusId where orders_registration.user_id=" . get_current_user_id(), OBJECT);
  if (!empty($results))                        // Checking if $results have some values or not
  {
    $res =  "<table width='100%' border='0'>" . "<tbody class=\"order-list\">";
    $res = $res .  "<tr><th>Дата заявки</th> <th>Тип зявителя</th><th>Статус1</th></tr>";
    foreach ($results as $row) {
      $res =  $res . "<tr>";
      $res =  $res .  "<td class=\"text-center\">  <a href =\"/register_bisnes?editid=" . $row->id . "\">" . $row->order_date . "</a></td >" .
        "<td class=\"text-center\">" . ($row->ApplicantType === 2 ? "ИП" : "ЮЛ") . "</td>" .
        "<td class=\"text-center\">" . $row->StatusName . "</td>";
      $res =  $res .  "</tr>";
    }
    $res =  $res . "</tbody>" . "</table>";
  }
   
  return  $res;
}



add_shortcode('sampo_oreders_reg_list', 'sampo_oreder_list');

function sampo_oreder_list($atts)
{
  $results = $GLOBALS['wpdb']->get_results("SELECT * FROM orders_registration", OBJECT);
  if (!empty($results))                        // Checking if $results have some values or not
  {
    $res =  "<table width='100%' border='0'>" . "<tbody>";
    foreach ($results as $row) {
      $res =  $res . "<tr>";
      $res =  $res .  "<th>ID</th>" . "<td>" . $row->FirstName . "</td>";
      $res =  $res .  "</tr>";
    }
    $res =  $res . "</tbody>" . "</table>";
  }
  return  $res;
}





function sampo_get_FNS_Codes($selected_value)
{
  $res = //"<label for=\"fns-select\">ФНС</label>" .
    "<select name=\"FtsDivisionCode\" id=\"FtsDivisionCode\">" .
    "<option value=\"\">ФНС</option>";
  $results = $GLOBALS['wpdb']->get_results("SELECT * FROM CodesFNC", OBJECT);
  if (!empty($results)) {
    foreach ($results as $row) {
      $res =  $res .  "<option ".($selected_value === $row->code?"selected":"")." value=" . $row->code .  ">" . $row->description."(".$row->code . ")</option>";
    }
  }
  $res = $res .  "</select> <br>";
  echo  $res;
}

function sampo_get_Region_Codes($legal,$selected_value = "0")
{
  $res = "<select name=\"RegionCode" . ($legal ? "Legal" : "Residential") . "\" id=\"RegionCode\">" .
    "<option value=\"\">Код субъекта РФ регистрации ЮЛ.</option>";
  $results = $GLOBALS['wpdb']->get_results("SELECT * FROM RegionCodeLegals", OBJECT);
  if (!empty($results)) {
    foreach ($results as $row) {
      $res =  $res .  "<option ".($selected_value === $row->regioncode?"selected":"")." value=" . $row->regioncode .  ">" . $row->description." (".$row->regioncode .")". "</option>";
    }
  }
  $res = $res .  "</select> <br>";
  echo  $res;
}

function sampo_get_CityLegal($legal,$selected_value = "0")
{
  $res = "<select name=\"City" . ($legal ? "Legal" : "Residential") . "Type\" id=\"CityLegal\">" .
    "<option value=\"\">Тип населнного пункта</option>";
  $results = $GLOBALS['wpdb']->get_results("SELECT * FROM CityResidentials", OBJECT);
  if (!empty($results)) {
    foreach ($results as $row) {
      $res =  $res .  "<option ".($selected_value === $row->Type?"selected":"")." value=" . $row->Type .  ">" . $row->name  . "</option>";
    }
  }
  $res = $res .  "</select> <br>";
  echo  $res;
}

function sampo_get_Settlement($legal,$selected_value = "0")
{
  $res = "<select  name=\"Settlement" . ($legal ? "Legal" : "Residential") . "Type\" id=\"SettlementResidential\">" .
    "<option  value=\"\">Тип поселения</option>";
  $results = $GLOBALS['wpdb']->get_results("SELECT * FROM SettlementResidentials", OBJECT);
  if (!empty($results)) {
    foreach ($results as $row) {
      $res =  $res .  "<option ".($selected_value === $row->Type?"selected":"")." value=" . $row->Type .  ">" . $row->value  . "</option>";
    }
  }
  $res = $res .  "</select> <br>";
  echo  $res;
}

function sampo_get_Address($legal,$selected_value = "0")
{
  $res = "<select name=\"Address" . ($legal ? "Legal" : "Residential") . "Type\" id=\"AddressResidential\">" .
    "<option value=\"\">Типы улиц</option>";
  $results = $GLOBALS['wpdb']->get_results("SELECT * FROM AddressResidentials", OBJECT);
  if (!empty($results)) {
    foreach ($results as $row) {
      $res =  $res .  "<option ".($selected_value === $row->Type?"selected":"")." value=" . $row->Type .  ">" . $row->value  . "</option>";
    }
  }
  $res = $res .  "</select> <br>";
  echo  $res;
}


function sampo_get_Building($legal,$selected_value = "0")
{
  $res = "<select name=\"Building" . ($legal ? "Legal" : "Residential") . "Type\" id=\"BuildingResidential\">" .
    "<option value=\"\">Тип здания</option>";
  $results = $GLOBALS['wpdb']->get_results("SELECT * FROM BuildingResidentials", OBJECT);
  if (!empty($results)) {
    foreach ($results as $row) {
      $res =  $res .  "<option ".($selected_value === $row->Type?"selected":"")." value=" . $row->Type .  ">" . $row->value  . "</option>";
    }
  }
  $res = $res .  "</select> <br>";
  echo  $res;
}

function sampo_get_Apartment($legal,$selected_value = "0")
{
  $res = "<select name=\"Apartment" . ($legal ? "Legal" : "Residential") . "Type\" id=\"ApartmentResidential\">" .
    "<option value=\"\">Тип помещения в пределах здания</option>";
  $results = $GLOBALS['wpdb']->get_results("SELECT * FROM ApartmentResidentials", OBJECT);
  if (!empty($results)) {
    foreach ($results as $row) {
      $res =  $res .  "<option ".($selected_value === $row->Type?"selected":"")." value=" . $row->Type .  ">" . $row->value  . "</option>";
    }
  }
  $res = $res .  "</select> <br>";
  echo  $res;
}

function sampo_get_Room($legal,$selected_value = "0")
{
  $res = "<select name=\"Room" . ($legal ? "Legal" : "Residential") . "Type\" id=\"RoomResidential\">" .
    "<option value=\"\">Тип помещения в пределах квартиры</option>";
  $results = $GLOBALS['wpdb']->get_results("SELECT * FROM RoomResidentials", OBJECT);
  if (!empty($results)) {
    foreach ($results as $row) {
      $res =  $res .  "<option ".($selected_value === $row->Type?"selected":"")." value=" . $row->Type .  ">" . $row->value  . "</option>";
    }
  }
  $res = $res .  "</select> <br>";
  echo  $res;
}


function sampo_get_District($legal,$selected_value = "0")
{
  $res = "<select name=\"District" . ($legal ? "Legal" : "Residential") . "Type\"  id=\"DistrictResidential\">" .
    "<option value=\"\">Тип муниципального района</option>";
  $results = $GLOBALS['wpdb']->get_results("SELECT * FROM DistrictResidentials", OBJECT);
  if (!empty($results)) {
    foreach ($results as $row) {
      $res =  $res .  "<option ".($selected_value === $row->Type?"selected":"")." value=" . $row->Type .  ">" . $row->value  . "</option>";
    }
  }
  $res = $res .  "</select> <br>";
  echo  $res;
}
add_shortcode('sampo_upload_register', 'sampo_upload_register');
function user_has_accses_to_order($order_id){
 
  $results = $GLOBALS['wpdb']->get_results(
    $GLOBALS['wpdb']->prepare("
    SELECT id FROM orders_registration WHERE id=%d AND user_id=%d",$order_id, get_current_user_id()));
    if (!empty($results)){
      return true;
    }
    error_log("bad news!");
    return false;
  }

function sampo_get_order_type($order_id){
  $results = $GLOBALS['wpdb']->get_results(
    $GLOBALS['wpdb']->prepare("
    SELECT ApplicantType FROM orders_registration WHERE id=%d",$order_id));
   
    if (!empty($results)){
    foreach ($results as $row) 
      break;}  
      return $row->ApplicantType;

}

function sampo_upload_register($atts){
  ob_start();
 
  if (!isset($_GET["order_id"]) || empty($_GET["order_id"])){
    return "";
  }
  $order_id = $_GET["order_id"];
  $ApplicantType = sampo_get_order_type($order_id);
  if (!user_has_accses_to_order($order_id)){
    return "";
  }
  
  ?>
     <div class="response">
            <?php if (isset($_SESSION['sampo_upload_file'])): ?>
                <p><?= $_SESSION['sampo_upload_file']; ?></p>
                <?php unset($_SESSION['sampo_upload_file']); ?>
            <?php endif; ?>
        </div>
 <form enctype="multipart/form-data" action="<?= admin_url('admin-post.php'); ?>" method="POST">
	<?php wp_nonce_field( 'sampo_file_upload', 'fileup_nonce' ); ?>
	<div class="flex flex-col">
  <input type="hidden" name="order_id" value="<?php echo $order_id;?>">
  <input type="hidden" name="action" value="sampo_upload_file" />
  <label for="photo">Фотография с паспортом</label>
  <input name="sampo_file_upload_photo" id="photo" type="file" />
  <label for="passport">Паспорт. Страница с фотографией и страница с регистрацией (один многостраничный файл)</label>
  <input name="sampo_file_upload_passport"  id="passport" type="file" />
  <?php 
   if ( $ApplicantType==1){echo ("<label for=\"ustav\">Устав</label>".
  "<input name=\"sampo_file_upload_ustav\"  id=\"ustav\" type=\"file\" />");}
  ?>
 </div>
	<input type="submit" value="Загрузить файлы" />
</form>
  <?php
   return ob_get_clean();
}
// ЗАгрузка Файлов Server

function sampo_add_files_to_db($file_name,$file_url,$order_id,$type){
  $type_of_files = [
    "sampo_file_upload_passport" => 1020,
    "sampo_file_upload_photo" => 1010,
    "sampo_file_upload_ustav" => 2000,
    "sampo_file_upload_vipiska" => 2010,
    "sampo_file_upload_passport_sig" =>991020,         
    "sampo_file_upload_uch_sig" =>    994000,
    "sampo_file_upload_ustav_sig"    =>  992000,  
    "sampo_file_upload_zayv_fns_ul_sig"   =>      9911001,  
    "sampo_file_upload_zayv_fns_ip_sig"      =>    9921001,  
    "sampo_file_upload_usn_sig"   =>    991150001,  
    "sampo_file_upload_xml_ip_sig" =>     998821,  
    "sampo_file_upload_xml_ul_sig"     =>    998811  
       
  
  ];
  
  $file_type = $type_of_files[$type];
  error_log($file_name."\n".$file_url."\n".$order_id."\n");
  $results = $GLOBALS['wpdb']->query(
    $GLOBALS['wpdb']->prepare("INSERT INTO orders_files 
      (file_name, url, order_id, file_type) VALUES (%s,%s,%d,%d)",$file_name, $file_url, $order_id, $file_type));
  
  return true;
}
add_action('admin_post_sampo_upload_file', function () {
 
  require_once ABSPATH . 'wp-admin/includes/file.php';
  error_log(print_r($_POST,true));
  $check1 = wp_verify_nonce(
      $_POST['fileup_nonce'],
      'sampo_file_upload'
  );
  $check2 = current_user_can('upload_files');
  ob_start();
  $file_keys = array("sampo_file_upload_photo",
                      "sampo_file_upload_passport",
                      "sampo_file_upload_ustav",
                      "sampo_file_upload_passport_sig",         
                      "sampo_file_upload_uch_sig",
                      "sampo_file_upload_ustav_sig",  
                      "sampo_file_upload_zayv_fns_ul_sig",  
                      "sampo_file_upload_zayv_fns_ip_sig",  
                      "sampo_file_upload_usn_sig",  
                      "sampo_file_upload_xml_ip_sig",  
                      "sampo_file_upload_xml_ul_sig"  
                    );
  $has_error = false; 
  if ($check1 && $check2) {
    error_log("Check OK upload files\n");
      $dest_path =  getcwd()."/attachmens/";
      if (!file_exists($dest_path)) {
        mkdir($dest_path, 0666, true);
     }
      
      
      $overrides = ['test_form' => false,
      "test_type"=> false];
     
      //  error_log(print_r($files,true));
     
      foreach ( $file_keys as $value ) {
        // if( empty( $files['name'][ $key ] ) ){
        //   continue;
        // }
        if (!isset($_FILES[$value])){
          continue;
        }
        
       
        $result = wp_handle_upload(
        $_FILES[$value],
        $overrides
      );
      error_log(print_r($result ,true));
      if (isset($result['error'])) {
        $has_error = true;
        error_log("upload error files\n");
        continue;}
       
     
      $extension = pathinfo($result['file'], PATHINFO_EXTENSION); 
      $tmpfname = tempnam( 
        $dest_path, "sampo");
        unlink($tmpfname);
        $tmpfname = $dest_path.pathinfo($tmpfname, PATHINFO_FILENAME). ".".$extension;
        copy($result['file'], $tmpfname);
        sampo_add_files_to_db(pathinfo($result['file'],PATHINFO_FILENAME),"/wp-admin/attachmens/".pathinfo($tmpfname,PATHINFO_FILENAME),$_POST['order_id'],$value);
      } 
      // показываем результаты загрузки файла
     
      if ($has_error ) {
          echo 'Ошибка при загрузке файла';
      } else {
          echo 'Файлы был успешно загружен';
      }
  } else {
      echo 'Проверка не пройдена, файл не загружен';
  }
  $_SESSION['sampo_upload_file'] = ob_get_clean();
  // после отправки формы делаем редирект, чтобы предотвратить
  // повторную отправку, если пользователь обновит страницу
  
  $redirect = home_url();
  if (isset($_POST['redirect'])) {
      $redirect = $_POST['redirect'];
      $redirect = wp_validate_redirect($redirect, home_url()."/upload_success/");
  }

  wp_redirect($redirect);
  die();
});

add_shortcode('sampo_form_register', 'sampo_form_register');

function sampo_form_register($atts,$selected_value = "0")
{ 
  wp_localize_script('wp-api', 'wpApiSettings', array(
    'root' => esc_url_raw(rest_url()),
    'nonce' => wp_create_nonce('wp_rest')
  ));
  
  wp_enqueue_script( 'sampo-reg-bis-script');
  $edit =  isset($_GET["editid"]) && !empty($_GET["editid"]);
  if ($edit) {
    $results = $GLOBALS['wpdb']->get_results(
      $GLOBALS['wpdb']->prepare("
      SELECT * FROM orders_registration WHERE id=%d",$_GET["editid"]));
     
      if (!empty($results)){
      foreach ($results as $row) 
        break;}
      else{
      $edit = FALSE;}
      }
      
    ?>

  <form enctype="application/json" id="sampo-reg-bisnes-form" action="">
    <input type="hidden" id="order_id" name="order_id" value="<?php echo $_GET["editid"]?>">
    
    <select onchange="OnApplicationTypeChange()" name="ApplicantType" id="ApplicantType" value="1">
      <option value="">Выберите тип заявителя</option>
      <option <?php if ($edit && $row->ApplicantType == 1) echo "selected " ?> value="1">ЮЛ</option>
      <option <?php if ($edit && $row->ApplicantType == 2) echo "selected " ?> value="2">ИП</option>
    </select>
    <br>
    <label>
      <span class="wpcf7-form-control-wrap">
        <input value="<?php echo $edit?$row->FirstName:""?>"  name="FirstName" id="FirstName" type="text"
          placeholder="Имя">
      </span>
    </label>
    <br>
    <label>
      <span class="wpcf7-form-control-wrap">
        <input value="<?php echo $edit?$row->MiddleName:""?>"  name="MiddleName" id="MiddleName" type="text" value="sfasfad" placeholder="Отчество (если есть).">
      </span>
    </label>
    <br>
    <label>
      <span class="wpcf7-form-control-wrap">
        <input value="<?php echo $edit?$row->LastName:""?>"  name="LastName" id="LastName" type="text" placeholder="Фамилия">
      </span>
    </label>
    <br>

    <select name="Gender" id="Gender">
      <option value="">Пол</option>
      <option <?php if ($edit && $row->Gender == 1) echo "selected " ?> value="1">Мужской</option>
      <option <?php if ($edit && $row->Gender == 2) echo "selected " ?> value="2">Женский</option>
    </select>
    <br>
    <div class="dspflex">
      <div class="dspflex">
        <label class="mr-5" for="BirthDate">Дата рождения</label>
        <input value="<?php echo $edit?$row->BirthDate:""?>" name="BirthDate" id="BirthDate" type="date">
      </div>
      <br>
      <div class="dspflex ">
        <label class="mr-5" for="DocumentIssueDate">Дата выдачи паспорта</label>
        <input value="<?php echo $edit?$row->DocumentIssueDate:""?>"  name="DocumentIssueDate" id="DocumentIssueDate" type="date">
      </div>
    </div>
    <div class="dspflex">
      <input value="<?php echo $edit?$row->DocumentSeries:""?>" name="DocumentSeries" id="DocumentSeries" placeholder="Серия паспорта" type="text">
      <input value="<?php echo $edit?$row->DocumentNumber:""?>" name="DocumentNumber" class="ml-5" id="DocumentNumber" placeholder="Номер паспорта" type="text">
    </div>
    <br>


    <br>
    <div class="dspflex">
      <input value="<?php echo $edit?$row->DocumentIssuerName:""?>"  name="DocumentIssuerName" id="DocumentIssuerName" placeholder="Название органа выдавшего паспорт" type="text">
    </div>
    <br>
    <div>
      <input value="<?php echo $edit?$row->DocumentIssuerCode:""?>"  name="DocumentIssuerCode" id="DocumentIssuerCode" placeholder="Код подразделения" type="text">
    </div>
    <br>
    <div>
      <input value="<?php echo $edit?$row->PlaceOfBirth:""?>" name="PlaceOfBirth" id="PlaceOfBirth" placeholder="Место рождения" type="text">
    </div>
    <br>
    <div>
      <input value="<?php echo $edit?$row->InnPersonal:""?>" name="InnPersonal" id="InnPersonal" placeholder="ИНН заявителя (физического лица)" type="text">
    </div>
    <br>
    <div>
      <input value="<?php echo $edit?$row->Snils:""?>" name="Snils" id="Snils" placeholder="СНИЛС заявителя" type="text">
    </div>
    <br>
    <div id="urlic">
      <div>
        <input value="<?php echo $edit?$row->OrganizationName:""?>" name="OrganizationName" id="OrganizationName" placeholder="Полное имя организации, без «ООО»" type="text">
      </div>
      <br>
      <div>
        <input value="<?php echo $edit?$row->OrganizationShortName:""?>" name="OrganizationShortName" id="OrganizationShortName" placeholder="Короткое имя организации без «ООО»" type="text">
      </div>
      <br>
      <div>
        <input value="<?php echo $edit?$row->LegalHeadPosition:""?>" name="LegalHeadPosition" id="LegalHeadPosition" placeholder="Должность руководителя ЮЛ" type="text">
      </div>
      <br>
      <div>
        <input value="<?php echo $edit?$row->LegalHeadAge:""?>" name="LegalHeadAge" id="LegalHeadAge" placeholder="Срок избрания руководителя ЮЛ, в годах" type="number">
      </div>
      <br>
      <div>
        <input value="<?php echo $edit?$row->LegalCapital:""?>" name="LegalCapital" id="LegalCapital" placeholder="Размер уставного капитала, в рублях, без копеек" type="number">
      </div>
      <br>
    </div>
    <h3 class="text-center">Юридический адрес</h3>
    <div><?php sampo_get_Region_Codes(true,$edit?$row->RegionCodeLegal:"0"); ?></div>
    <div><?php sampo_get_CityLegal(true,$edit?$row->CityLegalType:"0"); ?></div>
    <div>
      <input value="<?php echo $edit?$row->CityLegalValue:""?>" name="CityLegalValue" id="CityLegalValue" placeholder="Наименование населенного пункта" type="text">
    </div>
    <br>
    <div><?php sampo_get_District(true); ?></div>
    <div>
      <input value="<?php echo $edit?$row->DistrictLegalValue:""?>" name="DistrictLegalValue" id="DistrictResidentialValue" placeholder="Наименование муниципального района" type="text">
    </div>
    <br>
    <div><?php sampo_get_Settlement(true,$edit?$row->SettlementLegalType:"0"); ?></div>
    <div>
      <input value="<?php echo $edit?$row->SettlementLegalValue:""?>" name="SettlementLegalValue" id="SettlementResidentialValue" placeholder="Нименование поселения" type="text">
    </div>
    <br>
    <div><?php sampo_get_Address(true,$edit?$row->AddressLegalType:"0"); ?></div>
    <div>
      <input  value="<?php echo $edit?$row->AddressLegalValue:""?>" name="AddressLegalValue" id="AddressResidentialValue" placeholder="Намиенование улицы" type="text">
    </div>
    <br>
    <div><?php sampo_get_Building(true,$edit?$row->BuildingLegalType:"0"); ?></div>
    <div>
      <input  value="<?php echo $edit?$row->BuildingLegalValue:""?>" name="BuildingLegalValue" id="BuildingResidentialValue" placeholder="Номер здания" type="text">
    </div>
    <br>
    <div><?php sampo_get_Apartment(true,$edit?$row->ApartmentLegalType:"0"); ?></div>
    <div>
      <input  value="<?php echo $edit?$row->ApartmentLegalValue:""?>" name="ApartmentLegalValue" id="ApartmentResidentialValue" placeholder="Номер помещения" type="text">
    </div>
    <br>
    <div><?php sampo_get_Room(true,$edit?$row->RoomLegalType:"0"); ?></div>
    <div>
      <input  value="<?php echo $edit?$row->RoomLegalValue:""?>" name="RoomLegalValue" id="RoomResidentialValue" placeholder="Номер помещения" type="text">
    </div>
    <br>

    <h3 class="text-center">Фактический адрес</h3>
    <div><?php sampo_get_Region_Codes(false,$edit?$row->RegionCodeResidential:"0"); ?></div>
    <div><?php sampo_get_CityLegal(false,$edit?$row->CityResidentialType:"0"); ?></div>
    <div>
      <input value="<?php echo $edit?$row->CityResidentialValue:""?>"  name="CityResidentialValue" id="CityResidentialValue" placeholder="Наименование населенного пункта" type="text">
    </div>
    <br>
    <div><?php sampo_get_District(false,$edit?$row->DistrictResidentialType:"0"); ?></div>
    <div>
      <input value="<?php echo $edit?$row->DistrictResidentialValue:""?>"  name="DistrictResidentialValue" id="DistrictResidentialValue" placeholder="Наименование муниципального района" type="text">
    </div>
    <br>
    <div><?php sampo_get_Settlement(false,$edit?$row->SettlementResidentialType:"0"); ?></div>
    <div>
      <input value="<?php echo $edit?$row->SettlementResidentialValue:""?>"  name="SettlementResidentialValue" id="SettlementResidentialValue" placeholder="Нименование поселения" type="text">
    </div>
    <br>
    <div><?php sampo_get_Address(false,$edit?$row->AddressResidentialType:"0"); ?></div>
    <div>
      <input value="<?php echo $edit?$row->AddressResidentialValue:""?>"  name="AddressResidentialValue" id="AddressResidentialValue" placeholder="Намиенование улицы" type="text">
    </div>
    <br>
    <div><?php sampo_get_Building(false,$edit?$row->BuildingResidentialType:"0"); ?></div>
    <div>
      <input value="<?php echo $edit?$row->BuildingResidentialValue:""?>"  name="BuildingResidentialValue" id="BuildingResidentialValue" placeholder="Номер здания" type="text">
    </div>
    <br>
    <div><?php sampo_get_Apartment(false,$edit?$row->ApartmentResidentialType:"0"); ?></div>
    <div>
      <input value="<?php echo $edit?$row->ApartmentResidentialValue:""?>"  name="ApartmentResidentialValue" id="ApartmentResidentialValue" placeholder="Номер помещения" type="text">
    </div>
    <br>
    <div><?php sampo_get_Room(false,$edit?$row->RoomResidentialType:"0"); ?></div>
    <div>
      <input value="<?php echo $edit?$row->RoomResidentialValue:""?>"  name="RoomResidentialValue" id="RoomResidentialValue" placeholder="Номер помещения" type="text">
    </div>
    <br>
    <div>
      <input value="<?php echo $edit?$row->Email:""?>"  name="Email" id="Email" placeholder="Emial" type="email">
    </div>
    <br>
    <div>
      <input value="<?php echo $edit?$row->Phone:""?>"  name="Phone" id="Phone" placeholder="Номер здания" type="text">
    </div>
    <br>

    <select onchange="OnTaxTypeChange()" name="TaxSystem" id="TaxSystem">
      <option value="">Ситема налогооблажения</option>
      <option <?php if ($edit && $row->TaxSystem == 1) echo "selected " ?> value="1">УСН упрощенная система налогооблажения</option>
      <option <?php if ($edit && $row->TaxSystem == 2) echo "selected " ?> value="2">ОСНО общая система налогооблажения</option>
    </select>
    <br>
    <div id="TaxObjectGroup">
      <select name="TaxObject" id="TaxObject">
        <option value="">Вид УСН</option>
        <option <?php if ($edit && $row->TaxObject == 1) echo "selected " ?> value="1">Доход 6%</option>
        <option <?php if ($edit && $row->TaxObject == 2) echo "selected " ?> value="2">Доход минус расход</option>
      </select>
    </div>
    <br>
    <input name="HeadFarm" type="hidden">
    <br>
    <div>
      <input value="<?php echo $edit?$row->Okved2Main:""?>"  name="Okved2Main" id="Okved2Main" placeholder="Основной ОКВЭД" type="text">
    </div>
    <br>


    <!-- <h1><?php echo get_current_user_id(); ?></h1> -->
    <div><?php sampo_get_FNS_Codes($edit?$row->FtsDivisionCode:"0"); ?></div>
    <div><input name="ConsentToProcessingPersonalData" type="checkbox">Согласен с обработкой персональных данных</input></div>
    <br>
    <input type="submit" value="Сохранить">
  </form>
<?php
}

add_filter('the_post',"sampo_check_shortcode");

function sampo_check_shortcode($post){
  if( is_admin() )        return $post; // выходим, если админка
	if( empty($post) )     return $post; // выходим, если нет данных
	if( ! is_main_query() ) return $post; // проверяем только для основного запроса
  
  
  if( has_shortcode( $post->post_content, "sampo_sig_files" )) {
       add_action('wp_enqueue_scripts', 'sampo_add_sign_scripts');


  }
  
  
  
  return $post;
}
function sampo_add_sign_scripts(){
  wp_enqueue_script("cadesplugin_api");
  wp_enqueue_script("cryptohelper");
  wp_enqueue_script("sampo-sig-script");
}

add_shortcode('sampo_sig_files', 'sampo_sig_files');

function sampo_get_files_to_sig($order_id){
  $results = $GLOBALS['wpdb']->get_results(
  $GLOBALS['wpdb']->prepare("SELECT FileTypeCodes.type_name, orders_files.url, orders_files.file_name, orders_files.file_type, FileTypeCodes.signature_code,
       (CASE 
           
            WHEN FileTypeCodes.signature_code = 991020 THEN \"sampo_file_upload_passport_sig\"
            WHEN FileTypeCodes.signature_code = 994000 THEN \"sampo_file_upload_uch_sig\"
            WHEN FileTypeCodes.signature_code = 992000 THEN \"sampo_file_upload_ustav_sig\"
            WHEN FileTypeCodes.signature_code = 9911001 THEN \"sampo_file_upload_zayv_fns_ul_sig\"
            WHEN FileTypeCodes.signature_code = 9921001 THEN \"sampo_file_upload_zayv_fns_ip_sig\"
            WHEN FileTypeCodes.signature_code = 991150001 THEN \"sampo_file_upload_usn_sig\"
            WHEN FileTypeCodes.signature_code = 998821 THEN \"sampo_file_upload_xml_ip_sig\"
            WHEN FileTypeCodes.signature_code = 998811 THEN \"sampo_file_upload_xml_ul_sig\"
        END
        ) as file_elem
       FROM orders_files 
       LEFT JOIN FileTypeCodes 
       ON orders_files.file_type = FileTypeCodes.file_code
       WHERE orders_files.order_id = %d AND FileTypeCodes.is_signature = 0 AND FileTypeCodes.signature_code <> 0",$order_id));
  error_log(print_r($results,true));

  return $results;
}


function sampo_sig_files($atts){
  if (!isset($_GET["order_id"]) || empty($_GET["order_id"])) {
    return "<h2>Доступ запрещен!</h2>";
  }
  $order_id = $_GET["order_id"];
  if (!user_has_accses_to_order($order_id)){
    return "<div><h2>Доступ запрещен!</h2><div>";
  }
  
  $files_to_sig = sampo_get_files_to_sig($order_id);
  
  
  

  ?>
  <?php wp_nonce_field( 'sampo_file_upload', 'fileup_nonce' ); ?>
  <input type="hidden" id="order_id" name="order_id" value="<?php echo $order_id; ?>">
  <h3>Подписать файлы</h3>
  <?php 
  $str = "<div>";
  
    foreach ($files_to_sig as $file) {
      $str = $str."<div class=\"flex flex-row mb-4 bg-gray-400	\"><div class=\"basis-1/2 text-black\">$file->type_name</div><div class=\"basis-1/2 bg-gray-400\">".$file->file_name."</div></div>";
    } 
    $str = $str."</div>";
    echo $str;
   ?>
  
  
  <div>
        <select
          onchange="onChengeSertList(event)"
          name="certlist"
          id="sertList"
          class="mb-4"
        >
          <option value="">Выберите сертификат</option>
        </select>
      </div>
      <!-- <input id="filesToSign" type="file" /> -->
      <input class="mb-4" type="button" value="Подписать" onclick="doSigFile()" />
  
  <?php
}

add_action("rest_api_init", "sampo_rest_api_init");

function sampo_rest_api_init()
{
  register_rest_route('sampo-reg-bis/v1', 'register', array(
    'methods' => 'POST',
    'callback' => 'sampo_handle_rgister'
  ));
  register_rest_route('sampo-reg-bis/v1', 'upd_register', array(
    'methods' => 'POST',
    'callback' => 'sampo_handle_upd_rgister'
  ));
  register_rest_route('sampo-reg-bis/v1', 'get_files_to_sig', array(
    'methods' => 'POST',
    'callback' => 'sampo_rest_get_files_to_sig'
  ));

}

function sampo_rest_get_files_to_sig($data){
  // $headers = $data->get_headers();

  // $nonce =  $headers["x_wp_nonce"][0];
  // error_log(print_r($nonce, true));
  
  // if (!wp_verify_nonce($nonce, "sampo_reg_bis_nonce")) {
  //   return new WP_REST_Response("Data not save", 422);
  // }
  $params = $data->get_params();

  $order_id = $params["order_id"];
  if (!user_has_accses_to_order($order_id)){
    error_log(print_r($data,true));
    return new WP_REST_Response("Access denied", 403);
  }
  $params = $data->get_params();
  $files_to_sig = sampo_get_files_to_sig($order_id);
  return new WP_REST_Response($files_to_sig);
}


function filter_evil_char($srting) {}
function sampo_handle_upd_rgister($data)
{
  // $headers = $data->get_headers();

  // $nonce =  $headers["x_wp_nonce"][0];
  // error_log(print_r($nonce, true));
  // if (!wp_verify_nonce($nonce, "sampo_reg_bis_nonce")) {
  //   return new WP_REST_Response("Data not save", 422);
  // }
  $params = $data->get_params();
  //error_log(print_r($params, true));
  $results = $GLOBALS['wpdb']->query(
    $GLOBALS['wpdb']->prepare(
      "UPDATE  orders_registration 
      SET ApplicantType =%d, FirstName =%s, MiddleName =%s, 
      LastName = %s, Gender = %d, BirthDate = %s, PlaceOfBirth = %s,
      IdentityDocumentType = %d, DocumentSeries = %s, DocumentNumber =%s, 
      DocumentIssueDate = %s, DocumentIssuerName = %s, DocumentIssuerCode = %s, 
      InnPersonal = %s, Snils = %s,OrganizationName = %s, 
      OrganizationShortName = %s, LegalHeadPosition = %s, LegalHeadAge = %s, 
      LegalCapital = %s, CountryCodeLegal = %s, RegionCodeLegal = %s, 
      CityLegalType = %d, CityLegalValue = %s, DistrictLegalType = %d,
      DistrictLegalValue = %s, SettlementLegalType = %d, SettlementLegalValue = %s, 
      AddressLegalType = %d, AddressLegalValue =%s, BuildingLegalType = %d, 
      BuildingLegalValue = %s, ApartmentLegalType = %d, ApartmentLegalValue = %s,
      RoomLegalType = %d, RoomLegalValue = %s, CountryCodeResidential = %d,
      RegionCodeResidential = %s, CityResidentialType = %d, CityResidentialValue = %s, 
      DistrictResidentialType = %d, DistrictResidentialValue = %s, SettlementResidentialType = %d,
      SettlementResidentialValue = %s, AddressResidentialType = %d, AddressResidentialValue = %s, 
      BuildingResidentialType = %d, BuildingResidentialValue = %s, ApartmentResidentialType = %d, 
      ApartmentResidentialValue = %s, RoomResidentialType = %d, RoomResidentialValue = %s, 
      Email = %s, Phone = %s, Okved2Main = %s, 
      TaxSystem =  %d, TaxObject =  %d, FtsDivisionCode = %s, 
      HeadFarm = %d, ConsentToProcessingPersonalData = %d
      WHERE user_id =%d AND id =  %d
      ",

      [
        $params['ApplicantType'],
        $params['FirstName'],
        $params['MiddleName'],
        $params['LastName'],
        $params['Gender'],
        $params['BirthDate'],
        $params['PlaceOfBirth'], 
        21,
        $params['DocumentSeries'],
        $params['DocumentNumber'],
        $params['DocumentIssueDate'],
        $params['DocumentIssuerName'],
        $params['DocumentIssuerCode'],
        $params['InnPersonal'],
        $params['Snils'],
        $params['OrganizationName'],
        $params['OrganizationShortName'],
        $params['LegalHeadPosition'],
        $params['LegalHeadAge'],
        $params['LegalCapital'],
        643,
        $params['RegionCodeLegal'],
        $params['CityLegalType'],
        $params['CityLegalValue'],
        $params['DistrictLegalType'],
        $params['DistrictLegalValue'],
        $params['SettlementLegalType'],
        $params['SettlementLegalValue'],
        $params['AddressLegalType'],
        $params['AddressLegalValue'],
        $params['BuildingLegalType'],
        $params['BuildingLegalValue'],
        $params['ApartmentLegalType'],
        $params['ApartmentLegalValue'],
        $params['RoomLegalType'],
        $params['RoomLegalValue'],
        643,
        $params['RegionCodeResidential'],
        $params['CityResidentialType'],
        $params['CityResidentialValue'],
        $params['DistrictResidentialType'],
        $params['DistrictResidentialValue'],
        $params['SettlementResidentialType'],
        $params['SettlementResidentialValue'],
        $params['AddressResidentialType'],
        $params['AddressResidentialValue'],
        $params['BuildingResidentialType'],
        $params['BuildingResidentialValue'],
        $params['ApartmentResidentialType'],
        $params['ApartmentResidentialValue'],
        $params['RoomResidentialType'],
        $params['RoomResidentialValue'],
        $params['Email'],
        $params['Phone'],
        $params['Okved2Main'],
        $params['TaxSystem'],
        $params['TaxObject'],
        $params['FtsDivisionCode'],
        $params['HeadFarm'],
        $params['ConsentToProcessingPersonalData'],
        get_current_user_id(),
        $params['order_id'],
      ]

    )
  );
  error_log(print_r($results, true));
  return  new WP_REST_Response("Data ok", 200);
}

function sampo_handle_rgister($data)
{
  // $headers = $data->get_headers();

  // $nonce =  $headers["x_wp_nonce"][0];
  // error_log(print_r($nonce, true));
  // if (!wp_verify_nonce($nonce, "sampo_reg_bis_nonce")) {
  //   return new WP_REST_Response("Data not save", 422);
  // }
  $params = $data->get_params();
  //error_log(print_r($params, true));
  $results = $GLOBALS['wpdb']->query(
    $GLOBALS['wpdb']->prepare(
      "INSERT INTO orders_registration 
      (ApplicantType, FirstName, MiddleName, 
      LastName, Gender, BirthDate, 
      IdentityDocumentType, DocumentSeries, DocumentNumber, 
      DocumentIssueDate, DocumentIssuerName, DocumentIssuerCode, 
      InnPersonal, Snils ,OrganizationName, 
      OrganizationShortName, LegalHeadPosition, LegalHeadAge, 
      LegalCapital, CountryCodeLegal, RegionCodeLegal, 
      CityLegalType, CityLegalValue, DistrictLegalType,
      DistrictLegalValue, SettlementLegalType, SettlementLegalValue, 
      AddressLegalType, AddressLegalValue, BuildingLegalType, 
      BuildingLegalValue, ApartmentLegalType, ApartmentLegalValue,
      RoomLegalType, RoomLegalValue, CountryCodeResidential,
      RegionCodeResidential, CityResidentialType, CityResidentialValue, 
      DistrictResidentialType, DistrictResidentialValue, SettlementResidentialType,
      SettlementResidentialValue, AddressResidentialType, AddressResidentialValue, 
      BuildingResidentialType, BuildingResidentialValue, ApartmentResidentialType, 
      ApartmentResidentialValue, RoomResidentialType, RoomResidentialValue, 
      Email, Phone, Okved2Main, 
      TaxSystem, TaxObject, FtsDivisionCode, PlaceOfBirth,
      HeadFarm, ConsentToProcessingPersonalData, user_id)
       VALUES (%d, %s, %s, 
               %s, %d, %s, 
               %d, %s, %s, 
               %s, %s, %s, 
               %s, %s, %s, 
               %s, %s, %d, 
               %d, %d, %d, 
               %d, %s, %d,
               %s, %d, %s, 
               %d, %s, %d,
               %s, %d, %s,
               %d, %s, %d,
               %d, %d, %s, 
               %d, %s, %d,
               %s, %d, %s,
               %d, %s, %d,
               %s, %d, %s,
               %s, %s, %s,
               %d, %d, %s, %s,
               %d, %d, %d)",

      [
        $params['ApplicantType'],
        $params['FirstName'],
        $params['MiddleName'],
        $params['LastName'],
        $params['Gender'],
        $params['BirthDate'],
        21,
        $params['DocumentSeries'],
        $params['DocumentNumber'],
        $params['DocumentIssueDate'],
        $params['DocumentIssuerName'],
        $params['DocumentIssuerCode'],
        $params['InnPersonal'],
        $params['Snils'],
        $params['OrganizationName'],
        $params['OrganizationShortName'],
        $params['LegalHeadPosition'],
        $params['LegalHeadAge'],
        $params['LegalCapital'],
        643,
        $params['RegionCodeLegal'],
        $params['CityLegalType'],
        $params['CityLegalValue'],
        $params['DistrictLegalType'],
        $params['DistrictLegalValue'],
        $params['SettlementLegalType'],
        $params['SettlementLegalValue'],
        $params['AddressLegalType'],
        $params['AddressLegalValue'],
        $params['BuildingLegalType'],
        $params['BuildingLegalValue'],
        $params['ApartmentLegalType'],
        $params['ApartmentLegalValue'],
        $params['RoomLegalType'],
        $params['RoomLegalValue'],
        643,
        $params['RegionCodeResidential'],
        $params['CityResidentialType'],
        $params['CityResidentialValue'],
        $params['DistrictResidentialType'],
        $params['DistrictResidentialValue'],
        $params['SettlementResidentialType'],
        $params['SettlementResidentialValue'],
        $params['AddressResidentialType'],
        $params['AddressResidentialValue'],
        $params['BuildingResidentialType'],
        $params['BuildingResidentialValue'],
        $params['ApartmentResidentialType'],
        $params['ApartmentResidentialValue'],
        $params['RoomResidentialType'],
        $params['RoomResidentialValue'],
        $params['Email'],
        $params['Phone'],
        $params['Okved2Main'],
        $params['TaxSystem'],
        $params['TaxObject'],
        $params['FtsDivisionCode'],
        $params['PlaceOfBirth'],
        $params['HeadFarm'],
        $params['ConsentToProcessingPersonalData'],
        get_current_user_id()
      ]

    )
  );
  error_log(print_r($results, true));
  return  new WP_REST_Response("Data ok", 200);
}

// // регистрируем 5минутный интервал
// add_filter( 'cron_schedules', 'cron_add_five_min' );
// function cron_add_five_min( $schedules ) {
// 	$schedules['five_min'] = array(
// 		'interval' => 60 * 5,
// 		'display' => 'Раз в 5 минут'
// 	);
// 	return $schedules;
// }
