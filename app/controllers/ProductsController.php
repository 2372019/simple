<?php 

use Phalcon\Mvc\Model\Criteria;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Mvc\View;

class ProductsController extends ControllerBase
{
	public function initialize()
    {
        $this->tag->setTitle('SẢN PHẨM');
        $this->assets->addJs('js/js-products.js');
        //$this->assets->addJs('//cdn.ckeditor.com/4.11.4/standard/ckeditor.js');
        //$this->assets->addJs('ckfinder/ckfinder.js');
		$this->view->title = "QUẢN LÝ SẢN PHẨM";

		$this->view->duocXoa = $this->_duocXoa("Products");
    }

    private function _resetAll(){

        parent::_reset();

        $resetAll = $this->request->getPost('resetAll', "int");
        if ( !$this->persistent->params || $resetAll )
            $this->persistent->params   = array('order' => 'Products.id DESC');
    }
	
	public function indexAction()
	{
		$this->_resetAll();
		
		$form 			= new ProductsForm();

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

	private function _prepareData()
	{
		$data = new stdClass();

		$data->idMoi	 	= $this->request->getPost('idCtProductsList',['trim','int']);
		$data->idProducts 	= $this->request->getPost("idProducts", ['int', 'trim']);
		$data->soLuongList 	= $this->request->getPost("soLuong", ['int', 'trim']);
		$data->noiBat 		= $this->request->getPost("noiBat", ['int', 'trim']);

		return $data;
	}
	
	private function _add($form) {
		
		if ($this->request->isPost()) {
			$product = new Products();
			
			$form->bind($_POST, $product);
			$this->persistent->activeTab = "add"; // Khi đã vào form thì cho active tabs Form, Nếu thêm thành công thì mới active tabs search

			if ( $form->isValid() ) {

				if ( ($data = $this->_prepareData()) === false ) {
					$this->response->redirect('session/logout');
				}

				if ($product->createProducts($data) === false) {
					throw new Exception('Lỗi Không Thêm Được Sản Phẩm');
				}
				
				try{
					if ( $product->create() ) {

						$this->flash->success("Đăng Ký Thành Công");
						$this->persistent->activeTab = "search";
						$form->clear();
					} else {

						$this->flash->error( $product->getMessages() );
					}
				} catch(Exception $e){
					$this->flash->error($e->getMessage()); 
				}
			} else {
				
				$this->flash->error( $form->getMessages() );
			}
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
                    'join'       => ['leftJoin', 'LoaiSanPham']
        ) + $this->persistent->params;

       	if ( $this->request->getPost('filterRows') ){
			
			$this->persistent->filterRows = $this->request->getPost('filterRows', ['trim']);

			$this->persistent->params = 
			
				array( 'conditions' => 'tenSanPham LIKE :filterRows: OR moTa LIKE :filterRows: OR LoaiSanPham.type LIKE :filterRows: OR Products.maSanPham LIKE :filterRows:') +
				
				array( 'bind' => ['filterRows' =>"%". $this->request->getPost('filterRows', ['trim']) ."%"] ) +
				
				$this->persistent->params;
        }

        if ( $this->request->getPost('orderBy') ) {
			
			$orderBy = 'Products.'.$this->request->getPost('orderBy');

			//kiểm tra request orderby có chứa type
        	if (strpos($this->request->getPost('orderBy'), 'type') !== FALSE) {

        		$orderBy = 'LoaiSanPham.'.$this->request->getPost('orderBy');
        	}
        	//kiểm tra request orderby có chứa GT
        	if (strpos($this->request->getPost('orderBy'), 'gt') !== FALSE) {

        		$orderBy = 'Products.tonHienTai * Products.donGiaMuaVao'. 
        		str_replace('gt', '', $this->request->getPost('orderBy'));
        	}
			
			$this->persistent->orderBy = $this->request->getPost('orderBy');
        	
        	$this->persistent->params = array( 'order' => $orderBy ) +
			$this->persistent->params;
        }

        if ($this->request->getPost('noiBat')) {
        	
        	$this->persistent->params = 
			
				array( 'conditions' => 'noiBat = :filterRows:') +
				
				array( 'bind' => ['filterRows' =>$this->request->getPost('noiBat')] ) +
				
				$this->persistent->params;
        }
		
		if ( $this->request->getPost('resetAll') == 1 ) {
			//reset lại các biến session
			$this->_resetAll();
        }  else {

        	// Lấy số thứ tự trang (sử dụng cho list-products)
            $this->persistent->currentPage = ( $this->request->getPost("page") ) ? $this->request->getPost("page") : $this->persistent->currentPage;
        }
		
		$paginator = new Phalcon\Paginator\Adapter\NativeArray(array(
			"data"  => Products::getResult( $this->persistent->params, 'Products' ),
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
		$form 	 = new ProductsForm(null, ['edit' =>true] );
		
        try{
        	//get value product có id
            $product = Products::findFirstById($id);

            if (!$product) {
            
                $this->flashSession->warning("Sản Phẩm Không Tồn Tại");
                return $this->response->redirect('products/index');
            }

            //đưa value của product vào form
			$form->setEntity($product);
			
            if ( $this->request->isPost() ) {
				
				//gán các value request post vào các biến trong model Products
				$form->bind($_POST, $product);

                if ( $form->isValid() ) {
					
					if ( ($data = $this->_prepareData()) === false ) {
						$this->response->redirect('session/logout');
					}

					if ($product->createProducts($data) === false) {
						throw new Exception('Lỗi Không Thêm Được Sản Phẩm');
					}

					if ($product->update()) {

						$this->flashSession->success("Sản Phẩm Cập Nhật Thành Công");
						$this->persistent->activeTab = "search";
						
						return $this->response->redirect('products/index');
					}

					$this->flash->error( $product->getMessages() );
                    
                } else {
					$this->flash->error( $form->getMessages() );
                }
            }
        } catch(Exception $e){
			$this->flashSession->error($e->getMessage());
        }
		
		$this->view->ctproducts = $product->Ctproducts;
		$this->view->form 		= $form;
		$this->view->product 	= $product;
		$this->view->title 		= "SỬA SẢN PHẨM";

		//k hiển thị view trong layout
        $this->view->disableLevel(
            View::LEVEL_LAYOUT
        );
	}

	public function deleteAction($id)
    {
    	$this->_delete($id,'Products');
    }

    //dùng để hiển thị các products trong modal được sử dụng trong các trang khác nếu cần
    public function listAction()
    {
    	if ( $this->request->getQuery("page", "int") )
			$this->persistent->currentPage = $this->request->getQuery("page", "int");
		else
			//reset lại các biến session
			$this->_resetAll();
        
        $paginator = new Paginator(array(
            "data"  => Products::searchParams( $this->persistent->params),
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

	public function ajaxProductAction()
	{
		$product = Products::findFirstBymaSanPham($this->request->getPost('data'));
		$data = json_encode($product);
		return $data;
	}
	
	public function vatTuSanPhamAction(){
		
		$product 	= Products::findFirstBymaSanPham($this->request->getPost('data'));
		
		$ctproducts = $product->Ctproducts;
		$arrayVatTu = array();
		
		foreach ($ctproducts as $key => $value)
		{
			$arrayVatTu[] = [
				'id'			=> $value->Vattu->id,
				'maSanPham'		=> $value->Vattu->maSanPham,
				'tenSanPham'	=> $value->Vattu->tenSanPham,
				'soLuongVatTu'	=> $value->soLuongVatTu,
				'tonHienTai'	=> $value->Vattu->tonHienTai
			];
        }
		
		$data = json_encode($arrayVatTu);
		return $data;
	}
}