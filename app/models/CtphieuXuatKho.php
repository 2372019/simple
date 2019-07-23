<?php 

class CtphieuXuatKho extends BaseModel
{
	public $id;					// <int> Not null, Default AI
    public $idPhieuXuatKho;     // <int> Not null
	public $idProducts;         // <int> Not null
	public $tonDauKy;           // <int> Not null
	public $soLuong;            // <int> Not null
	public $tonCuoiKy;          // <int> Not null
	public $ghiChu;        		// <string>   
	
	public function initialize()  
	{
		$this->belongsTo("idPhieuXuatKho", "PhieuXuatKho", "id");
		$this->belongsTo("idProducts", "Products", "id");
	}

	public static function createCtPhieuXuatKho($data)
	{
		$ctphieuXuatKho = array();
		$tongSoLuong	= 0;

		for ($i = 0; $i < count($data['idProductsList']); $i++) {

			$product = Products::findFirstById( $data['idProductsList'][$i] );
			
			//kiểm tra có sản phẩm có tồn tại hay k
			if ( !$product ) {
				$this->getDI()->getShared("flashSession")->warning("Id sản phẩm ko tồn tại: " . $data['idProductsList'][$i]);
				continue;
			}

			//đưa các biến vào model
			$ctphieuXuatKho[$i] = new CtphieuXuatKho([

				'idProducts'	=> $data['idProductsList'][$i],
				'tonDauKy'		=> $product->tonHienTai,
				'soLuong'		=> $data['soLuongList'][$i],
				'tonCuoiKy'		=> $product->tonHienTai - $data['soLuongList'][$i],
				'ghiChu'		=> $data['ghiChuList'][$i]
			]);
			
			if ( !$product->setTonHienTai( $product->tonHienTai - $data['soLuongList'][$i] ) ) {
				return false;
			}
			
			$tongSoLuong	+= $data['soLuongList'][$i];
		}

		return ['ctphieuXuatKho' => $ctphieuXuatKho, 'tongSoLuong' => $tongSoLuong];
	}

	public function createOrderCtPhieuXuatKho($ctOrders, $phieuXuatKho){

		$ctPhieuXuatKho = array();
    	$tongSoLuong	= 0;
    	$leng 			= count($ctOrders);

    	for ($i = 0; $i < $leng; $i++) {

    		$product = Products::findFirstById( $ctOrders[$i]['idProducts'] );

    		//kiểm tra có sản phẩm có tồn tại hay k
			if ( !$product ) { continue; }
    		
    		$ctPhieuXuatKho[$i] = new CtphieuXuatKho([

				'idProducts'	=> $ctOrders[$i]['idProducts'],
				'tonDauKy'		=> $product->tonHienTai,
				'soLuong'		=> $ctOrders[$i]['soLuong'],
				'tonCuoiKy'		=> $product->tonHienTai - $ctOrders[$i]['soLuong'],
				'ghiChu'		=> $ctOrders[$i]['ghiChu']
			]);

			$tongSoLuong	+= $ctOrders[$i]['soLuong'];
			
			$product->setTonHienTai( $product->tonHienTai - $ctOrders[$i]['soLuong'] );
		}

		$phieuXuatKho->CtphieuXuatKho 	= $ctPhieuXuatKho;

		return $tongSoLuong;
	}

	public static function createCtPhieuXuatKhoForThanhPham($ctthanhpham){

		$ctphieuXuatKho = array();
		$tongSoLuong	= 0;

		for ($i = 0; $i < count($ctthanhpham); $i++) {
			
			$product = Products::findFirstById( $ctthanhpham[$i]->idCtProducts );
				
			if ( !$product ) {continue;} // Sản phẩm ko tồn tại

			//đưa các biến vào model
			$ctphieuXuatKho[$i] = new CtphieuXuatKho([

				'idProducts'	=> $product->id,
				'tonDauKy'		=> $product->tonHienTai,
				'soLuong'		=> $ctthanhpham[$i]->soLuongVatTu + $ctthanhpham[$i]->soLuongThem,
				'tonCuoiKy'		=> $product->tonHienTai - $ctthanhpham[$i]->soLuongVatTu - $ctthanhpham[$i]->soLuongThem,
				'ghiChu'		=> $ctthanhpham[$i]->ghiChu
			]);

			$tongSoLuong	+= $ctphieuXuatKho[$i]->soLuong;

			//trừ số lượng tồn kho tương ứng với số lượng nhập của từng sản phẩm
			$product->setTonHienTai( $product->tonHienTai - $ctphieuXuatKho[$i]->soLuong );
		}

		return ['ctphieuXuatKho' => $ctphieuXuatKho, 'tongSoLuong' => $tongSoLuong];
	}
	
	
	/** 
	* $newList <array>: Danh sách ID ctPhieuXuatKho mới
	* $oldObjList <array of Objects>: Danh sách Object CtphieuNhapKho cũ
	* Xóa ct cũ ko có trong $newList mới, đồng thời, update lại tồn hiện tại của sản phẩm có ct vừa xóa.
	**/
	/*
	private static function _deleteOldCtData( $newList, $oldObjList ){
		
		$leng = count($oldObjList);

		for ($i = 0; $i < $leng; $i++) {

			if ( !in_array( $oldObjList[$i]->id, $newList ) ) {

				// 1, cập nhật tồn hiện tại của sản phẩm
				$tonHienTai  = $oldObjList[$i]->Products->tonHienTai + $oldObjList[$i]->soLuong;
				
				if( $oldObjList[$i]->Products->setTonHienTai( $tonHienTai ) ) {
					$oldObjList[$i]->delete();
				}
				
				// 2, cập nhật lại tồn đầu kỳ và cuối kỳ của ct liền sau ct vừa xóa và có liên quan đến sản phẩm
				
			}
		}
	}
*/
	/** 
	* Hàm này sẽ:
	* - Chuẩn bị dữ liệu cho PhieuXuatKho
	* - Xóa những ct cũ
	* - Chuẩn bị lại danh sách CtphieuXuatKho mới
	* - Đồng thời, cập nhật lại tồn hiện tại của mỗi sản phẩm liên quan
	* Tham số:
	* - $data: Dữ liệu từ Form gửi về
	* - $oldObjList: List of Objects <CtphieuXuatKho>
	* Trả về:
	* - $kq = array ('tongSoLuong', 'tongTien', 'ctObjList')
	* - $kq['ctObjList']: List of Objects <CtphieuXuatKho> mới
	* 
	**/
	
	/*
	public static function prepareCtData_UpdateTonProducts( $phieuXuatKho, $data ){
		
		$oldObjList = $phieuXuatKho->CtphieuXuatKho;
		
		// Xóa những ct cũ ko có trong $data['idCtphieuXuatKhoMoi']
		CtphieuXuatKho::_deleteOldCtData( $data['idCtphieuXuatKhoMoi'], $oldObjList );

		$leng 			= count( $data['idProductsList'] );
		$objList		= array();
		$oldObjListLeng = count($oldObjList);
		$soLuong 		= 0;
		
		$kqUpdateProduct = true;
		
		for ($i = 0; $i < $leng; $i++)
		{
			$pr = Products::findFirstById( $data['idProductsList'][$i] );

			if ( !$pr ) {
				$this->getDI()->getShared("flashSession")->warning("Id sản phẩm ko tồn tại: " . $data['idProductsList'][$i]);
				continue;
			}
			
			$objList[$i]	= new CtphieuXuatKho([
				'idProducts'	=> $data['idProductsList'][$i],
				'tonDauKy'		=> $pr->tonHienTai,
				'soLuong'		=> $data['soLuongList'][$i],
				'tonCuoiKy'		=> $pr->tonHienTai - $data['soLuongList'][$i],
				'ghiChu'		=> $data['ghiChuList'][$i]
			]);
			
			if ( $data['idCtphieuXuatKhoMoi'][$i] < 0 ) // Thêm CtphieuNhapKho mới 
			{
				// Gán lại tonHienTai của sản phẩm chính là tonCuoiKy
				$kqUpdateProduct = $pr->setTonHienTai( $objList[$i]->tonCuoiKy );
				
				if ( $kqUpdateProduct == false ) {
					return false;
				}
			}
			else {  // CtphieuNhapKho cũ
				
				// Tìm ra ct cũ
				for ($index = 0; $index < $oldObjListLeng; $index++) {
										
					if ( $data['idCtphieuXuatKhoMoi'][$i] == $oldObjList[$index]->id ) {
						
						// Update lại tonHienTai của sản phẩm
						$kqUpdateProduct = $pr->setTonHienTai( $pr->tonHienTai - 
											$data['soLuongList'][$i] + 
											$oldObjList[$index]->soLuong );
						
						if ( $kqUpdateProduct == false )
							return false;
						
						/* Đã tìm được ct cũ, gán lại một số biến
						*  	- Gán lại Id
						*   - Gán lại tonDauKy
						*	- Tình lại tonCuoiKy = tonDauKy + soLuong
						
						$objList[$i]->id	= $oldObjList[$index]->id;
						
						$objList[$i]->tonDauKy  = $oldObjList[$index]->tonDauKy;
						$objList[$i]->tonCuoiKy	= $objList[$i]->tonDauKy - $data['soLuongList'][$i];
						
						break; // Thoát khỏi vòng lặp sau khi tìm thấy.
					}
				}
			}
			
			$phieuXuatKho->tongSoLuong	 += $objList[$i]->soLuong;
		}
		
		$phieuXuatKho->CtphieuXuatKho = $objList;
	}*/
}