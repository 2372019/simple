var linkSendAjax 		= $("#linkAddChiPhiSendAjax").val();

var critiaListChiPhi = {

	onReady: function() {

		$("#btnAddChiPhiResetFilter").click( critiaListChiPhi.resetAll );
		$("#limitAddChiPhi").change( critiaListChiPhi.limit );
		$("#btnAddChiPhiFilter").click( critiaListChiPhi.filter );
		$("#btnAddChiPhiDate").click( critiaListChiPhi.date );
	},

	resetAll: function(){

		$("input[name='addChiPhiFilterRows']").val("");

		var ketqua = onReady.sendAjax( { resetAll:1, duyetChi: 1 }, linkSendAjax );
		critiaListChiPhi.ketQua(ketqua);	
	},

	limit: function(){

		var data = {

			limit : $(this).val(),
			filterRows : $("#addChiPhiFilterRows").val(),
			duyetChi : 1
		}

		var ketqua = onReady.sendAjax(data, linkSendAjax);

		critiaListChiPhi.ketQua(ketqua);
	},

	get filter(){return this.limit},

	date: function(){

		var data = {

			limit		: $("#limitAddChiPhi").val(),
			dateStart	: $("input[name='addChiPhiDateStart']").val(),
			dateEnd		: $("input[name='addChiPhiDateEnd']").val(),
			filterRows  : $("#addChiPhiFilterRows").val(),
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
			critiaListChiPhi.ketQua(ketqua);
		}
	},

	ketQua: function(result){

		ketQuaAddChiPhiAjax(result);
	}
};

$( document ).ready( critiaListChiPhi.onReady );


//click chọn dòng nào đó trong modal
function chonAddChiPhi(e) {
	
	var idChiPhi	= $(e).attr("title");
	var soTien 		= $(e).closest("td").siblings("td.soTienChi").text();
	var chiChoAi 	= $(e).closest("td").siblings("td.chiChoAi").text();
	var lyDoChi 	= $(e).closest("td").siblings("td.lyDoChi").text();
	
	$(".idQuanLyChiPhi").val(idChiPhi);
	$(".soTienThucChi").val(soTien)
	$(".tenNhaCungCap").val(chiChoAi);
	$(".noiDungThucChi").val(lyDoChi);
	$("#listChiPhiModal").hide();
}


//nhấn phân trang
function paginationAjax(e) {

	var page = $(e).attr("title");

	var data = { 
		page : page,
		filterRows : $("#addChiPhiFilterRows").val()
	}

	var ketqua = onReady.sendAjax(data, linkSendAjax);
	
	ketQuaAddChiPhiAjax(ketqua);
}

//orderBy các cột
function orderByAddDatHang(column) {

	var orderBy 		= $(column).children().val(); //lấy value của input sắp xếp
	
	$("a.orderColumn").removeClass("w3-gray w3-green"); //xóa các class (các class đó được thêm khi chạy đoạn code ở dưới)

	$.ajax({

		url:  linkSendAjax,
		type: 'POST',
		data: { 
			limit 		: $("#limitAddChiPhi").val(),
			filterRows 	: $("#addChiPhiFilterRows").val(),
			orderBy 	: orderBy,
			duyetChi 	: 1
		}	
	}).done( function(result) {

		if (orderBy.search("DESC") != -1) { // Nếu cột vừa click là DESC thì đổi thành ASC
			
			$(column).children().val( orderBy.replace("DESC","ASC") );
			$(column).addClass("w3-gray");
			
		} else { //Ngược lại, thì đổi thành DESC
			
			$(column).children().val( orderBy.replace("ASC","DESC") );
			$(column).addClass("w3-green");
		}

		ketQuaAddChiPhiAjax(result);
	});
}

function ketQuaAddChiPhiAjax (result) {

	var data 	= JSON.parse(result);
	var html 	= '';

	$("#limitAddChiPhi").val( data["limit"] ); // Reset value select limit option
	$("#addChiPhiTbody").empty();	//xóa html trong tbody
	
	//append value vào tbody
	$.each(data["items"], function(i, value) {	

		html += "<tr><td data-label ='STT'>" + (i + 1 + (data["current"] - 1) * data["limit"] ) + "</td>";

		html += "<td data-label ='Ngày'><a href='#' onclick='chonAddChiPhi(this)' title=" + value['id'] + " class='chonAddChiPhi'>" + value['Ngay'] + "</a></td>";

		html += "<td data-label ='Chi Cho Ai' class='chiChoAi'> " + value['chiChoAi'] + "</td>";
		html += "<td data-label ='Số Tiền Chi' class='w3-right-align w3-bold soTienChi'>" + numberFormat(value['soTienChi']) + "</td>";
		html += "<td data-label ='Lý Do Chi' class='lyDoChi'>" + value['lyDoChi'] + "</td>";
		html += "<td data-label ='Loại CP'>" + value['loaiChiPhi'] + "</td>";
		html += "<td data-label ='Hoàn Tất'>" + value['trangThai'] + "</td>";
		
	});

	$("#addChiPhiTbody").append(html);
	
	//phân trang
	$("#paginationAddChiPhi").html("");
	
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
	
	$("#paginationAddChiPhi").html(html);
}
