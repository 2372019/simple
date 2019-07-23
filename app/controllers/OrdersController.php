<?php 

use Phalcon\Mvc\Model\Criteria;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Mvc\View;

class OrdersController extends ControllerBase
{
	public function initialize()
    {
        $this->tag->setTitle('ĐƠN HÀNG');
        $this->assets->addJs('js/js-orders.js');
		$this->view->title = "QUẢN LÝ ĐƠN HÀNG";
		
		$this->view->duocXoa = $this->_duocXoa("Orders");
    }

    private function _resetAll(){

        parent::_reset();

        $resetAll = $this->request->getPost('resetAll', "int");
        if ( !$this->persistent->params || $resetAll )
            $this->persistent->params   = array(
            	'conditions' => 'trangThai NOT IN ({trangThai:array})',
            	'bind'		 => ['trangThai' => ['huySauBH','huyTruocBH','khac']],
            	'order' => 'Orders.id DESC'
            );
    }
	
	public function indexAction()
	{
		$form = new OrdersForm();
		
		$this->_resetAll();
		
		$this->persistent->activeTab = "search";
		
		if ($this->request->getQuery('today')) {
    		
    		$today = $this->request->getQuery('today',['trim','striptags']);

    		$this->persistent->params = array( 'conditions' => 'Orders.ngay between :dateStart: AND :dateEnd:') +
                
            array( 'bind' => ['dateStart' => $today.' 00:00:01', 'dateEnd' => $today.' 23:59:59']);
    	}
		
		$this->_addFromForm($form);

		
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
	
	// Xử lý riêng cho Form
	private function _addFromForm($form)
	{
		if ($this->request->isPost()) {
			
			$order = new Orders();

			$this->persistent->activeTab = "add";

			//gán các value request post vào các biến trong model Orders
			$form->bind($_POST, $order);

			if ( $form->isValid() ) {
				
				if ( ($data = $this->_prepareDate()) === false )
					$this->response->redirect('session/logout');
				
				try{
					if ( $order->createOrders($data) === false )
						throw new Exception('Lỗi tạo đơn hàng');
					
					if ( $order->create() ) {
						
						$this->flashSession->success("Đăng Ký Thành Công");
						$this->_resetAll();
						$form->clear();

						return $this->response->redirect("orders/edit/".$order->id);
						
					} else {
						$this->flash->error( $order->getMessages() );
					}
				} catch(Exception $e){
					$this->flash->error($e->getMessage()); 
				}
            } else {
				$this->flash->error( $form->getMessages() );
            }
    	}
	}
	
	/*
	* Hàm này chuẩn bị dữ liệu cho cả thêm mới và update Orders
	* Đầu vào: Dữ liệu $_POST 
	* Trả về mảng $data
	*/
	private function _prepareDate(){
		
		$data = new stdClass();
		
		$data->idMoi			= $this->request->getPost('idCtorders',['trim','int']);
		$data->idProductsList 	= $this->request->getPost('idProducts',['trim','int']);
		$data->donGiaMoiNhatList  = str_replace(',','',$this->request->getPost('donGiaMoiNhat'));
		$data->soLuongList 		= $this->request->getPost('soLuong',['trim','int']);
		$data->ghiChuCtordersList = $this->request->getPost('ghiChuCtorders',['trim']);			
		// Chặn nếu Orders ko có chi tiết Ctorders thì logout
		if ( 	count($data->idProductsList) 	== 0 || 
				count($data->idProductsList) 	!= count($data->donGiaMoiNhatList) ||
				count($data->donGiaMoiNhatList) != count($data->soLuongList) ) {

			return false;
		}
		
		$data->coXuatHoaDonKhong	= $this->request->getPost('coXuatHoaDonKhong');
		
		return $data;
	}

	public function editAction($id)
	{
		$form 	= new OrdersForm();

        try{
        	//get value đơn hàng có id
            $order 	= Orders::findFirstById($id);

            if ( !$order ) {
	    		$this->flashSession->warning("Đơn Hàng Không Tồn Tại");
	    		return $this->response->redirect("orders/index");
	    	}

	    	// Nếu đơn hàng có trạng thái là hoàn tất hoặc hủy thì chuyển qua Edit
	    	if ( in_array($order->trangThai, array('hoanTat', 'huySauBH', 'huyTruocBH') )) {
				return $this->response->redirect('orders/view/'.$id);
        	}
			
			// Đưa values của đơn hàng vào form
            $form->setEntity($order);
			
            if ( $this->request->isPost() ) {

				$form->bind($_POST, $order);
				
				if ( $form->isValid() ) {
					
					if ( ( $data = $this->_prepareDate() ) === false )
						$this->response->redirect('session/logout');
				
					if ( $order->createOrders($data) === false )
						throw new Exception('Lỗi tạo đơn hàng');

					if ( $order->update() ) {
						$this->flashSession->success("Cập Nhật Thành Công");
						return $this->response->redirect('orders/index');
					}
						
				} else {
					$this->flashSession->error( $form->getMessages() );
				}
            }
        } catch(Exception $e){
			$this->flashSession->error($e->getMessage());
        }

        $this->view->ctorders 	= $order->Ctorders;
		$this->view->order 		= $order;
		$this->view->form 		= $form;
		$this->view->title 		= "SỬA ĐƠN HÀNG";
		
		//k hiển thị view trong layout
        $this->view->disableLevel(
            View::LEVEL_LAYOUT
        );
	}

	//tìm kiếm, orderby, litmit
	public function ajaxSearchAction()
	{
		$this->_resetAll();

		//join tb
        $this->persistent->params = array(
                    'join'       => ['leftJoin', 'NhanSu', 'Customers']
                ) + $this->persistent->params;
		
       	if ( $this->request->getPost('filterRows') ){
			
			$this->persistent->params = 
			
				array( 'conditions' => 'trangThai LIKE :filterRows: OR congNo LIKE :filterRows: OR diaChiGiaoHang LIKE :filterRows: OR thongTinNguoiNhanHang LIKE :filterRows: OR Customers.tenKhachHang LIKE :filterRows: OR NhanSu.hoTen LIKE :filterRows: OR Orders.ghiChu LIKE :filterRows:') +
				
				array( 'bind' => ['filterRows' =>"%". $this->request->getPost('filterRows', ['trim']) ."%"] ) +
				
				$this->persistent->params;
        }

        if ( $this->request->getPost('orderBy') ) {

			$orderBy = 'Orders.'.$this->request->getPost('orderBy');

			//kiểm tra request orderby có chứa tenKhachHang
        	if ( strpos($this->request->getPost('orderBy'), 'tenKhachHang') !== FALSE ) {

        		$orderBy = 'Customers.'.$this->request->getPost('orderBy');
        	}
			
        	$this->persistent->params = array( 'order' => $orderBy ) +
			$this->persistent->params;
        }
		
		if ($this->request->getPost('dateStart') && $this->request->getPost('dateEnd')) {

            //nâng cấp thêm giờ phút giây khi chọn ngày bắt đầu bằng ngày kết thúc
        	$this->persistent->params = 
            array( 'conditions' => 'Orders.ngay between :dateStart: AND :dateEnd:') +
                
            array( 'bind' => ['dateStart' => $this->request->getPost('dateStart'), 'dateEnd' => $this->request->getPost('dateEnd')]) + $this->persistent->params;
        }
		
		$paginator = new Phalcon\Paginator\Adapter\NativeArray( array(

			"data"  => Orders::getResult( $this->persistent->params, 'Orders' ),
			"limit" => $this->persistent->limit,
			"page"  => $this->persistent->currentPage
		));
		
		$page			 = $paginator->getPaginate();
		$page->duocXoa   = $this->view->duocXoa;
		$page 			 = json_encode( $page );
		
		return ( $page );
	}
	
	public function deleteAction($id)
	{
    	$this->_delete($id,'Orders');
    }

    //chuyển số tiền thành chữ
    public function phieuThuAjaxAction()
    {
    	$helps = new Helps();
		$convertNum = $helps->convertNumberToWords($this->request->getPost('data'));
		return ucfirst($convertNum);
    }

    // Cập nhật trạng thái ngoài view **note**
    public function ajaxTrangThaiAction()
    {
    	$order = Orders::findFirstById($this->request->getPost("id", ['trim','int']));

    	if ( $order ) {
    		
    		$kq = $order->updateTrangThai(
	    		$this->request->getPost( "trangThai", ['trim','striptags'] ), 
				$this->session->get('user')['permission'] 
			);

			return $kq->message;
    	} else {
    		return "Không tồn tại đơn hàng";
    	}
    }

    public function viewAction($id)
    {
    	$form  = new OrdersForm();
    	$order = Orders::findFirstById($id);


    	if ( !$order ) {
    		$this->flashSession->warning("Đơn Hàng Không Tồn Tại");
    		return $this->response->redirect("orders/index");
    	}

    	$form->setEntity($order);

    	if ($this->request->getPost()) {

    		$order->ghiChu 			= $this->request->getPost("ghiChu", ['trim','striptags']);
    		$order->idEmployees 	= $this->request->getPost("idEmployees", ['trim','int']);
    		$order->chiPhiGiaoHang 	= $this->request->getPost("chiPhiGiaoHang", ['trim','int']);
    		$order->ngayXuatHoaDon  = $this->request->getPost("ngayXuatHoaDon");
    		$order->soHoaDon  		= $this->request->getPost("soHoaDon");

    		$order->update();

			$this->flashSession->success("Đơn Hàng Cập Nhật Thành Công");
				
			return $this->response->redirect('orders/index');
    	}

    	$this->view->ctorders 	= $order->Ctorders;
    	$this->view->form 		= $form;
		$this->view->order 		= $order;
		
        $this->view->disableLevel(
            View::LEVEL_LAYOUT
        );
    }

}