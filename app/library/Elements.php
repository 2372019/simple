<?php

use Phalcon\Mvc\User\Component;

/**
 * Elements
 *
 * Helps to build UI elements for the application
 */
class Elements extends Component
{
	private $_tabs = [
		'products' => [
			'Tất cả' => array(
				'action' => 'search',
				'icon' => 'fa fa-tachometer fa-fw'
			),
			'Thêm sản phẩm' => array(
				'action' => 'add',
				'icon' => 'fa fa-list fa-fw'
			),
			'Tìm nâng cao' => array(
				'action' => 'advancedSearch',
				'icon' => 'fa fa-file-text fa-fw'
			)
		],
		'customers' => [
			'Tất cả' => array(
				'action' => 'search',
				'icon' => 'fa fa-tachometer fa-fw'
			),
			'Thêm khách hàng' => array(
				'action' => 'add',
				'icon' => 'fa fa-list fa-fw'
			),
			'Tìm nâng cao' => array(
				'action' => 'advancedSearch',
				'icon' => 'fa fa-file-text fa-fw'
			)
		],
		'orders' => [
			'Tất cả' => array(
				'action' => 'search',
				'icon' => 'fa fa-tachometer fa-fw'
			),
			'Thêm đơn hàng' => array(
				'action' => 'add',
				'icon' => 'fa fa-list fa-fw'
			),
			'Tìm nâng cao' => array(
				'action' => 'advancedSearch',
				'icon' => 'fa fa-file-text fa-fw'
			),
		],
		'donhangdxl' => [
			'Tất cả' => array(
				'action' => 'search',
				'icon' => 'fa fa-tachometer fa-fw'
			),
		],
		'nhansu' => [
			'Tất cả' => array(
				'action' => 'search',
				'icon' => 'fa fa-tachometer fa-fw'
			),
			'Thêm nhân sự' => array(
				'action' => 'add',
				'icon' => 'fa fa-list fa-fw'
			),
			'Tìm nâng cao' => array(
				'action' => 'advancedSearch',
				'icon' => 'fa fa-file-text fa-fw'
			),
		],
		'nhaxe' => [
			'Tất cả' => array(
				'action' => 'search',
				'icon' => 'fa fa-tachometer fa-fw'
			),
			'Thêm nhà xe' => array(
				'action' => 'add',
				'icon' => 'fa fa-list fa-fw'
			),
			'Tìm nâng cao' => array(
				'action' => 'advancedSearch',
				'icon' => 'fa fa-file-text fa-fw'
			),
		],
		'nhacungcap' => [
			'Tất cả' => array(
				'action' => 'search',
				'icon' => 'fa fa-tachometer fa-fw'
			),
			'Thêm NCC' => array(
				'action' => 'add',
				'icon' => 'fa fa-list fa-fw'
			),
			'Tìm nâng cao' => array(
				'action' => 'advancedSearch',
				'icon' => 'fa fa-file-text fa-fw'
			),
		],
		'dathang' => [
			'Tất cả' => array(
				'action' => 'search',
				'icon' => 'fa fa-tachometer fa-fw'
			),
			'Thêm' => array(
				'action' => 'add',
				'icon' => 'fa fa-list fa-fw'
			),
			'Tìm nâng cao' => array(
				'action' => 'advancedSearch',
				'icon' => 'fa fa-file-text fa-fw'
			),
		],
		'phieunhapkho' => [
			'Tất cả' => array(
				'action' => 'search',
				'icon' => 'fa fa-tachometer fa-fw'
			),
			'Thêm Phiếu Nhập' => array(
				'action' => 'add',
				'icon' => 'fa fa-list fa-fw'
			),
			'Tìm nâng cao' => array(
				'action' => 'advancedSearch',
				'icon' => 'fa fa-file-text fa-fw'
			),
		],
		'phieuxuatkho' => [
			'Tất cả' => array(
				'action' => 'search',
				'icon' => 'fa fa-tachometer fa-fw'
			),
			'Thêm Phiếu Xuất' => array(
				'action' => 'add',
				'icon' => 'fa fa-list fa-fw'
			),
			'Tìm nâng cao' => array(
				'action' => 'advancedSearch',
				'icon' => 'fa fa-file-text fa-fw'
			),
		],
		'thanhpham' => [
			'Tất cả' => array(
				'action' => 'search',
				'icon' => 'fa fa-tachometer fa-fw'
			),
			'Tìm nâng cao' => array(
				'action' => 'advancedSearch',
				'icon' => 'fa fa-file-text fa-fw'
			),
		],
		'quanlychiphi' => [
			'Tất cả' => array(
				'action' => 'search',
				'icon' => 'fa fa-tachometer fa-fw'
			),
			'Thêm QLCP' => array(
				'action' => 'add',
				'icon' => 'fa fa-list fa-fw'
			),
			'Tìm nâng cao' => array(
				'action' => 'advancedSearch',
				'icon' => 'fa fa-file-text fa-fw'
			),
		],
		'quanlythuchi' => [
			'THU' => array(
				'action' => 'thu',
				'icon' => 'fa fa-tachometer fa-fw'
			),
			'Thêm Thu' => array(
				'action' => 'addThu',
				'icon' => 'fa fa-list fa-fw'
			),
			'CHI' => array(
				'action' => 'chi',
				'icon' => 'fa fa-tachometer fa-fw'
			),
			'Thêm Chi' => array(
				'action' => 'addChi',
				'icon' => 'fa fa-list fa-fw'
			),
			'Chuyển Vốn' => array(
				'action' => 'chuyenVon',
				'icon' => 'fa fa-list fa-fw'
			),
			'Thống Kê' => array(
				'action' => 'thongKeThuChi',
				'icon' => 'fa fa-list fa-fw'
			)
		],
		'ghichu' => [
			'Tất cả' => array(
				'action' => 'search',
				'icon' => 'fa fa-tachometer fa-fw'
			),
			'Thêm Ghi Chú' => array(
				'action' => 'add',
				'icon' => 'fa fa-list fa-fw'
			),
			'Tìm nâng cao' => array(
				'action' => 'advancedSearch',
				'icon' => 'fa fa-file-text fa-fw'
			),
		],
		'users' => [
			'Tất cả' => array(
				'action' => 'search',
				'icon' => 'fa fa-tachometer fa-fw'
			),
			'Thêm Users' => array(
				'action' => 'add',
				'icon' => 'fa fa-list fa-fw'
			),
			'Phân Quyền' => array(
				'action' => 'phanquyen',
				'icon' => 'fa fa-list fa-fw'
			),
			'Tìm nâng cao' => array(
				'action' => 'advancedSearch',
				'icon' => 'fa fa-file-text fa-fw'
			),
		],
		'phatsinhtrongngay' => [
			'Tất cả' => array(
				'action' => '',
				'icon' => 'fa fa-tachometer fa-fw'
			),
		],
    ];
	
    private $_leftNav = [

    	'donhangdxl' => [
			'caption' => '<span class="w3-btn w3-ripple w3-red w3-round-xlarge w3-card">Đ.Hàng Đang Xử Lý</span>',
			'action' => 'index'
		],
		'orders' => [
			'caption' => '<span class="w3-btn w3-ripple w3-lime w3-round-xlarge w3-card">Đơn Hàng</span>',
			'action' => 'index'
		],
		'customers' => [
			'caption' => '<span class="w3-btn w3-ripple w3-blue w3-round-xlarge w3-card">Khách Hàng</span>',
			'action' => 'index'
		],
		'products' => [
			'caption' => '<span class="w3-btn w3-ripple w3-green w3-round-xlarge w3-card">Sản Phẩm</span>',
			'action' => 'index'
		],
		'nhansu' => [
			'caption' => '<span class="w3-btn w3-ripple w3-orange w3-round-xlarge w3-card">Nhân Sự</span>',
			'action' => 'index'
		],
		'nhaxe' => [
			'caption' => '<span class="w3-btn w3-ripple w3-teal w3-round-xlarge w3-card">Nhà Xe</span>',
			'action' => 'index'
		],
		'nhacungcap' => [
			'caption' => '<span class="w3-btn w3-ripple w3-black w3-round-xlarge w3-card">Nhà Cung Cấp</span>',
			'action' => 'index'
		],
		'dathang' => [
			'caption' => '<span class="w3-btn w3-ripple w3-black w3-round-xlarge w3-card">Mua Vào</span>',
			'action' => 'index'
		],
		'phieunhapkho' => [
			'caption' => '<span class="w3-btn w3-ripple w3-yellow w3-round-xlarge w3-card">Phiếu Nhập Kho</span>',
			'action' => 'index'
		],
		'phieuxuatkho' => [
			'caption' => '<span class="w3-btn w3-ripple w3-yellow w3-round-xlarge w3-card">Phiếu Xuất Kho</span>',
			'action' => 'index'
		],
		'thanhpham' => [
			'caption' => '<span class="w3-btn w3-ripple w3-yellow w3-round-xlarge w3-card">Nhập Thành Phẩm</span>',
			'action' => 'index'
		],
		'quanlychiphi' => [
			'caption' => '<span class="w3-btn w3-ripple w3-brown w3-round-xlarge w3-card">Chi Phí</span>',
			'action' => 'index'
		],
		'quanlythuchi' => [
			'caption' => '<span class="w3-btn w3-ripple w3-pink w3-round-xlarge w3-card">Q.Lý Thu Chi</span>',
			'action' => 'index'
		],
		'ghichu' => [
			'caption' => '<span class="w3-btn w3-ripple w3-yellow w3-round-xlarge w3-card">Ghi Chú</span>',
			'action' => 'index'
		],
		'users' => [
			'caption' => '<span class="w3-btn w3-ripple w3-purple w3-round-xlarge w3-card">Users</span>',
			'action' => 'index'
		],
		'phatsinhtrongngay' => [
			'caption' => '<span class="w3-btn w3-ripple w3-orange w3-round-xlarge w3-card">Thống Kê</span>',
			'action' => ''
		],
    ];
	
	//hiển thị các tên tab trong các trang
	public function getNavTabs($controller, $activeAction = null)
    {
        $controllerName = $this->view->getControllerName();

        echo '<div class="nav-tabs">';

        foreach ($this->_tabs[$controller] as $caption => $option) {

            if ( $controller == $controllerName && ($option['action'] == $activeAction) ) {
                echo "<div class='tabsItem active'>$caption</div>";
            } else {
                echo "<div class='tabsItem'>$caption</div>";
            }
        }
        echo '</div>';
    }

    //hiển thị nội dung bên trong các tên tab trong các trang
	public function getTabs($controller, $activeAction = null)
    {
		$tabsIndex = 0;
		echo "<div class='w3-container w3-border'>";
		foreach ( $this->_tabs[$controller] as $caption => $option ) {
			
			$html = "<div id='tabs" . $tabsIndex . "' name='tabs' ";
			
            if ( ($option['action'] != $activeAction) ) {
                $html .=  "class='w3-hide' ";
            }
			$tabsIndex++;
			echo $html . ">";
			
			$action = $option['action'];
			$this->view->partial("$controller/$action");
			
			echo '</div>';
        }
		echo "</div>";
    }
	
	public function getLoginMenu()
	{
		$auth = $this->session->get('user');
		
        if ($auth) {
            echo '<div class="nav-collapse">';
            echo '<ul class="nav navbar-nav ">';
            
            echo '</ul>';
            echo '</div>';
        } else {
			echo '<div class="nav-collapse">';
            echo '<ul class="nav navbar-nav ">';
            
            echo '</ul>';
            echo '</div>';
		}
	}
	
	//hiển thị menu các danh mục bên trái (controller) được cho phép truy cập
    public function getLeftNav()
    {
        $controllerName = $this->view->getControllerName();
		$userSession	= $this->session->get('user');
    		
    	$arrayPermission = ($userSession['arrayPermission']) ? get_object_vars(json_decode($userSession['arrayPermission'])) : array();
		
		echo '<div class="w3-bar topmenu">';

		echo $this->tag->linkTo([ 'index' , '<i class="fas fa-home fa-fw btnHome"></i>' , 'class' => 'w3-bar-item w3-button w3-round']);
		
		foreach ($this->_leftNav as $controller => $option) {

			if (array_key_exists($controller, $arrayPermission) || $userSession['permission'] == 'Admin') {
			
				$classes = '';
				
				if ( $controllerName == $controller ) {
					$classes .= ' selected';
				}
				
				echo $this->tag->linkTo([ $controller . '/' . $option['action'], $option['caption'], 'class' => $classes ]);
			}
		}
		
		echo '</div>';
    }

    //hiển thị menu các danh mục (controller)  trang index được cho phép truy cập
    public function indexGoController()
    {
    	$controllerName = $this->view->getControllerName();
    	$userSession	= $this->session->get('user');
    		
    	$arrayPermission = ($userSession['arrayPermission']) ? get_object_vars(json_decode($userSession['arrayPermission'])) : array();

        foreach ($this->_leftNav as $controller => $option) {

			if (array_key_exists($controller, $arrayPermission) || $userSession['permission'] == 'Admin') {

				echo '<div class="w3-col l2 m3 go-controller"><div class="plane"><h3>'
				.$this->tag->linkTo([ $controller . '/' . $option['action'], $option['caption'] ]) 
				.'</h3></div></div>';
			}
		}
    }
}
