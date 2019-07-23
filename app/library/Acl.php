<?php 

use Phalcon\Mvc\User\Component;
use Phalcon\Acl\Adapter\Memory as AclList;
use Phalcon\Acl\Role;
use Phalcon\Acl\Resource;

/**
* 
*/
class Acl extends Component
{

	private $privateResources = array();

	//kiểm tra controller và action có là key và value trong mảng $privateResources (liên quan function addPrivateResources và file PrivateResources trong config)
	public function isPrivateResources($controllerName, $actionName)
	{
		$controllerName = strtolower($controllerName);

		if ( isset($this->privateResources[$controllerName]) ) {
			
			return in_array( $actionName, $this->privateResources[$controllerName] );
		} else {

			return false;
		}

	}

	//kiểm tra 3 cái đó đã được allow trong ACL chưa
	public function isAllowed($groupId,$controller,$action)
	{

		return $this->acl->isAllowed($groupId,$controller,$action);
	}

	//set up Acl
	public function createAcl()
	{
		$acl = new AclList();
		$acl->setDefaultAction(\Phalcon\Acl::DENY); //gán tất cả các action là không vào được (deny)

		$userPhanQuyen = $this->session->get('user'); //get value session của user( khi đăng nhập )

		if ( $userPhanQuyen ) {
			
			$acl->addRole( new Role($userPhanQuyen['permission']) ); //set Role là tên quyền của user vào ACL

			foreach ($this->privateResources as $controller => $action) {

				$acl->addResource( new Resource( $controller) , $action ); //set Resource là các tên controller action vào ACL
			}

			if ($userPhanQuyen['arrayPermission']) {
				
				$arrayPermissions = (get_object_vars(json_decode($userPhanQuyen['arrayPermission'])));//decode json và chuyển nó thành mảng

				foreach ($arrayPermissions as $controller => $action) {

					$acl->allow($userPhanQuyen['permission'],$controller,$action);	//set Allow cho tên quyền ứng với controller action
				}

			}

		}

		return $acl;
	}

	//gán private $privateResources bằng biến $resource(file service trong config)
	public function addPrivateResources(array $resource)
	{
		if (count($resource) > 0) {
			
			$this->privateResources = array_change_key_case(array_merge($this->privateResources , $resource) , CASE_LOWER);
			$this->acl = $this->createAcl();
		}
	}
}