<?php

class QuanLyThucChi extends BaseModel
{
	public $id;                    // <int> Not null, Default AI
	public $ngay;                  // <date> Not null
	public $noiDung;               // <string> Not null
	public $soTien;                // <double> Not null
	public $nguoiNhan;		       // <string> Not null
    public $idDatHang;             // <int>
    public $idQuanLyChiPhi;        // <int>     
	public $nguonChi;              // <string> Not null
	public $nhomChiPhi;		       // <string>
	public $chiTietChiPhi ='' ;    // <string>
	public $hinhThuc;              // <string> Not null
	public $ckNganHang;            // <string>
	public $ghiChu;                // <string>

    public $tenNhaCungCap;         // không thêm vào db mà đây dùng để gán dữ liệu khi tìm kiếm hoặc sắp xếp
    public $tenNganHang;           // <string> không thêm vào db mà đây dùng để gán dữ liệu khi tìm kiếm hoặc sắp xếp

	public function initialize()
	{
        $this->ngay = date('Y-m-d');
        
		$this->hasOne("nguoiNhan", "NhaCungCap", "id");
        $this->hasOne("idDatHang", "DatHang", "id");
        $this->hasOne("idQuanLyChiPhi", "QuanLyChiPhi", "id");
        $this->hasOne("ckNganHang", "NganHang", "id");
	}

    public function getThongTinRow()
    {
        $kq = $this->toArray();

        $kq['tenNhaCungCap']    = isset($this->NhaCungCap->tenNhaCungCap) ? $this->NhaCungCap->tenNhaCungCap : $this->nguoiNhan;
        $kq['tenNganHang']      = isset($this->NganHang->tenNganHang) ? $this->NganHang->tenNganHang : '';
        $kq['ngay']             = date_format(date_create($this->ngay),'d-m-Y');
        
        return $kq;
    }

    //khi update và chuẩn bị dữ liệu để update congNo đặt hàng
    public function updateThucChi($id, $idNhaCungCap, $soTienNhap)
    {
        if (isset($idNhaCungCap)) {

            //ngày cách hiện tại 6 tháng
            $ngay = date('Y-m-d',strtotime("-6 months"));

            //lấy dặt hàng theo idNhaCungCap sắp xếp theo ngay
            $dathang = DatHang::find([

                'conditions'=> 'idNhaCungCap = :idNhaCungCap: AND ngay > :ngay:',
                'bind'      => ['idNhaCungCap' => $idNhaCungCap, 'ngay' => $ngay . ' 00:00:00'],
                'order'     => 'ngay ASC'
            ]);

             //sum tongTienThanhToan các đặt hàng có idNhaCungCap
            $sumDatHang = 0;
            for ($i = 0; $i < count($dathang); $i++) {
                $sumDatHang += $dathang[$i]->tongTienThanhToan;
            }

            //sum soTien các thực chi có idNhaCungCap không tính thực chi đang edit
            $sumThucChi = (double)QuanLyThucChi::sum([

                'column'    => 'soTien',
                'conditions'=> 'id <> ?0 AND nguoiNhan = ?1 AND ngay > ?2',
                'bind'      => [ $id, $idNhaCungCap, $ngay ]
            ]);

            $tienDu = $soTienNhap + $sumThucChi;

            //nếu số tiền lớn hơn tổng tiền thanh toán của nhà cung cấp
            if ($tienDu > $sumDatHang) { 
                return "Số Tiền Không Không Được Vượt Quá Tổng Công Nợ Của Nhà Cung Cấp";
            }

            $this->_datHangUpdate($dathang, $tienDu);
        }

        return true;
    }

    //khi xóa thực chi lien quan với mua vào
    public function deleteThuChi($data)
    {
        if ( $data->idDatHang ) {

            $datHang = DatHang::findFirstById($data->idDatHang);
            if ($datHang) {

                $datHang->congNo += $data->soTien; 

                if ( $datHang->congNo > 0 ) {
                    
                    $datHang->trangThai = '';
                    $datHang->daThanhToan = 0;
                    $datHang->update();
                }   
            } else {
                return "Lỗi";
            }
        } elseif ( $data->idQuanLyChiPhi ) {

            $qlcp = QuanLyChiPhi::findFirstById($data->idQuanLyChiPhi);

            if ($qlcp)
                $qlcp->update(['trangThai' => '']);
            else
                return "Lỗi";
        }

        return true;
    }

    //update congNo đặt hàng
    private function _datHangUpdate($dathang, $tienDu)
    {
        for ($i = 0; $i < count($dathang); $i++) {
                
            if ( $tienDu - $dathang[$i]->tongTienThanhToan >= 0 ) {

                $tienDu -= $dathang[$i]->tongTienThanhToan;

                $dathang[$i]->daThanhToan   = $dathang[$i]->tongTienThanhToan;
                $dathang[$i]->congNo        = 0;

                $dathang[$i]->update();
                $dathang[$i]->hoanTat();
            } else {

                $dathang[$i]->trangThai     = '';
                $dathang[$i]->congNo        = $dathang[$i]->tongTienThanhToan - $tienDu;
                $dathang[$i]->daThanhToan   = $tienDu;
                $tienDu = 0;

                $dathang[$i]->update();
            }
        }
    }

    //tạo thực chi khi chuyển vốn
    public function chuyenVon($data)
    {
        $kq = true;

        $this->noiDung      = 'Chuyển Vốn '. $data->nameChiChuyenVon . ' -> ' . $data->nameThuChuyenVon;
        $this->soTien       = $data->soTien;
        $this->nhomChiPhi   = 'Chuyển Vốn';
        $this->nguonChi     = 'Chuyển Vốn';
        $this->nguoiNhan    = 'Chuyển Vốn';

        if ($data->chiChuyenVon == 'Tiền Mặt') {
            $this->hinhThuc     = 'Tiền Mặt';
            $this->ckNganHang   = '';
        } else {
            $this->hinhThuc     = 'Chuyển Khoản';
            $this->ckNganHang   = $data->chiChuyenVon;
        }

        if ( !$this->create() ) {

            $kq = "Tạo Thực Chi Không Thành Công";
        }

        return $kq;
    }

    /*
    *   Tính toán tiền các thông tin như các chi tiết thực chi, thực chi khác, thực chi cũ( dữ liệu cũ ),...
    *   Kết Quả trả về là mảng các thông tin trên
    */
    public static function thongKeThucChi()
    {
        $arrayThucChi = array(

            'thucChi'    => [

                'tongThucChi'       => 0,
                'sanXuat'   => [
                    'binh'          => 0,
                    'motor'         => 0,
                    'dauNen'        => 0,
                    'vatTu'         => 0,
                    'khac'          => 0,
                ],

                'chiPhi'    => [
                    'luong'         => 0,
                    'vanPhong'      => 0,
                    'quanLy'        => 0,
                    'xuong'         => 0,
                    'caNhan'        => 0,
                    'chiBanHang'    => 0,
                    'traNo'         => 0,
                ],
                'thucChiKhac'   => [
                    'khac'              => 0,
                    'thucChiCu'         => 0
                ]
                
            ]
            
        );

        $thucchi = QuanLyThucChi::find(['conditions'    => 'ngay = "'. date('Y-m-d') .'"']);

        for ($i = 0; $i < count($thucchi) ; $i++) {

            $arrayThucChi['thucChi']['tongThucChi']     += $thucchi[$i]->soTien;
            
            if ($thucchi[$i]->nguonChi == 'Sản Xuất') {

                switch ( $thucchi[$i]->chiTietChiPhi ) {
                    case 'Bình':
                        $arrayThucChi['thucChi']['sanXuat']['binh']    += $thucchi[$i]->soTien;
                        break;

                    case 'Motor':
                        $arrayThucChi['thucChi']['sanXuat']['motor']   += $thucchi[$i]->soTien;
                        break;

                    case 'Đầu Nén':
                        $arrayThucChi['thucChi']['sanXuat']['dauNen']  += $thucchi[$i]->soTien;
                        break;

                    case 'Vật Tư':
                        $arrayThucChi['thucChi']['sanXuat']['vatTu']   += $thucchi[$i]->soTien;
                        break;

                    case 'Khác':
                        $arrayThucChi['thucChi']['sanXuat']['khac']    += $thucchi[$i]->soTien;
                        break;

                    default:
                        break;
                }

            } elseif ($thucchi[$i]->nguonChi == 'Chi Phí') {

                switch ($thucchi[$i]->nhomChiPhi) {
                    case 'Cá Nhân':
                        $arrayThucChi['thucChi']['chiPhi']['caNhan']       += $thucchi[$i]->soTien;
                        break;
                    case 'Văn Phòng':
                        $arrayThucChi['thucChi']['chiPhi']['vanPhong']     += $thucchi[$i]->soTien;
                        break;
                    case 'Quản Lý':
                        $arrayThucChi['thucChi']['chiPhi']['quanLy']       += $thucchi[$i]->soTien;
                        break;
                    case 'Lương':
                        $arrayThucChi['thucChi']['chiPhi']['luong']        += $thucchi[$i]->soTien;
                        break;
                    case 'Xưởng':
                        $arrayThucChi['thucChi']['chiPhi']['xuong']        += $thucchi[$i]->soTien;
                        break;
                    case 'Chi Bán Hàng':
                        $arrayThucChi['thucChi']['chiPhi']['chiBanHang']   += $thucchi[$i]->soTien;
                        break;
                    case 'Trả Nợ':
                        $arrayThucChi['thucChi']['chiPhi']['traNo']        += $thucchi[$i]->soTien;
                        break;
                    
                    default:
                        break;
                }
            } elseif ($thucchi[$i]->nguonChi == 'Khác') {
                
                $arrayThucChi['thucChi']['thucChiKhac']['khac'] += $thucchi[$i]->soTien;
            } else {

                $arrayThucChi['thucChi']['thucChiKhac']['thucChiCu'] += $thucchi[$i]->soTien;
            }
        }

        return $arrayThucChi;
    }

}
