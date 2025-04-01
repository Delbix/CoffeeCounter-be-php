<?php

/*
 * Classe per prendere informazioni dal db
 * __construct()
 * getID( $nome, $gruppo )
 * getAll()
 * getArrayData() --> per ricevere la variabile di istanza
 */

require_once 'Db.php';

class Db_getters extends Db{

    /*
     * Variabili di classe
     */
    private $array_dati;


    /**
     * Instanzio la classe con i parametri di connessione
	 * li prendo dal padre
     */

    public function __construct() {
        parent::__construct();
    }

    

    

    /**
     * Seleziona tutti gli starnuti di tomas
     * @return numero di righe recuperate se ci sono dati, null altrimenti
     */

    public function getAll() {
        $sql = "SELECT * FROM starnutiTomas ";
        $result = $this->conn->query( $sql );
        $nRows = $result->num_rows;
        
        if( $nRows > 0){
            $i = 0;
            // output data of each row
            while($row = $result->fetch_assoc()) {
                $this->array_dati[$i]['id'] = $row['id'];
                $this->array_dati[$i]['data'] = $row['data'];
                $this->array_dati[$i]['n_starnuti'] = $row['n_starnuti'];
                $i++;
            }
        }

       
        $result->close();

        return $nRows;  

    }

    

    /**
     * Ritorno variabile di istanza array_dati
     * @return {array}
     */

    public function getArrayData() {
        return $this->array_dati;
    }
}



