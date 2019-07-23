<?php 

use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\TextArea;
use Phalcon\Forms\Element\Hidden;
use Phalcon\Forms\Element\Select;
use Phalcon\Validation\Validator\PresenceOf;

/**
* 
*/
class QuanLyThucChiForm extends BaseForm
{
	public function initialize($entity = null, $options = array())
	{

        //Nội Dung
        $noiDung = new Text("noiDung");
        $noiDung->setFilters(['striptags','trim']);
        $noiDung->addValidators([
            new PresenceOf(['message' => 'Vui lòng nhập Nội Dung'])
        ]);
        $this->add($noiDung);    

        //Số Tiền
        $tien = new Text('soTien');
        $tien->addValidators([
            new PresenceOf(['message' => 'Vui lòng nhập Số Tiền'])
        ]);
        $this->add($tien);

        //Người Nhận
        $nguoiNhan = new Text("nguoiNhan");
        $nguoiNhan->setFilters(['striptags','trim']);
        $nguoiNhan->addValidators([
            new PresenceOf(['message' => 'Vui lòng nhập Người Nhận'])
        ]);
        $this->add($nguoiNhan);

        //
        $nguonChi = new Select('nguonChi', [
            'Sản Xuất'  => 'Sản Xuất',
            'Chi Phí'   => 'Chi Phí',
            'Khác'      => 'Khác'
            ],['useEmpty' => true,
            'emptyText' => '...',
            'emptyValue' => '']
        );
        $nguonChi->setFilters(['striptags','trim']);
        $nguonChi->addValidators([ new PresenceOf(['message' => 'Vui lòng chọn nguồn chi'])]);
        $this->add($nguonChi);

        //Nguồn Thu
        $nhomChiPhi = new Select('nhomChiPhi', [
            'Cá Nhân'       => 'Cá Nhân',
            'Văn Phòng'     => 'Văn Phòng',
            'Quản Lý'       => 'Quản Lý',
            'Lương'         => 'Lương',
            'Cá Nhân'       => 'Cá Nhân',
            'Xưởng'         => 'Xưởng',
            'Chi Bán Hàng'  => 'Chi Bán Hàng',
            'Trả Nợ'        => 'Trả Nợ',
            ], 
            ['useEmpty' => true,
            'emptyText' => '...',
            'emptyValue' => '']
        );
        $nhomChiPhi->setFilters(['striptags','trim']);
        $this->add($nhomChiPhi);

        //Hình Thức Thu
        $hinhthuc = new Select('hinhThuc', [
            'Tiền Mặt'  => 'Tiền Mặt',
            'Chuyển Khoản'  => 'Chuyển Khoản',
        ]);
        $hinhthuc->setFilters(['striptags','trim']);
        $hinhthuc->addValidators([ new PresenceOf(['message' => 'Vui lòng chọn Hình Thức'])]);
        $this->add($hinhthuc);

        //Chuyển khoản ngân hàng nào
        $ckNganHang = new Select('ckNganHang',NganHang::find(), [
            'using' => [ 'id', 'tenNganHang' ],
        ]);
        $ckNganHang->setFilters(['int','trim']);
        $this->add($ckNganHang);

        //Chi TIết Chi Phí
        $this->add((new Text('chiTietChiPhi'))->setFilters(['striptags','trim']));

        //Chi TIết Chi Phí
        $this->add((new Hidden('idDatHang'))->setFilters(['int','trim']));

        //Chi TIết Chi Phí
        $this->add((new Hidden('idQuanLyChiPhi'))->setFilters(['int','trim']));

        //Ghi Chú
		$this->add((new TextArea('ghiChu'))->setFilters(['striptags','trim']));
	}

}