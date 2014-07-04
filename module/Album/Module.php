<?php
namespace Album;

use Album\Model\Album;
use Album\Model\AlbumTable;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use ZendDiagnostics\Check\DirWritable;
use ZendDiagnostics\Check\ExtensionLoaded;
use ZendDiagnostics\Check\ProcessRunning;
use ZendDiagnostics\Check\PhpVersion;

class Module
{
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                'Album\Model\AlbumTable' =>  function($sm) {
                    $tableGateway = $sm->get('AlbumTableGateway');
                    $table = new AlbumTable($tableGateway);

                    return $table;
                },
                'AlbumTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Album());

                    return new TableGateway('album', $dbAdapter, null, $resultSetPrototype);
                },
            ),
        );
    }


    public function getDiagnostics()
    {
        return array(
            'Cache & Log Directories Available' => function() {
                $diagnostic = new DirWritable(array(
                    __DIR__ . '/../../data/cache',
                    __DIR__ . '/../../data/log'
                ));
                return $diagnostic->check();
            },'Check PHP extensions' => function(){
                $diagnostic = new ExtensionLoaded(array(
                    'json',
                    'pdo',
                    'pdo_pgsql',
                    'intl',
                    'session',
                    'pcre',
                    'zlib',
                    'apc',
                    'apcu',
                    'Zend OPcache'
                ));
                return $diagnostic->check();
            },'Check Apache is running' => function(){
                $diagnostic = new ProcessRunning('apache2');
                return $diagnostic->check();
            },'Check PostgreSQL is running' => function(){
                $diagnostic = new ProcessRunning('postgresql');
                return $diagnostic->check();
            },'Check Memcached is running' => function(){
                $diagnostic = new ProcessRunning('beanstalkd');
                return $diagnostic->check();
            },'Check PHP Version' => function(){
                $diagnostic = new PhpVersion('5.3.0', '>=');
                return $diagnostic->check();
            }
        );
    }
}
