<?php 
	/*
	* Első magyar fordítás 1.3 verzióig: Váczy Attila <vaczy.a.m@gmail.com>
	* Magyar fordítás 1.4.2 verzióig (az 1.3 bázis részbeni újrafordításával, módosításával): Lukács Péter <programozo@lukacspeter.hu>
	*/

	return array(
	'invalid email address' => 'Az email cím formátuma nem érvényes',
	'company name required' => 'Cég-, szervezetnév megadása kötelező',
	'company homepage invalid' => 'A honlap címe (URL) nem érvényes',
	'username value required' => 'Felhasználói név megadása kötelező',
	'username must be unique' => 'Sajnálom, de a megadott felhasználói név már létezik',
	'email value is required' => 'Kötelező érvényes email címet megadni',
	'email address must be unique' => 'Sajnálom, de a megadott email címmel már regisztrálta magát valaki',
	'company value required' => 'A felhasználónak kötelező valamelyik szervezet tagjának lennie',
	'password value required' => 'Jelszó megadása kötelező',
	'passwords dont match' => 'Nem egyezik meg a jelszó',
	'old password required' => 'A régi jelszó megadása kötelező',
	'invalid old password' => 'A régi jelszó nem érvényes',
	'users must belong to a company' => 'A személynek kötelezően az egyik szervezethez kell tartoznia, hogy felhasználó jöhessen létre',
	'contact linked to user' => 'A személy a {0} felhasználóhoz tartozik',

	// Password validation errors
  	'password invalid min length' => 'A jelszónak legalább {0} karakter hosszúságúnak kell lennie',
  	'password invalid numbers' => 'A jelszónak legalább {0} számot kell tartalmaznia',
  	'password invalid uppercase' => 'A jelszónak legalább {0} nagy betűt kell tartalmaznia',
  	'password invalid metacharacters' => 'A jelszónak legalább {0} egyéb írásjelet kell tartalmaznia',
  	'password exists history' => 'A megadott jelszót már egy korábbi alkalommal (utolsó tíz) használta',
  
    'password invalid difference' => 'A megadott jelszónak eltérőnek kell lennie legalább 3 karakterrel a korábbiakhoz (utolsó tíz) képest',
  	'password expired' => 'A jelszava lejárt',
  	'password invalid' => 'A jelszava már nem érvényes többé',
    
   
  	'invalid upload type' => 'Érvénytelen fájl típus! Az érvényes fájl típusok: {0}',
	'invalid upload dimensions' => 'Érvénytelen méret! A maximális méret {0}x{1} pixel',
	'invalid upload size' => 'Érvénytelen kép méret. A maximális méret {0}',
	'invalid upload failed to move' => 'Nem sikerült a fájl feltöltése',
	'terms of services not accepted' => 'Új felhasználói fiók létrehozásához el kell olvasnia és el kell fogadnia a Felhasználási feltételeket',
	'failed to load company website' => 'Nem sikerült a honlap betöltése. Az alapszervezet nem található',
	'failed to load project' => 'Nem sikerült az aktív projekt betöltése',
	'username value missing' => 'Add meg a felhasználói nevét',
	'password value missing' => 'Add meg a jelszavát',
	'invalid login data' => 'Nem sikerült a beléptetése. Ellenőrizze a belépési adatait, és próbálja újra',
	'project name required' => 'Kötelező projekt nevet megadni',
	'project name unique' => 'A projekt nevének egyedinek kell lennie',
	'message title required' => 'Kötelező a főcím megadása',
	'message title unique' => 'A főcímnek egy projekten belül egyedinek kell lennie',
	'message text required' => 'Kötelező szöveg megadása',
	'comment text required' => 'A feljegyzésnek kötelezően szöveget kell tartalmaznia',
	'milestone name required' => 'Kötelező a mérföldkő névének megadása',
	'milestone due date required' => 'Kötelező a mérföldkőhöz határidőt rendelni',
	'task list name required' => 'Kötelező a feladat névének megadása',
	'task list name unique' => 'A feladat nevének egy projekten belül egyedinek kell lennie',
	'task title required' => 'Kötelező a feladat főcímének megadása',
	'task text required' => 'Kötelező szöveg megadása',
	'event subject required' => 'Kötelező az esemény tárgyának megadása',
	'event description maxlength' => 'A leírás maximum 3000 karakter hosszú lehet',
	'event subject maxlength' => 'A tárgy maximum 100 karakter hosszú lehet',
	'form name required' => 'Kötelező a nyomtatvány nevének megadása',
	'form name unique' => 'A nyomtatvány nevének egyedinek kell lennie',
	'form success message required' => 'Siker feljegyzés megadása kötelező',
	'form action required' => '"Nyomtatvány tevékenység"??? kötelező',
	'project form select message' => 'Válasszon feljegyzést',
	'project form select task lists' => 'Válasszon feladatot',
	'form content required' => 'Írjon be valamit a szöveg mezőbe',
	'folder name required' => 'Kötelező mappa név megadása',
	'folder name unique' => 'A mappa nevének egy projekten belül egyedinek kell lennie',
	'folder id required' => 'Válasszon mappát',
	'filename required' => 'Kötelező a fájl nevének megadása',
	'weblink required' => 'Web cím url megadása kötelező',
  

	'file revision file_id required' => 'A verziónak kapcsolódnia kell egy fájlhoz',
	'file revision filename required' => 'Kötelező fájlnév megadása',
	'file revision type_string required' => 'Ismeretlen fájl típus',
  'file revision comment required' => 'Verzió megjegyzés hozzáadása szükséges',
 
	'test mail recipient required' => 'Kötelező a címzett megadása',
	'test mail recipient invalid format' => 'A címzett cím formátuma nem érvényes',
	'test mail message required' => 'Email üzenet megadása kötelező',

	'massmailer subject required' => 'Kötelező megadni az üzenet tárgyát',
	'massmailer message required' => 'Kötelező megadni az üzenet szövegét',
	'massmailer select recepients' => 'Válassza ki azokat a felhasználókat akik kapják az üzenetét',

	'mail account name required' => 'Felhasználói név megadása kötelező',
	'mail account id required' => 'Felhasználói azonosító (Id) megadása kötelező',
	'mail account server required' => 'Szerver név megadása kötelező',
	'mail account password required' => 'Jelszó megadása kötelező',
	'send mail error' => 'Hiba az email küldése során. Talán helytelenül lett megadva a levélküldő szerver (SMTP) néhány beállítása?',
	'email address already exists' => 'Az email cím már használatban van',

	'session expired error' => 'Aktivitás hiánya miatt lejárt munkamenetének ideje. Lépjen be újra!',
	'unimplemented type' => 'Nem alkalmazható típus',
	'unimplemented action' => 'Nem alkalmazható esemény',

	'workspace own parent error' => 'A projekt nem lehet saját maga szülője',
	'task own parent error' => 'A feladat nem lehet saját maga szülője',
	'task child of child error' => 'A feladat nem lehet gyermeke saját leszármazójának.',

	'chart title required' => 'Grafikon cím megadása kötelező',
	'chart title unique' => 'A grafikon címének egyedinek kell lennie',
	'must choose at least one workspace error' => 'Legalább egy projektet ki kell választania, amihez kapcsolódhat az elem',

	'user has contact' => 'Már egy személy hozzá van rendelve ehhez a felhasználóhoz',

	'maximum number of users reached error' => 'A rendszer elérte a felhasználók létszámának lehetséges maximumát',
	'maximum number of users exceeded error' => 'A felhasználók létszáma túllépte a lehetséges maximumot. A rendszer leáll, és a probléma megoldásáig nem fog működni.',
	'maximum disk space reached' => 'Az engedélyezett lemezterület megtelt. Amennyiben tud, töröljön néhány dokumentumot, mielőtt újakat adna hozzá, vagy vegye fel a kapcsolatot a rendszergazdával.',
	'error db backup' => 'Hiba az adatbázis mentése során: {0}',
	'backup command failed' => 'A mentés nem sikerült. Ellenőrizze a MYSQLDUMP_COMMAND értékét.',
	'success db backup' => 'A mentés sikerült.',
	'error create backup folder' => 'Hiba a mentési mappa létrehozás során. Nem fejezhető be a mentés',
	'error delete backup' => 'Hiba a mentett adatbázis törlése során',
	'success delete backup' => 'A mentett adatbázis törölve',
	'name must be unique' => 'Sajnálom, de az adott név már létezik',
	'not implemented' => 'Nem végrehajtható',
	'return code' => 'Eredmény kód: {0}',
	'task filter criteria not recognised' => 'A \'{0}\' feladat szűrési feltétel nem értelmezhető',
	'mail account dnx' => 'Az email postafiók nem létezik',
	 'error document checked out by another user' => 'A dokumentumot más felhasználó már lefoglalta.',
  	//Custom properties
  	'custom property value required' => '{0} megadása kötelező',
  	'value must be numeric' => '{0} értéke(i)nek számnak kell lennie',
  	'values cannot be empty' => '{0} értéke(i) nem lehet üres',
  
  	//Reports
  	'report name required' => 'Jelentés elnevezés megadása kötelező',
  	'report object type required' => 'Jelentésben szereplő objektum típus megadása kötelező',

  	'error assign task user dnx' => 'Nem létező felhasználóhoz próbálta hozzárendelni',
	'error assign task permissions user' => 'Ön nem rendelkezik a feladat máshoz rendeléséhez szükséges jogokkal',
	'error assign task company dnx' => 'Nem létező szervezethez próbálta hozzárendelni',
	'error assign task permissions company' => 'Ön nem rendelkezik a feladat szervezethez rendeléséhez szükséges jogokkal',

); ?>
