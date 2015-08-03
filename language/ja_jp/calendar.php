<?php

return array(
	// ########## QUERY ERRORS ###########
	'CAL_QUERY_GETEVENT_ERROR' => 'データベースエラー: IDで参照できませんでした。',
	'CAL_QUERY_SETEVENT_ERROR' => 'データベースエラー: イベントをセットできませんでした。',
	// ########## SUBMENU ITEMS ###########
	'CAL_SUBM_LOGOUT' => 'ログアウト',
	'CAL_SUBM_LOGIN' => 'ログイン',
	'CAL_SUBM_ADMINPAGE' => '管理画面',
	'CAL_SUBM_SEARCH' => '検索',
	'CAL_SUBM_BACK_CALENDAR' => 'カレンダーに戻る',
	'CAL_SUBM_VIEW_TODAY' => '今日のイベントを表示',
	'CAL_SUBM_ADD' => '今日のイベントを追加',
	// ########## NAVIGATION MENU ITEMS ##########
	'CAL_MENU_BACK_CALENDAR' => 'カレンダーに戻る',
	'CAL_MENU_NEWEVENT' => '新しいイベント',
	'CAL_MENU_BACK_EVENTS' => 'イベントに戻る',
	'CAL_MENU_GO' => '実行',
	'CAL_MENU_TODAY' => '今日',
	// ########## USER PERMISSION ERRORS ##########
	'CAL_NO_READ_PERMISSION' => 'このイベントを表示する権限がありません。',
	'CAL_NO_WRITE_PERMISSION' => 'イベントを作成・編集する権限がありません。',
	'CAL_NO_EDITOTHERS_PERMISSION' => '他のユーザーのイベントを編集する権限がありません。',
	'CAL_NO_EDITPAST_PERMISSION' => '過去のイベントを作成・編集する権限がありません。',
	'CAL_NO_ACCOUNTS' => 'このカレンダーは管理者しかログオンできません。',
	'CAL_NO_MODIFY' => '更新できません。',
	'CAL_NO_ANYTHING' => 'このページを操作する権限がありません。',
	'CAL_NO_WRITE' => '新しいイベントを作成する権限がありません。',
	// ############ DAYS ############
	'CAL_MONDAY' => '月曜日',
	'CAL_TUESDAY' => '火曜日',
	'CAL_WEDNESDAY' => '水曜日',
	'CAL_THURSDAY' => '木曜日',
	'CAL_FRIDAY' => '金曜日',
	'CAL_SATURDAY' => '土曜日',
	'CAL_SUNDAY' => '日曜日',
	'CAL_SHORT_MONDAY' => '月',
	'CAL_SHORT_TUESDAY' => '火',
	'CAL_SHORT_WEDNESDAY' => '水',
	'CAL_SHORT_THURSDAY' => '木',
	'CAL_SHORT_FRIDAY' => '金',
	'CAL_SHORT_SATURDAY' => '土',
	'CAL_SHORT_SUNDAY' => '日',
	// ############ MONTHS ############
	'CAL_JANUARY' => '1月',
	'CAL_FEBRUARY' => '2月',
	'CAL_MARCH' => '3月',
	'CAL_APRIL' => '4月',
	'CAL_MAY' => '5月',
	'CAL_JUNE' => '6月',
	'CAL_JULY' => '7月',
	'CAL_AUGUST' => '8月',
	'CAL_SEPTEMBER' => '9月',
	'CAL_OCTOBER' => '10月',
	'CAL_NOVEMBER' => '11月',
	'CAL_DECEMBER' => '12月',






	// SUBMITTING/EDITING EVENT SECTION TEXT (event.php)
	'CAL_MORE_TIME_OPTIONS' => 'さらなる時刻のオプション',
	'CAL_REPEAT' => '繰り返し',
	'CAL_EVERY' => '毎',
	'CAL_REPEAT_FOREVER' => '無期限に繰り返し',
	'CAL_REPEAT_UNTIL' => '期間まで繰り返し',
	'CAL_TIMES' => '時間',
	'CAL_HOLIDAY_EXPLAIN' => '繰り返されるイベントを作成します。',
	'CAL_DURING' => '期間',
	'CAL_EVERY_YEAR' => '毎年',
	'CAL_HOLIDAY_EXTRAOPTION' => 'または、この月の最終週となるので、ここをチェックしてイベントを最後までにします。',
	'CAL_IN' => 'in',
	'CAL_PRIVATE_EVENT_EXPLAIN' => 'プライベートなイベント',
	'CAL_SUBMIT_ITEM' => 'アイテムを提出',
	'CAL_MINUTES' => '分',
	'CAL_MINUTES_SHORT' => '分',
	'CAL_TIME_AND_DURATION' => '日時と期間',
	'CAL_REPEATING_EVENT' => '繰り返しのイベント',
	'CAL_EXTRA_OPTIONS' => '拡張オプション',
	'CAL_ONLY_TODAY' => '当日のみ',
	'CAL_DAILY_EVENT' => '毎日繰り返す',
	'CAL_WEEKLY_EVENT' => '毎週繰り返す',
	'CAL_MONTHLY_EVENT' => '毎月繰り返す',
	'CAL_YEARLY_EVENT' => '毎年繰り返す',
	'CAL_HOLIDAY_EVENT' => '休日を繰り返す',
	'CAL_UNKNOWN_TIME' => '不明な開始時間',
	'CAL_ADDING_TO' => '追加',
	'CAL_ANON_ALIAS' => 'エイリアス名',
	'CAL_EVENT_TYPE' => 'イベントタイプ',

	// MULTI-SECTION RELATED TEXT (used by more than one section, but not everwhere)
	'CAL_DESCRIPTION' => '説明', // (search, view date, view event)
	'CAL_DURATION' => '期間', // (view event, view date)
	'CAL_DATE' => '日付', // (search, view date)
	'CAL_NO_EVENTS_FOUND' => 'イベントは見つかりませんでした。', // (search, view date)
	'CAL_NO_SUBJECT' => 'タイトルがありません。', // (search, view event, view date, calendar)
	'CAL_PRIVATE_EVENT' => 'プライベートなイベント', // (search, view event)
	'CAL_DELETE' => '削除', // (view event, view date, admin)
	'CAL_MODIFY' => '変更', // (view event, view date, admin)
	'CAL_NOT_SPECIFIED' => '詳細は定められていません***', // (view event, view date, calendar)
	'CAL_FULL_DAY' => '終日', // (view event, view date, calendar, submit event)
	'CAL_HACKING_ATTEMPT' => 'クラッキングの試み - IPアドレスを保存しました。', // (delete)
	'CAL_TIME' => '時間', // (view date, submit event)
	'CAL_HOURS' => '時間', // (view event, submit event)
	'CAL_HOUR' => '時間', // (view event, submit event)
	'CAL_ANONYMOUS' => '匿名', // (view event, view date, submit event),


	'CAL_SELECT_TIME' => '開始時刻の選択',

	'event invitations' => 'イベントに招待',
	'event invitations desc' => '選択した人々をイベントに招待',
	'send new event notification' => '通知メールを送信',
	'new event notification' => '新しいイベントが追加されました。',
	'change event notification' => 'イベントを変更しました。',
	'deleted event notification' => 'イベントを削除しました。',
	'attendance' => '参加しますか?',
	'confirm attendance' => '出席を承認',
	'maybe' => 'おそらく',
	'decide later' => '後で決定',
	'view event' => 'イベントを表示',
	'new event created' => '新しいイベントを作成しました。',
	'event changed' => 'イベントを変更しました。',
	'event deleted' => 'イベントを削除しました。',
	'calendar of' => '{0}のカレンダー',
	'all users' => 'すべてのユーザ',
	'error delete event' => 'イベントの削除でエラー',
	'event invitation response' => 'イベントへの招待の回答',
	'user will attend to event' => '{0}はこのイベントに出席の予定です。',
	'user will not attend to event' => '{0}はこのイベントに欠席の予定です。',
	'accept or reject invitation help, click on one of the links below' => '招待の受諾や拒否をするには、以下のリンクの1つをクリックしてください。',
	'accept invitation' => '招待を受諾',
	'reject invitation' => '招待を拒否',
	'invitation accepted' => '招待を受諾しました。',
	'invitation rejected' => '招待を拒否しました。',

	'days' => '日',
	'weeks' => '週',
	'months' => '月',
	'years' => '年',

	'invitations' => '招待',
	'pending response' => '回答待ちの状態',
	'participate' => '出席予定',
	'no invitations to this event' => 'このイベントの招待を送りません。',
	'duration must be at least 15 minutes' => '期間は15分間隔でなければなりません。',

	'event dnx' => '要求されたイベントは存在しません。',
	'no subject' => '件名なし',
	'success import events' => '{0}個のイベントをインポートしました。',
	'no events to import' => 'インポートするイベントはありません。',
	'import events from file' => 'ファイルからイベントをインポート',
	'file should be in icalendar format' => 'ファイルはiCalendar形式でなければなりません。',
	'export calendar' => 'カレンダーのエクスポート',
	'range of events' => 'イベントの範囲',
	'from date' => '開始日',
	'to date' => '終了日',
	'success export calendar' => '{0}個のイベントをエクスポートしました',
	'calendar name desc' => 'カレンダーをエクスポートする名前',
	'calendar will be exported in icalendar format' => 'カレンダーはiCalendar形式でエクスポートされます。',
	'view date title' => '{0} (l)',

	'copy this url in your calendar client software' => 'このカレンダーからイベントをインポートするには、このURLをカレンダーのクライアント・ソフトウェアにコピーしてください。',
	'import events from third party software' => '第三者のソフトウェアからイベントをインポート',
	'subws' => 'サブワークスペース',
	'check to include sub ws' => 'ここをチェックすると、URLにサブワークスペースを含めます。',
	'week short' => '週',
	'week number x' => '週番号 {0}',
	); // array
?>
