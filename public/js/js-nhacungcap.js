//add ckeditor
CKEDITOR.replace('ghiChu',editor());

function taoTrTatCa(data, linkEdit, linkDelete) {

	var html = "";
	var tongTien = 0;

	$.each(data["items"], function(i, value) {

		html += "<tr><td data-label ='STT'>" + ( i + 1 + ( data['current'] -1 ) * data['limit'] ) + "</td>";

		if ( data["duocXoa"] ) {
			html += "<td data-label ='Xóa'><a href='" + linkDelete + value["id"] + "' title='Xóa nhà cung cấp này' class='w3-text-red delete' onclick='return confirm(\"Bạn Có Muốn Xóa Không\")'>&#10008;</a></td>";
		}

		html += "<td data-label ='Tên Nhà Cung Cấp'><a href='" + linkEdit + value["id"] + "' title='Sửa' class='edit'>" + value["tenNhaCungCap"] + "</a></td>";
		html += "<td data-label ='Số Đơn Hàng' class='w3-center'>" + value["soDonHang"] + "</td>";

		html += "<td data-label ='Tổng Công Nợ' class='w3-right-align w3-bold'>" + numberFormat(value["tongCongNo"]) + "</td>";

		html += "<td data-label ='Địa Chỉ'>" + value["diaChi"] + "</td>";
		html += "<td data-label ='Liên Hệ'>" + value["lienHe"] + "</td>";
		html += "<td data-label ='Mô Tả'>" + value["moTa"] + "</td>";
		html += "<td data-label ='Ghi Chú'>" + value["ghiChu"] + "</td></tr>";

		tongTien += parseInt(value["tongCongNo"]);
	});	

	$('.tongCongNo').html(numberFormat(tongTien));

	return html;
}
