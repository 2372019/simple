<?php 

use Phalcon\Mvc\Model\Criteria;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Mvc\View;

class QuanlychiphiController extends ControllerBase
{
	
	public function initialize()
    {
        $this->tag->setTitle('QUẢN LÝ CHI PHÍ');
        $this->assets->addJs('js/js-quanlychiphi.js');
		$this->view->title = "QUẢN LÝ CHI PHÍ";
		
		$this->view->duocXoa = $this->_duocXoa("quanlychiphi");
    }

    private function _resetAll(){

        parent::_reset();

        $resetAll = $this->request->getPost('resetAll', "int");
        if ( !$this->persistent->params || $resetAll )
            $this->persistent->params   = array('order' => 'QuanLyChiPhi.id DESC');

        if ($this->request->getPost('duyetChi'))
            $this->persistent->params   += array('conditions' => 'idNguoiXacNhan <> ?0 AND trangThai != ?1', 'bind' => [0, 'hoanTat']);

    }

	public function indexAction()
	{
        $this->persistent->params   = array('order' => 'QuanLyChiPhi.id DESC');
        $this->_resetAll();

		$form 			= new QuanLyChiPhiForm();

        $this->_add($form);

        //gán các biến để sử dụng ngoài view
        $this->view->setVars([
            "activeTab"          => $this->persistent->activeTab,
            "form"               => $form,
            "limit"              => $this->persistent->limit,
            "currentPage"        => $this->persistent->currentPage,
            "filterRows"         => $this->persistent->filterRows,
            "orderBy"            => $this->persistent->orderBy
        ]);
	}

    private function _add($form) {

        if ($this->request->isPost()) {
            $qlcp = new QuanLyChiPhi();
            //gán các value request post vào các biến trong model QuanLyChiPhi
            $form->bind($_POST, $qlcp);
            
            $this->persistent->activeTab = "add"; // Khi đã vào form thì cho active tabs Form, Nếu thêm thành công thì mới active tabs search
                        
            if ( $form->isValid() ) {

                try{
                    if ( $qlcp->create() ) {

                        $this->flash->success("Đăng Ký Thành Công");
                        $this->persistent->activeTab = "search";
                        $form->clear();
                    } else {
                        $this->flash->error( $qlcp->getMessages() );
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

        $duyetChi = '';
        if ($this->request->getPost('duyetChi')) {
            $duyetChi = 'AND idNguoiXacNhan <> 0 AND trangThai <> '. "'hoanTat'";
        }

        //join tb
        $this->persistent->params = array(
            'join'       => ['leftJoin', 'Users']
        ) + $this->persistent->params;
		
       	if ( $this->request->getPost('filterRows') ){
			
			$this->persistent->filterRows = $this->request->getPost('filterRows', ['trim']);

			$this->persistent->params = 
			
				array( 'conditions' => '(chiChoAi LIKE :filterRows: OR soTienChi LIKE :filterRows: OR lyDoChi LIKE :filterRows: OR loaiChiPhi LIKE :filterRows:) '. $duyetChi .'') +
				
				array( 'bind' => ['filterRows' =>"%". $this->request->getPost('filterRows', ['trim']) ."%"] ) +
				
				$this->persistent->params;
        }

        if ( $this->request->getPost('orderBy') ) {
			
			$orderBy = 'QuanLyChiPhi.'.$this->request->getPost('orderBy');

            //kiểm tra request orderby có chứa type
            if (strpos($this->request->getPost('orderBy'), 'name') !== FALSE) {

                $orderBy = 'Users.'.$this->request->getPost('orderBy');
            }
            
            $this->persistent->orderBy = $this->request->getPost('orderBy');
            
            $this->persistent->params = array( 'order' => $orderBy ) +
            $this->persistent->params;
        }

        if ($this->request->getPost('dateStart') && $this->request->getPost('dateEnd')) {

            //nâng cấp thêm giờ phút giây khi chọn ngày bắt đầu bằng ngày kết thúc
        	$this->persistent->params = 
            array( 'conditions' => '(Ngay between :dateStart: AND :dateEnd:) '. $duyetChi .'') +
                
            array( 'bind' => ['dateStart' => $this->request->getPost('dateStart'), 'dateEnd' => $this->request->getPost('dateEnd')]) 
            + $this->persistent->params;

        }
		
		$paginator = new Phalcon\Paginator\Adapter\NativeArray(array(
			"data"  => QuanLyChiPhi::getResult( $this->persistent->params, 'QuanLyChiPhi' ),
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
        $form    = new QuanLyChiPhiForm(null, ['edit' =>true] );
        
        try{
            //get value QuanLyChiPhi có id
            $quanLyChiPhi = QuanLyChiPhi::findFirstById($id);

            if ($quanLyChiPhi->idNguoiXacNhan != 0) {
                
                $this->flashSession->warning("Không Được Sửa Vì Đã Duyệt Chi");
                return $this->response->redirect("quanlychiphi/index");
            }

            //set id người sửa
            $user = $this->getSessionUser();
            $quanLyChiPhi->idNguoiNhap = $user['id'];

            $this->_edit($form, $quanLyChiPhi, "Cập nhật Quản Lý Chi Phí thành công");
        } catch(Exception $e){

            $this->flashSession->error($e->getMessage());
        }
        
        $this->view->form   = $form;
		$this->view->title 	= "SỬA QUẢN LÝ CHI PHÍ";

        //k hiển thị view trong layout
        $this->view->disableLevel(
            View::LEVEL_LAYOUT
        );
    }

	public function deleteAction($id)
    {
    	$this->_delete($id,'QuanLyChiPhi');
    }

    public function ajaxDuyetChiAction()
    {
        $user = $this->getSessionUser();
            
        $quanlychiphi = QuanLyChiPhi::findFirstById($this->request->getPost('id', ['trim','int']));
        if ( $quanlychiphi ) {

            if ( $quanlychiphi->update( ['idNguoiXacNhan' => $user['id']] ) )
                return "Duyệt Chi Thành Công";
        }
        
        return "Duyệt Chi Không Thành Công";
    }

    public function listAction()
    {
        if ( $this->request->getQuery("page", "int") )

            $this->persistent->currentPage = $this->request->getQuery("page", "int");
        else
            //reset lại các biến session
            $this->_resetAll();

        $this->persistent->params = array(

            'conditions'    => 'idNguoiXacNhan <> ?0 AND trangThai != ?1',
            'bind'          => [ 0, 'hoanTat' ],
            'order'         => 'QuanLyChiPhi.id DESC',
        );
        
        $paginator = new Paginator( array(
            "data"  => QuanLyChiPhi::find( $this->persistent->params),
            "limit" => $this->persistent->limit,
            "page"  => $this->persistent->currentPage
        ));

        //gán các biến để sử dụng ngoài view
        $this->view->setVars([
            "page"          => $paginator->getPaginate(),
            "limit"         => $this->persistent->limit,
            "currentPage"   => $this->persistent->currentPage,
            "filterRows"    => $this->persistent->filterRows,
            "orderBy"       => $this->persistent->orderBy
        ]);
        
        //k hiển thị view trong layout và index
        $this->view->disableLevel([
            View::LEVEL_LAYOUT      => true,
            View::LEVEL_MAIN_LAYOUT => true,
        ]);
    }

}