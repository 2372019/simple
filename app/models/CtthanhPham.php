<?php 

class CtthanhPham extends BaseModel
{
	public $id;				// <int> Not null, Default Auto Inc
	public $idThanhPham;	// <int> Not null,
	public $idCtProducts;	// <int> Not null,
	public $soLuongVatTu;	// <int> Not null,
	public $soLuongThem;	// <int> Not null, 
	public $lyDoThem;		// <int> Not null, 
	public $ghiChu;			// <string>
	
	public function initialize()  
	{
		$this->belongsTo("idThanhPham", "ThanhPham", "id");
		$this->belongsTo("idCtProducts", "Products", "id");
	}

	public static function createCtthanhPham($thanhPham, $data){

		$ctthanhpham 		= array();
		$tongSoLuongVatTu 	= 0;

		for ($i = 0; $i < count($data->ctProducts); $i++) {
			
			$ctthanhpham[$i] = new CtthanhPham([

				'idCtProducts'	=> $data->ctProducts[$i]['idProductsVatTu'],
				'soLuongVatTu'	=> $data->ctProducts[$i]['soLuongVatTu'] * $data->soLuongThanhPham,
				'soLuongThem'	=> $data->soLuongPhatSinh[$i],
				'ghiChu'		=> $data->ghiChuVatTu[$i]
			]);

			$tongSoLuongVatTu 	= $tongSoLuongVatTu + $ctthanhpham[$i]->soLuongVatTu + $ctthanhpham[$i]->soLuongThem;
		}

		for ($j = count($data->ctProducts); $j < count($data->idMaVatTu); $j++) {
			
			$ctthanhpham[$j] = new CtthanhPham([

				'idCtProducts'	=>	$data->idMaVatTu[$j],
				'soLuongVatTu'	=>	$data->soLuongPhatSinh[$j],
				'ghiChu'		=>	$data->ghiChuVatTu[$j]
			]);

			$tongSoLuongVatTu += $data->soLuongPhatSinh[$j];
		}

		$thanhPham->CtthanhPham = $ctthanhpham;

		return $tongSoLuongVatTu;
	}

}