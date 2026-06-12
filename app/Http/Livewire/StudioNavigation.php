<?php

namespace App\Http\Livewire;

use Livewire\Component;

class StudioNavigation extends Component
{
    public function render()
    {
        return view('livewire.studio-navigation', [
            'sections' => $this->sections(),
        ]);
    }

    private function sections(): array
    {
        $sections = [
            [
                'key' => 'home',
                'label' => __('messages.Home'),
                'icon' => 'bi bi-grid-1x2-fill',
                'items' => [
                    [
                        'label' => __('messages.Dashboard'),
                        'icon' => 'bi bi-grid-1x2-fill',
                        'url' => route('panelIndex'),
                        'active' => request()->segment(1) === 'dashboard',
                    ],
                ],
            ],
            [
                'key' => 'navigation',
                'label' => 'Navigation',
                'icon' => 'bi bi-compass-fill',
                'items' => [
                    [
                        'label' => __('messages.Links'),
                        'icon' => 'bi bi-link-45deg',
                        'url' => url('/studio/links'),
                        'active' => in_array(request()->segment(2), ['links', 'add-link'], true),
                        'skeleton' => '#ll-profile-skeleton',
                    ],
                    [
                        'label' => __('messages.Appearance'),
                        'icon' => 'bi bi-person-badge-fill',
                        'url' => url('/studio/page'),
                        'active' => request()->segment(2) === 'page',
                        'skeleton' => '#ll-profile-skeleton',
                    ],
                    [
                        'label' => __('messages.Themes'),
                        'icon' => 'bi bi-stars',
                        'url' => url('/studio/theme'),
                        'active' => request()->segment(2) === 'theme',
                        'skeleton' => '#ll-profile-skeleton',
                    ],
                ],
            ],
            [
                'key' => 'latchdeck',
                'label' => 'LatchDeck',
                'icon' => 'bi bi-grid-3x3-gap-fill',
                'items' => [
                    [
                        'label' => 'Overview',
                        'icon' => 'bi bi-grid-3x3-gap-fill',
                        'url' => url('/studio/latchdeck'),
                        'active' => request()->is('studio/latchdeck'),
                        'skeleton' => '#ll-card-grid-skeleton',
                    ],
                    [
                        'label' => 'Cards',
                        'icon' => 'bi bi-card-image',
                        'url' => url('/studio/latchdeck/cards'),
                        'active' => request()->is('studio/latchdeck/cards'),
                        'skeleton' => '#ll-card-grid-skeleton',
                    ],
                    [
                        'label' => 'Redemptions',
                        'icon' => 'bi bi-ticket-perforated-fill',
                        'url' => url('/studio/latchdeck/redemptions'),
                        'active' => request()->is('studio/latchdeck/redemptions'),
                        'skeleton' => '#ll-table-skeleton',
                    ],
                    [
                        'label' => 'Deck Settings',
                        'icon' => 'bi bi-sliders2',
                        'url' => url('/studio/latchdeck/settings'),
                        'active' => request()->is('studio/latchdeck/settings'),
                        'badge' => 'MVP',
                        'skeleton' => '#ll-card-grid-skeleton',
                    ],
                ],
            ],
            [
                'key' => 'account',
                'label' => 'Account',
                'icon' => 'bi bi-person-circle',
                'items' => [
                    [
                        'label' => 'My Subscription',
                        'icon' => 'bi bi-credit-card-fill',
                        'url' => url('/studio/subscription'),
                        'active' => request()->is('studio/subscription'),
                        'skeleton' => '#ll-table-skeleton',
                    ],
                    [
                        'label' => 'My Data',
                        'icon' => 'bi bi-database-lock',
                        'url' => url('/studio/my-data'),
                        'active' => request()->is('studio/my-data'),
                    ],
                    [
                        'label' => 'Documentation',
                        'icon' => 'bi bi-journal-richtext',
                        'url' => url('/studio/docs'),
                        'active' => request()->is('studio/docs*'),
                        'badge' => 'New',
                    ],
                ],
            ],
            [
                'key' => 'community',
                'label' => 'Community',
                'icon' => 'bi bi-people-fill',
                'items' => [
                    [
                        'label' => 'Socials',
                        'icon' => 'bi bi-share-fill',
                        'url' => url('/studio/socials'),
                        'active' => request()->is('studio/socials'),
                        'skeleton' => '#ll-card-grid-skeleton',
                    ],
                    [
                        'label' => 'Provide Feedback',
                        'icon' => 'bi bi-chat-heart-fill',
                        'url' => url('/studio/feedback'),
                        'active' => request()->is('studio/feedback'),
                        'badge' => 'Tally',
                    ],
                    [
                        'label' => 'Affiliate Program',
                        'icon' => 'bi bi-diagram-3-fill',
                        'url' => url('/studio/affiliate-program'),
                        'active' => request()->is('studio/affiliate-program'),
                    ],
                    [
                        'label' => 'Creator Program',
                        'icon' => 'bi bi-brush-fill',
                        'url' => url('/studio/creator-program'),
                        'active' => request()->is('studio/creator-program'),
                    ],
                ],
            ],
        ];

        if (auth()->user()?->role === 'admin') {
            array_splice($sections, 1, 0, [[
                'key' => 'admin',
                'label' => 'Admin',
                'icon' => 'bi bi-shield-lock-fill',
                'items' => [
                    [
                        'label' => __('messages.Config'),
                        'icon' => 'bi bi-sliders',
                        'url' => url('admin/config'),
                        'active' => request()->segment(2) === 'config',
                        'skeleton' => '#ll-table-skeleton',
                    ],
                    [
                        'label' => __('messages.Manage Users'),
                        'icon' => 'bi bi-people-fill',
                        'url' => url('admin/users/all'),
                        'active' => request()->segment(2) === 'users',
                        'skeleton' => '#ll-table-skeleton',
                    ],
                    [
                        'label' => __('messages.Footer Pages'),
                        'icon' => 'bi bi-collection-fill',
                        'url' => url('admin/pages'),
                        'active' => request()->segment(2) === 'pages',
                        'skeleton' => '#ll-table-skeleton',
                    ],
                    [
                        'label' => __('messages.Site Customization'),
                        'icon' => 'bi bi-palette-fill',
                        'url' => url('admin/site'),
                        'active' => request()->segment(2) === 'site',
                        'skeleton' => '#ll-table-skeleton',
                    ],
                ],
            ]]);
        }

        return $sections;
    }
}
