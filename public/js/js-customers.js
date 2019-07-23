//add ckeditor
CKEDITOR.replace('ghiChu',editor());

function taoTrTatCa(data, linkEdit, linkDelete) {

	var html = "";

	$.each(data["items"], function(i, value) {

		html += "<tr><td data-label ='STT'>" + ( i + 1 + ( data['current'] -1 ) * data['limit'] ) + "</td>";
		
		if ( data["duocXoa"] ) {
			html += "<td data-label ='Xóa'><a href='" + linkDelete + value["id"] + "' title='Xóa khách hàng này' class='w3-text-red delete' onclick='return confirm(\"Bạn Có Muốn Xóa Không\")'>&#10008;</a></td>";
		}
		html += "<td data-label ='Tên KH'><a href='" + linkEdit + value["id"] + "' title='Sửa Khách Hàng' class='edit'>" + value["tenKhachHang"] + "</a></td>";

		html += "<td data-label ='MST'>" + value["mST"] + "</td>";
		html += "<td data-label ='Số Đơn Hàng' class='w3-center'>" + value["soDonHang"] + "</td>";
		html += "<td data-label ='Tổng Công Nợ' class='w3-right-align w3-bold'>" + numberFormat(value["tongCongNo"]) + "</td>";
		html += "<td data-label ='Địa Chỉ'>" + value["diaChi"] + "</td>";
		html += "<td data-label ='SĐT'>" + value["soDienThoai"] + "</td>";
		html += "<td data-label ='Email'>" + value["email"] + "</td>";
		html += "<td data-label ='Người Mua Hàng'>" + value["nguoiMuaHang"] + "</td>";
		html += "<td data-label ='LKH'>" + value["loaiKhachHang"] + "</td>";
		html += "<td data-label ='Ghi Chú'>" + value["ghiChu"] + "</td>";
		html += "<td data-label ='Ngày'>" + value["ngay"] + "</td></tr>";
	});	

	return html;
}
