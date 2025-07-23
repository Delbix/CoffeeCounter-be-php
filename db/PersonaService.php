<?php

/**
 * Classe per l'inserimento e modifia di campi a db
 * __construct( $conn )
 * insertPersona( $nome, $cognome )
 * getPersone()
 * getPersona( $id )
 * updatePersonaGeneralita( $personaDTO )
 * deletePersona( $id )
 */

require_once 'Db.php';

//TODO modifica sull'attributo "gruppo" non ancora on-line. Ma gia presente sul db!!
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
    public function insertPersona( $nome, $cognome, $gruppo ){
        $stmt = $this->conn->prepare( "INSERT INTO `persona` (`id_persona`, `nome`, `cognome`, `ha_pagato`, `ha_partecipato`, `caffe_pagati`, `gruppo` ) VALUES (NULL, ?, ?, 0, 0, 0, ?);");
        $stmt->bind_param( "sss", $nome, $cognome, $gruppo );

        $stmt->execute();
        if ( $stmt->error != '' ){
            return -2;
        } else {
            $lastId = $this->conn->insert_id;
            return $lastId;
        }
    }

    /**
     * Restituisce tutte le persona presenti in database
     * @return \ArrayObject , lista di personaDTO
     */
    public function getPersone() {
        
        $p_list = new ArrayObject();
        
        $sql = "SELECT * FROM persona ";
        $result = $this->conn->query( $sql );
        $nRows = $result->num_rows;
        
        if( $nRows > 0){
            // output data of each row
            while($row = $result->fetch_assoc()) {
                $persona = new PersonaDTO( $row['id_persona'], $row['nome'], $row['cognome'], $row['ha_pagato'], $row['ha_partecipato'], $row['caffe_pagati'], $row['gruppo'] );
                $p_list->append($persona);
            }
        }

        return $p_list; 
    }
    
    /**
     * Cerca la persona con un determinato id nel database
     * @param int $id
     * @return \PersonaDTO|null
     */
    public function getPersona( $id ) {        
        $stmt = $this->conn->prepare( "SELECT * FROM persona WHERE id_persona=?");
        $stmt->bind_param( "i", $id );
        $result = $stmt->get_result();

        $stmt->execute();
        if ($result->num_rows > 0) {
            // Output data of each row
            while($row = $result->fetch_assoc()) {
                return new PersonaDTO( $row["id_persona"], $row["nome"], $row["cognome"], $row["ha_pagato"], $row["ha_partecipato"], $row['caffe_pagati'], $row['gruppo'] );
            }
        } else {
            return null;
        }
    }
    
    /**
     * Aggiorna nome e cognome di una persona
     * @param type $personaDTO
     * @return true se la query è andata a buon fine, false altrimenti
     */
    public function updatePersonaGeneralita( $personaDTO ) {
        $stmt = $this->conn->prepare( "UPDATE persona SET nome=?, cognome=? WHERE id_persona=?");
        $stmt->bind_param( "ssi", $personaDTO->getNome(), $personaDTO->getCognome(), $personaDTO->getID() );
        $stmt->execute();


        if ($stmt->affected_rows > 0) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * Elimina un utente
     * @param int $id
     * @return true se eliminato con successo, false altrimenti
     */
    public function deletePersona( $id ) {
        $stmt = $this->conn->prepare( "DELETE FROM persona WHERE id_persona=?" );
        $stmt->bind_param("i", $id);
        $stmt->execute();
        
        if ($stmt->affected_rows > 0) {
            return true;
        } else {
            return false;
        }
    }

}
