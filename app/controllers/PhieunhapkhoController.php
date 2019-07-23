<?php 

use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Mvc\View;

class PhieunhapkhoController extends ControllerBase
{
	public function initialize()
    {
        $this->tag->setTitle('Phiếu Nhập Kho');
        $this->assets->addJs('js/js-phieunhapkho.js');
		$this->view->title = "QUẢN LÝ NHẬP KHO";
		
		$this->view->duocXoa = $this->_duocXoa("PhieuNhapKho");
    }

    private function _resetAll(){

        parent::_reset();

        $resetAll = $this->request->getPost('resetAll', "int");
        if ( !$this->persistent->params || $resetAll )
            $this->persistent->params   = array('order' => 'PhieuNhapKho.id DESC');
    }

	public function indexAction()
	{
		$this->_resetAll();
		
		$form 	= new PhieuNhapKhoForm();
		
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

	// Trả về 1 danh sách dữ liệu dựa theo POST
	private function _prepareData()
	{	
		$idCtphieuNhapKhoMoi	= $this->request->getPost('idCtphieuNhapKho',['trim','int']);
		$idProductsList 		= $this->request->getPost('idProducts',['trim','int']);
		$soLuongList 			= $this->request->getPost('soLuong',['trim','int']);
		$donGiaList 			= str_replace(',', '', $this->request->getPost('donGia'));
		$ghiChuList 			= $this->request->getPost('ghiChuCtphieuNhapKho',['trim','striptags']);
		$coXuatHoaDonKhong 		= ($this->request->getPost("coXuatHoaDonKhong") != NULL) ? 1 : 0;

		//kiểm tra xem các resquest của sản phẩm có trùng gớp với nhau k và có sản phẩm nào k
		if (count($idProductsList) == 0 || count($idProductsList) != count($soLuongList) || count($donGiaList) != count($soLuongList)) {

			$this->flashSession->error('Chi tiết phiếu nhập trống hoặc ko hợp lệ');
			return false;	
		}

		$data	= array(
			'idCtphieuNhapKhoMoi'	=> $idCtphieuNhapKhoMoi,
			'idProductsList' 		=> $idProductsList,
			'soLuongList' 			=> $soLuongList,
			'donGiaList'			=> $donGiaList,
			'ghiChuList' 			=> $ghiChuList,
			'coXuatHoaDonKhong' 	=> $coXuatHoaDonKhong
		);
		
		return $data;
	}
	
	private function _add($form)
	{
		$model = new PhieuNhapKho();

		//gán các value request post vào các biến trong model
		$form->bind($_POST, $model);

		$this->persistent->activeTab = "add"; // Khi đã vào form thì cho active tabs Form, Nếu thêm thành công thì mới active tabs search

		if ( $form->isValid() ) {
			
			if ( ($data = $this->_prepareData()) === false )
				$this->response->redirect("phieunhapkho/index");

			if ( $model->createPhieuNhapKho($data) === false )
				throw new Exception('Lỗi Không Thêm Được Phiếu Nhập Kho');

			try{
				if ( $model->create() ) {

					$this->flashSession->success("Đăng Ký Thành Công");
					$this->persistent->activeTab = "search";
					$form->clear();
				} else {
					$this->flash->error( $model->getMessages() );
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

		//join tb
        $this->persistent->params = array(
                    'join'       => ['leftJoin', 'NhaCungCap', 'NhanSu']
                ) + $this->persistent->params;
		
       	if ( $this->request->getPost('filterRows') ){
			
			$this->persistent->filterRows = $this->request->getPost('filterRows', ['trim']);

			$this->persistent->params = 
			
				array( 'conditions' => 'noiDung LIKE :filterRows: OR lyDoNhap LIKE :filterRows: OR tongSoLuong LIKE :filterRows: OR NhaCungCap.tenNhaCungCap LIKE :filterRows: OR conNo LIKE :filterRows: OR tongThanhToan LIKE :filterRows: OR NhanSu.hoTen LIKE :filterRows: ') +
				
				array( 'bind' => ['filterRows' =>"%". $this->request->getPost('filterRows', ['trim']) ."%"] ) +
				
				$this->persistent->params;
        }

        if ( $this->request->getPost('orderBy') ) {
			
			$orderBy = 'PhieuNhapKho.'.$this->request->getPost('orderBy');

			//kiểm tra request orderby có chứa tenNhaCungCap
        	if (strpos($this->request->getPost('orderBy'), 'tenNhaCungCap') !== FALSE) {

        		$orderBy = 'NhaCungCap.'.$this->request->getPost('orderBy');

        	//kiểm tra request orderby có chứa hoTen
        	} elseif (strpos($this->request->getPost('orderBy'), 'hoTen') !== FALSE) {

        		$orderBy = 'NhanSu.'.$this->request->getPost('orderBy');
        	}
			
			$this->persistent->orderBy = $this->request->getPost('orderBy');
        	
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
			"data"  => PhieuNhapKho::getResult($this->persistent->params, 'PhieuNhapKho'),
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
		$form = new PhieuNhapKhoForm();

		try{
			$phieuNhapKho = PhieuNhapKho::findFirstById($id);
			
			if ( !$phieuNhapKho ) {

	    		$this->flashSession->warning("Phiếu Nhập Kho Không Tồn Tại");
	    		return $this->response->redirect("phieunhapkho/index");
	    	}
			
	    	if ( date_format(date_create($phieuNhapKho->ngay), "Y-m-d") != date("Y-m-d") || $phieuNhapKho->lyDoNhap == 'Thành Phẩm SX' ) {
	        	return $this->response->redirect('phieunhapkho/view/'.$id);
			}
	    	
	    	if ( $this->request->isPost() ) {
	    		
	    		//gán các value request post vào các biến trong model
	    		$form->bind( $_POST, $phieuNhapKho );

	    		if ( $form->isValid() ) {

	    			if ( $phieuNhapKho->update() ) {
						$this->flashSession->success("Cập nhật thành công.");
						$this->response->redirect("phieunhapkho/index");
					} else {
						$this->flashSession->success("Không thể cập nhật");
					}
				}
			} else 
				$form->setEntity( $phieuNhapKho );

		} catch(Exception $e) {
			$this->flashSession->error( $e->getMessages() );
		}

		$this->view->ctPhieuNhapKho 	= $phieuNhapKho->CtphieuNhapKho;
		$this->view->phieuNhapKho 		= $phieuNhapKho;
		$this->view->form 				= $form;
		$this->view->title 				= "SỬA PHIẾU NHẬP KHO";
		
		//k hiển thị view trong layout
        $this->view->disableLevel(
            View::LEVEL_LAYOUT
        );
	}

	public function viewAction($id)
	{
		$this->_view($id, "PhieuNhapKho");
		$this->view->ctPhieuNhapKho	= PhieuNhapKho::findFirstById($id)->CtphieuNhapKho;
	}

	//xóa value **note**
	public function deleteAction($id)
	{
    	$phieuNhapKho = PhieuNhapKho::findFirstById($id);

    	if ( !$phieuNhapKho ) {

    		$this->flashSession->warning("Phiếu Nhập Kho Không Tồn Tại");
    		return $this->response->redirect("phieunhapkho/index");
    	}

    	$ctPhieuNhapKho = $phieuNhapKho->CtphieuNhapKho;

		if ( $phieuNhapKho->delete() ) {

			//trừ lại số lượng tồn kho tương ứng với số lượng nhập của từng sản phẩm
			for ($i = 0; $i < count($ctPhieuNhapKho); $i++) {

				$product = $ctPhieuNhapKho[$i]->Products;

				if ( ($kq = $product->setTonHienTai( $product->tonHienTai - $ctPhieuNhapKho[$i]->soLuong )) !== true ) {
					
					$this->flashSession->warning($kq);
				}
			}

			$this->flashSession->success("Xóa Phiếu Nhập Kho Thành Công");
    	}
    	else{

			$this->flashSession->error("Xóa Phiếu Nhập Kho Không Thành Công");
    	}
    	
    	return $this->response->redirect("phieunhapkho/index");
    }

    //khi nhấn nút nhập kho trong edit đặt hàng
    public function datHangPhieuNhapKhoAction($id)
    {
    	$dathang 	= DatHang::findFirstById($id);

    	//kiểm tra dathang có tồn tại hoặc dathang đó có xuatKho = 1
    	if ( !$dathang || $dathang->nhapKho == 1 ) {
    		
    		$this->flashSession->warning("Đơn Hàng Đã Nhập Kho");
    		return $this->response->redirect("dathang/index");
    	}

    	$phieuNhapKho 	= new PhieuNhapKho();
    	$user 			= $this->getSessionUser();// **note**
    	
    	if ( $phieuNhapKho->createDatHangPhieuNhapKho( $dathang, $user) === false ) {
    		$this->flashSession->success("Lỗi Tạo Phiếu Nhập Kho");
			return $this->response->redirect('dathang/edit/'.$id);
    	}

    	if ( $phieuNhapKho->create() ) {

    		//chuẩn bị dữ liệu cập nhật dathang
	    	$dathang->nhapKho 	= 1;
	    	$dathang->idMuaVao	= (int)$phieuNhapKho->id;

	    	if ( $dathang->congNo <= 0 )
	    		$dathang->trangThai = 'hoanTat';

    		if ( $dathang->update() ) {
    			$this->flashSession->success("Tạo Phiếu Nhập Kho Thành Công");
    			return $this->response->redirect('dathang/view/'.$id);
    		}
    		$this->flashSession->success("Tạo Phiếu Nhập Kho Thành Công Nhưng Chưa Cập Nhật Nhập Kho Của Đơn Hàng");
    		return $this->response->redirect('dathang/index');
    	}

    	$this->flashSession->error("Tạo Phiếu Nhập Kho Không Thành Công");
		return $this->response->redirect('dathang/edit/'.$id);
    }

}