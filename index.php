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
            echo insertUpdatePersona($data);
            break;
        case '/db/transazione':
            echo insertTransazione($data);
            break;
        case '/persona/elimina':
            
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
 * @param type $data
 * @return type
 */
function insertUpdatePersona( $data ) {
    $result = null;
    $personaDTO = new PersonaDTO( $data['id'], $data['nome'], $data['cognome'], $data['ha_pagato'], $data['ha_partecipato'] );

    //caso di inserimento
    if ( $personaDTO->getID() == null ) {
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
        $p_old = readPersona($personaDTO->getID()); //personaDTO
        if ($p_old) {
            $ha_pagato = $p_old->getHaPagato();
            $personaDTO->setHaPagato( $ha_pagato );
        }
        $result = updatePersona($personaDTO);
    }
    
    if( $result->getID() == -2 ){
        return json_encode([
                    'message' => 'Qualcosa è andato storto',
                    'status' => 'error'
                ]);
    }
    

    return json_encode($result);

}

/**
 * Inserimento di una transazione
 * @param type $data
 * @return type
 */
function insertTransazione( $data ){
    $partecipanti = new ArrayObject();
    foreach ( $data['parteipanti'] as $persona ){
        $partecipante = new PersonaDTO($persona['id'], $persona['nome'], $persona['cognome'], $persona['ha_pagato'], $persona['ha_partecipato']);
        $partecipanti->append($partecipante);
    }
    
    $transazioneDTO = new TransazioneDTO( $data['id'], $data['data'], $partecipanti, $data['pagata_da'] );
    $transazioneService = new TransazioneService();
    $result = $transazioneService->insertTransazione($data['data'], $data['pagata_da']['id'], $partecipanti);
    
    if( $result->getID() == -2 ){
        return json_encode([
                    'message' => 'Qualcosa è andato storto',
                    'status' => 'error'
                ]);
    }
    

    return json_encode($result);
    
}

 

function readAllPersone() {
    // TODO logica per leggere tabella persone dal db
    $personaService = new PersonaService();
    $p_list = $personaService->getPersone();
    return $p_list;
}

 

function readPersona($id) {
    // TODO select * from persona where id = $id ++ fare un count per ha_pagato e ha_partecipato
    return null;
}

 

function insertPersona($persona) {
    // TODO insert persona nel database
    $personaService = new PersonaService();
    $persona->setID( $personaService->insertPersona($persona->getNome(), $persona->getCognome()) );
    return $persona;
}

function updatePersona($persona) {
    //TODO update persona nel database
    return $persona;
}