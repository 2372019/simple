<?php

class NhaXe extends BaseModel
{
	public $id;				// <int> Not null, Default AI
	public $tenNhaXe;		// <string> Not null
	public $diaChi;			// <string> Not null
	public $loTrinhDi;		// <string> Not null
	public $loTrinhDen;		// <string> Not null
	public $soDienThoai;	// <string> Not null
	public $ghiChu;			// <string>

	public function beforeSave()
	{
		$this->loTrinhDen = ucwords($this->loTrinhDen);
		$this->loTrinhDi = ucwords($this->loTrinhDi);
	}
}


