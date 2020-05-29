<?php

    class Discount
    {
        public $goods;
        private $operations;

        public function __construct($goods)
        {
            $this->goods = $goods;
            $this->operations = ['couple', 'oneof', 'together'];
            if (!$this->validateGoods()) {
                $this->Err('Не верный массив goods');
            }
        }

        //вывод ошибки
        private function Err($msg)
        {
            throw new Exception(PHP_EOL.$msg.PHP_EOL);
        }

        //проверка структуры массива
        protected function validateGoods()
        {
            $isValidate = true;
            if (is_array($this->goods)) {
                foreach ($this->goods as $item) {
                    if (!isset($item['name'],$item['price'])) {
                        $isValidate = false;
                        break;
                    }
                }
            } else {
                $isValidate = false;
            }

            return $isValidate;
        }

        //cуммирование цен
        protected function price_summ($rules)
        {
            $summ = 0;
            foreach ($this->goods as $key => $value) {
                if (isset($rules[$key])) {
                    $summ += $value['price'];
                }
            }
            unset($rules);

            return $summ;
        }

        //процент от числа
        protected function price_persentage($target, $persentage)
        {
            $sumOfPair = $this->price_summ($target);

            return $sumOfPair * ($persentage / 100);
        }

        // функция для получения промежуточных результатов (поиск значения одного массива в другой и вывода размера)
        public function getHelper($rules)
        {
            $names = array_column($this->goods, 'name');
            $rulesIntersect = array_intersect($names, $rules);
            $rulesCountVal = array_count_values($rulesIntersect);
            unset($names);

            return ['intersect' => $rulesIntersect, 'countval' => $rulesCountVal, 'count' => count($rulesCountVal)];
        }

        //функция для обработки случая " Если одновременно выбраны А и один из [K, L, M], "
        private function getOneof($rules, $persentage)
        {
            $helperForA = $this->getHelper([$rules[0]]);
            $helperForLKM = $this->getHelper($rules[1]);
            $keyLKM = array_keys($helperForLKM['intersect'])[0];
            $keyA = array_keys($helperForA['intersect'])[0];
            if (!isset($this->goods[$keyA]['discount_price']) && !isset($this->goods[$keyLKM]['discount_price']) && 1 === $helperForLKM['count'] && 1 == $helperForA['count']) {
                echo 'd';
                $sumPersentage = $this->price_persentage($helperForLKM['intersect'], $persentage);
                $this->goods[$keyLKM]['discount_price'] = $this->goods[$keyLKM]['price'] - $sumPersentage;
            }
        }

        // функция для обработки случая " Если пользователь выбрал одновременно N продукта, он получает k скидку"
        private function getTogether($rules, $persentage)
        {
            $helper = $this->getHelper($rules[1]);
            $countGoods = count($this->goods);
            if (0 == $helper['count'] && $countGoods == $rules[0]) {
                $sumPersentage = $this->price_persentage($this->body, $persentage) / $countGoods;
                foreach ($this->goods as $key => $item) {
                    if (!isset($item['discount_price'])) { // если скидки нету
                        $goods[$key]['discount_price'] = $goods[$key]['price'] - $sumPersentage;
                    }
                }
            }
            unset($helper,$countGoods,$sumPersentage);
        }

        //функция для обработки парных задач пункт 1-3
        private function getCouple($rules, $persentage)
        {
            $helper = $this->getHelper($rules);
            $intersect = $helper['intersect'];
            if ($helper['count'] > 1) {
                while ($intersect != []) {
                    $uniqueIntersect = array_unique($intersect);
                    $arrUniqueLength = count($uniqueIntersect);
                    if ($arrUniqueLength > 1) {
                        $sumPersentage = $this->price_persentage($uniqueIntersect, $persentage) / $arrUniqueLength;
                        foreach ($uniqueIntersect as $key => $value) {
                            if (!isset($this->goods[$key]['discount_price'])) {
                                $this->goods[$key]['discount_price'] = $this->goods[$key]['price'] - $sumPersentage;
                            }
                            unset($intersect[$key]);
                        }
                    } else {
                        break;
                    }
                    $uniqueIntersect = array_unique($intersect);
                }
            }
            unset($intersect,$helper);
        }

        //функция сопоставления операции с введенными данными
        public function calculate($operation, $rules, $persentage)
        {
            if (in_array($operation, $this->operations)) {
                switch ($operation) {
                    case 'couple':
                            if (!is_array($rules) && count($rules) < 2) {
                                $this->Err('Не верный массив rules для операции типа couple');
                            }
                            $this->getCouple($rules, $persentage);

                        break;
                        case 'oneof':
                            if (!is_array($rules) && !is_array($rules[1]) && 2 != count($rules)) {
                                $this->Err('Не верный массив rules для операции типа oneof');
                            }

                            $this->getOneof($rules, $persentage);
                        break;
                        case 'together':
                            if (!is_array($rules) && !is_array($rules[1]) && 2 != count($rules) && !is_numeric($rules[0])) {
                                $this->Err('Не верный массив rules для операции типа together');
                            }
                            $this->getTogether($rules, $persentage);
                        break;
                    default:
                        $this->Err('Не правильная операция выбанно!!!');
                        break;
                }
            } else {
                $this->Err('Не правильная операция выбанно!!!');
            }
        }

        public function getOrderSumm()
        {
            $summ = 0;
            foreach ($this->goods as $item) {
                if (isset($item['discount_price'])) {
                    $summ += $item['discount_price'];
                } else {
                    $summ += $item['price'];
                }
            }

            return $summ;
        }
    }
