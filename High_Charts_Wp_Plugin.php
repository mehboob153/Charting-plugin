<?php 
/*
Plugin Name: High Charts Wp Plugin
Plugin URI: http://www.veteranlogix.com
Description: This Plugins uses High Charts API to genrates Charts  .
Author: Mahboob Ur Rehman
Version: 1.0.0
Author URI: http://www.veteranlogix.com
*/
define('HC_BASENAME', trailingslashit(basename(dirname(__FILE__))));
define('HC_DIR', WP_CONTENT_DIR . '/plugins/' . HC_BASENAME);
define('HC_URL', WP_CONTENT_URL . '/plugins/' . HC_BASENAME);
if(!class_exists('WP_High_Charts')){
	
	/**
	* Wp_High_Charst class
	*/

	class WP_High_Charts {

		public function __construct() {

			register_activation_hook(__FILE__, array(&$this,'highcharts_plugin_install'));
			register_deactivation_hook( __FILE__, array(&$this,'highcharts_plugin_uninstall' ));
			add_action('admin_menu', array(&$this,'highcharts_admin_menu' ));
			add_action('wp_ajax_ajax_preview_new_chart', array(&$this,'ajax_preview_new_chart'));
			add_action('wp_ajax_nopriv_ajax_preview_new_chart', array(&$this,'ajax_preview_new_chart'));
			add_action('wp_ajax_ajax_create_new_chart', array(&$this,'ajax_create_new_chart'));
			add_action('wp_ajax_nopriv_ajax_create_new_chart', array(&$this,'ajax_create_new_chart'));
			add_action( 'wp_enqueue_scripts', array(&$this,'hc_theme_name_scripts') );
			add_shortcode("HIGH_CHARTS",array(&$this,'high_charts_shortcode_function'));

		}
		public function hc_theme_name_scripts(){
			 	wp_deregister_script('jquery');
			    wp_register_script('jquery', "http" . ($_SERVER['SERVER_PORT'] == 443 ? "s" : "") . "://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js", false, null);
			    wp_enqueue_script('jquery');
			    wp_enqueue_script('jquery-ui-core');
				wp_enqueue_script( 'script-highcharts', HC_URL.'js/highcharts.js' );
				wp_enqueue_script( 'script-highcharts-more', HC_URL.'js/highcharts-more.js' );
				wp_enqueue_script( 'script-exporting', HC_URL.'js/modules/exporting.js' );
				wp_enqueue_script( 'script-drilldown', HC_URL.'js/modules/drilldown.js' );

		}
		public function highcharts_plugin_install() {

			global $wpdb;
		    $highcharts_table = $wpdb->prefix . 'highcharts';

		    if( $wpdb->get_var( "SHOW TABLES LIKE '".$highcharts_table. "'") != $highcharts_table ) {
		        $create_highcharts_table ="CREATE TABLE $highcharts_table (              
		                 `id` bigint(20) NOT NULL AUTO_INCREMENT,  
		                 `creation_date`  datetime NOT NULL DEFAULT '0000-00-00 00:00:00', 
		                 `type`  char(100) NOT NULL DEFAULT 'area chart',
	                     `title`  varchar(255) NOT NULL,
	                     `subtitle`  varchar(255) NOT NULL,
	                     `xAxis_title` varchar(255) NOT NULL,
	                     `yAxis_title`  varchar(255) NOT NULL,
	                     `xAxisCats` longtext NOT NULL,
	                     `yAxisLabels` longtext NOT NULL,
		                 `shortcode_name` varchar(255) NOT NULL, 
		                 `series` longtext NOT NULL,
                    	 `hotSeries` longtext NOT NULL,
                    	 `drilldownseries` longtext NOT NULL,
                    	 `drilldownhotSeries` longtext NOT NULL,       
                    	 `chart_options` longtext NOT NULL,      
		                 PRIMARY KEY (`id`)                        
		               )";
		        $wpdb->query($create_highcharts_table);
		    }

		}
		public function highcharts_plugin_uninstall() {

			 global $wpdb;
		     $highcharts_table = $wpdb->prefix."highcharts";
		     $wpdb->query("DROP TABLE IF EXISTS $highcharts_table");

		}

		public function highcharts_admin_menu() {

		    add_menu_page('HC Charts', 'HC Charts',"administrator", 'highcharts',array(&$this,'high_charts_dashboard'),HC_URL.'/images/hc_icon.png' );
		    add_submenu_page("highcharts", "Current Charts", "Current Charts", "administrator", "current-charts", array(&$this,"current_charts") );
		    add_submenu_page("highcharts", "New Chart", "New Chart", "administrator", "create_new_chart", array(&$this,"create_new_chart"));

		}

		public function high_charts_dashboard() {

			?>
			<h1>High Charts Plugin Dashboard</h1>
			<p>This plugin is used to create high charts .</p>
			<p>User can select different options to creates chart of their choice .</p>
			<p>This plugin generates a shortcode which can be used on front end to show chart</p>
			<?php
		}

		public function current_charts(){

			global $wpdb;
			$this->load_highcharts_stylesheet();
			$shortcode_table 		= $wpdb->prefix."highcharts";

			if(isset($_GET['highcharts_action']) && !empty($_GET['highcharts_action'])){		$highcharts_action   = $_GET['highcharts_action'];		}

			if(isset($_GET['id']) && !empty($_GET['id'])){

				$id     =   $_GET['id'];

			}

			if(isset($highcharts_action) && isset($id) && !empty($highcharts_action) && !empty($id)){

				if($highcharts_action == 'duplicate'){
					$wpdb->query("INSERT INTO $shortcode_table(creation_date,type,title,subtitle,xAxis_title,yAxis_title,xAxisCats,yAxisLabels,shortcode_name,series,hotSeries,chart_options) 
						SELECT NOW(),type,title,subtitle,xAxis_title,yAxis_title,xAxisCats,yAxisLabels,shortcode_name,series,hotSeries,drilldownseries,drilldownhotSeries,chart_options FROM $shortcode_table WHERE id=$id");

				}
				if($highcharts_action == 'delete'){
					
					$wpdb->query("DELETE FROM $shortcode_table WHERE id=$id;");

				}

			}
			$current_charts_query   = "SELECT * FROM $shortcode_table order by id ASC";
			$current_charts         =  $wpdb->get_results($current_charts_query);

			?>

				<h1>Currently Created High Charts</h1>
				<p>You can use this page to play with currently created charts</p>
				<table class="wrap widefat">
				<tr>
					<th>Chart Type</th>
					<th>Creation Date</th>
					<th>Chart Title</th>
					<th>Chart SubTitle</th>
					<th>Chart xAxis</th>
					<th>Chart yAxis</th>
					<th>Chart Shortcode</th>
					<th>View/Edit | Duplicate | Delete </th>
					
				</tr>
					<?php 
						if(count($current_charts) > 0){
						 	foreach ($current_charts as $key => $chart) {
						 ?>
						<tr>
							<td><?php echo $chart->type; ?></td>
							<td><?php echo $chart->creation_date; ?></td>
							<td><?php echo $chart->title; ?></td>
							<td><?php echo $chart->subtitle; ?></td>
							<td><?php echo $chart->xAxis_title; ?></td>
							<td><?php echo $chart->yAxis_title; ?></td>
							<td><?php echo "[HIGH_CHARTS id='".$chart->id."']";  ?></td>
							<td>
								<a href="<?php echo admin_url().'admin.php?page=create_new_chart&id='.$chart->id; ?>">View/Edit</a> |
								<a href="<?php echo admin_url().'admin.php?page=current-charts&highcharts_action=duplicate&id='.$chart->id; ?>">Duplicate</a> |
								<a href="<?php echo admin_url().'admin.php?page=current-charts&highcharts_action=delete&id='.$chart->id; ?>">Delete</a>
							</td>
						</tr>
						<?php 
							} 
						} else {
							echo "<tr><td colspan='8'>No Chart Created yet !</td></td>";
						}
					 ?>
				</table>
			<?php

		}

		public function create_new_chart(){
			$this->load_highcharts_stylesheet();
			if (isset($_GET['id']) && !empty($_GET['id'])) {
				global $wpdb;
				$id = $_GET['id'];
				$hc_table = $wpdb->prefix."highcharts";
				$query    = "SELECT * FROM $hc_table WHERE id=$id";
				$chart_items = $wpdb->get_results($query);
				foreach ($chart_items as $key => $item) {
					$creation_date  			= $item->creation_date;
					$chart_type  				= $item->type;
					$chart_title  				= $item->title;
					$chart_sub_title  			= $item->subtitle;
					$chart_shortcode  			= $item->shortcode_name;
					$chart_xAxis  				= $item->xAxis_title;
					$chart_yAxis  				= $item->yAxis_title;
					$chart_xAxisCats    		= $item->xAxisCats;
					$chart_series  				= $item->series;
					$chart_data  				= $item->hotSeries;
					$chart_drilldown_series  	= $item->drilldownseries;
					$chart_drilldown_data  		= $item->drilldownhotSeries;
					$chart_option  	    		= $item->chart_options;
				}
				if($chart_drilldown_data == ''){
					$chart_drilldown_data = ' ';
				}
				$chart_options          		=   	json_decode($chart_option,true);
				$chart_background_color 		=   	$chart_options['chart_background_color'];
				$chart_border_color     		=   	$chart_options['chart_border_color'];
				$chart_border_width     		=   	$chart_options['chart_border_width'];
				$chart_border_radious   		=   	$chart_options['chart_border_radious'];
				$chart_allow_point_select    	= 		$chart_options['chart_allow_point_select'];
				$chart_animation 		    	= 		$chart_options['chart_animation'];
				$chart_animation_duration    	= 		$chart_options['chart_animation_duration'];
				$chart_point_brightness    		= 		$chart_options['chart_point_brightness'];
				$bar_border_color 		    	= 		$chart_options['bar_border_color'];
				$bar_border_width 		    	= 		$chart_options['bar_border_width'];
				$bar_border_radious 		    = 		$chart_options['bar_border_radious'];
				$chart_series_generl_color   	= 		$chart_options['chart_series_generl_color'];
				$chart_series_color 	        =   	$chart_options['chart_series_color'];
				$chart_cursor_pointer 	    	=       $chart_options['chart_cursor_pointer'];
				$chart_cursor_event_text     	=     	$chart_options['chart_cursor_event_text'];
				$chart_series_negative_color 	= 		$chart_options['chart_series_negative_color'];
				$column_point_padding 			= 		$chart_options['column_point_padding'];
				$chart_series_legend 			= 		$chart_options['chart_series_legend'];
				$chart_series_stacking 			= 		$chart_options['chart_series_stacking'];
				$intial_visibility 				= 		$chart_options['intial_visibility'];
				$chart_start_engle 				= 		$chart_options['chart_start_engle'];
				$chart_end_engle 				= 		$chart_options['chart_end_engle'];
				$chart_inner_size 				= 		$chart_options['chart_inner_size'];
				$show_checkbox 					= 		$chart_options['show_checkbox'];
				$check_box_series 				= 		$chart_options['check_box_series'];
				$pie_sliced 					= 		$chart_options['pie_sliced'];
				$pie_legend 					= 		$chart_options['pie_legend'];
				$pie_sliced_offset 				= 		$chart_options['pie_sliced_offset'];
				$columns_grouping 				= 		$chart_options['columns_grouping'];
				$chart_data_labels 				= 		$chart_options['chart_data_labels'];
				$chart_tooltip 					= 		$chart_options['chart_tooltip'];
				$chart_tooltip_crosshairs 		= 		$chart_options['chart_tooltip_crosshairs'];
				$chart_credit 					= 		$chart_options['chart_credit'];
				$credit_text 					= 		$chart_options['credit_text'];
				$credit_href 					= 		$chart_options['credit_href'];
				$navigation_buttons 			= 		$chart_options['navigation_buttons'];
				$value_decimals 				= 		$chart_options['value_decimals'];
				$value_prefix 					= 		$chart_options['value_prefix'];
				$value_suffix 					= 		$chart_options['value_suffix'];
				$chart_drilldown 				= 		$chart_options['chart_drilldown'];
				$chart_drilldown_series 		= 		$chart_options['chart_drilldown_series'];
				$chart_drilldown_text     		= 		$chart_options['chart_drilldown_text'];
				
				
			}
			?>
			
			<link rel="stylesheet" href="<?php echo HC_URL.'colorpicker/css/colorpicker.css';  ?>">
			<script src="<?php echo HC_URL.'js/jquery.min.js'; ?>"></script>
			<script src="<?php echo HC_URL.'js/jquery-ui.js'; ?>"></script>
			<script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.5/jquery-ui.min.js"></script>
			<script src="<?php echo HC_URL.'js/jquery.handsontable.full.js'; ?>"></script>
			<script src="<?php echo HC_URL.'js/highcharts.js'; ?>"></script>
			<script src="<?php echo HC_URL.'js/highcharts-more.js'; ?>"></script>
		    <script src="<?php echo HC_URL.'js/modules/exporting.js'; ?>"></script>
		    <script src="<?php echo HC_URL.'js/modules/drilldown.js'; ?>"></script>
		    <script src="<?php echo HC_URL.'js/main.js'; ?>"></script>
		    <script src="<?php echo HC_URL.'colorpicker/js/colorpicker.js'; ?>" type="text/javascript"></script>
		    <link rel="stylesheet" media="screen" href="<?php echo HC_URL.'css/jquery.handsontable.full.css'; ?>">
			<link rel="stylesheet" href="<?php echo HC_URL.'css/jquery-ui.css';  ?>">
			<script type="text/javascript">
			jQuery(document).ready(function($) {

				jQuery('#chart_background_color').ColorPicker({
					onSubmit: function(hsb, hex, rgb, el) {
						jQuery(el).val('#'+hex);
						jQuery(el).ColorPickerHide();
					    update_highchart(jQuery("#chart_type option:selected").val());
					},
					onBeforeShow: function () {
						jQuery(this).ColorPickerSetColor(this.value);
					},
					onChange: function (hsb, hex, rgb) {
						jQuery('#chart_background_color').css('backgroundColor', '#' + hex);
						
					}
				})
				.bind('keyup', function(){
					jQuery(this).ColorPickerSetColor(this.value);

				});

				jQuery('#chart_border_color').ColorPicker({
					onSubmit: function(hsb, hex, rgb, el) {
						jQuery(el).val('#'+hex);
						jQuery(el).ColorPickerHide();
						update_highchart(jQuery("#chart_type option:selected").val());
					},
					onBeforeShow: function () {
						jQuery(this).ColorPickerSetColor(this.value);
					},
					onChange: function (hsb, hex, rgb) {
						jQuery('#chart_border_color').css('backgroundColor', '#' + hex);
						
					}
				})
				.bind('keyup', function(){
					jQuery(this).ColorPickerSetColor(this.value);
					
				});

				jQuery('#chart_series_negative_color').ColorPicker({
					onSubmit: function(hsb, hex, rgb, el) {
						jQuery(el).val('#'+hex);
						jQuery(el).ColorPickerHide();
						update_highchart(jQuery("#chart_type option:selected").val());
					},
					onBeforeShow: function () {
						jQuery(this).ColorPickerSetColor(this.value);
					},
					onChange: function (hsb, hex, rgb) {
						jQuery('#chart_series_negative_color').css('backgroundColor', '#' + hex);
						
					}
				})
				.bind('keyup', function(){
					jQuery(this).ColorPickerSetColor(this.value);
					
				});


				jQuery('#chart_series_generl_color').ColorPicker({
					onSubmit: function(hsb, hex, rgb, el) {
						jQuery(el).val('#'+hex);
						jQuery(el).ColorPickerHide();
						update_highchart(jQuery("#chart_type option:selected").val());
					},
					onBeforeShow: function () {
						jQuery(this).ColorPickerSetColor(this.value);
					},
					onChange: function (hsb, hex, rgb) {
						jQuery('#chart_series_generl_color').css('backgroundColor', '#' + hex);
						
					}
				})
				.bind('keyup', function(){
					jQuery(this).ColorPickerSetColor(this.value);
					
				});


				jQuery('#chart_series_negative_color').ColorPicker({
					onSubmit: function(hsb, hex, rgb, el) {
						jQuery(el).val('#'+hex);
						jQuery(el).ColorPickerHide();
						update_highchart(jQuery("#chart_type option:selected").val());
					},
					onBeforeShow: function () {
						jQuery(this).ColorPickerSetColor(this.value);
					},
					onChange: function (hsb, hex, rgb) {
						jQuery('#chart_series_negative_color').css('backgroundColor', '#' + hex);
						
					}
				})
				.bind('keyup', function(){
					jQuery(this).ColorPickerSetColor(this.value);
					
				});

				jQuery('#bar_border_color').ColorPicker({
					onSubmit: function(hsb, hex, rgb, el) {
						jQuery(el).val('#'+hex);
						jQuery(el).ColorPickerHide();
						update_highchart(jQuery("#chart_type option:selected").val());
					},
					onBeforeShow: function () {
						jQuery(this).ColorPickerSetColor(this.value);
					},
					onChange: function (hsb, hex, rgb) {
						jQuery('#bar_border_color').css('backgroundColor', '#' + hex);
						
					}
				})
				.bind('keyup', function(){
					jQuery(this).ColorPickerSetColor(this.value);
					
				});


				<?php if (!$_GET['id']) { ?>
					var chart_type = "area chart";
					jQuery("#chart_title").val("US and USSR nuclear stockpiles");
					jQuery("#chart_sub_title").val('Source: <a href="http://thebulletin.metapress.com/content/c4120650912x74k7/fulltext.pdf">'+
			                    'thebulletin.metapress.com</a>');
					jQuery("#chart_xAxis").val("Years");
					jQuery("#chart_yAxis").val('Nuclear weapon states');
					jQuery("#chart_shortcode").val("Area Chart");
					jQuery("#container").css("display","block");
					jQuery("#table-sparkline").css("display","none");

					jQuery("#chart_background_color").val('#FFFFFF');
					jQuery("#chart_border_color").val('#4b4b4b');
					jQuery("#chart_border_width").val('0');
					jQuery("#chart_border_radious").val('0');
					jQuery("#chart_allow_point_select").val('false');
					jQuery("#chart_animation").val('easeOutBounce');
					jQuery("#chart_animation_duration").val('1000');
					jQuery("#chart_point_brightness").val('0.50');
					jQuery("#bar_border_color").val('#FF0000');
					jQuery("#bar_border_width").val('1');
					jQuery("#bar_border_radious").val('1');
					jQuery("#chart_series_generl_color").val('#818181');
					jQuery("#chart_series_color").val('#818181,#818181');
					jQuery("#chart_cursor_pointer").val('pointer');
					jQuery("#chart_cursor_event_text").val('You just clicked the chart');
					jQuery("#chart_series_negative_color").val('#f50a0a');
					jQuery("#column_point_padding").val('0.20');
					jQuery("#chart_series_legend").val('true,true');
					jQuery("#chart_series_stacking").val('normal');
					jQuery("#intial_visibility").val('true,false');
					jQuery("#chart_start_engle").val('90');
					jQuery("#chart_end_engle").val('350');
					jQuery("#chart_inner_size").val('10');
					jQuery("#show_checkbox").val('false');
					jQuery("#check_box_series").val('false,false');
					jQuery("#pie_sliced").val('true');
					jQuery("#pie_legend").val('true');
					jQuery("#pie_sliced_offset").val('20');
					jQuery("#columns_grouping").val('true');
					jQuery("#chart_data_labels").val('false');
					jQuery("#chart_tooltip").val('true');
					jQuery("#chart_tooltip_crosshairs").val('true');
					jQuery("#chart_credit").val('true');
					jQuery("#credit_text").val('http://derwentscotch.com');
					jQuery("#credit_href").val('http://derwentscotch.com');
					jQuery("#navigation_buttons").val('false');
					jQuery("#value_decimals").val('2');
					jQuery("#value_prefix").val('x');
					jQuery("#value_suffix").val('y');
					jQuery("#chart_drilldown").val('false');
					jQuery("chart_drilldown option[value='false']").attr("selected","selected");
					jQuery("#chart_drilldown_text").val('Back to main series');


					var chart_title 	= jQuery("#chart_title").val(); 
					var chart_sub_title = jQuery("#chart_sub_title").val();
					var chart_xAxis 	= jQuery("#chart_xAxis").val();
					var chart_yAxis 	= jQuery("#chart_yAxis").val();
					var chart_shortcode =  jQuery("#chart_shortcode").val();

					var chart_background_color 			= jQuery("#chart_background_color").val();
					var chart_border_color 				= jQuery("#chart_border_color").val();
					var chart_border_width 				= jQuery("#chart_border_width").val();
					var chart_border_radious 			= jQuery("#chart_border_radious").val();
					var chart_allow_point_select 		= jQuery("#chart_allow_point_select option:selected").val();
					var chart_animation 				= jQuery("#chart_animation").val();
					var chart_animation_duration 		= jQuery("#chart_animation_duration").val();
					var chart_point_brightness 			= jQuery("#chart_point_brightness").val();
					var bar_border_color 				= jQuery("#bar_border_color").val();
					var bar_border_width 				= jQuery("#bar_border_width").val();
					var bar_border_radious 				= jQuery("#bar_border_radious").val();
					var chart_series_generl_color 		= jQuery("#chart_series_generl_color").val();
					var chart_series_color 	        	= jQuery("#chart_series_color").val();
					var chart_cursor_pointer 	        = jQuery("#chart_cursor_pointer").val();
					var chart_cursor_event_text 	    = jQuery("#chart_cursor_event_text").val();
					var chart_series_negative_color 	= jQuery("#chart_series_negative_color").val();
					var column_point_padding 			= jQuery("#column_point_padding").val();
					var chart_series_legend 			= jQuery("#chart_series_legend").val();
					var chart_series_stacking 			= jQuery("#chart_series_stacking").val();
					var intial_visibility 				= jQuery("#intial_visibility").val();
					var chart_start_engle 				= jQuery("#chart_start_engle").val();
					var chart_end_engle 				= jQuery("#chart_end_engle").val();
					var chart_inner_size 				= jQuery("#chart_inner_size").val();
					var show_checkbox 					= jQuery("#show_checkbox").val();
					var check_box_series 				= jQuery("#check_box_series").val();
					var pie_sliced 						= jQuery("#pie_sliced").val();
					var pie_legend 						= jQuery("#pie_legend").val();
					var pie_sliced_offset 				= jQuery("#pie_sliced_offset").val();
					var columns_grouping 				= jQuery("#columns_grouping").val();
					var chart_data_labels 				= jQuery("#chart_data_labels").val();
					var chart_tooltip 					= jQuery("#chart_tooltip").val();
					var chart_tooltip_crosshairs 		= jQuery("#chart_tooltip_crosshairs").val();
					var chart_credit 					= jQuery("#chart_credit").val();
					var credit_text 					= jQuery("#credit_text").val();
					var credit_href 					= jQuery("#credit_href").val();
					var navigation_buttons 				= jQuery("#navigation_buttons").val();
					var value_decimals 					= jQuery("#value_decimals").val();
					var value_prefix 					= jQuery("#value_prefix").val();
					var value_suffix 					= jQuery("#value_suffix").val();
					var chart_drilldown 				= jQuery("#chart_drilldown").val();
					var chart_drilldown_text 			= jQuery("#chart_drilldown_text").val();

				    if(chart_allow_point_select == 'true'){ chart_allow_point_select = true; } else { chart_allow_point_select = false; }

				    if(show_checkbox == 'true'){ show_checkbox = true; } else { show_checkbox = false; }

				    if(columns_grouping == 'true'){ columns_grouping = true; } else { columns_grouping = false; }

				    if(chart_credit == 'true'){ chart_credit = true; } else { chart_credit = false; }

				    if(navigation_buttons == 'true'){ navigation_buttons = true; } else { navigation_buttons = false; }

				    if(chart_data_labels == 'true'){ chart_data_labels = true; } else { chart_data_labels = false; }

				    if(chart_tooltip == 'true'){ chart_tooltip = true; } else { chart_tooltip = false; }
				    if(pie_legend == 'true'){ pie_legend = true; } else { pie_legend = false; }


					jQuery('#chart_drilldown').parent().parent().css('display','none');
					jQuery('#chart_drilldown_text').parent().parent().css('display','none');
					jQuery('#chart-drildown-data').css('display','none');

					jQuery('#chart_start_engle').parent().parent().css('display','none');
					jQuery('#chart_end_engle').parent().parent().css('display','none');
					jQuery('#chart_inner_size').parent().parent().css('display','none');
					jQuery('#pie_sliced').parent().parent().css('display','none');
					jQuery('#pie_legend').parent().parent().css('display','none');
					jQuery('#pie_sliced_offset').parent().parent().css('display','none');
					jQuery('#bar_border_color').parent().parent().css('display','none');
					jQuery('#bar_border_width').parent().parent().css('display','none');
					jQuery('#bar_border_radious').parent().parent().css('display','none');

					var data = [
						  ["", "1996", "1997","1998","1999","2000","2001","2002","2003","2004","2005","2006","2007","2008","2009","2010","2011"],
						  ["USA", 6, 32,110,235,369,640,1005,1436,2063,31056,31982,31233,25722,27342,23586,23586],
						  ["Russia", 11, 2905, 2517, 2422, 2941, 2905, 2517, 2422, 2941, 2905, 2517, 2422, 2941, 2905, 2517,23586]
						];

			        $('#dataTable').handsontable({
			            data: data,
			            minSpareRows: 1,
					    minSpareCols: 1,
					    colHeaders: true,
					    contextMenu: true,
			            cells: function (row, col, prop) {
					      var cellProperties = {};
					      if ($("#dataTable").handsontable('getData')[row][prop] === '') {
					        cellProperties.readOnly = true;
					      }
					      return cellProperties;
					    },
			            onChange: function (changes, source) {
			            	if(changes != null){
			                	update_highchart(chart_type);
			            	}
			            }
			        });

			        var seriesArr       = [];
			        var theData         = $("#dataTable").data('handsontable').getData();
			        var theXCats        = $.extend(true, [], theData[0]);
			 		theXCats            = theXCats.splice(1,theXCats.length-2);
			        var theNewData      = [];
			        var buildNewData    = $.map(theData, function(item, i) {
			            if (i > 0 && i < theData.length-1) {
			                theNewData.push(item);
			            }
			        });
			        var theYCats    = [];
			        var buildYCats  = $.map(theNewData, function(item, i) {
			            theYCats.push(item[0]);
			        });
			        var theYLabels  = [],
			            theYData    = [];
			        var buildYData  = $.map(theNewData, function(item, i) {
			            theYLabels.push(item[0]);
			            $.each(item, function(x, xitem) {
			                if (x === 0) newArr = [];
			                if (x > 0 && x < theNewData[0].length-1) {
			                    newArr.push(parseFloat(xitem));
			                }
			             if (x === theNewData[0].length-1) theYData.push(newArr);
			            });
			        });
			        var chart_series_color_array   = chart_series_color.split(",");
			        var chart_series_legend_array  = chart_series_legend.split(",");
			        var intial_visibility_array    = intial_visibility.split(",");
			        var check_box_series_array     = check_box_series.split(",");
			        $.each(theYLabels, function(i, item) {
			            seriesArr.push({name:item,data:theYData[i],color:chart_series_color_array[i],
			                showInLegend:chart_series_legend_array[i],visible:intial_visibility_array[i],
			                selected:check_box_series_array[i]
			            });
			        });
			        jQuery('#container').highcharts({
			            chart: {
			                type: 'area',
			                animation: {
			                        duration:chart_animation_duration,
			                        easing:chart_animation
			                 },
			                backgroundColor:chart_background_color,
			                borderColor:chart_border_color,
			                borderWidth:chart_border_width,
			                borderRadious:chart_border_radious,
			                color:chart_series_generl_color,
			                allowPointSelect:chart_allow_point_select,
	                        borderColor:bar_border_color,
	                        borderRadious:bar_border_radious,
	                        borderWidth:bar_border_width,
	                        negativeColor:chart_series_negative_color,
	                        pointPadding:column_point_padding,
	                        stacking:chart_series_stacking,
			                showCheckbox:show_checkbox,
			                dataLabels: {
			                            enabled:chart_data_labels
			                        }
			            },
			            title: {
			                text: chart_title
			            },
			            subtitle: {
			                text: chart_sub_title
			            },
			            xAxis: {
			            	title: {
			                    text: chart_xAxis
			                },
			                allowDecimals: false,
			                labels: {
			                    formatter: function() {
			                        return this.value; // clean, unformatted number for year
			                    }
			                }
			            },
			            yAxis: {
			                title: {
			                    text: chart_yAxis
			                },
			                labels: {
			                    formatter: function() {
			                        return this.value / 1000 +'k';
			                    }
			                }
			            },
			            tooltip: {
			                enabled:chart_tooltip,
			                crosshairs:chart_tooltip_crosshairs,
			                valueDecimals: value_decimals,
			                valuePrefix: value_prefix,
			                valueSuffix: value_suffix,
			            },
			            credits: {
			                enabled:chart_credit,
			                text:credit_text,
			                href:credit_href
			            },
			             navigation: {
			                buttonOptions: {
			                    enabled: navigation_buttons
			                }
			            },
			            plotOptions: {
			                area: {
			                    pointStart: 1996,
			                    marker: {
			                        enabled:chart_allow_point_select,
			                        symbol: 'circle',
			                        radius: 2,
			                        states: {
			                            hover: {
			                                enabled: true,
			                                brightness:chart_point_brightness
			                            }
			                        }
			                    },
			                    cursor: chart_cursor_pointer,
			                    events: {
			                        click: function() {
			                            alert(chart_cursor_event_text);
			                        }
			                    }
			                }

			            },
			            series:seriesArr
			        });

				<?php } ?>

				<?php if ($_GET['id']) { ?>
					var chart_type = "<?php echo $chart_type; ?>";
					
					var chart_background_color 			= jQuery("#chart_background_color").val();
					var chart_border_color 				= jQuery("#chart_border_color").val();
					var chart_border_width 				= jQuery("#chart_border_width").val();
					var chart_border_radious 			= jQuery("#chart_border_radious").val();
					var chart_allow_point_select 		= jQuery("#chart_allow_point_select").val();
					var chart_animation 				= jQuery("#chart_animation").val();
					var chart_animation_duration 		= parseInt(jQuery("#chart_animation_duration").val());
					var chart_point_brightness 			= jQuery("#chart_point_brightness").val();
					var bar_border_color 				= jQuery("#bar_border_color").val();
					var bar_border_width 				= jQuery("#bar_border_width").val();
					var bar_border_radious 				= jQuery("#bar_border_radious").val();
					var chart_series_generl_color 		= jQuery("#chart_series_generl_color").val();
					var chart_series_color 	        	= jQuery("#chart_series_color").val();
					var chart_cursor_pointer 	        = jQuery("#chart_cursor_pointer").val();
					var chart_cursor_event_text 	    = jQuery("#chart_cursor_event_text").val();
					var chart_series_negative_color 	= jQuery("#chart_series_negative_color").val();
					var column_point_padding 			= jQuery("#column_point_padding").val();
					var chart_series_legend 			= jQuery("#chart_series_legend").val();
					var chart_series_stacking 			= jQuery("#chart_series_stacking").val();
					var intial_visibility 				= jQuery("#intial_visibility").val();
					var chart_start_engle 				= jQuery("#chart_start_engle").val();
					var chart_end_engle 				= jQuery("#chart_end_engle").val();
					var chart_inner_size 				= jQuery("#chart_inner_size").val();
					var show_checkbox 					= jQuery("#show_checkbox").val();
					var check_box_series 				= jQuery("#check_box_series").val();
					var pie_sliced 						= jQuery("#pie_sliced").val();
					var pie_legend 						= jQuery("#pie_legend").val();
					var pie_sliced_offset 				= jQuery("#pie_sliced_offset").val();
					var columns_grouping 				= jQuery("#columns_grouping").val();
					var chart_data_labels 				= jQuery("#chart_data_labels").val();
					var chart_tooltip 					= jQuery("#chart_tooltip").val();
					var chart_tooltip_crosshairs 		= jQuery("#chart_tooltip_crosshairs").val();
					var chart_credit 					= jQuery("#chart_credit option:selected").val();
					var credit_text 					= jQuery("#chart_data_labels").val();
					var credit_href 					= jQuery("#credit_href").val();
					var navigation_buttons 				= jQuery("#navigation_buttons").val();
					var value_decimals 					= jQuery("#value_decimals").val();
					var value_prefix 					= jQuery("#value_prefix").val();
					var value_suffix 					= jQuery("#value_suffix").val();
					var chart_drilldown 				= jQuery("#chart_drilldown").val();
					var chart_drilldown_text			= jQuery("#chart_drilldown_text").val();

					jQuery('#chart_background_color').css('backgroundColor',chart_background_color);
					jQuery('#chart_border_color').css('backgroundColor',chart_border_color);
					jQuery('#bar_border_color').css('backgroundColor',bar_border_color);
					jQuery('#chart_series_generl_color').css('backgroundColor',chart_series_generl_color);
					jQuery('#chart_series_negative_color').css('backgroundColor',chart_series_negative_color);

				    if(chart_allow_point_select == 'true'){ chart_allow_point_select = true; } else { chart_allow_point_select = false; }

				    if(show_checkbox == 'true'){ show_checkbox = true; } else { show_checkbox = false; }

				    if(columns_grouping == 'true'){ columns_grouping = true; } else { columns_grouping = false; }

				    if(chart_credit == 'true'){ chart_credit = true; } else { chart_credit = false; }

				    if(navigation_buttons == 'true'){ navigation_buttons = true; } else { navigation_buttons = false; }

				    if(chart_data_labels == 'true'){ chart_data_labels = true; } else { chart_data_labels = false; }

				    if(chart_tooltip == 'true'){ chart_tooltip = true; } else { chart_tooltip = false; }
				    if(pie_legend == 'true'){ pie_legend = true; } else { pie_legend = false; }



					if(chart_type == 'area chart'){
						jQuery("#table-sparkline").css("display","none");
						jQuery("#chart_title").parent().parent().css("display","table-row");
						jQuery("#chart_sub_title").parent().parent().css("display","table-row");
						jQuery("#chart_xAxis").parent().parent().css("display","table-row");
						jQuery("#chart_yAxis").parent().parent().css("display","table-row");
						jQuery("#chart_shortcode").parent().parent().css("display","table-row");
						jQuery('#chart_drilldown').parent().parent().css('display','none');
						jQuery('#chart_drilldown_text').parent().parent().css('display','none');
						jQuery('#chart_start_engle').parent().parent().css('display','none');
						jQuery('#chart_end_engle').parent().parent().css('display','none');
						jQuery('#chart_inner_size').parent().parent().css('display','none');
						jQuery('#pie_sliced').parent().parent().css('display','none');
						jQuery('#pie_legend').parent().parent().css('display','none');
						jQuery('#pie_sliced_offset').parent().parent().css('display','none');
						jQuery('#bar_border_color').parent().parent().css('display','none');
						jQuery('#bar_border_width').parent().parent().css('display','none');
						jQuery('#bar_border_radious').parent().parent().css('display','none');
						jQuery('#chart-drildown-data').css('display','none');

						var chart_title 	= jQuery("#chart_title").val(); 
						var chart_sub_title = jQuery("#chart_sub_title").val();
						var chart_xAxis 	= jQuery("#chart_xAxis").val();
						var chart_yAxis 	= jQuery("#chart_yAxis").val();
						var chart_shortcode =  jQuery("#chart_shortcode").val();


						jQuery('#dataTable').handsontable({
			                data: <?php echo $chart_data; ?>,
			                minSpareRows: 1,
						    minSpareCols: 1,
						    colHeaders: true,
						    contextMenu: true,
			                cells: function (row, col, prop) {
						      var cellProperties = {};
						      if ($("#dataTable").handsontable('getData')[row][prop] === '') {
						        cellProperties.readOnly = true;
						      }
						      return cellProperties;
						    },
			                onChange: function (changes, source) {
			                    if(changes != null){
			                		update_highchart(chart_type);
			            		}
			                }

			           });
						var seriesArr       = [];
				        var theData         = $("#dataTable").data('handsontable').getData();
				        var theXCats        = $.extend(true, [], theData[0]);
				 		theXCats            = theXCats.splice(1,theXCats.length-2);
				        var theNewData      = [];
				        var buildNewData    = $.map(theData, function(item, i) {
				            if (i > 0 && i < theData.length-1) {
				                theNewData.push(item);
				            }
				        });
				        var theYCats    = [];
				        var buildYCats  = $.map(theNewData, function(item, i) {
				            theYCats.push(item[0]);
				        });
				        var theYLabels  = [],
				            theYData    = [];
				        var buildYData  = $.map(theNewData, function(item, i) {
				            theYLabels.push(item[0]);
				            $.each(item, function(x, xitem) {
				                if (x === 0) newArr = [];
				                if (x > 0 && x < theNewData[0].length-1) {
				                    newArr.push(parseFloat(xitem));
				                }
				             if (x === theNewData[0].length-1) theYData.push(newArr);
				            });
				        });
				        var chart_series_color_array   = chart_series_color.split(",");
				        var chart_series_legend_array  = chart_series_legend.split(",");
				        var intial_visibility_array    = intial_visibility.split(",");
				        var check_box_series_array     = check_box_series.split(",");
				        $.each(theYLabels, function(i, item) {
				            seriesArr.push({name:item,data:theYData[i],color:chart_series_color_array[i],
				                showInLegend:chart_series_legend_array[i],visible:intial_visibility_array[i],
				                selected:check_box_series_array[i]
				            });
				        });
				        jQuery('#container').highcharts({
				            chart: {
				                type: 'area',
				                animation: {
				                        duration:chart_animation_duration,
				                        easing:chart_animation
				                 },
				                backgroundColor:chart_background_color,
				                borderColor:chart_border_color,
				                borderWidth:chart_border_width,
				                borderRadious:chart_border_radious,
				                color:chart_series_generl_color,
				                allowPointSelect:chart_allow_point_select,
				                negativeColor:chart_series_negative_color,
				                pointPadding:column_point_padding,
				                stacking:chart_series_stacking,
				                showCheckbox:show_checkbox,
				                enabled:chart_allow_point_select,
				                dataLabels: {
				                    enabled: chart_data_labels
				                }
				            },
				            title: {
				                text: chart_title
				            },
				            subtitle: {
				                text: chart_sub_title
				            },
				            xAxis: {
				                allowDecimals: false,
				                labels: {
				                    formatter: function() {
				                        return this.value; // clean, unformatted number for year
				                    }
				                }
				            },
				            yAxis: {
				                title: {
				                    text: chart_yAxis
				                },
				                labels: {
				                    formatter: function() {
				                        return this.value / 1000 +'k';
				                    }
				                }
				            },
				            tooltip: {
				                enabled:chart_tooltip,
				                crosshairs:chart_tooltip_crosshairs,
				                valueDecimals: value_decimals,
				                valuePrefix: value_prefix,
				                valueSuffix: value_suffix,
				            },
				            credits:{
				                enabled:chart_credit,
				                text:credit_text,
				                href:credit_href
				            },
				            navigation: {
				                buttonOptions: {
				                    enabled:navigation_buttons
				                }
				            },
				            plotOptions: {
				                series:{
				                    borderColor:bar_border_color,
				                    borderRadious:bar_border_radious,
				                    borderWidth:bar_border_width
				                },
				                area: {
				                    pointStart: 1996,
				                    marker: {
				                        symbol: 'circle',
				                        radius: 2,
				                        states: {
				                            hover: {
				                                enabled: true,
				                                brightness:chart_point_brightness
				                            }
				                        }
				                    },
				                    cursor: chart_cursor_pointer,
				                    events: {
				                        click: function() {
				                            alert(chart_cursor_event_text);
				                        }
				                    }
				                }

				            },
				            series:seriesArr
				        });
					}
					if(chart_type == 'bar chart'){
						jQuery("#table-sparkline").css("display","none");
						jQuery("#chart_title").parent().parent().css("display","table-row");
						jQuery("#chart_sub_title").parent().parent().css("display","table-row");
						jQuery("#chart_xAxis").parent().parent().css("display","table-row");
						jQuery("#chart_yAxis").parent().parent().css("display","table-row");
						jQuery("#chart_shortcode").parent().parent().css("display","table-row");
						jQuery('#chart_drilldown').parent().parent().css('display','table-row');
						jQuery('#chart_drilldown_text').parent().parent().css('display','none');
						jQuery('#chart_start_engle').parent().parent().css('display','none');
						jQuery('#chart_end_engle').parent().parent().css('display','none');
						jQuery('#chart_inner_size').parent().parent().css('display','none');
						jQuery('#pie_sliced').parent().parent().css('display','none');
						jQuery('#pie_legend').parent().parent().css('display','none');
						jQuery('#pie_sliced_offset').parent().parent().css('display','none');
						jQuery('#bar_border_color').parent().parent().css('display','table-row');
						jQuery('#bar_border_width').parent().parent().css('display','table-row');
						jQuery('#bar_border_radious').parent().parent().css('display','table-row');
						jQuery('#chart-drildown-data').css('display','none');
						
						var chart_title 	= jQuery("#chart_title").val(); 
						var chart_sub_title = jQuery("#chart_sub_title").val();
						var chart_xAxis 	= jQuery("#chart_xAxis").val();
						var chart_yAxis 	= jQuery("#chart_yAxis").val();
						var chart_shortcode =  jQuery("#chart_shortcode").val();
						if(chart_drilldown == 'true'){
								jQuery('#chart_drilldown_text').parent().parent().css('display','table-row');
							    jQuery('#chart-drildown-data').css('display','block');
								jQuery("#chart_shortcode").val('Bar chart Drilldown');

								var data = <?php echo $chart_data; ?>;
					            jQuery('#dataTable').handsontable({
					                data: data,
					                minSpareRows: 1,
								    minSpareCols: 1,
								    colHeaders: true,
								    contextMenu: true,
					                cells: function (row, col, prop) {
								      var cellProperties = {};
								      if ($("#dataTable").handsontable('getData')[row][prop] === '') {
								        cellProperties.readOnly = true;
								      }
								      return cellProperties;
								    },
					                onChange: function (changes, source) {
					                       if(changes != null){
					                     	update_highchart(chart_type);
					                 	  }
					                }

					            });
					            var seriesArr = [];
						        var theData = $("#dataTable").data('handsontable').getData();
						        var theXCats = $.extend(true, [], theData[0]);
						        theXCats = theXCats.splice(1,theXCats.length-2);
						        var theNewData = [];
						        var buildNewData = $.map(theData, function(item, i) {
						            if (i > 0 && i < theData.length-1) {
						                theNewData.push(item);
						            }
						        });
						        var theYCats = [];
						        var buildYCats = $.map(theNewData, function(item, i) {
						            theYCats.push(item[0]);
						        });
						        var theYLabels = [],
						            theYData = [];
						        var buildYData = $.map(theNewData, function(item, i) {
						            theYLabels.push(item[0]);
						            $.each(item, function(x, xitem) {
						                if (x === 0) newArr = [];
						                if (x > 0 && x < theNewData[0].length-1) {
						                    newArr.push(parseFloat(xitem));
						                }
						                if (x === theNewData[0].length-1) theYData.push(newArr);
						            });
						        });
						        var chart_series_color_array  = chart_series_color.split(",");
						        var chart_series_legend_array  = chart_series_legend.split(",");
						        var intial_visibility_array    = intial_visibility.split(",");
						        var check_box_series_array     = check_box_series.split(",");
						        $.each(theYLabels, function(i, item) {
						            seriesArr.push({name:item,y:theYData[i][0],drilldown:item,color:chart_series_color_array[i]});
						        });
					            var drilldown_data = <?php echo $chart_drilldown_data; ?>;
					            jQuery('#drilldown-dataTable').handsontable({
					                data: drilldown_data,
					                minSpareRows: 1,
					                minSpareCols: 1,
								    colHeaders: true,
								    contextMenu: true,
					                cells: function (row, col, prop) {
								      var cellProperties = {};
								      if ($("#drilldown-dataTable").handsontable('getData')[row][prop] === '') {
								        cellProperties.readOnly = true;
								      }
								      return cellProperties;
								    },
					                onChange: function (changes, source) {
					                       if(changes != null){
						                     	update_highchart(chart_type);
						                 	 }
					                }
					            });

					            var drilldownSeries = [];
						        var theData = $("#drilldown-dataTable").data('handsontable').getData();
						        var theXCats = $.extend(true, [], theData[0]);
						        var theNewData = [];
						        var buildNewData = $.map(theData, function(item, i) {
						            if (i > 0 && i < theData.length-1) {
						                theNewData.push(item);
						            }
						        });
						        var theYCats = [];
						        var buildYCats = $.map(theNewData, function(item, i) {
						            theYCats.push(item[0]);
						        });
						        var theYLabels = [],
						            theYData = [];
						        var buildYData = $.map(theNewData, function(item, i) {
						            theYLabels.push(item[0]);
						            $.each(item, function(x, xitem) {
						                if (x === 0) newArr = [];
						                if (x > 0 && x < theNewData[0].length-1) {
						                    newArr.push([theXCats[x],parseFloat(xitem)]);
						                }
						                if (x === theNewData[0].length-1) theYData.push(newArr);
						            });
						        });

						        $.each(theYLabels, function(i, item) {
						            drilldownSeries.push({name:item,id:item,data:theYData[i]});
						        });
						        Highcharts.setOptions({
									lang: {
										drillUpText: chart_drilldown_text
									}
								});
						        jQuery('#container').highcharts({
							            chart: {
							                type: 'bar',
							                animation: {
							                        duration:chart_animation_duration
							                        , easing:chart_animation
							                 },
							                backgroundColor:chart_background_color,
							                borderColor:chart_border_color,
							                borderWidth:chart_border_width,
							                borderRadious:chart_border_radious
							            },
							            title: {
							                text: chart_title
							            },
							            subtitle: {
							                text: chart_sub_title
							            },
							            xAxis: {
							                type: 'category'
							            },
							            yAxis: {
							                
							                title: {
							                    text: chart_yAxis
							                }
							            },
							             tooltip: {
							                enabled:chart_tooltip,
							                crosshairs:chart_tooltip_crosshairs,
							                valueDecimals: value_decimals,
							                valuePrefix: value_prefix,
							                valueSuffix: value_suffix,
							            },
							            credits: {
							                enabled:chart_credit,
							                text:credit_text,
							                href:credit_href
							            },
							             navigation: {
							                buttonOptions: {
							                    enabled:navigation_buttons
							                }
							            },
							            plotOptions: {
							                 series: {
							                 		stacking:chart_series_stacking,
							                        allowPointSelect:chart_allow_point_select,
							                        borderColor:bar_border_color,
							                        borderRadious:bar_border_radious,
							                        borderWidth:bar_border_width,
							                        negativeColor:chart_series_negative_color,
							                        pointPadding:column_point_padding,
							                        showCheckbox:show_checkbox
							                    },
							                bar: {
							                    pointPadding: column_point_padding,
							                    borderWidth: bar_border_width,
							                    borderRadious:bar_border_radious,
							                    borderColor:bar_border_color,
							                    grouping: columns_grouping
							                }
							            },
							            series:[{
							            name: 'Rainfall',
							            colorByPoint: true,
							            data:seriesArr
							        	}],drilldown:{
							            	series:drilldownSeries
							            }
							        });

				        	} else {
				        			jQuery('#chart_drilldown_text').parent().parent().css('display','none');
							        jQuery('#chart-drildown-data').css('display','none');
							        jQuery("#chart_shortcode").val('Bar chart');
									var data = <?php echo $chart_data; ?>;
						            jQuery('#dataTable').handsontable({
						                data: data,
						                minSpareRows: 1,
									    minSpareCols: 1,
									    colHeaders: true,
									    contextMenu: true,
						                cells: function (row, col, prop) {
									      var cellProperties = {};
									      if ($("#dataTable").handsontable('getData')[row][prop] === '') {
									        cellProperties.readOnly = true;
									      }
									      return cellProperties;
									    },
						                onChange: function (changes, source) {
						                       if(changes != null){
						                     	update_highchart(chart_type);
						                 	  }
						                }
						            });
						            var seriesArr = [];
							        var theData = $("#dataTable").data('handsontable').getData();
							        var theXCats = $.extend(true, [], theData[0]);
							        theXCats = theXCats.splice(1,theXCats.length-2);
							        var theNewData = [];
							        var buildNewData = $.map(theData, function(item, i) {
							            if (i > 0 && i < theData.length-1) {
							                theNewData.push(item);
							            }
							        });
							        var theYCats = [];
							        var buildYCats = $.map(theNewData, function(item, i) {
							            theYCats.push(item[0]);
							        });
							        var theYLabels = [],
							           theYData = [];
							        var buildYData = $.map(theNewData, function(item, i) {
							            theYLabels.push(item[0]);
							            $.each(item, function(x, xitem) {
							                if (x === 0) newArr = [];
							                if (x > 0 && x < theNewData[0].length-1) {
							                    newArr.push(parseFloat(xitem));
							                }
							                if (x === theNewData[0].length-1) theYData.push(newArr);
							            });
							        });
							        var chart_series_color_array  = chart_series_color.split(",");
							        var chart_series_legend_array  = chart_series_legend.split(",");
							        var intial_visibility_array    = intial_visibility.split(",");
							        var check_box_series_array     = check_box_series.split(",");
							        $.each(theYLabels, function(i, item) {
							            seriesArr.push({name:item,data:theYData[i],color:chart_series_color_array[i],
							                showInLegend:chart_series_legend_array[i],visible:intial_visibility_array[i],
							                selected:check_box_series_array[i]
							            });
							        });
									 jQuery('#container').highcharts({
							            chart: {
							                type: 'bar',
							                 animation: {
							                        duration:chart_animation_duration,
					                        		easing:chart_animation
							                 },
							                color:chart_series_generl_color,
							                backgroundColor:chart_background_color,
							                borderColor:chart_border_color,
							                borderWidth:chart_border_width,
							                borderRadious:chart_border_radious
							            },
							            title: {
							                text:chart_title 
							            },
							            subtitle: {
							                text: chart_sub_title
							            },
							            xAxis: {
							                categories: theXCats,
							                title: {
							                    text: chart_xAxis
							                }
							            },
							            yAxis: {
							                
							                title: {
							                    text: chart_yAxis,
							                    align: 'high'
							                },
							                labels: {
							                    overflow: 'justify'
							                }
							            },
							             tooltip: {
							                enabled:chart_tooltip,
							                crosshairs:chart_tooltip_crosshairs,
							                valueDecimals: value_decimals,
							                valuePrefix: value_prefix,
							                valueSuffix: value_suffix
							            },
							            plotOptions: {
							                 series: {
							                 		stacking:chart_series_stacking,
							                        allowPointSelect:chart_allow_point_select,
							                        borderColor:bar_border_color,
							                        borderRadious:bar_border_radious,
							                        borderWidth:bar_border_width,
							                        negativeColor:chart_series_negative_color,
							                        pointPadding:column_point_padding,
							                        showCheckbox:show_checkbox,
						                            cursor: chart_cursor_pointer,
						                            events: {

						                                click: function() {

						                                    alert(chart_cursor_event_text);

						                                }

						                            }
							                    },
							                bar: {
							                    dataLabels: {
							                        enabled: chart_data_labels
							                    },
							                    grouping: columns_grouping
							                }
							            },
							            legend: {
							                layout: 'vertical',
							                align: 'right',
							                verticalAlign: 'top',
							                x: -40,
							                y: 100,
							                floating: true,
							                borderWidth: 1,
							                backgroundColor: (Highcharts.theme && Highcharts.theme.legendBackgroundColor || '#FFFFFF'),
							                shadow: true
							            },
							           credits: {
							                enabled: chart_credit,
							                text:credit_text,
							                href:credit_href
							            },
							             navigation: {
							                buttonOptions: {
							                    enabled:navigation_buttons
							                }
							            },
							            cursor: chart_cursor_pointer,
							            events: {
							                click: function() {
							                    alert(chart_cursor_event_text);
							                }
							            },
							            series: seriesArr
							        });
							}
						}	
					if(chart_type == "bubble chart"){
								jQuery("#table-sparkline").css("display","none");
								jQuery("#chart_xAxis").parent().parent().css("display","table-row");
								jQuery("#chart_yAxis").parent().parent().css("display","table-row");
								jQuery("#chart_title").parent().parent().css("display","table-row");
								jQuery("#chart_shortcode").parent().parent().css("display","table-row");
								jQuery('#chart_drilldown').parent().parent().css('display','none');
								jQuery('#chart_drilldown_text').parent().parent().css('display','none');
								jQuery('#chart_start_engle').parent().parent().css('display','none');
								jQuery('#chart_end_engle').parent().parent().css('display','none');
								jQuery('#chart_inner_size').parent().parent().css('display','none');
								jQuery('#pie_sliced').parent().parent().css('display','none');
								jQuery('#pie_legend').parent().parent().css('display','none');
								jQuery('#pie_sliced_offset').parent().parent().css('display','none');
								jQuery('#bar_border_color').parent().parent().css('display','none');
								jQuery('#bar_border_width').parent().parent().css('display','none');
								jQuery('#bar_border_radious').parent().parent().css('display','none');
								jQuery('#chart-drildown-data').css('display','none');
								
								var chart_title 	= jQuery("#chart_title").val(); 
								var chart_sub_title = jQuery("#chart_sub_title").val();
								var chart_xAxis 	= jQuery("#chart_xAxis").val();
								var chart_yAxis 	= jQuery("#chart_yAxis").val();
								var chart_shortcode =  jQuery("#chart_shortcode").val();
								var data = <?php echo $chart_data; ?>;
					            jQuery('#dataTable').handsontable({
					                data: data,
					                minSpareRows: 1,
								    minSpareCols: 1,
								    colHeaders: true,
								    contextMenu: true,
					                cells: function (row, col, prop) {
								      var cellProperties = {};
								      if ($("#dataTable").handsontable('getData')[row][prop] === '') {
								        cellProperties.readOnly = true;
								      }
								      return cellProperties;
								    },
					                onChange: function (changes, source) {
					                    if(changes != null){
					                		update_highchart(chart_type);
					            		}
					                }
					            });
					            var seriesArr = [];
					            var theData = $("#dataTable").data('handsontable').getData();
					            var theXCats = $.extend(true, [], theData[0]);
					            theXCats = theXCats.splice(1,theXCats.length-2);
					            var theNewData = [];
					            var buildNewData = $.map(theData, function(item, i) {
					                if (i > 0 && i < theData.length-1) {
					                    theNewData.push(item);
					                }
					            });
					            var theYCats = [];
					            var buildYCats = $.map(theNewData, function(item, i) {
					                theYCats.push(item[0]);
					            });
								var theYLabels = [],
					                theYData = [],
					                temp = [];
					            var num_series = (((theNewData[0].length)-2)/3);
					            var buildYData = $.map(theNewData, function(item, i) {
					                theYLabels.push(item[0]);
					                var a = 0;      
					                $.each(item, function(x, xitem) {
					                    if (x === 0 || a === 3) {
					                    	newArr = [];
					                    	a = 0;
					                    }
					                    if (x > 0 && x < theNewData[0].length-1) {
					                        newArr.push(parseFloat(xitem));
					                    }
					                    if(x != 0){ a++; }
					                    if (a === 3) {  
					                    	theYData.push(newArr);
					                      }  
					                });
					            });
								for (var i=0,j=0; i<theYData.length; i++) {
									if(i%num_series == 0){
										if(i%num_series == 0 && i != 0){
											j++;
										}
										seriesArr[j] = new Array(theYData[i]);
									} else {
										seriesArr[j].push(theYData[i]);
									}
								};
								var newseriesArr = [];
					            var chart_series_color_array  = chart_series_color.split(",");
					            var chart_series_legend_array  = chart_series_legend.split(",");
					            var intial_visibility_array    = intial_visibility.split(",");
					            var check_box_series_array     = check_box_series.split(",");
					            $.each(theYLabels, function(i, item) {
					                newseriesArr.push({data:seriesArr[i],color:chart_series_color_array[i],
					                showInLegend:chart_series_legend_array[i],visible:intial_visibility_array[i],
					                selected:check_box_series_array[i]});
					            });
							    jQuery('#container').highcharts({
							    chart: {
							        type: 'bubble',
							        zoomType: 'xy',
							        animation: {
					                        duration:chart_animation_duration,
					                        easing:chart_animation
					                 },
					                backgroundColor:chart_background_color,
					                borderColor:chart_border_color,
					                borderWidth:chart_border_width,
					                borderRadious:chart_border_radious
							    },
					            credits: {
					                enabled:chart_credit,
					                text:credit_text,
					                href:credit_href
					            },
					             navigation: {
					                buttonOptions: {
					                    enabled:navigation_buttons
					                }
					            },
					            plotOptions:{
					                 series: {
					                        allowPointSelect:chart_allow_point_select,
					                        showCheckbox:show_checkbox,
				                            cursor: chart_cursor_pointer,
				                            events: {

				                                click: function() {

				                                    alert(chart_cursor_event_text);

				                                }

				                            }
					                    }
					            },
					             tooltip: {
					                enabled:chart_tooltip,
					                crosshairs:chart_tooltip_crosshairs,
					                valueDecimals: value_decimals,
					                valuePrefix: value_prefix,
					                valueSuffix: value_suffix
					            },
							    title: {
							    	text: chart_title
							    },
					             xAxis: {
					                title: {
					                    text: chart_xAxis
					                }
					            },
					            yAxis: {
					                title: {
					                    text: chart_yAxis
					                }
					            },
							    series:newseriesArr
							});
						}

						if(chart_type == "column chart"){
							jQuery("#table-sparkline").css("display","none");
							jQuery("#chart_title").parent().parent().css("display","table-row");
							jQuery("#chart_sub_title").parent().parent().css("display","table-row");
							jQuery("#chart_xAxis").parent().parent().css("display","table-row");
							jQuery("#chart_yAxis").parent().parent().css("display","table-row");
							jQuery("#chart_shortcode").parent().parent().css("display","table-row");
							jQuery('#chart_drilldown').parent().parent().css('display','table-row');
							jQuery('#chart_drilldown_text').parent().parent().css('display','none');
							jQuery('#chart_start_engle').parent().parent().css('display','none');
							jQuery('#chart_end_engle').parent().parent().css('display','none');
							jQuery('#chart_inner_size').parent().parent().css('display','none');
							jQuery('#pie_sliced').parent().parent().css('display','none');
							jQuery('#pie_legend').parent().parent().css('display','none');
							jQuery('#pie_sliced_offset').parent().parent().css('display','none');
							jQuery('#bar_border_color').parent().parent().css('display','table-row');
							jQuery('#bar_border_width').parent().parent().css('display','table-row');
							jQuery('#bar_border_radious').parent().parent().css('display','table-row');
							jQuery('#chart-drildown-data').css('display','none');
							
							var chart_title 	= jQuery("#chart_title").val(); 
							var chart_sub_title = jQuery("#chart_sub_title").val();
							var chart_xAxis 	= jQuery("#chart_xAxis").val();
							var chart_yAxis 	= jQuery("#chart_yAxis").val();
							var chart_shortcode =  jQuery("#chart_shortcode").val();
							if(chart_drilldown == 'true'){
								jQuery('#chart_drilldown_text').parent().parent().css('display','table-row');
								jQuery('#chart-drildown-data').css('display','block');
								jQuery("#chart_shortcode").val('Column chart Drilldown');
								var data = <?php echo $chart_data; ?>;
					            jQuery('#dataTable').handsontable({
					                data: data,
					                minSpareRows: 1,
								    minSpareCols: 1,
								    colHeaders: true,
								    contextMenu: true,
					                cells: function (row, col, prop) {
								      var cellProperties = {};
								      if ($("#dataTable").handsontable('getData')[row][prop] === '') {
								        cellProperties.readOnly = true;
								      }
								      return cellProperties;
								    },
					                onChange: function (changes, source) {
					                       if(changes != null){
					                     	update_highchart(chart_type);
					                 	  }
					                }

					            });
					            var seriesArr = [];
						        var theData = $("#dataTable").data('handsontable').getData();
						        var theXCats = $.extend(true, [], theData[0]);
						        theXCats = theXCats.splice(1,theXCats.length-2);
						        var theNewData = [];
						        var buildNewData = $.map(theData, function(item, i) {
						            if (i > 0 && i < theData.length-1) {
						                theNewData.push(item);
						            }
						        });
						        var theYCats = [];
						        var buildYCats = $.map(theNewData, function(item, i) {
						            theYCats.push(item[0]);
						        });
						        var theYLabels = [],
						            theYData = [];
						        var buildYData = $.map(theNewData, function(item, i) {
						            theYLabels.push(item[0]);
						            $.each(item, function(x, xitem) {
						                if (x === 0) newArr = [];
						                if (x > 0 && x < theNewData[0].length-1) {
						                    newArr.push(parseFloat(xitem));
						                }
						                if (x === theNewData[0].length-1) theYData.push(newArr);
						            });
						        });
						        var chart_series_color_array  = chart_series_color.split(",");
						        var chart_series_legend_array  = chart_series_legend.split(",");
						        var intial_visibility_array    = intial_visibility.split(",");
						        var check_box_series_array     = check_box_series.split(",");
						        $.each(theYLabels, function(i, item) {
						            seriesArr.push({name:item,y:theYData[i][0],drilldown:item,color:chart_series_color_array[i]});
						        });
					            var drilldown_data = <?php echo $chart_drilldown_data; ?>;
					            jQuery('#drilldown-dataTable').handsontable({
					                data: drilldown_data,
					                minSpareRows: 1,
					                minSpareCols: 1,
								    colHeaders: true,
								    contextMenu: true,
					                cells: function (row, col, prop) {
								      var cellProperties = {};
								      if ($("#drilldown-dataTable").handsontable('getData')[row][prop] === '') {
								        cellProperties.readOnly = true;
								      }
								      return cellProperties;
								    },
					                onChange: function (changes, source) {
					                       if(changes != null){
						                     	update_highchart(chart_type);
						                 	 }
					                }
					            });

					            var drilldownSeries = [];
						        var theData = $("#drilldown-dataTable").data('handsontable').getData();
						        var theXCats = $.extend(true, [], theData[0]);
						        var buildNewData = $.map(theData, function(item, i) {
						            if (i > 0 && i < theData.length-1) {
						                theNewData.push(item);
						            }
						        });
						        var theYCats = [];
						        var buildYCats = $.map(theNewData, function(item, i) {
						            theYCats.push(item[0]);
						        });
						        var theYLabels = [],
						            theYData = [];
						        var buildYData = $.map(theNewData, function(item, i) {
						            theYLabels.push(item[0]);
						            $.each(item, function(x, xitem) {
						                if (x === 0) newArr = [];
						                if (x > 0 && x < theNewData[0].length-1) {
						                    newArr.push([theXCats[x],parseFloat(xitem)]);
						                }
						                if (x === theNewData[0].length-1) theYData.push(newArr);
						            });
						        });

						        $.each(theYLabels, function(i, item) {
						            drilldownSeries.push({name:item,id:item,data:theYData[i]});
						        });
						       Highcharts.setOptions({
									lang: {
										drillUpText: chart_drilldown_text
									}
								});
						        jQuery('#container').highcharts({
							            chart: {
							                type: 'column',
							                animation: {
							                        duration:chart_animation_duration
							                        , easing:chart_animation
							                 },
							                backgroundColor:chart_background_color,
							                borderColor:chart_border_color,
							                borderWidth:chart_border_width,
							                borderRadious:chart_border_radious
							            },
							            title: {
							                text: chart_title
							            },
							            subtitle: {
							                text: chart_sub_title
							            },
							            xAxis: {
							                type: 'category'
							            },
							            yAxis: {
							                
							                title: {
							                    text: chart_yAxis
							                }
							            },
							             tooltip: {
							                enabled:chart_tooltip,
							                crosshairs:chart_tooltip_crosshairs,
							                valueDecimals: value_decimals,
							                valuePrefix: value_prefix,
							                valueSuffix: value_suffix,
							            },
							            credits: {
							                enabled:chart_credit,
							                text:credit_text,
							                href:credit_href
							            },
							            navigation: {
							                buttonOptions: {
							                    enabled:navigation_buttons
							                }
							            },
							            plotOptions: {
							                 series: {
							                 		stacking:chart_series_stacking,
							                        allowPointSelect:chart_allow_point_select,
							                        borderColor:bar_border_color,
							                        borderRadious:bar_border_radious,
							                        borderWidth:bar_border_width,
							                        negativeColor:chart_series_negative_color,
							                        pointPadding:column_point_padding,
							                        showCheckbox:show_checkbox
							                    },
							                column: {
							                    pointPadding: column_point_padding,
							                    borderWidth: bar_border_width,
							                    borderRadious:bar_border_radious,
							                    borderColor:bar_border_color,
							                    grouping: columns_grouping
							                }
							            },
							            series:[{
							            name: 'Rainfall',
							            colorByPoint: true,
							            data:seriesArr
							        	}],drilldown:{
							            	series:drilldownSeries
							            }
							        });

				        	} else {
				        			jQuery('#chart_drilldown_text').parent().parent().css('display','none');
									jQuery('#chart-drildown-data').css('display','none');
									jQuery("#chart_shortcode").val('Column chart');
						        	var data = <?php echo $chart_data; ?>;
						            jQuery('#dataTable').handsontable({
						                data: data,
						                minSpareRows: 1,
									    minSpareCols: 1,
									    colHeaders: true,
									    contextMenu: true,
						                cells: function (row, col, prop) {
									      var cellProperties = {};
									      if ($("#dataTable").handsontable('getData')[row][prop] === '') {
									        cellProperties.readOnly = true;
									      }
									      return cellProperties;
									    },
						                onChange: function (changes, source) {
						                       if(changes != null){
						                     	update_highchart(chart_type);
						                 	  }
						                }

						            });
						            var seriesArr = [];
							        var theData = $("#dataTable").data('handsontable').getData();
							        var theXCats = $.extend(true, [], theData[0]);
							        theXCats = theXCats.splice(1,theXCats.length-2);
							        var theNewData = [];
							        var buildNewData = $.map(theData, function(item, i) {
							            if (i > 0 && i < theData.length-1) {
							                theNewData.push(item);
							            }
							        });
							        var theYCats = [];
							        var buildYCats = $.map(theNewData, function(item, i) {
							            theYCats.push(item[0]);
							        });
							        var theYLabels = [],
							            theYData = [];
							        var buildYData = $.map(theNewData, function(item, i) {
							            theYLabels.push(item[0]);
							            $.each(item, function(x, xitem) {
							                if (x === 0) newArr = [];
							                if (x > 0 && x < theNewData[0].length-1) {
							                    newArr.push(parseFloat(xitem));
							                }
							                if (x === theNewData[0].length-1) theYData.push(newArr);
							            });
							        });
							        var chart_series_color_array  = chart_series_color.split(",");
							        var chart_series_legend_array  = chart_series_legend.split(",");
							        var intial_visibility_array    = intial_visibility.split(",");
							        var check_box_series_array     = check_box_series.split(",");
							        $.each(theYLabels, function(i, item) {
							            seriesArr.push({name:item,data:theYData[i],color:chart_series_color_array[i],
							                showInLegend:chart_series_legend_array[i],visible:intial_visibility_array[i],
							                selected:check_box_series_array[i]});
							        });
									 jQuery('#container').highcharts({
							            chart: {
							                type: 'column',
							                animation: {
							                        duration:chart_animation_duration,
							                        easing:chart_animation
							                 },
							                backgroundColor:chart_background_color,
							                borderColor:chart_border_color,
							                borderWidth:chart_border_width,
							                borderRadious:chart_border_radious
							            },
							            title: {
							                text: chart_title
							            },
							            subtitle: {
							                text: chart_sub_title
							            },
							            xAxis: {
							                categories:theXCats
							            },
							            yAxis: {
							                
							                title: {
							                    text: chart_yAxis
							                }
							            },
							             tooltip: {
							                enabled:chart_tooltip,
							                crosshairs:chart_tooltip_crosshairs,
							                valueDecimals: value_decimals,
							                valuePrefix: value_prefix,
							                valueSuffix: value_suffix,
							            },
							            credits: {
							                enabled:chart_credit,
							                text:credit_text,
							                href:credit_href
							            },
							            navigation: {
							                buttonOptions: {
							                    enabled:navigation_buttons
							                }
							            },
							            plotOptions: {
							                 series: {
							                 		stacking:chart_series_stacking,
							                        allowPointSelect:chart_allow_point_select,
							                        borderColor:bar_border_color,
							                        borderRadious:bar_border_radious,
							                        borderWidth:bar_border_width,
							                        negativeColor:chart_series_negative_color,
							                        pointPadding:column_point_padding,
							                        showCheckbox:show_checkbox,
						                            cursor: chart_cursor_pointer,
						                            events: {

						                                click: function() {

						                                    alert(chart_cursor_event_text);

						                                }

						                            }
							                    },
							                column: {
							                    pointPadding: column_point_padding,
							                    borderWidth: bar_border_width,
							                    borderRadious:bar_border_radious,
							                    borderColor:bar_border_color,
							                    grouping: columns_grouping
							                }
							            },
							            series:seriesArr
							        });
					        	}
						}

						if(chart_type == "line chart"){
							jQuery("#table-sparkline").css("display","none");
							jQuery("#chart_title").parent().parent().css("display","table-row");
							jQuery("#chart_sub_title").parent().parent().css("display","table-row");
							jQuery("#chart_xAxis").parent().parent().css("display","table-row");
							jQuery("#chart_yAxis").parent().parent().css("display","table-row");
							jQuery("#chart_shortcode").parent().parent().css("display","table-row");
							jQuery('#chart_drilldown').parent().parent().css('display','none');
							jQuery('#chart_drilldown_text').parent().parent().css('display','none');
							jQuery('#chart_start_engle').parent().parent().css('display','none');
							jQuery('#chart_end_engle').parent().parent().css('display','none');
							jQuery('#chart_inner_size').parent().parent().css('display','none');
							jQuery('#pie_sliced').parent().parent().css('display','none');
							jQuery('#pie_legend').parent().parent().css('display','none');
							jQuery('#pie_sliced_offset').parent().parent().css('display','none');
							jQuery('#bar_border_color').parent().parent().css('display','none');
							jQuery('#bar_border_width').parent().parent().css('display','none');
							jQuery('#bar_border_radious').parent().parent().css('display','none');
							jQuery('#chart-drildown-data').css('display','none');
							
							var chart_title 	= jQuery("#chart_title").val(); 
							var chart_sub_title = jQuery("#chart_sub_title").val();
							var chart_xAxis 	= jQuery("#chart_xAxis").val();
							var chart_yAxis 	= jQuery("#chart_yAxis").val();
							var chart_shortcode =  jQuery("#chart_shortcode").val();
							var data = <?php echo $chart_data; ?>;
				            jQuery('#dataTable').handsontable({
				                data: data,
				                minSpareRows: 1,
							    minSpareCols: 1,
							    colHeaders: true,
							    contextMenu: true,
				                cells: function (row, col, prop) {
							      var cellProperties = {};
							      if ($("#dataTable").handsontable('getData')[row][prop] === '') {
							        cellProperties.readOnly = true;
							      }
							      return cellProperties;
							    },
				                onChange: function (changes, source) {
				                    if(changes != null){
				                		update_highchart(chart_type);
				            		}
				                }
				            });
				            var seriesArr = [];
					        var theData = $("#dataTable").data('handsontable').getData();
					        var theXCats = $.extend(true, [], theData[0]);
					        theXCats = theXCats.splice(1,theXCats.length-2);
					        var theNewData = [];
					        var buildNewData = $.map(theData, function(item, i) {
					            if (i > 0 && i < theData.length-1) {
					                theNewData.push(item);
					            }
					        });
					        var theYCats = [];
					        var buildYCats = $.map(theNewData, function(item, i) {
					            theYCats.push(item[0]);
					        });
					        var theYLabels = [],
					            theYData = [];
					        var buildYData = $.map(theNewData, function(item, i) {
					            theYLabels.push(item[0]);
					            $.each(item, function(x, xitem) {
					                if (x === 0) newArr = [];
					                if (x > 0 && x < theNewData[0].length-1) {
					                    newArr.push(parseFloat(xitem));
					                }
					                if (x === theNewData[0].length-1) theYData.push(newArr);
					            });
					        });
					        var chart_series_color_array  = chart_series_color.split(",");
					        var chart_series_legend_array  = chart_series_legend.split(",");
					        var intial_visibility_array    = intial_visibility.split(",");
					        var check_box_series_array     = check_box_series.split(",");
					        $.each(theYLabels, function(i, item) {
					            seriesArr.push({name:item,data:theYData[i],color:chart_series_color_array[i],
					                showInLegend:chart_series_legend_array[i],visible:intial_visibility_array[i],
					                selected:check_box_series_array[i]});
					        });
					        jQuery('#container').highcharts({
					            chart:{
					                type:'line',
					                animation: {
					                        duration:chart_animation_duration,
					                        easing:chart_animation
					                 },
					                backgroundColor:chart_background_color,
					                borderColor:chart_border_color,
					                borderWidth:chart_border_width,
					                borderRadious:chart_border_radious
					            },
					            title: {
					                text: chart_title,
					                x: -20 //center
					            },
					            subtitle: {
					                text: chart_sub_title,
					                x: -20
					            },
					            xAxis: {
					                categories:theXCats
					            },
					            yAxis: {
					                title: {
					                    text: chart_yAxis
					                },
					                plotLines: [{
					                    value: 0,
					                    width: 1,
					                    color: '#808080'
					                }]
					            },
					             tooltip: {
					                enabled:chart_tooltip,
					                crosshairs:chart_tooltip_crosshairs,
					                valueDecimals: value_decimals,
					                valuePrefix: value_prefix,
					                valueSuffix: value_suffix,
					            },
					            credits: {
					                enabled: chart_credit,
					                text:credit_text,
					                href:credit_href
					            },
					            navigation: {
					                buttonOptions: {
					                    enabled:navigation_buttons
					                }
					            },
					            legend: {
					                layout: 'vertical',
					                align: 'right',
					                verticalAlign: 'middle',
					                borderWidth: 0
					            },
					            plotOptions:{
					                 series: {
					                        allowPointSelect:chart_allow_point_select,
					                        showCheckbox:show_checkbox,
				                            cursor: chart_cursor_pointer,
				                            events: {

				                                click: function() {

				                                    alert(chart_cursor_event_text);

				                                }

				                            }
					                    }
					            },
					            series: seriesArr
					        });
						}
						if(chart_type == "pie chart"){
							jQuery("#table-sparkline").css("display","none");
							jQuery("#chart_sub_title").parent().parent().css("display","none");
							jQuery("#chart_xAxis").parent().parent().css("display","none");
							jQuery("#chart_yAxis").parent().parent().css("display","none");
							jQuery("#chart_title").parent().parent().css("display","table-row");
						    jQuery("#chart_shortcode").parent().parent().css("display","table-row");
						    jQuery('#chart_drilldown').parent().parent().css('display','table-row');
							jQuery('#chart_drilldown_text').parent().parent().css('display','none');
							jQuery('#chart_start_engle').parent().parent().css('display','none');
							jQuery('#chart_end_engle').parent().parent().css('display','table-row');
							jQuery('#chart_inner_size').parent().parent().css('display','table-row');
							jQuery('#pie_sliced').parent().parent().css('display','table-row');
							jQuery('#pie_legend').parent().parent().css('display','table-row');
							jQuery('#pie_sliced_offset').parent().parent().css('display','table-row');
							jQuery('#bar_border_color').parent().parent().css('display','none');
							jQuery('#bar_border_width').parent().parent().css('display','none');
							jQuery('#bar_border_radious').parent().parent().css('display','none');
							jQuery('#chart-drildown-data').css('display','none');
							
							var chart_title 	= jQuery("#chart_title").val(); 
							var chart_sub_title = jQuery("#chart_sub_title").val();
							var chart_xAxis 	= jQuery("#chart_xAxis").val();
							var chart_yAxis 	= jQuery("#chart_yAxis").val();
							var chart_shortcode =  jQuery("#chart_shortcode").val();
							if(chart_drilldown == 'true'){
				            	jQuery('#chart_drilldown_text').parent().parent().css('display','table-row');
							    jQuery('#chart-drildown-data').css('display','block');
							    jQuery("#chart_shortcode").val('Pie chart Drilldown');
							    var data = <?php echo $chart_data; ?>;
					            jQuery('#dataTable').handsontable({
					                data: data,
					                minSpareRows: 1,
								    colHeaders: true,
								    contextMenu: true,
					                cells: function (row, col, prop) {
								      var cellProperties = {};
								      if ($("#dataTable").handsontable('getData')[row][prop] === '') {
								        cellProperties.readOnly = true;
								      }
								      return cellProperties;
								    },
					                onChange: function (changes, source) {
					                       if(changes != null){
						                     	update_highchart(chart_type);
						                 	 }
					                }
					            });
					            var drilldown_data = <?php echo $chart_drilldown_data; ?>;
					            jQuery('#drilldown-dataTable').handsontable({
					                data: drilldown_data,
					                minSpareRows: 1,
					                minSpareCols: 1,
								    colHeaders: true,
								    contextMenu: true,
					                cells: function (row, col, prop) {
								      var cellProperties = {};
								      if ($("#drilldown-dataTable").handsontable('getData')[row][prop] === '') {
								        cellProperties.readOnly = true;
								      }
								      return cellProperties;
								    },
					                onChange: function (changes, source) {
					                       if(changes != null){
						                     	update_highchart(chart_type);
						                 	 }
					                }

					            });
					            var seriesArr = [];
					            var theData = $("#dataTable").data('handsontable').getData();
					            var theXCats = $.extend(true, [], theData[0]);
					            theXCats = theXCats.splice(1,theXCats.length-2);
					            var theNewData = [];
					            var buildNewData = $.map(theData, function(item, i) {
					                if (i > 0 && i < theData.length-1) {
					                    theNewData.push(item);
					                }
					            });
					            var theYCats = [];
					            var buildYCats = $.map(theNewData, function(item, i) {
					                theYCats.push(item[0]);
					            });
					            var theYLabels = [],
					                theYData = [];
					            var buildYData = $.map(theNewData, function(item, i) {
					                theYLabels.push(item[0]);
					                $.each(item, function(x, xitem) {
					                    if (x === 0) newArr = [];
					                    if (x > 0 && x < theNewData[0].length-1) {
					                        newArr.push(parseFloat(xitem));
					                    }
					                    if (x === theNewData[0].length-1) theYData.push(newArr);
					                });
					            });
					            if(pie_sliced =="true"){ var sliced = true; } else { var sliced = false; }
					            var chart_series_color_array  = chart_series_color.split(",");
						        var chart_series_legend_array  = chart_series_legend.split(",");
						        var intial_visibility_array    = intial_visibility.split(",");
						        var check_box_series_array     = check_box_series.split(",");
				                seriesArr.push({name:theYLabels[0],y:theYData[0][0],drilldown:theYLabels[0],selected:true,sliced:sliced,color:chart_series_color_array[0]});
				                $.each(theYLabels, function(i, item) {
				                    if(i > 0){
				                         seriesArr.push({name:item,y:theYData[i][0],drilldown:item,color:chart_series_color_array[i],showInLegend:chart_series_legend_array[0]});
				                    }
				                   
				                });

				            	var drilldownSeries = [];
					            var theData = $("#drilldown-dataTable").data('handsontable').getData();
					            var theXCats = $.extend(true, [], theData[0]);
					            theXCats = theXCats.splice(1,theXCats.length-2);
					            var theNewData = [];
					            var buildNewData = $.map(theData, function(item, i) {
					                if (i > 0 && i < theData.length-1) {
					                    theNewData.push(item);
					                }
					            });
					            var theYCats = [];
					            var buildYCats = $.map(theNewData, function(item, i) {
					                theYCats.push(item[0]);
					            });
					            var theYLabels = [],
					                theYData = [],
					                temp = [],
					                newnewArray = [];
					            var num_series = (((theNewData[0].length)-2)/2);
					            var buildYData = $.map(theNewData, function(item, i) {
					                theYLabels.push(item[0]);
					                var a = 0;
					                $.each(item, function(x, xitem) {
					                    if (x === 0 || a === 2) {
					                    	newArr = [];
					                    	a = 0;	
					                    }
					                    if (x > 0 && x < theNewData[0].length-1) {
					                        newArr.push(xitem);
					                    }
					                    if(x != 0){ a++; }
					                    if (a === 2) {  
					                    	theYData.push(newArr);
					                    }
					                   if(x == theNewData[0].length-2){
					                   		newnewArray.push(theYData);
					                   		theYData = [];
					                   }
					                });
					            });
					             $.each(theYLabels, function(i, item) {
					                drilldownSeries.push({id:item,data:newnewArray[i],color:chart_series_color_array[i],showInLegend:chart_series_legend_array[i]});
					            });
					             Highcharts.setOptions({
									lang: {
										drillUpText: chart_drilldown_text
									}
								});
								 jQuery('#container').highcharts({
							        chart: {
							        	type:'pie',
							            plotBackgroundColor: null,
							            plotBorderWidth: null,
							            plotShadow: false,
					                    backgroundColor:chart_background_color,
					                    borderColor:chart_border_color,
					                    borderWidth:chart_border_width,
					                    borderRadious:chart_border_radious,
				                        animation: {

				                                duration: chart_animation_duration
				                                ,easing: chart_animation

				                         }
							        },
							        title: {
							            text: chart_title
							        },
					                 tooltip: {
					                    enabled:chart_tooltip,
					                    crosshairs:chart_tooltip_crosshairs,
					                    valueDecimals: value_decimals,
					                    valuePrefix: value_prefix,
					                    valueSuffix: value_suffix,
					                },
					                credits: {
					                    enabled:chart_credit,
					                    text:credit_text,
					                    href:credit_href
					                },
					                navigation: {
						                buttonOptions: {
						                    enabled:navigation_buttons
						                }
						            },
							        plotOptions: {
							            pie: {
							                cursor: chart_cursor_pointer,
					                        startAngle: chart_start_engle,
					                        endAngle:chart_end_engle,
					                        innerSize:chart_inner_size,
					                        slicedOffset:pie_sliced_offset,
							                dataLabels: {
							                    enabled: chart_data_labels,
							                    format: '<b>{point.name}</b>: {point.percentage:.1f} %',
							                    style: {
							                        color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
							                    }
							                }
							            },
					                     series: {
					                        allowPointSelect:chart_allow_point_select,
					                        showCheckbox:show_checkbox
					                    }
							        },
							        series: [{
							            type: 'pie',
							            name: 'Browser share',
							            data: seriesArr,
							            showInLegend:pie_legend
							        }],
					                drilldown: {
					                    series: drilldownSeries
					                }

							    });
				        	} else {
				        		jQuery('#chart_drilldown_text').parent().parent().css('display','none');
							    jQuery('#chart-drildown-data').css('display','none');
							    jQuery("#chart_shortcode").val('Pie chart');
							    var data = <?php echo $chart_data; ?>;
					            jQuery('#dataTable').handsontable({
					                data: data,
					                minSpareRows: 1,
								    colHeaders: true,
								    contextMenu: true,
					                cells: function (row, col, prop) {
								      var cellProperties = {};
								      if ($("#dataTable").handsontable('getData')[row][prop] === '') {
								        cellProperties.readOnly = true;
								      }
								      return cellProperties;
								    },
					                onChange: function (changes, source) {
					                       if(changes != null){
						                     	update_highchart(chart_type);
						                 	 }
					                }
					            });
					            var seriesArr = [];
					            var theData = $("#dataTable").data('handsontable').getData();
					            var theXCats = $.extend(true, [], theData[0]);
					            theXCats = theXCats.splice(1,theXCats.length-2);
					            var theNewData = [];
					            var buildNewData = $.map(theData, function(item, i) {
					                if (i > 0 && i < theData.length-1) {
					                    theNewData.push(item);
					                }
					            });
					            var theYCats = [];
					            var buildYCats = $.map(theNewData, function(item, i) {
					                theYCats.push(item[0]);
					            });
					            var theYLabels = [],
					                theYData = [];
					            var buildYData = $.map(theNewData, function(item, i) {
					                theYLabels.push(item[0]);
					                $.each(item, function(x, xitem) {
					                    if (x === 0) newArr = [];
					                    if (x > 0 && x < theNewData[0].length-1) {
					                        newArr.push(parseFloat(xitem));
					                    }
					                    if (x === theNewData[0].length-1) theYData.push(newArr);
					                });
					            });
					            if(pie_sliced =="true"){ var sliced = true; } else { var sliced = false; }
					            seriesArr.push({name:theYLabels[0],y:theYData[0][0],selected:true,sliced:sliced});
					            $.each(theYLabels, function(i, item) {
					            	if(i >0){
					            		seriesArr.push([item,theYData[i][0]]);
					            	}
					                
					            });
								 jQuery('#container').highcharts({
							        chart: {
							        	type:'pie',
							            plotBackgroundColor: null,
							            plotBorderWidth: null,
							            plotShadow: false,
					                    backgroundColor:chart_background_color,
					                    borderColor:chart_border_color,
					                    borderWidth:chart_border_width,
					                    borderRadious:chart_border_radious
							        },
							        animation: {
		                                duration: chart_animation_duration
		                                ,easing: chart_animation

				                    },
							        title: {
							            text: chart_title
							        },
					                 tooltip: {
					                    enabled:chart_tooltip,
					                    crosshairs:chart_tooltip_crosshairs,
					                    valueDecimals: value_decimals,
					                    valuePrefix: value_prefix,
					                    valueSuffix: value_suffix,
					                },
					                credits: {
					                    enabled:chart_credit,
					                    text:credit_text,
					                    href:credit_href
					                },
					                navigation: {
						                buttonOptions: {
						                    enabled:navigation_buttons
						                }
						            },
							        plotOptions: {
							            pie: {
							                cursor: chart_cursor_pointer,
					                        startAngle: chart_start_engle,
					                        endAngle:chart_end_engle,
					                        innerSize:chart_inner_size,
					                        slicedOffset:pie_sliced_offset,
							                dataLabels: {
							                    enabled: chart_data_labels,
							                    format: '<b>{point.name}</b>: {point.percentage:.1f} %',
							                    style: {
							                        color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
							                    }
							                }
							            },
					                     series: {
					                        allowPointSelect:chart_allow_point_select,
					                        showCheckbox:show_checkbox,
				                            cursor: chart_cursor_pointer,
				                            events: {
				                                click: function() {
				                                    alert(chart_cursor_event_text);
				                                }

				                            }
					                    }
							        },
							        series: [{
							            type: 'pie',
							            name: 'Browser share',
							            data: seriesArr,
							            showInLegend:pie_legend
							        }]

							    });
				        	}
						}

						if(chart_type == "scatter chart"){
							jQuery("#table-sparkline").css("display","none");
							jQuery("#chart_title").parent().parent().css("display","table-row");
							jQuery("#chart_sub_title").parent().parent().css("display","table-row");
							jQuery("#chart_xAxis").parent().parent().css("display","table-row");
							jQuery("#chart_yAxis").parent().parent().css("display","table-row");
							jQuery("#chart_shortcode").parent().parent().css("display","table-row");
							jQuery('#chart_drilldown').parent().parent().css('display','none');
							jQuery('#chart_drilldown_text').parent().parent().css('display','none');
							jQuery('#chart_start_engle').parent().parent().css('display','none');
							jQuery('#chart_end_engle').parent().parent().css('display','none');
							jQuery('#chart_inner_size').parent().parent().css('display','none');
							jQuery('#pie_sliced').parent().parent().css('display','none');
							jQuery('#pie_legend').parent().parent().css('display','none');
							jQuery('#pie_sliced_offset').parent().parent().css('display','none');
							jQuery('#bar_border_color').parent().parent().css('display','none');
							jQuery('#bar_border_width').parent().parent().css('display','none');
							jQuery('#bar_border_radious').parent().parent().css('display','none');
							jQuery('#chart-drildown-data').css('display','none');
							
							var chart_title 	= jQuery("#chart_title").val(); 
							var chart_sub_title = jQuery("#chart_sub_title").val();
							var chart_xAxis 	= jQuery("#chart_xAxis").val();
							var chart_yAxis 	= jQuery("#chart_yAxis").val();
							var chart_shortcode =  jQuery("#chart_shortcode").val();
							var data = <?php echo $chart_data; ?>;
					            jQuery('#dataTable').handsontable({
					                data: data,
								    minSpareCols: 1,
								    colHeaders: true,
								    contextMenu: true,
					                cells: function (row, col, prop) {
								      var cellProperties = {};
								      if ($("#dataTable").handsontable('getData')[row][prop] === '') {
								        cellProperties.readOnly = true;
								      }
								      return cellProperties;
								    },
					                onChange: function (changes, source) {
					                    if(changes != null){
					                		update_highchart(chart_type);
					            		}
					                }
					            });
					            var seriesArr = [];
					            var theData = $("#dataTable").data('handsontable').getData();
					            var theXCats = $.extend(true, [], theData[0]);
					            theXCats = theXCats.splice(1,theXCats.length-2);
					            var theNewData = [];
					            var buildNewData = $.map(theData, function(item, i) {
					                if (i > 0 && i < theData.length-1) {
					                    theNewData.push(item);
					                }
					            });
					            var theYCats = [];
					            var buildYCats = $.map(theNewData, function(item, i) {
					                theYCats.push(item[0]);
					            });
					            var theYLabels = [],
					                theYData = [],
					                temp = [];
					            var num_series = (((theNewData[0].length)-2)/2);
					            var buildYData = $.map(theNewData, function(item, i) {
					                theYLabels.push(item[0]);
					                var a = 0;
					                $.each(item, function(x, xitem) {
					                    if (x === 0 || a === 2) {
					                    	newArr = [];
					                    	a = 0;	
					                    }
					                    if (x > 0 && x < theNewData[0].length-1) {
					                        newArr.push(parseFloat(xitem));
					                    }
					                    if(x != 0){ a++; }
					                    if (a === 2) {  
					                    	theYData.push(newArr);
					                      }
					                });
					            });
								for (var i=0,j=0; i<theYData.length; i++) {
									if(i%5 == 0){
										if(i%num_series == 0 && i != 0){
											j++;
										}
										seriesArr[j] = new Array(theYData[i]);
									} else {
										seriesArr[j].push(theYData[i]);
									}
								};
								var newseriesArr = [];
					            var chart_series_color_array  = chart_series_color.split(",");
					            var chart_series_legend_array  = chart_series_legend.split(",");
					            var intial_visibility_array    = intial_visibility.split(",");
					            var check_box_series_array     = check_box_series.split(",");
								$.each(theYLabels, function(i, item) {
					                newseriesArr.push({name:item,data:seriesArr[i],color:chart_series_color_array[i],
					                showInLegend:chart_series_legend_array[i],visible:intial_visibility_array[i],
					                selected:check_box_series_array[i]});
					            });
							    jQuery('#container').highcharts({
						        chart: {
						            type: 'scatter',
						            zoomType: 'xy',
						            animation: {
					                        duration:chart_animation_duration,
					                        easing:chart_animation
					                 },
					                backgroundColor:chart_background_color,
					                borderColor:chart_border_color,
					                borderWidth:chart_border_width,
					                borderRadious:chart_border_radious
						        },
						        title: {
						            text: chart_title
						        },
						        subtitle: {
						            text: chart_sub_title
						        },
						        xAxis: {
						            title: {
						                enabled: true,
						                text: chart_xAxis
						            },
						            startOnTick: true,
						            endOnTick: true,
						            showLastLabel: true
						        },
						        yAxis: {
						            title: {
						                text: chart_yAxis
						            }
						        },
						        legend: {
						            layout: 'vertical',
						            align: 'left',
						            verticalAlign: 'top',
						            x: 100,
						            y: 70,
						            floating: true,
						            backgroundColor: (Highcharts.theme && Highcharts.theme.legendBackgroundColor) || '#FFFFFF',
						            borderWidth: 1
						        },
					            credits: {
					                enabled: chart_credit,
					                text:credit_text,
					                href:credit_href
					            },
					            navigation: {
					                buttonOptions: {
					                    enabled:navigation_buttons
					                }
					            },
					            tooltip: {
					                enabled:chart_tooltip,
					                crosshairs:chart_tooltip_crosshairs,
					                valueDecimals: value_decimals,
					                valuePrefix: value_prefix,
					                valueSuffix: value_suffix
					            },
						        plotOptions: {
					                 series: {
					                        allowPointSelect:chart_allow_point_select,
					                        showCheckbox:show_checkbox,
				                            cursor: chart_cursor_pointer,
				                            events: {

				                                click: function() {

				                                    alert(chart_cursor_event_text);

				                                }

				                            }
					                    },
						            scatter: {
						                marker: {
						                    radius: 5,
						                    states: {
						                        hover: {
						                            enabled: true,
						                            lineColor: 'rgb(100,100,100)'
						                        }
						                    }
						                },
						                states: {
						                    hover: {
						                        marker: {
						                            enabled: chart_allow_point_select
						                        }
						                    }
						                }
						            }
						        },
						        series: newseriesArr
						    });
						}
						if(chart_type == "spark chart"){
							jQuery("#thead-sparkline").html('');
							jQuery("#tbody-sparkline").html('');
							jQuery("#container").css("display","none");
							jQuery("#chart_title").parent().parent().css("display","none");
							jQuery("#chart_sub_title").parent().parent().css("display","none");
							jQuery("#chart_xAxis").parent().parent().css("display","none");
							jQuery("#chart_yAxis").parent().parent().css("display","none");
						    jQuery("#chart_shortcode").parent().parent().css("display","table-row");
						    jQuery('#chart_drilldown').parent().parent().css('display','none');
							jQuery('#chart_drilldown_text').parent().parent().css('display','none');
							jQuery('#chart_start_engle').parent().parent().css('display','none');
							jQuery('#chart_end_engle').parent().parent().css('display','none');
							jQuery('#chart_inner_size').parent().parent().css('display','none');
							jQuery('#pie_sliced').parent().parent().css('display','none');
							jQuery('#pie_legend').parent().parent().css('display','none');
							jQuery('#pie_sliced_offset').parent().parent().css('display','none');
							jQuery('#bar_border_color').parent().parent().css('display','none');
							jQuery('#bar_border_width').parent().parent().css('display','none');
							jQuery('#bar_border_radious').parent().parent().css('display','none');
							jQuery('#chart-drildown-data').css('display','none');
							jQuery("#table-sparkline").css("display","table");
							
							
							var chart_title 	= jQuery("#chart_title").val(); 
							var chart_sub_title = jQuery("#chart_sub_title").val();
							var chart_xAxis 	= jQuery("#chart_xAxis").val();
							var chart_yAxis 	= jQuery("#chart_yAxis").val();
							var chart_shortcode =  jQuery("#chart_shortcode").val();
							var data = <?php echo $chart_data; ?>;

					            jQuery('#dataTable').handsontable({
					                data: data,
					                minSpareCols: 1,
								    colHeaders: true,
								    contextMenu: true,
					                onChange: function (changes, source) {
					                    if(changes != null){
				                			update_highchart(chart_type);
				            			}
					                }
					            });
					            var seriesArr = [];
					            var theData = $("#dataTable").data('handsontable').getData();
					            var theXCats = $.extend(true, [], theData[0]);
			                    theXCats = theXCats.splice(0,theXCats.length-1);
			                    for(i in theXCats){
			                    	jQuery("#thead-sparkline  tr") .append('<td>'+theXCats[i]+'</td>');
			                    }			        
						        var theNewData = [];
						        var buildNewData = $.map(theData, function(item, i) {
						            if (i > 0 && i < theData.length-1) {
						                theNewData.push(item);
						            }
						        });
						        var theYCats = [];
						        var buildYCats = $.map(theNewData, function(item, i) {
						            theYCats.push(item[0]);
						        });
						        var theYLabels = [],
						            theYData = [],
						            str = '';
						        var buildYData = $.map(theNewData, function(item, i) {
						            theYLabels.push(item[0]);
						            $.each(item, function(x, xitem) {
						                if (x < theNewData[0].length-1) {
						                	if(x===0){ str= ''; str = '<th>'+xitem+'</th>'; }
						                	if(x===1){ str = str+'<td>'+parseFloat(xitem)+'</td>'; }
						                	if(x===2){ str = str+'<td data-sparkline="'+xitem+'"/>'; }
						                	if(x===3){ str = str+'<td>'+parseFloat(xitem)+'</td>'; }
						                	if(x===4){ str = str+'<td data-sparkline="'+xitem+'"/>'; }
						                	if(x===5){ str = str+'<td>'+parseFloat(xitem)+'</td>'; }
						                	if(x===6){ str = str+'<td data-sparkline="'+xitem+'"/>'; }
						               		if (x === 6) { jQuery("#tbody-sparkline").append('<tr>'+str+'</tr>'); }
						                }
						            
						            });
						        });
						        $.each(theYLabels, function(i, item) {
						            seriesArr.push({name:item,data:theYData[i]});
						        });
							Highcharts.SparkLine = function (options, callback) {
						        var defaultOptions = {
						            chart: {
						                renderTo: (options.chart && options.chart.renderTo) || this,
						                backgroundColor:chart_background_color,
						                borderColor:chart_border_color,
						                borderWidth:chart_border_width,
						                borderRadious:chart_border_radious,
						                type: 'area',
						                margin: [2, 0, 2, 0],
						                width: 120,
						                height: 20,
						                style: {
						                    overflow: 'visible'
						                },
						                skipClone: true
						            },
						            title: {
						                text: ''
						            },
						            credits: {
						                enabled: false
						            },
						            xAxis: {
						                labels: {
						                    enabled: false
						                },
						                title: {
						                    text: null
						                },
						                startOnTick: false,
						                endOnTick: false,
						                tickPositions: []
						            },
						            yAxis: {
						                endOnTick: false,
						                startOnTick: false,
						                labels: {
						                    enabled: false
						                },
						                title: {
						                    text: null
						                },
						                tickPositions: [0]
						            },
						            legend: {
						                enabled: false
						            },
						            navigation: {
						                buttonOptions: {
						                    enabled:navigation_buttons
						                }
						            },
						            tooltip: {
						                backgroundColor: null,
						                borderWidth: 0,
						                shadow: false,
						                useHTML: true,
						                hideDelay: 0,
						                shared: true,
						                padding: 0,
						                positioner: function (w, h, point) {
						                    return { x: point.plotX - w / 2, y: point.plotY - h};
						                }
						            },
						            plotOptions: {
						                series: {
						                    animation: false,
						                    lineWidth: 1,
						                    shadow: false,
						                    states: {
						                        hover: {
						                            lineWidth: 1
						                        }
						                    },
						                    marker: {
						                       radius: 1,
						                        states: {
						                            hover: {
						                                radius: 2
						                            }
						                        }
						                    },
						                    fillOpacity: 0.25
						                },
						                column: {
						                    negativeColor: '#910000',
						                    borderColor: 'silver'
						                },
				            	 		series: {
							                allowPointSelect: chart_allow_point_select
							            }
						            }
						        };
						        options = Highcharts.merge(defaultOptions, options);
						        return new Highcharts.Chart(options, callback);
						    };
						    var start = +new Date(),
						        $tds = jQuery("td[data-sparkline]"),
						        fullLen = $tds.length,
						        n = 0;

						    function doChunk() {
						        var time = +new Date(),
						            i,
						            len = $tds.length;
						        for (i = 0; i < len; i++) {
						            var $td = $($tds[i]),
						                stringdata = $td.data('sparkline'),
						                arr = stringdata.split('; '),
						                data = $.map(arr[0].split(', '), parseFloat),
						                chart = {};
						            if (arr[1]) {
						                chart.type = arr[1];
						            }
						            $td.highcharts('SparkLine', {
						                series: [{
						                    data: data,
						                    pointStart: 1
						                }],
						                tooltip: {
						                    headerFormat: '<span style="font-size: 10px">' + $td.parent().find('th').html() + ', Q{point.x}:</span><br/>',
						                    pointFormat: '<b>{point.y}.000</b> USD'
						                },
						                chart: chart
						            });
						            n++;			          
						            if (new Date() - time > 500) {
						               $tds.splice(0, i + 1);
						                setTimeout(doChunk, 0);
						                break;
						            }
						            if (n === fullLen) {
						                jQuery('#result').html('Generated ' + fullLen + ' sparklines in ' + (new Date() - start) + ' ms');
						            }
						        }
						    }
						    doChunk();
						}
						if(chart_type == "spline chart"){
							jQuery("#table-sparkline").css("display","none");
							jQuery("#container").css("display","block");
							jQuery("#chart_title").parent().parent().css("display","table-row");
							jQuery("#chart_sub_title").parent().parent().css("display","table-row");
							jQuery("#chart_xAxis").parent().parent().css("display","table-row");
							jQuery("#chart_yAxis").parent().parent().css("display","table-row");
							jQuery("#chart_shortcode").parent().parent().css("display","table-row");
							jQuery('#chart_drilldown').parent().parent().css('display','none');
							jQuery('#chart_drilldown_text').parent().parent().css('display','none');
							jQuery('#chart_start_engle').parent().parent().css('display','none');
							jQuery('#chart_end_engle').parent().parent().css('display','none');
							jQuery('#chart_inner_size').parent().parent().css('display','none');
							jQuery('#pie_sliced').parent().parent().css('display','none');
							jQuery('#pie_legend').parent().parent().css('display','none');
							jQuery('#pie_sliced_offset').parent().parent().css('display','none');
							jQuery('#bar_border_color').parent().parent().css('display','none');
							jQuery('#bar_border_width').parent().parent().css('display','none');
							jQuery('#bar_border_radious').parent().parent().css('display','none');
							jQuery('#chart-drildown-data').css('display','none');

							var chart_title 	= jQuery("#chart_title").val(); 
							var chart_sub_title = jQuery("#chart_sub_title").val();
							var chart_xAxis 	= jQuery("#chart_xAxis").val();
							var chart_yAxis 	= jQuery("#chart_yAxis").val();
							var chart_shortcode =  jQuery("#chart_shortcode").val();
							var data = <?php echo $chart_data; ?>;
				            jQuery('#dataTable').handsontable({
				                data: data,
				                minSpareRows: 1,
							    minSpareCols: 1,
							    colHeaders: true,
							    contextMenu: true,
				                cells: function (row, col, prop) {
							      var cellProperties = {};
							      if ($("#dataTable").handsontable('getData')[row][prop] === '') {
							        cellProperties.readOnly = true;
							      }
							      return cellProperties;
							    },
				                onChange: function (changes, source) {
				                    if(changes != null){
				                		update_highchart(chart_type);
				            		}
				                }
				            });
				            var seriesArr = [];
					        var theData = $("#dataTable").data('handsontable').getData();
					        var theXCats = $.extend(true, [], theData[0]);
					        theXCats = theXCats.splice(1,theXCats.length-2);
					        var theNewData = [];
					        var buildNewData = $.map(theData, function(item, i) {
					            if (i > 0 && i < theData.length-1) {
					                theNewData.push(item);
					            }
					        });
					        var theYCats = [];
					        var buildYCats = $.map(theNewData, function(item, i) {
					            theYCats.push(item[0]);
					        });
					        var theYLabels = [],
					            theYData = [];
					        var buildYData = $.map(theNewData, function(item, i) {
					            theYLabels.push(item[0]);
					            $.each(item, function(x, xitem) {
					                if (x === 0) newArr = [];
					                if (x > 0 && x < theNewData[0].length-1) {
					                    newArr.push(parseFloat(xitem));
					                }
					                if (x === theNewData[0].length-1) theYData.push(newArr);
					            });
					        });
				            var chart_series_color_array  = chart_series_color.split(",");
				            var chart_series_legend_array  = chart_series_legend.split(",");
				            var intial_visibility_array    = intial_visibility.split(",");
				            var check_box_series_array     = check_box_series.split(",");
					        $.each(theYLabels, function(i, item) {
					            seriesArr.push({name:item,data:theYData[i],color:chart_series_color_array[i],
				                            showInLegend:chart_series_legend_array[i],visible:intial_visibility_array[i],
				                            selected:check_box_series_array[i]});
					        });
							 jQuery('#container').highcharts({
					        chart: {
					            type: 'spline',
					            animation: {
				                        duration:chart_animation_duration,
				                        easing:chart_animation
				                 },
				                backgroundColor:chart_background_color,
				                borderColor:chart_border_color,
				                borderWidth:chart_border_width,
				                borderRadious:chart_border_radious
					        },
					        title: {
					            text: chart_title
					        },
					        subtitle: {
					            text: chart_sub_title
					        },
					        xAxis: {
					            categories: theXCats
					        },
					        yAxis: {
					            title: {
					                text: chart_yAxis
					            },
					            labels: {
					                formatter: function() {
					                    return this.value +'°'
					                }
					            }
					        },
					        tooltip: {
				                    enabled:chart_tooltip,
				                    crosshairs:chart_tooltip_crosshairs,
				                    valueDecimals: value_decimals,
				                    valuePrefix: value_prefix,
				                    valueSuffix: value_suffix,
				                },
				            credits: {
				                enabled: chart_credit,
				                text:credit_text,
				                href:credit_href
				            },
				            navigation: {
				                buttonOptions: {
				                    enabled:navigation_buttons
				                }
				            },
					        plotOptions: {
				                 series: {
				                        allowPointSelect:chart_allow_point_select,
				                        showCheckbox:show_checkbox,
			                            cursor: chart_cursor_pointer,
			                            events: {

			                                click: function() {

			                                    alert(chart_cursor_event_text);

			                                }

			                            }
				                    },
					            spline: {
					                marker: {
					                    radius: 4,
					                    lineColor: '#666666',
					                    lineWidth: 1
					                }
					            }
					        },
					        series:seriesArr 
					    });
					}
						if(chart_type == "waterfall chart"){
							jQuery("#table-sparkline").css("display","none");
							jQuery("#chart_yAxis").parent().parent().css("display","table-row");
							jQuery("#chart_sub_title").parent().parent().css("display","none");
							jQuery("#chart_title").parent().parent().css("display","table-row");
							jQuery("#chart_xAxis").parent().parent().css("display","table-row");
							jQuery("#chart_shortcode").parent().parent().css("display","table-row");
							jQuery('#chart_drilldown').parent().parent().css('display','none');
							jQuery('#chart_drilldown_text').parent().parent().css('display','none');
							jQuery('#chart_start_engle').parent().parent().css('display','none');
							jQuery('#chart_end_engle').parent().parent().css('display','none');
							jQuery('#chart_inner_size').parent().parent().css('display','none');
							jQuery('#pie_sliced').parent().parent().css('display','none');
							jQuery('#pie_legend').parent().parent().css('display','none');
							jQuery('#pie_sliced_offset').parent().parent().css('display','none');
							jQuery('#bar_border_color').parent().parent().css('display','none');
							jQuery('#bar_border_width').parent().parent().css('display','none');
							jQuery('#bar_border_radious').parent().parent().css('display','none');
							jQuery('#chart-drildown-data').css('display','none');
							jQuery("#container").css("display","block");
							
							var chart_title 	= jQuery("#chart_title").val(); 
							var chart_sub_title = jQuery("#chart_sub_title").val();
							var chart_xAxis 	= jQuery("#chart_xAxis").val();
							var chart_yAxis 	= jQuery("#chart_yAxis").val();
							var chart_shortcode =  jQuery("#chart_shortcode").val();
							var data = <?php echo $chart_data; ?>;
					            jQuery('#dataTable').handsontable({
					                data: data,
					                minSpareRows: 1,
								    colHeaders: true,
								    contextMenu: true,
					                cells: function (row, col, prop) {
								      var cellProperties = {};
								      if ($("#dataTable").handsontable('getData')[row][prop] === '') {
								        cellProperties.readOnly = true;
								      }
								      return cellProperties;
								    },
					                onChange: function (changes, source) {
					                    if(changes != null){
					                		update_highchart(chart_type);
					            		}
					                }

					            });
					 			var seriesArr = [];
				 	           var theData = $("#dataTable").data('handsontable').getData();
					            var theXCats = $.extend(true, [], theData[0]);
					            theXCats = theXCats.splice(1,theXCats.length-2);
					            var theNewData = [];
					            var buildNewData = $.map(theData, function(item, i) {
					                if (i > 0 && i < theData.length-1) {
					                    theNewData.push(item);
					                }
					            });
					            var theYCats = [];
					            var buildYCats = $.map(theNewData, function(item, i) {
					                theYCats.push(item[0]);
					            });

					            var theYLabels = [],
					                theYData = [];
					            var buildYData = $.map(theNewData, function(item, i) {
					                theYLabels.push(item[0]);
					                $.each(item, function(x, xitem) {
					                    if (x === 0) newArr = [];
					                    if (x > 0 && x < theNewData[0].length-1) {
					                        newArr.push(parseFloat(xitem));
					                    }
					                    if (x === theNewData[0].length-1) theYData.push(newArr);
					                });
					            });
					             var chart_series_color_array  = chart_series_color.split(",");
					            var chart_series_legend_array  = chart_series_legend.split(",");
					            var intial_visibility_array    = intial_visibility.split(",");
					            var check_box_series_array     = check_box_series.split(",");
					            $.each(theYLabels, function(i, item) {
					                seriesArr.push({name:item,y:theYData[i][0],color:chart_series_color_array[i],
					                            showInLegend:chart_series_legend_array[i],visible:intial_visibility_array[i],
					                            selected:check_box_series_array[i]});
					            });
							  jQuery('#container').highcharts({
						        chart: {
						            type: 'waterfall',
						            animation: {
					                        duration:chart_animation_duration,
					                        easing:chart_animation
					                 },
					                backgroundColor:chart_background_color,
					                borderColor:chart_border_color,
					                borderWidth:chart_border_width,
					                borderRadious:chart_border_radious
						        },
						        title: {
						            text: chart_title
						        },
						        xAxis: {
						            type: 'category'
						        },
						        yAxis: {
						            title: {
						                text: chart_yAxis
						            }
						        },
						        legend: {
						            enabled: false
						        },
						        tooltip: {
					                enabled:chart_tooltip,
					                crosshairs:chart_tooltip_crosshairs,
					                valueDecimals: value_decimals,
					                valuePrefix: value_prefix,
					                valueSuffix: value_suffix,
					            },
					            credits: {
					                enabled: chart_credit,
					                text:credit_text,
					                href:credit_href
					            },
					            navigation: {
					                buttonOptions: {
					                    enabled:navigation_buttons
					                }
					            },
					            plotOptions:{
					                 series: {
					                        allowPointSelect:chart_allow_point_select,
					                        showCheckbox:show_checkbox,
				                            cursor: chart_cursor_pointer,
				                            events: {

				                                click: function() {

				                                    alert(chart_cursor_event_text);

				                                }

				                            }
					                    }
					            },
						        series: [{
						            upColor: Highcharts.getOptions().colors[2],
						            color: Highcharts.getOptions().colors[3],
						            data: seriesArr,
						            dataLabels: {
						                enabled: chart_data_labels,
						                formatter: function () {
						                    return Highcharts.numberFormat(this.y / 1000, 0, ',') + 'k';
						                },
						                style: {
						                    color: '#FFFFFF',
						                    fontWeight: 'bold',
						                    textShadow: '0px 0px 3px black'
						                }
						            },
						            pointPadding: column_point_padding
						        }]
						    });
						}
				<?php } ?>

				jQuery('#clear-data').click(function() {
						 jQuery('#dataTable').handsontable('clear');
				});
				jQuery("#chart_type").change(function(e) {					
						var chart_type 						= jQuery("#chart_type option:selected").val();
						var chart_background_color 			= jQuery("#chart_background_color").val();
						var chart_border_color 				= jQuery("#chart_border_color").val();
						var chart_border_width 				= jQuery("#chart_border_width").val();
						var chart_border_radious 			= jQuery("#chart_border_radious").val();
						var chart_allow_point_select 		= jQuery("#chart_allow_point_select").val();
						var chart_animation 				= jQuery("#chart_animation").val();
						var chart_animation_duration 		= jQuery("#chart_animation_duration").val();
						var chart_point_brightness 			= jQuery("#chart_point_brightness").val();
						var bar_border_color 				= jQuery("#bar_border_color").val();
						var bar_border_width 				= jQuery("#bar_border_width").val();
						var bar_border_radious 				= jQuery("#bar_border_radious").val();
						var chart_series_generl_color 		= jQuery("#chart_series_generl_color").val();
						var chart_series_color 	        	= jQuery("#chart_series_color").val();
						var chart_cursor_pointer 	        = jQuery("#chart_cursor_pointer").val();
						var chart_cursor_event_text 	    = jQuery("#chart_cursor_event_text").val();
						var chart_series_negative_color 	= jQuery("#chart_series_negative_color").val();
						var column_point_padding 			= jQuery("#column_point_padding").val();
						var chart_series_legend 			= jQuery("#chart_series_legend").val();
						var chart_series_stacking 			= jQuery("#chart_series_stacking").val();
						var intial_visibility 				= jQuery("#intial_visibility").val();
						var chart_start_engle 				= jQuery("#chart_start_engle").val();
						var chart_end_engle 				= jQuery("#chart_end_engle").val();
						var chart_inner_size 				= jQuery("#chart_inner_size").val();
						var show_checkbox 					= jQuery("#show_checkbox").val();
						var check_box_series 				= jQuery("#check_box_series").val();
						var pie_sliced 						= jQuery("#pie_sliced").val();
						var pie_legend 						= jQuery("#pie_legend").val();
						var pie_sliced_offset 				= jQuery("#pie_sliced_offset").val();
						var columns_grouping 				= jQuery("#columns_grouping").val();
						var chart_data_labels 				= jQuery("#chart_data_labels").val();
						var chart_tooltip 					= jQuery("#chart_tooltip").val();
						var chart_tooltip_crosshairs 		= jQuery("#chart_tooltip_crosshairs").val();
						var chart_credit 					= jQuery("#chart_credit").val();
						var credit_text 					= jQuery("#chart_data_labels").val();
						var credit_href 					= jQuery("#credit_href").val();
						var navigation_buttons 				= jQuery("#navigation_buttons").val();
						var value_decimals 					= jQuery("#value_decimals").val();
						var value_prefix 					= jQuery("#value_prefix").val();
						var value_suffix 					= jQuery("#value_suffix").val();
						var chart_drilldown 				= jQuery("#chart_drilldown option:selected").val();
						var chart_drilldown_text 			= jQuery("#chart_drilldown_text").val();

						jQuery('#chart_background_color').css('backgroundColor', '#' + chart_background_color);
						jQuery('#chart_border_color').css('backgroundColor', '#' + chart_border_color);

						if(chart_allow_point_select == 'true'){ chart_allow_point_select = true; } else { chart_allow_point_select = false; }

					    if(show_checkbox == 'true'){ show_checkbox = true; } else { show_checkbox = false; }

					    if(columns_grouping == 'true'){ columns_grouping = true; } else { columns_grouping = false; }

					    if(chart_credit == 'true'){ chart_credit = true; } else { chart_credit = false; }

					    if(navigation_buttons == 'true'){ navigation_buttons = true; } else { navigation_buttons = false; }

					    if(chart_data_labels == 'true'){ chart_data_labels = true; } else { chart_data_labels = false; }

					    if(chart_tooltip == 'true'){ chart_tooltip = true; } else { chart_tooltip = false; }
					    if(pie_legend == 'true'){ pie_legend = true; } else { pie_legend = false; }


						if(chart_type == "area chart"){

							jQuery("#table-sparkline").css("display","none");
							jQuery("#result").css("display","none");
							jQuery("#container").css("display","block");
							jQuery("#chart_title").parent().parent().css("display","table-row");
							jQuery("#chart_sub_title").parent().parent().css("display","table-row");
							jQuery("#chart_xAxis").parent().parent().css("display","table-row");
							jQuery("#chart_yAxis").parent().parent().css("display","table-row");
							jQuery("#chart_shortcode").parent().parent().css("display","table-row");
							jQuery('#chart_drilldown').parent().parent().css('display','none');
							jQuery('#chart_drilldown_text').parent().parent().css('display','none');
							jQuery('#chart_start_engle').parent().parent().css('display','none');
							jQuery('#chart_end_engle').parent().parent().css('display','none');
							jQuery('#chart_inner_size').parent().parent().css('display','none');
							jQuery('#pie_sliced').parent().parent().css('display','none');
							jQuery('#pie_legend').parent().parent().css('display','none');
							jQuery('#pie_sliced_offset').parent().parent().css('display','none');
							jQuery('#bar_border_color').parent().parent().css('display','none');
							jQuery('#bar_border_width').parent().parent().css('display','none');
							jQuery('#bar_border_radious').parent().parent().css('display','none');
							jQuery('#chart-drildown-data').css('display','none');
							jQuery("#chart_title").val("US and USSR nuclear stockpiles");
							jQuery("#chart_sub_title").val('Source: <a href="http://thebulletin.metapress.com/content/c4120650912x74k7/fulltext.pdf">'+
					                    'thebulletin.metapress.com</a>');
							jQuery("#chart_xAxis").val("Years");
							jQuery("#chart_yAxis").val('Nuclear weapon states');
							jQuery("#chart_shortcode").val("Area Chart");

							var chart_title 	= jQuery("#chart_title").val(); 
							var chart_sub_title = jQuery("#chart_sub_title").val();
							var chart_xAxis 	= jQuery("#chart_xAxis").val();
							var chart_yAxis 	= jQuery("#chart_yAxis").val();
							var chart_shortcode =  jQuery("#chart_shortcode").val();

							var data = [
								  ["", "1996", "1997","1998","1999","2000","2001","2002","2003","2004","2005","2006","2007","2008","2009","2010","2011"],
								  ["USA", 6, 32,110,235,369,640,1005,1436,2063,31056,31982,31233,25722,27342,23586,23586],
								  ["Russia", 11, 2905, 2517, 2422, 2941, 2905, 2517, 2422, 2941, 2905, 2517, 2422, 2941, 2905, 2517,23586]
								];
				            jQuery('#dataTable').handsontable({
				                data: data,
				                minSpareRows: 1,
							    minSpareCols: 1,
							    colHeaders: true,
							    contextMenu: true,
				                cells: function (row, col, prop) {
							      var cellProperties = {};
							      if ($("#dataTable").handsontable('getData')[row][prop] === '') {
							        cellProperties.readOnly = true;
							      }
							      return cellProperties;
							    },
				                onChange: function (changes, source) {
				                       if(changes != null){
				                     	update_highchart(chart_type);
				                 	  }
				                }

				            });
							var seriesArr       = [];
					        var theData         = $("#dataTable").data('handsontable').getData();
					        var theXCats        = $.extend(true, [], theData[0]);
					 		theXCats            = theXCats.splice(1,theXCats.length-2);
					        var theNewData      = [];
					        var buildNewData    = $.map(theData, function(item, i) {
					            if (i > 0 && i < theData.length-1) {
					                theNewData.push(item);
					            }
					        });
					        var theYCats    = [];
					        var buildYCats  = $.map(theNewData, function(item, i) {
					            theYCats.push(item[0]);
					        });
					        var theYLabels  = [],
					            theYData    = [];
					        var buildYData  = $.map(theNewData, function(item, i) {
					            theYLabels.push(item[0]);
					            $.each(item, function(x, xitem) {
					                if (x === 0) newArr = [];
					                if (x > 0 && x < theNewData[0].length-1) {
					                    newArr.push(parseFloat(xitem));
					                }
					             if (x === theNewData[0].length-1) theYData.push(newArr);
					            });
					        });
					        var chart_series_color_array   = chart_series_color.split(",");
					        var chart_series_legend_array  = chart_series_legend.split(",");
					        var intial_visibility_array    = intial_visibility.split(",");
					        var check_box_series_array     = check_box_series.split(",");
					        $.each(theYLabels, function(i, item) {
					            seriesArr.push({name:item,data:theYData[i],color:chart_series_color_array[i],
					                showInLegend:chart_series_legend_array[i],visible:intial_visibility_array[i],
					                selected:check_box_series_array[i]
					            });
					        });
					        jQuery('#container').highcharts({
					            chart: {
					                type: 'area',
					                animation: {
					                        duration:chart_animation_duration,
					                        easing:chart_animation
					                 },
					                backgroundColor:chart_background_color,
					                borderColor:chart_border_color,
					                borderWidth:chart_border_width,
					                borderRadious:chart_border_radious,
					                color:chart_series_generl_color,
					                allowPointSelect:chart_allow_point_select,
					                negativeColor:chart_series_negative_color,
					                pointPadding:column_point_padding,
					                stacking:chart_series_stacking,
					                showCheckbox:show_checkbox,
					                enabled:chart_allow_point_select,
					                dataLabels: {
					                    enabled: chart_data_labels
					                }
					            },
					            title: {
					                text: chart_title
					            },
					            subtitle: {
					                text: chart_sub_title
					            },
					            xAxis: {
					                allowDecimals: false,
					                labels: {
					                    formatter: function() {
					                        return this.value; // clean, unformatted number for year
					                    }
					                }
					            },
					            yAxis: {
					                title: {
					                    text: chart_yAxis
					                },
					                labels: {
					                    formatter: function() {
					                        return this.value / 1000 +'k';
					                    }
					                }
					            },
					            tooltip: {
					                enabled:chart_tooltip,
					                crosshairs:chart_tooltip_crosshairs,
					                valueDecimals: value_decimals,
					                valuePrefix: value_prefix,
					                valueSuffix: value_suffix,
					            },
					            credits: {
					                enabled:chart_credit,
					                text:credit_text,
					                href:credit_href
					            },
					            navigation: {
					                buttonOptions: {
					                    enabled:navigation_buttons
					                }
					            },
					            plotOptions: {
					                series:{
					                     borderColor:bar_border_color,
					                    borderRadious:bar_border_radious,
					                    borderWidth:bar_border_width
					                },
					                area: {
					                    pointStart: 1996,
					                    marker: {
					                        symbol: 'circle',
					                        radius: 2,
					                        states: {
					                            hover: {
					                                enabled: true,
					                                brightness:chart_point_brightness
					                            }
					                        }
					                    },
					                    cursor: chart_cursor_pointer,
					                    events: {
					                        click: function() {
					                            alert(chart_cursor_event_text);
					                        }
					                    }
					                }

					            },
					            series:seriesArr
					        });
						}

						if(chart_type == "bar chart"){
							
							jQuery("#table-sparkline").css("display","none");
							jQuery("#result").css("display","none");
							jQuery("#container").css("display","block");
							jQuery("#chart_title").parent().parent().css("display","table-row");
							jQuery("#chart_sub_title").parent().parent().css("display","table-row");
							jQuery("#chart_xAxis").parent().parent().css("display","table-row");
							jQuery("#chart_yAxis").parent().parent().css("display","table-row");
							jQuery("#chart_shortcode").parent().parent().css("display","table-row");
							jQuery('#chart_drilldown').parent().parent().css('display','table-row');
							jQuery('#chart_start_engle').parent().parent().css('display','none');
							jQuery('#chart_end_engle').parent().parent().css('display','none');
							jQuery('#chart_inner_size').parent().parent().css('display','none');
							jQuery('#pie_sliced').parent().parent().css('display','none');
							jQuery('#pie_legend').parent().parent().css('display','none');
							jQuery('#pie_sliced_offset').parent().parent().css('display','none');
							jQuery('#bar_border_color').parent().parent().css('display','table-row');
							jQuery('#bar_border_width').parent().parent().css('display','table-row');
							jQuery('#bar_border_radious').parent().parent().css('display','table-row');							
							jQuery("#chart_title").val('Historic World Population by Region');
							jQuery("#chart_sub_title").val('Source: Wikipedia.org');
							jQuery("#chart_xAxis").val("Countries");
							jQuery("#chart_yAxis").val('Population (millions)');
							jQuery("#chart_shortcode").val("Bar Chart");
							jQuery("#chart_series_color").val('#FF0000,#EFEFEF,#FGFGFG');

							var chart_title 	= jQuery("#chart_title").val(); 
							var chart_sub_title = jQuery("#chart_sub_title").val();
							var chart_xAxis 	= jQuery("#chart_xAxis").val();
							var chart_yAxis 	= jQuery("#chart_yAxis").val();
							var chart_shortcode =  jQuery("#chart_shortcode").val();

							if(chart_drilldown == 'true'){
								jQuery('#chart_drilldown_text').parent().parent().css('display','table-row');
							    jQuery('#chart-drildown-data').css('display','block');
							    jQuery("#chart_shortcode").val('Bar chart Drilldown');
								var data = [
										  ["","Tokyo"],
										  ["jan", 49.9],
										  ["feb", 71.5],
										  ["mar", 106.4],
										  ["apr", 129.2],
										  ["may", 144.0],
										  ["jun", 176.0],
										  ["jul", 135.6],
										  ["aug", 148.5],
										  ["sep", 216.4],
										  ["oct", 194.1],
										  ["nov", 95.6],
										  ["dec", 54.4],
										];
					            jQuery('#dataTable').handsontable({
					                data: data,
					                minSpareRows: 1,
								    minSpareCols: 1,
								    colHeaders: true,
								    contextMenu: true,
					                cells: function (row, col, prop) {
								      var cellProperties = {};
								      if ($("#dataTable").handsontable('getData')[row][prop] === '') {
								        cellProperties.readOnly = true;
								      }
								      return cellProperties;
								    },
					                onChange: function (changes, source) {
					                       if(changes != null){
					                     	update_highchart(chart_type);
					                 	  }
					                }

					            });
					            var seriesArr = [];
						        var theData = $("#dataTable").data('handsontable').getData();
						        var theXCats = $.extend(true, [], theData[0]);
						        theXCats = theXCats.splice(1,theXCats.length-2);
						        var theNewData = [];
						        var buildNewData = $.map(theData, function(item, i) {
						            if (i > 0 && i < theData.length-1) {
						                theNewData.push(item);
						            }
						        });
						        var theYCats = [];
						        var buildYCats = $.map(theNewData, function(item, i) {
						            theYCats.push(item[0]);
						        });
						        var theYLabels = [],
						            theYData = [];
						        var buildYData = $.map(theNewData, function(item, i) {
						            theYLabels.push(item[0]);
						            $.each(item, function(x, xitem) {
						                if (x === 0) newArr = [];
						                if (x > 0 && x < theNewData[0].length-1) {
						                    newArr.push(parseFloat(xitem));
						                }
						                if (x === theNewData[0].length-1) theYData.push(newArr);
						            });
						        });
						        var chart_series_color_array  = chart_series_color.split(",");
						        var chart_series_legend_array  = chart_series_legend.split(",");
						        var intial_visibility_array    = intial_visibility.split(",");
						        var check_box_series_array     = check_box_series.split(",");
						        $.each(theYLabels, function(i, item) {
						            seriesArr.push({name:item,y:theYData[i][0],drilldown:item,color:chart_series_color_array[i]});
						        });
					            var drilldown_data = [
									  ["","Week 1","Week 2","Week 3","Week 4"],
									  ["jan",19.9, 61.5, 116.4, 119.2],
									  ["feb",29.9, 71.5, 126.4, 129.2],
									  ["mar",39.9, 81.5, 136.4, 139.2],
									  ["apr",49.9, 91.5, 146.4, 149.2],
									  ["may",59.9, 51.5, 156.4, 159.2],
									  ["jun",69.9, 41.5, 166.4, 169.2],
									  ["jul",79.9, 31.5, 176.4, 179.2],
									  ["aug",89.9, 21.5, 186.4, 129.2],
									  ["sep",99.9, 11.5, 196.4, 129.2],
									  ["oct",109.9, 321.5, 1106.4, 129.2],
									  ["nov",411.9, 91.5, 1011.4, 129.2],
									  ["dec",412.9, 21.5, 10124, 129.2]
									];
					            jQuery('#drilldown-dataTable').handsontable({
					                data: drilldown_data,
					                minSpareRows: 1,
					                minSpareCols: 1,
								    colHeaders: true,
								    contextMenu: true,
					                cells: function (row, col, prop) {
								      var cellProperties = {};
								      if ($("#drilldown-dataTable").handsontable('getData')[row][prop] === '') {
								        cellProperties.readOnly = true;
								      }
								      return cellProperties;
								    },
					                onChange: function (changes, source) {
					                       if(changes != null){
						                     	update_highchart(chart_type);
						                 	 }
					                }
					            });

					            var drilldownSeries = [];
						        var theData = $("#drilldown-dataTable").data('handsontable').getData();
						        var theXCats = $.extend(true, [], theData[0]);
						        var theNewData = [];
						        var buildNewData = $.map(theData, function(item, i) {
						            if (i > 0 && i < theData.length-1) {
						                theNewData.push(item);
						            }
						        });
						        var theYCats = [];
						        var buildYCats = $.map(theNewData, function(item, i) {
						            theYCats.push(item[0]);
						        });
						        var theYLabels = [],
						            theYData = [];
						        var buildYData = $.map(theNewData, function(item, i) {
						            theYLabels.push(item[0]);
						            $.each(item, function(x, xitem) {
						                if (x === 0) newArr = [];
						                if (x > 0 && x < theNewData[0].length-1) {
						                    newArr.push([theXCats[x],parseFloat(xitem)]);
						                }
						                if (x === theNewData[0].length-1) theYData.push(newArr);
						            });
						        });

						        $.each(theYLabels, function(i, item) {
						            drilldownSeries.push({name:item,id:item,data:theYData[i]});
						        });
						        Highcharts.setOptions({
									lang: {
										drillUpText: chart_drilldown_text
									}
								});
						        jQuery('#container').highcharts({
							            chart: {
							                type: 'bar',
							                animation: {
							                       duration:chart_animation_duration
							                       ,easing:chart_animation
							                 },
							                backgroundColor:chart_background_color,
							                borderColor:chart_border_color,
							                borderWidth:chart_border_width,
							                borderRadious:chart_border_radious
							            },
							            title: {
							                text: chart_title
							            },
							            subtitle: {
							                text: chart_sub_title
							            },
							            xAxis: {
							                type: 'category'
							            },
							            yAxis: {
							                
							                title: {
							                    text: chart_yAxis
							                }
							            },
							             tooltip: {
							                enabled:chart_tooltip,
							                crosshairs:chart_tooltip_crosshairs,
							                valueDecimals: value_decimals,
							                valuePrefix: value_prefix,
							                valueSuffix: value_suffix,
							            },
							            credits: {
							                enabled:chart_credit,
							                text:credit_text,
							                href:credit_href
							            },
							            navigation: {
							                buttonOptions: {
							                    enabled:navigation_buttons
							                }
							            },
							            plotOptions: {
							                 series: {
							                 		stacking:chart_series_stacking,
							                        allowPointSelect:chart_allow_point_select,
							                        borderColor:bar_border_color,
							                        borderRadious:bar_border_radious,
							                        borderWidth:bar_border_width,
							                        negativeColor:chart_series_negative_color,
							                        pointPadding:column_point_padding,
							                        showCheckbox:show_checkbox
							                    },
							                column: {
							                    pointPadding: column_point_padding,
							                    borderWidth: bar_border_width,
							                    borderRadious:bar_border_radious,
							                    borderColor:bar_border_color,
							                    grouping: columns_grouping
							                }
							            },
							            series:[{
							            name: 'Rainfall',
							            colorByPoint: true,
							            data:seriesArr
							        	}],drilldown:{
							            	series:drilldownSeries
							            }
							        });

				        	} else {
				        			jQuery('#chart_drilldown_text').parent().parent().css('display','none');
							        jQuery('#chart-drildown-data').css('display','none');
							        jQuery("#chart_shortcode").val('Bar chart');
									var data = [
										  ["", "Africa", "America","Asia","Europe","Oceania"],
										  ["Year 1800", 107, 31,635,203,2],
										  ["Year 1900", 133, 156, 947, 408, 6],
										  ["Year 2008",973, 914, 4054, 732, 34]
										];
						            jQuery('#dataTable').handsontable({
						                data: data,
						                minSpareRows: 1,
									    minSpareCols: 1,
									    colHeaders: true,
									    contextMenu: true,
						                cells: function (row, col, prop) {
									      var cellProperties = {};
									      if ($("#dataTable").handsontable('getData')[row][prop] === '') {
									        cellProperties.readOnly = true;
									      }
									      return cellProperties;
									    },
						                onChange: function (changes, source) {
						                       if(changes != null){
						                     	update_highchart(chart_type);
						                 	  }
						                }
						            });
						            var seriesArr = [];
							        var theData = $("#dataTable").data('handsontable').getData();
							        var theXCats = $.extend(true, [], theData[0]);
							        theXCats = theXCats.splice(1,theXCats.length-2);
							        var theNewData = [];
							        var buildNewData = $.map(theData, function(item, i) {
							            if (i > 0 && i < theData.length-1) {
							                theNewData.push(item);
							            }
							        });
							        var theYCats = [];
							        var buildYCats = $.map(theNewData, function(item, i) {
							            theYCats.push(item[0]);
							        });
							        var theYLabels = [],
							           theYData = [];
							        var buildYData = $.map(theNewData, function(item, i) {
							            theYLabels.push(item[0]);
							            $.each(item, function(x, xitem) {
							                if (x === 0) newArr = [];
							                if (x > 0 && x < theNewData[0].length-1) {
							                    newArr.push(parseFloat(xitem));
							                }
							                if (x === theNewData[0].length-1) theYData.push(newArr);
							            });
							        });
							        var chart_series_color_array  = chart_series_color.split(",");
							        var chart_series_legend_array  = chart_series_legend.split(",");
							        var intial_visibility_array    = intial_visibility.split(",");
							        var check_box_series_array     = check_box_series.split(",");
							        $.each(theYLabels, function(i, item) {
							            seriesArr.push({name:item,data:theYData[i],color:chart_series_color_array[i],
							                showInLegend:chart_series_legend_array[i],visible:intial_visibility_array[i],
							                selected:check_box_series_array[i]
							            });
							        });
									 jQuery('#container').highcharts({
							            chart: {
							                type: 'bar',
							                 animation: {
							                        duration:chart_animation_duration,
							                        easing:chart_animation
							                 },
							                color:chart_series_generl_color,
							                backgroundColor:chart_background_color,
							                borderColor:chart_border_color,
							                borderWidth:chart_border_width,
							                borderRadious:chart_border_radious
							            },
							            title: {
							                text:chart_title 
							            },
							            subtitle: {
							                text: chart_sub_title
							            },
							            xAxis: {
							                categories: theXCats,
							                title: {
							                    text: chart_xAxis
							                }
							            },
							            yAxis: {
							                
							                title: {
							                    text: chart_yAxis,
							                    align: 'high'
							                },
							                labels: {
							                    overflow: 'justify'
							                }
							            },
							             tooltip: {
							                enabled:chart_tooltip,
							                crosshairs:chart_tooltip_crosshairs,
							                valueDecimals: value_decimals,
							                valuePrefix: value_prefix,
							                valueSuffix: value_suffix
							            },
							            plotOptions: {
							                 series: {
							                 		stacking:chart_series_stacking,
							                        allowPointSelect:chart_allow_point_select,
							                        borderColor:bar_border_color,
							                        borderRadious:bar_border_radious,
							                        borderWidth:bar_border_width,
							                        negativeColor:chart_series_negative_color,
							                        pointPadding:column_point_padding,
							                        showCheckbox:show_checkbox,
						                            cursor: chart_cursor_pointer,
						                            events: {

						                                click: function() {

						                                    alert(chart_cursor_event_text);

						                                }

						                            }
							                    },
							                bar: {
							                    dataLabels: {
							                        enabled: chart_data_labels
							                    },
							                    grouping: columns_grouping
							                }
							            },
							            legend: {
							                layout: 'vertical',
							                align: 'right',
							                verticalAlign: 'top',
							                x: -40,
							                y: 100,
							                floating: true,
							                borderWidth: 1,
							                backgroundColor: (Highcharts.theme && Highcharts.theme.legendBackgroundColor || '#FFFFFF'),
							                shadow: true
							            },
							           credits: {
							                enabled: chart_credit,
							                text:credit_text,
							                href:credit_href
							            },
							            navigation: {
							                buttonOptions: {
							                    enabled:navigation_buttons
							                }
							            },
							            cursor: chart_cursor_pointer,
							            events: {
							                click: function() {
							                    alert(chart_cursor_event_text);
							                }
							            },
							            series: seriesArr
							        });
							}
						}
						if(chart_type == "bubble chart"){
								jQuery("#table-sparkline").css("display","none");
								jQuery("#result").css("display","none");
								jQuery("#container").css("display","block");
								jQuery("#chart_xAxis").parent().parent().css("display","table-row");
								jQuery("#chart_yAxis").parent().parent().css("display","table-row");
								jQuery("#chart_title").parent().parent().css("display","table-row");
								jQuery('#chart_drilldown').parent().parent().css('display','none');
								jQuery('#chart_drilldown_text').parent().parent().css('display','none');
								jQuery('#chart-drildown-data').css('display','none');
								jQuery("#chart_shortcode").parent().parent().css("display","table-row");
								jQuery('#chart_start_engle').parent().parent().css('display','none');
								jQuery('#chart_end_engle').parent().parent().css('display','none');
								jQuery('#chart_inner_size').parent().parent().css('display','none');
								jQuery('#pie_sliced').parent().parent().css('display','none');
								jQuery('#pie_legend').parent().parent().css('display','none');
								jQuery('#pie_sliced_offset').parent().parent().css('display','none');
								jQuery('#bar_border_color').parent().parent().css('display','none');
								jQuery('#bar_border_width').parent().parent().css('display','none');
								jQuery('#bar_border_radious').parent().parent().css('display','none');
								jQuery("#chart_title").val('Highcharts Bubbles');
								jQuery("#chart_shortcode").val("Bubble Chart");
								jQuery("#chart_sub_title").val('Source: Wikipedia.org');
								jQuery("#chart_xAxis").val("Countries");
								jQuery("#chart_yAxis").val('Population (millions)');

								var chart_title 	= jQuery("#chart_title").val(); 
								var chart_title 	= jQuery("#chart_shortcode").val(); 
								var chart_sub_title = jQuery("#chart_sub_title").val();
								var chart_xAxis 	= jQuery("#chart_xAxis").val();
								var chart_yAxis 	= jQuery("#chart_yAxis").val();

								var data = [
								  ["","X","Y","ZOOM","X","Y","ZOOM"],
								  ["Series 1", 97,36,79, 94,64,50],
								  ["Series 2", 25,10,87, 2,75,59],
								  ["Series 3",47,47,21,20,12,4]
								];
					            jQuery('#dataTable').handsontable({
					                data: data,
					                minSpareRows: 1,
								    minSpareCols: 1,
								    colHeaders: true,
								    contextMenu: true,
					                cells: function (row, col, prop) {
								      var cellProperties = {};
								      if ($("#dataTable").handsontable('getData')[row][prop] === '') {
								        cellProperties.readOnly = true;
								      }
								      return cellProperties;
								    },
					                onChange: function (changes, source) {
					                      if(changes != null){
					                     	update_highchart(chart_type);
					                 	  }
					                }
					            });
					             var seriesArr = [];
					            var theData = $("#dataTable").data('handsontable').getData();
					            var theXCats = $.extend(true, [], theData[0]);
					            theXCats = theXCats.splice(1,theXCats.length-2);
					            var theNewData = [];
					            var buildNewData = $.map(theData, function(item, i) {
					                if (i > 0 && i < theData.length-1) {
					                    theNewData.push(item);
					                }
					            });
					            var theYCats = [];
					            var buildYCats = $.map(theNewData, function(item, i) {
					                theYCats.push(item[0]);
					            });
								var theYLabels = [],
					                theYData = [],
					                temp = [];
					            var num_series = (((theNewData[0].length)-2)/3);
					            var buildYData = $.map(theNewData, function(item, i) {
					                theYLabels.push(item[0]);
					                var a = 0;      
					                $.each(item, function(x, xitem) {
					                    if (x === 0 || a === 3) {
					                    	newArr = [];
					                    	a = 0;
					                    }
					                    if (x > 0 && x < theNewData[0].length-1) {
					                        newArr.push(parseFloat(xitem));
					                    }
					                    if(x != 0){ a++; }
					                    if (a === 3) {  
					                    	theYData.push(newArr);
					                      }  
					                });
					            });
								for (var i=0,j=0; i<theYData.length; i++) {
									if(i%num_series == 0){
										if(i%num_series == 0 && i != 0){
											j++;
										}
										seriesArr[j] = new Array(theYData[i]);
									} else {
										seriesArr[j].push(theYData[i]);
									}
								};
								var newseriesArr = [];
					            var chart_series_color_array  = chart_series_color.split(",");
					            var chart_series_legend_array  = chart_series_legend.split(",");
					            var intial_visibility_array    = intial_visibility.split(",");
					            var check_box_series_array     = check_box_series.split(",");
					            $.each(theYLabels, function(i, item) {
					                newseriesArr.push({data:seriesArr[i],color:chart_series_color_array[i],
					                showInLegend:chart_series_legend_array[i],visible:intial_visibility_array[i],
					                selected:check_box_series_array[i]});
					            });
							    jQuery('#container').highcharts({
							    chart: {
							        type: 'bubble',
							        zoomType: 'xy',
							        animation: {
					                        duration:chart_animation_duration,
					                        easing:chart_animation
					                 },
					                backgroundColor:chart_background_color,
					                borderColor:chart_border_color,
					                borderWidth:chart_border_width,
					                borderRadious:chart_border_radious
							    },
					            credits: {
					                enabled:chart_credit,
					                text:credit_text,
					                href:credit_href
					            },
					            navigation: {
					                buttonOptions: {
					                    enabled:navigation_buttons
					                }
					            },
					            plotOptions:{
					                 series: {
					                        allowPointSelect:chart_allow_point_select,
					                        showCheckbox:show_checkbox,
				                            cursor: chart_cursor_pointer,
				                            events: {
				                                click: function() {
				                                    alert(chart_cursor_event_text);
				                                }
				                            }
					                    }
					            },
					             tooltip: {
					                enabled:chart_tooltip,
					                crosshairs:chart_tooltip_crosshairs,
					                valueDecimals: value_decimals,
					                valuePrefix: value_prefix,
					                valueSuffix: value_suffix
					            },
							    title: {
							    	text: chart_title
							    },
					             xAxis: {
					                title: {
					                    text: chart_xAxis
					                }
					            },
					            yAxis: {
					                title: {
					                    text: chart_yAxis
					                }
					            },
							    series:newseriesArr
							});

						}

						if(chart_type == "column chart"){
							jQuery("#table-sparkline").css("display","none");
							jQuery("#result").css("display","none");
							jQuery("#container").css("display","block");
							jQuery("#chart_title").parent().parent().css("display","table-row");
							jQuery("#chart_sub_title").parent().parent().css("display","table-row");
							jQuery("#chart_xAxis").parent().parent().css("display","table-row");
							jQuery("#chart_yAxis").parent().parent().css("display","table-row");
							jQuery("#chart_shortcode").parent().parent().css("display","table-row");
							jQuery('#chart_drilldown').parent().parent().css('display','table-row');
							jQuery('#chart_start_engle').parent().parent().css('display','none');
							jQuery('#chart_end_engle').parent().parent().css('display','none');
							jQuery('#chart_inner_size').parent().parent().css('display','none');
							jQuery('#pie_sliced').parent().parent().css('display','none');
							jQuery('#pie_legend').parent().parent().css('display','none');
							jQuery('#pie_sliced_offset').parent().parent().css('display','none');
							jQuery('#bar_border_color').parent().parent().css('display','table-row');
							jQuery('#bar_border_width').parent().parent().css('display','table-row');
							jQuery('#bar_border_radious').parent().parent().css('display','table-row');
							jQuery("#chart_title").val('Monthly Average Rainfall');
							jQuery("#chart_sub_title").val('Source: WorldClimate.com');
							jQuery("#chart_xAxis").val("Months");
							jQuery("#chart_yAxis").val('Rainfall (mm)');
							jQuery("#chart_shortcode").val("Column Chart");

							var chart_title 	= jQuery("#chart_title").val(); 
							var chart_sub_title = jQuery("#chart_sub_title").val();
							var chart_xAxis 	= jQuery("#chart_xAxis").val();
							var chart_yAxis 	= jQuery("#chart_yAxis").val();
							var chart_shortcode =  jQuery("#chart_shortcode").val();
							if(chart_drilldown == 'true'){
								jQuery('#chart_drilldown_text').parent().parent().css('display','table-row');
								jQuery('#chart-drildown-data').css('display','block');
								jQuery("#chart_shortcode").val('Column chart Drilldown');
								var data = [
										  ["","Tokyo"],
										  ["jan", 49.9],
										  ["feb", 71.5],
										  ["mar", 106.4],
										  ["apr", 129.2],
										  ["may", 144.0],
										  ["jun", 176.0],
										  ["jul", 135.6],
										  ["aug", 148.5],
										  ["sep", 216.4],
										  ["oct", 194.1],
										  ["nov", 95.6],
										  ["dec", 54.4],
										];
					            jQuery('#dataTable').handsontable({
					                data: data,
					                minSpareRows: 1,
								    minSpareCols: 1,
								    colHeaders: true,
								    contextMenu: true,
					                cells: function (row, col, prop) {
								      var cellProperties = {};
								      if ($("#dataTable").handsontable('getData')[row][prop] === '') {
								        cellProperties.readOnly = true;
								      }
								      return cellProperties;
								    },
					                onChange: function (changes, source) {
					                       if(changes != null){
					                     	update_highchart(chart_type);
					                 	  }
					                }

					            });
					            var seriesArr = [];
						        var theData = $("#dataTable").data('handsontable').getData();
						        var theXCats = $.extend(true, [], theData[0]);
						        theXCats = theXCats.splice(1,theXCats.length-2);
						        var theNewData = [];
						        var buildNewData = $.map(theData, function(item, i) {
						            if (i > 0 && i < theData.length-1) {
						                theNewData.push(item);
						            }
						        });
						        var theYCats = [];
						        var buildYCats = $.map(theNewData, function(item, i) {
						            theYCats.push(item[0]);
						        });
						        var theYLabels = [],
						            theYData = [];
						        var buildYData = $.map(theNewData, function(item, i) {
						            theYLabels.push(item[0]);
						            $.each(item, function(x, xitem) {
						                if (x === 0) newArr = [];
						                if (x > 0 && x < theNewData[0].length-1) {
						                    newArr.push(parseFloat(xitem));
						                }
						                if (x === theNewData[0].length-1) theYData.push(newArr);
						            });
						        });
						        var chart_series_color_array  = chart_series_color.split(",");
						        var chart_series_legend_array  = chart_series_legend.split(",");
						        var intial_visibility_array    = intial_visibility.split(",");
						        var check_box_series_array     = check_box_series.split(",");
						        $.each(theYLabels, function(i, item) {
						            seriesArr.push({name:item,y:theYData[i][0],drilldown:item,color:chart_series_color_array[i]});
						        });
					            var drilldown_data = [
									  ["","Week 1","Week 2","Week 3","Week 4"],
									  ["jan",19.9, 61.5, 116.4, 119.2],
									  ["feb",29.9, 71.5, 126.4, 129.2],
									  ["mar",39.9, 81.5, 136.4, 139.2],
									  ["apr",49.9, 91.5, 146.4, 149.2],
									  ["may",59.9, 51.5, 156.4, 159.2],
									  ["jun",69.9, 41.5, 166.4, 169.2],
									  ["jul",79.9, 31.5, 176.4, 179.2],
									  ["aug",89.9, 21.5, 186.4, 129.2],
									  ["sep",99.9, 11.5, 196.4, 129.2],
									  ["oct",109.9, 321.5, 1106.4, 129.2],
									  ["nov",411.9, 91.5, 1011.4, 129.2],
									  ["dec",412.9, 21.5, 10124, 129.2]
									];
					            jQuery('#drilldown-dataTable').handsontable({
					                data: drilldown_data,
					                minSpareRows: 1,
					                minSpareCols: 1,
								    colHeaders: true,
								    contextMenu: true,
					                cells: function (row, col, prop) {
								      var cellProperties = {};
								      if ($("#drilldown-dataTable").handsontable('getData')[row][prop] === '') {
								        cellProperties.readOnly = true;
								      }
								      return cellProperties;
								    },
					                onChange: function (changes, source) {
					                       if(changes != null){
						                     	update_highchart(chart_type);
						                 	 }
					                }
					            });

					            var drilldownSeries = [];
						        var theData = $("#drilldown-dataTable").data('handsontable').getData();
						        var theXCats = $.extend(true, [], theData[0]);
						        var theNewData = [];
						        var buildNewData = $.map(theData, function(item, i) {
						            if (i > 0 && i < theData.length-1) {
						                theNewData.push(item);
						            }
						        });
						        var theYCats = [];
						        var buildYCats = $.map(theNewData, function(item, i) {
						            theYCats.push(item[0]);
						        });
						        var theYLabels = [],
						            theYData = [];
						        var buildYData = $.map(theNewData, function(item, i) {
						            theYLabels.push(item[0]);
						            $.each(item, function(x, xitem) {
						                if (x === 0) newArr = [];
						                if (x > 0 && x < theNewData[0].length-1) {
						                    newArr.push([theXCats[x],parseFloat(xitem)]);
						                }
						                if (x === theNewData[0].length-1) theYData.push(newArr);
						            });
						        });

						        $.each(theYLabels, function(i, item) {
						            drilldownSeries.push({name:item,id:item,data:theYData[i]});
						        });
						       Highcharts.setOptions({
									lang: {
										drillUpText: chart_drilldown_text
									}
								});
						        jQuery('#container').highcharts({
							            chart: {
							                type: 'column',
							                animation: {
							                        duration:chart_animation_duration,
					                        		easing:chart_animation
							                 },
							                backgroundColor:chart_background_color,
							                borderColor:chart_border_color,
							                borderWidth:chart_border_width,
							                borderRadious:chart_border_radious
							            },
							            title: {
							                text: chart_title
							            },
							            subtitle: {
							                text: chart_sub_title
							            },
							            xAxis: {
							                type: 'category'
							            },
							            yAxis: {
							                
							                title: {
							                    text: chart_yAxis
							                }
							            },
							             tooltip: {
							                enabled:chart_tooltip,
							                crosshairs:chart_tooltip_crosshairs,
							                valueDecimals: value_decimals,
							                valuePrefix: value_prefix,
							                valueSuffix: value_suffix,
							            },
							            credits: {
							                enabled:chart_credit,
							                text:credit_text,
							                href:credit_href
							            },
							            navigation: {
							                buttonOptions: {
							                    enabled:navigation_buttons
							                }
							            },
							            plotOptions: {
							                 series: {
							                 		stacking:chart_series_stacking,
							                        allowPointSelect:chart_allow_point_select,
							                        borderColor:bar_border_color,
							                        borderRadious:bar_border_radious,
							                        borderWidth:bar_border_width,
							                        negativeColor:chart_series_negative_color,
							                        pointPadding:column_point_padding,
							                        showCheckbox:show_checkbox
							                    },
							                column: {
							                    pointPadding: column_point_padding,
							                    borderWidth: bar_border_width,
							                    borderRadious:bar_border_radious,
							                    borderColor:bar_border_color,
							                    grouping: columns_grouping
							                }
							            },
							            series:[{
							            name: 'Rainfall',
							            colorByPoint: true,
							            data:seriesArr
							        	}],drilldown:{
							            	series:drilldownSeries
							            }
							        });

				        	} else {
				        			jQuery('#chart_drilldown_text').parent().parent().css('display','none');
									jQuery('#chart-drildown-data').css('display','none');
									jQuery("#chart_shortcode").val('Column chart');
						        	var data = [
										  ["","Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"],
										  ["Tokyo", 49.9, 71.5, 106.4, 129.2, 144.0, 176.0, 135.6, 148.5, 216.4, 194.1, 95.6, 54.4],
										  ["New York", 83.6, 78.8, 98.5, 93.4, 106.0, 84.5, 105.0, 104.3, 91.2, 83.5, 106.6, 92.3],
										  ["London",48.9, 38.8, 39.3, 41.4, 47.0, 48.3, 59.0, 59.6, 52.4, 65.2, 59.3, 51.2],
										  ["Berlin",42.4, 33.2, 34.5, 39.7, 52.6, 75.5, 57.4, 60.4, 47.6, 39.1, 46.8, 51.1]
										];
						            jQuery('#dataTable').handsontable({
						                data: data,
						                minSpareRows: 1,
									    minSpareCols: 1,
									    colHeaders: true,
									    contextMenu: true,
						                cells: function (row, col, prop) {
									      var cellProperties = {};
									      if ($("#dataTable").handsontable('getData')[row][prop] === '') {
									        cellProperties.readOnly = true;
									      }
									      return cellProperties;
									    },
						                onChange: function (changes, source) {
						                       if(changes != null){
						                     	update_highchart(chart_type);
						                 	  }
						                }

						            });
						            var seriesArr = [];
							        var theData = $("#dataTable").data('handsontable').getData();
							        var theXCats = $.extend(true, [], theData[0]);
							        theXCats = theXCats.splice(1,theXCats.length-2);
							        var theNewData = [];
							        var buildNewData = $.map(theData, function(item, i) {
							            if (i > 0 && i < theData.length-1) {
							                theNewData.push(item);
							            }
							        });
							        var theYCats = [];
							        var buildYCats = $.map(theNewData, function(item, i) {
							            theYCats.push(item[0]);
							        });
							        var theYLabels = [],
							            theYData = [];
							        var buildYData = $.map(theNewData, function(item, i) {
							            theYLabels.push(item[0]);
							            $.each(item, function(x, xitem) {
							                if (x === 0) newArr = [];
							                if (x > 0 && x < theNewData[0].length-1) {
							                    newArr.push(parseFloat(xitem));
							                }
							                if (x === theNewData[0].length-1) theYData.push(newArr);
							            });
							        });
							        var chart_series_color_array  = chart_series_color.split(",");
							        var chart_series_legend_array  = chart_series_legend.split(",");
							        var intial_visibility_array    = intial_visibility.split(",");
							        var check_box_series_array     = check_box_series.split(",");
							        $.each(theYLabels, function(i, item) {
							            seriesArr.push({name:item,data:theYData[i],color:chart_series_color_array[i],
							                showInLegend:chart_series_legend_array[i],visible:intial_visibility_array[i],
							                selected:check_box_series_array[i]});
							        });
									 jQuery('#container').highcharts({
							            chart: {
							                type: 'column',
							                animation: {
							                        duration:chart_animation_duration,
							                        easing:chart_animation
							                 },
							                backgroundColor:chart_background_color,
							                borderColor:chart_border_color,
							                borderWidth:chart_border_width,
							                borderRadious:chart_border_radious
							            },
							            title: {
							                text: chart_title
							            },
							            subtitle: {
							                text: chart_sub_title
							            },
							            xAxis: {
							                categories:theXCats
							            },
							            yAxis: {
							                
							                title: {
							                    text: chart_yAxis
							                }
							            },
							             tooltip: {
							                enabled:chart_tooltip,
							                crosshairs:chart_tooltip_crosshairs,
							                valueDecimals: value_decimals,
							                valuePrefix: value_prefix,
							                valueSuffix: value_suffix,
							            },
							            credits: {
							                enabled:chart_credit,
							                text:credit_text,
							                href:credit_href
							            },
							            navigation: {
							                buttonOptions: {
							                    enabled:navigation_buttons
							                }
							            },
							            plotOptions: {
							                 series: {
							                 		stacking:chart_series_stacking,
							                        allowPointSelect:chart_allow_point_select,
							                        borderColor:bar_border_color,
							                        borderRadious:bar_border_radious,
							                        borderWidth:bar_border_width,
							                        negativeColor:chart_series_negative_color,
							                        pointPadding:column_point_padding,
							                        showCheckbox:show_checkbox,
						                            cursor: chart_cursor_pointer,
						                            events: {

						                                click: function() {

						                                    alert(chart_cursor_event_text);

						                                }

						                            }
							                    },
							                column: {
							                    pointPadding: column_point_padding,
							                    borderWidth: bar_border_width,
							                    borderRadious:bar_border_radious,
							                    borderColor:bar_border_color,
							                    grouping: columns_grouping
							                }
							            },
							            series:seriesArr
							        });
					        	}
							
						}

						if(chart_type == "line chart"){
							jQuery("#table-sparkline").css("display","none");
							jQuery("#result").css("display","none");
							jQuery("#container").css("display","block");
							jQuery("#chart_title").parent().parent().css("display","table-row");
							jQuery("#chart_sub_title").parent().parent().css("display","table-row");
							jQuery("#chart_xAxis").parent().parent().css("display","table-row");
							jQuery("#chart_yAxis").parent().parent().css("display","table-row");
							jQuery("#chart_shortcode").parent().parent().css("display","table-row");
							jQuery('#chart_drilldown').parent().parent().css('display','none');
							jQuery('#chart_drilldown_text').parent().parent().css('display','none');
							jQuery('#chart_start_engle').parent().parent().css('display','none');
							jQuery('#chart_end_engle').parent().parent().css('display','none');
							jQuery('#chart_inner_size').parent().parent().css('display','none');
							jQuery('#pie_sliced').parent().parent().css('display','none');
							jQuery('#pie_legend').parent().parent().css('display','none');
							jQuery('#pie_sliced_offset').parent().parent().css('display','none');
							jQuery('#bar_border_color').parent().parent().css('display','none');
							jQuery('#bar_border_width').parent().parent().css('display','none');
							jQuery('#bar_border_radious').parent().parent().css('display','none');
							jQuery('#chart-drildown-data').css('display','none');
							jQuery("#chart_title").val('Monthly Average Rainfall');
							jQuery("#chart_sub_title").val('Source: WorldClimate.com');
							jQuery("#chart_xAxis").val("Months");
							jQuery("#chart_yAxis").val('Rainfall (mm)');
							jQuery("#chart_shortcode").val("Line Chart");

							var chart_title 	= jQuery("#chart_title").val(); 
							var chart_sub_title = jQuery("#chart_sub_title").val();
							var chart_xAxis 	= jQuery("#chart_xAxis").val();
							var chart_yAxis 	= jQuery("#chart_yAxis").val();
							var chart_shortcode =  jQuery("#chart_shortcode").val();
							var data = [
								  ["","Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"],
								  ["Tokyo", 49.9, 71.5, 106.4, 129.2, 144.0, 176.0, 135.6, 148.5, 216.4, 194.1, 95.6, 54.4],
								  ["New York", 83.6, 78.8, 98.5, 93.4, 106.0, 84.5, 105.0, 104.3, 91.2, 83.5, 106.6, 92.3],
								  ["London",48.9, 38.8, 39.3, 41.4, 47.0, 48.3, 59.0, 59.6, 52.4, 65.2, 59.3, 51.2],
								  ["Berlin",42.4, 33.2, 34.5, 39.7, 52.6, 75.5, 57.4, 60.4, 47.6, 39.1, 46.8, 51.1]
								];
				            jQuery('#dataTable').handsontable({
				                data: data,
				                minSpareRows: 1,
							    minSpareCols: 1,
							    colHeaders: true,
							    contextMenu: true,
				                cells: function (row, col, prop) {
							      var cellProperties = {};
							      if ($("#dataTable").handsontable('getData')[row][prop] === '') {
							        cellProperties.readOnly = true;
							      }
							      return cellProperties;
							    },
				                onChange: function (changes, source) {
				                      if(changes != null){
				                     	update_highchart(chart_type);
				                 	  }
				                }
				            });
				            var seriesArr = [];
					        var theData = $("#dataTable").data('handsontable').getData();
					        var theXCats = $.extend(true, [], theData[0]);
					        theXCats = theXCats.splice(1,theXCats.length-2);
					        var theNewData = [];
					        var buildNewData = $.map(theData, function(item, i) {
					            if (i > 0 && i < theData.length-1) {
					                theNewData.push(item);
					            }
					        });
					        var theYCats = [];
					        var buildYCats = $.map(theNewData, function(item, i) {
					            theYCats.push(item[0]);
					        });
					        var theYLabels = [],
					            theYData = [];
					        var buildYData = $.map(theNewData, function(item, i) {
					            theYLabels.push(item[0]);
					            $.each(item, function(x, xitem) {
					                if (x === 0) newArr = [];
					                if (x > 0 && x < theNewData[0].length-1) {
					                    newArr.push(parseFloat(xitem));
					                }
					                if (x === theNewData[0].length-1) theYData.push(newArr);
					            });
					        });
					        var chart_series_color_array  = chart_series_color.split(",");
					        var chart_series_legend_array  = chart_series_legend.split(",");
					        var intial_visibility_array    = intial_visibility.split(",");
					        var check_box_series_array     = check_box_series.split(",");
					        $.each(theYLabels, function(i, item) {
					            seriesArr.push({name:item,data:theYData[i],color:chart_series_color_array[i],
					                showInLegend:chart_series_legend_array[i],visible:intial_visibility_array[i],
					                selected:check_box_series_array[i]});
					        });
					        jQuery('#container').highcharts({
					            chart:{
					                type:'line',
					                animation: {
					                        duration:chart_animation_duration,
					                        easing:chart_animation
					                 },
					                backgroundColor:chart_background_color,
					                borderColor:chart_border_color,
					                borderWidth:chart_border_width,
					                borderRadious:chart_border_radious
					            },
					            title: {
					                text: chart_title,
					                x: -20 //center
					            },
					            subtitle: {
					                text: chart_sub_title,
					                x: -20
					            },
					            xAxis: {
					                categories:theXCats
					            },
					            yAxis: {
					                title: {
					                    text: chart_yAxis
					                },
					                plotLines: [{
					                    value: 0,
					                    width: 1,
					                    color: '#808080'
					                }]
					            },
					             tooltip: {
					                enabled:chart_tooltip,
					                crosshairs:chart_tooltip_crosshairs,
					                valueDecimals: value_decimals,
					                valuePrefix: value_prefix,
					                valueSuffix: value_suffix,
					            },
					            credits: {
					                enabled: chart_credit,
					                text:credit_text,
					                href:credit_href
					            },
					            navigation: {
					                buttonOptions: {
					                    enabled:navigation_buttons
					                }
					            },
					            legend: {
					                layout: 'vertical',
					                align: 'right',
					                verticalAlign: 'middle',
					                borderWidth: 0
					            },
					            plotOptions:{
					                 series: {
					                        allowPointSelect:chart_allow_point_select,
					                        showCheckbox:show_checkbox,
				                            cursor: chart_cursor_pointer,
				                            events: {

				                                click: function() {

				                                    alert(chart_cursor_event_text);

				                                }

				                            }
					                    }
					            },
					            series: seriesArr
					        });
						}
						if(chart_type == "pie chart"){
							jQuery("#table-sparkline").css("display","none");
							jQuery("#result").css("display","none");
							jQuery("#container").css("display","block");
							if(chart_drilldown == 'true'){
								jQuery("#chart-drildown-data").css("display","block");
							}
							jQuery("#chart_xAxis").parent().parent().css("display","none");
							jQuery("#chart_yAxis").parent().parent().css("display","none");
							jQuery("#chart_title").parent().parent().css("display","table-row");
						    jQuery("#chart_shortcode").parent().parent().css("display","table-row");
						    jQuery('#chart_drilldown').parent().parent().css('display','table-row');
						    jQuery('#chart_start_engle').parent().parent().css('display','none');
							jQuery('#chart_end_engle').parent().parent().css('display','table-row');
							jQuery('#chart_inner_size').parent().parent().css('display','table-row');
							jQuery('#pie_sliced').parent().parent().css('display','table-row');
							jQuery('#pie_legend').parent().parent().css('display','table-row');
							jQuery('#pie_sliced_offset').parent().parent().css('display','table-row');
							jQuery('#bar_border_color').parent().parent().css('display','none');
							jQuery('#bar_border_width').parent().parent().css('display','none');
							jQuery('#bar_border_radious').parent().parent().css('display','none');
							jQuery("#chart_title").val('Browser market shares at a specific website, 2014');
							jQuery("#chart_sub_title").val('Source: Wikipedia.org');
							jQuery("#chart_xAxis").val("Countries");
							jQuery("#chart_yAxis").val('Population (millions)');
							jQuery("#chart_shortcode").val("Pie Chart");
							var chart_title 	= jQuery("#chart_title").val(); 
							var chart_sub_title = jQuery("#chart_sub_title").val();
							var chart_xAxis 	= jQuery("#chart_xAxis").val();
							var chart_yAxis 	= jQuery("#chart_yAxis").val();
							var chart_shortcode =  jQuery("#chart_shortcode").val();
							
				            if(chart_drilldown == 'true'){
				            	jQuery('#chart_drilldown_text').parent().parent().css('display','table-row');
							    jQuery('#chart-drildown-data').css('display','block');
							    jQuery("#chart_shortcode").val('Pie chart Drilldown');
							    var data = [
								  ["","Usage"],
								  ["IE",45.0],
								  ["Chrome",12.8],
								  ["Opera",6.2],
								  ["Safari",8.5],
								  ["Others",0.7]
								];
					            jQuery('#dataTable').handsontable({
					                data: data,
					                minSpareRows: 1,
								    colHeaders: true,
								    contextMenu: true,
					                cells: function (row, col, prop) {
								      var cellProperties = {};
								      if ($("#dataTable").handsontable('getData')[row][prop] === '') {
								        cellProperties.readOnly = true;
								      }
								      return cellProperties;
								    },
					                onChange: function (changes, source) {
					                       if(changes != null){
						                     	update_highchart(chart_type);
						                 	 }
					                }
					            });
					            var drilldown_data = [
									  ["","Version Name","Usage","Version Name","Usage"],
									  ["IE","IE 8",30.0,"IE 9",70.0],
									  ["Chrome","Chrome 8",12.8,"Chrome 9",25],
									  ["Opera","Opera 8",12.8,"Opera 9",25],
									  ["Safari","Safari 8",20.8,"Safari 9",40.8],
									  ["Others","Others 8",62.8,"Others 9",25]
									];
					            jQuery('#drilldown-dataTable').handsontable({
					                data: drilldown_data,
					                minSpareRows: 1,
					                minSpareCols: 1,
								    colHeaders: true,
								    contextMenu: true,
					                cells: function (row, col, prop) {
								      var cellProperties = {};
								      if ($("#drilldown-dataTable").handsontable('getData')[row][prop] === '') {
								        cellProperties.readOnly = true;
								      }
								      return cellProperties;
								    },
					                onChange: function (changes, source) {
					                       if(changes != null){
						                     	update_highchart(chart_type);
						                 	 }
					                }

					            });
					            var seriesArr = [];
					            var theData = $("#dataTable").data('handsontable').getData();
					            var theXCats = $.extend(true, [], theData[0]);
					            theXCats = theXCats.splice(1,theXCats.length-2);
					            var theNewData = [];
					            var buildNewData = $.map(theData, function(item, i) {
					                if (i > 0 && i < theData.length-1) {
					                    theNewData.push(item);
					                }
					            });
					            var theYCats = [];
					            var buildYCats = $.map(theNewData, function(item, i) {
					                theYCats.push(item[0]);
					            });
					            var theYLabels = [],
					                theYData = [];
					            var buildYData = $.map(theNewData, function(item, i) {
					                theYLabels.push(item[0]);
					                $.each(item, function(x, xitem) {
					                    if (x === 0) newArr = [];
					                    if (x > 0 && x < theNewData[0].length-1) {
					                        newArr.push(parseFloat(xitem));
					                    }
					                    if (x === theNewData[0].length-1) theYData.push(newArr);
					                });
					            });
					            if(pie_sliced =="true"){ var sliced = true; } else { var sliced = false; }
					            var chart_series_color_array  = chart_series_color.split(",");
						        var chart_series_legend_array  = chart_series_legend.split(",");
						        var intial_visibility_array    = intial_visibility.split(",");
						        var check_box_series_array     = check_box_series.split(",");
				                seriesArr.push({name:theYLabels[0],y:theYData[0][0],drilldown:theYLabels[0],selected:true,sliced:sliced,color:chart_series_color_array[0],showInLegend:chart_series_legend_array[0]});
				                $.each(theYLabels, function(i, item) {
				                    if(i > 0){
				                         seriesArr.push({name:item,y:theYData[i][0],drilldown:item,color:chart_series_color_array[i],showInLegend:chart_series_legend_array[i]});
				                    }
				                   
				                });

				            	var drilldownSeries = [];
					            var theData = $("#drilldown-dataTable").data('handsontable').getData();
					            var theXCats = $.extend(true, [], theData[0]);
					            theXCats = theXCats.splice(1,theXCats.length-2);
					            var theNewData = [];
					            var buildNewData = $.map(theData, function(item, i) {
					                if (i > 0 && i < theData.length-1) {
					                    theNewData.push(item);
					                }
					            });
					            var theYCats = [];
					            var buildYCats = $.map(theNewData, function(item, i) {
					                theYCats.push(item[0]);
					            });
					            var theYLabels = [],
					                theYData = [],
					                temp = [],
					                newnewArray = [];
					            var num_series = (((theNewData[0].length)-2)/2);
					            var buildYData = $.map(theNewData, function(item, i) {
					                theYLabels.push(item[0]);
					                var a = 0;
					                $.each(item, function(x, xitem) {
					                    if (x === 0 || a === 2) {
					                    	newArr = [];
					                    	a = 0;	
					                    }
					                    if (x > 0 && x < theNewData[0].length-1) {
					                        newArr.push(xitem);
					                    }
					                    if(x != 0){ a++; }
					                    if (a === 2) {  
					                    	theYData.push(newArr);
					                    }
					                   if(x == theNewData[0].length-2){
					                   		newnewArray.push(theYData);
					                   		theYData = [];
					                   }
					                });
					            });
					             $.each(theYLabels, function(i, item) {
					                drilldownSeries.push({id:item,data:newnewArray[i],color:chart_series_color_array[i],showInLegend:chart_series_legend_array[i]});
					            });
					             Highcharts.setOptions({
									lang: {
										drillUpText: chart_drilldown_text
									}
								});
								 jQuery('#container').highcharts({
							        chart: {
							        	type:'pie',
							            plotBackgroundColor: null,
							            plotBorderWidth: null,
							            plotShadow: false,
					                    backgroundColor:chart_background_color,
					                    borderColor:chart_border_color,
					                    borderWidth:chart_border_width,
					                    borderRadious:chart_border_radious
							        },
							        animation: {
					                        duration:chart_animation_duration,
					                        easing:chart_animation
					                 },
							        title: {
							            text: chart_title
							        },
					                 tooltip: {
					                    enabled:chart_tooltip,
					                    crosshairs:chart_tooltip_crosshairs,
					                    valueDecimals: value_decimals,
					                    valuePrefix: value_prefix,
					                    valueSuffix: value_suffix,
					                },
					                credits: {
					                    enabled:chart_credit,
					                    text:credit_text,
					                    href:credit_href
					                },
					                navigation: {
						                buttonOptions: {
						                    enabled:navigation_buttons
						                }
						            },
							        plotOptions: {
							            pie: {
							                cursor: chart_cursor_pointer,
					                        startAngle: chart_start_engle,
					                        endAngle:chart_end_engle,
					                        innerSize:chart_inner_size,
					                        slicedOffset:pie_sliced_offset,
							                dataLabels: {
							                    enabled: chart_data_labels,
							                    format: '<b>{point.name}</b>: {point.percentage:.1f} %',
							                    style: {
							                        color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
							                    }
							                }
							            },
					                     series: {
					                        allowPointSelect:chart_allow_point_select,
					                        showCheckbox:show_checkbox
					                    }
							        },
							        series: [{
							            type: 'pie',
							            name: chart_title,
							            data: seriesArr,
							            showInLegend:true
							        }],
					                drilldown: {
					                    series: drilldownSeries
					                }

							    });
				        	} else {
				        		jQuery('#chart_drilldown_text').parent().parent().css('display','none');
							    jQuery('#chart-drildown-data').css('display','none');
							    jQuery("#chart_shortcode").val('Pie chart');
							    var data = [
								  ["","Usage"],
								  ["IE",45.0],
								  ["Chrome",12.8],
								  ["Opera",6.2],
								  ["Safari",8.5],
								  ["Others",0.7]
								];
					            jQuery('#dataTable').handsontable({
					                data: data,
					                minSpareRows: 1,
								    colHeaders: true,
								    contextMenu: true,
					                cells: function (row, col, prop) {
								      var cellProperties = {};
								      if ($("#dataTable").handsontable('getData')[row][prop] === '') {
								        cellProperties.readOnly = true;
								      }
								      return cellProperties;
								    },
					                onChange: function (changes, source) {
					                       if(changes != null){
						                     	update_highchart(chart_type);
						                 	 }
					                }
					            });
					            var seriesArr = [];
					            var theData = $("#dataTable").data('handsontable').getData();
					            var theXCats = $.extend(true, [], theData[0]);
					            theXCats = theXCats.splice(1,theXCats.length-2);
					            var theNewData = [];
					            var buildNewData = $.map(theData, function(item, i) {
					                if (i > 0 && i < theData.length-1) {
					                    theNewData.push(item);
					                }
					            });
					            var theYCats = [];
					            var buildYCats = $.map(theNewData, function(item, i) {
					                theYCats.push(item[0]);
					            });
					            var theYLabels = [],
					                theYData = [];
					            var buildYData = $.map(theNewData, function(item, i) {
					                theYLabels.push(item[0]);
					                $.each(item, function(x, xitem) {
					                    if (x === 0) newArr = [];
					                    if (x > 0 && x < theNewData[0].length-1) {
					                        newArr.push(parseFloat(xitem));
					                    }
					                    if (x === theNewData[0].length-1) theYData.push(newArr);
					                });
					            });
					            if(pie_sliced =="true"){ var sliced = true; } else { var sliced = false; }
					            seriesArr.push({name:theYLabels[0],y:theYData[0][0],selected:true,sliced:sliced});
					            $.each(theYLabels, function(i, item) {
					            	if(i >0){
					            		 seriesArr.push([item,theYData[i][0]]);
					            	}
					               
					            });
								 jQuery('#container').highcharts({
							        chart: {
							        	type:'pie',
							            plotBackgroundColor: null,
							            plotBorderWidth: null,
							            plotShadow: false,
					                    backgroundColor:chart_background_color,
					                    borderColor:chart_border_color,
					                    borderWidth:chart_border_width,
					                    borderRadious:chart_border_radious
							        },
							        animation: {
					                        duration:chart_animation_duration,
					                        easing:chart_animation
					                 },
							        title: {
							            text: chart_title
							        },
					                 tooltip: {
					                    enabled:chart_tooltip,
					                    crosshairs:chart_tooltip_crosshairs,
					                    valueDecimals: value_decimals,
					                    valuePrefix: value_prefix,
					                    valueSuffix: value_suffix,
					                },
					                credits: {
					                    enabled:chart_credit,
					                    text:credit_text,
					                    href:credit_href
					                },
					                navigation: {
						                buttonOptions: {
						                    enabled:navigation_buttons
						                }
						            },
							        plotOptions: {
							            pie: {
							                cursor: chart_cursor_pointer,
					                        startAngle: chart_start_engle,
					                        endAngle:chart_end_engle,
					                        innerSize:chart_inner_size,
					                        slicedOffset:pie_sliced_offset,
							                dataLabels: {
							                    enabled: chart_data_labels,
							                    format: '<b>{point.name}</b>: {point.percentage:.1f} %',
							                    style: {
							                        color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
							                    }
							                }
							            },
					                     series: {
					                        allowPointSelect:chart_allow_point_select,
					                        showCheckbox:show_checkbox,
				                            cursor: chart_cursor_pointer,
				                            events: {

				                                click: function() {

				                                    alert(chart_cursor_event_text);

				                                }

				                            }
					                    }
							        },
							        series: [{
							            type: 'pie',
							            name: chart_title,
							            data: seriesArr,
							            showInLegend:true
							        }]

							    });
				        	}


						}

						if(chart_type == "scatter chart"){
								jQuery("#table-sparkline").css("display","none");
								jQuery("#result").css("display","none");
								jQuery("#container").css("display","block");
								jQuery("#chart_title").parent().parent().css("display","table-row");
								jQuery("#chart_sub_title").parent().parent().css("display","table-row");
								jQuery("#chart_xAxis").parent().parent().css("display","table-row");
								jQuery("#chart_yAxis").parent().parent().css("display","table-row");
								jQuery("#chart_shortcode").parent().parent().css("display","table-row");
								jQuery('#chart_drilldown').parent().parent().css('display','none');
								jQuery('#chart_drilldown_text').parent().parent().css('display','none');
								jQuery('#chart_start_engle').parent().parent().css('display','none');
								jQuery('#chart_end_engle').parent().parent().css('display','none');
								jQuery('#chart_inner_size').parent().parent().css('display','none');
								jQuery('#pie_sliced').parent().parent().css('display','none');
								jQuery('#pie_legend').parent().parent().css('display','none');
								jQuery('#pie_sliced_offset').parent().parent().css('display','none');
								jQuery('#bar_border_color').parent().parent().css('display','none');
								jQuery('#bar_border_width').parent().parent().css('display','none');
								jQuery('#bar_border_radious').parent().parent().css('display','none');
								jQuery('#chart-drildown-data').css('display','none');
								jQuery("#chart_title").val('Height Versus Weight of 507 Individuals by Gender');
								jQuery("#chart_sub_title").val('Source: Heinz  2003');
								jQuery("#chart_xAxis").val("Height (cm)");
								jQuery("#chart_yAxis").val('Weight (kg)');
								jQuery("#chart_shortcode").val("Scatter Chart");

								var chart_title 	= jQuery("#chart_title").val(); 
								var chart_sub_title = jQuery("#chart_sub_title").val();
								var chart_xAxis 	= jQuery("#chart_xAxis").val();
								var chart_yAxis 	= jQuery("#chart_yAxis").val();
								var chart_shortcode =  jQuery("#chart_shortcode").val();
							    var data = [
								  ["","Height","Weight","Height","Weight","Height","Weight","Height","Weight","Height","Weight"],
								  ["Male", 174.0, 65.6, 175.3, 71.8, 193.5, 80.7, 186.5, 72.6, 187.2, 78.8],
								  ["Female", 161.2, 51.6, 167.5, 59.0, 159.5, 49.2, 157.0, 63.0, 155.8, 53.6]
								];
					            jQuery('#dataTable').handsontable({
					                data: data,
								    minSpareCols: 1,
								    colHeaders: true,
								    contextMenu: true,
					                cells: function (row, col, prop) {
								      var cellProperties = {};
								      if ($("#dataTable").handsontable('getData')[row][prop] === '') {
								        cellProperties.readOnly = true;
								      }
								      return cellProperties;
								    },
					                onChange: function (changes, source) {
					                       if(changes != null){
					                     	update_highchart(chart_type);
					                 	  }
					                }
					            });
					            var seriesArr = [];
					            var theData = $("#dataTable").data('handsontable').getData();
					            var theXCats = $.extend(true, [], theData[0]);
					            theXCats = theXCats.splice(1,theXCats.length-2);
					            var theNewData = [];
					            var buildNewData = $.map(theData, function(item, i) {
					                if (i > 0 && i < theData.length-1) {
					                    theNewData.push(item);
					                }
					            });
					            var theYCats = [];
					            var buildYCats = $.map(theNewData, function(item, i) {
					                theYCats.push(item[0]);
					            });
					            var theYLabels = [],
					                theYData = [],
					                temp = [];
					            var num_series = (((theNewData[0].length)-2)/2);
					            var buildYData = $.map(theNewData, function(item, i) {
					                theYLabels.push(item[0]);
					                var a = 0;
					                $.each(item, function(x, xitem) {
					                    if (x === 0 || a === 2) {
					                    	newArr = [];
					                    	a = 0;	
					                    }
					                    if (x > 0 && x < theNewData[0].length-1) {
					                        newArr.push(parseFloat(xitem));
					                    }
					                    if(x != 0){ a++; }
					                    if (a === 2) {  
					                    	theYData.push(newArr);
					                      }
					                });
					            });
								for (var i=0,j=0; i<theYData.length; i++) {
									if(i%num_series == 0){
										if(i%num_series == 0 && i != 0){
											j++;
										}
										seriesArr[j] = new Array(theYData[i]);
									} else {
										seriesArr[j].push(theYData[i]);
									}
								};
								var newseriesArr = [];
					            var chart_series_color_array  = chart_series_color.split(",");
					            var chart_series_legend_array  = chart_series_legend.split(",");
					            var intial_visibility_array    = intial_visibility.split(",");
					            var check_box_series_array     = check_box_series.split(",");
								$.each(theYLabels, function(i, item) {
					                newseriesArr.push({name:item,data:seriesArr[i],color:chart_series_color_array[i],
					                showInLegend:chart_series_legend_array[i],visible:intial_visibility_array[i],
					                selected:check_box_series_array[i]});
					            });
							    jQuery('#container').highcharts({
						        chart: {
						            type: 'scatter',
						            zoomType: 'xy',
						            animation: {
					                        duration:chart_animation_duration,
					                        easing:chart_animation
					                 },
					                backgroundColor:chart_background_color,
					                borderColor:chart_border_color,
					                borderWidth:chart_border_width,
					                borderRadious:chart_border_radious
						        },
						        title: {
						            text: chart_title
						        },
						        subtitle: {
						            text: chart_sub_title
						        },
						        xAxis: {
						            title: {
						                enabled: true,
						                text: chart_xAxis
						            },
						            startOnTick: true,
						            endOnTick: true,
						            showLastLabel: true
						        },
						        yAxis: {
						            title: {
						                text: chart_yAxis
						            }
						        },
						        legend: {
						            layout: 'vertical',
						            align: 'left',
						            verticalAlign: 'top',
						            x: 100,
						            y: 70,
						            floating: true,
						            backgroundColor: (Highcharts.theme && Highcharts.theme.legendBackgroundColor) || '#FFFFFF',
						            borderWidth: 1
						        },
					            credits: {
					                enabled: chart_credit,
					                text:credit_text,
					                href:credit_href
					            },
					            navigation: {
					                buttonOptions: {
					                    enabled:navigation_buttons
					                }
					            },
					            tooltip: {
					                enabled:chart_tooltip,
					                crosshairs:chart_tooltip_crosshairs,
					                valueDecimals: value_decimals,
					                valuePrefix: value_prefix,
					                valueSuffix: value_suffix
					            },
						        plotOptions: {
					                 series: {
					                        allowPointSelect:chart_allow_point_select,
					                        showCheckbox:show_checkbox,
				                            cursor: chart_cursor_pointer,
				                            events: {

				                                click: function() {

				                                    alert(chart_cursor_event_text);

				                                }

				                            }
					                    },
						            scatter: {
						                marker: {
						                    radius: 5,
						                    states: {
						                        hover: {
						                            enabled: true,
						                            lineColor: 'rgb(100,100,100)'
						                        }
						                    }
						                },
						                states: {
						                    hover: {
						                        marker: {
						                            enabled: chart_allow_point_select
						                        }
						                    }
						                }
						            }
						        },
						        series: newseriesArr
						    });
						}
						if(chart_type == "spark chart"){

			
							jQuery("#container").css("display","block");
							jQuery("#thead-sparkline tr").find('td').remove();
							jQuery("#tbody-sparkline").find('tr').remove();
							jQuery("#container").css("display","none");
							jQuery("#table-sparkline").css("display","inline-table");
							jQuery("#result").css("display","block");

							jQuery("#chart_title").parent().parent().css("display","none");
							jQuery("#chart_sub_title").parent().parent().css("display","none");
							jQuery("#chart_xAxis").parent().parent().css("display","none");
							jQuery("#chart_yAxis").parent().parent().css("display","none");
							jQuery('#chart_drilldown').parent().parent().css('display','none');
							jQuery('#chart_drilldown_text').parent().parent().css('display','none');
							jQuery('#chart_start_engle').parent().parent().css('display','none');
							jQuery('#chart_end_engle').parent().parent().css('display','none');
							jQuery('#chart_inner_size').parent().parent().css('display','none');
							jQuery('#pie_sliced').parent().parent().css('display','none');
							jQuery('#pie_legend').parent().parent().css('display','none');
							jQuery('#pie_sliced_offset').parent().parent().css('display','none');
							jQuery('#bar_border_color').parent().parent().css('display','none');
							jQuery('#bar_border_width').parent().parent().css('display','none');
							jQuery('#bar_border_radious').parent().parent().css('display','none');
							jQuery('#chart-drildown-data').css('display','none');

							jQuery("#chart_title").val('Spark');
							jQuery("#chart_sub_title").val('Spark');
							jQuery("#chart_xAxis").val("Spark");
							jQuery("#chart_yAxis").val('Spark');
							jQuery("#chart_shortcode").val("Spark Chart");

							var chart_title 	= jQuery("#chart_title").val(); 
							var chart_sub_title = jQuery("#chart_sub_title").val();
							var chart_xAxis 	= jQuery("#chart_xAxis").val();
							var chart_yAxis 	= jQuery("#chart_yAxis").val();
							var chart_shortcode =  jQuery("#chart_shortcode").val();
					         var data = [
								  ["State","Income","Income per quarter","Costs","Costs Per quarter","Result","Result per quarter"],
								  ["Alabama", 254,"71, 78, 39,66",296,"68, 52, 80, 96",-42,"3, 26, -41, -30 ; column"],
								  ["Alaska", 290,"81, 88, 49, 76",396,"78, 62, 90, 106",-52,"13, 36, -51, -40 ; column"]
								];
					            jQuery('#dataTable').handsontable({
					                data: data,
					                minSpareCols: 1,
								    colHeaders: true,
								    contextMenu: true,
					                onChange: function (changes, source) {
					                     if(changes != null){
					                     	update_highchart(chart_type);
					                     }
					                }
					            });
					            var seriesArr = [];
					            var theData = $("#dataTable").data('handsontable').getData();
					            var theXCats = $.extend(true, [], theData[0]);
			                    theXCats = theXCats.splice(0,theXCats.length-1);
			                    for(i in theXCats){
			                    	jQuery("#thead-sparkline  tr") .append('<td>'+theXCats[i]+'</td>');
			                    }
			                  		        
						        var theNewData = [];
						        var buildNewData = $.map(theData, function(item, i) {
						            if (i > 0 && i < theData.length-1) {
						                theNewData.push(item);
						            }
						        });
						        var theYCats = [];
						        var buildYCats = $.map(theNewData, function(item, i) {
						            theYCats.push(item[0]);
						        });
						        var theYLabels = [],
						            theYData = [],
						            str = '';
						        var buildYData = $.map(theNewData, function(item, i) {
						            theYLabels.push(item[0]);
						            $.each(item, function(x, xitem) {
						                if ( x < theNewData[0].length-1) {
						                	if(x===0){ str= ''; str = '<th>'+xitem+'</th>'; }
						                	if(x===1){ str = str+'<td>'+parseFloat(xitem)+'</td>'; }
						                	if(x===2){ str = str+'<td data-sparkline="'+xitem+'"/>'; }
						                	if(x===3){ str = str+'<td>'+parseFloat(xitem)+'</td>'; }
						                	if(x===4){ str = str+'<td data-sparkline="'+xitem+'"/>'; }
						                	if(x===5){ str = str+'<td>'+parseFloat(xitem)+'</td>'; }
						                	if(x===6){ str = str+'<td data-sparkline="'+xitem+'"/>'; }
						               		if (x === 6) { jQuery("#tbody-sparkline").append('<tr>'+str+'</tr>'); }
						                }
						            
						            });
						        });
						        $.each(theYLabels, function(i, item) {
						            seriesArr.push({name:item,data:theYData[i]});
						        });
							Highcharts.SparkLine = function (options, callback) {
						        var defaultOptions = {
						            chart: {
						                renderTo: (options.chart && options.chart.renderTo) || this,
						                backgroundColor:chart_background_color,
						                borderColor:chart_border_color,
						                borderWidth:chart_border_width,
						                borderRadious:chart_border_radious,
						                type: 'area',
						                margin: [2, 0, 2, 0],
						                width: 120,
						                height: 20,
						                style: {
						                    overflow: 'visible'
						                },
						                skipClone: true
						            },
						            title: {
						                text: ''
						            },
						            credits: {
						                enabled: false
						            },
						            navigation: {
						                buttonOptions: {
						                    enabled:navigation_buttons
						                }
						            },
						            xAxis: {
						                labels: {
						                    enabled: false
						                },
						                title: {
						                    text: null
						                },
						                startOnTick: false,
						                endOnTick: false,
						                tickPositions: []
						            },
						            yAxis: {
						                endOnTick: false,
						                startOnTick: false,
						                labels: {
						                    enabled: false
						                },
						                title: {
						                    text: null
						                },
						                tickPositions: [0]
						            },
						            legend: {
						                enabled: false
						            },
						            tooltip: {
						                backgroundColor: null,
						                borderWidth: 0,
						                shadow: false,
						                useHTML: true,
						                hideDelay: 0,
						                shared: true,
						                padding: 0,
						                positioner: function (w, h, point) {
						                    return { x: point.plotX - w / 2, y: point.plotY - h};
						                }
						            },
						            plotOptions: {
						                series: {
							                allowPointSelect: chart_allow_point_select,
						                    animation: false,
						                    lineWidth: 1,
						                    shadow: false,
						                    states: {
						                        hover: {
						                            lineWidth: 1
						                        }
						                    },
						                    marker: {
						                       radius: 1,
						                        states: {
						                            hover: {
						                                radius: 2
						                            }
						                        }
						                    },
						                    fillOpacity: 0.25
						                },
						                column: {
						                    negativeColor: '#910000',
						                    borderColor: 'silver'
						                }
						            }
						        };
						        options = Highcharts.merge(defaultOptions, options);
						        return new Highcharts.Chart(options, callback);
						    };
						    var start = +new Date(),
						        $tds = jQuery("td[data-sparkline]"),
						        fullLen = $tds.length,
						        n = 0;

						    function doChunk() {
						        var time = +new Date(),
						            i,
						            len = $tds.length;
						        for (i = 0; i < len; i++) {
						            var $td = $($tds[i]),
						                stringdata = $td.data('sparkline'),
						                arr = stringdata.split('; '),
						                data = $.map(arr[0].split(', '), parseFloat),
						                chart = {};
						            if (arr[1]) {
						                chart.type = arr[1];
						            }
						            $td.highcharts('SparkLine', {
						                series: [{
						                    data: data,
						                    pointStart: 1
						                }],
						                tooltip: {
						                    headerFormat: '<span style="font-size: 10px">' + $td.parent().find('th').html() + ', Q{point.x}:</span><br/>',
						                    pointFormat: '<b>{point.y}.000</b> USD'
						                },
						                chart: chart
						            });
						            n++;			          
						            if (new Date() - time > 500) {
						               $tds.splice(0, i + 1);
						                setTimeout(doChunk, 0);
						                break;
						            }
						            if (n === fullLen) {
						                jQuery('#result').html('Generated ' + fullLen + ' sparklines in ' + (new Date() - start) + ' ms');
						            }
						        }
						    }
						    doChunk();
						}
						if(chart_type == "spline chart"){
							jQuery("#table-sparkline").css("display","none");
							jQuery("#result").css("display","none");
							jQuery("#container").css("display","block");
							jQuery("#chart_title").parent().parent().css("display","table-row");
							jQuery("#chart_sub_title").parent().parent().css("display","table-row");
							jQuery("#chart_xAxis").parent().parent().css("display","table-row");
							jQuery("#chart_yAxis").parent().parent().css("display","table-row");
							jQuery("#chart_shortcode").parent().parent().css("display","table-row");
							jQuery('#chart_drilldown').parent().parent().css('display','none');
							jQuery('#chart_drilldown_text').parent().parent().css('display','none');
							jQuery('#chart_start_engle').parent().parent().css('display','none');
							jQuery('#chart_end_engle').parent().parent().css('display','none');
							jQuery('#chart_inner_size').parent().parent().css('display','none');
							jQuery('#pie_sliced').parent().parent().css('display','none');
							jQuery('#pie_legend').parent().parent().css('display','none');
							jQuery('#pie_sliced_offset').parent().parent().css('display','none');
							jQuery('#bar_border_color').parent().parent().css('display','none');
							jQuery('#bar_border_width').parent().parent().css('display','none');
							jQuery('#bar_border_radious').parent().parent().css('display','none');
							jQuery('#chart-drildown-data').css('display','none');
							jQuery("#container").css("display","block");
							jQuery("#chart_title").val('Monthly Average Temperature');
							jQuery("#chart_sub_title").val('Source: WorldClimate.com');
							jQuery("#chart_xAxis").val("Months");
							jQuery("#chart_yAxis").val('Temperature');
							jQuery("#chart_shortcode").val("Spline Chart");

							var chart_title 	= jQuery("#chart_title").val(); 
							var chart_sub_title = jQuery("#chart_sub_title").val();
							var chart_xAxis 	= jQuery("#chart_xAxis").val();
							var chart_yAxis 	= jQuery("#chart_yAxis").val();
							var chart_shortcode =  jQuery("#chart_shortcode").val();
							 var data = [
								  ["","Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"],
								  ["Tokyo", 49.9, 71.5, 106.4, 129.2, 144.0, 176.0, 135.6, 148.5, 216.4, 194.1, 95.6, 54.4],
								  ["New York", 83.6, 78.8, 98.5, 93.4, 106.0, 84.5, 105.0, 104.3, 91.2, 83.5, 106.6, 92.3],
								  ["London",48.9, 38.8, 39.3, 41.4, 47.0, 48.3, 59.0, 59.6, 52.4, 65.2, 59.3, 51.2],
								  ["Berlin",42.4, 33.2, 34.5, 39.7, 52.6, 75.5, 57.4, 60.4, 47.6, 39.1, 46.8, 51.1]
								];
				            jQuery('#dataTable').handsontable({
				                data: data,
				                minSpareRows: 1,
							    minSpareCols: 1,
							    colHeaders: true,
							    contextMenu: true,
				                cells: function (row, col, prop) {
							      var cellProperties = {};
							      if ($("#dataTable").handsontable('getData')[row][prop] === '') {
							        cellProperties.readOnly = true;
							      }
							      return cellProperties;
							    },
				                onChange: function (changes, source) {
				                     if(changes != null){
				                     	update_highchart(chart_type);
				                 	  }
				                }
				            });
				            var seriesArr = [];
					        var theData = $("#dataTable").data('handsontable').getData();
					        var theXCats = $.extend(true, [], theData[0]);
					        theXCats = theXCats.splice(1,theXCats.length-2);
					        var theNewData = [];
					        var buildNewData = $.map(theData, function(item, i) {
					            if (i > 0 && i < theData.length-1) {
					                theNewData.push(item);
					            }
					        });
					        var theYCats = [];
					        var buildYCats = $.map(theNewData, function(item, i) {
					            theYCats.push(item[0]);
					        });
					        var theYLabels = [],
					            theYData = [];
					        var buildYData = $.map(theNewData, function(item, i) {
					            theYLabels.push(item[0]);
					            $.each(item, function(x, xitem) {
					                if (x === 0) newArr = [];
					                if (x > 0 && x < theNewData[0].length-1) {
					                    newArr.push(parseFloat(xitem));
					                }
					                if (x === theNewData[0].length-1) theYData.push(newArr);
					            });
					        });
				            var chart_series_color_array  = chart_series_color.split(",");
				            var chart_series_legend_array  = chart_series_legend.split(",");
				            var intial_visibility_array    = intial_visibility.split(",");
				            var check_box_series_array     = check_box_series.split(",");
					        $.each(theYLabels, function(i, item) {
					            seriesArr.push({name:item,data:theYData[i],color:chart_series_color_array[i],
				                            showInLegend:chart_series_legend_array[i],visible:intial_visibility_array[i],
				                            selected:check_box_series_array[i]});
					        });
							 jQuery('#container').highcharts({
					        chart: {
					            type: 'spline',
					            animation: {
				                        duration:chart_animation_duration,
				                        easing:chart_animation
				                 },
				                backgroundColor:chart_background_color,
				                borderColor:chart_border_color,
				                borderWidth:chart_border_width,
				                borderRadious:chart_border_radious
					        },
					        title: {
					            text: chart_title
					        },
					        subtitle: {
					            text: chart_sub_title
					        },
					        xAxis: {
					            categories: theXCats
					        },
					        yAxis: {
					            title: {
					                text: chart_yAxis
					            },
					            labels: {
					                formatter: function() {
					                    return this.value +'°'
					                }
					            }
					        },
					        tooltip: {
				                    enabled:chart_tooltip,
				                    crosshairs:chart_tooltip_crosshairs,
				                    valueDecimals: value_decimals,
				                    valuePrefix: value_prefix,
				                    valueSuffix: value_suffix,
				                },
				            credits: {
				                enabled: chart_credit,
				                text:credit_text,
				                href:credit_href
				            },
				            navigation: {
					                buttonOptions: {
					                    enabled:navigation_buttons
					                }
					            },
					        plotOptions: {
				                 series: {
				                        allowPointSelect:chart_allow_point_select,
				                        showCheckbox:show_checkbox,
			                            cursor: chart_cursor_pointer,
			                            events: {

			                                click: function() {

			                                    alert(chart_cursor_event_text);

			                                }

			                            }
				                    },
					            spline: {
					                marker: {
					                    radius: 4,
					                    lineColor: '#666666',
					                    lineWidth: 1
					                }
					            }
					        },
					        series:seriesArr 
					    });
						}
						if(chart_type == "waterfall chart"){
							jQuery("#table-sparkline").css("display","none");
							jQuery("#result").css("display","none");
							jQuery("#container").css("display","block");
							jQuery("#container").css("display","block");
							jQuery("#chart_title").parent().parent().css("display","table-row");
							jQuery("#chart_xAxis").parent().parent().css("display","table-row");
							jQuery("#chart_shortcode").parent().parent().css("display","table-row");
							jQuery("#chart_yAxis").parent().parent().css("display","table-row");
							jQuery("#chart_sub_title").parent().parent().css("display","none");
							jQuery('#chart_drilldown').parent().parent().css('display','none');
							jQuery('#chart_drilldown_text').parent().parent().css('display','none');
							jQuery('#chart_start_engle').parent().parent().css('display','none');
							jQuery('#chart_end_engle').parent().parent().css('display','none');
							jQuery('#chart_inner_size').parent().parent().css('display','none');
							jQuery('#pie_sliced').parent().parent().css('display','none');
							jQuery('#pie_legend').parent().parent().css('display','none');
							jQuery('#pie_sliced_offset').parent().parent().css('display','none');
							jQuery('#bar_border_color').parent().parent().css('display','none');
							jQuery('#bar_border_width').parent().parent().css('display','none');
							jQuery('#bar_border_radious').parent().parent().css('display','none');
							jQuery('#chart-drildown-data').css('display','none');

							jQuery("#chart_title").val('Highcharts Waterfall');
							jQuery("#chart_sub_title").val('Waterfall');
							jQuery("#chart_xAxis").val("USD");
							jQuery("#chart_yAxis").val('Waterfall');
							jQuery("#chart_shortcode").val("Waterfall Chart");

							var chart_title 	= jQuery("#chart_title").val(); 
							var chart_sub_title = jQuery("#chart_sub_title").val();
							var chart_xAxis 	= jQuery("#chart_xAxis").val();
							var chart_yAxis 	= jQuery("#chart_yAxis").val();
							var chart_shortcode =  jQuery("#chart_shortcode").val();
							 var data = [
								  ["","Usage"],
								  ["IE",45000.0],
								  ["Chrome",12000.8],
								  ["Opera",60000.2],
								  ["Safari",800000.5],
								  ["Others",7000000]
								];

					            jQuery('#dataTable').handsontable({
					                data: data,
					                minSpareRows: 1,
								    colHeaders: true,
								    contextMenu: true,
					                cells: function (row, col, prop) {
								      var cellProperties = {};
								      if ($("#dataTable").handsontable('getData')[row][prop] === '') {
								        cellProperties.readOnly = true;
								      }
								      return cellProperties;
								    },
					                onChange: function (changes, source) {
					                     if(changes != null){
					                     	update_highchart(chart_type);
					                 	  }
					                }

					            });
					            var seriesArr = [];
					            var theData = $("#dataTable").data('handsontable').getData();
					            var theXCats = $.extend(true, [], theData[0]);
					            theXCats = theXCats.splice(1,theXCats.length-2);
					            var theNewData = [];
					            var buildNewData = $.map(theData, function(item, i) {
					                if (i > 0 && i < theData.length-1) {
					                    theNewData.push(item);
					                }
					            });
					            var theYCats = [];
					            var buildYCats = $.map(theNewData, function(item, i) {
					                theYCats.push(item[0]);
					            });

					            var theYLabels = [],
					                theYData = [];
					            var buildYData = $.map(theNewData, function(item, i) {
					                theYLabels.push(item[0]);
					                $.each(item, function(x, xitem) {
					                    if (x === 0) newArr = [];
					                    if (x > 0 && x < theNewData[0].length-1) {
					                        newArr.push(parseFloat(xitem));
					                    }
					                    if (x === theNewData[0].length-1) theYData.push(newArr);
					                });
					            });
					             var chart_series_color_array  = chart_series_color.split(",");
					            var chart_series_legend_array  = chart_series_legend.split(",");
					            var intial_visibility_array    = intial_visibility.split(",");
					            var check_box_series_array     = check_box_series.split(",");
					            $.each(theYLabels, function(i, item) {
					                seriesArr.push({name:item,y:theYData[i][0],color:chart_series_color_array[i],
					                            showInLegend:chart_series_legend_array[i],visible:intial_visibility_array[i],
					                            selected:check_box_series_array[i]});
					            });
							  jQuery('#container').highcharts({
						        chart: {
						            type: 'waterfall',
						            animation: {
					                        duration:chart_animation_duration,
					                        easing:chart_animation
					                 },
					                backgroundColor:chart_background_color,
					                borderColor:chart_border_color,
					                borderWidth:chart_border_width,
					                borderRadious:chart_border_radious
						        },
						        title: {
						            text: chart_title
						        },
						        xAxis: {
						            type: 'category'
						        },
						        yAxis: {
						            title: {
						                text: chart_yAxis
						            }
						        },
						        legend: {
						            enabled: false
						        },
						        tooltip: {
					                enabled:chart_tooltip,
					                crosshairs:chart_tooltip_crosshairs,
					                valueDecimals: value_decimals,
					                valuePrefix: value_prefix,
					                valueSuffix: value_suffix,
					            },
					            credits: {
					                enabled: chart_credit,
					                text:credit_text,
					                href:credit_href
					            },
					            navigation: {
					                buttonOptions: {
					                    enabled:navigation_buttons
					                }
					            },
					            plotOptions:{
					                 series: {
					                        allowPointSelect:chart_allow_point_select,
					                        showCheckbox:show_checkbox,
				                            cursor: chart_cursor_pointer,
				                            events: {

				                                click: function() {

				                                    alert(chart_cursor_event_text);

				                                }

				                            }
					                    }
					            },
						        series: [{
						            upColor: Highcharts.getOptions().colors[2],
						            color: Highcharts.getOptions().colors[3],
						            data: seriesArr,
						            dataLabels: {
						                enabled: chart_data_labels,
						                formatter: function () {
						                    return Highcharts.numberFormat(this.y / 1000, 0, ',') + 'k';
						                },
						                style: {
						                    color: '#FFFFFF',
						                    fontWeight: 'bold',
						                    textShadow: '0px 0px 3px black'
						                }
						            },
						            pointPadding: column_point_padding
						        }]
						    });
						}
				});
				jQuery("#save_chart, #update_chart").click(function(e) {
					var chart_type 						= jQuery("#chart_type option:selected").val();
					var chart_shortcode  				= jQuery("#chart_shortcode").val();
					var chart_background_color 			= jQuery("#chart_background_color").val();
					var chart_border_color 				= jQuery("#chart_border_color").val();
					var chart_border_width 				= jQuery("#chart_border_width").val();
					var chart_border_radious 			= jQuery("#chart_border_radious").val();
					var chart_allow_point_select 		= jQuery("#chart_allow_point_select option:selected").val();
					var chart_animation 				= jQuery("#chart_animation").val();
					var chart_animation_duration 		= jQuery("#chart_animation_duration").val();
					var chart_point_brightness 			= jQuery("#chart_point_brightness").val();
					var bar_border_color 				= jQuery("#bar_border_color").val();
					var bar_border_width 				= jQuery("#bar_border_width").val();
					var bar_border_radious 				= jQuery("#bar_border_radious").val();
					var chart_series_generl_color 		= jQuery("#chart_series_generl_color").val();
					var chart_series_color 	        	= jQuery("#chart_series_color").val();
					var chart_cursor_pointer 	        = jQuery("#chart_cursor_pointer").val();
					var chart_cursor_event_text 	    = jQuery("#chart_cursor_event_text").val();
					var chart_series_negative_color 	= jQuery("#chart_series_negative_color").val();
					var column_point_padding 			= jQuery("#column_point_padding").val();
					var chart_series_legend 			= jQuery("#chart_series_legend").val();
					var chart_series_stacking 			= jQuery("#chart_series_stacking").val();
					var intial_visibility 				= jQuery("#intial_visibility").val();
					var chart_start_engle 				= jQuery("#chart_start_engle").val();
					var chart_end_engle 				= jQuery("#chart_end_engle").val();
					var chart_inner_size 				= jQuery("#chart_inner_size").val();
					var show_checkbox 					= jQuery("#show_checkbox").val();
					var check_box_series 				= jQuery("#check_box_series").val();
					var pie_sliced 						= jQuery("#pie_sliced").val();
					var pie_legend 						= jQuery("#pie_legend").val();
					var pie_sliced_offset 				= jQuery("#pie_sliced_offset").val();
					var columns_grouping 				= jQuery("#columns_grouping").val();
					var chart_data_labels 				= jQuery("#chart_data_labels").val();
					var chart_tooltip 					= jQuery("#chart_tooltip").val();
					var chart_tooltip_crosshairs 		= jQuery("#chart_tooltip_crosshairs").val();
					var chart_credit 					= jQuery("#chart_credit").val();
					var credit_text 					= jQuery("#credit_text").val();
					var credit_href 					= jQuery("#credit_href").val();
					var navigation_buttons 				= jQuery("#navigation_buttons").val();
					var value_decimals 					= jQuery("#value_decimals").val();
					var value_prefix 					= jQuery("#value_prefix").val();
					var value_suffix 					= jQuery("#value_suffix").val();
					var chart_drilldown 				= jQuery("#chart_drilldown").val();
					var chart_drilldown_text 			= jQuery("#chart_drilldown_text").val();
					if(chart_type == "area chart"){
								var seriesArr       = [];
						        var theData         = $("#dataTable").data('handsontable').getData();
						        var theXCats        = $.extend(true, [], theData[0]);
						 		theXCats            = theXCats.splice(1,theXCats.length-2);
						        var theNewData      = [];
						        var buildNewData    = $.map(theData, function(item, i) {
						            if (i > 0 && i < theData.length-1) {
						                theNewData.push(item);
						            }
						        });
						        var theYCats    = [];
						        var buildYCats  = $.map(theNewData, function(item, i) {
						            theYCats.push(item[0]);
						        });
						        var theYLabels  = [],
						            theYData    = [];
						        var buildYData  = $.map(theNewData, function(item, i) {
						            theYLabels.push(item[0]);
						            $.each(item, function(x, xitem) {
						                if (x === 0) newArr = [];
						                if (x > 0 && x < theNewData[0].length-1) {
						                    newArr.push(parseFloat(xitem));
						                }
						             if (x === theNewData[0].length-1) theYData.push(newArr);
						            });
						        });
						        var chart_series_color_array   = chart_series_color.split(",");
						        var chart_series_legend_array  = chart_series_legend.split(",");
						        var intial_visibility_array    = intial_visibility.split(",");
						        var check_box_series_array     = check_box_series.split(",");
						        $.each(theYLabels, function(i, item) {
						            seriesArr.push({name:item,data:theYData[i],color:chart_series_color_array[i],
						                showInLegend:chart_series_legend_array[i],visible:intial_visibility_array[i],
						                selected:check_box_series_array[i]
						            });
						        });

					}
					if(chart_type == "bar chart"){
						if(chart_drilldown == 'false'){
							var seriesArr = [];
					        var theData = $("#dataTable").data('handsontable').getData();
					        var theXCats = $.extend(true, [], theData[0]);
					        theXCats = theXCats.splice(1,theXCats.length-2);
					        var theNewData = [];
					        var buildNewData = $.map(theData, function(item, i) {
					            if (i > 0 && i < theData.length-1) {
					                theNewData.push(item);
					            }
					        });
					        var theYCats = [];
					        var buildYCats = $.map(theNewData, function(item, i) {
					            theYCats.push(item[0]);
					        });
					        var theYLabels = [],
					           theYData = [];
					        var buildYData = $.map(theNewData, function(item, i) {
					            theYLabels.push(item[0]);
					            $.each(item, function(x, xitem) {
					                if (x === 0) newArr = [];
					                if (x > 0 && x < theNewData[0].length-1) {
					                    newArr.push(parseFloat(xitem));
					                }
					                if (x === theNewData[0].length-1) theYData.push(newArr);
					            });
					        });
					        var chart_series_color_array  = chart_series_color.split(",");
					        var chart_series_legend_array  = chart_series_legend.split(",");
					        var intial_visibility_array    = intial_visibility.split(",");
					        var check_box_series_array     = check_box_series.split(",");
					        $.each(theYLabels, function(i, item) {
					            seriesArr.push({name:item,data:theYData[i],color:chart_series_color_array[i],
					                showInLegend:chart_series_legend_array[i],visible:intial_visibility_array[i],
					                selected:check_box_series_array[i]
					            });
					        });
						}else {
								var seriesArr = [];
						        var theData = $("#dataTable").data('handsontable').getData();
						        var theXCats = $.extend(true, [], theData[0]);
						        theXCats = theXCats.splice(1,theXCats.length-2);
						        var theNewData = [];
						        var buildNewData = $.map(theData, function(item, i) {
						            if (i > 0 && i < theData.length-1) {
						                theNewData.push(item);
						            }
						        });
						        var theYCats = [];
						        var buildYCats = $.map(theNewData, function(item, i) {
						            theYCats.push(item[0]);
						        });
						        var theYLabels = [],
						            theYData = [];
						        var buildYData = $.map(theNewData, function(item, i) {
						            theYLabels.push(item[0]);
						            $.each(item, function(x, xitem) {
						                if (x === 0) newArr = [];
						                if (x > 0 && x < theNewData[0].length-1) {
						                    newArr.push(parseFloat(xitem));
						                }
						                if (x === theNewData[0].length-1) theYData.push(newArr);
						            });
						        });
						        var chart_series_color_array  = chart_series_color.split(",");
						        var chart_series_legend_array  = chart_series_legend.split(",");
						        var intial_visibility_array    = intial_visibility.split(",");
						        var check_box_series_array     = check_box_series.split(",");
						        $.each(theYLabels, function(i, item) {
						            seriesArr.push({name:item,y:theYData[i][0],drilldown:item,color:chart_series_color_array[i]});
						        });
						        var drilldownSeries = [];
						        var thedData = $("#drilldown-dataTable").data('handsontable').getData();
						        var theXCats = $.extend(true, [], thedData[0]);
						        var theNewData = [];
						        var buildNewData = $.map(thedData, function(item, i) {
						            if (i > 0 && i < thedData.length-1) {
						                theNewData.push(item);
						            }
						        });
						        var theYCats = [];
						        var buildYCats = $.map(theNewData, function(item, i) {
						            theYCats.push(item[0]);
						        });
						        var theYLabels = [],
						            theYData = [];
						        var buildYData = $.map(theNewData, function(item, i) {
						            theYLabels.push(item[0]);
						            $.each(item, function(x, xitem) {
						                if (x === 0) newArr = [];
						                if (x > 0 && x < theNewData[0].length-1) {
						                    newArr.push([theXCats[x],parseFloat(xitem)]);
						                }
						                if (x === theNewData[0].length-1) theYData.push(newArr);
						            });
						        });

						        $.each(theYLabels, function(i, item) {
						            drilldownSeries.push({name:item,id:item,data:theYData[i]});
						        });

						}
							
					}
					if(chart_type == "bubble chart"){
							var seriesArr = [];
							var newseriesArr = [];
				            var theData = $("#dataTable").data('handsontable').getData();
				            var theXCats = $.extend(true, [], theData[0]);
		                    theXCats = theXCats.splice(1,theXCats.length-2);
				            var theNewData = [];
		                    var buildNewData = $.map(theData, function(item, i) {
		                        if (i > 0 && i < theData.length-1) {
		                            theNewData.push(item);
		                        }
		                    });
		                    var theYCats = [];
		                    var buildYCats = $.map(theNewData, function(item, i) {
		                        theYCats.push(item[0]);
		                    });
							var theYLabels = [],
		                        theYData = [],
		                        temp = [];
		                    var num_series = (((theNewData[0].length)-2)/3);
		                    var buildYData = $.map(theNewData, function(item, i) {
		                        theYLabels.push(item[0]);
		                        var a = 0;      
		                        $.each(item, function(x, xitem) {
		                            if (x === 0 || a === 3) {
		                            	newArr = [];
		                            	a = 0;
		                            }
		                            if (x > 0 && x < theNewData[0].length-1) {
		                                newArr.push(xitem);
		                            }
		                            if(x != 0){ a++; }
		                            if (a === 3) {  
		                            	theYData.push(newArr);
		                              }  
		                        });
		                    });
							for (var i=0,j=0; i<theYData.length; i++) {
								if(i%num_series == 0){
									if(i%num_series == 0 && i != 0){
										j++;
									}
									newseriesArr[j] = new Array(theYData[i]);
								} else {
									newseriesArr[j].push(theYData[i]);
								}
							};
							 var chart_series_color_array  = chart_series_color.split(",");
				            var chart_series_legend_array  = chart_series_legend.split(",");
				            var intial_visibility_array    = intial_visibility.split(",");
				            var check_box_series_array     = check_box_series.split(",");
							$.each(theYLabels, function(i, item) {
		                        seriesArr.push({data:newseriesArr[i],color:chart_series_color_array[i],
				                showInLegend:chart_series_legend_array[i],visible:intial_visibility_array[i],
				                selected:check_box_series_array[i]});
		                    });
					}
					if(chart_type == "column chart"){
						if(chart_drilldown == 'false'){
							var seriesArr = [];
					        var theData = $("#dataTable").data('handsontable').getData();
					        var theXCats = $.extend(true, [], theData[0]);
					        theXCats = theXCats.splice(1,theXCats.length-2);
					        var theNewData = [];
					        var buildNewData = $.map(theData, function(item, i) {
					            if (i > 0 && i < theData.length-1) {
					                theNewData.push(item);
					            }
					        });
					        var theYCats = [];
					        var buildYCats = $.map(theNewData, function(item, i) {
					            theYCats.push(item[0]);
					        });
					        var theYLabels = [],
					            theYData = [];
					        var buildYData = $.map(theNewData, function(item, i) {
					            theYLabels.push(item[0]);
					            $.each(item, function(x, xitem) {
					                if (x === 0) newArr = [];
					                if (x > 0 && x < theNewData[0].length-1) {
					                    newArr.push(parseFloat(xitem));
					                }
					                if (x === theNewData[0].length-1) theYData.push(newArr);
					            });
					        });
					        var chart_series_color_array  = chart_series_color.split(",");
					        var chart_series_legend_array  = chart_series_legend.split(",");
					        var intial_visibility_array    = intial_visibility.split(",");
					        var check_box_series_array     = check_box_series.split(",");
					        $.each(theYLabels, function(i, item) {
					            seriesArr.push({name:item,data:theYData[i],color:chart_series_color_array[i],
					                showInLegend:chart_series_legend_array[i],visible:intial_visibility_array[i],
					                selected:check_box_series_array[i]});
					        });
						} else {
								var seriesArr = [];
						        var theData = $("#dataTable").data('handsontable').getData();
						        var theXCats = $.extend(true, [], theData[0]);
						        theXCats = theXCats.splice(1,theXCats.length-2);
						        var theNewData = [];
						        var buildNewData = $.map(theData, function(item, i) {
						            if (i > 0 && i < theData.length-1) {
						                theNewData.push(item);
						            }
						        });
						        var theYCats = [];
						        var buildYCats = $.map(theNewData, function(item, i) {
						            theYCats.push(item[0]);
						        });
						        var theYLabels = [],
						            theYData = [];
						        var buildYData = $.map(theNewData, function(item, i) {
						            theYLabels.push(item[0]);
						            $.each(item, function(x, xitem) {
						                if (x === 0) newArr = [];
						                if (x > 0 && x < theNewData[0].length-1) {
						                    newArr.push(parseFloat(xitem));
						                }
						                if (x === theNewData[0].length-1) theYData.push(newArr);
						            });
						        });
						        var chart_series_color_array  = chart_series_color.split(",");
						        var chart_series_legend_array  = chart_series_legend.split(",");
						        var intial_visibility_array    = intial_visibility.split(",");
						        var check_box_series_array     = check_box_series.split(",");
						        $.each(theYLabels, function(i, item) {
						            seriesArr.push({name:item,y:theYData[i][0],drilldown:item,color:chart_series_color_array[i]});
						        });
						        var drilldownSeries = [];
						        var thedData = $("#drilldown-dataTable").data('handsontable').getData();
						        var theXCats = $.extend(true, [], thedData[0]);
						        var theNewData = [];
						        var buildNewData = $.map(thedData, function(item, i) {
						            if (i > 0 && i < thedData.length-1) {
						                theNewData.push(item);
						            }
						        });
						        var theYCats = [];
						        var buildYCats = $.map(theNewData, function(item, i) {
						            theYCats.push(item[0]);
						        });
						        var theYLabels = [],
						            theYData = [];
						        var buildYData = $.map(theNewData, function(item, i) {
						            theYLabels.push(item[0]);
						            $.each(item, function(x, xitem) {
						                if (x === 0) newArr = [];
						                if (x > 0 && x < theNewData[0].length-1) {
						                    newArr.push([theXCats[x],parseFloat(xitem)]);
						                }
						                if (x === theNewData[0].length-1) theYData.push(newArr);
						            });
						        });

						        $.each(theYLabels, function(i, item) {
						            drilldownSeries.push({name:item,id:item,data:theYData[i]});
						        });
						}
							
					}
					if(chart_type == "line chart"){
						var seriesArr = [];
				        var theData = $("#dataTable").data('handsontable').getData();
				        var theXCats = $.extend(true, [], theData[0]);
				        theXCats = theXCats.splice(1,theXCats.length-2);
				        var theNewData = [];
				        var buildNewData = $.map(theData, function(item, i) {
				            if (i > 0 && i < theData.length-1) {
				                theNewData.push(item);
				            }
				        });
				        var theYCats = [];
				        var buildYCats = $.map(theNewData, function(item, i) {
				            theYCats.push(item[0]);
				        });
				        var theYLabels = [],
				            theYData = [];
				        var buildYData = $.map(theNewData, function(item, i) {
				            theYLabels.push(item[0]);
				            $.each(item, function(x, xitem) {
				                if (x === 0) newArr = [];
				                if (x > 0 && x < theNewData[0].length-1) {
				                    newArr.push(parseFloat(xitem));
				                }
				                if (x === theNewData[0].length-1) theYData.push(newArr);
				            });
				        });
				        var chart_series_color_array  = chart_series_color.split(",");
				        var chart_series_legend_array  = chart_series_legend.split(",");
				        var intial_visibility_array    = intial_visibility.split(",");
				        var check_box_series_array     = check_box_series.split(",");
				        $.each(theYLabels, function(i, item) {
				            seriesArr.push({name:item,data:theYData[i],color:chart_series_color_array[i],
				                showInLegend:chart_series_legend_array[i],visible:intial_visibility_array[i],
				                selected:check_box_series_array[i]});
				        });
					}
					if(chart_type == "pie chart"){
						if(chart_drilldown == 'false' ){
							var seriesArr = [];
				            var theData = $("#dataTable").data('handsontable').getData();
				            var theXCats = $.extend(true, [], theData[0]);
				            theXCats = theXCats.splice(1,theXCats.length-2);
				            var theNewData = [];
				            var buildNewData = $.map(theData, function(item, i) {
				                if (i > 0 && i < theData.length-1) {
				                    theNewData.push(item);
				                }
				            });
				            var theYCats = [];
				            var buildYCats = $.map(theNewData, function(item, i) {
				                theYCats.push(item[0]);
				            });
				            var theYLabels = [],
				                theYData = [];
				            var buildYData = $.map(theNewData, function(item, i) {
				                theYLabels.push(item[0]);
				                $.each(item, function(x, xitem) {
				                    if (x === 0) newArr = [];
				                    if (x > 0 && x < theNewData[0].length-1) {
				                        newArr.push(parseFloat(xitem));
				                    }
				                    if (x === theNewData[0].length-1) theYData.push(newArr);
				                });
				            });
				            var chart_series_color_array  = chart_series_color.split(",");
					        var chart_series_legend_array  = chart_series_legend.split(",");
					        var intial_visibility_array    = intial_visibility.split(",");
					        var check_box_series_array     = check_box_series.split(",");
				            if(pie_sliced =="true"){ var sliced = true; } else { var sliced = false; }
				            seriesArr.push({name:theYLabels[0],y:theYData[0][0],selected:true});
				            $.each(theYLabels, function(i, item) {
				            	if(i > 0){
				            		seriesArr.push([item,theYData[i][0]]);
				            	}
				                
				            });
						}else {
							var seriesArr = [];
				            var theData = $("#dataTable").data('handsontable').getData();
				            var theXCats = $.extend(true, [], theData[0]);
				            theXCats = theXCats.splice(1,theXCats.length-2);
				            var theNewData = [];
				            var buildNewData = $.map(theData, function(item, i) {
				                if (i > 0 && i < theData.length-1) {
				                    theNewData.push(item);
				                }
				            });
				            var theYCats = [];
				            var buildYCats = $.map(theNewData, function(item, i) {
				                theYCats.push(item[0]);
				            });
				            var theYLabels = [],
				                theYData = [];
				            var buildYData = $.map(theNewData, function(item, i) {
				                theYLabels.push(item[0]);
				                $.each(item, function(x, xitem) {
				                    if (x === 0) newArr = [];
				                    if (x > 0 && x < theNewData[0].length-1) {
				                        newArr.push(parseFloat(xitem));
				                    }
				                    if (x === theNewData[0].length-1) theYData.push(newArr);
				                });
				            });
				             if(pie_sliced =="true"){ var sliced = true; } else { var sliced = false; }
				             var chart_series_color_array  = chart_series_color.split(",");
					        var chart_series_legend_array  = chart_series_legend.split(",");
					        var intial_visibility_array    = intial_visibility.split(",");
					        var check_box_series_array     = check_box_series.split(",");
			                seriesArr.push({name:theYLabels[0],y:theYData[0][0],drilldown:theYLabels[0],selected:true,sliced:sliced,color:chart_series_color_array[0]});
			                $.each(theYLabels, function(i, item) {
			                    if(i > 0){
			                         seriesArr.push({name:item,y:theYData[i][0],drilldown:item,color:chart_series_color_array[i]});
			                    }
			                   
			                });

			            	var drilldownSeries = [];
				            var thedData = $("#drilldown-dataTable").data('handsontable').getData();
				            var theXCats = $.extend(true, [], thedData[0]);
				            theXCats = theXCats.splice(1,theXCats.length-2);
				            var theNewData = [];
				            var buildNewData = $.map(thedData, function(item, i) {
				                if (i > 0 && i < theData.length-1) {
				                    theNewData.push(item);
				                }
				            });
				            var theYCats = [];
				            var buildYCats = $.map(theNewData, function(item, i) {
				                theYCats.push(item[0]);
				            });
				            var theYLabels = [],
				                theYData = [],
				                temp = [],
				                newnewArray = [];
				            var num_series = (((theNewData[0].length)-2)/2);
				            var buildYData = $.map(theNewData, function(item, i) {
				                theYLabels.push(item[0]);
				                var a = 0;
				                $.each(item, function(x, xitem) {
				                    if (x === 0 || a === 2) {
				                    	newArr = [];
				                    	a = 0;	
				                    }
				                    if (x > 0 && x < theNewData[0].length-1) {
				                        newArr.push(xitem);
				                    }
				                    if(x != 0){ a++; }
				                    if (a === 2) {  
				                    	theYData.push(newArr);
				                    }
				                   if(x == theNewData[0].length-2){
				                   		newnewArray.push(theYData);
				                   		theYData = [];
				                   }
				                });
				            });
				             $.each(theYLabels, function(i, item) {
				                drilldownSeries.push({id:item,data:newnewArray[i]});
				            });
						}
				 		
					}
					if(chart_type == "scatter chart"){
						var seriesArr = [];
						var newseriesArr = [];
			            var theData = $("#dataTable").data('handsontable').getData();
			            var theXCats = $.extend(true, [], theData[0]);
			            theXCats = theXCats.splice(1,theXCats.length-2);
			            var theNewData = [];
			            var buildNewData = $.map(theData, function(item, i) {
			                if (i > 0 && i < theData.length-1) {
			                    theNewData.push(item);
			                }
			            });
			            var theYCats = [];
			            var buildYCats = $.map(theNewData, function(item, i) {
			                theYCats.push(item[0]);
			            });
			            var theYLabels = [],
			                theYData = [],
			                temp = [];
			            var num_series = (((theNewData[0].length)-2)/2);
			            var buildYData = $.map(theNewData, function(item, i) {
			                theYLabels.push(item[0]);
			                var a = 0;
			                $.each(item, function(x, xitem) {
			                    if (x === 0 || a === 2) {
			                    	newArr = [];
			                    	a = 0;	
			                    }
			                    if (x > 0 && x < theNewData[0].length-1) {
			                        newArr.push(parseFloat(xitem));
			                    }
			                    if(x != 0){ a++; }
			                    if (a === 2) {  
			                    	theYData.push(newArr);
			                      }
			                });
			            });
						for (var i=0,j=0; i<theYData.length; i++) {
							if(i%5 == 0){
								if(i%num_series == 0 && i != 0){
									j++;
								}
								newseriesArr[j] = new Array(theYData[i]);
							} else {
								newseriesArr[j].push(theYData[i]);
							}
						};
						
			            var chart_series_color_array  = chart_series_color.split(",");
			            var chart_series_legend_array  = chart_series_legend.split(",");
			            var intial_visibility_array    = intial_visibility.split(",");
			            var check_box_series_array     = check_box_series.split(",");
						$.each(theYLabels, function(i, item) {
			                seriesArr.push({name:item,data:newseriesArr[i],color:chart_series_color_array[i],
			                showInLegend:chart_series_legend_array[i],visible:intial_visibility_array[i],
			                selected:check_box_series_array[i]});
			            });
					}
					if(chart_type == 'spark chart'){
							var seriesArr = [];
				            var theData = $("#dataTable").data('handsontable').getData();
				            var theXCats = $.extend(true, [], theData[0]);
		                    theXCats = theXCats.splice(0,theXCats.length-1);	        
					        var theNewData = [];
					        var buildNewData = $.map(theData, function(item, i) {
					            if (i > 0 && i < theData.length-1) {
					                theNewData.push(item);
					            }
					        });
					        var theYCats = [];
					        var buildYCats = $.map(theNewData, function(item, i) {
					            theYCats.push(item[0]);
					        });
					        var theYLabels = [],
					            theYData = [],
					            str = '';
							seriesArr.push("spark chart");
					}
					if(chart_type == "spline chart"){
						 var seriesArr = [];
				        var theData = $("#dataTable").data('handsontable').getData();
				        var theXCats = $.extend(true, [], theData[0]);
				        theXCats = theXCats.splice(1,theXCats.length-2);
				        var theNewData = [];
				        var buildNewData = $.map(theData, function(item, i) {
				            if (i > 0 && i < theData.length-1) {
				                theNewData.push(item);
				            }
				        });
				        var theYCats = [];
				        var buildYCats = $.map(theNewData, function(item, i) {
				            theYCats.push(item[0]);
				        });
				        var theYLabels = [],
				            theYData = [];
				        var buildYData = $.map(theNewData, function(item, i) {
				            theYLabels.push(item[0]);
				            $.each(item, function(x, xitem) {
				                if (x === 0) newArr = [];
				                if (x > 0 && x < theNewData[0].length-1) {
				                    newArr.push(parseFloat(xitem));
				                }
				                if (x === theNewData[0].length-1) theYData.push(newArr);
				            });
				        });
			            var chart_series_color_array  = chart_series_color.split(",");
			            var chart_series_legend_array  = chart_series_legend.split(",");
			            var intial_visibility_array    = intial_visibility.split(",");
			            var check_box_series_array     = check_box_series.split(",");
				        $.each(theYLabels, function(i, item) {
				            seriesArr.push({name:item,data:theYData[i],color:chart_series_color_array[i],
			                            showInLegend:chart_series_legend_array[i],visible:intial_visibility_array[i],
			                            selected:check_box_series_array[i]});
				        });
					}
					if(chart_type == "waterfall chart"){
						var seriesArr = [];
			            var theData = $("#dataTable").data('handsontable').getData();
			            var theXCats = $.extend(true, [], theData[0]);
			            theXCats = theXCats.splice(1,theXCats.length-2);
			            var theNewData = [];
			            var buildNewData = $.map(theData, function(item, i) {
			                if (i > 0 && i < theData.length-1) {
			                    theNewData.push(item);
			                }
			            });
			            var theYCats = [];
			            var buildYCats = $.map(theNewData, function(item, i) {
			                theYCats.push(item[0]);
			            });

			            var theYLabels = [],
			                theYData = [];
			            var buildYData = $.map(theNewData, function(item, i) {
			                theYLabels.push(item[0]);
			                $.each(item, function(x, xitem) {
			                    if (x === 0) newArr = [];
			                    if (x > 0 && x < theNewData[0].length-1) {
			                        newArr.push(parseFloat(xitem));
			                    }
			                    if (x === theNewData[0].length-1) theYData.push(newArr);
			                });
			            });
			            var chart_series_color_array  = chart_series_color.split(",");
			            var chart_series_legend_array  = chart_series_legend.split(",");
			            var intial_visibility_array    = intial_visibility.split(",");
			            var check_box_series_array     = check_box_series.split(",");
			            $.each(theYLabels, function(i, item) {
			                seriesArr.push({name:item,y:theYData[i][0],color:chart_series_color_array[i],
			                            showInLegend:chart_series_legend_array[i],visible:intial_visibility_array[i],
			                            selected:check_box_series_array[i]});
			            });
					}
					var chart_title 			= jQuery("#chart_title").val();
					var chart_sub_title 		= jQuery("#chart_sub_title").val();
					var chart_xAxis 			= jQuery("#chart_xAxis").val();
					var chart_yAxis 			= jQuery("#chart_yAxis").val();
					var chart_id 	    		= jQuery("#chart_id").val();
					var chart_series 			= seriesArr;
					var chart_drilldown_series  = drilldownSeries;

					jQuery.ajax({
						type: 'POST',
						url: ajaxurl,
						data: {
							action						: 		'ajax_create_new_chart',
							ajax						: 		'true',
							type 						: 		"POST",
							chart_type  				: 		chart_type,
							chart_title 				: 		chart_title,
							chart_sub_title 			: 		chart_sub_title,
							chart_xAxis 				: 		chart_xAxis,
							chart_yAxis 				: 		chart_yAxis,
							chart_shortcode 			: 		chart_shortcode,
							chart_series    			: 		chart_series,
							chart_drilldown_series      : 		chart_drilldown_series,
							chart_data 					: 		theData,
							chart_xcats     			: 		theXCats,
							chart_ylabels   			: 		theYLabels,
							chart_background_color 		: 		chart_background_color,
							chart_border_color     		: 		chart_border_color,
							chart_border_width     		: 		chart_border_width,
							chart_border_radious   		: 		chart_border_radious,
							chart_allow_point_select    : 		chart_allow_point_select,
							chart_animation 		    : 		chart_animation,
							chart_animation_duration    : 		chart_animation_duration,
							chart_point_brightness      : 		chart_point_brightness,
							bar_border_color 		    : 		bar_border_color,
							bar_border_width 		    : 		bar_border_width,
							bar_border_radious 		    : 		bar_border_radious,
							chart_series_generl_color   : 		chart_series_generl_color,
							chart_series_color 	        :   	chart_series_color,
							chart_cursor_pointer 	    :       chart_cursor_pointer,
							chart_cursor_event_text     :     	chart_cursor_event_text,
							chart_series_negative_color : 		chart_series_negative_color,
							column_point_padding 		:       column_point_padding,
							chart_series_legend 		: 		chart_series_legend,
							chart_series_stacking 		: 		chart_series_stacking,
							intial_visibility 			: 		intial_visibility,
							chart_start_engle 			: 		chart_start_engle,
							chart_end_engle 			: 		chart_end_engle,
							chart_inner_size 			: 		chart_inner_size,
							show_checkbox 				: 		show_checkbox,
							check_box_series 			: 		check_box_series,
							pie_sliced 					: 		pie_sliced,
							pie_legend 					: 		pie_legend,
							pie_sliced_offset 			: 		pie_sliced_offset,
							columns_grouping 			: 		columns_grouping,
							chart_data_labels 			: 		chart_data_labels,
							chart_tooltip 				: 		chart_tooltip,
							chart_tooltip_crosshairs 	: 		chart_tooltip_crosshairs,
							chart_credit 				: 		chart_credit,
							credit_text 				: 		credit_text,
							credit_href 				: 		credit_href,
							navigation_buttons 			: 		navigation_buttons,
							value_decimals 				: 		value_decimals,
							value_prefix 				: 		value_prefix,
							value_suffix 				: 		value_suffix,
							chart_drilldown 			: 		chart_drilldown,
							chart_drilldown_text 		: 		chart_drilldown_text,
							chart_drilldown_data 		: 		thedData,
							chart_id        			: 		chart_id

						},
						success: function(data, textStatus, XMLHttpRequest){
							alert(data);	
						},
						error: function(MLHttpRequest, textStatus, errorThrown){
							alert(1);
						}
					});
				});
				jQuery("#chart_title").keyup(function() {
					if(jQuery("#chart_type option:selected").val() != 'select'){
						update_highchart(jQuery("#chart_type option:selected").val());
					}
					
				});
				jQuery("#chart_sub_title").keyup(function() {
					if(jQuery("#chart_type option:selected").val() != 'select'){
						update_highchart(jQuery("#chart_type option:selected").val());
					}
				});
				jQuery("#chart_xAxis").keyup(function() {
					if(jQuery("#chart_type option:selected").val() != 'select'){
						update_highchart(jQuery("#chart_type option:selected").val());
					}
				});
				jQuery("#chart_yAxis").keyup(function() {
					if(jQuery("#chart_type option:selected").val() != 'select'){
						update_highchart(jQuery("#chart_type option:selected").val());
					}
				});

				jQuery("#chart_border_width").keyup(function() {
					if(jQuery("#chart_border_width").val() != ''){
						update_highchart(jQuery("#chart_type option:selected").val());
					}
				});

				jQuery("#chart_border_radious").keyup(function() {
					if(jQuery("#chart_border_radious").val() != ''){
						update_highchart(jQuery("#chart_type option:selected").val());
					}
				});

				jQuery("#chart_allow_point_select").change(function() {
					if(jQuery("#chart_allow_point_select option:selected").val() != ''){
						update_highchart(jQuery("#chart_type option:selected").val());
					}
				});

				jQuery("#chart_animation").keyup(function() {
					if(jQuery("#chart_animation").val() != ''){
						update_highchart(jQuery("#chart_type option:selected").val());
					}
				});

				jQuery("#chart_animation_duration").keyup(function() {
					if(jQuery("#chart_animation_duration").val() != ''){
						update_highchart(jQuery("#chart_type option:selected").val());
					}
				});

				jQuery("#chart_point_brightness").keyup(function() {
					if(jQuery("#chart_point_brightness").val() != ''){
						update_highchart(jQuery("#chart_type option:selected").val());
					}
				});

				jQuery("#bar_border_width").keyup(function() {
					if(jQuery("#bar_border_width").val() != ''){
						update_highchart(jQuery("#chart_type option:selected").val());
					}
				});

				jQuery("#bar_border_radious").keyup(function() {
					if(jQuery("#bar_border_radious").val() != ''){
						update_highchart(jQuery("#chart_type option:selected").val());
					}
				});

				jQuery("#chart_series_generl_color").keyup(function() {
					if(jQuery("#chart_series_generl_color").val() != ''){
						update_highchart(jQuery("#chart_type option:selected").val());
					}
				});


				jQuery("#chart_series_color").keyup(function() {
					if(jQuery("#chart_series_color").val() != ''){
						update_highchart(jQuery("#chart_type option:selected").val());
					}
				});


				jQuery("#chart_cursor_pointer").keyup(function() {
					if(jQuery("#chart_cursor_pointer").val() != ''){
						update_highchart(jQuery("#chart_type option:selected").val());
					}
				});


				jQuery("#chart_cursor_event_text").keyup(function() {
					if(jQuery("#chart_cursor_event_text").val() != ''){
						update_highchart(jQuery("#chart_type option:selected").val());
					}
				});


				jQuery("#chart_series_negative_color").keyup(function() {
					if(jQuery("#chart_series_negative_color").val() != ''){
						update_highchart(jQuery("#chart_type option:selected").val());
					}
				});


				jQuery("#column_point_padding").keyup(function() {
					if(jQuery("#column_point_padding").val() != ''){
						update_highchart(jQuery("#chart_type option:selected").val());
					}
				});


				jQuery("#chart_series_legend").keyup(function() {
					if(jQuery("#chart_series_legend").val() != ''){
						update_highchart(jQuery("#chart_type option:selected").val());
					}
				});


				jQuery("#chart_series_stacking").change(function() {
					if(jQuery("#chart_series_stacking option:selected").val() != ''){
						update_highchart(jQuery("#chart_type option:selected").val());
					}
				});



				jQuery("#intial_visibility").keyup(function() {
					if(jQuery("#intial_visibility").val() != ''){
						update_highchart(jQuery("#chart_type option:selected").val());
					}
				});



				jQuery("#chart_start_engle").keyup(function() {
					if(jQuery("#chart_start_engle").val() != ''){
						update_highchart(jQuery("#chart_type option:selected").val());
					}
				});


				jQuery("#chart_end_engle").keyup(function() {
					if(jQuery("#chart_end_engle").val() != ''){
						update_highchart(jQuery("#chart_type option:selected").val());
					}
				});


				jQuery("#chart_inner_size").keyup(function() {
					if(jQuery("#chart_inner_size").val() != ''){
						update_highchart(jQuery("#chart_type option:selected").val());
					}
				});



				jQuery("#show_checkbox").change(function() {
					if(jQuery("#show_checkbox option:selected").val() != ''){
						update_highchart(jQuery("#chart_type option:selected").val());
					}
				});


				jQuery("#check_box_series").keyup(function() {
					if(jQuery("#check_box_series").val() != ''){
						update_highchart(jQuery("#chart_type option:selected").val());
					}
				});


				jQuery("#pie_sliced").change(function() {
					if(jQuery("#pie_sliced option:selected").val() != ''){
						update_highchart(jQuery("#chart_type option:selected").val());
					}
				});
				jQuery("#pie_legend").change(function() {
					if(jQuery("#pie_legend option:selected").val() != ''){
						update_highchart(jQuery("#chart_type option:selected").val());
					}
				});



				jQuery("#pie_sliced_offset").keyup(function() {
					if(jQuery("#pie_sliced_offset").val() != ''){
						update_highchart(jQuery("#chart_type option:selected").val());
					}
				});


				jQuery("#columns_grouping").change(function() {
					if(jQuery("#columns_grouping option:selected").val() != ''){
						update_highchart(jQuery("#chart_type option:selected").val());
					}
				});

				jQuery("#chart_data_labels").change(function() {
					if(jQuery("#chart_data_labels option:selected").val() != ''){
						update_highchart(jQuery("#chart_type option:selected").val());
					}
				});


				jQuery("#chart_tooltip").change(function() {
					if(jQuery("#chart_tooltip option:selected").val() != ''){
						update_highchart(jQuery("#chart_type option:selected").val());
					}
				});


				jQuery("#chart_tooltip_crosshairs").keyup(function() {
					if(jQuery("#chart_tooltip_crosshairs").val() != ''){
						update_highchart(jQuery("#chart_type option:selected").val());
					}
				});


				jQuery("#chart_credit").change(function() {
					if(jQuery("#chart_credit option:selected").val() != ''){
						update_highchart(jQuery("#chart_type option:selected").val());
					}
				});

				jQuery("#credit_text").keyup(function() {
					if(jQuery("#credit_text").val() != ''){
						update_highchart(jQuery("#chart_type option:selected").val());
					}
				});


				jQuery("#navigation_buttons").change(function() {
					if(jQuery("#navigation_buttons option:selected").val() != ''){
						update_highchart(jQuery("#chart_type option:selected").val());
					}
				});


				jQuery("#value_decimals").keyup(function() {
					if(jQuery("#value_decimals").val() != ''){
						update_highchart(jQuery("#chart_type option:selected").val());
					}
				});



				jQuery("#value_prefix").keyup(function() {
					if(jQuery("#value_prefix").val() != ''){
						update_highchart(jQuery("#chart_type option:selected").val());
					}
				});

				jQuery("#value_suffix").keyup(function() {
					if(jQuery("#value_suffix").val() != ''){
						update_highchart(jQuery("#chart_type option:selected").val());
					}
				});

				jQuery("#chart_drilldown").change(function() {
					if(jQuery("#chart_drilldown option:selected").val() != ''){
						if(jQuery("#chart_drilldown option:selected").val() == 'true'){
							jQuery("#chart_drilldown_text").parent().parent().css("display","table-row");
							jQuery("#chart-drildown-data").css("display","block");
						}
						if(jQuery("#chart_drilldown option:selected").val() == 'false'){
							jQuery("#chart_drilldown_text").parent().parent().css("display","none");
							jQuery("#chart-drildown-data").css("display","none");
						}
						jQuery('#chart_type').trigger('change');
						//update_highchart(jQuery("#chart_type option:selected").val());
					}
				});

				jQuery("#chart_drilldown_text").keyup(function() {
					if(jQuery("#chart_drilldown_text").val() != ''){
						update_highchart(jQuery("#chart_type option:selected").val());
					}
				});

				jQuery("#step1").click(function() {
					//jQuery('.step1').toggle('slow');
					if(jQuery('#step1').text() == ' -- '){
						jQuery('#step1').text(' ++ ');
						jQuery('.step1').addClass('step1_hide');
					} else {
						jQuery('#step1').text(' -- ');
						jQuery('.step1').removeClass('step1_hide');
					}
					
				});

				jQuery("#step2").click(function() {
					//jQuery('.step2').toggle('slow');
					if(jQuery('#step2').text() == ' -- '){
						jQuery('#step2').text(' ++ ');
						jQuery('.step2').addClass('step2_hide');
					} else {
						jQuery('#step2').text(' -- ');
						jQuery('.step2').removeClass('step2_hide');
					}
					
				});
				jQuery('#step2').text(' ++ ');
				jQuery('.step2').addClass('step2_hide');
				jQuery("#step3").click(function() {
					jQuery('#data').toggle('slow');
					if(jQuery('#step3').text() == ' -- '){
						jQuery('#step3').text(' ++ ');
						//jQuery('.step3').addClass('step3_hide');
					} else {
						jQuery('#step3').text(' -- ');
						//jQuery('.step3').removeClass('step3_hide');
					}
					
				});

				jQuery("#step31").click(function() {
					jQuery('#drilldown-data').toggle('slow');
					if(jQuery('#step31').text() == ' -- '){
						jQuery('#step31').text(' ++ ');
					} else {
						jQuery('#step31').text(' -- ');
					}
					
				});

				jQuery("#step4").click(function() {
					jQuery('#chart').toggle('slow');
					if(jQuery('#step4').text() == ' -- '){
						jQuery('#step4').text(' ++ ');
					} else {
						jQuery('#step4').text(' -- ');
					}
					
				});

			});
			</script>
			<div class="wrap">
				<h1>Create High Charts</h1>
				<table>
				<tr>
					<td colspan="2"><h2>Step 1 Chart Info <span id="step1"> -- </span></h2></td>
				</tr>
					<tr class="step1">
						<td>
							<label>Chart Type:</label> 
						</td>
						<td>
							<?php $chart_series_array	=	array('--','Area Chart','Bar Chart','Bubble Chart','Column Chart','Line Chart','Pie Chart','Scatter Chart','Spark Chart','Spline Chart','Waterfall Chart');?>
							<select name="chart_type" id="chart_type">
								<?php
									foreach($chart_series_array as $chart_series_item) {
										if(	strtolower($chart_series_item)	==	$chart_type) {
											echo '<option value="'.strtolower($chart_series_item).'" selected="selected">'.ucfirst ($chart_series_item).'</option>';
										} else if($chart_series_item == "--") {
											echo '<option value="" disabled="disabled">'.$chart_series_item.'</option>';
										} else {
											echo '<option value="'.strtolower($chart_series_item).'">'.ucfirst ($chart_series_item).'</option>';
										}
									}
								?>
							</select>
						</td>
					</tr>
					<tr class="step1">
						<td>
							<label>Chart Drilldown:</label>
						</td>
						<td>
							<select style="width:300px;" id="chart_drilldown" name="chart_drilldown" >
								<?php 
									$chart_drilldown_array  = array('Enable'=>'true','Disable' => 'false');
										foreach($chart_drilldown_array as $key => $chart_drilldown_item) {
											if(	strtolower($chart_drilldown_item)	==	$chart_drilldown) {
												echo '<option value="'.strtolower($chart_drilldown_item).'" selected>'.ucfirst ($key).'</option>';
											}else {
												echo '<option value="'.strtolower($chart_drilldown_item).'">'.ucfirst ($key).'</option>';
											}
										}
								?>
							</select>
						</td>
					</tr>

					<tr class="step1">
						<td>
							<label>Chart Drilldown Button Text :</label>
						</td>
						<td>
							<input type="text" style="width:300px;" id="chart_drilldown_text" name="chart_drilldown_text" value="<?php echo $chart_drilldown_text!=''?$chart_drilldown_text:''; ?>" />
						</td>
					</tr>
					<tr class="step1">
						<td>
							<label>Chart Title</label>
						</td>
						<td>
							<input type="text" name="chart_title" id="chart_title" value="<?php echo $chart_title!=''?$chart_title:''; ?>" />
						</td>
					</tr>
					<tr class="step1">
						<td>
							<label>Chart Sub Title</label>
						</td>
						<td>
							<input type="text" name="chart_sub_title" id="chart_sub_title" value="<?php echo $chart_sub_title!=''?$chart_sub_title:''; ?>" />
						</td>
					</tr>
					<tr class="step1">
						<td>
							<label>Chart xAxis</label>
						</td>
						<td>
							<input type="text" name="chart_xAxis" id="chart_xAxis" value="<?php echo $chart_xAxis!=''?$chart_xAxis:''; ?>" />
						</td>
					</tr>
					<tr class="step1">
						<td>
							<label>Chart yAxis</label>
						</td>
						<td>
							<input type="text" name="chart_yAxis" id="chart_yAxis" value="<?php echo $chart_xAxis!=''?$chart_xAxis:''; ?>" />
						</td>
					</tr>
					<tr class="step1">
						<td>
							<label>Chart Shortcode :</label>
						</td>
						<td>
							<input type="text" name="chart_shortcode" id="chart_shortcode" value="<?php echo $chart_shortcode!=''?$chart_shortcode:''; ?>" />
						</td>
					</tr>
					
					<tr>
					<td colspan="2">
					<div id="chart-data">
						<h2>Step 2 Chart data <span id="step3"> -- </span></h2>
						<div id="data">
							<h3>Chart Data</h3><span id="clear-data">(Clear Data)</span>
							<div id="dataTable" style="height:300px;overflow:scroll;margin:2px 0 10px;"></div>
						</div>
					</div>
					</td>
					</tr>
					<tr>
					<td colspan="2">
					<div id="chart-drildown-data" style="display:none">
						<h2>Step 2.1 Chart Drildown data <span id="step31"> -- </span></h2>
						<div id="drilldown-data">
							<h3>Chart Data</h3><span id="clear-drildown-data">(Clear Data)</span>
							<div id="drilldown-dataTable" style="height:300px;overflow:scroll;margin:2px 0 10px;"></div>
						</div>
					</div>
					</td>
					</tr>
					<tr>
						<td colspan="2"><h2>Step 3 Chart Info <span id="step2"> -- </span></h2></td>
					</tr>
					<tr class="step2">
						<td>
							<label>Chart Background Color :</label>
						</td>
						<td>
							<input type="text" style="width:300px;" id="chart_background_color" name="chart_background_color" value="<?php echo $chart_background_color!=''?$chart_background_color:''; ?>" />
						</td>
					</tr>

					<tr class="step2">
						<td>
							<label>Chart Border Color :</label>
						</td>
						<td>
							<input type="text" style="width:300px;" id="chart_border_color" name="chart_border_color" value="<?php echo $chart_border_color!=''?$chart_border_color:''; ?>" />
						</td>
					</tr>

					<tr class="step2">
						<td>
							<label>Chart Border width :</label>
						</td>
						<td>
							<input type="text" style="width:300px;" id="chart_border_width" name="chart_border_width"  value="<?php echo $chart_border_width!=''?$chart_border_width:'0'; ?>" />
						</td>
					</tr>

					<tr class="step2">
						<td>
							<label>Chart Border Radious :</label>
						</td>
						<td>
							<input type="text" style="width:300px;" id="chart_border_radious" name="chart_border_radious" value="<?php echo $chart_border_radious!=''?$chart_border_radious:'0'; ?>" />
						</td>
					</tr>

					<tr class="step2">
						<td>
							<label>Chart Series AllowPointSelect :</label>
						</td>
						<td>
							<select style="width:300px;" id="chart_allow_point_select" name="chart_allow_point_select" >
								<?php 
								$allow_point_array  = array('Enable'=>'true','Disable' => 'false');
									foreach($allow_point_array as $key => $allow_point_item) {
										if(	strtolower($allow_point_item)	==	$chart_allow_point_select) {
											echo '<option value="'.strtolower($allow_point_item).'" selected>'.ucfirst ($key).'</option>';
										}else {
											echo '<option value="'.strtolower($allow_point_item).'">'.ucfirst ($key).'</option>';
										}
									}
								?>
							</select>
						</td>
					</tr>


					<tr class="step2">
						<td>
							<label>Chart Animation :</label>
						</td>
						<td>
							<input type="text" style="width:300px;" id="chart_animation" name="chart_animation" value="<?php echo $chart_animation!=''?$chart_animation:''; ?>" />
						</td>
					</tr>


					<tr class="step2">
						<td>
							<label>Chart Annimation Duration :</label>
						</td>
						<td>
							<input type="text" style="width:300px;" id="chart_animation_duration" name="chart_animation_duration" value="<?php echo $chart_animation_duration!=''?$chart_animation_duration:''; ?>" />
						</td>
					</tr>

					<tr class="step2">
						<td>
							<label>Chart Point Brightness :</label>
						</td>
						<td>
							<input type="text" style="width:300px;" id="chart_point_brightness" name="chart_point_brightness" value="<?php echo $chart_point_brightness!=''?$chart_point_brightness:''; ?>" />
						</td>
					</tr>

					<tr class="step2">
						<td>
							<label>Bar or Column Border Color :</label>
						</td>
						<td>
							<input type="text" style="width:300px;" id="bar_border_color" name="bar_border_color" value="<?php echo $bar_border_color!=''?$bar_border_color:''; ?>" />
						</td>
					</tr>

					<tr class="step2">
						<td>
							<label>Bar or Column Border width :</label>
						</td>
						<td>
							<input type="text" style="width:300px;" id="bar_border_width" name="chart_border_width" value="<?php echo $bar_border_width!=''?$bar_border_width:''; ?>" />
						</td>
					</tr>

					<tr class="step2">
						<td>
							<label>Bar or Column Border Radious :</label>
						</td>
						<td>
							<input type="text" style="width:300px;" id="bar_border_radious" name="chart_border_radious" value="<?php echo $bar_border_radious!=''?$bar_border_radious:''; ?>" />
						</td>
					</tr>
					<tr class="step2">
						<td>
							<label>Color for Series(Generl It will apply on each series if not specified for each series) :</label>
						</td>
						<td>
							<input type="text" style="width:300px;" id="chart_series_generl_color" name="chart_series_generl_color" value="<?php echo $chart_series_generl_color!=''?$chart_series_generl_color:''; ?>" />
						</td>
					</tr>

					<tr class="step2">
						<td>
							<label>Color for Each Series(multiple color for different series write by comma separated ie #FFFFFF,#343434) :</label>
						</td>
						<td>
							<input type="text" style="width:300px;" id="chart_series_color" name="chart_series_color"  value="<?php echo $chart_series_color!=''?$chart_series_color:''; ?>"/>
						</td>
					</tr class="step2">
					<tr class="step2">
						<td>
							<label>Cursor Pointer :</label>
						</td>
						<td>
							<input type="text" style="width:300px;" id="chart_cursor_pointer" name="chart_cursor_pointer" value="<?php echo $chart_cursor_pointer!=''?$chart_cursor_pointer:''; ?>" />
						</td>
					</tr>
					<tr class="step2">
						<td>
							<label>Cursor Pointer Event Text:</label>
						</td>
						<td>
							<input type="text" style="width:300px;" id="chart_cursor_event_text" name="chart_cursor_event_text" value="<?php echo $chart_cursor_event_text!=''?$chart_cursor_event_text:''; ?>" />
						</td>
					</tr>

					<tr class="step2">
						<td>
							<label>Negative Color for Series:</label>
						</td>
						<td>
							<input type="text" style="width:300px;" id="chart_series_negative_color" name="chart_series_negative_color" value="<?php echo $chart_series_negative_color!=''?$chart_series_negative_color:''; ?>" />
						</td>
					</tr>
					<tr class="step2">
						<td>
							<label>Column Point Padding:</label>
						</td>
						<td>
							<input type="text" style="width:300px;" id="column_point_padding" name="column_point_padding" value="<?php echo $column_point_padding!=''?$column_point_padding:''; ?>" />
						</td>
					</tr>

					<tr class="step2">
						<td>
							<label>Show Legend for each series: (multiple series legend on/off for different series write by comma separated i.e true,false)</label>
						</td>
						<td>
							<input type="text" style="width:300px;" id="chart_series_legend" name="chart_series_legend" value="<?php echo $chart_series_legend!=''?$chart_series_legend:''; ?>" />
						</td>
					</tr>

					<tr class="step2">
						<td>
							<label>Chart Series Stacking :</label>
						</td>
						<td>
							<select style="width:300px;" id="chart_series_stacking" name="chart_series_stacking" >
								<?php 
								$chart_series_stacking_array  = array('Normal'=>'normal','Null' => 'null','Percent' => 'percent');
									foreach($chart_series_stacking_array as $key => $chart_series_stacking_item) {
										if(	strtolower($chart_series_stacking_item)	==	$chart_series_stacking) {
											echo '<option value="'.strtolower($chart_series_stacking_item).'" selected>'.ucfirst ($key).'</option>';
										}else {
											echo '<option value="'.strtolower($chart_series_stacking_item).'">'.ucfirst ($key).'</option>';
										}
									}
								?>
							</select>
						</td>
					</tr>

					<tr class="step2">
						<td>
							<label>Intial Visibility :(multiple series Visiilityb on/off for different series write by comma separated i.e true,false)</label>
						</td>
						<td>
							<input type="text" style="width:300px;" id="intial_visibility" name="intial_visibility" value="<?php echo $intial_visibility!=''?$intial_visibility:''; ?>" />
		
						</td>
					</tr>

					<tr class="step2">
						<td>
							<label>Pie Chart start Engle :</label>
						</td>
						<td>
							<input type="text" style="width:300px;" id="chart_start_engle" name="chart_start_engle" value="<?php echo $chart_start_engle!=''?$chart_start_engle:''; ?>" />
						</td>
					</tr>

					<tr class="step2">
						<td>
							<label>Pie Chart End Engle :</label>
						</td>
						<td>
							<input type="text" style="width:300px;" id="chart_end_engle" name="chart_end_engle" value="<?php echo $chart_end_engle!=''?$chart_end_engle:''; ?>" />
						</td>
					</tr>
					<tr class="step2">
						<td>
							<label>Pie Chart Inner Size :</label>
						</td>
						<td>
							<input type="text" style="width:300px;" id="chart_inner_size" name="chart_inner_size" value="<?php echo $chart_inner_size!=''?$chart_inner_size:''; ?>" />
						</td>
					</tr>

					<tr class="step2">
						<td>
							<label>Show Checkbox :</label>
						</td>
						<td>
							<select style="width:300px;" id="show_checkbox" name="show_checkbox" >
								<?php 
									$show_checkbox_array  = array('Enable'=>'true','Disable' => 'false');
										foreach($show_checkbox_array as $key => $show_checkbox_item) {
											if(	strtolower($show_checkbox_item)	==	$show_checkbox) {
												echo '<option value="'.strtolower($show_checkbox_item).'" selected>'.ucfirst ($key).'</option>';
											}else {
												echo '<option value="'.strtolower($show_checkbox_item).'">'.ucfirst ($key).'</option>';
											}
										}
								?>
							</select>
						</td>
					</tr>

					<tr class="step2">
						<td>
							<label>Show Checkbox for each series :  (multiple series checbox for different series write by comma separated i.e true,false)</label>
						</td>
						<td>
							<input type="text" style="width:300px;" id="check_box_series" name="check_box_series" value="<?php echo $check_box_series!=''?$check_box_series:''; ?>" />
						</td>
					</tr>

					<tr class="step2">
						<td>
							<label>Pie Chart Sliced :</label>
						</td>
						<td>
							<select style="width:300px;" id="pie_sliced" name="pie_sliced" >
								<?php 
									$pie_sliced_array  = array('Enable'=>'true','Disable' => 'false');
										foreach($pie_sliced_array as $key => $pie_sliced_item) {
											if(	strtolower($pie_sliced_item)	==	$pie_sliced) {
												echo '<option value="'.strtolower($pie_sliced_item).'" selected>'.ucfirst ($key).'</option>';
											}else {
												echo '<option value="'.strtolower($pie_sliced_item).'">'.ucfirst ($key).'</option>';
											}
										}
								?>
							</select>
						</td>
					</tr>

					<tr class="step2">
						<td>
							<label>Pie Chart Legend on or Off :</label>
						</td>
						<td>
							<select style="width:300px;" id="pie_legend" name="pie_legend" >
								<?php 
									$pie_legend_array  = array('On'=>'true','Off' => 'false');
										foreach($pie_legend_array as $key => $pie_legend_item) {
											if(	strtolower($pie_legend_item)	==	$pie_legend) {
												echo '<option value="'.strtolower($pie_legend_item).'" selected>'.ucfirst ($key).'</option>';
											}else {
												echo '<option value="'.strtolower($pie_legend_item).'">'.ucfirst ($key).'</option>';
											}
										}
								?>
							</select>
						</td>
					</tr>

					<tr class="step2">
						<td>
							<label>Pie Chart Sliced Offset :</label>
						</td>
						<td>
							<input type="text" style="width:300px;" id="pie_sliced_offset" name="pie_sliced_offset" value="<?php echo $pie_sliced_offset!=''?$pie_sliced_offset:''; ?>" />
						</td>
					</tr>

					<tr class="step2">
						<td>
							<label>Columns Grouping :</label>
						</td>
						<td>
							<select style="width:300px;" id="columns_grouping" name="columns_grouping" >
								<?php 
									$columns_grouping_array  = array('Enable'=>'true','Disable' => 'false');
										foreach($columns_grouping_array as $key => $columns_grouping_item) {
											if(	strtolower($columns_grouping_item)	==	$columns_grouping) {
												echo '<option value="'.strtolower($columns_grouping_item).'" selected>'.ucfirst ($key).'</option>';
											}else {
												echo '<option value="'.strtolower($columns_grouping_item).'">'.ucfirst ($key).'</option>';
											}
										}
								?>
							</select>
						</td>
					</tr>
					
					<tr class="step2">
						<td>
							<label>Data Labels :</label>
						</td>
						<td>
							<select style="width:300px;" id="chart_data_labels" name="chart_data_labels" >
								<?php 
									$chart_data_labels_array  = array('Enable'=>'true','Disable' => 'false');
										foreach($chart_data_labels_array as $key => $chart_data_labels_item) {
											if(	strtolower($chart_data_labels_item)	==	$chart_data_labels) {
												echo '<option value="'.strtolower($chart_data_labels_item).'" selected>'.ucfirst ($key).'</option>';
											}else {
												echo '<option value="'.strtolower($chart_data_labels_item).'">'.ucfirst ($key).'</option>';
											}
										}
								?>
							</select>
						</td>
					</tr>

					<tr class="step2">
						<td>
							<label>Chart ToolTip :</label>
						</td>
						<td>
							<select style="width:300px;" id="chart_tooltip" name="chart_tooltip">
								<?php 
									$chart_tooltip_array  = array('Enable'=>'true','Disable' => 'false');
										foreach($chart_tooltip_array as $key => $chart_tooltip_item) {
											if(	strtolower($chart_tooltip_item)	==	$chart_tooltip) {
												echo '<option value="'.strtolower($chart_tooltip_item).'" selected>'.ucfirst ($key).'</option>';
											}else {
												echo '<option value="'.strtolower($chart_tooltip_item).'">'.ucfirst ($key).'</option>';
											}
										}
								?>
							</select>
						</td>
					</tr>

					<tr class="step2">
						<td>
							<label>Chart ToolTip Croshairs:(true or true,true)</label>
						</td>
						<td>
							<input type="text" style="width:300px;" id="chart_tooltip_crosshairs" name="chart_tooltip_crosshairs" value="<?php echo $chart_tooltip_crosshairs!=''?$chart_tooltip_crosshairs:''; ?>" />
						</td>
					</tr>

					<tr class="step2">
						<td>
							<label>Chart Credits:</label>
						</td>
						<td>
							<select style="width:300px;" id="chart_credit" name="chart_credit" >
								<?php 
									$chart_credit_array  = array('Enable'=>'true','Disable' => 'false');
										foreach($chart_credit_array as $key => $chart_credit_item) {
											if(	strtolower($chart_credit_item)	==	$chart_credit) {
												echo '<option value="'.strtolower($chart_credit_item).'" selected>'.ucfirst ($key).'</option>';
											}else {
												echo '<option value="'.strtolower($chart_credit_item).'">'.ucfirst ($key).'</option>';
											}
										}
								?>
							</select>
						</td>
					</tr>

					<tr class="step2">
						<td>
							<label>Credit Text :</label>
						</td>
						<td>
							<input type="text" style="width:300px;" id="credit_text" name="credit_text" value="<?php echo $credit_text!=''?$credit_text:''; ?>" />
						</td>
					</tr>

					<tr class="step2">
						<td>
							<label>Credit href :</label>
						</td>
						<td>
							<input type="text" style="width:300px;" id="credit_href" name="credit_href" value="<?php echo $credit_href!=''?$credit_href:''; ?>" />
						</td>
					</tr>

					<tr class="step2">
						<td>
							<label>Navigation Buttons:</label>
						</td>
						<td>
							<select style="width:300px;" id="navigation_buttons" name="navigation_buttons" >
								<?php 
									$navigation_buttons_array  = array('Enable'=>'true','Disable' => 'false');
										foreach($navigation_buttons_array as $key => $navigation_buttons_item) {
											if(	strtolower($navigation_buttons_item)	==	$navigation_buttons) {
												echo '<option value="'.strtolower($navigation_buttons_item).'" selected>'.ucfirst ($key).'</option>';
											}else {
												echo '<option value="'.strtolower($navigation_buttons_item).'">'.ucfirst ($key).'</option>';
											}
										}
								?>
							</select>
						</td>
					</tr>

					<tr class="step2">
						<td>
							<label>Value Decimals :</label>
						</td>
						<td>
							<input type="text" style="width:300px;" id="value_decimals" name="value_decimals" value="<?php echo $value_decimals!=''?$value_decimals:''; ?>" />
						</td>
					</tr>

					<tr class="step2">
						<td>
							<label>Value Prefix :</label>
						</td>
						<td>
							<input type="text" style="width:300px;" id="value_prefix" name="value_prefix" value="<?php echo $value_prefix!=''?$value_prefix:''; ?>" />
						</td>
					</tr>

					<tr class="step2">
						<td>
							<label>Value Suffix :</label>
						</td>
						<td>
							<input type="text" style="width:300px;" id="value_suffix" name="value_suffix" value="<?php echo $value_suffix!=''?$value_suffix:''; ?>" />
						</td>
					</tr>

				</table>

				<div id="chart+preview">
					<h2>Step 4: Chart Preview <span id="step4"> -- </span></h2>
					<div id="chart">
						<div id="container" style="min-width: 310px; height: 400px; margin: 0 auto"></div>
						<div id="result"></div>
						<table id="table-sparkline">
						    <thead id="thead-sparkline">
						        <tr></tr>
						    </thead>
						    <tbody id="tbody-sparkline">
						    </tbody>
						</table>
					</div>
				</div>
				<div id="chart_finish">
					<h2>Step 5: Chart Finish</h2>
					<div id="finish">
						<input type="hidden" id="chart_id" name="chart_id" value="<?php echo $_GET['id']; ?>" >
						<?php if(isset($_GET['id']) && !empty($_GET['id'])) {  ?>
						<input type="submit" id="update_chart" name="update_chart" value="Update Chart" >
						<?php }else { ?>
						<input type="submit" id="save_chart" name="save_chart" value="Save Chart" >
						<?php }?>
					</div>
				</div>
			</div>
			<?php
		}

		public function error_check_highcharts($error_msg) {
			$msg_string = '';
			foreach ($error_msg as $value) {
				if(!empty($value)) {
					$msg_string = $msg_string . '<div class="error">' . $msg_string = $value.'</div>';
				}
			}
			return $msg_string;
		}


		public function load_highcharts_stylesheet() {
			?>
			<style type="text/css">
				.wrap{
					width: 100%;
				}
				.wrap table{ 
					width:100%;
				}
				.wrap table tr{
				}
				.wrap table tr td{ 
					padding: 10px; 
					width: 50%;
				}
				.wrap table tr td label{ 
					font-size: 16px;
				}
				.wrap table tr td input[type="text"],select{
					width: 350px;
					height: 40px !important;
					padding: 6px;
					text-align: left;
				}
				.wrap table tr td input[type="button"]{
					width: 125px;
					height: 40px !important;
					padding: 10px;
					text-align: center;
					color: #fff;
					background: purple;
				}
				.success {
					background-color: #DFF2BF;
					border: 1px solid #BCDF7D;
					color: #4F8A10;
					padding: 10px;
					font-size: 15px;
					font-weight: bold;
					margin: 10px 0px;
					border-radius: 5px;
				}

				.error, .hc_failure {
					color: red !important;
					background-color: #FFBABA;
					border: solid 1px #dd3c10;
					padding: 10px !important;
					font-size: 15px;
					font-weight: bold;
					margin: 10px 0px;
					border-radius: 5px;
				}
				.field_error {
					color: #D8000C;
					background-color: #FFBABA;
					border: solid 1px #DD3C10;
					padding: 5px 10px;
					font-size: 15px;
					font-weight: bold;
					border-radius: 5px;
					width: 380px;
					margin: 5px 0px -10px;
				}
				.add_hc_shortcode {
					color: purple !important;
					font-weight: bold;
				}
				#result {
					text-align: right;
					color: gray;
					min-height: 2em;
				}
				#table-sparkline {
					margin: 0 auto;
				    border-collapse: collapse;
				    /*display: none;*/
				}
				#table-sparkline th {
				    font-weight: bold;
				    text-align: left;
				}
				#table-sparkline td, th {
				    padding: 5px;
				    border-bottom: 1px solid silver;
				    height: 20px;
				}

				#table-sparkline thead th {
				    border-top: 2px solid gray;
				    border-bottom: 2px solid gray;
				}
				.highcharts-tooltip>span {
					background: white;
					border: 1px solid silver;
					border-radius: 3px;
					box-shadow: 1px 1px 2px #888;
					padding: 8px;
				}
				.step2_hide{display: none;}
				.step1_hide{display: none;}
			</style>
		<?php
	}

	public function ajax_create_new_chart(){
		//require(HC_DIR."json.php");
		global $wpdb;
		$ajax 					= 	false;
		$highcharts_table 		=  $wpdb->prefix."highcharts";
		
		if(isset($_POST['ajax']) && $_POST['ajax']=='true')	{
			$chart_type  	 	= 	$_POST['chart_type'];
			$chart_title 	 	= 	$_POST['chart_title'];
			$chart_sub_title 	= 	$_POST['chart_sub_title'];
			$chart_xAxis 	 	= 	$_POST['chart_xAxis'];
			$chart_yAxis 	 	= 	$_POST['chart_yAxis'];
			$chart_shortcode  	= 	$_POST['chart_shortcode'];
			$chart_series  		= 	$_POST['chart_series'];
			$chart_data  		= 	$_POST['chart_data'];
			$chart_xcats  		= 	$_POST['chart_xcats'];
			$chart_ylabels  	= 	$_POST['chart_ylabels'];
			$chart_id  			= 	$_POST['chart_id'];

			$chart_background_color = $_POST['chart_background_color'];
			$chart_border_color 	= $_POST['chart_border_color'];
			$chart_border_width 	= $_POST['chart_border_width'];
			$chart_border_radious 	= $_POST['chart_border_radious'];

			$chart_allow_point_select    	= 		$_POST['chart_allow_point_select'];
			$chart_animation 		    	= 		$_POST['chart_animation'];
			$chart_animation_duration    	= 		$_POST['chart_animation_duration'];
			$chart_point_brightness    		= 		$_POST['chart_point_brightness'];
			$bar_border_color 		    	= 		$_POST['bar_border_color'];
			$bar_border_width 		    	= 		$_POST['bar_border_width'];
			$bar_border_radious 		    = 		$_POST['bar_border_radious'];
			$chart_series_generl_color   	= 		$_POST['chart_series_generl_color'];
			$chart_series_color 	        =   	$_POST['chart_series_color'];
			$chart_cursor_pointer 	    	=       $_POST['chart_cursor_pointer'];
			$chart_cursor_event_text     	=     	$_POST['chart_cursor_event_text'];
			$chart_series_negative_color 	= 		$_POST['chart_series_negative_color'];
			$column_point_padding 			= 		$_POST['column_point_padding'];
			$chart_series_legend 			= 		$_POST['chart_series_legend'];
			$chart_series_stacking 			= 		$_POST['chart_series_stacking'];
			$intial_visibility 				= 		$_POST['intial_visibility'];
			$chart_start_engle 				= 		$_POST['chart_start_engle'];
			$chart_end_engle 				= 		$_POST['chart_end_engle'];
			$chart_inner_size 				= 		$_POST['chart_inner_size'];
			$show_checkbox 					= 		$_POST['show_checkbox'];
			$check_box_series 				= 		$_POST['check_box_series'];
			$pie_sliced 					= 		$_POST['pie_sliced'];
			$pie_legend 					= 		$_POST['pie_legend'];
			$pie_sliced_offset 				= 		$_POST['pie_sliced_offset'];
			$columns_grouping 				= 		$_POST['columns_grouping'];
			$chart_data_labels 				= 		$_POST['chart_data_labels'];
			$chart_tooltip 					= 		$_POST['chart_tooltip'];
			$chart_tooltip_crosshairs 		= 		$_POST['chart_tooltip_crosshairs'];
			$chart_credit 					= 		$_POST['chart_credit'];
			$credit_text 					= 		$_POST['credit_text'];
			$credit_href 					= 		$_POST['credit_href'];
			$navigation_buttons 			= 		$_POST['navigation_buttons'];
			$value_decimals 				= 		$_POST['value_decimals'];
			$value_prefix 					= 		$_POST['value_prefix'];
			$value_suffix 					= 		$_POST['value_suffix'];
			$chart_drilldown 				= 		$_POST['chart_drilldown'];
			$chart_drilldown_series 		= 		$_POST['chart_drilldown_series'];
			$chart_drilldown_text 			= 		$_POST['chart_drilldown_text'];
			$chart_drilldown_data           =       $_POST['chart_drilldown_data'];
			

			$chart_options      =   array(
										'chart_background_color' => $chart_background_color, 'chart_border_color'=> $chart_border_color,
										'chart_border_width' => $chart_border_width,'chart_border_radious' => $chart_border_width,
										'chart_allow_point_select' => $chart_allow_point_select,'chart_animation' => $chart_animation,
										'chart_animation_duration'=> $chart_animation_duration,'chart_point_brightness'=> $chart_point_brightness,
										'bar_border_color' => $bar_border_color,'bar_border_width'=> $bar_border_width, 
										'bar_border_radious' => $bar_border_radious ,'chart_series_generl_color' => $chart_series_generl_color,
										'chart_series_color' => $chart_series_color,'chart_cursor_pointer' => $chart_cursor_pointer,
										'chart_cursor_event_text' => $chart_cursor_event_text,'chart_cursor_event_text' => $chart_cursor_event_text,
										'chart_series_negative_color'=> $chart_series_negative_color,
										'column_point_padding'=> $column_point_padding,
										'chart_series_legend' => $chart_series_legend,'chart_series_stacking' => $chart_series_stacking,
										'intial_visibility' => $intial_visibility,'chart_start_engle'=>$chart_start_engle,
										'chart_end_engle' => $chart_end_engle,'chart_inner_size' => $chart_inner_size,
										'show_checkbox'=>$show_checkbox,'check_box_series' => $check_box_series,
										'pie_sliced' => $pie_sliced,'pie_legend' => $pie_legend,'pie_sliced_offset' => $pie_sliced_offset,
										'columns_grouping' =>$columns_grouping,'chart_data_labels' => $chart_data_labels,
										'chart_tooltip' => $chart_tooltip,'chart_tooltip_crosshairs' => $chart_tooltip_crosshairs,
										'chart_credit' => $chart_credit,'credit_text' => $credit_text,
										'credit_text' => $credit_text,'credit_href' => $credit_href,
										'navigation_buttons' => $navigation_buttons,'value_decimals'=>$value_decimals,
										'value_prefix' => $value_prefix,'value_suffix' => $value_suffix,
										'chart_drilldown' => $chart_drilldown,'chart_drilldown_text' => $chart_drilldown_text
									);

			$chart_options      		=   json_encode($chart_options ,JSON_NUMERIC_CHECK);
			$chart_xcats    			=   json_encode($chart_xcats,JSON_NUMERIC_CHECK);
			$chart_ylabels    			=   json_encode($chart_ylabels ,JSON_NUMERIC_CHECK);
            $chart_series       		=   json_encode($chart_series ,JSON_NUMERIC_CHECK);
            $chart_data         		=   json_encode($chart_data ,JSON_NUMERIC_CHECK);
            $chart_drilldown_series     =   json_encode($chart_drilldown_series ,JSON_NUMERIC_CHECK);
            $chart_drilldown_data       =   json_encode($chart_drilldown_data ,JSON_NUMERIC_CHECK);
            
            $insert_data        = array( 
					'creation_date' => current_time('mysql'),
					'type' =>  $chart_type,
					'title' => $chart_title,
					'subtitle'=> $chart_sub_title,
					'xAxis_title' => $chart_xAxis,
					'yAxis_title' =>  $chart_yAxis,
					'xAxisCats' => $chart_xcats,
					'yAxisLabels' => $chart_ylabels,
					'shortcode_name' => $chart_shortcode,
					'series' =>  $chart_series,
					'hotSeries' => $chart_data,
					'drilldownseries' =>  $chart_drilldown_series,
					'drilldownhotSeries' => $chart_drilldown_data,
					'chart_options' => $chart_options
				);
            if($chart_id == ''){
				$insert_id = $wpdb->insert( 
						$highcharts_table,
					 	$insert_data,
					 	array('%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s' )
					 );
			} else {
				$update_id = $wpdb->update( 
						$highcharts_table,
					 	$insert_data,
					 	array('id'=>$chart_id),
					 	array('%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s' ),
					 	array('%d')
					 );
			}
			if($chart_id == ''){
				if($insert_id){
					echo "Shortcode Created Successfully.Please use this shortcode:[HIGH_CHARTS id='".$wpdb->insert_id."']";
				} else {
					print_r($wpdb);
					echo "Error In creating Chart";
				}
			} else {
				if($update_id){
					echo "Shortcode Updated Successfully.Please use this shortcode:[HIGH_CHARTS id='".$chart_id."']";
				} else {
					echo "Error In Updating  Chart";
				}
			}
			
		}
		exit();
	}
	public function high_charts_shortcode_function($arg){
		
		ob_start();
		global $wpdb;
		$highcharts_table 		=  $wpdb->prefix."highcharts";
		$id 					=  $arg['id'];
		$shortcode_query    	=  "SELECT  * FROM $highcharts_table WHERE id='$id';";
		$chart_items 			=  $wpdb->get_results($shortcode_query);
		if(! empty($chart_items)){
			foreach ($chart_items as $key => $item) {
				$creation_date  			= 		$item->creation_date;
				$chart_type  				= 		$item->type;
				$chart_title  				= 		$item->title;
				$chart_sub_title  			= 		$item->subtitle;
				$chart_shortcode  			= 		$item->shortcode_name;
				$chart_xAxis  				= 		$item->xAxis_title;
				$chart_yAxis  				= 		$item->yAxis_title;
				$chart_xAxisCats    		= 		$item->xAxisCats;
				$chart_series  				= 		$item->series;
				$chart_data  				= 		$item->hotSeries;
				$chart_option  	    		= 		$item->chart_options;
				$chart_drilldown_data 		= 		$item->drilldownhotSeries;
				$chart_drilldown_series  	= 		$item->drilldownseries;
			}
			$chart_options          		=   json_decode($chart_option,true);
			$chart_background_color 		=   $chart_options['chart_background_color'];
			$chart_border_color 			=   $chart_options['chart_border_color'];
			$chart_border_width 			=   $chart_options['chart_border_width'];
			$chart_border_radious 			=   $chart_options['chart_border_radious'];

			$chart_allow_point_select    	= 		$chart_options['chart_allow_point_select'];
			$chart_animation 		    	= 		$chart_options['chart_animation'];
			$chart_point_brightness 		= 		$chart_options['chart_point_brightness'];
			$chart_animation_duration    	= 		$chart_options['chart_animation_duration'];
			$bar_border_color 		    	= 		$chart_options['bar_border_color'];
			$bar_border_width 		    	= 		$chart_options['bar_border_width'];
			$bar_border_radious 		    = 		$chart_options['bar_border_radious'];
			$chart_series_generl_color   	= 		$chart_options['chart_series_generl_color'];
			$chart_series_color 	        =   	$chart_options['chart_series_color'];
			$chart_cursor_pointer 	    	=       $chart_options['chart_cursor_pointer'];
			$chart_cursor_event_text     	=     	$chart_options['chart_cursor_event_text'];
			$chart_series_negative_color 	= 		$chart_options['chart_series_negative_color'];
			$chart_series_legend 			= 		$chart_options['chart_series_legend'];
			$chart_series_stacking 			= 		$chart_options['chart_series_stacking'];
			$intial_visibility 				= 		$chart_options['intial_visibility'];
			$chart_start_engle 				= 		$chart_options['chart_start_engle'];
			$chart_end_engle 				= 		$chart_options['chart_end_engle'];
			$chart_inner_size 				= 		$chart_options['chart_inner_size'];
			$show_checkbox 					= 		$chart_options['show_checkbox'];
			$check_box_series 				= 		$chart_options['check_box_series'];
			$pie_sliced 					= 		$chart_options['pie_sliced'];
			$pie_legend 					= 		$chart_options['pie_legend'];
			$pie_sliced_offset 				= 		$chart_options['pie_sliced_offset'];
			$columns_grouping 				= 		$chart_options['columns_grouping'];
			$chart_data_labels 				= 		$chart_options['chart_data_labels'];
			$chart_tooltip 					= 		$chart_options['chart_tooltip'];
			$chart_tooltip_crosshairs 		= 		$chart_options['chart_tooltip_crosshairs'];
			$chart_credit 					= 		$chart_options['chart_credit'];
			$credit_text 					= 		$chart_options['credit_text'];
			$credit_href 					= 		$chart_options['credit_href'];
			$navigation_buttons 			= 		$chart_options['navigation_buttons'];
			$value_decimals 				= 		$chart_options['value_decimals'];
			$value_prefix 					= 		$chart_options['value_prefix'];
			$value_suffix 					= 		$chart_options['value_suffix'];
			$chart_drilldown 				= 		$chart_options['chart_drilldown'];
			$chart_drilldown_text 			= 		$chart_options['chart_drilldown_text'];
			$column_point_padding 			= 		$chart_options['column_point_padding'];
			
			?>
			<?php if($chart_type != 'spark chart') { ?>
			<div id="container_<?php echo $id; ?>" style="min-width: 310px; height: 400px; margin: 0 auto">
			</div>
			<?php } ?>
			<?php if($chart_type == 'spark chart') { ?>
			<div id="result_<?php echo $id; ?>"></div>
			<table id="table-sparkline_<?php echo $id; ?>">
			    <thead id="thead-sparkline_<?php echo $id; ?>">
			        <tr></tr>
			    </thead>
			    <tbody id="tbody-sparkline_<?php echo $id; ?>">
			    </tbody>
			</table>
			<?php } ?>
			<script type="text/javascript">
				jQuery(document).ready(function($) {
					var chart_type 					= '<?php echo $chart_type; ?>';
					var chart_drilldown 			= '<?php echo $chart_drilldown; ?>';
					var chart_allow_point_select 	= '<?php echo $chart_allow_point_select; ?>';
					var show_checkbox 				= '<?php echo $show_checkbox; ?>';
					var columns_grouping 			= '<?php echo $columns_grouping; ?>';
					var chart_credit 				= '<?php echo $chart_credit; ?>';
					var navigation_buttons 			= '<?php echo $navigation_buttons; ?>';
					var chart_data_labels 			= '<?php echo $chart_data_labels; ?>';
					var chart_tooltip 				= '<?php echo $chart_tooltip; ?>';
					var pie_legend 					= '<?php echo $pie_legend; ?>';


					if(chart_allow_point_select == 'true'){ chart_allow_point_select = true; } else { chart_allow_point_select = false; }
				    if(show_checkbox == 'true'){ show_checkbox = true; } else { show_checkbox = false; }
				    if(columns_grouping == 'true'){ columns_grouping = true; } else { columns_grouping = false; }
				    if(chart_credit == 'true'){ chart_credit = true; } else { chart_credit = false; }
				    if(navigation_buttons == 'true'){ navigation_buttons = true; } else { navigation_buttons = false; }
				    if(chart_data_labels == 'true'){ chart_data_labels = true; } else { chart_data_labels = false; }
				    if(chart_tooltip == 'true'){ chart_tooltip = true; } else { chart_tooltip = false; }
				    if(pie_legend == 'true'){ pie_legend = true; } else { pie_legend = false; }


					<?php if($chart_drilldown_series != ''){ ?>
				    	var drilldownSeries 	= <?php echo $chart_drilldown_series!=''?$chart_drilldown_series:'empty'; ?>;
					<?php } ?>
					
					var json_data = <?php echo $chart_series; ?>;
					var seriesArr = json_data;
					<?php if($chart_type == 'area chart'){ ?>
							jQuery('#container_<?php echo $id; ?>').highcharts({
					            chart: {
					                type: 'area',
					                animation: {
					                        duration:<?php echo $chart_animation_duration; ?>,
					                        easing:<?php echo "'".$chart_animation."'"; ?>
					                 },
					                backgroundColor:<?php echo "'".$chart_background_color."'"; ?>,
					                borderColor:<?php echo "'".$chart_border_color."'"; ?>,
					                borderWidth:<?php echo $chart_border_width; ?>,
					                borderRadious:<?php echo $chart_border_radious; ?>,
					                color:<?php echo "'".$chart_series_generl_color."'"; ?>,
					                allowPointSelect:<?php echo $chart_allow_point_select; ?>,
					                negativeColor:<?php echo "'".$chart_series_negative_color."'"; ?>,
					                pointPadding:<?php echo $column_point_padding; ?>,
					        
					                showCheckbox:<?php echo $show_checkbox; ?>,
					                enabled:<?php echo $chart_allow_point_select; ?>,
					                dataLabels: {
					                    enabled: <?php echo $chart_data_labels; ?>
					                }
					            },
					            title: {
					                text: <?php echo "'".$chart_title."'"; ?>
					            },
					            subtitle: {
					                text: <?php echo "'".$chart_sub_title."'"; ?>
					            },
					            xAxis: {
					                allowDecimals: false,
					                labels: {
					                  /*  formatter: function() {
					                        return this.value; // clean, unformatted number for year
					                    }*/
					                }
					            },
					            yAxis: {
					                title: {
					                    text: <?php echo "'".$chart_yAxis."'"; ?>
					                },
					                labels: {
					                   /* formatter: function() {
					                        return this.value / 1000 +'k';
					                    }*/
					                }
					            },
					            tooltip: {
					                enabled:<?php echo $chart_tooltip; ?>,
					                crosshairs:<?php echo $chart_tooltip_crosshairs; ?>,
					                valueDecimals: <?php echo $value_decimals; ?>,
					                valuePrefix: <?php echo "'".$value_prefix."'"; ?>,
					                valueSuffix: <?php echo "'".$value_suffix."'"; ?>,
					            },
					            credits: {
					                enabled:<?php echo $chart_credit; ?>,
					                text:<?php echo "'".$credit_text."'"; ?>,
					                href:<?php echo "'".$credit_href."'"; ?>
					            },
					            navigation: {
					                buttonOptions: {
					                    enabled:<?php echo $navigation_buttons; ?>
					                }
					            },
					            plotOptions: {
					                series:{
					                    borderColor:<?php echo "'".$bar_border_color."'"; ?>,
					                    borderRadious:<?php echo $bar_border_radious; ?>,
					                    borderWidth:<?php echo $bar_border_width; ?>,
					                    stacking:<?php echo "'".$chart_series_stacking."'"; ?>
					                },
					                area: {
					                    pointStart: 1996,
					                    marker: {
					                        symbol: 'circle',
					                        radius: 2,
					                        states: {
					                            hover: {
					                                enabled: true,
					                                brightness:<?php echo $chart_point_brightness; ?>
					                            }
					                        }
					                    },
					                    cursor: <?php echo "'".$chart_cursor_pointer."'"; ?>,
					                    events: {
					                        click: function() {
					                            alert(<?php echo "'".$chart_cursor_event_text."'"; ?>);
					                        }
					                    }
					                }

					            },
					            series:seriesArr
					        });
					<?php } else	if($chart_type == "bar chart"){ ?>
					<?php     if($chart_drilldown == 'false'){   ?>
						    	jQuery('#container_<?php echo $id; ?>').highcharts({
					            chart: {
					                type: 'bar',
					                 animation: {
					                        duration:<?php echo $chart_animation_duration; ?>,
					                        easing:<?php echo "'".$chart_animation."'"; ?>
					                 },
					                color:<?php echo "'".$chart_series_generl_color."'"; ?>,
					                backgroundColor:<?php echo "'".$chart_background_color."'"; ?>,
					                borderColor:<?php echo "'".$chart_border_color."'"; ?>,
					                borderWidth:<?php echo $chart_border_width; ?>,
					                borderRadious:<?php echo $chart_border_radious; ?>
					            },
					            title: {
					                text:<?php echo "'".$chart_title."'"; ?> 
					            },
					            subtitle: {
					                text: <?php echo "'".$chart_sub_title."'"; ?>
					            },
					            xAxis: {
					                categories: <?php echo $chart_xAxisCats ?>,
					                title: {
					                    text: <?php echo "'".$chart_xAxis."'"; ?>
					                }
					            },
					            yAxis: {
					                
					                title: {
					                    text: <?php echo "'".$chart_yAxis."'"; ?>,
					                    align: 'high'
					                },
					                labels: {
					                    overflow: 'justify'
					                }
					            },
					             tooltip: {
					                enabled:<?php echo $chart_tooltip; ?>,
					                crosshairs:<?php echo $chart_tooltip_crosshairs; ?>,
					                valueDecimals: <?php echo $value_decimals; ?>,
					                valuePrefix: <?php echo "'".$value_prefix."'"; ?>,
					                valueSuffix: <?php echo "'".$value_suffix."'"; ?>
					            },
					            plotOptions: {
					                bar: {
					                	stacking:<?php echo "'".$chart_series_stacking."'"; ?>,
					                    dataLabels: {
					                        enabled: <?php echo $chart_data_labels; ?>
					                    },
			                            cursor: <?php echo "'".$chart_cursor_pointer."'"; ?>,
			                            events: {

			                                click: function() {

			                                    alert(<?php echo "'".$chart_cursor_event_text."'"; ?>);

			                                }

			                            },
					                    grouping:<?php echo $columns_grouping; ?>,
					                    allowPointSelect:<?php echo $chart_allow_point_select; ?>,
				                        borderColor:<?php echo "'".$bar_border_color."'"; ?>,
				                        borderRadious:<?php echo $bar_border_radious; ?>,
				                        borderWidth:<?php echo $bar_border_width; ?>,
				                        negativeColor:<?php echo "'".$chart_series_negative_color."'"; ?>,
				                        pointPadding:<?php echo $column_point_padding; ?>,
				                        showCheckbox:<?php echo $show_checkbox; ?>
					                }
					            },
					            legend: {
					                layout: 'vertical',
					                align: 'right',
					                verticalAlign: 'top',
					                x: -40,
					                y: 100,
					                floating: true,
					                borderWidth: 1,
					                backgroundColor: (Highcharts.theme && Highcharts.theme.legendBackgroundColor || '#FFFFFF'),
					                shadow: true
					            },
					           credits: {
					                enabled: <?php echo $chart_credit; ?>,
					                text:<?php echo "'".$credit_text."'"; ?>,
					                href:<?php echo "'".$credit_href."'"; ?>
					            },
					            navigation: {
					                buttonOptions: {
					                    enabled:<?php echo $navigation_buttons; ?>
					                }
					            },
					            cursor: <?php echo "'".$chart_cursor_pointer."'"; ?>,
					            events: {
					                click: function() {
					                    alert(<?php echo "'".$chart_cursor_event_text."'"; ?>);
					                }
					            },
					            series: seriesArr
					        });
					    <?php } else { ?>
					    	 Highcharts.setOptions({
				                lang: {
				                    drillUpText: <?php echo "'".$chart_drilldown_text."'"; ?>
				                }
				            });
					    	jQuery('#container_<?php echo $id; ?>').highcharts({
					            chart: {
					                type: 'bar',
					                animation: {
					                        duration:<?php echo $chart_animation_duration; ?>,
					                        easing:<?php echo "'".$chart_animation."'"; ?>
					                 },
					                backgroundColor:<?php echo "'".$chart_background_color."'"; ?>,
					                borderColor:<?php echo "'".$chart_border_color."'"; ?>,
					                borderWidth:<?php echo $chart_border_width; ?>,
					                borderRadious:<?php echo $chart_border_radious; ?>
					            },
					            title: {
					                text: <?php echo "'".$chart_title."'"; ?>
					            },
					            subtitle: {
					                text: <?php echo "'".$chart_sub_title."'"; ?>
					            },
					            xAxis: {
					                type: 'category'
					            },
					            yAxis: {
					                
					                title: {
					                    text: <?php echo "'".$chart_yAxis."'"; ?>
					                }
					            },
					            tooltip: {
					                enabled:<?php echo $chart_tooltip; ?>,
					                crosshairs:<?php echo $chart_tooltip_crosshairs; ?>,
					                valueDecimals: <?php echo $value_decimals; ?>,
					                valuePrefix: <?php echo "'".$value_prefix."'"; ?>,
					                valueSuffix: <?php echo "'".$value_suffix."'"; ?>
					            },
					            plotOptions: {
					                 series: {
					                 		stacking:<?php echo "'".$chart_series_stacking."'"; ?>,
					                        allowPointSelect:<?php echo $chart_allow_point_select; ?>,
					                        borderColor:<?php echo "'".$bar_border_color."'"; ?>,
					                        borderRadious:<?php echo $bar_border_radious; ?>,
					                        borderWidth:<?php echo $bar_border_width; ?>,
					                        negativeColor:<?php echo "'".$chart_series_negative_color."'"; ?>,
					                        pointPadding:<?php echo $column_point_padding; ?>,
					                        showCheckbox:<?php echo $show_checkbox; ?>
					                    },
					                bar: {
					                    dataLabels: {
					                        enabled: <?php echo $chart_data_labels; ?>
					                    },
					                    grouping: <?php echo "'".$columns_grouping."'"; ?>
					                }
					            },
					            credits: {
					                enabled: <?php echo $chart_credit; ?>,
					                text:<?php echo "'".$credit_text."'"; ?>,
					                href:<?php echo "'".$credit_href."'"; ?>
					            },
					            navigation: {
					                buttonOptions: {
					                    enabled:<?php echo $navigation_buttons; ?>
					                }
					            },
					            series:[{
					            name: <?php echo "'".$chart_title."'"; ?>,
					            colorByPoint: true,
					            data:seriesArr
					        	}],drilldown:{
					            	series:drilldownSeries
					            }
					        });
					<?php     } ?>
						
					<?php } else 	if($chart_type == "bubble chart"){ ?>
					    jQuery('#container_<?php echo $id; ?>').highcharts({
						    chart: {
						        type: 'bubble',
						        zoomType: 'xy',
						        animation: {
				                        duration:<?php echo $chart_animation_duration; ?>,
					                    easing:<?php echo "'".$chart_animation."'"; ?>
				                 },
				                backgroundColor:<?php echo "'".$chart_background_color."'"; ?>,
				                borderColor:<?php echo "'".$chart_border_color."'"; ?>,
				                borderWidth:<?php echo $chart_border_width; ?>,
				                borderRadious:<?php echo $chart_border_radious; ?>
						    },
				            credits: {
				                enabled:<?php echo $chart_credit; ?>,
				                text:<?php echo "'".$credit_text."'"; ?>,
				                href:<?php echo "'".$credit_href."'"; ?>
				            },
				            navigation: {
					                buttonOptions: {
					                    enabled:<?php echo $navigation_buttons; ?>
					                }
					            },
				            plotOptions:{
				                 series: {
				                        allowPointSelect:<?php echo $chart_allow_point_select; ?>,
				                        showCheckbox:<?php echo $show_checkbox; ?>,
				                        dataLabels: {
						                    enabled: <?php echo $chart_data_labels; ?>
						                },
			                            cursor: <?php echo "'".$chart_cursor_pointer."'"; ?>,
			                            events: {

			                                click: function() {

			                                    alert(<?php echo "'".$chart_cursor_event_text."'"; ?>);

			                                }

			                            }
				                    }
				            },
				             tooltip: {
				                enabled:<?php echo $chart_tooltip; ?>,
				                crosshairs:<?php echo $chart_tooltip_crosshairs; ?>,
				                valueDecimals: <?php echo $value_decimals; ?>,
				                valuePrefix: <?php echo "'".$value_prefix."'"; ?>,
				                valueSuffix: <?php echo "'".$value_suffix."'"; ?>
				            },
						    title: {
						    	text: <?php echo "'".$chart_title."'"; ?>
						    },
				             xAxis: {
				                title: {
				                    text: <?php echo "'".$chart_xAxis."'"; ?>
				                }
				            },
				            yAxis: {
				                title: {
				                    text: <?php echo "'".$chart_yAxis."'"; ?>
				                }
				            },
						    series:seriesArr
						});
					<?php } else 	if($chart_type == "column chart"){ ?>
					<?php     if($chart_drilldown == 'false'){   ?>
						    	jQuery('#container_<?php echo $id; ?>').highcharts({
					            chart: {
					                type: 'column',
					                animation: {
					                        duration:<?php echo $chart_animation_duration; ?>,
					                        easing:<?php echo "'".$chart_animation."'"; ?>
					                 },
					                backgroundColor:<?php echo "'".$chart_background_color."'"; ?>,
					                borderColor:<?php echo "'".$chart_border_color."'"; ?>,
					                borderWidth:<?php echo $chart_border_width; ?>,
					                borderRadious:<?php echo $chart_border_radious; ?>
					            },
					            title: {
					                text: <?php echo "'".$chart_title."'"; ?>
					            },
					            subtitle: {
					                text: <?php echo "'".$chart_sub_title."'"; ?>
					            },
					            xAxis: {
					                categories:<?php echo $chart_xAxisCats; ?>
					            },
					            yAxis: {
					                
					                title: {
					                    text: <?php echo "'".$chart_yAxis."'"; ?>
					                }
					            },
					             tooltip: {
					                enabled:<?php echo $chart_tooltip; ?>,
					                crosshairs:<?php echo $chart_tooltip_crosshairs; ?>,
					                valueDecimals: <?php echo $value_decimals; ?>,
					                valuePrefix: <?php echo "'".$value_prefix."'"; ?>,
					                valueSuffix: <?php echo "'".$value_suffix."'"; ?>,
					            },
					            credits: {
					                enabled:<?php echo $chart_credit; ?>,
					                text:<?php echo "'".$credit_text."'"; ?>,
					                href:<?php echo "'".$credit_href."'"; ?>
					            },
					            navigation: {
					                buttonOptions: {
					                    enabled:<?php echo $navigation_buttons; ?>
					                }
					            },
					            plotOptions: {
					                 series: {
					                        stacking:<?php echo "'".$chart_series_stacking."'"; ?>,
					                        allowPointSelect:<?php echo $chart_allow_point_select; ?>,
					                        borderColor:<?php echo "'".$bar_border_color."'"; ?>,
					                        borderRadious:<?php echo $bar_border_radious; ?>,
					                        borderWidth:<?php echo $bar_border_width; ?>,
					                        negativeColor:<?php echo "'".$chart_series_negative_color."'"; ?>,
					                        pointPadding:<?php echo $column_point_padding; ?>,
					                        showCheckbox:<?php echo $show_checkbox; ?>,
					                        dataLabels: {
							                    enabled: <?php echo $chart_data_labels; ?>
							                },
				                            cursor: <?php echo "'".$chart_cursor_pointer."'"; ?>,
				                            events: {

				                                click: function() {

				                                    alert(<?php echo "'".$chart_cursor_event_text."'"; ?>);

				                                }

				                            }
					                    },
					                column: {
					                    pointPadding: <?php echo $column_point_padding; ?>,
					                    borderWidth: <?php echo $bar_border_width; ?>,
					                    borderRadious:<?php echo $bar_border_radious; ?>,
					                    borderColor:<?php echo "'".$bar_border_color."'"; ?>,
					                    grouping:columns_grouping
					                }
					            },
					            series:seriesArr
					        });
					   <?php  } else { ?>
					    		Highcharts.setOptions({
				                lang: {
					                    drillUpText: <?php echo "'".$chart_drilldown_text."'"; ?>
					                }
					            });
						    	jQuery('#container_<?php echo $id; ?>').highcharts({
					            chart: {
					                type: 'column',
					                animation: {
					                        duration:<?php echo $chart_animation_duration; ?>,
					                        easing:<?php echo "'".$chart_animation."'"; ?>
					                 },
					                backgroundColor:<?php echo "'".$chart_background_color ."'"; ?>,
					                borderColor:<?php echo "'".$chart_border_color ."'"; ?>,
					                borderWidth:<?php echo $chart_border_width; ?>,
					                borderRadious:<?php echo $chart_border_radious; ?>
					            },
					            title: {
					                text: <?php echo "'".$chart_title ."'"; ?>
					            },
					            subtitle: {
					                text: <?php echo "'".$chart_sub_title ."'"; ?>
					            },
					            xAxis: {
					                type: 'category'
					            },
					            yAxis: {
					                
					                title: {
					                    text: <?php echo "'".$chart_yAxis ."'"; ?>
					                }
					            },
					             tooltip: {
					                enabled:<?php echo $chart_tooltip; ?>,
					                crosshairs:<?php echo $chart_tooltip_crosshairs; ?>,
					                valueDecimals: <?php echo $value_decimals; ?>,
					                valuePrefix: <?php echo "'".$value_prefix."'"; ?>,
					                valueSuffix: <?php echo "'".$value_suffix."'"; ?>,
					            },
					            credits: {
					                enabled:<?php echo $chart_credit; ?>,
					                text:<?php echo "'".$credit_text."'"; ?>,
					                href:<?php echo "'".$credit_href."'"; ?>
					            },
					            navigation: {
					                buttonOptions: {
					                    enabled:<?php echo $navigation_buttons; ?>
					                }
					            },
					            plotOptions: {
					                 series: {
					                 		stacking:<?php echo "'".$chart_series_stacking."'"; ?>,
					                        allowPointSelect:<?php echo $chart_allow_point_select; ?>,
					                        borderColor:<?php echo "'".$bar_border_color."'"; ?>,
					                        borderRadious:<?php echo $bar_border_radious; ?>,
					                        borderWidth:<?php echo $bar_border_width; ?>,
					                        negativeColor:<?php echo "'".$chart_series_negative_color."'"; ?>,
					                        pointPadding:<?php echo $column_point_padding; ?>,
					                        showCheckbox:<?php echo $show_checkbox; ?>,
					                        dataLabels: {
							                    enabled: <?php echo $chart_data_labels; ?>
							                },
						                    cursor: <?php echo "'".$chart_cursor_pointer."'"; ?>,
						                    events: {
						                        click: function() {
						                            alert(<?php echo "'".$chart_cursor_event_text."'"; ?>);
						                        }
						                    }
					                    },
					                column: {
					                    pointPadding: <?php echo $column_point_padding; ?>,
					                    borderWidth: <?php echo $bar_border_width; ?>,
					                    borderRadious:<?php echo $bar_border_radious; ?>,
					                    borderColor:<?php echo "'".$bar_border_color."'"; ?>,
					                    grouping: <?php echo "'".$columns_grouping ."'"; ?>
					                }
					            },
					            series:[{
					            name: <?php echo "'".$chart_title ."'"; ?>,
					            colorByPoint: true,
					            data:seriesArr
					        	}],drilldown:{
					            	series:drilldownSeries
					            }
					        });
					<?php     }  ?>
					<?php } else	if($chart_type == "line chart"){ ?>
						        jQuery('#container_<?php echo $id; ?>').highcharts({
						            chart:{
						                type:'line',
						                animation: {
					                        duration:<?php echo $chart_animation_duration; ?>,
					                        easing:<?php echo "'".$chart_animation."'"; ?>
					                	 },
						                backgroundColor:<?php echo "'".$chart_background_color."'"; ?>,
						                borderColor:<?php echo "'".$chart_border_color."'"; ?>,
						                borderWidth:<?php echo $chart_border_width; ?>,
						                borderRadious:<?php echo $chart_border_radious; ?>
						            },
						            title: {
						                text: <?php echo "'".$chart_title."'"; ?>,
						                x: -20 //center
						            },
						            subtitle: {
						                text: <?php echo "'".$chart_sub_title."'"; ?>,
						                x: -20
						            },
						            xAxis: {
						                categories:<?php echo $chart_xAxisCats; ?>
						            },
						            yAxis: {
						                title: {
						                    text: <?php echo "'".$chart_yAxis."'"; ?>
						                },
						                plotLines: [{
						                    value: 0,
						                    width: 1,
						                    color: '#808080'
						                }]
						            },
						             tooltip: {
						                enabled:<?php echo $chart_tooltip; ?>,
						                crosshairs:<?php echo $chart_tooltip_crosshairs; ?>,
						                valueDecimals:<?php echo $value_decimals; ?>,
						                valuePrefix: <?php echo "'".$value_prefix."'"; ?>,
						                valueSuffix: <?php echo "'".$value_suffix."'"; ?>,
						            },
						            credits: {
						                enabled: <?php echo $chart_credit; ?>,
						                text:<?php echo "'".$credit_text."'"; ?>,
						                href:<?php echo "'".$credit_href."'"; ?>
						            },
						            navigation: {
						                buttonOptions: {
						                    enabled:<?php echo $navigation_buttons; ?>
						                }
						            },
						            legend: {
						                layout: 'vertical',
						                align: 'right',
						                verticalAlign: 'middle',
						                borderWidth: 0
						            },
						            plotOptions:{
						                 series: {
						                        allowPointSelect:<?php echo $chart_allow_point_select; ?>,
						                        showCheckbox:<?php echo $show_checkbox; ?>,
						                        dataLabels: {
								                    enabled: <?php echo $chart_data_labels; ?>
								                },
					                            cursor: <?php echo "'".$chart_cursor_pointer."'"; ?>,
					                            events: {

					                                click: function() {

					                                    alert(<?php echo "'".$chart_cursor_event_text."'"; ?>);

					                                }

					                            }
						                    }
						            },
						            series: seriesArr
						        });
					<?php } else 	if($chart_type == "pie chart"){ ?>
					 <?php    if($chart_drilldown == 'false'){   ?>
						    	jQuery('#container_<?php echo $id; ?>').highcharts({
						        chart: {
						        	type:'pie',
						        	animation: {
					                        duration:<?php echo $chart_animation_duration; ?>,
					                        easing:<?php echo "'".$chart_animation."'"; ?>
					                 },
						            plotBackgroundColor: null,
						            plotBorderWidth: null,
						            plotShadow: false,
				                    backgroundColor:<?php echo "'".$chart_background_color."'"; ?>,
				                    borderColor:<?php echo "'".$chart_border_color."'"; ?>,
				                    borderWidth:<?php echo $chart_border_width; ?>,
				                    borderRadious:<?php echo $chart_border_radious; ?>
						        },
						        title: {
						            text: <?php echo "'".$chart_title."'"; ?>
						        },
				                 tooltip: {
				                    enabled:<?php echo $chart_tooltip; ?>,
				                    crosshairs:<?php echo $chart_tooltip_crosshairs; ?>,
				                    valueDecimals: <?php echo $value_decimals; ?>,
				                    valuePrefix: <?php echo "'".$value_prefix."'"; ?>,
				                    valueSuffix: <?php echo "'".$value_suffix."'"; ?>,
				                },
				                credits: {
				                    enabled:<?php echo $chart_credit; ?>,
				                    text:<?php echo "'".$credit_text."'"; ?>,
				                    href:<?php echo "'".$credit_href."'"; ?>
				                },
				                navigation: {
					                buttonOptions: {
					                    enabled:<?php echo $navigation_buttons; ?>
					                }
					            },
						        plotOptions: {
						            pie: {
						                cursor: <?php echo "'".$chart_cursor_pointer."'"; ?>,
				                        startAngle: <?php echo $chart_start_engle; ?>,
				                        endAngle:<?php echo $chart_end_engle; ?>,
				                        innerSize:<?php echo $chart_inner_size; ?>,
				                        slicedOffset:<?php echo $pie_sliced_offset; ?>,
						                dataLabels: {
						                    enabled: <?php echo $chart_data_labels; ?>,
						                    format: '<b>{point.name}</b>: {point.percentage:.1f} %',
						                    style: {
						                        color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
						                    }
						                }
						            },
				                     series: {
				                        allowPointSelect:<?php echo $chart_allow_point_select; ?>,
				                        showCheckbox:<?php echo $show_checkbox; ?>,
					                    cursor: <?php echo "'".$chart_cursor_pointer."'"; ?>,
					                    events: {
					                        click: function() {
					                            alert(<?php echo "'".$chart_cursor_event_text."'"; ?>);
					                        }
					                    }
				                    }
						        },
						        series: [{
						            type: 'pie',
						            name: <?php echo "'".$chart_title."'"; ?>,
						            data: seriesArr,
						            showInLegend:pie_legend
						        }]
						    });
					    <?php } else { ?>
					    	Highcharts.setOptions({
			                lang: {
				                    drillUpText: <?php echo "'".$chart_drilldown_text."'"; ?>
				                }
				            });
					    	jQuery('#container_<?php echo $id; ?>').highcharts({
						        chart: {
						        	type:'pie',
						        	animation: {
					                        duration:<?php echo $chart_animation_duration; ?>,
					                        easing:<?php echo "'".$chart_animation."'"; ?>
					                 },
						            plotBackgroundColor: null,
						            plotBorderWidth: null,
						            plotShadow: false,
				                    backgroundColor:<?php echo "'".$chart_background_color."'"; ?>,
				                    borderColor:<?php echo "'".$chart_border_color."'"; ?>,
				                    borderWidth:<?php echo $chart_border_width; ?>,
				                    borderRadious:<?php echo $chart_border_radious; ?>
						        },
						        title: {
						            text: <?php echo "'".$chart_title."'"; ?>
						        },
				                 tooltip: {
				                    enabled:<?php echo $chart_tooltip; ?>,
				                    crosshairs:<?php echo $chart_tooltip_crosshairs; ?>,
				                    valueDecimals: <?php echo $value_decimals; ?>,
				                    valuePrefix: <?php echo "'".$value_prefix."'"; ?>,
				                    valueSuffix: <?php echo "'".$value_suffix."'"; ?>,
				                },
				                credits: {
				                    enabled:<?php echo $chart_credit; ?>,
				                    text:<?php echo "'".$credit_text."'"; ?>,
				                    href:<?php echo "'".$credit_href."'"; ?>
				                },
				                navigation: {
					                buttonOptions: {
					                    enabled:<?php echo $navigation_buttons; ?>
					                }
					            },
						        plotOptions: {
						            pie: {
						                cursor: <?php echo "'".$chart_cursor_pointer."'"; ?>,
				                        startAngle: <?php echo $chart_start_engle; ?>,
				                        endAngle:<?php echo $chart_end_engle; ?>,
				                        innerSize:<?php echo $chart_inner_size; ?>,
				                        slicedOffset:<?php echo $pie_sliced_offset; ?>,
						                dataLabels: {
						                    enabled: <?php echo $chart_data_labels; ?>,
						                    format: '<b>{point.name}</b>: {point.percentage:.1f} %',
						                    style: {
						                        color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
						                    }
						                }
						            },
				                     series: {
				                        allowPointSelect:<?php echo $chart_allow_point_select; ?>,
				                        showCheckbox:<?php echo $show_checkbox; ?>
				                    }
						        },
						        series: [{
						            type: 'pie',
						            name: <?php echo "'".$chart_title."'"; ?>,
						            data: seriesArr,
						            showInLegend:pie_legend
						        }],
				                drilldown: {
				                    series: drilldownSeries
				                }

						    });
					<?php    }  ?>

					<?php } else	if($chart_type == "scatter chart"){ ?>
							jQuery('#container_<?php echo $id; ?>').highcharts({
						        chart: {
						            type: 'scatter',
						            zoomType: 'xy',
						            animation: {
					                        duration:<?php echo $chart_animation_duration; ?>,
					                        easing:<?php echo "'".$chart_animation."'"; ?>
					                 },
					                backgroundColor:<?php echo "'".$chart_background_colo."'"; ?>,
					                borderColor:<?php echo "'".$chart_border_color."'"; ?>,
					                borderWidth:<?php echo $chart_border_width; ?>,
					                borderRadious:<?php echo $chart_border_radious; ?>
						        },
						        title: {
						            text:<?php echo "'".$chart_title."'"; ?> 
						        },
						        subtitle: {
						            text: <?php echo "'".$chart_sub_title."'"; ?>
						        },
						        xAxis: {
						            title: {
						                enabled: true,
						                text: <?php echo "'".$chart_xAxis."'"; ?>
						            },
						            startOnTick: true,
						            endOnTick: true,
						            showLastLabel: true
						        },
						        yAxis: {
						            title: {
						                text: <?php echo "'".$chart_yAxis."'"; ?>
						            }
						        },
						        legend: {
						            layout: 'vertical',
						            align: 'left',
						            verticalAlign: 'top',
						            x: 100,
						            y: 70,
						            floating: true,
						            backgroundColor: (Highcharts.theme && Highcharts.theme.legendBackgroundColor) || '#FFFFFF',
						            borderWidth: 1
						        },
					            credits: {
					                enabled: <?php echo $chart_credit; ?>,
					                text:<?php echo "'".$credit_text."'"; ?>,
					                href:<?php echo "'".$credit_href."'"; ?>
					            },
					            navigation: {
					                buttonOptions: {
					                    enabled:<?php echo $navigation_buttons; ?>
					                }
					            },
					            tooltip: {
					                enabled:<?php echo $chart_tooltip; ?>,
					                crosshairs:<?php echo $chart_tooltip_crosshairs; ?>,
					                valueDecimals: <?php echo $value_decimals; ?>,
					                valuePrefix: <?php echo "'".$value_prefix."'"; ?>,
					                valueSuffix: <?php echo "'".$value_suffix."'"; ?>
					            },
						        plotOptions: {
					                 series: {
					                        allowPointSelect:<?php echo $chart_allow_point_select; ?>,
					                        showCheckbox:<?php echo $show_checkbox; ?>,
					                        dataLabels: {
							                    enabled: <?php echo $chart_data_labels; ?>
							                },
						                    cursor: <?php echo "'".$chart_cursor_pointer."'"; ?>,
						                    events: {
						                        click: function() {
						                            alert(<?php echo "'".$chart_cursor_event_text."'"; ?>);
						                        }
						                    }
					                    },
						            scatter: {
						                marker: {
						                    radius: 5,
						                    states: {
						                        hover: {
						                            enabled: true,
						                            lineColor: 'rgb(100,100,100)'
						                        }
						                    }
						                },
						                states: {
						                    hover: {
						                        marker: {
						                            enabled: <?php echo $chart_allow_point_select; ?>
						                        }
						                    }
						                }
						            }
						        },
						        series: seriesArr
						    });
					<?php } else 	if(chart_type == "spark chart"){ ?>
						var seriesArr = [];
			            var theData = <?php echo $chart_data; ?>;
			            var theXCats = $.extend(true, [], theData[0]);
	                    theXCats = theXCats.splice(0,theXCats.length-1);
	                    for(i in theXCats){
	                    	jQuery("#thead-sparkline_<?php echo $id; ?>  tr") .append('<td>'+theXCats[i]+'</td>');
	                    }			        
				        var theNewData = [];
				        var buildNewData = $.map(theData, function(item, i) {
				            if (i > 0 && i < theData.length-1) {
				                theNewData.push(item);
				            }
				        });
				        var theYCats = [];
				        var buildYCats = $.map(theNewData, function(item, i) {
				            theYCats.push(item[0]);
				        });
				        var theYLabels = [],
				            theYData = [],
				            str = '';
				        var buildYData = $.map(theNewData, function(item, i) {
				            theYLabels.push(item[0]);
				            $.each(item, function(x, xitem) {
				                if (x < theNewData[0].length-1) {
				                	if(x===0){ str= ''; str = '<th>'+xitem+'</th>'; }
				                	if(x===1){ str = str+'<td>'+parseFloat(xitem)+'</td>'; }
				                	if(x===2){ str = str+'<td data-sparkline="'+xitem+'"/>'; }
				                	if(x===3){ str = str+'<td>'+parseFloat(xitem)+'</td>'; }
				                	if(x===4){ str = str+'<td data-sparkline="'+xitem+'"/>'; }
				                	if(x===5){ str = str+'<td>'+parseFloat(xitem)+'</td>'; }
				                	if(x===6){ str = str+'<td data-sparkline="'+xitem+'"/>'; }
				               		if (x === 6) { jQuery("#tbody-sparkline_<?php echo $id; ?>").append('<tr>'+str+'</tr>'); }
				                }
				            
				            });
				        });
				        $.each(theYLabels, function(i, item) {
				            seriesArr.push({name:item,data:theYData[i]});
				        });
						Highcharts.SparkLine = function (options, callback) {
					        var defaultOptions = {
					            chart: {
					                renderTo: (options.chart && options.chart.renderTo) || this,
					                backgroundColor: null,
					                borderWidth: 0,
					                type: 'area',
					                animation: {
					                        duration:<?php echo $chart_animation_duration; ?>,
					                        easing:<?php echo "'".$chart_animation."'"; ?>
					                 },
					                margin: [2, 0, 2, 0],
					                width: 120,
					                height: 20,
					                style: {
					                    overflow: 'visible'
					                },
					                skipClone: true
					            },
					            title: {
					                text: ''
					            },
					            credits: {
					                enabled: false
					            },
					            navigation: {
					                buttonOptions: {
					                    enabled:<?php echo $navigation_buttons; ?>
					                }
					            },
					            xAxis: {
					                labels: {
					                    enabled: false
					                },
					                title: {
					                    text: null
					                },
					                startOnTick: false,
					                endOnTick: false,
					                tickPositions: []
					            },
					            yAxis: {
					                endOnTick: false,
					                startOnTick: false,
					                labels: {
					                    enabled: false
					                },
					                title: {
					                    text: null
					                },
					                tickPositions: [0]
					            },
					            legend: {
					                enabled: false
					            },
					            tooltip: {
					                backgroundColor: null,
					                borderWidth: 0,
					                shadow: false,
					                useHTML: true,
					                hideDelay: 0,
					                shared: true,
					                padding: 0,
					                positioner: function (w, h, point) {
					                    return { x: point.plotX - w / 2, y: point.plotY - h};
					                }
					            },
					            plotOptions: {
					            	 series: {
				                        allowPointSelect:<?php echo $chart_allow_point_select; ?>
				                    },
					                series: {
					                    animation: false,
					                    lineWidth: 1,
					                    shadow: false,
					                    states: {
					                        hover: {
					                            lineWidth: 1
					                        }
					                    },
					                    marker: {
					                       radius: 1,
					                        states: {
					                            hover: {
					                                radius: 2
					                            }
					                        }
					                    },
					                    fillOpacity: 0.25
					                },
					                column: {
					                    negativeColor: '#910000',
					                    borderColor: 'silver'
					                }
					            }
					        };
					        options = Highcharts.merge(defaultOptions, options);
					        return new Highcharts.Chart(options, callback);
					    };
					    var start = +new Date(),
					        $tds = jQuery("td[data-sparkline]"),
					        fullLen = $tds.length,
					        n = 0;

					    function doChunk() {
					        var time = +new Date(),
					            i,
					            len = $tds.length;
					        for (i = 0; i < len; i++) {
					            var $td = $($tds[i]),
					                stringdata = $td.data('sparkline'),
					                arr = stringdata.split('; '),
					                data = $.map(arr[0].split(', '), parseFloat),
					                chart = {};
					            if (arr[1]) {
					                chart.type = arr[1];
					            }
					            $td.highcharts('SparkLine', {
					                series: [{
					                    data: data,
					                    pointStart: 1
					                }],
					                tooltip: {
					                    headerFormat: '<span style="font-size: 10px">' + $td.parent().find('th').html() + ', Q{point.x}:</span><br/>',
					                    pointFormat: '<b>{point.y}.000</b> USD'
					                },
					                chart: chart
					            });
					            n++;			          
					            if (new Date() - time > 500) {
					               $tds.splice(0, i + 1);
					                setTimeout(doChunk, 0);
					                break;
					            }
					            if (n === fullLen) {
					                jQuery('#result_<?php echo $id; ?>').html('Generated ' + fullLen + ' sparklines in ' + (new Date() - start) + ' ms');
					            }
					        }
					    }
					    doChunk();
					<?php } else	if($chart_type == "spline chart"){ ?>
						jQuery('#container_<?php echo $id; ?>').highcharts({
					        chart: {
					            type: 'spline',
					            animation: {
				                        duration:<?php echo $chart_animation_duration; ?>,
					                    easing:<?php echo "'".$chart_animation."'"; ?>
				                 },
				                backgroundColor:<?php echo "'".$chart_background_color."'"; ?>,
				                borderColor:<?php echo "'".$chart_border_color."'"; ?>,
				                borderWidth:<?php echo $chart_border_width; ?>,
				                borderRadious:<?php echo $chart_border_radious; ?>
					        },
					        title: {
					            text: <?php echo "'".$chart_title."'" ?>
					        },
					        subtitle: {
					            text: <?php echo "'".$chart_sub_title."'"; ?>
					        },
					        xAxis: {
					            categories: <?php echo $chart_xAxisCats; ?>
					        },
					        yAxis: {
					            title: {
					                text: <?php echo "'".$chart_yAxis."'"; ?>
					            },
					            labels: {
					                formatter: function() {
					                    return this.value +'°'
					                }
					            }
					        },
					        tooltip: {
				                    enabled:<?php echo $chart_tooltip; ?>,
				                    crosshairs:<?php echo $chart_tooltip_crosshairs; ?>,
				                    valueDecimals: <?php echo $value_decimals; ?>,
				                    valuePrefix: <?php echo "'".$value_prefix."'"; ?>,
				                    valueSuffix: <?php echo "'".$value_suffix."'"; ?>,
				                },
				            credits: {
				                enabled: <?php echo $chart_credit; ?>,
				                text:<?php echo "'".$credit_text."'"; ?>,
				                href:<?php echo "'".$credit_href."'"; ?>
				            },
				            navigation: {
				                buttonOptions: {
				                    enabled:<?php echo $navigation_buttons; ?>
				                }
				            },
					        plotOptions: {
				                 series: {
				                        allowPointSelect:<?php echo $chart_allow_point_select; ?>,
				                        showCheckbox:<?php echo $show_checkbox; ?>,
				                        dataLabels: {
						                    enabled: <?php echo $chart_data_labels; ?>
						                },
			                            cursor: <?php echo "'".$chart_cursor_pointer."'"; ?>,
			                            events: {

			                                click: function() {

			                                    alert(<?php echo "'".$chart_cursor_event_text."'"; ?>);

			                                }

			                            }

				                    },
					            spline: {
					                marker: {
					                    radius: 4,
					                    lineColor: '#666666',
					                    lineWidth: 1
					                }
					            }
					        },
					        series:seriesArr 
					    });
					<?php } else if($chart_type == "waterfall chart"){ ?>
						jQuery('#container_<?php echo $id; ?>').highcharts({
				        chart: {
				            type: 'waterfall',
				            animation: {
			                        duration:<?php echo $chart_animation_duration; ?>,
					                easing:<?php echo "'".$chart_animation."'"; ?>
			                 },
			                backgroundColor:<?php echo "'".$chart_background_color."'"; ?>,
			                borderColor:<?php echo "'".$chart_border_color."'"; ?>,
			                borderWidth:<?php echo $chart_border_width; ?>,
			                borderRadious:<?php echo $chart_border_radious; ?>
				        },
				        title: {
				            text: <?php echo "'".$chart_title."'"; ?>
				        },
				        xAxis: {
				            type: 'category'
				        },
				        yAxis: {
				            title: {
				                text:<?php echo "'".$chart_yAxis."'"; ?>
				            }
				        },
				        legend: {
				            enabled: false
				        },
				        tooltip: {
			                enabled:<?php echo $chart_tooltip; ?>,
			                crosshairs:<?php echo $chart_tooltip_crosshairs; ?>,
			                valueDecimals:<?php echo $value_decimals; ?>,
			                valuePrefix:<?php echo "'".$value_prefix."'"; ?>,
			                valueSuffix:<?php echo "'".$value_suffix."'"; ?>,
			            },
			            credits: {
			                enabled:<?php echo $chart_credit; ?>,
			                text:<?php echo "'".$credit_text."'"; ?>,
			                href:<?php echo "'".$credit_href."'"; ?>
			            },
			            navigation: {
			                buttonOptions: {
			                    enabled:<?php echo $navigation_buttons; ?>
			                }
			            },
			            plotOptions:{
			                 series: {
			                        allowPointSelect:<?php echo $chart_allow_point_select; ?>,
			                        showCheckbox:<?php echo $show_checkbox; ?>,
		                            cursor: <?php echo "'".$chart_cursor_pointer."'"; ?>,
		                            events: {

		                                click: function() {

		                                    alert(<?php echo "'".$chart_cursor_event_text."'"; ?>);

		                                }

		                            }
			                    }
			            },
				        series: [{
				            upColor: Highcharts.getOptions().colors[2],
				            color: Highcharts.getOptions().colors[3],
				            data: seriesArr,
				            dataLabels: {
				                enabled: <?php echo $chart_data_labels; ?>,
				                formatter: function () {
				                    return Highcharts.numberFormat(this.y / 1000, 0, ',') + 'k';
				                },
				                style: {
				                    color: '#FFFFFF',
				                    fontWeight: 'bold',
				                    textShadow: '0px 0px 3px black'
				                }
				            },
				            pointPadding: <?php echo $column_point_padding; ?>,
		                    cursor: <?php echo "'".$chart_cursor_pointer."'"; ?>,
		                    events: {
		                        click: function() {
		                            alert(<?php echo "'".$chart_cursor_event_text."'"; ?>);
		                        }
		                    }
				        }]
				    });
				<?php 	} ?>
			    });
	    	</script>
			<?php
			
		} else {
			echo "Chart with the given shortcode does not exist !";
		}
		$buffer = ob_get_contents();
		ob_end_clean();
		return $buffer;
	}
  }

}
new WP_High_Charts();



