<?php

require './db/PersonaDTO.php';
require './db/TransazioneDTO.php';
require './db/PersonaService.php';
require './db/TransazioneService.php';
require './db/app-init.php';

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

if( $method == 'POST' ){
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
    $personaDTO = new PersonaDTO( $data['id'], $data['nome'], $data['cognome'], $data['ha_pagato'], $data['ha_partecipato'] );

    //caso di inserimento
    if ( $personaDTO->getID() == 0 ) {
        //TODO da gestire l'individuazione di omonimie
        /*$persone = readAllPersone(); //mi aspetto una lista di PersonaDTO
        foreach ($persone as $next) {
            if (strcasecmp($next->getNome(), $personaDTO->getNome() ) === 0 && strcasecmp($next->getCognome(), $personaDTO->getCognome()) === 0) {
                // Persona già presente in archivio, il messaggio di errore viene trasmesso tramite id della persona (-2)
                return ['id' => -2];
            }
        }*/
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
 * Inserimento di una transazione
 * @param json $data
 * @return TransazioneDTO
 */
function insertTransazione( $data ){
    $partecipanti = new ArrayObject();
    foreach ( $data['parteipanti'] as $persona ){
        $partecipante = new PersonaDTO($persona['id'], $persona['nome'], $persona['cognome'], $persona['ha_pagato'], $persona['ha_partecipato']);
        $partecipanti->append($partecipante);
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
    
    return $transazioneDTO = new TransazioneDTO( $result, $data['data'], $partecipanti, $data['pagata_da'] );    
}

 
/**
 * Prende tutte le persone presenti in db
 * @return \ArrayObject , lista di personaDTO
 */
function readAllPersone() {
    // TODO logica per leggere tabella persone dal db
    $personaService = new PersonaService();
    $p_list = $personaService->getPersone();
    return $p_list;
}

 
/**
 * prende i dati di una persona se è presente in db
 * @param int $id
 * @return PersonaDTO|null
 */
function readPersona($id) {
    // TODO select * from persona where id = $id ++ fare un count per ha_pagato e ha_partecipato
    $personaService = new PersonaService();
    return $personaService->getPersona($id);
}

 
/**
 * Inserimento di una nuova persona
 * @param PersonaDTO $persona
 * @return PersonaDTO
 */
function insertPersona($persona) {
    // TODO insert persona nel database
    $personaService = new PersonaService();
    $persona->setID( $personaService->insertPersona($persona->getNome(), $persona->getCognome()) );
    return $persona;
}

/**
 * update di una persona
 * @param PersonaDTO $personaDTO
 * @return PersonaDTO|null
 */
function updatePersona($personaDTO) {
    //TODO update persona nel database
    $personaService = new PersonaService();
    if( $personaService->updatePersonaGeneralita($personaDTO) ){
        return $personaDTO;
    } 
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
    return $personaService->deletePersona( $id );
}