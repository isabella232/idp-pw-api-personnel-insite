<?php
namespace Sil\IdpPw\Common\Personnel;

use silintl\InsitePeopleSearch\InsitePeopleSearch as IPSearch;
use Sil\IdpPw\Common\Personnel\NotFoundException;
use Sil\IdpPw\Common\Personnel\PersonnelInterface;
use Sil\IdpPw\Common\Personnel\PersonnelUser;
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
            $config['api_secret'] = $this->insitePeopleSearchApiSecret;
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
     * @throws \Exception
     */  
    private function findByQuery($query) 
    {
      
        $config = $this->getInsiteApiConfig();
        $results = $this->callAdvancedSearch($query, $config);

        /*
         * Make sure only one result was found, otherwise throw exception
         */
        if ( ! isset($results[0]['items']) || ! is_array($results[0]['items']) || count($results[0]['items']) === 0) {
            throw new NotFoundException();
        } elseif (count($results[0]['items']) > 1) {
            throw new \Exception(
                'More than one personnel record found for query ' . $this->getQueryAsString($query),
                1472754578
            );
        }

        $userData = $results[0]['items'][0];

        try {
            $this->assertRequiredAttributesPresent($userData);
            $pUser = new PersonnelUser();
            $pUser->firstName = $userData['first_name'];
            $pUser->lastName = $userData['last_name'];
            $pUser->email = $userData['email'];
            $pUser->employeeId = $userData['giseispersonid'];
            $pUser->username = $userData['username'];
            $pUser->supervisorEmail = isset($userData['manager_email']) ? $userData['manager_email'] : null;
            $pUser->spouseEmail = isset($userData['spouse_email']) ? $userData['spouse_email'] : null;

            return $pUser;
        } catch (\Exception $e) {
            throw new \Exception(
                $e->getMessage() . ' for query: ' . $this->getQueryAsString($query),
                1472567249
            );
        }
    }

    private function assertRequiredAttributesPresent($userData)
    {
        $required = ['first_name', 'last_name', 'email', 'giseispersonid', 'username'];

        foreach ($required as $requiredAttr) {
            if ( ! array_key_exists($requiredAttr, $userData)) {
                throw new \Exception(
                    'Personnel attributes missing attribute: ' . $requiredAttr,
                    1472567011
                );
            }
        }
    }
  
    /**
     * @param string $employeeId
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
     * @param string $username
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
     * @param string $email
     * @return PersonnelUser
     */
    public function findByEmail($email)
    {            
        $query = [
          "email" => $email,
        ];
        
        return $this->findByQuery($query);        
    }

    /**
     * Convert query array to simple string
     * @param array $query
     * @return string
     */
    public function getQueryAsString($query)
    {
        $string = '';
        foreach ($query as $key => $value) {
            $string .= $key . '=' . $value;
        }
        return $string;
    }
}