
function taoTrTatCa(data, linkEdit, linkDelete) {

	var html = "";
	var gioitinh;

	$.each(data["items"], function(i, value) {

		gioitinh = 'Nam';
		if ( value["gender"] == 0 ) {

			gioitinh = 'Nữ';
		}

		html += "<tr><td data-label ='STT'>" + (i + 1) + "</td>";

		html += "<td data-label ='Xóa'><a href='" + linkDelete + value["id"] + "' title='Xóa user này' class='w3-text-red delete' onclick='return confirm(\"Bạn Có Muốn Xóa Không\")'>&#10008;</a></td>";

		html += "<td data-label ='Họ Tên'><a href='" + linkEdit + value["id"] + "' title='Sửa user' class='edit'>" + value["name"] + "</a></td>";
		
		html += "<td data-label ='Giới Tính'>" + gioitinh + "</td>";
		html += "<td data-label ='Ngày Sinh'>" + value["birth"] + "</td>";
		html += "<td data-label ='SĐT'>" + value["phone"] + "</td>";
		html += "<td data-label ='Địa Chỉ'>" + value["address"] + "</td>";
		html += "<td data-label ='HM Chi Phí'>" + numberFormat(value["hanMucChiPhi"]) + "</td>";
		html += "<td data-label ='Chức Vụ'>" + value["tenPhanQuyen"] + "</td></tr>";

	});	

	return html;
}
