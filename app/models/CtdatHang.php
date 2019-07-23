<?php 

class CtdatHang extends BaseModel
{
	public $id;				// <int> Not null, Default Auto Inc
	public $idProducts;		// <int> Not null,
	public $idDatHang;		// <int> Not null,
	public $soLuong;		// <int> Not null,
	public $donGia;			// <int> Not null, 
	public $thanhTien;		// <int> Not null, 
	public $ghiChu;			// <string>
	
	public function initialize()  
	{
		$this->belongsTo("idDatHang", "DatHang", "id");
		$this->belongsTo("idProducts", "Products", "id");
	}

	public static function createCtdatHang($dathang, $data)
	{
		$ctdathang 					= array();
		$dathang->tongCongChuaVAT 	= 0;
		$leng						= count($data->idProductsList);
		
		for ($i = 0; $i < $leng; $i++)
		{
			// Nếu sản phẩm nào đó ko tồn tại thì return false
			if ( !Products::findFirstById($data->idProductsList[$i]) ) {
				$this->flash->warning( "Id sản phẩm ko tồn tại: ". $data->idProductsList[$i] );
				return false;
			}
			
			//gán các giá trị vào các biến trong model CtdatHang
			$ctdathang[$i] = new CtdatHang([
			
				'idDatHang'   	=> $dathang->id,
				'idProducts'	=> $data->idProductsList[$i], 
				'donGia' 		=> (double)$data->donGiaMoiNhatList[$i], 
				'soLuong'		=> $data->soLuongList[$i],
				'thanhTien'		=> $data->donGiaMoiNhatList[$i] * $data->soLuongList[$i],
				'ghiChu'		=> $data->ghiChuCtdatHangList[$i]
			]);
			
			if ( $data->idMoi[$i] > 0 ) // Nếu trường hợp update
				$ctdathang[$i]->id = $data->idMoi[$i];

			$dathang->tongCongChuaVAT += 
				$data->donGiaMoiNhatList[$i] * $data->soLuongList[$i];
		}
		
		// TH Edit: Nếu Id của CtdatHang cũ ko có trong $data->idMoi thì xóa đi
		$idCuList = $dathang->CtdatHang->toArray();

		for ($i = 0; $i < count($idCuList); $i++)
		{
			if ( !in_array( $idCuList[$i]['id'], $data->idMoi ) ) {

				$dathang->CtdatHang[$i]->delete();
			}
		}

		$dathang->CtdatHang = $ctdathang;
	}

}

