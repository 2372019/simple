<?php 

use Phalcon\Config;
use Phalcon\Logger;

return new Config([
	
	'privateResources'	=> [
		'Customers'	=> [
			'index',
			'ajaxSearch',
			'edit',
			'delete'
		],
		'Nhansu'	=> [
			'index',
			'ajaxSearch',
			'edit',
			'delete'
		],
		'Nhaxe'	=> [
			'index',
			'ajaxSearch',
			'edit',
			'delete'
		],
		'nhacungcap'	=> [
			'index',
			'ajaxSearch',
			'edit',
			'delete'
		],
		'dathang'	=> [
			'index',
			'ajaxSearch',
			'edit',
			'delete'
		],
		'phieunhapkho'	=> [
			'index',
			'ajaxSearch',
			'edit',
			'delete'
		],
		'phieuxuatkho'	=> [
			'index',
			'ajaxSearch',
			'edit',
			'delete'
		],
		'donhangdxl'	=> [
			'index',
			'ajaxSearch',
		],
		'orders'	=> [
			'index',
			'ajaxSearch',
			'edit',
			'delete'
		],
		'products'	=> [
			'index',
			'ajaxSearch',
			'edit',
			'delete'
		],
		'users'	=> [
			'index',
			'edit',
			'ajaxSearch',
			'delete',
			'addPhanQuyen',
			'editPhanQuyen',
			'deletePhanQuyen'
		],
		'quanlychiphi'	=> [
			'index',
			'ajaxSearch',
			'edit',
			'delete'
		],
		'quanlythuchi'	=> [
			'index',
			'ajaxSearch',
			'editThu',
			'editChi',
			'thongKeThucChi',
			'thongKeDoanhThu',
			'delete'
		],
		'thanhpham'	=> [
			'index',
			'ajaxSearch',
			'edit',
		],
		'ghichu'	=> [
			'index',
			'ajaxSearch',
			'edit',
			'delete'
		],
		'phatsinhtrongngay'	=> [
			'index',
		]
	]
]);
