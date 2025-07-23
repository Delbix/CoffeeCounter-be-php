<?php

class PersonaDTO implements JsonSerializable{
    private $id; // int
    private $nome;      // String
    private $cognome;   // String
    private $ha_pagato; // int
    private $ha_partecipato; // int, numero di transazioni pagate
    private $caffe_pagati; //int
    private $gruppo; //String


    public function __construct( $id_persona, $nome, $cognome, $ha_pagato, $ha_partecipato, $caffe_pagati, $gruppo ){
        $this->id = $id_persona;
        $this->nome = $nome;
        $this->cognome = $cognome;
        $this->ha_pagato = $ha_pagato;
        $this->ha_partecipato = $ha_partecipato;
        $this->caffe_pagati = $caffe_pagati;
        $this->gruppo = $gruppo;
    }
    
    public function jsonSerialize() {
        return [
            'id' => $this->id,
            'nome' => $this->nome,
            'cognome' => $this->cognome,
            'ha_pagato' => $this->ha_pagato,
            'ha_partecipato' => $this->ha_partecipato,
            'caffe_pagati' => $this->caffe_pagati,
            'gruppo' => $this->gruppo,
        ];
    }
    
    // Getter e Setter
    public function getID() {
        return $this->id;
    }

    public function setID($id_persona) {
        $this->id = $id_persona;
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
    
    public function getCaffePagati() {
        return $this->caffe_pagati;
    }

    public function setCaffePagati($caffe_pagati) {
        $this->caffe_pagati = $caffe_pagati;
    }
    
    public function getGruppo() {
        return $this->gruppo;
    }

    public function setGruppo($gruppo) {
        $this->gruppo = $gruppo;
    }
}