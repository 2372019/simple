<?php

class QuanLyChiPhi extends BaseModel
{
	public $id;				// <int> Not null, Default AI
	public $chiChoAi;		// <string> Not null
	public $soTienChi;		// <double> Not null
	public $lyDoChi;		// <string>
	public $loaiChiPhi;		// <string> Not null
	public $Ngay;			// <date> Not null
	public $idNguoiXacNhan;	// <int> Not null
	public $idNguoiNhap;	// <int> Not null
	public $trangThai;		// <string>

	public $tenNguoiNhap;		// Trường này ko lưu vào database
	public $duyetChi;

	public function initialize()
	{
		$this->hasOne("idNguoiNhap", "Users", "id");
		$this->Ngay = date('Y-m-d');
	}

	public function beforeCreate()
	{
		$user = $this->getDI()->getSession()->get('user');
		$this->idNguoiNhap = $user['id'];
	}

	public function getThongTinRow()
	{
		$kq = $this->toArray();

		$user = $this->getDI()->getSession()->get('user');

		$kq['tenNguoiNhap']		= isset( $this->Users->name ) ? $this->Users->name : '';

		//kiểm tra có hiện button duyệt chi
		if ($this->checkDuyetChi($kq['soTienChi'])) {
			
			$kq['duyetChi'] = 1;
		}

		if ($kq['idNguoiNhap'] == $user['id'] ) {
			
			return $kq;
		} else {
			if ($user['permission'] == 'Admin' || $user['permission'] == 'Administrator') {
				 return $kq;
			}
		}
	}

	/*
	* kiểm tra user có được hiện duyệt chi không
	* Admin được duyệt chi tất cả
	* Các user khác nếu số tiền chi phí lớn hơn so tiền cho phép thì k được duyệt
	*/
	private function checkDuyetChi($soTien)
	{
		$user = $this->getDI()->getSession()->get('user');

		if ($user['permission'] == 'Admin') {
			return true;
		}

		if ($soTien <= $user['hanMucChiPhi']) {
			return true;
		}

		return false;
	}

	//cập nhật trạng thái hoàn tất khi thêm/sửa quản lý thực chi
	public static function updateTrangThaiThucChi($id, $soTien)
	{
		$kq = true;

		$qlcp = QuanLyChiPhi::findFirstById($id);

		if ( $qlcp && $qlcp->trangThai != 'hoanTat' ) {

			if ( $qlcp->soTienChi == $soTien ) {

				$qlcp->trangThai = 'hoanTat';
				if ( !$qlcp->update() )
					$kq = "Không Cập Nhật Được Số Tiền Chi Của Chi Phí";
			} else {

				$kq = "Số Tiền Nhập Phải Bằng Số Tiền Chi Bên Chi Phí";
			}
		} else {
			$kq = "Lỗi";
		}

		return $kq;
	}

}
