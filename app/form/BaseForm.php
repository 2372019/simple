<?php 

use Phalcon\Forms\Form;

/**
* 
*/
class BaseForm extends Form
{
	// Viết lại hàm getMessages của Phalcon\Forms\Form
	public function getMessages($byItemName = false)
    {
        $messages = [];
        foreach (parent::getMessages() as $message) {
			$messages[] = $message->getMessage();
        }
        return $messages;
    }
}