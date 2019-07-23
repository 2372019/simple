<?php

class CongTyBanHang extends BaseModel
{
	public $id; 						// <int> Not null, Default AI
	public $tenCongTy; 					// <string> Not null
	public $thongTinCongTy;				// <string> Not null
	
	public function initialize()
	{
		$this->hasMany("id", "Orders", "idCongTyBanHang",[
			'foreignKey' 	=> [
				'message'	=> 'Không Được Xóa Bởi Vì Công Ty Này Đang Được Sử Dụng Bên Orders'
			]
		]);	
	}
}