<?php 

use Phalcon\Mvc\Model\Criteria;
use Phalcon\Paginator\Adapter\NativeArray as Paginator;
use Phalcon\Mvc\View;

class DathangController extends ControllerBase
{
	public function initialize()
    {
        $this->tag->setTitle('ĐẶT HÀNG');
        $this->assets->addJs('js/js-dathang.js');
		$this->view->title = "QUẢN LÝ ĐẶT HÀNG";
		
		$this->view->duocXoa = $this->_duocXoa("DatHang");
    }

    private function _resetAll(){

        parent::_reset();

        $resetAll = $this->request->getPost('resetAll', "int");
        if ( !$this->persistent->params || $resetAll )
            $this->persistent->params   = array('order' => 'DatHang.id DESC');

        if ($this->request->getPost('duyetChi')) {
			$this->persistent->params   += array('conditions' => 'idNguoiXacNhan <> ?0 AND trangThai <> '. "'hoanTat'",
				'bind' => [0]);
		}
    }
	
	public function indexAction()
	{
		$form = new DatHangForm();
		
		$this->persistent->params   = array('order' => 'DatHang.id DESC');
		$this->_resetAll();
		
		$this->persistent->activeTab = "search";
		
		$this->_add($form);

		//gán các biến để sử dụng ngoài view
		$this->view->setVars([
			"activeTab"		=> $this->persistent->activeTab,
			"form" 			=> $form,
			"nhaCungCap"	=> NhaCungCap::find(),
			"limit" 		=> $this->persistent->limit,
			"currentPage" 	=> $this->persistent->currentPage,
			"filterRows" 	=> $this->persistent->filterRows,
			"orderBy" 		=> $this->persistent->orderBy
		]);
	}

	private function _prepareData(){
		
		$data = new stdClass();
		
		$data->idMoi				= $this->request->getPost('idCtdatHang',['trim','int']);
		$data->idProductsList 		= $this->request->getPost('idProducts',['trim','int']);
		$data->donGiaMoiNhatList  	= str_replace(',','',$this->request->getPost('donGiaMoiNhat'));
		$data->soLuongList 			= $this->request->getPost('soLuong',['trim','int']);
		$data->ghiChuCtdatHangList 	= $this->request->getPost('ghiChuCtdatHang',['trim']);
		$data->coXuatHoaDonKhong	= $this->request->getPost('coXuatHoaDonKhong');		
		// Chặn nếu DatHang ko có chi tiết CtdatHang thì logout
		if ( 	count($data->idProductsList) 	== 0 || 
				count($data->idProductsList) 	!= count($data->donGiaMoiNhatList) ||
				count($data->donGiaMoiNhatList) != count($data->soLuongList) ) {

			return false;
		}
		
		return $data;
	}

	private function _add($form)
	{
		if ($this->request->isPost()) {
			$dathang = new DatHang();

			//gán các value request post vào các biến trong model
			$form->bind($_POST, $dathang);

			if ( !NhaCungCap::findFirstById( $dathang->idNhaCungCap ) ) {
				$this->flashSession->error("Nhà Cung Cấp Không Tồn Tại");
	    		return $this->response->redirect("dathang/index");
			}

			if ( $form->isValid() ) {
				
				if ( ($data = $this->_prepareData()) === false )
					$this->response->redirect('session/logout');
				
				try{
					if ( $dathang->createDatHang($data) === false )
						throw new Exception('Lỗi tạo đặt hàng');
					
					if ( $dathang->create() ) {
						
						$this->flashSession->success("Đăng Ký Thành Công");

						return $this->response->redirect("dathang/index");
						
					} else {
						$this->flash->error( $dathang->getMessages() );
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
            'join'       => ['leftJoin', 'NhaCungCap', 'Users']
        ) + $this->persistent->params;
		
       	if ( $this->request->getPost('filterRows') ){

       		$this->persistent->filterRows = $this->request->getPost('filterRows', ['trim']);
			
			$this->persistent->params = 
			
				array( 'conditions' => '(congNo LIKE :filterRows: OR NhaCungCap.tenNhaCungCap LIKE :filterRows: OR DatHang.ghiChu LIKE :filterRows: OR Users.name LIKE :filterRows:) '. $duyetChi .'') +
				
				array( 'bind' => ['filterRows' =>"%". $this->request->getPost('filterRows', ['trim']) ."%"] ) +
				
				$this->persistent->params;
        }

        if ( $this->request->getPost('orderBy') ) {

			$orderBy = 'DatHang.'.$this->request->getPost('orderBy');

			//kiểm tra request orderby có chứa tenNhaCungCap
        	if ( strpos($this->request->getPost('orderBy'), 'tenNhaCungCap') !== FALSE ) {

        		$orderBy = 'NhaCungCap.'.$this->request->getPost('orderBy');
        	}

        	if ( strpos($this->request->getPost('orderBy'), 'name') !== FALSE ) {

        		$orderBy = 'Users.'.$this->request->getPost('orderBy');
        	}
			
        	$this->persistent->params = array( 'order' => $orderBy ) +
			$this->persistent->params;
        }
		
		if ($this->request->getPost('dateStart') && $this->request->getPost('dateEnd')) {

            //nâng cấp thêm giờ phút giây khi chọn ngày bắt đầu bằng ngày kết thúc
        	$this->persistent->params = 
            array( 'conditions' => '(DatHang.ngay between :dateStart: AND :dateEnd:) '. $duyetChi .'') +
                
            array( 'bind' => ['dateStart' => $this->request->getPost('dateStart'), 'dateEnd' => $this->request->getPost('dateEnd')]) + $this->persistent->params;
        }
		
		$paginator = new Phalcon\Paginator\Adapter\NativeArray( array(

			"data"  => DatHang::getResult( $this->persistent->params, 'DatHang' ),
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
		$form 	= new DatHangForm();

        try{
        	//get value đơn hàng có id
            $dathang 	= DatHang::findFirstById($id);

            if ( !$dathang ) {
	    		$this->flashSession->warning("Đặt Hàng Không Tồn Tại");
	    		return $this->response->redirect("dathang/index");
	    	}

	    	if ($dathang->trangThai == 'hoanTat' || $dathang->nhapKho == 1)
	    		return $this->response->redirect("dathang/view/$id");
			
			// Đưa values của đơn hàng vào form
            $form->setEntity($dathang);
			
            if ( $this->request->isPost() ) {

				$form->bind($_POST, $dathang);

				//check tồn tại nhà cung cấp
				if ( !NhaCungCap::findFirstById( $dathang->idNhaCungCap ) ) {
					$this->flashSession->error("Nhà Cung Cấp Không Tồn Tại");
		    		return $this->response->redirect("dathang/index");
				}
				
				if ( $form->isValid() ) {
					
					if ( ( $data = $this->_prepareData() ) === false )
						$this->response->redirect('session/logout');
				
					if ( $dathang->createDatHang($data) === false )
						throw new Exception('Lỗi tạo đặt hàng');

					if ( $dathang->update() ) {
						$this->flashSession->success("Cập Nhật Thành Công");
						return $this->response->redirect('dathang/index');
					}
						
				} else {
					$this->flashSession->error( $form->getMessages() );
				}
            }
        } catch(Exception $e){
			$this->flashSession->error($e->getMessage());
        }

        $this->view->ctdathang 	= $dathang->CtdatHang;
		$this->view->dathang 	= $dathang;
		$this->view->nhaCungCap = NhaCungCap::find();
		$this->view->form 		= $form;
		$this->view->title 		= "SỬA ĐƠN HÀNG";
		
		//k hiển thị view trong layout
        $this->view->disableLevel(
            View::LEVEL_LAYOUT
        );
	}

	public function viewAction($id)
	{
		$form 	= new DatHangForm();

    	//get value đơn hàng có id
        $dathang 	= DatHang::findFirstById($id);

        if ( !$dathang ) {
    		$this->flashSession->warning("Đặt Hàng Không Tồn Tại");
    		return $this->response->redirect("dathang/index");
    	}

        $form->setEntity($dathang);

        if ($this->request->getPost()) {

        	if (!$dathang->update(['ghiChu' => $this->request->getPost('ghiChu', ['trim', 'striptags'])])) {

        		$this->flashSession->warning("Cập Nhật Không Thành Công");
    			return $this->response->redirect("dathang/view/$id");
        	} else {

        		$this->flashSession->success("Cập Nhật Thành Công");
    			return $this->response->redirect("dathang/index");
        	}
	    
        }

        $this->view->ctdathang 	= $dathang->CtdatHang;
		$this->view->dathang 	= $dathang;
		$this->view->nhaCungCap = NhaCungCap::find();
		$this->view->form 		= $form;
		$this->view->title 		= "SỬA ĐƠN HÀNG";
		
		//k hiển thị view trong layout
        $this->view->disableLevel(
            View::LEVEL_LAYOUT
        );
	}

	public function deleteAction($id)
	{
    	$this->_delete($id,'DatHang');
    }

    //khi nhấn nut duyệt chi
    public function ajaxDuyetChiAction()
    {
    	$user = $this->getSessionUser();
    	if ( $user['permission'] == 'Admin' ) {
    		
    		$dathang = DatHang::findFirstById($this->request->getPost('id', ['trim','int']));
    		if ( $dathang ) {

    			if ( $dathang->update( ['idNguoiXacNhan' => $user['id']] ) )
    				return "Duyệt Chi Thành Công";
    		}
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

			'conditions'	=> 'idNguoiXacNhan <> ?0 AND trangThai != ?1',
			'bind'			=> [0,'hoanTat'],
			'order' 		=> 'DatHang.id DESC',
			'join'       	=> ['leftJoin', 'NhaCungCap']
		);
        
        $paginator = new Paginator( array(
            "data"  => DatHang::getResult( $this->persistent->params, 'DatHang' ),
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

    //hủy trạng thái đã nhập kho
    public function huyPhieuNhapKhoAction($id)
    {
    	$dathang 	= DatHang::findFirstById($id);

    	//kiểm tra dathang có tồn tại hoặc dathang đó có xuatKho = 1
    	if ( !$dathang || !$this->_duocXoa("DatHang")) {
    		
    		$this->flashSession->warning("Mua Vào Không Tồn Tại");
    		return $this->response->redirect("dathang/index");
    	}

    	$phieuNhapKho = PhieuNhapKho::findFirstById($dathang->idMuaVao);

		//xóa phiếu nhập kho khi hủy thành công
    	if (!$dathang->update(['nhapKho' => 0, 'idMuaVao' => 0]))
    	{
    		$this->flashSession->warning("Hủy Nhập Kho Không Thành Công");
    	} else {

    		$ctPhieuNhapKho = $phieuNhapKho->CtphieuNhapKho;

			if ( $phieuNhapKho->delete() ) {

				//trừ lại số lượng tồn kho tương ứng với số lượng nhập của từng sản phẩm
				for ($i = 0; $i < count($ctPhieuNhapKho); $i++) {

					$product = $ctPhieuNhapKho[$i]->Products;

					if ( ($kq = $product->setTonHienTai( $product->tonHienTai - $ctPhieuNhapKho[$i]->soLuong )) !== true ) {
						
						$this->flashSession->warning($kq);
					}
				}

				$this->flashSession->success("Hủy Nhập Kho Thành Công");
	    	}
	    	else{

				$this->flashSession->error("Hủy Nhập Kho Không Thành Công");
	    	}
    	}

    	return $this->response->redirect("dathang/index");
    }
}