<?php

class NhanSu extends BaseModel
{
	public $id;			// <int> Not null, Default AI
	public $hoTen;		// <string> Not null
	public $CMND;		// <string> Not null
	public $ngaySinh;	// <date> Not null
	public $SDT;		// <string> Not null
	public $diaChi;		// <string> Not null
	public $queQuan;	// <string> Not null
	public $chucVu;		// <string> Not null
	public $ghiChu;		// <string> Not null

	public function initialize()
	{
		$this->hasMany("id", "Orders", "idEmployees",[
			'foreignKey' 	=> [
				'message'	=> 'Không Được Xóa Bởi Vì Nhân Sự Này Đang Được Sử Dụng Bên Orders'
			]
		]);

		$this->hasMany("id", "PhieuNhapKho", "idNguoiNhanHang",[
			'foreignKey' 	=> [
				'message'	=> 'Không Được Xóa Bởi Vì Nhân Sự Này Đang Được Sử Dụng Bên Phiếu Nhập Kho'
			]
		]);
		
	}
}
