<?php
require_once('Deamon.php');
require_once("Log.class.php");
require_once("./db/Db.class.php");
require_once("./redis/RedisHandler.php");
/**
 * Created by PhpStorm.
 * User: localuser1
 * Date: 2018/11/29
 * Time: 下午8:00
 */
class Deamon_mm
{

    protected static $redis;

    protected static $db;

    protected static $log;//日志类

    const  REDIS_LIST_KEY = 'dacpapi';//

    /**
     * 配置文件参数
     * @var array
     */
    protected $settings;

    /**
     * 配置文件的前缀字符 如db:dacq_db  redis:dacq_redis
     * @var string
     */
    protected  $config_key = 'dacq_';

    public function __construct()
    {

        try{

            $this->settings = parse_ini_file("setting.ini.php",true);

            self::$log = new Log();


            if(!$this->settings[$this->config_key.'db']) throw new \Exception('The database connection parameter configuration does not exist');

            if(!$this->settings[$this->config_key.'redis']) throw new \Exception('The redis connection parameter configuration does not exist');

            self::$redis = RedisH::getInstance($this->settings[$this->config_key.'redis']);

            //self::$db = Db::getInstance($this->settings[$this->config_key.'db']);
            self::$db = new Db($this->settings[$this->config_key.'db']);

        }catch (\Exception $e){

           // $this->log->write($e->getMessage()."\r\n");
            die('Program abort');
        }

    }

    /**
     * 初始化运行程序
     */
    public function run($argv)
    {
        try{
            $deamon = new Deamon('','dacp1');
            $deamon->addJobs(array(
                'function' => 'Deamon_mm::syncLog',
                'argv' => 'Go'
            ));
            $deamon->run($argv);
        }catch (\Exception $e){
            // $this->log->write($e->getMessage()."\r\n");
        }

    }


    public static function testI()
    {
         self::$log->write('cadscsadcs'."\r\n");

        // Creates the instance
       /* $db = new Db();

        //todo 测试
        $db->bind("name","xiaoxiao");
        $data = $db->query("SELECT * FROM books WHERE books.name = :name", array("name"=>"xiaoxiao"));

        var_dump($data);die;
        //end


        // 3 ways to bind parameters :

        // 1. Read friendly method
        $db->bind("firstname","John");
        $db->bind("age","19");
        // 2. Bind more parameters
        $db->bindMore(array("firstname"=>"John","age"=>"19"));
        // 3. Or just give the parameters to the method
        $db->query("SELECT * FROM Persons WHERE firstname = :firstname AND age = :age", array("firstname"=>"John","age"=>"19"));
        //  Fetching data
        $person 	 =     $db->query("SELECT * FROM Persons");
        // If you want another fetchmode just give it as parameter
        $persons_num     =     $db->query("SELECT * FROM Persons", null, PDO::FETCH_NUM);

        // Fetching single value
        $firstname	 =     $db->single("SELECT firstname FROM Persons WHERE Id = :id ", array('id' => '3' ) );

        // Single Row
        $id_age 	 =     $db->row("SELECT Id, Age FROM Persons WHERE firstname = :f", array("f"=>"Zoe"));

        // Single Row with numeric index
        $id_age_num      =     $db->row("SELECT Id, Age FROM Persons WHERE firstname = :f", array("f"=>"Zoe"),PDO::FETCH_NUM);

        // Column, numeric index
        $ages  		 =     $db->column("SELECT age FROM Persons");
        // The following statements will return the affected rows

        // Update statement
        $update		 =  $db->query("UPDATE Persons SET firstname = :f WHERE Id = :id",array("f"=>"Johny","id"=>"1"));*/

    }


    public static function syncLog()
    {
        set_time_limit(0);
        ignore_user_abort(true);

        $i = 0;//无数数据时 计数器
        while(true){
            try{
               // $data = self::$redis->rpop(self::REDIS_LIST_KEY);
                /*
                *  利用$value进行逻辑和数据处理
                */
                echo 'cdcdcd';
               /* if($data){
                    var_dump($data);

                }*/
                sleep(30);
               /* if($data) {
                    //连接数据库
                    Yii::$app->db->open();

                    if(Yii::$app->db->getIsActive()){
                        //处理 推送到手机信息
                        $CommonCore = new CommonCore;

                        $data = json_decode($data,true);


                        $CommonCore->sendMobile($data['usermobile'],$data['msg'],$data['authcode'],$data['authtype']);
                    }


                }else{
                    //注意：因为数据库的连接有超时时间 默认是8小时 所以当连接即将失效的时候 主动区连接一次
                    if($i >= 2000){//默认设置一半时间 8小时 28800秒
                        Yii::$app->db->close();
                        Yii::$app->db->open();
                        $i = 0;
                    }else{
                        ++$i;
                    }
                    sleep(1);//队列中没有消息时，睡眠2s，让出cpu给其他进程
                }*/

            }catch(\Exception $e){
                //write log
              //  var_log($e->getMessage(),'send_emai_or_mobile_queue_error_log');
            }
        }

    }

}

(new Deamon_mm)->run($argv);



