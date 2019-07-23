<?php 

use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\TextArea;
use Phalcon\Forms\Element\Hidden;
use Phalcon\Forms\Element\Numeric;
use Phalcon\Forms\Element\Select;
use Phalcon\Forms\Element\Check;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Numericality;

/**
* 
*/
class PhieuXuatKhoForm extends BaseForm
{
	public function initialize($entity = null, $options = array())
	{

		//tên khách hàng
		$tenKH = new Text('tenKH');
		$tenKH->setFilters(['striptags','trim']);
        $tenKH->addValidators([ new PresenceOf(['message' => 'Vui lòng nhập Tên Khách Hàng']) ]);
        $this->add($tenKH);

        //số luọng
        $soLuong = new Numeric('tongSoLuong');
        $soLuong->setFilters( ['striptags','trim','int'] );

        $soLuong->addValidators([

            new PresenceOf(['message' => 'Vui lòng nhập số lượng' ]),
            new Numericality(array( 'message' => 'Vui lòng nhập đúng Số Lượng' ))
        ]);

        $this->add($soLuong);

        //tổng thah toán
        $diaChi = new Text('diaChi');
        $diaChi->setFilters(['striptags','trim']);

        $diaChi->addValidators([

			new PresenceOf(['message' => 'Vui lòng nhập Địa Chỉ'])
        ]);

        $this->add($diaChi);

        //id Người lập phiếu
        $idNguoiLapPhieu = new Hidden('idNguoiLapPhieu');
        $idNguoiLapPhieu->setFilters(['striptags','trim','int']);
        $idNguoiLapPhieu->addValidators([ new PresenceOf(['message' => 'Vui lòng nhập Người Lập Phiếu']) ]);

        $this->add($idNguoiLapPhieu);

        //lý do nhập
        $lyDoXuat = new Select('lyDoXuat', [
            ''                  => '...Chọn...',
            'Thay Thế'    		=> 'Thay Thế',
            'Trả Hàng'      	=> 'Trả Hàng',
            'Khác'              => 'Khác',
        ]);
        
        $lyDoXuat->setFilters(['striptags','trim']);
        $lyDoXuat->addValidators([ new PresenceOf(['message' => 'Vui lòng nhập Lý Do Xuất']) ]);
        $this->add($lyDoXuat);

        //đã thanh toán
        $this->add( (new Text('nguoiNhan'))->setFilters( ['striptags','trim'] ) );
        //người lập phiếu
        $this->add( (new Hidden('idNguoiXacNhan'))->setFilters( ['striptags','trim','int'] ) );
        //ghi chú
        $this->add( (new TextArea('ghiChu') )->setFilters( ['striptags','trim']) );
	}
}