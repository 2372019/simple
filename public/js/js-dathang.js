$( window ).on("load", function() { // 1. Window load # DOM Ready
	
	//add ckeditor
	CKEDITOR.replace('ghiChu',editor());

	//click nút check xuất hóa đơn
	$("#coXuatHoaDonKhong").click(function(){	
		tongTienThanhToan();
	});
	
	$('#submit').click(function(e){
		if( !$('.idProducts').length  ) {
			alert("Chưa nhập sản phẩm");
			e.preventDefault();
		}
	});

	//click để hiện ra list các nhà cung cấp
	$("#btnChonNhaCungCap").click(function(){
		
		var listNhaCungCapLink = $("#listNhaCungCapLink").val();

		var ketqua = onReady.sendAjax( {}, listNhaCungCapLink );
		$("#listNhaCungCapTable").html(ketqua);
		$(".listNhaCungCapModal").show();
	});

	//khi nhấn nút xuất kho thì gửi dữ liệu đi và disabled button
	$('#clickNhapKho').click(function(e) {

		$("#formNhapKho").submit();
		$(this).attr('disabled',true);
	})
});


function taoTrTatCa(data, linkEdit, linkDelete) {

	var html 	= "";
	var tongCongNo = 0;
	var tongTienChuaVAT = 0;
	var tongThueVAT		= 0;
	var tongTien 		= 0;
	
	$.each(data["items"], function(i, value) {

		button = "<button class='w3-green w3-button w3-round-large' onclick='xacNhan(this)'>Duyệt Chi</button> ";
		if (value['idNguoiXacNhan'] != 0) {

			button = "<button class='w3-gray w3-button w3-round-large' disabled>Đã Duyệt Chi</button> ";
		}

		html += "<tr><input type='hidden' class='id' name='id' value='" + value["id"] + "'>";
		html += "<td data-label ='STT'>" + ( i + 1 + ( data['current'] -1 ) * data['limit'] ) + "</td>";

		if ( data["duocXoa"] ) {
			html += "<td data-label ='Xóa'><a href='" + linkDelete + value["id"] + "' title='Xóa sản phẩm này' class='w3-text-red delete' onclick='return confirm(\"Bạn Có Muốn Xóa Không\")'>&#10008;</a></td>";
		}

		html 	+= "<td data-label ='Ngày'><a href='" + linkEdit + value["id"] + "' title='edit' class='edit'>" + value["ngay"] + "</a></td>";

		html += "<td data-label ='Tên Nhà CC'>" + value["tenNhaCungCap"] + "</td>";
		html += "<td class='w3-bold' data-label ='Mã Sản Phẩm'>" + value["maSanPham"] + "</td>";
		
		html += "<td data-label ='Công Nợ' class='w3-right-align'>" + numberFormat(value["congNo"]) + "</td>";

		html += "<td data-label ='Tổng Cộng Chưa VAT' class='w3-right-align'>" + numberFormat(value["tongCongChuaVAT"]) + "</td>";
		html += "<td data-label ='Thuế VAT' class='w3-right-align'>" + numberFormat(value["thueVAT"]) + "</td>";
		html += "<td data-label ='Tổng Tiền TT' class='w3-right-align'>" + numberFormat(value["tongTienThanhToan"]) + "</td>";

		html += "<td data-label ='Nhập Kho'>" + value["nhapKho"] + "</td>";
		html += "<td data-label ='Trạng Thái'>" + value["trangThai"] + "</td>";
		html += "<td data-label ='Tên Ng Nhập'>" + value["tenNguoiNhap"] + "</td>";

		html += "<td>" + button + "</td>";
		html += "<td data-label ='Ghi Chú'>" + value["ghiChu"] + "</td>";
		
		html += "</tr>";

		tongCongNo 		+= parseInt(value["congNo"]);
		tongTienChuaVAT += parseInt(value["tongCongChuaVAT"]);
		tongThueVAT		+= parseInt(value["thueVAT"]);
		tongTien 		+= parseInt(value["tongTienThanhToan"]);
	});

	html += "<tr>";
	html += "<td colspan ='4'></td>";
	html += "<td class='w3-text-blue w3-align-right w3-medium'>Tổng Cộng: </td>";
	html += "<td class='w3-text-blue w3-align-right w3-medium'>"+ numberFormat(tongCongNo) +"</td>";
	html += "<td class='w3-text-blue w3-align-right w3-medium'>"+ numberFormat(tongTienChuaVAT) +"</td>";
	html += "<td class='w3-text-blue w3-align-right w3-medium'>"+ numberFormat(tongThueVAT) +"</td>";
	html += "<td class='w3-text-blue w3-align-right w3-medium'>"+ numberFormat(tongTien) +"</td>";
	html += "<td colspan ='5'></td></tr>";

	return html;
}

//tạo tr hiển thị sản phẩm đã chọn
function taoTrChiTiet(indexTable, values){

	var trHtml = '<tr class="addTrTable">';
	
	trHtml += '<input class="idCtdatHang" type="hidden" name="idCtdatHang[]" value="-1">';
	
	trHtml += '<input class="idProducts" type="hidden" name="idProducts[]" value=' + values["id"] + '>';
	
	trHtml += '<td class="stt">' + (indexTable + 1) + '</td><td>' + values["maSanPham"] + '</td>';
	
	trHtml += '<td class="tenSP">' + values["tenSanPham"] + '</td>';
	
	trHtml += '<td><input class="soLuong" type="number" name="soLuong[]" value="1" onchange="changeSoLuong(this)"></td>';
	
	trHtml += '<td class="donGiaMoiNhat"><input class="donGiaMoiNhat w3-right-align" type ="text" name="donGiaMoiNhat[]" value=' + numberFormat(values["donGiaMuaVao"]) + ' onchange="changeDonGiaMoiNhat(this)"></td>';
	
	trHtml += '<td class="thanhTien w3-right-align">' + numberFormat( parseFloat(values["donGiaMuaVao"]) ) + '</td>';
	
	trHtml += '<td><input class="ghiChu" type ="text" name="ghiChuCtdatHang[]" value=""></td>';
	
	trHtml += '<td class="tonHienTai">' + values["tonHienTai"] + '</td>';
	
	trHtml += '<td><a href="#" title="Xóa sản phẩm này" class="w3-text-red" onclick="deleteTr(this)">✘</a></td>';
	
	trHtml += '</tr>';
	
	return trHtml;
}

//tăng tiền khi thay đổi số lượng các sản phẩm trong thêm/edit
function changeSoLuong(e){
	
	lonHonZero(e);

	var donGiaMoiNhat = parseInt( $(e).parent() .next() .children() .val() .replace(/đ|,/ig,'') );

	var thanhTien = numberFormat( parseInt( $(e).val() ) * donGiaMoiNhat );

	$(e).parent() .nextAll("td.thanhTien") .text( thanhTien );

	tongCongChuaVAT(); //call function
	tongTienThanhToan(); //call function
}

//thay đổi đơn giá các sản phẩm trong thêm/edit
function changeDonGiaMoiNhat(e) {

	if ($(e).val().match(/^[0-9,.]+$/)) {

		var soluong = parseInt( $(e).parent() .prev() .children() .val() );
		var donGiaMoiNhat = replacePriceToInt( $(e) );

		$(e).val( numberFormat(donGiaMoiNhat) );
		$(e).parent() .next() .text( numberFormat(soluong*donGiaMoiNhat) );

		tongCongChuaVAT(); //call function
		tongTienThanhToan(); //call function
		
	} else {

		alert('Vui lòng nhập đúng đơn giá');
		$(e).val(0);
	}

}

//tính tổng cộng số tiền chưa VAT
function tongCongChuaVAT(){

	var thanhTien = 0;

	$('td.thanhTien').each(function (index, c) {

		thanhTien += parseFloat(c.textContent.replace(/đ|,/ig,''));
	});

	$("#tongCongChuaVAT").val(numberFormat(thanhTien));
}

//tính tổng tiền thanh toán
function tongTienThanhToan(){
	
	var thanhTien 	= replacePriceToInt( $("#tongCongChuaVAT") );
	var tongTienThanhToan = thanhTien;

	if ( $("#coXuatHoaDonKhong").is(':checked') ) {

		$(".thuevat").show();
		tongTienThanhToan = parseInt(thanhTien*10/100+thanhTien);
		$("#thueVAT").val(numberFormat(parseInt(thanhTien*10/100)));
		
	} else {

		$(".thuevat").hide();
		$("#thueVAT").val(0);
	}

	$("#tongTienThanhToan").val(numberFormat(tongTienThanhToan));
	tinhCongNo(tongTienThanhToan);
}

//tinh công nợ trong edit
function tinhCongNo(tongTienThanhToan) {

	$('#congNo').val( numberFormat( tongTienThanhToan - replacePriceToInt($('#daThanhToan')) ) );
}

//duyệt chi 
function xacNhan(e) {

	var link = $("#linkSendDuyetChi").val();
	var data = { id: $(e).parent().parent().find("input.id").val() }

	var ketqua = onReady.sendAjax( data, link );

	alert(ketqua);
	$(e).attr('disabled','disabled');
}
