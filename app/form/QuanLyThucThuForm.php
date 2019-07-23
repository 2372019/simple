<?php 

use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\TextArea;
use Phalcon\Forms\Element\Hidden;
use Phalcon\Forms\Element\Select;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Numericality;

/**
* 
*/
class QuanLyThucThuForm extends BaseForm
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

        //Nguồn Thu
        $nguonthu = new Select('nguonThu', [
            'Bán Hàng'      => 'Bán Hàng',
            'Nhà Cung Cấp'  => 'Nhà Cung Cấp',
            'Vay Mượn'      => 'Vay Mượn',
            'Khác'          => 'Khác',
        	], 
        	['useEmpty' => true,
            'emptyText' => '...',
            'emptyValue' => '']
        );
        $nguonthu->setFilters(['striptags','trim']);
        $nguonthu->addValidators([ new PresenceOf(['message' => 'Vui lòng chọn Nguồn Thu'])]);
        $this->add($nguonthu);

        //id khách hàng( liên kết bảng Customers)
        $idKhachHang = new Hidden('idKhachHang');
        $idKhachHang->addValidators([
            new Numericality(array( 'message' => 'Vui lòng nhập đúng Khách Hàng' , 'allowEmpty' => true ))
        ]);
        $this->add($idKhachHang);

        //id khách hàng( liên kết bảng Customers)
        $idNhaCungCap = new Hidden('idNhaCungCap');
        $idNhaCungCap->addValidators([
            new Numericality(array( 'message' => 'Vui lòng nhập đúng Nhà Cung Cấp' , 'allowEmpty' => true ))
        ]);
        $this->add($idNhaCungCap);

        //Hình Thức Thu
        $hinhthuc = new Select('hinhThuc', [
            'Tiền Mặt'  => 'Tiền Mặt',
            'Chuyển Khoản'  => 'Chuyển Khoản',
        ]);
        $hinhthuc->setFilters(['striptags','trim']);
        $hinhthuc->addValidators([ new PresenceOf(['message' => 'Vui lòng chọn Hình Thức'])]);
        $this->add($hinhthuc);

        //Chuyển khoản qua ngân hàng nào
        $ckNganHang = new Select('ckNganHang',NganHang::find(), [
            'using' => [ 'id', 'tenNganHang' ],
        ]);
        $ckNganHang->setFilters(['int','trim']);
        $this->add($ckNganHang);

        //Ghi Chú
		$this->add((new TextArea('ghiChu'))->setFilters(['striptags','trim']));
	}

}