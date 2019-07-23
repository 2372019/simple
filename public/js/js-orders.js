$( window ).on("load", function() { // 1. Window load # DOM Ready
	
	//add ckeditor
	CKEDITOR.replace('ghiChu',editor());

	//click nút check xuất hóa đơn
	$("#coXuatHoaDonKhong").click(function(){	
		tongTienThanhToan();
	});

	//click để hiện ra list các khách hàng
	$("#btnChonKhachHang").click(function(){
		
		var listCustomerLink = $("#listCustomerLink").val();

		var ketqua = onReady.sendAjax( {}, listCustomerLink );

		$("#customersTable").html(ketqua);
		$("#customersModal").show();
	});
	
	$('#submit').click(function(e){
		if( !$('.idProducts').length  ) {
			alert("Chưa nhập sản phẩm");
			e.preventDefault();
		}
	});

	//khi nhấn nút xuất kho thì gửi dữ liệu đi và disabled button
	$('#clickXuatKho').click(function(e) {

		$("#formXuatKho").submit();
		$(this).attr('disabled',true);
	})
});

var flag = 0;
disabledButtonPrint(flag);
//kiểm tra có đang ở trang edit k và disabled nút Print nếu có chỉnh sửa
function disabledButtonPrint(flag){

	//kiểm tra có đang ở trang edit k
	if (window.location.href.match(/edit/g)) {

		if(flag == 1){
			$('.print button').prop('disabled','disabled');//disabled nút print
		}

		$('form select#hinhThucThanhToan, form select#idEmployees, form select#idCongTyBanHang, form select#trangThai, form input.soLuong, form input.ghiChu, form input#diaChiGiaoHang, form input#thongTinNguoiNhanHang, form input#chiPhiGiaoHang, form input#hoaHong, form input#daThanhToan, form input#hoaHong, form input#congNo, form input.donGiaMoiNhat').change(function(e){

		    $('.print button').prop('disabled','disabled')
		});

		$('form input#ngayHenThanhToan').on('input',function(e){

		    $('.print button').prop('disabled','disabled')
		});

	}
}

function taoTrTatCa(data, linkEdit, linkDelete) {

	var html 			= "";
	var linkView 		= $("#linkView").val();
	var tongCongNo 		= 0;
	var tongTienChuaVAT = 0;
	var tongThueVAT		= 0;
	var tongTien 		= 0;
	var tongChiPhiGH 	= 0;
	var linkEditOrView;
	
	$.each(data["items"], function(i, value) {

		var select = $('<select id="trangThai" name="trangThai" class="trangThai" onchange="updateTrangThai(this)"><option value="choThanhToan">Chờ Thanh Toán</option><option value="choXacNhan">Chờ Xác Nhận</option><option value="conNo">Còn Nợ</option><option value="huyTruocBH">Hủy Trước BH</option><option value="huySauBH">Hủy Sau BH</option><option value="khac">Khác</option></select>');

		switch ( value["trangThai"] ) {
			case 'hoanTat':
				trangThai = 'Hoàn Tất';
				break;
			case 'huySauBH':
				trangThai = 'Hủy Sau BH';
				break;
			case 'huyTruocBH':
				trangThai = 'Hủy Trước BH';
				break;
			default:
				$(select).find('option[value=' + value["trangThai"] + ']').attr("selected",true);
				trangThai = select[0].outerHTML;
		}

		linkEditOrView 	= linkEdit;
		//kiểm tra trường duocXem nếu = 1 thì gán linkView ngược lại gán linkEdit
		if (value['duocXem'] == 1) { linkEditOrView = linkView }

		html += "<tr><input type='hidden' class='id' name='id' value='" + value["id"] + "'>";
		html += "<td data-label ='STT'>" + ( i + 1 + ( data['current'] -1 ) * data['limit'] ) + "</td>";

		if ( data["duocXoa"] ) {
			html += "<td data-label ='Xóa'><a href='" + linkDelete + value["id"] + "' title='Xóa sản phẩm này' class='w3-text-red delete' onclick='return confirm(\"Bạn Có Muốn Xóa Không\")'>&#10008;</a></td>";
		}

		html += "<td class='trangThai' data-label ='Ngày'><a href='" + linkEditOrView + value["id"] + "' title='Sửa đơn hàng' class='edit'>" + value["ngay"] + "</a></td>";

		html += "<td data-label ='Trạng Thái'>" + trangThai + "</td>";
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
		html += "<td data-label ='Chi Phí Giao Hàng'>" + numberFormat(value["chiPhiGiaoHang"]) + "</td>";
		html += "<td data-label ='Xuất Kho' class='w3-right-align'>" + value["xuatKho"] + "</td></tr>";
		
		html += "</tr>";

		tongCongNo 		+= parseInt(value["congNo"]);
		tongTienChuaVAT += parseInt(value["tongCongChuaVAT"]);
		tongThueVAT		+= parseInt(value["thueVAT"]);
		tongTien 		+= parseInt(value["tongTienThanhToan"]);
		tongChiPhiGH 	+= parseInt(value["chiPhiGiaoHang"]);
	});

	html += "<tr>";
	html += "<td colspan ='4'></td>";
	html += "<td class='w3-text-blue w3-align-right w3-medium'>Tổng Cộng: </td>";
	html += "<td class='w3-text-blue w3-align-right w3-medium'>"+ numberFormat(tongTien) +"</td>";
	html += "<td class='w3-text-blue w3-align-right w3-medium'>"+ numberFormat(tongCongNo) +"</td>";
	html += "<td colspan ='4'></td>";
	html += "<td class='w3-text-blue w3-align-right w3-medium'>"+ numberFormat(tongTienChuaVAT) +"</td>";
	html += "<td class='w3-text-blue w3-align-right w3-medium'>"+ numberFormat(tongThueVAT) +"</td>";
	html += "<td colspan ='3'></td>";
	html += "<td class='w3-text-blue w3-align-right w3-medium'>"+ numberFormat(tongChiPhiGH) +"</td>";
	html += "<td></td></tr>";	

	return html;
}

//tạo tr hiển thị sản phẩm đã chọn trong order
function taoTrChiTiet(indexTable, values){

	var trHtml = '<tr class="addTrTable">';
	
	trHtml += '<input class="idCtorders" type="hidden" name="idCtorders[]" value="-1">';
	
	trHtml += '<input class="idProducts" type="hidden" name="idProducts[]" value=' + values["id"] + '>';

	trHtml += '<input class="moTaProducts" type="hidden" value="<strong>' + values["tenSanPham"]+ '</strong><br>' + values['moTa'] + '">';
	
	trHtml += '<td class="stt">' + (indexTable + 1) + '</td><td>' + values["maSanPham"] + '</td>';
	
	trHtml += '<td class="tenSP">' + values["tenSanPham"] + '</td>';
	
	trHtml += '<td><input class="soLuong" type="number" name="soLuong[]" value="1" onchange="changeSoLuong(this)"></td>';
	
	trHtml += '<td class="donGiaMoiNhat"><input class="donGiaMoiNhat w3-right-align" type ="text" name="donGiaMoiNhat[]" value=' + numberFormat(values["donGiaMoiNhat"]) + ' onchange="changeDonGiaMoiNhat(this)"></td>';
	
	trHtml += '<td class="thanhTien w3-right-align">' + numberFormat( parseFloat(values["donGiaMoiNhat"]) ) + '</td>';
	
	trHtml += '<td><input class="ghiChu" type ="text" name="ghiChuCtorders[]" value=""></td>';
	
	trHtml += '<td class="tonHienTai">' + values["tonHienTai"] + '</td>';
	
	trHtml += '<td><a href="#" title="Xóa sản phẩm này" class="w3-text-red" onclick="deleteTr(this)">✘</a></td>';
	
	trHtml += '</tr>';
	
	return trHtml;
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

//tăng tiền khi thay đổi số lượng các sản phẩm trong thêm/edit order
function changeSoLuong(e){
	
	lonHonZero(e);

	var donGiaMoiNhat = parseInt( $(e).parent() .next() .children() .val() .replace(/đ|,/ig,'') );

	var thanhTien = numberFormat( parseInt( $(e).val() ) * donGiaMoiNhat );

	$(e).parent() .nextAll("td.thanhTien") .text( thanhTien );

	tongCongChuaVAT(); //call function
	tongTienThanhToan(); //call function
}

//thay đổi đơn giá các sản phẩm trong thêm/edit order
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


/*-------------THỰC HIỆN IN CÁC PHIẾU---------------*/

//sau khi chọn select công ty thì tọa header của print
$("#idCongTyBanHang.chonCT").change(function(){

	var chonCT = $("#idCongTyBanHang.chonCT").val();

	switch (chonCT) {
        case "1":
            $("#header-print").html('<div class="w3-col l3 m3 s3 w3-center hinh"><img src="/qlycty/img/tbt.png" alt="" style="width:100%"></div><div class="w3-col l9 m9 s9"><span class="w3-bold">CÔNG TY TNHH XNK SX TM THIẾT BỊ TỐT </span> <br><u>Headquarters</u>: 154/4/33 Nguyễn Phúc Chu, P.15, Q. Tân Bình, HCM <br><u>Office Add:</u> : Tầng M Cao ốc Pntechcons, 48 Hoa Sứ, P.7, Q. Phú Nhuận, HCM <br><u>Email</u> : thietbitot.vn@gmail.com  &nbsp; - &nbsp; Tel:&nbsp;08 6679 4228 </div>');
            $("#thuchienin .kyten").html('Lê Như Ngọc');
            break;
        case "2":
            $("#header-print").html('<div class="w3-col l3 m3 s3 w3-center hinh"><img src="/qlycty/img/yama.png" alt="" style="width:95%"></div><div class="w3-col l9 m9 s9"><span class="w3-bold" style="font-size: 18px">CÔNG TY TNHH YAMA MACHINE </span> <br><u>Địa Chỉ</u>: 04 Vạn Hạnh, P. Tân Thành, Q. Tân Phú, TP. Hồ Chí Minh <br><u>Văn Phòng:</u> : 212/22 Nguyễn Thiện Thuật, phường 3, Quận 3, Hồ Chí Minh <br><u>Điện Thoại:</u> (028) 6271 5879 – Hotline: 0906 393 567 - Email: info@yama.vn </div>');
            $("#thuchienin .kyten").html('');
            break;
        case "3":
            $("#header-print").html('<div class="w3-col l3 m3 s3 w3-center hinh"><img src="/qlycty/img/nt.png" alt="" style="width:113px"></div><div class="w3-col l9 m9 s9"><span class="w3-bold" style="font-size: 18px">CÔNG TY TNHH CÔNG NGHIỆP NẶNG NT </span> <br><u>Headquarters</u>: 206/26/1 Huỳnh Thị Hai, KP4, P. Tân Chánh Hiệp, Q.12 <br><u>Email</u> : maynenkhi.nt@gmail.com &nbsp; - &nbsp; Tel: (028) 6271 5879 </div>');
            $("#thuchienin .kyten").html('Nguyễn Ngọc Hà');
            break;
        default:
        	$("#header-print").html('<div class="w3-col l3 m3 s3 w3-center hinh"><img src="/qlycty/img/tv.png" alt="" style="width:95%"></div><div class="w3-col l9 m9 s9"><span class="w3-bold" style="font-size: 18px">CÔNG TY TNHH CÔNG NGHỆ DỊCH VỤ TÀI VIỆT </span> <br><u>Địa chỉ</u>: 04 Vạn Hạnh, P. Tân Thành, Q. Tân Phú <br><u>Điện Thoại</u> : 08 6679 4228 &nbsp; - &nbsp; Hotline: 0938 454 978 &nbsp; - &nbsp; Email:&nbsp;maynenkhi101@gmail.com </div>');
        	$("#thuchienin .kyten").html('');
            break;
    }
})

//phiếu thu
$("#phieuthu").click(function(){

	var nameId = $("#inphieuthu");

	var lyDoThu = '';
	$("#addTable .addTrTable .tenSP").each(function(key,value){

		lyDoThu = lyDoThu + value.textContent + ', ';
	})

	$("#inLyDoThu").text( lyDoThu );

	if ($("#ghiChu").val()) {

		$("#inGhiChu").text( $("#ghiChu").val() + ".");
	}

	$.ajax ({

        url: $("#linkSendPhieuThu").val(),
        type: 'POST',
        data: { data : parseInt( $("#tongTienThanhToan").val() .replace(/đ|,/ig,'') ) }
   	}).done( function(result){

   		$("#inTongTien").html( $("#tongTienThanhToan").val().bold() + " đ - Bằng chữ: " + result + " đồng." );
   		lenhPrint(nameId);
	})

});

//phiếu xuất kho
$("#xuatkho").click(function(){

	var nameId = $("#phieuxuatkho");

	var mtspPhieuXuatKho = "<p>Thông Tin Sản Phẩm: </p>";
	var i=1;
	$("#addTable .addTrTable").each(function(){

		mtspPhieuXuatKho += "<tr><td class='w3-center'>" + i + "</td><td>" + $(this).find("input.moTaProducts").val() + "</td><td class='w3-center'>"+ $(this).find("td input.soLuong").val() +"</td><td></td></tr>";
		i++;
	});

	$("#inTablePhieuXuatKho").html(mtspPhieuXuatKho);

	lenhPrint(nameId);
});

//phiếu giao hàng
$("#giaohang").click(function(){

	var nameId = $("#phieugiaohang");
	$("#inThongTinKhachHang").text($("#thongTinNguoiNhanHang").val() + ".");

	var spPhieuGiaoHang = "";
	var spHoaDonVAT = "";
	var i=1;

	$("#addTable .addTrTable").each(function(){

		spPhieuGiaoHang += "<tr><td class='w3-center'>" + i + "</td><td>" + $(this).find("td.tenSP").text() + "</td><td class='w3-center'>"+ $(this).find("td input.soLuong").val() +"</td><td>" + $(this).find("td input.ghiChu").val() + "</td></tr>";
		i++;
	});

	if ( $("#coXuatHoaDonKhong").is(':checked') ) {
		
		spHoaDonVAT = "<tr><td class='w3-center'>"+ i++ +"</td><td>Hóa đơn VAT</td><td class='w3-center'>01</td><td></td></tr>";
	}

	spPhieuGiaoHang += spHoaDonVAT + "<tr><td class='w3-center'>" + i++ +"</td><td>Phiếu thu</td><td class='w3-center'>01</td><td></td></tr><tr><td class='w3-center'>"+ i++ +"</td><td>HDSD</td><td class='w3-center'>01</td><td><span class='w3-bold'><u>Lưu ý:</u> Đọc kỹ hướng dẫn</span></td></tr><tr><td class='w3-center'>"+ i++ +"</td><td>Phiếu xuất kho</td><td class='w3-center'>01</td><td></td></tr><tr><td class='w3-center'>"+ i++ +"</td><td>Phiếu bảo hành</td><td class='w3-center'>01</td><td></td></tr>";

	$("#inTablePhieuGiaoHang").html(spPhieuGiaoHang);

	lenhPrint(nameId);
});

function lenhPrint(nameId) {

	var chonCT = $("#idCongTyBanHang.chonCT").val();
	
	$(".inTenKhachHang").text($("#tenKhachHang").val() + ".");
	$(".inDiaChiKhachHang").text($("#diaChiGiaoHang").val() + ".");
	
	if (chonCT) {

        nameId.css("display","block");
        
		window.print();
		window.addEventListener("afterprint", nameId.hide());
		

	} else {
		alert('Vui lòng chọn Tên Công Ty ');
	}
}

/*-------------END THỰC HIỆN IN CÁC PHIẾU---------------*/