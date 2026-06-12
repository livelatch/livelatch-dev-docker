<?php

namespace App\Http\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class DashboardAnalytics extends Component
{
    public string $displayHandle = 'your-profile';
    public array $quickLinks = [];
    public array $overviewCards = [];
    public array $sampleActivity = [];
    public array $sampleTraffic = [];
    public array $sampleBreakdown = [];

    public function mount(): void
    {
        $user = Auth::user();

        $this->displayHandle = $user?->littlelink_name ?: 'your-profile';
        $this->quickLinks = [
            [
                'title' => 'Manage links',
                'hint' => 'Create and reorder profile blocks.',
                'url' => url('/studio/links'),
                'icon' => 'bi bi-link-45deg',
                'external' => false,
            ],
            [
                'title' => 'Edit profile',
                'hint' => 'Update profile details and hero content.',
                'url' => url('/studio/page'),
                'icon' => 'bi bi-person-badge',
                'external' => false,
            ],
            [
                'title' => 'Theme settings',
                'hint' => 'Adjust light and dark mode design tokens.',
                'url' => url('/studio/theme'),
                'icon' => 'bi bi-stars',
                'external' => false,
            ],
            [
                'title' => 'View public page',
                'hint' => 'Open how visitors see your profile.',
                'url' => url('/@' . $this->displayHandle),
                'icon' => 'bi bi-box-arrow-up-right',
                'external' => true,
            ],
        ];

        $this->overviewCards = [
            ['label' => 'Profile views', 'value' => 1234, 'delta' => '+8.4%', 'icon' => 'bi bi-eye-fill'],
            ['label' => 'Link clicks', 'value' => 842, 'delta' => '+5.1%', 'icon' => 'bi bi-cursor-fill'],
            ['label' => 'CTR', 'value' => 68.2, 'suffix' => '%', 'delta' => '+1.7%', 'icon' => 'bi bi-graph-up-arrow'],
            ['label' => 'Returning visitors', 'value' => 412, 'delta' => '+2.3%', 'icon' => 'bi bi-arrow-repeat'],
        ];

        $this->sampleActivity = [
            ['title' => 'Pinned a new top link', 'meta' => '2h ago', 'state' => 'Updated'],
            ['title' => 'Changed profile headline', 'meta' => 'Yesterday', 'state' => 'Edited'],
            ['title' => 'Theme preview exported', 'meta' => '2 days ago', 'state' => 'Saved'],
            ['title' => 'Synced social cards', 'meta' => '4 days ago', 'state' => 'Published'],
        ];

        $this->sampleTraffic = [
            ['label' => 'Mon', 'value' => 62],
            ['label' => 'Tue', 'value' => 71],
            ['label' => 'Wed', 'value' => 66],
            ['label' => 'Thu', 'value' => 79],
            ['label' => 'Fri', 'value' => 84],
            ['label' => 'Sat', 'value' => 57],
            ['label' => 'Sun', 'value' => 49],
        ];

        $this->sampleBreakdown = [
            ['label' => 'Instagram', 'value' => 38],
            ['label' => 'TikTok', 'value' => 27],
            ['label' => 'YouTube', 'value' => 21],
            ['label' => 'Direct', 'value' => 14],
        ];
    }

    public function render()
    {
        return view('livewire.dashboard-analytics');
    }
}
