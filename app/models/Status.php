<?php

class Status extends BaseModel
{
	public $id;				// <int> Not null, Default AI
	public $tenTrangThai;	// <string> Not null

	public function initialize()
	{
		$this->hasMany("id", "Orders", "trangThai",[
			'foreignKey' 	=> [
				'message'	=> 'Không Được Xóa Bởi Vì Trạng Thái Này Đang Được Sử Dụng Bên Orders'
			]
		]);	
	}

}
