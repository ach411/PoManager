<?php

namespace Ach\PoManagerBundle\ConnectProdDatabase;

class AchConnectProdDatabase
{

    protected $external_sql_host; 
	protected $external_sql_port; 
    protected $external_sql_db_name;
    protected $external_sql_user;
    protected $external_sql_pass;


    public function __construct($external_sql)
    {
        $this->external_sql_host    = $external_sql['host'];
        $this->external_sql_port    = $external_sql['port'];
		$this->external_sql_db_name = $external_sql['db_name'];
		$this->external_sql_user	= $external_sql['user'];
		$this->external_sql_pass	= $external_sql['pass'];
    }

    /**
    * simply get the PDO instance
    * essentially avoiding the need for re-writing the present lines of code when prod database query must be done
    * 
    * @param void
    * @return the PDO instance
    */
    public function getPDO()
    {
        try {
            $bdd = new \PDO('mysql:host='.$this->external_sql_host.';port='.$this->external_sql_port.';dbname='.$this->external_sql_db_name, $this->external_sql_user, $this->external_sql_pass);
        }
        catch(\Exception $e) {
            // re-throw exception
            throw $e;
        }

        return $bdd;
        
    }

}