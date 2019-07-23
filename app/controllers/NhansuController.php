<?php 

use Phalcon\Mvc\Model\Criteria;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Mvc\View;

class NhansuController extends ControllerBase
{	
	public function initialize()
    {
        $this->tag->setTitle('NHÂN SỰ');
        $this->assets->addJs('js/js-nhansu.js');
		$this->view->title = "QUẢN LÝ NHÂN SỰ";

		$this->view->duocXoa = $this->_duocXoa("NhanSu");
    }

    private function _resetAll(){

        parent::_reset();

        $resetAll = $this->request->getPost('resetAll', "int");
        if ( !$this->persistent->params || $resetAll )
            $this->persistent->params   = array('order' => 'id DESC');
    }

	public function indexAction()
	{
		$this->_resetAll();
		
		$form 	= new NhanSuForm();

        $this->_add($form);
		
		//gán các biến để sử dụng ngoài view
		$this->view->setVars([
			"activeTab"		=> $this->persistent->activeTab,
			"form" 			=> $form,
			"limit" 		=> $this->persistent->limit,
			"currentPage" 	=> $this->persistent->currentPage,
			"filterRows" 	=> $this->persistent->filterRows,
			"orderBy" 		=> $this->persistent->orderBy
		]);
	}

	private function _add($form) {

		if ($this->request->isPost()) {
			
			$nhansu = new NhanSu();
	        //gán các value request post vào các biến trong model NhanSu
	        $form->bind($_POST, $nhansu);
	        
	        $this->persistent->activeTab = "add"; // Khi đã vào form thì cho active tabs Form, Nếu thêm thành công thì mới active tabs search
	                    
	        if ( $form->isValid() ) {
	            
	            try{
	                if ( $nhansu->create() ) {

	                    $this->flash->success("Đăng Ký Thành Công");
	                    $this->persistent->activeTab = "search";
	                    $form->clear();
	                } else {
	                    $this->flash->error( $nhansu->getMessages() );
	                }
	                
	            } catch(Exception $e){
	                $this->flash->error($e->getMessage()); 
	            }  
	        } else {
	            $this->flash->error( $form->getMessages() );
	        }
	    }
    }

	//tìm kiếm, orderby, litmit
	public function ajaxSearchAction()
	{
		$this->_resetAll();
		
       	if ( $this->request->getPost('filterRows') ){
			
			$this->persistent->filterRows = $this->request->getPost('filterRows', ['trim']);

			$this->persistent->params = 
			
				array( 'conditions' => 'hoTen LIKE :filterRows: OR CMND LIKE :filterRows: OR SDT LIKE :filterRows: OR diaChi LIKE :filterRows: OR chucVu LIKE :filterRows: ') +
				
				array( 'bind' => ['filterRows' =>"%". $this->request->getPost('filterRows', ['trim']) ."%"] ) +
				
				$this->persistent->params;
        }

        if ( $this->request->getPost('orderBy') ) {
			
			$this->persistent->orderBy = $this->request->getPost('orderBy');
        	
        	$this->persistent->params = 
				array( 'order' => $this->request->getPost('orderBy') ) +
				$this->persistent->params;
        }

		$paginator = new Paginator(array(
			"data"  => NhanSu::find( $this->persistent->params ),
			"limit" => $this->persistent->limit,
			"page"  => $this->persistent->currentPage
		));

		$page			 = $paginator->getPaginate();
		$page->duocXoa   = $this->view->duocXoa;
		$page 			 = json_encode( $page );
		
		return ( $page );
	}

	public function editAction($id)
    {
        $form    = new NhanSuForm();
        
        try{
        	//get value nhan sự có id
            $nhansu = NhanSu::findFirstById($id);

            $this->_edit($form, $nhansu, "Cập nhật Nhân Sự thành công");
        } catch(Exception $e){

            $this->flashSession->error($e->getMessage());
        }
        
        $this->view->form   = $form;
		$this->view->title 	= "SỬA NHÂN SỰ";

        //k hiển thị view trong layout
        $this->view->disableLevel(
            View::LEVEL_LAYOUT
        );
    }

	public function deleteAction($id)
    {
    	$this->_delete($id,'NhanSu');
    }

}