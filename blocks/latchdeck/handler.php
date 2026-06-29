<?php

/**
 * Handles the logic for the "latchdeck" block.
 *
 * A boilerplate section that surfaces the creator's LatchDeck collectible cards
 * on their public profile. Adding the block turns the section on; removing it
 * turns it off (it behaves as a single on/off toggle).
 *
 * @param \Illuminate\Http\Request $request The incoming request.
 * @param mixed $linkType The link type information.
 * @return array The prepared link data.
 */
function handleLinkType($request, $linkType) {

    $rules = [
        'title' => ['nullable', 'string', 'max:255'],
        'speed' => ['nullable', 'string'],
    ];

    // When there are more cards than fit the theme's width they auto-scroll in a
    // loop; `speed` controls how fast. Lands in type_params ($link->speed on render).
    $speed = in_array($request->speed, ['slow', 'medium', 'fast'], true) ? $request->speed : 'slow';

    $linkData = [
        'title' => $request->title ?: 'LatchDeck',
        'button_id' => 1,
        'link' => null,
        'speed' => $speed,
    ];

    return ['rules' => $rules, 'linkData' => $linkData];
}
