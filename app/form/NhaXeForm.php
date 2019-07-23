<?php 

use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\TextArea;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\StringLength;

/**
* 
*/
class NhaXeForm extends BaseForm
{
	public function initialize($entity = null, $options = array())
	{
		//tên nhà xe
        $name = new Text("tenNhaXe");
        $name->setFilters(['striptags','trim']);
        $name->addValidators([
            new PresenceOf(['message' => 'Vui lòng nhập Tên Nhà Xe']),
            new StringLength(['max'   =>250,'messageMaximum' => 'Họ và Tên Chỉ Được Tối Đa 250 Ký Tự'
            ])
        ]);
        $this->add($name);    

        //lộ trình đi
        $lotrinhdi = new Text('loTrinhDi');
        $lotrinhdi->addValidators([
            new PresenceOf(['message' => 'Vui lòng nhập Lộ Trình Đi']),
            new StringLength(['max'   =>250,'messageMaximum' => 'Lộ Trình Đi Chỉ Được Tối Đa 100 Ký Tự'
            ])
        ]);
        $this->add($lotrinhdi);

        //lộ trình đến
        $lotrinhden = new Text('loTrinhDen');
        $lotrinhden->addValidators([
            new PresenceOf(['message' => 'Vui lòng nhập Lộ Trình Đến']),
            new StringLength(['max'   =>250,'messageMaximum' => 'Lộ Trình Đến Chỉ Được Tối Đa 100 Ký Tự'
            ])
        ]);
        $this->add($lotrinhden);

        //số điện thoại
        $dt = new Text('soDienThoai');
        $dt->addValidators([
            new StringLength(['max'   =>50,'messageMaximum' => 'Số Điện Thoại Chỉ Được Tối Đa 50 Ký Tự'
            ])
        ]);
        $this->add($dt);

        //địa chỉ
        $diachi = new Text('diaChi');
        $diachi->setFilters(['striptags','trim']);
        $diachi->addValidators([
            new PresenceOf(['message' => 'Vui lòng nhập Địa Chỉ']),
        ]);
        $this->add($diachi);

        //ghi chú
		$this->add((new TextArea('ghiChu'))->setFilters(['striptags','trim']));
	}

}