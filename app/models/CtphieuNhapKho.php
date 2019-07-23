<?php 

class CtphieuNhapKho extends BaseModel
{
	public $id;					// <int> Not null, Default AI
    public $idPhieuNhapKho;     // <int> Not null
	public $idProducts;         // <int> Not null
	public $tonDauKy;           // <int> Not null
	public $soLuong;            // <int> Not null
	public $tonCuoiKy;          // <int> Not null
	public $donGia;           	// <double> Not null
	public $thanhTien;       	// <double> Not null
	public $ghiChu;        		// <string>
	
	public function initialize()  
	{
		$this->belongsTo("idPhieuNhapKho", "PhieuNhapKho", "id");
		$this->belongsTo("idProducts", "Products", "id");
	}
	
	// Return string ,
	public static function getMaSanPhamListByIdPhieuNhapKho($idPhieuNhapKho) {
		
		$ctList 		= CtphieuNhapKho::findByIdPhieuNhapKho( $idPhieuNhapKho );
		
		if ( !$ctList )
			return "";
		
		$maSanPhamList 	= $ctList[0]->Products->maSanPham;
		
		$leng = count($ctList);
		for ($i = 1; $i < $leng; $i++) {
			$maSanPhamList = $maSanPhamList . ',' . $ctList[$i]->Products->maSanPham;
		}
		
		return $maSanPhamList;
	}

	public static function createCtphieuNhapKho($phieuNhapKho, $data) {

		$phieuNhapKho->tongSoLuong	= 0;
		$tongTien					= 0;
		$listLength					= count($data['idProductsList']);
		$ctphieuNhapKho 			= array();

		for ($i = 0; $i < $listLength; $i++) {

			$product = Products::findFirstById( $data['idProductsList'][$i] );
			
			if ( !$product ) {continue;} // Sản phẩm ko tồn tại

			//đưa các biến vào model
			$ctphieuNhapKho[$i] = new CtphieuNhapKho([

				'idProducts'	=> $data['idProductsList'][$i],
				'tonDauKy'		=> $product->tonHienTai,
				'soLuong'		=> $data['soLuongList'][$i],
				'tonCuoiKy'		=> $product->tonHienTai + $data['soLuongList'][$i],
				'donGia'		=> (double)$data['donGiaList'][$i],
				'thanhTien'		=> $data['donGiaList'][$i] * $data['soLuongList'][$i],
				'ghiChu'		=> $data['ghiChuList'][$i]
			]);

			$tongTien					+= $data['donGiaList'][$i] * $data['soLuongList'][$i];
			$phieuNhapKho->tongSoLuong	+= $data['soLuongList'][$i];

			//cộng số lượng tồn kho tương ứng với số lượng nhập của từng sản phẩm
			$product->setTonHienTai( $product->tonHienTai + $data['soLuongList'][$i] );
		}

		$phieuNhapKho->CtphieuNhapKho 	= $ctphieuNhapKho;

		return $tongTien;
	}

	/** 
	* $newList <array>: Danh sách ID ctPhieuNhapKho mới
	* $oldObjList <array of Objects>: Danh sách Object CtphieuNhapKho cũ
	* Xóa ct cũ ko có trong $newList mới, đồng thời, update lại tồn hiện tại của sản phẩm có ct vừa xóa.
	**/
	private static function _deleteOldCtData( $newList, $oldObjList ){
		
		$leng = count($oldObjList);

		for ($i = 0; $i < $leng; $i++) {

			if ( !in_array( $oldObjList[$i]->id, $newList ) ) {

				$tonHienTai  = $oldObjList[$i]->Products->tonHienTai - $oldObjList[$i]->soLuong;
				
				if( $oldObjList[$i]->Products->setTonHienTai( $tonHienTai ) ) {
					$oldObjList[$i]->delete();
				}
			}
		}
	}

	/** 
	* Hàm này sẽ:
	* - Chuẩn bị dữ liệu cho PhieuNhapKho
	* - Xóa những ct cũ
	* - Chuẩn bị lại danh sách CtphieuNhapKho mới
	* - Đồng thời, cập nhật lại tồn hiện tại của mỗi sản phẩm liên quan
	* Tham số:
	* - $data: Dữ liệu từ Form gửi về
	* - $oldObjList: List of Objects <CtphieuNhapKho>
	* Trả về:
	* - $kq = array ('tongSoLuong', 'tongTien', 'ctObjList')
	* - $kq['ctObjList']: List of Objects <CtphieuNhapKho> mới
	* 
	**/
	public static function prepareCtData_UpdateTonProducts( $phieuNhapKho, $data ){
		
		$oldObjList = $phieuNhapKho->CtphieuNhapKho;
		
		// Xóa những ct cũ ko có trong $data['idCtphieuNhapKhoMoi']
		CtphieuNhapKho::_deleteOldCtData( $data['idCtphieuNhapKhoMoi'], $oldObjList );

		$leng 			= count( $data['idProductsList'] );
		$objList		= array();
		$oldObjListLeng = count($oldObjList);
		$thanhTien      = 0;
		$soLuong 		= 0;

		$kqUpdateProduct = true;
		
		for ($i = 0; $i < $leng; $i++)
		{
			$pr = Products::findFirstById( $data['idProductsList'][$i] );

			if ( !$pr ) { 
				$this->getDI()->getShared("flashSession")->warning("Id sản phẩm ko tồn tại: " . $data['idProductsList'][$i]);
				continue;
			}
			
			$objList[$i]	= new CtphieuNhapKho([
				'idProducts'	=> $data['idProductsList'][$i],
				'tonDauKy'		=> $pr->tonHienTai,
				'soLuong'		=> $data['soLuongList'][$i],
				'tonCuoiKy'		=> $pr->tonHienTai + $data['soLuongList'][$i],
				'donGia'		=> (double)$data['donGiaList'][$i],
				'thanhTien'		=> $data['donGiaList'][$i] * $data['soLuongList'][$i],
				'ghiChu'		=> $data['ghiChuList'][$i]
			]);
			
			if ( $data['idCtphieuNhapKhoMoi'][$i] < 0 ) // Thêm CtphieuNhapKho mới 
			{
				// Gán lại tonHienTai của sản phẩm chính là tonCuoiKy
				$kqUpdateProduct = $pr->setTonHienTai( $objList[$i]->tonCuoiKy );
				
				if ( $kqUpdateProduct === false ) {
					return false;
				}
			}
			else {  // CtphieuNhapKho cũ
				
				// Tìm ra ct cũ
				for ($index = 0; $index < $oldObjListLeng; $index++) {
										
					if ( $data['idCtphieuNhapKhoMoi'][$i] == $oldObjList[$index]->id ) {
						
						// Update lại tonHienTai của sản phẩm
						$kqUpdateProduct = $pr->setTonHienTai( $pr->tonHienTai + 
											$data['soLuongList'][$i] - 
											$oldObjList[$index]->soLuong );
											
						if ( $kqUpdateProduct === false ) {
							return false;
						}
						
						/* Đã tìm được ct cũ, gán lại một số biến
						*  	- Gán lại Id
						*   - Gán lại tonDauKy
						*	- Tình lại tonCuoiKy = tonDauKy + soLuong
						*/
						$objList[$i]->id	= $oldObjList[$index]->id;
						
						$objList[$i]->tonDauKy  = $oldObjList[$index]->tonDauKy;
						$objList[$i]->tonCuoiKy	= $objList[$i]->tonDauKy + $data['soLuongList'][$i];
						
						break; // Thoát khỏi vòng lặp sau khi tìm thấy.
					}
				}
			}
			
			$phieuNhapKho->tongSoLuong	 += $objList[$i]->soLuong;
			$phieuNhapKho->tongThanhToan += $objList[$i]->thanhTien;
		}
		
		$phieuNhapKho->CtphieuNhapKho = $objList;
	}

	public static function createDatHangCtPhieuNhapKho($phieuNhapKho, $dathang)
	{
		$ctdatHang 		= $dathang->CtdatHang;
		$ctphieuNhapKho = array();
		$tongSoLuong	= 0;

		for ($i = 0; $i < count($ctdatHang); $i++) {

			$product = Products::findFirstById($ctdatHang[$i]->idProducts);
			
			$ctphieuNhapKho[] = new CtphieuNhapKho([

				'idProducts'	=> $ctdatHang[$i]->idProducts,
				'tonDauKy'		=> $product->tonHienTai,
				'soLuong'		=> $ctdatHang[$i]->soLuong,
				'tonCuoiKy'		=> $product->tonHienTai + $ctdatHang[$i]->soLuong,
				'donGia'		=> $ctdatHang[$i]->donGia,
				'thanhTien'		=> $ctdatHang[$i]->thanhTien,
				'ghiChu'		=> $ctdatHang[$i]->ghiChu
			]);

			$tongSoLuong += $ctdatHang[$i]->soLuong;

			$product->setTonHienTai( $product->tonHienTai + $ctdatHang[$i]->soLuong );
		}

		$phieuNhapKho->CtphieuNhapKho = $ctphieuNhapKho;

		return $tongSoLuong;
	}
}