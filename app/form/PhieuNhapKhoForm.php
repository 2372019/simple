<?php 

use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\TextArea;
use Phalcon\Forms\Element\Hidden;
use Phalcon\Forms\Element\Numeric;
use Phalcon\Forms\Element\Date;
use Phalcon\Forms\Element\Select;
use Phalcon\Forms\Element\Check;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Numericality;

/**
* 
*/
class PhieuNhapKhoForm extends BaseForm
{
	public function initialize($entity = null, $options = array())
	{

		//nội dung
		$noiDung = new Text('noiDung');
		$noiDung->setFilters(['striptags','trim']);
        $noiDung->addValidators([ new PresenceOf(['message' => 'Vui lòng nhập Nội Dung']) ]);
        $this->add($noiDung);

        //Nhà cung cấp
        $idNhaCungCap = new Hidden("idNhaCungCap");
        $idNhaCungCap->setFilters(['striptags','trim','int']);
        $idNhaCungCap->addValidators([
            new PresenceOf(['message' => 'Vui lòng nhập Nhà Cung Cấp'])
        ]);
        $this->add($idNhaCungCap);

        //số luọng
        $soLuong = new Numeric('tongSoLuong');
        $soLuong->setFilters( ['striptags','trim','int'] );

        $soLuong->addValidators([

            new PresenceOf(['message' => 'Vui lòng nhập số lượng' ]),
            new Numericality(array( 'message' => 'Vui lòng nhập đúng Số Lượng' ))
        ]);

        $this->add($soLuong);

        //tổng thah toán
        $tongThanhToan = new Text('tongThanhToan');
        $tongThanhToan->setFilters(['striptags','trim','int']);

        $tongThanhToan->addValidators([

			new PresenceOf(['message' => 'Vui lòng nhập Tổng Thanh Toán'])
        ]);

        $this->add($tongThanhToan);

        //id Người lập phiếu
        $idNguoiLapPhieu = new Hidden('idNguoiLapPhieu');
        $idNguoiLapPhieu->setFilters(['striptags','trim','int']);
        $idNguoiLapPhieu->addValidators([ new PresenceOf(['message' => 'Vui lòng nhập Người Nhập Phiếu']) ]);

        $this->add($idNguoiLapPhieu);

        //id Người xác nhận
        $idNguoiNhanHang = new Select('idNguoiNhanHang', NhanSu::find(), [

            'using'      => [ 'id', 'hoTen' ],
            'useEmpty'   => true,
            'emptyText'  => '...Chọn...',
            'emptyValue' => '',
            'filters'    => 'int'
        ]);

        $idNguoiNhanHang->setFilters(['striptags','trim','int']);
        $idNguoiNhanHang->addValidators([ new PresenceOf(['message' => 'Vui lòng nhập Người Nhận Hàng']) ]);

        $this->add($idNguoiNhanHang);

        //lý do nhập
        $lyDoNhap = new Select('lyDoNhap', [
            
            'Thay Thế'          => 'Thay Thế',
            'Công Cụ, Dụng Cụ'  => 'Công Cụ, Dụng Cụ',
            'Khác'               => 'Khác'
        ]);
        
        $lyDoNhap->setFilters(['striptags','trim']);
        $lyDoNhap->addValidators([ new PresenceOf(['message' => 'Vui lòng nhập Lý Do Nhập']) ]);
        $this->add($lyDoNhap);

        //ngày
        $this->add( new Date('ngay') );
        //thuế VAT
        $this->add( (new Text('thueVAT'))->setFilters( ['striptags','trim','int'] ) );
        //đã thanh toán
        $this->add( (new Text('daThanhToan'))->setFilters( ['striptags','trim','int'] ) );
        //ghi chú
        $this->add( (new TextArea('ghiChu') )->setFilters( ['striptags','trim']) );
        //còn nợ
        $this->add( (new Text('conNo'))->setFilters( ['striptags','trim','int'] ) );
	}
}