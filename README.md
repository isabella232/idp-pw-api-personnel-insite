# ARCHIVED: This library is no longer maintained.

# idp-pw-api-personnel-insite
IdP Password Management personnel component for Insite 

# Summary
This project has one class (*Insite*) with three public methods which 
use the Insite People Search php class to get person data from Insite.
Each of these functions attaches that data to a PersonnelUser instance
which it then returns.

The public methods are ...

  * findByEmployeeId($employeeId)
  * findByUsername($username)
  * findByEmail($email)

## Run the Unit Tests

```
$ cd tests
$ ..\vendor\bin\phpunit .
```
