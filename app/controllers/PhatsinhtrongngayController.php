<?php 

class PhatsinhtrongngayController extends ControllerBase
{
	
	public function initialize()
    {
        $this->assets->addJs('js/js-phatsinhtrongngay.js');
        $this->view->title = "THỐNG KÊ";
        $this->tag->setTitle('Thống Kê');
    }

    //Index
    public function indexAction()
    {

    }

    //ajax xuất thống kê
    public function ajaxThongKeAction()
    {
		$dateStart  = $this->request->getPost('dateStart');
    	$dateEnd	= $this->request->getPost('dateEnd');

    	if (empty($dateStart) || empty($dateEnd)) {
    		return 'Vui Lòng Chọn Ngày Thống Kê';
    	}

        //nếu thống kê theo năm
    	if (strpos(strtolower($this->request->getPost('tuyChon')), 'year') !== false) {
    		
    		if ( ($arrayThongKe = $this->_thongKeTheoThang($dateStart, $dateEnd)) === false ) {
		
				return 'Chưa có dữ liệu';
			}
    	} else {

    		if ( ($arrayThongKe = $this->_thongKeTheoNgay($dateStart, $dateEnd)) === false ) {
		
				return 'Chưa có dữ liệu';
			}
    	}

    	return json_encode($arrayThongKe);
    }

    /*
    *   Dữ liệu thống kê được gom thành tháng dùng để thống kê năm này, năm trước,...
    *   return Array( bao gồm: tên tháng có dữ liệu, value thống kê)
    */
    private function _thongKeTheoThang($dateStart, $dateEnd)
    {
        $monthStart = date('m', strtotime($dateStart));
        $monthEnd   = date('m', strtotime($dateEnd));
        $year       = date('Y', strtotime($dateStart));

        $value = array();
        $thang = array();

        //lấy row cuối cùng của từng tháng gọp thành 1 mảng
        for ($i = $monthStart; $i <= $monthEnd; $i++) {
            
            $row = PhatSinhTrongNgay::findFirst([
                'columns'       => 'thongKe',
                'conditions'    => 'ngay between :dateStart: AND :dateEnd:',
                'bind'          => ['dateStart' => date($year.'-'.$i.'-01'), 'dateEnd' => date($year.'-'.$i.'-31')],
                'order'         => 'id DESC'
            ]);

            //kiểm tra nếu tìm không thấy
            if (!$row) { continue; }

            $value[] = $row;
            $thang[] = $i;
        }

        return [$thang, $this->_xuatArrayThongKe($value)];
    }

    /*
    *   Dữ liệu thống kê được gom thành từng ngày dùng để thống kê ngày này đến ngày kia, tháng này,...
    *   return Array value thống kê
    */
    private function _thongKeTheoNgay($dateStart, $dateEnd)
    {
        $value = PhatSinhTrongNgay::find([
            'columns'       => 'thongKe',
            'conditions'    => 'ngay between :dateStart: AND :dateEnd:',
            'bind'          => ['dateStart' => $dateStart, 'dateEnd' => $dateEnd]
        ]);

        return $this->_xuatArrayThongKe($value);
    }

    /*
    *   Gọp các value thành 1 mảng chung
    *   Return mảng đó
    */
    private function _xuatArrayThongKe($value)
    {
		if (!count($value)) {
			return false;
		}
		
		$arrayThongKe = json_decode($value[0]->thongKe, true);

		if (count($value) > 1) {

            //chạy vòng lặp gọp value
			for ($i = 1; $i < count($value); $i++) {
				
				$arrayThongKe = array_merge_recursive($arrayThongKe, json_decode($value[$i]->thongKe, true));
			}
		}

		return $arrayThongKe;
    }
}
