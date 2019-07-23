<?php 

use Phalcon\Mvc\Model\Criteria;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Mvc\View;

class NhaxeController extends ControllerBase
{
	public function initialize()
    {
        $this->tag->setTitle('NHÀ XE');
        $this->assets->addJs('js/js-nhaxe.js');
		$this->view->title = "QUẢN LÝ NHÀ XE";

        $this->view->duocXoa = $this->_duocXoa("NhaXe");
    }

    private function _resetAll(){

        parent::_reset();

        $resetAll = $this->request->getPost('resetAll', "int");
        if ( !$this->persistent->params || $resetAll )
            $this->persistent->params   = array('order' => 'id DESC');
    }

    public function indexAction()
    {
        $this->_resetAll();
        
        $form   = new NhaXeForm();
                
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

            $nhaxe = new NhaXe();
            //gán các value request post vào các biến trong model NhaXe
            $form->bind($_POST, $nhaxe);
            
            $this->persistent->activeTab = "add"; // Khi đã vào form thì cho active tabs Form, Nếu thêm thành công thì mới active tabs search
                        
            if ( $form->isValid() ) {
                
                try{

                    if ( $nhaxe->create() ) {

                        $this->flash->success("Đăng Ký Thành Công");
                        $this->persistent->activeTab = "search";
                        $form->clear();
                    } else {
                        $this->flash->error( $nhaxe->getMessages() );
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

        if ( $this->request->getPost('filterRows') ){
            
            $this->persistent->filterRows = $this->request->getPost('filterRows', ['trim']);

            $this->persistent->params = 
            
                array( 'conditions' => 'tenNhaXe LIKE :filterRows: OR loTrinhDi LIKE :filterRows: OR loTrinhDen LIKE :filterRows: OR soDienThoai LIKE :filterRows: OR diaChi LIKE :filterRows: OR ghiChu LIKE :filterRows: ') +
                
                array( 'bind' => ['filterRows' =>"%". $this->request->getPost('filterRows', ['trim']) ."%"] ) +
                
                $this->persistent->params;
        }

        if ( $this->request->getPost('orderBy') ) {
            
            $this->persistent->orderBy = $this->request->getPost('orderBy');
            
            $this->persistent->params = 
                array( 'order' => $this->request->getPost('orderBy') ) +
                $this->persistent->params;
        }

        if ( $this->request->getPost('filterLoTrinhDi') || $this->request->getPost('filterLoTrinhDen') ){

            $this->persistent->params = 
            
                array( 'conditions' => 'loTrinhDi LIKE :loTrinhDi: AND loTrinhDen LIKE :loTrinhDen: ') +
                
                array( 'bind' => ['loTrinhDi' =>"%". $this->request->getPost('filterLoTrinhDi') ."%" ,
                 'loTrinhDen' =>"%". $this->request->getPost('filterLoTrinhDen') ."%"] )

                  + $this->persistent->params;
        }
        
        $paginator = new Paginator(array(
            "data"  => NhaXe::find( $this->persistent->params ),
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
        $form    = new NhaXeForm();
        
        try{
            //get value nhan sự có id
            $nhaxe = NhaXe::findFirstById($id);

            $this->_edit($form, $nhaxe, "Cập nhật Nhà Xe thành công");
        } catch(Exception $e){

            $this->flashSession->error($e->getMessage());
        }

        $this->view->form   = $form;
        $this->view->title  = "SỬA NHÀ XE";

        //k hiển thị view trong layout
        $this->view->disableLevel(
            View::LEVEL_LAYOUT
        );
    }

    public function deleteAction($id)
    {
        $this->_delete($id,'NhaXe');
    }

}