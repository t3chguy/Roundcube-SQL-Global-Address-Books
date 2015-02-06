<?php

class sql_global_addressbooks_Plugin extends PHPUnit_Framework_TestCase
{

    function setUp()
    {
        include_once dirname(__FILE__) . '/../sql_global_backend.php';
    }

    /**
     * Plugin object construction test
     */
    function test_constructor()
    {
        $rcube  = rcube::get_instance();
        $plugin = new sql_global_addressbooks($rcube->api);

        $this->assertInstanceOf('sql_global_addressbooks', $plugin);
        $this->assertInstanceOf('rcube_plugin', $plugin);
    }
}

