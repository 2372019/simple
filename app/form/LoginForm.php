<?php 

use Phalcon\Forms\Form;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\Password;
use Phalcon\Forms\Element\Submit;
use Phalcon\Forms\Element\Hidden;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Identical;
use Phalcon\Validation\Validator\StringLength;

/**
* 
*/
class LoginForm extends BaseForm
{
	
	public function initialize()
	{
		//Username
		$username = new Text("username");
        $username->setFilters(['striptags','trim']);
        $username->addValidators([
            new PresenceOf(['message' => 'Vui lòng nhập Tên Đăng Nhập']),
            new StringLength(['max'   =>100,'messageMaximum' => 'Tên Đăng Nhập Chỉ Được Tối Đa 100 Ký Tự'
            ])
        ]);
        $this->add($username);

        // Password
        $password = new Password('password');
        $password->setFilters(['striptags','trim']);
        $password->addValidator(new PresenceOf([
            'message' => 'Vui lòng nhập mật khẩu'
        ]));

        $password->clear();

        $this->add($password);

        //CSRF
        $csrf = new Hidden('csrf');

        $csrf->addValidator(new Identical([
        	'value'		=> $this->security->getSessionToken(),
        	'message'	=> 'CSRF validation failed'
        ]));

        $csrf->clear();
        $this->add($csrf);

        $this->add(new Submit('go'));
	}
}
?>