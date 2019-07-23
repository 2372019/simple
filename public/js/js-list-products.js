/*

file js này được sử dụng khi các page muốn lấy dữ liệu từ model này để hiển thị vào modal

*/

	var linkProductsEdit 	= $("#productsList #linkProductsEdit").val();	//link của function Edit của controller Products
	var listLinkSendAjax 	= $("#productsList #listLinkSendAjax").val();


	var critiaListProducts 	= {

		onReady: function() {

			var ketqua = onReady.sendAjax( {}, listLinkSendAjax );
			critiaListProducts.ketQua(ketqua);

			$("#productsList #btnResetFilter").click(critiaListProducts.resetAll);
			$("#productsList #listLimit").change(critiaListProducts.limit);
			$("#btnFilterListProduct").click(critiaListProducts.filter);
		},

		resetAll: function(){

			$("#productsList #listFilterRows").val("");

			var ketqua = onReady.sendAjax( { resetAll:1 }, listLinkSendAjax );
			critiaListProducts.ketQua(ketqua);	
		},

		limit: function(){

			var data = {

				limit : $(this).val(),
				filterRows : $("#productsList #listFilterRows").val()
			}

			var ketqua = onReady.sendAjax(data, listLinkSendAjax);

			critiaListProducts.ketQua(ketqua);
		},

		get filter(){return this.limit},

		ketQua: function(result){

			ketQuaAjaxProduct(result);
		}
	};

	$( document ).ready( critiaListProducts.onReady );


//nhấn phân trang
function listPaginationAjaxProducts(e) {

	var page = $(e).attr("title");
	var data = {

		page : page,
		filterRows : $("#productsList #listFilterRows").val()
	}

	var ketqua = onReady.sendAjax(data, listLinkSendAjax);

	ketQuaAjaxProduct(ketqua);
}

//orderBy các cột
function listOrderByProducts(column) {

	var orderBy 		= $(column).children().val(); //lấy value của input sắp xếp
	
	$("#productsList a.orderColumn").removeClass("w3-gray w3-green"); //xóa các class (các class đó được thêm khi chạy đoạn code ở dưới)

	$.ajax({

		url:  listLinkSendAjax,
		type: 'POST',
		data: { limit : $("#productsList #listLimit").val() , filterRows : $("#productsList #listFilterRows").val() , orderBy : orderBy }	// truyền các biến vào để gửi lên Ajax	
	}).done( function(result) {

		if (orderBy.search("DESC") != -1) { // Nếu cột vừa click là DESC thì đổi thành ASC
			
			$(column).children().val( orderBy.replace("DESC","ASC") );
			$(column).addClass("w3-gray");
			
		} else { //Ngược lại, thì đổi thành DESC
			
			$(column).children().val( orderBy.replace("ASC","DESC") );
			$(column).addClass("w3-green");
		}

		ketQuaAjaxProduct(result);
	});
}

function ketQuaAjaxProduct (result)
{
	var data 		= JSON.parse(result);

	$("#listLimit").val( data["limit"] ); // Reset value select limit option
	$("#productsList #listTable").empty();	//xóa html trong tbody

	var html = "";
	//append value vào tbody
	$.each(data["items"], function(i, value) {	
	
		html += htmlTrListProducts(i, data, value);
	});
	
	$("#productsList #listTable").append(html);
	
	paginationList(data, "#productsList #listPagination");
}
function htmlTrListProducts(i, data, value){
	var html = "<tr>";
	
	html	+= "<td><input type='checkbox' name='listCheckBox[]' value='" + value["id"] + "'></td>";

	html 	+= "<td data-label ='STT'>" + (i + 1 + (data["current"] - 1) * data["limit"] ) + "</td>";
	
	html	+= "<td data-label ='Mã SP' class='maSanPham'><a target='_blank' href='" + linkProductsEdit + value["id"] + "' title='Mã Sản Phẩm'>" + value["maSanPham"] + "</a></td>";
	
	html	+= "<td data-label ='Tên SP' class='tenSanPham'>" + value["tenSanPham"] + "</td>";
	
	html	+= "<td data-label ='Đơn Giá Bán' class='donGiaMoiNhat'>" + numberFormat(value["donGiaMoiNhat"]) + "</td>";
	
	html	+= "<td data-label ='Đơn Giá Gốc' class='donGiaMuaVao'>" + numberFormat(value["donGiaMuaVao"]) + "</td>";
	
	html	+= "<td data-label ='Tồn Kho BĐ' class='tonHienTai'>" + value["tonHienTai"] + "</td>";
	
	html	+= "<td data-label ='Tồn Kho Hiện Tại' class='tonKhoBanDau'>" + value["tonKhoBanDau"] + "</td>";

	html	+= "<td data-label ='Loại SP' class='loaiSanPham'>" + value["type"] + "</td>";
	
	html	+= "<td data-label ='Mô tả SP' class='moTa'>" + value['moTa'] + "</td>";
	
	return (html + "</tr>");
}

function paginationList(data, idTag){
	
	$(idTag).html("");
	
	html = '<a href="#" title="' + data["first"] + '" class="w3-button w3-hover-green" onclick="listPaginationAjaxProducts(this)">«</a>';
	
	for (var i = 1; i <= data['total_pages']; i++) {
		
		html = html + '<a href="#" onclick="listPaginationAjaxProducts(this)" title="' + i + '" class="w3-button w3-hover-gray ';
		
		if (i == data["current"])
			html = html + 'w3-blue w3-hover-green">' + i + '</a>';
		else 
			html = html + 'w3-hover-green">' + i + '</a>';
	}
	
	html = html + '<a href="#" title="' + data["last"] +'" class="w3-button w3-hover-green" onclick="listPaginationAjaxProducts(this)">»</a>';
	
	html = html + "( " + data["current"] + " of " + data["total_pages"] + " )";
	
	$(idTag).html(html);
}

//nhấn nut Chọn sau khi chọn các sản phẩm trong list
$("#productsList #btnArraySanPham").click(function(){

	if ($("#productsList :checkbox:checked").length < 1) {

		alert('Hãy Chọn Sản Phẩm Cần Thêm');

	} else {

		var tr = '';
		var tongtien = 0;
		//lấy tất cả dữ liệu của các row đã chọn gửi vào function khác
		$("#productsList :checkbox:checked").each(function(i){

			var values = {
				id: 			$(this).val(),
				maSanPham:		$(this).parent().siblings("td.maSanPham").text(),
				tenSanPham: 	$(this).parent().siblings("td.tenSanPham").text(),
				loaiSanPham: 	$(this).parent().siblings("td.loaiSanPham").text(),
				donGiaMoiNhat: 	$(this).parent().siblings("td.donGiaMoiNhat").text().replace(/đ|,/ig,''),
				donGiaMuaVao: 	$(this).parent().siblings("td.donGiaMuaVao").text().replace(/đ|,/ig,''),
				tonHienTai: 	$(this).parent().siblings("td.tonHienTai").text(),
				tonKhoBanDau: 	$(this).parent().siblings("td.tonKhoBanDau").text(),
				moTa:  			$(this).parent().siblings('td.moTa').text(),
			};

			tr += taoTrChiTiet(indexTable++, values); // File js-orders.js
		});

		$("#listProductsModal").hide();
		$('tr.addMaSanPham').before(tr);

		if (typeof tongCongChuaVAT == 'function') { tongCongChuaVAT(); }

		if (typeof tongTienThanhToan == 'function') { tongTienThanhToan(); }

		if (typeof tongSoLuong == 'function') { tongSoLuong(); }

		if (typeof disabledButtonPrint == 'function') { disabledButtonPrint(1); }
	}
});