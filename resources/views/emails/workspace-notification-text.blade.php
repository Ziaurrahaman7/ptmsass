{{ $notification->title }}

{{ $notification->message }}

@if($notification->link)
Open: {{ $notification->link }}
@endif
