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
        public function createDriver($id, $name, $username, $password, $ic, $email, $car_model, $car_number, $driver_contact)
        {
            //account status will be active upon registering and inactive only if account is deleted
            $account_status = 'active';   

             if(!$this->isDriverExist($username))
             {
                 $stmt = $this->con->prepare("INSERT INTO driver (driver_id, driver_name, username, password, driver_ic_number, driver_email, driver_car_number, driver_car_model, contact_number, account_status) VALUES (?,?,?,?,?,?,?,?,?,?)");
                 $stmt->bind_param("ssssssssis", $id, $name, $username, $password, $ic, $email, $car_number, $car_model, $driver_contact, $account_status);    
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
                    $stmt = $this->con->prepare("UPDATE driver SET online_status = 'online' WHERE username = ?");
                    $stmt->bind_param("s",$username);
                    $stmt->execute();
                     return USER_AUTHENTICATED;
                 }
             }
             else {
                 return false;
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
        
        public function updateDriverWorkingStatus($driver_id, $working_status)
        {
            $stmt = $this->con->prepare("UPDATE driver SET working_status=? WHERE driver_id = ?");
            $stmt->bind_param("si", $working_status, $driver_id);
                if($stmt->execute())
                    return true;
                return false;
        }
        
        public function getDriverLocationById($id)
        {
           if ($this->isDriverExist_id($id)) 
           {
            $stmt = $this->con->prepare("SELECT driver_c_lat, driver_c_lng FROM driver WHERE driver_id = ?");
            $stmt->bind_param("i", $id);
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

        public function getAllOnlineDriverId()
        {

            $stmt = $this->con->prepare("SELECT driver_id FROM driver WHERE online_status = 'online' && working_status = 'true'");
            $stmt->execute();
            $stmt->bind_result($id);
            $driver = array();
            while($stmt->fetch())
            {
                array_push($driver , $id);
            }
            return $driver;

        }

        public function getHistoryForDriver($id)
        {

            $stmt = $this->con->prepare("SELECT trip_id, passenger_id, driver_id, location_start_lat, location_start_lng, location_end_lat, location_end_lng, rating FROM completed_trip WHERE driver_id = ?");
            $stmt->bind_param("i",$id);
            $stmt->execute();
            $stmt->bind_result($t_id, $p_id, $d_id, $l_s_lat, $l_s_lng, $l_e_lat, $l_e_lng, $rtng);
            $history = array();
            while($stmt->fetch())
            {
                $trip_details = array();
                $trip_details['trip_id'] = $t_id;
                $trip_details['passenger_id'] = $p_id;
                $trip_details['driver_id'] = $d_id;
                $trip_details['location_start_lat'] = $l_s_lat;
                $trip_details['location_start_lng'] = $l_s_lng;
                $trip_details['location_end_lat'] = $l_e_lat;
                $trip_details['location_end_lng'] = $l_e_lng;
                $trip_details['rating'] = $rtng;
                array_push($history, $trip_details);
            }
            return $history;

        }

        //scanning if any trip is assigned to driver
        public function scanningAssignedTrip($id)
        {
            if ($this->isDriverExistById($id)) {
                return TRIP_FOUND;
            }else {
                return TRIP_NOT_FOUND;
            }

        }

        //getting destination from current trip table for driver usage
        public function getDestination($id)
        {

            if($this->isDriverExistById($id))
            {
               $stmt = $this->con->prepare("SELECT location_end_lat , location_end_lng FROM current_trip WHERE driver_id = ?");
               $stmt->bind_param("i", $id);
               $stmt->execute();
               $stmt->bind_result($lat, $lng);
               $stmt->fetch();
   
               $user_details = array();
               $user_details['driver_lat'] = $lat;
               $user_details['driver_lng'] = $lng;
               return $user_details;

            }else
            {
                return DESTINATION_NOT_FOUND;
            }

            

        }

        //getting passenger current location from current_trip table for driver usage
        public function getPassengerCurrentLocation($id)
        {

            if($this->isDriverExistById($id))
            {
               $stmt = $this->con->prepare("SELECT location_start_lat , location_start_lng FROM current_trip WHERE driver_id = ?");
               $stmt->bind_param("i", $id);
               $stmt->execute();
               $stmt->bind_result($lat, $lng);
               $stmt->fetch();
   
               $user_details = array();
               $user_details['driver_lat'] = $lat;
               $user_details['driver_lng'] = $lng;
               return $user_details;

            }else
            {
                return DESTINATION_NOT_FOUND;
            }

            

        }

        public function current_trip_to_ongoing_trip($id)
        {
              
            $stmt = $this->con->prepare("INSERT INTO ongoing_trip(trip_id,passenger_id,driver_id,location_start_lat,location_start_lng,location_end_lat,location_end_lng,booked_time) 
            SELECT trip_id,passenger_id,driver_id,location_start_lat,location_start_lng,location_end_lat,location_end_lng,booked_time FROM current_trip WHERE driver_id = ?");
            $stmt->bind_param("i", $id);
            if($stmt->execute())
            {
                return true;
            }else {
                return false;
            }
             
        }

        public function updatePickUpStatus($id,$picked_up_status)
        {
            
            $stmt = $this->con->prepare("UPDATE ongoing_trip SET picked_up = ? WHERE driver_id = ?");
            $stmt->bind_param("si", $picked_up_status, $id);
            if($stmt->execute())
                return true;
            return false;

        }

        public function updatePaidStatus($id,$paid_status)
        {
            
            $stmt = $this->con->prepare("UPDATE ongoing_trip SET paid = ? WHERE driver_id = ?");
            $stmt->bind_param("si", $paid_status, $id);
            if($stmt->execute())
                return true;
            return false;

        }

        public function updateDroppedOffStatus($id,$dropped_off_status)
        {
            
            $stmt = $this->con->prepare("UPDATE ongoing_trip SET dropped_off = ? WHERE driver_id = ?");
            $stmt->bind_param("si", $dropped_off_status, $id);
            if($stmt->execute())
                return true;
            return false;

        }

        public function ongoing_trip_to_completed_trip($id)
        {
              
            $stmt = $this->con->prepare("INSERT INTO completed_trip(trip_id,passenger_id,driver_id,location_start_lat,location_start_lng,location_end_lat,location_end_lng,booked_time) 
            SELECT trip_id,passenger_id,driver_id,location_start_lat,location_start_lng,location_end_lat,location_end_lng,booked_time FROM ongoing_trip WHERE driver_id = ?");
            $stmt->bind_param("i", $id);
            if($stmt->execute())
            {
                return true;
            }else {
                return false;
            }
             
        }

        //later change to curret_trip
        public function deleteCurrentTrip($id)
        {
            $stmt = $this->con->prepare('DELETE FROM current_trip WHERE driver_id = ?');
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) 
                return true;
            return false;
             
        }

        public function deleteOngoingTrip($id)
        {
            $stmt = $this->con->prepare('DELETE FROM ongoing_trip WHERE passenger_id = ?');
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) 
                return true;
            return false;
             
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

        //checking for driver exists or not in DRiver table
        private function isDriverExist($username)
        {
            $stmt = $this->con->prepare("SELECT username FROM driver WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $stmt->store_result();
            return $stmt->num_rows > 0;
        }      

        //checking for driver exists or not in DRiver table
        private function isDriverExist_id($id)
        {
            $stmt = $this->con->prepare("SELECT driver_id FROM driver WHERE driver_id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->store_result();
            return $stmt->num_rows > 0;
        }   
        
        //driver_id based on current trip table
        private function isDriverExistById($id)
        {
            $stmt = $this->con->prepare("SELECT driver_id FROM current_trip WHERE driver_id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->store_result();
            return $stmt->num_rows > 0;
        } 

        private function isDriverExistByIdOn($id)
        {
            $stmt = $this->con->prepare("SELECT driver_id FROM ongoing_trip WHERE driver_id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->store_result();
            return $stmt->num_rows > 0;
        } 

        public function driverLogout($username)
        {
            $stmt = $this->con->prepare("UPDATE driver SET online_status = 'offline' WHERE username = ?");
                    $stmt->bind_param("s",$username);
                    if($stmt->execute())
                        return true;
                    return false; 
                    
        }
//-----------------------------------------------------DRIVER---------------------------------------------------------------------------------------------        



//-----------------------------------------------------PASSENGER---------------------------------------------------------------------------------------------        

        //for creating Passenger and error checking
        public function createPassenger($id, $name, $username, $password, $ic, $email, $contact_number)
        {
            //account status will be active upon registering and inactive only if account is deleted or barred
            $account_status = 'active';   

             if(!$this->isPassengerExist($username))
             {
                 $stmt = $this->con->prepare("INSERT INTO passenger (passenger_id, passenger_name, username, password, passenger_ic_number, passenger_email, contact_number, account_status) VALUES (?,?,?,?,?,?,?,?)");
                 $stmt->bind_param("ssssssis", $id, $name, $username, $password, $ic, $email, $contact_number, $account_status);    
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

        public function newTrip($id, $passenger_id, $driver_id, $start_lat, $start_lng, $end_lat, $end_lng)
        {
            
            $stmt = $this->con->prepare("INSERT INTO current_trip (trip_id, passenger_id, driver_id, location_start_lat, location_start_lng, location_end_lat, location_end_lng ) VALUES (?,?,?,?,?,?,?)");
            $stmt->bind_param("siidddd", $id, $passenger_id, $driver_id, $start_lat, $start_lng, $end_lat, $end_lng );
                if($stmt->execute())
                {
                    return TRIP_CREATED;
                }else {
                    return TRIP_ERROR;
                }
        }

        public function getCurrentPassengerTripId($passenger_id){
            $stmt = $this->con->prepare("SELECT trip_id FROM ongoing_trip WHERE passenger_id = ?");
            $stmt->bind_param("i", $passenger_id);
            $stmt->execute();
            $stmt->bind_result($trip_id);
            $stmt->fetch();
            return $trip_id;
        }


        //for driver login and error checking
        public function passengerLogin($username, $password)
        {   //use $this-> when refering to functions in the same class to avoid  undefined class error
            $hashed_password = $this->getCurrentPassengerPasswordByUsername($username);
            if($this->isPassengerExist($username))
            {
                if(password_verify($password,$hashed_password))
                {
                    //updates online_status when logging in
                    $stmt = $this->con->prepare("UPDATE passenger SET online_status = 'online' WHERE username = ?");
                    $stmt->bind_param("s",$username);
                    $stmt->execute();
                    return USER_AUTHENTICATED;
                }
            }
            else {
                return false;
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

        public function passengerLogout($username)
        {
            $stmt = $this->con->prepare("UPDATE passenger SET online_status = 'offline' WHERE username = ?");
                    $stmt->bind_param("s",$username);
                    if($stmt->execute())
                        return true;
                    return false; 
                    
        }


        public function getPassengerPaidStatus($id)
        {
            if($this->isPassengerExistByIdOn($id))
            {
                $stmt = $this->con->prepare("SELECT paid FROM ongoing_trip WHERE passenger_id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $stmt->bind_result($status);
                $stmt->fetch();
                return $status;
            }else 
            {
                return false;
            }
               
        } 


        public function getPassengerDroppedOffStatus($id)
        {
            if($this->isPassengerExistByIdOn($id))
            {
                $stmt = $this->con->prepare("SELECT dropped_off FROM ongoing_trip WHERE passenger_id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $stmt->bind_result($status);
                $stmt->fetch();
                return $status;
            }else 
            {
                return false;
            }
               
        } 

        public function getPassengerPickedUpStatus($id)
        {
            if($this->isPassengerExistByIdOn($id))
            {
                $stmt = $this->con->prepare("SELECT picked_up FROM ongoing_trip WHERE passenger_id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $stmt->bind_result($status);
                $stmt->fetch();
                return $status;
            }else 
            {
                return false;
            }
               
        } 


        public function updatePaidStatusByPassenger($id,$paid_status)
        {
            
            $stmt = $this->con->prepare("UPDATE ongoing_trip SET paid = ? WHERE passenger_id = ?");
            $stmt->bind_param("si", $paid_status, $id);
            if($stmt->execute())
                return true;
            return false;

        }

        public function updateDroppedOffStatusByPassenger($id,$dropped_off_status)
        {
            
            $stmt = $this->con->prepare("UPDATE ongoing_trip SET dropped_off = ? WHERE passenger_id = ?");
            $stmt->bind_param("si", $dropped_off_status, $id);
            if($stmt->execute())
                return true;
            return false;

        }

        public function updatePickUpStatusByPassenger($id,$picked_up_status)
        {
            
            $stmt = $this->con->prepare("UPDATE ongoing_trip SET picked_up = ? WHERE passenger_id = ?");
            $stmt->bind_param("si", $picked_up_status, $id);
            if($stmt->execute())
                return true;
            return false;

        }

        public function updateFareRatingByPassenger($id,$fare, $rating)
        {
            
            $stmt = $this->con->prepare("UPDATE completed_trip SET rating = ? , fare = ? WHERE trip_id = ?");
            $stmt->bind_param("dsi", $rating, $fare, $id);
            if($stmt->execute())
                return true;
            return false;

        }

        public function getHistoryForPassenger($id)
        {

            $stmt = $this->con->prepare("SELECT trip_id, passenger_id, driver_id, location_start_lat, location_start_lng, location_end_lat, location_end_lng, rating FROM completed_trip WHERE passenger_id = ?");
            $stmt->bind_param("i",$id);
            $stmt->execute();
            $stmt->bind_result($t_id, $p_id, $d_id, $l_s_lat, $l_s_lng, $l_e_lat, $l_e_lng, $rtng);
            $history = array();
            while($stmt->fetch())
            {
                $trip_details = array();
                $trip_details['trip_id'] = $t_id;
                $trip_details['passenger_id'] = $p_id;
                $trip_details['driver_id'] = $d_id;
                $trip_details['location_start_lat'] = $l_s_lat;
                $trip_details['location_start_lng'] = $l_s_lng;
                $trip_details['location_end_lat'] = $l_e_lat;
                $trip_details['location_end_lng'] = $l_e_lng;
                $trip_details['rating'] = $rtng;
                array_push($history, $trip_details);
            }
            return $history;
        }


        public function isPassengerExistByIdCu($id)
        {
            $stmt = $this->con->prepare("SELECT passenger_id FROM current_trip WHERE passenger_id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->store_result();
            return $stmt->num_rows > 0;
        } 

        public function isPassengerExistByIdOn($id)
        {
            $stmt = $this->con->prepare("SELECT passenger_id FROM ongoing_trip WHERE passenger_id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->store_result();
            return $stmt->num_rows > 0;
        } 

        public function ongoing_trip_to_completed_trip_by_passenger($id)
        {
              
            $stmt = $this->con->prepare("INSERT INTO completed_trip(trip_id,passenger_id,driver_id,location_start_lat,location_start_lng,location_end_lat,location_end_lng,booked_time) 
            SELECT trip_id,passenger_id,driver_id,location_start_lat,location_start_lng,location_end_lat,location_end_lng,booked_time FROM ongoing_trip WHERE passenger_id = ?");
            $stmt->bind_param("i", $id);
            if($stmt->execute())
            {
                return true;
            }else {
                return false;
            }
             
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
    
   