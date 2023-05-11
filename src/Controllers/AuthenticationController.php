<?php

namespace Vanier\Api\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Vanier\Api\Exceptions\HttpUnprocessableContentException;
use Vanier\Api\Models\UserModel;
use Vanier\Api\Helpers\JWTManager;



class AuthenticationController extends BaseController
{
    // Model for Database Transactions
    private UserModel $user_model;

    public function __construct()
    {
        $this->user_model = new UserModel();
    }

    public function handleGetToken(Request $request, Response $response, array $args) {
    
        $user_data = $request->getParsedBody();

        
        if (empty($user_data)) {
            return $this->prepareResponse($response,
                    ['error' => true, 'message' => 'No data was provided in the request.'], 400);
        }

        $desired_columns = ['email','password'];
        
        

        $checkColumn = $this->checkColumns($user_data, $desired_columns, [], $request, $response);

        if($checkColumn->getStatusCode() == 422){

            return $checkColumn;
        }
        //var_dump($user_data);exit;
        $user_model = new UserModel();
        $jwtManager = new JWTManager();
    
        // The received user credentials.
       

        $email = $user_data["email"];
        $password = $user_data["password"];
        // Verify if the provided email address is already stored in the DB.
        $db_user = $user_model->verifyEmail($email);
        if (!$db_user) {
            return $this->prepareResponse($response,
                    ['error' => true, 'message' => 'The provided email does not match our records.'], 400);
        }
        // Now we verify if the provided passowrd.
        $db_user = $user_model->verifyPassword($email, $password);
        if (!$db_user) {
            return $this->prepareResponse($response,
                    ['error' => true, 'message' => 'The provided password was invalid.'], 400);
        }
    
        // Valid user detected => Now, we generate and return a JWT.
        // Current time stamp * 60 minutes * 60 seconds
        $jwt_user_info = ["id" => $db_user["user_id"], "email" => $db_user["email"], "role" => $db_user['role']];
        $expires_in = time() + 300; // Expires in 5 minutes.
        $user_jwt = $jwtManager->generateToken($jwt_user_info, $expires_in);
        //--
        $response_data = json_encode([
            'status' => 1,
            'token' => $user_jwt,
            'message' => 'User logged in successfully!',
        ]);
        $response->getBody()->write($response_data);
        return $response->withStatus(HTTP_OK);
    }
    
    // HTTP POST: URI /account 
    // Creates a new user account.
    public function handleCreateUserAccount(Request $request, Response $response, array $args) {
        
        $user_data = $request->getParsedBody();
        
        // Verify if information about the new user to be created was included in the 
        // request.
        if (empty($user_data)) {
            return $this->prepareResponse($response,
                    ['error' => true, 'message' => 'No data was provided in the request.'], 400);
        }
        // Data was provided, we attempt to create an account for the user.     
       
        $desired_columns = ['first_name', 'last_name','email','password' ];
        $optional_columns = ['role'];
        
        $checkColumn = $this->checkColumns($user_data, $desired_columns, $optional_columns, $request, $response);


        if($checkColumn->getStatusCode() == 422){

            return $checkColumn;
        }
        $user_model = new UserModel();

        $verify_email = $user_model->verifyEmail($user_data['email']);

        $verify_email = $user_model->verifyEmail($user_data['email']);

       
        if($verify_email != null){
            return $this->prepareResponse($response,
            ['error' => true, 'message' => 'The email is already in use'], 422);
        }
        $new_user = $user_model->createUser($user_data);
        //--
        if (!$new_user) {
            // Failed to create the new user.
            return $this->prepareResponse($response,
                    ['error' => true, 'message' => 'Failed to create the new user.'], 400);

        }
        // The user account has been created successfully.  
        return $this->prepareResponse($response,
                ['error' => false, 'message' => 'The new user account has been created successfully!'], 200);
    }


    
}
