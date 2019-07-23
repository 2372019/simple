<?php 

use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\TextArea;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\StringLength;

/**
* 
*/
class NhaCungCapForm extends BaseForm
{
	public function initialize($entity = null, $options = array())
	{
        $tenNhaCungCap = new Text("tenNhaCungCap");
        $tenNhaCungCap->setFilters(['striptags','trim']);
        $tenNhaCungCap->addValidators([
            new PresenceOf(['message' => 'Vui lòng nhập Tên Nhà Cung Cấp']),
            new StringLength(['max'   =>100,'messageMaximum' => 'Tên Nhà Cung Cấp Chỉ Được Tối Đa 100 Ký Tự'
            ])
        ]);
        $this->add($tenNhaCungCap);    

        $diachi = new Text('diaChi');
        $diachi->setFilters(['striptags','trim']);
        $diachi->addValidators([
            new PresenceOf(['message' => 'Vui lòng nhập Địa Chỉ']),
        ]);
        $this->add($diachi);

        $lienhe = new Text('lienHe');
        $lienhe->setFilters(['striptags','trim']);
        $lienhe->addValidators([
            new PresenceOf(['message' => 'Vui lòng nhập Liên Hệ']),
        ]);
        $this->add($lienhe);

        $this->add((new Text('moTa'))->setFilters(['striptags','trim']));
		$this->add((new TextArea('ghiChu'))->setFilters(['striptags','trim']));
	}

}