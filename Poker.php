<?php

    class CardDefine 
    {
        const SUITS = array("S", "H", "D", "C") ;
        const SUITSVALUE = array(3, 2, 1, 0);
        const POINTS = array("2", "3", "4", "5","6", "7", "8", "9", "10",
        "J", "Q", "K", "A");
        const POINTSVALUE = array(2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14);
        const Combination = [
                                "isStraightFlush" => "同花順",
                                "isFourOfKind" => "鐵支",
                                "isFullHouse" => "葫蘆",
                                "isFlush" => "同花",
                                "isStraight" => "順子",
                                "isThreeOfKind" => "三條",
                                "isTwoPair" => "兩對",
                                "isPair" => "一對"
                            ];
    }
    
    class Card
    {
        private $_suit;
        private $_point;
        private $_suitValue;
        private $_pointValue;

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
                $key = array_search($suit, CardDefine::SUITS);
                $this->_suitValue = CardDefine::SUITSVALUE[$key];
            }else {
                throw new Exception('Suit set error');
            }
            return $this;
        }

        private function setPoint($point) {
            if (in_array($point, CardDefine::POINTS)) {
                $this->_point = $point; 
                $key = array_search($point, CardDefine::POINTS);
                $this->_pointValue = CardDefine::POINTSVALUE[$key];
                
            }else {
                throw new Exception('Point set error');
            }
            return $this;
        }

        public function getSuitValue() {
            return $this->_suitValue;
        }

        public function getPointValue() {
            return $this->_pointValue;
        }

        public function getSuit() {
            return $this->_suit;
        }

        public function getPoint() {
            return $this->_point;
        }

        public function getCardString() {
            return $this->_suit.$this->_point;
        }

        public function __toString() {
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

        public static function pokerSort($card1, $card2) {
            $rlt = 0;
            if ($card1->getSuitValue() != $card2->getSuitValue()) {
                $rlt = $card2->getSuitValue() - $card1->getSuitValue();
            } else {
                $rlt = $card1->getPointValue() - $card2->getPointValue();
            }
            return $rlt;
        }
    }

    class Player
    {
        private $_username;
        public $cards;
        public $score;

        public function __construct($username) {
            $this->setName($username); 
        }

        public function setName($name) {
            $this->_username = $name;
        }

        public function pushCards($card) {
            $this->cards[] = $card;
        }

        public function setCards($cards) {
            $this->cards = $cards;
        }

        public function getName() {
            return $this->_username;
        }

        public function sortCard() {
            usort($this->cards, array("Deck", "pokerSort"));
        }

        public function calScore(Judge $judge) {
            $judge->init();
            $judge->counthands($this->cards);
            $this->score = $judge->check();
        }

        public function show() {
            return "{$this->_username} : ". implode(", ", $this->cards);
        }
    }

    class Game 
    {
        private $_poker;
        public $players;
        public $referee;

        public function __construct() {
            $this->_poker = new Deck();
            $this->_poker->Shuffle();
            $this->_poker->Cut();

            $this->referee = new Judge();
        }

        public function setPlayer($names = array()) {
            foreach ($names as $name) {
                $this->players[] = new Player($name);
            }
        }    

        public function deal($everyCardNum = 52) {
            $playersNum = count($this->players);
            $allCardSum = $everyCardNum * $playersNum;
            
            for ($i = 0; $i < $allCardSum && count($this->_poker->cards); $i += 1) {
                $this->players[$i%$playersNum]->pushCards(array_shift($this->_poker->cards));
            }
        }

        public function calculateScore() {
            foreach ($this->players as $player) {
                $player->calScore($this->referee);
            }
        }

        public function preview() {
            $str = "";
            foreach ($this->players as $player) {
                $player->sortCard();
                $str .= "{$player->show()} => score : {$player->score} <br> "; 
            }
            echo $str;
        }
    }

    class Judge 
    {
        private $_suitsArray ;
        private $_pointsArray ;
        private $_hand;
        public $max;

        public function init() {
            $this->_suitsArray = array_fill_keys(CardDefine::SUITS, 0);
            $this->_pointsArray = array("a" => 0);
            $this->_pointsArray += array_fill_keys(CardDefine::POINTS, 0);
        }

        public function counthands($cards){
            $this->_hand = $cards; 
            foreach ($cards as $card) {
                $this->setSuitsArray($card->getSuit());
                $this->setPointsArray($card->getPoint());
            }
        }

        public function check(){
            $rlt = "散牌";
            $res = array();
            $this->max = "";

            foreach (CardDefine::Combination as $comb => $name ) {
                if ($this->$comb()) {
                    $rlt = $name;
                    // $res[] = $name;
                    break;
                }
            }

            // if(count($res)) $rlt = implode(", ", $res);
            return $rlt;
        }

        private function setSuitsArray($suit) {
            if (in_array($suit, CardDefine::SUITS)){
                $this->_suitsArray[$suit] += 1;
            }
        }
        
        private function setPointsArray($point) {
            if (in_array($point, CardDefine::POINTS)){
                $this->_pointsArray[$point] += 1;
                if ($point == 14) $this->_pointsArray["a"] += 1;
            }
        }

        private function isStraightFlush() {
            $status = false;
            for ($i = 0; $i < 10; ++$i){
                $str = "";
                $sliceHand = array_slice($this->_pointsArray, $i, 5, true);
                if (array_product($sliceHand) > 0) {
                    $countSuit = array_fill_keys(CardDefine::SUITS, 0);
                    foreach ($sliceHand as $key => $value) {
                        foreach ($this->_hand as $card) {
                            if ($key == $card->getPoint()) {
                                $countSuit[$card->getSuit()] += 1;
                            }
                        }
                        if (array_search(5, $countSuit)) {
                            $status = true; 
                            $str = implode(",", array_keys($sliceHand));
                            $this->max = $str;
                        }
                    }
                }
            }
            return $status;
        }

        private function isFourOfKind() {
            return array_search(4,$this->_pointsArray);
        }

        private function isFullHouse() {
            $status = false;
            if (array_search(3,$this->_pointsArray)) {
                if (array_search(2,$this->_pointsArray)) {
                    $status = true;
                }
            }
            return $status;
        }

        private function isFlush() {
            $status = false;
            foreach ($this->_suitsArray as $value) {
                if($value > 4) {
                    $status = true;
                    break;
                }
            }
            return $status;
        }

        private function isStraight() {
            $status = false;
            for ($i = 0; $i < 10; ++$i) {
                $str = "";
                $sliceHand = array_slice($this->_pointsArray, $i, 5, true);
                if (array_product($sliceHand) > 0){
                    $status = true;
                    $str = implode(",", array_keys($sliceHand));
                    $this->max = $str;
                }
            }
            return $status;
        }

        private function isThreeOfKind() {
            return array_search(3, $this->_pointsArray);
        }

        private function isTwoPair() {
            return count(array_keys($this->_pointsArray, 2)) > 1;
        }

        private function isPair() {
            return count(array_keys($this->_pointsArray, 2)) == 1;
        }
    }

    $game = new Game();
    //$players = ['Dexter'];
    $players = ["Ann", "Jimmy", "Kitty", "Vivian"];
    $game->setPlayer($players);
    $game->deal(10);
    $game->calculateScore();
    $game->preview();
?>
