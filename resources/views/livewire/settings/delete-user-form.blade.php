<?php

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\MembershipRequest;
use Livewire\Volt\Component;

new class extends Component {
    public string $password = '';

    /**
     * Delete the currently authenticated user.
     */
    public function deleteUser(Logout $logout): void
    {
        $this->validate([
            'password' => ['required', 'string', 'current_password'],
        ]);

        $user = Auth::user();

        // Check if user is the sole admin in any organization
        $isSoleAdmin = false;
        $soleAdminOrgs = [];
        
        $adminOrgs = $user->organizations()->wherePivot('role', 'admin')->get();
        foreach ($adminOrgs as $org) {
            $adminCount = $org->users()->wherePivot('role', 'admin')->count();
            if ($adminCount <= 1) {
                $isSoleAdmin = true;
                $soleAdminOrgs[] = $org->name;
            }
        }

        if ($isSoleAdmin) {
            $orgList = implode(', ', $soleAdminOrgs);
            $this->addError('password', "Anda tidak dapat menghapus akun karena Anda adalah satu-satunya Admin di organisasi berikut: {$orgList}. Silakan transfer hak Admin atau hapus organisasi terlebih dahulu.");
            return;
        }

        // Cancel pending requests
        MembershipRequest::where('user_id', $user->id)->where('status', 'pending')->update(['status' => 'cancelled']);
        \App\Models\Borrowing::where('user_id', $user->id)->where('status', 'pending')->update(['status' => 'cancelled']);

        // Remove memberships (detach)
        $user->organizations()->detach();
        
        // Let the borrowings intact. 

        tap($user, $logout(...))->delete(); // This performs a soft delete based on the model configuration

        $this->redirect('/', navigate: true);
    }
}; ?>

<section class="mt-10 space-y-6">
    <div class="relative mb-5">
        <flux:heading>Hapus Akun</flux:heading>
        <flux:subheading>Hapus akun Anda secara permanen. Tindakan ini tidak dapat dibatalkan.</flux:subheading>
    </div>

    <flux:modal.trigger name="confirm-user-deletion">
        <flux:button variant="danger">
            Hapus Akun
        </flux:button>
    </flux:modal.trigger>

    <flux:modal name="confirm-user-deletion" :show="$errors->isNotEmpty()" focusable class="max-w-lg">
        <form wire:submit="deleteUser" class="space-y-6">
            <div>
                <flux:heading size="lg">Apakah Anda yakin ingin menghapus akun Anda?</flux:heading>

                <flux:subheading>
                    Setelah akun Anda dihapus, semua data profil dan keanggotaan Anda akan dihapus secara permanen. Riwayat peminjaman akan tetap dipertahankan. Silakan masukkan kata sandi Anda untuk mengonfirmasi bahwa Anda ingin menghapus akun Anda secara permanen.
                </flux:subheading>
            </div>

            <flux:input wire:model="password" id="password" label="Kata Sandi" type="password" name="password" required />

            <div class="flex justify-end space-x-2">
                <flux:modal.close>
                    <flux:button variant="filled">Batal</flux:button>
                </flux:modal.close>

                <flux:button variant="danger" type="submit">Hapus Akun</flux:button>
            </div>
        </form>
    </flux:modal>
</section>
