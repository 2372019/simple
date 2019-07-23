<?php 

use Phalcon\Mvc\Model\Criteria;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Mvc\View;

class NhacungcapController extends ControllerBase
{	
	public function initialize()
    {
        $this->tag->setTitle('Nhà Cung Cấp');
        $this->assets->addJs('js/js-nhacungcap.js');
		$this->view->title = "QUẢN LÝ NHÀ CUNG CẤP";

        $this->view->duocXoa = $this->_duocXoa("NhaCungCap");
    }

    private function _resetAll(){

        parent::_reset();

        $resetAll = $this->request->getPost('resetAll', "int");
        if ( !$this->persistent->params || $resetAll )
            $this->persistent->params   = array('order' => 'NhaCungCap.id DESC');
    }

    public function indexAction()
    {
        $this->persistent->params   = array('order' => 'NhaCungCap.id DESC');
        $this->_resetAll();
        
    	$form 			= new NhaCungCapForm();
            
        $this->_add($form);
		
		$this->view->setVars([
			"activeTab"		=> $this->persistent->activeTab,
			"form" 			=> $form,
			"limit" 		=> $this->persistent->limit,
			"currentPage" 	=> $this->persistent->currentPage,
			"filterRows" 	=> $this->persistent->filterRows,
			"orderBy" 		=> $this->persistent->orderBy
		]);
    }

    private function _add($form) {

        if ($this->request->isPost()) {
            
            $nhaCungCap = new NhaCungCap();
            $form->bind($_POST, $nhaCungCap);
                        
            if ( $form->isValid() ) {
                
                try{
                    if ( $nhaCungCap->create() ) {

                        $this->flash->success("Đăng Ký Thành Công");
                        $this->persistent->activeTab = "search";
                        $form->clear();
                    } else {
                        $this->flash->error( $nhaCungCap->getMessages() );
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

        //join tb
        $this->persistent->params = array(
                    'join'      => ['leftJoin', 'DatHang', 'QuanLyThucChi'],
                    'groupBy'   => 'NhaCungCap.tenNhaCungCap',
                    'order'     => 'COUNT(DatHang.id) DESC',
                    'sqlRaw'    => ''
                ) + $this->persistent->params;
		
       	if ( $this->request->getPost('filterRows') ){
			
			$this->persistent->filterRows = $this->request->getPost('filterRows', ['trim']);

			$this->persistent->params = 
			
				array( 'conditions' => 'tenNhaCungCap LIKE :filterRows: OR diaChi LIKE :filterRows: OR lienHe LIKE :filterRows: OR moTa LIKE :filterRows: OR NhaCungCap.ghiChu LIKE :filterRows: ') +
				
				array( 'bind' => ['filterRows' =>"%". $this->request->getPost('filterRows', ['trim']) ."%"] ) +
				
				$this->persistent->params;
        }

        if ( $this->request->getPost('orderBy') ) {

            $orderBy = 'NhaCungCap.'.$this->request->getPost('orderBy');

            //kiểm tra request orderby có chứa soDonHang
            if (strpos($this->request->getPost('orderBy'), 'soDonHang') !== FALSE) {

                $orderBy = 'COUNT(DatHang.id)'. str_replace('soDonHang', '', $this->request->getPost('orderBy'));
            }

            //kiểm tra request orderby có chứa congNo
            if (strpos($this->request->getPost('orderBy'), 'congNo') !== FALSE) {

                $orderBy = "SELECT nha_cung_cap.* FROM nha_cung_cap LEFT JOIN (SELECT * FROM dat_hang  GROUP BY dat_hang.idNhaCungCap) AS DatHang ON nha_cung_cap.id = DatHang.idNhaCungCap LEFT JOIN quan_ly_thuc_chi ON nha_cung_cap.id = quan_ly_thuc_chi.nguoiNhan LEFT JOIN quan_ly_thuc_thu ON nha_cung_cap.id = quan_ly_thuc_thu.idNhaCungCap GROUP BY nha_cung_cap.tenNhaCungCap ORDER BY IF (sum(DatHang.tongTienThanhToan) IS NOT NULL,sum(DatHang.tongTienThanhToan),0) - IF (sum(quan_ly_thuc_chi.soTien) IS NOT NULL,sum(quan_ly_thuc_chi.soTien),0) + IF (sum(quan_ly_thuc_thu.soTien) IS NOT NULL,sum(quan_ly_thuc_thu.soTien),0) " . str_replace('congNo', '', $this->request->getPost('orderBy'));

                $this->persistent->params = array( 'sqlRaw' => $orderBy);
            }
            
            $this->persistent->params = array( 'order' => $orderBy) +
            $this->persistent->params;
        }

		$paginator = new Phalcon\Paginator\Adapter\NativeArray(array(
			"data"  => NhaCungCap::getResult( $this->persistent->params, 'NhaCungCap' ),
			"limit" => $this->persistent->limit,
			"page"  => $this->persistent->currentPage
		));
		
		$page            = $paginator->getPaginate();
        $page->duocXoa   = $this->view->duocXoa;
        $page            = json_encode( $page );
        
        return ( $page );
	}

    public function editAction($id)
    {
        $form    = new NhaCungCapForm();
        
        try{

            $nhaCungCap = NhaCungCap::findFirstById($id);
            $this->_edit($form, $nhaCungCap, "Cập nhật Nhà Cung Cấp thành công");

        } catch(Exception $e){
            $this->flashSession->error($e->getMessage());
        }
        
        $this->view->form   = $form;
		$this->view->title 	= "SỬA NHÀ CUNG CẤP";

        $this->view->disableLevel(
            View::LEVEL_LAYOUT
        );
    }

	public function deleteAction($id)
    {
    	$this->_delete($id,'NhaCungCap');
    }

    public function listAction()
    {
        if ( $this->request->getQuery("page", "int") )

            $this->persistent->currentPage = $this->request->getQuery("page", "int");
        else
            //reset lại các biến session
            $this->_resetAll();
        
        $paginator = new Paginator( array(
            "data"  => NhaCungCap::find(),
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