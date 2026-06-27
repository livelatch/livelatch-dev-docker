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
    ];

    $linkData = [
        'title' => $request->title ?: 'Stream Schedule',
        'button_id' => 1,
        'link' => null,
    ];

    return ['rules' => $rules, 'linkData' => $linkData];
}
