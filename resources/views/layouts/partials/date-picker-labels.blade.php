@php
    $datePickerLabels = [
        'previous' => t('dashboard.ui.previous_month', [], app()->getLocale() === 'lt' ? 'Ankstesnis mėnuo' : 'Previous month'),
        'next' => t('dashboard.ui.next_month', [], app()->getLocale() === 'lt' ? 'Kitas mėnuo' : 'Next month'),
        'month' => t('date_picker.month', [], app()->getLocale() === 'lt' ? 'Mėnuo' : 'Month'),
        'year' => t('date_picker.year', [], app()->getLocale() === 'lt' ? 'Metai' : 'Year'),
        'choose' => t('date_picker.choose', [], app()->getLocale() === 'lt' ? 'Pasirink datą' : 'Choose a date'),
        'week' => t('date_picker.week', [], app()->getLocale() === 'lt' ? 'Pasirink savaitę' : 'Choose a week'),
        'today' => t('date_picker.today', [], app()->getLocale() === 'lt' ? 'Šiandien' : 'Today'),
        'currentWeek' => t('date_picker.current_week', [], app()->getLocale() === 'lt' ? 'Dabartinė savaitė' : 'Current week'),
        'clear' => t('date_picker.clear', [], app()->getLocale() === 'lt' ? 'Išvalyti' : 'Clear'),
        'close' => t('plans.close', [], app()->getLocale() === 'lt' ? 'Uždaryti' : 'Close'),
    ];
@endphp
<meta name="aurora-date-picker-labels" content="{{ json_encode($datePickerLabels, JSON_UNESCAPED_UNICODE) }}">