<?php 

use Phalcon\Mvc\Model\Criteria;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Mvc\View;

class DonhangdxlController extends ControllerBase
{
	
	public function initialize()
    {
        $this->tag->setTitle('ĐƠN HÀNG ĐANG XỬ LÝ');
        $this->assets->addJs('js/js-donhangdxl.js');
		$this->view->title = "QUẢN LÝ ĐƠN HÀNG";
    }

    private function _resetAll(){

        parent::_reset();

        $resetAll = $this->request->getPost('resetAll', "int");
        if ( !$this->persistent->params || $resetAll )
            $this->persistent->params   = array('order' => 'Orders.id DESC') + 
        	array( 'conditions' => 'trangThai IN ({trangThai:array})' ) +	
			array( 'bind' => ['trangThai' => ['choThanhToan','choXacNhan','conNo','khac'] ] );
    }

    public function indexAction()
    {
    	$this->_resetAll();

    	if ($this->request->getQuery('trangthai')) {
    		
    		$this->persistent->params = array( 'conditions' => 'trangThai IN ({trangThai:array})') +
				
			array( 'bind' => [ 'trangThai' => [ $this->request->getQuery('trangthai',['trim','striptags'])] ] )
			+ $this->persistent->params;
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

	//tìm kiếm, orderby, litmit
    public function ajaxSearchAction()
	{
		$this->_resetAll();

		//join tb
        $this->persistent->params = array(
                    'join'       => ['leftJoin', 'Customers']
                ) + $this->persistent->params;
		
       	if ( $this->request->getPost('filterRows') ){
			
			$this->persistent->filterRows = $this->request->getPost('filterRows', ['trim']);

			$this->persistent->params = 
			
				array( 'conditions' => '(idKhachHang LIKE :filterRows: OR trangThai LIKE :filterRows: OR congNo LIKE :filterRows: OR diaChiGiaoHang LIKE :filterRows: OR thongTinNguoiNhanHang LIKE :filterRows: OR Customers.tenKhachHang LIKE :filterRows: OR Orders.ghiChu LIKE :filterRows:) AND trangThai IN ({trangThai:array})') +
				
				array( 'bind' => ['filterRows' =>"%". $this->request->getPost('filterRows', ['trim']) ."%" , 'trangThai' => ['choThanhToan','choXacNhan','conNo','khac'] ] ) +
				
				$this->persistent->params;
        }

        if ( $this->request->getPost('orderBy') ) {

			$orderBy = 'Orders.'.$this->request->getPost('orderBy');

			//kiểm tra request orderby có chứa tenKhachHang
        	if (strpos($this->request->getPost('orderBy'), 'tenKhachHang') !== FALSE) {

        		$orderBy = 'Customers.'.$this->request->getPost('orderBy');
        	}
			
			$this->persistent->orderBy = $this->request->getPost('orderBy');
        	
        	$this->persistent->params = array( 'order' => $orderBy ) +
			$this->persistent->params;
        }
		
		if ($this->request->getPost('dateStart') && $this->request->getPost('dateEnd')) {

            //nâng cấp thêm giờ phút giây khi chọn ngày bắt đầu bằng ngày kết thúc
        	$this->persistent->params = 
            array( 'conditions' => 'Orders.ngay between :dateStart: AND :dateEnd:  AND trangThai IN ({trangThai:array})') +
                
            array( 'bind' => ['dateStart' => $this->request->getPost('dateStart'), 'dateEnd' => $this->request->getPost('dateEnd') , 'trangThai' => ['choThanhToan','choXacNhan','conNo','khac'] ]) + $this->persistent->params;

        }

		$paginator = new Phalcon\Paginator\Adapter\NativeArray( array(

			"data"  => Orders::getResult( $this->persistent->params, 'Orders' ),
			"limit" => $this->persistent->limit,
			"page"  => $this->persistent->currentPage
		));
		
		return ( json_encode( $paginator->getPaginate() ) );
	}

}