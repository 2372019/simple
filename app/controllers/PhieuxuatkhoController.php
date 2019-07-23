<?php 

use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Mvc\View;

class PhieuxuatkhoController extends ControllerBase
{
	
	public function initialize()
    {
        $this->tag->setTitle('Phiếu Xuất Kho');
        $this->assets->addJs('js/js-phieuxuatkho.js');
		$this->view->title = "QUẢN LÝ XUẤT KHO";
		
		// Thêm trường Được Xóa hay ko?
		$this->view->duocXoa = 0; // Mặc định là ko đc xóa
		
		$theUser = $this->view->user;
		if ( $this->acl->isAllowed( $theUser['permission'], "Orders", "delete" ) || $theUser['permission'] == 'Admin' ) {
			$this->view->duocXoa = 1;
		}
    }

    private function _resetAll(){

        parent::_reset();

        $resetAll = $this->request->getPost('resetAll', "int");
        if ( !$this->persistent->params || $resetAll )
            $this->persistent->params   = array('order' => 'PhieuXuatKho.id DESC');
    }
	
	public function indexAction()
	{
		$this->_resetAll();

		$form 	= new PhieuXuatKhoForm();

		if ($this->request->isPost()) {

			$this->_add($form);
		}

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

	private function _prepareData()
	{

		$idCtphieuXuatKhoMoi		= $this->request->getPost( 'idCtphieuXuatKho', ['trim','int'] );
		$idProductsList 			= $this->request->getPost( 'idProducts', ['trim','int'] );
		$soLuongList 				= $this->request->getPost( 'soLuong', ['trim','int'] );
		$ghiChuList 				= $this->request->getPost( 'ghiChuCtphieuXuatKho', ['trim','striptags'] );

		//kiểm tra xem các resquest của sản phẩm có trùng gớp với nhau k và có sản phẩm nào k
		if (count($idProductsList) == 0 || count($idProductsList) != count($soLuongList)) {

			$this->flashSession->error('Không Thể Thêm Phiếu Xuất Kho');
			return false;	
		}

		return [

			'idCtphieuXuatKhoMoi' 	=> $idCtphieuXuatKhoMoi,
			'idProductsList'		=> $idProductsList,
			'soLuongList'			=> $soLuongList,
			'ghiChuList'			=> $ghiChuList
		];
	}

	private function _add($form)
	{
		$phieuXuatKho = new PhieuXuatKho();

		//gán các value request post vào các biến trong model
		$form->bind($_POST, $phieuXuatKho);

		$this->persistent->activeTab = "add"; // Khi đã vào form thì cho active tabs Form, Nếu thêm thành công thì mới active tabs search

		if ( $form->isValid() ) {

			if ( ($data = $this->_prepareData()) === false )
				$this->response->redirect("phieuxuatkho/index");

			try{

				if ($phieuXuatKho->createPhieuXuatKho($data)) {

					$this->flash->success("Đăng Ký Thành Công");
					$this->persistent->activeTab = "search";
					$form->clear();
				}
			} catch(Exception $e) {
				$this->flash->error($e->getMessages());
			}			
		} else {				
			$this->flash->error( $form->getMessages() );
        }
	}

	//tìm kiếm, orderby, litmit
	public function ajaxSearchAction()
	{
		$this->_resetAll();
		
       	if ( $this->request->getPost('filterRows') ){
			
			$this->persistent->params = 
			
				array( 'conditions' => ' tenKH LIKE :filterRows: OR lyDoXuat LIKE :filterRows: OR nguoiNhan LIKE :filterRows: OR tongSoLuong LIKE :filterRows: ') +
				
				array( 'bind' => ['filterRows' =>"%". $this->request->getPost('filterRows', ['trim']) ."%"] ) +
				
				$this->persistent->params;
        }

        if ( $this->request->getPost('orderBy') ) {
			
			$orderBy = 'PhieuXuatKho.'.$this->request->getPost('orderBy');
			
        	$this->persistent->params = array( 'order' => $orderBy ) +
			$this->persistent->params;
        }

        if ($this->request->getPost('dateStart') && $this->request->getPost('dateEnd')) {

            //nâng cấp thêm giờ phút giây khi chọn ngày bắt đầu bằng ngày kết thúc
        	$this->persistent->params = 
            array( 'conditions' => 'ngay between :dateStart: AND :dateEnd:') +
                
            array( 'bind' => [
            	'dateStart' => $this->request->getPost('dateStart'),
            	'dateEnd' => $this->request->getPost('dateEnd')
            ]) + 
            $this->persistent->params;

        }

		$paginator = new Phalcon\Paginator\Adapter\NativeArray(array(
			"data"  => PhieuXuatKho::getResult( $this->persistent->params, 'PhieuXuatKho' ),
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
		$form = new PhieuXuatKhoForm(null, ['edit' =>true]);

		try{
			$phieuXuatKho = PhieuXuatKho::findFirstById($id);
			
			if ( !$phieuXuatKho ) {

	    		$this->flashSession->warning("Phiếu Xuất Kho Không Tồn Tại");
	    		return $this->response->redirect("phieuxuatkho/index");
	    	}
			
	    	if ( date_format(date_create($phieuXuatKho->ngay), "Y-m-d") != date("Y-m-d") || $phieuXuatKho->lyDoXuat == 'Thành Phẩm SX' ) {
	        	return $this->response->redirect('phieuxuatkho/view/'.$id);
			}
	    	
	    	if ( $this->request->isPost() ) {
	    		
	    		//gán các value request post vào các biến trong model
	    		$form->bind( $_POST, $phieuXuatKho );
				
	    		if ( $form->isValid() ) {

	    			if ( $phieuXuatKho->update() ) {
						$this->flashSession->success("Cập nhật thành công");
						return $this->response->redirect("phieuxuatkho/index");
					}
					
					$this->flashSession->success("Không thể cập nhật");
				}
			} else 
				$form->setEntity( $phieuXuatKho );

		} catch(Exception $e) {
			$this->flashSession->error( $e->getMessages() );
		}

		$this->view->ctPhieuXuatKho 	= $phieuXuatKho->CtphieuXuatKho;
		$this->view->phieuXuatKho 		= $phieuXuatKho;
		$this->view->form 				= $form;
		$this->view->title 				= "SỬA PHIẾU XUẤT KHO";
		
		//k hiển thị view trong layout
        $this->view->disableLevel(
            View::LEVEL_LAYOUT
        );
	}
	
	public function viewAction($id){
		
		$this->_view($id, "PhieuXuatKho");
		$this->view->ctPhieuXuatKho	= PhieuXuatKho::findFirstById($id)->CtphieuXuatKho;
	}

    //khi nhấn nút xác nhận ngoài view phiếu xuất kho
    public function xacNhanAction()
    {
    	if ($this->request->getPost('id')) {
    		
	    	$phieuXuatKho 	= PhieuXuatKho::findFirstById( $this->request->getPost('id') );

	    	if (!$phieuXuatKho || $phieuXuatKho->idNguoiXacNhan != 0) {

	    		$message 	= 'Lỗi';
	    	} else {

	    		$users = $this->getSessionUser();
	    		//nếu lyDoXuat là trả hàng thì chỉ có admin mới xác nhận được
	    		if ( $phieuXuatKho->lyDoXuat == 'Trả Hàng' && $users['permission'] != 'Admin') {
	    			return 'Cập Nhập Không Thành Công';
	    		}

		    	$phieuXuatKho->idNguoiXacNhan = $users['id'];

		    	if (!$phieuXuatKho->update()) {
		    		$message 	= 'Cập Nhập Không Thành Công';
		    	} else {
		    		$message = 'Cập Nhật Thành Công';
		    	}
	    	}

	    	return $message;
	    }
    }

    //khi nhấn nút xuất kho trong edit order
    public function orderPhieuXuatKhoAction($id)
    {
    	$order 		= Orders::findFirstById($id);

    	//kiểm tra order có tồn tại hoặc order đó có xuatKho = 1
    	if ( !$order || $order->xuatKho == 1 ) {
    		
    		$this->flashSession->warning("Đơn Hàng Đã Xuất Kho");
    		return $this->response->redirect("orders/index");
    	}

    	$phieuXuatKho 	= new PhieuXuatKho();
    	$user 			= $this->getSessionUser();// **note**
    	
    	if ( $phieuXuatKho->createOrderPhieuXuatKho( $order, $user) === false ) {
    		$this->flashSession->success("Lỗi Tạo Phiếu Xuất Kho");
			return $this->response->redirect('orders/edit/'.$id);
    	}

    	//chuẩn bị dữ liệu order
    	$order->xuatKho 	= 1;
		if( $order->congNo == 0 ) {
			$order->trangThai = 'hoanTat';
		}

    	if ( $phieuXuatKho->create() ) {

    		if ( $order->update() ) {
    			$this->flashSession->success("Tạo Phiếu Xuất Kho Thành Công");
    			return $this->response->redirect('phieuxuatkho/index');
    		}
    		$this->flashSession->success("Tạo Phiếu Xuất Kho Thành Công Nhưng Chưa Cập Nhật Xuất Kho");
    		return $this->response->redirect('orders/index');
    	}

    	$this->flashSession->error("Tạo Phiếu Xuất Kho Không Thành Công");
		return $this->response->redirect('orders/edit/'.$id);
    }

	
	//xóa value **note**
	public function deleteAction($id)
	{
    	$phieuXuatKho = PhieuXuatKho::findFirstById($id);
				
		if ( !$phieuXuatKho ) {
    		$this->flashSession->warning("Phiếu Xuất Kho Không Tồn Tại");
    		return $this->response->redirect("phieuxuatkho/index");
    	}
		
    	$ctPhieuXuatKho = $phieuXuatKho->CtphieuXuatKho;
		
		if ( $phieuXuatKho->delete() ) {

			//cộng lại số lượng tồn kho tương ứng với số lượng xuất của từng sản phẩm
			for ($i = 0; $i < count($ctPhieuXuatKho); $i++) {

				$product = $ctPhieuXuatKho[$i]->Products;

				if ( ($kq = $product->setTonHienTai( $product->tonHienTai + $ctPhieuXuatKho[$i]->soLuong )) !== true ) {
					
					$this->flashSession->warning($kq);
				}
			}
			$this->flashSession->success("Xóa Phiếu Xuất Kho Thành Công");
    	}
    	else{
			$this->flashSession->error("Xóa Phiếu Xuất Kho Không Thành Công");
    	}

    	return $this->response->redirect("phieuxuatkho/index");
    }

}