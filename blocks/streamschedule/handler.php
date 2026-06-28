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
        'title'     => ['nullable', 'string', 'max:255'],
        'days'      => ['nullable', 'integer'],
        'show_esrb' => ['nullable', 'boolean'],
    ];

    $days = (int) ($request->days ?? 7);
    if (!in_array($days, [3, 7, 14, 30], true)) {
        $days = 7;
    }

    // `days` / `show_esrb` aren't links columns, so they land in type_params and
    // are merged back onto $link on render (as $link->days / $link->show_esrb in
    // display.blade.php). ESRB ratings are hidden unless explicitly switched on.
    $linkData = [
        'title' => $request->title ?: 'Stream Schedule',
        'button_id' => 1,
        'link' => null,
        'days' => $days,
        'show_esrb' => $request->boolean('show_esrb') ? 1 : 0,
    ];

    return ['rules' => $rules, 'linkData' => $linkData];
}
