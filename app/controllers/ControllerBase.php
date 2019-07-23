<?php

use Phalcon\Mvc\Controller;
use Phalcon\Mvc\View;

class ControllerBase extends Controller
{
	const DEFAULT_LIMIT = 50;
    /**
    * Kiểm tra đăng nhập và có quyền được vào controller action đó không
    */
	protected function _reset()
    {
		$resetAll = $this->request->getPost('resetAll', "int");
		
		if ( $this->request->get('limit', "int") ) 
			$this->persistent->limit = $this->request->get('limit');
		else if ( !$this->persistent->limit || $resetAll )
			$this->persistent->limit = self::DEFAULT_LIMIT;
		
		if ( $this->request->get("page", "int") ) {
			$this->persistent->currentPage = $this->request->get('page');
			
			if ( $this->request->get('limit', "int") ) 
				$this->persistent->currentPage = 1;
		}
		else if ( !$this->persistent->currentPage || $resetAll )
			$this->persistent->currentPage = 1;
		
		if ( $this->request->get('filterRows') )
			$this->persistent->filterRows = $this->request->get('filterRows', ['trim', 'striptags']);
		else if ( !$this->persistent->filterRows || $resetAll )
			$this->persistent->filterRows = "";
		
		if ( $this->request->get('orderBy') )
			$this->persistent->orderBy = $this->request->get('orderBy');
		else if ( !$this->persistent->orderBy || $resetAll )
			$this->persistent->orderBy = "";
		
		if ( $this->request->get('activeTab') )
			$this->persistent->activeTab = $this->request->get('activeTab');
		else if ( !$this->persistent->activeTab || $resetAll )
			$this->persistent->activeTab = "search";
    }
	
	public function beforeExecuteRoute()
    {
    	$urlLogin = ['session/index'];
        $controllerName = $this->dispatcher->getControllerName();
        $actionName = $this->dispatcher->getActionName();

        $currentURL = $controllerName . "/" . $actionName;

        if( $this->session->has('user') ) {

            $users 				= $this->session->get('user');
            $this->view->user 	= $users;

            if ($this->acl->isPrivateResources($controllerName, $actionName)) {
            
                if ( !$this->acl->isAllowed( $users['permission'], $controllerName, $actionName ) && $users['permission'] != 'Admin' ) {

                    $this->flashSession->error("Bạn Không Có Quyền Vào Mục Đó");
                    $this->dispatcher->forward([
                        'controller' => 'index',
                        'action' => 'index'
                    ]);
                }
            }

        } elseif (!in_array($currentURL, $urlLogin)) { //nếu chưa đăng nhập và url hiện tại không phải là url đăng nhập
            
            $this->dispatcher->forward([
                'controller' => 'session',
                'action' => 'index'
            ]);
        }
    }

    //get session 
    public function getSessionUser(){

    	return $this->session->get('user');
    }
	
	//edit chung
	protected function _edit($form, $model, $success)
    {
		$controllerName = $this->dispatcher->getControllerName();

		if (!$model) {
		
			$this->flashSession->warning("Không tồn tại");
			return $this->response->redirect("$controllerName/index");
		}
		
		$form->setEntity($model);
		
		if ( $this->request->isPost() ) {
			
			//gán các value request post vào các biến trong model models
			$form->bind($_POST, $model);

			if ( $form->isValid() && $model->update() ) {

				$this->flashSession->success($success);
				$this->persistent->activeTab = "search";
				
				return $this->response->redirect("$controllerName/index");
				
			} else {
				$this->flash->error( $form->getMessages() );
			}
		}   
    }
	
	//xóa chung
	protected function _delete($id, $modelName)
    {
		$model = $modelName::findFirstById($id);
		
		$controllerName = $this->dispatcher->getControllerName();
		
    	if ( !$model ) {
    		$this->flashSession->warning("Không Tồn Tại mục cần xóa");

    	} else if ( !$model->delete() ) {
			
			$this->flashSession->error( $model->getMessages()  );
    	} else {
			
			$this->flashSession->success("Xóa Thành Công");
		}
		
		$this->persistent->activeTab = "search";
		return $this->response->redirect("$controllerName/index");
    }
	
	protected function _duocXoa($modelName) {
		
		$duocXoa = 0; // Mặc định ko đc xóa
		
		$theUser = $this->view->user;
		if ( $this->acl->isAllowed( $theUser['permission'], $modelName, "delete" ) || $theUser['permission'] == 'Admin' ) {
			$duocXoa = 1;
		}
		
		return $duocXoa;
	}
	
	//view chung
	protected function _view($id, $modelName){
		
		$model 			= $modelName::findFirstById($id);
		$controllerName = $this->dispatcher->getControllerName();

    	if ( !$model ) {

    		$this->flashSession->warning("Không Tồn Tại");
    		return $this->response->redirect("$controllerName/index");
    	}
    	
		$this->view->$modelName		= $model;
		
        $this->view->disableLevel(
            View::LEVEL_LAYOUT
        );
	}
}