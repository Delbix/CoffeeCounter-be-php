<?php

/**
 * Classe per l'inserimento e modifia di campi a db
 * __construct( $conn )
 * insertTransazione( $data, $pagata_da, $partecipanti )
 * getDataUltimaTransazione()
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
     * @return int ID della transazione inserita
     */
    public function insertTransazione( $data, $pagata_da, $partecipanti ){
        $stmt = $this->conn->prepare( "INSERT INTO `transazione` (`id_transazione`, `data`, `pagata_da`) VALUES (NULL, ?, ?);");
        $stmt->bind_param( "si", $data, $pagata_da );

        $stmt->execute();
        if ( $stmt->error != '' ){
            return -2;
        } 
        
        $idTransazione = $this->conn->insert_id;
        //TODO non funziona questo loop!!
        foreach ( $partecipanti as $persona ){
            $stmt = $this->conn->prepare( "INSERT INTO `partecipazione` (`id_transazione`, `id_persona`) VALUES (?, ?);");
            $stmt->bind_param( "ii", $idTransazione, $persona->getID() );
            $stmt->execute();
            if ( $stmt->error != '' ){
                return -2;
            } 
        }
        
        $stmt->close();
        return $idTransazione;
        
        
    }
    
    
    public function getDataUltimaTransazione() {
        $query = "SELECT * FROM transazione ORDER BY data DESC LIMIT 1;";
        $result = $this->conn->query($query);
        
        $res = null;
        if ($result->num_rows > 0) {
            // Output dei dati di ogni riga
            while($row = $result->fetch_assoc()) {
                $res = $row['data'];
            }
        }
 
        // Chiudi connessione
        $this->conn->close();
        return $res;
    }

        

}
