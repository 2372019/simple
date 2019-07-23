<?php

class NganHang extends BaseModel
{
	public $id;				// <int> Not null, Default AI
	public $tenNganHang;	// <string> Not null
	public $soTienDauKy;	// <double> Not null Default 0

	public function initialize()
	{
		$this->hasMany("id", "QuanLyThucThu", "ckNganHang",[
			'foreignKey' 	=> [
				'message'	=> 'Không Được Xóa Bởi Vì Ngân Hàng Này Đang Được Sử Dụng Bên Quản Lý Thu Chi'
			]
		]);

		$this->hasMany("id", "QuanLyThucChi", "ckNganHang",[
			'foreignKey' 	=> [
				'message'	=> 'Không Được Xóa Bởi Vì Ngân Hàng Này Đang Được Sử Dụng Bên Quản Lý Thu Chi'
			]
		]);
	}
}
