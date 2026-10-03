<?php

use App\Actions\Membership\ApproveJoinRequest;
use App\Actions\Membership\ChangeMemberRole;
use App\Actions\Membership\InviteMember;
use App\Actions\Membership\RejectJoinRequest;
use App\Actions\Membership\RemoveMember;
use App\Actions\Membership\RevokeInvitation;
use App\Http\Requests\Membership\InviteMemberRequest;
use App\Models\MembershipRequest;
use App\Models\Organization;
use App\Models\OrganizationInvitation;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.manage')] class extends Component {
    public Organization $organization;
    public string $tab = 'members'; // members, requests, invites

    // Invite form
    public string $inviteEmail = '';
    public string $inviteRole = 'member';

    public function mount(Organization $organization)
    {
        $this->organization = $organization;
    }

    public function getMembersProperty()
    {
        return $this->organization->users()->get();
    }

    public function getJoinRequestsProperty()
    {
        return $this->organization->membershipRequests()->where('status', 'pending')->with('user')->get();
    }

    public function getInvitationsProperty()
    {
        return $this->organization->invitations()->with('inviter')->latest()->get();
    }

    public function setTab($tab)
    {
        $this->tab = $tab;
    }

    public function changeRole(ChangeMemberRole $action, int $userId, string $role)
    {
        try {
            $user = User::findOrFail($userId);
            $action->execute($this->organization, $user, $role);
            session()->flash('success', "Role {$user->name} berhasil diubah.");
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function removeMember(RemoveMember $action, int $userId)
    {
        try {
            $user = User::findOrFail($userId);
            $action->execute($this->organization, $user);
            session()->flash('success', "Anggota {$user->name} berhasil dikeluarkan.");
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function approveRequest(ApproveJoinRequest $action, int $requestId)
    {
        $req = MembershipRequest::findOrFail($requestId);
        $action->execute($req, Auth::user());
        session()->flash('success', 'Permintaan bergabung disetujui.');
    }

    public function rejectRequest(RejectJoinRequest $action, int $requestId)
    {
        $req = MembershipRequest::findOrFail($requestId);
        $action->execute($req, Auth::user());
        session()->flash('success', 'Permintaan bergabung ditolak.');
    }

    public function invite(InviteMember $action)
    {
        $request = new InviteMemberRequest();
        $this->validate($request->rules());

        try {
            $action->execute($this->organization, Auth::user(), $this->inviteEmail, $this->inviteRole);
            session()->flash('success', 'Undangan berhasil dikirim.');
            $this->inviteEmail = '';
            $this->inviteRole = 'member';
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function revokeInvite(RevokeInvitation $action, int $inviteId)
    {
        $invitation = OrganizationInvitation::findOrFail($inviteId);
        $action->execute($invitation);
        session()->flash('success', 'Undangan dibatalkan.');
    }
    
    public function resendInvite(InviteMember $action, int $inviteId)
    {
        $invitation = OrganizationInvitation::findOrFail($inviteId);
        $action->execute($this->organization, Auth::user(), $invitation->email, $invitation->role);
        session()->flash('success', 'Undangan berhasil dikirim ulang.');
    }
}; ?>


    <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg mb-6 p-6">
            <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Kelola Anggota</h2>
            <p class="text-gray-500 dark:text-gray-400 text-sm mt-1">Mengelola anggota, permintaan bergabung, dan undangan.</p>
        </div>

        @if (session()->has('success'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif
        @if (session()->has('error'))
            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg">
            <div class="border-b border-gray-200 dark:border-gray-700">
                <nav class="-mb-px flex" aria-label="Tabs">
                    <button wire:click="setTab('members')" class="{{ $tab === 'members' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }} w-1/3 py-4 px-1 text-center border-b-2 font-medium text-sm">
                        Anggota
                    </button>
                    <button wire:click="setTab('requests')" class="{{ $tab === 'requests' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }} w-1/3 py-4 px-1 text-center border-b-2 font-medium text-sm">
                        Permintaan Bergabung
                    </button>
                    <button wire:click="setTab('invites')" class="{{ $tab === 'invites' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }} w-1/3 py-4 px-1 text-center border-b-2 font-medium text-sm">
                        Undangan
                    </button>
                </nav>
            </div>

            <div class="p-6">
                @if($tab === 'members')
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Nama</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Email</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Role</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tgl Gabung</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($this->members as $member)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">{{ $member->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $member->email }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            <select wire:change="changeRole({{ $member->id }}, $event.target.value)" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                                <option value="admin" {{ $member->pivot->role === 'admin' ? 'selected' : '' }}>Admin</option>
                                                <option value="staff" {{ $member->pivot->role === 'staff' ? 'selected' : '' }}>Staff</option>
                                                <option value="member" {{ $member->pivot->role === 'member' ? 'selected' : '' }}>Member</option>
                                            </select>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ \Carbon\Carbon::parse($member->pivot->joined_at)->format('d M Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <button wire:click="removeMember({{ $member->id }})" wire:confirm="Yakin ingin mengeluarkan anggota ini?" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">Hapus</button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                @elseif($tab === 'requests')
                    @if($this->joinRequests->isEmpty())
                        <div class="text-center py-10 text-gray-500 dark:text-gray-400">
                            Tidak ada permintaan bergabung.
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-900">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">User</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Pesan</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tanggal</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($this->joinRequests as $req)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                                {{ $req->user->name }}<br>
                                                <span class="text-xs text-gray-500">{{ $req->user->email }}</span>
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $req->message ?? '-' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                {{ $req->created_at->format('d M Y H:i') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <button wire:click="approveRequest({{ $req->id }})" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 mr-3">Terima</button>
                                                <button wire:click="rejectRequest({{ $req->id }})" class="text-red-600 hover:text-red-900 dark:text-red-400">Tolak</button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                @elseif($tab === 'invites')
                    <div class="mb-8 bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Kirim Undangan Baru</h3>
                        <form wire:submit="invite" class="flex flex-col sm:flex-row gap-4">
                            <div class="flex-1">
                                <x-input-label for="inviteEmail" value="Email" />
                                <x-text-input wire:model="inviteEmail" id="inviteEmail" type="email" class="mt-1 block w-full" required />
                                <x-input-error :messages="$errors->get('inviteEmail')" class="mt-2" />
                            </div>
                            <div class="sm:w-48">
                                <x-input-label for="inviteRole" value="Role" />
                                <select wire:model="inviteRole" id="inviteRole" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                    <option value="member">Member</option>
                                    <option value="staff">Staff</option>
                                </select>
                                <x-input-error :messages="$errors->get('inviteRole')" class="mt-2" />
                            </div>
                            <div class="flex items-end">
                                <x-primary-button class="mb-1">Undang</x-primary-button>
                            </div>
                        </form>
                    </div>

                    @if($this->invitations->isEmpty())
                        <div class="text-center py-10 text-gray-500 dark:text-gray-400">
                            Belum ada undangan yang dikirim.
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-900">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Email</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Role</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($this->invitations as $invite)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                                {{ $invite->email }}<br>
                                                <span class="text-xs text-gray-500">Oleh: {{ $invite->inviter->name }}</span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ ucfirst($invite->role) }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                @if($invite->status === 'pending' && $invite->expires_at->isPast())
                                                    <span class="text-red-500">Expired</span>
                                                @else
                                                    <span class="
                                                        {{ $invite->status === 'accepted' ? 'text-green-600' : '' }}
                                                        {{ $invite->status === 'pending' ? 'text-yellow-600' : '' }}
                                                        {{ $invite->status === 'revoked' ? 'text-red-600' : '' }}
                                                    ">
                                                        {{ ucfirst($invite->status) }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                @if($invite->status === 'pending' && !$invite->expires_at->isPast())
                                                    <button wire:click="revokeInvite({{ $invite->id }})" class="text-red-600 hover:text-red-900 mr-3">Batalkan</button>
                                                @endif
                                                @if(in_array($invite->status, ['pending', 'expired', 'revoked']))
                                                    <button wire:click="resendInvite({{ $invite->id }})" class="text-indigo-600 hover:text-indigo-900">Kirim Ulang</button>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>

