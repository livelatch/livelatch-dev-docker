<?php

namespace App\Http\Livewire;

use App\Services\DashboardAnalyticsService;
use Livewire\Component;

class DashboardAnalytics extends Component
{
    public array $analytics = [];

    public function mount(): void
    {
        $user = auth()->user();

        $this->analytics = $user
            ? app(DashboardAnalyticsService::class)->forUser($user)
            : [];
    }

    public function render()
    {
        return view('livewire.dashboard-analytics');
    }
}
