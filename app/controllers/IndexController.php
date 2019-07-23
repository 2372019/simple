<?php
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Mvc\View;

class IndexController extends ControllerBase
{

    public function initialize()
    {
        $this->tag->setTitle('PHẦN MỀM QUẢN LÝ');
    }
	
    private function _resetAll()
    {
		$this->_reset();
    }

    public function indexAction()
    {
		
    	$today = date('Y-m-d');

        $arrayDonHangNow = array(
            'conditions' => 'ngay between :dateStart: AND :dateEnd:' ,
            'bind' => ['dateStart' => $today.' 00:00:01', 'dateEnd' => $today.' 23:59:59']
        );

        //đếm đơn hàng ngày hôm nay
        $countDonHangNow    = Orders::count($arrayDonHangNow);

        //get đơn hàng có trạng thái là chờ xác nhận
        $countDonHangCXN    = Orders::searchParams([
            'conditions'    => 'trangThai = :trangThai:',
            'bind' => ['trangThai' =>'choXacNhan']
        ]);

        //get đơn hàng có trạng thái là chờ thanh toán
        $countDonHangCTT    = Orders::searchParams([
            'conditions'    => 'trangThai = :trangThai:',
            'bind' => ['trangThai' =>'choThanhToan']
        ]);

        //get đơn hàng có trạng thái là chờ xác nhận, chờ thanh toán, còn nợ
        $countDonHangDXL    = Orders::searchParams([
            'conditions'    => 'trangThai IN ({trangThai:array})',
            'bind' => ['trangThai' =>['choThanhToan','choXacNhan','conNo']]
        ]);

        //get sum đơn hàng hôm nay
        $sumDonHangNow      = Orders::sum(['column' => 'tongTienThanhToan'] + $arrayDonHangNow);

        //get sum đơn hàng có trạng thái là còn nợ
        $sumDonHangCN   = Orders::sum([
            'column'    => 'congNo',
            'conditions'=> 'trangThai = :trangThai:',
            'bind'      => ['trangThai' => 'conNo']
        ]);

        //get sum đơn hàng có trạng thái là còn nợ hôm nay
        $sumDonHangCNNow   = Orders::sum([
            'column'    => 'congNo',
            'conditions'=> 'ngay between :dateStart: AND :dateEnd: AND trangThai IN ({trangThai:array})',
            'bind'      => ['dateStart' => $today.' 00:00:01', 'dateEnd' => $today.' 23:59:59', 'trangThai' =>['choThanhToan','choXacNhan','conNo']]
        ]);

        //get sum số tiền thực thu hôm nay
        $sumThucThuNow  = QuanLyThucThu::sum([
            'column'    => 'soTien',
            'conditions'=> 'ngay = :dateStart:',
            'bind'      => ['dateStart' => $today]
        ]);
		
        $thuBanHangToday    = QuanLyThucThu::sum([
            'column'    => 'soTien',
            'conditions' => 'ngay = :dateStart: AND nguonThu = :nguonThu:',
            'bind' => ['dateStart' => $today, 'nguonThu' => 'Bán Hàng']
        ]);
		
        //get sum số tiền chi thu hôm nay
        $sumThucChiNow    = QuanLyThucChi::sum([
            'column'    => 'soTien',
            'conditions'=> 'ngay = :dateStart:' ,
            'bind'      => ['dateStart' => $today]
        ]);
		

        //gán các biến để sử dụng ngoài view
        $this->view->setVars([
            'countDonHangCXN'   => count($countDonHangCXN),
            'countDonHangCTT'   => count($countDonHangCTT),
            'countDonHangDXL'   => count($countDonHangDXL),
            'countDonHangNow'   => $countDonHangNow,
            'sumDonHangCN'      => $sumDonHangCN,
            'sumDonHangNow'     => $sumDonHangNow,
            'sumThucThuNow'     => $sumThucThuNow,
            'thuBanHangToday'   => $thuBanHangToday,
            'sumDonHangCNNow'   => $sumDonHangCNNow,
            'sumThucChiNow'     => $sumThucChiNow
        ]);
    }
	
	/*public function phatSinhTrongNgayAction(){
		
		$this->view->title = "PHÁT SINH TRONG NGÀY";
		 
		$dateList = PhatSinhTrongNgay::Search();
		 
		$this->_resetAll();
		
		$paginator = new Paginator( array(
			"data"  => $dateList,
			"limit" => $this->persistent->limit,
			"page"  => $this->persistent->currentPage
		) );
		
		//gán các biến để sử dụng ngoài view
		$this->view->setVars([
			"activeTab"		=> $this->persistent->activeTab,
			"page"			=> $paginator->getPaginate(),
			"limit" 		=> $this->persistent->limit,
			"currentPage" 	=> $this->persistent->currentPage,
			"filterRows" 	=> $this->persistent->filterRows,
			"orderBy" 		=> $this->persistent->orderBy
		]);
	}*/
}