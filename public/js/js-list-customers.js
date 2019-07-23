/*

file js này được sử dụng khi các page muốn lấy dữ liệu model này để hiển thị vào modal

*/

var linkCustomersEdit 	= $("#linkCustomersEdit").val();	//link của function Edit của controller Customers
var linkSendAjax 		= $("#linkAddOrderSendAjax").val();

var critiaListCustomers = {

	onReady: function() {

		$("#btnAddOrderResetFilter").click( critiaListCustomers.resetAll );
		$("#limitAddOrder").change( critiaListCustomers.limit );
		$("#btnAddOrderFilter").click( critiaListCustomers.filter );
		$("#btnAddOrderDate").click( critiaListCustomers.date );
	},

	resetAll: function(){

		$("input[name='addOrderFilterRows']").val("");

		var ketqua = onReady.sendAjax( { resetAll:1 }, linkSendAjax );
		critiaListCustomers.ketQua(ketqua);	
	},

	limit: function(){

		var data = {

			limit : $(this).val(),
			filterRows : $("#addOrderFilterRows").val()
		}

		var ketqua = onReady.sendAjax(data, linkSendAjax);

		critiaListCustomers.ketQua(ketqua);
	},

	get filter(){return this.limit},

	date: function(){

		var data = {

			limit		: $("#limitAddOrder").val(),
			dateStart	: $("input[name='dateAddOrderStart']").val(),
			dateEnd		: $("input[name='dateAddOrderEnd']").val(),
			filterRows : $("#addOrderFilterRows").val()
		}

		if (data.dateStart.length == 0 || data.dateEnd.length == 0) {

			alert('Vui lòng chọn ngày');
		}
		else if (data.dateStart > data.dateEnd) {

			alert('Ngày bắt đầu không được lớn hơn ngày kết thúc');
		}
		else {

			var ketqua = onReady.sendAjax(data, linkSendAjax);
			critiaListCustomers.ketQua(ketqua);
		}
	},

	ketQua: function(result){

		ketQuaAddOrderAjax(result);
	}
};

$( document ).ready( critiaListCustomers.onReady );


//click tên KH trong modal
function chonAddOrder(e) {
	
	var idKhachHang	 = $(e).attr("title");
	var tenKhachHang = $(e).text();
	var diaChi 		 = $(e).closest("td").siblings("td.diaChi").text();
	var nguoiMuaHang = $(e).closest("td").siblings("td.nguoiMuaHang").text();
	var soDienThoai = $(e).closest("td").siblings("td.soDienThoai").text();
	
	$(".tenKhachHang").val(tenKhachHang);
	$(".idKhachHang").val(idKhachHang);
	$(".diaChiGiaoHang").val(diaChi);
	$(".thongTinNguoiNhanHang").val(nguoiMuaHang + " - SĐT: " + soDienThoai);
	
	$("#customersModal").hide();
	if (typeof disabledButtonPrint == 'function') { 

	  	disabledButtonPrint(1); 			// Hàm này bên file js-orders.js
	}
}


//nhấn phân trang
function paginationAjax(e) {

	var page = $(e).attr("title");

	var data = { 
		page : page,
		filterRows : $("#addOrderFilterRows").val()
	}

	var ketqua = onReady.sendAjax(data, linkSendAjax);
	
	ketQuaAddOrderAjax (ketqua);
}

//orderBy các cột
function orderByAddOrder(column) {

	var orderBy 		= $(column).children().val(); //lấy value của input sắp xếp
	
	$("a.orderColumn").removeClass("w3-gray w3-green"); //xóa các class (các class đó được thêm khi chạy đoạn code ở dưới)

	$.ajax({

		url:  linkSendAjax,
		type: 'POST',
		data: { limit : $("#limitAddOrder").val() , filterRows : $("#addOrderFilterRows").val() , orderBy : orderBy }	// truyền các biến vào để gửi lên Ajax	
	}).done( function(result) {

		if (orderBy.search("DESC") != -1) { // Nếu cột vừa click là DESC thì đổi thành ASC
			
			$(column).children().val( orderBy.replace("DESC","ASC") );
			$(column).addClass("w3-gray");
			
		} else { //Ngược lại, thì đổi thành DESC
			
			$(column).children().val( orderBy.replace("ASC","DESC") );
			$(column).addClass("w3-green");
		}

		ketQuaAddOrderAjax (result);
	});
}

function ketQuaAddOrderAjax (result) {

	var data 		= JSON.parse(result);

	$("#limitAddOrder").val( data["limit"] ); // Reset value select limit option
	$("#addOrderTbody").empty();	//xóa html trong tbody
	
	//append value vào tbody
	$.each(data["items"], function(i, value) {	
	
		$("#addOrderTbody").append("<tr><td data-label ='STT'>" + (i + 1 + (data["current"] - 1) * data["limit"] ) + "</td><td data-label ='Tên Khách Hàng' class='tenKhachHang'><a href='#' title='" + value["id"] + "' class='chonAddOrder' onclick='chonAddOrder(this)'>" + value["tenKhachHang"] + "</a></td><td data-label ='Tổng Cộng Nợ'>" + numberFormat(value["tongCongNo"]) + "</td><td data-label ='Mã Số Thuế'>" + value["mST"] + "</td><td data-label ='Địa Chỉ' class='diaChi'>" + value["diaChi"] + "</td><td data-label ='Người MH' class='nguoiMuaHang'>" + value["nguoiMuaHang"] + "</td><td data-label ='SĐT' class='soDienThoai'>" + value["soDienThoai"] + "</td><td data-label ='Loại KH'>" + value["loaiKhachHang"] + "</td></tr>");	
	});
	
	//phân trang
	$("#paginationAddOrder").html("");
	
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
	
	$("#paginationAddOrder").html(html);
}