<?php

require_once('Deamon.php');
require_once("Log.class.php");
require_once("./db/Db.class.php");
require_once("./redis/RedisHandler.php");

require_once("./model/dacp.php");
/**
 * 注意：文件名必须  Deamon_ 前缀开头 方便进程查找
 * Created by PhpStorm.
 * User: localuser1
 * Date: 2018/11/29
 * Time: 下午8:00
 */
class Deamon_test
{

    protected static $redis;

    protected static $db;

    protected static $log;//日志类

    public static $tableName;//表名称

    /**
     * log前缀名称
     * @var string
     */
    protected $logName;

    protected static $redis_list_key;//队列的key

    /**
     * 配置文件参数
     * @var array
     */
    protected static $settings;

    /**
     * 配置文件的前缀字符 如db:dacq_db  redis:dacq_redis
     * @var string
     */
    protected  static $config_key;

    /**
     * 进程文件名
     * @var
     */
    protected  static $pid_name;

    /**
     *  config配置文件名称
     * @var
     */
    protected  static $conf_name;


    /**
     * 初始化运行程序
     */
    public function run($argv)
    {

        if(count($argv) > 1 && in_array($argv[1],['start','stop','status'])){
            $input_name = $argv[2];//需要执行的配置文件名
        }else{
            die('Error executing command script');
        }

        self::$conf_name =  $argv[2].'.ini.php';//配置文件名称

        $settings = parse_ini_file(self::$conf_name,true);


        self::$redis_list_key = $settings[$input_name.'_config']['redis_list_ket'];//redis队列

        self::$config_key = $settings[$input_name.'_config']['prix_name'];//前缀

        self::$pid_name = $settings[self::$config_key.'config']['pid_name'];



        if($input_name != self::$pid_name) die('The configured file name could not be found');//找不到配置的文件名

        try{
            $deamon = new Deamon('',self::$pid_name);
            $deamon->addJobs(array(
                'function' => 'Deamon_test::syncLog',
                'argv' => 'Go'
            ));
            $deamon->run($argv);
        }catch (\Exception $e){
             self::$log->write($e->getMessage()."\r\n");
            die('Error executing command script');
        }

    }

    public static function getI()
    {
        try{

             self::$log = new Log("com-sh-log");

            self::$settings = parse_ini_file(self::$conf_name,true);

            if(!self::$settings[self::$config_key.'db']) throw new \Exception('The database connection parameter configuration does not exist');

            if(!self::$settings[self::$config_key.'redis']) throw new \Exception('The redis connection parameter configuration does not exist');

            self::$redis = RedisH::getInstance(self::$settings[self::$config_key.'redis']);

            /*    self::$db = Db::getInstance($this->settings[self::$config_key.'db']);*/
            self::$db = new Db(self::$settings[self::$config_key.'db']);

        }catch (\Exception $e){

            self::$log->write($e->getMessage()."\r\n");
            die('Program abort');
        }
    }

    /**
     * 数据同步接口
     */
    public static function syncLog()
    {

        set_time_limit(0);
        ignore_user_abort(true);

        self::getI();//初始化连接对象
        $i = 0;//无数数据时 计数器
        while(true){
            try{
                $data = self::$redis->rpop(self::$redis_list_key);

                if($data){
                    if(!self::$db) self::getI();//如果同步数据过程 数据库终断 需要从新链接
                     $data = json_decode($data,true);
                    //接口数据缺少必传信息
                    if(!isset($data['data_table'])) throw new \Exception('Interface data is missing mandatory information');

                    if(!isset($data['data_json'])) throw new \Exception('Interface data cannot be empty');//上传数据不能为空


                    self::$tableName = $data['data_table'];
                  //$dacp->setTableName($data['data_table']);//设置表明
                    $fields     =  array_keys($data['data_json']);
                    //处理表字段
                    $fields_k = array_map("Deamon_test::joinStr",$fields);

                    $fieldsvals =  array(implode(",",$fields_k),":" . implode(",:",$fields));
                    $sql 		= "INSERT INTO ".$data['data_table']." (".$fieldsvals[0].") VALUES (".$fieldsvals[1].")";

                    $saved =  self::$db->query($sql, $data['data_json']);

                    if(!$saved) throw new \Exception("table:$data write ".json_encode($data)."Write failed");//写入某张表的数据 写入失败
                }else{

                    //注意：因为数据库的连接有超时时间 默认是8小时 所以当连接即将失效的时候 主动区连接一次
                    if($i >= 20000){//默认设置一半时间 8小时 28800秒
                        //
                        self::$db->close();//先关闭链接
                       self::getI();//从新连接
                        $i = 0;
                    }else{
                        ++$i;
                    }
                    sleep(2);//队列中没有消息时，睡眠2s，让出cpu给其他进程
                }

            }catch(\Exception $e){
               // var_dump($e->getMessage());die;
                //write log
                self::$log->write($e->getMessage()."\r\n");
            }
        }

    }


    //拼接表明 防止特殊字段
    public static function joinStr($filed)
    {
        return self::$tableName.".".$filed;
    }

}




(new Deamon_test)->run($argv);



