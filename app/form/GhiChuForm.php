<?php 

use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\TextArea;
use Phalcon\Forms\Element\Select;
use Phalcon\Validation\Validator\PresenceOf;

/**
* 
*/
class GhiChuForm extends BaseForm
{
	public function initialize($entity = null, $options = array())
	{
		
		//tiêu đề
		$tieuDe = new Text('tieuDe');
		$tieuDe->setFilters(['striptags','trim']);
        $tieuDe->addValidators([ new PresenceOf(['message' => 'Vui lòng nhập Tiêu Đề']) ]);
        $this->add($tieuDe);

        //nội dung
		$noiDung = new TextArea('noiDung');
		$noiDung->setFilters(['striptags','trim']);
        $noiDung->addValidators([ new PresenceOf(['message' => 'Vui lòng nhập Nội Dung']) ]);
        $this->add($noiDung);

        //trạng thái
        $trangThai = new Select('trangThai', [
            
            'dangXuLy'  => 'Đang Xử Lý',
            'hoanTat'   => 'Hoàn Tất'
        ]);
        
        $trangThai->setFilters(['striptags','trim']);
        $this->add($trangThai);

        //trạng thái
        $cheDo = new Select('cheDo', [
            
            'Public'    => 'Public',
            'Private'  	=> 'Private'
        ]);
        
        $cheDo->setFilters(['striptags','trim']);
        $cheDo->addValidators([ new PresenceOf(['message' => 'Vui lòng nhập Chế Độ']) ]);
        $this->add($cheDo);
	}

}