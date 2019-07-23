<?php 

use Phalcon\Mvc\Model\Relation;

class Orders extends BaseModel
{
	public $id;							// <int> Not null, Default Auto Inc
	public $ngay;						// <timestamp> Not null, Default CURRENT_TIMESTAMP
	public $ngayXuatHoaDon;				// <date>
	public $soHoaDon = '';				// <string>
	public $idKhachHang;				// <int> Not null
	public $diaChiGiaoHang;				// <string> 
	public $thongTinNguoiNhanHang;		// <string> 
	public $hinhThucThanhToan;			// <string> Not null, Giá trị: [Tiền mặt, Chuyển Khoản, Nợ, Theo hợp đồng, Khác]
	public $daThanhToan = 0;			// <double> Not null, Default 0
	public $congNo = 0;					// <double> Not null, Default 0
	public $ngayHenThanhToan;			// <date>
	public $idEmployees;				// <int> Not null
	public $chiPhiGiaoHang = 0;			// <int> Default 0
	public $hoaHong = 0;				// <int> Default 0
	public $idCongTyBanHang;			// <int> Not null
	public $coXuatHoaDonKhong;			// <boolean> Default 0
	public $ghiChu;						// <string> 
	public $tongCongChuaVAT = 0;		// <float> Not null
	public $thueVAT = 0;				// <float> Not null
	public $tongTienThanhToan = 0;		// <float> Not null
	public $donGiaMuaVaoChuaVAT = 0;	// <float> Not null
	public $loiNhuanGopTruocThue = 0;	// <float> Not null
	public $trangThai;					// <int> Not null
    public $duocXem = 0;                // <boolse> Not Null
    public $xuatKho = 0;                // <boolse>
	
	public $tenKhachHang;			// <string> Biến này ko thêm vào database
	public $maHang;					// <string> Biến này ko thêm vào database
	public $congTyBanHang;			// <string> Biến này ko thêm vào database
	public $nguoiGiaoHang;			// <string> Biến này ko thêm vào database

	private static $array = [

		'choThanhToan' 	=> 'Chờ Thanh Toán', 
		'choXacNhan' 	=> 'Chờ Xác Nhận',
		'conNo' 		=> 'Còn Nợ', 
		'hoanTat' 		=> 'Hoàn Tất',
		'huyTruocBH'	=> 'Hủy Trước BH',
		'huySauBH'		=> 'Hủy Sau BH',
		'khac'			=> 'Khác' 
	];

	public function initialize()  
	{
		$this->ngay = date('Y-m-d H:i:s');
        
		$this->belongsTo("idKhachHang", "Customers", "id");
		$this->belongsTo("idCongTyBanHang", "CongTyBanHang", "id");
		$this->hasOne("idEmployees", "NhanSu", "id");
        $this->hasOne("trangThai", "Status", "id");
		
		$this->hasMany("id", "Ctorders", "idOrders",[
			'foreignKey' => [
                'action' => Relation::ACTION_CASCADE,//vd: nếu xóa order thì các ctorders liên quan cũng bị xóa theo
            ]
		]);
		
		$this->hasManyToMany(
            'id',
            'Ctorders',
            'idOrders', 'idProducts',
            'Products',
            'id'
        );
	}

	public static function status($key = '') {

        $array = self::$array;
        if (empty($key)) {
        	
        	return $array;
        } else {
        	return $array[$key];
        }
    }
	
	public function hoanTat() {
		
		if($this->congNo == 0 && $this->xuatKho == 1) {
			$this->trangThai = 'hoanTat';
			$this->update();
		}
	}
	
	public function getResultsetClass()
    {
		//return 'Model\Resultset\Custom';
    }

	public function getThongTinRow() {
		
		$kq = $this->toArray();

		$kq['tenKhachHang'] 	= $this->Customers->tenKhachHang;
		$kq['ngay']			 	= date_format(date_create($this->ngay),'d-m-Y H:i:s');
		$kq['maSanPham']		= Products::getMaSanPhamList( $this->Ctorders );
		$kq['congTyBanHang']	= $this->CongTyBanHang->tenCongTy;
		$kq['nguoiGiaoHang']	= isset($this->NhanSu->hoTen) ? $this->NhanSu->hoTen : '';
		$kq['xuatKho']			= ( $this->xuatKho == 1 ) ? "Đã XK" : "Chưa XK";
		
		return $kq;
	}
	
	public function createOrders($data){
		
		if ( Ctorders::createCtOrders( $this, $data) === false )
			return false;
		
		$this->thueVAT 			= 0;
		$this->duocXem 			= 0;

		if ( $this->xuatKho != 1 ) {
			$this->xuatKho 		= 0;
		}

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
	
	/*
	* Điều kiện sửa trạng thái đơn hàng như sau:
	* 	- Trạng thái khác: $arrayTrangThai
	* 	- Đơn hàng đã xuất kho:
	*		+ Ko cho chọn trạng thái: huyTruocBH
	*		+ Nếu chọn huySauBH thì chỉ có admin mới được cập nhật và phải cảnh báo ở tầng Js: cho chọn có đồng ý hoặc ko và lưu ý phải nhập kho lại sản phẩm thủ công; ở tầng PHP cập nhật xong thì warning là phải tự nhập kho lại sản phẩm:  "Đã cập nhật order thành Hủy Sau BH, lưu ý: PHẢI TỰ NHẬP KHO các sản phẩm thủ công"
	* $trangThai: Biến trạng thái truyền vào, $userGroup: string
	* $kq stdClass(message, state);
	*/
	public function updateTrangThai($trangThai, $userGroup) {
		
		$arrayTrangThai = ['hoanTat','huySauBH', 'huyTruocBH'];

		$kq = new stdClass();
		$kq->message 	= '';
		$kq->state		= false;

		if (!in_array($this->trangThai, $arrayTrangThai)) {
			
			if ( in_array( $trangThai , $arrayTrangThai ) ) {
				$this->duocXem = 1;
			}
			
			if ($this->xuatKho == 1) {
				
				if ( $trangThai == 'huyTruocBH' ) {
					$kq->message = "Đơn hàng đã xuất kho, bạn phải chọn lại là hủy sau BH";
				} else {
				
					if ( $userGroup == 'Admin' ) {// admin
						
						$kq->message = "Đơn hàng đã được xuất kho. Lưu ý: Phải tự nhập kho THỦ CÔNG các sản phẩm đã xuất!. ";
						$kq->state		= true;
						
					} else 
						$kq->message = "Đơn hàng đã xuất kho, bạn ko đc quyền hủy sau bán hàng, vui lòng liên hệ quản lý!. ";
				}
				
			} else if ( $trangThai == 'huySauBH' )
				$kq->message = "Đơn hàng chưa xuất kho, bạn chọn trạng thái chưa đúng. Nên chọn lại là 'Hủy Trước BH'. ";
			else
				$kq->state	= true;
			
			if ($kq->state) {
				
				$this->trangThai 	= $trangThai;
				$kq->state 	 		= $this->update();
				
				$kq->message = $kq->message . "Đã cập nhật trạng thái thành công";
			}

		} else
			$kq->message = "Lỗi Cập Nhật Đơn Hàng";

		return $kq;
	}

	//thống kê trong ngày
	public static function thongKeTrongNgay()
	{
		$orders = Orders::find([
			'conditions'	=> '(ngay between :dateStart: AND :dateEnd:) AND trangThai NOT IN ({trangThai:array})',
			'bind'			=> [
				'dateStart' => date('Y-m-d').' 00:00:01',
				'dateEnd' 	=> date('Y-m-d').' 23:59:59',
				'trangThai' => ['huyTruocBH','huySauBH']
			]
		]);

		//tạo mảng để chứa data
		$arrayTrongNgay = array(
			'trongNgay' => [

				'soDonHangTrongNgay'			=> count($orders),
				'doanhThuCoVATTrongNgay'		=> 0,
				'doanhThuKhongVATTrongNgay'		=> 0,
				'thueVATTrongNgay'				=> 0,
				'congNoTrongNgay'				=> 0,

				'thuDonHangTrongNgay'  			=> QuanLyThucThu::sum([
						'column' => 'soTien',
						'conditions' => 'nguonThu = ' . "'Bán Hàng'" . ' AND ngay = "'. date('Y-m-d') .'"'
				]),

				'tongThuTrongNgay'				=> 0,
				'tongChiTrongNgay'				=> 0
			]
		);

		//gán dữ liệu vào mảng
		$arrayTrongNgay['trongNgay']['tongThuTrongNgay'] += QuanLyThucThu::sum([
						'column' => 'soTien',
						'conditions' => 'ngay = "'. date('Y-m-d') .'"'
				]);

		$arrayTrongNgay['trongNgay']['tongChiTrongNgay'] += QuanLyThucChi::sum([
						'column' => 'soTien',
						'conditions' => 'ngay = "'. date('Y-m-d') .'"'
				]);

		for ($i = 0; $i < $arrayTrongNgay['trongNgay']['soDonHangTrongNgay']; $i++) {
			
			$arrayTrongNgay['trongNgay']['thueVATTrongNgay']				+= $orders[$i]->thueVAT;
			$arrayTrongNgay['trongNgay']['thuDonHangTrongNgay']				+= $orders[$i]->tongTienThanhToan - $orders[$i]->congNo;
			$arrayTrongNgay['trongNgay']['congNoTrongNgay']					+= $orders[$i]->congNo;

			if ( $orders[$i]->thueVAT )
				$arrayTrongNgay['trongNgay']['doanhThuCoVATTrongNgay']		+= $orders[$i]->tongTienThanhToan;
			else
				$arrayTrongNgay['trongNgay']['doanhThuKhongVATTrongNgay']	+= $orders[$i]->tongCongChuaVAT;
			
		}

		return $arrayTrongNgay;

	}
}