var linkSendAjax 		= $("#linkAddThongTinSendAjax").val();

var critiaListDatHang = {

	onReady: function() {

		$("#btnAddThongTinResetFilter").click( critiaListDatHang.resetAll );
		$("#limitAddThongTin").change( critiaListDatHang.limit );
		$("#btnAddThongTinFilter").click( critiaListDatHang.filter );
		$("#btnAddThongTinDate").click( critiaListDatHang.date );
	},

	resetAll: function(){

		$("input[name='addThongTinFilterRows']").val("");

		var ketqua = onReady.sendAjax( { resetAll:1, duyetChi: 1 }, linkSendAjax );
		critiaListDatHang.ketQua(ketqua);	
	},

	limit: function(){

		var data = {

			limit : $(this).val(),
			filterRows : $("#addThongTinFilterRows").val(),
			duyetChi : 1
		}

		var ketqua = onReady.sendAjax(data, linkSendAjax);

		critiaListDatHang.ketQua(ketqua);
	},

	get filter(){return this.limit},

	date: function(){

		var data = {

			limit		: $("#limitAddThongTin").val(),
			dateStart	: $("input[name='addThongTinDateStart']").val(),
			dateEnd		: $("input[name='addThongTinDateEnd']").val(),
			filterRows  : $("#addThongTinFilterRows").val(),
			duyetChi	: 1
		}

		if (data.dateStart.length == 0 || data.dateEnd.length == 0) {

			alert('Vui lòng chọn ngày');
		}
		else if (data.dateStart > data.dateEnd) {

			alert('Ngày bắt đầu không được lớn hơn ngày kết thúc');
		}
		else {

			var ketqua = onReady.sendAjax(data, linkSendAjax);
			critiaListDatHang.ketQua(ketqua);
		}
	},

	ketQua: function(result){

		ketQuaAddThongTinAjax(result);
	}
};

$( document ).ready( critiaListDatHang.onReady );


//click chọn dòng nào đó trong modal
function chonAddThongTin(e) {
	
	var idDatHang		= $(e).attr("title");
	var congNo 			= $(e).closest("td").siblings("td.congNoDatHang").text();
	var tenNhaCungCap 	= $(e).closest("td").siblings("td.tenNhaCungCapDatHang").text();
	var idNhaCungCap	= $(e).closest("td").siblings("input.idDatHangNhaCungCap").val();
	var ghiChu			= $(e).closest("td").siblings("td.ghiChuDatHang").text();
	
	$(".idDatHang").val(idDatHang);
	$(".chiPhiMuaVao").val(tenNhaCungCap);

	if (ghiChu == '') {
		$(".noiDungThucChi").val(tenNhaCungCap);
	} else {
		$(".noiDungThucChi").val(ghiChu);
	}

	$("#nguoiNhan").val(tenNhaCungCap);
	$(".soTienThucChi").val(congNo);
	$(".idNhaCungCap").val(idNhaCungCap);
	$("#listDatHangModal").hide();
}


//nhấn phân trang
function paginationAjax(e) {

	var page = $(e).attr("title");

	var data = { 
		page : page,
		filterRows : $("#addThongTinFilterRows").val()
	}

	var ketqua = onReady.sendAjax(data, linkSendAjax);
	
	ketQuaAddThongTinAjax(ketqua);
}

//orderBy các cột
function orderByAddThongTin(column) {

	var orderBy 		= $(column).children().val(); //lấy value của input sắp xếp
	
	$("a.orderColumn").removeClass("w3-gray w3-green"); //xóa các class (các class đó được thêm khi chạy đoạn code ở dưới)

	$.ajax({

		url:  linkSendAjax,
		type: 'POST',
		data: { 
			limit : $("#limitAddThongTin").val(),
			filterRows : $("#addThongTinFilterRows").val(),
			orderBy : orderBy,
			duyetChi : 1
		}	
	}).done( function(result) {

		if (orderBy.search("DESC") != -1) { // Nếu cột vừa click là DESC thì đổi thành ASC
			
			$(column).children().val( orderBy.replace("DESC","ASC") );
			$(column).addClass("w3-gray");
			
		} else { //Ngược lại, thì đổi thành DESC
			
			$(column).children().val( orderBy.replace("ASC","DESC") );
			$(column).addClass("w3-green");
		}

		ketQuaAddThongTinAjax(result);
	});
}

function ketQuaAddThongTinAjax (result) {

	var data 	= JSON.parse(result);
	var html 	= '';

	$("#limitAddThongTin").val( data["limit"] ); // Reset value select limit option
	$("#addThongTinTbody").empty();	//xóa html trong tbody
	
	//append value vào tbody
	$.each(data["items"], function(i, value) {	

		html += "<tr><td data-label ='STT'>" + (i + 1 + (data["current"] - 1) * data["limit"] ) + "</td>";

		html += "<td data-label ='Ngày'><a href='#' onclick='chonAddThongTin(this)' title=" + value['id'] + " class='chonAddThongTin'>" + value['ngay'] + "</a></td>";
		html += "<input type='hidden' name='' class ='idDatHangNhaCungCap' value='" + value['idNhaCungCap'] + "'>"
		html += "<td data-label ='Tên Nhà CC' class='tenNhaCungCapDatHang'>" + value['tenNhaCungCap'] + "</td>";
		html += "<td data-label ='Mã SP'>" + value['maSanPham'] + "</td>";
		html += "<td data-label ='Công Nợ' class='w3-right-align w3-bold congNoDatHang'>" + numberFormat(value['congNo']) + "</td>";
		html += "<td data-label ='Tổng Cộng Chưa VAT' class='w3-right-align w3-bold'>" + numberFormat(value['tongCongChuaVAT']) + "</td>";
		html += "<td data-label ='Thuế VAT' class='w3-right-align w3-bold'>" + numberFormat(value['thueVAT']) + "</td>";
		html += "<td data-label ='Tổng Tiền TT' class='w3-right-align w3-bold'>" + numberFormat(value['tongTienThanhToan']) + "</td>";
		html += "<td data-label ='Nhập Kho'>" + value['nhapKho'] + "</td>";
		html += "<td data-label ='Ghi Chú' class='ghiChuDatHang'>" + value['ghiChu'] + "</td></tr>";
		
	});

	$("#addThongTinTbody").append(html);
	
	//phân trang
	$("#paginationAddThongTin").html("");
	
	html = '<a href="#" title="' + data["first"] + '" class="w3-button w3-hover-green" onclick="paginationAjax(this)">«</a>';
	
	for (var i = 1; i <= data['total_pages']; i++) {
		
		html = html + '<a href="#" onclick="paginationAjax(this)" title="' + i + '" class="w3-button w3-hover-gray ';
		
		if (i == data["current"])
			html = html + 'w3-blue w3-hover-green">' + i + '</a>';
		else 
			html = html + 'w3-hover-green">' + i + '</a>';
	}
	
	html = html + '<a href="#" title="' + data["last"] +'" class="w3-button w3-hover-green" onclick="paginationAjax(this)">»</a>';
	
	html = html + "( " + data["current"] + " of " + data["total_pages"] + " )";
	
	$("#paginationAddThongTin").html(html);
}
