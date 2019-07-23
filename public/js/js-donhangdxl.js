var trangThai = {"choThanhToan" : "Chờ Thanh Toán" , "choXacNhan" : "Chờ Xác Nhận" , "conNo" : "Còn Nợ" , "khac" : "Khác"};

function taoTrTatCa(data, linkEdit, linkDelete) {

	var html 		= "";
	var linkView 	= $("#linkView").val();
	var linkEditOrView;
	
	$.each(data["items"], function(i, value) {

		linkEditOrView 	= linkEdit;
		//kiểm tra trường duocXem nếu = 1 thì gán linkView ngược lại gán linkEdit
		if (value['duocXem'] == 1) { linkEditOrView = linkView }

		html += "<tr><input type='hidden' class='id' name='id' value='" + value["id"] + "'>";
		html += "<td data-label ='STT'>" + ( i + 1 + ( data['current'] -1 ) * data['limit'] ) + "</td>";

		html += "<td class='trangThai' data-label ='Ngày'><a href='" + linkEditOrView + value["id"] + "' title='Sửa đơn hàng' class='edit'>" + value["ngay"] + "</a></td>";

		html += "<td data-label ='Trạng Thái'>" + trangThai[value["trangThai"]] + "</td>";
		html += "<td data-label ='Tên KH'>" + value["tenKhachHang"] + "</td>";
		html += "<td data-label ='Tổng Tiền TT' class='w3-right-align'>" + numberFormat(value["tongTienThanhToan"]) + "</td>";
		html += "<td data-label ='Công Nợ' class='w3-right-align'>" + numberFormat(value["congNo"]) + "</td>";
		
		html += "<td data-label ='Ngày Hẹn TT'>";
		if( value["congNo"] > 0 && jQuery.inArray( value["trangThai"], ['khac', 'choXacNhan', 'huySauBH', 'huyTruocBH'] ) < 0 && new Date( value["ngayHenThanhToan"] ) <= new Date() )
			html += "<span class='w3-red'>" + value["ngayHenThanhToan"] + "</span>";
		else
			html += value["ngayHenThanhToan"];
		html += "</td>";
		
		html += "<td class='w3-bold' data-label ='Mã Sản Phẩm'>" + value["maSanPham"] + "</td>";
		
		html += "<td data-label ='Hình Thức TT'>" + value["hinhThucThanhToan"] + "</td>";
		html += "<td data-label ='Công ty Bán Hàng'>" + value["congTyBanHang"] + "</td>";
		html += "<td data-label ='Tổng Cộng Chưa VAT' class='w3-right-align'>" + numberFormat(value["tongCongChuaVAT"]) + "</td>";
		html += "<td data-label ='Thuế VAT' class='w3-right-align'>" + numberFormat(value["thueVAT"]) + "</td>";
		html += "<td data-label ='Số Hóa Đơn' class='w3-right-align'>" + value["soHoaDon"] + "</td>";
		
		html += "<td data-label ='Thông Tin NMH'>" + value["thongTinNguoiNhanHang"] + "</td>";
		html += "<td data-label ='Người Giao Hàng'>" + value["nguoiGiaoHang"] + "</td>";
		html += "<td data-label ='Chi Phí Giao Hàng'>" + value["chiPhiGiaoHang"] + "</td>";
		html += "<td data-label ='Xuất Kho' class='w3-right-align'>" + value["xuatKho"] + "</td></tr>";
		
		html += "</tr>";
	});	

	return html;
}