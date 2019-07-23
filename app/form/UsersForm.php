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
class UsersForm extends BaseForm
{
	
	public function initialize($entity = null, $options = array())
	{
        if (isset($options['edit']) && $options['edit']) {

            //new username
            $username = new Text("username", ['disabled' => 'disabled']);

            //password
            $password = new Password("password");
            $password->setFilters(['striptags', 'trim']);
            $password->addValidators([

                new StringLength([
                    'min'   =>8,
                    'messageMinimum' => 'Mật Khẩu Phải Tối Thiểu 8 Ký Tự',
                    'allowEmpty' =>true
                ]),
                new Confirmation([
                    'message' => 'Nhập Lại Mật Khẩu Không Trùng Khớp',
                    'with' => 'confirmPassword'
                ])
            ]);
            $this->add($password);

            //confimPassword
            $this->add((new Password('confirmPassword'))->setFilters(['striptags','trim']));

        } else {

            //new username
            $username = new Text("username");
            $username->addValidators([
                new PresenceOf([
                    'message' => 'Vui lòng nhập Tên Tài Khoản'
                ])
            ]);

            //password
            $password = new Password("password");
            $password->setFilters(['striptags', 'trim']);
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
            $confirmpassword->setFilters(['striptags', 'trim']);
            $confirmpassword->addValidators([
                new PresenceOf([
                    'message' => 'Vui lòng nhập Nhập Lại Mật Khẩu'
                ])
            ]);
            $this->add($confirmpassword);

        }

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
        $username->setFilters(['striptags', 'string']);
        $this->add($username);

        //phone
        $phone = new Text('phone');
        $phone->addValidators([
            new StringLength(['max'   =>50,'messageMaximum' => 'Số Điện Thoại Chỉ Được Tối Đa 50 Ký Tự'
            ]),
            new Numericality(array('message' => 'Vui lòng nhập đúng Số Điện Thoại', 'allowEmpty' => true
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
        $groupid = new Select('group_id', PhanQuyen::find(), [

            'using' => [ 'id', 'name' ],
        ]);
        $groupid->setDefault(3);
        $groupid->addValidators([
            new StringLength(['max'   =>1]),
            new Numericality(array('message' => 'Vui lòng nhập Số'
            ))
        ]);
        $this->add($groupid);

        //status
        $status = new Select('blocked', [
            '1' => 'Kích Hoạt',
            '0' => 'Không Kích Hoạt'
        ]);
        $this->add($status);

        $this->add((new Text('address'))->setFilters(['striptags','trim']));
        $this->add((new Text('email'))->setFilters(['striptags','trim']));
        $this->add((new Text('hanMucChiPhi'))->setFilters(['striptags','trim']));

	}
}