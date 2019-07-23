<?php

use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\Numeric;
use Phalcon\Forms\Element\TextArea;
use Phalcon\Forms\Element\Hidden;
use Phalcon\Forms\Element\Select;
use Phalcon\Forms\Element\Check;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\StringLength;

/**
*
*/
class ProductsForm extends BaseForm
{
	public function initialize($entity = null, $options = array())
	{
		if (isset($options['edit']) && $options['edit']) {
            
            $this->add(new Text('donGiaMuaVao'));
        } else {

            $donGiaMuaVao = new Text('donGiaMuaVao');
            $donGiaMuaVao->addValidators([
                new PresenceOf(['message' => 'Vui lòng nhập Đơn Giá Mua Vào'])
            ]);
            $this->add($donGiaMuaVao);
        }

        
		$maSanPham = new Text("maSanPham");
		$maSanPham->setFilters(['striptags', 'string']);
		$maSanPham->addValidators([
			new PresenceOf(['message' => 'Vui lòng nhập Mã Sản Phẩm']),
			new StringLength([ 'max'   =>50, 'messageMaximum' => 'Mã Sản Phẩm Chỉ Được Tối Đa 50 Ký Tự' ]),
		]);
        $this->add($maSanPham);

        $tenSanPham = new Text("tenSanPham");
        $tenSanPham->setFilters(['striptags']);
        $tenSanPham->addValidators([
            new PresenceOf([ 'message' => 'Vui lòng nhập Tên Sản Phẩm' ]),
            new StringLength([ 'max'   =>100, 'messageMaximum' => 'Mã Sản Phẩm Chỉ Được Tối Đa 100 Ký Tự' ])
        ]);
        $this->add($tenSanPham);
		
		$tonKhoBanDau = new Numeric('tonKhoBanDau');
        $this->add($tonKhoBanDau);

        $donGiaMoiNhat = new Text('donGiaMoiNhat');
        $donGiaMoiNhat->addValidators([
			new PresenceOf(['message' => 'Vui lòng nhập Đơn Giá Sản Phẩm'])
        ]);
        $this->add($donGiaMoiNhat);

        $loaiSanPham = LoaiSanPham::find();
        
        $this->add(new Select('loaiSanPham', $loaiSanPham, [
            'using' => [ 'id', 'type' ],
            'filters'    => 'int'
        ]));
		
		$moTa = new TextArea('moTa');
		$moTa->setFilters('trim');
        $this->add($moTa);

        $this->add( new Check('noiBat'));
    }
}