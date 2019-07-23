<?php 

class Ctproducts extends BaseModel
{
	public $id;					// <int> Not null, Default Auto Inc
	public $idProducts;			// <int> Not null,
	public $idProductsVatTu;	// <int> Not null,
	public $soLuongVatTu;		// <int> Not null,
	
	public function initialize()  
	{
		$this->belongsTo("idProductsVatTu", "Products", "id", ['alias' => 'Vattu']);
		$this->belongsTo("idProducts", "Products", "id");
	}

	public static function createCtProducts($product, $data) {

		$ctProducts = array();
		$leng 		= count($data->idProducts);
		
		for ($i = 0; $i < $leng; $i++) {
			
			if ( !Products::findFirstById( $data->idProducts[$i] ) ) {
				$this->flashSession->error("Không tồn sản phẩm này");
				continue;
			}

			$ctProducts[$i] = new Ctproducts([

				'idProducts'		=> $product->id,
				'idProductsVatTu'	=> $data->idProducts[$i],
				'soLuongVatTu'		=> $data->soLuongList[$i]
			]);

			if ( $data->idMoi[$i] > 0 ) // Nếu trường hợp update
				$ctProducts[$i]->id = $data->idMoi[$i];
		}

		// TH Edit: Nếu Id của Ctproducts cũ ko có trong $data->idMoi thì xóa đi
		$idCuList = $product->Ctproducts->toArray( ['columns' => 'id'] );
		for ($i = 0; $i < count($idCuList); $i++)
		{
			if ( !in_array( $idCuList[$i], $data->idMoi ) ) {

				$product->Ctproducts[$i]->delete();
			}
		}

		$product->Ctproducts = $ctProducts;

		return true;
	}

}