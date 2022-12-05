<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

namespace szh\db\connector;

use PDO;
use think\db\Connection as BaseConnection;

/**
 * Oracle数据库驱动
 */
class DM extends BaseConnection
{

    protected $builder = 'szh\\db\\builder\\DM';

    // PDO连接参数
    protected $params = [
        PDO::ATTR_CASE              => PDO::CASE_LOWER,
        PDO::ATTR_ERRMODE           => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_ORACLE_NULLS      => PDO::NULL_NATURAL,
        PDO::ATTR_STRINGIFY_FETCHES => true
    ];

    /**
     * 解析pdo连接的dsn信息
     * @access protected
     * @param array $config 连接信息
     * @return string
     */
    protected function parseDsn($config)
    {
        $dsn = 'dm:';
        if (!empty($config['hostname'])) {
            $dsn .= 'host=' . $config['hostname'];
        }
        // dm:host=172.17.218.20;charset=utf8
        if (!empty($config['charset'])) {
            $dsn .= ';charset=' . $config['charset'];
        }
        return $dsn;
    }

    /**
     * 取得数据表的字段信息
     * @access public
     * @param string $tableName
     * @return array
     */
    public function getFields($tableName)
    {
        $schema_name = str_replace('.', '', strtoupper(config('database.prefix')));

        $this->initConnect(true);
        list($tableName) = explode(' ', $tableName);

        $tablenameArr = explode('.', $tableName, 2);
        if (count($tablenameArr) > 1) {
            $tableName = $tablenameArr[1];
        }
        $sql             = "select a.column_name,data_type,DECODE (nullable, 'Y', 0, 1) notnull,data_default, DECODE (A .column_name,b.column_name,1,0) pk from all_tab_columns a,(select column_name from all_constraints c, all_cons_columns col where c.constraint_name = col.constraint_name and c.constraint_type = 'P' and c.owner='$schema_name' and c.table_name = '" . strtoupper($tableName) . "' ) b where table_name = '" . strtoupper($tableName) . "' and a.owner='$schema_name' and a.column_name = b.column_name (+)";
        $pdo             = $this->linkID->query($sql);
        $result          = $pdo->fetchAll(PDO::FETCH_ASSOC);
        $info            = [];

//        if ($tableName == 'storeroom') {
//            echo $sql;
//            print_r($result);
//        }

//        select a.column_name,data_type,DECODE (nullable, 'Y', 0, 1) notnull,data_default, DECODE (a.column_name,b.column_name,1,0) pk from all_tab_columns a,(select column_name from all_constraints c, all_cons_columns col where c.constraint_name = col.constraint_name and c.constraint_type = 'P' and c.table_name = 'INSPECTION_ARCHIVES_RECORDS' ) b where table_name = 'INSPECTION_ARCHIVES_RECORDS' and a.column_name = b.column_name (+);
//        select a.column_name,data_type,DECODE (nullable, 'Y', 0, 1) notnull,data_default, DECODE (a.column_name,b.column_name,1,0) pk from all_tab_columns a,(select column_name from all_constraints c, all_cons_columns col where c.constraint_name = col.constraint_name and c.constraint_type = 'P' and c.table_name = 'INSPECTION_ARCHIVES_RECORDS' ) b where table_name = 'INSPECTION_ARCHIVES_RECORDS'  and a.column_name = b.column_name (+);

//        if ('inspection_archives_records' === $tableName) {
//            echo $sql . chr(10).chr(13);
//            print_r($result);
//        }
//        document_manager.INSPECTION_ARCHIVES_RECORDS
//    select column_name from all_constraints c, all_cons_columns col where c.constraint_name = col.constraint_name and c.constraint_type = 'P' and c.table_name = 'user';
//select column_name from all_constraints c, all_cons_columns col where c.constraint_name = col.constraint_name and c.constraint_type = 'P' and c.table_name = 'INSPECTION_ARCHIVES_RECORDS' and c.OWNER='DOCUMENT_MANAGER';



        if ($result) {
            foreach ($result as $key => $val) {
                $val                       = array_change_key_case($val);
                $info[$val['column_name']] = [
                    'name'    => $val['column_name'],
                    'type'    => $val['data_type'],
                    'notnull' => $val['notnull'],
                    'default' => $val['data_default'],
                    'primary' => $val['pk'],
                    'autoinc' => $val['pk'],
                ];
            }
        }
        return $this->fieldCase($info);
    }
    /**
     * 取得数据库的表信息（暂时实现取得用户表信息）
     * @access   public
     * @param string $dbName
     * @return array
     */
    public function getTables($dbName = '')
    {
        $pdo    = $this->linkID->query("select table_name from all_tables");
        $result = $pdo->fetchAll(PDO::FETCH_ASSOC);
        $info   = [];
        foreach ($result as $key => $val) {
            $info[$key] = current($val);
        }
        return $info;
    }

//    /**
//     * 获取最近插入的ID
//     * @access public
//     * @param string  $sequence     自增序列名
//     * @return string
//     */
//    public function getLastInsID($sequence = null)
//    {
//        if ($sequence) {
//            $sequence .= '.';
//        }
//        echo "select {$sequence}currval as id from dual" . chr(10).chr(13);
//
//        $pdo    = $this->linkID->query("select {$sequence}currval as id from dual");
//        $result = $pdo->fetchColumn();
//        return $result;
//    }

    /**
     * SQL性能分析
     * @access protected
     * @param string $sql
     * @return array
     */
    protected function getExplain($sql)
    {
        return [];
    }

    protected function supportSavepoint()
    {
        return true;
    }
}
