<?php

use Firebase\JWT\JWT;

class JwtAuth {
	private $secret_key;
	private $ci;
	private $user;
	private $expire = 3600;

	public function __construct(){
		$this->ci= & get_instance();
		$this->secret_key = $this->ci->config->item('jwt_key');
	}

	public function attempt($email,$password){

		$user = $this->ci->user->as_array()->get(array('email'=>$email));

		if($user){
			$is_verify = password_verify($password,$user['password']);
			if($is_verify){
				$token = $this->generateToken($user);
				return $token;
			}else{
				$response = array('status'=>false,'error'=>'Password doen\'match');
				$this->ci->response($response,REST_Controller::HTTP_BAD_REQUEST);
			}
		}else{
			$response = array('status'=>false,'error'=>'User not found');
			$this->ci->response($response,REST_Controller::HTTP_BAD_REQUEST);
		}

		
	}


	public function generateToken($user){
		    	$tokenId    = base64_encode(uniqid());
    			$issuedAt   = time();
    			$notBefore  = $issuedAt + 10;             //Adding 10 seconds
   				$expire     = $notBefore + 60;            // Adding 60 seconds
    			$serverName = base_url(); // Retrieve the server name from config file
    			$data = [
    			    'sub' =>$user['id'],
			        'iat'  => $issuedAt,         // Issued at: time when the token was generated
			        'jti'  => $tokenId,          // Json Token Id: an unique identifier for the token
			        'iss'  => $serverName,       // Issuer
			        'nbf'  => $notBefore,        // Not before
		       		//'exp'  => $expire,           // Expire
		    	];

		    $jwt = JWT::encode($data, $this->secret_key,'HS512');
            
		    return ($jwt) ? $jwt :false;
	}

	public function authenticate(){
		//dd($this->ci->input->request_headers());
		$authorization = $this->ci->input->get_request_header('Authorization');

		list($jwt) = sscanf( $authorization, 'Bearer %s');

		if (!empty($jwt)){
            try {
                $secret_key = $this->secret_key;
                $claims = JWT::decode($jwt, $secret_key, array('HS512'));
                $this->user=$claims->sub;
                return;

            }catch (Exception $e) {
            	 $this->ci->response(array('status'=>false,'message'=>$e->getMessage()),REST_Controller::HTTP_UNAUTHORIZED);
            }
        } 
        	
        $this->ci->response(array('status'=>false,'message'=>'Token is required'),REST_Controller::HTTP_BAD_REQUEST);
       
      
	}


	public function getUserId(){
		return $this->user;
	}

	public function refresh($token) {

		$secret_key = $this->secret_key;
         try{
             $decoded = JWT::decode($token, $secret_key, ['HS512']);
             //return ;
         }catch ( \Firebase\JWT\ExpiredException $e ) {
             JWT::$leeway = 720000;
             $decoded = (array) JWT::decode($token, $secret_key, ['HS512']);
             // TODO: test if token is blacklisted
             $decoded['iat'] = time();
             $decoded['exp'] = time() + 60;

             return JWT::encode($decoded, $secret_key,'HS512');
         }catch ( \Exception $e ){
              $this->ci->response(array('status'=>false,'message'=>$e->getMessage()),REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
         }
     }


}// end of class
?>
