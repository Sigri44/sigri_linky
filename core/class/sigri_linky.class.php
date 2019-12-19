<?php
	
	/* This file is part of Jeedom.
		*
		* Jeedom is free software: you can redistribute it and/or modify
		* it under the terms of the GNU General Public License as published by
		* the Free Software Foundation, either version 3 of the License, or
		* (at your option) any later version.
		*
		* Jeedom is distributed in the hope that it will be useful,
		* but WITHOUT ANY WARRANTY; without even the implied warranty of
		* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
		* GNU General Public License for more details.
		*
		* You should have received a copy of the GNU General Public License
		* along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
	*/
	
	/* * ***************************Includes********************************* */
	require_once __DIR__ . '/../../../../core/php/core.inc.php';
	
	class sigri_linky extends eqLogic 
	{
		/*     * *************************Attributs****************************** */
		
		/*     * ***********************Methode static*************************** */
		
		/*
			* Fonction exécutée automatiquement toutes les minutes par Jeedom
			public static function cron() {
			
			}
		*/
		
		/* Fonction exécutée automatiquement toutes les 5 minutes par Jeedom
			public static function cron5() {
			
			}
		*/
		
		/*
			* Fonction exécutée automatiquement tous les jours par Jeedom
			public static function cronDayly() {
			
			}
		*/
		
		/*     * *********************Méthodes d'instance************************* */
		
		/*public function preInsert() {
			
			}
			
			public function postInsert() {
			
			}
			
			public function preSave() {
			
			}
			
			public function postSave() {
			
		}*/
		
		public function preUpdate() 
		{
			if (empty($this->getConfiguration('identifiant'))) {
				throw new Exception(__('L\'identifiant ne peut pas être vide',__FILE__));
			}
			
			if (empty($this->getConfiguration('password'))) {
				throw new Exception(__('Le mot de passe ne peut etre vide',__FILE__));
			}
		}
		
		public function postUpdate() 
		{
			if ( $this->getIsEnable() ){
				$cmd = $this->getCmd(null, 'consoheure');
				if ( ! is_object($cmd)) {
					$cmd = new sigri_linkyCmd();
					$cmd->setName('Consommation Horaire');
					$cmd->setEqLogic_id($this->getId());
					$cmd->setLogicalId('consoheure');
					$cmd->setUnite('kW');
					$cmd->setType('info');
					$cmd->setSubType('numeric');
					$cmd->setIsHistorized(1);
					$cmd->setEventOnly(1);
					$cmd->save();
				}
				
				$cmd = $this->getCmd(null, 'consojour');
				if ( ! is_object($cmd)) {
					$cmd = new sigri_linkyCmd();
					$cmd->setName('Consommation journalière');
					$cmd->setEqLogic_id($this->getId());
					$cmd->setLogicalId('consojour');
					$cmd->setUnite('kWh');
					$cmd->setType('info');
					$cmd->setSubType('numeric');
					$cmd->setIsHistorized(1);
					$cmd->setEventOnly(1);
					$cmd->save();
				}
				$cmd = $this->getCmd(null, 'consomois');
				if ( ! is_object($cmd)) {
					$cmd = new sigri_linkyCmd();
					$cmd->setName('Consommation Mensuelle');
					$cmd->setEqLogic_id($this->getId());
					$cmd->setLogicalId('consomois');
					$cmd->setUnite('kWh');
					$cmd->setType('info');
					$cmd->setSubType('numeric');
					$cmd->setIsHistorized(1);
					$cmd->setEventOnly(1);
					$cmd->save();
				}
				
				$cmd = $this->getCmd(null, 'consoan');
				if ( ! is_object($cmd)) {
					$cmd = new sigri_linkyCmd();
					$cmd->setName('Consommation annuelle');
					$cmd->setEqLogic_id($this->getId());
					$cmd->setLogicalId('consoan');
					$cmd->setUnite('kWh');
					$cmd->setType('info');
					$cmd->setSubType('numeric');
					$cmd->setIsHistorized(1);
					$cmd->setEventOnly(1);
					$cmd->save();
				}
			}
		}
		
		public function preRemove() {
			
		}
		
		public function postRemove() {
			
		}

		public function toHtml($_version = 'dashboard') {
		$replace = $this->preToHtml($_version);
				if (!is_array($replace)) {
					return $replace;
				}
				$version = jeedom::versionAlias($_version);
				if ($this->getDisplay('hideOn' . $version) == 1) {
					return '';
				}
			/* ------------ Ajouter votre code ici ------------*/
				foreach ($this->getCmd('info') as $cmd) {
				$replace['#' . $cmd->getLogicalId() . '_history#'] = '';
				$replace['#' . $cmd->getLogicalId() . '_id#'] = $cmd->getId();
				$replace['#' . $cmd->getLogicalId() . '#'] = $cmd->execCmd();
				$replace['#' . $cmd->getLogicalId() . '_collect#'] = $cmd->getCollectDate();
				if ($cmd->getLogicalId() == 'encours'){
					$replace['#thumbnail#'] = $cmd->getDisplay('icon');
				}
				if ($cmd->getIsHistorized() == 1) {
					$replace['#' . $cmd->getLogicalId() . '_history#'] = 'history cursor';
				}
			}
			/* ------------ N'ajouter plus de code apres ici------------ */

			return $this->postToHtml($_version, template_replace($replace, getTemplate('core', $version, 'sigriLinky', 'sigri_linky')));
		}

		/** **********************Getteur Setteur*************************** */
		
		public static function launch_sigri_linky() 
		{
			foreach (eqLogic::byType('sigri_linky', true) as $sigri_linky) {
				
				log::add('sigri_linky', 'info', 'Debut d\'interrogration Enedis');
				if ($sigri_linky->getIsEnable() == 1) {
					if (!empty($sigri_linky->getConfiguration('identifiant')) && !empty($sigri_linky->getConfiguration('password'))) {
						
						$cmd_date = $sigri_linky->getCmd(null, 'consojour');
						if (is_object($cmd_date)) {
							$value = $cmd_date->execCmd();
							$collectDate = $cmd_date->getCollectDate();
							$command_date = new DateTime($collectDate);
							$start_date = new DateTime();
							$start_date->sub(new DateInterval('P1D'));
							if(date_format($command_date, 'Y-m-d') == date_format($start_date, 'Y-m-d')) {
								log::add('sigri_linky', 'debug', 'Donnees deja presentes pour aujourd\'hui');
							} else {
								$Useragent = $sigri_linky->GetUserAgent();
								log::add('sigri_linky', 'debug', 'UserAgent pour ce lancement : '.$Useragent);
								$API_cookies = $sigri_linky->Login_Enedis_API($Useragent);
								
								$cmd = $sigri_linky->getCmd(null, 'consoheure');
								if (is_object($cmd)) {
									$end_date = new DateTime();
									$start_date = (new DateTime())->setTime(0,0);
									$start_date->sub(new DateInterval('P7D'));
									$sigri_linky->Call_Enedis_API($API_cookies, $Useragent, "urlCdcHeure", $start_date, $end_date);
								}
								
								$cmd = $sigri_linky->getCmd(null, 'consojour');
								if (is_object($cmd)) {
									$end_date = new DateTime();
									$start_date = new DateTime();
									$start_date->sub(new DateInterval('P30D'));
									$sigri_linky->Call_Enedis_API($API_cookies, $Useragent, "urlCdcJour", $start_date, $end_date);
								}
								
								$cmd = $sigri_linky->getCmd(null, 'consomois');
								if (is_object($cmd)) {
									$end_date = new DateTime();
									$start_date = new DateTime('first day of this month');
									$start_date->sub(new DateInterval('P12M'));
									$sigri_linky->Call_Enedis_API($API_cookies, $Useragent, "urlCdcMois", $start_date, $end_date);
								}
								
								$cmd = $sigri_linky->getCmd(null, 'consoan');
								if (is_object($cmd)) {
									$end_date = new DateTime('first day of January');
									$start_date = new DateTime('first day of January');
									$start_date->sub(new DateInterval('P5Y'));
									$sigri_linky->Call_Enedis_API($API_cookies, $Useragent, "urlCdcAn", $start_date, $end_date);
								}
							}
						}
						log::add('sigri_linky', 'info', 'Fin d\'interrogration Enedis');
					} else {
						log::add('sigri_linky', 'error', 'Identifiants requis');
					}
				}
			}
		}
		
		public function Login_Enedis_API($Useragent) 
		{
			log::add('sigri_linky', 'debug', 'Tentative d\'authentification sur Enedis');
			
			$URL_LOGIN = "https://espace-client-connexion.enedis.fr/auth/UI/Login";
			$URL_ACCUEIL = "https://espace-client-particuliers.enedis.fr/group/espace-particuliers/accueil";
			
			$data = array(
				"IDToken1=".urlencode($this->getConfiguration('identifiant')),
				"IDToken2=".urlencode($this->getConfiguration('password')),
				"SunQueryParamsString=".base64_encode('realm=particuliers'),
				"encoded=true",
				"gx_charset=UTF-8",
			);
			
			for ($login_phase1_attemps = 1; $login_phase1_attemps <= 11; $login_phase1_attemps++) {
				
				if ($login_phase1_attemps == 11) {
					log::add('sigri_linky', 'error', 'Erreur de connexion au site Enedis (Phase 1)');
					exit(1);
				}
				log::add('sigri_linky', 'debug', 'Connexion au site Enedis Phase 1 : Tentative '.$login_phase1_attemps.'/10');
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, implode('&', $data));
				curl_setopt($ch, CURLOPT_URL, $URL_LOGIN);
				curl_setopt($ch, CURLOPT_HEADER  ,1);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER  ,1);
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION  ,0);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
				$content = curl_exec($ch);
				$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
				curl_close($ch);
				
				if ($http_status == "302") { 
					preg_match_all('|Set-Cookie: (.*);|U', $content, $cookiesheader);   
					$ResponseCookie = $cookiesheader[1];
					foreach($ResponseCookie as $key => $val) {
						$cookie_explode = explode('=', $val);
						$cookies[$cookie_explode[0]]=$cookie_explode[1];
					}
					$cookie_iPlanetDirectoryPro = $cookies['iPlanetDirectoryPro'];
					if($cookie_iPlanetDirectoryPro === "LOGOUT") {
						log::add('sigri_linky', 'error', 'Erreur d\'identification');
						exit(1);
						} else {
						log::add('sigri_linky', 'info', 'Connexion au site Enedis Phase 1 : OK');
						break;
					}
				}
			}
			
			$headers = array(
				"Cookie: iPlanetDirectoryPro=".$cookie_iPlanetDirectoryPro
			);
			
			for ($login_phase2_attemps = 1; $login_phase2_attemps <= 11; $login_phase2_attemps++) {
				
				if ($login_phase2_attemps == 11) {
					log::add('sigri_linky', 'error', 'Erreur de connexion au site Enedis (Phase 2)');
					exit(1);
				}
				log::add('sigri_linky', 'debug', 'Connexion au site Enedis Phase 2 : Tentative '.$login_phase2_attemps.'/10');
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $URL_ACCUEIL);
				curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
				curl_setopt($ch, CURLOPT_HEADER  ,1);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER  ,1);
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION  ,0);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
				curl_setopt($ch, CURLOPT_USERAGENT, $Useragent);
				$content = curl_exec($ch);
				$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
				curl_close($ch);
				
				if ($http_status == "302") { 
					preg_match_all('|Set-Cookie: (.*);|U', $content, $cookiesheader);   
					$ResponseCookie = $cookiesheader[1];
					foreach($ResponseCookie as $key => $val) {
						$cookie_explode = explode('=', $val);
						$cookies[$cookie_explode[0]]=$cookie_explode[1];
					}
					$cookie_JSESSIONID = $cookies['JSESSIONID'];
					log::add('sigri_linky', 'info', 'Connexion au site Enedis Phase 2 : OK');
					break;
				}
				
			}
			
			$API_cookies = array(
			"Cookie: iPlanetDirectoryPro=".$cookie_iPlanetDirectoryPro,
			"Cookie: JSESSIONID=".$cookie_JSESSIONID,
			);
			
			log::add('sigri_linky', 'debug', 'Cookies d\'authentification OK : '.print_r($API_cookies));
			
			log::add('sigri_linky', 'debug', 'Verification si demande des conditions d\'utilisation');
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $URL_ACCUEIL);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $API_cookies);
			curl_setopt($ch, CURLOPT_HEADER  ,1);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER  ,1);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION  ,1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_USERAGENT, $Useragent);
			$content = curl_exec($ch);
			$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);
			
			if ($http_status == "200") { 
				preg_match("/\<title.*\>(.*)\<\/title\>/isU", $content, $matches);
				if (strpos($matches[1], "Conditions d'utilisation") !== false) {
					log::add('sigri_linky', 'error', 'Enedis vous demande de reconfirmer les conditions d\'utilisation, merci de vous reconnecter via leur site web');
					exit(1);
					} else {
					log::add('sigri_linky', 'debug', 'Pas de demande de conditions d\'utilisation : OK');
				}
			}
			return $API_cookies;
		}
		
		public function Call_Enedis_API($cookies, $Useragent, $resource_id, $start_datetime=None, $end_datetime=None) 
		{
			$URL_CONSO = "https://espace-client-particuliers.enedis.fr/group/espace-particuliers/suivi-de-consommation";
			
			$prefix = '_lincspartdisplaycdc_WAR_lincspartcdcportlet_';
			
			$start_date = $start_datetime->format('d/m/Y');
			$end_date = $end_datetime->format('d/m/Y');
			
			$data = array(
				$prefix."dateDebut"."=".$start_date,
				$prefix."dateFin"."=".$end_date
			);
			
			$param = array(
				"p_p_id=lincspartdisplaycdc_WAR_lincspartcdcportlet",
				"p_p_lifecycle=2",
				"p_p_state=normal",
				"p_p_mode=view",
				"p_p_resource_id=".$resource_id,
				"p_p_cacheability=cacheLevelPage",
				"p_p_col_id=column-1",
				"p_p_col_pos=1",
				"p_p_col_count=3"
			);
			
			for ($retreive_attemps = 1; $retreive_attemps <= 11; $retreive_attemps++) {
				
				if ($retreive_attemps == 11) {
					log::add('sigri_linky', 'error', 'Erreur lors de la récupération des données ('.$resource_id.') depuis Enedis');
					break;
				}
				log::add('sigri_linky', 'info', 'Recupération des données ('.$resource_id.') depuis Enedis : Tentative '.$retreive_attemps.'/10');
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $URL_CONSO."?".implode('&', $param));
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, implode('&', $data));
				curl_setopt($ch, CURLOPT_HTTPHEADER, $cookies);
				curl_setopt($ch, CURLOPT_HEADER, 0);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
				curl_setopt($ch, CURLOPT_USERAGENT, $Useragent);
				$content = curl_exec($ch);
				$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
				curl_close($ch);
				
				if ($http_status == "200") {
					$this->Enedis_Results_Jeedom($resource_id, $content, $start_datetime);
					log::add('sigri_linky', 'info', 'Recupération des données ('.$resource_id.') depuis Enedis : OK');
					break;
				}
			}
		}
		
		public function Enedis_Results_Jeedom($resource_id, $content, $start_datetime) {
			$obj = json_decode($content, true);
			log::add('sigri_linky', 'debug',var_dump($obj));
			
			if ($obj['etat']['valeur'] == "erreur") {
				log::add('sigri_linky', 'error', 'Enedis renvoi une erreur sur la page '.$resource_id);
				if (isset($obj['etat']['erreurText'])) { 
					log::add('sigri_linky', 'error', 'Message d\'erreur : '.$obj['etat']['erreurText']);
				}
			} else {
				if ($resource_id == "urlCdcHeure") {
					log::add('sigri_linky', 'debug', 'Traitement données heures');
					$cmd = $this->getCmd(null, 'consoheure');
					$delta = "30 minutes";
					$start_date = $start_datetime;
					$date_format = "Y-m-d H:i:00";
					} elseif ($resource_id == "urlCdcJour") { 
					log::add('sigri_linky', 'debug', 'Traitement données jours');
					$cmd = $this->getCmd(null, 'consojour');
					$delta = "1 day";
					$start_date = $obj['graphe']['periode']['dateDebut'];
					$start_date = date_create_from_format('d/m/Y', $start_date);
					$date_format = "Y-m-d";
					} elseif ($resource_id == "urlCdcMois") { 
					log::add('sigri_linky', 'debug', 'Traitement données mois');
					$cmd = $this->getCmd(null, 'consomois');
					$delta = "1 month";
					$start_date = $obj['graphe']['periode']['dateDebut'];
					$start_date = date_create_from_format('d/m/Y', $start_date);
					$date_format = "Y-m-d";
					} elseif ($resource_id == "urlCdcAn") { 
					$cmd = $this->getCmd(null, 'consoan');
					log::add('sigri_linky', 'debug', 'Traitement données ans');
					$delta = "1 year";
					$start_date = $obj['graphe']['periode']['dateDebut'];
					$start_date = date_create_from_format('d/m/Y', $start_date);
					$start_date = date_create($start_date->format('Y-1-1'));
					$date_format = "Y-m-d";
				}
				
				foreach ($obj['graphe']['data'] as &$value) {
					$jeedom_event_date = $start_date->format($date_format);
					if ($value['valeur'] == "-1" OR $value['valeur'] == "-2") {
						log::add('sigri_linky', 'debug', 'Date : '.$jeedom_event_date.' : Valeur incorrect : '.$value['valeur']);
					} else {
						log::add('sigri_linky', 'debug', 'Date : '.$jeedom_event_date.' : Indice : '.$value['valeur'].' kWh');
						$cmd->event($value['valeur'], $jeedom_event_date);
					}
					date_add($start_date,date_interval_create_from_date_string($delta));
				}
			}
		}
		
		public function GetUserAgent() {
			$useragents = array(
			"Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/525.19 (KHTML, like Gecko) Chrome/1.0.154.53 Safari/525.19",
			"Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/525.19 (KHTML, like Gecko) Chrome/1.0.154.36 Safari/525.19",
			"Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US) AppleWebKit/534.10 (KHTML, like Gecko) Chrome/7.0.540.0 Safari/534.10",
			"Mozilla/5.0 (Windows; U; Windows NT 5.2; en-US) AppleWebKit/534.4 (KHTML, like Gecko) Chrome/6.0.481.0 Safari/534.4",
			"Mozilla/5.0 (Macintosh; U; Intel Mac OS X; en-US) AppleWebKit/533.4 (KHTML, like Gecko) Chrome/5.0.375.86 Safari/533.4",
			"Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/532.2 (KHTML, like Gecko) Chrome/4.0.223.3 Safari/532.2",
			"Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US) AppleWebKit/532.0 (KHTML, like Gecko) Chrome/4.0.201.1 Safari/532.0",
			"Mozilla/5.0 (Windows; U; Windows NT 5.2; en-US) AppleWebKit/532.0 (KHTML, like Gecko) Chrome/3.0.195.27 Safari/532.0",
			"Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/530.5 (KHTML, like Gecko) Chrome/2.0.173.1 Safari/530.5",
			"Mozilla/5.0 (Windows; U; Windows NT 5.2; en-US) AppleWebKit/534.10 (KHTML, like Gecko) Chrome/8.0.558.0 Safari/534.10",
			"Mozilla/5.0 (X11; U; Linux x86_64; en-US) AppleWebKit/540.0 (KHTML,like Gecko) Chrome/9.1.0.0 Safari/540.0",
			"Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/534.14 (KHTML, like Gecko) Chrome/9.0.600.0 Safari/534.14",
			"Mozilla/5.0 (X11; U; Windows NT 6; en-US) AppleWebKit/534.12 (KHTML, like Gecko) Chrome/9.0.587.0 Safari/534.12",
			"Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US) AppleWebKit/534.13 (KHTML, like Gecko) Chrome/9.0.597.0 Safari/534.13",
			"Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US) AppleWebKit/534.16 (KHTML, like Gecko) Chrome/10.0.648.11 Safari/534.16",
			"Mozilla/5.0 (Windows; U; Windows NT 6.0; en-US) AppleWebKit/534.20 (KHTML, like Gecko) Chrome/11.0.672.2 Safari/534.20",
			"Mozilla/5.0 (Windows NT 6.0) AppleWebKit/535.1 (KHTML, like Gecko) Chrome/14.0.792.0 Safari/535.1",
			"Mozilla/5.0 (Windows NT 5.1) AppleWebKit/535.2 (KHTML, like Gecko) Chrome/15.0.872.0 Safari/535.2",
			"Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/535.7 (KHTML, like Gecko) Chrome/16.0.912.36 Safari/535.7",
			"Mozilla/5.0 (Windows NT 6.0; WOW64) AppleWebKit/535.11 (KHTML, like Gecko) Chrome/17.0.963.66 Safari/535.11",
			"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_6_8) AppleWebKit/535.19 (KHTML, like Gecko) Chrome/18.0.1025.45 Safari/535.19",
			"Mozilla/5.0 (Windows NT 6.2; WOW64) AppleWebKit/535.24 (KHTML, like Gecko) Chrome/19.0.1055.1 Safari/535.24",
			"Mozilla/5.0 (Windows NT 6.2) AppleWebKit/536.6 (KHTML, like Gecko) Chrome/20.0.1090.0 Safari/536.6",
			"Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.1 (KHTML, like Gecko) Chrome/22.0.1207.1 Safari/537.1",
			"Mozilla/5.0 (Windows NT 6.2; WOW64) AppleWebKit/537.15 (KHTML, like Gecko) Chrome/24.0.1295.0 Safari/537.15",
			"Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/27.0.1453.93 Safari/537.36",
			"Mozilla/5.0 (Windows NT 6.2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/28.0.1467.0 Safari/537.36",
			"Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/30.0.1599.101 Safari/537.36",
			"Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/31.0.1623.0 Safari/537.36",
			"Mozilla/5.0 (Windows NT 6.2; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/34.0.1847.116 Safari/537.36",
			"Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/37.0.2062.103 Safari/537.36",
			"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/40.0.2214.38 Safari/537.36",
			"Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/46.0.2490.71 Safari/537.36",
			"Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.103 Safari/537.36",
			"Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.62 Safari/537.36",
			"Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10.5; en-US; rv:1.9.1b3) Gecko/20090305 Firefox/3.1b3 GTB5",
			"Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10.5; ko; rv:1.9.1b2) Gecko/20081201 Firefox/3.1b2",
			"Mozilla/5.0 (X11; U; SunOS sun4u; en-US; rv:1.9b5) Gecko/2008032620 Firefox/3.0b5",
			"Mozilla/5.0 (X11; U; Linux x86_64; en-US; rv:1.8.1.12) Gecko/20080214 Firefox/2.0.0.12",
			"Mozilla/5.0 (Windows; U; Windows NT 5.1; cs; rv:1.9.0.8) Gecko/2009032609 Firefox/3.0.8",
			"Mozilla/5.0 (X11; U; OpenBSD i386; en-US; rv:1.8.0.5) Gecko/20060819 Firefox/1.5.0.5",
			"Mozilla/5.0 (Windows; U; Windows NT 5.0; es-ES; rv:1.8.0.3) Gecko/20060426 Firefox/1.5.0.3",
			"Mozilla/5.0 (Windows; U; WinNT4.0; en-US; rv:1.7.9) Gecko/20050711 Firefox/1.0.5",
			"Mozilla/5.0 (Windows; Windows NT 6.1; rv:2.0b2) Gecko/20100720 Firefox/4.0b2",
			"Mozilla/5.0 (X11; Linux x86_64; rv:2.0b4) Gecko/20100818 Firefox/4.0b4",
			"Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.2) Gecko/20100308 Ubuntu/10.04 (lucid) Firefox/3.6 GTB7.1",
			"Mozilla/5.0 (Windows NT 6.1; WOW64; rv:2.0b7) Gecko/20101111 Firefox/4.0b7",
			"Mozilla/5.0 (Windows NT 6.1; WOW64; rv:2.0b8pre) Gecko/20101114 Firefox/4.0b8pre",
			"Mozilla/5.0 (X11; Linux x86_64; rv:2.0b9pre) Gecko/20110111 Firefox/4.0b9pre",
			"Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:2.0b9pre) Gecko/20101228 Firefox/4.0b9pre",
			"Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:2.2a1pre) Gecko/20110324 Firefox/4.2a1pre",
			"Mozilla/5.0 (X11; U; Linux amd64; rv:5.0) Gecko/20100101 Firefox/5.0 (Debian)",
			"Mozilla/5.0 (Windows NT 6.1; WOW64; rv:6.0a2) Gecko/20110613 Firefox/6.0a2",
			"Mozilla/5.0 (X11; Linux i686 on x86_64; rv:12.0) Gecko/20100101 Firefox/12.0",
			"Mozilla/5.0 (Windows NT 6.1; rv:15.0) Gecko/20120716 Firefox/15.0a2",
			"Mozilla/5.0 (X11; Ubuntu; Linux armv7l; rv:17.0) Gecko/20100101 Firefox/17.0",
			"Mozilla/5.0 (Windows NT 6.1; rv:21.0) Gecko/20130328 Firefox/21.0",
			"Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:22.0) Gecko/20130328 Firefox/22.0",
			"Mozilla/5.0 (Windows NT 5.1; rv:25.0) Gecko/20100101 Firefox/25.0",
			"Mozilla/5.0 (Macintosh; Intel Mac OS X 10.8; rv:25.0) Gecko/20100101 Firefox/25.0",
			"Mozilla/5.0 (Windows NT 6.1; rv:28.0) Gecko/20100101 Firefox/28.0",
			"Mozilla/5.0 (X11; Linux i686; rv:30.0) Gecko/20100101 Firefox/30.0",
			"Mozilla/5.0 (Windows NT 5.1; rv:31.0) Gecko/20100101 Firefox/31.0",
			"Mozilla/5.0 (Windows NT 6.1; WOW64; rv:33.0) Gecko/20100101 Firefox/33.0",
			"Mozilla/5.0 (Windows NT 10.0; WOW64; rv:40.0) Gecko/20100101 Firefox/40.0"
			);
			
			$rand_key = array_rand($useragents);
			return $useragents[$rand_key];
		}
	}
	
	class sigri_linkyCmd extends cmd {
		public function execute($_options = array()) {
		}
	}
?>