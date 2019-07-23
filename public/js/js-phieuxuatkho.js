$( window ).on("load", function() {

	//add ckeditor
	CKEDITOR.replace('ghiChu',editor());

	var critiaPhieuXuatKho = {

		onReady: function() {

			$('#lyDoXuat').change( critiaPhieuXuatKho.lyDoXuat );
			$("#btnChonKhachHang").click( critiaPhieuXuatKho.chonKhachHang );	
			$('#submit.addNew').click( critiaPhieuXuatKho.checkSubmit );
		},

		lyDoXuat: function() {

			if ( $(this).val() == 'Xuất Bán Hàng') {

				$('#tenKH').attr('readonly', true);
				$('#idKhachHang, #tenKH, #diaChi, #nguoiNhan').val('');
				$("#btnChonKhachHang").show();
			} else {

				$('#tenKH').attr('readonly', false);
				$('#idKhachHang, #tenKH, #diaChi, #nguoiNhan').val('');
				$("#btnChonKhachHang").hide();
			}
		},

		chonKhachHang: function() {

			var listCustomerLink = $("#listCustomerLink").val();

			var ketqua = onReady.sendAjax( {}, listCustomerLink );

			$("#customersTable").html(ketqua);
			$("#customersModal").show();
		},
		
		checkSubmit: function(e) {
			
			if( !$('.idProducts').length  ) {
				alert("Chưa nhập sản phẩm");
				e.preventDefault();
			}
			
			$('td.tonCuoiKy').each(function(){
				if($(this).text() < 0) {
					
					alert("Tồn Tại Sản Phẩm Có Tồn Cuối Kỳ Bị Âm");
					e.preventDefault();
				}
			});
		}
	}

	$( document ).ready( critiaPhieuXuatKho.onReady );

});

//tạo tr hiển thị sản phẩm đã chọn trong 
function taoTrChiTiet(indexTable, values){

	var trHtml = '<tr class="addTrTable">';
	
	trHtml += '<input class="idCtphieuXuatKho" type="hidden" name="idCtphieuXuatKho[]" value="-1">';
	
	trHtml += '<input class="idProducts" type="hidden" name="idProducts[]" value=' + values["id"] + '>';
	
	trHtml += '<td class="stt">' + (indexTable + 1) + '</td><td>' + values["maSanPham"] + '</td>';
	
	trHtml += '<td class="tenSP">' + values["tenSanPham"] + '</td>';

	trHtml += '<td class="tonHienTai">' + values["tonHienTai"] + '</td>';
	
	trHtml += '<td><input class="soLuong" type="number" name="soLuong[]" value="1" onchange="changeSoLuong(this)"></td>';
	
	trHtml += '<td><input class="ghiChu" type ="text" name="ghiChuCtphieuXuatKho[]" value=""></td>';
	
	trHtml += '<td class="tonCuoiKy">' + ( parseInt( values["tonHienTai"] ) - 1 ) + '</td>';
	
	trHtml += '<td><a href="#" title="Xóa sản phẩm này" class="w3-text-red" onclick="deleteTr(this)">✘</a></td>';
	
	trHtml += '</tr>';
	
	return trHtml;
}

function changeSoLuong(e){

	var soLuong = parseInt ( $(e).val() );

	if ( lonHonZero(e) ) {
		soLuong = 1;
	}
	
	var tonDauKy = parseInt( $(e).parent().siblings('.tonHienTai').text() );
	$(e).parent().siblings('.tonCuoiKy').text( tonDauKy - soLuong );

	tongSoLuong();
}

//đếm tổng số lượng của tất cả sp
function tongSoLuong() {

	var tongSoLuong = 0;

	$('.soLuong').each(function(){

		tongSoLuong += parseInt( $(this).val() );
	})

	$('#tongSoLuong').val( tongSoLuong );
}

//xác nhận phiếu xuất kho
function xacNhan(e) {

	var link = $("#linkSendXacNhan").val();
	var data = { id: $(e).parent().parent().find("input.id").val() }

	var ketqua = onReady.sendAjax( data, link );

	alert(ketqua);
	$(e).text('Đã Xác Nhận');
	$(e).attr('disabled','disabled');
}

function taoTrTatCa(data, linkEdit, linkDelete) {

	var html 	= "";
	var td 		= '';
	var button 	= '';

	$.each(data["items"], function(i, value) {

		button = "<button class='w3-green w3-button w3-round-large' onclick='xacNhan(this)'>Chưa Xác Nhận</button> ";
		if (value['idNguoiXacNhan'] != 0) {

			button = "<button class='w3-gray w3-button w3-round-large' disabled>" + value['nguoiXacNhan'] + "</button> ";
		}

		html += "<tr>";
		html += "<input type='hidden' class='id' value='" + value["id"] + "'>";
		html += "<td data-label ='STT'>" + ( i + 1 + ( data['current'] -1 ) * data['limit'] ) + "</td>";

		if ( data["duocXoa"] ) {
			html += "<td data-label ='Xóa'><a href='" + linkDelete + value["id"] + "' title='Xóa phiếu nhập này' class='w3-text-red delete' onclick='return confirm(\"Bạn Có Muốn Xóa Không\")'>&#10008;</a></td>";
		}
		
		html += "<td data-label ='Ngày'><a href='" + linkEdit + value["id"] + "' title='edit' class='edit'>" + value["ngay"] + "</a></td>";
		html += "<td data-label ='Tên KH'>" + value["tenKH"] + "</td>";
		html += "<td data-label ='Lý Do Xuất'>" + value["lyDoXuat"] + "</td>";
		html += "<td data-label ='Các Mã Hàng'>" + value["maHang"] + "</td>";
		html += "<td data-label ='Người Nhận'>" + value["nguoiNhan"] + "</td>";
		html += "<td data-label ='Tổng Số Lượng'>" + value["tongSoLuong"] + "</td>";
		html += "<td data-label ='Xác Nhận'>" + button + "</td>";
		html += "</tr>";

	});	

	return html;
}
