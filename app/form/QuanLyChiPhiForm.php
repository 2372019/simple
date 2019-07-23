<?php 

use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\TextArea;
use Phalcon\Forms\Element\Date;
use Phalcon\Forms\Element\Select;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Date as DateValidator;

/**
* 
*/
class QuanLyChiPhiForm extends BaseForm
{
	public function initialize($entity = null, $options = array())
	{
        //Chi CHo Ai
        $name = new Text("chiChoAi");
        $name->setFilters(['striptags','trim']);
        $name->addValidators([
            new PresenceOf(['message' => 'Vui lòng nhập Chi Cho Ai'])
        ]);
        $this->add($name);    

        //Số Tiền Chi
        $tien = new Text('soTienChi');
        $tien->addValidators([
            new PresenceOf(['message' => 'Vui lòng nhập Số Tiền Chi'])
        ]);
        $this->add($tien);

        //loại chi phí
        $loaiChiPhi = new Select('loaiChiPhi', [
            'CP Vật Tư' => 'CP Vật Tư',
            'CP Gửi Nhận Hàng' => 'CP Gửi Nhận Hàng',
            'CP CPN' => 'CP CPN',
            'CP VPP' => 'CP VPP',
            'CP Lương' => 'CP Lương',
            'CP Khác' => 'CP Khác'
        ]);
        $loaiChiPhi->setFilters(['striptags','trim']);
        $loaiChiPhi->addValidators([ new PresenceOf(['message' => 'Vui lòng nhập Loại Chi Phí']),]);
        $this->add($loaiChiPhi);

        //lý do chi
		$this->add((new TextArea('lyDoChi'))->setFilters(['striptags','trim']));
	}

}