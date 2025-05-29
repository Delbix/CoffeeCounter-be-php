<?php

require './db/PersonaDTO.php';
require './db/TransazioneDTO.php';
require './db/PersonaService.php';
require './db/TransazioneService.php';
require './db/app-init.php';

/**
 * Questo index ha la funzione di analizzare le richieste che arrivano al BE di questo progetto ed instradarle
 * per l'inserimento a DB
 */

// Impostare il tipo di contenuto in JSON
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *"); //Allow any origin for CORS protocol
header('Access-Control-Allow-Methods: OPTIONS, POST');
header('Access-Control-Allow-Headers: app-request, auth, Content-Type, Origin');

// Ottenere l'URI della richiesta
$requestUri = $_SERVER['REQUEST_URI'];
$requestUri = str_replace('/CoffeeCounter-be/angular', '', $requestUri);

// Verifica il metodo HTTP
$method = $_SERVER['REQUEST_METHOD'];
//leggo il json che mi arriva
$data = json_decode(file_get_contents("php://input"), true);

//mi aspetto che il metodo per il passaggio dei dati sia in POST
if( $method == 'POST' ){
    //casistiche accettate
    switch( $requestUri ){
        case '/db/persona':
            echo json_encode( insertUpdatePersona($data) );
            break;
        case '/db/transazione':
            $result = insertTransazione($data);
            $numericPartecipanti = array_values( (array)$result->getPartecipanti() );
            $result->setPartecipanti( $numericPartecipanti );
            echo json_encode( $result );
            break;
        case '/db/persona/elimina':
            echo json_encode( deletePersona($data) );
            break;
        case '/transazione' : //lista degli utenti
            // Converti l'array associativo in un array numerico
            //è reso necessario dal fatto che l'app android si aspetta l'array esterno come array numerico e non associativo
            $numericArray = array_values((array)readAllPersone());

            echo json_encode($numericArray);
            break;
        case '/db/statistiche/ultimatransazione' :
            echo json_encode( getDataUltimaTransazione() );
            break;
        //TODO da fare 
        case '/db/statistiche/transazionePiuPartecipata' :
            //echo json_encode( gettransazionePiuPartecipata() );
            break;
        case '/db/statistiche/caffeBevuti' :
            echo json_encode( getCaffeBevuti() );
            break;
        default :
            echo json_encode([
                'message' => 'Metodo non supportato',
                'status' => 'error'
            ]);
            break;
    }
}

/**
 * Insert/update di un utente
 * @param json $data
 * @return personaDTO
 */
function insertUpdatePersona( $data ) {
    $result = null;
    $personaDTO = new PersonaDTO( $data['id'], $data['nome'], $data['cognome'], $data['ha_pagato'], $data['ha_partecipato'], 0 );

    //se ho qualcuno con lo stesso nome e cognome, rifiuto l'inserimetno/modifica
    if( verificaOmonimie($personaDTO) ){
        //ID == -2 è sintomo di errore, il FE deve riconoscerlo
        return new PersonaDTO(-2, $personaDTO->getNome(), $personaDTO->getCognome(), 0, 0, 0);
    }
    //caso di inserimento
    if ( $personaDTO->getID() == 0 ) {
        $result = insertPersona($personaDTO);
    } else { // caso update
        $result = updatePersona($personaDTO);
    }
    
    if( $result->getID() == -2 ){
        return json_encode([
                    'message' => 'Qualcosa è andato storto',
                    'status' => 'error'
                ]);
    }
    
    return $result;

}

/**
 * Verifico che nelle persone inserite in db ce ne sia una con lo stesso nome e cognome
 * fatta esclusione di se stessa (persona con lo stesso ID), ciò permette di modificare maiuscole e minuscole.
 * @param PersoonaDTO $personaDTO
 * @return true se è presente una persona omonima, false altrimenti
 */
function verificaOmonimie( $personaDTO ){
    //Individua le omonimie
    $persone = (array)readAllPersone(); //mi aspetto una lista di PersonaDTO
    foreach ($persone as $next) {
        //controllo se esiste un utente con lo stesso nome e cognome.
        //elimino eventuali spazi bianchi o tabulazioni, altrimenti "Federico" != "Federico "
        if ( $next->getID() != $personaDTO->getID() && strcasecmp( preg_replace('/\s+/', '', $next->getNome() ), preg_replace('/\s+/', '', $personaDTO->getNome() ) ) === 0 && strcasecmp( preg_replace('/\s+/', '', $next->getCognome() ), preg_replace('/\s+/', '', $personaDTO->getCognome() ) ) === 0) {
            // Persona già presente in archivio
            return true;
        }
    }
    return false;
}

/**
 * Inserimento di una transazione
 * @param json $data
 * @return TransazioneDTO
 */
function insertTransazione( $data ){
    $partecipanti = [];
    foreach ( $data['partecipanti'] as $persona ){
        $partecipanti[] = new PersonaDTO($persona['id'], $persona['nome'], $persona['cognome'], $persona['ha_pagato'], $persona['ha_partecipato'], $persona['caffe_pagati']);
    }
    
    $transazioneService = new TransazioneService();
    //result è l'id della transazione appena inserita
    $result = $transazioneService->insertTransazione($data['data'], $data['pagata_da']['id'], $partecipanti);
    
    if( $result == -2 ){
        return json_encode([
                    'message' => 'Qualcosa è andato storto',
                    'status' => 'error'
                ]);
    }
    $transazioneService->closeDB();
    
    return $transazioneDTO = new TransazioneDTO( $result, $data['data'], $partecipanti, $data['pagata_da'] );    
}

 
/**
 * Prende tutte le persone presenti in db
 * @return \ArrayObject , lista di personaDTO
 */
function readAllPersone() {
    $personaService = new PersonaService();
    $p_list = $personaService->getPersone();
    $personaService->closeDB();
    return $p_list;
}

 
/**
 * prende i dati di una persona se è presente in db
 * @param int $id
 * @return PersonaDTO|null
 */
function readPersona($id) {
    $personaService = new PersonaService();
    $result = $personaService->getPersona($id);
    $personaService->closeDB();
    return $result;
}

 
/**
 * Inserimento di una nuova persona
 * @param PersonaDTO $persona
 * @return PersonaDTO
 */
function insertPersona($persona) {
    $personaService = new PersonaService();
    $persona->setID( $personaService->insertPersona($persona->getNome(), $persona->getCognome()) );
    $personaService->closeDB();
    return $persona;
}

/**
 * update di una persona
 * @param PersonaDTO $personaDTO
 * @return PersonaDTO|null
 */
function updatePersona($personaDTO) {
    $personaService = new PersonaService();
    if( $personaService->updatePersonaGeneralita($personaDTO) ){
        $personaService->closeDB();
        return $personaDTO;
    } 
    $personaService->closeDB();
    return null;
}

/**
 * Elimina una persona
 * @param json $data
 * @return true se eliminata con successo, false altrimenti
 */
function deletePersona($data){
    $personaService = new PersonaService();
    $id = $data['id'];
    $result = $personaService->deletePersona( $id );
    $personaService->closeDB();
    return $result;
}

/**
 * Recupera la data dell'ultima transazione registrata nel db
 * @return String, data dell'ultima transazione
 */
function getDataUltimaTransazione(){
    $transazioneService = new TransazioneService();
    $result = $transazioneService->getDataUltimaTransazione();
    $transazioneService->closeDB();
    return $result;
}

/**
 * Restituisce il numero dei caffe bevuti
 * - dal primo giorno di utilizzo dell'app
 * - nell'ultimo mese
 * - TODO dell'ultima settimana
 */
function getCaffeBevuti(){
    $transazioneService = new TransazioneService();
    $month = date('m');
    $result = array( "day0" => $transazioneService->getNumCaffeDayZero(), 
        "Mese" => $transazioneService->getNumCaffeMonth($month), "Settimana" => $transazioneService->getNumCaffeWeek() );
    $transazioneService->closeDB();
    return $result;
    
}