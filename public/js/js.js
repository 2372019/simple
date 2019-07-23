var indexTable = 0;

class ajaxChung {

	sendAjax(data, link = $("#linkSendAjax").val()) {

		return $.ajax({

			url:  link,
			type: 'POST',
			async: false,
			data: data
		}).responseText;
	}
}

var onReady = new ajaxChung();

$( window ).on("load", function() { // 1. Window load # DOM Ready

	if ( $("#addTable tr.addTrTable") ){
		indexTable = $("#addTable tr.addTrTable").length;
	}
	//cuộn thanh ngang table ở file search
	//mx là vị trí chuột ban đầu - mx2 là vị trí chuột cập nhật mới
	var mx = 0;

	$("#drag-scroll").on({
		mousemove: function(e) {

			var mx2 = e.pageX - this.offsetLeft;
			if(mx){
				this.scrollLeft = this.sx + mx - mx2;
		}},

		mousedown: function(e) {

			this.sx = this.scrollLeft;
			mx = e.pageX - this.offsetLeft;
	}});

	$(document).on("mouseup", function(){ mx = 0; });

	//END cuộn thanh ngang table ở file search

	/*	
	*	format amount khi nhập hoặc sửa trong  input sử dụng keyup
	*	sử dụng cho các form có input nhập tiền
	*	lưu ý chỉ dùng cho trang search có 1 tab thêm, nếu >1 tab thêm thì phải gọi riêng
	*/

	// Change Tabs
	$(".tabsItem").click(function(e){
		var index = $( this ).index( '.tabsItem' ) ;
		
		// Active tabsItem
		$(".tabsItem").removeClass('active');
		$(this).addClass('active');
		
		// hide & show tabs		
		$("div[name='tabs']").addClass("w3-hide");
		$('#tabs' + index).removeClass("w3-hide");
	});

	$('input.keyUpFormatAmount').keyup(function(event) {

		var thisInput = $(this);
		keyUpFormatAmount(thisInput);
	});

	
	// object 
	var critia = { 

		onReady: function() {

			if(window.location.href.indexOf("index") > 0) {

				var ketqua = onReady.sendAjax( {} );
				critia.ketQua(ketqua);
			}

			$('#submit').click(function(e){

				checkInputEmptyAndReplacePrice(e);
			});

			$("#btnResetFilter").click( critia.resetAll );
			$("#limit").change( critia.limit );
			$("#btnFilter").click( critia.filter );
			$("#btnDate").click( critia.date );
			$("#btnChonSanPham").click( critia.chonSanPham );
			$("#addMaSanPham").keydown( critia.addMaSanPham );

		},

		resetAll: function(){

			$("input[name='filterRows']").val("");

			var ketqua = onReady.sendAjax( { resetAll:1 } );
			critia.ketQua(ketqua);	
		},

		limit: function(){

			var data = {

				limit : $("#limit").val(),
				filterRows : $("input[name='filterRows']").val()
			}

			var ketqua = onReady.sendAjax(data);

			critia.ketQua(ketqua);
		},

		get filter(){return this.limit},

		date: function(){

			var dateStart 	= $("input[name='dateStart']").val();
			var dateEnd 	= $("input[name='dateEnd']").val();

			var data = {

				limit		: $("#limit").val(),
				dateStart	: dateStart + " 00:00:00",
				dateEnd		: dateEnd + " 23:59:59"
			}

			if (dateStart.length == 0 || dateEnd.length == 0) {

				alert('Vui lòng chọn ngày');
			}
			else if (dateStart > dateEnd) {

				alert('Ngày bắt đầu không được lớn hơn ngày kết thúc');
			}
			else {

				var ketqua = onReady.sendAjax(data);
				critia.ketQua(ketqua);
			}
		},

		chonSanPham: function(){

			var data = {};
			var link = $("#listProductsLink").val();
			var ketqua = onReady.sendAjax(data, link);

			critia.ketQuaChonSP(ketqua);
		},

		addMaSanPham: function(e){

			if (e.keyCode == 9 && !e.shiftKey) {
		   	
		   		var link = $("#addLinkSendAjax").val();
				var maSanPham = { data : $.trim( $(this).val() ) };

				$(this).val("");

				var ketqua = onReady.sendAjax(maSanPham, link);

				critia.ketQuaAddMaSP(ketqua);		

			}
		},

		ketQua: function(result){

			ketQuaAjax(result);
		},

		ketQuaChonSP: function(result){

			$("#listProductsTable").html(result);
			$("#listProductsModal").show();
		},

		ketQuaAddMaSP: function(result){

			if(result == 'false')
				alert('Không Có Sản Phẩm Này');
			else {

				var product			= JSON.parse(result);

				$("tr.addMaSanPham").before( taoTrChiTiet(indexTable++, product) );

				if (typeof tongCongChuaVAT == 'function') { 
					tongCongChuaVAT(); 				// Hàm này bên file js-orders.js
				}

				if (typeof tongTienThanhToan == 'function') { 
					tongTienThanhToan(); 				// Hàm này bên file js-orders.js
				}

				if (typeof disabledButtonPrint == 'function') { 
					disabledButtonPrint(1); 			// Hàm này bên file js-orders.js
				}
			}
		},
	};

	$( critia.onReady );


	//trước khi submit form
	function checkInputEmptyAndReplacePrice(e) {

		var error = '';

		//kiểm tra những ô bắt buộc nhập có nhập hay chưa
		$('.validateFiled').each(function(){

			if ( $(this).val() == '' ) {

				error += 'Vui Lòng Nhập Những Ô Bắt Buộc (*)';
				return false;
			}
		});

		if ( $('.checkSoLuong').val() < 0 ) {

			error += ' - Số Lượng Không Được Nhỏ Hơn 0 - ';
		}

		//chưa nhập thì thông báo
		if ( error.length ) {

			e.preventDefault();
			alert(error);
		} else {

			//bỏ dấu , trên tiền
			$('.replacePrice').each(function() {

				$(this).val( $(this).val().replace( /,/ig,'' ) );
			});
		}
	}
		
});

//orderBy các cột trong table
function orderBy(column) {

	var orderBy 		= $(column).children().val(); //lấy value của input sắp xếp
	var linkSendAjax 	= $("#linkSendAjax").val();
	var limit			= $("#limit").val();
	var filterRows		= $("input[name='filterRows']").val();
	
	$("a.orderColumn").removeClass("w3-gray w3-green");	//xóa các class (các class đó được thêm khi chạy đoạn code ở dưới)

	$.ajax({

		url:  linkSendAjax,
		type: 'POST',
		data: { limit : limit , filterRows : filterRows , orderBy : orderBy }
	}).done( function(result) {

		if (orderBy.search("DESC") != -1) { // Nếu cột vừa click là DESC thì đổi thành ASC
			$(column).children().val( orderBy.replace("DESC","ASC") );
			$(column).addClass("w3-gray");	
		} else { //Ngược lại, thì đổi thành DESC
			
			$(column).children().val( orderBy.replace("ASC","DESC") );
			$(column).addClass("w3-green");
		}

		ketQuaAjax (result);
	});
}

function ketQuaAjax (result){

	var data 		= JSON.parse(result);
	var linkEdit 	= $("#linkEdit").val();
	var linkDelete 	= $("#linkDelete").val();
	var linkIndex 	= $("#linkIndex").val();

	$("#limit").val( data["limit"] ); // Reset value select limit option
	$(".tbody").empty();	//xóa html trong tbody
	
	//append value vào tbody

	$(".tbody").append( taoTrTatCa(data, linkEdit, linkDelete) );	
	
	pagination(data, linkIndex, "#pagination");
}


//number format
function numberFormat(nStr)
{
    nStr += '';
    x = nStr.split('.');
    x1 = x[0];
    x2 = x.length > 1 ? '.' + x[1] : '';

    var rgx = /(\d+)(\d{3})/;
    while (rgx.test(x1)) {

        x1 = x1.replace(rgx, '$1' + ',' + '$2');
    }

    return x1 + x2;
}

//format amount khi nhập hoặc sửa trong  input sử dụng keyup
function keyUpFormatAmount(thisInput){

  	if(event.which >= 37 && event.which <= 40) return;

	thisInput.val(function(index, value) {

	    return value .replace(/\D/g, "") .replace(/\B(?=(\d{3})+(?!\d))/g, ",");
	});

}

function replacePriceToInt( select ) {

	var price = parseFloat( select.val() .replace(/đ|,/ig,'') );
	return price;
}

function lonHonZero(e){

	if ( $(e).val() < 1 || $(e).val() > 100000 ) {
		alert('Số Lượng Không Được Nhỏ Hơn 1 Và Nhỏ Hơn 100000');
		$(e).val("1");

		return true;
	}
	return false;
}

// Xóa 1 dòng trong table
function deleteTr(e){
	
	var index = $("#addTable tr.addTrTable").index( $(e).closest("tr.addTrTable") );
	
	$('#addTable tr.addTrTable')[index].remove();

	var sttList = $("#addTable td.stt");
	
	for ( ; index < sttList.length; index++ ) {
		
		sttList[index].innerText		= index + 1;
	}
	
	indexTable = index; // Gan lai bien Global
	
	if (typeof tongSoLuong == 'function') { 
	  tongSoLuong();
	}

	if (typeof tongCongChuaVAT == 'function') { 
	  tongCongChuaVAT(); 
	}

	if (typeof tongTienThanhToan == 'function') { 
	  tongTienThanhToan(); 
	}

	if (typeof disabledButtonPrint == 'function') { 
	  disabledButtonPrint(1); 
	}
}

function pagination(data, linkIndex, idTag) {
	//phân trang
	$(idTag).html("");
	
	html = '<a href="' + linkIndex + '?page=' + data["first"] + '" class="w3-button w3-hover-green">«</a>';
	
	for (var i = 1; i <= data['total_pages']; i++) {
		
		html = html + '<a href="' + linkIndex + '?page=' + i + '" class="w3-button w3-hover-gray ';
		
		if (i == data["current"])
			html = html + 'w3-blue w3-hover-green">' + i + '</a>';
		else 
			html = html + 'w3-hover-green">' + i + '</a>';
	}
	
	html = html + '<a href="' + linkIndex + '?page=' + data["last"] + '" class="w3-button w3-hover-green">»</a>';
	
	html = html + "( " + data["current"] + " of " + data["total_pages"] + " )";
	
	$("#pagination").html(html);
}

function editor()
{
	var link = location.pathname;
    var str  = link.indexOf('/',2);
    var url  = link.slice(0,str+1);
    
    var editor = {
          language:'vi',
          filebrowserBrowseUrl : url + 'public/ckfinder/ckfinder.php',

          filebrowserImageBrowseUrl : url + 'public/ckfinder/ckfinder.html?type=imgCKfinder',
           
          filebrowserUploadUrl : url + 'public/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Files',
           
          filebrowserImageUploadUrl : url + 'public/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=imgCKfinder',
    };

    return editor;
}