<?php

class Customers extends BaseModel
{
	public $id; 						// <int> Not null, Default AI
	public $ngay; 						// <timestamp> Not null, Default CURRENT_TIMESTAMP
	public $tenKhachHang;				// <string> Not null
	public $mST;						// <string> 
	public $diaChi;						// <string> 
	public $soDienThoai;				// <string> 
	public $email;						// <string> 
	public $ghiChu;						// <string> 
	public $loaiKhachHang;				// <string> Not null, Giá trị: [Khách lẻ , Đại lý công ty  Cửa hàng , Khác]
	public $nguoiMuaHang;				// <string> 
	public $tongDoanhThuChuaVAT = 0;	// <double> Not null, Default 0
	public $tongThueVAT = 0;			//  <double> Not null, Default 0
	public $tongTienThanhToan = 0;		//  <double> Not null, Default 0
	public $loiNhuanGopSauThueVAT = 0;	//  <double> Not null, Default 0
	
	public $tongCongNo	 = 0;	// Trường này ko lưu vào database
	public $soDonHang	 = 0;	// Trường này ko lưu vào database
	
	public function initialize()
	{
		$this->hasMany("id", "Orders", "idKhachHang",[
			'foreignKey' 	=> [
				'message'	=> 'Không Được Xóa Bởi Vì Khách Hàng Này Đang Được Sử Dụng Bên Orders'
			]
		]);	

		$this->hasMany("id", "PhieuXuatKho", "tenKH",[
			'foreignKey' 	=> [
				'message'	=> 'Không Được Xóa Bởi Vì Khách Hàng Này Đang Được Sử Dụng Bên PhieuXuatKho'
			]
		]);
	}
	/*
	* Trước khi update/create thì sẽ gán ngày
	*/
	public function beforeValidation () {
		
		$this->ngay = date("Y-m-d H:i:s");
	}
	
	public function afterFetch() {
		
		$this->tongCongNo =  Orders::sum([
			'column' 		=> 'congNo',
			'conditions'	=> "idKhachHang = '" . $this->id . "' AND trangThai NOT IN ({trangThai:array})",
			'bind' => ['trangThai' =>['hoanTat','huyTruocBH','huySauBH']]
							]);
		if ($this->tongCongNo == null)
			$this->tongCongNo = 0;
		
		$this->soDonHang  = count ($this->Orders);
	}
	
	public function getThongTinRow() {
		
		$kq = $this->toArray();
		
		$kq['tongCongNo'] 	= $this->tongCongNo;
		$kq['soDonHang'] 	= $this->soDonHang;
		
		return $kq;
	}
}