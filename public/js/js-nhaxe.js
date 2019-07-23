$( window ).on("load", function() { // 1. Window load # DOM Ready

	//add ckeditor
	CKEDITOR.replace('ghiChu',editor());

	//filter theo lộ trình
	$('#btnLoTrinh').click(function(){

		var filterLoTrinhDi = $("input[name='filterLoTrinhDi']").val();
		var filterLoTrinhDen= $("input[name='filterLoTrinhDen']").val();

		var data = {

			limit: $("#limit").val(),
			filterLoTrinhDi: filterLoTrinhDi,
			filterLoTrinhDen: filterLoTrinhDen,
		}

		if (filterLoTrinhDi.length == 0 && filterLoTrinhDen.length == 0) {

			alert('Vui lòng nhập Lộ Trình Đi hoặc Lộ Trình Đến ');
		} else {

			var ketqua = onReady.sendAjax( data );
			ketQuaAjax(ketqua);
		}
		
	})
});


function taoTrTatCa(data, linkEdit, linkDelete) {

	var html = "";

	$.each(data["items"], function(i, value) {

		html += "<tr><td data-label ='STT'>" + ( i + 1 + ( data['current'] -1 ) * data['limit'] ) + "</td>";

		if ( data["duocXoa"] ) {
			html += "<td data-label ='Xóa'><a href='" + linkDelete + value["id"] + "' title='Xóa nhà xe này' class='w3-text-red delete' onclick='return confirm(\"Bạn Có Muốn Xóa Không\")'>&#10008;</a></td>";
		}

		html += "<td data-label ='Tên Nhà Xe'><a href='" + linkEdit + value["id"] + "' title='Sửa Nhà Xe' class='edit'>" + value["tenNhaXe"] + "</a></td>";

		html += "<td data-label ='LT Đi'>" + value["loTrinhDi"] + "</td>";
		html += "<td data-label ='LT Đến'>" + value["loTrinhDen"] + "</td>";
		html += "<td data-label ='SĐT'>" + value["soDienThoai"] + "</td>";
		html += "<td data-label ='Địa Chỉ'>" + value["diaChi"] + "</td>";
		html += "<td data-label ='Ghi Chú'>" + value["ghiChu"] + "</td></tr>";
	});	

	return html;
}
