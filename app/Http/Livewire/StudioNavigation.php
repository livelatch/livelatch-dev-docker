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
                        'label' => 'Blocks',
                        'icon' => 'bi bi-link-45deg',
                        'url' => url('/studio/links'),
                        'active' => in_array(request()->segment(2), ['links', 'add-link'], true),
                        'skeleton' => '#ll-profile-skeleton',
                    ],
                    [
                        'label' => 'Profile',
                        'icon' => 'bi bi-person-badge-fill',
                        'url' => url('/studio/page'),
                        'active' => request()->segment(2) === 'page',
                        'skeleton' => '#ll-profile-skeleton',
                    ],
                    [
                        'label' => __('messages.Themes'),
                        'icon' => 'bi bi-stars',
                        'url' => url('/studio/themes-beta'),
                        'active' => in_array(request()->segment(2), ['theme', 'themes-beta'], true),
                        'skeleton' => '#ll-profile-skeleton',
                    ],
                ],
            ],
            [
                'key' => 'latchapps',
                'label' => 'LatchApps',
                'icon' => 'bi bi-grid-3x3-gap-fill',
                'items' => [
                    [
                        'label' => 'About LatchApps',
                        'icon' => 'bi bi-info-circle-fill',
                        'url' => url('/studio/latchapps'),
                        'active' => request()->is('studio/latchapps'),
                        'skeleton' => '#ll-page-skeleton',
                    ],
                    [
                        'label' => 'LatchDeck',
                        'icon' => 'bi bi-grid-3x3-gap-fill',
                        'url' => url('/studio/latchdeck'),
                        'active' => request()->is('studio/latchdeck'),
                        'skeleton' => '#ll-card-grid-skeleton',
                        'badge' => 'In development',
                    ],
                    [
                        'label' => 'Fax',
                        'icon' => 'bi bi-send-fill',
                        'url' => url('/studio/fax'),
                        'active' => request()->is('studio/fax'),
                        'skeleton' => '#ll-page-skeleton',
                        'badge' => 'Soon™',
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
                        'label' => 'Theme Manager',
                        'icon' => 'bi bi-palette-fill',
                        'url' => url('admin/theme-manager'),
                        'active' => request()->segment(2) === 'theme-manager',
                        'skeleton' => '#ll-page-skeleton',
                    ],
                    [
                        'label' => 'Creator Requests',
                        'icon' => 'bi bi-fire',
                        'url' => url('admin/creator-requests'),
                        'active' => request()->segment(2) === 'creator-requests',
                        'skeleton' => '#ll-table-skeleton',
                    ],
                    [
                        'label' => 'User Requests',
                        'icon' => 'bi bi-person-badge-fill',
                        'url' => url('admin/user-requests'),
                        'active' => request()->segment(2) === 'user-requests',
                        'skeleton' => '#ll-table-skeleton',
                    ],
                    [
                        'label' => 'Impersonation Mitigation',
                        'icon' => 'bi bi-shield-lock-fill',
                        'url' => url('admin/impersonation'),
                        'active' => request()->segment(2) === 'impersonation',
                        'skeleton' => '#ll-table-skeleton',
                    ],
                    [
                        'label' => 'Username Blacklist',
                        'icon' => 'bi bi-slash-circle-fill',
                        'url' => url('admin/username-blacklist'),
                        'active' => request()->segment(2) === 'username-blacklist',
                        'skeleton' => '#ll-table-skeleton',
                    ],
                    [
                        'label' => 'Dev Tools',
                        'icon' => 'bi bi-code-square',
                        'url' => url('admin/dev-tools'),
                        'active' => request()->segment(2) === 'dev-tools',
                        'skeleton' => '#ll-page-skeleton',
                    ],
                    [
                        'label' => 'Tour Builder',
                        'icon' => 'bi bi-signpost-split-fill',
                        'url' => url('admin/tour-builder'),
                        'active' => request()->segment(2) === 'tour-builder',
                        'external' => true,
                    ],
                ],
            ];

            // The Shell is locked to specific user IDs (config/shell.php); only
            // show the nav link to those users — everyone else gets a 403 anyway.
            $shellAllowed = array_map('strval', (array) config('shell.allowed_user_ids', []));
            if (in_array((string) auth()->id(), $shellAllowed, true)) {
                $sections[array_key_last($sections)]['items'][] = [
                    'label' => 'Shell',
                    'icon' => 'bi bi-terminal-fill',
                    'url' => url('admin/shell'),
                    'active' => request()->segment(2) === 'shell',
                    'skeleton' => '#ll-page-skeleton',
                ];
            }
        }

        return $sections;
    }
}
