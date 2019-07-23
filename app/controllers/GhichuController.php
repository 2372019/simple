<?php 

use Phalcon\Mvc\Model\Criteria;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Mvc\View;

class GhichuController extends ControllerBase
{
	//private $user = $this->getSessionUser();

	public function initialize()
    {
        $this->tag->setTitle('Ghi Chú');
        $this->assets->addJs('js/js-ghichu.js');
		$this->view->title = "QUẢN LÝ GHI CHÚ";
		
		$this->view->duocXoa = $this->_duocXoa("GhiChu");
    }

    private function _resetAll(){

        parent::_reset();

        $user = $this->getSessionUser();

        $resetAll = $this->request->getPost('resetAll', "int");
        if ( !$this->persistent->params || $resetAll )
        {
        	$this->persistent->params   = array('order' => 'id DESC');

        	if ($user['permission'] != 'Admin')
        		$this->persistent->params  += array( 'conditions' => 'cheDo = "Public" OR ( idNguoiNhap = :idNguoiNhap: AND cheDo = "Private")' ) + array( 'bind' => ['idNguoiNhap' => $user['id'] ] );
        }
    }
	
	public function indexAction()
	{

		$form = new GhiChuForm();
		
		$this->_resetAll();
		
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

	private function _add($form)
	{
		if ($this->request->isPost()) {
			
			$ghiChu = new GhiChu();
	        //gán các value request post vào các biến trong model NhanSu
	        $form->bind($_POST, $ghiChu);
	        
	        $this->persistent->activeTab = "add"; // Khi đã vào form thì cho active tabs Form, Nếu thêm thành công thì mới active tabs search
	                    
	        if ( $form->isValid() ) {
	            
	            try{
	                if ( $ghiChu->create() ) {

	                    $this->flash->success("Đăng Ký Thành Công");
	                    $this->persistent->activeTab = "search";
	                    $form->clear();
	                } else {
	                    $this->flash->error( $ghiChu->getMessages() );
	                }
	                
	            } catch(Exception $e){
	                $this->flash->error($e->getMessage()); 
	            }  
	        } else {
	            $this->flash->error( $form->getMessages() );
	        }
	    }
	}

	public function ajaxSearchAction()
	{
		$this->_resetAll();
		$user = $this->getSessionUser();

		$arrayConditions = ['conditions' => ''];

		if ( $user['permission'] != 'Admin' ) {

			$arrayConditions = [
				'conditions' 	=> "AND cheDo = 'Public' OR (idNguoiNhap ='" . $user["id"]. "' AND cheDo = 'Private')",
			];
		}
		
       	if ( $this->request->getPost('filterRows') ){

       		$this->persistent->filterRows = $this->request->getPost('filterRows', ['trim']);
			
			$this->persistent->params = 
			
				array( 'conditions' => '(tieuDe LIKE :filterRows: OR noiDung LIKE :filterRows: OR trangThai LIKE :filterRows:) '. $arrayConditions["conditions"] .'') +
				
				array( 'bind' => ['filterRows'	=> "%". $this->request->getPost('filterRows', ['trim']) ."%"] ) 
				+ $this->persistent->params;
        }

        if ( $this->request->getPost('orderBy') ) {

			$orderBy = 'GhiChu.'.$this->request->getPost('orderBy');
			
        	$this->persistent->params = array( 'order' => $orderBy ) +
			$this->persistent->params;
        }
		
		if ($this->request->getPost('dateStart') && $this->request->getPost('dateEnd')) {

            //nâng cấp thêm giờ phút giây khi chọn ngày bắt đầu bằng ngày kết thúc
        	$this->persistent->params = 
            array( 'conditions' => '(ngay between :dateStart: AND :dateEnd:) '. $arrayConditions["conditions"] .' ') +
                
            array( 'bind' => ['dateStart' => $this->request->getPost('dateStart'), 'dateEnd' => $this->request->getPost('dateEnd') ]) + $this->persistent->params;
        }
		
		$paginator = new Paginator( array(

			"data"  => GhiChu::find( $this->persistent->params ),
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
		$form    = new GhiChuForm();
        
        try{
        	//get value nhan sự có id
            $ghiChu = GhiChu::findFirstById($id);
            $user = $this->getSessionUser();

            if ($ghiChu->trangThai != 'hoanTat') {//nếu trang thai hoàn tất thì k update được
            	
	            //nếu ở chế độ private thì chỉ người thêm mới vào được trừ Admin
	            if ( $ghiChu->cheDo == 'Private' && $user['id'] != $ghiChu->idNguoiNhap && $user['permission'] != 'Admin') {
	            	
	            	$this->flashSession->warning("Bạn Không Thể Xem Ghi Chú Này");
	            	return $this->response->redirect("ghichu/index");
	            }

	            $this->_edit($form, $ghiChu, "Cập nhật Ghi Chú thành công");
	        } else {

	        	$form->setEntity($ghiChu);
	        }
        } catch(Exception $e){

            $this->flashSession->error($e->getMessage());
        }
        
        $this->view->form   = $form;
        $this->view->ghiChu = $ghiChu;
		$this->view->title 	= "SỬA GHI CHÚ";

        //k hiển thị view trong layout
        $this->view->disableLevel(
            View::LEVEL_LAYOUT
        );
	}

	public function deleteAction($id)
    {
    	$this->_delete($id,'GhiChu');
    }

    //cập nhật trạng thái
    public function ajaxTrangThaiAction()
    {
    	$ghichu = GhiChu::findFirstById($this->request->getPost("id", ['trim','int']));

    	if ( $ghichu && $ghichu->trangThai != 'hoanTat') {
    		
    		if(($kq = $ghichu->updateTrangThai( $this->request->getPost( "trangThai", ['trim','striptags'] ))) !== true)
				return $kq;

			return "Cập Nhật Thành Công";
    	} else {
    		return "Không tồn tại ghi chú";
    	}
    }
}