<?php 

use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\TextArea;
use Phalcon\Forms\Element\Date;
use Phalcon\Forms\Element\Select;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Numericality;
use Phalcon\Validation\Validator\StringLength;
use Phalcon\Validation\Validator\Date as DateValidator;

/**
* 
*/
class NhanSuForm extends BaseForm
{
	public function initialize($entity = null, $options = array())
	{
        $name = new Text("hoTen");
        $name->setFilters(['striptags','trim']);
        $name->addValidators([
            new PresenceOf(['message' => 'Vui lòng nhập Tên khách hàng']),
            new StringLength(['max'   =>100,'messageMaximum' => 'Họ và Tên Chỉ Được Tối Đa 100 Ký Tự'
            ])
        ]);
        $this->add($name);    

        $cmnd = new Text('CMND');
        $cmnd->addValidators([
            new StringLength(['max'   =>100,'messageMaximum' => 'Chứng Minh Nhân Dân Chỉ Được Tối Đa 100 Ký Tự'
            ]),
            new Numericality(array('message' => 'Vui lòng nhập đúng Chứng Minh Nhân Dân'
            ))
        ]);
        $this->add($cmnd);

        $dt = new Text('SDT');
        $dt->addValidators([
            new StringLength(['max'   =>50,'messageMaximum' => 'Số Điện Thoại Chỉ Được Tối Đa 50 Ký Tự'
            ])
        ]);
        $this->add($dt);

        $diachi = new Text('diaChi');
        $diachi->setFilters(['striptags','trim']);
        $diachi->addValidators([
            new PresenceOf(['message' => 'Vui lòng nhập Địa Chỉ']),
        ]);
        $this->add($diachi);

        $chucVu = new Select('chucVu', [
            'Nhân viên giao hàng'   => 'Nhân viên giao hàng',
            'Giao hàng thời vụ'     => 'Giao hàng thời vụ',
            'Nhân viên kỹ thuật'    => 'Nhân viên kỹ thuật',
            'Văn phòng'             => 'Văn phòng',
            'Khác'                  => 'Khác'
        ]);
        $chucVu->setFilters(['striptags','trim']);
        $chucVu->addValidators([ new PresenceOf(['message' => 'Vui lòng nhập Chức Vụ']) ]);
        $this->add($chucVu);

        $ngaySinh = new Date('ngaySinh');
        $ngaySinh->addValidators([
            new DateValidator(['message' => 'Vui lòng nhập Ngày Sinh'])
        ]);
        $this->add($ngaySinh);

        $this->add((new Text('queQuan'))->setFilters(['striptags','trim']));
		$this->add((new TextArea('ghiChu'))->setFilters(['striptags','trim']));
	}

}