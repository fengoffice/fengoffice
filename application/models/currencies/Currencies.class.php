<?php 

	class Currencies extends BaseCurrencies {
		
		private static $all_currencies = null;
		
		static function getAllCurrencies() {
			if (is_null(self::$all_currencies)) {
				self::$all_currencies = self::instance()->findAll(array('order' => 'is_default DESC'));
			}
			return self::$all_currencies;
		}
		
		static function getCurrenciesInfo() {
			$currencies = self::getAllCurrencies();
			$info = array();
			foreach ($currencies as $c) $info[] = $c->getArrayInfo();
			
			return $info;
		}
		
		static function getDefaultCurrencyInfo() {
			$infos = self::getCurrenciesInfo();
			return array_var($infos, 0, array());
		}

		static function getDefaultCurrencySymbol() {
			$currency = self::getDefaultCurrencyInfo();
			return array_var($currency, 'symbol', '$');
		}
		
		private $cache = null;
		static function getCurrency($id) {
			$all = Currencies::instance()->findAll();
			foreach ($all as $obj) {
				if( $obj->getId() == $id) {
					return $obj;
				}
			}
			return null;
			/*
			if (!isset($this) || !$this instanceof Currencies) {
				return Currencies::instance()->getCurrency($id);
			}
			
			if ($this->cache == null) {
				$all = Currencies::instance()->findAll();
				$this->cache = array();
				foreach ($all as $obj) {
					$this->cache[$obj->getId()] = $obj;
				}
			}
			
			return array_var($this->cache, $id);
			*/
		}
		
	}
