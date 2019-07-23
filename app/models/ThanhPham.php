<?php 
use Phalcon\Mvc\Model\Relation;

class ThanhPham extends BaseModel
{
	public $id;                 // <int> Not null, Default AI
    public $idProducts;         // <int> Not null
	public $soLuongThanhPham;	// <int> Not null
	public $tongSoLuongVatTu;   // <int> Not null
	public $ngay;               // <date> Not null
	public $idNguoiRap;         // <int> Tạm thời chưa sử dụng
	public $soSerial;           // <float>
	public $idNguoiNhap;        // <int> Not null
	
	public $tenSanPham;         // <string> Biến này ko thêm vào database
	public $maHang;         	// <string> Biến này ko thêm vào database
	
	public function initialize()  
	{
		$this->ngay = date('Y-m-d');

		$this->belongsTo("idProducts", "Products", "id");

		$this->hasMany("id", "CtthanhPham", "idThanhPham",[
			'foreignKey' => [
                'action' => Relation::ACTION_CASCADE,
            ]
		]);

		/*$this->hasManyToMany(
            'id',
            'CtthanhPham',
            'idThanhPham', 'idCtProducts',
            'Products',
            'id'
        );*/
		
		$this->hasOne("idNhanSu", "NhanSu", "id");
	}

	//note: có thể lỗi khi update thành phẩm
	public function afterFetch(){
		$this->tenSanPham = $this->Products->tenSanPham;
		$this->maHang 	  =	Products::getMaSanPhamList($this->CtthanhPham);
	}

	public function getThongTinRow() {
		
		$kq = $this->toArray();

		$kq['tenSanPham'] 	 = $this->tenSanPham;
		$kq['maHang']		 = $this->maHang;
		$kq['ngay']			 = date_format(date_create($this->ngay),'d-m-Y');
		
		return $kq;
	}

	public function createThanhPham($data, $user) {

		if ( ($result = CtthanhPham::createCtthanhPham($this, $data)) === false ) {
			return false;
		}

		$this->idProducts 		= $data->idSanPham;
		$this->soLuongThanhPham = $data->soLuongThanhPham;
		$this->tongSoLuongVatTu = $result;
		$this->idNguoiNhap 		= $user['id'];

		return true;
	}
}