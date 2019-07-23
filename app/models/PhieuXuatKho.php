<?php 

use Phalcon\Mvc\Model\Relation;

class PhieuXuatKho extends BaseModel
{
	public $id;					// <int> Not null, Default AI
    public $ngay;              	// <timestamp> Not null
	public $tenKH;            	// <string> Not null
	public $diaChi;            	// <string> Not null, Default 0
	public $lyDoXuat;           // <string> Not null, Default 0
	public $nguoiNhan;       	// <string>
	public $ghiChu;      		// <string>
	public $tongSoLuong;        // <int> Not null, Default 0   
	public $idNguoiLapPhieu;	// <int> Not null
	public $idNguoiXacNhan;		// <int> Not null
	public $nguoiXacNhan;		// <string>  Biến này ko thêm vào database
    public $maHang;             // <string>  Biến này ko thêm vào database
	
	public function initialize()  
	{
		$this->ngay = date('Y-m-d H:i:s');

        $this->hasOne("tenKH", "Customers", "id");
        $this->hasOne("idNguoiLapPhieu", "Users", "id", ['alias' => 'NguoiLapPhieu']);
        $this->hasOne("idNguoiXacNhan", "Users", "id", ['alias' => 'NguoiXacNhan']);

		$this->hasMany("id", "CtphieuXuatKho", "idPhieuXuatKho",[
			'foreignKey' => [
                'action' => Relation::ACTION_CASCADE,//vd: nếu xóa PhieuXuatKho thì các ctphieuXuatKho liên quan cũng bị xóa theo
            ]
		]);
		
		$this->hasManyToMany(
            'id',
            'CtphieuXuatKho',
            'idPhieuXuatKho', 'idProducts',
            'Products',
            'id'
        );
	}

	public function getThongTinRow() {
		
		$kq = $this->toArray();

		$kq['tenKH'] 		 = isset( $this->Customers->tenKhachHang ) ? $this->Customers->tenKhachHang : $this->tenKH;
		$kq['nguoiXacNhan']  = isset( $this->NguoiXacNhan->name ) ? $this->NguoiXacNhan->name : 'Chưa Xác Nhận';
		$kq['maHang']		 = Products::getMaSanPhamList($this->CtphieuXuatKho);
		$kq['ngay']			 = date_format(date_create($this->ngay),'d-m-Y H:i:s');
		
		return $kq;
	}

	public function createPhieuXuatKho($data)
	{
		if ( ($phieuXuatKho = CtphieuXuatKho::createCtPhieuXuatKho($data)) === false)
			return false;

		$this->tongSoLuong		= $phieuXuatKho['tongSoLuong'];
		$this->CtphieuXuatKho 	= $phieuXuatKho['ctphieuXuatKho'];

		if ( !$this->create() )
			return false;

		return true;
	}

	public function createOrderPhieuXuatKho($order, $user){

		$ctOrders	= $order->Ctorders->toArray();

    	if ( ($result = CtphieuXuatKho::createOrderCtPhieuXuatKho($ctOrders, $this)) === false ) {
    		return false;
    	}

    	$this->tenKH 			= $order->Customers->tenKhachHang;
    	$this->diaChi 			= $order->diaChiGiaoHang;
    	$this->lyDoXuat 		= 'Xuất Bán Hàng';
    	$this->nguoiNhan 		= $order->thongTinNguoiNhanHang;
    	$this->ghiChu 			= $order->ghiChu;
    	$this->tongSoLuong		= $result;
    	$this->idNguoiLapPhieu	= $user['id'];
    	$this->idNguoiXacNhan 	= 0;

    	return true;
	}

	public function createPhieuXuatKhoForThanhPham($ctthanhpham, $data){

		if ( ($result = CtphieuXuatKho::createCtPhieuXuatKhoForThanhPham($ctthanhpham)) === false ) {
			return false;
		}

		$this->tenKH			= 'Xuất Thành Phẩm - ' . $data['tenSanPham'];
		$this->lyDoXuat			= 'Thành Phẩm SX';
		$this->diaChi			= 'Xuất Thành Phẩm';
		$this->tongSoLuong		= $result['tongSoLuong'];
		$this->idNguoiLapPhieu	= $data['user'];
		$this->idNguoiXacNhan	= $data['user'];

		$this->CtphieuXuatKho = $result['ctphieuXuatKho'];

		return true;
	}
	
	/* Hàm này cập nhật (update/new) toàn bộ CtphieuNhapKho dựa trên
	* $data['idCtphieuNhapKhoMoi'] được cung cấp. 
	* Nếu các chi tiết cũ ko có trong $data thì nó sẽ xóa đi
	* Sau khi xóa, nó sẽ cập nhật lại toàn bộ số tồn đầu kỳ và cuối kỳ của cả
	* Xuất Kho và Nhập Kho
	*/
}