Hi {{ $invitation->name }},

{{ $invitation->inviter?->name ?? 'A teammate' }} ({{ $invitation->inviter?->email }}) added you to {{ $invitation->company?->name ?? config('app.name') }} as {{ $invitation->roleLabel() }}.

Create your password using this link (expires in 7 days):

{{ $acceptUrl }}

If you were not expecting this invite, you can ignore this email.
