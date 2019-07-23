<?php 

use Phalcon\Validation;
use Phalcon\Validation\Validator\Uniqueness as UniquenessValidator;

class Users extends BaseModel
{
	public $id;					// <int> Not null, Default AI
	public $group_id;			// <int> Not null
	public $tenPhanQuyen;		// <string> Cái này dùng để lọc
	public $username;			// <string> Not null
	public $email = '';			// <string>
	public $password;			// <string> Not null
	public $name = '';				// <string> Not null
	public $gender;				// <int> Not null
	public $birth;				// <date> Not null
	public $phone = '';			// <string>
	public $address = '';		// <string>
	public $blocked = '0';		// <int> Not null
	public $registed;			// <date> Not null
	public $last_visited;		// <date>
	public $log_failed = '0';	// <int> Not null

	public function initialize()  
	{
		$this->belongsTo("group_id", "PhanQuyen", "id");
	}

	public function getUser($id = null) {

		if ($id !== null) { return Users::findFirstById($id); }
	}

    //kiểm tra có tồn tại username đó chưa
	public function validation()
    {
        $validator = new Validation();
        
        $validator->add(
            'username',
            new UniquenessValidator([
            'message' => 'Tên Username Đã Tồn Tại'
        ]));
        
        return $this->validate($validator);
    }

    /*
	* Trước khi update/create
	*/
	public function beforeValidation () {

		$this->registed = date("Y-m-d H:i:s");
		$this->last_visited = date("Y-m-d H:i:s");

		//kiểm tra password nếu rỗng thì không update password, ngược lại thì hash password
		if (empty($this->password)) {
			
			$this->skipAttributes(array('password'));
		}
		else{

			$this->password = $this->getDI()->getSecurity()->hash($this->password);
		}
	}

	public function getThongTinRow() {
		
		$kq = $this->toArray();

		$kq['tenPhanQuyen'] = $this->PhanQuyen->name;
		
		return $kq;
	}

}