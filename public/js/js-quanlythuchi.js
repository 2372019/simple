/*
	tất cả các file page đều sử dụng tất cả các hàm trong file js.js riêng quản lý thu chi thì chỉ sử dụng 
	function numberformat và cuộn thanh ngang table

*/
$( window ).on("load", function() { // 1. Window load # DOM Ready

	function nhomChiPhi(){

		$('.chiTietChiPhi').prop('disabled','disabled');
		$('.chiTietChiPhi').hide();
	}

	function nguonChi() {

		$(".idQuanLyChiPhi").val("");
		$(".idDatHang").val("");
		$(".chiPhiMuaVao").val("");
		$('#nguoiNhan').val("");
		$('.idNhaCungCap').val("");
		$("#nhomChiPhi").val("");

		$('#nhomChiPhi').prop('disabled',false);
		$('#chiTietChiPhi').prop('disabled',false);
		$('#chiTietChiPhiSX').prop('disabled',false);
		$('#nguoiNhan').prop('readonly',false);

	}

	function nguonThu(){

		$('form #idKhachHang').val('');
		$('form #tenKhachHang').val('');
		$('form #idNhaCungCapThu').val('');
		$('form #tenNhaCungCap').val('');

		$('form #input-nhaCungCap').hide();
		$('form #input-khachHang').show();
	}

	//chọn select nguồn thu là bán hàng
	$('#nguonThu').change(function(){

		nguonThu();

		if( $(this).val() == 'Bán Hàng'){

			var listCustomerLink = $("#listCustomerLink").val();

			var ketqua = onReady.sendAjax( {}, listCustomerLink );

			$("#customersTable").html(ketqua);
			$("#customersModal").show();

		} else if($(this).val() == 'Nhà Cung Cấp'){

			var listNhaCungCapLink = $("#listNhaCungCapLink").val();

			var ketqua = onReady.sendAjax( {}, listNhaCungCapLink );
			$("#listNhaCungCapTable").html(ketqua);
			$(".listNhaCungCapModal").show();

			$('form #input-khachHang').hide();
			$('form #input-nhaCungCap').show();
			
		}
	});

	//khi chọn nguồn chi
	$('#nguonChi').change(function() {

		nhomChiPhi();

		if ( $(this).val() == 'Sản Xuất' ){

			//send Ajax
			var listDatHangLink = $("#listDatHangLink").val();
			var ketqua = onReady.sendAjax( {}, listDatHangLink );
			$("#listDatHangTable").html(ketqua);
			$("#listDatHangModal").show();

			nguonChi();

			$('#nhomChiPhi').prop('disabled',true);
	        $('#chiTietChiPhiSX').show();
	        $('#nguoiNhan').prop('readonly',true);

		} else if ($(this).val() == 'Chi Phí') {

			$('#nhomChiPhi').prop('disabled',false);

			//send Ajax
			var listChiPhiLink = $("#listChiPhiLink").val();
			var ketqua = onReady.sendAjax( {}, listChiPhiLink );

			$("#listChiPhiTable").html(ketqua);
			$("#listChiPhiModal").show();

			nguonChi();

	        $('#chiTietChiPhi').show();
	        $('#nguoiNhan').prop('readonly',true);
		} else {

			nguonChi();

	        $('#chiTietChiPhi').show();
		}
	});

	//chọn select nhóm chi phí
	$('#nhomChiPhi').change(function(){

		nhomChiPhi();

		switch ( $(this).val() ) {

	        case "Quản Lý":

	            $('#chiTietChiPhiQL').prop('disabled',false);
	            $('#chiTietChiPhiQL').show();
	            break;
	        case "Xưởng":

	            $('#chiTietChiPhiX').prop('disabled',false);
	            $('#chiTietChiPhiX').show();
	            break;
	        case "Chi Bán Hàng":

	            $('#chiTietChiPhiCBH').prop('disabled',false);
	            $('#chiTietChiPhiCBH').show();
	            break;
	        case "Cá Nhân":

	            $('#chiTietChiPhiCN').prop('disabled',false);
	            $('#chiTietChiPhiCN').show();
	            break;
	        case "Trả Nợ":

	            $('#chiTietChiPhiTN').prop('disabled',false);
	            $('#chiTietChiPhiTN').show();
	            break;
	        default:

	        	$('#chiTietChiPhi').prop('disabled',false);
	            $('#chiTietChiPhi').show();
	            break;
    	}
	});

	//chọn select hình thức là chuyển khoản
	$('.hinhThuc').change(function(){

		if( $(this).val() == 'Chuyển Khoản'){

			$("form .ckNganHang").show();
			$("form .ckNganHang").prop('disabled',false);
			$('form .tenNganHang').hide();
		} else {

			$("form .ckNganHang").hide();
			$("form .ckNganHang").prop('disabled','disabled');
			$("form .tenNganHang").prop('disabled',false);
			$('form .tenNganHang').show();
		}
	});

	/*------------------TAB THU---------------------*/
	/*
		lưu ý: chữ thu hoặc chi khi truyền biến vào function sendAjaxThuChi phải viết hoa chữ cái đầu để có thể
		đồng bộ với html và có thể dễ xử lý hơn
	*/

	//xóa input search
	$("#btnResetFilterThu").click(function(){
		$("input#filterRowsThu").val("");
		
		sendAjaxThuChi();
	});

	//chọn số dòng hiển thị trên 1 trang
	$("#limitThu").change( function(e) {

		sendAjaxThuChi('filter');
	});

	//search
	$("#btnFilterThu").click(function(e) {

		sendAjaxThuChi('filter');
	});

	//chọn ngày
	$("#btnDateThu").click(function(e) {

		var dateStart	 = $("input#dateStartThu").val() + " 00:00:00";
		var dateEnd		 = $("input#dateEndThu").val() + " 23:59:59";

		if (dateStart.length == 0 || dateEnd.length == 0) {

			alert('Vui lòng chọn ngày');
		}
		else if (dateStart > dateEnd) {

			alert('Ngày bắt đầu không được lớn hơn ngày kết thúc');
		}
		else {

			sendAjaxThuChi('date','Thu',dateStart,dateEnd);
		}

	});

	/*------------------TAB CHI---------------------*/
	/*
		lưu ý: chữ thu hoặc chi khi truyền biến vào function sendAjaxThuChi phải viết hoa chữ cái đầu để có thể
		đồng bộ với html và có thể dễ xử lý hơn
	*/

	//xóa input search
	$("#btnResetFilterChi").click(function(){
		$("input#filterRowsChi").val("");
		
		sendAjaxThuChi('reset','Chi');
	});

	//chọn số dòng hiển thị trên 1 trang
	$("#limitChi").change( function(e) {

		sendAjaxThuChi('filter','Chi');
	});

	//search
	$("#btnFilterChi").click(function(e) {

		sendAjaxThuChi('filter','Chi');
	});

	//chọn ngày
	$("#btnDateChi").click(function(e) {

		var dateStart	 = $("input#dateStartChi").val() + " 00:00:00";
		var dateEnd		 = $("input#dateEndChi").val() + " 23:59:59";

		if (dateStart.length == 0 || dateEnd.length == 0) {

			alert('Vui lòng chọn ngày');
		}
		else if (dateStart > dateEnd) {

			alert('Ngày bắt đầu không được lớn hơn ngày kết thúc');
		}
		else {

			sendAjaxThuChi('date','Chi',dateStart,dateEnd);
		}

	});

	/*--------------TAB THÔNG KÊ-------------*/

	//khi nhấn nút gửi phần thống kê thực chi
	$('#sendThongKeThucChi').click(function(){

		var dateStart	 = $("input#ngayStartThongKeThucChi").val();
		var dateEnd		 = $("input#ngayEndThongKeThucChi").val();

		if (dateStart.length == 0 || dateEnd.length == 0) {

			alert('Vui lòng chọn ngày');
		}
		else if (dateStart > dateEnd) {

			alert('Ngày bắt đầu không được lớn hơn ngày kết thúc');
		}
		else {

			$.ajax({

				url:  $("#linkThongKeThucChi").val(),
				type: 'POST',
				data: {dateStart : dateStart , dateEnd : dateEnd}
				
			}).done( function(result) {

				var data = JSON.parse(result);

				//mảng dùng chạy vòng lặp tương ứng với key của mảng trả về
				var array = ['Sản Xuất','Văn Phòng','Quản Lý','Lương','Cá Nhân','Xưởng','Chi Bán Hàng','Trả Nợ','Chuyển Vốn','Vay Mượn','Khác','Tổng'];

				var td = $('#thongKeThucChi tr td:last-child');

				var tang = 0;

				for (var i = 0; i < array.length; i++){

					//chạy vòng lặp each với key là array ở trên
					$.each(data[array[i]], function(i , v) {

						//gán lần lượt các giá trị trong vòng lặp với td thứ tự từ trên xuống
						for (var e = 0; e < $('#thongKeThucChi tr td:last-child').length; e++) {

							td.eq( tang ).html( numberFormat( v ) );
							tang++;
							
							break;
						}
					})
				}
				
			});
		}

	})

	//xem thông kê thu chi
	$('#sendThongKeCT').click(function(){

		var linkSendAjax 	= $("#linkThongKeDoanhThu").val();
		var ngayEndCT 		= $('#ngayEndThongKeCT').val();
		var ngayStartCT 	= $('#ngayStartThongKeCT').val();

		//check chọn ngày
		if (ngayStartCT.length == 0 || ngayEndCT.length == 0) {

			alert('Vui lòng chọn ngày');
		}
		else if (ngayStartCT > ngayEndCT) {

			alert('Ngày bắt đầu không được lớn hơn ngày kết thúc');
		}
		else {

			$.ajax({

			url:  linkSendAjax,
			type: 'POST',
			data: { 

				ngayStartCT 	: ngayStartCT ,
				ngayEndCT 		: ngayEndCT ,
				nguonThu 		: $('#thongKeNguonThu').val()

			}}).done( function(result) {

				//try catch dùng để không hiện lỗi console khi tài khoản đó k được cấp quyền vào function url
				try {
					
					var data = JSON.parse(result);
					
					var tieuDe 		= ['Đầu Kỳ', 'Thu', 'Chi', 'Tồn', 'Hiện Tại'];
					var keyThuChi	= ['dauky','thu','chi','ton','hientai'];

					var appendTbody = '';

					for (var i = 0; i < 5; i++) {

						appendTbody += "<tr><td>" + tieuDe[i] + "</td>";

						$.each(data, function(key, values) {

							appendTbody += "<td>" + numberFormat( values[ keyThuChi[i] ] ) + "</td>";
							
						});

					}
					//html dữ liệu
					$("#thongKe").html(appendTbody);
				} catch (e) {

					alert('Bạn Không Được Cho Phép Xem');
				}
				
			});
		}
	});

});

function sendAjaxThuChi(chongi = '',thuchi = 'Thu',dateStart = '',dateEnd = '') {
	
	var linkSendAjax = $("#linkSendAjax").val();
	switch (chongi){

		case "filter":
		case "limit":

			var limit		= $("#limit"+thuchi).val();
			var filterRows	= $("input#filterRows"+thuchi).val();
			var data 		= { thuchi: thuchi , limit : limit , filterRows : filterRows }
			break;
		case "date":

			var limit		= $("#limit"+thuchi).val();
			var data 		= { thuchi: thuchi , limit : limit , dateStart : dateStart , dateEnd : dateEnd }
			break;
		default:

			var data 		= { thuchi: thuchi , resetAll : 1 }
			break;
	}

	var ketqua = onReady.sendAjax( data );

	ketQuaAjaxThuChi(ketqua,thuchi);
}

function taoTrTatCaChi(data, linkEdit, linkDelete) {

	var html = '';
	var tongTienThuChi = 0;

	$.each(data["items"], function(i, value) {

		html += "<tr><td data-label ='STT'>" + (i + 1) + "</td>";
		if ( data["duocXoa"] ) {
			html += "<td data-label ='Xóa'><a href='" + linkDelete + value["id"] + "' title='Xóa quản lý thực chi này' class='w3-text-red delete' onclick='return confirm(\"Bạn Có Muốn Xóa Không\")'>&#10008;</a></td>";
		}
		html += "<td data-label ='Ngày'><a href='" + linkEdit + value["id"] + "' title='edit'>" + value["ngay"] + "</a></td>";
		html += "<td data-label ='Nội Dung'>" + value["noiDung"] + "</td>";
		html += "<td data-label ='Số Tiền' class='w3-right-align'>" + numberFormat(value["soTien"]) + "</td>";
		html += "<td data-label ='Nguồn Chi'>" + value["nguonChi"] + "</td>";
		html += "<td data-label ='Người Nhận'>" + value["tenNhaCungCap"] + "</td>";
		html += "<td data-label ='Nhóm Chi Phí'>" + value["nhomChiPhi"] + "</td>";
		html += "<td data-label ='Chi Tiết Chi Phí'>" + value["chiTietChiPhi"] + "</td>";
		html += "<td data-label ='Hình Thức'>" + value["hinhThuc"] + "</td>";
		html += "<td data-label ='CK Ngân Hàng'>" + value["tenNganHang"] + "</td>";
		html += "<td data-label ='Ghi Chú'>" + value["ghiChu"] + "</td></tr>";

		tongTienThuChi += parseInt(value["soTien"]);
	});

	$('.tongTienThucChi').html(numberFormat(tongTienThuChi));
	
	return html;
}

function taoTrTatCaThu(data, linkEdit, linkDelete) {

	var html = '';
	var tongTienThuChi = 0;

	$.each(data["items"], function(i, value) {

		html += "<tr><td data-label ='STT'>" + (i + 1) + "</td>";
		
		if ( data["duocXoa"] ) {
			html += "<td data-label ='Xóa'><a href='" + linkDelete + value["id"] + "' title='Xóa quản lý thu chi này' class='w3-text-red delete' onclick='return confirm(\"Bạn Có Muốn Xóa Không\")'>&#10008;</a></td>";
		}
		
		html += "<td data-label ='Ngày'><a href='" + linkEdit + value["id"] + "' title='edit'>" + value["ngay"] + "</a></td>";
		html += "<td data-label ='Nội Dung'>" + value["noiDung"] + "</td>";
		html += "<td data-label ='Số Tiền' class='w3-right-align'>" + numberFormat(value["soTien"]) + "</td>";
		html += "<td data-label ='Nguồn Thu'>" + value["nguonThu"] + "</td>";
		html += "<td data-label ='Tên KH'>" + value["tenKhachHang"] + "</td>";
		html += "<td data-label ='Tên NCC'>" + value["tenNhaCungCap"] + "</td>";
		html += "<td data-label ='Hình Thức'>" + value["hinhThuc"] + "</td>";
		html += "<td data-label ='CK Ngân Hàng'>" + value["tenNganHang"] + "</td>";
		html += "<td data-label ='Ghi Chú'>" + value["ghiChu"] + "</td></tr>";

		tongTienThuChi += parseInt(value["soTien"]);
	});

	$('.tongTienThucThu').html(numberFormat(tongTienThuChi));
	
	return html;
}

function ketQuaAjaxThuChi(result,thuchi) {

	var data 		= JSON.parse(result);
	var linkEdit 	= $("#linkEdit" + thuchi).val();
	var linkDelete 	= $("#linkDelete" + thuchi).val();
	var linkIndex 	= $("#linkIndex").val();

	$("#limit" + thuchi).val( data["limit"] ); // Reset value select limit option
	$(".tbody" + thuchi).empty();	//xóa html trong tbody

	if (thuchi == 'Chi') {

		$(".tbodyChi").append( taoTrTatCaChi(data, linkEdit, linkDelete) );
	} else {

		$(".tbodyThu").append( taoTrTatCaThu(data, linkEdit, linkDelete) );
	}

	paginationThuChi(data, linkIndex, thuchi);

}

function paginationThuChi(data, linkIndex, thuchi) {

	var pageThuChi = 'pageThuc' + thuchi;
	//phân trang
	$("#pagination" + thuchi).html("");
	
	html = '<a href="' + linkIndex + '?'+ pageThuChi +'=' + data["first"] + '" class="w3-button w3-hover-green">«</a>';
	
	for (var i = 1; i <= data['total_pages']; i++) {
		
		html = html + '<a href="' + linkIndex + '?'+ pageThuChi +'=' + i + '" class="w3-button w3-hover-gray ';
		
		if (i == data["current"])
			html = html + 'w3-blue w3-hover-green">' + i + '</a>';
		else 
			html = html + 'w3-hover-green">' + i + '</a>';
	}
	
	html = html + '<a href="' + linkIndex + '?'+ pageThuChi +'=' + data["last"] + '" class="w3-button w3-hover-green">»</a>';
	
	html = html + "( " + data["current"] + " of " + data["total_pages"] + " )";
	
	$("#pagination" + thuchi).html(html);
}

//orderBy các cột
function orderByThuChi(column,thuchi) {

	var orderBy 		= $(column).children().val(); //lấy value của input sắp xếp
	var linkSendAjax 	= $("#linkSendAjax").val();	 // lấy link send Ajax
	var limit			= $("#limit"+thuchi).val(); // lấy value của số dòng hiển thị trang
	var filterRows		= $("input#filterRows"+thuchi).val();
	
	$("a.orderColumn").removeClass("w3-gray w3-green"); //xóa các class (các class đó được thêm khi chạy đoạn code ở dưới)

	$.ajax({

		url:  linkSendAjax,
		type: 'POST',
		data: { thuchi : thuchi , limit : limit , filterRows : filterRows , orderBy : orderBy }	// truyền các biến vào để gửi lên Ajax	
	}).done( function(result) {

		if (orderBy.search("DESC") != -1) { // Nếu cột vừa click là DESC thì đổi thành ASC
			
			$(column).children().val( orderBy.replace("DESC","ASC") );
			$(column).addClass("w3-gray");
			
		} else { //Ngược lại, thì đổi thành DESC
			
			$(column).children().val( orderBy.replace("ASC","DESC") );
			$(column).addClass("w3-green");
		}

		ketQuaAjaxThuChi(result,thuchi);
	});
}

function taoTrTatCa(data, linkEdit, linkDelete) {
	
}