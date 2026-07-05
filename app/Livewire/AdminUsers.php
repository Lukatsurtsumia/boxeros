<?php

namespace App\Livewire;

use App\Models\SiteVisit;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

/**
 * Admin-only screen to see every registered fighter and remove accounts. Deleting a user
 * also wipes all of their data (logs, meals, fights, chat, plans, profile, coach memory).
 * Guarded so an admin can never delete their own account and lock themselves out.
 */
class AdminUsers extends Component
{
    public string $search = '';

    public function mount(): void
    {
        abort_unless(auth()->user()?->is_admin, 403);
    }

    public function deleteUser(int $id): void
    {
        abort_unless(auth()->user()?->is_admin, 403);

        if ($id === auth()->id()) {
            session()->flash('message', __('You cannot delete your own account.'));

            return;
        }

        $user = User::find($id);
        if (! $user) {
            return;
        }

        DB::transaction(function () use ($user) {
            $user->dailyLogs()->delete();
            $user->meals()->delete();
            $user->fights()->delete();
            $user->chatMessages()->delete();
            $user->weightEntries()->delete();
            $user->plans()->delete();
            $user->boxerProfile()->delete();
            $user->coachMemory()->delete();
            $user->delete();
        });

        session()->flash('message', __('Account deleted.'));
    }

    public function render()
    {
        $q = trim($this->search);

        $users = User::query()
            ->when($q !== '', fn ($query) => $query->where(fn ($w) => $w
                ->where('name', 'like', "%{$q}%")
                ->orWhere('email', 'like', "%{$q}%")))
            ->orderByDesc('created_at')
            ->limit(100)
            ->get();

        return view('livewire.admin-users', [
            'users' => $users,
            'total' => User::count(),
            'trialing' => User::whereNotNull('trial_ends_at')->where('trial_ends_at', '>', now())->count(),
            'subscribers' => User::whereIn('paddle_status', ['active', 'trialing'])->count(),
            'visitorsToday' => SiteVisit::whereDate('visited_on', today())->count(),
            'visitors7d' => SiteVisit::where('visited_on', '>=', today()->subDays(6))->distinct('visitor_hash')->count('visitor_hash'),
            'visitorsTotal' => SiteVisit::distinct('visitor_hash')->count('visitor_hash'),
        ])->layout('layouts.app');
    }
}
