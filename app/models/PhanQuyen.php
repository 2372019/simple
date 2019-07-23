<?php

class PhanQuyen extends BaseModel
{
	public $id;					// <int> Not null, Default AI
	public $name;				// <string> Not null
	public $permissions = ''; 	// <string>

	public function initialize()
	{
		$this->hasMany("id", "Users", "group_id",[
			'foreignKey' 	=> [
				'message'	=> 'Không Được Xóa Bởi Vì Phân Quyền Này Đang Được Sử Dụng Bên Users'
			]
		]);	
	}

}
