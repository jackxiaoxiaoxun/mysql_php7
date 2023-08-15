<?php
/**
 * mysql 在php7 中的兼容层
 * @author jk.qi
 * 
 */
if (! function_exists('mysql_connect')) {

    function mysql_pconnect($server, $user, $pass, $new_link = null, $client_flags = null)
    {
        mysql_php7::getSelf()->config[] = [
            'dsn' => "mysql:host=$server;",
            'user' => $user,
            'pass' => $pass,
            'option' => [
                \PDO::ATTR_PERSISTENT
            ]
        ];
        return count(mysql_php7::getSelf()->config);
    }

    function mysql_connect($server, $user, $pass, $new_link = null, $client_flags = null)
    {
        mysql_php7::getSelf()->config[] = [
            'dsn' => "mysql:host=$server;",
            'user' => $user,
            'pass' => $pass,
            'option' => []
        ];
        return count(mysql_php7::getSelf()->config);
    }

    function mysql_close($link_identifier = null)
    {
        if ($link_identifier === null) {
            $link_identifier = count(mysql_php7::getSelf()->config);
        }
        unset(mysql_php7::getSelf()->pdo[$link_identifier - 1]);
        return true;
    }

    function mysql_select_db($dbname, $link_identifier = null)
    {
        if ($link_identifier === null) {
            $link_identifier = count(mysql_php7::getSelf()->config);
        }
        mysql_php7::getSelf()->config[$link_identifier - 1]['dsn'] .= "dbname=$dbname;";
        return true;
    }

    function mysql_set_charset($charset, $link_identifier = null)
    {
        if ($link_identifier === null) {
            $link_identifier = count(mysql_php7::getSelf()->config);
        }
        
        mysql_php7::getSelf()->config[$link_identifier - 1]['dsn'] .= "charset=$charset;";
        return true;
    }

    function mysql_get_server_info($link_identifier = null)
    {
        if ($link_identifier === null) {
            $link_identifier = count(mysql_php7::getSelf()->config);
        }
        return mysql_php7::getSelf()->getPdo($link_identifier)->getAttribute(\PDO::ATTR_SERVER_VERSION);
    }

    function mysql_get_client_info($link_identifier = null)
    {
        if ($link_identifier === null) {
            $link_identifier = count(mysql_php7::getSelf()->config);
        }

        return mysql_php7::getSelf()->getPdo($link_identifier)->getAttribute(\PDO::ATTR_CLIENT_VERSION);
    }

    function mysql_real_escape_string($data,  $link_identifier = null )
    {
        return addslashes($data);
    }

    function mysql_query($sql, $link_identifier = null)
    {
        if ($link_identifier === null) {
            $link_identifier = count(mysql_php7::getSelf()->config);
        }
        
        $pdo = mysql_php7::getSelf()->getPdo($link_identifier);
        try{
            return $pdo->PDOStatement = $pdo->query($sql);
        } catch (PDOException $e)
        {
            return null;
        }
    }

    function mysql_fetch_assoc(\PDOStatement $result)
    {
        return $result->fetch(\PDO::FETCH_ASSOC);
    }

    function mysql_fetch_row(\PDOStatement $result)
    {
        return $result->fetch(\PDO::FETCH_NUM);
    }

    function mysql_fetch_object(\PDOStatement $result)
    {
        return $result->fetch(\PDO::FETCH_OBJ);
    }

    function mysql_fetch_array(\PDOStatement $result, $result_type = 1)
    {
        $arr = [
            1 => \PDO::FETCH_ASSOC,
            2 => \PDO::FETCH_NUM,
            3 => \PDO::FETCH_BOTH
        ];
        return $result->fetch($arr[$result_type]);
    }

    function mysql_ping($link_identi)
    {
        return true;
    }

    function mysql_error($link_identifier = null)
    {
        if ($link_identifier === null) {
            $link_identifier = count(mysql_php7::getSelf()->config);
        }
        return  mysql_php7::getSelf()->getPdo($link_identifier)->errorInfo() [2];
    }

    function mysql_errno($link_identifier = null)
    {
        if ($link_identifier === null) {
            $link_identifier = count(mysql_php7::getSelf()->config);
        }
        
        return mysql_php7::getSelf()->getPdo($link_identifier)->errorCode();
    }


    function mysql_affected_rows($link_identifier = null)
    {
        if ($link_identifier === null) {
            $link_identifier = count(mysql_php7::getSelf()->config);
        }
        return mysql_php7::getSelf()->getPdo($link_identifier)->PDOStatement->rowCount();
    }

    function mysql_result(\PDOStatement $result, $num, $col = 0)
    {
        if (is_numeric($col)) {
            $fetch_style = \PDO::FETCH_NUM;
        } else {
            $fetch_style = \PDO::FETCH_ASSOC;
        }
        $r = $result->fetch($fetch_style, \PDO::FETCH_ORI_ABS, $num);
        return $r[$col];
    }

    function mysql_num_rows(\PDOStatement $result)
    {
        return $result->rowCount();
    }

    function mysql_num_fields(\PDOStatement $result)
    {
        return $result->columnCount();
    }

    function mysql_free_result(\PDOStatement $result)
    {
        return true;
    }

    function mysql_insert_id($link_identifier = null)
    {
        if ($link_identifier === null) {
            $link_identifier = count(mysql_php7::getSelf()->config);
        }
        return mysql_php7::getSelf()->getPdo($link_identifier)->lastInsertId();
    }

    function mysql_fetch_field()
    {
        return false;
    }

    class mysql_php7
    {

        /** @var \PDO */
        public $pdo = [];

        public $config = [];

        private static $self;

        /**
         *
         * @return mysql_php7
         */
        public static function getSelf()
        {
            if (empty(self::$self)) {
                self::$self = new static();
            }
            return self::$self;
        }

        /**
         *
         * @param number $id            
         * @return PDO
         */
        public function getPdo($id = 1)
        {
            $id -= 1;
            if (empty($this->pdo[$id])) {
                $this->pdo[$id] = new \PDO(
                    $this->config[$id]['dsn'],
                    $this->config[$id]['user'],
                    $this->config[$id]['pass'],
                    $this->config[$id]['option']
                    );
            }
            return $this->pdo[$id];
        }
    }
}
