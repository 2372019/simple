<?php

class NhaCungCap extends BaseModel
{
	public $id;					// <int> Not null, Default AI
	public $ngay;				// <date> Not null
	public $tenNhaCungCap;		// <string> Not null
	public $diaChi;				// <string> Not null
	public $lienHe;				// <string> Not null
	public $moTa = '';			// <string>
	public $ghiChu = '';		// <string>

	public $tongCongNo	 = 0;	// Trường này ko lưu vào database
	public $soDonHang	 = 0;	// Trường này ko lưu vào database

	public function initialize()
	{
		$this->ngay = date('Y-m-d');

		$this->hasMany("id", "QuanLyThucChi", "nguoiNhan",[
			'foreignKey' 	=> [
				'message'	=> 'Không Được Xóa Bởi Vì Nhà Cung Cấp Này Đang Được Sử Dụng Bên Quản Lý Thực Chi'
			]
		]);

		$this->hasMany("id", "QuanLyThucThu", "idNhaCungCap");

		$this->hasMany("id", "DatHang", "idNhaCungCap",[
			'foreignKey' 	=> [
				'message'	=> 'Không Được Xóa Bởi Vì Nhà Cung Cấp Này Đang Được Sử Dụng Bên Đặt Hàng'
			]
		]);

		$this->hasMany("id", "PhieuNhapKho", "idNhaCungCap",[
			'foreignKey' 	=> [
				'message'	=> 'Không Được Xóa Bởi Vì Nhà Cung Cấp Này Đang Được Sử Dụng Bên Phiếu Nhập Kho'
			]
		]);	
	}

	public function getThongTinRow() {

		$sumDathang = (double)DatHang::sum([
			'column'	=> 'tongTienThanhToan',
			'conditions' => 'idNhaCungCap = '. $this->id . ' AND nhapKho = 1'
		]);
		
		$sumThucChi = (double)QuanLyThucChi::sum([
			'column'	=> 'soTien',
			'nguoiNhan = '. $this->id
		]);

		$sumThucThu = (double)QuanLyThucThu::sum([
			'column'	=> 'soTien',
			'idNhaCungCap = '. $this->id
		]);

		$kq = $this->toArray();

		$kq['tongCongNo'] 	= $sumDathang - $sumThucChi + $sumThucThu;
		$kq['soDonHang']	= count($this->DatHang);
		
		return $kq;
	}

}
