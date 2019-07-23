<?php 

use Phalcon\Mvc\Model\Criteria;
use Phalcon\Paginator\Adapter\NativeArray as Paginator;
use Phalcon\Mvc\View;

class CustomersController extends ControllerBase
{
    public function initialize()
    {
        $this->tag->setTitle('KHÁCH HÀNG');
        $this->assets->addJs('js/js-customers.js');
		$this->view->title = "QUẢN LÝ KHÁCH HÀNG";
		
		$this->view->duocXoa = $this->_duocXoa("Customers");
    }

    private function _resetAll(){

        parent::_reset();

        $resetAll = $this->request->getPost('resetAll', "int");
        if ( !$this->persistent->params || $resetAll )
            $this->persistent->params   = array('order' => 'Customers.id DESC');
    }
    
    //Index
    public function indexAction()
    {
        $this->persistent->params   = array('order' => 'Customers.id DESC');
        $this->_resetAll();

    	$form = new CustomersForm();

        $this->_add($form);
        
        //gán các biến để sử dụng ngoài view
        $this->view->setVars([
            "activeTab"     => $this->persistent->activeTab,
            "form"          => $form,
            "limit"         => $this->persistent->limit,
            "currentPage"   => $this->persistent->currentPage,
            "filterRows"    => $this->persistent->filterRows,
            "orderBy"       => $this->persistent->orderBy
        ]);

    }

    private function _add($form) {

        if ($this->request->isPost()) {

            $customer = new Customers();
            //gán các value request post vào các biến trong model Customers
            $form->bind($_POST, $customer);
            
            $this->persistent->activeTab = "add"; // Khi đã vào form thì cho active tabs Form, Nếu thêm thành công thì mới active tabs search
                        
            if ( $form->isValid() ) {
                
                try{
                    if ( $customer->create() ) {

                        $this->flash->success("Đăng Ký Thành Công");
                        $this->persistent->activeTab = "search";
                        $form->clear();
                    } else {
                        $this->flash->error( $customer->getMessages() );
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

        //join tb
        $this->persistent->params = array(
            'join'      => ['leftJoin', 'Orders'],
            'groupBy'   => 'Customers.tenKhachHang'
        ) + $this->persistent->params;
        
        if ( $this->request->getPost('filterRows') ){
            
            $this->persistent->filterRows = $this->request->getPost('filterRows', ['trim']);

            $this->persistent->params = 
            
                array( 'conditions' => 'tenKhachHang LIKE :filterRows: OR mST LIKE :filterRows: OR diaChi LIKE :filterRows: OR soDienThoai LIKE :filterRows: OR nguoiMuaHang LIKE :filterRows: OR Customers.ghiChu LIKE :filterRows:') +
                
                array( 'bind' => ['filterRows' =>"%". $this->request->getPost('filterRows', ['trim']) ."%"] ) +
                
                $this->persistent->params;
        }

        if ( $this->request->getPost('orderBy') ) {
            
            $orderBy = 'Customers.'.$this->request->getPost('orderBy');

            //kiểm tra request orderby có chứa congNo
            if (strpos($this->request->getPost('orderBy'), 'congNo') !== FALSE) {

                $orderBy = 'SUM(Orders.congNo) '. str_replace('congNo', '', $this->request->getPost('orderBy'));

                $this->persistent->params = array(
                    'groupBy'       => 'Customers.tenKhachHang',
                ) + $this->persistent->params;
            }

            //kiểm tra request orderby có chứa soDonHang
            if (strpos($this->request->getPost('orderBy'), 'soDonHang') !== FALSE) {

                $orderBy = 'COUNT(Orders.id)'. str_replace('soDonHang', '', $this->request->getPost('orderBy'));
            }
            
            $this->persistent->params = array( 'order' => $orderBy ) +
            $this->persistent->params;
        }

        if ($this->request->getPost('dateStart') && $this->request->getPost('dateEnd')) {

            //nâng cấp thêm giờ phút giây khi chọn ngày bắt đầu bằng ngày kết thúc
        	$this->persistent->params = 
            array( 'conditions' => 'Customers.ngay between :dateStart: AND :dateEnd:') +
                
            array( 'bind' => ['dateStart' => $this->request->getPost('dateStart'), 'dateEnd' => $this->request->getPost('dateEnd')]) 
            + $this->persistent->params;

        }
        
        if ( $this->request->getPost('resetAll') == 1 ) {
            
            //reset lại các biến session
            $this->_resetAll();
        }  else {

            // Lấy số thứ tự trang (sử dụng cho list-customer)
            $this->persistent->currentPage = ( $this->request->getPost("page") ) ? $this->request->getPost("page") : $this->persistent->currentPage;
        }
   
        $paginator = new Phalcon\Paginator\Adapter\NativeArray(array(
            "data"  => Customers::getResult( $this->persistent->params, 'Customers' ),
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
        $form    			= new CustomersForm(null, ['edit' =>true] );
        try{
            //get value khách hàng có id
            $customer = Customers::findFirstById($id);
			
			$this->_edit($form, $customer, "Cập nhật khách hàng thành công");

        } catch(Exception $e){
            $this->flashSession->error($e->getMessage());
        }
		
		$this->view->form   = $form;
		$this->view->title    = "SỬA KHÁCH HÀNG";
		//k hiển thị view trong layout
        $this->view->disableLevel(
            View::LEVEL_LAYOUT
        );
    }

    public function deleteAction($id)
    {
    	$this->_delete($id,'Customers');
    }
	
    //dùng để hiển thị các customers trong modal được sử dụng trong các trang khác nếu cần
    public function listAction()
    {
		if ( $this->request->getQuery("page", "int") )

			$this->persistent->currentPage = $this->request->getQuery("page", "int");
		else
            //reset lại các biến session
			$this->_resetAll();
        
        $paginator = new Paginator( array(
            "data"  => Customers::getResult( $this->persistent->params, 'Customers' ),
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