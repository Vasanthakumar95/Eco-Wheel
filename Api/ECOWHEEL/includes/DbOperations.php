<?php 
    class DbOperations
    {
        private $con;

        function __construct()
        {
            require_once dirname(__FILE__)  .  '/DbConnect.php';

            $db = new DbConnect;

            $this->con = $db->connect();
        }





//-----------------------------------------------------DRIVER---------------------------------------------------------------------------------------------        

        //for creating Driver and error checking
        public function createDriver($id, $name, $username, $password, $ic, $email)
        {
            //account status will be active upon registering and inactive only if account is deleted
            $account_status = 'active';   

             if(!$this->isDriverExist($username))
             {
                 $stmt = $this->con->prepare("INSERT INTO driver (driver_id, driver_name, username, password, driver_ic_number, driver_email, account_status) VALUES (?,?,?,?,?,?,?)");
                 $stmt->bind_param("sssssss", $id, $name, $username, $password, $ic, $email, $account_status);    
                     if($stmt->execute())
                     {
                         return USER_CREATED;
                     }
                         else
                         {
                             return USER_FAILURE;
                         }
             } 
             return USER_EXIST;
        }

        //getting user details after authentication 
        public function getDriverByUsername($username)
        {
            $stmt = $this->con->prepare("SELECT driver_id, username FROM driver WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $stmt->bind_result($id, $username);
            $stmt->fetch();
            $user_details = array();
            $user_details['driver_id'] = $id;
            $user_details['username'] = $username;
            return $user_details;
        } 

         //for driver login and error checking
         public function driverLogin($username, $password)
         {   //use $this-> when refering to functions in the same class to avoid  undefined class error
             $hashed_password = $this->getCurrentDriverPasswordByUsername($username);
             if($this->isDriverExist($username))
             {
                 if(password_verify($password,$hashed_password))
                 {
                     return USER_AUTHENTICATED;
                 }else {
                     return USER_PASSWORD_DO_NOT_MATCH;
                 }
             }
             else {
                 return USER_NOT_FOUND;
             }
         }

        //update current driver location
        public function updateDriverCurrentLocation($username, $latitude, $longitude)
        {
            if($this->isDriverExist($username))
            {
                $stmt = $this->con->prepare("UPDATE driver SET driver_c_lat=?, driver_c_lng=? WHERE username = ?");
                $stmt->bind_param("dds", $latitude ,$longitude, $username);
                if($stmt->execute())
                    return true;
                return false;    
            }
        }       
        
        //geting driver password and compare
        public function getDriverLocationByUsername($username){
           if ($this->isDriverExist($username)) 
           {
            $stmt = $this->con->prepare("SELECT driver_c_lat, driver_c_lng FROM driver WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $stmt->bind_result($lat, $lng);
            $stmt->fetch();

            $user_details = array();
            $user_details['driver_lat'] = $lat;
            $user_details['driver_lng'] = $lng;
            return $user_details;
           }
           else {
               return USER_NOT_FOUND;
           }
            
        } 



        //geting driver password and compare
        private function getCurrentDriverPasswordByUsername($username){
            $stmt = $this->con->prepare("SELECT password FROM driver WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $stmt->bind_result($password);
            $stmt->fetch();
            return $password;
        }         

        //checking for driver exists or not
        private function isDriverExist($username)
        {
            $stmt = $this->con->prepare("SELECT username FROM driver WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $stmt->store_result();
            return $stmt->num_rows > 0;
        }          
//-----------------------------------------------------DRIVER---------------------------------------------------------------------------------------------        



//-----------------------------------------------------PASSENGER---------------------------------------------------------------------------------------------        

        //for creating Passenger and error checking
        public function createPassenger($id, $name, $username, $password, $ic, $email)
        {
            //account status will be active upon registering and inactive only if account is deleted or barred
            $account_status = 'active';   

             if(!$this->isPassengerExist($username))
             {
                 $stmt = $this->con->prepare("INSERT INTO passenger (passenger_id, passenger_name, username, password, passenger_ic_number, passenger_email, account_status) VALUES (?,?,?,?,?,?,?)");
                 $stmt->bind_param("sssssss", $id, $name, $username, $password, $ic, $email, $account_status);    
                     if($stmt->execute())
                     {
                         return USER_CREATED;
                     }
                         else
                         {
                             return USER_FAILURE;
                         }
             } 
             return USER_EXIST;
        }


        //for driver login and error checking
        public function passengerLogin($username, $password)
        {   //use $this-> when refering to functions in the same class to avoid  undefined class error
            $hashed_password = $this->getCurrentPassengerPasswordByUsername($username);
            if($this->isPassengerExist($username))
            {
                if(password_verify($password,$hashed_password))
                {
                    return USER_AUTHENTICATED;
                }else {
                    return USER_PASSWORD_DO_NOT_MATCH;
                }
            }
            else {
                return USER_NOT_FOUND;
            }
        }

        //update current passenger location
        public function updatePassengerCurrentLocation($username, $latitude, $longitude)
        {
            if($this->isPassengerExist($username))
            {
                $stmt = $this->con->prepare("UPDATE passenger SET passenger_c_lat=?, passenger_c_lng=? WHERE username = ?");
                $stmt->bind_param("dds", $latitude ,$longitude, $username);
                if($stmt->execute())
                    return true;
                return false;    
            }
        } 

        //getting passenger details after authentication 
        public function getPassengerByUsername($username)
        {
            $stmt = $this->con->prepare("SELECT passenger_id, username FROM passenger WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $stmt->bind_result($id, $username);
            $stmt->fetch();
            $user_details = array();
            $user_details['passenger_id'] = $id;
            $user_details['username'] = $username;
            return $user_details;
        }         
        
        //geting passenger password and compare
        private function getCurrentPassengerPasswordByUsername($username){
            $stmt = $this->con->prepare("SELECT password FROM passenger WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $stmt->bind_result($password);
            $stmt->fetch();
            return $password;
        }

        //checking for passenger exists or nt
        private function isPassengerExist($username)
        {
            $stmt = $this->con->prepare("SELECT username FROM passenger WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $stmt->store_result();
            return $stmt->num_rows > 0;
        }
//-----------------------------------------------------PASSENGER---------------------------------------------------------------------------------------------  
        

        //hibernate



//--------------------------------------------------------------------------------------------------------------------
       
        /* 
    
        "UPDATE user SET username= ?, role = ? WHERE id = ?"

        INSERT INTO driver(driver_c_lat, driver_c_lng) VALUES(?,?) WHERE username = $username
        */

        //for creating user and error checking
        public function createUser($id, $username, $password, $role)
        {
            
            if(!$this->isUsernameExist($username))
            {
                $stmt = $this->con->prepare("INSERT INTO user (id, username, password, role) VALUES (?,?,?,?)");
                $stmt->bind_param("ssss", $id, $username, $password, $role);    
                    if($stmt->execute())
                    {
                        return USER_CREATED;
                    }
                        else
                        {
                            return USER_FAILURE;
                        }
            } 
            return USER_EXIST;
        }

        //updating user details
         public function updateUser($username, $role, $id)
         {
             $stmt = $this->con->prepare("UPDATE user SET username= ?, role = ? WHERE id = ?");
             $stmt->bind_param("ssi", $username, $role, $id);
             if($stmt->execute())
                 return true;
             return false;
         }

        //getting user details after authentication 
        public function getUserByUsername($username)
        {
            $stmt = $this->con->prepare("SELECT id, username, role FROM user WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $stmt->bind_result($id, $username, $role);
            $stmt->fetch();
            $user_details = array();
            $user_details['id'] = $id;
            $user_details['username'] = $username;
            $user_details['role'] = $role;
            return $user_details;
        }   

        //getting all user details
        public function getAllUserDetails()
        {
            $stmt = $this->con->prepare("SELECT id, username, role FROM user;");
            $stmt->execute();
            $stmt->bind_result($id, $username, $role);
            $users = array();
            while($stmt->fetch())
            {
                $user_details = array();
                $user_details['id'] = $id;
                $user_details['username'] = $username;
                $user_details['role'] = $role;
                array_push($users , $user_details);
            }
            return $users;
        }
        
        

        //checking for user exists or nt by using id
        private function isIdExist($id)
        {
            $stmt = $this->con->prepare("SELECT id FROM user WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->store_result();
            return $stmt->num_rows > 0;
        }

       

        //deleting user by id referencing
        public function deleteUser($id)
        {
            $stmt = $this->con->prepare('DELETE FROM user WHERE id = ?');
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) 
                return true;
            return false;
               
            

            
        }

        //updating password by username reference
        public function updatePassword($currentpassword, $newpassword, $username)
        {
            $hashed_password = $this->getCurrentUserPasswordByUsername($username);

            if(password_verify($currentpassword,$hashed_password))
            {
                $hash_password = password_hash($newpassword, PASSWORD_DEFAULT);
                $stmt = $this->con->prepare("UPDATE user SET password = ? WHERE username = ?");
                $stmt->bind_param("ss",$hash_password, $username);

                if($stmt->execute()){
                    return PASSWORD_CHANGED;
                }else {
                    return PASSWORD_NOT_CHANGED;
                }
                    

            }else
            {
                return PASSWORD_DO_NOT_MATCH;
            }

        }
    }
    
   