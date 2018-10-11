<?php

return array(
	'administration tool desc test_mail_settings' => 'Użyj tego narzędzia by wysłać testowe wiadomości i sprawdzić czy moduł pocztowy '.product_name().' jest dobrze skonfigurowany',
	'config category desc general' => 'Ogólne ustawienia '.product_name().'.',
	'config category desc mailing' => 'Użyj tych opcji by skonfigurować sposób w jaki '.product_name().' wysyła wiadomości. Możesz użyć opcji ze swojego php.ini albo ustawić dowolny inny serwer SMTP.',
	'config category desc modules' => 'Użyj tych opcji by włączyć lub wyłączyć poszczególne moduły '.product_name().'. Wyłączenie modułu powoduje jedynie jego ukrycie na ekranie, nie usuwa ono uprawnień użytkowników do tworzenia lub edycji obiektów.',
	'config option desc theme' => 'Używając skórek możesz zmienić domyślny wygląd '.product_name().'. Musisz odświeżyć stronę by zobaczyć zmiany.',
	'config option desc upgrade_check_enabled' => 'Jeśli włączone system będzie raz dziennie sprawdzał, czy są nowe wersje '.product_name().' do pobrania',
	'backup process desc' => 'Kopia Bezpieczeństwa zapisuje stan całej aplikacji do skompresowanego katalogu. Funkcji tej można użyć by w prosty sposób utworzyć kopię bezpieczeństwa instalacji '.product_name().'. <br> Utworzenie kopii bezpieczeństwa bazy danych i systemu plików może zająć więcej niż kilka sekund, więc proces podzielono na 3 etapy: <br>1.- Start procesu tworzenia kopii bezpieczeństwa, <br>2.- Pobranie kopii bezpieczeństwa. <br> 3.- Opcjonalnie, kopia bezpieczeństwa może zostać ręcznie usunięta by nie była dostępna w przyszłości. <br> ',
	'cron events info' => 'Zdarzenia Cron pozwalają Ci na okresowe wykonywanie pewnych czynności w '.product_name().' bez potrzeby logowania się do systemu. Aby włączyć zdarzenia Cron musisz skonfigurować zadanie Cron tak by okresowo wykonywało plik \'cron.php\', zlokalizowany w katalogu głównym '.product_name().'. Cykl wykonywania tego zadania zdecyduje o tym jak często będziesz mógł wykonywać zdarzenia Cron. Przykładowo, jeśli skonfigurujesz zadanie Cron tak by wykonywane było co 5 minut, a równocześnie ustawisz zdarzenie Cron sprawdzające dostępnośc aktualizacji co 1 minutę, to zdarzenie to będzie w stanie sprawdzić dostępność aktualizacji tylko raz na 5 minut. Aby dowiedzieć się, jak skonfigurować zadanie Cron, poproś o pomoc swojego administratora systemu lub dostawcę usług hostingowych.',
	'cron event desc check_upgrade' => 'To zdarzenie Cron sprawdzi, czy nie ma nowych wersji '.product_name().' do pobrania.',
	'manual upgrade desc' => 'Aby ręcznie zaktualizować '.product_name().', musisz pobrać nową wersję '.product_name().', wypakować ją do katalogu głównego obecnej instalacji a następnie przejść do katalogu <a href=\'public/upgrade\'>\'public/upgrade\'</a> w swojej przeglądarce by rozpocząć proces aktualizacji.',
	'cron event name backup' => 'Okresowe tworzenie kopii zapasowej '.product_name().'',
	'cron event desc backup' => 'Jeśli włączone, co pewien czas będzie tworzona kopia zapasowa '.product_name().'. Administrator będzie mógł pobierać kopie zapasowe przez panel administracyjny. Kopie zapasowe '.product_name().' są trzymane w formie archiwów zip w katalogu \'tmp/backup\'',
	'cron event desc import_google_calendar' => 'Po wybraniu tej opcji wydarzenia z kalendarza Google zostaną zaimportowane do '.product_name().' pod warunkiem właściwego ustawienia.',
	'cron event name export_google_calendar' => 'Eksportuj zdarzenia z '.product_name().'',
	'cron event desc export_google_calendar' => 'Po wybraniu tej opcji wydarzenia z '.product_name().' zostaną zaimportowane do zewnetrznego  kalendarza np. kalendarza Google.',
	'user config option desc root_dimensions' => 'Po włączeniu opcji wyświetlana jest fiszka wymiarów w postaci drzewka po zalogowaniu do '.product_name().'',
	'config option name sent_mails_sync' => 'Włącz synchronizację IMAP podczas wysyłania e-maili z '.product_name().'',
	
	'feng calendar' => 'Kalendarz '.product_name().' - {0}',
	'sync event feng' => 'Zsynchronizuj wszystkie wydarzenia z '.product_name().'',

	'view object and comments' => 'Pokaż {0} i wszystkie komentarze na '.product_name().'',
	'attach from fengoffice' => 'Dołącz z '.product_name().'',

	'will this person use feng office?' => 'Czy ta osoba będzie używać '.product_name().'?',
	
	'add ticket desc' => 'Uzyskanie spersonalizowanej pomocy dot. '.product_name().'',

	'system error message' => 'Przepraszamy ale poważny błąd uniemożliwił systemowi '.product_name().' wykonanie Twojego żądania. Raport błędu został wysłany do administratora.',
	'execute action error message' => 'Przepraszamy ale system '.product_name().' nie jest obecnie w stanie wykonać Twojego żądania. Raport błędu został wysłany do administratora.',
	
	'new '.product_name().' version available' => 'Dostępna jest nowa wersja oprogramowania '.product_name().'. <a class=\'internalLink\' href=\'{0}\'  onclick=\'{1}\'>Więcej</a>.',
	'upgrade is not available' => 'Brak nowych wersji oprogramowania '.product_name().'',
	'new account step configuration info' => '<a class=\'internalLink\' href=\'{0}\'>Zarządzaj</a> ogólnymi ustawieniami '.product_name().', konfiguracją poczty, włączaj/wyłączaj moduły, etc.',
	
	'back to fengoffice' => 'Powrót do '.product_name().'',
	'upgrade fengoffice' => 'Aktualizuj '.product_name().'',
	'upgrade your fengoffice installation' => 'Aktualizuj swoją instalację '.product_name().'',
	
);