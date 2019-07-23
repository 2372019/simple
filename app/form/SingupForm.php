<?php 

use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\Select;
use Phalcon\Forms\Element\Password;
use Phalcon\Forms\Element\Date;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Numericality;
use Phalcon\Validation\Validator\StringLength;
use Phalcon\Validation\Validator\Confirmation;

/**
* 
*/
class SingupForm extends BaseForm
{
	
	public function initialize($entity = null, $options = array())
	{
        //name
		$name = new Text("name");
        $name->setFilters(['striptags', 'string']);
        $name->addValidators([
            new PresenceOf([
                'message' => 'Vui lòng nhập Tên'
            ]),
            new StringLength([
                'max'   =>50,
                'messageMaximum' => 'Tên Chỉ Được Tối Đa 50 Ký Tự'
            ]),
        ]);
        $this->add($name);

        //username
        $username = new Text("username");
        $username->setFilters(['striptags', 'string']);
        $username->addValidators([
            new PresenceOf([
                'message' => 'Vui lòng nhập Tên Tài Khoản'
            ])
        ]);
        $this->add($username);

        //password
        $password = new Password("password");
        $password->addValidators([
            new PresenceOf([
                'message' => 'Vui lòng nhập Mật Khẩu'
            ]),
            new StringLength([
                'min'   =>8,
                'messageMinimum' => 'Mật Khẩu Phải Tối Thiểu 8 Ký Tự'
            ]),
            new Confirmation([
                'message' => 'Nhập Lại Mật Khẩu Không Trùng Khớp',
                'with' => 'confirmPassword'
            ])
        ]);
        $this->add($password);

        //confimPassword
        $confirmpassword = new Password("confirmPassword");
        $confirmpassword->addValidators([
            new PresenceOf([
                'message' => 'Vui lòng nhập Nhập Lại Mật Khẩu'
            ])
        ]);
        $this->add($confirmpassword);

        //phone
        $phone = new Text('phone');
        $phone->addValidators([
            new StringLength(['max'   =>50,'messageMaximum' => 'Số Điện Thoại Chỉ Được Tối Đa 50 Ký Tự'
            ]),
            new Numericality(array('message' => 'Vui lòng nhập đúng Số Điện Thoại','allowEmpty' => true
            ))
        ]);
        $this->add($phone);

        //gender
        $gender = new Select('gender', [
            '1' => 'Nam',
            '0' => 'Nữ',
        ]);
        $gender->addValidators([
            new StringLength(['max'   =>1]),
            new Numericality(array('message' => 'Vui lòng nhập Số'
            ))
        ]);
        $this->add($gender);

        //birth
        $birth = new Date('birth');
        $birth->addValidators([
            new PresenceOf([
                'message' => 'Vui lòng nhập Ngày Sinh'
            ]),
        ]);
        $this->add($birth);

        //groupID
        $groupid = new Select('group_id', [
            '1' => 'Admin',
            '2' => 'Đăng Bài',
            '3' => 'Khách',
        ]);
        $groupid->addValidators([
            new StringLength(['max'   =>1]),
            new Numericality(array('message' => 'Vui lòng nhập Số'
            ))
        ]);
        $this->add($groupid);

        //status
        $status = new Select('blocked', [
            '1' => 'Xuất Bản',
            '0' => 'Không Xuất Bản'
        ]);
        $status->addValidators([
            new StringLength(['max'   =>1]),
            new Numericality(array('message' => 'Vui lòng nhập Số'
            ))
        ]);
        $this->add($status);

        $this->add((new Text('address'))->setFilters(['striptags','trim']));
        $this->add((new Text('email'))->setFilters(['striptags','trim']));

	}
}