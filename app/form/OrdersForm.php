<?php 

use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\TextArea;
use Phalcon\Forms\Element\Hidden;
use Phalcon\Forms\Element\Date;
use Phalcon\Forms\Element\Select;
use Phalcon\Forms\Element\Check;
use Phalcon\Validation\Validator\PresenceOf;

/**
* 
*/
class OrdersForm extends BaseForm
{
	public function initialize($entity = null, $options = array())
	{
        $this->add( new Hidden( 'idKhachHang' ) );

		$tenKhachHang = new Text('tenKhachHang', ['readonly' => 'true']);
		$tenKhachHang->setFilters(['striptags','trim']);
        $tenKhachHang->addValidators([ new PresenceOf(['message' => 'Vui lòng chọn khách hàng']),]);
        $this->add($tenKhachHang);

        $hinhThucThanhToan = new Select('hinhThucThanhToan', [
            'Tiền Mặt' => 'Tiền Mặt',
            'Nợ' => 'Nợ',
            'Chuyển Khoản' => 'Chuyển Khoản',
            'Theo Hợp Đồng' => 'Theo Hợp Đồng',
            'Khác' => 'Khác'
        ]);
        $hinhThucThanhToan->setFilters(['striptags','trim']);
        $hinhThucThanhToan->addValidators([ new PresenceOf(['message' => 'Vui lòng nhập Hình Thức Thanh Toán']) ]);
        $this->add($hinhThucThanhToan);

        $nhansu = NhanSu::find();
        $idEmployees = new Select('idEmployees', $nhansu, [
            'using' => [ 'id', 'hoTen' ],
            'useEmpty'   => true,
            'emptyText'  => '...Chọn...',
            'emptyValue' => '',
            'filters'    => 'int'
        ]);
        $idEmployees->addValidators([ new PresenceOf(['message' => 'Vui lòng nhập Người Giao Hàng']) ]);
        $this->add($idEmployees);

        $trangThai = new Select('trangThai', [
					'choThanhToan' 	=> Orders::status('choThanhToan'),
					'conNo' 		=> Orders::status('conNo'),
					'choXacNhan' 	=> Orders::status('choXacNhan'),
					'khac' 			=> Orders::status('khac')
		]);
        $trangThai->addValidators([ new PresenceOf(array( 'message' => 'Vui lòng nhập Trạng Thái' )) ]);
        $this->add($trangThai);

        $congTyBanHang = new Select('idCongTyBanHang', CongTyBanHang::find(), [
            'using' => [ 'id', 'tenCongTy' ],
            'useEmpty'   => true,
            'emptyText'  => '...Chọn...',
            'emptyValue' => '',
            'filters'    => 'int'
        ]);
		$congTyBanHang->addValidators([ new PresenceOf(['message' => 'Vui lòng nhập Công Ty Bán Hàng']) ]);
        $this->add($congTyBanHang);
		
        $this->add( new Date('ngayHenThanhToan') );

        $this->add( new Date('ngayXuatHoaDon') );

        $this->add( (new Text('soHoaDon') )->setFilters(['striptags','trim']) );

        $this->add( new Check('coXuatHoaDonKhong'));

        $this->add( new Text('chiPhiGiaoHang'));

        $this->add( new Text('hoaHong'));

        $this->add( new Text('congNo'));

        $this->add( new Text('daThanhToan'));

        $this->add( (new Text('chiTietDonHang'))->setFilters(['trim']) );

        $this->add( (new Text('diaChiGiaoHang'))->setFilters(['striptags','trim']) );

        $this->add( (new Text('thongTinNguoiNhanHang'))->setFilters(['striptags','trim']) );
        
		$this->add( (new TextArea('ghiChu'))->setFilters(['striptags','trim']) );
	}
}