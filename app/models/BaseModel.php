<?php 

use Phalcon\Db\Profiler as ProfilerDb;
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Resultset\Simple as Resultset;

use Phalcon\DI;
use Phalcon\Logger\Adapter\File;
//use Phalcon\Events\Manager;
use Phalcon\Mvc\Model\Manager;
use Phalcon\Logger;

class BaseModel extends Model
{
    public function afterCreate()
    {
        
        $this->_insertLogQuery();

        /*$data = [
            'type' => 'Insert',
            'modelName' => get_class($this)
        ];

        $this->_pusher($data);*/
    }

    protected function _pusher($data)
    {
        $helps = new Helps();
        $helps->pusher($data);
    }

    protected function _getRawQuery()
    {
        $connection = DI::getDefault()->getShared('db');

        $query = $connection->getSQLStatement();
        $value = $connection->getSQLVariables();
        $te = $connection->getRealSQLStatement();

        //echo"<pre />";var_dump($query).'<br>';var_dump($value).'<br>';exit;
        echo"<pre />";var_dump($value);
         echo"<pre />";var_dump($query);
         echo"<pre />";var_dump($te);
         echo"<pre />";var_dump($connection);



        return [ 'ngay' => date('Y-m-d H:i:s'), 'query' => $query, 'value' => implode(',', $value) ];


    }
    protected function _insertLogQuery()
    {
        $data = $this->_getRawQuery();

        // echo"<pre />";var_dump($data);

        // $sql = 'INSERT INTO file_logs (ngay, query, value) VALUES ("'.implode('","', $data).'")';

        //$a = new FileLogs();
       // $a->save(['ngay'    => '2019-07-19 10:46:23', 'query'   =>$data['query'], 'value' =>'ds']);

        //$this->getReadConnection()->query($sql);
        // echo"<pre />";var_dump($this->getReadConnection()->query($sql));
        // exit;

    }

	public function getMessages()
    {
        $messages = [];

        foreach (parent::getMessages() as $message) {
            switch ($message->getType()) {
                case 'InvalidCreateAttempt':
                    $messages[] = 'Tên Mã Đã Trùng';
                    break;
				case 'InvalidUpdateAttempt':
					$messages[] = 'Tên Mã Đã Trùng';
                    break;
				default:
					$messages[] = $message->getMessage();
                    break;
            }
        }
		
		$messages = implode("<br>", $messages);
        return $messages;
    }
	
	public function renderMoney($money)
    {
        $element  = $this->get($name);

        // Get any generated messages for the current element
        $messages = $this->getMessagesFor(
            $element->getName()
        );

        if (count($messages)) {
            // Print each element
            echo "<div class='messages'>";

            foreach ($messages as $message) {
                echo $this->flash->error($message);
            }

            echo '</div>';
        }

        echo '<p>';

        echo '<label for="', $element->getName(), '">', $element->getLabel(), '</label>';

        echo $element;

        echo '</p>';
    }

    public static function getResult($param, $modelName) {

        $resultSet = $modelName::searchParams($param, $modelName);
        
        $kq = [];
        foreach ($resultSet as $value) {

            if ($value->getThongTinRow() == null) {
                continue;
            }
            
            $kq[]   = $value->getThongTinRow();
        }
        
        return $kq;
    }

    public static function searchParams($params = null, $modelName = null)
    {
        if (empty($params['sqlRaw']) ) {

            $query = self::query();

            if (isset($params['conditions']))
                $query->where($params['conditions'],$params['bind']);

            if (isset($params['groupBy']))
                $query->groupBy($params['groupBy']);

            if (isset($params['join'])) {

                for ($i=1; $i < count($params['join']); $i++) {

                    $query->$params['join'][0]( $params['join'][$i] );
                }
            }

            if(isset($params['order']))
                $query->orderBy($params['order']);    

            return $query->execute();
        } else {

            $sql = $params['sqlRaw'];

            $model = new $modelName();
            return new Resultset(
                null,
                $model,
                $model->getReadConnection()->query($sql)
            );
        }
    }

}