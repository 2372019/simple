<?php

class QuanLyThucThu extends BaseModel
{
	public $id;				   // <int> Not null, Default AI
	public $ngay;			   // <date> Not null
	public $noiDung;           // <string> Not null
	public $soTien;            // <double> Not null
	public $nguonThu;          // <string> Not null
	public $idKhachHang;       // <int>
    public $idNhaCungCap;      // <int>
	public $hinhThuc;          // <string> Not null
	public $ckNganHang;        // <int>
	public $ghiChu;            // <string>

    public $tenNganHang;       // <string> không thêm vào db mà đây dùng để gán dữ liệu khi tìm kiếm hoặc sắp xếp
    public $tenKhachHang;      // <string> không thêm vào db mà đây dùng để gán dữ liệu khi tìm kiếm hoặc sắp xếp
    public $tenNhaCungCap;      // <string> không thêm vào db mà đây dùng để gán dữ liệu khi tìm kiếm hoặc sắp xếp

	public function initialize()
	{

        $this->ngay = date('Y-m-d');
        
        //Thiết lập quan hệ với bảng liên quan
		$this->hasOne("idKhachHang", "Customers", "id");
        $this->hasOne("ckNganHang", "NganHang", "id");
        $this->hasOne("idNhaCungCap", "NhaCungCap", "id");
	}

    public function getThongTinRow() {
        
        $kq = $this->toArray();

        $kq['tenKhachHang']     = isset($this->Customers->tenKhachHang) ? $this->Customers->tenKhachHang : '';
        $kq['tenNhaCungCap']    = isset($this->NhaCungCap->tenNhaCungCap) ? $this->NhaCungCap->tenNhaCungCap : '';
        $kq['tenNganHang']      = isset($this->NganHang->tenNganHang) ? $this->NganHang->tenNganHang : '';
        $kq['ngay']             = date_format(date_create($this->ngay),'d-m-Y');
        
        return $kq;
    }


    /*
    *   Tính toán tiền các thông tin như thu từ nguồn thu, thu từ ngân hàng, tiền mặt,...
    *   Kết Quả trả về là mảng các thông tin trên
    */
    public static function thongKeThucThu()
    {
        $arrayThucThu = array(
            'thucThu' => [

                'tongThu'           => 0,
                'thuNguonThu'       => [

                    'banHang'           => 0,
                    'nhaCungCap'        => 0,
                    'vayMuon'           => 0,
                    'khac'              => 0,
                ],

                'thuNganHang'       => [

                    'tienMat'           => 0,
                    'VCB (Cá Nhân)'     => 0,
                    'Á Châu (Cty)'      => 0,
                    'Á Châu (Cá Nhân)'  => 0,
                    'Vietin (Cty)'      => 0,
                    'Sacom (Cty)'       => 0,
                    'Vietin CN'         => 0,
                    'Khác'              => 0
                ]
            ]    
        );


        $thucthu = QuanLyThucThu::find(['conditions'    => 'ngay = "'. date('Y-m-d') .'"']);
        $thucchi = QuanLyThucChi::find(['conditions'    => 'ngay = "'. date('Y-m-d') .'"']);

        for ($i = 0; $i < count($thucthu) ; $i++) {
            
            $arrayThucThu['thucThu']['tongThu'] += $thucthu[$i]->soTien;
            switch ( $thucthu[$i]->nguonThu ) {
                case 'Bán Hàng':
                    $arrayThucThu['thucThu']['thuNguonThu']['banHang']    += $thucthu[$i]->soTien;
                    break;
                case 'Nhà Cung Cấp':
                    $arrayThucThu['thucThu']['thuNguonThu']['nhaCungCap'] += $thucthu[$i]->soTien;
                    break;
                case 'Vay Mượn':
                    $arrayThucThu['thucThu']['thuNguonThu']['vayMuon']    += $thucthu[$i]->soTien;
                    break;
                case 'Khác':
                    $arrayThucThu['thucThu']['thuNguonThu']['khac']       += $thucthu[$i]->soTien;
                    break;
                default:
                    
                    break;
            }
            
            $name = (isset($thucthu[$i]->NganHang->tenNganHang)) ? $thucthu[$i]->NganHang->tenNganHang : 'tienMat';
            $arrayThucThu['thucThu']['thuNganHang'][$name] += $thucthu[$i]->soTien; 

        }

        for ($i = 0; $i < count($thucchi); $i++) {
            
            $name = (isset($thucchi[$i]->NganHang->tenNganHang)) ? $thucchi[$i]->NganHang->tenNganHang : 'tienMat';
            $arrayThucThu['thucThu']['thuNganHang'][$name] -= $thucchi[$i]->soTien;
        }

        return $arrayThucThu;
    }

    public function createThucThu($data){

        $kq = true;

        //get value orders theo idKhachHang
        $donhang = Orders::searchParams([

            'conditions'    => 'trangThai NOT IN ({trangThai:array}) AND idKhachHang = :id:',
            'bind' => [ 
                'trangThai' =>['hoanTat','huyTruocBH','huySauBH'],
                'id' => $data->idKhachHang 
            ],
            "order" => 'ngay ASC'
        ]); 

        //get sum congNo
        $sum = 0;
        for ($i = 0; $i < count($donhang); $i++) {
            $sum += $donhang[$i]->congNo;
        }

        //nếu số tiền nhập nhỏ hơn tổng congNo của khách hàng
        if ( $data->soTien <= $sum ) {
            
            $tienDu = $data->soTien;

            for ($i = 0; $i < count($donhang); $i++) {
                
                //số tiền nhập trừ cho công nợ mỗi đơn hàng của khách hàng
                if ( $tienDu - $donhang[$i]->congNo >= 0 ) {

                    $tienDu = $tienDu - $donhang[$i]->congNo;

                    $donhang[$i]->daThanhToan   = $donhang[$i]->congNo;
                    $donhang[$i]->congNo        = 0;

                    $donhang[$i]->update();
                    $donhang[$i]->hoanTat();
                } else {

                    $donhang[$i]->congNo        = $donhang[$i]->congNo - $tienDu;
                    $donhang[$i]->daThanhToan   = $tienDu;

                    $donhang[$i]->update();
                    break;
                }
            }

        } else {

            $kq = 'Số Tiền Không Không Được Vượt Quá Tổng Công Nợ Của Công Ty';
        }

        return $kq;
    }

    public function updateThucThu($id, $idKhachHang, $soTienNhap){

        if (isset($idKhachHang)) {

            //lấy đơn hàng theo idKhachHang sắp xếp theo ngay
            $donhang = Orders::find([

                "order" => 'ngay ASC',
                'conditions'    => 'trangThai NOT IN ({arTrangThai:array}) AND idKhachHang = :id:',
                'bind' => [ 
                    'arTrangThai'   => ['huyTruocBH','huySauBH'],
                    'id'            => $idKhachHang 
                ]
            ]);

            //sum tongTienThanhToan các đơn hàng có idKhachHang
            $sumOrders = 0;
            for ($i = 0; $i < count($donhang); $i++) {
                $sumOrders += $donhang[$i]->tongTienThanhToan;
            }

            //sum soTien các thực thu có idKhachHang không tính thực thu đang edit
            $sumThucThuKhachHang = (double)QuanLyThucThu::sum([

                'column'    => 'soTien',
                'conditions'=> 'id <> ?0 AND idKhachHang = ?1',
                'bind'      => [ $id, $idKhachHang ]
            ]);

            $tienDu = $soTienNhap + $sumThucThuKhachHang;

            //nếu số tiền lớn hơn tổng tiền thanh toán của khách hàng
            if ($tienDu > $sumOrders) { 
                return "Số Tiền Không Không Được Vượt Quá Tổng Công Nợ Của Công Ty";
            }

            $this->_ordersUpdate($donhang, $tienDu);
        }

        return true;
    }

    public function deleteThuChi($data)
    {
        if ( isset($data) ) {

            //tìm các đơn hàng có trạng thái khác đã hủy và có idKhachHang đã truyền vào
            $donhang = Orders::find([

                'conditions'    => 'trangThai NOT IN ({arTrangThai:array}) AND idKhachHang = :id:',
                'bind' => [ 
                    'arTrangThai'   => ['huyTruocBH','huySauBH'],
                    'id'            => $data
                ]
            ]);

            //sum số tiền của tất cả các thực thu (trừ thực thu vừa xóa) có idKhachHang được truyền vào 
            $sumThucThuKhachHang = (double)QuanLyThucThu::sum([

                'column'    => 'soTien',
                "idKhachHang = " . $data,
            ]);

            $this->_ordersUpdate($donhang, $sumThucThuKhachHang);
        }
    }

    private function _ordersUpdate($donhang, $tienDu){

        for ($i = 0; $i < count($donhang); $i++) {
                
            if ( $tienDu - $donhang[$i]->tongTienThanhToan >= 0 ) {

                $tienDu -= $donhang[$i]->tongTienThanhToan;

                $donhang[$i]->daThanhToan   = $donhang[$i]->tongTienThanhToan;
                $donhang[$i]->congNo        = 0;

                $donhang[$i]->update();
                $donhang[$i]->hoanTat();
            } else {

                $donhang[$i]->trangThai     = 'conNo';
                $donhang[$i]->congNo        = $donhang[$i]->tongTienThanhToan - $tienDu;
                $donhang[$i]->daThanhToan   = $tienDu;
                $tienDu = 0;

                $donhang[$i]->update();
            }
        }
    }

    //tạo thực thu khi chuyển vốn
    public function chuyenVon($data)
    {
        $kq = true;

        $this->noiDung  = 'Chuyển Vốn '. $data->nameChiChuyenVon . ' -> ' . $data->nameThuChuyenVon;
        $this->soTien   = $data->soTien;
        $this->nguonThu = 'Chuyển Vốn';

        if ($data->thuChuyenVon == 'Tiền Mặt') {
            $this->hinhThuc     = 'Tiền Mặt';
            $this->ckNganHang   = '';
        } else {
            $this->hinhThuc     = 'Chuyển Khoản';
            $this->ckNganHang   = $data->thuChuyenVon;
        }

        if ( $this->create() ) {

            $quanLyThucChi = new QuanLyThucChi();

            if ( ($kq = $quanLyThucChi->chuyenVon( $data )) !== true ) {

                return $kq;
            }
        } else {
            $kq = "Tạo Thực Thu và Thực Chi Không Thành Công";
        }

        return $kq;   
    }

    //get dữ liệu có hình thức là chuyển khoản ( Ajax sử dụng )
    public static function tienNganHang($nganHang, $dateStart, $dateEnd, $nguonThu = '%', $nhomChiPhi = '%')
    {
        $thudauky       = (int)QuanLyThucThu::sum([

            'column'        => 'soTien',
            'conditions'    => 'ckNganHang = :nganHang: AND ngay < :date: AND nguonThu LIKE :nguonThu:',
            'bind'  => [
                'nganHang'  => $nganHang,
                'date'      => $dateStart,
                'nguonThu'  => $nguonThu
            ]
        ]);

        $thu            = (int)QuanLyThucThu::sum([

            'column'        => 'soTien',
            'conditions'    => 'ckNganHang = :nganHang: AND ngay between :dateStart: AND :dateEnd: AND nguonThu LIKE :nguonThu:',
            'bind'  => [
                'nganHang'  => $nganHang,
                'dateStart' => $dateStart,
                'dateEnd'   => $dateEnd,
                'nguonThu'  => $nguonThu
            ]
        ]);

        $thuDenHienTai  = (int)QuanLyThucThu::sum([

            'column'        => 'soTien',
            'conditions'    => 'ckNganHang = :nganHang: AND ngay between :dateStart: AND :dateEnd: AND nguonThu LIKE :nguonThu:',
            'bind'  => [
                'nganHang'  => $nganHang,
                'dateStart' => $dateStart,
                'dateEnd'   => date('Y-m-d'),
                'nguonThu'  => $nguonThu
            ]
        ]);

        $chidauky       = (int)QuanLyThucChi::sum([

            'column'        => 'soTien',
            'conditions'    => 'ckNganHang = :nganHang: AND ngay < :date: AND nhomChiPhi LIKE :nhomChiPhi:',
            'bind'  => [
                'nganHang'  => $nganHang,
                'date'  => $dateStart,
                'nhomChiPhi'=> $nhomChiPhi
            ]
        ]);

        $chi            = (int)QuanLyThucChi::sum([

            'column'        => 'soTien',
            'conditions'    => 'ckNganHang = :nganHang: AND ngay between :dateStart: AND :dateEnd: AND nhomChiPhi LIKE :nhomChiPhi:',
            'bind'  => [
                'nganHang'  => $nganHang,
                'dateStart' => $dateStart,
                'dateEnd'   => $dateEnd,
                'nhomChiPhi'=> $nhomChiPhi
            ]
        ]);

        $chiDenHienTai  = (int)QuanLyThucChi::sum([

            'column'        => 'soTien',
            'conditions'    => 'ckNganHang = :nganHang: AND ngay between :dateStart: AND :dateEnd: AND nhomChiPhi LIKE :nhomChiPhi:',
            'bind'  => [
                'nganHang'  => $nganHang,
                'dateStart' => $dateStart,
                'dateEnd'   => date('Y-m-d'),
                'nhomChiPhi'=> $nhomChiPhi
            ]
        ]);

        $dauky      = $thudauky - $chidauky;
        $ton        = $dauky + $thu - $chi;
        $hientai    = $dauky + $thuDenHienTai - $chiDenHienTai;

        return [ 'dauky' 	=> $dauky, 
				 'thu'		=> $thu, 
				 'chi'		=> $chi, 
				 'ton'		=> $ton, 
				 'hientai'	=> $hientai ];
    }

    //get dữ liệu có hình thức là tiền mặt ( Ajax sử dụng )
    public static function tienMat($dateStart, $dateEnd, $nguonThu = '%', $nhomChiPhi = '%')
    {

        $thudauky       = (int)QuanLyThucThu::sum([

            'column' => 'soTien',
            'conditions'    => 'hinhThuc = :hinhThuc: AND ngay < :date: AND nguonThu LIKE :nguonThu:',
            'bind'  => [
                'hinhThuc'  => 'Tiền Mặt',
                'date'      => $dateStart,
                'nguonThu'  => $nguonThu
            ]
        ]);
		
        $thu            = (int)QuanLyThucThu::sum([

            'column' => 'soTien',
            'conditions'    => 'hinhThuc = :hinhThuc: AND ngay between :dateStart: AND :dateEnd: AND nguonThu LIKE :nguonThu:',
            'bind'  => [
                'hinhThuc'  => 'Tiền Mặt',
                'dateStart' => $dateStart,
                'dateEnd'   => $dateEnd,
                'nguonThu'  => $nguonThu
            ]
        ]);

        $thuDenHienTai  = (int)QuanLyThucThu::sum([

            'column' => 'soTien',
            'conditions'    => 'hinhThuc = :hinhThuc: AND ngay between :dateStart: AND :dateEnd: AND nguonThu LIKE :nguonThu:',
            'bind'  => [
                'hinhThuc'  => 'Tiền Mặt',
                'dateStart' => $dateStart,
                'dateEnd'   => date('Y-m-d'),
                'nguonThu'  => $nguonThu
            ]
        ]);

        $chidauky       = (int)QuanLyThucChi::sum([

            'column' => 'soTien',
            'conditions'    => 'hinhThuc = :hinhThuc: AND ngay < :date: AND nhomChiPhi LIKE :nhomChiPhi:',
            'bind'  => [
                'hinhThuc'  => 'Tiền Mặt',
                'date'  => $dateStart,
                'nhomChiPhi'=> $nhomChiPhi
            ]
        ]);

        $chi            = (int)QuanLyThucChi::sum([

            'column' => 'soTien',
            'conditions'    => 'hinhThuc = :hinhThuc: AND ngay between :dateStart: AND :dateEnd: AND nhomChiPhi LIKE :nhomChiPhi:',
            'bind'  => [
                'hinhThuc'  => 'Tiền Mặt',
                'dateStart' => $dateStart,
                'dateEnd'   => $dateEnd,
                'nhomChiPhi'=> $nhomChiPhi
            ]
        ]);

        $chiDenHienTai  = (int)QuanLyThucChi::sum([

            'column' => 'soTien',
            'conditions'    => 'hinhThuc = :hinhThuc: AND ngay between :dateStart: AND :dateEnd: AND nhomChiPhi LIKE :nhomChiPhi:',
            'bind'  => [
                'hinhThuc'  => 'Tiền Mặt',
                'dateStart' => $dateStart,
                'dateEnd'   => date('Y-m-d'),
                'nhomChiPhi'=> $nhomChiPhi
            ]
        ]);

        $dauky      = $thudauky - $chidauky;
        $ton        = $dauky + $thu - $chi;
        $hientai    = $dauky + $thuDenHienTai - $chiDenHienTai;
		
        return [ 'dauky' 	=> $dauky, 
				 'thu'		=> $thu, 
				 'chi'		=> $chi, 
				 'ton'		=> $ton, 
				 'hientai'	=> $hientai ];
    }

}
