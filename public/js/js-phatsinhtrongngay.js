var arrayThongKe;
var dateStart 	= '';
var dateEnd		= '';
var checkYear 	= false;

$( window ).on("load", function() {

	//đưa background các button chọn thống kê về mặc định
	function resetColor()
	{
		$('.button-thongKe').css('background','#e8dcdc');
	}

	//khi nhấn các button thống kê nhanh
	$('#lastWeek, #thisMonth, #lastMonth, #year, #lastYear').click(function(){

		resetColor();
		$(this).css('background','red');

		var id = $(this).attr('id');

		checkYearShowDate(id);

		dateStart  	= $('#'+id+'DateStart').val();
		dateEnd		= $('#'+id+'DateEnd').val();

		var data = { tuyChon: id, dateStart : dateStart, dateEnd : dateEnd };

		sendAjaxThongKe(data);
	});

	//khi nhấn gửi ngày
	$('#sendDateTK').click(function(e){

		resetColor();
		//đưa checkYear về mặc định
		checkYear = false;

		//chuẩn bị data
		dateStart	= $('input[name=dateStart]').val();
		dateEnd		= $('input[name=dateEnd]').val();

		var data 	= {dateStart : dateStart, dateEnd : dateEnd}
		//kiểm tra ngày có hợp lệ
		if (dateStart.length == 0 || dateEnd.length == 0) {

			alert('Vui lòng chọn ngày');
		}
		else if (dateStart > dateEnd) {

			alert('Ngày bắt đầu không được lớn hơn ngày kết thúc');
		}
		else {
			sendAjaxThongKe(data);
		}
	});

	//khi nhấn chọn mục cần xem thống kê
	$('.clickThongKe').click(function(e){

		var nameArray 	= $(this).closest('ul.items').children('input.nameArray').val();
		var keyArray	= $(this).attr('title');

		if (typeof arrayThongKe === 'object') {

			var data = arrayThongKe[nameArray];
			
			var dataThongKe = [];

			//nếu thống kê thu chi
			if (nameArray == 'thucChi' || nameArray == 'thucThu') {

				if (typeof data[ keyArray ][ Object.keys(data[keyArray])[0] ] === 'object') {//nếu value chỉ có 1 giá trị

					$.each( data[keyArray], function(key, value) {

						dataThongKe = dataThongKe.concat({name : key, data: value, visible: false});
					});
					
				} else {

					$.each( data[keyArray], function(key, value) {

						dataThongKe = dataThongKe.concat({name : key, data: [value]});
					});
				}

			} else {//ngược lại

				dataThongKe = (typeof data[keyArray] === 'object') ? [{ name: keyArray, data: data[keyArray] }] : [{ name: keyArray, data: [ data[keyArray] ] }];
			}

			thongKe(dataThongKe, dateStart, dateEnd);
		} else {
			alert('Không Có Dữ Liệu');
		}
	});

});

//Kết quả trả về là 1 mảng kết quả
function sendAjaxThongKe(data)
{
	$.ajax({

		url:  $('#sendThongKe').val(),
		type: 'POST',
		data: data,
		
	}).done( function(result) {

		try {

			value	= jQuery.parseJSON(result);
			
			//kiểm tra nếu chọn thống kê theo năm
			if (value.length) {

				thang	= value[0];
				arrayThongKe = value[1];
			} else {
				arrayThongKe = value;
			}
		} catch (e){
			alert(result);
		}
	})
}

//vẽ biểu đồ thống kê
function thongKe(array, dateStart, dateEnd)
{
	Highcharts.setOptions({
		lang: {
            thousandsSep: ','
		},
		credits: {
		    enabled: false
		},
	});

	Highcharts.chart('thongKe', {
	    chart: {
	        type: 'line'
	    },
	    xAxis: {
	        categories: arrayLoopDate(dateStart, dateEnd)
	    },
	    title: { "text": "THỐNG KÊ"},
	    plotOptions: {
	        line: {
	            dataLabels: {
	                enabled: true
	            },
	            enableMouseTracking: false
	        }
	    },
	    series: array
	});
}

//set checkYear nếu chọn lastyear hoặc year
function checkYearShowDate(id)
{
	if (id.toLowerCase().match(/year.*/))
		checkYear = true;
	else
		checkYear = false;
}

//trả về mảng tất cả ngày trong khoảng thời gian nào đó
function arrayLoopDate(dateStart, dateEnd)
{
	var arrayDate = [];

	//kiểm tra là có chọn thống kê lastyear hay year
	if (checkYear) {

		arrayDate = thang;
	} else {

		for (var i = new Date(dateStart); i <= new Date(dateEnd); i.setDate( i.getDate() + 1 )) {

			arrayDate.push(i.getDate() + '/' + (i.getMonth()+1) );
		}
	}

	return arrayDate;
}

function taoTrTatCa(data, linkEdit, linkDelete) {
	
}
