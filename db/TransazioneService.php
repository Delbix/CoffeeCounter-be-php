<?php

/**
 * Classe per l'inserimento e modifia di campi a db
 * __construct( $conn )
 * insertPersona( $n_starnuti )
 */

require_once 'Db.php';

class TransazioneService extends Db{
    /**
     * Instanzio la classe con i parametri di connessione
	 * lo fa il padre
     */

    public function __construct() {
        parent::__construct();
    }

    

    /**
     * Inserisci record a db
     * @param String $data
     * @param Persona $pagata_da
     * @param List<Persona> $partecipanti 
     * @return int ID della persona inserita
     */
    public function insertTransazione( $data, $pagata_da, $partecipanti ){
        $id_pagatore = $pagata_da->getIDPersona();
        $stmt = $this->conn->prepare( "INSERT INTO `transazione` (`id_transazione`, `data`, `pagata_da`) VALUES (NULL, ?, ?);");
        $stmt->bind_param( "si", $data, $pagata_da->getID() );

        $stmt->execute();
        if ( $stmt->error != '' ){
            return -2;
        } 
        
        $idTransazione = $this->conn->insert_id;
        foreach ( $persona as $partecipanti ){
            $stmt = $this->conn->prepare( "INSERT INTO `partecipazione` (`id_transazione`, `id_persona`) VALUES (?, ?);");
            $stmt->bind_param( "ii", $idTransazione, $persona->getID() );
            $stmt->execute();
            if ( $stmt->error != '' ){
                return -2;
            } 
        }
        
        return $idTransazione;
        
        $stmt->close();
    }

        

}
