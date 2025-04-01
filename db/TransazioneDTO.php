<?php
class TransazioneDTO implements JsonSerializable {
    private $id; //int auto-increment
    private $data; //string
    private $partecipanti = []; //relazione con tabella persona
    private $pagata_da; //chiave esterna a id_persona
    
    public function __construct( $id, $data, $partecipanti, $pagata_da ){
        $this->id = $id;
        $this->data = $data;
        $this->partecipanti = $partecipanti;
        $this->pagata_da = $pagata_da;
    }

    /*
     * GETTERS E SETTERS
     */
    public function getID() {
        return $this->id_transazione;
    }

    public function setID($id_transazione) {
        $this->id_transazione = $id_transazione;
    }

    public function getData() {
        return $this->data;
    }

    public function setData($data) {
        $this->data = $data;
    }

    public function getPartecipanti() {
        return $this->partecipanti;
    }

    public function setPartecipanti(array $partecipanti) {
        $this->partecipanti = $partecipanti;
    }

    public function addPartecipante($partecipante) {
        $this->partecipanti[] = $partecipante;
    }

    public function getPagataDa() {
        return $this->pagata_da;
    }

    public function setPagataDa($persona) {
        $this->pagata_da = $persona;
        if (method_exists($persona, 'addPagamento')) {
            $persona->addPagamento($this);
        }
    }

    public function jsonSerialize() {
        return [
            'id' => $this->id,
            'data' => $this->data,
            'partecipanti' => $this->partecipanti,
            'pagata_da' => $this->pagata_da,
        ];
    }
}
