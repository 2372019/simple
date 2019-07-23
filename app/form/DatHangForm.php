<?php 

use Phalcon\Forms\Element\TextArea;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\Hidden;
use Phalcon\Forms\Element\Check;
use Phalcon\Validation\Validator\PresenceOf;

/**
* 
*/
class DatHangForm extends BaseForm
{
	public function initialize($entity = null, $options = array())
	{
		//Nhà cung cấp
        $idNhaCungCap = new Hidden("idNhaCungCap");
        $idNhaCungCap->setFilters(['striptags','trim','int']);
        $idNhaCungCap->addValidators([
            new PresenceOf(['message' => 'Vui lòng nhập Nhà Cung Cấp'])
        ]);
        $this->add($idNhaCungCap);

        //có xuất hóa đơn không
        $this->add( new Check('coXuatHoaDonKhong'));

        //ghi chú
        $noiDung = new Text('noiDung');
        $noiDung->setFilters(['striptags','trim']);
        $noiDung->addValidators([
            new PresenceOf(['message' => 'Vui lòng nhập Nội Dung'])
        ]);
        $this->add($noiDung);

        //ghi chú
        $this->add( (new TextArea('ghiChu'))->setFilters(['striptags','trim']) );
	}

}