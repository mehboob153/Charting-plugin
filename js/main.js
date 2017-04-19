function update_highchart(chart_type){

    var chart_background_color          = jQuery("#chart_background_color").val();

    var chart_border_color              = jQuery("#chart_border_color").val();

    var chart_border_width              = jQuery("#chart_border_width").val();

    var chart_border_radious            = jQuery("#chart_border_radious").val();

    var chart_allow_point_select        = jQuery("#chart_allow_point_select option:selected").val();

    var chart_animation                 = jQuery("#chart_animation").val();

    var chart_animation_duration        = parseInt(jQuery("#chart_animation_duration").val());

    var chart_point_brightness          = jQuery("#chart_point_brightness").val();

    var bar_border_color                = jQuery("#bar_border_color").val();

    var bar_border_width                = jQuery("#bar_border_width").val();

    var bar_border_radious              = jQuery("#bar_border_radious").val();

    var chart_series_generl_color       = jQuery("#chart_series_generl_color").val();

    var chart_series_color              = jQuery("#chart_series_color").val();

    var chart_cursor_pointer            = jQuery("#chart_cursor_pointer").val();

    var chart_cursor_event_text         = jQuery("#chart_cursor_event_text").val();

    var chart_series_negative_color     = jQuery("#chart_series_negative_color").val();

    var column_point_padding            = jQuery("#column_point_padding").val();

    var chart_series_legend             = jQuery("#chart_series_legend").val();

    var chart_series_stacking           = jQuery("#chart_series_stacking").val();

    var intial_visibility               = jQuery("#intial_visibility").val();

    var chart_start_engle               = jQuery("#chart_start_engle").val();

    var chart_end_engle                 = jQuery("#chart_end_engle").val();

    var chart_inner_size                = jQuery("#chart_inner_size").val();

    var show_checkbox                   = jQuery("#show_checkbox").val();

    var check_box_series                = jQuery("#check_box_series").val();

    var pie_sliced                      = jQuery("#pie_sliced").val();
    var pie_legend                      = jQuery("#pie_legend").val();

    var pie_sliced_offset               = jQuery("#pie_sliced_offset").val();

    var columns_grouping                = jQuery("#columns_grouping option:selected").val();

    var chart_data_labels               = jQuery("#chart_data_labels").val();

    var chart_tooltip                   = jQuery("#chart_tooltip option:selected").val();

    var chart_tooltip_crosshairs        = jQuery("#chart_tooltip_crosshairs").val();

    var chart_credit                    = jQuery("#chart_credit option:selected").val();

    var credit_text                     = jQuery("#credit_text").val();

    var credit_href                     = jQuery("#credit_href").val();

    var navigation_buttons              = jQuery("#navigation_buttons option:selected").val();

    var value_decimals                  = jQuery("#value_decimals").val();

    var value_prefix                    = jQuery("#value_prefix").val();

    var value_suffix                    = jQuery("#value_suffix").val();

    var chart_drilldown                 = jQuery("#chart_drilldown").val();

    var chart_drilldown_type            = jQuery("#chart_drilldown_type").val();

    var chart_drilldown_text            = jQuery("#chart_drilldown_text").val();



    if(chart_allow_point_select == 'true'){ chart_allow_point_select = true; } else { chart_allow_point_select = false; }

    if(show_checkbox == 'true'){ show_checkbox = true; } else { show_checkbox = false; }

    if(columns_grouping == 'true'){ columns_grouping = true; } else { columns_grouping = false; }

    if(chart_credit == 'true'){ chart_credit = true; } else { chart_credit = false; }

    if(navigation_buttons == 'true'){ navigation_buttons = true; } else { navigation_buttons = false; }

    if(chart_data_labels == 'true'){ chart_data_labels = true; } else { chart_data_labels = false; }

    if(chart_tooltip == 'true'){ chart_tooltip = true; } else { chart_tooltip = false; }
    if(pie_legend == 'true'){ pie_legend = true; } else { pie_legend = false; }



	if(chart_type == "area chart"){

		var chart_title 	= jQuery("#chart_title").val(); 

		var chart_sub_title = jQuery("#chart_sub_title").val();

		var chart_xAxis 	= jQuery("#chart_xAxis").val();

		var chart_yAxis 	= jQuery("#chart_yAxis").val();

		var chart_shortcode =  jQuery("#chart_shortcode").val();

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

                backgroundColor:chart_background_color,

                borderColor:chart_border_color,

                borderWidth:chart_border_width,

                borderRadious:chart_border_radious,

                color:chart_series_generl_color,

                allowPointSelect:chart_allow_point_select,

                negativeColor:chart_series_negative_color,

                pointPadding:column_point_padding,

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

                    borderWidth:bar_border_width,

                    stacking:chart_series_stacking

                },
                animation: {

                    duration: chart_animation_duration,

                    easing: chart_animation

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

		var chart_title 	= jQuery("#chart_title").val(); 

		var chart_sub_title = jQuery("#chart_sub_title").val();

		var chart_xAxis 	= jQuery("#chart_xAxis").val();

		var chart_yAxis 	= jQuery("#chart_yAxis").val();

		var chart_shortcode =  jQuery("#chart_shortcode").val();

        if(chart_drilldown == 'true'){

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

                                 duration: chart_animation_duration
                                 //,easing: chart_animation

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
                            grouping: columns_grouping,

                        }

                    },

                    series:[{

                    name: chart_title,

                    colorByPoint: true,

                    data:seriesArr

                    }],drilldown:{

                        series:drilldownSeries

                    }

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

                    seriesArr.push({name:item,data:theYData[i],color:chart_series_color_array[i],

                        showInLegend:chart_series_legend_array[i],visible:intial_visibility_array[i],

                        selected:check_box_series_array[i]

                    });

                });

                 jQuery('#container').highcharts({

                    chart: {

                        type: 'bar',

                         animation: {

                                 duration: chart_animation_duration,
                                 easing: chart_animation

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

                        bar: {

                            stacking:chart_series_stacking,
                            dataLabels: {

                                enabled: chart_data_labels

                            },
                            cursor: chart_cursor_pointer,
                            events: {

                                click: function() {

                                    alert(chart_cursor_event_text);

                                }

                            },
                            grouping: columns_grouping,
                            allowPointSelect:chart_allow_point_select,
                            borderColor:bar_border_color,
                            borderRadious:bar_border_radious,
                            borderWidth:bar_border_width,
                            negativeColor:chart_series_negative_color,
                            pointPadding:column_point_padding,
                            showCheckbox:show_checkbox

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

			var chart_title 	= jQuery("#chart_title").val(); 

			var chart_sub_title = jQuery("#chart_sub_title").val();

			var chart_xAxis 	= jQuery("#chart_xAxis").val();

			var chart_yAxis 	= jQuery("#chart_yAxis").val();

			var chart_shortcode =  jQuery("#chart_shortcode").val();

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

                         duration: chart_animation_duration,
                         easing: chart_animation

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

                        dataLabels: {

                            enabled: chart_data_labels

                        },
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

		var chart_title 	= jQuery("#chart_title").val(); 

		var chart_sub_title = jQuery("#chart_sub_title").val();

		var chart_xAxis 	= jQuery("#chart_xAxis").val();

		var chart_yAxis 	= jQuery("#chart_yAxis").val();

		var chart_shortcode =  jQuery("#chart_shortcode").val();

        if(chart_drilldown == 'true'){

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

                                 duration: chart_animation_duration
                                 //,easing: chart_animation

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
                            grouping:columns_grouping

                        }

                    },

                    series:[{

                    name: chart_title,

                    colorByPoint: true,

                    data:seriesArr

                    }],drilldown:{

                        series:drilldownSeries

                    }

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

                    seriesArr.push({name:item,data:theYData[i],color:chart_series_color_array[i],

                        showInLegend:chart_series_legend_array[i],visible:intial_visibility_array[i],

                        selected:check_box_series_array[i]});

                });

                 jQuery('#container').highcharts({

                    chart: {

                        type: 'column',

                        animation: {

                                 duration: chart_animation_duration,
                                 easing: chart_animation

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
                            grouping:columns_grouping

                        }

                    },

                    series:seriesArr

                });

            }

	}

	if(chart_type == "line chart"){

		var chart_title 	= jQuery("#chart_title").val(); 

		var chart_sub_title = jQuery("#chart_sub_title").val();

		var chart_xAxis 	= jQuery("#chart_xAxis").val();

		var chart_yAxis 	= jQuery("#chart_yAxis").val();

		var chart_shortcode =  jQuery("#chart_shortcode").val();

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

                         duration: chart_animation_duration,
                         easing: chart_animation

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

                        dataLabels: {

                            enabled: chart_data_labels

                        },
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

			var chart_title 	= jQuery("#chart_title").val(); 

			var chart_sub_title = jQuery("#chart_sub_title").val();

			var chart_xAxis 	= jQuery("#chart_xAxis").val();

			var chart_yAxis 	= jQuery("#chart_yAxis").val();

			var chart_shortcode =  jQuery("#chart_shortcode").val();

            if(chart_drilldown == 'true'){

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

                seriesArr.push({name:theYLabels[0],y:theYData[0][0],drilldown:theYLabels[0],selected:true,sliced:sliced,color:chart_series_color_array[0]});

                $.each(theYLabels, function(i, item) {

                    if(i > 0){

                         seriesArr.push({name:item,y:theYData[i][0],drilldown:item,color:chart_series_color_array[i]});

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

                            slicedOffset: pie_sliced_offset,

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
                        showInLegend: pie_legend

                    }],

                    drilldown: {

                        series: drilldownSeries

                    }



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
                
                if(pie_sliced =="true"){ var sliced = true; } else { var sliced = false; }

                seriesArr.push({name:theYLabels[0],y:theYData[0][0],selected:true,sliced:sliced});

                $.each(theYLabels, function(i, item) {

                    seriesArr.push([item,theYData[i][0]]);

                });

                 jQuery('#container').highcharts({

                    chart: {

                        type:'pie',

                        animation: {

                                 duration: chart_animation_duration,
                                easing: chart_animation

                         },

                        plotBackgroundColor: null,

                        plotBorderWidth: null,

                        plotShadow: false,

                        backgroundColor:chart_background_color,

                        borderColor:chart_border_color,

                        borderWidth:chart_border_width,

                        borderRadious:chart_border_radious

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

                        showInLegend: pie_legend

                    }]



                });

            }

	}

	if(chart_type == "scatter chart"){

			var chart_title 	 = jQuery("#chart_title").val(); 

			var chart_sub_title  = jQuery("#chart_sub_title").val();

			var chart_xAxis 	 = jQuery("#chart_xAxis").val();

			var chart_yAxis 	 = jQuery("#chart_yAxis").val();

			var chart_shortcode  =  jQuery("#chart_shortcode").val();

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

                         duration: chart_animation_duration,
                         easing: chart_animation

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

                        dataLabels: {

                            enabled: chart_data_labels

                        },

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

			jQuery("#thead-sparkline tr").find('td').remove();

			jQuery("#tbody-sparkline").find('tr').remove();

			var chart_title 	= jQuery("#chart_title").val(); 

			var chart_sub_title = jQuery("#chart_sub_title").val();

			var chart_xAxis 	= jQuery("#chart_xAxis").val();

			var chart_yAxis 	= jQuery("#chart_yAxis").val();

			var chart_shortcode =  jQuery("#chart_shortcode").val();

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

                    animation: {

                         duration: chart_animation_duration,
                         easing: chart_animation

                    },

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

                        allowPointSelect:chart_allow_point_select,

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

	                    negativeColor: chart_series_negative_color,

	                    borderColor: bar_border_color

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

			var chart_title 	= jQuery("#chart_title").val(); 

			var chart_sub_title = jQuery("#chart_sub_title").val();

			var chart_xAxis 	= jQuery("#chart_xAxis").val();

			var chart_yAxis 	= jQuery("#chart_yAxis").val();

			var chart_shortcode =  jQuery("#chart_shortcode").val();

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

                         duration: chart_animation_duration,
                         easing: chart_animation

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

                        dataLabels: {

                            enabled: chart_data_labels

                        },

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

			var chart_title 	= jQuery("#chart_title").val(); 

			var chart_sub_title = jQuery("#chart_sub_title").val();

			var chart_xAxis 	= jQuery("#chart_xAxis").val();

			var chart_yAxis 	= jQuery("#chart_yAxis").val();

			var chart_shortcode =  jQuery("#chart_shortcode").val();

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

                         duration: chart_animation_duration,
                         easing: chart_animation

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



}