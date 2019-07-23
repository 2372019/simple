<?php

use Phalcon\Mvc\Model\Relation;

class GhiChu extends BaseModel
{
	public $id;				// <int> Not null, Default Auto Inc
	public $ngay;			// <timestamp> Not null, Default CURRENT_TIMESTAMP
	public $tieuDe;			// <string> Not null
	public $noiDung;		// <string> Not null
	public $trangThai;		// <string> Not null
	public $idNguoiNhap;	// <int>  Not null
	public $cheDo;			// <string> Not null

    public function initialize()  
	{
		$this->ngay = date('Y-m-d H:i:s');
	}

	public function beforeSave()
	{
		$user = $this->getDI()->getSession()->get('user');
		$this->idNguoiNhap = $user['id'];
	}

	public function updateTrangThai($trangThai)
	{
		$this->trangThai = $trangThai;
		if (!$this->update())
			return "Cập Nhật Trạng Thái Không Thành Công";
		
		return true;
	}
}