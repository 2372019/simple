var tonHienTai = parseInt( $('#tonHienTai').html() );
var tonKhoBanDau = parseInt( $('#tonKhoBanDau').val() );
// Khi số lượng tồn kho ban đầu thay đổi --> Sửa dụng trong file edit.phtml
$('#tonKhoBanDau').change(function(){
	
	$('#tonHienTai').html( tonHienTai - (tonKhoBanDau - parseInt( $('#tonKhoBanDau').val() ) ) );
});

$('#checkNoiBat').click(function(){

	var ketqua = onReady.sendAjax({resetAll : 1}, $('#linkSendAjax').val());
	if ( $('#checkNoiBat').is(":checked") ) {
		var data = 1;
		ketqua = onReady.sendAjax({ noiBat: data}, $('#linkSendAjax').val());
	}

	ketQuaAjax(ketqua);
});

// Sửa / Thêm (Bảng chi tiết sản phẩm)
function taoTrChiTiet(indexTable, values){

	var trHtml = '<tr class="addTrTable">';
	
	trHtml += '<input class="idCtProductsList" type="hidden" name="idCtProductsList[]" value="-1">';
	
	trHtml += '<input class="idProducts" type="hidden" name="idProducts[]" value=' + values["id"] + '>';
	
	trHtml += '<td class="stt">' + (indexTable + 1) + '</td><td>' + values["maSanPham"] + '</td>';
	
	trHtml += '<td class="tenSP">' + values["tenSanPham"] + '</td>';
	
	trHtml += '<td><input type="number" min="1" class="soLuong" name="soLuong[]" value="1" onchange="lonHonZero(this)"></td>';
	
	trHtml += "<td data-label ='Đơn Giá Bán' class='donGiaMoiNhat'>" + numberFormat(values["donGiaMoiNhat"]) + "</td>";
	
	trHtml += "<td data-label ='Đơn Giá Gốc' class='donGiaMuaVao'>" + numberFormat(values["donGiaMuaVao"]) + "</td>";
	
	trHtml += "<td data-label ='Tồn Kho BĐ' class='tonHienTai'>" + values["tonHienTai"] + "</td>";
	
	trHtml += "<td data-label ='Tồn Kho Hiện Tại' class='tonKhoBanDau'>" + values["tonKhoBanDau"] + "</td>";
	
	trHtml += "<td data-label ='Loại SP' class='loaiSanPham'>" + values["loaiSanPham"] + "</td>";
	
	trHtml += "<td data-label ='Mô tả'>" + values["moTa"] + "</td>";
	
	trHtml += '<td><a href="#" title="Xóa sản phẩm này" class="w3-text-red" onclick="deleteTr(this)">✘</a></td>';
	
	trHtml += '</tr>';
	
	return trHtml;
}
// trang Search
function taoTrTatCa(data, linkEdit, linkDelete){
	
	var html = "";
	var tongTien = 0;
	
	$.each(data["items"], function(i, value) {	
		
		html += "<tr><td data-label ='STT'>" + ( i + 1 + ( data['current'] -1 ) * data['limit'] ) + "</td>";

		if ( data["duocXoa"] ) {
			html += "<td data-label ='Xóa'><a href='" + linkDelete + value["id"] + "' title='Xóa sản phẩm này' class='w3-text-red delete' onclick='return confirm(\"Bạn Có Muốn Xóa Không\")'>&#10008;</a></td>";
		}
		
		html += "<td data-label ='Mã SP'><a href='" + linkEdit + value["id"] + "' title='Sửa sản phẩm' class='edit'>" + value["maSanPham"] + "</a></td>";
		
		html += "<td data-label ='Tên SP'>" + value["tenSanPham"] + "</td>";
		html += "<td data-label ='SL Tồn Kho'>" + value["tonHienTai"] + "</td>";

		if ( data["duocXoa"] ) {
			html += "<td data-label ='Đơn Giá' class='w3-right-align'>" + numberFormat(value["donGiaMuaVao"]) + "</td>";
		}

		html += "<td data-label ='Đơn Giá' class='w3-right-align'>" + numberFormat(value["donGiaMoiNhat"]) + "</td>";
		html += "<td data-label ='Loại SP'>" + value["type"] + "</td>";
		html += "<td data-label ='Tồn Kho Ban Đầu'>" + value["tonKhoBanDau"] + "</td>";

		if ( data["duocXoa"] ) {
			html += "<td data-label ='Tổng Giá'>" + 
			numberFormat( Math.max( 0, value["tonHienTai"] * value["donGiaMuaVao"] ) ) 
			+ "</td>";

			tongTien += value["tonHienTai"] * value["donGiaMuaVao"];
		}
		
		html += "</tr>";
		
	});
	//html tong tien GT
	if ( data["duocXoa"] ) {
		$('.tongTienProduct').html(numberFormat(tongTien));
	}
	
	return html;
}