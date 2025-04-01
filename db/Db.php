<?php
//require './app-init.php';
/*
 * Classe per la connessione al db
 * __construct()
 * getConnection() --> per recuperare la variabile di connessione [non troppo utile al momento]
 * connection( )
 * closeDB()
 */

class Db{

    /*
     * Variabili di classe
     */

    protected	$conn;
    
    /**
     * Costruttore 
     * Tipo singleton
     */

    public function __construct() {
        if( $this->conn == null ){
            $this->connection();
        }
    }

    

    /**
     * Ritorna istanza di connessione
     * @return connessione al db
     */

    public function getConnection() {
        return $this->conn;
    }

    

    /**
     * Connessione al db
     * PRE: parametri presi da config.php
     */

    private function connection( ){
        $this->conn = new mysqli(HOST,UTENTE,PASS,NOME_DB);
        // Check connection
        if ($this->conn->connect_error) {
          die("Connection failed: " . $this->conn->connect_error);
        }
    }

    

    /*
     * Chiude la connesione al db
     */
    public function closeDB(){
        $this->conn->close();
    }

}
