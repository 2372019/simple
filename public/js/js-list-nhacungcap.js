/*

file js này được sử dụng khi các page muốn lấy dữ liệu model này để hiển thị vào modal

*/

var linkSendAjax 		= $("#linkAddNhaCungCapSendAjax").val();

var critiaListNhaCungCap = {

	onReady: function() {

		$("#btnAddNhaCungCapResetFilter").click( critiaListNhaCungCap.resetAll );
		$("#limitAddNhaCungCap").change( critiaListNhaCungCap.limit );
		$("#btnAddNhaCungCapFilter").click( critiaListNhaCungCap.filter );
	},

	resetAll: function(){

		$("input[name='addNhaCungCapFilterRows']").val("");

		var ketqua = onReady.sendAjax( { resetAll:1 }, linkSendAjax );
		critiaListNhaCungCap.ketQua(ketqua);	
	},

	limit: function(){

		var data = {

			limit : $("#limitAddNhaCungCap").val(),
			filterRows : $("#addNhaCungCapFilterRows").val()
		}

		var ketqua = onReady.sendAjax(data, linkSendAjax);

		critiaListNhaCungCap.ketQua(ketqua);
	},

	get filter(){return this.limit},


	ketQua: function(result){

		ketQuaAddNhaCungCapAjax(result);
	}
};

$( document ).ready( critiaListNhaCungCap.onReady );


//click tên KH trong modal
function chonAddNhaCungCap(e) {
	
	var idNhaCungCap	= $(e).attr("title");
	var tenNhaCungCap 	= $(e).text();
	
	$(".tenNhaCungCap").val(tenNhaCungCap);
	$(".idNhaCungCap").val(idNhaCungCap);
	
	$(".listNhaCungCapModal").hide();
}


//nhấn phân trang
function paginationAjax(e) {

	var page = $(e).attr("title");

	var data = { 
		page : page,
		filterRows : $("#addNhaCungCapFilterRows").val()
	}

	var ketqua = onReady.sendAjax(data, linkSendAjax);
	
	ketQuaAddNhaCungCapAjax(ketqua);
}

//orderBy các cột
function orderByAddNhaCungCap(column) {

	var orderBy 		= $(column).children().val(); //lấy value của input sắp xếp
	
	$("a.orderColumn").removeClass("w3-gray w3-green"); //xóa các class (các class đó được thêm khi chạy đoạn code ở dưới)

	$.ajax({

		url:  linkSendAjax,
		type: 'POST',
		data: { limit : $("#limitAddNhaCungCap").val() , filterRows : $("#addNhaCungCapFilterRows").val() , orderBy : orderBy }	// truyền các biến vào để gửi lên Ajax	
	}).done( function(result) {

		if (orderBy.search("DESC") != -1) { // Nếu cột vừa click là DESC thì đổi thành ASC
			
			$(column).children().val( orderBy.replace("DESC","ASC") );
			$(column).addClass("w3-gray");
			
		} else { //Ngược lại, thì đổi thành DESC
			
			$(column).children().val( orderBy.replace("ASC","DESC") );
			$(column).addClass("w3-green");
		}

		ketQuaAddNhaCungCapAjax(result);
	});
}

function ketQuaAddNhaCungCapAjax (result) {

	var data 	= JSON.parse(result);
	var html 	= '';

	$("#limitAddNhaCungCap").val( data["limit"] ); // Reset value select limit option
	$("#addNhaCungCapTbody").empty();	//xóa html trong tbody
	
	//append value vào tbody
	$.each(data["items"], function(i, value) {	

		html += "<tr><td data-label ='STT'>" + (i + 1 + (data["current"] - 1) * data["limit"] ) + "</td>";
		html += "<td data-label ='Tên Nhà CC' class='tenNhaCungCap'><a href='#' title='" + value["id"] + "' class='chonAddNhaCungCap' onclick='chonAddNhaCungCap(this)'>" + value["tenNhaCungCap"] + "</a></td>";

		html += "<td data-label ='Liên Hệ'>" + value['lienHe'] + "</td>";
		html += "<td data-label ='Địa Chỉ' class='diaChi'>" + value['diaChi'] + "</td>";
		html += "<td data-label ='Mô Tả' class='moTa'>" + value['moTa'] + "</td>";
		html += "<td data-label ='Ghi Chú' class='ghiChu'>" + value['ghiChu'] + "</td></tr>";
	});

	$("#addNhaCungCapTbody").append(html);
	
	//phân trang
	$("#paginationAddNhaCungCap").html("");
	
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
	
	$("#paginationAddNhaCungCap").html(html);
}