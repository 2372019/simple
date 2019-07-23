<?php

class PhatSinhTrongNgay extends BaseModel
{
	public $id; 				// <int> Not null, Default AI
	public $ngay; 				// <timestamp> Not null, Default CURRENT_TIMESTAMP
	public $thongKe;			// <double> Not null, Default 0
	
	public function beforeCreate()
	{
		$this->ngay = date('Y-m-d');
	}

	/*
	*	Tính toán tiền các thông tin như doanh thu, công nợ, sản phẩm nổi bật,...
	*	Kết Quả trả về là mảng các thông tin trên
	*
	*/
	public static function thongKeChung()
	{
		$arrayChung = array(

			'chung'	=> [

				'doanhThuCoVAT'	=> 0,
				'doanhThuKhongVAT'	=> 0,
				'thueVAT'			=> 0,
				'tongCongNo'		=> 0,

				'congNoThayDoi'		=> Orders::sum(['column' => 'tongTienThanhToan', 'conditions' => 'ngay = "'. date('Y-m-d') .'"'])
				 - Orders::sum(['column' => 'tongTienThanhToan', 'conditions' => 'ngay = "'. date('Y-m-d',(strtotime ( '-1 day' , strtotime ( date('Y-m-d')) ) )) .'"' ]),

				'sanPhamNoiBat'		=> 0,
				'sanPhamKhac'		=> 0,
			]
		);

		$products = Products::find();
		for ($i = 0; $i < count($products); $i++) {

			if ( $products[$i]->noiBat )
				$arrayChung['chung']['sanPhamNoiBat'] += $products[$i]->tonHienTai * $products[$i]->donGiaMuaVao;
			else
				$arrayChung['chung']['sanPhamKhac'] += $products[$i]->tonHienTai * $products[$i]->donGiaMuaVao;
			
		}

		$orders = Orders::find([
			'conditions'	=> 'trangThai NOT IN ({trangThai:array}) AND ngay = "'. date('Y-m-d') .'"',
			'bind'			=> ['trangThai' => ['huyTruocBH','huySauBH']]
		]);

		for ($i = 0; $i < count($orders); $i++) {
			
			if ( $orders[$i]->thueVAT )
				$arrayChung['chung']['doanhThuCoVAT']	+= $orders[$i]->tongTienThanhToan;
			else
				$arrayChung['chung']['doanhThuKhongVAT']	+= $orders[$i]->tongTienThanhToan;

			$arrayChung['chung']['thueVAT']			+= $orders[$i]->thueVAT;
			$arrayChung['chung']['tongCongNo']		+= $orders[$i]->congNo;
		}

		return $arrayChung;
	}


	//Cộng các function lại với nhau để trả về 1 mảng 
	public function thongKe()
	{
		$arrayThongKe = QuanLyThucThu::thongKeThucThu() + QuanLyThucChi::thongKeThucChi() + Orders::thongKeTrongNgay() + $this->thongKeChung();

		return $arrayThongKe;
	}

	//Lấy kết quả của function thongKe để sắp xếp, xử lý với cột thống kê trong row cuối cùng của model PhatSinhTrongNgay
	//Tạo ra một mảng tổng hợp các thông tin thống kê trong hôm nay và insert vào model PhatSinhTrongNgay
	public function createThongKe()
	{
		$arrayThongKe 	= $this->thongKe();
		$lastValue		= json_decode(PhatSinhTrongNgay::findFirst(['order' => 'id DESC'])->toArray()['thongKe'], true);

		foreach ($lastValue as $key1 => $value) {

	    	if ($key1 == 'chung') {
    			
	    		foreach ($value as $key2 => $v) {
	    			
	    			$arrayThongKe[$key1][$key2] += $lastValue[$key1][$key2];
	    		}
	    	}

	    	if($key1 == 'thucThu' || $key1 =='thucChi')
	    	{
	    		foreach ($value as $key2 => $v) {

	    			if ($key2 == 'tongThu' || $key2 == 'tongThucChi') {
	    				
	    				$arrayThongKe[$key1][$key2] += $lastValue[$key1][$key2];
	    			} else {

	    				foreach ($v as $key3 => $val) {
		    				
	    					$arrayThongKe[$key1][$key2][$key3] += $lastValue[$key1][$key2][$key3];
		    			}
	    			}

	    		}
	    	}
    		
    	}

		$this->create([

			'ngay'		=> date('Y-m-d'),
			'thongKe'	=> json_encode($arrayThongKe)
		]);
		return true;
	}

}