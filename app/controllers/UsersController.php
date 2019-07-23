<?php 

use Phalcon\Mvc\Model\Criteria;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Mvc\View;

class UsersController extends ControllerBase
{

    public function initialize()
    {
        $this->tag->setTitle('USERS');
        $this->assets->addJs('js/js-users.js');
		$this->view->title = "QUẢN LÝ USERS";
    }

    private function _resetAll(){

        parent::_reset();

        $resetAll = $this->request->getPost('resetAll', "int");
        if ( !$this->persistent->params || $resetAll )
            $this->persistent->params   = array('order' => 'Users.id DESC');
    }

    //index users và add user và index phân quyền
    public function indexAction()
    {
        $this->_resetAll();

        $form = new UsersForm();
            
        $this->_add($form);

        //trả về kết quả truy vấn model PhanQuyen
        $phanQuyen  = PhanQuyen::find();
        
        //gán các biến để sử dụng ngoài view
        $this->view->setVars([
            "phanQuyen"     => $phanQuyen,
            "activeTab"     => $this->persistent->activeTab,
            "form"          => $form,
            "limit"         => $this->persistent->limit,
            "currentPage"   => $this->persistent->currentPage,
            "filterRows"    => $this->persistent->filterRows,
            "orderBy"       => $this->persistent->orderBy
        ]);

    }

    private function _add($form) {

        if ($this->request->isPost()) {
            $user = new Users();
            //gán các value request post vào các biến trong model Users
            $form->bind($_POST, $user);
            
            $this->persistent->activeTab = "add"; // Khi đã vào form thì cho active tabs Form, Nếu thêm thành công thì mới active tabs search

            //kiểm tra có nhập đúng phân quyền hay k
            if ($this->request->getPost('group_id')) {
            
                $group_id = PhanQuyen::findFirstById($this->request->getPost('group_id',['trim','int']));

                if (!$group_id) {
                    $this->flashSession->error("Vui Lòng Nhập Đúng Phân Quyền");
                    return $this->response->redirect('users/index');
                }
            }
                        
            if ( $form->isValid() ) {
                
                try{
                    if ( $user->create() ) {

                        $this->flash->success("Đăng Ký Thành Công");
                        $this->persistent->activeTab = "search";
                        $form->clear();
                    } else {
                        $this->flash->error( $user->getMessages() );
                    }
                    
                } catch(Exception $e){
                    $this->flash->error($e->getMessage()); 
                }
            } else {
                $this->flash->error( $form->getMessages() );
            }
        }
    }

    //tìm kiếm, orderby, litmit user
    public function ajaxSearchAction()
    {
        $this->_resetAll();

        //join tb
        $this->persistent->params = array(
            'join'       => ['leftJoin', 'PhanQuyen']
        ) + $this->persistent->params;
        
        if ( $this->request->getPost('filterRows') ){
            
            $this->persistent->filterRows = $this->request->getPost('filterRows', ['trim']);

            $this->persistent->params = 
            
                array( 'conditions' => 'PhanQuyen.name LIKE :filterRows: OR Users.name LIKE :filterRows: OR phone LIKE :filterRows: OR address LIKE :filterRows:') +
                
                array( 'bind' => ['filterRows' =>"%". $this->request->getPost('filterRows', ['trim']) ."%"] ) +
                
                $this->persistent->params;
        }

        if ( $this->request->getPost('orderBy') ) {
            
            $orderBy = 'Users.'.$this->request->getPost('orderBy');

            //kiểm tra request orderby có chứa type
            if (strpos($this->request->getPost('orderBy'), 'type') !== FALSE) {

                $orderBy = 'PhanQuyen.'.$this->request->getPost('orderBy');
            }
            
            $this->persistent->orderBy = $this->request->getPost('orderBy');
            
            $this->persistent->params = array( 'order' => $orderBy ) +
            $this->persistent->params;
        }
        
        $paginator = new Phalcon\Paginator\Adapter\NativeArray(array(
            "data"  => Users::getResult( $this->persistent->params, 'Users' ),
            "limit" => $this->persistent->limit,
            "page"  => $this->persistent->currentPage
        ));
        
        $page            = $paginator->getPaginate();
        $page            = json_encode( $page );
        
        return ( $page );
    }

    //edit user
    public function editAction($id)
    {
        $form    = new UsersForm(null, ['edit' =>true] );
        try{
            //get value user có id
            $user = Users::findFirstById($id);

            if ( !$user ) {
                $this->flashSession->warning("User Không Tồn Tại");
                return $this->response->redirect('users/index');
            }

            $user->password = '';
            //đưa value của user vào form
            $form->setEntity($user);
            
            if ( $this->request->isPost() ) {
                
                //gán các value request post vào các biến trong model User
                $form->bind($_POST, $user);

                //kiểm tra có nhập đúng phân quyền hay k
                if ($this->request->getPost('group_id')) {

                    if ( !PhanQuyen::findFirstById($this->request->getPost('group_id',['trim','int'])) ) {
                        
                        $this->flashSession->error("Vui Lòng Nhập Đúng Phân Quyền");
                        return $this->response->redirect('users/edit/'.$id);
                    }
                }

                //kiểm tra ràng buộc form và input username không bị sửa disaled trong HTML
                if ( $form->isValid() && empty($this->request->getPost('username')) ) {
                    if ($user->update()) {

                        $this->flashSession->success("User Cập Nhật Thành Công");
                        $this->persistent->activeTab = "search";
                        return $this->response->redirect('users/index');
                    }

                    $this->flash->error( $user->getMessages() );
                } else {
                    $this->flash->error( $form->getMessages() );
                }
            }
        } catch(Exception $e){
            $this->flashSession->error($e->getMessage());
        }
        
        $this->view->form   = $form;
        $this->view->users   = $user;
		$this->view->title 	= "SỬA USERS";

        //k hiển thị view trong layout
        $this->view->disableLevel(
            View::LEVEL_LAYOUT
        );
    }

    //delete user
    public function deleteAction($id)
    {
        $this->_delete($id, 'Users');
    }

    //add phân quyền
    public function addPhanQuyenAction()
    {
        if ($this->request->getPost()) {

           $phanquyen = new PhanQuyen([
                'name'          => $this->request->getPost('name',['trim','striptags']),
                'permissions'   => ($this->request->getPost('phanquyen')) ? json_encode(array_change_key_case($this->request->getPost('phanquyen'),CASE_LOWER)) : ''
           ]);

           try{
                if ($phanquyen->create()) {
                   
                   $this->flashSession->success("Đăng Ký Thành Công");
                   $this->persistent->activeTab = "phanquyen";

                   return $this->response->redirect('users/index');
                } else {
                    $this->flash->error($phanquyen->getMessages());
                }
            } catch(Exception $e){
                $this->flash->error($e->getMessage()); 
            }
   
        }

        //gán mảng privateResources(trong file config->privateResources.php )
        $this->view->resource = $this->aclResource->privateResources->toArray();
        //k hiển thị view trong layout
        $this->view->disableLevel(
            View::LEVEL_LAYOUT
        );
    }

    //edit phân quyền
    public function editPhanQuyenAction($id)
    {
        //get value PhanQuyen có id
        $phanquyen = PhanQuyen::findFirstById($id);

        if (!$phanquyen) {
            
            $this->flashSession->warning("Phân Quyền Không Tồn Tại");
            return $this->response->redirect('users/index');
        }

        if ($this->request->getPost()) {
            
            $phanquyen->name        = $this->request->getPost('name',['trim','striptags']);

            $phanquyen->permissions = '';
            if ($this->request->getPost('phanquyen')) {
                
                $phanquyen->permissions = json_encode(array_change_key_case($this->request->getPost('phanquyen'),CASE_LOWER));
            } 

            try{

                if ( $phanquyen->update() ) {
                    
                    $this->flashSession->success("Cập Nhật Thành Công");
                    $this->persistent->activeTab = "phanquyen";

                    return $this->response->redirect('users/index');
                } else {
                    $this->flash->error($phanquyen->getMessages());
                }
            } catch(Exception $e){
                $this->flash->error($e->getMessage()); 
            }
        }

        $this->view->phanquyen = $phanquyen;
        //gán mảng privateResources(trong file config->privateResources.php )
        $this->view->resource = $this->aclResource->privateResources->toArray();
        //k hiển thị view trong layout
        $this->view->disableLevel(
            View::LEVEL_LAYOUT
        );
    }

    //xóa phân quyền
    public function deletePhanQuyenAction($id)
    {
         $this->_delete($id, 'PhanQuyen');
    }
}