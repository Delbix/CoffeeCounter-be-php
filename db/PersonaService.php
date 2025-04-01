<?php

/**
 * Classe per l'inserimento e modifia di campi a db
 * __construct( $conn )
 * insertPersona( $n_starnuti )
 */

require_once 'Db.php';

class PersonaService extends Db{
    /**
     * Instanzio la classe con i parametri di connessione
	 * lo fa il padre
     */

    public function __construct() {
        parent::__construct();
    }

    

    /**
     * Inserisci record a db
     * @param String $nome
     * @param String $cognome
     * @return int ID della persona inserita
     */
    public function insertPersona( $nome, $cognome ){
        $stmt = $this->conn->prepare( "INSERT INTO `persona` (`id_persona`, `nome`, `cognome`, `ha_pagato`, `ha_partecipato`) VALUES (NULL, ?, ?, 0, 0);");
        $stmt->bind_param( "ss", $nome, $cognome );

        $stmt->execute();
        if ( $stmt->error != '' ){
            return -2;
        } else {
            $lastId = $this->conn->insert_id;
            return $lastId;
        }
        $stmt->close();
    }

    /**
     * 
     * @return \ArrayObject
     */
    public function getPersone() {
        
        $p_list = new ArrayObject();
        
        $sql = "SELECT * FROM persona ";
        $result = $this->conn->query( $sql );
        $nRows = $result->num_rows;
        
        if( $nRows > 0){
            // output data of each row
            while($row = $result->fetch_assoc()) {
                $persona = new PersonaDTO( $row['id_persona'], $row['nome'], $row['cognome'], $row['ha_pagato'], $row['ha_partecipato'] );
                $p_list->append($persona);
            }
        }

       
        $result->close();

        return $p_list; 
    }
        

}
