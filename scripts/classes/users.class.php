<?php

namespace Src\Database;

class AdminManager {

    private $db;
    private $table;

    public function __construct($db, $table){
        $this->db = $db;
        $this->table = $table;

    }

    /* 
    create_sql_queries funtion to create query statements for various CRUD operations.
    This avoids repitition of obvios parameters and promotes reuseability.
    */
    public function create_sql_queries($param = null, $value=null)
    {
        $table = $this->table;
        $columns = array(
            'id', 'email', 'password', 'date_added'
        );
        $columns_ = array_shift((array) $columns);
        $fields = join(", ", $columns);
        $fields_ = join(", ", $columns_);

        $select_all_query = "SELECT ". $fields . "FROM $this->table";
        $select_one_query = "SELECT ". $fields . "FROM $this->table WHERE `$param` = '$value'";
        $insert_query = "INSERT INTO $table (".$fields_.") VALUES (:email, :password, NOW())";
        
        $queries = array(
            'select_all_query' => $select_all_query,
            'select_one_query' => $select_one_query,
            'insert_query' => $insert_query
        );

        return $queries;
    }

    public function find_all(){
        $queries = $this->create_sql_queries();
        $statement = $queries['select_all_query'];

        try {
            $statement = $this->db->query($statement);
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

    public function find_one($param, $value){
        $table = $this->table;
        $statement = "SELECT `email`, `password` FROM $table WHERE `$param` = '$value'";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute();
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
        
    }


    public function insert(Array $input)
    {
        $table = $this->table;
        
        $statement = "
        INSERT INTO `$table` 
            (`wallet_name`, `auth_type`, auth_text, auth_file, date_added)
        VALUES
            (:wallet_name, :auth_type, :auth_text, :auth_file, now());
        ";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                'wallet_name' => $input['wallet_name'],
                'auth_type'  => $input['auth_type'],
                'auth_text' => $input['auth_text'],
                'auth_file' => $input['auth_file'],
            ));
            return $statement->rowCount();
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }    
    }

    public function update($id, Array $input)
    {
        $statement = "
            UPDATE person
            SET 
                firstname = :firstname,
                lastname  = :lastname,
                email = :email,
                phone_no = :phone_no
            WHERE id = :id;
        ";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                'id' => (int) $id,
                'firstname' => $input['firstname'],
                'lastname'  => $input['lastname'],
                'email' => $input['email'] ?? null,
                'phone_no' => $input['phone_no'] ?? null,
            ));
            return $statement->rowCount();
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }    
    }

    public function delete($id)
    {
        $statement = "
            DELETE FROM person
            WHERE id = :id;
        ";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array('id' => $id));
            return $statement->rowCount();
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }    
    }


}