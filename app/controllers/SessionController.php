<?php 

use Phalcon\Mvc\Model\Criteria;
use Vokuro\Auth\Exception as AuthException;
use Phalcon\Mvc\View;

class SessionController extends ControllerBase
{
	public function initialize()
	{
		$this->tag->setTitle('Đăng Nhập');
	}

	//đăng nhập
	public function indexAction()
    {
    	//nếu đã đăng nhập (tồn tại session user)
    	if ($this->session->has("user")) {
    		return $this->response->redirect('index');
    	}

    	if ($this->request->isPost()) {
			
			if($this->security->checkToken()) {

				//truy vấn tên đăng nhập có tồn tại k và có được active
				$user = Users::findFirst(array(
					'conditions' =>'username = :username: AND blocked = :blocked:',
					'bind' => array('username' => $this->request->getPost('username'), 'blocked' => 1)
				));

				if ($user != false) {
					
					//kiểm tra password có đúng k
					if($this->security->checkHash($this->request->getPost('password'), $user->password))
					{
						//call function setSession
						$this->setSession($user);
						return $this->response->redirect('index/index');
					}

				}
				
				$this->flash->error('Tài Khoản Hoặc Mật khẩu Không Đúng.');	
			} else {
			
				$this->flash->error('Lỗi xác nhận phiên bảo mật.');
			}
        }

    	$form = new LoginForm();
		$this->view->form = $form;

		//k hiển thị view trong layout
		$this->view->disableLevel(
            View::LEVEL_MAIN_LAYOUT
        ); 
    }

    //set Session sau khi đăng nhập
    private function setSession(Users $user){

    	return $this->session->set('user', array(

    		'id' 				=> $user->id,
            'group_id' 			=> $user->group_id,
			'name' 				=> $user->name,
			'hanMucChiPhi'		=> $user->hanMucChiPhi,
			'permission'		=> $user->PhanQuyen->name,
			'arrayPermission'	=> $user->PhanQuyen->permissions
    	));
    }

    //Log out
    public function logOutAction()
    {
        $this->session->remove('user');

        return $this->response->redirect('session/index');
    }
}