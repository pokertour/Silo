<?php

namespace App\Livewire;

use App\Models\IsoFile;
use App\Models\ShareLink;
use Illuminate\Support\Str;
use LivewireUI\Modal\ModalComponent;

class ShareModal extends ModalComponent
{
    public $isoId;

    public $isoName;

    public $password = '';

    public $max_downloads = null;

    public $expires_at = null;

    public $generated_url = null;

    public function mount($isoId)
    {
        $iso = IsoFile::findOrFail($isoId);

        if (auth()->user()->cannot('share', $iso)) {
            abort(403);
        }

        $this->isoId = $iso->id;
        $this->isoName = $iso->name;
    }

    public function generatePassword()
    {
        $this->password = Str::password(16, symbols: false);
    }

    public function generate()
    {
        $iso = IsoFile::findOrFail($this->isoId);
        if (auth()->user()->cannot('share', $iso)) {
            abort(403);
        }

        $this->validate([
            'password' => 'nullable|string|min:8|max:255',
            'max_downloads' => 'nullable|integer|min:1|max:1000000',
            'expires_at' => 'nullable|date|after:now',
        ]);

        $token = Str::random(32);

        ShareLink::create([
            'iso_file_id' => $this->isoId,
            'user_id' => auth()->id(),
            'token' => $token,
            'password' => $this->password ?: null,
            'max_downloads' => $this->max_downloads ?: null,
            'expires_at' => $this->expires_at ?: null,
        ]);

        $this->generated_url = route('share.show', $token);
    }

    public static function modalMaxWidth(): string
    {
        return 'md';
    }

    public function render()
    {
        return view('livewire.share-modal');
    }
}
