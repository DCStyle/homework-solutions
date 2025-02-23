<?php

use Carbon\Carbon;
use Illuminate\Support\HtmlString;

if (!function_exists('format_time')) {
    /**
     * Format a Unix timestamp into an HTML time element
     *
     * @param int|string|null $timestamp Unix timestamp
     * @param string $format Display format (default: 'D, d M Y')
     * @param array $attributes Additional HTML attributes for the time tag
     * @return HtmlString
     */
    function format_time(string $dateTimeString, array $attributes = []): HtmlString
    {
        if (empty($dateTimeString)) {
            return new HtmlString('');
        }

        $datetime = new DateTime($dateTimeString);
        $iso8601 = $datetime->format('c');
        $displayDate = $datetime->format('D, d M Y');

        // Build HTML attributes string
        $attrs = array_merge(['class' => 'relative', 'data-plugin-relative-time' => ''], $attributes);
        $attributeString = collect($attrs)
            ->map(fn ($value, $key) => sprintf('%s="%s"', $key, e($value)))
            ->implode(' ');

        return new HtmlString(sprintf(
            '<time %s datetime="%s">%s</time>',
            $attributeString,
            $iso8601,
            $displayDate
        ));
    }
}

if (!function_exists('setting')) {
    function setting($key, $default = null)
    {
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                \App\Models\Setting::updateOrCreate(
                    ['key' => $k],
                    ['value' => $v]
                );
            }
            return true;
        }

        $setting = \App\Models\Setting::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }
}
