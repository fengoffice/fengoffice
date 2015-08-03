/*
 * Ext JS Library 2.1
 * Copyright(c) 2006-2008, Ext JS, LLC.
 * licensing@extjs.com
 *
 * http://extjs.com/license
 */

/*
 * Ukrainian translation
 * By Andr (utf-8 encoding)
 * 14 September 2009
 */
 
 
Ext.UpdateManager.defaults.indicatorText = '<div class="loading-indicator">Іде завантаження...</div>';

if (Ext.View) 
{
	Ext.View.prototype.emptyText = "";
}

if (Ext.grid.GridPanel) 
{
	Ext.grid.GridPanel.prototype.ddText = "{0} обраних рядків";
}

if (Ext.TabPanelItem) 
{
	Ext.TabPanelItem.prototype.closeText = "Закрити цю вкладку";
}

if (Ext.form.Field) 
{
	Ext.form.Field.prototype.invalidText = "Значення у цьому полі невірне";
}

if (Ext.LoadMask) 
{
	Ext.LoadMask.prototype.msg = "Завантаження...";
}

Date.monthNames = [ "Січень", "Лютий", "Березень", "Квітень", "Травень", "Червень", "Липень", "Серпень", "Вересень", "Жовтень", "Листопад", "Грудень" ];
Date.monthShortNames = [ "Січ", "Лют", "Бер", "Квіт", "Трав", "Чер", "Лип", "Серп", "Вер", "Жовт", "Лист", "Груд" ];

Date.getShortMonthName = function(month)
{
	return Date.monthShortNames[month];
};

Date.monthNumbers = {
	'Січ': 0,
	'Лют': 1,
	'Бер': 2,
	'Кві': 3,
	'Тра': 4,
	'Чер': 5,
	'Лип': 6,
	'Сер': 7,
	'Вер': 8,
	'Жов': 9,
	'Лис': 10,
	'Гру': 11
};

Date.getMonthNumber = function(name)
{
	return Date.monthNumbers[name.substring(0, 1).toUpperCase() + name.substring(1, 3).toLowerCase()];
};

Date.dayNames = ["Неділя", "Понеділок", "Вівторок", "Середа", "Четвер", "П’ятниця", "Субота"];

Date.getShortDayName = function(day)
{
	return Date.dayNames[day].substring(0, 3);
};

if (Ext.MessageBox) 
{
	Ext.MessageBox.buttonText = {
		ok: "OK",
		cancel: "Скасувати",
		yes: "Так",
		no: "Ні"
	};
}

if (Ext.util.Format) 
{
	Ext.util.Format.date = function(v, format)
	{
		if (!v) 
			return "";
		if (!(v instanceof Date)) 
			v = new Date(Date.parse(v));
		return v.dateFormat(format || "d.m.Y");
	};
}

if (Ext.DatePicker) 
{
	Ext.apply(Ext.DatePicker.prototype, {
		todayText: "Сьогодні",
		minText: "Ця дата менша за мінімальну дату",
		maxText: "Ця дата більша за максимальну дату",
		disabledDaysText: "",
		disabledDatesText: "",
		monthNames: Date.monthNames,
		dayNames: Date.dayNames,
		nextText: 'Наступний місяць (Ctrl+Вправо)',
		prevText: 'Попередній місяць (Ctrl+Вліво)',
		monthYearText: 'Вибір місяця (Ctrl+Уверх/Вниз для обрання року)',
		todayTip: "{0} (Пробіл)",
		format: "d.m.y",
		okText: "&#160;OK&#160;",
		cancelText: "Скасувати",
		startDay: 1
	});
}

if (Ext.PagingToolbar) 
{
	Ext.apply(Ext.PagingToolbar.prototype, {
		beforePageText: "Сторінка",
		afterPageText: "з {0}",
		firstText: "Перша сторінка",
		prevText: "Попередня сторінка",
		nextText: "Наступна сторінка",
		lastText: "Остання сторінка",
		refreshText: "Поновити",
		displayMsg: "Відображаються записи з {0} по {1}, загалом {2}",
		emptyMsg: 'Немає даних для відображення'
	});
}

if (Ext.form.TextField) 
{
	Ext.apply(Ext.form.TextField.prototype, {
		minLengthText: "Мінімальна довжина цього поля {0}",
		maxLengthText: "Максимальна довжина цього поля {0}",
		blankText: "Це поле повинно бути заповненим",
		regexText: "",
		emptyText: null
	});
}

if (Ext.form.NumberField) 
{
	Ext.apply(Ext.form.NumberField.prototype, {
		minText: "Значення цього поля не може бути меншим за {0}",
		maxText: "Значення цього поля не може бути більшим за {0}",
		nanText: "{0} не є числом"
	});
}

if (Ext.form.DateField) 
{
	Ext.apply(Ext.form.DateField.prototype, {
		disabledDaysText: "Не доступно",
		disabledDatesText: "Не доступно",
		minText: "Дата у цьому полі має бути після {0}",
		maxText: "Дата у цьому полі має бути до {0}",
		invalidText: "{0} - неправильний запис: дата має бути у форматі {1}",
		format: "d.m.y",
		altFormats: "d.m.y|d/m/Y|d-m-y|d-m-Y|d/m|d-m|dm|dmy|dmY|d|Y-m-d"
	});
}

if (Ext.form.ComboBox) 
{
	Ext.apply(Ext.form.ComboBox.prototype, {
		loadingText: "Завантаження...",
		valueNotFoundText: undefined
	});
}

if (Ext.form.VTypes) 
{
	Ext.apply(Ext.form.VTypes, {
		emailText: 'Це поле має містити адресу електронної пошти у форматі "user@domain.com"',
		urlText: 'Це поле має містити URL у форматі "http:/' + '/www.domain.com"',
		alphaText: 'Це поле має містити лише латинські літери та символ підкреслення "_"',
		alphanumText: 'Це поле має містити лише латинські літери, цифри та символ підкреслення "_"'
	});
}

if (Ext.form.HtmlEditor) 
{
	Ext.apply(Ext.form.HtmlEditor.prototype, {
		createLinkText: 'Будь ласка, уведіть адресу:',
		buttonTips: {
			bold: {
				title: 'Напівжирний (Ctrl+B)',
				text: 'Застосування напівжирного накреслення до позначеного тексту.',
				cls: 'x-html-editor-tip'
			},
			italic: {
				title: 'Курсив (Ctrl+I)',
				text: 'Застосування курсивного накреслення до позначеного тексту.',
				cls: 'x-html-editor-tip'
			},
			underline: {
				title: 'Підкреслений (Ctrl+U)',
				text: 'Підкреслення позначеного тексту.',
				cls: 'x-html-editor-tip'
			},
			increasefontsize: {
				title: 'Збільшити розмір',
				text: 'Збільшення розміру шрифта.',
				cls: 'x-html-editor-tip'
			},
			decreasefontsize: {
				title: 'Зменшити розмір',
				text: 'Зменшення розміру шрифта.',
				cls: 'x-html-editor-tip'
			},
			backcolor: {
				title: 'Заливка',
				text: 'Зміна кольору фону для позначеного тексту абзаца.',
				cls: 'x-html-editor-tip'
			},
			forecolor: {
				title: 'Колір тексту',
				text: 'Зміна кольору тексту.',
				cls: 'x-html-editor-tip'
			},
			justifyleft: {
				title: 'До лівого краю',
				text: 'Вирівнювання тексту до лівого краю.',
				cls: 'x-html-editor-tip'
			},
			justifycenter: {
				title: 'По центру',
				text: 'Вирівнювання тексту по центру.',
				cls: 'x-html-editor-tip'
			},
			justifyright: {
				title: 'До правого краю',
				text: 'Вирівнювання тексту до правого краю.',
				cls: 'x-html-editor-tip'
			},
			insertunorderedlist: {
				title: 'Маркери',
				text: 'Розпочати маркований перелік.',
				cls: 'x-html-editor-tip'
			},
			insertorderedlist: {
				title: 'Нумерація',
				text: 'Розпочати нумерований перелік.',
				cls: 'x-html-editor-tip'
			},
			createlink: {
				title: 'Додати посилання',
				text: 'Створити посилання з позначеного тексту.',
				cls: 'x-html-editor-tip'
			},
			sourceedit: {
				title: 'Початковий код',
				text: 'Переключитись на початковий код.',
				cls: 'x-html-editor-tip'
			}
		}
	});
}

if (Ext.form.BasicForm) 
{
	Ext.form.BasicForm.prototype.waitTitle = "Будь ласка, зачекайте...";
}

if (Ext.grid.GridView) 
{
	Ext.apply(Ext.grid.GridView.prototype, {
		sortAscText: "Сортувати за збільшенням",
		sortDescText: "Сортувати за зменшенням",
		lockText: "Закріпити стовпчик",
		unlockText: "Зняти закріплення стовпчика",
		columnsText: "Стовпчики"
	});
}

if (Ext.grid.GroupingView) 
{
	Ext.apply(Ext.grid.GroupingView.prototype, {
		emptyGroupText: '(Пусто)',
		groupByText: 'Групувати за цим полем',
		showGroupsText: 'Відображати по групах'
	});
}

if (Ext.grid.PropertyColumnModel) 
{
	Ext.apply(Ext.grid.PropertyColumnModel.prototype, {
		nameText: "Назва",
		valueText: "Значення",
		dateFormat: "d.m.Y"
	});
}

if (Ext.SplitLayoutRegion) 
{
	Ext.apply(Ext.SplitLayoutRegion.prototype, {
		splitTip: "Тягніть для зміни розміру",
		collapsibleSplitTip: "Тягніть для зміни розміру. Подвійний клік сховає панель."
	});
}

if (Ext.layout.BorderLayout && Ext.layout.BorderLayout.SplitRegion) 
{
	Ext.apply(Ext.layout.BorderLayout.SplitRegion.prototype, {
		splitTip: "Тягніть для зміни розміру",
		collapsibleSplitTip: "Тягніть для зміни розміру. Подвійний клік сховає панель."
	});
}
