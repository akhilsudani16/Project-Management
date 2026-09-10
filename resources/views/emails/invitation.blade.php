<x-mail::message>
# You're Invited!

Hello **{{ $user->name }}**,

{{ $invitedBy->name }} has invited you to join **{{ $organization->name }}**@if($project) for the project **{{ $project->name }}**@endif.

## Next Steps

Click the button below to accept your invitation and set your password:

<x-mail::button :url="$acceptUrl">
Accept Invitation
</x-mail::button>

@if($project)
**Project:** {{ $project->name }}  
@endif
**Organization:** {{ $organization->name }}  
**Invited by:** {{ $invitedBy->name }}

## Important

- This invitation will expire on **{{ \Carbon\Carbon::parse($expiresAt)->format('F j, Y \a\t g:i A') }}**
- The link can only be used once
- You'll need to set a password when accepting the invitation

If you didn't expect this invitation, you can safely ignore this email.

Thanks,<br>
{{ config('app.name') }}

---

<x-mail::subcopy>
If you're having trouble clicking the "Accept Invitation" button, copy and paste the URL below into your web browser:

{{ $acceptUrl }}
</x-mail::subcopy>
</x-mail::message>
