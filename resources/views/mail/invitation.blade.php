<x-mail::message>
# Hello,

You have been invited to join **Silo**, the private disk image repository.

Click the button below to create your account and start downloading:

<x-mail::button :url="route('register', ['token' => $invitation->token, 'email' => $invitation->email])">
Create Account
</x-mail::button>

This link is for one-time use only. If you weren't expecting this invitation, you can safely ignore this email.

Thanks,<br>
The {{ config('app.name') }} Team
</x-mail::message>
