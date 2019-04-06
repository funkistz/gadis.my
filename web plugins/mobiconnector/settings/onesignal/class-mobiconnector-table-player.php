<?php 
defined('ABSPATH') or die('Denied');
if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( MOBICONNECTOR_ADMIN_PATH . 'includes/class-wp-list-table.php' );
}
/**
 * Create table for tab player
 */
class BAMobile_MobiconnectorTablePlayer extends WP_List_Table{
	
	/**
	 * MobiconnectorTablePlayer construct
	 */
	public function __construct() {

		$args = array(
			'singular' => __( 'Player','mobiconnector'), //singular name of the listed records
			'plural'   => __( 'Players','mobiconnector'), //plural name of the listed records
			'ajax'     => true //should this table support ajax?
		);
		parent::__construct($args);
	}

	/**
	 * Get all player in database
	 * 
	 * @param int $per_page     number player in one page
	 * @param int $page_number  number page
	 */
	public static function getItemsList( $per_page = 10, $page_number = 1 ) {
		$api = get_option('mobiconnector_settings-onesignal-api');		
		$api = esc_sql($api);
		global $wpdb;
		$table_name = $wpdb->prefix . "mobiconnector_data_api";
		$datas = $wpdb->get_results(
			"
			SELECT * 
			FROM $table_name
			WHERE api_key = '$api'
			"
		);
		$idmobiconnectorapi = 0;
		if(!empty($datas)){
			foreach($datas as $data){
				$idmobiconnectorapi = $data->api_id;
			}	
		}	
		
		$sql = "SELECT * FROM {$wpdb->prefix}mobiconnector_data_player";
		
		$sql .= " WHERE api_id = $idmobiconnectorapi";
		if(!empty($_REQUEST['plist'])){
			if($_REQUEST['plist'] == 'user'){
				$sql .= " AND test_type = 0";
			}elseif($_REQUEST['plist'] == 'test'){
				$sql .= " AND test_type IN (1,2)";
			}else{
				$sql = $sql;
			}
		}
		if ( ! empty( $_REQUEST['orderby'] ) ) {
			$sql .= ' ORDER BY ' . esc_sql( $_REQUEST['orderby'] );
			$sql .= ! empty( $_REQUEST['order'] ) ? ' ' . esc_sql( $_REQUEST['order'] ) : ' ASC';
		}else{
			$sql .= ' ORDER BY identifier';
			$sql .= ' ASC';
		}				
		$sql .= " LIMIT $per_page";
		$sql .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;		
		$datas = $wpdb->get_results( $sql, 'ARRAY_A' );
		
		return $datas;
	}
	
	/**
	 * Returns the count of records in the database.
	 *
	 * @return null|string
	 */
	public function record_count() {
	  global $wpdb;

	  $sql = "SELECT COUNT(*) FROM {$wpdb->prefix}mobiconnector_data_player";

	  return $wpdb->get_var( $sql );
	}
	
	/** Text displayed when no mobiconnector_data data is available */
	public function no_items() {
		_e( 'No players avaliable.', 'mobiconnector' );
	}
	
	/**
	 * Render a column when no column specific method exists.
	 *
	 * @param array $item
	 * @param string $column_name
	 *
	 * @return mixed
	 */
	public function column_default( $item, $column_name ) {
	  switch ( $column_name ) {
		case 'player_id':	
		case 'identifier':
		case 'device_model':
		case 'device_type':	
		case 'device_os':		
		case 'language':			
		case 'session_count':
		case 'test_type':
		case 'mobiconnector_action':
		  return $item[ $column_name ];
		default:
		  return print_r( $item, true ); //Show the whole array for troubleshooting purposes
	  }
	}	
	
	/**
	 * Content column devicec_type
	 * 
	 * @param array $item     details of data
	 */
	function column_device_type($item){
		if($item['device_type'] == 0){
			echo esc_html("iOS");
		}elseif($item['device_type'] == 1){
			echo esc_html("ANDROID");
		}elseif($item['device_type'] == 2){
			echo esc_html("AMAZON");
		}elseif($item['device_type'] == 3){
			echo esc_html("WINDOWSPHONE");
		}elseif($item['device_type'] == 4){
			echo esc_html("CHROME APP");
		}elseif($item['device_type'] == 5){
			echo esc_html("CHROME WEB PUSH");
		}elseif($item['device_type'] == 6){
			echo esc_html("WINDOWSPHONE");
		}elseif($item['device_type'] == 7){
			echo esc_html("SAFARI");
		}elseif($item['device_type'] == 8){
			echo esc_html("FIREFOX");
		}elseif($item['device_type'] == 9){
			echo esc_html("MACOS");
		}		
	}
	
	/**
	 * Content column language
	 * 
	 * @param array $item     details of data
	 */
	function column_language($item){
		$language_codes = array(
			'en' => 'English' , 
			'aa' => 'Afar' , 
			'ab' => 'Abkhazian' , 
			'af' => 'Afrikaans' , 
			'am' => 'Amharic' , 
			'ar' => 'Arabic' , 
			'as' => 'Assamese' , 
			'ay' => 'Aymara' , 
			'az' => 'Azerbaijani' , 
			'ba' => 'Bashkir' , 
			'be' => 'Byelorussian' , 
			'bg' => 'Bulgarian' , 
			'bh' => 'Bihari' , 
			'bi' => 'Bislama' , 
			'bn' => 'Bengali/Bangla' , 
			'bo' => 'Tibetan' , 
			'br' => 'Breton' , 
			'ca' => 'Catalan' , 
			'co' => 'Corsican' , 
			'cs' => 'Czech' , 
			'cy' => 'Welsh' , 
			'da' => 'Danish' , 
			'de' => 'German' , 
			'dz' => 'Bhutani' , 
			'el' => 'Greek' , 
			'eo' => 'Esperanto' , 
			'es' => 'Spanish' , 
			'et' => 'Estonian' , 
			'eu' => 'Basque' , 
			'fa' => 'Persian' , 
			'fi' => 'Finnish' , 
			'fj' => 'Fiji' , 
			'fo' => 'Faeroese' , 
			'fr' => 'French' , 
			'fy' => 'Frisian' , 
			'ga' => 'Irish' , 
			'gd' => 'Scots/Gaelic' , 
			'gl' => 'Galician' , 
			'gn' => 'Guarani' , 
			'gu' => 'Gujarati' , 
			'ha' => 'Hausa' , 
			'hi' => 'Hindi' , 
			'hr' => 'Croatian' , 
			'hu' => 'Hungarian' , 
			'hy' => 'Armenian' , 
			'ia' => 'Interlingua' , 
			'ie' => 'Interlingue' , 
			'ik' => 'Inupiak' , 
			'in' => 'Indonesian' , 
			'is' => 'Icelandic' , 
			'it' => 'Italian' , 
			'iw' => 'Hebrew' , 
			'ja' => 'Japanese' , 
			'ji' => 'Yiddish' , 
			'jw' => 'Javanese' , 
			'ka' => 'Georgian' , 
			'kk' => 'Kazakh' , 
			'kl' => 'Greenlandic' , 
			'km' => 'Cambodian' , 
			'kn' => 'Kannada' , 
			'ko' => 'Korean' , 
			'ks' => 'Kashmiri' , 
			'ku' => 'Kurdish' , 
			'ky' => 'Kirghiz' , 
			'la' => 'Latin' , 
			'ln' => 'Lingala' , 
			'lo' => 'Laothian' , 
			'lt' => 'Lithuanian' , 
			'lv' => 'Latvian/Lettish' , 
			'mg' => 'Malagasy' , 
			'mi' => 'Maori' , 
			'mk' => 'Macedonian' , 
			'ml' => 'Malayalam' , 
			'mn' => 'Mongolian' , 
			'mo' => 'Moldavian' , 
			'mr' => 'Marathi' , 
			'ms' => 'Malay' , 
			'mt' => 'Maltese' , 
			'my' => 'Burmese' , 
			'na' => 'Nauru' , 
			'ne' => 'Nepali' , 
			'nl' => 'Dutch' , 
			'no' => 'Norwegian' , 
			'oc' => 'Occitan' , 
			'om' => '(Afan)/Oromoor/Oriya' , 
			'pa' => 'Punjabi' , 
			'pl' => 'Polish' , 
			'ps' => 'Pashto/Pushto' , 
			'pt' => 'Portuguese' , 
			'qu' => 'Quechua' , 
			'rm' => 'Rhaeto-Romance' , 
			'rn' => 'Kirundi' , 
			'ro' => 'Romanian' , 
			'ru' => 'Russian' , 
			'rw' => 'Kinyarwanda' , 
			'sa' => 'Sanskrit' , 
			'sd' => 'Sindhi' , 
			'sg' => 'Sangro' , 
			'sh' => 'Serbo-Croatian' , 
			'si' => 'Singhalese' , 
			'sk' => 'Slovak' , 
			'sl' => 'Slovenian' , 
			'sm' => 'Samoan' , 
			'sn' => 'Shona' , 
			'so' => 'Somali' , 
			'sq' => 'Albanian' , 
			'sr' => 'Serbian' , 
			'ss' => 'Siswati' , 
			'st' => 'Sesotho' , 
			'su' => 'Sundanese' , 
			'sv' => 'Swedish' , 
			'sw' => 'Swahili' , 
			'ta' => 'Tamil' , 
			'te' => 'Tegulu' , 
			'tg' => 'Tajik' , 
			'th' => 'Thai' , 
			'ti' => 'Tigrinya' , 
			'tk' => 'Turkmen' , 
			'tl' => 'Tagalog' , 
			'tn' => 'Setswana' , 
			'to' => 'Tonga' , 
			'tr' => 'Turkish' , 
			'ts' => 'Tsonga' , 
			'tt' => 'Tatar' , 
			'tw' => 'Twi' , 
			'uk' => 'Ukrainian' , 
			'ur' => 'Urdu' , 
			'uz' => 'Uzbek' , 
			'vi' => 'Vietnamese' , 
			'vo' => 'Volapuk' , 
			'wo' => 'Wolof' , 
			'xh' => 'Xhosa' , 
			'yo' => 'Yoruba' , 
			'zh' => 'Chinese' , 
			'zu' => 'Zulu' , 
        );	
		foreach($language_codes as $code => $name){
			if($item['language'] == $code){
				echo $name;
			}
		}			
	}
	
	/**
	 * Content column test_type
	 * 
	 * @param array $item     details of data
	 */
	function column_test_type($item){
		if($item['test_type'] == 1 || $item['test_type'] == 2){
			echo esc_html("Yes");
		}else{
			echo esc_html("No");
		}
	}

	/**
	 * Content column identifier
	 * 
	 * @param array $item     details of data
	 */
	function column_identifier($item){
		if($item['identifier'] == 1){
			echo '<span class="dashicons dashicons-no-alt"></span>';
		}else{
			echo '<span class="dashicons dashicons-yes"></span>';
		}
	}
	
	/**
	 * Content column action of mobiconnector
	 * 
	 * @param array $item     details of data
	 */
	function column_mobiconnector_action($item){
		$w_nonce = wp_create_nonce( 'mobiconnector_change_testtype' );
		$link = '?page='.esc_attr( $_REQUEST['page'] ).'&mtask=changeTesttype&player='. $item['player_id'] .'&device='.$item['device_model'].'&mtab=onesignal&actions=player&_wpnonce='.$w_nonce;
		switch($item['test_type']){
		case 0: return '<a class="button" href="'.$link.'&sections=addtotest">Add to Test</a>';
				break;
		case 1: return '<a class="button" href="'.$link.'&sections=deletetotest">Delete from Test</a>';
				break;
		case 2: return '<a class="button" href="'.$link.'&sections=deletetotest">Delete from Test</a>';
			break;
	  }
	}
	
	/**
	 * Get submenu list player by type
	 */
	protected function get_views() { 
		$current_page = isset($_REQUEST['plist']) ? $_REQUEST['plist'] : 'all';
		if($current_page == 'all'){
			$classall = "class='current'";
		}else{
			$classall = null;
		}
		if($current_page == 'user'){
			$classuser = "class='current'";
		}else{
			$classuser = null;
		}
		if($current_page == 'test'){
			$classtest = "class='current'";
		}else{
			$classtest = null;
		}
		global $wpdb;
		$api = get_option('mobiconnector_settings-onesignal-api');	
		$table_name = $wpdb->prefix . "mobiconnector_data_api";
		$datas = $wpdb->get_results(
			"
			SELECT * 
			FROM $table_name
			WHERE api_key = '$api'
			"
		);
		$idmobiconnectorapi = 0;
		if(!empty($datas)){
			foreach($datas as $data){
				$idmobiconnectorapi = $data->api_id;
			}	
		}	
		$all = "SELECT COUNT(*) FROM {$wpdb->prefix}mobiconnector_data_player WHERE api_id = $idmobiconnectorapi";
		$countall = $wpdb->get_var( $all );	
		$user = "SELECT COUNT(*) FROM {$wpdb->prefix}mobiconnector_data_player WHERE api_id = $idmobiconnectorapi AND test_type = 0";
		$countuser = $wpdb->get_var( $user );	
		$test = "SELECT COUNT(*) FROM {$wpdb->prefix}mobiconnector_data_player WHERE api_id = $idmobiconnectorapi AND test_type IN (1,2)";
		$counttest = $wpdb->get_var( $test );	
		$status_links = array(
			"all"   => __("<a ".$classall." href='?page=mobiconnector-settings&mtab=onesignal&actions=player&plist=all'>All (".$countall.")</a>",'mobiconnector'),
			"users" => __("<a ".$classuser." href='?page=mobiconnector-settings&mtab=onesignal&actions=player&plist=user'>List Users (".$countuser.")</a>",'mobiconnector'),
			"test"  => __("<a ".$classtest." href='?page=mobiconnector-settings&mtab=onesignal&actions=player&plist=test'>List User Test (".$counttest.")</a>",'mobiconnector')
		);
		return $status_links;
	}
	
	/**
	 *  Associative array of columns
	 *
	 * @return array
	 */
	function get_columns() {
	  $columns = array(
			'player_id'    => __( 'PlayerID', 'mobiconnector' ),
			'identifier'   => __('Subscribed','mobiconnector'),			
			'device_model' => __( 'Device', 'mobiconnector' ),
			'device_type' => __( 'Platform', 'mobiconnector' ),
			'device_os' => __( 'Os', 'mobiconnector' ),			
			'language' => __( 'Language', 'mobiconnector' ),	
			'session_count' => __( 'Session Count', 'mobiconnector' ),
			'test_type' => __( 'Player Test', 'mobiconnector' ),
			'mobiconnector_action' => __('Action','mobiconnector')
	  );

	  return $columns;
	}
	/**
	 * Columns to make sortable.
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
	  $sortable_columns = array(
		'player_id' => array( 'player_id', false ),
		'identifier' => array('identifier',false),
		'device_model' => array( 'device_model', false ),
		'device_type' => array('device_type',false)
	  );

	  return $sortable_columns;
	}
	
	/**
	 * Handles data query and filter, sorting, and pagination.
	 */
	public function prepare_items() {

	  	$columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = array($columns, $hidden, $sortable);	  

		$per_page     = $this->get_items_per_page( 'mobiconnector_per_page', 20 );
		$current_page = $this->get_pagenum();
		$total_items  = self::record_count();

		$this->set_pagination_args( array(
			'total_items' => $total_items, //WE have to calculate the total number of items
			'per_page'    => $per_page //WE have to determine how many items to show on a page
		) );


		$this->items = self::getItemsList( $per_page, $current_page );
	}
}
?>