<?php 

if (!class_exists('Database')) {
    class Database {

        private $con;

        // construct
        function __construct() {
            $this->con = $this->connect();
        }

        // connect to db
        private function connect() {
            $string = "mysql:host=localhost;dbname=fk_db";
            try {
                $connection = new PDO($string, DBUSER, DBPASS);
                return $connection;
            } catch (PDOException $e) {
                echo $e->getMessage();
                die;
            }

            return false;
        }

        // write to database
        public function write($query, $data_array = []) {
            $statement = $this->con->prepare($query);
            $check = $statement->execute($data_array);

            if (!$check) {
                file_put_contents("db_errors.txt", print_r($statement->errorInfo(), true), FILE_APPEND);
            }

            return $check;
        }

        // read from database
        public function read($query, $data_array = []) {
            $statement = $this->con->prepare($query);
            $check = $statement->execute($data_array);

            if (!$check) {
                file_put_contents("db_errors.txt", print_r($statement->errorInfo(), true), FILE_APPEND);
            }

            if ($check) {
                $result = $statement->fetchAll(PDO::FETCH_OBJ);
                if (is_array($result) && count($result) > 0) {
                    return $result;
                }
            }

            return false;
        }

        // get user
        public function get_user($userid) {
            $arr['userid'] = $userid;
            $query = "select * from users where userid = :userid limit 1";
            $statement = $this->con->prepare($query);
            $check = $statement->execute($arr);

            if ($check) {
                $result = $statement->fetchAll(PDO::FETCH_OBJ);
                if (is_array($result) && count($result) > 0) {
                    return $result[0];
                }
            }

            return false;
        }

        // generate random id
        public function generate_id($max) {
            $rand = "";
            $rand_count = rand(4, $max);
            for ($i = 0; $i < $rand_count; $i++) {
                $r = rand(0, 9);
                $rand .= $r;
            }

            return $rand;
        }

        // last insert id
        public function last_insert_id() {
            return $this->con->lastInsertId();
        }
    }
}
