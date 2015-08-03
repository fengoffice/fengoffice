<?php 
/* 
Translated into Ukrainian for OpenGoo
Last update:  see readme_ua.txt
*/

return array(
  // General
	'invalid email address' => 'Неправильний формат email адреси',
	
  // Company validation errors
	'company name required' => 'Потрібна назва компанії',
	'company homepage invalid' => 'Формат адреси домашньої сторінки невірний',
	
  // User validation errors
	'username value required' => 'Необхідно увести ім’я користувача',
	'username must be unique' => 'Вибачте, але обране ім’я користувача вже існує',
	'email value is required' => 'Необхідно увести адресу email',
	'email address must be unique' => 'Вибачте, але обрана адреса email вже існує',
	'company value required' => 'Користувач має належати до якоїсь компанії або організації',
	'password value required' => 'Необхідно увести пароль',
	'passwords dont match' => 'Значення пароля не співпадають',
	'old password required' => 'Потрібен старий пароль',
	'invalid old password' => 'Старий пароль невірний',
	'users must belong to a company' => 'Для створення користувача контакти мають належати компанії',
	'contact linked to user' => 'Контакти зв’язані з користувачем {0}',

  	// Password validation errors
  	'password invalid min length' => 'Довжина пароля має бути не меншою за {0} символів',
  	'password invalid numbers' => 'Пароль має містити щонайменше {0} числових символів',
  	'password invalid uppercase' => 'Пароль має містити щонайменше {0} великих літер',
  	'password invalid metacharacters' => 'Пароль має містити щонайменше {0} метасимволів',
  	'password exists history' => 'Пароль уже був використаним в останніх десяти паролях',
  	'password invalid difference' => 'Пароль має відрізнятися від останніх 10 паролів щонайменше 3-ма символами',
  	'password expired' => 'Термін дії вашого пароля скінчився',
  	'password invalid' => 'Ваш пароль уже недійсний',

  // Avatar
	'invalid upload type' => 'Невірний тип файлу. Допустимі типи файлів - {0}',
	'invalid upload dimensions' => 'Недопустимий розмір зображення. Максимальний розмір - {0}x{1} пікселів',
	'invalid upload size' => 'Недопустимий розмір зображення. Максимальний розмір - {0}',
	'invalid upload failed to move' => 'Не вдалося перемістити завантажений файл',

  // Registration form	
	'terms of services not accepted' => 'Для того, щоб створити рахунок, Ви маєте прочитати та прийняти умови сервіса',

  // Init company website	
	'failed to load company website' => 'Не вдалося завантажити веб-сайт. Не знайдено основну компанію',
	'failed to load project' => 'Не вдалося завантажити активний проект',

  // Login form	
	'username value missing' => 'Будь ласка, уведіть ім’я користувача',
	'password value missing' => 'Будь ласка, уведіть пароль',
	'invalid login data' => 'Не вдалося увійти до системи. Будь ласка, перевірте ім’я користувача та його пароль і спробуйте знову',

  // Add project form	
	'project name required' => 'Необхідно увести ім’я проекта',
	'project name unique' => 'Ім’я проекта має бути унікальним',

  // Add message form	
	'message title required' => 'Необхідно увести заголовок',
	'message title unique' => 'Заголовок не повинен повторюватися в одному проекті',
	'message text required' => 'Необхідно увести текст',

  // Add comment form	
	'comment text required' => 'Необхідно увести текст коментаря',

  // Add milestone form	
	'milestone name required' => 'Необхідно увести назву етапу',
	'milestone due date required' => 'Необхідно увести дату виконання етапу',

  // Add task list	
	'task list name required' => 'Необхідно увести назву завдання',
	'task list name unique' => 'Назва завдання має бути унікальною в межах проекту',
	'task title required' => 'Необхідно увести заголовок завдання',
	
  // Add task
  'task text required' => 'Необхідно увести текст завдання',
	'repeat x times must be a valid number between 1 and 1000' => 'Кількість повторів має бути дійсним числом від 1 до 1000',
	'repeat period must be a valid number between 1 and 1000' => 'Період повтора має бути дійсним числом від 1 до 1000',
  'to repeat by start date you must specify task start date' => 'Щоб повернутись до дати початку, ви маєте вказати дату запуска завдання',
	'to repeat by due date you must specify task due date' => 'Щоб перейти до певної дати, ви маєте вказати неї',
	'task cannot be instantiated more times' => 'Завдання не може більше виконуватись, це останній раз.',

  // Add event		
	'event subject required' => 'Необхідно увести тему події',
	'event description maxlength' => 'Опис не повинен перебільшувати 3000 символів',
	'event subject maxlength' => 'Тема може містити до 100 символів',

  // Add project form	
	'form name required' => 'Необхідно увести назву форми',
	'form name unique' => 'Назва форми має бути унікальною',
	'form success message required' => 'Необхідна позначка успішного виконання',
	'form action required' => 'Необхідно вказати дії форми',
	'project form select message' => 'Будь ласка, оберіть нотатку',
	'project form select task lists' => 'Будь ласка, оберіть завдання',
	
  // Submit project form
	'form content required' => 'Будь ласка, вставте вміст до текстового поля',
	
  // Validate project folder
	'folder name required' => 'Необхідно вказати ім’я теки',
	'folder name unique' => 'Ім’я теки має бути унікальним у межах проекту',
	
  // Validate add/edit file form
	'folder id required' => 'Будь ласка, оберіть теку',
	'filename required' => 'Необхідно увести ім’я файлу',
	'weblink required' => 'Потрібно Веб-посилання',
	
  // File revisions (internal)
	'file revision file_id required' => 'Версія має бути зв’язана з файлом',
	'file revision filename required' => 'Необхідно увести ім’я файлу',
	'file revision type_string required' => 'Невідомий тип файлу',
	'file revision comment required' => 'Необхідно додати коментар для цієї редакції',
	
  // Test mail settings
	'test mail recipient required' => 'Необхідно увести адресу отримувача',
	'test mail recipient invalid format' => 'Неправильний формат адреси отримувача',
	'test mail message required' => 'Необхідно увести повідомлення',
	
  // Mass mailer
	'massmailer subject required' => 'Необхідно увести тему листа',
	'massmailer message required' => 'Необхідно увести текст повідомлення',
	'massmailer select recepients' => 'Будь ласка, оберіть користувачів, які отримають Ваше повідомлення',
	
  // Email module
	'mail account name required' => 'Необхідно увести назву облікового запису',
	'mail account id required' => 'Необхідно увести ID рахунку',
	'mail account server required' => 'Потрібен сервер',
	'mail account password required' => 'Потрібен пароль',
	'send mail error' => 'Помилка при відправці пошти. Можливо, неправильні налаштування SMTP.',
	'email address already exists' => 'Ця адреса вже використовується',
  
	'session expired error' => 'Сесія закрита у зв’язку з відсутністю активності користувача. Будь ласка, зайдіть знову',
	'unimplemented type' => 'Не використаний тип',
	'unimplemented action' => 'Не використана дія',
	
	'workspace own parent error' => 'Проект не може бути батьківським самому собі',
	'task own parent error' => 'Завдання не може бути батьківським саме собі',
	'task child of child error' => 'Завдання не може бути нащадком одного з власних нащадків',
	
	'chart title required' => 'Необхідно увести заголовок діаграми.',
	'chart title unique' => 'Заголовок діаграми має бути унікальним.',
	'must choose at least one workspace error' => 'Ви повинні обрати хоча б один проект для розміщення об’єкта у ньому.',
	
	'user has contact' => 'Цей контакт уже існує',

	'maximum number of users reached error' => 'У системі зареєстрована максимальна кількість користувачів',
	'maximum number of users exceeded error' => 'Перевищено максимально допустиму кількість користувачів у системі. Програма не зможе працювати, поки Ви не виправите цю ситуацію.',
	'maximum disk space reached' => 'Ваша дискова квота скінчилась. Будь ласка, видаліть щось перед тим як додати нове або зверніться до адміністратора.',
  'name must be unique' => 'Вибачте, але обране ім’я вже використовується',
	'not implemented' => 'Не виконано',
	'return code' => 'Код повернення: {0}',
	'task filter criteria not recognised' => 'Критерії фільтру \'{0}\' завдання не розпізнані',
  'mail account dnx' => 'Поштової скриньки не існує',
   'error document checked out by another user' => 'Цей документ заблоковано іншим користувачем',
  
  //Custom properties
  'custom property value required' => '{0} має бути',
  'value must be numeric' => 'Значення для {0} має бути числовим',
  'values cannot be empty' => 'Значення для {0} не повинно бути пустим',
  
  //Reports
  'report name required' => 'Потрібна назва звіту',
  'report object type required' => 'Потрібен тип об’єкта для звіту',

  'error assign task user dnx' => 'Спроба асоціювати наявного користувача',
	'error assign task permissions user' => 'Ви не маєте права ставити завдання цьому користувачеві',
	'error assign task company dnx' => 'Спроба асоціювати наявну компанію',
	'error assign task permissions company' => 'Ви не маєте права ставити завдання цій компанії',
  	'account already being checked' => 'Аккаунт уже перевірено.',
  	'no files to compress' => 'Немає файлів для стиснення',
  
  	//Subscribers
  	'cant modify subscribers' => 'Не вдалося змінити перелік тих, хто підписаний',
  	'this object must belong to a ws to modify its subscribers' => 'Цей об’єкт має належати до проекту, щоб уможливити зміну переліку тих, хто на нього підписаний',

  	'mailAccount dnx' => 'Облікового запису електронної пошти не існує',
  	'error add contact from user' => 'Не вдалося додати контакт із користувачем.',
  	'zip not supported' => 'ZIP не підтримується сервером.',

);

?>
