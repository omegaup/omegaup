<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

require_once '../ApiLoader.php';
require_once 'Utils.php';


class ApiLoaderTest extends PHPUnit_Framework_TestCase
{
    
    public function testLoginLoad()
    {
        // Set context to load login
        RequestContext::set("apicmd", "Login");
        RequestContext::set("username", Utils::GetContestantUsername());
        RequestContext::set("password", Utils::$contestant->getPassword());
        
        $return_value = ApiLoader::load('test');
                        
        $this->assertNotEmpty($return_value["auth_token"]);
    }
    
    public function testApiError()
    {
        // Set context to load login
        RequestContext::set("apicmd", "Login");
        RequestContext::set("username", Utils::GetContestantUsername());
        RequestContext::set("password", "invalidpwd");
        
        $return_value_json = ApiLoader::load('test');
                        
        $this->assertStringStartsWith("{\"status\":\"error\",", $return_value_json);
        $this->assertStringEndsWith("\"errorcode\":101,\"header\":\"HTTP\/1.1 403 FORBIDDEN\"}", $return_value_json);
    }
    
    public function testInvalidCmd()
    {
        // Set context to load login
        RequestContext::set("apicmd", "NotAnApi");
        $return_value_json = ApiLoader::load('test');
                   
        $this->assertEquals('{"status":"error","error":"Site requested not found","errorcode":107,"header":"HTTP\/1.1 404 NOT FOUND"}', $return_value_json);
    }
}

?>
