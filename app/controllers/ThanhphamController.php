<?php 

use Phalcon\Mvc\Model\Criteria;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Mvc\View;

class ThanhphamController extends ControllerBase
{
	public function initialize()
    {
        $this->tag->setTitle('THÀNH PHẨM');
        $this->assets->addJs('js/js-thanhpham.js');
		$this->view->title = "QUẢN LÝ THÀNH PHẨM";

		$this->view->duocXoa = $this->_duocXoa("ThanhPham");
    }

    private function _resetAll(){

        parent::_reset();

        $resetAll = $this->request->getPost('resetAll', "int");
        if ( !$this->persistent->params || $resetAll )
            $this->persistent->params   = array('order' => 'ThanhPham.id DESC');
    }
	
	public function indexAction()
	{
		$this->_resetAll();

		// Xử lý cho cái Form
		if ($this->request->isPost()) {

			$this->_add();
    	}
		
		//gán các biến để sử dụng ngoài view
		$this->view->setVars([
			"activeTab"		=> $this->persistent->activeTab,
			"limit" 		=> $this->persistent->limit,
			"currentPage" 	=> $this->persistent->currentPage,
			"filterRows" 	=> $this->persistent->filterRows,
			"orderBy" 		=> $this->persistent->orderBy	
		]);
	}
	
	public function editAction($id){
		
		$this->response->redirect('thanhpham/view/' . $id);
	}
	
	public function viewAction($id){
		
		$thanhPham = ThanhPham::findFirstById($id);
		
		$phieuXuatKho 				= PhieuXuatKho::findFirstById($thanhPham->idPhieuXuat);

		$this->view->ctthanhPham 	= $thanhPham->CtthanhPham;
		$this->view->thanhPham 		= $thanhPham;
		$this->view->ctPhieuXuatKho = $phieuXuatKho->CtphieuXuatKho;
		$this->view->title 			= "SỬA THÀNH PHẨM";
		
		//k hiển thị view trong layout
        $this->view->disableLevel(
            View::LEVEL_LAYOUT
        );
	}
	
	private function _add()
	{
		$user		= $this->getSessionUser();
		$this->persistent->activeTab = "search"; // Khi đã vào form thì cho active tabs Form, Nếu thêm thành công thì mới active tabs search

		try{
			$sanPham = Products::findFirstBymaSanPham( $this->request->getPost('maThanhPham', ['trim','striptags']) );

			if ( !$sanPham ) {
				$this->flashSession->warning("Mã Sản Phẩm không tồn tại");
				return $this->response->redirect("thanhpham/index");
			}
			//chuẩn bị dữ liệu
			$data = new stdClass();

			$data->idSanPham 		= $sanPham->id;
			$data->soLuongPhatSinh 	= $this->request->getPost('soLuongPhatSinh', ['trim','int']);
			$data->soLuongThanhPham = $this->request->getPost('soLuongThanhPham', ['trim','int']);
			$data->ghiChuVatTu 		= $this->request->getPost('ghiChuCtthanhPham', ['trim','striptags']);
			$data->idMaVatTu		= $this->request->getPost('idMaVatTu', ['trim','int']);
			$data->ctProducts 		= $sanPham->ctProducts->toArray();
			
			if ( count($data->idMaVatTu) < 1 ) {
				
				$this->flashSession->warning("Không Tồn Tại Vật Tư Để Thêm");
				return $this->response->redirect("thanhpham/index");
			}

			$thanhPham = new ThanhPham();

			if ( $thanhPham->createThanhPham($data, $user) === false ) {
				
				throw new Exception('Lỗi Tạo Được Thành Phẩm');
			}

			if ( $thanhPham->create() ) {
				
				$idPhieuNhapKho = $this->phieuNhapKho( $thanhPham->id);
				$idPhieuXuatKho = $this->phieuXuatKho( $thanhPham->id, $thanhPham->idProducts );

				$thanhPham->idPhieuNhap = $idPhieuNhapKho;
				$thanhPham->idPhieuXuat = $idPhieuXuatKho;

				$thanhPham->update();

				$this->flash->success("Đăng Ký Thành Công");

			} else {						
				$this->flash->error( $thanhPham->getMessages() );
			}

		} catch(Exception $e){
			$this->flash->error($e->getMessage()); 
		}
	}

	private function phieuNhapKho( $idThanhPham )
	{
		$thanhpham 		= ThanhPham::findFirstById($idThanhPham);
		$user 	 		= $this->getSessionUser();
		$phieuNhapKho 	= new PhieuNhapKho();

		$phieuNhapKho->createPhieuNhapKhoForThanhPham($thanhpham, $user);

		//kiểm tra model ctPhieuNhapKho có rỗng k
		if ( !$phieuNhapKho->create() ) {
			$this->flash->error('Lỗi Thêm Phiếu Nhập Kho');	
		}

		return $phieuNhapKho->id;
	}

	private function phieuXuatKho( $idThanhPham, $idProducts )
	{
		$ctthanhpham 	= CtthanhPham::find("idThanhPham = $idThanhPham");
		$sanPham 		= Products::findFirstById( $idProducts );
		$user 	 		= $this->getSessionUser();

		$phieuXuatKho 	= new PhieuXuatKho();

		$data = [
			'tenSanPham' 	=> $sanPham->tenSanPham,
			'user'			=> $user['id']
		];

		if ( $phieuXuatKho->createPhieuXuatKhoForThanhPham( $ctthanhpham, $data ) === false ) {
			$this->flash->error('Không Thể Thêm Phiếu Xuất Kho');

		} else {
			$phieuXuatKho->create();
			return $phieuXuatKho->id;
		}
	}
	
	/**
    * tìm kiếm, orderby, litmit
    */
    public function ajaxSearchAction()
    {
		$this->_resetAll();

		//join tb
        $this->persistent->params = array(
            'join'       => ['leftJoin', 'Products']
        ) + $this->persistent->params;
		
       	if ( $this->request->getPost('filterRows') ){
			
			$this->persistent->filterRows = $this->request->getPost('filterRows', ['trim']);

			$this->persistent->params = 
			
				array( 'conditions' => 'soLuongThanhPham LIKE :filterRows: OR Products.tenSanPham LIKE :filterRows: OR tongSoLuongVatTu LIKE :filterRows:') +
				
				array( 'bind' => ['filterRows' =>"%". $this->request->getPost('filterRows', ['trim']) ."%"] ) +
				
				$this->persistent->params;
        }

        if ( $this->request->getPost('orderBy') ) {
			
			$orderBy = 'ThanhPham.'.$this->request->getPost('orderBy');

			//kiểm tra request orderby có chứa type
        	if (strpos($this->request->getPost('orderBy'), 'tenSanPham') !== FALSE) {

        		$orderBy = 'Products.'.$this->request->getPost('orderBy');
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
			"data"  => ThanhPham::getResult( $this->persistent->params, 'ThanhPham' ),
			"limit" => $this->persistent->limit,
			"page"  => $this->persistent->currentPage
		));
		
		$page			 = $paginator->getPaginate();
		$page->duocXoa   = $this->view->duocXoa;
		$page 			 = json_encode( $page );
		
		return ( $page );
    }
}