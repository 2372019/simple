
// tìm mã thành phẩm
$('#btnChonThanhPham').click(function(){

	var maThanhPham 		= $.trim( $('#maThanhPham').val() );
	var soLuongThanhPham 	= $.trim( $('#soLuongThanhPham').val() );

	var ketqua = onReady.sendAjax( { data : maThanhPham }, $("#addLinkSendAjax").val() );

	if(ketqua == 'false')
    {
        alert('Không Có Sản Phẩm Này');
        $(this).val();

    } else {

		var product			= JSON.parse(ketqua);

		$('#idThanhPham').val( product["id"] );
		$('#tenThanhPham').html( product["tenSanPham"] );

		var ketQuaVatTu = onReady.sendAjax( { data : maThanhPham }, $("#linkSendVatTu").val() );

		var vatTu			= JSON.parse(ketQuaVatTu);
		indexTable			= 1;
		
		$("#formThanhPham tbody tr:not(.addMaSanPham)").html('');
		
		$.each(vatTu, function(key, values) {
			
			$("tr.addMaSanPham").before( taoTrVatTuSanPham(indexTable++, key, values, soLuongThanhPham) );
		});
	}

});

$('#btnSoLuong').click(function(){

	var soLuongThanhPham = $('#soLuongThanhPham').val();

	$('.slVatTu').each(function(index, value){

		$('.tongSLVatTu').eq( [index] ).text( value.textContent * soLuongThanhPham );
		var tonHienTai = $('.tonHienTai').eq( [index] ).text();
		$('.tonCuoiKy').eq( [index] ).text( tonHienTai - value.textContent * soLuongThanhPham );
	});

});

$('.checkSL').click(function(e) {

	checkTonCuoiKy(e);
})

function changeSoLuong(e) {

	var tonHienTai = parseInt( $(e).parent().nextAll('td.tonHienTai').text() );
	var soLuong = parseInt ( $(e).val() );

	if ( lonHonZero(e) ) {

		soLuong = 1;
	}

	$(e).parent().nextAll('td.tonCuoiKy').text( tonHienTai- soLuong );

}

function changeSoLuongPhatSinh(e) {

	var tonHienTai = parseInt( $(e).parent().nextAll('td.tonHienTai').text() );
	var soLuong = parseInt ( $(e).val() );

	if ( $(e).val() < 0 || $(e).val() > 100000 ) {

		alert('Số Lượng Không Được Nhỏ Hơn 0 Và Nhỏ Hơn 100000');
		$(e).val("0");

		soLuong = 0;
	}

	var tongSL = parseInt( $(e).parent().prev().text() ) + soLuong;

	$(e).parent().nextAll('td.tonCuoiKy').text( tonHienTai- tongSL );
}

function checkTonCuoiKy(e) {
	
	var flag = 0;
	$('td.tonCuoiKy').each(function(){

		if ( $(this).text() < 0 ) {

			flag = 1;
		}
	});

	if (flag == 1) {

		alert('Cảnh Báo: Tồn Tại Sản Phẩm Có Tồn Cuối Kỳ Nhỏ Hơn 0');
	}
}

// Sửa / Thêm (Bảng chi tiết sản phẩm)
function taoTrChiTiet(indexTable, values){

	var trHtml = '<tr class="addTrTable">';
	
	trHtml += '<input class="idCtVatTuThanhPham" type="hidden" name="idCtVatTuThanhPham[]" value="-1">';
	
	trHtml += '<input class="idMaVatTu" type="hidden" name="idMaVatTu[]" value=' + values["id"] + '>';
	
	trHtml += '<td class="stt">' + indexTable + '</td><td>' + values["maSanPham"] + '</td>';
	
	trHtml += '<td class="tenSP">' + values["tenSanPham"] + '</td>';
	
	trHtml += '<td>----</td>';
	
	trHtml += '<td>----</td>';
	
	trHtml += '<td><input class="soLuongPhatSinh" type ="number" name="soLuongPhatSinh[]" value="1" onchange="changeSoLuong(this)"></td>';
	
	trHtml += '<td><input class="ghiChu" type ="text" name="ghiChuCtthanhPham[]" value=""></td>';
	
	trHtml += '<td class="tonHienTai">' + values["tonHienTai"] + '</td>';

	trHtml += '<td class="tonCuoiKy">' + parseInt(values["tonHienTai"] - 1) + '</td>';
	
	trHtml += '<td><a href="#" title="Xóa sản phẩm này" class="w3-text-red" onclick="deleteTr(this)">✘</a></td>';
	
	trHtml += '</tr>';
	
	return trHtml;
}

function taoTrVatTuSanPham(indexTable, key, values, soLuongThanhPham){
	
	var trHtml = '<tr class="addTrTable">';
	
	trHtml += '<input class="idCtVatTuThanhPham" type="hidden" name="idCtVatTuThanhPham[]" value="-1">';
	
	trHtml += '<input class="idMaVatTu" type="hidden" name="idMaVatTu[]" value=' + values["id"] + '>';
	
	trHtml += '<td class="stt">' + indexTable + '</td><td>' + values["maSanPham"] + '</td>';
	
	trHtml += '<td class="tenSP">' + values["tenSanPham"] + '</td>';
	
	trHtml += '<td>' + values['soLuongVatTu'] + '</td>';
	
	trHtml += '<td>' + values['soLuongVatTu'] * soLuongThanhPham + '</td>';
	
	trHtml += '<td><input class="soLuongPhatSinh" type ="number" name="soLuongPhatSinh[]" value="0" onchange="changeSoLuongPhatSinh(this)"></td>';
	
	trHtml += '<td><input class="ghiChu" type ="text" name="ghiChuCtthanhPham[]" value=""></td>';
	
	trHtml += '<td class="tonHienTai">' + values["tonHienTai"] + '</td>';

	trHtml += '<td class="tonCuoiKy">' + parseInt(values["tonHienTai"] - values['soLuongVatTu'] * soLuongThanhPham) + '</td>';
	
	trHtml += '<td><a href="#" title="Xóa sản phẩm này" class="w3-text-red""></a></td>';
	
	trHtml += '</tr>';
	
	return trHtml;
}

function taoTrTatCa(data, linkEdit, linkDelete) {

	var html = "";

	$.each(data["items"], function(i, value) {

		html += "<tr><td data-label ='STT'>" + ( i + 1 + ( data['current'] -1 ) * data['limit'] ) + "</td>";

		html += "<td data-label ='Ngày'><a href='" + linkEdit + value["id"] + "' title='Sửa' class='edit'>" + value["ngay"] + "</a></td>";

		html += "<td data-label ='Tên SP'>" + value["tenSanPham"] + "</td>";
		html += "<td data-label ='Số Lượng Thành Phẩm'>" + value["soLuongThanhPham"] + "</td>";
		html += "<td data-label ='Tổng SL Vật Tư'>" + value["tongSoLuongVatTu"] + "</td>";
		html += "<td data-label ='Các Mã Hàng'>" + value["maHang"] + "</td>";
	});	

	return html;
}
