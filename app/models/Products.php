<?php 
use Phalcon\Mvc\Model\Relation;

class Products extends BaseModel
{
	public $id;                         // <int> Not null, Default AI
    public $maSanPham;                  // <string> Not null
	public $tenSanPham;                 // <string> Not null
	public $donGiaMuaVao;	            // <double> Not null, Default 0
	public $tonKhoBanDau;              	// <int> Not null, Default 0
	public $tonHienTai;              	// <int> Not null, Default 0
	public $donGiaMoiNhat;              // <double> Not null, Default 0   
	public $moTa;                       // <string>
	public $loaiSanPham;				// <int> Not null
	public $noiBat;						// <int>
	public $type;						// <string> Biến này ko thêm vào database
	
	/*
	*	cập nhật số lượng tồn sản phẩm
	*	return kq = true || Thông báo lỗi
	*/
	public function setTonHienTai($tonHienTai)
	{
		$kq = true;
		$this->tonHienTai = $tonHienTai;

		if ( !$this->update() ) {
			$kq = "Lỗi trừ số lượng sản phẩm: " . $this->tenSanPham;
		}
		
		if ($tonHienTai < 0){
			$kq = "Mã sản phẩm bị âm: " . $this->maSanPham;
		}

		return $kq;
	}
	
	/*
	* Hàm này trả về string (ma, ma, ma, ...)
	* $objList: Mảng đối tượng phải chứa 2 biến:
	*	$objList[$i]->Products
	*	$objList[$i]->soLuong
	*/
	public static function getMaSanPhamList( $objList ) {
		
		if ( count($objList) < 1 )
			return "";

		$leng = count($objList);

		//kiểm tra có trường soLuong trong chi tiết k
		if (isset($objList[0]->soLuong)) {
			
			$maSanPhamList 	= $objList[0]->Products->maSanPham . "(" . $objList[0]->soLuong . ")";

			for ($i = 1; $i < $leng; $i++) {
				
				$maSanPhamList = $maSanPhamList . ', ' . 
					$objList[$i]->Products->maSanPham . "(" . $objList[$i]->soLuong . ")";
			}
		} else {

			$maSanPhamList 	= $objList[0]->Products->maSanPham;

			for ($i = 1; $i < $leng; $i++) {
				
				$maSanPhamList = $maSanPhamList . ', ' . 
					$objList[$i]->Products->maSanPham;
			}
		}
		
		return $maSanPhamList;
	}

	public function initialize()  
	{
		$this->hasMany("id", "Ctorders", "idProducts",[
			'foreignKey' 	=> [
				'message'	=> 'Không Được Xóa Bởi Vì Sản Phẩm Này Đang Được Sử Dụng Bên Đơn hàng'
			]
		]);
		
		$this->hasMany("id", "CtthanhPham", "idCtProducts",[
			'foreignKey' 	=> [
				'message'	=> 'Không Được Xóa Bởi Vì Sản Phẩm Này Đang Được Sử Dụng Bên Ct Thành Phẩm'
			]
		]);
		
		$this->hasMany("id", "ThanhPham", "idProducts",[
			'foreignKey' 	=> [
				'message'	=> 'Không Được Xóa Bởi Vì Sản Phẩm Này Đang Được Sử Dụng Bên Thành Phẩm'
			]
		]);

		$this->hasMany("id", "Ctproducts", "idProducts",[
			'foreignKey' => [
                'action' => Relation::ACTION_CASCADE,
            ]
		]);
		
		$this->hasMany("id", "Ctproducts", "idProductsVatTu", [

			'foreignKey' 	=> [
				'message'	=> 'Không Được Xóa Bởi Vì Sản Phẩm Này Đang Được Sử Dụng Bên Chi Tiết Sản Phẩm'
			],

			'alias' => 'Vattssu'
		]);

        $this->hasMany("id", "CtphieuNhapKho", "idProducts",[
            'foreignKey'    => [
                'message'   => 'Không Được Xóa Bởi Vì Sản Phẩm Này Đang Được Sử Dụng Bên Chi Tiết Phiếu Nhập Kho'
            ]
        ]);

        $this->hasMany("id", "CtphieuXuatKho", "idProducts",[
            'foreignKey'    => [
                'message'   => 'Không Được Xóa Bởi Vì Sản Phẩm Này Đang Được Sử Dụng Bên Chi Tiết Phiếu Xuất Kho'
            ]
        ]);

		$this->hasOne("loaiSanPham", "LoaiSanPham", "id");
	}

	public function getThongTinRow() {
		
		$kq = $this->toArray();

		$kq['type'] 	= $this->LoaiSanPham->type;
		
		return $kq;
	}

	public function createProducts($data) {

		if ( Ctproducts::createCtProducts($this, $data) === false ) {
			return false;
		}

		if ( !isset( $this->id ) ) {
			$this->tonHienTai = $this->tonKhoBanDau;
		}

		$this->noiBat = 0; // Vì un checkbox thì giá trị là NULL, form ko bind giá trị này vào, ta phải gán thủ công mặc định là 0
		if ( $data->noiBat == 1 )
			$this->noiBat 	= 1;
		
		return true;
	}
}