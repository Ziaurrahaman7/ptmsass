<x-company-layout :title="'Priorities'">

<div style="display:flex; justify-content:center; padding-top:20px;">
    @include('company.priorities._editor', ['closeAction' => "window.location.href='" . route('company.dashboard', $slug) . "'"])
</div>

</x-company-layout>
