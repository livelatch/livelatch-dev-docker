<?php

/**
 * Handles the "streamschedule" block.
 *
 * Surfaces the creator's next 7 days of streams (managed in the Pro Stream
 * Schedule LatchApp) on their public profile, with a one-tap calendar
 * subscription. Adding the block turns the section on; removing it turns it off.
 *
 * @param \Illuminate\Http\Request $request
 * @param mixed $linkType
 * @return array
 */
function handleLinkType($request, $linkType) {

    $rules = [
        'title' => ['nullable', 'string', 'max:255'],
        'days'  => ['nullable', 'integer'],
    ];

    $days = (int) ($request->days ?? 7);
    if (!in_array($days, [3, 7, 14, 30], true)) {
        $days = 7;
    }

    // `days` isn't a links column, so it lands in type_params and is merged back
    // onto $link on render (available as $link->days in display.blade.php).
    $linkData = [
        'title' => $request->title ?: 'Stream Schedule',
        'button_id' => 1,
        'link' => null,
        'days' => $days,
    ];

    return ['rules' => $rules, 'linkData' => $linkData];
}
