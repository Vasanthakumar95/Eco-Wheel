<?php
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

require '../vendor/autoload.php';

require '../includes/DbOperations.php';

//to show error log in console
$config = ['settings' => ['displayErrorDetails' => true]]; 
$app = new \Slim\App($config);

//Api call starts here--------------------------------------------------------------------------------------------------------------------------------------------------------

//-----------------------------------------------------DRIVER---------------------------------------------------------------------------------------------        

//Create new Driver
$app->post('/createdriver', function(Request $request, Response $response){

    if(!haveEmptyParameters(array('driver_id', 'driver_name', 'username', 'password', 'driver_ic_number', 'driver_email'), $request, $response))
    {       
        $request_data = $request->getParsedBody();

        $id = $request_data['driver_id'];
        $name = $request_data['driver_name'];
        $username = $request_data['username'];
        $password = $request_data['password'];
        $ic = $request_data['driver_ic_number'];
        $email = $request_data['driver_email'];

        $hash_password = password_hash($password, PASSWORD_DEFAULT);

        $db = new DbOperations;

        $result = $db->createDriver($id, $name, $username, $hash_password, $ic, $email);

        if($result == USER_CREATED)
        {
            $message = array();
            $message['error'] = false;
            $message['message'] = 'User Created Sucessfully';
            $response->write(json_encode($message));

            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(201); 
        }
        else if($result == USER_FAILURE)
        {
            $message = array();
            $message['error'] = true;
            $message['message'] = 'Some error occured';
            $response->write(json_encode($message)); 

            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(421);
        }
        else if($result == USER_EXIST)
        {
            $message = array();
            $message['error'] = true;
            $message['message'] = 'User already exists';
            $response->write(json_encode($message));

            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(422);
        }

    }

}); 

//driver login 
$app->post('/driverlogin', function(Request $request, Response $response)
{

    if(!haveEmptyParameters(array('username' , 'password'), $request, $response))
    {
        $request_data = $request->getParsedBody();

        $username = $request_data['username'];
        $password = $request_data['password'];

        $db = new DbOperations;

        $result = $db->driverLogin($username, $password);

        if($result == USER_AUTHENTICATED)
        {
            $driver = $db->getDriverByUsername($username);
            $response_data = array();

            $response_data['error'] = false;
            $response_data['message'] = 'Login Successful';
            $response_data['driver'] = $driver;

            $response->write(json_encode($response_data));
            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(200);
                        //200='ok'

        }elseif($result == USER_NOT_FOUND)
        {

            $response_data = array();

            $response_data['error'] = true;
            $response_data['message'] = 'User does not exist';

            $response->write(json_encode($response_data));
            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(404);
                        //404='not found'
   

        }elseif ($result == USER_PASSWORD_DO_NOT_MATCH) {

            $response_data = array();

            $response_data['error'] = true;
            $response_data['message'] = 'Invalid Credentials';

            $response->write(json_encode($response_data));
            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(404);
                        //404='not found'
        }
    }

});

//updating driver current location
$app->put('/drivercurrentposition' , function(Request $request , Response $response)
{
    if(!haveEmptyParameters(array('username', 'driver_c_lat', 'driver_c_lng'), $request, $response))
    {
        $request_data = $request->getParsedBody();
        $username = $request_data['username'];
        $longitude = $request_data['driver_c_lng'];
        $latitude = $request_data['driver_c_lat'];

        $db = new DbOperations;

        $result = $db->updateDriverCurrentLocation($username, $latitude, $longitude);

        if($result == true)
        {
            $message = array();
            $message['error'] = false;
            $message['message'] = 'Driver Position Updated Sucessfully';
            $response->write(json_encode($message));

            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(201);
        }else if($result == false)
        {
            $message = array();
            $message['error'] = true;
            $message['message'] = 'Some error occured';
            $response->write(json_encode($message)); 

            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(422);
        }


    }
});

//returns one driver's last location 
$app->post('/getdriverlocation', function(Request $request, Response $response)
{
    if(!haveEmptyParameters(array('username'), $request, $response))
    {

    $request_data = $request->getParsedBody();
    $username = $request_data['username'];    

    $db = new DbOperations;
    $location = $db->getDriverLocationByUsername($username);

    if ($location != USER_NOT_FOUND) {
        $response_data = array();
        $response_data['error'] = false;
        $response_data['location'] = $location;
        $response->write(json_encode($response_data));

        return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(200);
        
    }
    else {
        $response_data = array();
        $response_data['error'] = true;
        $response_data['location'] = 'No location found';
        $response->write(json_encode($response_data));

        return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(200);
    }
    

    }
});

//-----------------------------------------------------DRIVER---------------------------------------------------------------------------------------------        


//-----------------------------------------------------PASSENGER---------------------------------------------------------------------------------------------        

//Create new Driver
$app->post('/createpassenger', function(Request $request, Response $response){

    if(!haveEmptyParameters(array('passenger_id', 'passenger_name', 'username', 'password', 'passenger_ic_number', 'passenger_email'), $request, $response))
    {       
        $request_data = $request->getParsedBody();

        $id = $request_data['passenger_id'];
        $name = $request_data['passenger_name'];
        $username = $request_data['username'];
        $password = $request_data['password'];
        $ic = $request_data['passenger_ic_number'];
        $email = $request_data['passenger_email'];

        $hash_password = password_hash($password, PASSWORD_DEFAULT);

        $db = new DbOperations;

        $result = $db->createPassenger($id, $name, $username, $hash_password, $ic, $email);

        if($result == USER_CREATED)
        {
            $message = array();
            $message['error'] = false;
            $message['message'] = 'User Created Sucessfully';
            $response->write(json_encode($message));

            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(201); 
        }
        else if($result == USER_FAILURE)
        {
            $message = array();
            $message['error'] = true;
            $message['message'] = 'Some error occured';
            $response->write(json_encode($message)); 

            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(421);
        }
        else if($result == USER_EXIST)
        {
            $message = array();
            $message['error'] = true;
            $message['message'] = 'User already exists';
            $response->write(json_encode($message));

            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(422);
        }

    }

});

//passenger login 
$app->post('/passengerlogin', function(Request $request, Response $response)
{

    if(!haveEmptyParameters(array('username' , 'password'), $request, $response))
    {
        $request_data = $request->getParsedBody();

        $username = $request_data['username'];
        $password = $request_data['password'];

        $db = new DbOperations;

        $result = $db->passengerLogin($username, $password);

        if($result == USER_AUTHENTICATED)
        {
            $passenger = $db->getPassengerByUsername($username);
            $response_data = array();

            $response_data['error'] = false;
            $response_data['message'] = 'Login Successful';
            $response_data['passenger'] = $passenger;

            $response->write(json_encode($response_data));
            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(200);
                        //200='ok'

        }elseif($result == USER_NOT_FOUND)
        {

            $response_data = array();

            $response_data['error'] = true;
            $response_data['message'] = 'User does not exist';

            $response->write(json_encode($response_data));
            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(404);
                        //404='not found'
   

        }elseif ($result == USER_PASSWORD_DO_NOT_MATCH) {

            $response_data = array();

            $response_data['error'] = true;
            $response_data['message'] = 'Invalid Credentials';

            $response->write(json_encode($response_data));
            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(404);
                        //404='not found'
        }
    }

});

//updating passenger current location
$app->put('/passengercurrentposition' , function(Request $request , Response $response)
{
    if(!haveEmptyParameters(array('username', 'passenger_c_lat', 'passenger_c_lng'), $request, $response))
    {
        $request_data = $request->getParsedBody();
        $username = $request_data['username'];
        $longitude = $request_data['passenger_c_lng'];
        $latitude = $request_data['passenger_c_lat'];

        $db = new DbOperations;

        $result = $db->updatePassengerCurrentLocation($username, $latitude, $longitude);

        if($result == true)
        {
            $message = array();
            $message['error'] = false;
            $message['message'] = 'Passenger Position Updated Sucessfully';
            $response->write(json_encode($message));

            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(201);
        }else if($result == false)
        {
            $message = array();
            $message['error'] = true;
            $message['message'] = 'Some error occured';
            $response->write(json_encode($message)); 

            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(422);
        }


    }
});

//-----------------------------------------------------PASSENGER---------------------------------------------------------------------------------------------        

//-----------------------------------------------------AUTHENTICATION---------------------------------------------------------------------------------------------        
function haveEmptyParameters($required_params, $request, $response)
    {
        $error = false;
        $error_params = '';
        $request_params = $request->getParsedBody();

        foreach($required_params as $param)
        {
            if(!isset($request_params[$param]) || strlen($request_params[$param])<=0)
            {
                $error = true;
                $error_params .= $param . ', ';
            }
        }

        if($error)
        {
            $error_detail = array();
            $error_detail['error'] = true;
            $error_detail['message'] = 'Required parameters ' . substr($error_params, 0, -2) . ' are missing or empty';
            $response->write(json_encode($error_detail));
        }

        return $error;
    }


//--------------------------------------------------------------------------------------------------------------------------------------------------------        

//Create new user 
$app->post('/createuser', function(Request $request, Response $response){

    if(!haveEmptyParameters(array('id','username','password','role'), $request, $response))
    {
        $request_data = $request->getParsedBody();

        $id = $request_data['id'];
        $username = $request_data['username'];
        $password = $request_data['password'];
        $role = $request_data['role'];

        $hash_password = password_hash($password, PASSWORD_DEFAULT);

        $db = new DbOperations;

        $result = $db->createUser($id, $username, $hash_password, $role);

        if($result == USER_CREATED)
        {
            $message = array();
            $message['error'] = false;
            $message['message'] = 'User Created Sucessfully';
            $response->write(json_encode($message));

            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(201); 
        }
        else if($result == USER_FAILURE)
        {
            $message = array();
            $message['error'] = true;
            $message['message'] = 'Some error occured';
            $response->write(json_encode($message)); 

            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(422);
        }
        else if($result == USER_EXIST)
        {
            $message = array();
            $message['error'] = true;
            $message['message'] = 'User already exists';
            $response->write(json_encode($message));

            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(422);
        }

    }

}); 


//user login 
$app->post('/userlogin', function(Request $request, Response $response)
{

    if(!haveEmptyParameters(array('username' , 'password'), $request, $response))
    {
        $request_data = $request->getParsedBody();

        $username = $request_data['username'];
        $password = $request_data['password'];

        $db = new DbOperations;

        $result = $db->userLogin($username, $password);

        if($result == USER_AUTHENTICATED)
        {
            $user = $db->getUserByUsername($username);
            $response_data = array();

            $response_data['error'] = false;
            $response_data['message'] = 'Login Successful';
            $response_data['user'] = $user;

            $response->write(json_encode($response_data));
            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(200);
                        //200='ok'

        }elseif($result == USER_NOT_FOUND)
        {

            $response_data = array();

            $response_data['error'] = true;
            $response_data['message'] = 'User does not exist';

            $response->write(json_encode($response_data));
            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(404);
                        //404='not found'
   

        }elseif ($result == USER_PASSWORD_DO_NOT_MATCH) {

            $response_data = array();

            $response_data['error'] = true;
            $response_data['message'] = 'Invalid Credentials';

            $response->write(json_encode($response_data));
            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(404);
                        //404='not found'
        }
    }

});

//display all user in database
$app->get('/allusers', function(Request $request, Response $response)
{

    $db = new DbOperations;
    $users = $db->getAllUserDetails();

    $response_data = array();
    $response_data['error'] = false;
    $response_data['users'] = $users;

    $response->write(json_encode($response_data));

    return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);


}); 

//updating user info by id referencing
$app->put('/updateuser/{id}', function(Request $request, Response $response, array $args)
{
    $id = $args['id'];

    if(!haveEmptyParameters(array('username','role','id'), $request, $response))
    {
            $request_data = $request->getParsedBody();
            $username = $request_data['username'];
            $role = $request_data['role'];
            
            $db = new DbOperations;

            if($db->updateUser($username,$role,$id))
            {
                $response_data = array();
                $response_data['error'] = false;
                $response_data['message'] = 'User Updated Successfully';
                $user = $db->getUserByUsername($username);
                $response_data['user'] = $user;

                $response->write(json_encode($response_data));

                return $response
                            ->withHeader('Content-type', 'application/json')
                            ->withStatus(200);
            }else
            {
                $response_data = array();
                $response_data['error'] = true;
                $response_data['message'] = 'Please try again later or contact admin';
                $user = $db->getUserByUsername($username);
                $response_data['user'] = $user;

                $response->write(json_encode($response_data));

                return $response
                            ->withHeader('Content-type', 'application/json')
                            ->withStatus(404);
            }
    }

    return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);

});

//updating user password by username referencing
$app->put('/updatepassword', function(Request $request, Response $response, array $args)
{
    if(!haveEmptyParameters(array('currentpassword', 'newpassword', 'username'), $request, $response))
    {
        $request_data = $request->getParsedBody();

        $currentpassword = $request_data['currentpassword'];
        $newpassword = $request_data['newpassword'];
        $username = $request_data['username'];

        $db = new DbOperations;

        $result = $db->updatePassword($currentpassword, $newpassword, $username);

        if($result == PASSWORD_CHANGED)
        {
            $response_data = array();
            $response_data['error'] = false;
            $response_data['message'] = 'Password Changed Successfully';
            $response->write(json_encode($response_data));

            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(200);

        }else if ($result == PASSWORD_DO_NOT_MATCH) {
            $response_data = array();
            $response_data['error'] = true;
            $response_data['message'] = 'Password Do Not Match';
            $response->write(json_encode($response_data));

            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(404);

        }else if ($result == PASSWORD_NOT_CHANGED) {
            $response_data = array();
            $response_data['error'] = true;
            $response_data['message'] = 'Some Error Occured';
            $response->write(json_encode($response_data));

            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(404);
        }

    }

    return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(422);

});

//deleting user(must have id checking)
$app->delete('/deleteuser/{id}', function(Request $request, Response $response, array $args)
{
    $id = $args['id'];

    $db = new DbOperations;

    $response_data = array();

    if($db->deleteUser($id))
    {
        $response_data['error'] = false;
        $response_data['message'] = 'User has been deleted';

    }else {
        $response_data['error'] = true;
        $response_data['message'] = 'Some Error Occured, Please try again.';

    }

    $response->write(json_encode($response_data));

    return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);


});

//Api call ends here--------------------------------------------------------------------------------------------------------------------------------------------------------



$app->run();

//change the tracking to driver
//pass the value of searched location
//pass value of current location into my sql for and passenger
