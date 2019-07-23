<?php 

use Phalcon\Mvc\Model\Relation;

class PhieuNhapKho extends BaseModel
{
	public $id;					// <int> Not null, Default AI
    public $ngay;              	// <timestamp> Not null
	public $noiDung;            // <string> Not null
	public $ghiChu;            	// <string> Not null, Default 0
	public $lyDoNhap;           // <string> Not null, Default 0
	public $idNhaCungCap;       // <int> Not null
	public $tenNhaCungCap;      // <string> Biến này ko thêm vào database
	public $tongSoLuong;        // <int> Not null, Default 0   
	public $tongThanhToan;      // <double> Not null
	public $thueVAT;			// <int> Not null
	public $daThanhToan;		// <string>
	public $conNo;				// <string>
	public $idNguoiLapPhieu;	// <int> Not null // NhanSu
	public $idNguoiNhanHang;		// <int> Not null // NhanSu
	public $nguoiNhanHang;		// <string> Biến này ko thêm vào database
	public $maHang;				// <string> Biến này ko thêm vào database
	

	public function initialize()  
	{
		$this->ngay = date('Y-m-d H:i:s');

		$this->hasOne("idNhaCungCap", "NhaCungCap", "id");
		$this->hasOne("idNguoiNhanHang", "NhanSu", "id");
		$this->hasOne("idNguoiLapPhieu", "Users", "id");
		$this->hasOne("id", "DatHang", "idMuaVao",[
			'foreignKey' 	=> [
				'message'	=> 'Không Được Xóa Vì Nó Được Nhập Kho Từ Mua Vào',
			]
		]);

		$this->hasMany("id", "CtphieuNhapKho", "idPhieuNhapKho",[
			'foreignKey' => [
                'action' => Relation::ACTION_CASCADE,//vd: nếu xóa PhieuNhapKho thì các ctphieuNhapKho liên quan cũng bị xóa theo
            ]
		]);
		
		$this->hasManyToMany(
            'id',
            'CtphieuNhapKho',
            'idPhieuNhapKho', 'idProducts',
            'Products',
            'id'
        );
	}

	public function getThongTinRow() {
		
		$kq = $this->toArray();

		$kq['tenNhaCungCap'] = $this->NhaCungCap->tenNhaCungCap;
		$kq['nguoiNhanHang'] = isset($this->NhanSu->hoTen) ? $this->NhanSu->hoTen : '';
		$kq['maHang']		 = Products::getMaSanPhamList($this->CtphieuNhapKho);
		$kq['ngay']			 = date_format(date_create($this->ngay),'d-m-Y H:i:s');
		
		return $kq;
	}

	public function createPhieuNhapKho($data){

		if ( ( $result = CtphieuNhapKho::createCtphieuNhapKho($this, $data) ) === false ) {
			return false;
		}

		$this->tongThanhToan 	= 0;

		//xuất hóa đơn có checked k
		if ( $data['coXuatHoaDonKhong'] == 1 )
			$this->thueVAT		= $result / 10;
		else
			$this->thueVAT		= 0;

		$this->tongThanhToan	= $result + $this->thueVAT;
		$this->conNo			= $this->tongThanhToan - $this->daThanhToan;

		return true;
		
	}

	public function createPhieuNhapKhoForThanhPham($thanhpham, $user){

		$product 	= Products::findFirstById( $thanhpham->idProducts );

		$soLuong 	= $thanhpham->soLuongThanhPham;
		$thanhTien	= $product->donGiaMoiNhat * $soLuong;

		$this->noiDung 			= 'Nhập Thành Phẩm - ' . $product->tenSanPham;
		$this->lyDoNhap			= 'Thành Phẩm SX';
		$this->idNhaCungCap		= 22;
		$this->tongSoLuong		= $soLuong;
		$this->tongThanhToan 	= $thanhTien;
		$this->thueVAT			= 0;
		$this->daThanhToan		= $thanhTien;
		$this->conNo			= 0;
		$this->idNguoiLapPhieu	= $user['id'];
		$this->idNguoiNhanHang	= 1;

		$ctphieuNhapKho = new CtphieuNhapKho([

			'idProducts'	=> $product->id,
			'tonDauKy'		=> $product->tonHienTai,
			'soLuong'		=> $soLuong,
			'tonCuoiKy'		=> $product->tonHienTai + $soLuong,
			'donGia'		=> $product->donGiaMoiNhat,
			'thanhTien'		=> $thanhTien,
			'ghiChu'		=> ''
		]);

		if ( !$product->setTonHienTai( $product->tonHienTai + $soLuong ) ) {
			$this->getDI()->getShared("flashSession")->warning(
				"Lỗi ko cập nhật được bảng sản phẩm:" . $product->tenSanPham
			);
		}

		$this->CtphieuNhapKho = $ctphieuNhapKho;
	}

	public function createDatHangPhieuNhapKho($dathang, $user)
	{

		if ( ($kq = CtphieuNhapKho::createDatHangCtPhieuNhapKho($this, $dathang)) === false ) {
			return false;
		}

		$this->noiDung 			= 'Theo Đơn Đặt Hàng Ngày : '. $dathang->ngay;
		$this->lyDoNhap			= 'Vật Tư';
		$this->idNhaCungCap		= $dathang->idNhaCungCap;
		$this->tongSoLuong		= $kq;
		$this->coXuatHoaDonKhong= $dathang->coXuatHoaDonKhong;
		$this->tongThanhToan 	= $dathang->tongTienThanhToan;
		$this->thueVAT			= $dathang->thueVAT;
		$this->daThanhToan		= $dathang->daThanhToan;
		$this->conNo			= $dathang->congNo;
		$this->idNguoiLapPhieu	= $user['id'];
		$this->idNguoiNhanHang	= 21;

		return true;
	}

	/* Hàm này cập nhật (update/new) toàn bộ CtphieuNhapKho dựa trên
	* $data['idCtphieuNhapKhoMoi'] được cung cấp. 
	* Nếu các chi tiết cũ ko có trong $data thì nó sẽ xóa đi
	* Sau khi xóa, nó sẽ cập nhật lại toàn bộ số tồn đầu kỳ và cuối kỳ của cả
	* Xuất Kho và Nhập Kho
	*/
	/*public function updateNewData( $data ){
		
		$kq = CtphieuNhapKho::prepareCtData_UpdateTonProducts( $this, $data );

		if ( $kq === false )
			return false;

		$this->thueVAT 		= 0;
		if ( $this->getDI()->getRequest()->getPost("coXuatHoaDonKhong") != NULL ) {
			$this->thueVAT 		= $this->tongThanhToan / 10;
		}

		$this->tongThanhToan	= $this->tongThanhToan + $this->thueVAT;
		$this->conNo 			= $this->tongThanhToan - $this->daThanhToan;

		if ( !$this->update() ) {
	    		
			$this->getDI()->getShared("flashSession")->error("Không thể update phiếu nhập kho này" );
			
			return false;
		}

		// Cập nhật những Phiếu Nhập # và Xuất # + Cập nhật sản phẩm luôn.
		$kq = PhieuNhapKho::updateTonNhapXuat( $this->getDI(), $this->ngay);

		if ( $kq ) {
			$this->getDI()->getShared("flashSession")->success("Cập nhật thành công" );
			
			return true;
		}

		return false;
	}*/
	
	/*
	* Hàm này sẽ lấy danh sách toàn bộ phiếu Nhập và Xuất có ngày lớn hơn $beginDate
	* rồi sau đó, 
	*/
	/*public static function updateTonNhapXuat($di, $beginDate) {
		
		$sql = "SELECT ctNhap.id, ctNhap.idProducts, ctNhap.soLuong, ctNhap.tonDauKy, ctNhap.tonCuoiKy, phieu_nhap_kho.ngay as ngayThucHien, ctNhap.idPhieuNhapKho As idPhieu, 'PhieuNhapKho' As loaiPhieu FROM ctphieu_nhap_kho as ctNhap LEFT JOIN phieu_nhap_kho ON ctNhap.idPhieuNhapKho = phieu_nhap_kho.id WHERE phieu_nhap_kho.ngay >= '".$beginDate."' UNION ALL SELECT ctXuat.id, ctXuat.idProducts, ctXuat.soLuong, ctXuat.tonDauKy, ctXuat.tonCuoiKy, phieu_xuat_kho.ngay as ngayThucHien, ctXuat.idPhieuXuatKho As idPhieu, 'PhieuXuatKho' As loaiPhieu FROM ctphieu_xuat_kho as ctXuat LEFT JOIN phieu_xuat_kho ON ctXuat.idPhieuXuatKho = phieu_xuat_kho.id WHERE phieu_xuat_kho.ngay >= '".$beginDate."' ORDER BY idProducts, ngayThucHien";
		
		$db 	= $di->get('db');
		$stmt 	= $db->prepare( $sql );
		
		try {
			$stmt->execute();
			$ctList = $stmt->fetchAll(\PDO::FETCH_ASSOC);
			
		} catch (Exception $e){
			$di->getShared("flashSession")->warning("Không thể đọc dữ liệu để cập nhật tồn đầu kỳ và tồn cuối kỳ" );
			return false;
		}

		// $ctListArray chứa mảng 2 chiều mỗi chiều cùng 1 idProducts
		$ctListArray 	= array([]);
		$index 			= 0;
		for ($i = 0; $i < count( $ctList ); $i++)
		{
			for ( $j = $i; $j < count( $ctList ); $j++ ) 
			{	
				if ($ctList[$i]['idProducts'] == $ctList[$j]['idProducts']) {
					
					$ctListArray[$index][] = $ctList[$j];
					
					if ($j != $i) // Cắt bỏ j ra và lùi j về 1 bước
						array_splice($ctList, $j--, 1);
				}
			}
			$index++;
		} */
				
		/* Trong mỗi chiều của $ctListArray thì ta gán lại toàn bộ tonDauKy và tonCuoiKy như sau:
		* - Tồn đầu kỳ: tonDauKy này = tonCuoiKy liền trước
		* - Tồn cuối kỳ, có 2 trường hợp
		*     + Nếu là Nhập kho: tonCuoiKy = tonDauKy + soLuong
		*	  + Nếu là Xuất kho: tonCuoiKy = tonDauKy - soLuong
		* Cách phân biệt Nhập hay Xuất dựa vào trường donGia
		*	PhieuNhapKho có trường donGia, PhieuXuatKho ko có
		*/
		/*for ($i = 0; $i < count($ctListArray) ; $i++) 
		{
			for ( $j = 0; $j < count($ctListArray[$i]); $j++ ) 
			{
				if ($j > 0)
					$ctListArray[$i][$j]['tonDauKy']  = $ctListArray[$i][$j - 1]['tonCuoiKy'];
				
				if ( $ctListArray[$i][$j]['loaiPhieu'] == 'PhieuNhapKho' ) { // PhieuNhapKho
					
					$ctListArray[$i][$j]['tonCuoiKy'] = 
					$ctListArray[$i][$j]['tonDauKy'] + $ctListArray[$i][$j]['soLuong'];
					
				} else { // PhieuXuatKho
					$ctListArray[$i][$j]['tonCuoiKy'] = 
					$ctListArray[$i][$j]['tonDauKy'] - $ctListArray[$i][$j]['soLuong'];
				}
		}} // End for, End for

		$updateCtPhieu = "";

		for ($i = 0; $i < count($ctListArray); $i++) {
			
			for ( $j = 0; $j < count($ctListArray[$i]); $j++ ) 
			{
				if ($ctListArray[$i][$j]['loaiPhieu'] == 'PhieuNhapKho') {
					
					$updateCtPhieu .= "UPDATE ctphieu_nhap_kho SET tonDauKy = ".$ctListArray[$i][$j]['tonDauKy'].", tonCuoiKy = ".$ctListArray[$i][$j]['tonCuoiKy']." WHERE id = ".$ctListArray[$i][$j]['id']."; ";
				} else {

					$updateCtPhieu .= "UPDATE ctphieu_xuat_kho SET tonDauKy = ".$ctListArray[$i][$j]['tonDauKy'].", tonCuoiKy = ".$ctListArray[$i][$j]['tonCuoiKy']." WHERE id = ".$ctListArray[$i][$j]['id']."; ";
				}
			}
		}

		$update = $db->prepare( $updateCtPhieu );
			
		try {
			$update->execute();
			
		} catch (Exception $e){
			$di->getShared("flashSession")->warning("Không Update Được Tồn Đầu Kỳ Và Tồn Cuối Kỳ của các phiếu nhập, xuất." );
			return false;
		}

		return true;
	}*/
}