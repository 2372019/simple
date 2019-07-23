$( window ).on("load", function() {

	//add ckeditor
	CKEDITOR.replace('ghiChu',editor());

	//click nút check xuất hóa đơn
	$("#coXuatHoaDonKhong").click(function(){	

		tongTienThanhToan();
	});

	//khi thay đổi tiền thanh toán
	$('#daThanhToan').change(function() {

		var tongThanhToan = replacePriceToInt( $("#tongThanhToan") );
		var conNo = replacePriceToInt( $(this) );

		if ( tongThanhToan < conNo || isNaN(conNo) ) {

			alert('Số Tiền Đã Thanh Toán Không Hợp Lệ');
			$(this) .val( numberFormat(tongThanhToan) );
		} 
		
		tinhConNo(); //call function
		
	});

	//click để hiện ra list các nhà cung cấp
	$("#btnChonNhaCungCap").click(function(){
		
		var listNhaCungCapLink = $("#listNhaCungCapLink").val();

		var ketqua = onReady.sendAjax( {}, listNhaCungCapLink );
		$("#listNhaCungCapTable").html(ketqua);
		$(".listNhaCungCapModal").show();
	});
	
	//kiểm tra có thêm sản phẩm chưa
	$('#submit').click(function(e){
		if( !$('.idProducts').length  ) {
			alert("Chưa nhập sản phẩm");
			e.preventDefault();
		}
	});
	
});


//tạo tr hiển thị sản phẩm đã chọn trong 
function taoTrChiTiet(indexTable, values) {

	var trHtml = '<tr class="addTrTable">';
	
	trHtml += '<input class="idCtphieuNhapKho" type="hidden" name="idCtphieuNhapKho[]" value="-1">';
	
	trHtml += '<input class="idProducts" type="hidden" name="idProducts[]" value=' + values["id"] + '>';
	
	trHtml += '<td class="stt">' + (indexTable + 1) + '</td><td>' + values["maSanPham"] + '</td>';
	
	trHtml += '<td class="tenSP">' + values["tenSanPham"] + '</td>';

	trHtml += '<td class="tonDauKy">' + values["tonHienTai"] + '</td>';
	
	trHtml += '<td><input class="soLuong" type="number" name="soLuong[]" value="1" onchange="changeSoLuong(this)"></td>';
	
	trHtml += '<td class="donGia"><input class="donGia w3-right-align" type ="text" name="donGia[]" value=' + numberFormat(values["donGiaMoiNhat"]) + ' onchange="changeDonGiaMoiNhat(this)"></td>';
	
	trHtml += '<td><input class="ghiChu" type ="text" name="ghiChuCtphieuNhapKho[]" value=""></td>';
	
	trHtml += '<td class="tonCuoiKy">' + ( parseInt( values["tonHienTai"] ) + 1 ) + '</td>';
	
	trHtml += '<td><a href="#" title="Xóa sản phẩm này" class="w3-text-red" onclick="deleteTr(this)">✘</a></td>';
	
	trHtml += '</tr>';
	
	return trHtml;
}

//tăng tiền khi thay đổi số lượng các sản phẩm trong thêm/edit 
function changeSoLuong(e) {
	
	var soLuong = parseInt ( $(e).val() );

	if ( lonHonZero(e) ) {
		soLuong = 1;
	}

	var tonDauKy = parseInt( $(e).parent().siblings('.tonDauKy').text() );
	$(e).parent().siblings('.tonCuoiKy').text( tonDauKy + soLuong );

	tongCongChuaVAT(); //call function
	tongTienThanhToan(); //call function
}

//thay đổi đơn giá các sản phẩm trong thêm/edit
function changeDonGiaMoiNhat(e) {

	if ( $(e) .val() .match( /^[0-9,.]+$/ ) ) {

		$(e).val( numberFormat( replacePriceToInt( $(e) ) ) );//format lại số vừa nhập
	} else {

		alert('Vui lòng nhập đúng đơn giá');
		$(e).val(0);
	}

	tongCongChuaVAT(); //call function
	tongTienThanhToan(); //call function

}

//tính tổng cộng số tiền chưa VAT
function tongCongChuaVAT() {

	var tongCongChuaVAT = 0;
	var tongSoLuong = 0;
	var soLuong;

	$('td input.donGia').each(function (index, value) {

		soLuong 	 	 = $('td input.soLuong').eq(index).val();
		tongSoLuong		+= parseInt(soLuong); 
		tongCongChuaVAT += replacePriceToInt( $(this) ) * soLuong ;
	});

	$("#tongSoLuong").val(tongSoLuong);
	$("#tongCongChuaVAT").val( numberFormat(tongCongChuaVAT) );
}

//tính tổng tiền thanh toán
function tongTienThanhToan(){
	
	var thanhTien 			= replacePriceToInt( $("#tongCongChuaVAT") );
	var tongTienThanhToan 	= thanhTien;
	
	if ( $("#coXuatHoaDonKhong").is(':checked') ) {

		$(".thuevat").show();

		tongTienThanhToan 	= thanhTien/10 + thanhTien;

		$("#thueVAT").val( numberFormat(thanhTien/10) );
	} else {

		$(".thuevat").hide();
		$("#thueVAT").val(0);
	}

	$("#tongThanhToan").val( numberFormat(tongTienThanhToan) );
	$("#daThanhToan").val( numberFormat(tongTienThanhToan) );
	tinhConNo(); //call function
}

//tính giá còn nợ
function tinhConNo(){

	var tongThanhToan = replacePriceToInt( $('#tongThanhToan') );
	$('#conNo').val( numberFormat( tongThanhToan - replacePriceToInt( $('#daThanhToan') ) ) );
}

function taoTrTatCa(data, linkEdit, linkDelete) {

	var html = "";

	$.each(data["items"], function(i, value) {

		html 	+= "<tr>";
		html 	+= "<td data-label ='STT'>" + ( i + 1 + ( data['current'] -1 ) * data['limit'] ) + "</td>";

		if ( data["duocXoa"] ) {
			html += "<td data-label ='Xóa'><a href='" + linkDelete + value["id"] + "' title='Xóa phiếu nhập này' class='w3-text-red delete' onclick='return confirm(\"Bạn Có Muốn Xóa Không\")'>&#10008;</a></td>";
		}

		html 	+= "<td data-label ='Ngày'><a href='" + linkEdit + value["id"] + "' title='edit' class='edit'>" + value["ngay"] + "</a></td>";

		html 	+= "<td data-label ='Nội Dung'>" + value["noiDung"] + "</td>";
		html 	+= "<td data-label ='Lý Do Nhập'>" + value["lyDoNhap"] + "</td>";
		html 	+= "<td data-label ='Các Mã Hàng'>" + value["maHang"] + "</td>";
		html 	+= "<td data-label ='Tổng Số Lượng'>" + value["tongSoLuong"] + "</td>";
		html 	+= "<td data-label ='Nhà Cung Cấp'>" + value["tenNhaCungCap"] + "</td>";
		html 	+= "<td data-label ='Còn Nợ' class='w3-right-align'>" + numberFormat(value["conNo"]) + "</td>";
		html 	+= "<td data-label ='Tông Tiền TT' class='w3-right-align'>" + numberFormat(value["tongThanhToan"]) + "</td>";
		html 	+= "<td data-label ='Người Nhận'>" + value["nguoiNhanHang"] + "</td>";
		html 	+= "</tr>";

	});	

	return html;
}

