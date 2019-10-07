<?php

    class CardDefine 
    {
        const SUITS = array("S", "H", "D", "C") ;
        const POINTS = array("A", "2", "3", "4", "5","6", "7", "8", "9", "10",
        "J", "Q", "K");
    }
    class PlayerName
    {
        const NAME = ["Ann", "Jimmy", "Kitty", "Vivian"];
    }
    
    class Card
    {
        private $_suit;
        private $_point;

        public function __construct($suit, $point) {
            try {
                $this->setSuit($suit);
                $this->setPoint($point);
            }catch(Exception $e){
                echo 'Caught exception: ',  $e->getMessage(), "\n";
            }
        }

        private function setSuit($suit) {
            if (in_array($suit, CardDefine::SUITS)) {
                $this->_suit = $suit; 
            }else {
                throw new Exception('Suit set error');
            }
            return $this;
        }

        private function setPoint($point) {
            if (in_array($point, CardDefine::POINTS)) {
                $this->_point = $point; 
            }else {
                throw new Exception('Point set error');
            }
            return $this;
        }

        public function getCardString() {
            return $this->_suit.$this->_point;
        }
    }

    class Deck
    {
        public $cards = array();

        public function __construct() {
            foreach (CardDefine::SUITS as $suit) {
                foreach (CardDefine::POINTS as $num) {
                    $card = new Card($suit, $num);
                    $this->cards[] = $card;
                }
            }
        }

        public function Shuffle() {
            shuffle($this->cards);
        }

        public function Cut() {
            $randomNum = rand(1,51);
            $this->cards = array_merge(array_slice($this->cards, $randomNum), array_slice($this->cards, 0, $randomNum));
        }
    }

    class Player
    {
        private $username;
        public $cards;

        public function __construct($username) {
            $this->setName($username); 
        }

        public function setName($name) {
            $this->username = $name;
        }

        public function pushCards($card) {
            $this->cards[] = $card;
        }

        public function setCards($cards) {
            $this->cards = $cards;
        }
    }

    class Game 
    {
        private $_poker;
        public $players = array();
        
        public function __construct() {
            $this->_poker = new Deck();
            $this->_poker->Shuffle();
            $this->_poker->Cut();
        }

        public function setPlayer($names = array()) {
            foreach ($names as $name) {
                $this->players[] = new Player($name);
            }
        }    

        public function deal() {
            $playersNum = count($this->players);
            $pokerNum = count($this->_poker->cards);
            $order = 0 ;
            while (count($this->_poker->cards)) {
                $this->players[$order%$playersNum]->pushCards(array_shift($this->_poker->cards));
                $order += 1;
            }

            // $cardGroup = array_chunk($this->_poker->cards, $num);
            // foreach ($this->players as $order => $player) {
            //     $player->setCards($cardGroup[$order]);
            // }
        }

        public function preview() {
            print_r($this->players);
            // foreach ($this->players as $player) {
            //     print_r($player->show());
            // }
            
        }
    }

    $game = new Game();
    $game->setPlayer(PlayerName::NAME);
    $game->deal();
    $game->preview();
?>
