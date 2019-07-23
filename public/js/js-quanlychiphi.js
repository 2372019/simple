//add ckeditor
CKEDITOR.replace('lyDoChi',editor());

function taoTrTatCa(data, linkEdit, linkDelete) {

	var html 		= "";
	var tongTien 	= 0;

	$.each(data["items"], function(i, value) {

		button = '';

		if (value['duyetChi'] == 1) {

			button = "<button class='w3-green w3-button w3-round-large' onclick='xacNhan(this)'>Duyệt Chi</button> ";
		}

		if (value['idNguoiXacNhan'] != 0) {

			button = "<button class='w3-gray w3-button w3-round-large' disabled>Đã Duyệt Chi</button> ";
		}
		
		html += "<tr><input type='hidden' class='id' name='id' value='" + value["id"] + "'>";
		html += "<td data-label ='STT'>" + (i + 1) + "</td>";

		if ( data["duocXoa"] ) {
			html += "<td data-label ='Xóa'><a href='" + linkDelete + value["id"] + "' title='Xóa quản lý chi phí này' class='w3-text-red delete' onclick='return confirm(\"Bạn Có Muốn Xóa Không\")'>&#10008;</a></td>";
		}
		
		html += "<td data-label ='Chi Cho Ai'><a href='" + linkEdit + value["id"] + "' title='edit'>" + value["chiChoAi"] + "</a></td>";

		html += "<td data-label ='Số Tiền Chi' class='w3-right-align'>" + numberFormat(value["soTienChi"]) + "</td>";
		html += "<td data-label ='Lý Do Chi'>" + value["lyDoChi"] + "</td>";
		html += "<td data-label ='Loại Chi Phí'>" + value["loaiChiPhi"] + "</td>";
		html += "<td data-label ='Ngày'>" + value["Ngay"] + "</td>";
		html += "<td data-label ='Hoàn Tất'>" + value["trangThai"] + "</td>";
		html += "<td data-label ='Người Nhập'>" + value["tenNguoiNhap"] + "</td>";
		html += "<td data-label =''>" + button + "</td></tr>";

		tongTien += parseInt(value["soTienChi"]);
	});	

	$('.tongTienChiPhi').html(numberFormat(tongTien));
	
	return html;
}

//duyệt chi 
function xacNhan(e) {

	var link = $("#linkSendDuyetChi").val();
	var data = { id: $(e).parent().parent().find("input.id").val() }

	var ketqua = onReady.sendAjax( data, link );

	alert(ketqua);
	$(e).attr('disabled','disabled');
}
