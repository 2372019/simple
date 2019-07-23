<?php 

use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\TextArea;
use Phalcon\Forms\Element\Select;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\StringLength;

/**
* 
*/
class CustomersForm extends BaseForm
{
	public function initialize($entity = null, $options = array())
	{
        $name = new Text("tenKhachHang");
        $name->setFilters(['striptags','trim']);
        $name->addValidators([
            new PresenceOf(['message' => 'Vui lòng nhập Tên khách hàng']),
            new StringLength(['max'   =>300,'messageMaximum' => 'Tên Sản Phẩm Chỉ Được Tối Đa 300 Ký Tự'
            ])
        ]);
        $this->add($name);

        $mst = new Text('mST');
        $mst->setFilters(['striptags','trim']);
        $mst->addValidators([
            new StringLength(['max'   =>20,'messageMaximum' => 'Mã Số Thuế Chỉ Được Tối Đa 20 Ký Tự'
            ])
        ]);
        $this->add($mst);    

        $dt = new Text('soDienThoai');
        $dt->setFilters(['striptags','trim']);
        $dt->addValidators([
            new StringLength(['max'   =>50,'messageMaximum' => 'Số Điện Thoại Chỉ Được Tối Đa 50 Ký Tự'
            ]),
        ]);
        $this->add($dt);

        $loaiKhachHang = new Select('loaiKhachHang', [
            'Khách Lẻ' => 'Khách Lẻ',
            'Đại Lý Công Ty' => 'Đại Lý Công Ty',
            'Cửa Hàng' => 'Cửa Hàng',
            'Công Ty Thương Mại' => 'Công Ty Thương Mại',
            'Khác' => 'Khác'
        ]);
        $loaiKhachHang->setFilters(['striptags','trim']);
        $loaiKhachHang->addValidators([ new PresenceOf(['message' => 'Vui lòng nhập Loại Khách Hàng']),]);
        $this->add($loaiKhachHang);

        $this->add((new Text('diaChi'))->setFilters(['striptags','trim']));
        $this->add((new Text('email'))->setFilters(['striptags','trim']));
        $this->add((new Text('nguoiMuaHang'))->setFilters(['striptags','trim']));
		$this->add((new TextArea('ghiChu'))->setFilters(['striptags','trim']));
	}

}