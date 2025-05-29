<?php

/**
 * Classe per l'inserimento e modifia di campi a db
 * __construct( $conn )
 * insertTransazione( $data, $pagata_da, $partecipanti )
 * getDataUltimaTransazione()
 * getNumCaffeDayZero()
 * getNumCaffeMonth()
 * getNumCaffeWeek()
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
        return $res;
    }
    
    private function getNumCaffe($query) {
        //TODO non funziona.. capire errore
        $result = $this->conn->query($query);

        // Controllo del risultato
        if ($result) {
            $row = $result->fetch_assoc();
            $totale = $row['totale_caffe'];
            return $totale;
        } else {
            echo "Errore nella query: " . $this->conn->error;
        }
    }


    /**
     * Funzione per ricavare il numero di caffè totali presenti in archivio
     * @return [Int]
     */
    public function getNumCaffeDayZero(){
        $query = "SELECT COUNT(*) AS totale_caffe FROM `partecipazione` WHERE 1;";
        
        return $this->getNumCaffe($query);
    }
    
    /**
     * Funzione per ricavare il numero di caffè presenti in archivio consumati nell'ultimo mese
     * @return [Int]
     */
    public function getNumCaffeMonth($month) {
        $query = "SELECT COUNT(*) AS totale_caffe FROM partecipazione p JOIN transazione t ON p.id_transazione = t.id_transazione WHERE MONTH(t.data) = ".$month.";";
        
        return $this->getNumCaffe($query);
    }
    
    /**
     * Funzione per ricavare il numero di caffè presenti in archivio consumati nell'ultima settimana
     * @return [Int]
     */
    public function getNumCaffeWeek() {
        $query = "SELECT 
            SUM(numero_partecipazioni) AS totale_caffe
          FROM (
            SELECT 
              transazione.id_transazione,
              COUNT(partecipazione.id_persona) AS numero_partecipazioni
            FROM 
              transazione
            LEFT JOIN 
              partecipazione ON transazione.id_transazione = partecipazione.id_transazione
            WHERE 
              transazione.data >= DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY)
              AND transazione.data <= NOW()
            GROUP BY 
              transazione.id_transazione
          ) AS subquery;
          ";
        
        return $this->getNumCaffe($query);
    }

}
