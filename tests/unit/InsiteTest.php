<?php

use \Sil\IdpPw\Common\Personnel\Insite;

use GuzzleHttp\Subscriber\Mock;
use GuzzleHttp\Message\Response;
use GuzzleHttp\Stream\Stream;
use GuzzleHttp\Client;

use GuzzleHttp\Ring\Client\MockHandler;
use GuzzleHttp\Ring\Future\CompletedFutureArray;

class InsiteTest extends PHPUnit_Framework_TestCase
{

    public $api_base_url = 'http://www.anywhere.org/peoplesearch/';
    public $api_key = 'abc123';
    public $api_sig = 'abcd1234';

    public $userData1 = [
        "first_name" => "Test",
        "last_name" => "User",
        "email" => "test_user@domain.org",
        "giseispersonid" => 123,
        "job_title" => "SW Test User",
        "published_phone_number" => "+1 1234567890",
        "username" => "TEST_USER",
        "manager_name" => "John Smithy",
        "manager_email" => "john_smithy@domain.org",
        "manager_giseispersonid" => 111,
        "spouse_name" => "Test User2",
        "spouse_email" => "test_user2@domain.org",
        "spouse_giseisperson_id" => 124,
        "role_type" => "Individual Contributor",
        "member_status" => "Member"
    ];

    public $userData2 = [
        "first_name" => "Test",
        "last_name" => "User2",
        "email" => "test_user2@domain.org",
        "giseispersonid" => 124,
        "job_title" => "SW Test User",
        "published_phone_number" => "+1 7771234567",
        "username" => "TEST_USER2",
        "manager_name" => "Jack Brown",
        "manager_email" => "jack_brown@domain.org",
        "manager_giseispersonid" => 222,
        "spouse_name" => "Test User",
        "spouse_email" => "test_user@domain.org",
        "spouse_giseisperson_id" => 123,
        "role_type" => "Individual Contributor",
        "member_status" => "Member"
    ];

    public function getConfig() {
        return [
            'api_base_url' => $this->api_base_url,
            'api_key' => $this->api_key,
            'api_secret' => $this->api_sig
        ];
    }

    public function testFindByEmployeeId_BadConfigException1()
    {
        $insite = new Insite();
        $employeeId = '123456';
        
        try {
            $results = $insite->findByEmployeeId($employeeId);
            $this->fail(" *** Didn't get exception for invalid config");
        } catch (\Exception $e) {
            $msg = " *** Didn't get the right exception code";
            $this->assertEquals(1456781489, $e->getCode(), $msg);
        }
    }

    public function testFindByEmployeeId_BadConfigException2()
    {
        $insite = new Insite();
        $insite->insitePeopleSearchBaseUrl = "anywhere@company.org";
        $employeeId = '123456';
        
        try {
            $results = $insite->findByEmployeeId($employeeId);
            $this->fail(" *** Didn't get exception for invalid config");
        } catch (\Exception $e) {
            $msg = " *** Didn't get the right exception code";
            $this->assertEquals(1456781490, $e->getCode(), $msg);
        }
    }

    public function testFindByEmployeeId_BadConfigException3()
    {
        $insite = new Insite();
        $insite->insitePeopleSearchBaseUrl = "anywhere@company.org";
        $insite->insitePeopleSearchApiKey = "abc123";
        $employeeId = '123456';
        
        try {
            $results = $insite->findByEmployeeId($employeeId);
            $this->fail(" *** Didn't get exception for invalid config");
        } catch (\Exception $e) {
            $msg = " *** Didn't get the right exception code";
            $this->assertEquals(1456781491, $e->getCode(), $msg);
        }
    }

    private function getMockReturnValue()
    {
       return [
                [
                    'next' => [
                        '$ref' => 'https://some.site.org/peoplesearch/simplesearch/test_user?page=1'
                    ],  
                    'items' => [
                        [
                          "first_name" => "Test",
                          "last_name" => "User",
                          "email" => "test_user@domain.org",
                          "giseispersonid" => 123,
                          "job_title" => "SW Test User",
                          "published_phone_number" => "+1 1234567890",
                          "username" => "TEST_USER",
                          "manager_name" => "John Smith",
                          "manager_email" => "john_smith@domain.org",
                          "manager_giseispersonid" => 111,
                          "spouse_name" => "Test User2",
                          "spouse_email" => "test_user2@domain.org",
                          "spouse_giseisperson_id" => 124,
                          "role_type" => "Individual Contributor",
                          "member_status" => "Member"
                        ]
                    ]
                ],
                ['giseispersonid',], // This is ignored
        ];
    }
    
    public function testFindByEmployeeId_OK()
    {
        $mockReturnValue = $this->getMockReturnValue();
        $insiteMock = $this->getMockBuilder('\Sil\IdpPw\Common\Personnel\Insite')
                           ->setMethods(array('callAdvancedSearch'))
                           ->getMock();
        $insiteMock->expects($this->any())
                   ->method('callAdvancedSearch')
                   ->will($this->returnValue($mockReturnValue));    

        $insiteMock->insitePeopleSearchBaseUrl = "some.site.org";
        $insiteMock->insitePeopleSearchApiKey = "abc123";
        $insiteMock->insitePeopleSearchApiSecret = "abcdef123456";
                   
        $employeeId = '123456';
        $results = $insiteMock->findByEmployeeId($employeeId);
        
        $expected = $mockReturnValue[0]['items'][0]['username'];
        $msg = " *** Bad results for username";
        $this->assertEquals($expected, $results->username, $msg); 
    }
    
    public function testFindByUsername_OK()
    {
        $mockReturnValue = $this->getMockReturnValue();
        $insiteMock = $this->getMockBuilder('\Sil\IdpPw\Common\Personnel\Insite')
                           ->setMethods(array('callAdvancedSearch'))
                           ->getMock();
        $insiteMock->expects($this->any())
                   ->method('callAdvancedSearch')
                   ->will($this->returnValue($mockReturnValue));    

        $insiteMock->insitePeopleSearchBaseUrl = "some.site.org";
        $insiteMock->insitePeopleSearchApiKey = "abc123";
        $insiteMock->insitePeopleSearchApiSecret = "abcdef123456";
                   
        $username = 'TEST_USER';
        $results = $insiteMock->findByUsername($username);
        
        $expected = $mockReturnValue[0]['items'][0]['email'];
        $msg = " *** Bad results for email";
        $this->assertEquals($expected, $results->email, $msg);  
    }
    
    public function testFindByEmail_OK()
    {
        $mockReturnValue = $this->getMockReturnValue();
        $insiteMock = $this->getMockBuilder('\Sil\IdpPw\Common\Personnel\Insite')
                           ->setMethods(array('callAdvancedSearch'))
                           ->getMock();
        $insiteMock->expects($this->any())
                   ->method('callAdvancedSearch')
                   ->will($this->returnValue($mockReturnValue));    

        $insiteMock->insitePeopleSearchBaseUrl = "some.site.org";
        $insiteMock->insitePeopleSearchApiKey = "abc123";
        $insiteMock->insitePeopleSearchApiSecret = "abcdef123456";
                   
        $email = "test_user@domain.org";
        $results = $insiteMock->findByEmail($email);
        
        $expected = $mockReturnValue[0]['items'][0]['manager_email'];
        $msg = " *** Bad results for manager email";
        $this->assertEquals($expected, $results->supervisorEmail, $msg);  
    }

}