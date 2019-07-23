<?php 

use Phalcon\Mvc\Model\Criteria;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Mvc\View;

class QuanlythuchiController extends ControllerBase
{
	
	public function initialize()
    {
        $this->tag->setTitle('QUẢN LÝ THU CHI');
        $this->assets->addJs('js/js-quanlythuchi.js');
		$this->view->title = "QUẢN LÝ THU CHI";
		
		$this->view->duocXoa = $this->_duocXoa("quanlythucthu");
    }

    private function _resetAll()
    {
        $this->persistent->paramsThucChi   		= array('order' => 'QuanLyThucChi.id DESC');
        $this->persistent->paramsThucThu   		= array('order' => 'QuanLyThucThu.id DESC');
        $this->persistent->limit            	= self::DEFAULT_LIMIT;
        $this->persistent->currentPageThucThu   = 1;
        $this->persistent->currentPageThucChi   = 1;
        $this->persistent->activeTab        	= ($this->persistent->activeTab) ? $this->persistent->activeTab : "thu";
        $this->persistent->filterRows       	= "";
    }

    private function _setupPersistent(){

    	$this->_resetAll();
		
		//gán activeTab nếu có avtivetabs được gởi lên
		if ($this->request->getQuery('activeTabs')) {
			
			$this->persistent->activeTab = $this->request->getQuery('activeTabs',['trim','string']);
		}

        //nếu click phân trang các tab còn ngược lại thì đưa params về mặc định
        if ($this->request->getQuery("pageThucThu", "int")) {

        	$this->persistent->currentPageThucThu 	= $this->request->getQuery("pageThucThu", "int");
        	$this->persistent->activeTab			= "thu";

        } elseif ($this->request->getQuery("pageThucChi", "int")) {

        	$this->persistent->currentPageThucChi	= $this->request->getQuery("pageThucChi", "int");
        	$this->persistent->activeTab 			= "chi";
        }
    }

    private function _setupPersistentNow()
    {
    	//nếu xem thực thu trong ngày
        if ($this->request->getQuery('todaythucthu')) {

    		$this->persistent->paramsThucThu = array( 'conditions' => 'QuanLyThucThu.ngay = :dateStart:') +
                
            array( 'bind' => ['dateStart' => $this->request->getQuery('todaythucthu',['trim','striptags']) ]);

            $this->persistent->activeTab = "thu";
    	}

    	//nếu xem thực chi trong ngày
    	if ($this->request->getQuery('todaythucchi')) {

    		$this->persistent->paramsThucChi = array( 'conditions' => 'ngay = :dateStart:') +
                
            array( 'bind' => ['dateStart' => $this->request->getQuery('todaythucchi',['trim','striptags']) ]);

            $this->persistent->activeTab = "chi";
    	}
    }

    //hiển thị dữ liệu của THU và CHI và thêm THU , CHI
	public function indexAction()
	{
		$formThucThu 		= new QuanLyThucThuForm();
		$formThucChi 		= new QuanLyThucChiForm();

		// Khởi tạo các biến toàn cục trong controller (Sửa dụng chung cho tât cả các action)
		$this->_setupPersistent();
        
        // gán biến persistent khi xem thu chi ngày cụ thể
        $this->_setupPersistentNow();
		
		//phân trang tab Thu
		$paginatorThucThu = new Paginator( array(
			"data"  => QuanLyThucThu::searchParams($this->persistent->paramsThucThu),
			"limit" => $this->persistent->limit,
			"page"  => $this->persistent->currentPageThucThu
		) );

		//phân trang tab Chi
		$paginatorThucChi = new Paginator( array(
			"data"  => QuanLyThucChi::searchParams($this->persistent->paramsThucChi),
			"limit" => $this->persistent->limit,
			"page"  => $this->persistent->currentPageThucChi
		) );

		//gán các biến để sử dụng ngoài view
		$this->view->setVars([
			"activeTab"		=> $this->persistent->activeTab,
			"pageThucThu"	=> $paginatorThucThu->getPaginate(),
			"pageThucChi"	=> $paginatorThucChi->getPaginate(),
			"formThucThu" 	=> $formThucThu,
			"formThucChi" 	=> $formThucChi,
			"filterRows" 	=> $this->persistent->filterRows,
			"limit" 		=> $this->persistent->limit
		]);
	}

	public function addThuAction()
	{
		$form 	= new QuanLyThucThuForm();
		$model	= new QuanLyThucThu();

		$this->persistent->activeTab = 'addThu';
		
		//gán các value request post vào các biến trong model
		$form->bind($_POST, $model);

		//chuẩn bị dữ liệu
		$data = new stdClass();
		$data->soTien 		= (double)str_replace(',','',$this->request->getPost('soTien',['trim','int']));
		$data->idKhachHang 	= $this->request->getPost('idKhachHang');
		$data->idNhaCungCap = $this->request->getPost('idNhaCungCap',['trim', 'int']);

		if ( $form->isValid() ) {
            
            //kiểm tra nếu chọn nguồn thu là bán hàng
			if ($data->idKhachHang) {

				if ( ($kq = $model->createThucThu($data)) !== true ) {
				
					$this->flashSession->error($kq);
					return $this->response->redirect('quanlythuchi/index');
				}
			}
			
			$model->soTien = $data->soTien;

            try{
                if ( $model->create() ) {
                	
                    $this->flashSession->success("Đăng Ký Thành Công");
        			$this->persistent->activeTab = 'thu';

					return $this->response->redirect('quanlythuchi/index');
                } else {

                    $this->flash->error( $model->getMessages() );
                }
                
            } catch(Exception $e){
                $this->flash->error($e->getMessage()); 
            }
        } else {
            $this->flash->error( $form->getMessages() );
        }

        return $this->dispatcher->forward(['action' => 'index']);
	}

	//thêm chi
	public function addChiAction()
	{
		$form 	= new QuanLyThucChiForm();
		$model	= new QuanLyThucChi(); 
		$this->persistent->activeTab = 'addChi';

		$form->bind($_POST, $model);

		//chuẩn bị dữ liệu
		$data = new stdClass();
		$data->soTien 		= (double)str_replace(',','',$this->request->getPost('soTien',['trim','int']));
		$data->nguoiNhan 	= $this->request->getPost('nguoiNhan',['trim']);

		if ( $form->isValid() ) {

			if ( $this->request->getPost('idNhaCungCap') )
				$model->nguoiNhan = $this->request->getPost('idNhaCungCap',['trim','int']);

			//call function ở dưới
			if ( $this->_updateTableKhac($data, $model->idDatHang, $model->idQuanLyChiPhi) === false ) {
				
				return $this->response->redirect('quanlythuchi/index');
			}

			$model->soTien 	= $data->soTien;

            try{
                if ( $model->create() ) {

                    $this->flashSession->success("Đăng Ký Thành Công"); 
                    $this->persistent->activeTab = 'chi';
                } else {
                    $this->flashSession->error( $model->getMessages() );
                }
                
            } catch(Exception $e){
                $this->flashSession->error($e->getMessage()); 
            }
        } else {
            $this->flashSession->error( $form->getMessages() );
        }

        return $this->response->redirect('quanlythuchi/index');
	}

	//dùng để update table khác khi addchi ( DatHang và QuanLyThucChi )
	private function _updateTableKhac($data, $idDatHang, $idQuanLyChiPhi)
	{
		//nếu nguồn chi là Sản Xuất
		if ($this->request->getPost('idDatHang')) {

			$kq = DatHang::updateCongNoWhenThucChi($idDatHang, $data->soTien);

		} elseif ($this->request->getPost('idQuanLyChiPhi')) {//nếu nguồn chi là chi phí

			$kq = QuanLyChiPhi::updateTrangThaiThucChi( $idQuanLyChiPhi, $data->soTien );
		}

		if ( isset($kq) && $kq !== true ) {
			$this->flashSession->error($kq);
			return false;
		}

		return true;
	}

	//edit QuanLyThucThu
	public function editThuAction($id)
	{
		$this->view->title 	= 'SỬA THU';
		$form 		= new QuanLyThucThuForm(null, ['edit' =>true]);
		//get value QuanLyThucThu có id
		$qltc 		= QuanLyThucThu::findFirstById($id);

		if (!$qltc ||  $qltc->ngay != date('Y-m-d') || $qltc->nguonThu == 'Chuyển Vốn') {
			
			$this->flashSession->warning('Quản Lý Thu Chi Không Được Phép Truy Cập');
        	return $this->response->redirect('quanlythuchi/index');
		}

		try{
			//đưa value của model vào form
        	$form->setEntity($qltc);

        	if ( $this->request->isPost() ) {

        		//lấy idKhachHang cũ nếu có
        		$idKhachHangCu = NULL;
        		if (!empty($qltc->idKhachHang))		
					$idKhachHangCu 	= $qltc->idKhachHang;

        		$form->bind($_POST, $qltc);
				
		        if ( $form->isValid() ) {

		        	//format đơn giá thành double và bỏ dấu ,
    				$soTien 		= (double)str_replace(',','',$this->request->getPost('soTien',['trim','int']));
    				$qltc->soTien 	= $soTien;

    				//nếu chọn nguonThu là bán hàng
    				if ($this->request->getPost('idKhachHang')) {

    					$idKhachHang = $this->request->getPost('idKhachHang');//lấy id khách hàng mới
    					
    					//cập nhập order của idKhachHang
						if( ($kq = $qltc->updateThucThu($id, $idKhachHang, $soTien)) !== true) {
							$this->flashSession->warning($kq);
							return $this->response->redirect('quanlythuchi/editThu/' . $id);
						}

						//nếu idKhachHangCu == idKhachHang thì không cập nhật order của idKhachHangCu
						$idKhachHangKhongDoi = ($idKhachHang == $idKhachHangCu) ? 1 : 0;
    				}

    				//cập nhật order của idKhachHangCu nếu có và khi idKhachHangCu != idKhachHang
    				if ( $idKhachHangCu !== NULL && !$idKhachHangKhongDoi ) {
    					
    					if( ($kq = $qltc->updateThucThu($id, $idKhachHangCu, 0)) !== true) {
							$this->flashSession->warning($kq);
							return $this->response->redirect('quanlythuchi/editThu/' . $id);
						}
    				}
					
					//update model
		        	if ($qltc->update()) {

						$this->flashSession->success("Quản Lý Thu Chi Cập Nhật Thành Công");
						$this->persistent->activeTab = "thu";
						
						return $this->response->redirect('quanlythuchi/index');
					}

					$this->flash->error( $qltc->getMessages() );
		        } else {
					$this->flash->error( $form->getMessages() );
		        }
		    }
		} catch(Exception $e){
			$this->flashSession->error($e->getMessage());
        }

        $this->view->qltc 	= $qltc;
		$this->view->form 	= $form;

		//k hiển thị view trong layout
        $this->view->disableLevel(
            View::LEVEL_LAYOUT
        );
	}

	//edit QuanLyThucChi
	public function editChiAction($id)
	{
		$this->view->title 	= 'SỬA CHI';
		$form 		= new QuanLyThucChiForm(null, ['edit' =>true]);
		//get value QuanLyThucChi có id
		$qltc 		= QuanLyThucChi::findFirstById($id);

		//những trường hợp không được vào edit
		if ( !$qltc || $qltc->ngay != date('Y-m-d') || $qltc->nhomChiPhi == 'Chuyển Vốn' || ( $qltc->nguonChi == 'Khác' && !$this->_duocXoa("quanlythuchi") ) ) {
			
			$this->flashSession->warning('Quản Lý Thu Chi Không Phép Truy Cập');
        	return $this->response->redirect('quanlythuchi/index');
		}

		try{
			//đưa value của model vào form
        	$form->setEntity($qltc);

        	if ( $this->request->isPost() ) {

        		//chuẩn bị dữ liệu
        		$data = new stdClass();
        		$data->soTienCu 		= $qltc->soTien;
        		$data->idDatHangCu 		= ($qltc->idDatHang) ? $qltc->idDatHang : NULL;
        		$data->idQuanLyChiPhiCu = ($qltc->idQuanLyChiPhi) ? $qltc->idQuanLyChiPhi : NULL;

        		$form->bind($_POST, $qltc);

        		if ( $form->isValid() ) {

		        	//format đơn giá thành double và bỏ dấu , và gán soTien bằng cái mới format
    				$data->soTienMoi = (double)str_replace(',','',$this->request->getPost('soTien',['trim','int']));
    				$qltc->soTien 	 = $data->soTienMoi;

    				//nếu mà tồn tại idNhaCungCap( chọn bên nhà cung cấp ) thì gán nó vào nguoiNhan
    				if ( $this->request->getPost('idNhaCungCap') )
						$qltc->nguoiNhan = $this->request->getPost('idNhaCungCap',['trim','int']);

					//nếu nguồn chi là Sản Xuất thì nhomChiPhi sẽ là rỗng
					if ( $qltc->nguonChi == 'Sản Xuất' )
						$qltc->nhomChiPhi = '';

					//call function
					if ( $this->_updateDatHangVaChiPhi($data, $qltc) === false )
						return $this->response->redirect("quanlythuchi/editChi/$id");

		        	if ($qltc->update()) {

						$this->flashSession->success("Quản Lý Thu Chi Cập Nhật Thành Công");
						$this->persistent->activeTab = "chi";
						
						return $this->response->redirect('quanlythuchi/index');
					}

					$this->flash->error( $qltc->getMessages() );
		        } else {
					$this->flash->error( $form->getMessages() );
		        }
		    }
		} catch(Exception $e){
			$this->flashSession->error($e->getMessage());
        }

        $this->view->qltc 	= $qltc;
		$this->view->form 	= $form;

		//k hiển thị view trong layout
        $this->view->disableLevel(
            View::LEVEL_LAYOUT
        );
	}

	private function _updateDatHangVaChiPhi($data, $qltc)
	{
		//nếu chọn nguonChi là Sản Xuất
		if ($this->request->getPost('idDatHang')) {
			
			if ($data->idDatHangCu == $this->request->getPost('idDatHang'))
				//dùng để cập nhất $data->soTienMoi sử dụng cho việc update CongNo cho DatHang có idDatHangCu
				$flash = 1;
			else
				//update CongNo cho DatHang đó
				$kq = DatHang::updateCongNoWhenThucChi($qltc->idDatHang, $data->soTienMoi, 0);
		}

		//nếu chọn nguonChi là Chi Phí
		if ($this->request->getPost('idQuanLyChiPhi'))
			$kq = QuanLyChiPhi::updateTrangThaiThucChi($qltc->idQuanLyChiPhi, $qltc->soTien);

		//nếu kq không đúng thì return
		if ( isset($kq) && $kq !== true ) {

			$this->flashSession->error($kq);
			return false;
		}

		//update congNo của DatHang với idDatHangCu
		if ($data->idDatHangCu !== NULL) {

			if (!$flash)
				$data->soTienMoi = 0;

			if (($kq = DatHang::updateCongNoWhenThucChi($data->idDatHangCu, $data->soTienMoi, $data->soTienCu)) !== true)
			{
				$this->flashSession->error($kq);
				return false;
			}
		}

		//update trangThai của ChiPhi với idChiPhiCu
		if ($data->idQuanLyChiPhiCu !== NULL) {
			
			$updateChiPhiCu = QuanLyChiPhi::findFirstById($data->idQuanLyChiPhiCu);
			$updateChiPhiCu->update(['trangThai' => '']);
		}

		return true;
	}

	//xóa thu chi
	public function deleteAction($tab,$id)
    {
    	$this->persistent->activeTab = $tab;
    	if ($tab == 'thu') {

    		$quanLyThuChi 	= QuanLyThucThu::findFirstById($id);
    		$data 			= $quanLyThuChi->idKhachHang;
    	} elseif ($tab == 'chi') {

    		$quanLyThuChi 	= QuanLyThucChi::findFirstById($id);
    		$data 			= $quanLyThuChi;
    	} else {

    		return $this->dispatcher->forward([ 'action' => 'index' ]);
    	}

        if ( !$quanLyThuChi ) {

            $this->flash->warning("Quản Lý Thu Chi Không Tồn Tại");
            return $this->dispatcher->forward([ 'action' => 'index' ]);
        }

        if ( ! $quanLyThuChi->delete() ) {

            $this->flash->error("Xóa Quản Lý Thu Chi Không Thành Công");
            return $this->dispatcher->forward([ 'action' => 'index' ]);  
        }

        //update công nợ Order hoặc DatHang hoặc QuanLyChiPhi khi xóa thu chi
        $quanLyThuChi->deleteThuChi($data);
        
        $this->flashSession->success("Xóa Quản Lý Thu Chi Thành Công");
        return $this->response->redirect('quanlythuchi/index');
    }

	//tìm kiếm, orderby, litmit
	public function ajaxSearchAction()
	{
		$thuchi = ($this->request->getPost('thuchi') == 'Chi') ? 'Chi' : 'Thu';

		$this->params($thuchi);
		
		if ( $this->request->getPost('resetAll') == 1 ) {
			
			$this->_resetAll();
		}

    	if ($thuchi == 'Chi') {

    		//join tb
        	$this->persistent->paramsThucChi = array(
                    'join'       => ['leftJoin', 'NhaCungCap','NganHang']
            ) + $this->persistent->paramsThucChi;

			$qltcArray = QuanLyThucChi::getResult( $this->persistent->paramsThucChi, 'QuanLyThucChi' );
    	} else {

    		//join tb
        	$this->persistent->paramsThucThu = array(
                    'join'       => ['leftJoin', 'Customers','NganHang','NhaCungCap']
            ) + $this->persistent->paramsThucThu;

    		$qltcArray = QuanLyThucThu::getResult( $this->persistent->paramsThucThu, 'QuanLyThucThu' );
    	}

		$paginator 	= new Phalcon\Paginator\Adapter\NativeArray(array(
			"data"  => $qltcArray,
			"limit" => $this->persistent->limit,
			"page"  => $this->persistent->currentPage,
		));
		
		$page			 = $paginator->getPaginate();
		$page->duocXoa   = $this->view->duocXoa;
		$page 			 = json_encode( $page );
		
		return ( $page );
	}

	public function params($thuchi)
	{
		//khi chọn limit
		if ( $this->request->getPost('limit') ){
			$this->persistent->limit = $this->request->getPost('limit');
        }
		
		//khi seach ô tìm kiếm
       	if ( $this->request->getPost('filterRows') ){

       		$this->persistent->filterRows = $this->request->getPost('filterRows', ['trim']);

       		if ($thuchi == 'Chi') {

       			$this->persistent->paramsThucChi = 
			
				array( 'conditions' => 'noiDung LIKE :filterRows: OR soTien LIKE :filterRows: OR nhomChiPhi LIKE :filterRows: OR chiTietChiPhi LIKE :filterRows: OR hinhThuc LIKE :filterRows: OR NganHang.tenNganHang LIKE :filterRows: OR NhaCungCap.tenNhaCungCap LIKE :filterRows:  OR QuanLyThucChi.nguoiNhan LIKE :filterRows:') +
				
				array( 'bind' => ['filterRows' =>"%". $this->request->getPost('filterRows', ['trim']) ."%"] ) +
				
				$this->persistent->paramsThucChi;
       		} else {

       			$this->persistent->paramsThucThu = 
			
				array( 'conditions' => 'noiDung LIKE :filterRows: OR soTien LIKE :filterRows: OR nguonThu LIKE :filterRows: OR hinhThuc LIKE :filterRows: OR NhaCungCap.tenNhaCungCap LIKE :filterRows: OR Customers.tenKhachHang LIKE :filterRows: OR NganHang.tenNganHang LIKE :filterRows: OR QuanLyThucThu.ghiChu LIKE :filterRows:') +
				
				array( 'bind' => ['filterRows' =>"%". $this->request->getPost('filterRows', ['trim']) ."%"] ) +
				
				$this->persistent->paramsThucThu;
       		}

        }

        //khi nhấn các column
        if ( $this->request->getPost('orderBy') ) {

        	$getPostOrderBy = $this->request->getPost('orderBy');

			if ($thuchi == 'Chi') {
				
				$orderByChi = 'QuanLyThucChi.'.$getPostOrderBy;

				//kiểm tra request orderby có chứa tenNhaCungCap
				if (strpos($getPostOrderBy, 'tenNhaCungCap') !== FALSE) {
        		
	        		$orderByChi = 'NhaCungCap.'.$getPostOrderBy;
	        	}

	        	$this->persistent->paramsThucChi = array( 'order' => $orderByChi ) +
				$this->persistent->paramsThucChi;

			} else {

				$orderByThu = 'QuanLyThucThu.'.$getPostOrderBy;

				//kiểm tra request orderby có chứa tenKhachHang
				if (strpos($getPostOrderBy, 'tenKhachHang') !== FALSE) {

	        		$orderByThu = 'Customers.'.$getPostOrderBy;
	        	}

	        	if (strpos($getPostOrderBy, 'tenNhaCungCap') !== FALSE) {
        		
	        		$orderByThu = 'NhaCungCap.'.$getPostOrderBy;
	        	}

	        	$this->persistent->paramsThucThu = array( 'order' => $orderByThu ) +
				$this->persistent->paramsThucThu;
			}
        	
        }

        //khi chọn ngày tháng
        if ($this->request->getPost('dateStart') && $this->request->getPost('dateEnd')) {

        	if ($thuchi == 'Chi') {

        		$this->persistent->paramsThucChi = 
	            array( 'conditions' => 'ngay between :dateStart: AND :dateEnd:') +
	                
	            array( 'bind' => ['dateStart' => $this->request->getPost('dateStart'), 'dateEnd' => $this->request->getPost('dateEnd')]) 
	            + $this->persistent->paramsThucChi;

        	} else {

        		$this->persistent->paramsThucThu = 
	            array( 'conditions' => 'QuanLyThucThu.ngay between :dateStart: AND :dateEnd:') +
	                
	            array( 'bind' => ['dateStart' => $this->request->getPost('dateStart'), 'dateEnd' => $this->request->getPost('dateEnd')]) 
	            + $this->persistent->paramsThucThu;
        	}
        }
	}

	//khi nhấn nút chuyển vốn
	public function chuyenVonAction()
	{
		$quanLyThucThu = new QuanLyThucThu();

		if ($this->request->getPost('chiChuyenVon') && $this->request->getPost('thuChuyenVon') && $this->request->getPost('soTien')) {

			//chuẩn bị dữ liệu
			$prepareData = new stdClass();

			$prepareData->chiChuyenVon 	= $this->request->getPost('chiChuyenVon', ['trim', 'striptags']);
			$prepareData->thuChuyenVon 	= $this->request->getPost('thuChuyenVon', ['trim', 'striptags']);

			$prepareData->nameChiChuyenVon = (NganHang::findFirstById( $prepareData->chiChuyenVon )) ? NganHang::findFirstById( $prepareData->chiChuyenVon )->tenNganHang : 'Tiền Mặt';

			$prepareData->nameThuChuyenVon = (NganHang::findFirstById( $prepareData->thuChuyenVon )) ? NganHang::findFirstById( $prepareData->thuChuyenVon )->tenNganHang : 'Tiền Mặt';

			$prepareData->soTien 		= (double)str_replace(',','',$this->request->getPost('soTien',['trim','int']));

			//tạo chuyển vốn thực thu/thực chi
			if ( ($kq = $quanLyThucThu->chuyenVon( $prepareData )) !== true)
				$this->flashSession->error($kq);
			else
				$this->flashSession->success("Chuyển Vốn Thành Công");

		} else {
			$this->flashSession->warning("Vui Lòng Nhập Đầy Đủ Thông Tin");
		}

		$this->persistent->activeTab = "chuyenVon";
		return $this->response->redirect("quanlythuchi/index");
	}

    //thống kê dữ liệu thực chi theo ngày( Ajax sử dụng )
    public function thongKeThucChiAction()
    {

    	$arrayNhomChiPhi = [

    		'Sản Xuất'		=> [ 'Bình'		=> 0, 'Motor'	=> 0, 'Đầu Nén'		=> 0,
    							 'Vật Tư'	=> 0, 'Khác'	=> 0, 'Tổng Cộng'	=> 0 ],

    		'Văn Phòng'		=> [ 'Tổng Cộng' => 0 	],

    		'Quản Lý'		=> [ 'Hóa Đơn'	=> 0, 'Khác'	=> 0, 'Tổng Cộng'	=> 0 ],

    		'Lương'			=> [ 'Tổng Cộng' => 0 ],

    		'Cá Nhân'		=> [ 'Tổng Cộng' => 0 ],

    		'Xưởng'			=> [ 'Thuê Xưởng'	=> 0, 'Nước'	=> 0, 'Điện'	=> 0,
    							 'Internet'		=> 0, 'Khác'	=> 0, 'Tổng Cộng'	=> 0 ],

    		'Chi Bán Hàng'	=> [ 'Tiền Xe'	=> 0, 'Hoa Hồng'	=> 0, 'Kiểm Định'	=> 0,
    							 'Tổng Cộng'	=> 0 ],

    		'Trả Nợ'		=> [ 'Ngân Hàng'	=> 0, 'Người Thân'	=> 0, 'Khác'	=> 0,
								 'Tổng Cộng'	=> 0 ],

    		'Chuyển Vốn'	=> [ 'Tổng Cộng'	=> 0 ],

    		'Vay Mượn'		=> [ 'Tổng Cộng'	=> 0 ],

    		'Khác'			=> [ 'Tổng Cộng'	=> 0 ],

    		'Tổng'			=> [ 'Tổng Cộng'	=> 0 ]
    	];


    	if( $this->request->getPost('dateStart') && $this->request->getPost('dateEnd') ) {

    		//get value QuanLyThucChi theo ngày
    		$data = QuanLyThucChi::find([
	    		'conditions'	=> 'ngay between :dateStart: AND :dateEnd:',
	    		'bind'	=> [
	    			'dateStart'	=> $this->request->getPost('dateStart') . " 00:00:00",
	    			'dateEnd' 	=> $this->request->getPost('dateEnd') . " 23:59:59",
	    		]
	    	])->toArray();

    		//sum value QuanLyThucChi theo ngày
	    	$sum = (int)QuanLyThucChi::sum([
	    		'column'		=> 'soTien',
	    		'conditions'	=> 'ngay between :dateStart: AND :dateEnd:',
	    		'bind'	=> [
	    			'dateStart'	=> $this->request->getPost('dateStart') . " 00:00:00",
	    			'dateEnd' 	=> $this->request->getPost('dateEnd') . " 23:59:59",
	    		]
	    	]);
    	
    		//gán sum vào mảng array
   			$arrayNhomChiPhi['Tổng']['Tổng Cộng'] = $sum;

   			//cộng tiền vào mảng array tương ứng với key
	    	foreach ($data as $key => $value) {

	    		if ( $value['chiTietChiPhi'] ) {

	    			$arrayNhomChiPhi[ $value['nhomChiPhi'] ][ $value['chiTietChiPhi'] ] += $value['soTien'];
	    		}

	    		$arrayNhomChiPhi[ $value['nhomChiPhi'] ]['Tổng Cộng'] += $value['soTien'];
	    	}
	    }

	    return json_encode($arrayNhomChiPhi);
    }

    //thống kê dữ liệu thu chi theo ngày ( Ajax sử dụng )
    public function thongKeDoanhThuAction()
    {
    	
    	$dateStart = $this->request->getPost('ngayStartCT');
    	$dateEnd   = $this->request->getPost('ngayEndCT');

    	//get id tất cả các ngân hàng
    	$nganHangVCBCN  = NganHang::find([

    		'columns' => 'id',
    		'conditions' => 'tenNganHang = :nganHang:',
    		'bind' => ['nganHang' => 'VCB (Cá Nhân)']
    	]);

    	$nganHangACCT  = NganHang::find([

    		'columns' => 'id',
    		'conditions' => 'tenNganHang = :nganHang:' ,
    		'bind' => ['nganHang' => 'Á Châu (Cty)']
    	]);

    	$nganHangACCN  = NganHang::find([

    		'columns' => 'id',
    		'conditions' => 'tenNganHang = :nganHang:',
    		'bind' => ['nganHang' => 'Á Châu (Cá Nhân)']
    	]);

    	$nganHangVICT  = NganHang::find([

    		'columns' => 'id',
    		'conditions' => 'tenNganHang = :nganHang:',
    		'bind' => ['nganHang' => 'Vietin (Cty)']
    	]);

    	$nganHangSCCT  = NganHang::find([

    		'columns' => 'id',
    		'conditions' => 'tenNganHang = :nganHang:',
    		'bind' => ['nganHang' => 'Sacom (Cty)']
    	]);

    	$nganHangKhac  = NganHang::find([

    		'columns' => 'id',
    		'conditions' => 'tenNganHang = :nganHang:',
    		'bind' => ['nganHang' => 'Khác']
    	]);

    	//kiểm tra có nguồn thu cụ thể hay là tất cả
    	if ($this->request->getPost('nguonThu')) {
    		
    		$valNguonThu 	= $this->request->getPost('nguonThu');
			$nhomChiPhi 	= $valNguonThu;

    		if ($valNguonThu == 'Bán Hàng') {
    			
    			$nhomChiPhi = 'Chi Bán Hàng';
    		}

    	} else {

    		$valNguonThu 	= '%';
			$nhomChiPhi 	= '%';
    	}

    	//call function và trả về dữ liệu
    	$tienMat 	= ['tienMat'=> QuanLyThucThu::tienMat( $dateStart , $dateEnd, $valNguonThu, $nhomChiPhi )];

		$vcb  		= ['vcb' 	=> QuanLyThucThu::tienNganHang( $nganHangVCBCN[0]->id, $dateStart, $dateEnd, $valNguonThu, $nhomChiPhi )];

    	$acct 		= ['acct' 	=> QuanLyThucThu::tienNganHang( $nganHangACCT[0]->id, $dateStart, $dateEnd, $valNguonThu, $nhomChiPhi )];

    	$accn 		= ['accn' 	=> QuanLyThucThu::tienNganHang( $nganHangACCN[0]->id, $dateStart, $dateEnd, $valNguonThu, $nhomChiPhi )];

    	$vict 		= ['vict' 	=> QuanLyThucThu::tienNganHang( $nganHangVICT[0]->id, $dateStart, $dateEnd, $valNguonThu, $nhomChiPhi )];

    	$scct 		= ['scct' 	=> QuanLyThucThu::tienNganHang( $nganHangSCCT[0]->id, $dateStart, $dateEnd, $valNguonThu, $nhomChiPhi )];
    	
    	$khac 		= ['khac' 	=> QuanLyThucThu::tienNganHang( $nganHangKhac[0]->id, $dateStart, $dateEnd, $valNguonThu, $nhomChiPhi )];

    	//gộp dữ liệu thành mảng
    	$arrayKetQua = array_merge($tienMat, $vcb, $acct, $accn, $vict, $scct, $khac);

		return json_encode($arrayKetQua);
    }

}