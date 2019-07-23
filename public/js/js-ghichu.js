//add ckeditor
CKEDITOR.replace('noiDung',editor());

function taoTrTatCa(data, linkEdit, linkDelete) {

	var html = "";

	$.each(data["items"], function(i, value) {

		var select = $('<select id="trangThai" name="trangThai" class="trangThai" onchange="updateTrangThai(this)"><option value="dangXuLy">Đang Xử Lý</option><option value="hoanTat">Hoàn Tất</option></select>');

		if (value["trangThai"] == 'hoanTat'){
			trangThai = 'Hoàn Tất';
		} else {
			$(select).find('option[value=' + value["trangThai"] + ']').attr("selected",true);
			trangThai = select[0].outerHTML;
		}
		
		html += "<tr><input type='hidden' class='id' name='id' value='" + value["id"] + "'>";
		html += "<td data-label ='STT'>" + ( i + 1 + ( data['current'] -1 ) * data['limit'] ) + "</td>";
		if ( data["duocXoa"] ) {
			html += "<td data-label ='Xóa'><a href='" + linkDelete + value["id"] + "' title='Xóa nhân sự này' class='w3-text-red delete' onclick='return confirm(\"Bạn Có Muốn Xóa Không\")'>&#10008;</a></td>";
		}
		html += "<td data-label ='Ngày'><a href='" + linkEdit + value["id"] + "' title='Sửa Ghi Chú' class='edit'>" + value["ngay"] + "</a></td>";
		html += "<td data-label ='Tiêu Đề'>" + value["tieuDe"] + "</td>";
		html += "<td data-label ='Nội Dung'>" + value["noiDung"] + "</td>";
		html += "<td data-label ='Trạng Thái'>" + trangThai + "</td>";
		html += "<td data-label ='Chế Độ'>" + value["cheDo"] + "</td>";

	});	

	return html;
}

//update trạng thái ngoài index
function updateTrangThai(e) {

	var data = {

		trangThai : $(e).val(),
		id: $(e).parent().parent().find("input.id").val()
	}
	var ketqua = onReady.sendAjax( data, $("#linkAjaxTrangThai").val() );

	alert(ketqua);	
}
