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
                'key' => 'dashboard',
                'label' => __('messages.Dashboard'),
                'icon' => 'bi bi-grid-1x2-fill',
                'url' => route('panelIndex'),
                'active' => request()->segment(1) === 'dashboard',
                'skeleton' => '#ll-page-skeleton',
                'single' => true,
            ],
            [
                'key' => 'navigation',
                'label' => 'MyLivelatch',
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
                        'label' => 'Manage My Data',
                        'icon' => 'bi bi-database-lock',
                        'url' => url('/studio/my-data'),
                        'active' => request()->is('studio/my-data'),
                        'skeleton' => '#ll-page-skeleton',
                    ],
                    [
                        'label' => 'LatchID',
                        'icon' => 'bi bi-person-vcard-fill',
                        'url' => url('/studio/latchid'),
                        'active' => request()->is('studio/latchid'),
                        'skeleton' => '#ll-card-grid-skeleton',
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
            $sections[] = [
                'key' => 'admin',
                'label' => 'Admin',
                'icon' => 'bi bi-shield-lock-fill',
                'items' => [
                    [
                        'label' => __('messages.Manage Users'),
                        'icon' => 'bi bi-people-fill',
                        'url' => url('admin/users/all'),
                        'active' => request()->segment(2) === 'users',
                        'skeleton' => '#ll-table-skeleton',
                    ],
                    [
                        'label' => 'Documentation',
                        'icon' => 'bi bi-journal-richtext',
                        'url' => url('/studio/docs'),
                        'active' => request()->is('studio/docs*'),
                        'badge' => 'New',
                    ],
                    [
                        'label' => 'Development Timeline',
                        'icon' => 'bi bi-hourglass-split',
                        'url' => url('admin/development-timeline'),
                        'active' => request()->segment(2) === 'development-timeline',
                        'skeleton' => '#ll-page-skeleton',
                    ],
                    [
                        'label' => 'Dev Tools',
                        'icon' => 'bi bi-code-square',
                        'url' => url('admin/dev-tools'),
                        'active' => request()->segment(2) === 'dev-tools',
                        'skeleton' => '#ll-page-skeleton',
                    ],
                ],
            ];
        }

        return $sections;
    }
}
