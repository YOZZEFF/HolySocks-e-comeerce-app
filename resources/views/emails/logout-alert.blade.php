<x-mail::message>
# Logout Alert

Hello **{{ $user->name }}**,

A logout was detected on your account.

If this was you, you can ignore this email.

If you didn't make this request, please secure your account immediately.

<x-mail::button :url="config('app.url')">
Visit Site
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
