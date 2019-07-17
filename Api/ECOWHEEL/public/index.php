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

    if(!haveEmptyParameters(array('driver_id', 'driver_name', 'username', 'password', 'driver_ic_number', 'driver_email', 'driver_car_number', 'driver_car_model', 'contact_number'), $request, $response))
    {       
        $request_data = $request->getParsedBody();

        $id = $request_data['driver_id'];
        $name = $request_data['driver_name'];
        $username = $request_data['username'];
        $password = $request_data['password'];
        $ic = $request_data['driver_ic_number'];
        $email = $request_data['driver_email'];
        $car_model = $request_data['driver_car_model'];
        $car_number = $request_data['driver_car_number'];
        $driver_contact = $request_data['contact_number'];


        $hash_password = password_hash($password, PASSWORD_DEFAULT);

        $db = new DbOperations;

        $result = $db->createDriver($id, $name, $username, $hash_password, $ic, $email, $car_model, $car_number, $driver_contact);

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

    if(!haveEmptyParameters(array('username', 'password'), $request, $response))
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

        }elseif($result == false)
        {
            $response_data = array();
            $response_data['error'] = true;
            $response_data['message'] = 'Invalid Credentials';

            $response->write(json_encode($response_data));
            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(201);
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
    if(!haveEmptyParameters(array('driver_id'), $request, $response))
    {

    $request_data = $request->getParsedBody();
    $driver_id = $request_data['driver_id'];    

    $db = new DbOperations;
    $location = $db->getDriverLocationById($driver_id);

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

$app->post('/scanningassignedtrip', function(Request $request, Response $response)
{
    if(!haveEmptyParameters(array('driver_id'), $request, $response))
    {

    $request_data = $request->getParsedBody();
    $id = $request_data['driver_id'];    

    $db = new DbOperations;
    $trip = $db->scanningAssignedTrip($id);

    if ($trip == TRIP_FOUND) {
        $response_data = array();
        $response_data['error'] = false;
        $response_data['message'] = "Found a new Trip";
        $response->write(json_encode($response_data));

        return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(200);
        
    }
    else {
        $response_data = array();
        $response_data['error'] = true;
        $response_data['message'] = 'No Trip Available';
        $response->write(json_encode($response_data));

        return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(200);
    }
    

    }
});

$app->put('/updatedriverworkingstatus', function(Request $request, Response $response)
{
    if(!haveEmptyParameters(array('driver_id', 'working_status'), $request, $response))
    {

    $request_data = $request->getParsedBody();
    $id = $request_data['driver_id'];    
    $w_s = $request_data['working_status'];    


    $db = new DbOperations;
    $working_status = $db->updateDriverWorkingStatus($id , $w_s);

    if ($working_status == true) {
        $response_data = array();
        $response_data['error'] = false;
        $response_data['message'] = "Working Status Changed!";
        $response->write(json_encode($response_data));

        return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(200);
        
    }
    else {
        $response_data = array();
        $response_data['error'] = true;
        $response_data['message'] = 'There was a problem!';
        $response->write(json_encode($response_data));

        return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(200);
    }
    

    }
});

$app->put('/updatepickupstatus', function(Request $request, Response $response)
{
    if(!haveEmptyParameters(array('driver_id', 'picked_up_status'), $request, $response))
    {

    $request_data = $request->getParsedBody();
    $id = $request_data['driver_id'];    
    $p_u_s = $request_data['picked_up_status'];    


    $db = new DbOperations;
    $pick_up_status = $db->updatePickUpStatus($id, $p_u_s);

    if ($pick_up_status == true) {
        $response_data = array();
        $response_data['error'] = false;
        $response_data['message'] = "Passenger Pick Up Status Changed!";
        $response->write(json_encode($response_data));

        return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(200);
        
    }
    else {
        $response_data = array();
        $response_data['error'] = true;
        $response_data['message'] = 'There was a problem!';
        $response->write(json_encode($response_data));

        return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(200);
    }
    

    }
});

$app->put('/updatedropoffstatus', function(Request $request, Response $response)
{
    if(!haveEmptyParameters(array('driver_id', 'picked_up_status'), $request, $response))
    {

    $request_data = $request->getParsedBody();
    $id = $request_data['driver_id'];    
    $p_u_s = $request_data['picked_up_status'];    


    $db = new DbOperations;
    $pick_up_status = $db->updateDroppedOffStatus($id, $p_u_s);

    if ($pick_up_status == true) {
        $response_data = array();
        $response_data['error'] = false;
        $response_data['message'] = "Passenger Pick Up Status Changed!";
        $response->write(json_encode($response_data));

        return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(200);
        
    }
    else {
        $response_data = array();
        $response_data['error'] = true;
        $response_data['message'] = 'There was a problem!';
        $response->write(json_encode($response_data));

        return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(200);
    }
    

    }
});

$app->put('/updatepaidstatus', function(Request $request, Response $response)
{
    if(!haveEmptyParameters(array('driver_id', 'picked_up_status'), $request, $response))
    {

    $request_data = $request->getParsedBody();
    $id = $request_data['driver_id'];    
    $p_u_s = $request_data['picked_up_status'];    


    $db = new DbOperations;
    $pick_up_status = $db->updatePaidStatus($id, $p_u_s);

    if ($pick_up_status == true) {
        $response_data = array();
        $response_data['error'] = false;
        $response_data['message'] = "Passenger Pick Up Status Changed!";
        $response->write(json_encode($response_data));

        return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(200);
        
    }
    else {
        $response_data = array();
        $response_data['error'] = true;
        $response_data['message'] = 'There was a problem!';
        $response->write(json_encode($response_data));

        return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(200);
    }
    }
});

$app->post('/getdriverdestinationlocation', function(Request $request, Response $response)
{
    if(!haveEmptyParameters(array('driver_id'), $request, $response))
    {

    $request_data = $request->getParsedBody();
    $driver_id = $request_data['driver_id'];    

    $db = new DbOperations;
    $location = $db->getDestination($driver_id);

    if ($location != DESTINATION_NOT_FOUND) {
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
        $response_data['location'] = DESTINATION_NOT_FOUND;
        $response->write(json_encode($response_data));

        return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(200);
    }
    

    }
});

$app->post('/getpassengercurrentlocation', function(Request $request, Response $response)
{
    if(!haveEmptyParameters(array('driver_id'), $request, $response))
    {

    $request_data = $request->getParsedBody();
    $driver_id = $request_data['driver_id'];    

    $db = new DbOperations;
    $location = $db->getPassengerCurrentLocation($driver_id);

    if ($location != DESTINATION_NOT_FOUND) {
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
        $response_data['location'] = DESTINATION_NOT_FOUND;
        $response->write(json_encode($response_data));

        return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(200);
    }
    

    }
});


$app->post('/current_trip_to_ongoing_trip', function(Request $request, Response $response)
{
    if(!haveEmptyParameters(array('driver_id'), $request, $response))
    {

    $request_data = $request->getParsedBody();
    $driver_id = $request_data['driver_id'];    

    $db = new DbOperations;
    $current_to_ongoing = $db->current_trip_to_ongoing_trip($driver_id);

    if ($current_to_ongoing == true) {
        $response_data = array();
        $response_data['error'] = false;
        $response_data['message'] = 'Sucessful';
        $response->write(json_encode($response_data));

        return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(200);
        
    }
    else {
        $response_data = array();
        $response_data['error'] = true;
        $response_data['message'] = 'Data Already Exists or Invalid Data';
        $response->write(json_encode($response_data));

        return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(200);
    }
    

    }
});




//deleting user(must have id checking)
$app->post('/deletecurrenttrip', function(Request $request, Response $response, array $args)
{
    if(!haveEmptyParameters(array('driver_id'), $request, $response))
    {
        $request_data = $request->getParsedBody();
        $id = $request_data['driver_id']; 

        $db = new DbOperations;

        $response_data = array();

        if($db->deleteCurrentTrip($id))
        {
            $response_data['error'] = false;
            $response_data['message'] = 'Trip status changed into Ongoing';

        }else {
            $response_data['error'] = true;
            $response_data['message'] = 'Some Error Occured, Please try again.';

        }

        $response->write(json_encode($response_data));

        return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(200);

    }
});

$app->post('/deleteongoingtrip', function(Request $request, Response $response, array $args)
{
    if(!haveEmptyParameters(array('passenger_id'), $request, $response))
    {
        $request_data = $request->getParsedBody();
        $id = $request_data['passenger_id']; 

        $db = new DbOperations;

        $response_data = array();

        if($db->deleteOngoingTrip($id))
        {
            $response_data['error'] = false;
            $response_data['message'] = 'Trip status changed into Completed';

        }else {
            $response_data['error'] = true;
            $response_data['message'] = 'Some Error Occured, Please try again.';

        }

        $response->write(json_encode($response_data));

        return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(200);

    }
});

$app->get('/getallonlinedriverid', function(Request $request, Response $response)
{
    $request_data = $request->getParsedBody();   
    $db = new DbOperations;
    $driver = $db->getAllOnlineDriverId();
    if ($driver != null) {
        $response_data = array();
        $response_data['error'] = false;
        $response_data['drivers'] = $driver;
        $response->write(json_encode($response_data));

        return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(200);
        
    }
    else {
        $response_data = array();
        $response_data['error'] = true;
        $response_data['drivers'] = 'Not found';
        $response->write(json_encode($response_data));

        return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(200);
    }
        
});

$app->post('/getdriverhistory' , function(Request $request, Response $response)
{
    
    if(!haveEmptyParameters(array('driver_id'), $request, $response))
    {
        $request_data = $request->getParsedBody();   
        $driver_id = $request_data['driver_id'];

        $db = new DbOperations;
        $history = $db->getHistoryForDriver($driver_id);

        if ($history != null) {
            $response_data = array();
            $response_data['error'] = false;
            $response_data['history'] = $history;
            $response->write(json_encode($response_data));

            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(200);
            
        }
        else {
            $response_data = array();
            $response_data['error'] = true;
            $response_data['history'] = 'No history Were Found';
            $response->write(json_encode($response_data));

            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(200);
        }
    
    }
    
});

$app->post('/driverlogout' , function(Request $request, Response $response)
{
    
    if(!haveEmptyParameters(array('username'), $request, $response))
    {
        $request_data = $request->getParsedBody();   
        $username = $request_data['username'];

        $db = new DbOperations;
        $status = $db->driverLogout($username);

        if ($status == true) {
            $response_data = array();
            $response_data['error'] = false;
            $response_data['message'] = 'Logout Sucessful';
            $response->write(json_encode($response_data));

            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(200);
            
        }
        else {
            $response_data = array();
            $response_data['error'] = true;
            $response_data['message'] = 'Logout Failed';
            $response->write(json_encode($response_data));

            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(200);
        }
    
    }
    
});


//-----------------------------------------------------DRIVER---------------------------------------------------------------------------------------------        


//-----------------------------------------------------PASSENGER---------------------------------------------------------------------------------------------        

//Create new passenger
$app->post('/createpassenger', function(Request $request, Response $response){

    if(!haveEmptyParameters(array('passenger_id', 'passenger_name', 'username', 'password', 'passenger_ic_number', 'passenger_email', 'contact_number'), $request, $response))
    {       
        $request_data = $request->getParsedBody();

        $id = $request_data['passenger_id'];
        $name = $request_data['passenger_name'];
        $username = $request_data['username'];
        $password = $request_data['password'];
        $ic = $request_data['passenger_ic_number'];
        $email = $request_data['passenger_email'];
        $contact_number = $request_data['contact_number'];

        $hash_password = password_hash($password, PASSWORD_DEFAULT);

        $db = new DbOperations;

        $result = $db->createPassenger($id, $name, $username, $hash_password, $ic, $email, $contact_number);

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

        }elseif($result == false)
        {
            $response_data = array();
            $response_data['error'] = true;
            $response_data['message'] = 'Invalid Credentials';
            $response->write(json_encode($response_data));
            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(201);
                        //404='not found'
        }
    }

});

//creating new trip
$app->post('/passengernewtrip' , function(Request $request , Response $response)
{

    if(!haveEmptyParameters(array('trip_id', 'passenger_id', 'driver_id', 'l_s_lat', 'l_s_lng', 'l_e_lat', 'l_e_lng'), $request, $response))
    {
        $request_data = $request->getParsedBody();

        $trip_id = $request_data['trip_id'];
        $passenger_id = $request_data['passenger_id'];
        $driver_id = $request_data['driver_id'];
        $l_s_lat = $request_data['l_s_lat'];
        $l_s_lng = $request_data['l_s_lng'];
        $l_e_lat = $request_data['l_e_lat'];
        $l_e_lng = $request_data['l_e_lng'];

        $db = new DbOperations;

        $result = $db->newTrip($trip_id, $passenger_id, $driver_id, $l_s_lat, $l_s_lng, $l_e_lat, $l_e_lng);

        if($result == TRIP_CREATED)
        {
            $response_data = array();

            $response_data['error'] = false;
            $response_data['message'] = 'Trip Created';
            

            $response->write(json_encode($response_data));
            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(200);
                        //200='ok'

        }elseif($result == TRIP_ERROR)
        {

            $response_data = array();

            $response_data['error'] = true;
            $response_data['message'] = 'There was an error!';

            $response->write(json_encode($response_data));
            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(201);
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

//passenger logout query
$app->post('/passengerlogout' , function(Request $request, Response $response)
{
    
    if(!haveEmptyParameters(array('username'), $request, $response))
    {
        $request_data = $request->getParsedBody();   
        $username = $request_data['username'];

        $db = new DbOperations;
        $status = $db->passengerLogout($username);

        if ($status == true) {
            $response_data = array();
            $response_data['error'] = false;
            $response_data['message'] = 'Logout Sucessful';
            $response->write(json_encode($response_data));

            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(200);
            
        }
        else {
            $response_data = array();
            $response_data['error'] = true;
            $response_data['message'] = 'Logout Failed';
            $response->write(json_encode($response_data));

            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(200);
        }
    
    }
    
});

$app->post('/getpassengerhistory' , function(Request $request, Response $response)
{
    
    if(!haveEmptyParameters(array('passenger_id'), $request, $response))
    {
        $request_data = $request->getParsedBody();   
        $passenger_id = $request_data['passenger_id'];

        $db = new DbOperations;
        $history = $db->getHistoryForPassenger($passenger_id);

        if ($history != null) {
            $response_data = array();
            $response_data['error'] = false;
            $response_data['history'] = $history;
            $response->write(json_encode($response_data));

            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(200);
            
        }
        else {
            $response_data = array();
            $response_data['error'] = true;
            $response_data['history'] = 'No history Were Found';
            $response->write(json_encode($response_data));

            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(200);
        }
    
    }
    
});

$app->post('/getpassengertripid' , function(Request $request, Response $response)
{
    
    if(!haveEmptyParameters(array('passenger_id'), $request, $response))
    {
        $request_data = $request->getParsedBody();   
        $passenger_id = $request_data['passenger_id'];

        $db = new DbOperations;
        $trip_id = $db->getCurrentPassengerTripId($passenger_id);

        if ($trip_id != null) {
            $response_data = array();
            $response_data['error'] = false;
            $response_data['message'] = $trip_id;
            $response->write(json_encode($response_data));

            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(200);
            
        }
        else {
            $response_data = array();
            $response_data['error'] = true;
            $response_data['message'] = 'No history Were Found';
            $response->write(json_encode($response_data));

            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(200);
        }
    
    }
    
});

$app->post('/ispassengerexistbyidcu' , function(Request $request, Response $response)
{
    
    if(!haveEmptyParameters(array('passenger_id'), $request, $response))
    {
        $request_data = $request->getParsedBody();   
        $passenger_id = $request_data['passenger_id'];

        $db = new DbOperations;
        $status = $db->isPassengerExistByIdCu($passenger_id);

        if ($status > 0) {
            $response_data = array();
            $response_data['error'] = false;
            $response_data['message'] = 'Trip Exist in Current Trip';
            $response->write(json_encode($response_data));

            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(200);
            
        }
        else {
            $response_data = array();
            $response_data['error'] = true;
            $response_data['message'] = 'Trip Does Not Exist in Current Trip';
            $response->write(json_encode($response_data));

            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(200);
        }
    
    }
    
});

$app->post('/ispassengerexistbyidon' , function(Request $request, Response $response)
{
    
    if(!haveEmptyParameters(array('passenger_id'), $request, $response))
    {
        $request_data = $request->getParsedBody();   
        $passenger_id = $request_data['passenger_id'];

        $db = new DbOperations;
        $status = $db->isPassengerExistByIdOn($passenger_id);

        if ($status > 0) {
            $response_data = array();
            $response_data['error'] = false;
            $response_data['message'] = 'Trip Exist in Current Trip';
            $response->write(json_encode($response_data));

            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(200);
            
        }
        else {
            $response_data = array();
            $response_data['error'] = true;
            $response_data['message'] = 'Trip Does Not Exist in Current Trip';
            $response->write(json_encode($response_data));

            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(200);
        }
    
    }
    
});

$app->post('/getpassengerpickedupstatus' , function(Request $request, Response $response)
{
    
    if(!haveEmptyParameters(array('passenger_id'), $request, $response))
    {
        $request_data = $request->getParsedBody();   
        $passenger_id = $request_data['passenger_id'];

        $db = new DbOperations;
        $status = $db->getPassengerPickedUpStatus($passenger_id);

        if ($status == 'true') {
            $response_data = array();
            $response_data['error'] = false;
            $response_data['message'] = 'Passenger Have Been Picked Up';
            $response->write(json_encode($response_data));

            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(200);
            
        }
        else {
            $response_data = array();
            $response_data['error'] = true;
            $response_data['message'] = 'Passenger Have Not Been Picked Up';
            $response->write(json_encode($response_data));

            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(200);
        }
    
    }
    
});

$app->post('/getpassengerdroppedoffstatus' , function(Request $request, Response $response)
{
    
    if(!haveEmptyParameters(array('passenger_id'), $request, $response))
    {
        $request_data = $request->getParsedBody();   
        $passenger_id = $request_data['passenger_id'];

        $db = new DbOperations;
        $status = $db->getPassengerDroppedOffStatus($passenger_id);

        if ($status == 'true') {
            $response_data = array();
            $response_data['error'] = false;
            $response_data['message'] = 'Passenger Have Been Dropped';
            $response->write(json_encode($response_data));

            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(200);
            
        }
        else {
            $response_data = array();
            $response_data['error'] = true;
            $response_data['message'] = 'Passenger Have Not Been Dropped';
            $response->write(json_encode($response_data));

            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(200);
        }
    
    }
    
});

$app->post('/getpassengerpaidstatus' , function(Request $request, Response $response)
{
    
    if(!haveEmptyParameters(array('passenger_id'), $request, $response))
    {
        $request_data = $request->getParsedBody();   
        $passenger_id = $request_data['passenger_id'];

        $db = new DbOperations;
        $status = $db->getPassengerPaidStatus($passenger_id);

        if ($status == 'true') {
            $response_data = array();
            $response_data['error'] = false;
            $response_data['message'] = 'Passenger Have  Paid';
            $response->write(json_encode($response_data));

            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(200);
            
        }
        else {
            $response_data = array();
            $response_data['error'] = true;
            $response_data['message'] = 'Passenger Have Not Paid';
            $response->write(json_encode($response_data));

            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(200);
        }
    
    }
    
});

$app->post('/setpassengerpickedupstatus' , function(Request $request, Response $response)
{
    
    if(!haveEmptyParameters(array('passenger_id','status'), $request, $response))
    {
        $request_data = $request->getParsedBody();   
        $passenger_id = $request_data['passenger_id'];
        $p_u_s = $request_data['status'];


        $db = new DbOperations;
        $picked_up_status = $db->updatePickUpStatusByPassenger($passenger_id , $p_u_s);

        if ($picked_up_status == 'true') {
            $response_data = array();
            $response_data['error'] = false;
            $response_data['message'] = 'Status Changed';
            $response->write(json_encode($response_data));

            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(200);
            
        }
        else {
            $response_data = array();
            $response_data['error'] = true;
            $response_data['message'] = 'Status Not Changed';
            $response->write(json_encode($response_data));

            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(200);
        }
    
    }
    
});

$app->post('/setpassengerdroppedoffstatus' , function(Request $request, Response $response)
{
    
    if(!haveEmptyParameters(array('passenger_id','status'), $request, $response))
    {
        $request_data = $request->getParsedBody();   
        $passenger_id = $request_data['passenger_id'];
        $p_u_s = $request_data['status'];


        $db = new DbOperations;
        $picked_up_status = $db->updateDroppedOffStatusByPassenger($passenger_id , $p_u_s);

        if ($picked_up_status == 'true') {
            $response_data = array();
            $response_data['error'] = false;
            $response_data['message'] = 'Status Changed';
            $response->write(json_encode($response_data));

            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(200);
            
        }
        else {
            $response_data = array();
            $response_data['error'] = true;
            $response_data['message'] = 'Status Not Changed';
            $response->write(json_encode($response_data));

            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(200);
        }
    
    }
    
});

$app->post('/setpassengerpaidstatus' , function(Request $request, Response $response)
{
    
    if(!haveEmptyParameters(array('passenger_id','status'), $request, $response))
    {
        $request_data = $request->getParsedBody();   
        $passenger_id = $request_data['passenger_id'];
        $p_u_s = $request_data['status'];


        $db = new DbOperations;
        $picked_up_status = $db->updatePaidStatusByPassenger($passenger_id , $p_u_s);

        if ($picked_up_status == 'true') {
            $response_data = array();
            $response_data['error'] = false;
            $response_data['message'] = 'Status Changed';
            $response->write(json_encode($response_data));

            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(200);
            
        }
        else {
            $response_data = array();
            $response_data['error'] = true;
            $response_data['message'] = 'Status Not Changed';
            $response->write(json_encode($response_data));

            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(200);
        }
    
    }
    
});

$app->post('/setfarerating' , function(Request $request, Response $response)
{
    
    if(!haveEmptyParameters(array('trip_id', 'rate', 'rating' ), $request, $response))
    {
        $request_data = $request->getParsedBody();   
        $trip_id = $request_data['trip_id'];
        $rate = $request_data['rate'];
        $rating = $request_data['rating'];


        $db = new DbOperations;
        $picked_up_status = $db->updateFareRatingByPassenger($trip_id , $rate, $rating);

        if ($picked_up_status == 'true') {
            $response_data = array();
            $response_data['error'] = false;
            $response_data['message'] = 'Status Changed';
            $response->write(json_encode($response_data));

            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(200);
            
        }
        else {
            $response_data = array();
            $response_data['error'] = true;
            $response_data['message'] = 'Status Not Changed';
            $response->write(json_encode($response_data));

            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(200);
        }
    
    }
    
});

$app->post('/ongoing_trip_to_completed_trip', function(Request $request, Response $response)
{
    if(!haveEmptyParameters(array('driver_id'), $request, $response))
    {

    $request_data = $request->getParsedBody();
    $driver_id = $request_data['driver_id'];    

    $db = new DbOperations;
    $ongoing_to_completed = $db->ongoing_trip_to_completed_trip($driver_id);

    if ($ongoing_to_completed == true) {
        $response_data = array();
        $response_data['error'] = false;
        $response_data['message'] = 'Sucessful';
        $response->write(json_encode($response_data));

        return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(200);
        
    }
    else {
        $response_data = array();
        $response_data['error'] = true;
        $response_data['message'] = 'Data Already Exists or Invalid Data';
        $response->write(json_encode($response_data));

        return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(200);
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
//pass value of current location into my sql for and passenger
