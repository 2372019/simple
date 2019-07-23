<?php 

class Ctorders extends BaseModel
{
	public $id;				// <int> Not null, Default Auto Inc
	public $idProducts;		// <int> Not null,
	public $idOrders;		// <int> Not null,
	public $soLuong;		// <int> Not null,
	public $donGia;			// <int> Not null, 
	public $thanhTien;		// <int> Not null, 
	public $ghiChu;			// <string>
	
	public function initialize()  
	{
		$this->belongsTo("idOrders", "Orders", "id");
		$this->belongsTo("idProducts", "Products", "id");
	}
	
	/*
	* Hàm này sẽ tạo ra Ctorders và chỉ tính tongCongChuaVAT
	* Trả về false nếu ko tìm đc sản phẩm
	*/
	public static function createCtOrders( $order, $data ){
		
		$ctorders 				= array();
		$order->tongCongChuaVAT = 0;
		$leng					= count($data->idProductsList);
		
		for ($i = 0; $i < $leng; $i++)
		{
			// Nếu sản phẩm nào đó ko tồn tại thì return false
			if ( !Products::findFirstById($data->idProductsList[$i]) ) {
				$this->flash->warning( "Id sản phẩm ko tồn tại: ". $data->idProductsList[$i] );
				return false;
			}
			
			//gán các giá trị vào các biến trong model Ctorders
			$ctorders[$i] = new Ctorders([
			
				'idOrders'   	=> $order->id,
				'idProducts'	=> $data->idProductsList[$i], 
				'donGia' 		=> (double)$data->donGiaMoiNhatList[$i], 
				'soLuong'		=> $data->soLuongList[$i],
				'thanhTien'		=> $data->donGiaMoiNhatList[$i] * $data->soLuongList[$i],
				'ghiChu'		=> $data->ghiChuCtordersList[$i]
			]);
			
			
			if ( $data->idMoi[$i] > 0 ) // Nếu trường hợp update
				$ctorders[$i]->id = $data->idMoi[$i];

			$order->tongCongChuaVAT += 
				$data->donGiaMoiNhatList[$i] * $data->soLuongList[$i];
		}
		
		// TH Edit: Nếu Id của Ctorders cũ ko có trong $data->idMoi thì xóa đi
		$idCuList = $order->Ctorders->toArray( ['columns' => 'id'] );
		for ($i = 0; $i < count($idCuList); $i++)
		{
			if ( !in_array( $idCuList[$i], $data->idMoi ) ) {

				$order->Ctorders[$i]->delete();
			}
		}
		
		$order->Ctorders 		= $ctorders;
	}
}