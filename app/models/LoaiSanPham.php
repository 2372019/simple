<?php

class LoaiSanPham extends BaseModel
{
	public $id;			// <int> Not null, Default AI
	public $type;		// <string> Not null

	public function initialize()
	{
		$this->hasMany("id", "Products", "LoaiSanPham",[
			'foreignKey' 	=> [
				'message'	=> 'Không Được Xóa Bởi Vì Loại Sản Phẩm Này Đang Được Sử Dụng Bên Products'
			]
		]);	
	}
}
