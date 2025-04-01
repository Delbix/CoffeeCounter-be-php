<?php

class PersonaDTO implements JsonSerializable{
    private $id; // int
    private $nome;      // String
    private $cognome;   // String
    private $ha_pagato; // int
    private $ha_partecipato; // int, numero di transazioni pagate


    public function __construct( $id_persona, $nome, $cognome, $ha_pagato, $ha_parttecipato ){
        $this->id = $id_persona;
        $this->nome = $nome;
        $this->cognome = $cognome;
        $this->ha_pagato = $ha_pagato;
        $this->ha_partecipato = $ha_parttecipato;
    }
    
    public function jsonSerialize() {
        return [
            'id' => $this->id,
            'nome' => $this->nome,
            'cognome' => $this->cognome,
            'ha_pagato' => $this->ha_pagato,
            'ha_partecipato' => $this->ha_partecipato,
        ];
    }
    
    // Getter e Setter
    public function getID() {
        return $this->id_persona;
    }

    public function setID($id_persona) {
        $this->id_persona = $id_persona;
    }

    public function getNome() {
        return $this->nome;
    }

    public function setNome($nome) {
        $this->nome = $nome;
    }

    public function getCognome() {
        return $this->cognome;
    }

    public function setCognome($cognome) {
        $this->cognome = $cognome;
    }

    public function getHaPagato() {
        return $this->ha_pagato;
    }

    public function setHaPagato($ha_pagato) {
        $this->ha_pagato = $ha_pagato;
    }

    public function getHaPartecipato() {
        return $this->ha_partecipato;
    }

    public function setHaPartecipato($ha_partecipato) {
        $this->ha_partecipato = $ha_partecipato;
    }
}