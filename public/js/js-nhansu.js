//add ckeditor
CKEDITOR.replace('ghiChu',editor());

function taoTrTatCa(data, linkEdit, linkDelete) {

	var html = "";

	$.each(data["items"], function(i, value) {

		html += "<tr><td data-label ='STT'>" + ( i + 1 + ( data['current'] -1 ) * data['limit'] ) + "</td>";
		if ( data["duocXoa"] ) {
			html += "<td data-label ='Xóa'><a href='" + linkDelete + value["id"] + "' title='Xóa nhân sự này' class='w3-text-red delete' onclick='return confirm(\"Bạn Có Muốn Xóa Không\")'>&#10008;</a></td>";
		}
		html += "<td data-label ='Họ Tên'><a href='" + linkEdit + value["id"] + "' title='Sửa Nhân Sự' class='edit'>" + value["hoTen"] + "</a></td>";
		html += "<td data-label ='CMND'>" + value["CMND"] + "</td>";
		html += "<td data-label ='Ngày Sinh'>" + value["ngaySinh"] + "</td>";
		html += "<td data-label ='SĐT'>" + value["SDT"] + "</td>";
		html += "<td data-label ='Địa Chỉ'>" + value["diaChi"] + "</td>";
		html += "<td data-label ='Quê Quán'>" + value["queQuan"] + "</td>";
		html += "<td data-label ='Chức Vụ'>" + value["chucVu"] + "</td>";
		html += "<td data-label ='Ghi Chú'>" + value["ghiChu"] + "</td></tr>";

	});	

	return html;
}
