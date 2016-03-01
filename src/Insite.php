<?php

namespace Sil\IdpPw\Common\Personnel;

use silintl\InsitePeopleSearch as IPSearch;
use Sil\IdpPw\Common\Personnel\PersonnelInterface;
use Sil\IdpPw\Common\Personnel\PersonnelUser;
use Sil\IdpPw\Common\Personnel\NotFoundException;
use yii\base\Component;

class Insite extends Component implements PersonnelInterface
{
 
    /**
     * @var string
     */
    public $insitePeopleSearchBaseUrl;
 
    /**
     * @var string
     */
    public $insitePeopleSearchApiKey;
 
    /**
     * @var string
     */
    public $insitePeopleSearchApiSecret;    

    /**
     * @return array
     * @throws \Exception
     */
    private function getInsiteApiConfig()
    {
        $config = [];
        
        if (is_string($this->insitePeopleSearchBaseUrl) &&
              $this->insitePeopleSearchBaseUrl) {
            $config['api_base_url'] = $this->insitePeopleSearchBaseUrl;
        } else {
            throw new \Exception("Invalid Base Url for the Insite People Search. " . 
                                 " A non-empty string is required.", 1456781489);
        }
        
        if (is_string($this->insitePeopleSearchApiKey) &&
              $this->insitePeopleSearchApiKey) {
            $config['api_key'] = $this->insitePeopleSearchApiKey;
        } else {
            throw new \Exception("Invalid API Key for the Insite People Search. " . 
                                 " A non-empty string is required.", 1456781490);
        }
        
        if (is_string($this->insitePeopleSearchApiSecret) &&
              $this->insitePeopleSearchApiSecret) {
            $config['api_secret'] = $this->insitePeopleSearchApiKey;
        } else {
            throw new \Exception("Invalid API Secret for the Insite People Search. " . 
                                 " A non-empty string is required.", 1456781491);
        }
              
        return $config;
    }    

  
    /**
     * Separated out for mocking for unit tests
     * @param array $query
     * @param array $query
     * @return array
     */      
    public function callAdvancedSearch($query, $config)
    {
        return IPSearch::advancedSearch($query, $config);
    }
  
    /**
     * @param array $query
     * @return PersonnelUser
     * @throws NotFoundException
     */  
    private function findByQuery($query) 
    {
      
        $config = $this->getInsiteApiConfig();
        $results = $this->callAdvancedSearch($query, $config);
        
        if ( ! $results[0]['items']) {
            throw new NotFoundException();
        }
        
        $userData = $results[0]['items'][0];
        
        $pUser = new PersonnelUser();
        $pUser->firstName = $userData['first_name'];
        $pUser->lastName = $userData['last_name'];
        $pUser->email = $userData['email'];
        $pUser->employeeId = $userData['giseispersonid'];
        $pUser->username = $userData['username'];
        $pUser->supervisorEmail = $userData['manager_email'];
        $pUser->spouseEmail = $userData['spouse_email'];
        
        return $pUser;      
    }
  
    /**
     * @param mixed $employeeId
     * @return PersonnelUser
     */
    public function findByEmployeeId($employeeId)
    {            
        $query = [
          "giseispersonid" => $employeeId,
        ];
        
        return $this->findByQuery($query);        
    }
  
    /**
     * @param mixed $employeeId
     * @return PersonnelUser
     */
    public function findByUsername($username)
    {            
        $query = [
          "username" => $username,
        ];
        
        return $this->findByQuery($query);        
    }
  
    /**
     * @param mixed $employeeId
     * @return PersonnelUser
     */
    public function findByEmail($email)
    {            
        $query = [
          "email" => $email,
        ];
        
        return $this->findByQuery($query);        
    }
}