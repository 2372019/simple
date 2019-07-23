<?php

use Phalcon\Mvc\Model\Relation;

class DatHang extends BaseModel
{
	public $id;							// <int> Not null, Default Auto Inc
	public $ngay;						// <timestamp> Not null, Default CURRENT_TIMESTAMP
	public $noiDung;					// <string> Not null
	public $idNhaCungCap;				// <int> Not null
	public $daThanhToan = 0;			// <double> Not null, Default 0
	public $congNo = 0;					// <double> Not null, Default 0
	public $coXuatHoaDonKhong;			// <boolean> Default 0
	public $ghiChu;						// <string> 
	public $tongCongChuaVAT = 0;		// <float> Not null
	public $thueVAT = 0;				// <float> Not null
	public $tongTienThanhToan = 0;		// <float> Not null
    public $xuatKho = 0;                // <boolean>
    public $idNguoiXacNhan;             // <int>
    public $idNguoiNhap;             	// <int> Not null
    public $trangThai;                	// <string>


    public $tenNhaCungCap;				// <string> Biến này ko thêm vào database
    public $tenNguoiNhap;				// <string> Biến này ko thêm vào database
	public $maSanPham;					// <string> Biến này ko thêm vào database

    public function initialize()  
	{
		$this->ngay = date('Y-m-d H:i:s');
        
		$this->belongsTo("idNhaCungCap", "NhaCungCap", "id");
		$this->hasOne("idNguoiNhap", "Users", "id");
		$this->hasOne("idMuaVao", "PhieuNhapKho", "id",[
			'foreignKey' 	=> [
				'message'	=> 'Vui Lòng Hủy Phiếu Nhập Kho Trước Khi Xóa',
			]
		]);
		
		$this->hasMany("id", "CtdatHang", "idDatHang",[
			'foreignKey' => [
                'action' => Relation::ACTION_CASCADE,//vd: nếu xóa dathang thì các ctdanghang liên quan cũng bị xóa theo
            ]
		]);
		
		$this->hasManyToMany(
            'id',
            'CtdatHang',
            'idDatHang', 'idProducts',
            'Products',
            'id'
        );
	}

	public function beforeSave()
	{
		$user = $this->getDI()->getSession()->get('user');
		$this->idNguoiNhap = $user['id'];
	}

	public function getThongTinRow()
	{
		$kq = $this->toArray();

		$kq['tenNhaCungCap'] 	= $this->NhaCungCap->tenNhaCungCap;
		$kq['ngay']			 	= date_format(date_create($this->ngay),'d-m-Y H:i:s');
		$kq['maSanPham']		= Products::getMaSanPhamList( $this->CtdatHang );
		$kq['tenNguoiNhap']		= isset( $this->Users->name ) ? $this->Users->name : '';

		$kq['nhapKho'] = 'Chưa NK';
		if ($this->nhapKho == 1) {
			if ($this->PhieuNhapKho)
				$kq['nhapKho']	= $this->PhieuNhapKho->Users->name. ' - ' .$this->PhieuNhapKho->ngay;
			else
				$kq['nhapKho']	= 'Đã NK';
		}
		
		return $kq;
	}

	public function createDatHang($data)
	{
		if ( CtdatHang::createCtdatHang( $this, $data) === false )
			return false;
		
		$this->thueVAT 			 = 0;
		$this->nhapKho 			 = 0;
		$this->coXuatHoaDonKhong = 0; // Vì un checkbox thì giá trị là NULL, form ko bind giá trị này vào, ta phải gán thủ công mặc định là 0
		
		if ( $data->coXuatHoaDonKhong == 1 ) {
			$this->coXuatHoaDonKhong 	= 1;
			$this->thueVAT 				= floor($this->tongCongChuaVAT / 10);
		}

		$this->tongTienThanhToan 	= $this->tongCongChuaVAT + $this->thueVAT;
		$this->congNo 				= $this->tongCongChuaVAT + $this->thueVAT;

		if (!empty($this->daThanhToan)) {
			$this->congNo = $this->tongTienThanhToan - $this->daThanhToan;
		}
	}

	//cập nhật công nợ và trạng thái khi thêm quản lý thực chi
	public static function updateCongNoWhenThucChi($id, $soTienMoi, $soTienCu = 0)
	{
		$kq = true;
		$datHang = DatHang::findFirstById($id);

		if ($datHang) {
			
			$datHang->congNo = $datHang->congNo - $soTienMoi + $soTienCu;
			$datHang->daThanhToan = $datHang->tongTienThanhToan - $datHang->congNo;

			if ($datHang->congNo <= 0)
				$datHang->trangThai = 'hoanTat';
			else
				$datHang->trangThai = '';

			if ( !$datHang->update() )
				$kq = "Không Cập Nhật Được Công Nợ Của Mua Vào";
		} else {
			$kq = "Lỗi";
		}

		return $kq;
	}

	public function hoanTat() {
		
		if($this->congNo <= 0 && $this->nhapKho == 1) {
			$this->trangThai = 'hoanTat';
			$this->update();
		}
	}
}